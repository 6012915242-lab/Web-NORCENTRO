-- Vincula cada venta con la línea de solicitud que atiende, sin modificar registros existentes.
CREATE TABLE IF NOT EXISTS solicitud_credito_ventas (
    pedido_item_id BIGINT UNSIGNED NOT NULL,
    venta_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (venta_id),
    KEY idx_solicitud_credito_item (pedido_item_id),
    CONSTRAINT fk_solicitud_credito_item FOREIGN KEY (pedido_item_id) REFERENCES pedido_items (id),
    CONSTRAINT fk_solicitud_credito_venta FOREIGN KEY (venta_id) REFERENCES ventas (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
