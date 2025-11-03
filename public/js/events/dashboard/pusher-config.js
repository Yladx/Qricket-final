(function(){
  if (window.Pusher && window.DashboardConfig && window.DashboardConfig.pusherAppKey) {
    try {
      window.QricketPusher = new Pusher(window.DashboardConfig.pusherAppKey, {
        cluster: window.DashboardConfig.pusherCluster || 'ap1',
        forceTLS: true,
      });
    } catch (e) {
      console.warn('[Dashboard] Pusher init failed:', e);
    }
  }
})();


