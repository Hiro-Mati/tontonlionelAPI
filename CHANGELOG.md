# Changelog

All notable changes to the **Compliance Store** project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `Holder` aggregate persisted in full (`birth_date`, `status`, GDPR timestamps) with a dedicated `birth_date` Doctrine type; `GET /v1/holders/{id}` exposed through `GetHolderUseCase` and `GetHolderProvider`.
- `Document.holderId` persisted (`holder_id` column, indexed); `StoreDocumentUseCase` rejects an unknown holder with `HolderNotFoundException` (422 on `POST /v1/documents`).
- `Document` entity and Value Objects (`DocumentId`, `OwnerId`, `DocumentMetadata`, `FileHash`, `EncryptedPayload`, `WrappedDataKey`, `StorageKey`, `EncryptionEnvelope`).
- `SodiumCipherGateway` implementation for Libsodium envelope encryption.
- `MinioStorageGateway` implementation for S3-compatible document storage (Flysystem).
- `StoreDocumentUseCase` exposed as `POST /v1/documents` (multipart upload).

### Removed
- `RegulatoryScope` feature (entity, value objects, use cases, repository, API resource, `DocumentComplianceChecker` domain service and `NonCompliantDocumentException`): never released, dropped before its first stable version.

### Changed
- Dependencies: Symfony 8.1.9, Doctrine ORM 3.7.5 and DBAL 4.5.0, PHPStan 2.3.1, PHPUnit 13.4.3, Deptrac 4.7.2. API Platform stays on 4.3.
- `HolderRepository` moved to `Infrastructure\Persistence\Doctrine` next to the other Doctrine repositories.

### Fixed
- `POST /v1/documents` answered 500 since `StoreDocumentCommand` requires a `holderId`: the processor now reads a `holderId` multipart field and validates it (`NotBlank`, `Uuid`).
- `BirthDate::fromString()` rejects malformed or auto-corrected dates with a 422 instead of a `TypeError`.
- `HolderRepository` was bound to `StorageSpace`; it now targets `Holder` and looks up by `HolderId`.
- `GeneratorInterface` declares `generate(): string`; `HolderProcessor` validates its inputs and maps domain exceptions to 409/422.
- Development stack: `Caddyfile.dev` aligned with Mercure >= 0.21 (named hub, `issuer` block, no `demo` directive) and `doctrine/dbal` bumped to 4.5.0 so `doctrine:schema:update` works with ORM 3.6.9.

### Planned
- `ConsultDocumentUseCase` (`GET /v1/documents/{id}`).

## [0.1.0] - 2026-07-24

### Added
- Symfony 7 & API Platform 3 skeleton with FrankenPHP and Caddy runtime.
- Docker Compose configuration featuring PostgreSQL 16 database and MinIO local S3 storage.
- Clean Architecture & DDD folder layout under `src/` (`Domain`, `Application`, `Infrastructure`, `Domain/Port/Gateway`).
- Comprehensive architecture documentation and ADRs (ADR 0001, ADR 0002, ADR 0003).
- Initial project configuration (`composer.json`, `README.md`).
