# Journal de bord

Chronologie des interventions sur la copie de travail `Hiro-Mati/tontonlionelAPI`. Une entrée par action, dans l'ordre. Les détails techniques sont dans les documents numérotés de ce dossier.

Convention : heure locale (Paris), commit cité quand il existe.

## 24 octobre 2026

| Heure | Action | Résultat | Référence |
|-------|--------|----------|-----------|
| 05:05 | Clonage de `lionelkouame/compliance-store` | Dépôt à `2eb6ec5` (premier commit wip Holder en tête ; deux autres seront tirés à 05:40) | — |
| 05:10 | Inventaire du poste : Docker Desktop arrêté, PHP et Composer absents, PostgreSQL Windows sur 5432 et 5433 | Décision : tout dans Docker, base publiée sur 5434 | Doc 1 |
| 05:12 | Entrée `compliance-store.loc` dans le fichier hosts, hooks git activés, `.env.local` créé | — | Doc 1 |
| 05:15 | `docker compose build` | Image `app-php-dev` construite en 2 min | Doc 1 |
| 05:20 | `docker compose up` puis `make up` | Base refusée sur 5432 avec `docker compose` seul, OK avec `make up` ; `composer install` 8 min | Doc 1 |
| 05:35 | Conteneur PHP en boucle de redémarrage | `frankenphp validate` révèle 3 erreurs Mercure successives | Doc 2 |
| 05:40 | Pull des commits `2bcb2c7` et `675654f` du dépôt d'origine | `symfony/web-profiler-bundle` ajouté, nouvelle migration `holder` | — |
| 05:45 | `composer install` dans un conteneur jetable, correction `Caddyfile.dev` et `compose.override.yaml` | API en 200 sur HTTP et HTTPS, conteneur `healthy` | Doc 2 |
| 05:50 | `make test` : Deptrac OK, PHPStan 9 erreurs | Toutes dans le code wip Holder | Doc 4 |
| 05:55 | PHPUnit : 29 erreurs « relation storage_space does not exist » | `schema:update` plante, DBAL 4.4.4 trop ancien pour ORM 3.6.9 | Doc 3 |
| 06:00 | `composer update doctrine/dbal` → 4.5.0 | `make test-db` OK, PHPUnit 81 tests, 1 échec | Doc 3 |
| 06:10 | Correction des 9 erreurs PHPStan et du test `DocumentApiTest::testItStoresADocument`, ajout de `HolderApiTest` | PHPStan 0 erreur, PHPUnit 88 tests OK | Doc 4 |
| 06:30 | Commits `fix(dev-stack)` et `fix(holder)` sur la branche `fix/holder-phpstan-and-dev-stack`, hook `make test` validé deux fois | Push impossible vers l'origine (lecture seule) | Doc 5 |
| 06:35 | Push vers `Hiro-Mati/tontonlionelAPI` (`main` et `fix/...`) | Dependabot s'active et ouvre 6 PR | Doc 5 |
| 06:40 | Réécriture des messages de commit, push forcé | `726d17b`, `77465e6` | Doc 5 |
| 06:50 | Suppression de `.github/dependabot.yaml` sur `main` perso, fermeture des 6 PR et de leurs branches | `cb43859` | Doc 5 |
| 07:00 | Rédaction de `docs/correctifs/` (README + 5 documents) | `e0cdb12` | Ce dossier |
| 07:10 | Début de la phase d'amélioration : mise à jour des dépendances, persistance complète de `Holder`, consultation d'un holder | Voir entrées suivantes | Doc 6 |
| 07:15 | `composer update` : Symfony 8.1.9, Doctrine ORM 3.7.5, DBAL 4.5.0, PHPStan 2.3.1, PHPUnit 13.4.3, Deptrac 4.7.2, bundles Doctrine. API Platform laissé en 4.3.18 (5.0 est une version majeure) | `make test` relancé par le hook | Doc 6 |
| 07:20 | Commit `chore(deps)` | `6fcfe51`, hook `make test` OK | Doc 6.1 |
| 07:25 | Lecture des modèles existants (mapping `StorageSpace`, `GetStorageSpaceProvider`, tests API) pour rester dans le style du projet | Décision : pas de clé étrangère SQL, existence du holder vérifiée dans le use case | Doc 6.3 |
| 07:35 | Écriture du type Doctrine `birth_date`, mapping complet `Holder.orm.xml`, `holderId` dans `Document.orm.xml`, migration `Version20261024071500`, `HolderNotFoundException`, `StoreDocumentUseCase` avec contrôle du holder, `GetHolderUseCase` + `GetHolderProvider`, opération `Get` sur `HolderResource`, tests | — | Doc 6.2 à 6.5 |
| 07:45 | Déplacement de `HolderRepository` vers `Persistence/Doctrine`, `strict_types` sur 5 fichiers, coquille `HolderAlreadyExistsException`, `post.http` corrigé | — | Doc 6.6 |
| 07:50 | Premier essai : erreur fatale PHP, `BirthDateType` ne peut pas étendre `DateImmutableType` (type de retour figé) | Réécrit en étendant `Type` directement | Doc 6.2 |
| 07:55 | `doctrine:migrations:migrate` en dev, `doctrine:schema:validate`, `make test` | 1 migration, schéma en phase, Deptrac 0, PHPStan 0, PHPUnit 93 tests OK | Doc 6.7 |
| 08:00 | Test réel sur l'API de dev : POST holder 201, GET holder 200, GET inconnu 404, POST document 201, POST document holder inconnu 422. Deux 200 transitoires observés pendant le rechargement des workers FrankenPHP (mode `watch`), disparus une fois les workers relancés | Données de test supprimées ensuite | Doc 6.7 |
| 08:05 | `CHANGELOG.md` (`Added`, `Changed`), rédaction du document 6 | — | Doc 6 |
| 08:10 | Commit `feat(holder)` | Hook `make test` OK | Doc 6 |
| 08:15 | Commit `feat(holder)` validé par le hook | `ac50bb0`, PHPUnit 93 tests OK | Doc 6 |
| 08:20 | `composer.json` : Composer avait relevé les planchers de version aux releases installées, commit dédié | `fd1e1ff` | Doc 6.1 |
| 08:25 | Mise à jour du README des correctifs (résumé, commits, points ouverts), finalisation du journal, commit de la documentation et push vers `Hiro-Mati/tontonlionelAPI` (`main`) | `f4ad5d3` | Ce dossier |
| 08:30 | Lancement de l'audit complet : quatre relectures indépendantes (Domain ; Application + Infrastructure + config + migrations ; tests + outillage ; Docker + CI + documentation) et contrôles automatisés (`composer validate --strict`, `composer audit`, `lint:yaml`, `lint:container`, `lint:twig`, `php -l`, `debug:router`, fins de ligne, `website/`) | 94 constats bruts | Doc 7.1 |
| 08:40 | Tri des constats : vérification de chacun contre le code, séparation erreurs à corriger / décisions de conception à consigner | 60 corrections retenues, 27 points consignés | Doc 7.2, 7.8 |
| 08:45 | Domain : noms stockés trimés, identifiants vides refusés, `BirthDate` et `IdentityDocument` en UTC, `__debugInfo()` sur les secrets, `.gitkeep` superflus | Régression détectée par `IdentityDocumentTest` (comparaison d'identité d'objet) et corrigée | Doc 7.4 |
| 08:50 | Infrastructure et Application : 12 types Doctrine lèvent `InvalidType`, `SodiumCipherGateway` robuste aux données tronquées, `StorageSpaceRepository::update()` en DQL, health check lié à l'environnement réel, `StoreDocumentUseCase` avec validateur, ressources API Platform, `services.yaml`, `Comparator` supprimé | 5 tests ajoutés | Doc 7.4 |
| 08:55 | Compose : secrets retirés du fichier de base, obligatoires en prod ; Caddyfile prod aligné sur Mercure ≥ 0.21 sans directives TLS de dev ; image épinglée `1.13.3-php8.5` ; CI PHP 8.5 ; Makefile ; PHPUnit en deux suites ; `.http` corrigés, dossier `holders/` renommé | `docker compose config` dev et prod, `frankenphp validate` sur les deux Caddyfiles OK | Doc 7.3, 7.5 |
| 09:00 | `composer.json` : Flysystem borné, `ext-sodium`, `symfony/validator`, `symfony/property-info` ; `composer update --lock` | `composer validate --strict` sans avertissement | Doc 7.5 |
| 09:05 | Documentation : liens morts (`README.md`, `docs/README.md`), ADR 0008 et 0009, arborescence `overview/index.md`, `CHANGELOG.md`, accents et incohérences des documents correctifs | — | Doc 7.6 |
| 09:10 | `make test` : Deptrac 0 violation, PHPStan 0 erreur ; PHPUnit refusé par un `--` dans un commentaire XML de `phpunit.dist.xml`, corrigé, relancé | PHPUnit 107 tests, 200 assertions, 0 échec | Doc 7.7 |
| 09:15 | Commit de l'audit (code, outillage, documentation du projet), hook `make test` relancé | `1f7a07e`, PHPUnit 107 tests OK | Doc 7.9 |
| 09:20 | Rédaction du document 7 (audit), mise à jour du sommaire et du résumé du README des correctifs, finalisation du journal, commit de la documentation et push vers `Hiro-Mati/tontonlionelAPI` (`main`) | Voir `git log` | Ce dossier |
