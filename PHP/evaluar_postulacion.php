<?php
require_once "guard.php";
requerirRol('coordinador');
include "conexion.php";
$__titulo = "Evaluar Postulación";
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit(); }
$stmt = $conexion->prepare("SELECT * FROM vista_postulaciones WHERE P_Id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post || !in_array($post['P_Estado_ID'], [2, 3])) {
    $_SESSION['flash_error'] = "Esta postulación no está disponible para evaluación.";
    header("Location: index.php"); exit();
}
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentario   = trim($_POST['comentario'] ?? '');
    $nuevo_estado = (int)($_POST['nuevo_estado'] ?? 0);
    if (!$comentario || !$nuevo_estado) {
        $error = "Debes completar el comentario y seleccionar un resultado.";
    } else {
        // llamamos al SP que registra la evaluacion y cambia el estado
        $sp = $conexion->prepare("CALL sp_registrar_evaluacion(?, ?, ?, ?)");
        $sp->bind_param("issi", $id, $__rut, $comentario, $nuevo_estado);
        if ($sp->execute()) {
            $_SESSION['flash_ok'] = "Evaluación registrada correctamente.";
            header("Location: ver_postulacion.php?id=$id"); exit();
        } else {
            $error = "Error al registrar evaluación: " . $conexion->error;
        }
    }
}
include "navbar.php";
?>
<h2 class="titulo-seccion">Evaluar: <?= htmlspecialchars($post['P_Nombre']) ?></h2>
<p class="texto-muted">Estado actual: <span class="badge badge-<?= strtolower(str_replace(' ', '-', $post['Estado'])) ?>"><?= $post['Estado'] ?></span></p>
<?php if ($error): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<div class="card">
    <div class="grid-2col" style="margin-bottom:1rem">
        <div><label>Empresa</label><p><?= htmlspecialchars($post['Empresa']) ?></p></div>
        <div><label>Campus</label><p><?= htmlspecialchars($post['Campus']) ?></p></div>
        <div><label>Presupuesto</label><p>$<?= number_format($post['P_Presupuesto'], 0, ',', '.') ?></p></div>
        <div><label>Región Ejecución</label><p><?= htmlspecialchars($post['RegionRealizar']) ?></p></div>
    </div>
</div>
<form method="POST" class="formulario-grande">
    <div class="card">
        <h3 class="card-titulo">Registrar Evaluación</h3>
        <div class="campo">
            <label>Comentario / Fundamento *</label>
            <textarea name="comentario" rows="5" required placeholder="Describe el resultado de la evaluación..."><?= htmlspecialchars($_POST['comentario'] ?? '') ?></textarea>
        </div>
        <div class="campo">
            <label>Resultado *</label>
            <select name="nuevo_estado" required>
                <option value="">-- Selecciona resultado --</option>
                <option value="3" <?= ($_POST['nuevo_estado'] ?? '') == 3 ? 'selected' : '' ?>>En Revisión (continuar revisando)</option>
                <option value="4" <?= ($_POST['nuevo_estado'] ?? '') == 4 ? 'selected' : '' ?>>Aprobada</option>
                <option value="5" <?= ($_POST['nuevo_estado'] ?? '') == 5 ? 'selected' : '' ?>>Rechazada</option>
                <option value="6" <?= ($_POST['nuevo_estado'] ?? '') == 6 ? 'selected' : '' ?>>Cerrada</option>
            </select>
        </div>
    </div>
    <div class="acciones-bottom">
        <a href="ver_postulacion.php?id=<?= $id ?>" class="btn btn-secundario">Cancelar</a>
        <button type="submit" class="btn btn-primary">Registrar Evaluación</button>
    </div>
</form>
<?php include "footer.php"; ?>
