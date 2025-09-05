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

// Procesar formulario de creación
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    
    // Validar campos
    if (empty($nombre) || empty($correo) || empty($password) || empty($tipo_usuario)) {
        $mensaje = "Todos los campos obligatorios deben ser completados.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el correo ya existe
        $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $mensaje = "El correo electrónico ya está registrado.";
            $tipo_mensaje = "error";
        } else {
            // Hash de la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $sql_insert = "INSERT INTO usuarios (nombre, correo, contraseña, tipo_usuario, telefono, direccion) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssss", $nombre, $correo, $hashed_password, $tipo_usuario, $telefono, $direccion);
            
            if ($stmt_insert->execute()) {
                $mensaje = "Usuario creado exitosamente.";
                $tipo_mensaje = "success";
                
                // Limpiar formulario
                $_POST = array();
            } else {
                $mensaje = "Error al crear el usuario: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - FreePets</title>
    <link rel="stylesheet" href="vista/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="user-form">
        <h2>Crear Nuevo Usuario</h2>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
                <span class="close-btn" onclick="this.parentElement.style.display='none'">&times;</span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" 
                       value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="correo">Correo electrónico *</label>
                <input type="email" id="correo" name="correo" 
                       value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="tipo_usuario">Tipo de usuario *</label>
                <select id="tipo_usuario" name="tipo_usuario" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="adoptante" <?php echo (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] == 'adoptante') ? 'selected' : ''; ?>>Adoptante</option>
                    <option value="admin" <?php echo (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" 
                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea id="direccion" name="direccion" rows="3"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <a href="admin_usuarios.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Crear Usuario</button>
            </div>
        </form>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>