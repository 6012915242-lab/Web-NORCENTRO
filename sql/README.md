# Base de datos de Norcentro

## Instalacion nueva

1. Crea una base llamada `norcentro_web` con cotejamiento `utf8mb4_unicode_ci`.
2. Importa `norcentro_web.sql` desde phpMyAdmin.
3. Importa `und2_seguridad.sql` sobre la misma base.
4. Importa `credito_recargo.sql` para asegurar que estén disponibles los campos del crédito.

## Actualizacion de una base existente

Si ya tienes las motos, clientes, pedidos y ventas cargados, ejecuta solamente
`und2_seguridad.sql`. La migracion no borra informacion comercial y puede volver
a ejecutarse sin duplicar roles o permisos.

Para habilitar el recargo de crédito en una base existente, ejecuta también
`credito_recargo.sql`. Los créditos anteriores conservan sus montos y registran
un recargo de cero.

La actualizacion agrega RBAC por permisos, auditoria, control de intentos de
acceso, sesiones persistentes, campos de bloqueo de usuarios e indices para las
consultas mas usadas. Tambien inactiva una cuenta antigua si detecta que su
contrasena esta guardada sin cifrado.

La aplicacion usa PDO con consultas preparadas. La conexion se configura con
`NORCENTRO_DB_HOST`, `NORCENTRO_DB_PORT`, `NORCENTRO_DB_NAME`,
`NORCENTRO_DB_USER` y `NORCENTRO_DB_PASS`.
