<?php
session_start();
include 'conexion.php';

// Obtener mascotas disponibles
$sql = "SELECT * FROM mascotas WHERE estado = 'disponible' ORDER BY fecha_ingreso DESC";
$resultado = $conn->query($sql);
$mascotas = [];
if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        $mascotas[] = $fila;
    }
}

$page_title = "FreePets - Centro de Adopción";
include 'includes/header.php';
?>

<section class="content adopcion">
    <h2 class="title">Centro de Adopción</h2>
    
    <div class="adopcion-opciones">
        <button onclick="mostrarSeccion('ver')" class="btn-adopcion active">Adoptar Mascota</button>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <button onclick="mostrarSeccion('registro')" class="btn-adopcion">Poner en Adopción</button>
        <?php else: ?>
            <button onclick="alert('Debe iniciar sesión para registrar mascotas')" class="btn-adopcion">Poner en Adopción</button>
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
                        
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <button class="btn" onclick="solicitarAdopcion(<?php echo $mascota['id_mascota']; ?>)">
                                Solicitar Adopción
                            </button>
                        <?php else: ?>
                            <button class="btn" onclick="alert('Debe iniciar sesión para adoptar')">
                                Iniciar Sesión para Adoptar
                            </button>
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
            <form class="formulario-registro" action="procesar_mascota.php" method="POST" enctype="multipart/form-data">
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

// Mostrar sección de ver por defecto
document.addEventListener('DOMContentLoaded', function() {
    mostrarSeccion('ver');
});
</script>

<?php
include 'includes/footer.php';
?>