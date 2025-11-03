(function(){
  var chart;
  function buildData(days){
    // Build from window.revenueChartData if present; otherwise dummy flat
    var src = (window.revenueChartData && Array.isArray(window.revenueChartData)) ? window.revenueChartData : [];
    if(days && src.length){
      src = src.slice(-days);
    }
    if(!src.length){
      var labels = [], data=[]; for(var i=29;i>=0;i--){ labels.push('D-'+i); data.push(0); }
      return { labels: labels, values: data };
    }
    return { labels: src.map(function(p){ return p.label || p.date || ''; }), values: src.map(function(p){ return Number(p.value || p.revenue || 0); }) };
  }
  function render(days){
    var ctx = document.getElementById('revenueChart'); if(!ctx){ return; }
    var d = buildData(days);
    if(chart){ chart.destroy(); }
    chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: d.labels,
        datasets: [{
          label: 'Revenue',
          data: d.values,
          tension: 0.35,
          borderColor: '#22c55e',
          pointRadius: 2.5,
          pointHoverRadius: 4,
          pointBackgroundColor: '#22c55e',
          pointBorderColor: 'transparent',
          backgroundColor: 'rgba(34,197,94,.10)',
          fill: true,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { mode: 'index', intersect: false }
        },
        interaction: { mode: 'nearest', intersect: false },
        scales: {
          x: {
            type: 'category',
            position: 'bottom',
            grid: { display: false },
            ticks: { color: getComputedStyle(document.documentElement).getPropertyValue('--tw-prose-body') || '#9ca3af' }
          },
          y: {
            position: 'left',
            grid: { color: 'rgba(156,163,175,.15)' },
            ticks: {
              callback: function(v){ return '₱' + Number(v).toLocaleString(); },
              color: getComputedStyle(document.documentElement).getPropertyValue('--tw-prose-body') || '#9ca3af'
            }
          }
        }
      }
    });
  }
  window.DashboardCharts = {
    renderRevenue: render,
    updateRevenueRange: function(days){ render(days); },
    init: function(data){ window.revenueChartData = data || window.revenueChartData; render(30); },
    updateData: function(data){ window.revenueChartData = data || []; render(30); }
  };
  document.addEventListener('DOMContentLoaded', function(){ render(30); });
})();


