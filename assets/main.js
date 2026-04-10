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
      const show = filter === 'todos' || card.dataset.category === filter;
      card.style.display = show ? 'flex' : 'none';
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