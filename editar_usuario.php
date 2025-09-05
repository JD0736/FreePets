<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener ID del usuario a editar
$id_usuario = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_usuario) {
    header("Location: admin_usuarios.php");
    exit();
}

// Obtener datos del usuario
$sql = "SELECT id_usuario, nombre, correo, tipo_usuario, telefono, direccion 
        FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    header("Location: admin_usuarios.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Procesar formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $cambiar_password = isset($_POST['cambiar_password']) && $_POST['cambiar_password'] == '1';
    $password = $_POST['password'] ?? '';
    
    // Validar campos
    if (empty($nombre) || empty($correo) || empty($tipo_usuario)) {
        $mensaje = "Todos los campos obligatorios deben ser completados.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $correo, $id_usuario);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $mensaje = "El correo electrónico ya está registrado por otro usuario.";
            $tipo_mensaje = "error";
        } else {
            if ($cambiar_password && !empty($password)) {
                // Actualizar con nueva contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET nombre = ?, correo = ?, contraseña = ?, 
                              tipo_usuario = ?, telefono = ?, direccion = ? 
                              WHERE id_usuario = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssssi", $nombre, $correo, $hashed_password, 
                                        $tipo_usuario, $telefono, $direccion, $id_usuario);
            } else {
                // Actualizar sin cambiar contraseña
                $sql_update = "UPDATE usuarios SET nombre = ?, correo = ?, 
                              tipo_usuario = ?, telefono = ?, direccion = ? 
                              WHERE id_usuario = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sssssi", $nombre, $correo, $tipo_usuario, 
                                        $telefono, $direccion, $id_usuario);
            }
            
            if ($stmt_update->execute()) {
                $mensaje = "Usuario actualizado correctamente.";
                $tipo_mensaje = "success";
                
                // Actualizar datos locales
                $usuario['nombre'] = $nombre;
                $usuario['correo'] = $correo;
                $usuario['tipo_usuario'] = $tipo_usuario;
                $usuario['telefono'] = $telefono;
                $usuario['direccion'] = $direccion;
            } else {
                $mensaje = "Error al actualizar el usuario: " . $conn->error;
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
    <title>Editar Usuario - FreePets</title>
    <link rel="stylesheet" href="vista/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="user-form">
        <h2>Editar Usuario</h2>
        
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
                       value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="correo">Correo electrónico *</label>
                <input type="email" id="correo" name="correo" 
                       value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="cambiar_password" name="cambiar_password" value="1" 
                           onchange="togglePasswordField()"> Cambiar contraseña
                </label>
            </div>
            
            <div class="form-group" id="password_field" style="display: none;">
                <label for="password">Nueva contraseña</label>
                <input type="password" id="password" name="password">
            </div>
            
            <div class="form-group">
                <label for="tipo_usuario">Tipo de usuario *</label>
                <select id="tipo_usuario" name="tipo_usuario" required>
                    <option value="adoptante" <?php echo ($usuario['tipo_usuario'] == 'adoptante') ? 'selected' : ''; ?>>Adoptante</option>
                    <option value="admin" <?php echo ($usuario['tipo_usuario'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" 
                       value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($usuario['direccion']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <a href="admin_usuarios.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Actualizar Usuario</button>
            </div>
        </form>
    </div>
    
    <script>
        function togglePasswordField() {
            var checkbox = document.getElementById('cambiar_password');
            var passwordField = document.getElementById('password_field');
            
            if (checkbox.checked) {
                passwordField.style.display = 'block';
            } else {
                passwordField.style.display = 'none';
            }
        }
    </script>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>