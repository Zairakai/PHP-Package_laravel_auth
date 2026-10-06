# Laravel Auth

[![GitLab Release][gitlab-release-badge]][gitlab-release]
[![Packagist][packagist-badge]][packagist]
[![Downloads][downloads-badge]][packagist]
[![License][license-badge]][license]
[![Docs][docs-badge]][docs]

`zairakai/laravel-auth` centralizes the shared authentication runtime used by
Zairakai Laravel applications.

**Documentation: [laravel-auth-279d17.gitlab.io][docs]**

It is intentionally headless:

- It configures `laravel/fortify` for authentication flows.
- It configures `laravel/sanctum` for shared Blade and SPA sessions.
- It standardizes JSON and redirect responses for auth endpoints.
- It leaves views, domain-specific redirects, and user model details to each
  application.

## Scope

This package is designed for Laravel 12 applications.

`laravel/fortify` does not currently publish Laravel 13 support, so the package
is intentionally constrained until upstream compatibility is available.

## Installation

```bash
composer require zairakai/laravel-auth
```

Publish the configuration when application-level overrides are needed:

```bash
php artisan vendor:publish --tag=zairakai-config
```

## Configuration

The package exposes one config file: `config/laravel-auth.php`.

Key areas:

- `fortify`: shared Fortify runtime settings
- `features`: enabled Fortify features
- `sanctum`: stateful domain and guard settings
- `redirects`: default redirect targets for HTML requests

## Testing

```bash
make quality
make test-all
```

[docs]: https://laravel-auth-279d17.gitlab.io
[docs-badge]: https://img.shields.io/badge/docs-online-blue
[gitlab-release-badge]: https://img.shields.io/gitlab/v/release/zairakai/php-packages/laravel-auth?logo=gitlab
[gitlab-release]: https://gitlab.com/zairakai/php-packages/laravel-auth/-/releases
[packagist-badge]: https://img.shields.io/packagist/v/zairakai/laravel-auth
[packagist]: https://packagist.org/packages/zairakai/laravel-auth
[downloads-badge]: https://img.shields.io/packagist/dt/zairakai/laravel-auth
[license-badge]: https://img.shields.io/badge/license-MIT-blue.svg
[license]: ./LICENSE
