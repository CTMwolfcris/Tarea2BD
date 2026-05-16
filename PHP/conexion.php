<?php
// Este archivo tiene  como unico proposito realizar la conexion a la base de datos para la busqueda de los datos que tenemos almacenados en esta, en caso de que falle arrojamos un error 
$server = "127.0.0.1";
$user   = "root";
$pass   = "";
$db     = "tarea2_bd";
$conexion = new mysqli($server, $user, $pass, $db, 3306);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
//hgo
$conexion->set_charset("utf8mb4");
?>