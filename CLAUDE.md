# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

QIT CLI (Quality Insights Toolkit) is a PHP CLI tool for WooCommerce's testing platform. It enables developers to run automated tests (security, E2E, performance, PHPStan, phpcompat, activation) against WordPress plugins and themes. Built on Symfony Console 5.x with lucatume/di52 DI container. Must support PHP 7.4+.

## Build & Test Commands

All tests run inside Docker containers via the Makefile:

```bash
make tests              # Run all checks: phpcs, phpstan, phpunit, phan
make phpunit            # Unit tests only
make phpunit ARGS="--filter=TestClassName"  # Single test
make phpcs              # Code style (WordPress Coding Standards, auto-fixes first)
make phpstan            # Static analysis (level 6)
make phan               # Phan static analysis
make build              # Build PHAR distribution
```

Tests run against PHP 8.3 by default. Override with `PHP_VERSION=8.1 make phpunit`. Use `make tests-all` / `make phpunit-all` to test all PHP versions (7.4, 8.0-8.4).

Debug with Xdebug: `DEBUG=1 make phpunit`.

Composer dependencies live in `src/composer.json` (not root). Run `composer install` from `src/`.

## Architecture

### Entry Flow

`src/qit-cli.php` → normalizes CLI option aliases (`--php` → `--php_version`, `--wp` → `--wordpress_version`, `--woo` → `--woocommerce_version`) → creates DI container → `src/src/bootstrap.php` → syncs with QIT Manager backend → conditionally registers commands based on connection state and dev mode.

### Key Patterns

**Dynamic Command Creation**: Test run commands (`run:security`, `run:e2e`, etc.) are not statically defined. `DynamicCommandCreator` builds them from JSON schemas fetched from the QIT Manager API via `ManagerSync`. The schema defines available options, types, and validation. This means command definitions can change without CLI code changes.

**DI Container**: `App` extends `lucatume\DI52\App`. Access services via `App::make(ClassName::class)`. Singletons registered in `bootstrap.php` include `Config`, `Cache`, `ManagerSync`, `ManagerBackend`.

**PreCommand Pipeline**: Before test execution, `src/src/PreCommand/` handles configuration resolution (`ConfigResolver`, `QitJsonParser`), extension resolution (finding plugins/themes, resolving versions, parsing metadata), and downloading test environments and packages.

**Environment System**: `src/src/Environment/` manages Docker-based test environments. `EnvironmentManager` orchestrates lifecycle. `UpEnvironmentCommand` spins up WordPress+WooCommerce containers for local testing.

**Offline Mode**: If `ManagerSync` fails, the app enters offline mode with limited commands. `Diagnosis` class runs connectivity checks.

### Source Layout

- `src/qit-cli.php` — Entry point
- `src/src/bootstrap.php` — App initialization, command registration
- `src/src/Commands/` — CLI commands (Backend, Environment, Partner, TestPackages, Group, Tunnel, AI)
- `src/src/Environment/` — Docker environment management
- `src/src/PreCommand/` — Pre-execution pipeline (config, downloads, extension resolution)
- `src/src/Validation/` — Artifact validation (plugins, themes, test packages)
- `src/tests/unit/` — PHPUnit tests with snapshot assertions

### Testing

Tests bootstrap at `src/tests/unit/bootstrap.php`. It mocks the Manager sync response using `tests/unit/data/sync.json` (pulled from production at test init). Config dir is `/tmp/.woo-qit-tests`. All commands are auto-registered during test bootstrap regardless of connection state.

### Static Analysis Exclusions

`src/src/AI/` and `src/src/Commands/AI/` are excluded from PHPStan and Phan analysis.

## Coding Conventions

- WordPress Coding Standards (PHPCS with WPCS ruleset)
- PSR-4 autoloading under `QIT_CLI\` namespace, rooted at `src/src/`
- Option aliases must fail fast if both short and long forms are provided (no silent fallback)
