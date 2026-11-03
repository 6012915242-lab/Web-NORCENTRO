<?php
declare(strict_types=1);

namespace App\Servicios;

use App\AccesoDatos\ClienteDAO;
use App\AccesoDatos\MotoDAO;
use App\AccesoDatos\OfertaDAO;
use DomainException;

/**
 * Capa de Servicio para la Lógica de Negocio Comercial (Clientes, Motos, Inventario y Ofertas).
 */
final class GestionComercialServicio
{
    public function validarYGuardarCliente(ClienteDAO $clienteDAO, array $entrada, bool $esEdicion = false): int
    {
        $id        = filter_var($entrada['id'] ?? 0, FILTER_VALIDATE_INT);
        $dni       = trim((string) ($entrada['dni'] ?? ''));
        $nombres   = trim((string) ($entrada['nombres'] ?? ''));
        $apellidos = trim((string) ($entrada['apellidos'] ?? ''));
        $telefono  = trim((string) ($entrada['telefono'] ?? ''));
        $direccion = trim((string) ($entrada['direccion'] ?? ''));
        $correo    = trim((string) ($entrada['correo'] ?? ''));

        if ($esEdicion && (!$id || $id < 1)) {
            throw new DomainException('El identificador del cliente a editar no es válido.');
        }

        if ($esEdicion) {
            $clienteExistente = $clienteDAO->obtenerEntidad((int) $id);

            if ($clienteExistente === null || $clienteExistente->estado !== 'Activo') {
                throw new DomainException('No se encontró un cliente activo con ese identificador.');
            }
        }

        if (!preg_match('/^\d{8}$/', $dni)) {
            throw new DomainException('El DNI debe contener exactamente 8 dígitos numéricos.');
        }

        $patronNombre = "/^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u";

        if (mb_strlen($nombres) < 2 || mb_strlen($nombres) > 100 || !preg_match($patronNombre, $nombres)) {
            throw new DomainException('Ingresa nombres válidos (entre 2 y 100 caracteres).');
        }

        if (mb_strlen($apellidos) < 2 || mb_strlen($apellidos) > 100 || !preg_match($patronNombre, $apellidos)) {
            throw new DomainException('Ingresa apellidos válidos (entre 2 y 100 caracteres).');
        }

        if ($telefono !== '' && (mb_strlen($telefono) > 20 || !preg_match('/^[+0-9 ()-]{7,20}$/', $telefono))) {
            throw new DomainException('Ingresa un número de teléfono válido (7 a 20 caracteres).');
        }

        if (mb_strlen($direccion) > 200 || mb_strlen($correo) > 120) {
            throw new DomainException('La dirección o el correo superan la longitud máxima permitida.');
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Ingresa un correo válido.');
        }

        if ($clienteDAO->existeDni($dni, $esEdicion ? (int) $id : null)) {
            throw new DomainException('Ya existe un cliente con ese DNI.');
        }

        $datos = [
            'dni'       => $dni,
            'nombres'   => $nombres,
            'apellidos' => $apellidos,
            'telefono'  => $telefono,
            'direccion' => $direccion,
            'correo'    => $correo,
        ];

        if ($esEdicion) {
            $clienteDAO->actualizar((int) $id, $datos);
            return (int) $id;
        }

        return $clienteDAO->guardar($datos);
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
        $modelo = trim((string) ($entrada['modelo'] ?? ''));
        $descripcion = trim((string) ($entrada['descripcion'] ?? ''));
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
            || mb_strlen($modelo) > 120
        ) {
            throw new DomainException('Revisa la marca y el nombre del modelo (2 a 120 caracteres).');
        }

        if (
            $precio === false
            || $precio <= 0
            || $precio > 1000000
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

        $colores = $this->procesarColoresMoto(
            is_array($entrada['colores'] ?? null) ? $entrada['colores'] : [],
            is_array($archivos['imagenes_color'] ?? null)
                ? $archivos['imagenes_color']
                : [],
            $modelo
        );
        $imagenPrincipal = $this->guardarImagenSubida($archivos['imagen_principal'] ?? null);

        if (!$esEdicion && $imagenPrincipal === null && $colores !== []) {
            $imagenPrincipal = $colores[0]['imagenes'][0]['ruta'];
        }

        if (!$esEdicion && $imagenPrincipal === null) {
            throw new DomainException(
                'Sube una foto principal o al menos una toma dentro de un color.'
            );
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
            $motoDAO->actualizar((int) $id, $datos);
            $motoDAO->guardarGaleriaColores((int) $id, $colores);
            return;
        }

        $motoId = $motoDAO->guardar($datos);
        $motoDAO->guardarGaleriaColores($motoId, $colores);
    }

    private function procesarColoresMoto(
        array $coloresEntrada,
        array $grupoArchivos,
        string $modelo
    ): array {
        if (count($coloresEntrada) > 6) {
            throw new DomainException('Puedes registrar como máximo 6 colores por operación.');
        }

        $colores = [];
        foreach ($coloresEntrada as $indice => $datosColor) {
            if (!is_array($datosColor)) {
                continue;
            }

            $nombreColor = trim((string) ($datosColor['nombre'] ?? ''));
            $codigoHex = strtolower(trim((string) ($datosColor['codigo_hex'] ?? '#cccccc')));
            $archivosColor = $this->normalizarArchivosColor($grupoArchivos, $indice);
            $archivosSeleccionados = array_values(array_filter(
                $archivosColor,
                static fn (array $archivo): bool =>
                    (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            ));

            if ($nombreColor === '' && $archivosSeleccionados === []) {
                continue;
            }

            if (mb_strlen($nombreColor) < 2 || mb_strlen($nombreColor) > 60) {
                throw new DomainException('Cada color debe tener un nombre de 2 a 60 caracteres.');
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
                $ruta = $this->guardarImagenSubida($archivo);
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

    private function guardarImagenSubida(?array $archivo): ?string
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

        return 'img/motos/uploads/' . $nombreArchivo;
    }

    public function validarYGuardarOferta(OfertaDAO $ofertaDAO, array $entrada): void
    {
        $idMoto      = filter_var($entrada['moto_id'] ?? null, FILTER_VALIDATE_INT);
        $titulo      = trim((string) ($entrada['titulo'] ?? ''));
        $descripcion = trim((string) ($entrada['descripcion'] ?? ''));
        $normal      = filter_var($entrada['precio_normal'] ?? null, FILTER_VALIDATE_FLOAT);
        $oferta      = filter_var($entrada['precio_oferta'] ?? null, FILTER_VALIDATE_FLOAT);
        $inicio      = trim((string) ($entrada['fecha_inicio'] ?? ''));
        $fin         = trim((string) ($entrada['fecha_fin'] ?? ''));

        if (!$idMoto || $idMoto < 1 || mb_strlen($titulo) < 3 || mb_strlen($titulo) > 100 || mb_strlen($descripcion) > 500) {
            throw new DomainException('Revisa el modelo seleccionado, el título y la descripción de la oferta.');
        }

        if ($normal === false || $oferta === false || $normal <= 0 || $oferta <= 0 || $oferta >= $normal) {
            throw new DomainException('El precio promocional debe ser positivo y menor al precio normal.');
        }

        if (!$this->esFechaValida($inicio) || !$this->esFechaValida($fin) || $fin < $inicio) {
            throw new DomainException('Revisa las fechas de inicio y fin de la oferta.');
        }

        if (!$ofertaDAO->motoActiva((int) $idMoto)) {
            throw new DomainException('La oferta debe estar asociada a una motocicleta activa.');
        }

        $ofertaDAO->guardar([
            'moto'        => (int) $idMoto,
            'titulo'      => $titulo,
            'descripcion' => $descripcion,
            'normal'      => (float) $normal,
            'oferta'      => (float) $oferta,
            'inicio'      => $inicio,
            'fin'         => $fin,
        ]);
    }

    public function validarYRegistrarUnidad(MotoDAO $motoDAO, array $entrada): void
    {
        $idMoto = filter_var($entrada['moto_id'] ?? null, FILTER_VALIDATE_INT);
        $codigoSerie = strtoupper(trim((string) ($entrada['codigo_serie'] ?? '')));

        if (!$idMoto || $idMoto < 1) {
            throw new DomainException('Selecciona una motocicleta activa.');
        }

        if (!preg_match('/^[A-Z0-9][A-Z0-9-]{2,49}$/', $codigoSerie)) {
            throw new DomainException(
                'Escribe un código de serie válido de 3 a 50 letras, números o guiones.'
            );
        }

        $motoDAO->registrarUnidad((int) $idMoto, $codigoSerie);
    }

    private function esFechaValida(string $valor): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return false;
        }

        $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        return $fecha !== false && $fecha->format('Y-m-d') === $valor;
    }
}
