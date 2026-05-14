<?php
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
require_once "guard.php";
requerirRol('postulante');
include "conexion.php";

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: mis_postulaciones.php"); exit(); }

$stmt = $conexion->prepare("SELECT P_Estado_ID, P_Responsable1_Rut, P_Responsable2_Rut FROM postulacion WHERE P_Id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    $_SESSION['flash_error'] = "Postulación no encontrada.";
    header("Location: mis_postulaciones.php"); exit();
}

if ($post['P_Responsable1_Rut'] !== $__rut && $post['P_Responsable2_Rut'] !== $__rut) {
    $_SESSION['flash_error'] = "No tienes permiso.";
    header("Location: mis_postulaciones.php"); exit();
}

if ($post['P_Estado_ID'] !== 1) {
    $_SESSION['flash_error'] = "Solo puedes enviar postulaciones en Borrador.";
    header("Location: mis_postulaciones.php"); exit();
}

// Actualizar estado a 2 (Enviada) — el trigger trg_fecha_envio asigna P_Fecha_Envio automáticamente
$upd = $conexion->prepare("UPDATE postulacion SET P_Estado_ID = 2 WHERE P_Id = ?");
$upd->bind_param("i", $id);

if ($upd->execute()) {
    $_SESSION['flash_ok'] = "Postulación enviada correctamente. La fecha de envío fue registrada automáticamente.";
} else {
    $_SESSION['flash_error'] = "Error al enviar: " . $conexion->error;
}

header("Location: mis_postulaciones.php");
exit();
?>
