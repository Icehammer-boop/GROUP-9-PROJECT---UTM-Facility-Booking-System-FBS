/**
 * FBS — Facility Booking System
 * Main JavaScript
 */

(function () {
  'use strict';

  // ---------- FLASH MESSAGES AUTO-DISMISS (legacy .alert blocks, not .alert-flash) ----------
  function initFlashDismiss() {
    var alerts = document.querySelectorAll('.alert:not(.alert-flash)');
    alerts.forEach(function (alert) {
      setTimeout(function () {
        alert.style.transition = 'opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1)';
        alert.style.opacity = '0';
        setTimeout(function () { alert.remove(); }, 400);
      }, 5000);
    });
  }

  // ---------- CONFIRM DIALOGS ----------
  function initConfirmDialogs() {
    var confirmLinks = document.querySelectorAll('[data-confirm]');
    confirmLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        var msg = link.getAttribute('data-confirm') || 'Are you sure?';
        if (!confirm(msg)) {
          e.preventDefault();
        }
      });
    });
  }

  // ---------- MOBILE NAV TOGGLE ----------
  function initMobileNav() {
    var toggle = document.getElementById('mobileMenuToggle');
    var nav = document.getElementById('navLinks');
    if (toggle && nav) {
      toggle.addEventListener('click', function () {
        nav.classList.toggle('open');
      });
    }
  }

  // ---------- FORM VALIDATION (client-side enhancement) ----------
  function initFormValidation() {
    var forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        var required = form.querySelectorAll('[required]');
        var valid = true;
        required.forEach(function (field) {
          if (!field.value.trim()) {
            valid = false;
            field.style.borderColor = '#dc2626';
            field.addEventListener('input', function handler() {
              field.style.borderColor = '';
              field.removeEventListener('input', handler);
            });
          }
        });
        if (!valid) {
          e.preventDefault();
        }
      });
    });
  }

  // ---------- SCHEDULE CELL INTERACTION ----------
  function initScheduleGrid() {
    var cells = document.querySelectorAll('.schedule-cell');
    cells.forEach(function (cell) {
      if (cell.classList.contains('available')) {
        cell.style.cursor = 'pointer';
        cell.title = 'Available — click to select';
        cell.addEventListener('click', function () {
          // Toggle selection visual
          if (cell.classList.contains('selected')) {
            cell.classList.remove('selected');
            cell.style.background = '';
            cell.style.color = '';
          } else {
            cell.classList.add('selected');
            cell.style.background = '#0d9373';
            cell.style.color = '#fff';
          }
        });
      }
    });
  }

  // ---------- SEARCH DEBOUNCE ----------
  function initSearchDebounce() {
    var searchInput = document.querySelector('.search-bar .form-input');
    if (searchInput && searchInput.getAttribute('data-autosubmit')) {
      var timeout;
      searchInput.addEventListener('input', function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
          searchInput.closest('form').submit();
        }, 500);
      });
    }
  }

  // ---------- BOOKING TIME VALIDATION ----------
  function initBookingTimeValidation() {
    var startInput = document.querySelector('input[name="start_time"]');
    var endInput = document.querySelector('input[name="end_time"]');
    if (startInput && endInput) {
      endInput.addEventListener('change', function () {
        if (startInput.value && endInput.value) {
          if (endInput.value <= startInput.value) {
            alert('End time must be after start time.');
            endInput.value = '';
          }
        }
      });
    }
  }

  // ---------- FADE-IN ANIMATION ON SCROLL ----------
  function initScrollAnimations() {
    var elements = document.querySelectorAll('.animate-in');
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1 });

      elements.forEach(function (el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(12px)';
        el.style.transition = 'opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
        observer.observe(el);
      });
    } else {
      // Fallback: just show everything
      elements.forEach(function (el) {
        el.style.opacity = '1';
        el.style.transform = 'none';
      });
    }
  }

  // ---------- DATE INPUT MIN = TODAY ----------
  function initDateMin() {
    var dateInputs = document.querySelectorAll('input[type="date"]');
    var today = new Date();
    var yyyy = today.getFullYear();
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var dd = String(today.getDate()).padStart(2, '0');
    var minDate = yyyy + '-' + mm + '-' + dd;
    dateInputs.forEach(function (input) {
      if (!input.getAttribute('min')) {
        input.setAttribute('min', minDate);
      }
    });
  }

  // ---------- TOAST NOTIFICATIONS ----------
  var toastContainer = null;

  function getToastContainer() {
    if (!toastContainer) {
      toastContainer = document.querySelector('.toast-container');
      if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
      }
    }
    return toastContainer;
  }

  function showToast(msg, type, duration) {
    type = type || 'success';
    duration = duration || 4000;
    var container = getToastContainer();

    var icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML =
      '<span class="toast-icon">' + (icons[type] || icons.success) + '</span>' +
      '<span class="toast-msg">' + msg + '</span>' +
      '<span class="toast-close" onclick="this.parentElement.remove()">✕</span>';

    container.appendChild(toast);

    // Auto-dismiss
    setTimeout(function () {
      if (toast.parentElement) {
        toast.classList.add('toast-out');
        setTimeout(function () { toast.remove(); }, 300);
      }
    }, duration);
  }

  // Expose globally so inline scripts can call it
  window.showToast = showToast;

  // ---------- INSTANTIATE ALL ----------
  document.addEventListener('DOMContentLoaded', function () {
    initFlashDismiss();
    initConfirmDialogs();
    initMobileNav();
    initFormValidation();
    initScheduleGrid();
    initSearchDebounce();
    initBookingTimeValidation();
    initScrollAnimations();
    initDateMin();
  });
})();
