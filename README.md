# Tarea 2 — INF-239 Bases de Datos

## CT-USM Sistema de Postulaciones de Iniciativas

---

## Integrantes

| Nombre              | Rol         | Rut          | Paralelo |
| :------------------ | ----------- | ------------ | -------- |
| Vicente Urbina      | 202310582-1 | 21.963.953-8 | 202      |
| Cristopher Villagra | 202310584-8 | 21.966.034-0 | 202      |

---

## Instrucciones de ejecución

### Requisitos

- XAMPP con Apache y MySQL activos
- PHP 8.x (incluido en XAMPP)
- phpMyAdmin para importar la base de datos

### Pasos

1. **Instalar la base de datos**

   - Abrir phpMyAdmin en `http://localhost/phpmyadmin`
   - Crear base de datos `tarea2_bd` (o dejar que el script la cree solo)
   - Importar el archivo `BD/tarea2_bd.sql` desde la carpeta del proyecto
2. **Copiar archivos PHP**

   - Copiar la carpeta `PHP/` completa dentro de `htdocs/` de XAMPP
   - Ejemplo: `C:/xampp/htdocs/T2/PHP/`
3. **Configurar la conexión** (si es necesario)

   - Editar `PHP/conexion.php` con los datos correctos:
     - `$server = "127.0.0.1"` → host de MySQL
     - `$user = "root"` → usuario
     - `$pass = ""` → contraseña (vacía por defecto en XAMPP)
     - `$db = "tarea2_bd"` → nombre de la base de datos
     - Puerto 3306 por defecto; si usas otro puerto, ajústalo
4. **Abrir en el navegador**

   - Ir a `http://localhost/T2/PHP/login.php`

---

## Cuentas de prueba

La contraseña de todos los usuarios de prueba es: **`password`**

| RUT          | Nombre            | Rol                                 |
| ------------ | ----------------- | ----------------------------------- |
| 14.555.666-7 | Ricardo Salas     | Postulante (Responsable Académico) |
| 15.222.333-K | Mauricio Figueroa | Postulante                          |
| 18.222.333-4 | Claudio Lobos     | Postulante                          |
| 08.555.444-2 | Hernán Astudillo | Coordinador (Evaluador CT-USM)      |
| 12.555.666-K | Andrea Vásquez   | Coordinador                         |
| 14.777.888-9 | Claudio Torres    | Coordinador                         |
| 99.999.999-9 | Admin CT-USM      | Administrador                       |

---

## Funcionalidades implementadas

### Login y roles

- **ROL 1 — Postulante (Responsable Académico):** crea y edita postulaciones en borrador, las envía, visualiza las suyas.
- **ROL 2 — Coordinador (Evaluador CT-USM):** revisa postulaciones asignadas, registra evaluación y nuevo estado mediante Stored Procedure.
- **ROL 3 — Administrador CT-USM:** gestiona usuarios del sistema y asigna evaluadores a postulaciones.

### Navegación

Todas las secciones tienen navegación por navbar. No se requiere recargar ni retroceder.

### Páginas incluidas

| Archivo                        | Descripción                                                         |
| ------------------------------ | -------------------------------------------------------------------- |
| `login.php`                  | Inicio de sesión con 3 roles                                        |
| `logout.php`                 | Cierra sesión y redirige                                            |
| `index.php`                  | Listado general + barra de búsqueda rápida                         |
| `mis_postulaciones.php`      | Postulaciones propias (ROL 1)                                        |
| `crear_postulacion.php`      | Formulario completo de nueva postulación                            |
| `editar_postulacion.php`     | Editar cabecera, equipo y cronograma                                 |
| `ver_postulacion.php`        | Vista detalle completa                                               |
| `enviar_postulacion.php`     | Cambia estado Borrador → Enviada (activa el trigger)                |
| `eliminar_postulacion.php`   | Elimina borradores (CASCADE a equipo/etapas)                         |
| `evaluar_postulacion.php`    | Registra evaluación vía Stored Procedure (ROL 2)                   |
| `mis_asignaciones.php`       | Postulaciones asignadas al coordinador (ROL 2)                       |
| `buscar.php`                 | Búsqueda rápida por texto                                          |
| `busqueda_avanzada.php`      | Filtros combinables (región, campus, empresa, estado, evaluador...) |
| `gestionar_usuarios.php`     | CRUD usuarios (ROL 3)                                                |
| `gestionar_asignaciones.php` | Asignar evaluadores a postulaciones (ROL 3)                          |

---

## Objetos SQL implementados

### VIEW — `vista_postulaciones`

Vista que junta la tabla postulacion con empresa, campus, región y estado para no tener que hacer joins cada vez que necesitamos mostrar el listado.

### FUNCTION — `fn_total_semanas(p_id INT)`

Suma las semanas de todas las etapas de una postulación y devuelve el total.
Se usa para mostrar la duración en mis postulaciones y en el detalle.

### STORED PROCEDURE — `sp_registrar_evaluacion`

Guarda el comentario del evaluador y actualiza el estado de la postulación al mismo tiempo. Lo usamos en evaluar_postulacion.php.

### TRIGGER — `trg_fecha_envio`

Cuando el postulante envía una postulación, este trigger registra automáticamente la fecha y hora de envío sin que el usuario tenga que ingresarla.

---

## Supuestos asumidos

1. La normalizacion de la base de datos se hizo en base al modelo logico obtenido en la tarea anterior
2. El responsable 1 y 2 de una postulación deben ser personas diferentes.
3. Los borradores solo pueden ser eliminados; las postulaciones enviadas o en revisión pueden ser editadas pero no eliminadas.
4. Un postulante puede ver y editar postulaciones donde es responsable 1 o responsable 2.
5. El campo `P_Fecha_Envio` se rellena automáticamente con el trigger al cambiar a estado "Enviada"; no se ingresa manualmente.
6. La búsqueda rápida y búsqueda avanzada están disponibles para todos los roles; el postulante solo ve sus propias postulaciones en los resultados.
7. El administrador puede asignar cualquier coordinador activo a cualquier postulación en estado Enviada o En Revisión.
