(function(){
  document.addEventListener('DOMContentLoaded', function(){
    if(window.DashboardActivity && window.DashboardActivity.init) window.DashboardActivity.init();
    if(window.DashboardSales && window.DashboardSales.refresh) window.DashboardSales.refresh();
  });
})();


