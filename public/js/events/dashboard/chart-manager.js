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
          tension: 0.4,
          borderColor: '#10b981',
          pointRadius: 3,
          pointHoverRadius: 5,
          pointBackgroundColor: '#10b981',
          pointBorderColor: '#ffffff',
          backgroundColor: 'rgba(16,185,129,0.1)',
          fill: true,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { 
            display: true,
            position: 'top',
            labels: {
              color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
              font: { size: 12 }
            }
          },
          tooltip: { 
            enabled: true,
            mode: 'index', 
            intersect: false 
          }
        },
        interaction: { mode: 'index', intersect: false },
        scales: {
          x: {
            type: 'category',
            position: 'bottom',
            grid: { 
              display: false,
              color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
            },
            ticks: { 
              color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
              font: { size: 11 },
              maxRotation: 45,
              minRotation: 45
            }
          },
          y: {
            position: 'left',
            beginAtZero: true,
            grid: { 
              color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
            },
            ticks: {
              callback: function(v){ return '₱' + Number(v).toLocaleString(); },
              color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
              font: { size: 11 }
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


