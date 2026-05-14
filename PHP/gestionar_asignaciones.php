<?php
require_once "guard.php";
requerirRol('administrador');
include "conexion.php";
$__titulo = "Gestionar Asignaciones";

// Asignar evaluador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'asignar') {
    $postulacion_id  = (int)$_POST['postulacion_id'];
    $evaluador_rut   = trim($_POST['evaluador_rut']);

    if ($postulacion_id && $evaluador_rut) {
        // Desactivar asignación previa
        $des = $conexion->prepare("UPDATE asignacion_evaluador SET AE_Activo=0 WHERE AE_Postulacion_ID=?");
        $des->bind_param("i", $postulacion_id);
        $des->execute();

        // Crear nueva
        $ins = $conexion->prepare(
            "INSERT INTO asignacion_evaluador (AE_Postulacion_ID, AE_Evaluador_Rut) VALUES (?,?)"
        );
        $ins->bind_param("is", $postulacion_id, $evaluador_rut);
        if ($ins->execute()) {
            // Cambiar estado a "En Revision" si estaba en "Enviada"
            $conexion->prepare("UPDATE postulacion SET P_Estado_ID=3 WHERE P_Id=? AND P_Estado_ID=2")
                ->execute() || null;
            $upd = $conexion->prepare("UPDATE postulacion SET P_Estado_ID=3 WHERE P_Id=? AND P_Estado_ID=2");
            $upd->bind_param("i", $postulacion_id);
            $upd->execute();

            $_SESSION['flash_ok'] = "Evaluador asignado correctamente.";
        } else {
            $_SESSION['flash_error'] = "Error al asignar: " . $conexion->error;
        }
    }
    header("Location: gestionar_asignaciones.php"); exit();
}

// Listar postulaciones enviadas sin asignación activa + las asignadas
$postulaciones = $conexion->query(
    "SELECT vp.*, ae.AE_Evaluador_Rut, u.U_Nombre AS EvaluadorNombre
    FROM vista_postulaciones vp
    LEFT JOIN asignacion_evaluador ae ON ae.AE_Postulacion_ID = vp.P_Id AND ae.AE_Activo = 1
    LEFT JOIN usuarios u ON ae.AE_Evaluador_Rut = u.U_Rut
    WHERE vp.P_Estado_ID IN (2,3,4,5)
    ORDER BY vp.P_Fecha DESC"
);

$evaluadores = $conexion->query(
    "SELECT U_Rut, U_Nombre FROM usuarios WHERE U_Rol='coordinador' AND U_Activo=1 ORDER BY U_Nombre"
)->fetch_all(MYSQLI_ASSOC);

include "navbar.php";
?>

<h2 class="titulo-seccion">Gestionar Asignaciones de Evaluadores</h2>

<div class="tabla-wrapper">
    <table class="tabla">
        <thead>
            <tr>
                <th>Código</th>
                <th>Iniciativa</th>
                <th>Empresa</th>
                <th>Estado</th>
                <th>Evaluador Actual</th>
                <th>Asignar Evaluador</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $postulaciones->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['P_Codigo_interno']) ?></td>
                <td>
                    <a href="ver_postulacion.php?id=<?= $row['P_Id'] ?>">
                        <?= htmlspecialchars($row['P_Nombre']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($row['Empresa']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['Estado'])) ?>"><?= $row['Estado'] ?></span></td>
                <td><?= $row['EvaluadorNombre'] ? htmlspecialchars($row['EvaluadorNombre']) : '<span class="texto-muted">Sin asignar</span>' ?></td>
                <td>
                    <?php if (in_array($row['P_Estado_ID'], [2, 3])): ?>
                    <form method="POST" style="display:flex;gap:.5rem;align-items:center">
                        <input type="hidden" name="accion" value="asignar">
                        <input type="hidden" name="postulacion_id" value="<?= $row['P_Id'] ?>">
                        <select name="evaluador_rut" required>
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($evaluadores as $ev): ?>
                                <option value="<?= $ev['U_Rut'] ?>" <?= $row['AE_Evaluador_Rut'] === $ev['U_Rut'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ev['U_Nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Asignar</button>
                    </form>
                    <?php else: ?>
                        <span class="texto-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "footer.php"; ?>
