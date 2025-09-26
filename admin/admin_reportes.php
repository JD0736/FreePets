<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Procesar cambios de estado
if (isset($_GET['cambiar_estado'])) {
    $id_reporte = $_GET['cambiar_estado'];
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

// Obtener reportes
$reportes_mascotas = [];
$situaciones_precarias = [];

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

$page_title = "Administrar Reportes";
include 'header_admin.php';
?>

<div class="admin-content">
    <div class="breadcrumb">
        <h3><i class="fas fa-flag"></i> Administrar Reportes</h3>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-tabs">
        <button class="tab-button active" onclick="mostrarTab('mascotas')">Reportes de Mascotas</button>
        <button class="tab-button" onclick="mostrarTab('situaciones')">Situaciones Precarias</button>
    </div>
    
    <!-- Reportes de mascotas -->
    <div id="tab-mascotas" class="tab-content active">
        <div class="admin-card">
            <h3>Reportes de Mascotas</h3>
            
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
                                <th>Fecha Avistamiento</th>
                                <th>Estado</th>
                                <th>Fecha Reporte</th>
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
                                        <span class="badge estado-<?php echo $reporte['estado']; ?>">
                                            <?php echo ucfirst($reporte['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-info" onclick="verDetalles(<?php echo $reporte['id_reporte']; ?>, 'mascota')">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <?php if ($reporte['estado'] == 'pendiente'): ?>
                                                <button class="btn-warning" onclick="cambiarEstado(<?php echo $reporte['id_reporte']; ?>, 'en_proceso', 'mascota')">
                                                    <i class="fas fa-play"></i> En Proceso
                                                </button>
                                            <?php elseif ($reporte['estado'] == 'en_proceso'): ?>
                                                <button class="btn-success" onclick="cambiarEstado(<?php echo $reporte['id_reporte']; ?>, 'resuelto', 'mascota')">
                                                    <i class="fas fa-check"></i> Resuelto
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn-delete" onclick="eliminarReporte(<?php echo $reporte['id_reporte']; ?>, 'mascota')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
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
    </div>
    
    <!-- Situaciones precarias -->
    <div id="tab-situaciones" class="tab-content">
        <div class="admin-card">
            <h3>Situaciones Precarias</h3>
            
            <?php if (count($situaciones_precarias) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reportador</th>
                                <th>Tipo</th>
                                <th>Dirección</th>
                                <th>Fecha Situación</th>
                                <th>Estado</th>
                                <th>Fecha Reporte</th>
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
                                        <span class="badge estado-<?php echo $situacion['estado']; ?>">
                                            <?php echo ucfirst($situacion['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($situacion['fecha_reporte'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-info" onclick="verDetalles(<?php echo $situacion['id_situacion']; ?>, 'situacion')">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <?php if ($situacion['estado'] == 'pendiente'): ?>
                                                <button class="btn-warning" onclick="cambiarEstado(<?php echo $situacion['id_situacion']; ?>, 'en_proceso', 'situacion')">
                                                    <i class="fas fa-play"></i> En Proceso
                                                </button>
                                            <?php elseif ($situacion['estado'] == 'en_proceso'): ?>
                                                <button class="btn-success" onclick="cambiarEstado(<?php echo $situacion['id_situacion']; ?>, 'resuelto', 'situacion')">
                                                    <i class="fas fa-check"></i> Resuelto
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn-delete" onclick="eliminarReporte(<?php echo $situacion['id_situacion']; ?>, 'situacion')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
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
</div>

<!-- Modal para ver detalles -->
<div id="modal-detalles" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Detalles del Reporte</h3>
        <div id="detalles-contenido"></div>
    </div>
</div>

<script>
function mostrarTab(tabName) {
    // Ocultar todos los contenidos de pestañas
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Mostrar la pestaña seleccionada
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Actualizar botones de pestañas
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function cambiarEstado(id, estado, tipo) {
    if (confirm('¿Está seguro de que desea cambiar el estado de este reporte?')) {
        window.location.href = 'admin_reportes.php?cambiar_estado=' + id + '&estado=' + estado + '&tipo=' + tipo;
    }
}

function eliminarReporte(id, tipo) {
    if (confirm('¿Está seguro de que desea eliminar este reporte? Esta acción no se puede deshacer.')) {
        window.location.href = 'admin_reportes.php?eliminar_reporte=' + id + '&tipo=' + tipo;
    }
}

function verDetalles(id, tipo) {
    // Aquí iría una llamada AJAX para obtener los detalles
    // Por ahora, mostramos un mensaje de ejemplo
    const modal = document.getElementById('modal-detalles');
    const contenido = document.getElementById('detalles-contenido');
    
    contenido.innerHTML = `
        <p><strong>ID:</strong> ${id}</p>
        <p><strong>Tipo:</strong> ${tipo === 'mascota' ? 'Reporte de Mascota' : 'Situación Precaria'}</p>
        <p><strong>Descripción:</strong> Información detallada del reporte...</p>
        <p>Esta funcionalidad cargaría los detalles completos del reporte seleccionado.</p>
    `;
    
    modal.style.display = 'block';
}

// Cerrar modal
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('modal-detalles').style.display = 'none';
});

// Cerrar modal al hacer clic fuera
window.addEventListener('click', function(event) {
    const modal = document.getElementById('modal-detalles');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 20px;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    position: relative;
}

.close {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.admin-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 2px solid #ddd;
}

.tab-button {
    padding: 10px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    border-bottom: 3px solid transparent;
}

.tab-button.active {
    border-bottom-color: #4aa6b5;
    color: #4aa6b5;
    font-weight: bold;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>

<?php
include 'footer_admin.php';
?>