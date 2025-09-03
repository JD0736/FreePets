<?php
session_start();
include 'conexion.php';

// Procesar formulario de contacto
$mensaje_contacto = "";
$tipo_mensaje_contacto = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contacto'])) {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING);
    
    // Validar campos
    if (empty($nombre) || empty($email) || empty($mensaje)) {
        $mensaje_contacto = "Por favor, complete todos los campos obligatorios.";
        $tipo_mensaje_contacto = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje_contacto = "Por favor, ingrese un email válido.";
        $tipo_mensaje_contacto = "error";
    } else {
        // Guardar en base de datos
        $sql = "INSERT INTO contactos (nombre, email, telefono, mensaje, fecha_contacto) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $email, $telefono, $mensaje);
        
        if ($stmt->execute()) {
            $mensaje_contacto = "¡Gracias por contactarnos! Te responderemos pronto.";
            $tipo_mensaje_contacto = "exito";
            
            // Limpiar campos del formulario
            $_POST = array();
        } else {
            $mensaje_contacto = "Error al enviar el mensaje. Por favor, intente nuevamente.";
            $tipo_mensaje_contacto = "error";
        }
    }
}

$page_title = "FreePets - Inicio";
include 'includes/header.php';
?>

<section class="home" id="home">
    <div class="carousel-container">
        <div class="carousel" id="carousel-images">
            <div class="carousel-slide">
                <img src="multimedia/descargar.jpg" alt="Mascotas felices">
                <div class="carousel-caption">
                    <h2>¡Bienvenido a FreePets!</h2>
                    <p>Donde las mascotas encuentran su hogar perfecto</p>
                    <a href="#services" class="btn">Conoce nuestros servicios</a>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="multimedia/carrusel 2.jpg" alt="Adopta una mascota">
                <div class="carousel-caption">
                    <h2>Adopta, no compres</h2>
                    <p>Cambia una vida y encuentra un amigo fiel</p>
                    <a href="adopcion.php" class="btn">Ver mascotas para adoptar</a>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="multimedia/carrusel.jpg" alt="Ayuda a los animales">
                <div class="carousel-caption">
                    <h2>Tu ayuda hace la diferencia</h2>
                    <p>Apoya nuestra causa y sé parte del cambio</p>
                    <a href="donacion.php" class="btn">Hacer una donación</a>
                </div>
            </div>
        </div>
        <button class="carousel-btn" id="prevBtn">&#10094;</button>
        <button class="carousel-btn" id="nextBtn">&#10095;</button>
        <div class="carousel-indicators">
            <span class="carousel-indicator active" data-index="0"></span>
            <span class="carousel-indicator" data-index="1"></span>
            <span class="carousel-indicator" data-index="2"></span>
        </div>
    </div>
</section>

<section class="content about" id="about">
    <h2 class="title">Nosotros</h2>
    <p>Somos una organización sin ánimo de lucro dedicada a rescatar, cuidar y buscar familias responsables para animales en situación de abandono. Nuestro compromiso es darles una segunda oportunidad llena de amor.</p>
</section>

<section class="content services" id="services">
    <h2 class="title">Servicios</h2>
    <div class="service-grid">
        <div class="service-item" onclick="window.location.href='adopcion.php'">
            <img src="multimedia/perro y humano.avif" alt="Adopciones">
            <div class="btn">Centro de adopción</div>
        </div>
        <div class="service-item" onclick="window.location.href='eventos.php'">
            <img src="multimedia/eventos.jpg" alt="Eventos">
            <div class="btn">Eventos solidarios</div>
        </div>
        <div class="service-item" onclick="window.location.href='reportes_mascotas_situaciones.php'">
            <img src="multimedia/perro abandonado.jpg" alt="Reportes">
            <div class="btn">Reportar mascotas</div>
        </div>
        <div class="service-item" onclick="window.location.href='donacion.php'">
            <img src="multimedia/descargar.png" alt="Donar">
            <div class="btn">Donar</div>
        </div>
    </div>
</section>

<section class="content image"></section>

<section class="content contact" id="contact">
    <h2 class="title">Contacto</h2>
    <p>¿Tienes preguntas? ¡Contáctanos!</p>
    
    <!-- Mostrar mensaje de contacto si existe -->
    <?php if (!empty($mensaje_contacto)): ?>
        <div class="alert <?php echo $tipo_mensaje_contacto; ?>" id="contact-alert">
            <?php echo $mensaje_contacto; ?>
            <span class="close-btn" onclick="document.getElementById('contact-alert').style.display='none'">&times;</span>
        </div>
    <?php endif; ?>
    
    <div class="contact-form">
        <form method="POST" action="">
            <input type="hidden" name="contacto" value="1">
            
            <label for="nombre">Nombre:*</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" required>
            
            <label for="email">Correo electrónico:*</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            
            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
            
            <label for="mensaje">Mensaje:*</label>
            <textarea id="mensaje" name="mensaje" rows="4" required><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
            
            <button type="submit">Enviar Mensaje</button>
        </form>
    </div>
    
    <h3>Teléfono: 300 762 09 48</h3>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3645.396664609902!2d-75.56212461727259!3d6.3252043816440375!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e442f0fa0af557b%3A0x15d05bf2badcec71!2sInstituci%C3%B3n%20Educativa%20Tom%C3%A1s%20Cadavid%20Restrepo!5e1!3m2!1ses-419!2sco!4v1744996658796!5m2!1ses-419!2sco" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</section>

<?php
include 'includes/footer.php';
?>