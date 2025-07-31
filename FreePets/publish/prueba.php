<?php
require_once __DIR__ . '/../config/Free_Pets_DB.php';

$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo "✅ Conexión exitosa a la base de datos.";
} else {
    echo "❌ No se pudo conectar a la base de datos.";
}
?>

