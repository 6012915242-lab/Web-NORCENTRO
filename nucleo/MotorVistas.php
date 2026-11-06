<?php

declare(strict_types=1);

namespace App\Nucleo;

/** Renderiza una página dentro del layout común de la aplicación. */
final class MotorVistas
{
    public function __construct(private string $directorioVistas) {}

    public function renderizar(string $pagina, array $datos): string
    {
        $vista = $this->directorioVistas.'/paginas/'.$pagina.'.php';
        /** Si la página no existe, muestra la vista de error 404. */
        if (! is_file($vista)) {
            http_response_code(404);
            $vista = $this->directorioVistas.'/paginas/no_encontrado.php';
        }

        $datos['vista'] = $vista;
        extract($datos, EXTR_SKIP);
        ob_start();
        require $this->directorioVistas.'/layout.php';

        return (string) ob_get_clean();
    }
}
