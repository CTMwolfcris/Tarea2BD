<?php
session_start();
include("conexion.php");
$error = "";
$exito = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rut    = trim($_POST['rut'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $clave  = trim($_POST['clave'] ?? '');
    if (empty($rut) || empty($nombre) || empty($email) || empty($clave)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar si el RUT ya existe para no duplicar
        $check = $conexion->prepare("SELECT U_Id FROM usuarios WHERE U_Rut = ?");
        $check->bind_param("s", $rut);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = "Este RUT ya se encuentra registrado.";
        } else {
            // encriptamos la clave antes de guardarla
            $passwordHash = password_hash($clave, PASSWORD_BCRYPT);
            
            $stmt = $conexion->prepare("INSERT INTO usuarios (U_Rut, U_Nombre, U_Email, U_Password, U_Rol, U_Activo) VALUES (?, ?, ?, ?, 'postulante', 1)");
            $stmt->bind_param("ssss", $rut, $nombre, $email, $passwordHash);
            
            if ($stmt->execute()) {
                $exito = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            } else {
                $error = "Hubo un error al crear la cuenta.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CT-USM — Registro</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="login-body">
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <h1>Crear Cuenta</h1>
            <p>Postulante CT-USM</p>
        </div>
        <?php if ($error): ?>
            <div class="alerta alerta-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div class="alerta alerta-ok"><?php echo htmlspecialchars($exito); ?></div>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <div class="campo">
                <label>RUT</label>
                <input type="text" name="rut" placeholder="12.345.678-k" required>
            </div>
            <div class="campo">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" placeholder="Ej: Juan Pérez" required>
            </div>
            <div class="campo">
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="ejemplo@correo.cl" required>
            </div>
            <div class="campo">
                <label>Contraseña</label>
                <input type="password" name="clave" placeholder="Mínimo 6 caracteres" required>
            </div>
            <button type="submit" class="btn-login">Registrarse</button>
        </form>
        <div class="login-footer">
            <p><a href="login.php">← Volver al login</a></p>
        </div>
    </div>
</div>
</body>
</html>