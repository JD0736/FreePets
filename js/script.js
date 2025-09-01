// --- Menú hamburguesa ---
let navbar = document.querySelector('.navbar');
const menuBtn = document.querySelector('#menu-btn');

if (menuBtn) {
  menuBtn.onclick = () => {
    navbar.classList.toggle('active');
  };
}



// --- Alternar secciones en adopcion.html ---
function mostrarSeccion(seccion) {
  const ver = document.getElementById('seccion-ver');
  const registro = document.getElementById('seccion-registro');

  if (ver && registro) {
    ver.style.display = seccion === 'ver' ? 'block' : 'none';
    registro.style.display = seccion === 'registro' ? 'block' : 'none';
  }
      function enviarReporteMascota() {
      const datos = {
        tipo: document.getElementById('tipoMascota').value,
        nombre: document.getElementById('nombreMascota').value,
        especie: document.getElementById('especie').value,
        lugar: document.getElementById('lugarMascota').value,
        fecha: document.getElementById('fechaMascota').value,
        descripcion: document.getElementById('descMascota').value
      };
      alert("Reporte de mascota enviado:\n" + JSON.stringify(datos, null, 2));
    }

    function enviarReporteSituacion() {
      const datos = {
        tipo: document.getElementById('tipoSituacion').value,
        direccion: document.getElementById('direccion').value,
        fecha: document.getElementById('fechaSituacion').value,
           descripcion: document.getElementById('descSituacion').value
   };
      alert("Reporte de situación enviado:\n" + JSON.stringify(datos, null, 2));
    }

    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".form-content");

    tabs.forEach(tab => {
      tab.addEventListener("click", () => {
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));
        tab.classList.add("active");
        document.getElementById(tab.dataset.target).classList.add("active");
      });
    });

    // Carrusel functionality
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('carousel-images');
    const images = document.querySelectorAll('.carousel img');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicators = document.querySelectorAll('.carousel-indicator');
    
    let currentIndex = 0;
    const totalImages = images.length;
    
    // Configurar el ancho del carrusel
    carousel.style.width = `${totalImages * 100}%`;
    
    // Actualizar carrusel
    function updateCarousel() {
        carousel.style.transform = `translateX(-${currentIndex * (100 / totalImages)}%)`;
        
        // Actualizar indicadores
        indicators.forEach((indicator, index) => {
            if (index === currentIndex) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });
        
        // Reiniciar temporizador de auto-avance
        clearInterval(autoAdvance);
        autoAdvance = setInterval(nextImage, 5000);
    }
    
    // Avanzar a la siguiente imagen
    function nextImage() {
        currentIndex = (currentIndex + 1) % totalImages;
        updateCarousel();
    }
    
    // Retroceder a la imagen anterior
    function prevImage() {
        currentIndex = (currentIndex - 1 + totalImages) % totalImages;
        updateCarousel();
    }
    
    // Event listeners para botones
    nextBtn.addEventListener('click', nextImage);
    prevBtn.addEventListener('click', prevImage);
    
    // Event listeners para indicadores
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            currentIndex = index;
            updateCarousel();
        });
    });
    
    // Auto-avance cada 5 segundos
    let autoAdvance = setInterval(nextImage, 5000);
    
    // Pausar auto-avance al pasar el mouse sobre el carrusel
    carousel.addEventListener('mouseenter', () => {
        clearInterval(autoAdvance);
    });
    
    // Reanudar auto-avance al quitar el mouse del carrusel
    carousel.addEventListener('mouseleave', () => {
        autoAdvance = setInterval(nextImage, 5000);
    });
    
    // Inicializar carrusel
    updateCarousel();
});

}