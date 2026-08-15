(function () {
  'use strict';

  var header = document.querySelector('[data-site-header]');
  var nav = document.querySelector('[data-site-nav]');
  var menuToggle = document.querySelector('[data-menu-toggle]');
  var userMenuToggle = document.querySelector('[data-user-menu-toggle]');
  var userMenu = userMenuToggle ? userMenuToggle.closest('.nav-user-menu') : null;
  var lastScrollY = window.scrollY;
  var ticking = false;

  if (!header || !nav || !menuToggle) return;

  function setMenuState(open) {
    nav.classList.toggle('is-open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('menu-is-open', open);
  }

  function setUserMenuState(open) {
    if (!userMenuToggle || !userMenu) return;
    userMenu.classList.toggle('is-open', open);
    userMenuToggle.setAttribute('aria-expanded', String(open));
  }

  menuToggle.addEventListener('click', function () {
    setMenuState(!nav.classList.contains('is-open'));
  });

  if (userMenuToggle) {
    userMenuToggle.addEventListener('click', function (event) {
      event.stopPropagation();
      setUserMenuState(!userMenu.classList.contains('is-open'));
    });
  }

  document.addEventListener('click', function (event) {
    if (!header.contains(event.target)) {
      setMenuState(false);
      setUserMenuState(false);
    }
  });

  nav.addEventListener('click', function (event) {
    if (event.target.closest('a')) setMenuState(false);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      setMenuState(false);
      setUserMenuState(false);
      menuToggle.focus();
    }
  });

  function updateHeaderVisibility() {
    var currentScrollY = window.scrollY;
    if (nav.classList.contains('is-open') || currentScrollY < 80) {
      header.classList.remove('is-hidden');
    } else if (currentScrollY > lastScrollY + 8) {
      header.classList.add('is-hidden');
    } else if (currentScrollY < lastScrollY - 8) {
      header.classList.remove('is-hidden');
    }
    lastScrollY = currentScrollY;
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) {
      window.requestAnimationFrame(updateHeaderVisibility);
      ticking = true;
    }
  }, { passive: true });
})();

(function () {
  'use strict';

  document.querySelectorAll('.store-notice, .feedback-notice').forEach(function (notice) {
    notice.setAttribute('role', 'status');

    if (!notice.classList.contains('feedback-notice--success') && !notice.classList.contains('store-notice--success')) {
      return;
    }

    window.setTimeout(function () {
      notice.classList.add('is-dismissed');
    }, 6000);
  });
})();

(function () {
  'use strict';

  var modal = document.querySelector('[data-cake-order-modal]');
  if (!modal) return;

  var form = modal.querySelector('[data-cake-order-form]');
  var productImage = modal.querySelector('[data-cake-order-image]');
  var productName = modal.querySelector('[data-cake-order-name]');
  var productPrice = modal.querySelector('[data-cake-order-price]');
  var status = modal.querySelector('[data-cake-order-status]');
  var activeTrigger = null;
  var product = {};

  function setStatus(message) {
    status.textContent = message;
  }

  function buildMessage() {
    var date = form.elements.date.value;
    var note = form.elements.note.value.trim();
    var lines = [
      'Chào tiệm, mình muốn đặt mẫu bánh:',
      'Tên bánh: ' + product.name
    ];

    if (product.price) lines.push('Giá tham khảo: ' + product.price);
    if (date) lines.push('Ngày nhận mong muốn: ' + date);
    if (note) lines.push('Ghi chú: ' + note);
    if (product.url) lines.push('Xem mẫu: ' + product.url);
    if (product.image) lines.push('Ảnh mẫu: ' + product.image);

    return lines.join('\n');
  }

  function copyMessage(message) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(message);
    }

    var helper = document.createElement('textarea');
    helper.value = message;
    helper.setAttribute('readonly', '');
    helper.style.position = 'fixed';
    helper.style.opacity = '0';
    document.body.appendChild(helper);
    helper.select();
    document.execCommand('copy');
    helper.remove();
    return Promise.resolve();
  }

  function openModal(trigger) {
    activeTrigger = trigger;
    product = {
      name: trigger.getAttribute('data-cake-name') || 'Mẫu bánh',
      price: trigger.getAttribute('data-cake-price') || '',
      image: trigger.getAttribute('data-cake-image') || '',
      url: trigger.getAttribute('data-cake-url') || ''
    };

    productName.textContent = product.name;
    productPrice.textContent = product.price;
    productImage.hidden = !product.image;
    productImage.src = product.image;
    productImage.alt = product.name;
    form.reset();
    setStatus('Thông tin mẫu sẽ được sao chép trước khi mở kênh liên hệ.');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('order-modal-is-open');
    modal.querySelector('#cake-order-date').focus();
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('order-modal-is-open');
    if (activeTrigger) activeTrigger.focus();
  }

  document.addEventListener('click', function (event) {
    var trigger = event.target.closest('[data-cake-order]');
    if (trigger) {
      openModal(trigger);
      return;
    }

    if (event.target.closest('[data-cake-order-close]')) closeModal();
  });

  modal.addEventListener('click', function (event) {
    var contactButton = event.target.closest('[data-cake-order-contact]');
    if (!contactButton) return;

    copyMessage(buildMessage()).then(function () {
      setStatus('Đã sao chép thông tin mẫu. Hãy dán vào khung chat để gửi tiệm.');
    }).catch(function () {
      setStatus('Hãy sao chép thông tin mẫu rồi gửi tiệm qua khung chat.');
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
  });
})();
