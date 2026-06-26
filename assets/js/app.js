// ============================================================
//  BizInsight — App JavaScript (app.js)
//  Shared utilities for all dashboard pages
// ============================================================

// ===== TOAST =====
function showToast(message, duration = 3000) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => toast.classList.remove('show'), duration);
}

// ===== MODAL =====
function openModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// Close modal on overlay click
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
    document.body.style.overflow = '';
  }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(el => {
      el.classList.remove('open');
      document.body.style.overflow = '';
    });
  }
});

// ===== CONFIRM DELETE =====
function confirmDelete(url, message = 'Are you sure you want to delete this record? This cannot be undone.') {
  if (confirm(message)) {
    window.location.href = url;
  }
}

// ===== AUTO-HIDE ALERTS =====
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      alert.style.transition = 'opacity .5s, max-height .5s';
      alert.style.opacity = '0';
      alert.style.maxHeight = '0';
      alert.style.overflow = 'hidden';
      setTimeout(() => alert.remove(), 600);
    }, 5000);
  });
});

// ===== TOPBAR SEARCH (basic filter) =====
const searchInput = document.querySelector('.topbar-search input');
if (searchInput) {
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && this.value.trim()) {
      window.location.href = 'sales.php?search=' + encodeURIComponent(this.value.trim());
    }
  });
}

// ===== SIDEBAR ACTIVE HIGHLIGHT =====
(function() {
  const current = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-item').forEach(function(link) {
    const href = link.getAttribute('href');
    if (href && href === current) {
      link.classList.add('active');
    }
  });
})();

// ===== NUMBER FORMAT HELPER =====
function formatINR(amount) {
  if (amount >= 100000) return '₹' + (amount / 100000).toFixed(2) + 'L';
  if (amount >= 1000)   return '₹' + (amount / 1000).toFixed(1) + 'K';
  return '₹' + amount.toLocaleString('en-IN');
}
