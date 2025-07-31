<?php
session_start();
if ($_SESSION['tipo_usuario'] !== 'admin') {
    echo "Acceso denegado.";
    exit();
}
include '../conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "INSERT INTO mascotas (nombre, especie, raza, edad, sexo, descripcion, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisss", $_POST['nombre'], $_POST['especie'], $_POST['raza'],
                      $_POST['edad'], $_POST['sexo'], $_POST['descripcion'], $_POST['estado']);
    $stmt->execute();
    echo "Mascota registrada.";
}
?>
<form method="POST">
    <input name="nombre" required placeholder="Nombre">
    <input name="especie" placeholder="Especie">
    <input name="raza" placeholder="Raza">
    <input name="edad" type="number" placeholder="Edad">
    <select name="sexo"><option>macho</option><option>hembra</option></select>
    <textarea name="descripcion" placeholder="Descripción"></textarea>
    <select name="estado"><option>disponible</option><option>adoptado</option></select>
    <button type="submit">Guardar</button>
</form>