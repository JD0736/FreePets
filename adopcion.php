<?php
session_start();
include 'conexion.php';

// Verificar si el usuario es administrador
$es_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';

// Procesar formularios de administración
$mensaje = "";
$tipo_mensaje = "";

// Crear mascota (para administradores)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_mascota']) && $es_admin) {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_STRING);
    $raza = filter_input(INPUT_POST, 'raza', FILTER_SANITIZE_STRING);
    $edad = $_POST['edad'];
    $sexo = $_POST['sexo'];
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $estado = $_POST['estado'];
    
    // Procesar imagen
    $imagen_url = 'multimedia/default-pet.jpg';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "uploads/mascotas/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imageFileType = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            $imagen_url = $target_file;
        }
    }
    
    $sql = "INSERT INTO mascotas (nombre, especie, raza, edad, sexo, descripcion, imagen_url, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdssss", $nombre, $especie, $raza, $edad, $sexo, $descripcion, $imagen_url, $estado);
    
    if ($stmt->execute()) {
        $mensaje = "Mascota creada correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al crear la mascota: " . $conn->error;
        $tipo_mensaje = "error";
    }
}

// Procesar registro de mascota (para usuarios normales)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_mascota']) && isset($_SESSION['usuario_id'])) {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_STRING);
    $raza = filter_input(INPUT_POST, 'raza', FILTER_SANITIZE_STRING);
    $edad = $_POST['edad'];
    $sexo = $_POST['sexo'];
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $estado = 'disponible'; // Por defecto para mascotas registradas por usuarios
    
    // Procesar imagen
    $imagen_url = 'multimedia/default-pet.jpg';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "uploads/mascotas/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imageFileType = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            $imagen_url = $target_file;
        }
    }
    
    $sql = "INSERT INTO mascotas (nombre, especie, raza, edad, sexo, descripcion, imagen_url, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdssss", $nombre, $especie, $raza, $edad, $sexo, $descripcion, $imagen_url, $estado);
    
    if ($stmt->execute()) {
        $mensaje = "Mascota registrada correctamente. Será revisada por nuestro equipo.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al registrar la mascota: " . $conn->error;
        $tipo_mensaje = "error";
    }
}

// Editar mascota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_mascota']) && $es_admin) {
    $id_mascota = $_POST['id_mascota'];
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_STRING);
    $raza = filter_input(INPUT_POST, 'raza', FILTER_SANITIZE_STRING);
    $edad = $_POST['edad'];
    $sexo = $_POST['sexo'];
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $estado = $_POST['estado'];
    
    // Procesar imagen si se subió una nueva
    $imagen_url = $_POST['imagen_actual'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $target_dir = "uploads/mascotas/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $imageFileType = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            // Eliminar imagen anterior si no es la default
            if ($imagen_url != 'multimedia/default-pet.jpg' && file_exists($imagen_url)) {
                unlink($imagen_url);
            }
            $imagen_url = $target_file;
        }
    }
    
    $sql = "UPDATE mascotas SET nombre=?, especie=?, raza=?, edad=?, sexo=?, descripcion=?, imagen_url=?, estado=? 
            WHERE id_mascota=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdssssi", $nombre, $especie, $raza, $edad, $sexo, $descripcion, $imagen_url, $estado, $id_mascota);
    
    if ($stmt->execute()) {
        $mensaje = "Mascota actualizada correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al actualizar la mascota: " . $conn->error;
        $tipo_mensaje = "error";
    }
}

// Eliminar mascota
if (isset($_GET['eliminar_mascota']) && $es_admin) {
    $id_mascota = $_GET['eliminar_mascota'];
    
    // Obtener información de la imagen para eliminarla
    $sql_img = "SELECT imagen_url FROM mascotas WHERE id_mascota = ?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("i", $id_mascota);
    $stmt_img->execute();
    $resultado_img = $stmt_img->get_result();
    $mascota_img = $resultado_img->fetch_assoc();
    
    // Eliminar la mascota
    $sql = "DELETE FROM mascotas WHERE id_mascota = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_mascota);
    
    if ($stmt->execute()) {
        // Eliminar imagen si no es la default
        if ($mascota_img['imagen_url'] != 'multimedia/default-pet.jpg' && file_exists($mascota_img['imagen_url'])) {
            unlink($mascota_img['imagen_url']);
        }
        $mensaje = "Mascota eliminada correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al eliminar la mascota: " . $conn->error;
        $tipo_mensaje = "error";
    }
}

// Obtener mascotas disponibles
$sql = "SELECT * FROM mascotas ORDER BY fecha_ingreso DESC";
$resultado = $conn->query($sql);
$mascotas = [];
if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $mascotas[] = $fila;
    }
}

// Obtener datos de mascota para edición
$mascota_editar = null;
if (isset($_GET['editar_mascota']) && $es_admin) {
    $id_mascota = $_GET['editar_mascota'];
    $sql_editar = "SELECT * FROM mascotas WHERE id_mascota = ?";
    $stmt_editar = $conn->prepare($sql_editar);
    $stmt_editar->bind_param("i", $id_mascota);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $mascota_editar = $resultado_editar->fetch_assoc();
}

$page_title = "FreePets - Centro de Adopción";
include 'includes/header.php';
?>

<section class="content adopcion">
    <h2 class="title">Centro de Adopción</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="adopcion-opciones">
        <button onclick="mostrarSeccion('ver')" class="btn-adopcion active">Adoptar Mascota</button>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <button onclick="mostrarSeccion('registro')" class="btn-adopcion">Poner en Adopción</button>
        <?php else: ?>
            <button onclick="alert('Debe iniciar sesión para registrar mascotas')" class="btn-adopcion">Poner en Adopción</button>
        <?php endif; ?>
        
        <?php if ($es_admin): ?>
            <button onclick="mostrarSeccion('admin')" class="btn-adopcion admin">Administrar Mascotas</button>
        <?php endif; ?>
    </div>

    <!-- Ver mascotas disponibles -->
    <div id="seccion-ver" class="seccion">
        <h3>Mascotas Disponibles para Adopción</h3>
        
        <?php if (count($mascotas) > 0): ?>
            <div class="mascotas-grid">
                <?php foreach ($mascotas as $mascota): ?>
                    <div class="mascota-card">
                        <img src="<?php echo $mascota['imagen_url'] ?: 'multimedia/default-pet.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($mascota['nombre']); ?>">
                        <h3><?php echo htmlspecialchars($mascota['nombre']); ?></h3>
                        <p>
                            <?php echo ucfirst($mascota['sexo']); ?>, 
                            <?php echo $mascota['edad']; ?> años<br>
                            <?php echo htmlspecialchars($mascota['especie']); ?>
                            <?php echo $mascota['raza'] ? ' - ' . htmlspecialchars($mascota['raza']) : ''; ?>
                        </p>
                        <p class="descripcion-mascota"><?php echo htmlspecialchars($mascota['descripcion']); ?></p>
                        <p class="estado-mascota"><strong>Estado:</strong> <?php echo ucfirst($mascota['estado']); ?></p>
                        
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <button class="btn" onclick="solicitarAdopcion(<?php echo $mascota['id_mascota']; ?>)">
                                Solicitar Adopción
                            </button>
                        <?php else: ?>
                            <button class="btn" onclick="alert('Debe iniciar sesión para adoptar')">
                                Iniciar Sesión para Adoptar
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($es_admin): ?>
                            <div class="admin-actions">
                                <button class="admin-btn" onclick="editarMascota(<?php echo $mascota['id_mascota']; ?>)">Editar</button>
                                <button class="admin-btn delete" onclick="eliminarMascota(<?php echo $mascota['id_mascota']; ?>)">Eliminar</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-mascotas">
                <h4>No hay mascotas disponibles en este momento</h4>
                <p>Pronto tendremos nuevos compañeros esperando por un hogar.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Formulario de registro de mascota -->
    <div id="seccion-registro" class="seccion" style="display: none;">
        <h3>Registrar Mascota para Adopción</h3>
        
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <form class="formulario-registro" action="adopcion.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="registrar_mascota" value="1">
                
                <div class="form-group">
                    <label for="nombre">Nombre:*</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="especie">Especie:*</label>
                    <select id="especie" name="especie" required>
                        <option value="">Seleccionar especie</option>
                        <option value="Perro">Perro</option>
                        <option value="Gato">Gato</option>
                        <option value="Conejo">Conejo</option>
                        <option value="Ave">Ave</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="raza">Raza:</label>
                    <input type="text" id="raza" name="raza">
                </div>
                
                <div class="form-group">
                    <label for="edad">Edad (años):*</label>
                    <input type="number" id="edad" name="edad" min="0" max="30" required>
                </div>
                
                <div class="form-group">
                    <label for="sexo">Sexo:*</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Seleccionar sexo</option>
                        <option value="macho">Macho</option>
                        <option value="hembra">Hembra</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:*</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required 
                              placeholder="Describa el temperamento, salud, necesidades especiales, etc."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Foto de la mascota:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                
                <button type="submit" class="btn">Registrar Mascota</button>
            </form>
        <?php else: ?>
            <div class="alert error">
                Debe <a href="login.php">iniciar sesión</a> para registrar mascotas en adopción.
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel de administración de mascotas -->
    <?php if ($es_admin): ?>
    <div id="seccion-admin" class="seccion" style="display: none;">
        <h3>Administrar Mascotas</h3>
        
        <div class="admin-panel">
            <button class="btn" onclick="mostrarFormularioMascota()">Agregar Nueva Mascota</button>
            
            <!-- Formulario para crear/editar mascota -->
            <div id="formulario-mascota" style="display: <?php echo $mascota_editar ? 'block' : 'none'; ?>; margin-top: 20px;">
                <h4><?php echo $mascota_editar ? 'Editar' : 'Crear'; ?> Mascota</h4>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="id_mascota" value="<?php echo $mascota_editar ? $mascota_editar['id_mascota'] : ''; ?>">
                    <input type="hidden" name="imagen_actual" value="<?php echo $mascota_editar ? $mascota_editar['imagen_url'] : ''; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_nombre">Nombre:*</label>
                            <input type="text" id="admin_nombre" name="nombre" value="<?php echo $mascota_editar ? $mascota_editar['nombre'] : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_especie">Especie:*</label>
                            <select id="admin_especie" name="especie" required>
                                <option value="">Seleccionar especie</option>
                                <option value="Perro" <?php echo ($mascota_editar && $mascota_editar['especie'] == 'Perro') ? 'selected' : ''; ?>>Perro</option>
                                <option value="Gato" <?php echo ($mascota_editar && $mascota_editar['especie'] == 'Gato') ? 'selected' : ''; ?>>Gato</option>
                                <option value="Conejo" <?php echo ($mascota_editar && $mascota_editar['especie'] == 'Conejo') ? 'selected' : ''; ?>>Conejo</option>
                                <option value="Ave" <?php echo ($mascota_editar && $mascota_editar['especie'] == 'Ave') ? 'selected' : ''; ?>>Ave</option>
                                <option value="Otro" <?php echo ($mascota_editar && $mascota_editar['especie'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_raza">Raza:</label>
                            <input type="text" id="admin_raza" name="raza" value="<?php echo $mascota_editar ? $mascota_editar['raza'] : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_edad">Edad (años):*</label>
                            <input type="number" id="admin_edad" name="edad" min="0" max="30" value="<?php echo $mascota_editar ? $mascota_editar['edad'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_sexo">Sexo:*</label>
                            <select id="admin_sexo" name="sexo" required>
                                <option value="">Seleccionar sexo</option>
                                <option value="macho" <?php echo ($mascota_editar && $mascota_editar['sexo'] == 'macho') ? 'selected' : ''; ?>>Macho</option>
                                <option value="hembra" <?php echo ($mascota_editar && $mascota_editar['sexo'] == 'hembra') ? 'selected' : ''; ?>>Hembra</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_estado">Estado:*</label>
                            <select id="admin_estado" name="estado" required>
                                <option value="disponible" <?php echo ($mascota_editar && $mascota_editar['estado'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                                <option value="adoptado" <?php echo ($mascota_editar && $mascota_editar['estado'] == 'adoptado') ? 'selected' : ''; ?>>Adoptado</option>
                                <option value="proceso" <?php echo ($mascota_editar && $mascota_editar['estado'] == 'proceso') ? 'selected' : ''; ?>>En proceso</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_descripcion">Descripción:*</label>
                        <textarea id="admin_descripcion" name="descripcion" rows="4" required><?php echo $mascota_editar ? $mascota_editar['descripcion'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_imagen">Foto de la mascota:</label>
                        <input type="file" id="admin_imagen" name="imagen" accept="image/*">
                        <?php if ($mascota_editar && $mascota_editar['imagen_url']): ?>
                            <p>Imagen actual: <a href="<?php echo $mascota_editar['imagen_url']; ?>" target="_blank">Ver</a></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <?php if ($mascota_editar): ?>
                            <button type="submit" name="editar_mascota" class="btn">Actualizar Mascota</button>
                            <a href="adopcion.php" class="btn btn-secondary">Cancelar</a>
                        <?php else: ?>
                            <button type="submit" name="crear_mascota" class="btn">Crear Mascota</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
function mostrarSeccion(seccion) {
    // Ocultar todas las secciones
    document.querySelectorAll('.seccion').forEach(sec => {
        sec.style.display = 'none';
    });
    
    // Mostrar la sección seleccionada
    document.getElementById('seccion-' + seccion).style.display = 'block';
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-adopcion').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function solicitarAdopcion(mascotaId) {
    if (confirm('¿Está seguro de que desea solicitar la adopción de esta mascota?')) {
        window.location.href = 'solicitar_adopcion.php?id=' + mascotaId;
    }
}

function editarMascota(mascotaId) {
    window.location.href = 'adopcion.php?editar_mascota=' + mascotaId + '#seccion-admin';
}

function eliminarMascota(mascotaId) {
    if (confirm('¿Está seguro de que desea eliminar esta mascota? Esta acción no se puede deshacer.')) {
        window.location.href = 'adopcion.php?eliminar_mascota=' + mascotaId;
    }
}

function mostrarFormularioMascota() {
    document.getElementById('formulario-mascota').style.display = 'block';
    // Scroll al formulario
    document.getElementById('formulario-mascota').scrollIntoView({ behavior: 'smooth' });
}

// Mostrar sección de ver por defecto
document.addEventListener('DOMContentLoaded', function() {
    mostrarSeccion('ver');
    
    // Si hay parámetros de edición en la URL, mostrar sección admin
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('editar_mascota')) {
        mostrarSeccion('admin');
        document.querySelector('.btn-adopcion.admin').classList.add('active');
    }
});
</script>

<?php
include 'includes/footer.php';
?>