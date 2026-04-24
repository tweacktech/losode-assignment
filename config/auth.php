<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been provided for you
    | here which uses session storage and the Eloquent user provider.
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | Supported: "session", "token", "jwt"
    |
    */

    'guards' => [
        // Web Guard - For traditional session-based authentication
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // API Guard - For token-based authentication (Sanctum)
        // Used for REST API with Bearer tokens
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
            'hash' => false,
        ],

        // Vendor Guard - For vendor/seller authentication via Sanctum
        // Alternative: separate guard for vendors
        'vendor' => [
            'driver' => 'sanctum',
            'provider' => 'vendors',
            'hash' => false,
        ],

        // Customer Guard - For customer/user authentication via Sanctum
        // Alternative: separate guard for customers
        'customer' => [
            'driver' => 'sanctum',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models, you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        // Users Provider - For customers/regular users
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Vendors Provider - For vendors/sellers
        'vendors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Vendor::class,
        ],

        // Database Provider - Alternative to Eloquent
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Here you may set the options for resetting passwords including the view
    | that is rendered when an email is sent to a user to help them reset
    | their password. You may also set the name of the table that stores
    | the reset tokens for your application.
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        // User password reset settings
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        // Vendor password reset settings
        'vendors' => [
            'provider' => 'vendors',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of time (in seconds) before a password
    | confirmation window expires and the user is asked to re-enter their
    | password via the confirmation screen. By default, the timeout lasts
    | for three hours.
    |
    */

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Configuration
    |--------------------------------------------------------------------------
    |
    | Sanctum provides simple API token authentication for single-page
    | applications (SPAs) and simple APIs. When the application has been
    | upgraded to support Sanctum, it will automatically handle token
    | generation and validation.
    |
    | This is primarily used for our REST API endpoints.
    |
    */

    'sanctum' => [
        // Token expires in X seconds (null = never expires)
        'expiration' => env('SANCTUM_TOKEN_EXPIRATION', null),
    ],

];
