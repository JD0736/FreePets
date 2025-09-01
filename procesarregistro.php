<?php
require_once __DIR__ . "/database.php";


$db = new Database();
$conn = $db->connect();


if ($_SERVER["REQUEST_METHOD"]=="POST")
{
   $nombre = $_POST ["nombre"];
   $correo = $_POST ["correo"];
   $numero = $_POST ["numero"];
   $pass = password_hash('123456', PASSWORD_BCRYPT);
   $tipodedocumento = $_POST ["tipodedocumento"];
   $documento = $_POST ["documento"];
   $rol = 'estandar';

   $sql = "INSERT INTO usuarios (nombre, correo, contraseña, rol)
        VALUES (:nombre, :correo, :pass, :rol)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':nombre', $nombre);
$stmt->bindParam(':correo', $correo);
$stmt->bindParam(':pass', $pass);
$stmt->bindParam(':rol', $rol);


if ($stmt->execute()) {
    echo "✅ Usuario creado correctamente.";
} else {
    echo "❌ Error al crear usuario.";
}
   
}
?>

