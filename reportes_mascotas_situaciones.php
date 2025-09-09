<?php
session_start();
include 'conexion.php';

// Verificar si el usuario es administrador
$es_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';

// Procesar reportes
$mensaje = "";
$tipo_mensaje = "";

// Procesar administración de reportes
if ($es_admin) {
    // Cambiar estado de reporte
    if (isset($_GET['cambiar_estado_reporte'])) {
        $id_reporte = $_GET['cambiar_estado_reporte'];
        $nuevo_estado = $_GET['estado'];
        $tipo_reporte = $_GET['tipo'];
        
        if ($tipo_reporte === 'mascota') {
            $sql = "UPDATE reportes_mascotas SET estado = ? WHERE id_reporte = ?";
        } else {
            $sql = "UPDATE situaciones_precarias SET estado = ? WHERE id_situacion = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id_reporte);
        
        if ($stmt->execute()) {
            $mensaje = "Estado actualizado correctamente.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al actualizar el estado: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
    
    // Eliminar reporte
    if (isset($_GET['eliminar_reporte'])) {
        $id_reporte = $_GET['eliminar_reporte'];
        $tipo_reporte = $_GET['tipo'];
        
        if ($tipo_reporte === 'mascota') {
            $sql = "DELETE FROM reportes_mascotas WHERE id_reporte = ?";
        } else {
            $sql = "DELETE FROM situaciones_precarias WHERE id_situacion = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_reporte);
        
        if ($stmt->execute()) {
            $mensaje = "Reporte eliminado correctamente.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al eliminar el reporte: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['reportar_mascota'])) {
        $tipo_reporte = $_POST['tipo_reporte'];
        $nombre_mascota = filter_input(INPUT_POST, 'nombre_mascota', FILTER_SANITIZE_STRING);
        $especie_raza = filter_input(INPUT_POST, 'especie_raza', FILTER_SANITIZE_STRING);
        $lugar = filter_input(INPUT_POST, 'lugar', FILTER_SANITIZE_STRING);
        $fecha_avistamiento = $_POST['fecha_avistamiento'];
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        
        // Validar campos
        if (empty($especie_raza) || empty($lugar) || empty($fecha_avistamiento) || empty($descripcion)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        } else {
            // Guardar reporte
            $sql = "INSERT INTO reportes_mascotas (tipo_reporte, nombre_mascota, especie_raza_color, lugar, fecha_avistamiento, descripcion, id_usuario) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $tipo_reporte, $nombre_mascota, $especie_raza, $lugar, $fecha_avistamiento, $descripcion, $id_usuario);
            
            if ($stmt->execute()) {
                $mensaje = "Reporte enviado correctamente. Gracias por tu ayuda.";
                $tipo_mensaje = "exito";
                $_POST = array();
            } else {
                $mensaje = "Error al enviar el reporte: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
    
    if (isset($_POST['reportar_situacion'])) {
        $tipo_situacion = $_POST['tipo_situacion'];
        $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
        $fecha_situacion = $_POST['fecha_situacion'];
        $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        
        // Validar campos
        if (empty($tipo_situacion) || empty($direccion) || empty($fecha_situacion) || empty($descripcion)) {
            $mensaje = "Por favor, complete todos los campos obligatorios.";
            $tipo_mensaje = "error";
        } else {
            // Guardar situación
            $sql = "INSERT INTO situaciones_precarias (tipo_situacion, direccion, fecha_situacion, descripcion, id_usuario) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $tipo_situacion, $direccion, $fecha_situacion, $descripcion, $id_usuario);
            
            if ($stmt->execute()) {
                $mensaje = "Situación reportada correctamente. Tomaremos medidas.";
                $tipo_mensaje = "exito";
                $_POST = array();
            } else {
                $mensaje = "Error al reportar la situación: " . $conn->error;
                $tipo_mensaje = "error";
            }
        }
    }
}

// Obtener reportes para administración
$reportes_mascotas = [];
$situaciones_precarias = [];

if ($es_admin) {
    // Obtener reportes de mascotas
    $sql_mascotas = "SELECT r.*, u.nombre as reportador 
                    FROM reportes_mascotas r 
                    LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                    ORDER BY r.fecha_reporte DESC";
    $resultado_mascotas = $conn->query($sql_mascotas);
    if ($resultado_mascotas->num_rows > 0) {
        while($fila = $resultado_mascotas->fetch_assoc()) {
            $reportes_mascotas[] = $fila;
        }
    }
    
    // Obtener situaciones precarias
    $sql_situaciones = "SELECT s.*, u.nombre as reportador 
                       FROM situaciones_precarias s 
                       LEFT JOIN usuarios u ON s.id_usuario = u.id_usuario 
                       ORDER BY s.fecha_reporte DESC";
    $resultado_situaciones = $conn->query($sql_situaciones);
    if ($resultado_situaciones->num_rows > 0) {
        while($fila = $resultado_situaciones->fetch_assoc()) {
            $situaciones_precarias[] = $fila;
        }
    }
}

$page_title = "FreePets - Reportar Situaciones";
include 'includes/header.php';
?>

<section class="content reportes">
    <h2 class="title">Reportar Situaciones</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <!-- Panel de administración de reportes -->
    <?php if ($es_admin): ?>
    <div class="admin-panel">
        <h3>Administrar Reportes</h3>
        
        <div class="admin-tabs">
            <button class="admin-tab active" onclick="mostrarTab('mascotas')">Reportes de Mascotas</button>
            <button class="admin-tab" onclick="mostrarTab('situaciones')">Situaciones Precarias</button>
        </div>
        
        <!-- Reportes de mascotas -->
        <div id="tab-mascotas" class="admin-tab-content">
            <h4>Reportes de Mascotas</h4>
            
            <?php if (count($reportes_mascotas) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reportador</th>
                                <th>Tipo</th>
                                <th>Mascota</th>
                                <th>Especie/Raza</th>
                                <th>Lugar</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportes_mascotas as $reporte): ?>
                                <tr>
                                    <td><?php echo $reporte['id_reporte']; ?></td>
                                    <td><?php echo $reporte['reportador'] ? htmlspecialchars($reporte['reportador']) : 'Anónimo'; ?></td>
                                    <td><?php echo ucfirst($reporte['tipo_reporte']); ?></td>
                                    <td><?php echo $reporte['nombre_mascota'] ? htmlspecialchars($reporte['nombre_mascota']) : 'No especificado'; ?></td>
                                    <td><?php echo htmlspecialchars($reporte['especie_raza_color']); ?></td>
                                    <td><?php echo htmlspecialchars($reporte['lugar']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($reporte['fecha_avistamiento'])); ?></td>
                                    <td>
                                        <span class="estado-badge estado-<?php echo $reporte['estado']; ?>">
                                            <?php echo ucfirst($reporte['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-actions">
                                            <?php if ($reporte['estado'] == 'pendiente'): ?>
                                                <button class="admin-btn" onclick="cambiarEstado(<?php echo $reporte['id_reporte']; ?>, 'en_proceso', 'mascota')">En Proceso</button>
                                                <button class="admin-btn" onclick="cambiarEstado(<?php echo $reporte['id_reporte']; ?>, 'resuelto', 'mascota')">Resuelto</button>
                                            <?php elseif ($reporte['estado'] == 'en_proceso'): ?>
                                                <button class="admin-btn" onclick="cambiarEstado(<?php echo $reporte['id_reporte']; ?>, 'resuelto', 'mascota')">Resuelto</button>
                                            <?php endif; ?>
                                            <button class="admin-btn delete" onclick="eliminarReporte(<?php echo $reporte['id_reporte']; ?>, 'mascota')">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay reportes de mascotas.</p>
            <?php endif; ?>
        </div>
        
        <!-- Situaciones precarias -->
        <div id="tab-situaciones" class="admin-tab-content" style="display: none;">
            <h4>Situaciones Precarias</h4>
            
            <?php if (count($situaciones_precarias) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reportador</th>
                                <th>Tipo</th>
                                <th>Dirección</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($situaciones_precarias as $situacion): ?>
                                <tr>
                                    <td><?php echo $situacion['id_situacion']; ?></td>
                                    <td><?php echo $situacion['reportador'] ? htmlspecialchars($situacion['reportador']) : 'Anónimo'; ?></td>
                                    <td><?php echo ucfirst($situacion['tipo_situacion']); ?></td>
                                    <td><?php echo htmlspecialchars($situacion['direccion']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($situacion['fecha_situacion'])); ?></td>
                                    <td>
                                        <span class="estado-badge estado-<?php echo $situacion['estado']; ?>">
                                            <?php echo ucfirst($situacion['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-actions">
                                            <?php if ($situacion['estado'] == 'pendiente'): ?>
                                                <button class="admin-btn" onclick="cambiarEstado(<?php echo $situacion['id_situacion']; ?>, 'en_proceso', 'situacion')">En Proceso</button>
                                                <button class="admin-btn" onclick="cambiarEstado(<?php echo $situacion['id_situacion']; ?>, 'resuelto', 'situacion')">Resuelto</button>
                                            <?php elseif ($situacion['estado'] == 'en_proceso'): ?>
                                                <button class="admin-btn" onclick="cambiarEstado(<?php echo $situacion['id_situacion']; ?>, 'resuelto', 'situacion')">Resuelto</button>
                                            <?php endif; ?>
                                            <button class="admin-btn delete" onclick="eliminarReporte(<?php echo $situacion['id_situacion']; ?>, 'situacion')">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay situaciones precarias reportadas.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="reportes-grid">
        <!-- Reporte de mascota -->
        <div class="reporte-card">
            <h3><i class="fas fa-paw"></i> Reportar Mascota</h3>
            <form method="POST">
                <input type="hidden" name="reportar_mascota" value="1">
                
                <div class="form-group">
                    <label for="tipo_reporte">Tipo de reporte:*</label>
                    <select id="tipo_reporte" name="tipo_reporte" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="Perdida">Mascota Perdida</option>
                        <option value="Encontrada">Mascota Encontrada</option>
                        <option value="En adopcion">En Adopción</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nombre_mascota">Nombre de la mascota (opcional):</label>
                    <input type="text" id="nombre_mascota" name="nombre_mascota">
                </div>
                
                <div class="form-group">
                    <label for="especie_raza">Especie/Raza/Color:*</label>
                    <input type="text" id="especie_raza" name="especie_raza" required 
                           placeholder="Ej: Perro labrador amarillo, Gato siamés, etc.">
                </div>
                
                <div class="form-group">
                    <label for="lugar">Lugar del reporte:*</label>
                    <input type="text" id="lugar" name="lugar" required 
                           placeholder="Dirección exacta o punto de referencia">
                </div>
                
                <div class="form-group">
                    <label for="fecha_avistamiento">Fecha del avistamiento:*</label>
                    <input type="date" id="fecha_avistamiento" name="fecha_avistamiento" required 
                           max="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción detallada:*</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required 
                              placeholder="Describa la situación, condición de la mascota, comportamiento, etc."></textarea>
                </div>
                
                <button type="submit" class="btn">Enviar Reporte</button>
            </form>
        </div>

        <!-- Reporte de situación precaria -->
        <div class="reporte-card">
            <h3><i class="fas fa-exclamation-triangle"></i> Reportar Situación Precaria</h3>
            <form method="POST">
                <input type="hidden" name="reportar_situacion" value="1">
                
                <div class="form-group">
                    <label for="tipo_situacion">Tipo de situación:*</label>
                    <select id="tipo_situacion" name="tipo_situacion" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="Maltrato">Maltrato animal</option>
                        <option value="Abandono">Abandono</option>
                        <option value="Falta de alimento">Falta de alimento/agua</option>
                        <option value="Condiciones precarias">Condiciones precarias</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección exacta:*</label>
                    <input type="text" id="direccion" name="direccion" required 
                           placeholder="Dirección donde ocurre la situación">
                </div>
                
                <div class="form-group">
                    <label for="fecha_situacion">Fecha de la situación:*</label>
                    <input type="date" id="fecha_situacion" name="fecha_situacion" required 
                           max="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="descripcion_situacion">Descripción detallada:*</label>
                    <textarea id="descripcion_situacion" name="descripcion" rows="4" required 
                              placeholder="Describa la situación en detalle, número de animales afectados, condiciones, etc."></textarea>
                </div>
                
                <button type="submit" class="btn">Reportar Situación</button>
            </form>
        </div>
    </div>
    
    <div class="info-importante">
        <h4><i class="fas fa-info-circle"></i> Información Importante</h4>
        <p>Todos los reportes son verificados por nuestro equipo. En caso de emergencia, contacta directamente a las autoridades locales.</p>
        <p>Tu reporte puede salvar vidas animales. ¡Gracias por tu colaboración!</p>
    </div>
</section>

<script>
function mostrarTab(tab) {
    // Ocultar todos los contenidos de pestañas
    document.querySelectorAll('.admin-tab-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Mostrar la pestaña seleccionada
    document.getElementById('tab-' + tab).style.display = 'block';
    
    // Actualizar botones de pestañas activas
    document.querySelectorAll('.admin-tab').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function cambiarEstado(id, estado, tipo) {
    if (confirm('¿Está seguro de que desea cambiar el estado de este reporte?')) {
        window.location.href = 'reportes_mascotas_situaciones.php?cambiar_estado_reporte=' + id + '&estado=' + estado + '&tipo=' + tipo;
    }
}

function eliminarReporte(id, tipo) {
    if (confirm('¿Está seguro de que desea eliminar este reporte? Esta acción no se puede deshacer.')) {
        window.location.href = 'reportes_mascotas_situaciones.php?eliminar_reporte=' + id + '&tipo=' + tipo;
    }
}
</script>

<?php
include 'includes/footer.php';
?>