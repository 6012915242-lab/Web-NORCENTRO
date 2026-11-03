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

    public function validarYGuardarMoto(MotoDAO $motoDAO, array $entrada, ?array $archivo, bool $esEdicion = false): void
    {
        $id          = filter_var($entrada['id'] ?? 0, FILTER_VALIDATE_INT);
        $marcaId     = filter_var($entrada['marca_id'] ?? null, FILTER_VALIDATE_INT);
        $modelo      = trim((string) ($entrada['modelo'] ?? ''));
        $color       = trim((string) ($entrada['color'] ?? 'Varios'));
        $descripcion = trim((string) ($entrada['descripcion'] ?? ''));
        $precio      = filter_var($entrada['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $cantidad    = filter_var($entrada['cantidad'] ?? 1, FILTER_VALIDATE_INT);
        $cc          = filter_var($entrada['cilindrada'] ?? null, FILTER_VALIDATE_INT);
        $anio        = filter_var($entrada['anio'] ?? null, FILTER_VALIDATE_INT);

        $anioValido = ($anio !== false && $anio >= 1970 && $anio <= (int) date('Y') + 2);

        if (($esEdicion && (!$id || $id < 1)) || !$marcaId || $marcaId < 1 || mb_strlen($modelo) < 2 || mb_strlen($modelo) > 120) {
            throw new DomainException('Revisa la marca y el nombre del modelo (2 a 120 caracteres).');
        }

        if ($precio === false || $precio <= 0 || $precio > 1000000 || $cc === false || $cc < 1 || $cc > 2000 || !$anioValido) {
            throw new DomainException('Revisa la cilindrada, el año y el precio de la motocicleta.');
        }

        if ($cantidad === false || $cantidad < 1 || (!$esEdicion && $cantidad > 200) || mb_strlen($color) > 60 || mb_strlen($descripcion) > 1200) {
            throw new DomainException('Revisa la cantidad de unidades iniciales, el color y la descripción.');
        }

        if (!$motoDAO->marcaActiva((int) $marcaId)) {
            throw new DomainException('Selecciona una marca activa.');
        }

        $registroAnterior = $esEdicion ? $motoDAO->obtener((int) $id) : null;

        if ($esEdicion && !$registroAnterior) {
            throw new DomainException('No se encontró la motocicleta que quieres editar.');
        }

        $imagen = $registroAnterior['imagen'] ?? 'img/motos/Bajaj Pulsar NS200.jpg';

        if ($archivo && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || (int) ($archivo['size'] ?? 0) > 5 * 1024 * 1024 || empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
                throw new DomainException('La imagen no pudo cargarse (máximo 5 MB).');
            }

            $tipoMime = (new \finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
            $mapaExt  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext      = $mapaExt[$tipoMime] ?? null;

            if (!$ext || @getimagesize($archivo['tmp_name']) === false) {
                throw new DomainException('Usa una imagen JPG, PNG o WEBP válida.');
            }

            $carpeta = dirname(__DIR__) . '/public/img/motos/uploads';
            if (!is_dir($carpeta) && !mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
                throw new DomainException('No se pudo preparar la carpeta de imágenes.');
            }

            $nombre = bin2hex(random_bytes(12)) . '.' . $ext;
            if (!move_uploaded_file($archivo['tmp_name'], $carpeta . '/' . $nombre)) {
                throw new DomainException('No se pudo guardar la imagen.');
            }

            $imagen = 'img/motos/uploads/' . $nombre;
        }

        $datos = [
            'marca_id'    => (int) $marcaId,
            'modelo'      => $modelo,
            'cilindrada'  => (int) $cc,
            'color'       => $color,
            'precio'      => (float) $precio,
            'descripcion' => $descripcion,
            'anio'        => (int) $anio,
            'imagen'      => $imagen,
            'cantidad'    => (int) $cantidad,
        ];

        if ($esEdicion) {
            $motoDAO->actualizar((int) $id, $datos);
            return;
        }

        $motoDAO->guardar($datos);
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

    private function esFechaValida(string $valor): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return false;
        }

        $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        return $fecha !== false && $fecha->format('Y-m-d') === $valor;
    }
}
