<?php
session_start();
include 'conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Variables para mensajes
$mensaje = "";
$tipo_mensaje = "";

// Procesar acciones CRUD
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Crear usuario
    if (isset($_POST['crear_usuario'])) {
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $tipo_usuario = $_POST['tipo_usuario'];
        $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
        $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
        
        // Validar campos
        if (empty($nombre) || empty($correo) || empty($password) || empty($tipo_usuario)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Por favor, ingrese un email válido.";
            $tipo_mensaje = "error";
        } else {
            // Verificar si el correo ya existe
            $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $correo);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $mensaje = "El correo electrónico ya está en uso.";
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
                    $tipo_mensaje = "exito";
                } else {
                    $mensaje = "Error al crear el usuario: " . $conn->error;
                    $tipo_mensaje = "error";
                }
            }
        }
    }
    
    // Editar usuario
    if (isset($_POST['editar_usuario'])) {
        $id_usuario = $_POST['id_usuario'];
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $tipo_usuario = $_POST['tipo_usuario'];
        $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
        $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
        
        // Validar campos
        if (empty($nombre) || empty($correo) || empty($tipo_usuario)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Por favor, ingrese un email válido.";
            $tipo_mensaje = "error";
        } else {
            // Verificar si el correo ya existe (excluyendo el usuario actual)
            $sql_check = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("si", $correo, $id_usuario);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $mensaje = "El correo electrónico ya está en uso por otro usuario.";
                $tipo_mensaje = "error";
            } else {
                // Actualizar usuario
                $sql_update = "UPDATE usuarios SET nombre = ?, correo = ?, tipo_usuario = ?, telefono = ?, direccion = ? 
                               WHERE id_usuario = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sssssi", $nombre, $correo, $tipo_usuario, $telefono, $direccion, $id_usuario);
                
                if ($stmt_update->execute()) {
                    $mensaje = "Usuario actualizado exitosamente.";
                    $tipo_mensaje = "exito";
                } else {
                    $mensaje = "Error al actualizar el usuario: " . $conn->error;
                    $tipo_mensaje = "error";
                }
            }
        }
    }
    
    // Eliminar usuario
    if (isset($_POST['eliminar_usuario'])) {
        $id_usuario = $_POST['id_usuario'];
        
        // No permitir eliminarse a sí mismo
        if ($id_usuario == $_SESSION['usuario_id']) {
            $mensaje = "No puede eliminarse a sí mismo.";
            $tipo_mensaje = "error";
        } else {
            $sql_delete = "DELETE FROM usuarios WHERE id_usuario = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $id_usuario);
            
            if ($stmt_delete->execute()) {
                $mensaje = "Usuario eliminado exitosamente.";
                $tipo_mensaje = "exito";
            } else {
                $mensaje = "Error al eliminar el usuario: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
}

// Obtener lista de usuarios
$sql_usuarios = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
$resultado_usuarios = $conn->query($sql_usuarios);
$usuarios = [];
if ($resultado_usuarios->num_rows > 0) {
    while($fila = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}

$page_title = "FreePets - Administrar Usuarios";
include 'includes/header.php';
?>

<section class="content admin-usuarios">
    <h2 class="title">Administrar Usuarios</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <!-- Formulario para crear/editar usuario -->
    <div class="admin-form">
        <h3 id="form-titulo">Crear Nuevo Usuario</h3>
        <form method="POST" action="" id="usuario-form">
            <input type="hidden" name="id_usuario" id="id_usuario" value="">
            <input type="hidden" name="accion" id="accion" value="crear">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre completo:*</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo electrónico:*</label>
                    <input type="email" id="correo" name="correo" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password" id="label-password">Contraseña:*</label>
                    <input type="password" id="password" name="password" required>
                    <small id="password-help">Dejar en blanco si no desea cambiar la contraseña (solo edición)</small>
                </div>
                
                <div class="form-group">
                    <label for="tipo_usuario">Tipo de usuario:*</label>
                    <select id="tipo_usuario" name="tipo_usuario" required>
                        <option value="adoptante">Adoptante</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono">
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <textarea id="direccion" name="direccion" rows="2"></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="crear_usuario" id="btn-submit">Crear Usuario</button>
                <button type="button" id="btn-cancelar" style="display: none;">Cancelar</button>
            </div>
        </form>
    </div>
    
    <!-- Lista de usuarios -->
    <div class="admin-list">
        <h3>Lista de Usuarios</h3>
        
        <?php if (count($usuarios) > 0): ?>
            <div class="table-responsive">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Tipo</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id_usuario']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['tipo_usuario']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                <td>
                                    <button class="btn-edit" onclick="editarUsuario(<?php echo $usuario['id_usuario']; ?>)">Editar</button>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                                        <button type="submit" name="eliminar_usuario" class="btn-delete" 
                                                onclick="return confirm('¿Está seguro de que desea eliminar este usuario?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay usuarios registrados.</p>
        <?php endif; ?>
    </div>
</section>

<script>
// Función para editar usuario
function editarUsuario(id) {
    // Obtener datos del usuario mediante AJAX
    fetch('obtener_usuario.php?id=' + id)
        .then(response => response.json())
        .then(usuario => {
            if (usuario) {
                // Llenar el formulario con los datos del usuario
                document.getElementById('id_usuario').value = usuario.id_usuario;
                document.getElementById('nombre').value = usuario.nombre;
                document.getElementById('correo').value = usuario.correo;
                document.getElementById('tipo_usuario').value = usuario.tipo_usuario;
                document.getElementById('telefono').value = usuario.telefono || '';
                document.getElementById('direccion').value = usuario.direccion || '';
                
                // Cambiar el formulario a modo edición
                document.getElementById('form-titulo').textContent = 'Editar Usuario';
                document.getElementById('accion').value = 'editar';
                document.getElementById('label-password').textContent = 'Contraseña:';
                document.getElementById('password').required = false;
                document.getElementById('password-help').style.display = 'block';
                document.getElementById('btn-submit').textContent = 'Actualizar Usuario';
                document.getElementById('btn-submit').name = 'editar_usuario';
                document.getElementById('btn-cancelar').style.display = 'inline-block';
                
                // Desplazarse al formulario
                document.querySelector('.admin-form').scrollIntoView({ behavior: 'smooth' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del usuario');
        });
}

// Cancelar edición
document.getElementById('btn-cancelar').addEventListener('click', function() {
    resetForm();
});

// Resetear formulario
function resetForm() {
    document.getElementById('usuario-form').reset();
    document.getElementById('id_usuario').value = '';
    document.getElementById('form-titulo').textContent = 'Crear Nuevo Usuario';
    document.getElementById('accion').value = 'crear';
    document.getElementById('label-password').textContent = 'Contraseña:*';
    document.getElementById('password').required = true;
    document.getElementById('password-help').style.display = 'none';
    document.getElementById('btn-submit').textContent = 'Crear Usuario';
    document.getElementById('btn-submit').name = 'crear_usuario';
    document.getElementById('btn-cancelar').style.display = 'none';
}
</script>

<?php
include 'includes/footer.php';
?>