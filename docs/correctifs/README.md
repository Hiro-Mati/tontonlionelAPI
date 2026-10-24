# Documents correctifs

Rapport complet des interventions réalisées le 24 octobre 2026 sur la copie de travail du projet **Compliance Store** (dépôt `Hiro-Mati/tontonlionelAPI`, issu de `lionelkouame/compliance-store`).

Cette documentation est indépendante de la documentation existante (`docs/README.md`, ADR, workflows) : elle décrit uniquement ce qui a été changé, pourquoi, et comment le vérifier.

## Sommaire

| # | Document | Sujet |
|---|----------|-------|
| 1 | [Environnement de développement local](01-environnement-local.md) | Installation de la stack sur Windows, ports, `.env.local`, `make` |
| 2 | [Stack Docker : Mercure et FrankenPHP](02-stack-docker-mercure.md) | Conteneur PHP en boucle de redémarrage, Caddyfile et `compose.override.yaml` |
| 3 | [Doctrine : montée de DBAL en 4.5](03-doctrine-dbal.md) | `doctrine:schema:update` cassé, base de test vide, 29 tests en erreur |
| 4 | [Code : 9 erreurs PHPStan et test en échec](04-phpstan-holder.md) | Agrégat Holder, `DocumentProcessor`, `BirthDate`, tests API |
| 5 | [Dépôt Git et Dependabot](05-depot-git-et-dependabot.md) | Commits, branches, push vers le dépôt personnel, désactivation de Dependabot |

## Résumé en une page

**Point de départ.** Le dépôt venait d'être cloné sur un poste Windows 11 sans PHP ni Composer, avec Docker Desktop installé mais arrêté. Les deux derniers commits (`feat: wip`) introduisaient l'agrégat `Holder` et n'étaient pas terminés.

**Constats.**

1. Le conteneur PHP redémarrait en boucle : l'image `dunglas/frankenphp:1-php8.5` embarque désormais un module Mercure plus strict que ce que la `Caddyfile.dev` du projet déclarait.
2. `doctrine:schema:update` échouait : `doctrine/orm` 3.6.9 et `symfony/doctrine-bridge` 8.1 exigent `doctrine/dbal` 4.5, le lock installait 4.4.4. La base de test restait vide et 29 tests API tombaient.
3. PHPStan (niveau max) signalait 9 erreurs dans le code `Holder` en cours, et `POST /api/v1/documents` répondait 500.
4. Un PostgreSQL Windows occupait déjà les ports 5432 et 5433.

**Corrections.**

| Zone | Fichiers | Résultat |
|------|----------|----------|
| Stack Docker | `frankenphp/Caddyfile.dev`, `compose.override.yaml` | Conteneur PHP sain, API en HTTP et HTTPS |
| Dépendances | `composer.lock` | `doctrine/dbal` 4.4.4 → 4.5.0, `schema:update` fonctionne |
| Code | 9 fichiers `src/`, 2 fichiers `tests/`, `CHANGELOG.md` | PHPStan 0 erreur, PHPUnit 88 tests OK |
| Dépôt personnel | `.github/dependabot.yaml` supprimé | Plus de PR automatiques |

**Vérification finale.** `make test` passe intégralement : Deptrac 0 violation, PHPStan 0 erreur, PHPUnit 88 tests et 159 assertions (81 tests et 1 échec avant intervention).

**Commits.**

| Commit | Branche | Message |
|--------|---------|---------|
| `726d17b` | `main`, `fix/holder-phpstan-and-dev-stack` | `fix(dev-stack): run on current FrankenPHP image and DBAL 4.5` |
| `77465e6` | `main`, `fix/holder-phpstan-and-dev-stack` | `fix(holder): resolve PHPStan errors and failing document upload` |
| `cb43859` | `main` uniquement | `chore: disable Dependabot on this personal copy` |

## Ce qui n'a pas été fait

Ces points relèvent du même chantier `Holder` mais demandent une décision de conception. Ils sont détaillés à la fin du document 4.

- `Document.holderId` n'est ni mappé dans Doctrine ni persisté en base.
- `Holder.orm.xml` ne mappe que `id`, `name` et `createdAt`.
- La migration `Version20261024020529` crée `name VARCHAR(20)` alors que le mapping et `HolderName` acceptent 255 caractères.
- `HolderRepository` est rangé dans `Persistence/` et non dans `Persistence/Doctrine/` comme les autres.
- `HolderAlreadyExistsException` teste un UUID fraîchement généré, ce qui n'a pas d'intérêt pratique.
