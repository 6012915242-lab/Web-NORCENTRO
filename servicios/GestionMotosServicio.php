<?php
declare(strict_types=1);

namespace App\Servicios;

use App\AccesoDatos\MotoDAO;
use DomainException;

final class GestionMotosServicio
{
    public function desactivarMoto(MotoDAO $motoDAO, int $id): void
    {
        if ($id < 1 || !$motoDAO->desactivar($id)) {
            throw new DomainException('No se encontró una motocicleta activa con ese identificador.');
        }
    }

    public function validarYGuardarMoto(
        MotoDAO $motoDAO,
        array $entrada,
        array $archivos,
        bool $esEdicion = false
    ): void
    {
        $id = filter_var($entrada['id'] ?? 0, FILTER_VALIDATE_INT);
        $marcaId = filter_var($entrada['marca_id'] ?? null, FILTER_VALIDATE_INT);
        $modelo = TextoEntrada::normalizar($entrada['modelo'] ?? '');
        $descripcion = TextoEntrada::normalizar($entrada['descripcion'] ?? '');
        $precio = filter_var($entrada['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $cantidad = filter_var($entrada['cantidad'] ?? 1, FILTER_VALIDATE_INT);
        $cc = filter_var($entrada['cilindrada'] ?? null, FILTER_VALIDATE_INT);
        $anio = filter_var($entrada['anio'] ?? null, FILTER_VALIDATE_INT);

        $anioValido = ($anio !== false && $anio >= 1970 && $anio <= (int) date('Y') + 2);

        if (
            ($esEdicion && (!$id || $id < 1))
            || !$marcaId
            || $marcaId < 1
            || mb_strlen($modelo) < 2
            || mb_strlen($modelo) > 100
        ) {
            throw new DomainException('Revisa la marca y el nombre del modelo (2 a 100 caracteres).');
        }

        if (
            $precio === false
            || $precio <= 0
            || $precio > 1000000
            || !is_scalar($entrada['precio'] ?? null)
            || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', trim((string) $entrada['precio']))
            || $cc === false
            || $cc < 1
            || $cc > 2000
            || !$anioValido
        ) {
            throw new DomainException('Revisa la cilindrada, el año y el precio de la motocicleta.');
        }

        if (
            $cantidad === false
            || $cantidad < 1
            || (!$esEdicion && $cantidad > 200)
            || mb_strlen($descripcion) > 1200
        ) {
            throw new DomainException('Revisa la cantidad de unidades iniciales y la descripción.');
        }

        if (!$motoDAO->marcaActiva((int) $marcaId)) {
            throw new DomainException('Selecciona una marca activa.');
        }

        $registroAnterior = $esEdicion ? $motoDAO->obtenerEntidad((int) $id) : null;

        if ($esEdicion && ($registroAnterior === null || $registroAnterior->estado !== 'Activo')) {
            throw new DomainException('No se encontró una motocicleta activa con ese identificador.');
        }

        $rutasCreadas = [];
        try {
            $colores = $this->procesarColoresMoto(
                is_array($entrada['colores'] ?? null) ? $entrada['colores'] : [],
                is_array($archivos['imagenes_color'] ?? null) ? $archivos['imagenes_color'] : [],
                $modelo,
                $rutasCreadas
            );
            $imagenPrincipal = $this->guardarImagenSubida(
                $archivos['imagen_principal'] ?? null,
                $rutasCreadas
            );

            if (!$esEdicion && $imagenPrincipal === null && $colores !== []) {
                $imagenPrincipal = $colores[0]['imagenes'][0]['ruta'];
            }

            if (!$esEdicion && $imagenPrincipal === null) {
                throw new DomainException('Sube una foto principal o al menos una toma dentro de un color.');
            }

            $imagen = $imagenPrincipal
                ?? ($registroAnterior?->imagen ?? 'img/motos/Bajaj Pulsar NS200.jpg');
            $nombresColores = array_column($colores, 'nombre');
            $color = $esEdicion
                ? (string) ($registroAnterior?->color ?? 'Varios')
                : (count($nombresColores) === 1 ? $nombresColores[0] : 'Varios');

            $datos = [
                'marca_id' => (int) $marcaId,
                'modelo' => $modelo,
                'cilindrada' => (int) $cc,
                'color' => $color,
                'precio' => (float) $precio,
                'descripcion' => $descripcion,
                'anio' => (int) $anio,
                'imagen' => $imagen,
                'cantidad' => (int) $cantidad,
            ];

            if ($esEdicion) {
                $motoDAO->actualizarConGaleria((int) $id, $datos, $colores);
            } else {
                $motoDAO->guardarConGaleria($datos, $colores);
            }
        } catch (\Throwable $error) {
            foreach ($rutasCreadas as $ruta) {
                @unlink(dirname(__DIR__) . '/public/' . $ruta);
            }
            throw $error;
        }
    }

    private function procesarColoresMoto(
        array $coloresEntrada,
        array $grupoArchivos,
        string $modelo,
        array &$rutasCreadas
    ): array {
        if (count($coloresEntrada) > 6) {
            throw new DomainException('Puedes registrar como máximo 6 colores por operación.');
        }

        $colores = [];
        foreach ($coloresEntrada as $indice => $datosColor) {
            if (!is_array($datosColor)) {
                continue;
            }

            $nombreColor = TextoEntrada::normalizar($datosColor['nombre'] ?? '');
            $codigoHex = strtolower(TextoEntrada::normalizar($datosColor['codigo_hex'] ?? '#cccccc'));
            $archivosColor = $this->normalizarArchivosColor($grupoArchivos, $indice);
            $archivosSeleccionados = array_values(array_filter(
                $archivosColor,
                static fn (array $archivo): bool =>
                    (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            ));

            if ($nombreColor === '' && $archivosSeleccionados === []) {
                continue;
            }

            if (mb_strlen($nombreColor) < 2 || mb_strlen($nombreColor) > 50) {
                throw new DomainException('Cada color debe tener un nombre de 2 a 50 caracteres.');
            }

            if (!preg_match('/^#[0-9a-f]{6}$/', $codigoHex)) {
                throw new DomainException('El código de cada color debe tener el formato #RRGGBB.');
            }

            if ($archivosSeleccionados === []) {
                throw new DomainException('Selecciona al menos una fotografía para ' . $nombreColor . '.');
            }

            if (count($archivosSeleccionados) > 10) {
                throw new DomainException('Puedes subir como máximo 10 tomas por color.');
            }

            $imagenes = [];
            foreach ($archivosSeleccionados as $posicion => $archivo) {
                $ruta = $this->guardarImagenSubida($archivo, $rutasCreadas);
                if ($ruta === null) {
                    continue;
                }

                $imagenes[] = [
                    'ruta' => $ruta,
                    'texto_alt' => sprintf(
                        '%s %s, vista %02d',
                        $modelo,
                        $nombreColor,
                        $posicion + 1
                    ),
                ];
            }

            $colores[] = [
                'nombre' => $nombreColor,
                'codigo_hex' => $codigoHex,
                'imagenes' => $imagenes,
            ];
        }

        return $colores;
    }

    private function normalizarArchivosColor(array $grupo, int|string $indice): array
    {
        $nombres = $grupo['name'][$indice] ?? [];
        if (!is_array($nombres)) {
            $nombres = [$nombres];
        }

        $archivos = [];
        foreach (array_keys($nombres) as $posicion) {
            $archivos[] = [
                'name' => $grupo['name'][$indice][$posicion] ?? '',
                'type' => $grupo['type'][$indice][$posicion] ?? '',
                'tmp_name' => $grupo['tmp_name'][$indice][$posicion] ?? '',
                'error' => $grupo['error'][$indice][$posicion] ?? UPLOAD_ERR_NO_FILE,
                'size' => $grupo['size'][$indice][$posicion] ?? 0,
            ];
        }

        return $archivos;
    }

    private function guardarImagenSubida(?array $archivo, array &$rutasCreadas): ?string
    {
        if ($archivo === null || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (
            ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || (int) ($archivo['size'] ?? 0) > 5 * 1024 * 1024
            || empty($archivo['tmp_name'])
            || !is_uploaded_file($archivo['tmp_name'])
        ) {
            throw new DomainException('Una imagen no pudo cargarse o supera el máximo de 5 MB.');
        }

        $tipoMime = (new \finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $extensiones = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $extension = $extensiones[$tipoMime] ?? null;

        if ($extension === null || @getimagesize($archivo['tmp_name']) === false) {
            throw new DomainException('Usa únicamente imágenes JPG, PNG o WEBP válidas.');
        }

        $carpeta = dirname(__DIR__) . '/public/img/motos/uploads';
        if (!is_dir($carpeta) && !mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
            throw new DomainException('No se pudo preparar la carpeta de imágenes.');
        }

        $nombreArchivo = bin2hex(random_bytes(12)) . '.' . $extension;
        if (!move_uploaded_file($archivo['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
            throw new DomainException('No se pudo guardar una de las imágenes.');
        }

        $ruta = 'img/motos/uploads/' . $nombreArchivo;
        $rutasCreadas[] = $ruta;

        return $ruta;
    }

    public function validarYRegistrarUnidad(MotoDAO $motoDAO, array $entrada): int
    {
        $idMoto = filter_var($entrada['moto_id'] ?? null, FILTER_VALIDATE_INT);
        $codigoSerie = strtoupper(TextoEntrada::normalizar($entrada['codigo_serie'] ?? ''));

        if (!$idMoto || $idMoto < 1) {
            throw new DomainException('Selecciona una motocicleta activa.');
        }

        if (!preg_match('/^[A-Z0-9][A-Z0-9-]{2,49}$/', $codigoSerie)) {
            throw new DomainException(
                'Escribe un código de serie válido de 3 a 50 letras, números o guiones.'
            );
        }

        return $motoDAO->registrarUnidad((int) $idMoto, $codigoSerie);
    }
}
