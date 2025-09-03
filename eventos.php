<?php
session_start();
include 'conexion.php';

// Procesar creación de eventos (solo admin)
$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_evento'])) {
    if (isset($_SESSION['usuario_id']) && $_SESSION['tipo_usuario'] === 'admin') {
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
    } else {
        $mensaje = "No tienes permisos para crear eventos.";
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
    
    <!-- Formulario de creación de evento (solo para admin) -->
    <?php if (isset($_SESSION['usuario_id']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
        <div class="crear-evento">
            <h3><i class="fas fa-plus-circle"></i> Crear Nuevo Evento</h3>
            <form method="POST">
                <input type="hidden" name="crear_evento" value="1">
                
                <div class="form-group">
                    <label for="titulo">Título del evento:*</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                
                <div class="form-group">
                    <label for="fecha">Fecha:*</label>
                    <input type="date" id="fecha" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="hora_inicio">Hora de inicio:</label>
                    <input type="time" id="hora_inicio" name="hora_inicio">
                </div>
                
                <div class="form-group">
                    <label for="hora_fin">Hora de fin:</label>
                    <input type="time" id="hora_fin" name="hora_fin">
                </div>
                
                <div class="form-group">
                    <label for="lugar">Lugar:*</label>
                    <input type="text" id="lugar" name="lugar" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:*</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="info_adicional">Información adicional:</label>
                    <textarea id="info_adicional" name="info_adicional" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn">Crear Evento</button>
            </form>
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
</script>

<?php
include 'includes/footer.php';
?>