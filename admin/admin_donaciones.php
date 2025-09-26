<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// Eliminar donación
if (isset($_GET['eliminar_donacion'])) {
    $id_donacion = $_GET['eliminar_donacion'];
    
    $sql = "DELETE FROM donaciones WHERE id_donacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_donacion);
    
    if ($stmt->execute()) {
        $mensaje = "Donación eliminada correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al eliminar la donación: " . $conn->error;
        $tipo_mensaje = "error";
    }
}

// Cambiar estado de donación
if (isset($_GET['cambiar_estado'])) {
    $id_donacion = $_GET['cambiar_estado'];
    $nuevo_estado = $_GET['estado'];
    
    $sql = "UPDATE donaciones SET estado = ? WHERE id_donacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id_donacion);
    
    if ($stmt->execute()) {
        $mensaje = "Estado de donación actualizado correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al actualizar el estado: " . $conn->error;
        $tipo_mensaje = "error";
    }
}

// Obtener donaciones
$donaciones = [];
$sql_donaciones = "SELECT d.*, u.nombre as donante 
                  FROM donaciones d 
                  LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario 
                  ORDER BY d.fecha_donacion DESC";
$resultado_donaciones = $conn->query($sql_donaciones);
if ($resultado_donaciones->num_rows > 0) {
    while($fila = $resultado_donaciones->fetch_assoc()) {
        $donaciones[] = $fila;
    }
}

// Estadísticas
$total_donaciones = count($donaciones);
$donaciones_pendientes = 0;
$donaciones_aprobadas = 0;
$total_monetario = 0;

foreach ($donaciones as $donacion) {
    if ($donacion['estado'] == 'pendiente') $donaciones_pendientes++;
    if ($donacion['estado'] == 'aprobado') $donaciones_aprobadas++;
    if ($donacion['tipo_donacion'] == 'dinero') $total_monetario += $donacion['monto'];
}

$page_title = "Administrar Donaciones";
include 'header_admin.php';
?>

<div class="admin-content">
    <div class="breadcrumb">
        <h3><i class="fas fa-hand-holding-heart"></i> Administrar Donaciones</h3>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert <?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-grid">
        <div class="admin-card">
            <h3>Estadísticas de Donaciones</h3>
            <div class="stats-mini">
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $total_donaciones; ?></div>
                    <div class="stat-label">Total Donaciones</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $donaciones_pendientes; ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number"><?php echo $donaciones_aprobadas; ?></div>
                    <div class="stat-label">Aprobadas</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-number">$<?php echo number_format($total_monetario, 2); ?></div>
                    <div class="stat-label">Total Monetario</div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <h3>Filtros</h3>
            <div class="filtros">
                <select id="filtro-estado" onchange="filtrarDonaciones()">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="aprobado">Aprobadas</option>
                </select>
                
                <select id="filtro-tipo" onchange="filtrarDonaciones()">
                    <option value="">Todos los tipos</option>
                    <option value="dinero">Monetarias</option>
                    <option value="juguetes">Juguetes</option>
                    <option value="comida">Comida</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="admin-card">
        <h3>Lista de Donaciones</h3>
        
        <?php if (count($donaciones) > 0): ?>
            <div class="table-responsive">
                <table class="data-table" id="tabla-donaciones">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donante</th>
                            <th>Tipo</th>
                            <th>Artículo/Cantidad</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donaciones as $donacion): ?>
                            <tr data-estado="<?php echo $donacion['estado']; ?>" data-tipo="<?php echo $donacion['tipo_donacion']; ?>">
                                <td><?php echo $donacion['id_donacion']; ?></td>
                                <td><?php echo $donacion['donante'] ? htmlspecialchars($donacion['donante']) : 'Anónimo'; ?></td>
                                <td><?php echo ucfirst($donacion['tipo_donacion']); ?></td>
                                <td>
                                    <?php if ($donacion['tipo_donacion'] === 'dinero'): ?>
                                        Donación monetaria
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($donacion['tipo_articulo']); ?> (<?php echo $donacion['cantidad']; ?>)
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($donacion['monto'] > 0): ?>
                                        $<?php echo number_format($donacion['monto'], 2); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($donacion['fecha_donacion'])); ?></td>
                                <td>
                                    <span class="badge estado-<?php echo $donacion['estado']; ?>">
                                        <?php echo ucfirst($donacion['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($donacion['estado'] == 'pendiente'): ?>
                                            <button class="btn-success" onclick="cambiarEstado(<?php echo $donacion['id_donacion']; ?>, 'aprobado')">
                                                <i class="fas fa-check"></i> Aprobar
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-delete" onclick="eliminarDonacion(<?php echo $donacion['id_donacion']; ?>)">
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
            <p>No hay donaciones registradas.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function filtrarDonaciones() {
    const filtroEstado = document.getElementById('filtro-estado').value;
    const filtroTipo = document.getElementById('filtro-tipo').value;
    const filas = document.querySelectorAll('#tabla-donaciones tbody tr');
    
    filas.forEach(fila => {
        const estado = fila.getAttribute('data-estado');
        const tipo = fila.getAttribute('data-tipo');
        
        const mostrarEstado = !filtroEstado || estado === filtroEstado;
        const mostrarTipo = !filtroTipo || tipo === filtroTipo;
        
        if (mostrarEstado && mostrarTipo) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}

function cambiarEstado(id, estado) {
    if (confirm('¿Está seguro de que desea cambiar el estado de esta donación?')) {
        window.location.href = 'admin_donaciones.php?cambiar_estado=' + id + '&estado=' + estado;
    }
}

function eliminarDonacion(id) {
    if (confirm('¿Está seguro de que desea eliminar esta donación? Esta acción no se puede deshacer.')) {
        window.location.href = 'admin_donaciones.php?eliminar_donacion=' + id;
    }
}
</script>

<?php
include 'footer_admin.php';
?>