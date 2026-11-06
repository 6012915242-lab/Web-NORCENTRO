# Norcentro – Sistema web de venta de motocicletas

Proyecto del curso **Desarrollo de Aplicaciones Web II** (UNTRM). Aplicación web en
**PHP 8 vanilla** con arquitectura **MVC de n-capas**, **ORM artesanal** sobre PDO y una
capa de seguridad con **middlewares y RBAC**.

## Instalación en XAMPP

1. Copia el proyecto en `C:\xampp\htdocs\nor_centro` e inicia **Apache** y **MySQL**.
2. En phpMyAdmin crea la base `norcentro_web` con cotejamiento `utf8mb4_unicode_ci`.
3. Ejecuta las migraciones desde la carpeta del proyecto:

   ```bash
   C:\xampp\php\php.exe scripts\migrar.php
   ```

   (También puedes importar en phpMyAdmin los archivos de `migraciones/` en orden: 001 al 005).
4. Abre `http://localhost:8080/nor_centro/`.

La conexión por defecto es `127.0.0.1:4404`, usuario `root` y sin contraseña
(`configuracion/BaseDatos.php`). Se puede cambiar con las variables de entorno
`NORCENTRO_DB_HOST`, `NORCENTRO_DB_PORT`, `NORCENTRO_DB_NAME`, `NORCENTRO_DB_USER` y `NORCENTRO_DB_PASS`.

**Consulta de DNI (opcional):** crea `configuracion/factiliza.ini` con la línea
`FACTILIZA_TOKEN = "tu-token"`. Ese archivo no se sube a Git.

**Backend Laravel (opcional):** dentro de `backend-laravel/` ejecuta `composer install`, copia `.env.example` a `.env` con los datos de MySQL y usa `php artisan serve`.

**Crear un usuario:** revisa las instrucciones al inicio de `scripts/crear-usuario.php`.

## Arquitectura

```
Navegador ──► public/index.php (Front Controller)
                 │
                 ├─ middlewares/   Seguridad (sesión, CSRF, cabeceras) → Autenticación → RBAC → Auditoría
                 ├─ nucleo/        DespachadorAcciones (POST) · MotorVistas · ContenedorDatos · ORM base
                 ├─ controladores/ Controladores delgados: reciben la petición y delegan
                 ├─ servicios/     Lógica de negocio: validaciones, cálculos de crédito, reglas
                 ├─ acceso_datos/  DAO (extienden ModeloBaseORM): consultas preparadas con PDO
                 ├─ modelos/       Entidades (implementan EntidadORM)
                 └─ vistas/        Plantillas HTML sin acceso a la base de datos
```

| Carpeta | Contenido |
|---|---|
| `public/` | Única carpeta pública: `index.php`, APIs JSON (`api/`), CSS, JS e imágenes |
| `nucleo/` | `ModeloBaseORM` (mapeo tabla → objeto, relaciones, transacciones), `EntidadORM`, `MotorVistas`, `DespachadorAcciones`, `ContenedorDatos` |
| `middlewares/` | `SeguridadMiddleware`, `AutenticacionMiddleware`, `AutorizacionRbacMiddleware`, `AuditoriaMiddleware`, `ApiMiddleware` |
| `migraciones/` | Esquema de la base de datos versionado (`001`–`005`) |
| `scripts/` | Herramientas de consola: `migrar.php` y `crear-usuario.php` |
| `configuracion/` | Conexión PDO, funciones de apoyo y certificados SSL para XAMPP |
| `storage/sessions/` | Sesiones de PHP (no se suben a Git) |
| `backend-laravel/` | API REST con Laravel que reutiliza los DAO y servicios del núcleo vanilla (`autoload.php`) |

## Seguridad implementada

- **CSRF:** token por sesión en cada formulario y en las APIs, validado con `hash_equals`.
- **XSS:** toda salida pasa por `Herramientas::escaparHtml()` y la entrada se sanea en el Front Controller.
- **Inyección SQL:** solo consultas preparadas; los nombres de columnas del ORM se validan.
- **Sesiones:** cookies `HttpOnly` y `SameSite`, regeneración del id al iniciar sesión, cierre por inactividad y sesión persistida en BD.
- **RBAC:** permisos por rol guardados en la BD (`roles`, `permisos`, `rol_permisos`), aplicados a páginas, acciones y APIs.
- **Cabeceras HTTP:** `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy` y `Referrer-Policy`.
- **Auditoría:** cada acción y cada acceso denegado se registran en `auditoria_seguridad`.
