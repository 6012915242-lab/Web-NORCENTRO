<?php
declare(strict_types=1);

namespace App\Configuracion;

/** Funciones compartidas por las vistas y los controladores. */
final class Herramientas
{
    /** Protege el texto antes de mostrarlo en una página. */
    public static function escaparHtml(mixed $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Muestra un importe con el formato de moneda usado en la tienda. */
    public static function formatearDinero(mixed $importe): string
    {
        return 'S/ ' . number_format((float) $importe, 2, '.', ',');
    }

    /** Comprueba la ruta de una foto y usa una imagen de respaldo si no existe. */
    public static function rutaImagen(string $ruta): string
    {
        $ruta = trim($ruta);
        $imagenRespaldo = 'img/motos/Bajaj Pulsar NS200.jpg';

        if ($ruta === '' || str_contains($ruta, '..')) {
            return $imagenRespaldo;
        }

        // Las fotos se sirven desde el proyecto para no depender de enlaces externos.
        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $imagenRespaldo;
        }

        $archivo = dirname(__DIR__) . '/public/' . $ruta;
        return is_file($archivo) ? $ruta : $imagenRespaldo;
    }

    /** Prepara un mensaje breve según el tiempo restante de una oferta. */
    public static function textoOferta(string $fechaFin): string
    {
        $fin = new \DateTimeImmutable($fechaFin . ' 23:59:59');
        $ahora = new \DateTimeImmutable();

        if ($fin < $ahora) {
            return '';
        }

        $horasRestantes = ($fin->getTimestamp() - $ahora->getTimestamp()) / 3600;

        if ($horasRestantes <= 24) {
            return 'Últimas horas';
        }
        if ($horasRestantes <= 48) {
            return 'Oferta finaliza mañana';
        }
        if ($horasRestantes <= 168) {
            return 'Aprovecha esta semana';
        }

        return 'Llévate esta moto a precio especial';
    }

    /** Crea el campo oculto que protege los formularios contra envíos falsos. */
    public static function campoCsrf(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        $token = self::escaparHtml($_SESSION['_csrf']);
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    /** Verifica el código de seguridad antes de procesar un formulario. */
    public static function verificarCsrf(): void
    {
        $tokenGuardado = $_SESSION['_csrf'] ?? '';
        $tokenRecibido = $_POST['_csrf'] ?? '';

        $peticionPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
        $tokenAusente = $tokenGuardado === '' || $tokenRecibido === '';
        $tokenDiferente = !$tokenAusente && !hash_equals($tokenGuardado, $tokenRecibido);

        if ($peticionPost && ($tokenAusente || $tokenDiferente)) {
            http_response_code(403);
            exit('La sesión del formulario venció. Regresa e inténtalo de nuevo.');
        }
    }

    /** Construye una dirección interna y lleva al usuario a la página solicitada. */
    public static function redirigir(string $pagina, array $parametros = []): never
    {
        $consulta = http_build_query(['page' => $pagina] + $parametros);
        header('Location: index.php?' . $consulta);
        exit;
    }

    /** Guarda un aviso para mostrarlo una sola vez en la siguiente página. */
    public static function mostrarAviso(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'] = [$tipo, $mensaje];
    }
}
