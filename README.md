# Tijd voor de test

![CI](https://github.com/MarijnDoeve/TijdVoorDeTest/actions/workflows/ci.yml/badge.svg)

PHP/Symfony application for WIDM-style quiz management.
Built with FrankenPHP, PostgreSQL, and Docker.

> **Disclaimer:** This is an unofficial, non-commercial, open-source fan
> project. It is not affiliated with, endorsed by, or associated with
> *Wie is de Mol?* (produced by IDTV, broadcast by AVROTROS/NPO) or
> *De Mol* (produced by Woestijnvis, broadcast by Play/De Vijver Media).
> *Wie is de Mol?* and *De Mol* are trademarks of their respective rights
> holders. No copyright infringement is intended.

## Requirements

- Docker
- [Just](https://just.systems) (`brew install just`)

## Local development

```bash
just up        # Start PHP + PostgreSQL containers
just migrate   # Run pending database migrations
just fixtures  # Load dev fixtures (truncates first)
```

The app is available at **https://localhost** (self-signed cert — run
`just trust-cert` on macOS to trust it).

### Useful commands

```bash
just shell              # Shell inside the running PHP container
just shell-run          # Shell in a fresh one-off container
just stop               # Stop containers (keep volumes)
just down               # Stop and remove containers
just clean              # Nuclear: remove containers + volumes + generated files
just exec <cmd>         # Run any command inside the PHP container
```

### Environment

Copy `.env` and override locally via `.env.local` (not committed):

| Variable       | Description                         |
|----------------|-------------------------------------|
| `APP_SECRET`   | Symfony app secret                  |
| `DATABASE_URL` | PostgreSQL DSN (auto-set in Docker) |
| `SENTRY_DSN`   | Sentry error tracking               |
| `DEFAULT_URI`  | Base URL for CLI-generated links    |

## Testing

```bash
just test                                   # Full PHPUnit suite
just test tests/Path/To/TestFile.php        # Single file
just test --coverage-html var/coverage      # HTML coverage report
just reload-tests             # Drop/recreate test DB + migrate + fixtures
```

Tests use a separate database configured via `.env.test`. The DAMA
Doctrine bundle wraps each test in a transaction that is rolled back after.

## Code quality

All checks run in CI and must pass before merging.

```bash
just fix-cs              # Auto-fix PHP-CS-Fixer + Twig-CS-Fixer
just phpstan             # PHPStan static analysis (level 8)
just rector              # Apply Rector modernizations
just rector --dry-run    # Preview Rector changes without applying
```

## Database

```bash
just migrate                      # Run pending migrations
just fixtures                     # Load dev fixtures
bin/console make:migration        # Generate a new migration (inside container)
```

Migrations live in `migrations/` (namespace `DoctrineMigrations`). Test
fixtures are in `src/DataFixtures/` loaded with `--group=test`.

## Translations

```bash
just translations    # Extract/update nl translation strings into translations/
```

## Contributing

1. Create a branch from `main` — use a prefix like `feat/`, `fix/`,
   or `docs/`.
2. Open a pull request; CI must pass before merging.
3. Run `just fix-cs` and `just phpstan` locally before pushing to
   avoid CI failures.

## Deployment

Docker images are published to `ghcr.io/marijndoeve/tijdvoordetest`
for each tagged release.

### First-time setup

1. Copy `compose.yaml` and `compose.prod.yaml` to your server.
2. Create a `.env.prod.local` file with the required variables (see below).
3. Start the stack — migrations run automatically on container start:

```bash
IMAGE_TAG=latest docker compose -f compose.yaml -f compose.prod.yaml up -d
```

### Updating to a new version

```bash
IMAGE_TAG=<tag> docker compose -f compose.yaml -f compose.prod.yaml pull
IMAGE_TAG=<tag> docker compose -f compose.yaml -f compose.prod.yaml up -d
```

### Required environment variables

| Variable                   | Description                                 |
|----------------------------|---------------------------------------------|
| `IMAGE_TAG`                | Image tag to run (e.g. `1.2.3` or `latest`) |
| `APP_SECRET`               | Random secret string for Symfony            |
| `CADDY_MERCURE_JWT_SECRET` | JWT secret for the Mercure hub              |
| `POSTGRES_PASSWORD`        | PostgreSQL password                         |
| `MAILER_DSN`               | Mailer transport DSN                        |
| `MAILER_SENDER`            | From address for emails                     |
| `SENTRY_DSN`               | Sentry project DSN (optional)               |

The `compose.prod.yaml` configures Traefik labels for TLS termination at
`tijdvoordetest.nl`. Adjust the `traefik` labels in that file if you're
hosting on a different domain or using a different reverse proxy.

## License

[MIT](LICENSE)
