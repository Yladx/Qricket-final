// Bootstrap-style Offcanvas (Tailwind implementation)
// Provides open/close behavior similar to Bootstrap 5 offcanvas

(function () {
  const BREAKPOINT = 760; // px

  function qs(id) {
    return document.getElementById(id);
  }

  function initOffcanvas() {
    const panel = qs('offcanvas-nav');
    const backdrop = qs('offcanvas-backdrop');
    const toggle = qs('offcanvas-toggle');
    const closeBtn = qs('offcanvas-close');

    if (!panel || !backdrop) return;

    let isOpen = false;

    function isMobile() {
      return window.innerWidth <= BREAKPOINT;
    }

    function open() {
      if (!isMobile()) return; // only on <= 760px
      if (isOpen) return;
      isOpen = true;
      backdrop.classList.remove('hidden');
      panel.classList.remove('translate-x-full');
      document.body.classList.add('overflow-hidden');
    }

    function close() {
      if (!isOpen) return;
      isOpen = false;
      panel.classList.add('translate-x-full');
      setTimeout(() => {
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }, 300);
    }

    // Expose globally for other callers
    window.openOffcanvasNavigation = open;
    window.closeOffcanvasNavigation = close;

    // Toggle button
    if (toggle) {
      toggle.addEventListener('click', (e) => {
        if (!isMobile()) return; // ignore on wide screens
        e.preventDefault();
        open();
      });
    }

    // Close button inside panel
    if (closeBtn) {
      closeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        close();
      });
    }

    // Backdrop click closes
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) {
        close();
      }
    });

    // Support elements with data-bs-toggle="offcanvas"
    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-bs-toggle="offcanvas"]');
      if (!trigger) return;
      const targetSelector = trigger.getAttribute('data-bs-target') || '#offcanvas-nav';
      if (targetSelector === '#offcanvas-nav') {
        if (!isMobile()) return;
        e.preventDefault();
        open();
      }
    });

    // ESC key closes
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isOpen) {
        close();
      }
    });

    // Handle resize: auto-close when going above breakpoint
    function handleResize() {
      if (!isMobile()) {
        close();
      }
      // Show/hide toggle for UX if present
      if (toggle) {
        if (isMobile()) toggle.classList.remove('hidden');
        else toggle.classList.add('hidden');
      }
    }

    window.addEventListener('resize', handleResize);
    // initial state
    handleResize();
  }

  document.addEventListener('DOMContentLoaded', initOffcanvas);
})();
