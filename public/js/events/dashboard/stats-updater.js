(function(){
  window.DashboardStats = {
    set(textSelector, value){
      var el = document.querySelector(textSelector); if(el){ el.textContent = value; }
    }
  };
})();


