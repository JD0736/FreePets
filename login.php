<?php
session_start();
include 'conexion.php';

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
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
                $_SESSION['email'] = $usuario['correo'];
                
                header("Location: index.php");
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
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $tipo_usuario = $_POST['tipo_usuario'];
        
        $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $mensaje = "El usuario ya existe.";
            $tipo_mensaje = "error";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql_insert = "INSERT INTO usuarios (nombre, correo, contraseña, tipo_usuario) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nombre, $correo, $hashed_password, $tipo_usuario);
            
            if ($stmt_insert->execute()) {
                $mensaje = "Usuario creado exitosamente. Ahora puedes iniciar sesión.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "Error al crear el usuario: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
}

$page_title = "FreePets - Login";
$additional_css = "vista/forms.css";
include 'includes/header.php';
?>

<div class="form-container">
    <div class="tabs">
        <div class="tab active" data-target="login">Login</div>
        <div class="tab" data-target="registro">Registro</div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <div id="login" class="form-content active">
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
    </div>

    <div id="registro" class="form-content">
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
                <select name="tipo_usuario" required>
                    <option value="">Selecciona un rol</option>
                    <option value="adoptante">Adoptante</option>
                    <option value="admin">Administrador</option>
                </select>
                <i class="fa fa-users"></i>
            </div>
            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.form-content').forEach(c => c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.target).classList.add('active');
    });
});
</script>

<?php
include 'includes/footer.php';
?>