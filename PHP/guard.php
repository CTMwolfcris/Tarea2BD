<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
$__usuario = $_SESSION['usuario'];
$__rol     = $_SESSION['rol'];
$__nombre  = $__usuario['nombre'];
$__rut     = $__usuario['rut'];
/**
 * Verifica que el rol actual sea uno de los permitidos.
 * Si no, redirige al index con un mensaje de error.
 */
function requerirRol(string ...$roles): void {
    global $__rol;
    if (!in_array($__rol, $roles, true)) {
        $_SESSION['flash_error'] = "No tienes permiso para acceder a esa sección.";
        header("Location: index.php");
        exit();
    }
}
?>
