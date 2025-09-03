// --- Menú hamburguesa ---
let navbar = document.querySelector('.navbar');
const menuBtn = document.querySelector('#menu-btn');

if (menuBtn) {
  menuBtn.onclick = () => {
    navbar.classList.toggle('active');
  };
}

// --- Sistema de tabs para login/registro ---
document.addEventListener('DOMContentLoaded', function() {
  const tabs = document.querySelectorAll(".tab");
  const contents = document.querySelectorAll(".form-content");

  if (tabs.length > 0) {
    tabs.forEach(tab => {
      tab.addEventListener("click", () => {
        tabs.forEach(t => t.classList.remove("active"));
        contents.forEach(c => c.classList.remove("active"));
        tab.classList.add("active");
        document.getElementById(tab.dataset.target).classList.add("active");
      });
    });
  }
});

// --- Alternar secciones en adopcion.html ---
function mostrarSeccion(seccion) {
  const ver = document.getElementById('seccion-ver');
  const registro = document.getElementById('seccion-registro');

  if (ver && registro) {
    ver.style.display = seccion === 'ver' ? 'block' : 'none';
    registro.style.display = seccion === 'registro' ? 'block' : 'none';
  }
}

// --- Funciones de reportes ---
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

// --- Carrusel functionality ---
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.getElementById('carousel-images');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const indicators = document.querySelectorAll('.carousel-indicator');
  
  if (!carousel) return;
  
  const slides = document.querySelectorAll('.carousel-slide');
  let currentIndex = 0;
  const totalSlides = slides.length;
  
  // Configurar el ancho del carrusel
  carousel.style.width = `${totalSlides * 100}%`;
  
  // Actualizar carrusel
  function updateCarousel() {
    carousel.style.transform = `translateX(-${currentIndex * (100 / totalSlides)}%)`;
    
    // Actualizar indicadores
    if (indicators.length > 0) {
      indicators.forEach((indicator, index) => {
        if (index === currentIndex) {
          indicator.classList.add('active');
        } else {
          indicator.classList.remove('active');
        }
      });
    }
    
    // Reiniciar temporizador de auto-avance
    clearInterval(autoAdvance);
    autoAdvance = setInterval(nextSlide, 5000);
  }
  
  // Avanzar a la siguiente slide
  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateCarousel();
  }
  
  // Retroceder a la slide anterior
  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    updateCarousel();
  }
  
  // Event listeners para botones
  if (nextBtn) nextBtn.addEventListener('click', nextSlide);
  if (prevBtn) prevBtn.addEventListener('click', prevSlide);
  
  // Event listeners para indicadores
  indicators.forEach((indicator, index) => {
    indicator.addEventListener('click', () => {
      currentIndex = index;
      updateCarousel();
    });
  });
  
  // Auto-avance cada 5 segundos
  let autoAdvance = setInterval(nextSlide, 5000);
  
  // Pausar auto-avance al pasar el mouse sobre el carrusel
  carousel.addEventListener('mouseenter', () => {
    clearInterval(autoAdvance);
  });
  
  // Reanudar auto-avance al quitar el mouse del carrusel
  carousel.addEventListener('mouseleave', () => {
    autoAdvance = setInterval(nextSlide, 5000);
  });
  
  // Inicializar carrusel
  updateCarousel();
});

// --- Navegación de cards de servicios ---
document.addEventListener('DOMContentLoaded', function() {
  const serviceCards = document.querySelectorAll('.service-item');
  
  serviceCards.forEach(card => {
    card.addEventListener('click', function(e) {
      // Evitar que se active si se hace clic en un enlace dentro de la card
      if (e.target.tagName === 'A' || e.target.closest('a')) return;
      
      const btn = this.querySelector('.btn');
      if (btn) {
        const link = btn.closest('a');
        if (link && link.href) {
          window.location.href = link.href;
        } else if (btn.textContent.includes('adopción')) {
          window.location.href = 'adopcion.html';
        } else if (btn.textContent.includes('Eventos')) {
          window.location.href = 'eventos.html';
        } else if (btn.textContent.includes('Reportar')) {
          window.location.href = 'reportes.html';
        } else if (btn.textContent.includes('Donar')) {
          window.location.href = 'donacion.html';
        }
      }
    });
  });
});

// --- Dropdown de servicios ---
document.addEventListener('DOMContentLoaded', function() {
  const dropdowns = document.querySelectorAll('.dropdown');
  
  dropdowns.forEach(dropdown => {
    const dropbtn = dropdown.querySelector('.dropbtn');
    
    if (dropbtn) {
      // Cerrar otros dropdowns al abrir uno
      dropbtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Cerrar otros dropdowns
        dropdowns.forEach(otherDropdown => {
          if (otherDropdown !== dropdown) {
            otherDropdown.querySelector('.dropdown-content').style.display = 'none';
          }
        });
        
        // Alternar el dropdown actual
        const content = this.nextElementSibling;
        if (content.style.display === 'block') {
          content.style.display = 'none';
        } else {
          content.style.display = 'block';
        }
      });
    }
  });
  
  // Cerrar dropdowns al hacer clic fuera
  document.addEventListener('click', function(e) {
    if (!e.target.matches('.dropbtn') && !e.target.closest('.dropdown')) {
      dropdowns.forEach(dropdown => {
        dropdown.querySelector('.dropdown-content').style.display = 'none';
      });
    }
  });

  // --- Funciones para popups ---
function mostrarPopup(mensaje, tipo) {
    const popup = document.getElementById('popup');
    const overlay = document.getElementById('overlay');
    
    if (popup && overlay) {
        // Configurar el contenido del popup
        const titulo = popup.querySelector('h3');
        const parrafo = popup.querySelector('p');
        const boton = popup.querySelector('.popup-btn');
        
        if (titulo) titulo.textContent = tipo === 'exito' ? '¡Éxito!' : 'Error';
        if (parrafo) parrafo.textContent = mensaje;
        
        // Configurar clases según el tipo
        popup.className = 'popup ' + tipo;
        boton.className = 'popup-btn ' + tipo;
        
        // Mostrar popup y overlay
        popup.style.display = 'block';
        overlay.style.display = 'block';
    }
}

function cerrarPopup() {
    const popup = document.getElementById('popup');
    const overlay = document.getElementById('overlay');
    
    if (popup && overlay) {
        popup.style.display = 'none';
        overlay.style.display = 'none';
    }
}

// Inicializar event listeners para popups
document.addEventListener('DOMContentLoaded', function() {
    // Cerrar popup al hacer clic en el overlay
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.addEventListener('click', cerrarPopup);
    }
    
    // Cerrar popup con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarPopup();
        }
    });
    
    // Asignar la función cerrarPopup al botón del popup
    const popupBoton = document.querySelector('.popup-btn');
    if (popupBoton) {
        popupBoton.addEventListener('click', cerrarPopup);
    }
    
    // Mostrar popup automáticamente si hay parámetros en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');
    const tipo = urlParams.get('tipo');
    
    if (mensaje && tipo) {
        mostrarPopup(decodeURIComponent(mensaje), tipo);
    }
});

// --- Validación del formulario de contacto ---
function validarFormularioContacto() {
    const nombre = document.getElementById('nombre');
    const email = document.getElementById('email');
    const mensaje = document.getElementById('mensaje');
    
    let valido = true;
    
    // Validar nombre
    if (nombre.value.trim() === '') {
        mostrarError(nombre, 'Por favor ingrese su nombre');
        valido = false;
    } else {
        limpiarError(nombre);
    }
    
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
        mostrarError(email, 'Por favor ingrese un email válido');
        valido = false;
    } else {
        limpiarError(email);
    }
    
    // Validar mensaje
    if (mensaje.value.trim() === '') {
        mostrarError(mensaje, 'Por favor ingrese su mensaje');
        valido = false;
    } else {
        limpiarError(mensaje);
    }
    
    return valido;
}

function mostrarError(campo, mensaje) {
    limpiarError(campo);
    campo.style.borderColor = '#F44336';
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-mensaje';
    errorDiv.style.color = '#F44336';
    errorDiv.style.fontSize = '14px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = mensaje;
    campo.parentNode.appendChild(errorDiv);
}

function limpiarError(campo) {
    campo.style.borderColor = '#ccc';
    const errorDiv = campo.parentNode.querySelector('.error-mensaje');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Inicializar validación del formulario de contacto
document.addEventListener('DOMContentLoaded', function() {
    const formularioContacto = document.querySelector('.contact-form form');
    if (formularioContacto) {
        formularioContacto.addEventListener('submit', function(e) {
            if (!validarFormularioContacto()) {
                e.preventDefault();
            }
        });
    }
    
    // Ocultar alerta automáticamente después de 5 segundos
    const alerta = document.getElementById('contact-alert');
    if (alerta) {
        setTimeout(function() {
            alerta.style.display = 'none';
        }, 5000);
    }
});

});