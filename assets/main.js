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