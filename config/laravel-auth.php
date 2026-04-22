<?php

declare(strict_types=1);

return [
    'fortify' => [
        'guard'               => env('ZAIRAKAI_AUTH_GUARD', 'web'),
        'passwords'           => env('ZAIRAKAI_AUTH_PASSWORDS', 'users'),
        'prefix'              => env('ZAIRAKAI_AUTH_PREFIX', ''),
        'domain'              => env('ZAIRAKAI_AUTH_DOMAIN'),
        'home'                => env('ZAIRAKAI_AUTH_HOME', '/dashboard'),
        'views'               => env('ZAIRAKAI_AUTH_VIEWS', false),
        'auth_middleware'     => env('ZAIRAKAI_AUTH_MIDDLEWARE', 'auth'),
        'lowercase_usernames' => env('ZAIRAKAI_AUTH_LOWERCASE_USERNAMES', true),
        'middleware'          => ['web'],
        'limiters'            => [
            'login'      => env('ZAIRAKAI_AUTH_LOGIN_LIMITER', 'login'),
            'two-factor' => env('ZAIRAKAI_AUTH_TWO_FACTOR_LIMITER', 'two-factor'),
        ],
        'username'   => env('ZAIRAKAI_AUTH_USERNAME', 'email'),
        'email'      => env('ZAIRAKAI_AUTH_EMAIL', 'email'),
    ],

    'features' => [
        'registration'               => false,
        'reset-passwords'            => true,
        'email-verification'         => true,
        'update-profile-information' => false,
        'update-passwords'           => true,
        'two-factor-authentication'  => [
            'enabled'          => true,
            'confirm'          => true,
            'confirm_password' => true,
        ],
    ],

    'sanctum' => [
        'stateful' => env(
            'SANCTUM_STATEFUL_DOMAINS',
            'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        ),
        'guard' => ['web'],
    ],

    'redirects' => [
        'login'              => env('ZAIRAKAI_AUTH_LOGIN_REDIRECT', '/dashboard'),
        'logout'             => env('ZAIRAKAI_AUTH_LOGOUT_REDIRECT', '/'),
        'register'           => env('ZAIRAKAI_AUTH_REGISTER_REDIRECT', '/dashboard'),
        'password-reset'     => env('ZAIRAKAI_AUTH_PASSWORD_RESET_REDIRECT', '/login'),
        'email-verification' => env('ZAIRAKAI_AUTH_VERIFY_EMAIL_REDIRECT', '/dashboard'),
    ],

    'email_filter' => [
        'enabled'         => env('ZAIRAKAI_AUTH_EMAIL_FILTER_ENABLED', true),
        'check_dns'       => env('ZAIRAKAI_AUTH_EMAIL_FILTER_CHECK_DNS', true),
        'check_blocklist' => env('ZAIRAKAI_AUTH_EMAIL_FILTER_CHECK_BLOCKLIST', true),
        'cache_ttl'       => (int) env('ZAIRAKAI_AUTH_EMAIL_FILTER_CACHE_TTL', 86400),
        'cache_prefix'    => env('ZAIRAKAI_AUTH_EMAIL_FILTER_CACHE_PREFIX', 'laravel-auth:blocked-email-domain:'),
        'apply_to'        => [
            'registration' => true,
            'email_update' => true,
            'login'        => false,
        ],
    ],
];
