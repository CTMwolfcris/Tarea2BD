# Instructivo de Estudio — Tarea 2 BD

Este archivo es una guía para entender dónde está cada cosa en el proyecto y cómo funciona.

---

## 1. Conexión a Base de Datos

* **Archivo:** `conexion.php`
* **Líneas clave:** 8, 10-11, 14
* **Dónde se usa:** Se incluye en todos los archivos PHP con `include("conexion.php")` y crea la variable `$conexion` que usamos en todas las consultas.
* **Cómo funciona:** Línea 8 crea la conexión con mysqli. Líneas 10-11 detienen el programa si no se puede conectar. Línea 14 configura el charset a utf8mb4 para que los acentos y caracteres especiales funcionen bien.

---

## 2. Login y Registro

* **Archivos:** `login.php`, `sing_up.php`, `guard.php`

### login.php

* **Líneas clave:** 4, 20-21, 28-35
* **Cómo funciona:** Línea 4 redirige al inicio si ya hay sesión activa. Líneas 20-21 buscan el usuario en la BD con prepared statement. Línea 28 verifica la contraseña con `password_verify`. Líneas 29-35 guardan los datos del usuario en la sesión y redirigen.

### sing_up.php

* **Líneas clave:** 17-18, 25, 27-28
* **Cómo funciona:** Líneas 17-18 verifican que el RUT no esté ya registrado. Línea 25 encripta la contraseña con BCRYPT antes de guardarla. Líneas 27-28 insertan el nuevo usuario con rol postulante por defecto.

### guard.php

* **Líneas clave:** 2-3, 6, 11-14, 20-24
* **Dónde se usa:** Al inicio de todas las páginas con `require_once("guard.php")`.
* **Cómo funciona:** Líneas 2-3 inician la sesión si no está iniciada. Línea 6 redirige al login si no hay sesión. Líneas 11-14 definen las variables `$__rol`, `$__rut` y `$__nombre` que usamos en todas las páginas para saber quién está logueado. Líneas 20-24 definen la función `requerirRol()` que bloquea el acceso si el rol no corresponde.

---

## 3. Navbar y Bootstrap

* **Archivo:** `navbar.php`
* **Líneas clave:** 15-16, 25, 56, 60-67
* **Cómo funciona:** Línea 15 carga Bootstrap 5. Línea 16 carga el CSS propio encima de Bootstrap. Línea 25 usa la clase `d-flex gap-2` de Bootstrap para los links del navbar. Línea 56 carga el JS de Bootstrap al final. Líneas 60-67 muestran los mensajes de éxito o error que vienen de otras páginas (flash messages).

---

## 4. CRUD Postulaciones

### crear_postulacion.php

* **Líneas clave:** 53, 60-61, 78-100
* **Cómo funciona:** Línea 53 verifica que el código interno no esté repetido. Líneas 60-61 insertan la cabecera de la postulación. Líneas 78-89 recorren el equipo e insertan cada integrante. Líneas 93-103 recorren las etapas e insertan cada una.

### ver_postulacion.php

* **Líneas clave:** 16, 35-38, 46-47, 54, 60-62
* **Cómo funciona:** Línea 16 trae los datos de la postulación desde la vista SQL. Líneas 35-38 traen el equipo de trabajo. Líneas 46-47 traen las etapas del cronograma. Línea 54 llama a la función SQL `fn_total_semanas` para calcular la duración total. Líneas 60-62 traen el historial de evaluaciones.

### editar_postulacion.php

* **Líneas clave:** 14, 39-45, 84-85, 100-127
* **Cómo funciona:** Línea 14 carga los datos actuales de la postulación. Líneas 39-45 cargan el equipo y etapas actuales para mostrarlos en el formulario. Líneas 84-85 hacen el UPDATE de la cabecera. Líneas 100-127 borran el equipo y etapas anteriores y los vuelven a insertar con los nuevos datos.

### eliminar_postulacion.php

* **Líneas clave:** 12, 22, 27-28
* **Cómo funciona:** Línea 12 verifica que la postulación exista. Línea 22 bloquea la eliminación si no está en estado borrador. Línea 27-28 hace el DELETE. El CASCADE de la BD borra el equipo y las etapas automáticamente.

### enviar_postulacion.php

* **Líneas clave:** 12, 27, 32-33
* **Cómo funciona:** Línea 12 verifica que la postulación exista. Línea 27 bloquea el envío si no está en estado borrador. Líneas 32-33 cambian el estado a 2 (Enviada) y el trigger de la BD registra la fecha automáticamente.

---

## 5. Búsqueda

### buscar.php

* **Líneas clave:** 29-35, 37-42
* **Cómo funciona:** Líneas 29-35 aplican la búsqueda solo entre las postulaciones del postulante. Líneas 37-42 aplican la búsqueda sobre todas las postulaciones si el rol es coordinador o admin. El LIKE con `%texto%` busca el texto en cualquier posición dentro del nombre, empresa o código.

### busqueda_avanzada.php

* **Líneas clave:** 21-24, 51, 53-109, 218
* **Cómo funciona:** Líneas 21-24 controlan si el usuario ya buscó o recién entró a la página. Línea 51 empieza el WHERE con `1=1` para poder agregar filtros dinámicamente. Líneas 53-109 agregan cada filtro al SQL solo si el usuario lo seleccionó. Línea 218 muestra los resultados solo si el usuario ya apretó buscar.

---

## 6. Stored Procedure — sp_registrar_evaluacion

* **Definido en:** `tarea2_bd.sql`
* **Se llama en:** `evaluar_postulacion.php`, líneas 30-31
* **Cómo funciona:** Línea 30 llama al procedimiento con `CALL`. Línea 31 le pasa el ID de la postulación, el RUT del evaluador, el comentario y el nuevo estado. El procedimiento guarda la evaluación y actualiza el estado de la postulación dentro de una transacción.

---

## 7. Function SQL — fn_total_semanas

* **Definida en:** `tarea2_bd.sql`
* **Se llama en:** `mis_postulaciones.php` línea 8 y `ver_postulacion.php` línea 54
* **Cómo funciona:** Recibe el ID de una postulación y devuelve la suma de todas las semanas de sus etapas. Se usa así: `SELECT fn_total_semanas(?) AS total`.

---

## 8. Trigger — trg_fecha_envio

* **Definido en:** `tarea2_bd.sql`
* **Se activa desde:** `enviar_postulacion.php` línea 33 cuando se hace el UPDATE del estado
* **Cómo funciona:** Detecta si el estado cambió de 1 a 2 y si la fecha de envío está vacía. Si se cumplen ambas condiciones registra la fecha y hora actual automáticamente.

---

## 9. Vista SQL — vista_postulaciones

* **Definida en:** `tarea2_bd.sql`
* **Se usa en:** `index.php` líneas 18 y 28, `buscar.php` líneas 30 y 38, `ver_postulacion.php` línea 16, `mis_postulaciones.php` línea 8, `evaluar_postulacion.php` línea 10
* **Cómo funciona:** Junta la tabla postulacion con empresa, campus, región, estado y responsables en una sola consulta. Así en cada archivo PHP solo hacemos `SELECT * FROM vista_postulaciones` y ya viene todo junto sin repetir JOINs.

---

## 10. Gestión de Usuarios y Asignaciones (Admin)

### gestionar_usuarios.php

* **Líneas clave:** 26-28, 44
* **Cómo funciona:** Líneas 26-28 encriptan la contraseña e insertan el nuevo usuario. Línea 44 activa o desactiva un usuario cambiando el campo U_Activo.

### gestionar_asignaciones.php

* **Líneas clave:** 14, 19-20, 25-27, 43
* **Cómo funciona:** Línea 14 desactiva la asignación anterior. Líneas 19-20 crean la nueva asignación. Líneas 25-27 cambian el estado de la postulación a "En Revisión" si estaba en "Enviada". Línea 43 trae todas las postulaciones con su evaluador actual usando LEFT JOIN.
