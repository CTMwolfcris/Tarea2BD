<?php
require_once "guard.php";
requerirRol('coordinador');
include "conexion.php";
$__titulo = "Mis Asignaciones";

$stmt = $conexion->prepare(
    "SELECT vp.*, ae.AE_Fecha_Asignacion
    FROM vista_postulaciones vp
    JOIN asignacion_evaluador ae ON ae.AE_Postulacion_ID = vp.P_Id
    WHERE ae.AE_Evaluador_Rut = ? AND ae.AE_Activo = 1
    ORDER BY ae.AE_Fecha_Asignacion DESC"
);
$stmt->bind_param("s", $__rut);
$stmt->execute();
$asignaciones = $stmt->get_result();

include "navbar.php";
?>

<h2 class="titulo-seccion">Mis Asignaciones</h2>

<?php if ($asignaciones->num_rows > 0): ?>
<div class="tabla-wrapper">
    <table class="tabla">
        <thead>
            <tr>
                <th>Código</th>
                <th>Iniciativa</th>
                <th>Empresa</th>
                <th>Campus</th>
                <th>Región</th>
                <th>Presupuesto</th>
                <th>Estado</th>
                <th>Asignada</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $asignaciones->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['P_Codigo_interno']) ?></td>
                <td><?= htmlspecialchars($row['P_Nombre']) ?></td>
                <td><?= htmlspecialchars($row['Empresa']) ?></td>
                <td><?= htmlspecialchars($row['Campus']) ?></td>
                <td><?= htmlspecialchars($row['RegionRealizar']) ?></td>
                <td>$<?= number_format($row['P_Presupuesto'], 0, ',', '.') ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['Estado'])) ?>"><?= htmlspecialchars($row['Estado']) ?></span></td>
                <td><?= date('d/m/Y', strtotime($row['AE_Fecha_Asignacion'])) ?></td>
                <td>
                    <a href="ver_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-info">Ver</a>
                    <?php if (in_array($row['P_Estado_ID'], [2, 3])): ?>
                        <a href="evaluar_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-primary">Evaluar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="vacio">No tienes postulaciones asignadas.</div>
<?php endif; ?>

<?php include "footer.php"; ?>
