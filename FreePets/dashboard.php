<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
echo "Bienvenido " . $_SESSION['nombre'] . " (" . $_SESSION['tipo_usuario'] . ")";
if ($_SESSION['tipo_usuario'] === 'admin') {
    echo "<p><a href='mascotas/crear.php'>Registrar mascota</a></p>";
    echo "<p><a href='mascotas/listar.php'>Ver mascotas</a></p>";
}
?>