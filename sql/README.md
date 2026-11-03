# Base de datos de Norcentro

1. Importa `norcentro_web.sql` en MariaDB desde phpMyAdmin.
2. Ejecuta `und2_seguridad.sql` sobre la misma base.

La segunda migración crea el catálogo `permisos`, la relación `rol_permisos` y `auditoria_seguridad`. También corrige la modalidad de los pedidos que pudo quedar con una codificación antigua.

La aplicación usa PDO con consultas preparadas. El usuario de conexión, el puerto y la contraseña se pueden cambiar mediante `NORCENTRO_DB_HOST`, `NORCENTRO_DB_PORT`, `NORCENTRO_DB_NAME`, `NORCENTRO_DB_USER` y `NORCENTRO_DB_PASS`.
