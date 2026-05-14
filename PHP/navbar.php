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
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <a href="index.php">CT-USM</a>
    </div>

    <div class="nav-links">
        <a href="index.php"         class="<?= basename($_SERVER['PHP_SELF']) === 'index.php'         ? 'activo' : '' ?>">Inicio</a>
        <a href="buscar.php"        class="<?= basename($_SERVER['PHP_SELF']) === 'buscar.php'        ? 'activo' : '' ?>">Buscar</a>
        <a href="busqueda_avanzada.php" class="<?= basename($_SERVER['PHP_SELF']) === 'busqueda_avanzada.php' ? 'activo' : '' ?>">Búsqueda Avanzada</a>

        <?php if ($__rol === 'postulante'): ?>
            <a href="crear_postulacion.php" class="<?= basename($_SERVER['PHP_SELF']) === 'crear_postulacion.php' ? 'activo' : '' ?>">Nueva Postulación</a>
            <a href="mis_postulaciones.php" class="<?= basename($_SERVER['PHP_SELF']) === 'mis_postulaciones.php' ? 'activo' : '' ?>">Mis Postulaciones</a>
        <?php endif; ?>

        <?php if ($__rol === 'coordinador'): ?>
            <a href="mis_asignaciones.php" class="<?= basename($_SERVER['PHP_SELF']) === 'mis_asignaciones.php' ? 'activo' : '' ?>">Mis Asignaciones</a>
        <?php endif; ?>

        <?php if ($__rol === 'administrador'): ?>
            <a href="gestionar_usuarios.php"    class="<?= basename($_SERVER['PHP_SELF']) === 'gestionar_usuarios.php'    ? 'activo' : '' ?>">Usuarios</a>
            <a href="gestionar_asignaciones.php" class="<?= basename($_SERVER['PHP_SELF']) === 'gestionar_asignaciones.php' ? 'activo' : '' ?>">Asignaciones</a>
        <?php endif; ?>
    </div>

    <div class="nav-user">
        <span class="nav-rol
            <?= $__rol === 'postulante'    ? 'rol-postulante'    : '' ?>
            <?= $__rol === 'coordinador'   ? 'rol-coordinador'   : '' ?>
            <?= $__rol === 'administrador' ? 'rol-admin'         : '' ?>
        ">
            <?= ucfirst($__rol) ?>
        </span>
        <span><?= htmlspecialchars($__nombre) ?></span>
        <a href="logout.php" class="btn-logout">Salir</a>
    </div>
</nav>

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
