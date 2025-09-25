<?php
session_start();
include 'conexion.php';

$mensaje = "";
$tipo_mensaje = ""; // éxito o error

// Verificar si hay una sesión de administrador activa
$es_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';

// Permitir registro de admin sin ser admin (solo para desarrollo)
$modo_desarrollo = true; // Cambiar a false en producción

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si es login o registro
    if (isset($_POST['login'])) {
        // Proceso de login
        $correo = $_POST['correo'];
        $contraseña = $_POST['password'];
        
        $sql = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($contraseña, $usuario['contraseña'])) {
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
                
                // Redirigir según el tipo de usuario
                if ($usuario['tipo_usuario'] === 'admin') {
                    header("Location: admin_usuarios.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $mensaje = "Contraseña incorrecta.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "Usuario no encontrado.";
            $tipo_mensaje = "error";
        }
    } 
    elseif (isset($_POST['registro'])) {
        // Proceso de registro
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $tipo_usuario = $_POST['tipo_usuario'];
        
        // Verificar si el usuario ya existe
        $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $mensaje = "El usuario ya existe.";
            $tipo_mensaje = "error";
        } else {
            // Si se intenta registrar como admin, verificar si hay una sesión de admin o está en modo desarrollo
            if ($tipo_usuario === 'admin' && !$es_admin && !$modo_desarrollo) {
                $mensaje = "Solo los administradores pueden crear cuentas de administrador.";
                $tipo_mensaje = "error";
                // Forzar el tipo de usuario a adoptante
                $tipo_usuario = 'adoptante';
            }
            
            // Hash de la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $sql_insert = "INSERT INTO usuarios (nombre, correo, contraseña, tipo_usuario) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nombre, $correo, $hashed_password, $tipo_usuario);
            
            if ($stmt_insert->execute()) {
                $mensaje = "Usuario creado exitosamente.";
                $tipo_mensaje = "exito";
                
                // Iniciar sesión automáticamente después del registro
                $_SESSION['usuario_id'] = $stmt_insert->insert_id;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['tipo_usuario'] = $tipo_usuario;
                
                // Redirigir según el tipo de usuario
                if ($tipo_usuario === 'admin') {
                    header("Location: admin_usuarios.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                $mensaje = "Error al crear el usuario: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
}

// Determinar qué pestaña mostrar por defecto
$mostrar_login = true;
if (isset($_GET['admin_registro']) && ($es_admin || $modo_desarrollo)) {
    $mostrar_login = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login & Registro - FreePets</title>
  <link rel="stylesheet" href="vista/forms.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
</head>
<body>

  <div class="form-container">
    <div class="tabs">
      <div class="tab <?php echo $mostrar_login ? 'active' : ''; ?>" data-target="login">Login</div>
      <div class="tab <?php echo !$mostrar_login ? 'active' : ''; ?>" data-target="registro">Registro</div>
    </div>

    <!-- Login -->
    <div id="login" class="form-content <?php echo $mostrar_login ? 'active' : ''; ?>">
      <h2>Iniciar Sesión</h2>
      <form method="POST" action="">
        <input type="hidden" name="login" value="1">
        <div class="input-group">
          <input type="email" name="correo" placeholder="Correo electrónico" required>
          <i class="fa fa-envelope"></i>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Contraseña" required>
          <i class="fa fa-lock"></i>
        </div>
        <button type="submit" class="btn-submit">Entrar</button>
      </form>
      <div class="form-links">
        <a href="#">¿Olvidaste tu contraseña?</a>
      </div>
      
      <?php if ($es_admin || $modo_desarrollo): ?>
        <a href="?admin_registro=1" class="admin-link">Registrar nuevo administrador</a>
      <?php endif; ?>
    </div>

    <!-- Registro -->
    <div id="registro" class="form-content <?php echo !$mostrar_login ? 'active' : ''; ?>">
      <h2>Registro</h2>
      <form method="POST" action="">
        <input type="hidden" name="registro" value="1">
        <div class="input-group">
          <input type="text" name="nombre" placeholder="Nombre completo" required>
          <i class="fa fa-user"></i>
        </div>
        <div class="input-group">
          <input type="email" name="correo" placeholder="Correo electrónico" required>
          <i class="fa fa-envelope"></i>
        </div>
        <div class="input-group">
          <input type="password" name="password" placeholder="Contraseña" required>
          <i class="fa fa-lock"></i>
        </div>

        <div class="input-group">
          <select name="tipo_usuario" id="tipo_usuario" required>
            <option value="">Selecciona un rol</option>
            <option value="adoptante">Adoptante</option>
            <?php if ($es_admin || $modo_desarrollo): ?>
              <option value="admin">Administrador</option>
            <?php endif; ?>
          </select>
          <i class="fa fa-users"></i>
        </div>

        <button type="submit" class="btn-submit">Registrarse</button>
      </form>
      
      <?php if ($es_admin): ?>
        <a href="admin_usuarios.php" class="admin-link">Volver al panel de administración</a>
      <?php else: ?>
        <a href="?" class="admin-link">Volver al login</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Popup para mensajes -->
  <div class="overlay" id="overlay"></div>
  
  <div class="popup <?php echo $tipo_mensaje; ?>" id="popup">
    <h3><?php echo $tipo_mensaje == 'exito' ? '¡Éxito!' : 'Error'; ?></h3>
    <p><?php echo $mensaje; ?></p>
    <button class="popup-btn <?php echo $tipo_mensaje; ?>" onclick="cerrarPopup()">Aceptar</button>
  </div>
<script src="js/script.js"></script>
</body>
</html>