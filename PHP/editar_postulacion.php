<?php
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
require_once "guard.php";
requerirRol('postulante', 'coordinador');
include "conexion.php";
$__titulo = "Editar Postulación";
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit(); }
// Cargar postulación actual
$stmtLoad = $conexion->prepare("SELECT * FROM postulacion WHERE P_Id = ?");
$stmtLoad->bind_param("i", $id);
$stmtLoad->execute();
$post = $stmtLoad->get_result()->fetch_assoc();
if (!$post) {
    $_SESSION['flash_error'] = "Postulación no encontrada.";
    header("Location: index.php"); exit();
}
// Verificar permisos según rol
if ($__rol === 'postulante') {
    if ($post['P_Responsable1_Rut'] !== $__rut && $post['P_Responsable2_Rut'] !== $__rut) {
        $_SESSION['flash_error'] = "No tienes permiso para editar esa postulación.";
        header("Location: mis_postulaciones.php"); exit();
    }
    if (!in_array($post['P_Estado_ID'], [1, 2, 3])) {
        $_SESSION['flash_error'] = "Solo puedes editar postulaciones en estado Borrador, Enviada o En Revisión.";
        header("Location: mis_postulaciones.php"); exit();
    }
}
$error = "";
// Cargar datos equipo y etapas actuales
$stmtEq = $conexion->prepare(
    "SELECT eq.EQ_Rut_Integrante, eq.EQ_AreaEspecializacion, eq.EQ_Rol
    FROM equipotrabajo eq WHERE eq.EQ_Id_Postulacion = ?"
);
$stmtEq->bind_param("i", $id);
$stmtEq->execute();
$equipoActual = $stmtEq->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtEt = $conexion->prepare(
    "SELECT ET_Id, ET_Nombre, ET_Semanas, ET_Entregable FROM etapa WHERE ET_Postulacion_ID = ? ORDER BY ET_Id"
);
$stmtEt->bind_param("i", $id);
$stmtEt->execute();
$etapasActual = $stmtEt->get_result()->fetch_all(MYSQLI_ASSOC);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre      = trim($_POST['nombre']      ?? '');
    $presupuesto = (int)($_POST['presupuesto'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $objetivo    = trim($_POST['objetivo']    ?? '');
    $solucion    = trim($_POST['solucion']    ?? '');
    $resultados  = trim($_POST['resultados']  ?? '');
    $otros_docs  = trim($_POST['otros_docs']  ?? '');
    $iniciativa  = (int)($_POST['iniciativa'] ?? 0);
    $resp1       = trim($_POST['resp1']       ?? '');
    $resp2       = trim($_POST['resp2']       ?? '');
    $empresa_rut = trim($_POST['empresa_rut'] ?? '');
    $region_ej   = (int)($_POST['region_ej']  ?? 0);
    $region_imp  = (int)($_POST['region_imp'] ?? 0);
    $campus_id   = (int)($_POST['campus_id']  ?? 0);
    $eq_ruts  = $_POST['eq_rut']  ?? [];
    $eq_areas = $_POST['eq_area'] ?? [];
    $eq_roles = $_POST['eq_rol']  ?? [];
    $et_nombres     = $_POST['et_nombre']     ?? [];
    $et_semanas     = $_POST['et_semanas']    ?? [];
    $et_entregables = $_POST['et_entregable'] ?? [];
    if (!$nombre || !$presupuesto || !$descripcion || !$iniciativa
        || !$resp1 || !$resp2 || !$empresa_rut || !$region_ej || !$region_imp || !$campus_id) {
        $error = "Completa todos los campos obligatorios.";
    } elseif ($resp1 === $resp2) {
        $error = "El Responsable 1 y el Responsable 2 no pueden ser la misma persona.";
    } else {
        $stmtUp = $conexion->prepare(
            "UPDATE postulacion SET
            P_Nombre=?, P_Presupuesto=?, P_Descripcion=?, P_Objetivo=?,
            P_Solucion=?, P_Resultados_Esperados=?, P_Otros_Documentos=?,
            P_Iniciativa_ID=?, P_Responsable1_Rut=?, P_Responsable2_Rut=?,
            P_Empresa_Rut=?, P_Region_Realizar=?, P_Region_Impacto=?, P_ID_Campus=?
            WHERE P_Id=?"
        );
            $stmtUp->bind_param("sisssssississiii",
            $nombre, $presupuesto, $descripcion, $objetivo,
            $solucion, $resultados, $otros_docs,
            $iniciativa, $resp1, $resp2,
            $empresa_rut, $region_ej, $region_imp, $campus_id, $id
        );
        if ($stmtUp->execute()) {
            // borramos el equipo anterior y metemos el nuevo
            $delEq = $conexion->prepare("DELETE FROM equipotrabajo WHERE EQ_Id_Postulacion=?");
            $delEq->bind_param("i", $id); $delEq->execute();
            foreach ($eq_ruts as $idx => $rut) {
                $rut  = trim($rut);
                $area = trim($eq_areas[$idx] ?? '');
                $rol  = trim($eq_roles[$idx] ?? '');
                if ($rut && $area && $rol) {
                    $ins = $conexion->prepare("INSERT IGNORE INTO equipotrabajo (EQ_Rut_Integrante, EQ_Id_Postulacion, EQ_AreaEspecializacion, EQ_Rol) VALUES (?,?,?,?)");
                    $ins->bind_param("siss", $rut, $id, $area, $rol);
                    $ins->execute();
                }
            }
            // Reemplazar etapas
            $delEt = $conexion->prepare("DELETE FROM etapa WHERE ET_Postulacion_ID=?");
            $delEt->bind_param("i", $id); $delEt->execute();
            foreach ($et_nombres as $idx => $nombre_et) {
                $nombre_et  = trim($nombre_et);
                $semanas    = (int)($et_semanas[$idx] ?? 0);
                $entregable = trim($et_entregables[$idx] ?? '');
                if ($nombre_et && $semanas > 0 && $entregable) {
                    $ins = $conexion->prepare("INSERT INTO etapa (ET_Nombre, ET_Semanas, ET_Entregable, ET_Postulacion_ID) VALUES (?,?,?,?)");
                    $ins->bind_param("sisi", $nombre_et, $semanas, $entregable, $id);
                    $ins->execute();
                }
            }
            $_SESSION['flash_ok'] = "Postulación actualizada correctamente.";
            header("Location: ver_postulacion.php?id=$id"); exit();
        } else {
            $error = "Error al actualizar: " . $conexion->error;
        }
    }
    // si fallo algo dejamos los datos que ya tenia el form
    $post['P_Nombre']       = $nombre;
    $post['P_Presupuesto']  = $presupuesto;
    $post['P_Descripcion']  = $descripcion;
    $post['P_Objetivo']     = $objetivo;
    $post['P_Solucion']     = $solucion;
    $post['P_Otros_Documentos'] = $otros_docs;
}
// Cargar listas para selects
$regiones   = $conexion->query("SELECT R_ID, R_Nombre FROM region ORDER BY R_ID");
$campus     = $conexion->query("SELECT C_Id, C_Nombre FROM campus ORDER BY C_Nombre");
$tipos      = $conexion->query("SELECT TI_Id, TI_Tipo FROM tipoiniciativa");
$empresas   = $conexion->query("SELECT E_Rut, E_Nombre FROM empresaexterna ORDER BY E_Nombre");
$integrantesOpts = $conexion->query("SELECT I_Rut, I_Nombre FROM integrantes ORDER BY I_Nombre")->fetch_all(MYSQLI_ASSOC);
include "navbar.php";
?>
<h2 class="titulo-seccion">Editar Postulación — <?= htmlspecialchars($post['P_Codigo_interno']) ?></h2>
<?php if ($error): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST" class="formulario-grande">
    <div class="card">
        <h3 class="card-titulo">Antecedentes de Postulación</h3>
        <div class="grid-2col">
            <div class="campo">
                <label>Sede / Campus *</label>
                <select name="campus_id" required>
                    <?php while ($c = $campus->fetch_assoc()): ?>
                        <option value="<?= $c['C_Id'] ?>" <?= $post['P_ID_Campus'] == $c['C_Id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['C_Nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Tipo Iniciativa *</label>
                <select name="iniciativa" required>
                    <?php while ($t = $tipos->fetch_assoc()): ?>
                        <option value="<?= $t['TI_Id'] ?>" <?= $post['P_Iniciativa_ID'] == $t['TI_Id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['TI_Tipo']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Región de Ejecución *</label>
                <select name="region_ej" required>
                    <?php while ($r = $regiones->fetch_assoc()): ?>
                        <option value="<?= $r['R_ID'] ?>" <?= $post['P_Region_Realizar'] == $r['R_ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['R_Nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Región de Impacto *</label>
                <select name="region_imp" required>
                    <?php
                    $reg2 = $conexion->query("SELECT R_ID, R_Nombre FROM region ORDER BY R_ID");
                    while ($r = $reg2->fetch_assoc()):
                    ?>
                        <option value="<?= $r['R_ID'] ?>" <?= $post['P_Region_Impacto'] == $r['R_ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['R_Nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Responsable 1 *</label>
                <select name="resp1" required>
                    <?php foreach ($integrantesOpts as $i): ?>
                        <option value="<?= $i['I_Rut'] ?>" <?= $post['P_Responsable1_Rut'] === $i['I_Rut'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['I_Nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label>Responsable 2 *</label>
                <select name="resp2" required>
                    <?php foreach ($integrantesOpts as $i): ?>
                        <option value="<?= $i['I_Rut'] ?>" <?= $post['P_Responsable2_Rut'] === $i['I_Rut'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['I_Nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="card">
        <h3 class="card-titulo">Entidad Externa</h3>
        <div class="campo">
            <label>Empresa *</label>
            <select name="empresa_rut" required>
                <?php while ($e = $empresas->fetch_assoc()): ?>
                    <option value="<?= $e['E_Rut'] ?>" <?= $post['P_Empresa_Rut'] === $e['E_Rut'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['E_Nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
    <div class="card">
        <h3 class="card-titulo">Iniciativa</h3>
        <div class="campo">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($post['P_Nombre']) ?>">
        </div>
        <div class="campo">
            <label>Objetivo / Descripción del problema *</label>
            <textarea name="descripcion" rows="4" required><?= htmlspecialchars($post['P_Descripcion']) ?></textarea>
        </div>
        <div class="campo">
            <label>Posible(s) solución(es)</label>
            <textarea name="objetivo" rows="3"><?= htmlspecialchars($post['P_Objetivo'] ?? '') ?></textarea>
        </div>
        <div class="campo">
            <label>Resultados esperados</label>
            <textarea name="solucion" rows="3"><?= htmlspecialchars($post['P_Solucion'] ?? '') ?></textarea>
        </div>
        <div class="campo">
            <label>Otros documentos</label>
            <input type="text" name="otros_docs" value="<?= htmlspecialchars($post['P_Otros_Documentos'] ?? '') ?>">
        </div>
        <div class="campo">
            <label>Presupuesto ($) *</label>
            <input type="number" name="presupuesto" min="1" required value="<?= $post['P_Presupuesto'] ?>">
        </div>
    </div>
    <!-- EQUIPO -->
    <div class="card">
        <h3 class="card-titulo">Equipo de Trabajo</h3>
        <table class="tabla tabla-sm">
            <thead><tr><th>Integrante</th><th>Área</th><th>Rol</th><th></th></tr></thead>
            <tbody id="filas-equipo">
            <?php foreach ($equipoActual as $m): ?>
                <tr>
                    <td>
                        <select name="eq_rut[]">
                            <?php foreach ($integrantesOpts as $i): ?>
                                <option value="<?= $i['I_Rut'] ?>" <?= $m['EQ_Rut_Integrante'] === $i['I_Rut'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($i['I_Nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="eq_area[]" value="<?= htmlspecialchars($m['EQ_AreaEspecializacion']) ?>"></td>
                    <td><input type="text" name="eq_rol[]"  value="<?= htmlspecialchars($m['EQ_Rol']) ?>"></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">✕</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-secundario" onclick="agregarIntegrante()">+ Agregar</button>
    </div>
    <!-- ETAPAS -->
    <div class="card">
        <h3 class="card-titulo">Cronograma</h3>
        <table class="tabla tabla-sm">
            <thead><tr><th>Etapa</th><th>Semanas</th><th>Entregable</th><th></th></tr></thead>
            <tbody id="filas-etapas">
            <?php foreach ($etapasActual as $et): ?>
                <tr>
                    <td><input type="text" name="et_nombre[]"     value="<?= htmlspecialchars($et['ET_Nombre']) ?>"></td>
                    <td><input type="number" name="et_semanas[]"  value="<?= $et['ET_Semanas'] ?>" min="1"></td>
                    <td><input type="text" name="et_entregable[]" value="<?= htmlspecialchars($et['ET_Entregable']) ?>"></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">✕</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-secundario" onclick="agregarEtapa()">+ Agregar</button>
    </div>
    <div class="acciones-bottom">
        <a href="ver_postulacion.php?id=<?= $id ?>" class="btn btn-secundario">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>
<script>
const opcionesIntegrantes = `<?php
$opts = '';
foreach ($integrantesOpts as $i) {
    $opts .= '<option value="' . htmlspecialchars($i['I_Rut'], ENT_QUOTES) . '">'
    . htmlspecialchars($i['I_Nombre'], ENT_QUOTES) . '</option>';
}
echo addslashes($opts);
?>`;
function agregarIntegrante() {
    const tbody = document.getElementById('filas-equipo');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><select name="eq_rut[]">${opcionesIntegrantes}</select></td>
        <td><input type="text" name="eq_area[]" placeholder="Área"></td>
        <td><input type="text" name="eq_rol[]" placeholder="Rol"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">✕</button></td>`;
    tbody.appendChild(tr);
}
function agregarEtapa() {
    const tbody = document.getElementById('filas-etapas');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><input type="text" name="et_nombre[]" placeholder="Etapa"></td>
        <td><input type="number" name="et_semanas[]" min="1" placeholder="4"></td>
        <td><input type="text" name="et_entregable[]" placeholder="Entregable"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">✕</button></td>`;
    tbody.appendChild(tr);
}
</script>
<?php include "footer.php"; ?>
