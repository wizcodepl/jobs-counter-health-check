# Changelog

All notable changes to `jobs-counter-health-check` will be documented in this file.

## 1.0.8 - 2026-05-05

### Fixed
- `phpstan.neon.dist` referenced larastan-only directives (`checkOctaneCompatibility`, `checkModelProperties`) but `larastan/larastan` was not in `require-dev` — `vendor/bin/phpstan` failed to start. Added `larastan/larastan` so the directives resolve.
- `composer format` script invoked Pint, but `laravel/pint` was missing from `require-dev`. Added.
