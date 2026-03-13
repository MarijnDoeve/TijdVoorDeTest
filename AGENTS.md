# Agent Guide: Tijd Voor De Test (Tvdt)

This document provides essential context and instructions for AI agents working on the **Tijd Voor De Test** project.

## Project Overview
A web application for managing "Wie is de Mol?" style tests, including seasons, quizzes, candidates, and eliminations.

- **Namespace**: `Tvdt`
- **PHP Version**: 8.5+
- **Framework**: Symfony 8.0

## Tech Stack
- **Server**: FrankenPHP (Caddy-based PHP server)
- **Database**: PostgreSQL
- **Frontend**: Symfony Asset Mapper (no Node.js/Webpack), Stimulus, Turbo
- **Styling**: Sass (via `symfonycasts/sass-bundle`)
- **Persistence**: Doctrine ORM 3.x

## Core Domain Entities
- **Season**: Groups quizzes and candidates for a specific period.
- **SeasonSettings**: Configuration for a season.
- **Quiz**: A test within a season containing multiple questions.
- **Question**: Questions belonging to a quiz.
- **Answer**: Possible answers for a question.
- **Candidate**: A participant in the season.
- **QuizCandidate**: Represents a candidate's attempt at a specific quiz (tracking start/end time).
- **GivenAnswer**: The specific answer a candidate selected during a quiz.
- **Elimination**: Records of red/green screens and forced results.
- **User**: Administrative accounts for managing the system.

## Development Workflow
The project uses `just` as the primary task runner. Always prefer `just` commands over manual docker calls.

### Common Commands
- `just up`: Start the environment.
- `just down`: Stop the environment.
- `just shell`: Enter the PHP container.
- `just migrate`: Run database migrations.
- `just fixtures`: Load development fixtures.
- `just fix-cs`: Run `php-cs-fixer` and `twig-cs-fixer`.
- `just phpstan`: Run static analysis.
- `just rector`: Run Rector for automated refactorings.
- `just reload-tests`: Reset the test database and load test fixtures.

## Coding Standards
- **PSR-12**: Follow standard PHP coding styles.
- **Strict Typing**: Use strict types in all PHP files.
- **Doctrine ORM 3**: Be aware of ORM 3 changes (e.g., lazy loading behavior, attribute-based mapping).
- **Symfony 8**: Use modern Symfony features (Attributes, Type-hinting).
- **Safe Functions**: Use `thecodingmachine/safe` for standard PHP functions that throw exceptions instead of returning false.

## Testing
- **Framework**: PHPUnit
- **Bundle**: `dama/doctrine-test-bundle` is used to wrap tests in transactions.
- **Location**: `tests/` directory mirroring `src/`.
- **Execution**: Run via `bin/phpunit` inside the container or `just reload-tests` to prepare the environment.

## Frontend Development
- JavaScript is managed via **Import Maps**.
- Stimulus controllers are located in `assets/controllers/`.
- CSS/Sass is in `assets/styles/`.
- Assets are compiled on-the-fly or mapped; do not look for a `node_modules` folder.

## Key Files
- `composer.json`: Dependency management.
- `importmap.php`: JavaScript module mapping.
- `Justfile`: Automation shortcuts.
- `config/`: Application configuration.
- `templates/`: Twig templates.
