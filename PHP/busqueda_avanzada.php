<?php
#Con el archivo guard lo que hacemos es mantener la consistencia de que el usuario siga en linea o haya un usuario logeado en caso contrario lo mandamos devuelta al login
#Con el requiere_once decimos si se encuentra lo ejecutamos y se puede seguir adelante en caso contrario paramos todo
require_once "guard.php";
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
#Conectamos con la base de datos
include "conexion.php";
$__titulo = "Búsqueda Avanzada";

// Cargamos las opciones de filtro antes del inicion de la pagina 
$regiones = $conexion->query("SELECT R_ID, R_Nombre FROM region ORDER BY R_ID")->fetch_all(MYSQLI_ASSOC);
$campus = $conexion->query("SELECT C_Id, C_Nombre FROM campus ORDER BY C_Nombre")->fetch_all(MYSQLI_ASSOC);
$tipos = $conexion->query("SELECT TI_Id, TI_Tipo FROM tipoiniciativa")->fetch_all(MYSQLI_ASSOC);
$estados = $conexion->query("SELECT EP_Id, EP_Nombre FROM estadopostulacion")->fetch_all(MYSQLI_ASSOC);
$tamanios = $conexion->query("SELECT T_Id, T_Tamaño FROM tamañoempresa")->fetch_all(MYSQLI_ASSOC);
$evaluadores = $conexion->query("SELECT U_Rut, U_Nombre FROM usuarios WHERE U_Rol = 'coordinador' ORDER BY U_Nombre")->fetch_all(MYSQLI_ASSOC);
// Variables para detectar si el usuario ya le dio a buscar o es la primera vez que entra a la pagina
$resultados = null;
$buscando = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['buscar'])) {
    $buscando = true;
    // Construir query dinámicamente con prepared statement
    $where  = ["1=1"];
    $params = [];
    $types  = "";
    if (!empty($_GET['region_ej'])) {
        $where[]  = "vp.P_Region_Realizar = ?"; // usamos alias de la vista
        // la vista no expone P_Region_Realizar directamente como ID, usamos join directo
    }
    // Realizamos la consulta sql segun el usuario realizo el filtro para eso la consultas sql y los if(!empty())
    $sql = "SELECT p.P_Id, p.P_Codigo_interno, p.P_Nombre, p.P_Presupuesto,
        ep.EP_Nombre AS Estado, p.P_Estado_ID,
        c.C_Nombre AS Campus,
        r1.R_Nombre AS RegionRealizar,
        r2.R_Nombre AS RegionImpacto,
        e.E_Nombre AS Empresa,
        te.T_Tamaño AS TamanoEmpresa,
        e.E_ConvenioUSM AS ConvenioUSM,
        ti.TI_Tipo AS TipoIniciativa
        FROM postulacion p
        JOIN estadopostulacion ep ON p.P_Estado_ID = ep.EP_Id
        JOIN campus c ON p.P_ID_Campus = c.C_Id
        JOIN region r1 ON p.P_Region_Realizar = r1.R_ID
        JOIN region r2 ON p.P_Region_Impacto = r2.R_ID
        JOIN empresaexterna e ON p.P_Empresa_Rut = e.E_Rut
        JOIN tamañoempresa te ON e.E_Tamaño = te.T_Id
        JOIN tipoiniciativa ti ON p.P_Iniciativa_ID = ti.TI_Id
        WHERE 1=1
    ";
    if (!empty($_GET['region_ej'])) {
        $sql .= " AND p.P_Region_Realizar = ?";
        $params[] = (int)$_GET['region_ej'];
        $types .= "i";
    }
    if (!empty($_GET['region_imp'])) {
        $sql .= " AND p.P_Region_Impacto = ?";
        $params[] = (int)$_GET['region_imp'];
        $types .= "i";
    }
    if (!empty($_GET['campus_id'])) {
        $sql .= " AND p.P_ID_Campus = ?";
        $params[] = (int)$_GET['campus_id'];
        $types .= "i";
    }
    if (!empty($_GET['tipo_iniciativa'])) {
        $sql .= " AND p.P_Iniciativa_ID = ?";
        $params[] = (int)$_GET['tipo_iniciativa'];
        $types .= "i";
    }
    if (!empty($_GET['tamanio'])) {
        $sql .= " AND e.E_Tamaño = ?";
        $params[] = (int)$_GET['tamanio'];
        $types .= "i";
    }
    if (isset($_GET['convenio']) && $_GET['convenio'] !== '') {
        $sql .= " AND e.E_ConvenioUSM = ?";
        $params[] = (int)$_GET['convenio'];
        $types .= "i";
    }
    if (!empty($_GET['estado'])) {
        $sql .= " AND p.P_Estado_ID = ?";
        $params[] = (int)$_GET['estado'];
        $types .= "i";
    }
    if (!empty($_GET['evaluador'])) {
        $sql .= " AND EXISTS (SELECT 1 FROM asignacion_evaluador ae WHERE ae.AE_Postulacion_ID = p.P_Id AND ae.AE_Evaluador_Rut = ? AND ae.AE_Activo = 1)";
        $params[] = $_GET['evaluador'];
        $types .= "s";
    }
    if (!empty($_GET['texto'])) {
        $sql .= " AND (p.P_Nombre LIKE ? OR e.E_Nombre LIKE ? OR p.P_Codigo_interno LIKE ?)";
        $like = "%" . $_GET['texto'] . "%";
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types .= "sss";
    }
    // si el rol de la persona es postulante solo puede ver sus postulaciones
    if ($__rol === 'postulante') {
        $sql .= " AND (p.P_Responsable1_Rut = ? OR p.P_Responsable2_Rut = ?)";
        $params[] = $__rut; $params[] = $__rut;
        $types .= "ss";
    }
    $sql .= " ORDER BY p.P_Fecha DESC";
    if (!empty($params)) {
        // Luego de haber realizado todos los filtros necesarios, ejecutamos la consulta sql
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultados = $stmt->get_result();
    } else {
        $resultados = $conexion->query($sql);
    }
}
include "navbar.php";
?>
<h2 class="titulo-seccion">Búsqueda Avanzada</h2>
<form method="GET" action="busqueda_avanzada.php" class="formulario-filtros">
    <div class="filtros-grid">
        <div class="campo">
            <label>Texto libre</label>
            <input type="text" name="texto" placeholder="Nombre, empresa, código..." value="<?= htmlspecialchars($_GET['texto'] ?? '') ?>">
        </div>
        <div class="campo">
            <label>Región de Ejecución</label>
            <select name="region_ej">
                <option value="">Todas</option>
                <?php foreach ($regiones as $r): ?>
                    <option value="<?= $r['R_ID'] ?>" <?= ($_GET['region_ej'] ?? '') == $r['R_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['R_Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label>Región de Impacto</label>
            <select name="region_imp">
                <option value="">Todas</option>
                <?php foreach ($regiones as $r): ?>
                    <option value="<?= $r['R_ID'] ?>" <?= ($_GET['region_imp'] ?? '') == $r['R_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['R_Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label>Sede / Campus</label>
            <select name="campus_id">
                <option value="">Todos</option>
                <?php foreach ($campus as $c): ?>
                    <option value="<?= $c['C_Id'] ?>" <?= ($_GET['campus_id'] ?? '') == $c['C_Id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['C_Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label>Tipo de Iniciativa</label>
            <select name="tipo_iniciativa">
                <option value="">Todos</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t['TI_Id'] ?>" <?= ($_GET['tipo_iniciativa'] ?? '') == $t['TI_Id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['TI_Tipo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label>Tamaño Empresa</label>
            <select name="tamanio">
                <option value="">Todos</option>
                <?php foreach ($tamanios as $t): ?>
                    <option value="<?= $t['T_Id'] ?>" <?= ($_GET['tamanio'] ?? '') == $t['T_Id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['T_Tamaño']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label>Convenio Marco</label>
            <select name="convenio">
                <option value="">Todos</option>
                <option value="1" <?= ($_GET['convenio'] ?? '') === '1' ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= ($_GET['convenio'] ?? '') === '0' ? 'selected' : '' ?>>No</option>
            </select>
        </div>
        <div class="campo">
            <label>Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <?php foreach ($estados as $e): ?>
                    <option value="<?= $e['EP_Id'] ?>" <?= ($_GET['estado'] ?? '') == $e['EP_Id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['EP_Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($__rol !== 'postulante'): ?>
        <div class="campo">
            <label>Evaluador Asignado</label>
            <select name="evaluador">
                <option value="">Todos</option>
                <?php foreach ($evaluadores as $ev): ?>
                    <option value="<?= $ev['U_Rut'] ?>" <?= ($_GET['evaluador'] ?? '') === $ev['U_Rut'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ev['U_Nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
    <div class="filtros-acciones">
        <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
        <a href="busqueda_avanzada.php" class="btn btn-secundario">Limpiar</a>
    </div>
</form>
<?php if ($buscando): ?>
    <?php if ($resultados && $resultados->num_rows > 0): ?>
        <p class="resultado-busqueda"><strong><?= $resultados->num_rows ?></strong> resultado(s) encontrado(s)</p>
        <div class="tabla-wrapper">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Iniciativa</th>
                        <th>Empresa</th>
                        <th>Tamaño</th>
                        <th>Convenio</th>
                        <th>Campus</th>
                        <th>Reg. Ejecución</th>
                        <th>Reg. Impacto</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $resultados->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['P_Codigo_interno']) ?></td>
                        <td><?= htmlspecialchars($row['P_Nombre']) ?></td>
                        <td><?= htmlspecialchars($row['Empresa']) ?></td>
                        <td><?= htmlspecialchars($row['TamanoEmpresa']) ?></td>
                        <td><?= $row['ConvenioUSM'] ? 'Sí' : 'No' ?></td>
                        <td><?= htmlspecialchars($row['Campus']) ?></td>
                        <td><?= htmlspecialchars($row['RegionRealizar']) ?></td>
                        <td><?= htmlspecialchars($row['RegionImpacto']) ?></td>
                        <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $row['Estado'])) ?>"><?= htmlspecialchars($row['Estado']) ?></span></td>
                        <td><a href="ver_postulacion.php?id=<?= $row['P_Id'] ?>" class="btn btn-sm btn-info">Ver</a></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="vacio">No se encontraron postulaciones con esos filtros.</div>
    <?php endif; ?>
<?php endif; ?>
<?php include "footer.php"; ?>
