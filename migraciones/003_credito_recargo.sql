-- Agrega el detalle del recargo sin modificar créditos ya registrados.
-- Se puede ejecutar más de una vez en MariaDB de XAMPP.
ALTER TABLE creditos
    ADD COLUMN IF NOT EXISTS porcentaje_recargo DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS importe_recargo DECIMAL(10,2) NOT NULL DEFAULT 0.00;
