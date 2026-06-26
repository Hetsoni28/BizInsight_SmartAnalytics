// ============================================================
//  BizInsight — Charts JavaScript (charts.js)
//  Chart.js configurations for dashboard & analytics pages
// ============================================================

const COLORS = {
  blue:   '#1a7fd4',
  green:  '#16a34a',
  amber:  '#d97706',
  purple: '#7c3aed',
  red:    '#dc2626',
  cyan:   '#0891b2',
  pink:   '#db2777',
  teal:   '#0d9488',
};

const PALETTE = Object.values(COLORS);

const CHART_DEFAULTS = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: { color: '#5a7a99', font: { size: 12, family: "'Segoe UI', system-ui, sans-serif" }, padding: 16 }
    },
    tooltip: {
      backgroundColor: '#1a2d42',
      titleColor: '#fff',
      bodyColor: 'rgba(255,255,255,.8)',
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: function(ctx) {
          // For bar/line charts use ctx.parsed.y; for pie/doughnut use ctx.parsed
          const val = (ctx.parsed && typeof ctx.parsed === 'object' && 'y' in ctx.parsed)
            ? ctx.parsed.y
            : ctx.parsed;
          if (typeof val === 'number' && val > 100) {
            return ' ₹' + val.toLocaleString('en-IN');
          }
          return ' ' + (val ?? '');
        }
      }
    }
  }
};

// ===== MONTH LABELS (only non-zero months) =====
function getActiveMonths(labels, data) {
  const active = { labels: [], data: [] };
  labels.forEach((l, i) => {
    if (data[i] > 0) { active.labels.push(l.substring(0, 3)); active.data.push(data[i]); }
  });
  if (active.labels.length === 0) {
    return { labels: labels.map(l => l.substring(0, 3)), data };
  }
  return active;
}

// ===== REVENUE BAR CHART (Dashboard) =====
function initRevenueBarChart() {
  const el = document.getElementById('revenueBarChart');
  if (!el || typeof revenueData === 'undefined') return;
  const { labels, data } = getActiveMonths(revenueLabels, revenueData);
  new Chart(el, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Revenue (₹)',
        data,
        backgroundColor: labels.map((_, i) => i === data.indexOf(Math.max(...data)) ? COLORS.blue : '#a0c8ee'),
        borderRadius: 10,
        borderSkipped: false,
      }]
    },
    options: {
      ...CHART_DEFAULTS,
      plugins: {
        ...CHART_DEFAULTS.plugins,
        legend: { display: false }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#5a7a99' } },
        y: {
          grid: { color: '#e8f4ff', border: { display: false } },
          ticks: {
            color: '#5a7a99',
            callback: v => v >= 100000 ? '₹' + (v/100000).toFixed(1) + 'L' : v >= 1000 ? '₹' + (v/1000).toFixed(0) + 'K' : '₹' + v
          }
        }
      }
    }
  });
}

// ===== CATEGORY PIE CHART (Dashboard) =====
function initCategoryPieChart() {
  const el = document.getElementById('categoryPieChart');
  if (!el || typeof categoryLabels === 'undefined') return;
  if (categoryLabels.length === 0) {
    el.parentElement.innerHTML = '<div class="empty-state" style="padding:60px 20px"><div style="font-size:36px;margin-bottom:8px">📊</div><p>Upload sales data to see category breakdown</p></div>';
    return;
  }
  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: categoryLabels,
      datasets: [{
        data: categoryValues,
        backgroundColor: PALETTE,
        borderWidth: 3,
        borderColor: '#fff',
        hoverOffset: 6,
      }]
    },
    options: {
      ...CHART_DEFAULTS,
      plugins: {
        ...CHART_DEFAULTS.plugins,
        legend: {
          position: 'bottom',
          labels: { color: '#5a7a99', font: { size: 12 }, padding: 14, usePointStyle: true }
        },
        tooltip: {
          ...CHART_DEFAULTS.plugins.tooltip,
          callbacks: {
            label: ctx => ` ${ctx.label}: ₹${ctx.parsed.toLocaleString('en-IN')} (${((ctx.parsed / categoryValues.reduce((a,b)=>a+b,0))*100).toFixed(1)}%)`
          }
        }
      },
      cutout: '62%',
    }
  });
}

// ===== ANALYTICS: REVENUE vs PROFIT GROUPED BAR =====
function initRevProfitChart() {
  const el = document.getElementById('revProfitChart');
  if (!el || typeof revenueData === 'undefined') return;
  const { labels, data: rData } = getActiveMonths(revenueLabels, revenueData);
  const pData = labels.map(l => {
    const full = revenueLabels.map(x => x.substring(0, 3)).indexOf(l);
    return typeof profitData !== 'undefined' && full >= 0 ? profitData[full] : 0;
  });
  new Chart(el, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Revenue',
          data: rData,
          backgroundColor: COLORS.blue,
          borderRadius: 8,
          borderSkipped: false,
        },
        {
          label: 'Profit',
          data: pData,
          backgroundColor: COLORS.green,
          borderRadius: 8,
          borderSkipped: false,
        }
      ]
    },
    options: {
      ...CHART_DEFAULTS,
      plugins: {
        ...CHART_DEFAULTS.plugins,
        legend: {
          position: 'top',
          labels: { color: '#5a7a99', font: { size: 12 }, usePointStyle: true }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#5a7a99' } },
        y: {
          grid: { color: '#e8f4ff', border: { display: false } },
          ticks: { color: '#5a7a99', callback: v => v >= 100000 ? '₹'+(v/100000).toFixed(1)+'L' : v >= 1000 ? '₹'+(v/1000).toFixed(0)+'K' : '₹'+v }
        }
      }
    }
  });
}

// ===== ANALYTICS PIE =====
function initAnalyticsPieChart() {
  const el = document.getElementById('analyticsPieChart');
  if (!el || typeof categoryLabels === 'undefined') return;
  if (categoryLabels.length === 0) {
    el.parentElement.innerHTML = '<div class="empty-state" style="padding:60px 20px"><div style="font-size:36px;margin-bottom:8px">🗂️</div><p>No category data yet</p></div>';
    return;
  }
  new Chart(el, {
    type: 'pie',
    data: {
      labels: categoryLabels,
      datasets: [{
        data: categoryValues,
        backgroundColor: PALETTE,
        borderWidth: 3,
        borderColor: '#fff',
        hoverOffset: 6,
      }]
    },
    options: {
      ...CHART_DEFAULTS,
      plugins: {
        ...CHART_DEFAULTS.plugins,
        legend: { position: 'bottom', labels: { color: '#5a7a99', font: { size: 12 }, usePointStyle: true } }
      }
    }
  });
}

// ===== GROWTH LINE CHART (Analytics) =====
function initGrowthLineChart() {
  const el = document.getElementById('growthLineChart');
  if (!el || typeof revenueData === 'undefined') return;
  const { labels, data } = getActiveMonths(revenueLabels, revenueData);
  new Chart(el, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Revenue (₹)',
        data,
        borderColor: COLORS.blue,
        backgroundColor: 'rgba(26,127,212,.08)',
        fill: true,
        tension: 0.42,
        pointBackgroundColor: COLORS.blue,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      }]
    },
    options: {
      ...CHART_DEFAULTS,
      plugins: { ...CHART_DEFAULTS.plugins, legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#5a7a99' } },
        y: {
          grid: { color: '#e8f4ff', border: { display: false } },
          ticks: { color: '#5a7a99', callback: v => v >= 100000 ? '₹'+(v/100000).toFixed(1)+'L' : v >= 1000 ? '₹'+(v/1000).toFixed(0)+'K' : '₹'+v }
        }
      }
    }
  });
}

// ===== HERO CHART (Landing page) =====
function initHeroChart() {
  const el = document.getElementById('heroChart');
  if (!el) return;
  new Chart(el, {
    type: 'bar',
    data: {
      labels: ['Jan','Feb','Mar','Apr','May','Jun'],
      datasets: [{
        data: [420000, 380000, 510000, 620000, 580000, 840000],
        backgroundColor: ['rgba(255,255,255,.4)','rgba(255,255,255,.3)','rgba(255,255,255,.45)','rgba(255,255,255,.5)','rgba(255,255,255,.4)','rgba(255,255,255,.7)'],
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { enabled: false } },
      scales: { x: { display: false }, y: { display: false } },
      animation: { duration: 1200 }
    }
  });
}

// ===== PREVIEW BAR CHART (Landing) =====
function initPreviewBarChart() {
  const el = document.getElementById('previewBarChart');
  if (!el) return;
  new Chart(el, {
    type: 'bar',
    data: {
      labels: ['Jan','Feb','Mar','Apr','May','Jun'],
      datasets: [{
        label: 'Revenue (₹)',
        data: [420000,380000,510000,620000,580000,842500],
        backgroundColor: '#1a7fd4',
        borderRadius: 8,
        borderSkipped: false,
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

// ===== PREVIEW PIE CHART (Landing) =====
function initPreviewPieChart() {
  const el = document.getElementById('previewPieChart');
  if (!el) return;
  new Chart(el, {
    type: 'doughnut',
    data: {
      labels: ['Electronics','Phones','Tablets','Audio','Accessories'],
      datasets: [{ data: [38,27,15,12,8], backgroundColor: PALETTE, borderWidth: 3, borderColor: '#fff' }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { color: '#5a7a99', font: { size: 11 }, padding: 10, usePointStyle: true } } },
      cutout: '55%',
    }
  });
}

// ===== INIT ALL CHARTS ON DOM READY =====
document.addEventListener('DOMContentLoaded', function() {
  // Dashboard charts (always init — safe: functions check if canvas element exists)
  initRevenueBarChart();
  initCategoryPieChart();

  // Analytics charts (only fire if pageType is set to 'analytics' OR the canvases exist)
  if (typeof pageType !== 'undefined' && pageType === 'analytics') {
    initRevProfitChart();
    initAnalyticsPieChart();
    initGrowthLineChart();
  } else {
    // Fallback: init if canvas elements are present on the page
    if (document.getElementById('revProfitChart'))   initRevProfitChart();
    if (document.getElementById('analyticsPieChart')) initAnalyticsPieChart();
    if (document.getElementById('growthLineChart'))   initGrowthLineChart();
  }

  // Landing charts (only render if canvas exists — landing page embeds these)
  initHeroChart();
  initPreviewBarChart();
  initPreviewPieChart();
});
