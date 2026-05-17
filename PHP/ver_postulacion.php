<?php
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
require_once "guard.php";
include "conexion.php";
$__titulo = "Detalle Postulación";
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: index.php");
    exit();
}
// Obtener postulación desde la vista
$stmt = $conexion->prepare("SELECT * FROM vista_postulaciones WHERE P_Id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) {
    $_SESSION['flash_error'] = "Postulación no encontrada.";
    header("Location: index.php");
    exit();
}
// Restricción: postulante solo ve las suyas
if ($__rol === 'postulante' && $post['P_Responsable1_Rut'] !== $__rut && $post['P_Responsable2_Rut'] !== $__rut) {
    $_SESSION['flash_error'] = "No tienes permiso para ver esa postulación.";
    header("Location: index.php");
    exit();
}
// Equipo de trabajo
$stmtEq = $conexion->prepare(
    "SELECT eq.EQ_Rol, eq.EQ_AreaEspecializacion, i.I_Nombre, i.I_Rut, i.I_Email, i.I_Telefono
    FROM equipotrabajo eq
    JOIN integrantes i ON eq.EQ_Rut_Integrante = i.I_Rut
    WHERE eq.EQ_Id_Postulacion = ?"
);
$stmtEq->bind_param("i", $id);
$stmtEq->execute();
$equipo = $stmtEq->get_result();
// Cronograma
$stmtEt = $conexion->prepare(
    "SELECT ET_Nombre, ET_Semanas, ET_Entregable FROM etapa WHERE ET_Postulacion_ID = ? ORDER BY ET_Id"
);
$stmtEt->bind_param("i", $id);
$stmtEt->execute();
$etapas = $stmtEt->get_result();
// Total semanas usando function SQL
$stmtSem = $conexion->prepare("SELECT fn_total_semanas(?) AS total");
$stmtSem->bind_param("i", $id);
$stmtSem->execute();
$totalSem = $stmtSem->get_result()->fetch_assoc()['total'];
// Historial de evaluaciones
$stmtEv = $conexion->prepare(
    "SELECT ev.EV_Fecha, ev.EV_Comentario, ep.EP_Nombre AS estado, ev.EV_Evaluador_Rut
    FROM evaluacion ev
    JOIN estadopostulacion ep ON ev.EV_Estado_Nuevo = ep.EP_Id
    WHERE ev.EV_Postulacion_ID = ?
    ORDER BY ev.EV_Fecha DESC"
);
$stmtEv->bind_param("i", $id);
$stmtEv->execute();
$evaluaciones = $stmtEv->get_result();
include "navbar.php";
?>
<div class="seccion-header">
    <h2 class="titulo-seccion">
        <?= htmlspecialchars($post['P_Codigo_interno']) ?> — <?= htmlspecialchars($post['P_Nombre']) ?>
    </h2>
    <span class="badge badge-<?= strtolower(str_replace(' ', '-', $post['Estado'])) ?> badge-lg"><?= htmlspecialchars($post['Estado']) ?></span>
</div>
<!-- ANTECEDENTES POSTULACIÓN -->
<div class="card">
    <h3 class="card-titulo">Antecedentes de Postulación</h3>
    <div class="grid-2col">
        <div><label>Fecha Postulación</label><p><?= $post['P_Fecha'] ?></p></div>
        <div><label>Código Interno</label><p><?= htmlspecialchars($post['P_Codigo_interno']) ?></p></div>
        <div><label>Sede / Campus</label><p><?= htmlspecialchars($post['Campus']) ?></p></div>
        <div><label>Tipo Iniciativa</label><p><?= htmlspecialchars($post['TipoIniciativa']) ?></p></div>
        <div><label>Región de Ejecución</label><p><?= htmlspecialchars($post['RegionRealizar']) ?></p></div>
        <div><label>Región de Impacto</label><p><?= htmlspecialchars($post['RegionImpacto']) ?></p></div>
        <div><label>Responsable 1 (Jefe de Carreras)</label><p><?= htmlspecialchars($post['Responsable1']) ?></p></div>
        <div><label>Responsable 2 (Coord. CT-USM)</label><p><?= htmlspecialchars($post['Responsable2']) ?></p></div>
        <?php if ($post['P_Fecha_Envio']): ?>
        <div><label>Fecha de Envío</label><p><?= $post['P_Fecha_Envio'] ?></p></div>
        <?php endif; ?>
        <div><label>Presupuesto Total</label><p><strong>$<?= number_format($post['P_Presupuesto'], 0, ',', '.') ?></strong></p></div>
    </div>
</div>
<!-- ANTECEDENTES EMPRESA -->
<div class="card">
    <h3 class="card-titulo">Antecedentes Entidad Externa</h3>
    <div class="grid-2col">
        <div><label>Nombre Empresa</label><p><?= htmlspecialchars($post['Empresa']) ?></p></div>
        <div><label>RUT Empresa</label><p><?= htmlspecialchars($post['EmpresaRut']) ?></p></div>
        <div><label>Tamaño</label><p><?= htmlspecialchars($post['TamanoEmpresa']) ?></p></div>
        <div><label>Convenio Marco USM</label><p><?= $post['ConvenioUSM'] ? 'Sí' : 'No' ?></p></div>
    </div>
</div>
<!-- ANTECEDENTES INICIATIVA -->
<div class="card">
    <h3 class="card-titulo">Antecedentes de la Iniciativa</h3>
    <div>
        <label>Nombre Iniciativa</label><p><?= htmlspecialchars($post['P_Nombre']) ?></p>
        <label>Objetivo / Descripción del problema</label>
        <p><?= nl2br(htmlspecialchars($post['P_Descripcion'] ?? '')) ?></p>
        <?php if ($post['P_Objetivo'] ?? null): ?>
        <label>Descripción posibles soluciones</label>
        <p><?= nl2br(htmlspecialchars($post['P_Objetivo'] ?? '')) ?></p>
        <?php endif; ?>
        <?php if ($post['P_Solucion'] ?? null): ?>
        <label>Resultados Esperados</label>
        <p><?= nl2br(htmlspecialchars($post['P_Solucion'] ?? '')) ?></p>
        <p><?= nl2br(htmlspecialchars($post['P_Solucion'])) ?></p>
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars($post['P_Otros_Documentos'] ?? '')) ?></p>
        <?php if ($post['P_Otros_Documentos'] ?? null): ?>
        <label>Otros Documentos</label>
        <p><?= htmlspecialchars($post['P_Otros_Documentos']) ?></p>
        <?php endif; ?>
    </div>
</div>
<!-- EQUIPO DE TRABAJO -->
<div class="card">
    <h3 class="card-titulo">Equipo de Trabajo</h3>
    <?php if ($equipo->num_rows > 0): ?>
    <table class="tabla tabla-sm">
        <thead>
            <tr><th>RUT</th><th>Nombre</th><th>Área</th><th>Rol</th><th>Email</th><th>Teléfono</th></tr>
        </thead>
        <tbody>
        <?php while ($m = $equipo->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($m['I_Rut']) ?></td>
                <td><?= htmlspecialchars($m['I_Nombre']) ?></td>
                <td><?= htmlspecialchars($m['EQ_AreaEspecializacion']) ?></td>
                <td><?= htmlspecialchars($m['EQ_Rol']) ?></td>
                <td><?= htmlspecialchars($m['I_Email']) ?></td>
                <td><?= htmlspecialchars($m['I_Telefono'] ?? '-') ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="texto-muted">Sin integrantes registrados.</p>
    <?php endif; ?>
</div>
<!-- CRONOGRAMA -->
<div class="card">
    <h3 class="card-titulo">Cronograma</h3>
    <?php if ($etapas->num_rows > 0): ?>
    <table class="tabla tabla-sm">
        <thead>
            <tr><th>Etapa</th><th>Semanas</th><th>Entregable</th></tr>
        </thead>
        <tbody>
        <?php while ($et = $etapas->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($et['ET_Nombre']) ?></td>
                <td><?= $et['ET_Semanas'] ?></td>
                <td><?= htmlspecialchars($et['ET_Entregable']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <p class="total-semanas"><strong>Total duración: <?= $totalSem ?> semanas</strong></p>
    <?php else: ?>
        <p class="texto-muted">Sin etapas registradas.</p>
    <?php endif; ?>
</div>
<!-- HISTORIAL EVALUACIONES -->
<?php if ($evaluaciones->num_rows > 0): ?>
<div class="card">
    <h3 class="card-titulo">Historial de Evaluaciones</h3>
    <?php while ($ev = $evaluaciones->fetch_assoc()): ?>
        <div class="evaluacion-item">
            <span class="badge badge-<?= strtolower(str_replace(' ', '-', $ev['estado'])) ?>"><?= htmlspecialchars($ev['estado']) ?></span>
            <span class="eval-fecha"><?= $ev['EV_Fecha'] ?></span>
            <p><?= nl2br(htmlspecialchars($ev['EV_Comentario'])) ?></p>
        </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>
<div class="acciones-bottom">
    <a href="javascript:history.back()" class="btn btn-secundario">← Volver</a>
    <?php if ($__rol === 'postulante' && in_array($post['P_Estado_ID'], [1, 2, 3])): ?>
        <a href="editar_postulacion.php?id=<?= $id ?>" class="btn btn-warning">Editar</a>
    <?php endif; ?>
    <?php if ($__rol === 'coordinador' && in_array($post['P_Estado_ID'], [2, 3])): ?>
        <a href="evaluar_postulacion.php?id=<?= $id ?>" class="btn btn-primary">Evaluar</a>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
