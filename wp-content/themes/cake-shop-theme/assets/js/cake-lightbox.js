document.addEventListener('DOMContentLoaded', function () {
  const lightbox = document.getElementById('cake-lightbox');
  if (!lightbox) return;

  const lightboxImage = lightbox.querySelector('.cake-lightbox__image');
  const lightboxTitle = lightbox.querySelector('.cake-lightbox__title');
  const lightboxPrice = lightbox.querySelector('.cake-lightbox__price');
  const lightboxExcerpt = lightbox.querySelector('.cake-lightbox__excerpt');
  const detailAction = lightbox.querySelector('.cake-lightbox__detail-action');
  const lightboxThumbs = lightbox.querySelector('.cake-lightbox__thumbs');
  const closeBtn = lightbox.querySelector('.cake-lightbox__close');
  const overlay = lightbox.querySelector('.cake-lightbox__overlay');
  const prevBtn = lightbox.querySelector('.cake-lightbox__nav--prev');
  const nextBtn = lightbox.querySelector('.cake-lightbox__nav--next');
  const triggers = document.querySelectorAll('.cake-popup-trigger');

  if (!lightboxImage || !lightboxTitle || !lightboxPrice || !lightboxExcerpt || !detailAction || !lightboxThumbs || !closeBtn || !overlay || !prevBtn || !nextBtn || !triggers.length) {
    return;
  }

  let currentGallery = [];
  let currentIndex = 0;
  let touchStartX = 0;
  let touchStartY = 0;

  function renderCurrentImage() {
    if (!currentGallery.length) return;

    lightboxImage.src = currentGallery[currentIndex];
    lightboxImage.alt = lightboxTitle.textContent || '';

    const thumbButtons = lightboxThumbs.querySelectorAll('button');
    thumbButtons.forEach(function (btn, index) {
      btn.classList.toggle('is-active', index === currentIndex);
    });

    const showNav = currentGallery.length > 1;
    prevBtn.style.display = showNav ? 'flex' : 'none';
    nextBtn.style.display = showNav ? 'flex' : 'none';
    lightboxThumbs.style.display = showNav ? 'grid' : 'none';
  }

  function renderThumbs() {
    lightboxThumbs.innerHTML = '';

    currentGallery.forEach(function (imageUrl, index) {
      const button = document.createElement('button');
      button.type = 'button';
      button.innerHTML = '<img src="' + imageUrl + '" alt="">';
      button.addEventListener('click', function () {
        currentIndex = index;
        renderCurrentImage();
      });
      lightboxThumbs.appendChild(button);
    });
  }

  function openLightbox(gallery, imageTitle, imageExcerpt, imagePrice, detailUrl, startIndex) {
    currentGallery = Array.isArray(gallery) ? gallery.filter(Boolean) : [];
    if (!currentGallery.length) return;

    currentIndex = Math.min(Math.max(parseInt(startIndex, 10) || 0, 0), currentGallery.length - 1);
    lightboxTitle.textContent = imageTitle || '';
    lightboxPrice.textContent = imagePrice || '';
    lightboxExcerpt.textContent = imageExcerpt || '';
    detailAction.href = detailUrl || '#';

    renderThumbs();
    renderCurrentImage();

    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.classList.add('lightbox-open');
  }

  function closeLightbox() {
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('lightbox-open');
    lightboxImage.src = '';
    lightboxImage.alt = '';
    lightboxTitle.textContent = '';
    lightboxPrice.textContent = '';
    lightboxExcerpt.textContent = '';
    detailAction.href = '#';
    lightboxThumbs.innerHTML = '';
    currentGallery = [];
    currentIndex = 0;
  }

  function showPrev() {
    if (currentGallery.length < 2) return;
    currentIndex = (currentIndex - 1 + currentGallery.length) % currentGallery.length;
    renderCurrentImage();
  }

  function showNext() {
    if (currentGallery.length < 2) return;
    currentIndex = (currentIndex + 1) % currentGallery.length;
    renderCurrentImage();
  }

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      let gallery = [];
      try {
        gallery = JSON.parse(trigger.getAttribute('data-gallery') || '[]');
      } catch (error) {
        gallery = [];
      }

      openLightbox(
        gallery,
        trigger.getAttribute('data-title'),
        trigger.getAttribute('data-excerpt'),
        trigger.getAttribute('data-price'),
        trigger.getAttribute('data-detail-url'),
        trigger.getAttribute('data-start-index')
      );
    });
  });

  closeBtn.addEventListener('click', closeLightbox);
  overlay.addEventListener('click', closeLightbox);
  prevBtn.addEventListener('click', showPrev);
  nextBtn.addEventListener('click', showNext);

  lightboxImage.addEventListener('touchstart', function (event) {
    const touch = event.changedTouches[0];
    touchStartX = touch.clientX;
    touchStartY = touch.clientY;
  }, { passive: true });

  lightboxImage.addEventListener('touchend', function (event) {
    if (!lightbox.classList.contains('is-open') || currentGallery.length < 2) return;

    const touch = event.changedTouches[0];
    const horizontalDistance = touch.clientX - touchStartX;
    const verticalDistance = touch.clientY - touchStartY;

    if (Math.abs(horizontalDistance) < 40 || Math.abs(horizontalDistance) <= Math.abs(verticalDistance)) return;

    if (horizontalDistance > 0) {
      showPrev();
    } else {
      showNext();
    }
  }, { passive: true });

  document.addEventListener('keydown', function (event) {
    if (!lightbox.classList.contains('is-open')) return;

    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowLeft') showPrev();
    if (event.key === 'ArrowRight') showNext();
  });
});
