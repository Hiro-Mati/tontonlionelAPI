# Compliance Store API

[![CI](https://github.com/lionelkouame/compliance-store/actions/workflows/ci.yaml/badge.svg)](https://github.com/lionelkouame/compliance-store/actions/workflows/ci.yaml)
![PHP](https://img.shields.io/badge/PHP-%3E%3D%208.4-777BB4?logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-8.1-000000?logo=symfony&logoColor=white)
![API Platform](https://img.shields.io/badge/API%20Platform-v4.3-30B69E?logo=apiplatform&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)
![FrankenPHP](https://img.shields.io/badge/FrankenPHP-Ready-00A88F?logo=frankenphp&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)
![PHPUnit](https://img.shields.io/badge/PHPUnit-Passing-3776AB?logo=phpunit&logoColor=white)
![Super-Linter](https://img.shields.io/badge/Super--Linter-Passing-2088FF?logo=githubactions&logoColor=white)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**Compliance Store** is a highly available, secure open-source REST API designed for managing the full lifecycle of sensitive document storage (IDs, passports, utility bills).

It provides *Zero Trust* envelope encryption (Libsodium) for every stored document and integrates with any S3-compatible storage backend (AWS S3, MinIO) via Flysystem.

---

## 🏛️ Architecture & Design

The application is built with **PHP 8.4+**, **Symfony 8.1**, and **API Platform 4**, strictly adhering to the principles of **Clean Architecture** and **Domain-Driven Design (DDD)**:

```text
src/
├── Domain/              # Pure business logic (Entities, Value Objects, Ports)
│   └── Port/            # Interfaces (Gateways, Repositories, Services)
├── Application/         # Orchestration & Use Cases (Use Cases & DTOs)
└── Infrastructure/      # Technical implementations
    ├── Persistence/     # Doctrine repositories, mappings & custom types (PostgreSQL)
    ├── Presentation/    # API Platform resources & state providers/processors
    ├── Storage/         # MinIO / S3-compatible document storage (Flysystem)
    ├── Gateway/         # Libsodium envelope encryption
    ├── Security/        # API clients, bearer API key authentication (ADR 0013)
    ├── Audit/           # Audit trail adapter (actor = authenticated API client)
    ├── Console/         # app:api-client:create / app:api-client:revoke
    └── Service/         # UUID generators
```

To explore the detailed architecture documentation:
* 📘 [Documentation Table of Contents](docs/README.md)
* 📜 [Architecture Decision Records (ADRs)](docs/architecture/adr/)
* 🩹 [Documents correctifs](docs/correctifs/README.md) (interventions du 24 octobre 2026, journal de bord)

---

## 🚀 Quick Start (Development Environment)

The application runs on **FrankenPHP** and **Caddy** powered by Docker Compose.

### Prerequisites
* Docker & Docker Compose (v2.10+)

### 🌐 Local DNS Setup (`compliance-store.loc`)

Map `compliance-store.loc` to `127.0.0.1` on your development host:
* **Linux / macOS**: Run `make setup-dns` or execute `echo "127.0.0.1 compliance-store.loc" | sudo tee -a /etc/hosts`
* **Windows**: Add `127.0.0.1 compliance-store.loc` to `C:\Windows\System32\drivers\etc\hosts`

### Launching the Stack

1. **Build Docker images**:
   ```bash
   make build
   ```

2. **Start all services** (FrankenPHP API + PostgreSQL + MinIO S3):
   ```bash
   make up
   ```
   `make` loads `.env.local` before calling Docker Compose; a bare `docker compose up` does not. Use that file to override published ports when they are taken on your host, for example `POSTGRES_PORT=5434`.

3. **Access the application**:
   * **API Platform / Swagger UI**: `https://compliance-store.loc/api` (or `http://compliance-store.loc/api`)
   * **MinIO Web Console (Local S3)**: `http://localhost:9001` *(User: `minioadmin` / Pass: `minioadmin`)*

4. **Create an API key** (every endpoint but `GET /v1/health` requires one):
   ```bash
   make sf c="app:api-client:create back-office --role=ROLE_ADMIN"
   ```
   The key (`cs_...`) is printed once; send it as `Authorization: Bearer cs_...`. Roles: `ROLE_ADMIN` > `ROLE_OPERATOR` > `ROLE_READER`. Revoke with `make sf c="app:api-client:revoke back-office"`. For the `tests/http` requests, copy `tests/http/http-client.private.env.json.dist` to `http-client.private.env.json` and paste the key.

5. **Stop the environment**:
   ```bash
   make down
   ```

---

### Already have a checkout? Do this after pulling

These steps apply to **every** developer, integrator and operator, not only to the person who made the changes:

1. `make build && make up`: the PHP upload limits live in `frankenphp/conf.d/10-app.ini`, which is baked into the image.
2. `make migrate`: new `api_client` and `audit_event` tables, nullable `holder.birth_date`.
3. Create your own API key (see step 4 above) with the lowest role that fits your work, and paste it into `tests/http/http-client.private.env.json` (copied from the `.dist` file, ignored by Git) or into Swagger UI's "Authorize" dialog.
4. `make test` must end with Deptrac 0 violation, PHPStan 0 error, PHPUnit 159 tests.

Open design decisions (key rotation and expiry, rate limiting, audit log retention, anonymized record visibility…) are listed in [docs/correctifs/08-corrections-prioritaires.md](docs/correctifs/08-corrections-prioritaires.md#88-ce-qui-reste-à-décider) and [07-audit.md](docs/correctifs/07-audit.md#78-points-de-conception-non-tranchés): they need the project owner's call before anyone implements them.

---

## 🔌 API Overview (`/api/v1`)

| Endpoint | Role | Purpose |
|----------|------|---------|
| `GET /health` | public | Liveness, environment and version |
| `POST /holders`, `GET /holders/{id}` | operator / reader | Register and read a data subject |
| `POST /holders/{id}/erasure-request`, `POST /holders/{id}/anonymization` | admin | GDPR art. 17: flag, then destroy the documents and anonymize the record |
| `POST /documents` (multipart `file` + `holderId`) | operator | Encrypt and store a document (size and type limits: `DOCUMENT_MAX_SIZE`, `DOCUMENT_MIME_TYPES`) |
| `GET /documents/{id}`, `GET /holders/{id}/documents` | reader | Metadata, list per holder |
| `GET /documents/{id}/content` (`Accept: application/octet-stream`) | operator | Decrypted download, integrity-checked, audited |
| `POST /identity-documents` | operator | Register a passport or ID card |
| `POST`/`GET`/`PATCH /storage-spaces` | admin / reader | Storage spaces |
| `GET /audit-events?subjectId=&limit=` | admin | Who did what to which holder or document |

---

## 🛠️ Technology Stack

* **Framework**: Symfony 8.1 + API Platform 4
* **HTTP Server / Runtime**: FrankenPHP + Caddy (Worker Mode)
* **Database**: PostgreSQL 16
* **Object Storage**: MinIO (S3-compatible) / League Flysystem
* **Cryptography**: Libsodium (`sodium_crypto_secretbox`)

---

## 📖 Underlying Template

This project is built upon the [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker) template.
For original Docker/FrankenPHP infrastructure documentation, refer to [README.symfony-docker.md](README.symfony-docker.md) or the [docs/docker-infra/](docs/docker-infra/) directory.

---

## 📄 License

This project is released under the MIT License.
