<?php
#Con el archivo guard lo que hacemos es mantener la consistencia de que el usuario siga en linea o haya un usuario logeado en caso contrario lo mandamos devuelta al login
#Con el requiere_once decimos si se encuentra lo ejecutamos y se puede seguir adelante en caso contrario paramos todo
require_once "guard.php";
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
#Conectamos con la base de datos
include "conexion.php";
$__titulo = "Buscar";
$q = trim($_GET['q'] ?? '');
#Incluimos la barra de navegacion de la web
include "navbar.php";
?>
<!--Le ponemos el titulo a la barra de busquedas -->
<h2 class="titulo-seccion">Buscar Postulaciones</h2>
<form method="GET" action="buscar.php" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nombre, empresa o código..." autofocus>
    <button type="submit" class="btn btn-primary">Buscar</button>
    <?php if ($q): ?>
        <a href="buscar.php" class="btn btn-secundario">Limpiar</a>
    <?php endif; ?>
</form>
<?php if ($q !== ''): ?>
<?php
$like = "%$q%";
#Si el usuario es postulante solo buscaremos las postulaciones que tiene
if ($__rol === 'postulante') {
    $stmt = $conexion->prepare(
        "SELECT * FROM vista_postulaciones
        WHERE (P_Responsable1_Rut = ? OR P_Responsable2_Rut = ?)
        AND (P_Nombre LIKE ? OR Empresa LIKE ? OR P_Codigo_interno LIKE ?)
        ORDER BY P_Fecha DESC"
    );
    $stmt->bind_param("sssss", $__rut, $__rut, $like, $like, $like);
} else {
    $stmt = $conexion->prepare(
        "SELECT * FROM vista_postulaciones
        WHERE P_Nombre LIKE ? OR Empresa LIKE ? OR P_Codigo_interno LIKE ?
        ORDER BY P_Fecha DESC"
    );
    $stmt->bind_param("sss", $like, $like, $like);
}
$stmt->execute();
$resultados = $stmt->get_result();
#Codigo Html
?>
<p class="resultado-busqueda">Resultados para: <strong><?= htmlspecialchars($q) ?></strong></p>
<?php if ($resultados->num_rows > 0): ?>
<div class="tabla-wrapper">
    <table class="tabla">
        <thead>
            <tr><th>Código</th><th>Iniciativa</th><th>Empresa</th><th>Campus</th><th>Estado</th><th></th></tr>
        </thead>
        <tbody>
        <?php while ($row = $resultados->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['P_Codigo_interno']) ?></td>
                <td><?= htmlspecialchars($row['P_Nombre']) ?></td>
                <td><?= htmlspecialchars($row['Empresa']) ?></td>
                <td><?= htmlspecialchars($row['Campus']) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['Estado'])) ?>"><?= htmlspecialchars($row['Estado']) ?></span></td>
                <td><a href="ver_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-info">Ver</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="vacio">Sin resultados para "<?= htmlspecialchars($q) ?>".</div>
<?php endif; ?>
<?php endif; ?>
<?php include "footer.php"; ?>
