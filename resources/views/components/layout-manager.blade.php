{{-- Layout Manager Component loads all necessary scripts for the layout builder --}}

{{-- Step 2 Core Modules --}}
<script src="{{ asset('js/step2/autosave.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/step2/uploader.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/step2/components.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/step2/form-monitor.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/step2/modal.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/step2/init.js') }}?v={{ time() }}"></script>

{{-- Layout Modules --}}
<script src="{{ asset('js/layouts/center-text.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/left-text-right-image.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/right-text-left-image.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/hero-carousel.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/event-details-cards.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/about-two-images.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/speakers-grid.js') }}?v={{ time() }}"></script>
{{-- Shared photo utilities must load before photo-based layouts --}}
<script src="{{ asset('js/layouts/layout-photo-utils.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/photo-highlights.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/faq-list.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/tickets-pricing.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/footer-simple.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/premium-hero-spotlight.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/premium-gallery-mosaic.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/premium-schedule-timeline.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/premium-testimonials-slider.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/layouts/premium-sponsors-grid.js') }}?v={{ time() }}"></script>

{{-- Main Builder Script removed; Step 2 uses modular files only --}}
