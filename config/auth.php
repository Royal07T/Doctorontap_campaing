<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        
        'admin' => [
            'driver' => 'session',
            'provider' => 'admin_users',
        ],
        
        'canvasser' => [
            'driver' => 'session',
            'provider' => 'canvassers',
        ],
        
        'nurse' => [
            'driver' => 'session',
            'provider' => 'nurses',
        ],
        
        'doctor' => [
            'driver' => 'session',
            'provider' => 'doctors',
        ],
        
        'patient' => [
            'driver' => 'session',
            'provider' => 'patients',
        ],
        
        'customer_care' => [
            'driver' => 'session',
            'provider' => 'customer_cares',
        ],
        
        'care_giver' => [
            'driver' => 'session',
            'provider' => 'care_givers',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
        
        // NOTE: In the unified user architecture, admin_users should use User model
        // with role='admin'. Keeping AdminUser for backward compatibility during migration.
        // TODO: Update to use User model with role filtering after full migration.
        'admin_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\AdminUser::class,
        ],
        
        // NOTE: In the unified user architecture, canvassers should use User model
        // with role='canvasser'. Keeping Canvasser for backward compatibility during migration.
        'canvassers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Canvasser::class,
        ],
        
        // NOTE: In the unified user architecture, nurses should use User model
        // with role='nurse'. Keeping Nurse for backward compatibility during migration.
        'nurses' => [
            'driver' => 'eloquent',
            'model' => App\Models\Nurse::class,
        ],
        
        // NOTE: In the unified user architecture, doctors should use User model
        // with role='doctor'. Keeping Doctor for backward compatibility during migration.
        'doctors' => [
            'driver' => 'eloquent',
            'model' => App\Models\Doctor::class,
        ],
        
        // NOTE: In the unified user architecture, patients should use User model
        // with role='patient'. Keeping Patient for backward compatibility during migration.
        // TODO: Update to use User model with role filtering after full migration.
        'patients' => [
            'driver' => 'eloquent',
            'model' => App\Models\Patient::class,
        ],
        
        // NOTE: In the unified user architecture, customer_cares should use User model
        // with role='customer_care'. Keeping CustomerCare for backward compatibility during migration.
        'customer_cares' => [
            'driver' => 'eloquent',
            'model' => App\Models\CustomerCare::class,
        ],
        
        // NOTE: In the unified user architecture, care_givers should use User model
        // with role='care_giver'. Keeping CareGiver for backward compatibility during migration.
        'care_givers' => [
            'driver' => 'eloquent',
            'model' => App\Models\CareGiver::class,
        ],

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
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'patients' => [
            'provider' => 'patients',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'doctors' => [
            'provider' => 'doctors',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'nurses' => [
            'provider' => 'nurses',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'canvassers' => [
            'provider' => 'canvassers',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'admin_users' => [
            'provider' => 'admin_users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
