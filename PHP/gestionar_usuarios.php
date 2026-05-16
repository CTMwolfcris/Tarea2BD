<?php
#Establecemos las variables de sesión mas que nada si las quitamos arroja un error que no influye en elm funcionamiento del codigo y la busqueda
/** @var string $__rol */
/** @var string $__rut */
require_once "guard.php";
requerirRol('administrador');
include "conexion.php";
$__titulo = "Gestionar Usuarios";
$error = "";
$ok    = "";
// Crear usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $rut    = trim($_POST['rut']    ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $clave  = trim($_POST['clave']  ?? '');
    $rol    = trim($_POST['rol']    ?? '');
    if (!$rut || !$nombre || !$email || !$clave || !$rol) {
        $error = "Completa todos los campos.";
    } elseif (!in_array($rol, ['postulante','coordinador','administrador'])) {
        $error = "Rol inválido.";
    } else {
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare(
            "INSERT INTO usuarios (U_Rut, U_Nombre, U_Email, U_Password, U_Rol) VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("sssss", $rut, $nombre, $email, $hash, $rol);
        if ($stmt->execute()) {
            $_SESSION['flash_ok'] = "Usuario creado correctamente.";
            header("Location: gestionar_usuarios.php"); exit();
        } else {
            $error = "Error: " . $conexion->error;
        }
    }
}
// Activar/desactivar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'toggle') {
    $uid = (int)$_POST['uid'];
    $act = (int)$_POST['activo'];
    $stmt = $conexion->prepare("UPDATE usuarios SET U_Activo = ? WHERE U_Id = ?");
    $stmt->bind_param("ii", $act, $uid);
    $stmt->execute();
    $_SESSION['flash_ok'] = $act ? "Usuario activado." : "Usuario desactivado.";
    header("Location: gestionar_usuarios.php"); exit();
}
// Listar usuarios
$usuarios = $conexion->query("SELECT * FROM usuarios ORDER BY U_Rol, U_Nombre");
include "navbar.php";
?>
<h2 class="titulo-seccion">Gestionar Usuarios</h2>
<?php if ($error): ?>
    <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<!-- Crear usuario -->
<div class="card">
    <h3 class="card-titulo">Crear Nuevo Usuario</h3>
    <form method="POST" class="formulario-grande">
        <input type="hidden" name="accion" value="crear">
        <div class="grid-2col">
            <div class="campo">
                <label>RUT *</label>
                <input type="text" name="rut" placeholder="12.345.678-9" required>
            </div>
            <div class="campo">
                <label>Nombre completo *</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="campo">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="campo">
                <label>Contraseña inicial *</label>
                <input type="password" name="clave" required>
            </div>
            <div class="campo">
                <label>Rol *</label>
                <select name="rol" required>
                    <option value="">-- Selecciona --</option>
                    <option value="postulante">Postulante (Responsable Académico)</option>
                    <option value="coordinador">Coordinador (Evaluador CT-USM)</option>
                    <option value="administrador">Administrador CT-USM</option>
                </select>
            </div>
        </div>
        <div class="acciones-bottom">
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </div>
    </form>
</div>
<!-- Listar usuarios -->
<div class="card">
    <h3 class="card-titulo">Usuarios del Sistema</h3>
    <div class="tabla-wrapper">
        <table class="tabla">
            <thead>
                <tr><th>RUT</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
            <?php while ($u = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($u['U_Rut']) ?></td>
                    <td><?= htmlspecialchars($u['U_Nombre']) ?></td>
                    <td><?= htmlspecialchars($u['U_Email']) ?></td>
                    <td><span class="badge rol-<?= $u['U_Rol'] ?>"><?= ucfirst($u['U_Rol']) ?></span></td>
                    <td><?= $u['U_Activo'] ? '<span class="badge badge-aprobada">Activo</span>' : '<span class="badge badge-rechazada">Inactivo</span>' ?></td>
                    <td>
                        <?php if ($u['U_Rut'] !== $__rut): // No puede desactivarse a sí mismo ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="accion" value="toggle">
                                <input type="hidden" name="uid" value="<?= $u['U_Id'] ?>">
                                <input type="hidden" name="activo" value="<?= $u['U_Activo'] ? '0' : '1' ?>">
                                <button type="submit" class="btn btn-sm <?= $u['U_Activo'] ? 'btn-danger' : 'btn-primary' ?>">
                                    <?= $u['U_Activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="texto-muted">(tú)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include "footer.php"; ?>
