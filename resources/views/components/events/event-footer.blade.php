
@props(['event', 'fixed' => false])

@php
  // Load footer data from R2 storage
  $footerData = [];
  if (isset($event)) {
      try {
          $disk = \Storage::disk('r2');
          $footerPath = "events/{$event->id}/footer.json";
          if ($disk->exists($footerPath)) {
              $json = $disk->get($footerPath);
              $decoded = json_decode($json, true);
              if (is_array($decoded)) { 
                  $footerData = $decoded; 
              }
          }
      } catch (\Throwable $t) {
          \Log::warning('Failed to load footer.json', ['event_id' => $event->id, 'error' => $t->getMessage()]);
      }
  }

  // Get default values if not set
  $footerTitle = $footerData['title'] ?? 'Contact Us';
  $footerDescription = $footerData['description'] ?? "We'd love to hear from you! Reach out through our social media channels or visit us directly.";
  $footerEmail = $footerData['email'] ?? '';
  
  // Social media links
  $socialLinks = [
      'facebook' => $footerData['facebook'] ?? '',
      'twitter' => $footerData['twitter'] ?? '',
      'instagram' => $footerData['instagram'] ?? '',
      'youtube' => $footerData['youtube'] ?? '',
      'tiktok' => $footerData['tiktok'] ?? ''
  ];
  
  // Footer classes
  $footerClasses = 'bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-300 relative z-20 w-full';
@endphp

<!-- Footer -->
<footer class="{{ $footerClasses }}">
  <div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Contact Us -->
    <div>
      <h2 class="font-semibold text-lg mb-3 text-gray-900 dark:text-white">{{ $footerTitle }}</h2>
      @if($footerDescription)
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
          {{ $footerDescription }}
        </p>
      @endif
      @if($footerEmail)
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
          <a href="mailto:{{ $footerEmail }}" class="hover:text-qricket-600 dark:hover:text-qricket-400 transition-colors">
            <i class="fas fa-envelope mr-2"></i>{{ $footerEmail }}
          </a>
        </p>
      @endif
      
      @if(array_filter($socialLinks))
        <div class="flex space-x-4 text-2xl text-gray-500 dark:text-gray-400">
          @if($socialLinks['facebook'])
            <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Facebook">
              <i class="fab fa-facebook"></i>
            </a>
          @endif
          @if($socialLinks['twitter'])
            <a href="{{ $socialLinks['twitter'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-sky-500 dark:hover:text-sky-400 transition-colors" title="Twitter/X">
              <i class="fab fa-x-twitter"></i>
            </a>
          @endif
          @if($socialLinks['instagram'])
            <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-pink-500 dark:hover:text-pink-400 transition-colors" title="Instagram">
              <i class="fab fa-instagram"></i>
            </a>
          @endif
          @if($socialLinks['youtube'])
            <a href="{{ $socialLinks['youtube'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-red-600 dark:hover:text-red-400 transition-colors" title="YouTube">
              <i class="fab fa-youtube"></i>
            </a>
          @endif
          @if($socialLinks['tiktok'])
            <a href="{{ $socialLinks['tiktok'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-black dark:hover:text-white transition-colors" title="TikTok">
              <i class="fab fa-tiktok"></i>
            </a>
          @endif
        </div>
      @endif
    </div>
  </div>

  <!-- Bottom Bar -->
  <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-900 dark:text-gray-400 py-4 px-6 flex flex-col md:flex-row justify-between items-center">
    <p>© {{ date('Y') }} {{ $event->title ?? 'Event' }}. All rights reserved.</p>
    <div class="flex space-x-4 mt-2 md:mt-0">
      <a href="#" class="hover:text-gray-800 dark:hover:text-gray-200 transition-colors">Privacy Policy</a>
      <a href="#" class="hover:text-gray-800 dark:hover:text-gray-200 transition-colors">Terms of Service</a>
      <a href="#" class="hover:text-gray-800 dark:hover:text-gray-200 transition-colors">Cookies Settings</a>
    </div>
  </div>
</footer>
 