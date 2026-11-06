/**
 * JAVASCRIPT ĐIỀU HÀNH TƯƠNG TÁC THỜI GIAN THỰC & TIỆN ÍCH TRẢI NGHIỆM
 * Sinh viên: Lâm Nhựt Hải Đăng - MSSV: N25DTCN007
 * Tiêu chí A7, A8, Ajax Add-to-cart, Stepper, Eye Toggle, Toast & Back-to-Top
 */

document.addEventListener('DOMContentLoaded', function () {

  // ============================================================================
  // 0. HỆ THỐNG TOAST THÔNG BÁO NỔI SANG TRỌNG (FLOATING TOAST)
  // ============================================================================
  let toastContainer = document.querySelector('.toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
  }

  window.showToast = function (message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    
    // Icon vector tương ứng
    let iconSvg = '<svg class="svg-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
    if (type === 'warning') {
      iconSvg = '<svg class="svg-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
    } else if (type === 'error' || type === 'danger') {
      iconSvg = '<svg class="svg-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
    }

    toast.innerHTML = `${iconSvg} <span>${message}</span>`;
    toastContainer.appendChild(toast);

    // Kích hoạt animation trượt vào
    requestAnimationFrame(() => {
      toast.classList.add('show');
    });

    // Tự động mờ và biến mất sau 3 giây
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 250);
    }, 3200);
  };

  // ============================================================================
  // 0.1 HỘP THOẠI POPUP MODAL SANG TRỌNG (THAY THẾ CONFIRM & ALERT WINDOWS)
  // ============================================================================
  let activeModalResolve = null;

  function ensureModalContainer() {
    let modalBackdrop = document.getElementById('studio-modal-backdrop');
    if (!modalBackdrop) {
      modalBackdrop = document.createElement('div');
      modalBackdrop.id = 'studio-modal-backdrop';
      modalBackdrop.className = 'studio-modal-backdrop';
      modalBackdrop.innerHTML = `
        <div class="studio-modal-card" role="dialog" aria-modal="true" aria-labelledby="studio-modal-title">
          <div class="studio-modal-icon" id="studio-modal-icon"></div>
          <h3 class="studio-modal-title" id="studio-modal-title"></h3>
          <div class="studio-modal-message" id="studio-modal-message"></div>
          <div class="studio-modal-actions" id="studio-modal-actions">
            <button type="button" class="studio-modal-btn studio-modal-btn-cancel" id="studio-modal-btn-cancel">Hủy bỏ</button>
            <button type="button" class="studio-modal-btn studio-modal-btn-confirm" id="studio-modal-btn-confirm">Xác nhận</button>
          </div>
        </div>
      `;
      document.body.appendChild(modalBackdrop);

      const cancelBtn = modalBackdrop.querySelector('#studio-modal-btn-cancel');
      const confirmBtn = modalBackdrop.querySelector('#studio-modal-btn-confirm');

      function closeModal(result) {
        modalBackdrop.classList.remove('active');
        document.body.classList.remove('modal-open');
        const resolver = activeModalResolve;
        activeModalResolve = null;
        if (resolver) {
          resolver(result);
        }
      }

      cancelBtn.addEventListener('click', () => closeModal(false));
      confirmBtn.addEventListener('click', () => closeModal(true));

      modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) {
          closeModal(false);
        }
      });

      document.addEventListener('keydown', (e) => {
        if (!modalBackdrop.classList.contains('active')) return;
        if (e.key === 'Escape') {
          e.preventDefault();
          closeModal(false);
        } else if (e.key === 'Enter') {
          e.preventDefault();
          closeModal(true);
        }
      });
    }
    return modalBackdrop;
  }

  window.showConfirmDialog = function (options) {
    return new Promise((resolve) => {
      const modal = ensureModalContainer();
      activeModalResolve = (result) => {
        if (result && typeof options.onConfirm === 'function') {
          options.onConfirm();
        } else if (!result && typeof options.onCancel === 'function') {
          options.onCancel();
        }
        resolve(result);
      };

      const titleEl = modal.querySelector('#studio-modal-title');
      const msgEl = modal.querySelector('#studio-modal-message');
      const iconEl = modal.querySelector('#studio-modal-icon');
      const cancelBtn = modal.querySelector('#studio-modal-btn-cancel');
      const confirmBtn = modal.querySelector('#studio-modal-btn-confirm');

      const title = options.title || 'Xác nhận thao tác';
      const message = options.message || 'Bạn có chắc chắn muốn tiếp tục thực hiện?';
      const confirmText = options.confirmText || 'Xác nhận';
      const cancelText = options.cancelText || 'Hủy bỏ';
      const type = options.type || 'danger';

      titleEl.textContent = title;
      msgEl.innerHTML = message;
      cancelBtn.textContent = cancelText;
      cancelBtn.style.display = 'inline-flex';
      confirmBtn.textContent = confirmText;

      confirmBtn.className = `studio-modal-btn studio-modal-btn-confirm studio-modal-btn-${type}`;
      iconEl.className = `studio-modal-icon studio-modal-icon-${type}`;

      if (type === 'danger') {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
      } else if (type === 'warning') {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
      } else if (type === 'success') {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
      } else {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
      }

      document.body.classList.add('modal-open');
      modal.classList.add('active');
      confirmBtn.focus();
    });
  };

  window.showAlertDialog = function (options) {
    if (typeof options === 'string') {
      options = { message: options };
    }
    return new Promise((resolve) => {
      const modal = ensureModalContainer();
      activeModalResolve = () => {
        if (typeof options.onDismiss === 'function') {
          options.onDismiss();
        }
        resolve();
      };

      const titleEl = modal.querySelector('#studio-modal-title');
      const msgEl = modal.querySelector('#studio-modal-message');
      const iconEl = modal.querySelector('#studio-modal-icon');
      const cancelBtn = modal.querySelector('#studio-modal-btn-cancel');
      const confirmBtn = modal.querySelector('#studio-modal-btn-confirm');

      titleEl.textContent = options.title || 'Thông báo';
      msgEl.innerHTML = options.message || '';
      cancelBtn.style.display = 'none';
      confirmBtn.textContent = options.buttonText || 'Đã hiểu';
      const type = options.type || 'info';

      confirmBtn.className = `studio-modal-btn studio-modal-btn-confirm studio-modal-btn-${type}`;
      iconEl.className = `studio-modal-icon studio-modal-icon-${type}`;

      if (type === 'danger') {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
      } else if (type === 'warning') {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
      } else if (type === 'success') {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>';
      } else {
        iconEl.innerHTML = '<svg class="svg-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
      }

      document.body.classList.add('modal-open');
      modal.classList.add('active');
      confirmBtn.focus();
    });
  };

  // Thay thế triệt để alert mặc định của trình duyệt / Windows bằng popup sang trọng
  window.alert = function (message) {
    window.showAlertDialog({ message: String(message) });
  };

  // Cập nhật số đếm giỏ hàng trên Header với hiệu ứng nhịp tim & nảy giỏ (Micro-Bump)
  window.updateHeaderCartCount = function (count) {
    const headerCartEl = document.getElementById('header-cart-count');
    if (headerCartEl) {
      headerCartEl.textContent = count;
      const cartPill = headerCartEl.closest('.cart-pill');
      if (cartPill) {
        cartPill.classList.remove('bump');
        void cartPill.offsetWidth; // Trigger reflow for animation restart
        cartPill.classList.add('bump');
        setTimeout(() => cartPill.classList.remove('bump'), 350);
      }
    }
  };

  // ============================================================================
  // LUXURY ACOUSTIC RESONANCE: CRYSTAL CHIME (Apple Pay Triad Harmonics C6-E7-G6-C7)
  // ============================================================================
  let audioCtx = null;
  function getAudioContext() {
    if (!audioCtx) {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (AudioContextClass) {
        audioCtx = new AudioContextClass();
      }
    }
    if (audioCtx && audioCtx.state === 'suspended') {
      audioCtx.resume();
    }
    return audioCtx;
  }

  window.playLuxuryChime = function () {
    try {
      const ctx = getAudioContext();
      if (!ctx) return;
      const now = ctx.currentTime;

      // 1. Crystal Fundamental Strike (C6 - 1046.5Hz)
      const osc1 = ctx.createOscillator();
      const gain1 = ctx.createGain();
      osc1.type = 'sine';
      osc1.frequency.setValueAtTime(1046.5, now);
      gain1.gain.setValueAtTime(0.001, now);
      gain1.gain.linearRampToValueAtTime(0.35, now + 0.004);
      gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.58);
      osc1.connect(gain1);
      gain1.connect(ctx.destination);

      // 2. High Shimmer Overtone (C7 - 2093Hz) - Metallic sheen
      const osc2 = ctx.createOscillator();
      const gain2 = ctx.createGain();
      osc2.type = 'triangle';
      osc2.frequency.setValueAtTime(2093, now);
      gain2.gain.setValueAtTime(0.001, now);
      gain2.gain.linearRampToValueAtTime(0.18, now + 0.003);
      gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.42);
      osc2.connect(gain2);
      gain2.connect(ctx.destination);

      // 3. Golden Fifth Harmonic (G6 - 1567.98Hz)
      const osc3 = ctx.createOscillator();
      const gain3 = ctx.createGain();
      osc3.type = 'sine';
      osc3.frequency.setValueAtTime(1567.98, now);
      gain3.gain.setValueAtTime(0.001, now);
      gain3.gain.linearRampToValueAtTime(0.14, now + 0.005);
      gain3.gain.exponentialRampToValueAtTime(0.0001, now + 0.32);
      osc3.connect(gain3);
      gain3.connect(ctx.destination);

      // 4. Sparkle Sheen (E7 - 2637Hz)
      const osc4 = ctx.createOscillator();
      const gain4 = ctx.createGain();
      osc4.type = 'sine';
      osc4.frequency.setValueAtTime(2637, now);
      gain4.gain.setValueAtTime(0.001, now);
      gain4.gain.linearRampToValueAtTime(0.09, now + 0.003);
      gain4.gain.exponentialRampToValueAtTime(0.0001, now + 0.22);
      osc4.connect(gain4);
      gain4.connect(ctx.destination);

      osc1.start(now); osc2.start(now); osc3.start(now); osc4.start(now);
      osc1.stop(now + 0.60); osc2.stop(now + 0.44); osc3.stop(now + 0.34); osc4.stop(now + 0.24);
    } catch (e) {
      // AudioContext policy restrictions
    }
  };

  // ============================================================================
  // TRUE BALLISTIC PARABOLIC ARC FLY-TO-CART (KINEMATIC PROJECTILE PHYSICS)
  // ============================================================================
  window.animateFlyToCart = function (imgElement, onArrival) {
    const cartPill = document.querySelector('.cart-pill');
    if (!imgElement || !cartPill) {
      if (onArrival) onArrival();
      return;
    }

    const imgRect = imgElement.getBoundingClientRect();
    const cartRect = cartPill.getBoundingClientRect();

    if (imgRect.width === 0 || imgRect.height === 0) {
      if (onArrival) onArrival();
      return;
    }

    // 1. Phản hồi nhấc ảnh vật lý tại thẻ gốc (Lift Pulse)
    imgElement.classList.remove('card-img-lift');
    void imgElement.offsetWidth;
    imgElement.classList.add('card-img-lift');
    setTimeout(() => imgElement.classList.remove('card-img-lift'), 400);

    // 2. Tạo đồng xu thiết bị thu nhỏ (Glossy Product Token)
    const token = document.createElement('div');
    token.className = 'flying-cart-token';
    const innerImg = document.createElement('img');
    innerImg.src = imgElement.currentSrc || imgElement.src;
    token.appendChild(innerImg);
    document.body.appendChild(token);

    // Tọa độ tâm điểm
    const x0 = imgRect.left + (imgRect.width / 2);
    const y0 = imgRect.top + (imgRect.height / 2);
    const x1 = cartRect.left + (cartRect.width / 2);
    const y1 = cartRect.top + (cartRect.height / 2);

    const dx = x1 - x0;
    const dy = y1 - y0;

    // Chiều cao đỉnh vòm cung Parabol (Peak Arc) tính theo khoảng cách
    const arcHeight = Math.max(90, Math.min(220, Math.abs(dx) * 0.24 + 60));

    const flightDuration = 540; // 540ms tốc độ mắt người bắt chuyển động tự nhiên
    const startTime = performance.now();

    function stepFlight(now) {
      const elapsed = now - startTime;
      const t = Math.min(1, elapsed / flightDuration);

      // Easing mượt mà cho trục X (nhanh dần rồi êm lại khi tiếp cận giỏ)
      const ex = t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;

      // Phương trình quỹ đạo Parabol chuẩn vật lý: y(t) = y0 + dy*t - 4*H*t*(1-t)
      const currentX = x0 + dx * ex - 32;
      const currentY = y0 + dy * t - (4 * arcHeight * t * (1 - t)) - 32;

      // Thu nhỏ dần và hút xoáy vào miệng giỏ hàng
      const currentScale = 1.0 - (0.65 * t) - (0.30 * Math.pow(t, 3));
      const currentRot = -5 + (30 * t); // Nghiêng tự nhiên theo hướng vung tay ném
      const currentOpacity = t > 0.88 ? (1 - t) / 0.12 : 1.0;

      token.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) scale(${Math.max(0.05, currentScale)}) rotate(${currentRot}deg)`;
      token.style.opacity = currentOpacity;

      if (t < 1) {
        requestAnimationFrame(stepFlight);
      } else {
        // Hoàn tất chuyến bay: Hủy token
        if (token.parentNode) {
          token.parentNode.removeChild(token);
        }

        // 3. Phản hồi hấp thụ lực tại Giỏ Hàng (Momentum Catch Reaction)
        cartPill.classList.remove('cart-catch', 'ting-ring');
        void cartPill.offsetWidth;
        cartPill.classList.add('cart-catch', 'ting-ring');
        setTimeout(() => cartPill.classList.remove('cart-catch', 'ting-ring'), 750);

        // 4. Bật thẻ huy hiệu nổi "+1" bay lên trên giỏ hàng
        const plusBadge = document.createElement('span');
        plusBadge.className = 'cart-floating-plus';
        plusBadge.textContent = '+1';
        cartPill.appendChild(plusBadge);
        setTimeout(() => {
          if (plusBadge.parentNode) {
            plusBadge.parentNode.removeChild(plusBadge);
          }
        }, 750);

        // 5. Ngân vang chuông tinh thể Apple Pay
        window.playLuxuryChime();

        if (onArrival) {
          onArrival();
        }
      }
    }

    requestAnimationFrame(stepFlight);
  };

  // ============================================================================
  // THEME TOGGLE (DARK / LIGHT MODE VỚI LOCALSTORAGE & WCAG AA)
  // ============================================================================
  function initThemeToggle() {
    const toggleBtn = document.getElementById('theme-toggle');
    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', function () {
      const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);

      // Cập nhật meta theme-color
      const metaThemeColor = document.querySelector('meta[name="theme-color"]');
      if (metaThemeColor) {
        metaThemeColor.setAttribute('content', newTheme === 'dark' ? '#0b0f17' : '#ffffff');
      }
    });
  }
  initThemeToggle();

  // ============================================================================
  // 1. AJAX THÊM VÀO GIỎ HÀNG KHÔNG GIÁN ĐOẠN TRANG CHỦ & TRANG CHI TIẾT (KÈM POPUP)
  // ============================================================================
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-add-cart-ajax');
    if (!btn) return;

    e.preventDefault();
    const productId = btn.getAttribute('data-id');
    const productName = btn.getAttribute('data-name') || 'Thiết bị';
    const form = btn.closest('form');
    const csrfInput = form ? form.querySelector('input[name="csrf_token"]') : null;
    const csrfToken = (csrfInput ? csrfInput.value : '') || window.CSRF_TOKEN || '';
    const qtyInput = form ? form.querySelector('input[name="quantity"]') : null;
    const quantity = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;

    // Tìm thẻ ảnh sản phẩm để kích hoạt hiệu ứng bay parabol vào giỏ hàng
    const card = btn.closest('.precision-card, .product-card, .detail-card, .product-item, .manifest-card, article');
    const img = card ? card.querySelector('.card-img-wrap img, .product-img, .detail-main-img, img') : null;
    if (img && window.animateFlyToCart) {
      window.animateFlyToCart(img);
    }

    // Trạng thái rung nhịp haptic và spinner vi mô
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="btn-spinner"></span> Đang thêm';
    btn.disabled = true;

    fetch(`${window.BASE_URL || ''}cart-add.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(quantity)}&ajax=1&csrf_token=${encodeURIComponent(csrfToken)}`
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          btn.innerHTML = '<svg class="svg-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Đã thêm!';
          btn.classList.add('btn-added-success');
          if (typeof data.cart_count !== 'undefined') {
            window.updateHeaderCartCount(data.cart_count);
          }

          // Hiển thị Popup Modal sang trọng sau khi đồng xu bay vào giỏ hàng (540ms)
          const popupDelay = img ? 560 : 80;
          setTimeout(() => {
            if (data.type === 'warning') {
              window.showConfirmDialog({
                title: 'Đã điều chỉnh số lượng tối đa',
                message: data.message || `Kho đã đạt giới hạn tồn kho cho <strong>${productName}</strong>.`,
                confirmText: 'Xem giỏ hàng',
                cancelText: 'Tiếp tục mua sắm',
                type: 'warning',
                onConfirm: () => {
                  window.location.href = `${window.BASE_URL || ''}cart.php`;
                }
              });
            } else {
              window.showConfirmDialog({
                title: 'Đã thêm vào giỏ hàng!',
                message: `Thiết bị <strong>${productName}</strong> (Số lượng: <strong>${quantity}</strong>) đã được thêm vào giỏ hàng của bạn. Hiện giỏ hàng đang có <strong>${data.cart_count || 1}</strong> sản phẩm.`,
                confirmText: 'Xem giỏ hàng ngay',
                cancelText: 'Tiếp tục mua sắm',
                type: 'success',
                onConfirm: () => {
                  window.location.href = `${window.BASE_URL || ''}cart.php`;
                }
              });
            }
          }, popupDelay);

          setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-added-success');
            btn.disabled = false;
          }, 950);
        } else {
          btn.innerHTML = originalHtml;
          btn.disabled = false;
          window.showAlertDialog({
            title: 'Không thể thêm vào giỏ',
            message: data.message || 'Không thể thêm sản phẩm này vào giỏ hàng.',
            buttonText: 'Đã hiểu',
            type: 'warning'
          });
        }
      })
      .catch(err => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        console.error('Lỗi thêm giỏ hàng:', err);
        window.showAlertDialog({
          title: 'Lỗi kết nối',
          message: 'Không thể kết nối tới máy chủ. Vui lòng thử lại.',
          buttonText: 'Đóng',
          type: 'danger'
        });
      });
  });

  // ============================================================================
  // 2. BỘ NÚT TĂNG GIẢM SỐ LƯỢNG (STEPPER [-] [1] [+])
  // ============================================================================
  document.addEventListener('click', function (e) {
    const minusBtn = e.target.closest('.qty-btn-minus');
    const plusBtn = e.target.closest('.qty-btn-plus');

    if (minusBtn) {
      const stepper = minusBtn.closest('.qty-stepper');
      if (!stepper) return;
      const input = stepper.querySelector('input.cart-qty-input');
      if (!input) return;
      let val = parseInt(input.value, 10) || 1;
      const min = parseInt(input.getAttribute('min') || '1', 10);
      if (val > min) {
        input.value = val - 1;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    if (plusBtn) {
      const stepper = plusBtn.closest('.qty-stepper');
      if (!stepper) return;
      const input = stepper.querySelector('input.cart-qty-input');
      if (!input) return;
      let val = parseInt(input.value, 10) || 1;
      const max = parseInt(input.getAttribute('max') || '999', 10);
      if (val < max) {
        input.value = val + 1;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      } else {
        window.showToast(`Số lượng tối đa trong kho là ${max} chiếc.`, 'warning');
      }
    }
  });

  // ============================================================================
  // 3. TIÊU CHÍ A8: GIỎ HÀNG TÍNH LẠI THÀNH TIỀN NGAY KHI ĐỔI SỐ LƯỢNG (REAL-TIME)
  // ============================================================================
  const cartTable = document.querySelector('.cart-table');
  if (cartTable) {
    const qtyInputs = cartTable.querySelectorAll('.cart-qty-input');
    const grandTotalEl = document.getElementById('cart-grand-total');

    function formatVND(amount) {
      return new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
    }

    qtyInputs.forEach(input => {
      input.addEventListener('change', function () {
        let qty = parseInt(this.value, 10);
        const maxStock = parseInt(this.getAttribute('data-stock') || '99', 10);
        const price = parseFloat(this.getAttribute('data-price') || '0');
        const productId = this.getAttribute('data-id');

        if (isNaN(qty) || qty < 1) {
          qty = 1;
          this.value = 1;
        } else if (qty > maxStock) {
          window.showToast(`Kho chỉ còn ${maxStock} chiếc`, 'warning');
          qty = maxStock;
          this.value = maxStock;
        }

        // Tính lại dòng con
        const subtotal = qty * price;
        const rowSubtotalEl = document.querySelector(`.subtotal-${productId}`);
        if (rowSubtotalEl) {
          rowSubtotalEl.textContent = formatVND(subtotal);
        }

        // Tính lại tổng tiền cả giỏ
        let grandTotal = 0;
        qtyInputs.forEach(item => {
          const itemPrice = parseFloat(item.getAttribute('data-price') || '0');
          const itemQty = parseInt(item.value, 10) || 0;
          grandTotal += itemPrice * itemQty;
        });

        if (grandTotalEl) {
          grandTotalEl.textContent = formatVND(grandTotal);
        }

        // Gửi ngầm Ajax cập nhật vào Session phía máy chủ kèm CSRF Token & đồng bộ badge
        fetch(`${window.BASE_URL || ''}cart-update.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(qty)}&ajax=1&csrf_token=${encodeURIComponent(window.CSRF_TOKEN || '')}`
        })
          .then(res => res.json())
          .then(data => {
            if (data && typeof data.cart_count !== 'undefined') {
              window.updateHeaderCartCount(data.cart_count);
            }
          })
          .catch(err => console.error('Lỗi đồng bộ giỏ hàng:', err));
      });
    });
  }

  // ============================================================================
  // 4. TIÊU CHÍ A7: KIỂM TRA MẬT KHẨU KHỚP NHAU
  // ============================================================================
  const registerForm = document.querySelector('form#register-form');
  if (registerForm) {
    const passwordInput = registerForm.querySelector('input[name="password"]');
    const confirmInput = registerForm.querySelector('input[name="confirm_password"]');
    const errorMsg = document.createElement('div');
    errorMsg.className = 'field-error-msg';
    errorMsg.style.color = '#ef4444';
    errorMsg.style.fontSize = '0.8rem';
    errorMsg.style.marginTop = '0.35rem';
    errorMsg.style.fontFamily = 'var(--font-mono)';
    confirmInput.parentNode.appendChild(errorMsg);

    function validatePasswordMatch() {
      if (confirmInput.value && passwordInput.value !== confirmInput.value) {
        errorMsg.textContent = 'Mật khẩu xác nhận không khớp!';
        confirmInput.style.borderColor = '#ef4444';
        return false;
      } else {
        errorMsg.textContent = '';
        confirmInput.style.borderColor = '';
        return true;
      }
    }

    confirmInput.addEventListener('input', validatePasswordMatch);
    passwordInput.addEventListener('input', validatePasswordMatch);

    registerForm.addEventListener('submit', function (e) {
      if (!validatePasswordMatch()) {
        e.preventDefault();
        confirmInput.focus();
      }
    });
  }

  // ============================================================================
  // 5. TIÊU CHÍ A7: FORM LIÊN HỆ CHẶN NỘI DUNG DƯỚI 10 KÝ TỰ
  // ============================================================================
  const contactForm = document.querySelector('form#contact-form');
  if (contactForm) {
    const messageInput = contactForm.querySelector('textarea[name="message"]');
    const charCounter = document.createElement('div');
    charCounter.className = 'char-counter';
    charCounter.style.fontSize = '0.78rem';
    charCounter.style.fontFamily = 'var(--font-mono)';
    charCounter.style.marginTop = '0.35rem';
    charCounter.style.color = 'var(--text-muted)';
    messageInput.parentNode.appendChild(charCounter);

    function updateCounter() {
      const len = messageInput.value.trim().length;
      if (len < 10) {
        charCounter.textContent = `Độ dài: ${len}/10 ký tự (Yêu cầu tối thiểu 10 ký tự)`;
        charCounter.style.color = '#f59e0b';
      } else {
        charCounter.textContent = `✓ Đã đạt ${len} ký tự (Hợp lệ)`;
        charCounter.style.color = '#10b981';
      }
    }

    messageInput.addEventListener('input', updateCounter);
    updateCounter();

    contactForm.addEventListener('submit', function (e) {
      if (messageInput.value.trim().length < 10) {
        e.preventDefault();
        window.showToast('Nội dung tin nhắn liên hệ phải có tối thiểu 10 ký tự!', 'warning');
        messageInput.focus();
      }
    });
  }

  // ============================================================================
  // 6. NÚT MẮT ẨN / HIỆN MẬT KHẨU (EYE TOGGLE)
  // ============================================================================
  document.querySelectorAll('.password-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const wrapper = this.closest('.password-wrapper');
      if (!wrapper) return;
      const input = wrapper.querySelector('input');
      if (!input) return;

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      // Đổi icon SVG
      if (isPassword) {
        // Icon mắt mở
        this.innerHTML = '<svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        this.title = 'Ẩn mật khẩu';
      } else {
        // Icon mắt gạch chéo
        this.innerHTML = '<svg class="svg-icon no-margin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
        this.title = 'Hiện mật khẩu';
      }
    });
  });

  // ============================================================================
  // 7. HIỆU ỨNG SPOTLIGHT ÁNH SÁNG TINH TẾ THEO CHUỘT (SUBTLE CARD SPOTLIGHT)
  // ============================================================================
  let spotlightRaf = null;
  document.addEventListener('mousemove', function (e) {
    const card = e.target.closest('.precision-card');
    if (!card) return;
    if (spotlightRaf) cancelAnimationFrame(spotlightRaf);
    spotlightRaf = requestAnimationFrame(() => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      card.style.setProperty('--mouse-x', `${x}px`);
      card.style.setProperty('--mouse-y', `${y}px`);
    });
  }, { passive: true });

  // ============================================================================
  // 8. XEM TRƯỚC ẢNH SẢN PHẨM TRONG ADMIN (LIVE IMAGE PREVIEW)
  // ============================================================================
  const prodImgInput = document.getElementById('prod-img');
  const prodImgPreview = document.getElementById('prod-img-preview');
  if (prodImgInput && prodImgPreview) {
    prodImgInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          prodImgPreview.src = e.target.result;
          prodImgPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // ============================================================================
  // 9. NÚT CUỘN NHANH LÊN ĐẦU TRANG (BACK TO TOP)
  // ============================================================================
  const backToTopBtn = document.getElementById('back-to-top');
  if (backToTopBtn) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 350) {
        backToTopBtn.classList.add('show');
      } else {
        backToTopBtn.classList.remove('show');
      }
    });

    backToTopBtn.addEventListener('click', function () {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // ============================================================================
  // 10. HỘP THOẠI XÁC NHẬN [data-confirm] BẰNG POPUP THIẾT KẾ RIÊNG
  // ============================================================================
  document.addEventListener('click', function (e) {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;

    // Không can thiệp nếu là các nút xóa giỏ hàng hoặc hủy đơn hàng đã có AJAX handler riêng
    if (el.closest('.cart-remove-form, .cart-clear-form, .order-cancel-form, .order-detail-cancel-form, .btn-cart-remove-ajax, .btn-cart-clear-ajax, .btn-cancel-order-ajax, .btn-cancel-detail-ajax')) {
      return;
    }

    e.preventDefault();
    const message = el.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';
    const title = el.getAttribute('data-confirm-title') || 'Xác nhận hành động';

    window.showConfirmDialog({
      title: title,
      message: message,
      confirmText: 'Đồng ý',
      cancelText: 'Hủy bỏ',
      type: 'danger',
      onConfirm: () => {
        const form = el.closest('form');
        if (form) {
          HTMLFormElement.prototype.submit.call(form);
        } else if (el.tagName === 'A' && el.href) {
          window.location.href = el.href;
        }
      }
    });
  });

  // ============================================================================
  // 11. ZERO-RELOAD: LỌC DANH MỤC, TÌM KIẾM TỨC THÌ (LIVE SEARCH) & SẮP XẾP
  // ============================================================================
  let catalogAbortController = null;
  let searchDebounceTimer = null;

  function loadCatalogAjax(url, preserveSearchInput = false) {
    const container = document.getElementById('catalog-ajax-container');
    if (!container) return;

    if (catalogAbortController) {
      catalogAbortController.abort();
    }
    catalogAbortController = new AbortController();

    const gridSection = document.getElementById('catalog-grid-section');
    if (gridSection) {
      gridSection.style.opacity = '0.45';
      gridSection.style.transition = 'opacity 160ms ease';
    } else {
      container.style.opacity = '0.45';
    }

    fetch(url, { signal: catalogAbortController.signal })
      .then(res => res.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContainer = doc.getElementById('catalog-ajax-container');
        if (!newContainer) return;

        if (preserveSearchInput) {
          const newGrid = doc.getElementById('catalog-grid-section');
          const curGrid = document.getElementById('catalog-grid-section');
          if (newGrid && curGrid) {
            curGrid.innerHTML = newGrid.innerHTML;
          }
          const newCount = doc.getElementById('catalog-result-count');
          const curCount = document.getElementById('catalog-result-count');
          if (newCount && curCount) {
            curCount.innerHTML = newCount.innerHTML;
          }
          const newPills = doc.querySelector('.category-pills');
          const curPills = container.querySelector('.category-pills');
          if (newPills && curPills) {
            curPills.innerHTML = newPills.innerHTML;
          }
          const newSortForm = doc.getElementById('sort-form');
          const curSortForm = document.getElementById('sort-form');
          if (newSortForm && curSortForm) {
            curSortForm.innerHTML = newSortForm.innerHTML;
          }
        } else {
          container.innerHTML = newContainer.innerHTML;
        }
        window.history.replaceState({}, '', url);
      })
      .catch(err => {
        if (err.name === 'AbortError') return;
        console.error('Lỗi tải danh mục:', err);
        window.location.href = url;
      })
      .finally(() => {
        const curGrid = document.getElementById('catalog-grid-section');
        if (curGrid) curGrid.style.opacity = '1';
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
      });
  }

  document.addEventListener('click', function (e) {
    const pill = e.target.closest('#catalog-ajax-container .category-pills .cat-pill');
    const pageItem = e.target.closest('#catalog-ajax-container .pagination .page-item');
    const clearBtn = e.target.closest('.search-clear-btn');

    if (pill && pill.href) {
      e.preventDefault();
      loadCatalogAjax(pill.href, false);
      return;
    }
    if (pageItem && pageItem.href) {
      e.preventDefault();
      loadCatalogAjax(pageItem.href, false);
      return;
    }
    if (clearBtn) {
      e.preventDefault();
      const form = clearBtn.closest('form');
      const input = form ? form.querySelector('.search-input') : null;
      if (input) {
        input.value = '';
        clearBtn.style.display = 'none';
        input.focus();
        const formData = new FormData(form);
        formData.delete('q');
        const params = new URLSearchParams(formData);
        const queryStr = params.toString();
        const baseAction = (form.getAttribute('action') || window.location.pathname).split('?')[0];
        const url = baseAction + (queryStr ? '?' + queryStr : '');
        loadCatalogAjax(url, true);
      }
      return;
    }
  });

  document.addEventListener('input', function (e) {
    if (e.target && e.target.classList.contains('search-input') && e.target.closest('#catalog-ajax-container')) {
      const input = e.target;
      const form = input.closest('form');
      const clearBtn = form ? form.querySelector('.search-clear-btn') : null;
      if (clearBtn) {
        clearBtn.style.display = input.value.trim().length > 0 ? 'inline-flex' : 'none';
      }
      if (!form) return;

      if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(() => {
        const formData = new FormData(form);
        if (!input.value.trim()) {
          formData.delete('q');
        }
        const params = new URLSearchParams(formData);
        const queryStr = params.toString();
        const baseAction = (form.getAttribute('action') || window.location.pathname).split('?')[0];
        const url = baseAction + (queryStr ? '?' + queryStr : '');
        loadCatalogAjax(url, true);
      }, 260);
    }
  });

  document.addEventListener('submit', function (e) {
    if (e.target && e.target.classList.contains('search-form') && e.target.closest('#catalog-ajax-container')) {
      e.preventDefault();
      if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
      const form = e.target;
      const formData = new FormData(form);
      const params = new URLSearchParams(formData);
      const queryStr = params.toString();
      const baseAction = (form.getAttribute('action') || window.location.pathname).split('?')[0];
      const url = baseAction + (queryStr ? '?' + queryStr : '');
      loadCatalogAjax(url, true);
    }
  });

  document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'sort-select' && e.target.closest('#catalog-ajax-container')) {
      e.preventDefault();
      const form = e.target.closest('form');
      if (form) {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const baseAction = (form.getAttribute('action') || window.location.pathname).split('?')[0];
        const url = baseAction + '?' + params.toString();
        loadCatalogAjax(url, false);
      }
    }
  });

  // ============================================================================
  // 12. ZERO-RELOAD: XÓA DÒNG GIỎ HÀNG & XÓA SẠCH GIỎ QUA AJAX
  // ============================================================================
  document.addEventListener('click', function (e) {
    const removeBtn = e.target.closest('.btn-cart-remove-ajax');
    if (!removeBtn) return;

    e.preventDefault();
    const form = removeBtn.closest('form');
    if (!form) return;

    const confirmMsg = removeBtn.getAttribute('data-confirm') || 'Xóa sản phẩm này khỏi giỏ hàng?';

    window.showConfirmDialog({
      title: 'Xác nhận xóa thiết bị',
      message: confirmMsg,
      confirmText: 'Xóa khỏi giỏ',
      cancelText: 'Giữ lại',
      type: 'danger',
      onConfirm: () => {
        const productId = removeBtn.getAttribute('data-id');
        const row = document.getElementById(`cart-row-${productId}`);
        const csrfInput = form.querySelector('input[name="csrf_token"]');
        const csrfToken = (csrfInput ? csrfInput.value : '') || window.CSRF_TOKEN || '';
        const actionUrl = form.getAttribute('action') || `${window.BASE_URL || ''}cart-update.php`;

        fetch(actionUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=remove&product_id=${encodeURIComponent(productId)}&ajax=1&csrf_token=${encodeURIComponent(csrfToken)}`
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              if (row) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 250);
              }
              const grandTotalEl = document.getElementById('cart-grand-total');
              if (grandTotalEl) {
                grandTotalEl.textContent = data.formatted_grand_total;
              }
              window.updateHeaderCartCount(data.cart_count);
              window.showToast(data.message || 'Đã loại bỏ thiết bị khỏi giỏ!', 'info');

              if (data.is_empty) {
                setTimeout(() => {
                  window.location.reload();
                }, 400);
              }
            }
          })
          .catch(err => console.error('Lỗi xóa giỏ hàng:', err));
      }
    });
  });

  document.addEventListener('click', function (e) {
    const clearBtn = e.target.closest('.btn-cart-clear-ajax');
    if (!clearBtn) return;

    e.preventDefault();
    const form = clearBtn.closest('form');
    if (!form) return;

    const confirmMsg = clearBtn.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn làm sạch toàn bộ giỏ hàng?';

    window.showConfirmDialog({
      title: 'Làm sạch giỏ hàng',
      message: confirmMsg,
      confirmText: 'Xóa sạch',
      cancelText: 'Hủy bỏ',
      type: 'danger',
      onConfirm: () => {
        const csrfInput = form.querySelector('input[name="csrf_token"]');
        const csrfToken = (csrfInput ? csrfInput.value : '') || window.CSRF_TOKEN || '';
        const actionUrl = form.getAttribute('action') || `${window.BASE_URL || ''}cart-update.php`;

        fetch(actionUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=clear&ajax=1&csrf_token=${encodeURIComponent(csrfToken)}`
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              window.updateHeaderCartCount(0);
              window.showToast('Đã xóa sạch giỏ hàng.', 'info');
              setTimeout(() => {
                window.location.reload();
              }, 350);
            }
          })
          .catch(err => console.error('Lỗi làm sạch giỏ:', err));
      }
    });
  });

  // ============================================================================
  // 13. ZERO-RELOAD: ÁP DỤNG VOUCHER THANH TOÁN QUA AJAX
  // ============================================================================
  const voucherForm = document.getElementById('voucher-form');
  if (voucherForm) {
    voucherForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const codeInput = document.getElementById('voucher-input-code');
      const code = codeInput ? codeInput.value.trim() : '';
      const csrfInput = voucherForm.querySelector('input[name="csrf_token"]');
      const csrfToken = (csrfInput ? csrfInput.value : '') || window.CSRF_TOKEN || '';
      const actionUrl = voucherForm.getAttribute('action') || window.location.href;

      fetch(actionUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action_voucher=1&voucher_code=${encodeURIComponent(code)}&ajax=1&csrf_token=${encodeURIComponent(csrfToken)}`
      })
        .then(res => res.json())
        .then(data => {
          const discountWrap = document.getElementById('voucher-discount-wrap');
          const originalWrap = document.getElementById('original-total-wrap');
          const feedbackMsg = document.getElementById('voucher-feedback-msg');
          const errorText = document.getElementById('voucher-error-text');
          const labelDisplay = document.getElementById('voucher-label-display');
          const amountDisplay = document.getElementById('voucher-amount-display');
          const finalTotalEl = document.getElementById('checkout-final-total');
          const btnTotalEl = document.getElementById('checkout-btn-total');
          const vietqrTotalEl = document.getElementById('vietqr-total-display');
          const vietqrImg = document.getElementById('vietqr-img');

          if (data.success) {
            if (discountWrap) discountWrap.style.display = 'block';
            if (originalWrap) originalWrap.style.display = 'block';
            if (feedbackMsg) feedbackMsg.style.display = 'none';

            if (labelDisplay) labelDisplay.textContent = `Ưu đãi (${data.voucher_code} -${data.discount_percent}%):`;
            if (amountDisplay) amountDisplay.textContent = `-${data.formatted_discount}`;
            if (finalTotalEl) finalTotalEl.textContent = data.formatted_final_total;
            if (btnTotalEl) btnTotalEl.textContent = data.formatted_final_total;
            if (vietqrTotalEl) vietqrTotalEl.textContent = data.formatted_final_total;

            if (vietqrImg) {
              vietqrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=${encodeURIComponent("2|99|0987654321|LAM NHUT HAI DANG||0|0|" + data.final_total + "|MSSV N25DTCN007|transfer_myqr")}`;
            }

            window.showToast(data.message, 'success');
          } else {
            if (discountWrap) discountWrap.style.display = 'none';
            if (originalWrap) originalWrap.style.display = 'none';
            if (feedbackMsg) feedbackMsg.style.display = 'block';
            if (errorText) errorText.textContent = data.message;
            if (finalTotalEl) finalTotalEl.textContent = data.formatted_final_total;
            if (btnTotalEl) btnTotalEl.textContent = data.formatted_final_total;

            window.showToast(data.message, 'warning');
          }
        })
        .catch(err => {
          console.error('Lỗi áp dụng voucher:', err);
          window.showToast('Lỗi kết nối máy chủ.', 'error');
        });
    });
  }

  // ============================================================================
  // 14. ZERO-RELOAD: ADMIN ĐỔI TRẠNG THÁI ĐƠN HÀNG QUA AJAX
  // ============================================================================
  document.addEventListener('change', function (e) {
    if (e.target && e.target.name === 'status' && e.target.closest('.cart-table') && e.target.closest('form[action*="admin/orders.php"]')) {
      const select = e.target;
      const form = select.closest('form');
      if (!form) return;

      const orderIdInput = form.querySelector('input[name="order_id"]');
      const orderId = orderIdInput ? orderIdInput.value : '';
      const newStatus = select.value;
      const csrfInput = form.querySelector('input[name="csrf_token"]');
      const csrfToken = (csrfInput ? csrfInput.value : '') || window.CSRF_TOKEN || '';
      const actionUrl = form.getAttribute('action') || window.location.href;

      fetch(actionUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${encodeURIComponent(orderId)}&status=${encodeURIComponent(newStatus)}&ajax=1&csrf_token=${encodeURIComponent(csrfToken)}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            window.showToast(data.message, 'success');
          } else {
            window.showToast('Không thể cập nhật trạng thái đơn.', 'warning');
          }
        })
        .catch(err => console.error('Lỗi cập nhật trạng thái đơn:', err));
    }
  });

  // ============================================================================
  // 15. ZERO-RELOAD: KHÁCH HỦY ĐƠN HÀNG QUA AJAX
  // ============================================================================
  document.addEventListener('submit', function (e) {
    const cancelForm = e.target.closest('.order-cancel-form, .order-detail-cancel-form');
    if (!cancelForm) return;

    e.preventDefault();
    const confirmBtn = cancelForm.querySelector('[data-confirm]');
    const confirmMsg = confirmBtn ? confirmBtn.getAttribute('data-confirm') : 'Bạn có chắc chắn muốn hủy đơn hàng này không?';

    window.showConfirmDialog({
      title: 'Xác nhận hủy đơn hàng',
      message: confirmMsg,
      confirmText: 'Xác nhận hủy đơn',
      cancelText: 'Đóng lại',
      type: 'danger',
      onConfirm: () => {
        const orderIdInput = cancelForm.querySelector('input[name="order_id"]');
        const orderId = orderIdInput ? orderIdInput.value : '';
        const csrfInput = cancelForm.querySelector('input[name="csrf_token"]');
        const csrfToken = (csrfInput ? csrfInput.value : '') || window.CSRF_TOKEN || '';
        const actionUrl = cancelForm.getAttribute('action') || `${window.BASE_URL || ''}order-cancel.php`;

        fetch(actionUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `order_id=${encodeURIComponent(orderId)}&ajax=1&csrf_token=${encodeURIComponent(csrfToken)}`
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              window.showToast(data.message, 'info');

              // Cập nhật giao diện orders.php
              const statusCell = document.getElementById(`order-status-${orderId}`);
              if (statusCell) {
                statusCell.innerHTML = '<span class="badge-status badge-cancelled"><svg class="svg-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Đã hủy</span>';
              }
              cancelForm.remove();

              // Cập nhật giao diện order-detail.php
              const stepper = document.querySelector('.order-stepper');
              if (stepper) {
                const stepperCard = stepper.closest('.manifest-card');
                if (stepperCard) {
                  stepperCard.outerHTML = '<div class="alert alert-danger" style="margin-bottom: 2rem;"><span><svg class="svg-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></span> <div><strong>Đơn hàng này đã bị hủy.</strong> Các linh kiện phần cứng đã được hệ thống tự động hoàn trả tồn kho.</div></div>';
                }
              }
            } else {
              window.showToast(data.message || 'Không thể hủy đơn hàng.', 'warning');
            }
          })
          .catch(err => console.error('Lỗi hủy đơn:', err));
      }
    });
  });

});
