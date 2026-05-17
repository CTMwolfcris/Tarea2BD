<?php
// navbar.php — se incluye en todas las páginas después de guard.php
// No llama session_start(); guard.php ya lo hizo.
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
/** @var string $__nombre */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CT-USM — <?= $__titulo ?? 'Sistema de Postulaciones' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">
        <a href="index.php">CT-USM</a>
    </div>
    <div class="nav-links d-flex gap-2">
        <a href="index.php" class="btn btn-outline-light btn-sm">Inicio</a>
        <a href="buscar.php" class="btn btn-outline-light btn-sm">Buscar</a>
        <a href="busqueda_avanzada.php" class="btn btn-outline-light btn-sm">Búsqueda Avanzada</a>
        <?php if ($__rol === 'postulante'): ?>
            <a href="crear_postulacion.php" class="btn btn-outline-light btn-sm">Nueva Postulación</a>
            <a href="mis_postulaciones.php" class="btn btn-outline-light btn-sm">Mis Postulaciones</a>
        <?php endif; ?>
        <?php if ($__rol === 'coordinador'): ?>
            <a href="mis_asignaciones.php" class="btn btn-outline-light btn-sm">Mis Asignaciones</a>
        <?php endif; ?>

        <?php if ($__rol === 'administrador'): ?>
            <a href="gestionar_usuarios.php"    class="btn btn-outline-light btn-sm">Usuarios</a>
            <a href="gestionar_asignaciones.php" class="btn btn-outline-light btn-sm">Asignaciones</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        <span class="nav-rol <?= $__rol === 'postulante' ? 'rol-postulante' : ($__rol === 'coordinador' ? 'rol-coordinador' : 'rol-admin') ?>"></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<div class="contenido">
<?php
// Mostrar flash messages si existen
if (!empty($_SESSION['flash_ok'])) {
    echo '<div class="alerta alerta-ok">' . htmlspecialchars($_SESSION['flash_ok']) . '</div>';
    unset($_SESSION['flash_ok']);
}
if (!empty($_SESSION['flash_error'])) {
    echo '<div class="alerta alerta-error">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
    unset($_SESSION['flash_error']);
}
?>
