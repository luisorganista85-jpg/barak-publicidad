(function () {
  const root = document.documentElement;
  const themeButton = document.querySelector('[data-theme-toggle]');
  let mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  root.setAttribute('data-theme', mode);

  const renderIcon = () => {
    if (!themeButton) return;
    themeButton.innerHTML = mode === 'dark'
      ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>'
      : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
  };

  renderIcon();

  themeButton?.addEventListener('click', () => {
    mode = mode === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', mode);
    renderIcon();
  });

  const menuButton = document.querySelector('.menu-toggle');
  const menu = document.querySelector('#main-menu');

  menuButton?.addEventListener('click', () => {
    const expanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!expanded));
    menu?.classList.toggle('open');
  });

  menu?.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      menu.classList.remove('open');
      menuButton?.setAttribute('aria-expanded', 'false');
    });
  });

 const tabButtons = [...document.querySelectorAll('.tab-btn')];
  const cards = [...document.querySelectorAll('.catalog-card')];

  tabButtons.forEach(btn => btn.addEventListener('click', () => {
    tabButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    const filter = btn.dataset.filter;

    cards.forEach(card => {
      const category = card.dataset.category;
      const show = filter === 'todos' || category === filter;
      
      if (show) {
        card.style.display = 'flex'; 
        card.style.opacity = '0';
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transition = 'opacity 0.4s ease';
        }, 10);
      } else {
        card.style.display = 'none';
      }
    });
  }));

  const faq = document.querySelector('#faq-box');
  const faqHead = faq?.querySelector('.faq-head');

  faqHead?.addEventListener('click', () => {
    const open = faq.classList.toggle('open');
    faqHead.setAttribute('aria-expanded', String(open));
    if (faqHead.lastElementChild) faqHead.lastElementChild.textContent = open ? '−' : '+';
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('is-visible');
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));
})();

const lightbox = document.querySelector('#lightbox');
const lightboxImage = document.querySelector('#lightboxImage');
const lightboxClose = document.querySelector('#lightboxClose');
const referenceImages = document.querySelectorAll('.references-image');

referenceImages.forEach(img => {
  img.addEventListener('click', () => {
    if (!lightbox || !lightboxImage) return;
    lightboxImage.src = img.src;
    lightboxImage.alt = img.alt || 'Vista ampliada de referencia';
    lightbox.classList.add('active');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  });
});

const closeLightbox = () => {
  if (!lightbox || !lightboxImage) return;
  lightbox.classList.remove('active');
  lightbox.setAttribute('aria-hidden', 'true');
  lightboxImage.src = '';
  document.body.style.overflow = '';
};

lightboxClose?.addEventListener('click', closeLightbox);

lightbox?.addEventListener('click', (e) => {
  if (e.target === lightbox) {
    closeLightbox();
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && lightbox?.classList.contains('active')) {
    closeLightbox();
  }
});

const referencesTrack = document.querySelector('#referencesTrack');
const referencesPrev = document.querySelector('.references-btn-prev');
const referencesNext = document.querySelector('.references-btn-next');

if (referencesTrack) {
  const getCardWidth = () => {
    const firstCard = referencesTrack.querySelector('.references-card');
    if (!firstCard) return 340;
    const styles = window.getComputedStyle(referencesTrack);
    const gap = parseInt(styles.gap || 16, 10);
    return firstCard.offsetWidth + gap;
  };

  const moveReferences = (direction = 1) => {
    const step = getCardWidth();
    const maxScroll = referencesTrack.scrollWidth - referencesTrack.clientWidth;

    if (direction === 1 && referencesTrack.scrollLeft + step >= maxScroll - 5) {
      referencesTrack.scrollTo({ left: 0, behavior: 'smooth' });
      return;
    }

    if (direction === -1 && referencesTrack.scrollLeft <= 5) {
      referencesTrack.scrollTo({ left: maxScroll, behavior: 'smooth' });
      return;
    }

    referencesTrack.scrollBy({
      left: direction * step,
      behavior: 'smooth'
    });
  };

  referencesPrev?.addEventListener('click', () => moveReferences(-1));
  referencesNext?.addEventListener('click', () => moveReferences(1));

  let autoReferences = setInterval(() => moveReferences(1), 3000);

  const stopAutoReferences = () => clearInterval(autoReferences);
  const startAutoReferences = () => {
    stopAutoReferences();
    autoReferences = setInterval(() => moveReferences(1), 3000);
  };

  referencesTrack.addEventListener('mouseenter', stopAutoReferences);
  referencesTrack.addEventListener('mouseleave', startAutoReferences);
  referencesTrack.addEventListener('touchstart', stopAutoReferences, { passive: true });
  referencesTrack.addEventListener('touchend', startAutoReferences);

  referencesPrev?.addEventListener('click', startAutoReferences);
  referencesNext?.addEventListener('click', startAutoReferences);
}
// Usamos una variable global para no buscar el elemento cada vez (más rápido)
let capaCacheNode = null;

function cambiarColor(colorElegido) {
    // Si la caché está vacía, buscamos el elemento una sola vez
    if (!capaCacheNode) {
        capaCacheNode = document.getElementById("capaColor");
    }

    if (!capaCacheNode) {
        console.error("No se encontró el elemento con id 'capaColor'");
        return;
    }

    // Cambiamos el color de forma DIRECTA y RÁPIDA
    // mix-blend-mode: screen en el CSS hará el resto.

    if (colorElegido === 'rojo') {
        // Rojo intenso, usando RGBA para controlar la saturación sobre el negro.
        // mix-blend-mode: screen hará el resto.
        capaCacheNode.style.backgroundColor = "rgba(230, 33, 23, 0.9)";
    } 
    else if (colorElegido === 'rosa') {
        capaCacheNode.style.backgroundColor = "rgba(246, 183, 197, 0.8)";
    }
    else if (colorElegido === 'verde') {
        capaCacheNode.style.backgroundColor = "rgba(18, 140, 70, 0.8)";
    }
    else if (colorElegido === 'vino') {
        capaCacheNode.style.backgroundColor = "rgba(128, 24, 44, 0.9)";
    }
    else if (colorElegido === 'morado') {
        capaCacheNode.style.backgroundColor = "rgba(94, 23, 138, 0.85)";
    }
    else if (colorElegido === 'amarillo') {
        // El amarillo necesita ser muy intenso para verse sobre negro
        capaCacheNode.style.backgroundColor = "rgba(255, 220, 17, 0.95)";
    }
    else if (colorElegido === 'azulrey') {
        capaCacheNode.style.backgroundColor = "rgba(30, 92, 194, 0.85)";
    }
    else if (colorElegido === 'naranja') {
        capaCacheNode.style.backgroundColor = "rgba(255, 138, 17, 0.9)";
    }
    else if (colorElegido === 'azulmarino') {
        // Los colores oscuros necesitan menos intensidad o se verán negros
        capaCacheNode.style.backgroundColor = "rgba(28, 60, 111, 0.7)";
    }
    else if (colorElegido === 'fucsia') {
        capaCacheNode.style.backgroundColor = "rgba(219, 45, 161, 0.8)";
    }
    else if (colorElegido === 'azulcielo') {
        capaCacheNode.style.backgroundColor = "rgba(142, 202, 230, 0.8)";
    }
    else if (colorElegido === 'gris') {
        capaCacheNode.style.backgroundColor = "rgba(177, 177, 177, 0.75)";
    }
    else if (colorElegido === 'negro') {
        // Volvemos a la playera negra original
        capaCacheNode.style.backgroundColor = "transparent";
    }
    else {
        capaCacheNode.style.backgroundColor = "transparent";
    }
}
document.addEventListener('DOMContentLoaded', function() {
  const slides = document.querySelectorAll('#bvScreen .bv-slide');
  const dotsEl = document.getElementById('bvDots');
  const fill   = document.getElementById('bvFill');
  const timeEl = document.getElementById('bvTime');
  const icon   = document.getElementById('bvIcon');
  const bigIcon= document.getElementById('bvBigIcon');
  const center = document.getElementById('bvCenter');
  const titleEl= document.getElementById('bvTitle');
  const subEl  = document.getElementById('bvSub');
  const audio  = document.getElementById('bvAudio');
  
  // Si no encuentra los elementos, no hace nada para no dar error
  if (!slides.length || !audio) return;

  const SPEED  = 5000;
  let cur=0, playing=true, ticker=null, prog=null, elapsed=0, muted=true, cTO=null;

  // Crear puntitos
  slides.forEach((_,i)=>{
    const d=document.createElement('button');
    d.className='bv-dot'+(i===0?' on':'');
    d.onclick=function(e){ e.stopPropagation(); goTo(i); };
    dotsEl.appendChild(d);
  });

  function goTo(n){
    slides[cur].classList.remove('active');
    if(dotsEl.children[cur]) dotsEl.children[cur].classList.remove('on');
    cur=(n+slides.length)%slides.length;
    slides[cur].classList.add('active');
    if(dotsEl.children[cur]) dotsEl.children[cur].classList.add('on');
    titleEl.textContent=slides[cur].dataset.title||'';
    subEl.textContent=slides[cur].dataset.sub||'';
    elapsed=0; fill.style.width='0%';
  }

  function startProg(){
    clearInterval(prog);
    prog=setInterval(()=>{
      elapsed+=100;
      fill.style.width=Math.min((elapsed/SPEED)*100,100)+'%';
      timeEl.textContent=Math.max(Math.ceil((SPEED-elapsed)/1000),0)+'s';
    },100);
  }

  function startTicker(){
    clearInterval(ticker);
    ticker=setInterval(()=>{ goTo(cur+1); startProg(); },SPEED);
  }

  window.bvTogglePlay=function(){
    playing=!playing;
    const pause='<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>';
    const play='<path d="M8 5v14l11-7z"/>';
    icon.innerHTML = playing ? pause : play;
    if(bigIcon) bigIcon.innerHTML = playing ? pause : play;
    
    if(playing){
      startProg(); startTicker();
      if(!muted) audio.play().catch(()=>{});
    } else {
      clearInterval(ticker); clearInterval(prog);
      audio.pause();
    }
    if(center) {
        center.classList.add('show');
        clearTimeout(cTO);
        cTO=setTimeout(()=>center.classList.remove('show'),1200);
    }
  };

  window.bvToggleMute=function(){
    muted=!muted;
    audio.muted=muted;
    if(!muted && playing) audio.play().catch(()=>{});
    document.getElementById('bvMusicLabel').textContent=muted?'🔇 Sin sonido':'♪ Música de fondo';
  };

  // --- ARRANQUE FORZADO ---
  audio.muted = true;
  muted = true;
  playing = true;
  
  // Esto inicia el carrusel
  goTo(0);
  startProg();
  startTicker();
  
  // Intenta darle play al audio silenciado
  audio.play().catch(()=>{});
});
// Variable para guardar la referencia y que sea más rápido
let cacheCapaGorra = null;

function cambiarColorGorra(nombreColor) {
    // Si no tenemos la capa guardada, la buscamos
    if (!cacheCapaGorra) {
        cacheCapaGorra = document.getElementById("capaColorGorra");
    }

    if (!cacheCapaGorra) return;

    // Diccionario de colores RGBA (Red, Green, Blue, Alpha)
    const paletaGorra = {
        'rojo': "rgba(230, 33, 23, 0.8)",
        'rosa': "rgba(246, 183, 197, 0.7)",
        'verde': "rgba(18, 140, 70, 0.8)",
        'vino': "rgba(128, 24, 44, 0.9)",
        'morado': "rgba(94, 23, 138, 0.8)",
        'amarillo': "rgba(255, 220, 17, 0.9)",
        'azulrey': "rgba(30, 92, 194, 0.8)",
        'naranja': "rgba(255, 138, 17, 0.9)",
        'azulmarino': "rgba(28, 60, 111, 0.7)",
        'fucsia': "rgba(219, 45, 161, 0.8)",
        'negro': "transparent" // Vuelve al color original
    };

    // Aplicar el color o transparente si no existe
    cacheCapaGorra.style.backgroundColor = paletaGorra[nombreColor] || "transparent";
}
// Variable para guardar la referencia de las dos capas y que sea más rápido
let cacheSoft = null;
let cacheRelleno = null;
function cambiarColorChamarra(color) {
    const capa1 = document.getElementById('capaSoft');
    const capa2 = document.getElementById('capaRelleno');

    const colores = {
        'negro': 'transparent',
        'azulmarino': 'rgba(20, 30, 70, 0.7)',
        'gris': 'rgba(120, 120, 120, 0.6)',
        'rojo': 'rgba(200, 0, 0, 0.6)',
        'guinda': 'rgba(100, 0, 20, 0.7)',
        'azulreal': 'rgba(0, 50, 200, 0.6)'
    };

    const colorFinal = colores[color] || 'transparent';

    capa1.style.backgroundColor = colorFinal;
    capa2.style.backgroundColor = colorFinal;
}

   let seleccionActual = "";

// 1. Esta función sirve para resaltar el botón en CUALQUIER tarjeta
function setMedida(elemento, valor) {
    // Quita el color rosa de todos los botones de la página
    document.querySelectorAll('.option-btn').forEach(btn => btn.classList.remove('active'));
    
    // Pone el color rosa solo al que tocaste
    elemento.classList.add('active');
    
    // Guarda la medida y el precio
    seleccionActual = valor;
}

// 2. Esta función ahora recibe el "tipo" (rect o cuad) para saber qué archivo enviar
function enviarWhatsApp(tipo) {
    // Busca el diseño y el archivo según la tarjeta donde hiciste clic
    const servicio = document.getElementById('diseno-' + tipo).value;
    const archivoInput = document.getElementById('file-' + tipo);
    const tieneArchivo = archivoInput && archivoInput.files.length > 0;
    
    const medidaFinal = seleccionActual;

    if (!medidaFinal) {
        alert("Por favor selecciona una medida de la lista.");
        return;
    }

    let mensajeArchivo = tieneArchivo 
        ? "%0A%0A*Nota:* He seleccionado mi archivo para adjuntar. 📎" 
        : "";
    
    // Identifica si es Rectangular o Cuadrado para el mensaje
    const textoTipo = (tipo === 'rect') ? "Rectangular" : "Cuadrado";
    
    const mensaje = `Hola BARAK, me interesa un Cuadro Canvas ${textoTipo}:%0A- *Medida:* ${medidaFinal}%0A- *Servicio:* ${servicio}${mensajeArchivo}`;
    
    window.open(`https://wa.me/5632971001?text=${mensaje}`, '_blank');
}
let pedidoCajas = [];

function setPromo(elemento, nombre, precio) {
    let productoExistente = pedidoCajas.find(item => item.nombre === nombre);

    if (productoExistente) {
        productoExistente.cantidad += 1;
    } else {
        pedidoCajas.push({ nombre: nombre, precio: precio, cantidad: 1 });
        elemento.classList.add('selected'); // Activa el borde rosa
    }
    actualizarResumen();
}

// Esta función permite quitar piezas con clic derecho sin botones extra
function restarPromo(event, nombre) {
    event.preventDefault(); 
    let index = pedidoCajas.findIndex(item => item.nombre === nombre);

    if (index > -1) {
        pedidoCajas[index].cantidad -= 1;
        
        if (pedidoCajas[index].cantidad <= 0) {
            pedidoCajas.splice(index, 1);
            // Buscamos el botón para quitarle el borde rosa
            const btn = document.querySelector(`button[onclick*="'${nombre}'"]`);
            if(btn) btn.classList.remove('selected');
        }
    }
    actualizarResumen();
}

function actualizarResumen() {
    const totalPiezas = pedidoCajas.reduce((total, item) => total + item.cantidad, 0);
    const resumen = document.getElementById('resumen-pedido');
    
    if (totalPiezas > 0) {
        // Mensaje discreto en rosa que no estorba al diseño
        resumen.innerHTML = `📦 Total de piezas: ${totalPiezas} <br> <small style="font-weight:400; opacity:0.7;">(Clic derecho para restar)</small>`;
    } else {
        resumen.innerHTML = "";
    }
}
function enviarWhatsAppPromo(id) {
    if (pedidoCajas.length === 0) {
        alert("⚠️ Selecciona al menos una medida antes de cotizar.");
        return;
    }

    let mensaje = "Hola BARAK, quiero cotizar las siguientes Cajas de Luz:%0A%0A";
    pedidoCajas.forEach((item) => {
        mensaje += `• ${item.nombre} x${item.cantidad} pieza(s)%0A`;
    });
    mensaje += `%0A*Adjunto los detalles para mi pedido.*`;

    const numeroTel = "5632971001"; 
    const url = `https://wa.me/${numeroTel}?text=${mensaje}`;

    // 1. Intentamos abrir WhatsApp
    const nuevaVentana = window.open(url, '_blank');

    // 2. EL TRUCO PARA "REGRESAR"
    // Si la pestaña se abrió, le pedimos al navegador que regrese el foco a nuestra web
    if (nuevaVentana) {
        setTimeout(() => {
            window.focus(); // Esto intenta traer tu página al frente de nuevo
        }, 1000); 
    }

    // 3. LIMPIEZA PARA SEGUIR NAVEGANDO
    pedidoCajas = []; 
    const resumen = document.getElementById('resumen-pedido');
    if (resumen) resumen.innerHTML = "";
    document.querySelectorAll('.option-btn.selected').forEach(btn => {
        btn.classList.remove('selected');
    });
}
// Variable separada para Roll up
let pedidoRollup = [];

function setPromoRollup(elemento, nombre, precio) {
    let productoExistente = pedidoRollup.find(item => item.nombre === nombre);

    if (productoExistente) {
        // Sumar pieza
        productoExistente.cantidad += 1;
    } else {
        // Agregar nuevo
        pedidoRollup.push({ nombre: nombre, precio: precio, cantidad: 1 });
        elemento.classList.add('selected'); // Activa el borde rosa
    }
    actualizarResumenRollup();
}

// Clic derecho para restar piezas
function restarPromoRollup(event, nombre) {
    event.preventDefault(); 
    let index = pedidoRollup.findIndex(item => item.nombre === nombre);

    if (index > -1) {
        pedidoRollup[index].cantidad -= 1;
        
        if (pedidoRollup[index].cantidad <= 0) {
            pedidoRollup.splice(index, 1);
            // Quitar borde rosa
            const btn = document.querySelector(`button[onclick*="'${nombre}'"]`);
            if(btn) btn.classList.remove('selected');
        }
    }
    actualizarResumenRollup();
}

function actualizarResumenRollup() {
    const totalPiezas = pedidoRollup.reduce((total, item) => total + item.cantidad, 0);
    // IMPORTANTE: ID diferente para el mensaje rosa de Roll up
    const resumen = document.getElementById('resumen-pedido-rollup');
    
    if (resumen) {
        if (totalPiezas > 0) {
            resumen.innerHTML = `📦 Total de piezas: ${totalPiezas} <br> <small style="font-weight:400; opacity:0.7;">(Clic derecho para restar)</small>`;
        } else {
            resumen.innerHTML = "";
        }
    }
}

// Función de WhatsApp para Roll up
function enviarWhatsAppRollup(id) {
    if (pedidoRollup.length === 0) {
        alert("⚠️ Selecciona al menos una medida antes de cotizar.");
        return;
    }

    let mensaje = "Hola BARAK, quiero cotizar los siguientes Roll ups exhibidores:%0A%0A";
    let granTotal = 0;

    pedidoRollup.forEach((item) => {
        let subtotal = item.precio * item.cantidad;
        granTotal += subtotal;
        mensaje += `• ${item.nombre} x${item.cantidad} pza(s) - $${subtotal} MXN%0A`;
    });

    mensaje += `%0A*TOTAL ESTIMADO: $${granTotal} MXN*%0A%0A*Adjunto los detalles para mi pedido.* ✨`;

    const numeroTel = "5632971001"; 
    window.open(`https://wa.me/${numeroTel}?text=${mensaje}`, '_blank');

    // Limpieza automática para BARAK (Opción de limpiar variables)
    pedidoRollup = []; 
    actualizarResumenRollup();
    document.querySelectorAll('#prod-rollup .option-btn.selected').forEach(btn => {
        btn.classList.remove('selected');
    });
}
let pedidoStand = [];

function setPromoStand(elemento, nombre, precio) {
    let productoExistente = pedidoStand.find(item => item.nombre === nombre);
    if (productoExistente) {
        productoExistente.cantidad += 1;
    } else {
        pedidoStand.push({ nombre, precio, cantidad: 1 });
        elemento.classList.add('selected');
    }
    actualizarResumenStand();
}

function restarPromoStand(event, nombre) {
    event.preventDefault(); 
    let index = pedidoStand.findIndex(item => item.nombre === nombre);
    if (index > -1) {
        pedidoStand[index].cantidad -= 1;
        if (pedidoStand[index].cantidad <= 0) {
            pedidoStand.splice(index, 1);
            const btn = document.querySelector(`button[onclick*="'${nombre}'"]`);
            if(btn) btn.classList.remove('selected');
        }
    }
    actualizarResumenStand();
}

function actualizarResumenStand() {
    const total = pedidoStand.reduce((t, i) => t + i.cantidad, 0);
    const resumen = document.getElementById('resumen-pedido-stand');
    if (resumen) {
        resumen.innerHTML = total > 0 ? `📦 Total de piezas: ${total} <br> <small style="font-weight:400; opacity:0.7;">(Clic derecho para restar)</small>` : "";
    }
}

function enviarWhatsAppStand(id) {
    if (pedidoStand.length === 0) {
        alert("⚠️ Selecciona un tipo de Stand primero.");
        return;
    }

    let mensaje = "Hola BARAK, quiero cotizar estos Stands de exhibición:%0A%0A";
    pedidoStand.forEach(i => mensaje += `• ${i.nombre} x${i.cantidad} pza(s)%0A`);
    mensaje += `%0A*Adjunto los detalles para mi pedido.* ✨`;

    window.open(`https://wa.me/5632971001?text=${mensaje}`, '_blank');

    // Limpiamos todo para que el cliente siga navegando en BARAK
    pedidoStand = [];
    actualizarResumenStand();
    document.querySelectorAll('#prod-stand .option-btn.selected').forEach(b => b.classList.remove('selected'));
}
let pedidoMarco = [];

function setPromoMarco(elemento, nombre, precio) {
    let productoExistente = pedidoMarco.find(item => item.nombre === nombre);
    if (productoExistente) {
        productoExistente.cantidad += 1;
    } else {
        pedidoMarco.push({ nombre, precio, cantidad: 1 });
        elemento.classList.add('selected');
    }
    actualizarResumenMarco();
}

function restarPromoMarco(event, nombre) {
    event.preventDefault(); 
    let index = pedidoMarco.findIndex(item => item.nombre === nombre);
    if (index > -1) {
        pedidoMarco[index].cantidad -= 1;
        if (pedidoMarco[index].cantidad <= 0) {
            pedidoMarco.splice(index, 1);
            const btn = document.querySelector(`button[onclick*="'${nombre}'"]`);
            if(btn) btn.classList.remove('selected');
        }
    }
    actualizarResumenMarco();
}

function actualizarResumenMarco() {
    const total = pedidoMarco.reduce((t, i) => t + i.cantidad, 0);
    const resumen = document.getElementById('resumen-pedido-marco');
    if (resumen) {
        resumen.innerHTML = total > 0 ? `📦 Total de piezas: ${total} <br> <small style="font-weight:400; opacity:0.7;">(Clic derecho para restar)</small>` : "";
    }
}

function enviarWhatsAppMarco(id) {
    if (pedidoMarco.length === 0) {
        alert("⚠️ Selecciona una medida de Marco primero.");
        return;
    }

    let mensaje = "Hola BARAK, quiero cotizar los siguientes Marcos de Luz:%0A%0A";
    pedidoMarco.forEach(i => mensaje += `• ${i.nombre} x${i.cantidad} pza(s)%0A`);
    mensaje += `%0A*Adjunto los detalles para mi pedido.* ✨`;

    window.open(`https://wa.me/5632971001?text=${mensaje}`, '_blank');

    // Resetear para seguir navegando
    pedidoMarco = [];
    actualizarResumenMarco();
    document.querySelectorAll('#prod-marcoluz .option-btn.selected').forEach(b => b.classList.remove('selected'));
}
let pedidoGiratoria = [];

function setPromoGiratoria(elemento, nombre, precio) {
    let productoExistente = pedidoGiratoria.find(item => item.nombre === nombre);
    if (productoExistente) {
        productoExistente.cantidad += 1;
    } else {
        pedidoGiratoria.push({ nombre, precio, cantidad: 1 });
        elemento.classList.add('selected');
    }
    actualizarResumenGiratoria();
}

function restarPromoGiratoria(event, nombre) {
    event.preventDefault(); 
    let index = pedidoGiratoria.findIndex(item => item.nombre === nombre);
    if (index > -1) {
        pedidoGiratoria[index].cantidad -= 1;
        if (pedidoGiratoria[index].cantidad <= 0) {
            pedidoGiratoria.splice(index, 1);
            const btn = document.querySelector(`button[onclick*="'${nombre}'"]`);
            if(btn) btn.classList.remove('selected');
        }
    }
    actualizarResumenGiratoria();
}

function actualizarResumenGiratoria() {
    const total = pedidoGiratoria.reduce((t, i) => t + i.cantidad, 0);
    const resumen = document.getElementById('resumen-pedido-giratoria');
    if (resumen) {
        resumen.innerHTML = total > 0 ? `📦 Total de piezas: ${total} <br> <small style="font-weight:400; opacity:0.7;">(Clic derecho para restar)</small>` : "";
    }
}

function enviarWhatsAppGiratoria(id) {
    if (pedidoGiratoria.length === 0) {
        alert("⚠️ Selecciona una medida primero.");
        return;
    }

    let mensaje = "Hola BARAK, quiero cotizar la Caja de luz giratoria:%0A%0A";
    pedidoGiratoria.forEach(i => mensaje += `• ${i.nombre} x${i.cantidad} pza(s)%0A`);
    mensaje += `%0A*Adjunto los detalles para mi pedido.* ✨`;

    window.open(`https://wa.me/5632971001?text=${mensaje}`, '_blank');

    // Limpieza automática para seguir navegando
    pedidoGiratoria = [];
    actualizarResumenGiratoria();
    document.querySelectorAll('#prod-giratoria .option-btn.selected').forEach(b => b.classList.remove('selected'));
}
let pedidoPoster = [];

// Nombre unificado para que el HTML lo encuentre
function setPoster(elemento, nombre, precio) {
    let productoExistente = pedidoPoster.find(item => item.nombre === nombre);
    if (productoExistente) {
        productoExistente.cantidad += 1;
    } else {
        pedidoPoster.push({ nombre, precio, cantidad: 1 });
        elemento.classList.add('selected');
    }
    actualizarResumenPoster();
}

function restarPoster(event, nombre) {
    event.preventDefault(); 
    let index = pedidoPoster.findIndex(item => item.nombre === nombre);
    if (index > -1) {
        pedidoPoster[index].cantidad -= 1;
        if (pedidoPoster[index].cantidad <= 0) {
            pedidoPoster.splice(index, 1);
            // Busca cualquier botón que tenga el nombre del producto para quitar el borde rosa
            const btn = document.querySelector(`button[onclick*="${nombre}"]`);
            if(btn) btn.classList.remove('selected');
        }
    }
    actualizarResumenPoster();
}

function actualizarResumenPoster() {
    const total = pedidoPoster.reduce((t, i) => t + i.cantidad, 0);
    const resumen = document.getElementById('resumen-pedido-poster');
    
    if (resumen) {
        if (total > 0) {
            resumen.style.display = "block";
            // Estilos aplicados directamente para asegurar que se vea pequeño y centrado
            resumen.innerHTML = `
                <div style="text-align: center; margin-top: 10px;">
                    <span style="font-size: 10px; font-weight: 800; color: #e91e63;">
                        📦 Total de piezas: ${total}
                    </span>
                    <br>
                    <span style="font-size: 8px; font-weight: 400; color: #e91e63; opacity: 0.7;">
                        (Clic derecho para restar)
                    </span>
                </div>
            `;
        } else {
            resumen.style.display = "none";
        }
    }
}

function enviarWhatsAppPoster() {
    if (pedidoPoster.length === 0) {
        alert("⚠️ Selecciona una medida primero.");
        return;
    }

    let mensaje = "Hola BARAK, quiero cotizar el siguiente Display poster stand:%0A%0A";
    pedidoPoster.forEach(i => mensaje += `• ${i.nombre} x${i.cantidad} pza(s)%0A`);
    mensaje += `%0A*Adjunto los detalles para mi pedido.* ✨`;

    window.open(`https://wa.me/5632971001?text=${mensaje}`, '_blank');

    // Limpieza total
    pedidoPoster = [];
    actualizarResumenPoster();
    document.querySelectorAll('.selected').forEach(b => b.classList.remove('selected'));
}
let pedidoCanvas = {};

function setCanvas(elemento, medida, precio) {
    if (!pedidoCanvas[medida]) {
        pedidoCanvas[medida] = { cantidad: 0, precio: precio };
    }
    pedidoCanvas[medida].cantidad++;
    
    // Activa visualmente el botón
    elemento.classList.add('selected');
    elemento.blur(); // Limpia bordes de enfoque del navegador
    actualizarResumenCanvas();
}

function restarCanvas(event, medida, elemento) {
    event.preventDefault(); // Bloquea el menú del clic derecho
    if (pedidoCanvas[medida] && pedidoCanvas[medida].cantidad > 0) {
        pedidoCanvas[medida].cantidad--;
        
        // Si llega a 0 piezas, quitamos el color rosa del botón
        if (pedidoCanvas[medida].cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    elemento.blur();
    actualizarResumenCanvas();
}

function actualizarResumenCanvas() {
    const resumenDiv = document.getElementById('resumen-pedido-canvas');
    let totalPiezas = 0;
    let totalPrecio = 0;

    for (let m in pedidoCanvas) {
        if (pedidoCanvas[m].cantidad > 0) {
            totalPiezas += pedidoCanvas[m].cantidad;
            totalPrecio += (pedidoCanvas[m].cantidad * pedidoCanvas[m].precio);
        }
    }

    if (totalPiezas > 0) {
        resumenDiv.innerHTML = `📦 Total de piezas: ${totalPiezas}<br><span style="font-size:9px; font-weight:400;">(Clic derecho para restar)</span>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}
function enviarWhatsAppCanvas() {
    // Sustituye por tu número de WhatsApp de BARAK
    const telefono = "5632971001"; 
    let mensaje = "¡Hola! Me interesan los siguientes Cuadros Canvas:\n\n";
    let tieneProductos = false;
    let totalPiezasGlobal = 0;

    // Recorremos el pedido para listar cada medida y sumar el total
    for (let medida in pedidoCanvas) {
        if (pedidoCanvas[medida].cantidad > 0) {
            mensaje += `- ${medida}: ${pedidoCanvas[medida].cantidad} pieza(s)\n`;
            totalPiezasGlobal += pedidoCanvas[medida].cantidad;
            tieneProductos = true;
        }
    }

    if (!tieneProductos) {
        alert("Por favor, selecciona al menos una medida antes de cotizar.");
        return;
    }

    // Agregamos el total de piezas y el cierre igual a tus otras tarjetas
    mensaje += `\n📦 Total de piezas: ${totalPiezasGlobal}`;
    mensaje += "\n\n¿Me podrían dar más información?";

    const url = `https://wa.me/${5632971001}?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}
let pedidoCuadrado = {};

function setCanvasCuadrado(elemento, medida, precio) {
    if (!pedidoCuadrado[medida]) {
        pedidoCuadrado[medida] = { cantidad: 0, precio: precio };
    }
    pedidoCuadrado[medida].cantidad++;
    
    elemento.classList.add('selected'); // Activa el color rosa
    elemento.blur(); // Quita el borde de foco del navegador
    actualizarResumenCuadrado();
}

function restarCanvasCuadrado(event, medida, elemento) {
    event.preventDefault(); // Bloquea el menú derecho
    if (pedidoCuadrado[medida] && pedidoCuadrado[medida].cantidad > 0) {
        pedidoCuadrado[medida].cantidad--;
        
        if (pedidoCuadrado[medida].cantidad === 0) {
            elemento.classList.remove('selected'); // Quita el color al llegar a 0
        }
    }
    elemento.blur();
    actualizarResumenCuadrado();
}

function actualizarResumenCuadrado() {
    const resumenDiv = document.getElementById('resumen-canvas-cuadrado');
    let totalPiezas = 0;

    for (let m in pedidoCuadrado) {
        totalPiezas += pedidoCuadrado[m].cantidad;
    }

    if (totalPiezas > 0) {
        resumenDiv.innerHTML = `📦 Total de piezas: ${totalPiezas}<br><span style="font-size:9px; font-weight:400;">(Clic derecho para restar)</span>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

function enviarWhatsAppCuadrado() {
    const telefono = "5632971001"; 
    let mensaje = "¡Hola! Me interesan los siguientes Cuadros Canvas Cuadrados:\n\n";
    let tieneProductos = false;
    let totalPiezas = 0;

    for (let m in pedidoCuadrado) {
        if (pedidoCuadrado[m].cantidad > 0) {
            mensaje += `- ${m}: ${pedidoCuadrado[m].cantidad} pieza(s)\n`;
            totalPiezas += pedidoCuadrado[m].cantidad;
            tieneProductos = true;
        }
    }

    if (!tieneProductos) {
        alert("Por favor, selecciona al menos una medida.");
        return;
    }

    mensaje += `\n📦 Total de piezas: ${totalPiezas}\n\n¿Me podrían dar más información?`;
    window.open(`https://wa.me/${5632971001}?text=${encodeURIComponent(mensaje)}`, '_blank');
}
// --- SECCIÓN DE GORRAS BARAK ---
let pedidoGorra = { cantidad: 0, precio: 100 };

function setGorra(elemento, nombre, precio) {
    pedidoGorra.cantidad++;
    elemento.classList.add('selected'); 
    elemento.blur(); 
    actualizarResumenGorra();
}

function restarGorra(event, nombre, elemento) {
    event.preventDefault();
    if (pedidoGorra.cantidad > 0) {
        pedidoGorra.cantidad--;
        if (pedidoGorra.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    elemento.blur();
    actualizarResumenGorra();
}

function actualizarResumenGorra() {
    const resumenDiv = document.getElementById('resumen-gorras');
    if (pedidoGorra.cantidad > 0) {
        resumenDiv.innerHTML = `📦 Total de piezas: ${pedidoGorra.cantidad}<br><span style="font-size:9px; font-weight:400;">(Clic derecho para restar)</span>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

function enviarWhatsAppGorra() {
    if (pedidoGorra.cantidad === 0) {
        alert("Por favor, selecciona al menos una gorra.");
        return;
    }

    let mensaje = "¡Hola! Me interesa cotizar gorras personalizadas:\n\n";
    mensaje += `- Producto: Gorra con bordado chico\n`;
    mensaje += `- Cantidad: ${pedidoGorra.cantidad} pieza(s)\n`;
    mensaje += `\n📦 Total de piezas: ${pedidoGorra.cantidad}`;
    mensaje += "\n\nEl color que elegí es: [ESCRIBE EL COLOR]\n";
    mensaje += "¿Me podrían cotizar con el logo que les voy a enviar?";

    window.open(`https://wa.me/5632971001?text=${encodeURIComponent(mensaje)}`, '_blank');
    resetearTodoBarak(); // Limpia al terminar
// FUNCIÓN QUE BORRA TODO EL RASTRO ROSA
function resetearTodoBarak() {
    pedidoGorra.cantidad = 0;
    pedidoTaza.cantidad = 0;

    if(document.getElementById('resumen-gorras')) document.getElementById('resumen-gorras').innerHTML = "";
    if(document.getElementById('resumen-tazas')) document.getElementById('resumen-tazas').innerHTML = "";

    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.classList.remove('selected');
        btn.blur();
    });
}

// ESTO DETECTA CUANDO EL CLIENTE REGRESA DE WHATSAPP
window.addEventListener('pageshow', function(event) {
    resetearTodoBarak();
});

// ESTO DETECTA SI EL CLIENTE SIMPLEMENTE VUELVE A HACER CLIC EN TU PÁGINA
window.onfocus = function() {
    resetearTodoBarak();
};
    
}
let pedidoTaza = { cantidad: 0, precio: 55 };

function setTaza(elemento, nombre, precio) {
    pedidoTaza.cantidad++;
    elemento.classList.add('selected'); 
    elemento.blur(); 
    actualizarResumenTaza();
}

function restarTaza(event, nombre, elemento) {
    event.preventDefault();
    if (pedidoTaza.cantidad > 0) {
        pedidoTaza.cantidad--;
        if (pedidoTaza.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    elemento.blur();
    actualizarResumenTaza();
}

function actualizarResumenTaza() {
    const resumenDiv = document.getElementById('resumen-tazas');
    if (pedidoTaza.cantidad > 0) {
        resumenDiv.innerHTML = `📦 Total: ${pedidoTaza.cantidad} piezas`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

function enviarWhatsAppTaza() {
    const telefono = "5632971001"; 
    
    if (pedidoTaza.cantidad === 0) {
        alert("Por favor, selecciona al menos una taza.");
        return;
    }

    let mensaje = "¡Hola! Me interesa cotizar tazas blancas personalizadas:\n\n";
    mensaje += `- Cantidad: ${pedidoTaza.cantidad} pieza(s)\n`;
    mensaje += `\n📦 Total de piezas: ${pedidoTaza.cantidad}`;
    mensaje += "\n\nEl diseño que quiero es: [ESCRIBE AQUÍ SI ES FOTO O LOGO]";

    // Abrimos WhatsApp (corregido el formato del número)
    window.open(`https://wa.me/${telefono}?text=${encodeURIComponent(mensaje)}`, '_blank');

    // Llamamos a la limpieza maestra de inmediato
    resetearTodoBarak();
}
function resetearTodoBarak() {
    // 1. Reiniciar las variables de conteo
    if (typeof pedidoGorra !== 'undefined') pedidoGorra.cantidad = 0;
    if (typeof pedidoTaza !== 'undefined') pedidoTaza.cantidad = 0;

    // 2. Limpiar los textos de "Total de piezas" (los rosas)
    const idResumenes = ['resumen-gorras', 'resumen-tazas'];
    idResumenes.forEach(id => {
        const cajaTexto = document.getElementById(id);
        if (cajaTexto) cajaTexto.innerHTML = "";
    });

    // 3. Quitar el color rosa de todos los botones seleccionados
    document.querySelectorAll('.option-btn').forEach(boton => {
        boton.classList.remove('selected');
        boton.blur(); // Quita el borde de selección del navegador
    });
}
// 1. Variable
let pedidoStickers = { cantidad: 0, precio: 350 };

// 2. Función para sumar
function setStickers(elemento, nombre, precio) {
    pedidoStickers.cantidad++;
    elemento.classList.add('selected'); 
    actualizarResumenStickers();
}

// 3. Función para restar (clic derecho)
function restarStickers(event, nombre, elemento) {
    event.preventDefault();
    if (pedidoStickers.cantidad > 0) {
        pedidoStickers.cantidad--;
        if (pedidoStickers.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    actualizarResumenStickers();
}

function actualizarResumenStickers() {
    const resumenDiv = document.getElementById('resumen-stickers');
    if (pedidoStickers.cantidad > 0) {
        resumenDiv.innerHTML = `
            <div style="text-align: center; color: #ff4d8d; font-weight: bold; font-size: 14px;">
                📦 Total de piezas: ${pedidoStickers.cantidad}
                <br><span style="font-size: 10px; font-weight: normal;">(Clic derecho para restar)</span>
            </div>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}
function enviarWhatsAppStickers() {
    if (pedidoStickers.cantidad === 0) {
        alert("Por favor, selecciona al menos una planilla.");
        return;
    }

    // Mensaje completo con diseño y logo incluido
    let mensaje = "¡Hola! Me interesa cotizar planillas de stickers:\n\n";
    mensaje += `✅ Producto: Planilla 150x140cm\n`;
    mensaje += `📦 Cantidad: ${pedidoStickers.cantidad} planilla(s)\n\n`;
    mensaje += `🎨 El diseño que quiero es: [Escribe aquí si es foto o logo]\n`;
    mensaje += `📩 Ya cuento con mi archivo de logo listo para enviar.`;

    window.open(`https://wa.me/5632971001?text=${encodeURIComponent(mensaje)}`, '_blank');
    
    // Limpia la selección para que no se quede rosa al regresar
    resetearTodoBarak(); 
}
// 1. Variable para el pedido (Igual que stickers)
let pedidoLona = { cantidad: 0, precio: 85 };

// 2. Función para sumar M2
function setLona(elemento, nombre, precio) {
    pedidoLona.cantidad++;
    elemento.classList.add('selected'); 
    actualizarResumenLona();
}

// 3. Función para restar M2 (clic derecho)
function restarLona(event, nombre, elemento) {
    event.preventDefault();
    if (pedidoLona.cantidad > 0) {
        pedidoLona.cantidad--;
        if (pedidoLona.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    actualizarResumenLona();
}

// 4. Actualizar el texto rosa de resumen
function actualizarResumenLona() {
    const resumenDiv = document.getElementById('resumen-lona');
    if (pedidoLona.cantidad > 0) {
        resumenDiv.innerHTML = `
            <div style="text-align: center; color: #ff4d8d; font-weight: bold; font-size: 14px;">
                📦 Total de M2: ${pedidoLona.cantidad}
                <br><span style="font-size: 10px; font-weight: normal;">(Clic derecho para restar)</span>
            </div>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

// 5. Enviar a tu WhatsApp de BARAK
function enviarWhatsAppLona() {
    if (pedidoLona.cantidad === 0) {
        alert("Por favor, selecciona al menos 1 metro cuadrado.");
        return;
    }

    let mensaje = "¡Hola BARAK! Me interesa cotizar lona gran formato:\n\n";
    mensaje += `✅ Producto: Lona Impresa x M2\n`;
    mensaje += `📏 Cantidad: ${pedidoLona.cantidad} metro(s) cuadrado(s)\n`;
    mensaje += `💰 Total estimado: $${pedidoLona.cantidad * 85} MXN\n\n`;
    mensaje += `🎨 El diseño que quiero es: [Escribe aquí si es foto o logo]\n`;
    mensaje += `📩 Envío mi archivo por este medio.`;

    window.open(`https://wa.me/5630145944?text=${encodeURIComponent(mensaje)}`, '_blank');
}
// 1. Variable para el pedido
let pedidoVinil = { cantidad: 0, precio: 95 };

// 2. Sumar M2 (Clic izquierdo)
function setVinil(elemento, nombre, precio) {
    pedidoVinil.cantidad++;
    elemento.classList.add('selected'); // Activa el borde rosa
    actualizarResumenVinil();
}

// 3. Restar M2 (Clic derecho)
function restarVinil(event, nombre, elemento) {
    event.preventDefault();
    if (pedidoVinil.cantidad > 0) {
        pedidoVinil.cantidad--;
        if (pedidoVinil.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    actualizarResumenVinil();
}

// 4. Actualizar contador en pantalla
function actualizarResumenVinil() {
    const resumenDiv = document.getElementById('resumen-vinil');
    if (pedidoVinil.cantidad > 0) {
        resumenDiv.innerHTML = `
            <div style="text-align: center; color: #ff4d8d; font-weight: bold; font-size: 14px;">
                📦 Total de M2: ${pedidoVinil.cantidad}
                <br><span style="font-size: 10px; font-weight: normal;">(Clic derecho para restar)</span>
            </div>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

// 5. WhatsApp de BARAK
function enviarWhatsAppVinil() {
    if (pedidoVinil.cantidad === 0) {
        alert("Por favor, selecciona los metros cuadrados de vinil.");
        return;
    }

    let mensaje = "¡Hola BARAK! Me interesa cotizar vinil adhesivo:\n\n";
    mensaje += `✅ Producto: Vinil Adhesivo x M2\n`;
    mensaje += `📏 Cantidad: ${pedidoVinil.cantidad} metro(s) cuadrado(s)\n`;
    mensaje += `💰 Total estimado: $${pedidoVinil.cantidad * 95} MXN\n\n`;
    mensaje += `🎨 El diseño que quiero es: [Escribir aquí]\n`;
    mensaje += `📩 Envío mi archivo para revisión.`;

    window.open(`https://wa.me/5630145944?text=${encodeURIComponent(mensaje)}`, '_blank');
}
// 1. Variable para el pedido
let pedidoCorteVinil = { cantidad: 0, precio: 95 };

// 2. Sumar M2 (Clic normal)
function setCorteVinil(elemento, nombre, precio) {
    pedidoCorteVinil.cantidad++;
    elemento.classList.add('selected'); // Activa el estilo rosa de BARAK
    actualizarResumenCorteVinil();
}

// 3. Restar M2 (Clic derecho)
function restarCorteVinil(event, nombre, elemento) {
    event.preventDefault();
    if (pedidoCorteVinil.cantidad > 0) {
        pedidoCorteVinil.cantidad--;
        if (pedidoCorteVinil.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    actualizarResumenCorteVinil();
}

// 4. Actualizar el contador rosa
function actualizarResumenCorteVinil() {
    const resumenDiv = document.getElementById('resumen-corte-vinil');
    if (pedidoCorteVinil.cantidad > 0) {
        resumenDiv.innerHTML = `
            <div style="text-align: center; color: #ff4d8d; font-weight: bold; font-size: 14px;">
                📦 Total de M2: ${pedidoCorteVinil.cantidad}
                <br><span style="font-size: 10px; font-weight: normal;">(Clic derecho para restar)</span>
            </div>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

// 5. WhatsApp de BARAK (Cálculo automático)
function enviarWhatsAppCorteVinil() {
    if (pedidoCorteVinil.cantidad === 0) {
        alert("Por favor, selecciona los metros cuadrados para el corte de vinil.");
        return;
    }

    let mensaje = "¡Hola BARAK! Me interesa cotizar corte de vinil:\n\n";
    mensaje += `✅ Producto: Corte de Vinil (Plotter)\n`;
    mensaje += `📏 Cantidad: ${pedidoCorteVinil.cantidad} metro(s) cuadrado(s)\n`;
    mensaje += `💰 Total estimado: $${pedidoCorteVinil.cantidad * 95} MXN\n\n`;
    mensaje += `🎨 El logo/letras que quiero son: [Escribir aquí]\n`;
    mensaje += `📩 Envío mi archivo vectorizado para cotizar.`;

    window.open(`https://wa.me/5630145944?text=${encodeURIComponent(mensaje)}`, '_blank');
}
// 1. Variable para el pedido
let pedidoXBanner = { cantidad: 0, precio: 600 };

// 2. Sumar piezas (Clic normal)
function setXBanner(elemento, nombre, precio) {
    pedidoXBanner.cantidad++;
    elemento.classList.add('selected'); // Activa el borde rosa
    actualizarResumenXBanner();
}

// 3. Restar piezas (Clic derecho)
function restarXBanner(event, nombre, elemento) {
    event.preventDefault();
    if (pedidoXBanner.cantidad > 0) {
        pedidoXBanner.cantidad--;
        if (pedidoXBanner.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    actualizarResumenXBanner();
}

// 4. Actualizar el contador en pantalla
function actualizarResumenXBanner() {
    const resumenDiv = document.getElementById('resumen-xbanner');
    if (pedidoXBanner.cantidad > 0) {
        resumenDiv.innerHTML = `
            <div style="text-align: center; color: #ff4d8d; font-weight: bold; font-size: 14px;">
                📦 Estructuras seleccionadas: ${pedidoXBanner.cantidad}
                <br><span style="font-size: 10px; font-weight: normal;">(Clic derecho para restar)</span>
            </div>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

// 5. WhatsApp de BARAK
function enviarWhatsAppXBanner() {
    if (pedidoXBanner.cantidad === 0) {
        alert("Por favor, selecciona al menos una estructura de X-Banner.");
        return;
    }

    let mensaje = "¡Hola BARAK! Me interesa la estructura de X-Banner:\n\n";
    mensaje += `✅ Producto: X-Banner con base de agua\n`;
    mensaje += `📦 Cantidad: ${pedidoXBanner.cantidad} pieza(s)\n`;
    mensaje += `💰 Total estimado: $${pedidoXBanner.cantidad * 600} MXN\n\n`;
    mensaje += `💬 ¿Me podrían confirmar disponibilidad y tiempo de entrega?`;

    window.open(`https://wa.me/5630145944?text=${encodeURIComponent(mensaje)}`, '_blank');
}
// 1. Variable para el pedido
let pedidoBannerX = { cantidad: 0, precio: 350 };

// 2. Sumar piezas (Clic normal)
function setBannerX(elemento, nombre, precio) {
    pedidoBannerX.cantidad++;
    elemento.classList.add('selected'); // Activa el borde rosa del CSS
    actualizarResumenBannerX();
}

// 3. Restar piezas (Clic derecho)
function restarBannerX(event, nombre, elemento) {
    event.preventDefault(); // Bloquea el menú del navegador
    if (pedidoBannerX.cantidad > 0) {
        pedidoBannerX.cantidad--;
        if (pedidoBannerX.cantidad === 0) {
            elemento.classList.remove('selected');
        }
    }
    actualizarResumenBannerX();
}

// 4. Actualizar el resumen visual
function actualizarResumenBannerX() {
    const resumenDiv = document.getElementById('resumen-bannerx');
    if (pedidoBannerX.cantidad > 0) {
        resumenDiv.innerHTML = `
            <div style="text-align: center; color: #ff4d8d; font-weight: bold; font-size: 14px;">
                📦 Estructuras seleccionadas: ${pedidoBannerX.cantidad}
                <br><span style="font-size: 10px; font-weight: normal;">(Clic derecho para restar)</span>
            </div>`;
    } else {
        resumenDiv.innerHTML = "";
    }
}

// 5. WhatsApp automático para BARAK
function enviarWhatsAppBannerX() {
    if (pedidoBannerX.cantidad === 0) {
        alert("Por favor, selecciona cuántos Banner X necesitas.");
        return;
    }

    let mensaje = "¡Hola BARAK! Me interesa la estructura económica de Banner X:\n\n";
    mensaje += `✅ Producto: Banner X (Económico)\n`;
    mensaje += `📦 Cantidad: ${pedidoBannerX.cantidad} pieza(s)\n`;
    mensaje += `💰 Total estimado: $${pedidoBannerX.cantidad * 350} MXN\n\n`;
    mensaje += `💬 ¿Tienen entrega inmediata? Quedo atento.`;

    window.open(`https://wa.me/5630145944?text=${encodeURIComponent(mensaje)}`, '_blank');
}