# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Tijd voor de test** is a PHP/Symfony 8.1 application for managing quizzes in the style of **Wie is de Mol?** (WIDM) — a Dutch TV show where contestants try to identify a saboteur ("de Mol") among them. At the end of each episode, participants take a quiz about the Mol's identity and actions; the candidate with the least correct answers is eliminated. This app replicates that quiz format with:
- Test creation with variable question counts
- Season management with active test controls
- Candidate answer tracking with automatic timing
- Elimination tracking with joker adjustments
- Backoffice management for quiz administration and statistics

Tech Stack:
- **Framework**: Symfony 8.1
- **PHP**: 8.5+
- **Database**: PostgreSQL 16
- **ORM**: Doctrine
- **Server**: FrankenPHP with Caddy
- **Container**: Docker Compose
- **Frontend**: Twig templates with SASS (via asset mapper)
- **Testing**: PHPUnit 13 with DAMA Doctrine test bundle

## Build & Development Commands

All commands assume Docker is running. The project uses a [Justfile](https://just.systems) as the primary interface.

### Essential Commands

```bash
just up           # Start Docker services (PHP, PostgreSQL)
just stop         # Stop services
just down         # Stop and remove containers/orphans
just shell        # Interactive shell inside the PHP container
just shell-run    # Shell in a fresh one-off container
```

### Database

```bash
just migrate                  # Run Doctrine migrations (starts services first)
just fixtures                 # Load dev fixtures (truncates first)
just reload-tests             # Drop/recreate test DB, migrate, load test fixtures
```

### Testing

```bash
just test                                  # Run full PHPUnit suite
just test tests/Path/To/TestFile.php       # Run a specific test file
just test --coverage-html var/coverage     # Generate HTML coverage report
```

### Code Quality & Linting

```bash
just fix-cs                        # Auto-fix PHP-CS-Fixer + Twig-CS-Fixer
just phpstan                       # PHPStan static analysis (level 9)
just phpstan --no-progress         # Without progress output
just rector                        # Apply Rector modernizations
just rector --dry-run              # Preview Rector changes
```

### Other

```bash
just translations   # Extract/update nl translation strings
just clean          # Nuke containers (volumes) + all generated files (prompts for confirmation)
just trust-cert     # Trust the local Caddy TLS certificate (macOS)
just exec <cmd>     # Run any command inside the PHP container
```

All code quality checks run in CI/CD (.github/workflows/ci.yml) and should pass before merging.

## Project Structure

```
src/
  Controller/              # HTTP request handlers (attribute-routed)
    Backoffice/           # Admin panel controllers
  Entity/                 # Doctrine ORM entities
  Repository/             # Database queries
  Service/                # Business logic
  Command/                # CLI commands
  Form/                   # Symfony form types
  Dto/                    # Data transfer objects
  Enum/                   # Enumerations (FlashType, etc.)
  Exception/              # Custom exceptions
  Factory/                # Object factories
  Helpers/                # Utility functions
  Security/               # Auth and voter classes
    Voter/                # Authorization voters
  DataFixtures/           # Test data loaders

config/
  packages/               # Symfony bundle configurations
  routes/                 # Route definitions
  services.yaml           # Service container configuration
  routes.yaml             # Main route entry point

templates/
  backoffice/             # Admin UI templates
  quiz/                   # Public quiz UI templates
  base.html.twig          # Main layout

tests/
  Command/                # Command tests
  Controller/             # Controller/integration tests
  Repository/             # Repository tests
  Security/               # Auth tests
  Helpers/                # Utility tests
  bootstrap.php           # PHPUnit bootstrap with test container setup
```

## Core Domain Entities

- **Season**: Groups quizzes and candidates for a specific period, with a linked `SeasonSettings`.
- **Quiz**: A test within a season containing multiple `Question`s, each with multiple `Answer`s.
- **Candidate**: A participant in the season.
- **QuizCandidate**: Represents a candidate's attempt at a specific quiz (tracks start/end time).
- **GivenAnswer**: The specific answer a candidate selected during a quiz.
- **Elimination**: Records red/green screens and forced results with joker adjustments.
- **User**: Administrative accounts for managing the system.

## Architecture Notes

### Routing
- Routes are **attribute-based** (PHP 8 attributes in controller methods)
- Configured in `config/routes/attributes.yaml` for automatic discovery
- Main entry point: `config/routes.yaml`

### Service Container & Dependency Injection
- Services in `src/` are automatically registered via PSR-4 namespace `Tvdt\`
- Exclusions: Entity, DependencyInjection, Kernel classes
- Autowiring and autoconfiguration enabled by default
- Service definitions in `config/services.yaml`

### Database & Migrations
- PostgreSQL-based with Doctrine ORM
- Migrations in `migrations/` at project root, namespace `DoctrineMigrations` (intentionally not autoloaded); generate with `bin/console make:migration`
- Test fixtures in `src/DataFixtures/` (loaded with `--group=test`)
- Test database configured separately via `.env.test`

### Testing Infrastructure
- **PHPUnit 13** with DAMA Doctrine Test Bundle for transaction rollback
- Bootstrap: `tests/bootstrap.php` loads env vars and autoloader; `tests/symfony-container.php` boots the test kernel/container (used by Rector)
- Symfony test utilities (BrowserKit, CSS selectors) available
- Coverage excluded from: `src/DataFixtures/`
- Test environment: `APP_ENV=test` (set in phpunit.dist.xml)

### Testing Conventions (TDD)
- **Write the failing test first.** When fixing any PHP-reachable bug, write a PHPUnit test that reproduces the failure before touching the production code. Fix the code until the test passes.
- Only skip a test if the bug is purely in JavaScript/frontend where PHPUnit cannot reach it.
- Don't write tests for trivial presentational markup (e.g. asserting a tooltip/popover attribute or a CSS class exists in a template). Tests cover behavior: routing, forms, persistence, authorization.
- Follow the pattern in `tests/Controller/Backoffice/` for controller/integration tests: log in, GET for CSRF token, POST form data, assert redirect, clear entity manager, assert DB state.

### Code Style & Standards
- **PHP-CS-Fixer**: Symfony ruleset + risky rules enabled
  - Strict types declaration required
  - Trailing commas in multiline structures
  - No else-only blocks
- **Rector**: Aggressive modernization with all attribute sets + prepared sets (dead code, code quality, Doctrine, Symfony, PHPUnit)
- **PHPStan**: Level 8 with extensions for Doctrine and Symfony
- **Twig-CS-Fixer**: Template style enforcement
- **Safe functions**: Use `thecodingmachine/safe` wrappers for standard PHP functions that return `false` on failure — they throw exceptions instead

### Environment Configuration
- `.env` - Local development defaults (uncommitted in .env.local)
- `.env.dev` - Development overrides
- `.env.test` - Test environment configuration
- Production uses `composer dump-env prod` for compiled configuration
- Key variables:
  - `APP_ENV` - Environment (dev/test/prod)
  - `DATABASE_URL` - PostgreSQL connection string
  - `MAILER_SENDER` - From address for emails

### Frontend Build
- Asset mapper (no Node.js/Webpack) for JS/CSS bundling; JS modules declared in `importmap.php`
- **Stimulus** controllers in `assets/controllers/`, **Turbo** for SPA-like navigation
- Sass sources in `assets/styles/`, compiled via `bin/console sass:build`
- Production: Assets precompiled during Docker build
- Development: Watch mode enabled in FrankenPHP container

## CI/CD Pipeline

GitHub Actions workflow (`.github/workflows/ci.yml`):

1. **Linting**: Dockerfile (hadolint), Twig templates
2. **Code Quality**:
   - PHP-CS-Fixer style check
   - Twig-CS-Fixer style check
   - PHPStan static analysis
   - Rector dry-run
3. **Integration Tests**:
   - Docker image build and start services
   - Database creation and migration
   - Fixture loading
   - Full PHPUnit test suite with JUnit XML output
   - Doctrine schema validation
4. **Build & Deploy** (on tags or main, disabled currently):
   - Docker image push to GitHub Container Registry
   - Sentry release creation
   - Portainer webhook trigger for production deployment

Runs on all pushes to main and pull requests. Concurrency cancels old runs on new commits.

## Important Files & Conventions

- **Kernel**: `src/Kernel.php` - Symfony kernel class
- **AbstractController**: Base class for all controllers — defines route parameter regexes (`SEASON_CODE_REGEX`, `CANDIDATE_HASH_REGEX`) and flash helpers
- **Flash Messages**: Use `FlashType` enum instead of string literals
- **QuizSpreadsheetService**: Handles importing quizzes from XLSX files
- **Rector container**: `tests/symfony-container.php` — boots a test kernel so Rector can resolve Symfony service types
- **.gitignore**: Excludes var/, vendor/, .env.local, .phpunit.cache
- **Dockerfile**: Multi-stage build with dev/prod separation, FrankenPHP-based
- **Docker Compose**: PHP service with Caddy, PostgreSQL database, persistent volumes

## Security & Authorization

- Doctrine extensions enabled (timestamps, slugs, etc.)
- Voter-based authorization in `src/Security/Voter/`
- User entity with security encoding configured
- CSRF protection enabled
- Email verification available via SymfonyCasts bundle

## Composer Scripts

Auto-executed scripts on install/update:
- `cache:clear` - Symfony cache clear
- `assets:install` - Copy public assets
- `importmap:install` - JS import map setup

## Writing Style (Help Content & UI Text)

When writing Dutch help content in `templates/backoffice/help/nl/`:

- **No em-dashes** (—): use a comma or restructure the sentence instead.
- **No semicolons** (;): use a comma. Semicolons are technically correct but read as AI-generated text.
- **Natural Dutch**: write the way a person would explain it to a colleague, not in formal documentation style.
- **Colons after bold labels** (e.g. `<strong>Label:</strong> description`) are fine and intentional.

## Notes for Future Work

- The backoffice elimination logic is in `Controller/Backoffice/PrepareEliminationController.php`
- Quiz timing logic starts on candidate start click and stops on final answer selection
- Background music feature noted but not yet implemented (requirements only)
- Statistics functionality is marked TBD in README
