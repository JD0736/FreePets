</main>

<footer class="admin-footer">
    <div class="admin-footer-content">
        <p>&copy; <?php echo date('Y'); ?> FreePets - Sistema de Administración. Todos los derechos reservados.</p>
        <p>Versión 1.0 | Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>
</footer>

<script>
// Funcionalidad para menús desplegables en móviles
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const adminNav = document.querySelector('.admin-nav');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            adminNav.classList.toggle('active');
        });
    }
    
    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.admin-nav') && !event.target.closest('.menu-toggle')) {
            adminNav.classList.remove('active');
        }
    });
});

// Mostrar/ocultar contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}
</script>

</body>
</html>