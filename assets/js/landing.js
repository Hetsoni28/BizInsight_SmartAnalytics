// ============================================================
//  BizInsight — Landing Page JavaScript (landing.js)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

  // ===== HERO CHART =====
  const heroEl = document.getElementById('heroChart');
  if (heroEl) {
    new Chart(heroEl, {
      type: 'bar',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: [{
          data: [420000,380000,510000,620000,580000,840000],
          backgroundColor: ['rgba(255,255,255,.35)','rgba(255,255,255,.28)','rgba(255,255,255,.42)','rgba(255,255,255,.5)','rgba(255,255,255,.38)','rgba(255,255,255,.7)'],
          borderRadius: 6, borderSkipped: false,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        animation: { duration: 1500, easing: 'easeOutQuart' }
      }
    });
  }

  // ===== PREVIEW BAR CHART =====
  const barEl = document.getElementById('previewBarChart');
  if (barEl) {
    new Chart(barEl, {
      type: 'bar',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: [{
          label: 'Revenue',
          data: [420000,380000,510000,620000,580000,842500],
          backgroundColor: '#1a7fd4',
          borderRadius: 8, borderSkipped: false,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#5a7a99' } },
          y: { grid: { color: '#e8f4ff' }, ticks: { color: '#5a7a99', callback: v => '₹'+(v/100000).toFixed(1)+'L' } }
        }
      }
    });
  }

  // ===== PREVIEW PIE CHART =====
  const pieEl = document.getElementById('previewPieChart');
  if (pieEl) {
    new Chart(pieEl, {
      type: 'doughnut',
      data: {
        labels: ['Electronics','Phones','Tablets','Audio','Accessories'],
        datasets: [{ data: [38,27,15,12,8], backgroundColor: ['#1a7fd4','#16a34a','#d97706','#7c3aed','#dc2626'], borderWidth: 3, borderColor: '#fff' }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { color: '#5a7a99', font: { size: 11 }, padding: 10 } } },
        cutout: '55%',
      }
    });
  }

  // ===== SMOOTH SCROLL for anchor links =====
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ===== SCROLL-IN ANIMATION =====
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.feature-card, .pricing-card, .mini-kpi').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = 'opacity .5s ease, transform .5s ease';
    observer.observe(el);
  });

  // ===== NAVBAR scroll effect =====
  window.addEventListener('scroll', function() {
    const nav = document.querySelector('.navbar');
    if (nav) {
      if (window.scrollY > 20) {
        nav.style.boxShadow = '0 4px 24px rgba(26,127,212,.18)';
      } else {
        nav.style.boxShadow = '0 2px 16px rgba(26,127,212,.08)';
      }
    }
  });
});
