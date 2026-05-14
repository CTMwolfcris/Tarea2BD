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

Une `postulacion` con todas sus tablas relacionadas (estado, campus, región, empresa, tamaño, tipo, responsables).
Usada en el listado general, mis postulaciones, ver detalle y búsquedas.

### FUNCTION — `fn_total_semanas(p_id INT)`

Calcula el total de semanas del cronograma de una postulación sumando todas las etapas.
Usada en la vista de mis postulaciones (columna "Duración") y en ver detalle.

### STORED PROCEDURE — `sp_registrar_evaluacion`

Recibe: `p_postulacion_id`, `p_evaluador_rut`, `p_comentario`, `p_nuevo_estado`.
Dentro de una transacción: inserta registro en `evaluacion` y actualiza `P_Estado_ID` en `postulacion`.
Usado en `evaluar_postulacion.php`.

### TRIGGER — `trg_fecha_envio`

Se activa BEFORE UPDATE en `postulacion`.
Cuando el estado cambia de 1 (Borrador) a 2 (Enviada) y `P_Fecha_Envio` es NULL, asigna `NOW()` automáticamente.
Activado desde `enviar_postulacion.php`.

---

## Supuestos asumidos

1. El responsable 1 y 2 de una postulación deben ser personas diferentes.
2. Los borradores solo pueden ser eliminados; las postulaciones enviadas o en revisión pueden ser editadas pero no eliminadas.
3. Un postulante puede ver y editar postulaciones donde es responsable 1 o responsable 2.
4. La contraseña de los usuarios de prueba insertados en la BD es `password` (hash bcrypt).
5. El campo `P_Fecha_Envio` se rellena automáticamente con el trigger al cambiar a estado "Enviada"; no se ingresa manualmente.
6. La búsqueda rápida y búsqueda avanzada están disponibles para todos los roles; el postulante solo ve sus propias postulaciones en los resultados.
7. El administrador puede asignar cualquier coordinador activo a cualquier postulación en estado Enviada o En Revisión.
8. Al asignar un evaluador a una postulación "Enviada", se cambia automáticamente su estado a "En Revisión".
9. ON DELETE CASCADE está habilitado en `equipotrabajo` y `etapa` respecto a `postulacion`, por lo que eliminar una postulación borra automáticamente su equipo y cronograma.
10. La columna `EQ_EMAIL` y `EQ_Telefono` fueron movidas de `equipotrabajo` a `integrantes` (normalización 2FN): email y teléfono dependen del integrante, no de su participación en un proyecto.
