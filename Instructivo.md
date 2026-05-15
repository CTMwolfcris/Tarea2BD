# Guía de Funciones Mínimas - Tarea 2 BD

Este archivo indica la ubicación de los requisitos mínimos solicitados en el proyecto.

## 1. Conexión a Base de Datos
* **Nombre:** `conexion.php`
* **Función:** Establece la conexión con MySQL usando `mysqli`. Configura el charset a utf8mb4.

## 2. Autenticación y Seguridad
* **Archivos:** `login.php`, `sing_up.php`, `guard.php`
* **Función:**
    * `sing_up.php`: Registro de usuarios con `password_hash` (BCRYPT).
    * `login.php`: Inicio de sesión verificando hash con `password_verify`.
    * `guard.php`: Protege las rutas verificando la sesión y los roles (Postulante, Coordinador, Administrador).

## 3. CRUD de Postulaciones (Postulante)
* **Crear:** `crear_postulacion.php` -> Inserta cabecera, equipo y etapas.
* **Leer:** `index.php` (resumen) y `ver_postulacion.php` (detalle completo).
* **Editar:** `editar_postulacion.php` -> Permite modificar datos si está en borrador/enviada.
* **Eliminar:** `eliminar_postulacion.php` -> Borra lógicamente o físicamente (si es borrador).

## 4. Gestión y Búsqueda
* **Buscar:** `buscar.php` -> Búsqueda simple por nombre/código.
* **Búsqueda Avanzada:** `busqueda_avanzada.php` -> Filtros por región, campus, estado y evaluador.
* **Mis Postulaciones:** `mis_postulaciones.php` -> Vista privada para el postulante.

## 5. Lógica de Negocio (SQL)
* **Archivo:** `tarea2_bd.sql`
* **Stored Procedure:** `sp_registrar_evaluacion` -> Registra el comentario del evaluador y cambia el estado de la postulación.
* **Función:** `fn_total_semanas` -> Suma la duración de todas las etapas de una postulación específica.
* **Trigger:** `trg_fecha_envio` (en el SQL) -> Asigna la fecha actual automáticamente al cambiar el estado a 'Enviada'.

## 6. Vistas SQL
* **Vista:** `vista_postulaciones` (en `tarea2_bd.sql`)
* **Ubicación de uso:** Se llama en `index.php`, `buscar.php` y `ver_postulacion.php` para traer nombres de empresas y estados en lugar de IDs.

## 7. Bonus (Implementados)
* **Seguridad:** Uso de `prepare()` y `bind_param()` en todos los archivos PHP para evitar Inyecciones SQL.
* **Diseño:** Uso de `estilo.css` para el orden visual de los formularios y tablas.
