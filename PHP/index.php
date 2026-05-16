<?php
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
require_once "guard.php";
include "conexion.php";
$__titulo = "Inicio";

// Búsqueda rápida desde barra
$busqueda = trim($_GET['q'] ?? '');

// Listado de postulaciones "gestionadas" (estado != borrador)
// Postulante: solo ve sus postulaciones (no borrador de otros)
// Coordinador/Admin: ve todas las enviadas/evaluadas
if ($__rol === 'postulante') {
    if ($busqueda !== '') {
        $stmt = $conexion->prepare(
            "SELECT * FROM vista_postulaciones
            WHERE (P_Responsable1_Rut = ? OR P_Responsable2_Rut = ?)
            AND P_Estado_ID != 1
            AND (P_Nombre LIKE ? OR Empresa LIKE ? OR P_Codigo_interno LIKE ?)
            ORDER BY P_Fecha DESC"
        );
        $like = "%$busqueda%";
        $stmt->bind_param("sssss", $__rut, $__rut, $like, $like, $like);
    } else {
        $stmt = $conexion->prepare(
            "SELECT * FROM vista_postulaciones
            WHERE (P_Responsable1_Rut = ? OR P_Responsable2_Rut = ?)
            AND P_Estado_ID != 1
            ORDER BY P_Fecha DESC"
        );
        $stmt->bind_param("ss", $__rut, $__rut);
    }
} else {
    if ($busqueda !== '') {
        $stmt = $conexion->prepare(
            "SELECT * FROM vista_postulaciones
            WHERE P_Estado_ID != 1
            AND (P_Nombre LIKE ? OR Empresa LIKE ? OR P_Codigo_interno LIKE ?)
            ORDER BY P_Fecha DESC"
        );
        $like = "%$busqueda%";
        $stmt->bind_param("sss", $like, $like, $like);
    } else {
        $stmt = $conexion->prepare(
            "SELECT * FROM vista_postulaciones
            WHERE P_Estado_ID != 1
            ORDER BY P_Fecha DESC"
        );
    }
}
$stmt->execute();
$postulaciones = $stmt->get_result();
if ($__rol === 'postulante') {
    $stmt_rev = $conexion->prepare("SELECT COUNT(*) as total FROM postulacion WHERE (P_Responsable1_Rut = ? OR P_Responsable2_Rut = ?) AND P_Estado_ID = 3");
    $stmt_rev->bind_param("ss", $__rut, $__rut);
} else {
    $stmt_rev = $conexion->prepare("SELECT COUNT(*) as total FROM postulacion WHERE P_Estado_ID = 3");
}

$stmt_rev->execute();
$res_rev = $stmt_rev->get_result();
$conteo_revision = $res_rev->fetch_assoc()['total'] ?? 0;
if ($__rol === 'postulante') {
    $stmt_aprob = $conexion->prepare("SELECT COUNT(*) as total FROM postulacion WHERE (P_Responsable1_Rut = ? OR P_Responsable2_Rut = ?) AND P_Estado_ID = 4");
    $stmt_aprob->bind_param("ss", $__rut, $__rut);
} else {
    $stmt_aprob = $conexion->prepare("SELECT COUNT(*) as total FROM postulacion WHERE P_Estado_ID = 4");
}
if ($__rol === 'postulante') {
    $stmt_rech = $conexion->prepare("SELECT COUNT(*) as total FROM postulacion WHERE (P_Responsable1_Rut = ? OR P_Responsable2_Rut = ?) AND P_Estado_ID = 5");
    $stmt_rech->bind_param("ss", $__rut, $__rut);
} else {
    $stmt_rech = $conexion->prepare("SELECT COUNT(*) as total FROM postulacion WHERE P_Estado_ID = 5");
}

$stmt_rech->execute();
$res_rech = $stmt_rech->get_result();
$conteo_rechazadas = $res_rech->fetch_assoc()['total'] ?? 0;
$stmt_aprob->execute();
$res_aprob = $stmt_aprob->get_result();
$conteo_aprobadas = $res_aprob->fetch_assoc()['total'] ?? 0;
// --- FIN DEL BLOQUE NUEVO ---

include "navbar.php";
?>


<h2 class="titulo-seccion">Postulaciones Gestionadas</h2>

<div class="dashboard-stats">
    <div class="stat-block">
        <span class="stat-label">Total Gestionadas</span>
        <span class="stat-value"><?= $postulaciones->num_rows ?></span>
    </div>
    <div class="stat-block">
        <span class="stat-label">En Revisión</span>
        <span class="stat-value" style="color: var(--dorado);"><?= $conteo_revision ?></span>
    </div>
    <div class="stat-block">
        <span class="stat-label">Aprobadas</span>
        <span class="stat-value" style="color: var(--verde);"><?= $conteo_aprobadas ?></span>
    </div>
    <div class="stat-block">
        <span class="stat-label">Rechazadas</span>
        <span class="stat-value" style="color: var(--rojo);"><?= $conteo_rechazadas ?></span>
    </div>
</div>

<form method="GET" action="index.php" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar...">
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>
<!-- Barra de búsqueda rápida -->
<form method="GET" action="index.php" class="barra-busqueda">
    <input type="text" name="q"
        value="<?= htmlspecialchars($busqueda) ?>"
        placeholder="Buscar por nombre, empresa o código...">
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if ($busqueda): ?>
        <a href="index.php" class="btn btn-secundario">Limpiar</a>
    <?php endif; ?>
</form>


<?php if ($busqueda): ?>
    <p class="resultado-busqueda">Resultados para: <strong><?= htmlspecialchars($busqueda) ?></strong></p>
<?php endif; ?>

<?php if ($postulaciones->num_rows > 0): ?>
<div class="tabla-wrapper">
    <table class="tabla">
        <thead>
            <tr>
                <th>Código</th>
                <th>Iniciativa</th>
                <th>Empresa</th>
                <th>Sede</th>
                <th>Región Ejecución</th>
                <th>Presupuesto</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $postulaciones->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['P_Codigo_interno']) ?></td>
                <td><?= htmlspecialchars($row['P_Nombre']) ?></td>
                <td><?= htmlspecialchars($row['Empresa']) ?></td>
                <td><?= htmlspecialchars($row['Campus']) ?></td>
                <td><?= htmlspecialchars($row['RegionRealizar']) ?></td>
                <td>$<?= number_format($row['P_Presupuesto'], 0, ',', '.') ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['Estado'])) ?>"><?= htmlspecialchars($row['Estado']) ?></span></td>
                <td>
                    <a href="ver_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-info">Ver</a>
                    <?php if ($__rol === 'coordinador' && in_array($row['P_Estado_ID'], [2,3])): ?>
                        <a href="evaluar_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-primary">Evaluar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="vacio">
        <?= $busqueda ? "No se encontraron resultados para \"" . htmlspecialchars($busqueda) . "\"." : "No hay postulaciones gestionadas aún." ?>
    </div>
<?php endif; ?>

<?php include "footer.php"; ?>
