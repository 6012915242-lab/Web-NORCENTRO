<?php

use Illuminate\Support\Str;

return [

    /* Almacenamiento predeterminado de sesiones. */

    'driver' => env('SESSION_DRIVER', 'database'),

    /* Duración de la sesión. */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /* Cifrado de datos de sesión. */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /* Ubicación de archivos de sesión. */

    'files' => storage_path('framework/sessions'),

    /* Conexión de base de datos para sesiones. */

    'connection' => env('SESSION_CONNECTION'),

    /* Tabla de sesiones. */

    'table' => env('SESSION_TABLE', 'sessions'),

    /* Almacén de caché para sesiones. */

    'store' => env('SESSION_STORE'),

    /* Limpieza de sesiones vencidas. */

    'lottery' => [2, 100],

    /* Nombre de la cookie de sesión. */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /* Ruta de la cookie de sesión. */

    'path' => env('SESSION_PATH', '/'),

    /* Dominio de la cookie de sesión. */

    'domain' => env('SESSION_DOMAIN'),

    /* Cookie solo con HTTPS. */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /* Restringe el acceso a la cookie desde JavaScript. */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /* Política de envío de cookies entre sitios. */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /* Cookies particionadas. */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    /* Serialización de la sesión. */

    'serialization' => 'json',

];
