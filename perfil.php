<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener datos actualizados del usuario
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Variables para mensajes
$mensaje = "";
$tipo_mensaje = "";

// Procesar actualización de perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_perfil'])) {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
    
    // Validar campos
    if (empty($nombre) || empty($email)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Por favor, ingrese un email válido.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el email ya existe (excepto para el usuario actual)
        $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $email, $usuario_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $mensaje = "El email ya está en uso por otro usuario.";
            $tipo_mensaje = "error";
        } else {
            // Actualizar datos del usuario
            $sql_update = "UPDATE usuarios SET nombre = ?, correo = ?, telefono = ?, direccion = ? WHERE id_usuario = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssi", $nombre, $email, $telefono, $direccion, $usuario_id);
            
            if ($stmt_update->execute()) {
                $mensaje = "Perfil actualizado correctamente.";
                $tipo_mensaje = "exito";
                
                // Actualizar datos en la sesión
                $_SESSION['nombre'] = $nombre;
                $_SESSION['email'] = $email;
                
                // Actualizar datos locales
                $usuario['nombre'] = $nombre;
                $usuario['correo'] = $email;
                $usuario['telefono'] = $telefono;
                $usuario['direccion'] = $direccion;
            } else {
                $mensaje = "Error al actualizar el perfil: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
}

// Procesar cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $nueva_password = $_POST['nueva_password'];
    $confirmar_password = $_POST['confirmar_password'];
    
    // Validar campos
    if (empty($password_actual) || empty($nueva_password) || empty($confirmar_password)) {
        $mensaje = "Por favor, complete todos los campos de contraseña.";
        $tipo_mensaje = "error";
    } elseif ($nueva_password !== $confirmar_password) {
        $mensaje = "Las contraseñas nuevas no coinciden.";
        $tipo_mensaje = "error";
    } elseif (strlen($nueva_password) < 6) {
        $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } else {
        // Verificar contraseña actual
        if (password_verify($password_actual, $usuario['contraseña'])) {
            // Hash de la nueva contraseña
            $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
            
            // Actualizar contraseña
            $sql_password = "UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?";
            $stmt_password = $conn->prepare($sql_password);
            $stmt_password->bind_param("si", $hashed_password, $usuario_id);
            
            if ($stmt_password->execute()) {
                $mensaje = "Contraseña actualizada correctamente.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "Error al actualizar la contraseña: " . $conn->error;
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "La contraseña actual es incorrecta.";
            $tipo_mensaje = "error";
        }
    }
}
?>
<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['actualizar_perfil'])) {
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
        $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
        
        if (empty($nombre) || empty($email)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Por favor, ingrese un email válido.";
            $tipo_mensaje = "error";
        } else {
            $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("si", $email, $usuario_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $mensaje = "El email ya está en uso por otro usuario.";
                $tipo_mensaje = "error";
            } else {
                $sql_update = "UPDATE usuarios SET nombre = ?, correo = ?, telefono = ?, direccion = ? WHERE id_usuario = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssi", $nombre, $email, $telefono, $direccion, $usuario_id);
                
                if ($stmt_update->execute()) {
                    $mensaje = "Perfil actualizado correctamente.";
                    $tipo_mensaje = "exito";
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['email'] = $email;
                    $usuario['nombre'] = $nombre;
                    $usuario['correo'] = $email;
                    $usuario['telefono'] = $telefono;
                    $usuario['direccion'] = $direccion;
                } else {
                    $mensaje = "Error al actualizar el perfil: " . $conn->error;
                    $tipo_mensaje = "error";
                }
            }
        }
    }

    if (isset($_POST['cambiar_password'])) {
        $password_actual = $_POST['password_actual'];
        $nueva_password = $_POST['nueva_password'];
        $confirmar_password = $_POST['confirmar_password'];
        
        if (empty($password_actual) || empty($nueva_password) || empty($confirmar_password)) {
            $mensaje = "Por favor, complete todos los campos de contraseña.";
            $tipo_mensaje = "error";
        } elseif ($nueva_password !== $confirmar_password) {
            $mensaje = "Las contraseñas nuevas no coinciden.";
            $tipo_mensaje = "error";
        } elseif (strlen($nueva_password) < 6) {
            $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
            $tipo_mensaje = "error";
        } else {
            if (password_verify($password_actual, $usuario['contraseña'])) {
                $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
                $sql_password = "UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?";
                $stmt_password = $conn->prepare($sql_password);
                $stmt_password->bind_param("si", $hashed_password, $usuario_id);
                
                if ($stmt_password->execute()) {
                    $mensaje = "Contraseña actualizada correctamente.";
                    $tipo_mensaje = "exito";
                } else {
                    $mensaje = "Error al actualizar la contraseña: " . $conn->error;
                    $tipo_mensaje = "error";
                }
            } else {
                $mensaje = "La contraseña actual es incorrecta.";
                $tipo_mensaje = "error";
            }
        }
    }
}

$page_title = "FreePets - Mi Perfil";
$additional_css = "vista/style-perfil.css";
include 'includes/header.php';
?>

<section class="content">
    <div class="perfil-container">
        <div class="perfil-header">
            <h2 class="title">Mi Perfil</h2>
            <p>Gestiona tu información personal y preferencias</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="perfil-grid">
            <div class="perfil-card">
                <h3><i class="fas fa-user"></i> Información Personal</h3>
                <div class="info-usuario">
                    <div class="info-item">
                        <strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong> <?php echo htmlspecialchars($usuario['correo']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?>
                    </div>
                    <div class="info-item">
                        <strong>Dirección:</strong> <?php echo htmlspecialchars($usuario['direccion'] ?? 'No especificada'); ?>
                    </div>
                    <div class="info-item">
                        <strong>Tipo de usuario:</strong> <?php echo htmlspecialchars($usuario['tipo_usuario']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Fecha de registro:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                    </div>
                </div>
            </div>

            <div class="perfil-card">
                <h3><i class="fas fa-edit"></i> Actualizar Perfil</h3>
                <form method="POST" action="">
                    <input type="hidden" name="actualizar_perfil" value="1">
                    <div class="form-group">
                        <label for="nombre">Nombre completo:*</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:*</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <textarea id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn-actualizar">Actualizar Información</button>
                </form>
            </div>

            <div class="perfil-card">
                <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                <form method="POST" action="">
                    <input type="hidden" name="cambiar_password" value="1">
                    <div class="form-group">
                        <label for="password_actual">Contraseña actual:*</label>
                        <input type="password" id="password_actual" name="password_actual" required>
                    </div>
                    <div class="form-group">
                        <label for="nueva_password">Nueva contraseña:*</label>
                        <input type="password" id="nueva_password" name="nueva_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirmar_password">Confirmar nueva contraseña:*</label>
                        <input type="password" id="confirmar_password" name="confirmar_password" required minlength="6">
                    </div>
                    <button type="submit" class="btn-actualizar">Cambiar Contraseña</button>
                </form>
            </div>

            <div class="perfil-card">
                <h3><i class="fas fa-cog"></i> Acciones Rápidas</h3>
                <div class="acciones-rapidas">
                    <a href="adopcion.php" class="accion-btn">
                        <i class="fas fa-paw"></i> Adoptar Mascota
                    </a>
                    <a href="donacion.php" class="accion-btn">
                        <i class="fas fa-hand-holding-heart"></i> Hacer Donación
                    </a>
                    <a href="reportes_mascotas_situaciones.php" class="accion-btn">
                        <i class="fas fa-exclamation-circle"></i> Reportar Situación
                    </a>
                    <?php if ($usuario['tipo_usuario'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="accion-btn admin">
                        <i class="fas fa-cog"></i> Panel de Administración
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - FreePets</title>
    <link rel="stylesheet" href="vista/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>

    <header class="header">
        <a href="index.php" class="logo">
            <img src="multimedia/Imagen1.png" alt="Logo Free Pets">
        </a>

        <nav class="navbar">
            <a href="index.php#home">Inicio</a>
            <div class="dropdown">
                <a href="index.php#about" class="dropbtn">Nosotros</a>
                <div class="dropdown-content">
                    <a href="mision_y_vision.php">Misión y Visión</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="index.php#services" class="dropbtn">Servicios</a>
                <div class="dropdown-content">
                    <a href="adopcion.php">Centro de adopción</a>
                    <a href="eventos.php">Eventos solidarios</a>
                    <a href="reportes_mascotas_situaciones.php">Reportar mascotas</a>
                    <a href="donacion.php">Donar</a>
                </div>
            </div>
            <a href="index.php#contact">Contacto</a>
            <a href="perfil.php" class="active">Mi Perfil</a>
            <a href="logout.php">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['nombre']); ?>)</a>
        </nav>

        <div class="icons">
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
    </header>

    <section class="content">
        <div class="perfil-container">
            <div class="perfil-header">
                <h2 class="title">Mi Perfil</h2>
                <p>Gestiona tu información personal y preferencias</p>
            </div>

            <!-- Mostrar mensajes -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert <?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                    <span class="close-btn" onclick="this.parentElement.style.display='none'">&times;</span>
                </div>
            <?php endif; ?>

            <div class="perfil-grid">
                <!-- Información del usuario -->
                <div class="perfil-card">
                    <h3><i class="fas fa-user"></i> Información Personal</h3>
                    
                    <div class="info-usuario">
                        <div class="info-item">
                            <strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong> <?php echo htmlspecialchars($usuario['correo']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?>
                        </div>
                        <div class="info-item">
                            <strong>Dirección:</strong> <?php echo htmlspecialchars($usuario['direccion'] ?? 'No especificada'); ?>
                        </div>
                        <div class="info-item">
                            <strong>Tipo de usuario:</strong> <?php echo htmlspecialchars($usuario['tipo_usuario']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Fecha de registro:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Actualizar perfil -->
                <div class="perfil-card">
                    <h3><i class="fas fa-edit"></i> Actualizar Perfil</h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="actualizar_perfil" value="1">
                        
                        <div class="form-group">
                            <label for="nombre">Nombre completo:*</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:*</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección:</label>
                            <textarea id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-actualizar">Actualizar Información</button>
                    </form>
                </div>

                <!-- Cambiar contraseña -->
                <div class="perfil-card">
                    <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="cambiar_password" value="1">
                        
                        <div class="form-group">
                            <label for="password_actual">Contraseña actual:*</label>
                            <input type="password" id="password_actual" name="password_actual" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nueva_password">Nueva contraseña:*</label>
                            <input type="password" id="nueva_password" name="nueva_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmar_password">Confirmar nueva contraseña:*</label>
                            <input type="password" id="confirmar_password" name="confirmar_password" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn-actualizar">Cambiar Contraseña</button>
                    </form>
                </div>

                <!-- Acciones rápidas -->
                <div class="perfil-card">
                    <h3><i class="fas fa-cog"></i> Acciones Rápidas</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <a href="adopcion.php" class="accion-btn">
                            <i class="fas fa-paw"></i> Adoptar Mascota
                        </a>
                        
                        <a href="donacion.php" class="accion-btn">
                            <i class="fas fa-hand-holding-heart"></i> Hacer Donación
                        </a>
                        
                        <a href="reportes_mascotas_situaciones.php" class="accion-btn">
                            <i class="fas fa-exclamation-circle"></i> Reportar Situación
                        </a>
                        
                        <?php if ($usuario['tipo_usuario'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="accion-btn admin">
                            <i class="fas fa-cog"></i> Panel de Administración
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <h6>© Free Pets 2025 - Todos los derechos reservados</h6>
    </footer>

    <script src="js/script.js"></script>

</body>
</html>