-- ================================================================
-- NORCENTRO - MIGRACION 005: COLOR DE CADA UNIDAD FISICA
-- ================================================================
-- Cada unidad del inventario puede tener uno de los colores de su modelo.
-- Así se sabe cuántas motos hay de cada color y en la venta se elige
-- una unidad del color que quiere el cliente.
-- Se puede ejecutar más de una vez (compatible con MariaDB 10.4 de XAMPP).

ALTER TABLE unidades_moto
    ADD COLUMN IF NOT EXISTS color_id INT UNSIGNED NULL AFTER moto_id,
    ADD INDEX IF NOT EXISTS idx_unidades_color_estado (color_id, estado);

-- Si se elimina un color, sus unidades quedan como "color por asignar".
ALTER TABLE unidades_moto
    ADD CONSTRAINT fk_unidades_color FOREIGN KEY IF NOT EXISTS (color_id)
        REFERENCES moto_colores (id) ON DELETE SET NULL;

-- Los modelos que tienen un solo color asignan ese color a sus unidades.
UPDATE unidades_moto u
INNER JOIN (
    SELECT moto_id, MIN(id) AS color_id
    FROM moto_colores
    GROUP BY moto_id
    HAVING COUNT(*) = 1
) unico ON unico.moto_id = u.moto_id
SET u.color_id = unico.color_id
WHERE u.color_id IS NULL;
