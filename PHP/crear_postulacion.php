<?php
require_once "guard.php";
requerirRol('postulante');
include "conexion.php";
$__titulo = "Crear Postulación";
$error = "";
$ok    = "";
// Cargar datos para selects
$regiones   = $conexion->query("SELECT R_ID, R_Nombre FROM region ORDER BY R_ID");
$campus     = $conexion->query("SELECT C_Id, C_Nombre FROM campus ORDER BY C_Nombre");
$tipos      = $conexion->query("SELECT TI_Id, TI_Tipo FROM tipoiniciativa");
$empresas   = $conexion->query("SELECT E_Rut, E_Nombre FROM empresaexterna ORDER BY E_Nombre");
$integrantes= $conexion->query("SELECT I_Rut, I_Nombre FROM integrantes ORDER BY I_Nombre");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Cabecera
    $codigo      = trim($_POST['codigo']      ?? '');
    $nombre      = trim($_POST['nombre']      ?? '');
    $presupuesto = (int)($_POST['presupuesto'] ?? 0);
    $fecha       = $_POST['fecha']            ?? date('Y-m-d');
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
    // Equipo (arrays)
    $eq_ruts  = $_POST['eq_rut']  ?? [];
    $eq_areas = $_POST['eq_area'] ?? [];
    $eq_roles = $_POST['eq_rol']  ?? [];
    // Etapas (arrays)
    $et_nombres     = $_POST['et_nombre']     ?? [];
    $et_semanas     = $_POST['et_semanas']    ?? [];
    $et_entregables = $_POST['et_entregable'] ?? [];
    if (!$codigo || !$nombre || !$presupuesto || !$descripcion || !$iniciativa
        || !$resp1 || !$resp2 || !$empresa_rut || !$region_ej || !$region_imp || !$campus_id) {
        $error = "Completa todos los campos obligatorios.";
    } elseif ($resp1 === $resp2) {
        $error = "El Responsable 1 y el Responsable 2 no pueden ser la misma persona.";
    } else {
        // Verificar código único
        $chk = $conexion->prepare("SELECT P_Id FROM postulacion WHERE P_Codigo_interno = ?");
        $chk->bind_param("s", $codigo);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = "El código interno ya existe. Elige otro.";
        } else {
            // insertamos la postulacion como borrador
            $stmt = $conexion->prepare(
                "INSERT INTO postulacion
                (P_Codigo_interno, P_Nombre, P_Presupuesto, P_Fecha, P_Descripcion,
                P_Objetivo, P_Solucion, P_Resultados_Esperados, P_Otros_Documentos,
                P_Iniciativa_ID, P_Responsable1_Rut, P_Responsable2_Rut,
                P_Empresa_Rut, P_Region_Realizar, P_Region_Impacto, P_Estado_ID, P_ID_Campus)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,?)"
            );
                $stmt->bind_param("ssissssssissiiii",
                $codigo, $nombre, $presupuesto, $fecha, $descripcion,
                $objetivo, $solucion, $resultados, $otros_docs,
                $iniciativa, $resp1, $resp2,
                $empresa_rut, $region_ej, $region_imp, $campus_id
            );
            if ($stmt->execute()) {
                $new_id = $conexion->insert_id;
                // Insertar equipo
                foreach ($eq_ruts as $idx => $rut) {
                    $rut   = trim($rut);
                    $area  = trim($eq_areas[$idx] ?? '');
                    $rol   = trim($eq_roles[$idx] ?? '');
                    if ($rut && $area && $rol) {
                        $stmtEq = $conexion->prepare(
                            "INSERT IGNORE INTO equipotrabajo (EQ_Rut_Integrante, EQ_Id_Postulacion, EQ_AreaEspecializacion, EQ_Rol)
                            VALUES (?,?,?,?)"
                        );
                        $stmtEq->bind_param("siss", $rut, $new_id, $area, $rol);
                        $stmtEq->execute();
                    }
                }
                // Insertar etapas
                foreach ($et_nombres as $idx => $nombre_et) {
                    $nombre_et   = trim($nombre_et);
                    $semanas     = (int)($et_semanas[$idx] ?? 0);
                    $entregable  = trim($et_entregables[$idx] ?? '');
                    if ($nombre_et && $semanas > 0 && $entregable) {
                        $stmtEt = $conexion->prepare(
                            "INSERT INTO etapa (ET_Nombre, ET_Semanas, ET_Entregable, ET_Postulacion_ID)
                            VALUES (?,?,?,?)"
                        );
                        $stmtEt->bind_param("sisi", $nombre_et, $semanas, $entregable, $new_id);
                        $stmtEt->execute();
                    }
                }
                $_SESSION['flash_ok'] = "Postulación creada como borrador (ID: $new_id).";
                header("Location: mis_postulaciones.php");
                exit();
            } else {
                $error = "Error al guardar: " . $conexion->error;
            }
        }
    }
}
// recargamos las listas pa que el formulario no quede vacio si hay error
$regiones->data_seek(0);
$campus->data_seek(0);
$tipos->data_seek(0);
$empresas->data_seek(0);
$integrantes->data_seek(0);
include "navbar.php";
?>
<h2 class="titulo-seccion">Nueva Postulación</h2>
<?php if ($error): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<form method="POST" class="formulario-grande">
    <!-- ANTECEDENTES POSTULACIÓN -->
    <div class="card">
        <h3 class="card-titulo">Antecedentes de Postulación (*)</h3>
        <div class="grid-2col">
            <div class="campo">
                <label>Fecha Postulación *</label>
                <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="campo">
                <label>Código Uso Interno *</label>
                <input type="text" name="codigo" placeholder="Ej: proy-013" required
                    value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>">
            </div>
            <div class="campo">
                <label>Sede(s) USM Asociada *</label>
                <select name="campus_id" required>
                    <option value="">-- Selecciona --</option>
                    <?php while ($c = $campus->fetch_assoc()): ?>
                        <option value="<?= $c['C_Id'] ?>" <?= ($_POST['campus_id'] ?? '') == $c['C_Id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['C_Nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Tipo Iniciativa *</label>
                <select name="iniciativa" required>
                    <option value="">-- Selecciona --</option>
                    <?php while ($t = $tipos->fetch_assoc()): ?>
                        <option value="<?= $t['TI_Id'] ?>" <?= ($_POST['iniciativa'] ?? '') == $t['TI_Id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['TI_Tipo']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Región de Ejecución *</label>
                <select name="region_ej" required>
                    <option value="">-- Selecciona --</option>
                    <?php while ($r = $regiones->fetch_assoc()): ?>
                        <option value="<?= $r['R_ID'] ?>" <?= ($_POST['region_ej'] ?? '') == $r['R_ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['R_Nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Región de Impacto *</label>
                <select name="region_imp" required>
                    <option value="">-- Selecciona --</option>
                    <?php
                    $regiones2 = $conexion->query("SELECT R_ID, R_Nombre FROM region ORDER BY R_ID");
                    while ($r = $regiones2->fetch_assoc()):
                    ?>
                        <option value="<?= $r['R_ID'] ?>" <?= ($_POST['region_imp'] ?? '') == $r['R_ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['R_Nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Responsable 1 — Jefe de Carreras *</label>
                <select name="resp1" required>
                    <option value="">-- Selecciona --</option>
                    <?php while ($i = $integrantes->fetch_assoc()): ?>
                        <option value="<?= $i['I_Rut'] ?>" <?= ($_POST['resp1'] ?? '') == $i['I_Rut'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['I_Nombre']) ?> (<?= $i['I_Rut'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="campo">
                <label>Responsable 2 — Coord. CT-USM *</label>
                <select name="resp2" required>
                    <option value="">-- Selecciona --</option>
                    <?php
                    $integrantes2 = $conexion->query("SELECT I_Rut, I_Nombre FROM integrantes ORDER BY I_Nombre");
                    while ($i = $integrantes2->fetch_assoc()):
                    ?>
                        <option value="<?= $i['I_Rut'] ?>" <?= ($_POST['resp2'] ?? '') == $i['I_Rut'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['I_Nombre']) ?> (<?= $i['I_Rut'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </div>
    <!-- ANTECEDENTES EMPRESA -->
    <div class="card">
        <h3 class="card-titulo">Antecedentes Entidad Externa (*)</h3>
        <div class="campo">
            <label>Empresa *</label>
            <select name="empresa_rut" required>
                <option value="">-- Selecciona empresa --</option>
                <?php while ($e = $empresas->fetch_assoc()): ?>
                    <option value="<?= $e['E_Rut'] ?>" <?= ($_POST['empresa_rut'] ?? '') === $e['E_Rut'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['E_Nombre']) ?> (<?= $e['E_Rut'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
    <!-- ANTECEDENTES INICIATIVA -->
    <div class="card">
        <h3 class="card-titulo">Antecedentes de la Iniciativa (*)</h3>
        <div class="campo">
            <label>Nombre Iniciativa *</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>
        <div class="campo">
            <label>Objetivo / Descripción del problema (*)</label>
            <textarea name="descripcion" rows="4" required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>
        <div class="campo">
            <label>Descripción posible(s) solución(es) (*)</label>
            <textarea name="objetivo" rows="4"><?= htmlspecialchars($_POST['objetivo'] ?? '') ?></textarea>
        </div>
        <div class="campo">
            <label>Resultados esperados por la Entidad Externa (*)</label>
            <textarea name="solucion" rows="4"><?= htmlspecialchars($_POST['solucion'] ?? '') ?></textarea>
        </div>
        <div class="campo">
            <label>Otros Documentos</label>
            <input type="text" name="otros_docs" placeholder="nombre_archivo.pdf"
                value="<?= htmlspecialchars($_POST['otros_docs'] ?? '') ?>">
        </div>
        <div class="campo">
            <label>Presupuesto Total ($) *</label>
            <input type="number" name="presupuesto" min="1" required
                value="<?= htmlspecialchars($_POST['presupuesto'] ?? '') ?>">
        </div>
    </div>
    <!-- EQUIPO DE TRABAJO -->
    <div class="card">
        <h3 class="card-titulo">Equipo de Trabajo (*)</h3>
        <table class="tabla tabla-sm" id="tablaEquipo">
            <thead>
                <tr><th>Integrante</th><th>Área Especialización</th><th>Rol</th><th></th></tr>
            </thead>
            <tbody id="filas-equipo">
                <tr>
                    <td>
                        <select name="eq_rut[]">
                            <option value="">-- Selecciona --</option>
                            <?php
                            $int3 = $conexion->query("SELECT I_Rut, I_Nombre FROM integrantes ORDER BY I_Nombre");
                            while ($i = $int3->fetch_assoc()):
                            ?>
                                <option value="<?= $i['I_Rut'] ?>"><?= htmlspecialchars($i['I_Nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td><input type="text" name="eq_area[]" placeholder="Ej: IA"></td>
                    <td><input type="text" name="eq_rol[]" placeholder="Ej: Investigador"></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="quitarFila(this)">✕</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-secundario" onclick="agregarIntegrante()">+ Agregar integrante</button>
    </div>
    <!-- CRONOGRAMA -->
    <div class="card">
        <h3 class="card-titulo">Cronograma (*)</h3>
        <table class="tabla tabla-sm" id="tablaCronograma">
            <thead>
                <tr><th>Etapa</th><th>Plazos (Semanas)</th><th>Entregable</th><th></th></tr>
            </thead>
            <tbody id="filas-etapas">
                <tr>
                    <td><input type="text" name="et_nombre[]" placeholder="Ej: Diseño"></td>
                    <td><input type="number" name="et_semanas[]" min="1" placeholder="4"></td>
                    <td><input type="text" name="et_entregable[]" placeholder="Ej: Informe"></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="quitarFila(this)">✕</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-secundario" onclick="agregarEtapa()">+ Agregar etapa</button>
    </div>
    <div class="acciones-bottom">
        <a href="mis_postulaciones.php" class="btn btn-secundario">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar Borrador</button>
    </div>
</form>
<script>
// recargamos las listas pa que el formulario no quede vacio si hay error
const opcionesIntegrantes = `<?php
$int4 = $conexion->query("SELECT I_Rut, I_Nombre FROM integrantes ORDER BY I_Nombre");
$opts = '<option value="">-- Selecciona --</option>';
while ($i = $int4->fetch_assoc()) {
    $opts .= '<option value="' . htmlspecialchars($i['I_Rut'], ENT_QUOTES) . '">'
    . htmlspecialchars($i['I_Nombre'], ENT_QUOTES) . '</option>';
}
echo addslashes($opts);
?>`;
function agregarIntegrante() {
    const tbody = document.getElementById('filas-equipo');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><select name="eq_rut[]">${opcionesIntegrantes}</select></td>
        <td><input type="text" name="eq_area[]" placeholder="Ej: IA"></td>
        <td><input type="text" name="eq_rol[]" placeholder="Ej: Investigador"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="quitarFila(this)">✕</button></td>
    `;
    tbody.appendChild(tr);
}
function agregarEtapa() {
    const tbody = document.getElementById('filas-etapas');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="et_nombre[]" placeholder="Ej: Diseño"></td>
        <td><input type="number" name="et_semanas[]" min="1" placeholder="4"></td>
        <td><input type="text" name="et_entregable[]" placeholder="Ej: Informe"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="quitarFila(this)">✕</button></td>
    `;
    tbody.appendChild(tr);
}
function quitarFila(btn) {
    btn.closest('tr').remove();
}
</script>
<?php include "footer.php"; ?>
