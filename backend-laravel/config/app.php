<?php

return [

    /* Nombre de la aplicación. */

    'name' => env('APP_NAME', 'Laravel'),

    /* Entorno de ejecución. */

    'env' => env('APP_ENV', 'production'),

    /* Mostrar detalles de errores. */

    'debug' => (bool) env('APP_DEBUG', false),

    /* Dirección principal de la aplicación. */

    'url' => env('APP_URL', 'http://localhost'),

    /* Zona horaria. */

    'timezone' => 'America/Lima',

    /* Idioma y configuración regional. */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /* Clave para cifrado. */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /* Configuración del modo de mantenimiento. */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
