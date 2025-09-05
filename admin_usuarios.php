<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Procesar eliminación de usuario
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    
    // No permitir que el administrador se elimine a sí mismo
    if ($id_eliminar == $_SESSION['usuario_id']) {
        $mensaje = "No puedes eliminar tu propio usuario.";
        $tipo_mensaje = "error";
    } else {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_eliminar);
        
        if ($stmt->execute()) {
            $mensaje = "Usuario eliminado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar el usuario: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
}

// Obtener lista de usuarios
$sql = "SELECT id_usuario, nombre, correo, tipo_usuario, fecha_registro, telefono, direccion 
        FROM usuarios ORDER BY fecha_registro DESC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Usuarios - FreePets</title>
    <link rel="stylesheet" href="vista/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="crud-container">
        <div class="crud-header">
            <h1 class="crud-title">Administración de Usuarios</h1>
            <a href="crear_usuario.php" class="btn-add">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </a>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <span class="close-btn" onclick="this.parentElement.style.display='none'">&times;</span>
            </div>
        <?php endif; ?>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Tipo</th>
                    <th>Fecha Registro</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado->num_rows > 0): ?>
                    <?php while($usuario = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $usuario['id_usuario']; ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                            <td><?php echo $usuario['tipo_usuario']; ?></td>
                            <td><?php echo $usuario['fecha_registro']; ?></td>
                            <td><?php echo $usuario['telefono'] ? htmlspecialchars($usuario['telefono']) : 'N/A'; ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="admin_usuarios.php?eliminar=<?php echo $usuario['id_usuario']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>