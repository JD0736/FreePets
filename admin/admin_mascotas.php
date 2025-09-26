<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Procesar acciones
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Crear mascota
    if (isset($_POST['crear_mascota'])) {
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
    
    // Editar mascota
    if (isset($_POST['editar_mascota'])) {
        $id_mascota = $_POST['id_mascota'];
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_STRING);
        $raza = filter_input(INPUT_POST, 'raza', FILTER_SANITIZE_STRING);
        $edad = $_POST['edad'];
        $sexo = $_POST['sexo'];
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
        $estado = $_POST['estado'];
        $imagen_actual = $_POST['imagen_actual'];
        
        // Procesar imagen si se subió una nueva
        $imagen_url = $imagen_actual;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $target_dir = "uploads/mascotas/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $imageFileType = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
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
}

// Eliminar mascota
if (isset($_GET['eliminar_mascota'])) {
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

// Obtener mascotas
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
if (isset($_GET['editar_mascota'])) {
    $id_mascota = $_GET['editar_mascota'];
    $sql_editar = "SELECT * FROM mascotas WHERE id_mascota = ?";
    $stmt_editar = $conn->prepare($sql_editar);
    $stmt_editar->bind_param("i", $id_mascota);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $mascota_editar = $resultado_editar->fetch_assoc();
}

$page_title = "Administrar Mascotas";
include 'header_admin.php';
?>

<div class="admin-content">
    <div class="breadcrumb">
        <h3><i class="fas fa-paw"></i> Administrar Mascotas</h3>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-grid">
        <div class="admin-card">
            <h3 id="form-titulo"><?php echo $mascota_editar ? 'Editar' : 'Agregar'; ?> Mascota</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="id_mascota" value="<?php echo $mascota_editar ? $mascota_editar['id_mascota'] : ''; ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo $mascota_editar ? $mascota_editar['imagen_url'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre:*</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo $mascota_editar ? $mascota_editar['nombre'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="especie">Especie:*</label>
                        <select id="especie" name="especie" required>
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
                        <label for="raza">Raza:</label>
                        <input type="text" id="raza" name="raza" value="<?php echo $mascota_editar ? $mascota_editar['raza'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="edad">Edad (años):*</label>
                        <input type="number" id="edad" name="edad" min="0" max="30" value="<?php echo $mascota_editar ? $mascota_editar['edad'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sexo">Sexo:*</label>
                        <select id="sexo" name="sexo" required>
                            <option value="">Seleccionar sexo</option>
                            <option value="macho" <?php echo ($mascota_editar && $mascota_editar['sexo'] == 'macho') ? 'selected' : ''; ?>>Macho</option>
                            <option value="hembra" <?php echo ($mascota_editar && $mascota_editar['sexo'] == 'hembra') ? 'selected' : ''; ?>>Hembra</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado:*</label>
                        <select id="estado" name="estado" required>
                            <option value="disponible" <?php echo ($mascota_editar && $mascota_editar['estado'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                            <option value="adoptado" <?php echo ($mascota_editar && $mascota_editar['estado'] == 'adoptado') ? 'selected' : ''; ?>>Adoptado</option>
                            <option value="proceso" <?php echo ($mascota_editar && $mascota_editar['estado'] == 'proceso') ? 'selected' : ''; ?>>En proceso</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:*</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required><?php echo $mascota_editar ? $mascota_editar['descripcion'] : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Foto de la mascota:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <?php if ($mascota_editar && $mascota_editar['imagen_url']): ?>
                        <p>Imagen actual: <img src="<?php echo $mascota_editar['imagen_url']; ?>" alt="Imagen actual" style="max-width: 100px; margin-top: 10px;"></p>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <?php if ($mascota_editar): ?>
                        <button type="submit" name="editar_mascota" class="btn-primary">Actualizar Mascota</button>
                        <a href="admin_mascotas.php" class="btn-secondary">Cancelar</a>
                    <?php else: ?>
                        <button type="submit" name="crear_mascota" class="btn-primary">Crear Mascota</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="admin-card">
            <h3>Estadísticas de Mascotas</h3>
            <div class="stats-mini">
                <?php
                $total_mascotas = count($mascotas);
                $disponibles = 0;
                $adoptadas = 0;
                $en_proceso = 0;
                
                foreach ($mascotas as $mascota) {
                    if ($mascota['estado'] == 'disponible') $disponibles++;
                    if ($mascota['estado'] == 'adoptado') $adoptadas++;
                    if ($mascota['estado'] == 'proceso') $en_proceso++;
                }
                ?>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $total_mascotas; ?></div>
                    <div class="stat-label">Total Mascotas</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $disponibles; ?></div>
                    <div class="stat-label">Disponibles</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $adoptadas; ?></div>
                    <div class="stat-label">Adoptadas</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $en_proceso; ?></div>
                    <div class="stat-label">En Proceso</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-card">
        <h3>Lista de Mascotas</h3>
        
        <div class="filtros">
            <select id="filtro-estado" onchange="filtrarMascotas()">
                <option value="">Todos los estados</option>
                <option value="disponible">Disponibles</option>
                <option value="adoptado">Adoptadas</option>
                <option value="proceso">En proceso</option>
            </select>
            
            <select id="filtro-especie" onchange="filtrarMascotas()">
                <option value="">Todas las especies</option>
                <option value="Perro">Perros</option>
                <option value="Gato">Gatos</option>
                <option value="Conejo">Conejos</option>
                <option value="Ave">Aves</option>
                <option value="Otro">Otros</option>
            </select>
        </div>
        
        <?php if (count($mascotas) > 0): ?>
            <div class="table-responsive">
                <table class="data-table" id="tabla-mascotas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Edad</th>
                            <th>Sexo</th>
                            <th>Estado</th>
                            <th>Fecha Ingreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mascotas as $mascota): ?>
                            <tr data-estado="<?php echo $mascota['estado']; ?>" data-especie="<?php echo $mascota['especie']; ?>">
                                <td><?php echo $mascota['id_mascota']; ?></td>
                                <td>
                                    <img src="<?php echo $mascota['imagen_url'] ?: 'multimedia/default-pet.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($mascota['nombre']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                </td>
                                <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($mascota['especie']); ?></td>
                                <td><?php echo $mascota['raza'] ? htmlspecialchars($mascota['raza']) : 'No especificado'; ?></td>
                                <td><?php echo $mascota['edad']; ?> años</td>
                                <td><?php echo ucfirst($mascota['sexo']); ?></td>
                                <td>
                                    <span class="badge estado-<?php echo $mascota['estado']; ?>">
                                        <?php echo ucfirst($mascota['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($mascota['fecha_ingreso'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin_mascotas.php?editar_mascota=<?php echo $mascota['id_mascota']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="admin_mascotas.php?eliminar_mascota=<?php echo $mascota['id_mascota']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('¿Está seguro de que desea eliminar esta mascota?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No hay mascotas registradas.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function filtrarMascotas() {
    const filtroEstado = document.getElementById('filtro-estado').value;
    const filtroEspecie = document.getElementById('filtro-especie').value;
    const filas = document.querySelectorAll('#tabla-mascotas tbody tr');
    
    filas.forEach(fila => {
        const estado = fila.getAttribute('data-estado');
        const especie = fila.getAttribute('data-especie');
        
        const mostrarEstado = !filtroEstado || estado === filtroEstado;
        const mostrarEspecie = !filtroEspecie || especie === filtroEspecie;
        
        if (mostrarEstado && mostrarEspecie) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>

<?php
include 'footer_admin.php';
?>