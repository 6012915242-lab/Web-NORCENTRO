-- Migración de seguridad y calidad para Desarrollo de Aplicaciones Web II.
-- Ejecutar después de importar norcentro_web.sql en la base norcentro_web.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS permisos (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(60) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permisos_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rol_permisos (
    rol_id TINYINT UNSIGNED NOT NULL,
    permiso_id SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rol_permisos_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rol_permisos_permiso FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoria_seguridad (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NULL,
    accion VARCHAR(80) NOT NULL,
    recurso VARCHAR(120) NOT NULL DEFAULT '',
    metodo_http VARCHAR(10) NOT NULL,
    direccion_ip VARCHAR(45) NOT NULL DEFAULT '',
    datos JSON NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_auditoria_usuario_fecha (usuario_id, creado_en),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permisos (codigo, nombre) VALUES
 ('catalogo.ver', 'Consultar el catálogo'),
 ('motos.gestionar', 'Registrar y actualizar motocicletas'),
 ('inventario.gestionar', 'Gestionar unidades de inventario'),
 ('clientes.gestionar', 'Gestionar clientes'),
 ('ofertas.gestionar', 'Gestionar ofertas'),
 ('ventas.registrar', 'Registrar ventas'),
 ('creditos.revisar', 'Revisar solicitudes y cuotas'),
 ('auditoria.consultar', 'Consultar auditoría de seguridad')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permisos p
WHERE r.nombre = 'Administrador';

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r JOIN permisos p
WHERE r.nombre = 'Vendedor'
  AND p.codigo IN ('catalogo.ver', 'clientes.gestionar', 'ventas.registrar', 'creditos.revisar');

-- Normaliza la modalidad cuando el volcado fue importado con una codificación antigua.
ALTER TABLE pedidos MODIFY modalidad VARCHAR(20) NOT NULL;
UPDATE pedidos SET modalidad = 'Crédito' WHERE numero_cuotas > 0;
UPDATE pedidos SET modalidad = 'Contado' WHERE numero_cuotas = 0;
ALTER TABLE pedidos MODIFY modalidad ENUM('Contado','Crédito') NOT NULL;
