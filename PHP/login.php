<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include("conexion.php");
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rut   = trim($_POST['rut']   ?? '');
    $clave = trim($_POST['clave'] ?? '');
    if (empty($rut) || empty($clave)) {
        $error = "Debes completar ambos campos.";
    } else {
        $stmt = $conexion->prepare("SELECT U_Id, U_Rut, U_Nombre, U_Email, U_Password, U_RolFROM usuariosWHERE U_Rut = ? AND U_Activo = 1");
        $stmt->bind_param("s", $rut);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
             // Verificar contraseña encriptada
            if (password_verify($clave, $usuario['U_Password'])) {
                $_SESSION['usuario'] = [
                    'id'     => $usuario['U_Id'],
                    'rut'    => $usuario['U_Rut'],
                    'nombre' => $usuario['U_Nombre'],
                    'email'  => $usuario['U_Email'],
                    'rol'    => $usuario['U_Rol'],
                ];
                $_SESSION['rol'] = $usuario['U_Rol'];
                // Redirección según rol
                header("Location: index.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "No existe ningún usuario con ese RUT.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CT-USM — Iniciar Sesión</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="login-body">
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">CT-USM</div>
            <h1>Sistema de Postulaciones</h1>
            <p>Centro Tecnológico USM</p>
        </div>
        <?php if ($error): ?>
            <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <div class="campo">
                <label for="rut">RUT</label>
                <input type="text" id="rut" name="rut"
                placeholder="Ej: 14.555.666-7"
                value="<?= htmlspecialchars($_POST['rut'] ?? '') ?>" required>
            </div>
            <div class="campo">
                <label for="clave">Contraseña</label>
                <input type="password" id="clave" name="clave"
                placeholder="Ingresa tu contraseña" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
            <a href="sing_up.php" class="boton-bonito">Haz clic aquí</a>
        </form>
        <div class="login-hint">
            <strong>Cuentas de prueba</strong> (contraseña: <code>password</code>)<br>
            <small>
                Postulante: 14.555.666-7<br>
                Coordinador: 08.555.444-2<br>
                Administrador: 99.999.999-9
            </small>
        </div>
    </div>
</div>
</body>
</html>
