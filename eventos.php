<?php
session_start();
include 'conexion.php';

// Verificar si el usuario es administrador
$es_admin = isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';

// Procesar creación de eventos (solo admin)
$mensaje = "";
$tipo_mensaje = "";

// Crear evento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_evento']) && $es_admin) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $fecha = $_POST['fecha'];
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $lugar = filter_input(INPUT_POST, 'lugar', FILTER_SANITIZE_STRING);
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $info_adicional = filter_input(INPUT_POST, 'info_adicional', FILTER_SANITIZE_STRING);
    
    // Validar campos
    if (empty($titulo) || empty($fecha) || empty($descripcion) || empty($lugar)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje = "error";
    } else {
        // Guardar evento
        $sql = "INSERT INTO eventos (titulo, fecha, descripcion, lugar, hora_inicio, hora_fin, informacion_adicional) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $titulo, $fecha, $descripcion, $lugar, $hora_inicio, $hora_fin, $info_adicional);
        
        if ($stmt->execute()) {
            $mensaje = "Evento creado correctamente.";
            $tipo_mensaje = "exito";
            $_POST = array();
        } else {
            $mensaje = "Error al crear el evento: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
}

// Editar evento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_evento']) && $es_admin) {
    $id_evento = $_POST['id_evento'];
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $fecha = $_POST['fecha'];
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $lugar = filter_input(INPUT_POST, 'lugar', FILTER_SANITIZE_STRING);
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $info_adicional = filter_input(INPUT_POST, 'info_adicional', FILTER_SANITIZE_STRING);
    
    // Validar campos
    if (empty($titulo) || empty($fecha) || empty($descripcion) || empty($lugar)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje = "error";
    } else {
        // Actualizar evento
        $sql = "UPDATE eventos SET titulo=?, fecha=?, descripcion=?, lugar=?, hora_inicio=?, hora_fin=?, informacion_adicional=?
                WHERE id_evento=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $titulo, $fecha, $descripcion, $lugar, $hora_inicio, $hora_fin, $info_adicional, $id_evento);
        
        if ($stmt->execute()) {
            $mensaje = "Evento actualizado correctamente.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al actualizar el evento: " . $conn->error;
            $tipo_mensaje = "error";
        }
    }
}

// Eliminar evento
if (isset($_GET['eliminar_evento']) && $es_admin) {
    $id_evento = $_GET['eliminar_evento'];
    
    $sql = "DELETE FROM eventos WHERE id_evento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_evento);
    
    if ($stmt->execute()) {
        $mensaje = "Evento eliminado correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al eliminar el evento: " . $conn->error;
        $tipo_mensaje = "error";
    }
}

// Obtener eventos
$sql = "SELECT * FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha, hora_inicio";
$resultado = $conn->query($sql);
$eventos = [];
if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $eventos[] = $fila;
    }
}

// Obtener datos de evento para edición
$evento_editar = null;
if (isset($_GET['editar_evento']) && $es_admin) {
    $id_evento = $_GET['editar_evento'];
    $sql_editar = "SELECT * FROM eventos WHERE id_evento = ?";
    $stmt_editar = $conn->prepare($sql_editar);
    $stmt_editar->bind_param("i", $id_evento);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $evento_editar = $resultado_editar->fetch_assoc();
}

$page_title = "FreePets - Eventos Solidarios";
include 'includes/header.php';
?>

<section class="content eventos">
    <h2 class="title">Eventos Solidarios</h2>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <!-- Panel de administración de eventos -->
    <?php if ($es_admin): ?>
    <div class="admin-panel">
        <h3>Administrar Eventos</h3>
        
        <button class="btn" onclick="mostrarFormularioEvento()">Agregar Nuevo Evento</button>
        
        <!-- Formulario para crear/editar evento -->
        <div id="formulario-evento" style="display: <?php echo $evento_editar ? 'block' : 'none'; ?>; margin-top: 20px;">
            <h4><?php echo $evento_editar ? 'Editar' : 'Crear'; ?> Evento</h4>
            <form method="POST" action="">
                <input type="hidden" name="id_evento" value="<?php echo $evento_editar ? $evento_editar['id_evento'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="titulo">Título del evento:*</label>
                        <input type="text" id="titulo" name="titulo" value="<?php echo $evento_editar ? $evento_editar['titulo'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha">Fecha:*</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo $evento_editar ? $evento_editar['fecha'] : ''; ?>" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="hora_inicio">Hora de inicio:</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" value="<?php echo $evento_editar ? $evento_editar['hora_inicio'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hora_fin">Hora de fin:</label>
                        <input type="time" id="hora_fin" name="hora_fin" value="<?php echo $evento_editar ? $evento_editar['hora_fin'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="lugar">Lugar:*</label>
                    <input type="text" id="lugar" name="lugar" value="<?php echo $evento_editar ? $evento_editar['lugar'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:*</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required><?php echo $evento_editar ? $evento_editar['descripcion'] : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="info_adicional">Información adicional:</label>
                    <textarea id="info_adicional" name="info_adicional" rows="3"><?php echo $evento_editar ? $evento_editar['informacion_adicional'] : ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <?php if ($evento_editar): ?>
                        <button type="submit" name="editar_evento" class="btn">Actualizar Evento</button>
                        <a href="eventos.php" class="btn btn-secondary">Cancelar</a>
                    <?php else: ?>
                        <button type="submit" name="crear_evento" class="btn">Crear Evento</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Lista de eventos -->
    <div class="eventos-lista">
        <h3>Próximos Eventos</h3>
        
        <?php if (count($eventos) > 0): ?>
            <div class="eventos-grid">
                <?php foreach ($eventos as $evento): ?>
                    <div class="evento-card">
                        <div class="evento-fecha">
                            <?php echo date('d M', strtotime($evento['fecha'])); ?>
                        </div>
                        <h4><?php echo htmlspecialchars($evento['titulo']); ?></h4>
                        <p class="evento-descripcion"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                        
                        <div class="evento-info">
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($evento['lugar']); ?>
                            </div>
                            
                            <?php if ($evento['hora_inicio']): ?>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <?php echo date('H:i', strtotime($evento['hora_inicio'])); ?>
                                <?php if ($evento['hora_fin']): ?>
                                - <?php echo date('H:i', strtotime($evento['hora_fin'])); ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($evento['informacion_adicional']): ?>
                        <div class="evento-extra">
                            <p><?php echo htmlspecialchars($evento['informacion_adicional']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <button class="btn-evento" onclick="asistirEvento(<?php echo $evento['id_evento']; ?>)">
                            <i class="fas fa-calendar-check"></i> Confirmar Asistencia
                        </button>
                        
                        <?php if ($es_admin): ?>
                            <div class="admin-actions">
                                <button class="admin-btn" onclick="editarEvento(<?php echo $evento['id_evento']; ?>)">Editar</button>
                                <button class="admin-btn delete" onclick="eliminarEvento(<?php echo $evento['id_evento']; ?>)">Eliminar</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-eventos">
                <h4>No hay eventos programados en este momento</h4>
                <p>Próximamente anunciaremos nuevos eventos solidarios. ¡Mantente atento!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Eventos pasados -->
    <?php
    $sql_pasados = "SELECT * FROM eventos WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 5";
    $resultado_pasados = $conn->query($sql_pasados);
    if ($resultado_pasados->num_rows > 0):
    ?>
        <div class="eventos-pasados">
            <h3>Eventos Anteriores</h3>
            <ul>
                <?php while($evento = $resultado_pasados->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($evento['titulo']); ?></strong> - 
                        <?php echo date('d/m/Y', strtotime($evento['fecha'])); ?> - 
                        <?php echo htmlspecialchars($evento['lugar']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endif; ?>
</section>

<script>
function asistirEvento(eventoId) {
    <?php if (isset($_SESSION['usuario_id'])): ?>
        if (confirm('¿Confirmar tu asistencia a este evento?')) {
            // Aquí iría la lógica para registrar la asistencia
            alert('¡Asistencia confirmada! Te esperamos en el evento.');
        }
    <?php else: ?>
        alert('Debes iniciar sesión para confirmar asistencia a eventos.');
    <?php endif; ?>
}

function editarEvento(eventoId) {
    window.location.href = 'eventos.php?editar_evento=' + eventoId;
}

function eliminarEvento(eventoId) {
    if (confirm('¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.')) {
        window.location.href = 'eventos.php?eliminar_evento=' + eventoId;
    }
}

function mostrarFormularioEvento() {
    document.getElementById('formulario-evento').style.display = 'block';
    // Scroll al formulario
    document.getElementById('formulario-evento').scrollIntoView({ behavior: 'smooth' });
}

// Si hay parámetros de edición en la URL, mostrar formulario
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('editar_evento')) {
        document.getElementById('formulario-evento').style.display = 'block';
    }
});
</script>

<?php
include 'includes/footer.php';
?>