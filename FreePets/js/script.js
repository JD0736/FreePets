// --- Menú hamburguesa ---
let navbar = document.querySelector('.navbar');
const menuBtn = document.querySelector('#menu-btn');

if (menuBtn) {
  menuBtn.onclick = () => {
    navbar.classList.toggle('active');
  };
}

// --- Carrusel de inicio ---
const track = document.getElementById("carousel-track");
const slides = document.querySelectorAll(".carousel-slide");
let currentIndex = 0;

if (track && slides.length > 0) {
  document.getElementById("nextBtn").addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % slides.length;
    updateCarousel();
  });

  document.getElementById("prevBtn").addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    updateCarousel();
  });

  function updateCarousel() {
    track.style.transform = `translateX(-${currentIndex * 100}%)`;
  }
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
    
}