<?php
declare(strict_types=1);

spl_autoload_register(static function (string $clase): void {
    $capas = [
        'App\\Configuracion\\' => __DIR__ . '/configuracion/',
        'App\\Controladores\\' => __DIR__ . '/controladores/',
        'App\\AccesoDatos\\' => __DIR__ . '/acceso_datos/',
        'App\\Modelos\\' => __DIR__ . '/modelos/',
        'App\\Servicios\\' => __DIR__ . '/servicios/',
        'App\\Nucleo\\' => __DIR__ . '/nucleo/',
        'App\\Middlewares\\' => __DIR__ . '/middlewares/',
    ];

    foreach ($capas as $prefijo => $directorio) {
        if (!str_starts_with($clase, $prefijo)) {
            continue;
        }

        $nombreClase = substr($clase, strlen($prefijo));
        $archivo = $directorio . str_replace('\\', '/', $nombreClase) . '.php';

        if (is_file($archivo)) {
            require_once $archivo;
        }

        return;
    }
});
