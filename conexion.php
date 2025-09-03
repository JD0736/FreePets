<?php
$host = 'localhost';
$usuario = 'root';
$contrasena = '';
$base_datos = 'free pets';

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer el conjunto de caracteres a utf8
$conn->set_charset("utf8");
?>