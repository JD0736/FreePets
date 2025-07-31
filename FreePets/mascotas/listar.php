<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}
include '../conexion.php';

$resultado = $conn->query("SELECT * FROM mascotas");
while ($fila = $resultado->fetch_assoc()) {
    echo "<p>{$fila['nombre']} - {$fila['especie']}</p>";
}
?>