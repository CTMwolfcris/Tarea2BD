<?php
session_start();
// Si ya hay sesión, redirigir al inicio
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
        $error = "Debes completar todos los campos.";
    } else {
        // Sentencia preparada para evitar Inyección SQL
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE U_Rut = ? AND U_Activo = 1");
        $stmt->bind_param("s", $rut);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            // verificamos que la clave ingresada coincida con el hash
            if (password_verify($clave, $usuario['U_Password'])) {
                $_SESSION['usuario'] = [
                    'id'     => $usuario['U_Id'],
                    'rut'    => $usuario['U_Rut'],
                    'nombre' => $usuario['U_Nombre'],
                    'rol'    => $usuario['U_Rol']
                ];
                $_SESSION['rol'] = $usuario['U_Rol'];
                header("Location: index.php");
                exit();
            } else {
                $error = "La contraseña es incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado o cuenta desactivada.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CT-USM — Iniciar Sesión</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="login-body">
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <h1>Sistema de Postulaciones</h1>
            <p>Centro Tecnológico USM</p>
        </div>
        <?php if ($error): ?>
            <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <div class="campo">
                <label>RUT</label>
                <input type="text" name="rut" placeholder="Ej: 12.345.678-9" value="<?php echo htmlspecialchars($_POST['rut'] ?? ''); ?>" required>
            </div>
            <div class="campo">
                <label>Contraseña</label>
                <input type="password" name="clave" placeholder="Tu contraseña" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        <div class="login-footer">
            <p>¿No tienes cuenta? <a href="sing_up.php">Regístrate aquí</a></p>
        </div>
    </div>
</div>
</body>
</html>