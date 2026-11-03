<?php
declare(strict_types=1);

namespace App\Servicios;

use DomainException;
use RuntimeException;

/** Consulta a Factiliza sin exponer el token al navegador. */
final class ConsultaDniServicio
{
    public function consultar(string $dni): array
    {
        if (!preg_match('/^[0-9]{8}$/', $dni)) {
            throw new DomainException('El DNI debe tener exactamente 8 dígitos.');
        }

        $tokenConfigurado = getenv('FACTILIZA_TOKEN') ?: ($_SERVER['FACTILIZA_TOKEN'] ?? '');
        if ($tokenConfigurado === '' && function_exists('apache_getenv')) {
            $tokenConfigurado = apache_getenv('FACTILIZA_TOKEN') ?: '';
        }
        if ($tokenConfigurado === '') {
            $archivoConfiguracion = dirname(__DIR__, 3) . '/apache/conf/extra/norcentro_factiliza.ini';
            if (is_readable($archivoConfiguracion)) {
                $configuracion = parse_ini_file($archivoConfiguracion);
                $tokenConfigurado = is_array($configuracion)
                    ? ($configuracion['FACTILIZA_TOKEN'] ?? '')
                    : '';
            }
        }
        $token = trim((string) $tokenConfigurado);
        if ($token === '') {
            throw new RuntimeException('La consulta de DNI todavía no está configurada. Puedes ingresar los datos manualmente.');
        }
        if (preg_match('/[\r\n]/', $token) || !function_exists('curl_init')) {
            throw new RuntimeException('No se pudo iniciar la consulta de DNI.');
        }

        $consulta = curl_init('https://api.factiliza.com/v1/dni/info/' . $dni);
        curl_setopt_array($consulta, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);
        $respuesta = curl_exec($consulta);
        $estado = (int) curl_getinfo($consulta, CURLINFO_RESPONSE_CODE);
        curl_close($consulta);

        if ($respuesta === false || $estado !== 200) {
            throw new RuntimeException('No se pudo consultar el DNI. Revisa la configuración o ingresa los datos manualmente.');
        }

        $resultado = json_decode($respuesta, true);
        $datos = is_array($resultado) ? ($resultado['data'] ?? null) : null;
        if (!is_array($datos) || ($resultado['success'] ?? false) !== true
            || (string) ($datos['numero'] ?? '') !== $dni) {
            throw new RuntimeException('No se encontraron datos válidos para este DNI.');
        }

        $nombres = trim((string) ($datos['nombres'] ?? ''));
        $apellidos = trim((string) ($datos['apellido_paterno'] ?? '') . ' ' . (string) ($datos['apellido_materno'] ?? ''));
        if ($nombres === '' || $apellidos === '') {
            throw new RuntimeException('La respuesta no contiene nombres y apellidos completos.');
        }

        $direccionCompleta = trim((string) ($datos['direccion_completa'] ?? ''));
        $direccion = $direccionCompleta !== ''
            ? $direccionCompleta
            : trim((string) ($datos['direccion'] ?? ''));

        return [
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'direccion' => $direccion,
        ];
    }
}
