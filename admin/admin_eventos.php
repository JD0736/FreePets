<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Crear evento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_evento'])) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $fecha = $_POST['fecha'];
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $lugar = filter_input(INPUT_POST, 'lugar', FILTER_SANITIZE_STRING);
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $info_adicional = filter_input(INPUT_POST, 'info_adicional', FILTER_SANITIZE_STRING);
    
    if (empty($titulo) || empty($fecha) || empty($descripcion) || empty($lugar)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje = "error";
    } else {
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_evento'])) {
    $id_evento = $_POST['id_evento'];
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $fecha = $_POST['fecha'];
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $lugar = filter_input(INPUT_POST, 'lugar', FILTER_SANITIZE_STRING);
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $info_adicional = filter_input(INPUT_POST, 'info_adicional', FILTER_SANITIZE_STRING);
    
    if (empty($titulo) || empty($fecha) || empty($descripcion) || empty($lugar)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje = "error";
    } else {
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
if (isset($_GET['eliminar_evento'])) {
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
$sql = "SELECT * FROM eventos ORDER BY fecha DESC";
$resultado = $conn->query($sql);
$eventos = [];
if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $eventos[] = $fila;
    }
}

// Obtener datos de evento para edición
$evento_editar = null;
if (isset($_GET['editar_evento'])) {
    $id_evento = $_GET['editar_evento'];
    $sql_editar = "SELECT * FROM eventos WHERE id_evento = ?";
    $stmt_editar = $conn->prepare($sql_editar);
    $stmt_editar->bind_param("i", $id_evento);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $evento_editar = $resultado_editar->fetch_assoc();
}

$page_title = "Administrar Eventos";
include 'header_admin.php';
?>

<div class="admin-content">
    <div class="breadcrumb">
        <h3><i class="fas fa-calendar"></i> Administrar Eventos</h3>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-grid">
        <div class="admin-card">
            <h3 id="form-titulo"><?php echo $evento_editar ? 'Editar' : 'Crear'; ?> Evento</h3>
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
                        <button type="submit" name="editar_evento" class="btn-primary">Actualizar Evento</button>
                        <a href="admin_eventos.php" class="btn-secondary">Cancelar</a>
                    <?php else: ?>
                        <button type="submit" name="crear_evento" class="btn-primary">Crear Evento</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="admin-card">
            <h3>Estadísticas de Eventos</h3>
            <div class="stats-mini">
                <?php
                $total_eventos = count($eventos);
                $eventos_pasados = 0;
                $eventos_futuros = 0;
                $hoy = date('Y-m-d');
                
                foreach ($eventos as $evento) {
                    if ($evento['fecha'] < $hoy) {
                        $eventos_pasados++;
                    } else {
                        $eventos_futuros++;
                    }
                }
                ?>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $total_eventos; ?></div>
                    <div class="stat-label">Total Eventos</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $eventos_futuros; ?></div>
                    <div class="stat-label">Próximos</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $eventos_pasados; ?></div>
                    <div class="stat-label">Pasados</div>
                </div>
            </div>
            
            <h4 style="margin-top: 20px;">Próximos Eventos</h4>
            <div class="eventos-proximos">
                <?php
                $sql_proximos = "SELECT * FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 3";
                $resultado_proximos = $conn->query($sql_proximos);
                if ($resultado_proximos->num_rows > 0) {
                    while($evento = $resultado_proximos->fetch_assoc()) {
                        echo '<div class="evento-mini">
                                <strong>' . htmlspecialchars($evento['titulo']) . '</strong><br>
                                <small>' . date('d/m/Y', strtotime($evento['fecha'])) . ' - ' . htmlspecialchars($evento['lugar']) . '</small>
                              </div>';
                    }
                } else {
                    echo '<p>No hay eventos próximos.</p>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <div class="admin-card">
        <h3>Lista de Eventos</h3>
        
        <div class="filtros">
            <select id="filtro-fecha" onchange="filtrarEventos()">
                <option value="">Todos los eventos</option>
                <option value="futuros">Próximos eventos</option>
                <option value="pasados">Eventos pasados</option>
            </select>
        </div>
        
        <?php if (count($eventos) > 0): ?>
            <div class="table-responsive">
                <table class="data-table" id="tabla-eventos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Lugar</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventos as $evento): ?>
                            <tr data-fecha="<?php echo $evento['fecha']; ?>">
                                <td><?php echo $evento['id_evento']; ?></td>
                                <td><?php echo htmlspecialchars($evento['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($evento['fecha'])); ?></td>
                                <td>
                                    <?php if ($evento['hora_inicio']): ?>
                                        <?php echo date('H:i', strtotime($evento['hora_inicio'])); ?>
                                        <?php if ($evento['hora_fin']): ?>
                                            - <?php echo date('H:i', strtotime($evento['hora_fin'])); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Todo el día
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($evento['lugar']); ?></td>
                                <td><?php echo strlen($evento['descripcion']) > 50 ? substr($evento['descripcion'], 0, 50) . '...' : $evento['descripcion']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="admin_eventos.php?editar_evento=<?php echo $evento['id_evento']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="admin_eventos.php?eliminar_evento=<?php echo $evento['id_evento']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('¿Está seguro de que desea eliminar este evento?')">
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
            <p>No hay eventos registrados.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function filtrarEventos() {
    const filtro = document.getElementById('filtro-fecha').value;
    const filas = document.querySelectorAll('#tabla-eventos tbody tr');
    const hoy = new Date().toISOString().split('T')[0];
    
    filas.forEach(fila => {
        const fechaEvento = fila.getAttribute('data-fecha');
        
        if (filtro === 'futuros') {
            fila.style.display = fechaEvento >= hoy ? '' : 'none';
        } else if (filtro === 'pasados') {
            fila.style.display = fechaEvento < hoy ? '' : 'none';
        } else {
            fila.style.display = '';
        }
    });
}
</script>

<?php
include 'footer_admin.php';
?>