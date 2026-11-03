-- ================================================================
-- NORCENTRO - ACTUALIZACION DE BASE DE DATOS PARA LA UNIDAD II
-- ================================================================
-- Ejecutar una sola vez DESPUES de importar sql/norcentro_web.sql.
-- El script tambien puede volver a ejecutarse: no elimina ventas,
-- clientes, motos, pedidos ni ningun otro dato comercial existente.
-- Compatible con MariaDB 10.4+ (incluido en XAMPP).

SET NAMES utf8mb4;

-- ----------------------------------------------------------------
-- 1. ROLES Y PERMISOS (RBAC)
-- ----------------------------------------------------------------
INSERT INTO roles (nombre) VALUES
    ('Administrador'),
    ('Vendedor')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

CREATE TABLE IF NOT EXISTS permisos (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(60) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    modulo VARCHAR(40) NOT NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permisos_codigo (codigo),
    KEY idx_permisos_modulo_estado (modulo, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rol_permisos (
    rol_id TINYINT UNSIGNED NOT NULL,
    permiso_id SMALLINT UNSIGNED NOT NULL,
    asignado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rol_permisos_rol
        FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rol_permisos_permiso
        FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permisos (codigo, nombre, descripcion, modulo) VALUES
    ('dashboard.ver', 'Consultar el panel', 'Permite ingresar al panel privado.', 'Panel'),
    ('catalogo.ver', 'Consultar el catalogo', 'Permite consultar motos, colores y especificaciones.', 'Catalogo'),
    ('motos.gestionar', 'Gestionar motocicletas', 'Permite registrar, actualizar y desactivar motocicletas.', 'Catalogo'),
    ('inventario.gestionar', 'Gestionar inventario', 'Permite registrar y cambiar el estado de las unidades.', 'Inventario'),
    ('clientes.gestionar', 'Gestionar clientes', 'Permite registrar y actualizar clientes.', 'Clientes'),
    ('clientes.desactivar', 'Desactivar clientes', 'Permite retirar clientes activos de la operación.', 'Clientes'),
    ('ofertas.gestionar', 'Gestionar ofertas', 'Permite registrar, actualizar y desactivar ofertas.', 'Ofertas'),
    ('ventas.registrar', 'Registrar ventas', 'Permite confirmar ventas al contado o a credito.', 'Ventas'),
    ('creditos.revisar', 'Revisar creditos', 'Permite evaluar solicitudes, cuotas y pagos.', 'Creditos'),
    ('usuarios.gestionar', 'Gestionar usuarios', 'Permite administrar usuarios, roles y permisos.', 'Seguridad'),
    ('auditoria.consultar', 'Consultar auditoria', 'Permite revisar eventos de seguridad y trazabilidad.', 'Seguridad')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    modulo = VALUES(modulo),
    estado = 'Activo';

-- El administrador recibe todos los permisos existentes.
INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT roles.id, permisos.id
FROM roles
CROSS JOIN permisos
WHERE roles.nombre = 'Administrador'
  AND permisos.estado = 'Activo';

-- El vendedor solo accede a las operaciones comerciales necesarias.
INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT roles.id, permisos.id
FROM roles
INNER JOIN permisos
    ON permisos.codigo IN (
        'dashboard.ver',
        'catalogo.ver',
        'clientes.gestionar',
        'ventas.registrar',
        'creditos.revisar'
    )
WHERE roles.nombre = 'Vendedor'
  AND permisos.estado = 'Activo';

DELETE rol_permisos
FROM rol_permisos
INNER JOIN roles ON roles.id = rol_permisos.rol_id
INNER JOIN permisos ON permisos.id = rol_permisos.permiso_id
WHERE roles.nombre = 'Vendedor'
  AND permisos.codigo NOT IN (
      'dashboard.ver',
      'catalogo.ver',
      'clientes.gestionar',
      'ventas.registrar',
      'creditos.revisar'
  );

-- ----------------------------------------------------------------
-- 2. SEGURIDAD DE USUARIOS Y SESIONES
-- ----------------------------------------------------------------
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS intentos_fallidos SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER estado,
    ADD COLUMN IF NOT EXISTS bloqueado_hasta DATETIME NULL AFTER intentos_fallidos,
    ADD COLUMN IF NOT EXISTS ultimo_acceso DATETIME NULL AFTER bloqueado_hasta,
    ADD COLUMN IF NOT EXISTS password_cambiado_en DATETIME NULL AFTER ultimo_acceso,
    ADD COLUMN IF NOT EXISTS actualizado_en TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER creado_en,
    ADD INDEX IF NOT EXISTS idx_usuarios_estado_rol (estado, rol_id),
    ADD INDEX IF NOT EXISTS idx_usuarios_bloqueo (bloqueado_hasta);

CREATE TABLE IF NOT EXISTS sesiones_usuario (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    identificador_hash CHAR(64) NOT NULL,
    direccion_ip VARCHAR(45) NOT NULL DEFAULT '',
    agente_usuario VARCHAR(255) NOT NULL DEFAULT '',
    ultima_actividad DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    vence_en DATETIME NOT NULL,
    revocada_en DATETIME NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sesiones_identificador (identificador_hash),
    KEY idx_sesiones_usuario_estado (usuario_id, revocada_en, vence_en),
    CONSTRAINT fk_sesiones_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intentos_acceso (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL DEFAULT '',
    direccion_ip VARCHAR(45) NOT NULL DEFAULT '',
    exitoso TINYINT(1) NOT NULL DEFAULT 0,
    motivo VARCHAR(120) NOT NULL DEFAULT '',
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_intentos_usuario_fecha (usuario, creado_en),
    KEY idx_intentos_ip_fecha (direccion_ip, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La cuenta de prueba se inactiva si conserva una clave sin cifrar.
UPDATE usuarios
SET estado = 'Inactivo',
    nombre = 'Administrador de prueba (inactivo)'
WHERE usuario = 'admin_prueba'
  AND password_hash NOT LIKE '$2y$%';

UPDATE usuarios
SET password_cambiado_en = COALESCE(password_cambiado_en, creado_en)
WHERE password_hash LIKE '$2y$%';

-- ----------------------------------------------------------------
-- 3. AUDITORIA Y TRAZABILIDAD
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS auditoria_seguridad (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NULL,
    accion VARCHAR(80) NOT NULL,
    recurso VARCHAR(120) NOT NULL DEFAULT '',
    metodo_http VARCHAR(10) NOT NULL,
    direccion_ip VARCHAR(45) NOT NULL DEFAULT '',
    resultado ENUM('Exitoso', 'Denegado', 'Error') NOT NULL DEFAULT 'Exitoso',
    datos JSON NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_auditoria_usuario_fecha (usuario_id, creado_en),
    KEY idx_auditoria_accion_fecha (accion, creado_en),
    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Completa instalaciones que usaron la primera version de la migracion.
ALTER TABLE auditoria_seguridad
    ADD COLUMN IF NOT EXISTS resultado ENUM('Exitoso', 'Denegado', 'Error')
        NOT NULL DEFAULT 'Exitoso' AFTER direccion_ip,
    ADD INDEX IF NOT EXISTS idx_auditoria_accion_fecha (accion, creado_en);

-- ----------------------------------------------------------------
-- 4. NORMALIZACION E INDICES PARA CONSULTAS DEL SISTEMA
-- ----------------------------------------------------------------
ALTER TABLE pedidos MODIFY modalidad VARCHAR(20) NOT NULL;
UPDATE pedidos SET modalidad = 'Crédito' WHERE numero_cuotas > 0;
UPDATE pedidos SET modalidad = 'Contado' WHERE numero_cuotas = 0;
ALTER TABLE pedidos MODIFY modalidad ENUM('Contado', 'Crédito') NOT NULL;

ALTER TABLE motos
    ADD INDEX IF NOT EXISTS idx_motos_estado_marca (estado, marca_id),
    ADD INDEX IF NOT EXISTS idx_motos_modelo (modelo);

ALTER TABLE unidades_moto
    ADD INDEX IF NOT EXISTS idx_unidades_moto_estado (moto_id, estado);

ALTER TABLE ofertas
    ADD INDEX IF NOT EXISTS idx_ofertas_vigencia (estado, fecha_inicio, fecha_fin);

ALTER TABLE pedidos
    ADD INDEX IF NOT EXISTS idx_pedidos_estado_fecha (estado, creado_en),
    ADD INDEX IF NOT EXISTS idx_pedidos_dni (dni);

ALTER TABLE ventas
    ADD INDEX IF NOT EXISTS idx_ventas_estado_fecha (estado, fecha),
    ADD INDEX IF NOT EXISTS idx_ventas_modalidad_fecha (modalidad, fecha);

ALTER TABLE cuotas
    ADD INDEX IF NOT EXISTS idx_cuotas_estado_vencimiento (estado, fecha_vencimiento);

-- ----------------------------------------------------------------
-- 5. VISTA DE APOYO PARA EL MIDDLEWARE RBAC
-- ----------------------------------------------------------------
CREATE OR REPLACE VIEW vista_usuarios_permisos AS
SELECT
    usuarios.id AS usuario_id,
    usuarios.usuario,
    usuarios.nombre AS nombre_usuario,
    roles.id AS rol_id,
    roles.nombre AS rol,
    permisos.codigo AS permiso
FROM usuarios
INNER JOIN roles ON roles.id = usuarios.rol_id
INNER JOIN rol_permisos ON rol_permisos.rol_id = roles.id
INNER JOIN permisos ON permisos.id = rol_permisos.permiso_id
WHERE usuarios.estado = 'Activo'
  AND permisos.estado = 'Activo';

-- Comprobacion final visible desde phpMyAdmin.
SELECT roles.nombre AS rol, COUNT(rol_permisos.permiso_id) AS total_permisos
FROM roles
LEFT JOIN rol_permisos ON rol_permisos.rol_id = roles.id
GROUP BY roles.id, roles.nombre
ORDER BY roles.id;

SELECT
    (SELECT COUNT(*) FROM permisos WHERE estado = 'Activo') AS permisos_activos,
    (SELECT COUNT(*) FROM usuarios WHERE estado = 'Activo') AS usuarios_activos,
    (SELECT COUNT(*) FROM auditoria_seguridad) AS registros_auditoria;
