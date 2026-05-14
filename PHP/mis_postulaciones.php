<?php
require_once "guard.php";
requerirRol('postulante');
include "conexion.php";
$__titulo = "Mis Postulaciones";

$stmt = $conexion->prepare(
    "SELECT vp.*, fn_total_semanas(vp.P_Id) AS TotalSemanas
    FROM vista_postulaciones vp
    WHERE vp.P_Responsable1_Rut = ? OR vp.P_Responsable2_Rut = ?
    ORDER BY vp.P_Fecha DESC"
);
$stmt->bind_param("ss", $__rut, $__rut);
$stmt->execute();
$postulaciones = $stmt->get_result();

include "navbar.php";
?>

<div class="seccion-header">
    <h2 class="titulo-seccion">Mis Postulaciones</h2>
    <a href="crear_postulacion.php" class="btn btn-primary">+ Nueva Postulación</a>
</div>

<?php if ($postulaciones->num_rows > 0): ?>
<div class="tabla-wrapper">
    <table class="tabla">
        <thead>
            <tr>
                <th>Código</th>
                <th>Iniciativa</th>
                <th>Empresa</th>
                <th>Presupuesto</th>
                <th>Duración (sem.)</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $postulaciones->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['P_Codigo_interno']) ?></td>
                <td><?= htmlspecialchars($row['P_Nombre']) ?></td>
                <td><?= htmlspecialchars($row['Empresa']) ?></td>
                <td>$<?= number_format($row['P_Presupuesto'], 0, ',', '.') ?></td>
                <td><?= $row['TotalSemanas'] ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['Estado'])) ?>"><?= htmlspecialchars($row['Estado']) ?></span></td>
                <td class="acciones">
                    <a href="ver_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-info">Ver</a>

                    <?php if ($row['P_Estado_ID'] == 1): // Borrador — puede editar y enviar ?>
                        <a href="editar_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="enviar_postulacion.php?id=<?= $row['P_Id'] ?>"
                        class="btn btn-sm btn-primary"
                        onclick="return confirm('¿Confirmas enviar esta postulación? No podrás editarla después.')">
                            Enviar
                        </a>
                        <a href="eliminar_postulacion.php?id=<?= $row['P_Id'] ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('¿Eliminar esta postulación en borrador?')">
                            Eliminar
                        </a>
                    <?php elseif (in_array($row['P_Estado_ID'], [2, 3])): // Enviada o En Revision ?>
                        <a href="editar_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="vacio">
        Aún no tienes postulaciones.
        <br><a href="crear_postulacion.php" class="btn btn-primary" style="margin-top:1rem">Crear tu primera postulación</a>
    </div>
<?php endif; ?>

<?php include "footer.php"; ?>
