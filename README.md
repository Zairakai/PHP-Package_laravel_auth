<p align="center">
  <img src="https://gitlab.com/zairakai/php-packages/laravel-auth/-/raw/main/assets/icon.png" alt="Laravel Auth" width="160">
</p>

# Laravel Auth

[![Main][pipeline-main-badge]][pipeline-main-link]
[![Develop][pipeline-develop-badge]][pipeline-develop-link]
[![Coverage][coverage-badge]][coverage-link]

[![GitLab Release][gitlab-release-badge]][gitlab-release]
[![Packagist][packagist-badge]][packagist]
[![Docs][docs-badge]][docs]
[![Downloads][downloads-badge]][packagist]
[![License][license-badge]][license]

[![PHP][php-badge]][php]
[![Laravel][laravel-badge]][laravel]
[![Static Analysis][phpstan-badge]][phpstan]
[![Code Style][pint-badge]][pint]

Headless authentication bridge for Laravel applications: shared runtime for `laravel/fortify` and `laravel/sanctum`, with an email domain filter.

**Documentation: [laravel-auth-279d17.gitlab.io][docs]**

## Features

- **Fortify runtime**: one config file sets guard, prefix, home, middleware, limiters and the enabled features.
- **Sanctum**: stateful domains and guard for shared Blade and SPA sessions.
- **Responses**: JSON for requests that want JSON, a redirect from the config otherwise (login, logout, register, password reset, email verification, two-factor login).
- **Email domain filter**: the `PublicEmailDomain` validation rule refuses an address whose domain is invalid, is in the blocklist or cannot receive mail (no MX record: no mail server).
- **Lookalike domains**: a blocked domain keeps the form it was given, its normalized form and indicators of why it is blocked (Cyrillic or Greek characters, mixed alphabets, punycode), set by analysis or by hand. The indicators only describe: characters never block a domain by themselves.
- **Headless**: views, domain-specific redirects and the user model stay in each application.

## Requirements

PHP 8.4 or higher, Laravel 12 or 13, `laravel/fortify` and `laravel/sanctum`.

The `intl` PHP extension is strongly recommended: the email filter uses it to read international domains (UTS #46). Without it a built-in punycode encoder takes over, which encodes the labels but applies no mapping and is not infallible.

## Install

```bash
composer require zairakai/laravel-auth
php artisan migrate
```

The migration creates the `blocked_email_domains` table of the filter and loads by itself. Three tags publish what the application may want to own:

```bash
php artisan vendor:publish --tag=zairakai-config
php artisan vendor:publish --tag=zairakai-migrations
php artisan vendor:publish --tag=zairakai-translations
```

The published migration keeps the file name of the package, so `--force` publishes it again over the same file when the package changes it. Once it is published, set `load_migrations` to `false` (or `ZAIRAKAI_AUTH_LOAD_MIGRATIONS=false`), otherwise the table is created twice.

## Configuration

The package has one config file, `config/laravel-auth.php`.

| Key | Role |
| --- | --- |
| `fortify` | shared Fortify runtime settings |
| `features` | enabled Fortify features, with the options of two-factor authentication |
| `sanctum` | stateful domains (`SANCTUM_STATEFUL_DOMAINS`) and guard |
| `redirects` | redirect targets for HTML requests |
| `email_filter` | the filter: `enabled`, `check_dns`, `dns_record_types`, `check_blocklist`, cache and `apply_to` |
| `configure_runtime` | `true` by default: the package writes `fortify.*` and `sanctum.*` from `fortify`, `features`, `redirects` and `sanctum`. Set it to `false` to keep the settings of the application as they are |
| `load_migrations` | `true` by default: the package loads its migration. Set it to `false` when the migration is published |

## Email domain filter

The `PublicEmailDomain` validation rule refuses an address whose domain is invalid, is blocked or cannot receive mail. It only runs for a context set to `true` in `email_filter.apply_to`: `registration` and `email_update` are on by default, `login` is off, and any other name is off until you add it.

```php
$request->validate([
    'email' => ['required', 'email', app(PublicEmailDomain::class, ['context' => 'registration'])],
]);
```

<details>
<summary>Examples of the email filter</summary>

```php
use Zairakai\LaravelAuth\Models\BlockedEmailDomain;
use Zairakai\LaravelAuth\Rules\PublicEmailDomain;

// In a validation, with the context it applies to
$request->validate([
    'email' => ['required', 'email', app(PublicEmailDomain::class, ['context' => 'email_update'])],
]);

// Block a domain: the domain is stored as given, with its lowercase form
BlockedEmailDomain::query()->create(['domain' => 'Mailinator.com']);

// A lookalike domain (here a Cyrillic "а" in gmail): the analysis sets the indicators by itself
$blocked = BlockedEmailDomain::query()->create(['domain' => "gm\u{0430}il.com"]);
$blocked->indicators;       // ['non_ascii', 'cyrillic_characters', 'mixed_scripts']
$blocked->indicator_labels; // the same, translated

// Indicators chosen by hand are kept next to the detected ones
BlockedEmailDomain::query()->create(['domain' => 'spam.example', 'indicators' => ['spam']]);
```

The validation fails with one of these reasons:

| Reason | Meaning |
| --- | --- |
| `invalid_email_domain` | the address has no usable domain |
| `unresolvable_email_domain` | the domain has none of the records of `dns_record_types`, `MX` by default (`check_dns`) |
| `blocked_email_domain` | the domain is in the blocklist (`check_blocklist`) |

A domain is accepted when it is not in the blocklist **and** it can receive mail: it has a mail server (an `MX` record, and not a null MX that says the domain takes no mail). Its characters do not matter: an international or lookalike domain that exists, has a mail server and is not blocked is valid. RFC 5321 also lets a domain without MX receive mail on its `A` or `AAAA` record, but a parked domain has them without any mail server, so `dns_record_types` is `['MX']` by default: add `'A'` and `'AAAA'` to accept those.

The blocklist is read first, from the cache, and the DNS only for a domain that is not blocked. The whole blocklist is one cache entry (`email_filter.cache_prefix` followed by `all`), kept for `email_filter.cache_ttl` seconds: this suits a list of up to a few thousand domains. Creating, renaming and deleting a `BlockedEmailDomain` rebuilds the entry. A bulk query (`BlockedEmailDomain::query()->delete()`, `insert()`, `upsert()`) fires no model event: call `resolve(BlockedEmailDomainRepository::class)->refresh()` afterwards, which clears the entry and fills it again from the table.

A blocked domain has three columns: `domain` (as given), `normalized_domain` (lowercase and in ASCII, that is punycode, unique, the one that is compared) and `indicators` (why it is blocked). Indicators set by analysis:

| Indicator | Detected when |
| --- | --- |
| `non_ascii` | the domain has a character outside ASCII |
| `cyrillic_characters` | the domain has a Cyrillic letter |
| `greek_characters` | the domain has a Greek letter |
| `mixed_scripts` | one label mixes alphabets (a Cyrillic "а" in a Latin `gmail`) |
| `punycode` | a label starts with `xn--` |

Any other key is a manual indicator (`phishing`, `spam`, `disposable` and `lookalike` have a translation). The labels are in `laravel-auth::indicators`: publish the translations with the `zairakai-translations` tag to change or add a language. A domain in Unicode, in capitals or in punycode gives the same normalized form, and so the same answer from the blocklist and from the DNS check.

</details>

<details>
<summary>Responses of the auth endpoints</summary>

A request that wants JSON gets the JSON answer, any other request is redirected to the target of the `redirects` key.

| Response | JSON | Redirect (`redirects` key) |
| --- | --- | --- |
| Login | `200`, `{"two_factor": false, "intended": ...}` | `login`, to the intended URL first |
| Logout | `204` | `logout` (`/` by default) |
| Register | `201` | `register`, to the intended URL first |
| Two-factor login | `204` | `login`, to the intended URL first |
| Email verification | `204` | `email-verification`, with `?verified=1` |
| Password reset | `200`, `{"message": ...}` | `password-reset`, with the status in the session |

</details>

<details>
<summary>Environment variables</summary>

| Variable | Default | Config key |
| --- | --- | --- |
| `ZAIRAKAI_AUTH_CONFIGURE_RUNTIME` | `true` | `configure_runtime` |
| `ZAIRAKAI_AUTH_LOAD_MIGRATIONS` | `true` | `load_migrations` |
| `ZAIRAKAI_AUTH_GUARD` | `web` | `fortify.guard` |
| `ZAIRAKAI_AUTH_PASSWORDS` | `users` | `fortify.passwords` |
| `ZAIRAKAI_AUTH_PREFIX` | empty | `fortify.prefix` |
| `ZAIRAKAI_AUTH_DOMAIN` | none | `fortify.domain` |
| `ZAIRAKAI_AUTH_HOME` | `/dashboard` | `fortify.home` |
| `ZAIRAKAI_AUTH_VIEWS` | `false` | `fortify.views` |
| `ZAIRAKAI_AUTH_MIDDLEWARE` | `auth` | `fortify.auth_middleware` |
| `ZAIRAKAI_AUTH_LOWERCASE_USERNAMES` | `true` | `fortify.lowercase_usernames` |
| `ZAIRAKAI_AUTH_LOGIN_LIMITER` | `login` | `fortify.limiters.login` |
| `ZAIRAKAI_AUTH_TWO_FACTOR_LIMITER` | `two-factor` | `fortify.limiters.two-factor` |
| `ZAIRAKAI_AUTH_USERNAME` | `email` | `fortify.username` |
| `ZAIRAKAI_AUTH_EMAIL` | `email` | `fortify.email` |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1` | `sanctum.stateful` |
| `ZAIRAKAI_AUTH_LOGIN_REDIRECT` | `/dashboard` | `redirects.login` |
| `ZAIRAKAI_AUTH_LOGOUT_REDIRECT` | `/` | `redirects.logout` |
| `ZAIRAKAI_AUTH_REGISTER_REDIRECT` | `/dashboard` | `redirects.register` |
| `ZAIRAKAI_AUTH_PASSWORD_RESET_REDIRECT` | `/login` | `redirects.password-reset` |
| `ZAIRAKAI_AUTH_VERIFY_EMAIL_REDIRECT` | `/dashboard` | `redirects.email-verification` |
| `ZAIRAKAI_AUTH_EMAIL_FILTER_ENABLED` | `true` | `email_filter.enabled` |
| `ZAIRAKAI_AUTH_EMAIL_FILTER_CHECK_DNS` | `true` | `email_filter.check_dns` |
| `ZAIRAKAI_AUTH_EMAIL_FILTER_CHECK_BLOCKLIST` | `true` | `email_filter.check_blocklist` |
| `ZAIRAKAI_AUTH_EMAIL_FILTER_CACHE_TTL` | `86400` | `email_filter.cache_ttl` |
| `ZAIRAKAI_AUTH_EMAIL_FILTER_CACHE_PREFIX` | `laravel-auth:blocked-email-domain:` | `email_filter.cache_prefix` |

</details>

## Development

See [CONTRIBUTING.md][contributing] for the workflow and the quality standards. `composer install` and `make install-hooks` set the package up.

```bash
make quality        # Pint, PHPStan and Markdownlint (with the rest of the gate)
make quality-fast   # Pint, PHPStan and Markdownlint
make quality-fix    # every automatic fix
make test           # PHPUnit
make test-ci        # PHPUnit with the coverage and the junit report (build/logs)
make bats           # the structure tests of the repository
make ci             # the whole gate: quality, tests and Bats
```

The configuration of each tool is in `config/dev-tools/`, one file per setting (PHPStan, Pint, PHPUnit, Rector, PHPInsights). The root holds only the thin `.markdownlint.json` and the `.markdownlintignore`. These files come from [laravel-dev-tools][dev-tools]: `make setup-check` tells which ones are late on the package (`make setup-update` publishes them again), and `make config-check` that no file sits where its tool does not read it.

## Statistics

![Statistics of laravel-auth][stats-card]

## Getting Help

[![License][license-badge]][license]
[![Security Policy][security-badge]][security]
[![Issues][issues-badge]][issues]

**Made with ❤️ by [Zairakai][ecosystem]**

<!-- Reference Links -->
[pipeline-main-badge]: https://gitlab.com/zairakai/php-packages/laravel-auth/badges/main/pipeline.svg?ignore_skipped=true&key_text=Main
[pipeline-main-link]: https://gitlab.com/zairakai/php-packages/laravel-auth/commits/main
[pipeline-develop-badge]: https://gitlab.com/zairakai/php-packages/laravel-auth/badges/develop/pipeline.svg?ignore_skipped=true&key_text=Develop
[pipeline-develop-link]: https://gitlab.com/zairakai/php-packages/laravel-auth/commits/develop
[coverage-badge]: https://gitlab.com/zairakai/php-packages/laravel-auth/badges/main/coverage.svg
[coverage-link]: https://gitlab.com/zairakai/php-packages/laravel-auth/-/commits/main
[gitlab-release-badge]: https://img.shields.io/gitlab/v/release/zairakai/php-packages/laravel-auth?logo=gitlab
[gitlab-release]: https://gitlab.com/zairakai/php-packages/laravel-auth/-/releases
[packagist-badge]: https://img.shields.io/packagist/v/zairakai/laravel-auth
[packagist]: https://packagist.org/packages/zairakai/laravel-auth
[downloads-badge]: https://img.shields.io/packagist/dt/zairakai/laravel-auth
[license-badge]: https://img.shields.io/badge/license-MIT-blue.svg
[license]: ./LICENSE
[security-badge]: https://img.shields.io/badge/security-scanned-green.svg
[security]: ./SECURITY.md
[issues-badge]: https://img.shields.io/gitlab/issues/open-raw/zairakai%2Fphp-packages%2Flaravel-auth?logo=gitlab&label=Issues
[issues]: https://gitlab.com/zairakai/php-packages/laravel-auth/-/issues
[php-badge]: https://img.shields.io/badge/php-8.4-blue?logo=php
[php]: https://www.php.net
[laravel-badge]: https://img.shields.io/badge/Laravel-12%20%7C%2013-red?logo=laravel
[laravel]: https://laravel.com
[phpstan-badge]: https://img.shields.io/badge/static%20analysis-phpstan-5B2C6F.svg?logo=php
[phpstan]: https://phpstan.org
[pint-badge]: https://img.shields.io/badge/code%20style-pint-22C55E.svg
[pint]: https://laravel.com/docs/pint
[ecosystem]: https://gitlab.com/zairakai
[contributing]: ./CONTRIBUTING.md
[dev-tools]: https://gitlab.com/zairakai/php-packages/laravel-dev-tools
[docs]: https://laravel-auth-279d17.gitlab.io
[docs-badge]: https://img.shields.io/badge/docs-online-blue
[stats-card]: https://gitlab.com/zairakai/gitlab-profile/-/raw/main/assets/projects/laravel-auth.svg
