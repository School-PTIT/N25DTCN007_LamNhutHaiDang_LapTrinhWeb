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

  // Cập nhật số đếm giỏ hàng trên Header với hiệu ứng nhịp tim
  window.updateHeaderCartCount = function (count) {
    const headerCartEl = document.getElementById('header-cart-count');
    if (headerCartEl) {
      headerCartEl.textContent = count;
      headerCartEl.style.transform = 'scale(1.35)';
      headerCartEl.style.transition = 'transform 180ms ease';
      setTimeout(() => {
        headerCartEl.style.transform = 'scale(1)';
      }, 200);
    }
  };

  // ============================================================================
  // 1. AJAX THÊM VÀO GIỎ HÀNG KHÔNG GIÁN ĐOẠN TRANG CHỦ
  // ============================================================================
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-add-cart-ajax');
    if (!btn) return;

    e.preventDefault();
    const productId = btn.getAttribute('data-id');
    const productName = btn.getAttribute('data-name') || 'Thiết bị';
    const csrfToken = window.CSRF_TOKEN || '';

    // Trạng thái loading nhẹ
    const originalText = btn.innerHTML;
    btn.innerHTML = '...';
    btn.disabled = true;

    fetch(`${window.BASE_URL || ''}cart-add.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `product_id=${encodeURIComponent(productId)}&quantity=1&ajax=1&csrf_token=${encodeURIComponent(csrfToken)}`
    })
      .then(res => res.json())
      .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        if (data.success) {
          window.showToast(`Đã thêm <strong>${productName}</strong> vào giỏ!`, 'success');
          if (typeof data.cart_count !== 'undefined') {
            window.updateHeaderCartCount(data.cart_count);
          }
        } else {
          window.showToast(data.message || 'Không thể thêm sản phẩm.', 'warning');
        }
      })
      .catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        console.error('Lỗi thêm giỏ hàng:', err);
        window.showToast('Lỗi kết nối máy chủ.', 'error');
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
  // 7. NÚT XÓA TỪ KHÓA TÌM KIẾM NHANH (CLEAR SEARCH)
  // ============================================================================
  const searchInput = document.querySelector('.search-input');
  const searchClearBtn = document.querySelector('.search-clear-btn');
  if (searchInput && searchClearBtn) {
    searchClearBtn.addEventListener('click', function () {
      searchInput.value = '';
      searchInput.focus();
      const form = searchInput.closest('form');
      if (form) form.submit();
    });
  }

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
  // 10. HỘP THOẠI XÁC NHẬN [data-confirm]
  // ============================================================================
  const confirmActions = document.querySelectorAll('[data-confirm]');
  confirmActions.forEach(el => {
    // Không bắt sự kiện submit của form có cơ chế ajax riêng
    if (el.closest('.cart-remove-form, .cart-clear-form, .order-cancel-form, .order-detail-cancel-form')) {
      return;
    }
    el.addEventListener('click', function (e) {
      const message = this.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn thực hiện thao tác này?';
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // ============================================================================
  // 11. ZERO-RELOAD: LỌC DANH MỤC, PHÂN TRANG & SẮP XẾP QUA AJAX
  // ============================================================================
  function loadCatalogAjax(url) {
    const container = document.getElementById('catalog-ajax-container');
    if (!container) return;

    container.style.opacity = '0.4';
    container.style.pointerEvents = 'none';

    fetch(url)
      .then(res => res.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContainer = doc.getElementById('catalog-ajax-container');
        if (newContainer) {
          container.innerHTML = newContainer.innerHTML;
          window.history.pushState({}, '', url);
        }
      })
      .catch(err => {
        console.error('Lỗi tải danh mục:', err);
        window.location.href = url;
      })
      .finally(() => {
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
      });
  }

  document.addEventListener('click', function (e) {
    const pill = e.target.closest('#catalog-ajax-container .category-pills .cat-pill');
    const pageItem = e.target.closest('#catalog-ajax-container .pagination .page-item');
    if (pill && pill.href) {
      e.preventDefault();
      loadCatalogAjax(pill.href);
      return;
    }
    if (pageItem && pageItem.href) {
      e.preventDefault();
      loadCatalogAjax(pageItem.href);
      return;
    }
  });

  document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'sort-select' && e.target.closest('#catalog-ajax-container')) {
      e.preventDefault();
      const form = e.target.closest('form');
      if (form) {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = form.action.split('?')[0] + '?' + params.toString();
        loadCatalogAjax(url);
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

    const confirmMsg = removeBtn.getAttribute('data-confirm') || 'Xóa sản phẩm này khỏi giỏ?';
    if (!confirm(confirmMsg)) return;

    const productId = removeBtn.getAttribute('data-id');
    const row = document.getElementById(`cart-row-${productId}`);
    const csrfToken = window.CSRF_TOKEN || '';

    fetch(form.action, {
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
            }, 600);
          }
        }
      })
      .catch(err => console.error('Lỗi xóa giỏ hàng:', err));
  });

  document.addEventListener('click', function (e) {
    const clearBtn = e.target.closest('.btn-cart-clear-ajax');
    if (!clearBtn) return;

    e.preventDefault();
    const form = clearBtn.closest('form');
    if (!form) return;

    const confirmMsg = clearBtn.getAttribute('data-confirm') || 'Bạn có chắc chắn muốn xóa tất cả sản phẩm khỏi giỏ hàng?';
    if (!confirm(confirmMsg)) return;

    const csrfToken = window.CSRF_TOKEN || '';

    fetch(form.action, {
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
      const csrfToken = window.CSRF_TOKEN || '';

      fetch(voucherForm.action, {
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
      const csrfToken = window.CSRF_TOKEN || '';

      fetch(form.action, {
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
    if (!confirm(confirmMsg)) return;

    const orderIdInput = cancelForm.querySelector('input[name="order_id"]');
    const orderId = orderIdInput ? orderIdInput.value : '';
    const csrfToken = window.CSRF_TOKEN || '';

    fetch(cancelForm.action, {
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
  });

});
