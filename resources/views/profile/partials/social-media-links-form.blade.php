<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('Social Media Links') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Add your social media profiles so others can connect with you.') }}
        </p>
    </header>

    <form id="social-media-form" method="post" action="{{ route('profile.social-media.update') }}" class="mt-4 space-y-4">
        @csrf
        @method('patch')

        @php
            $platforms = [
                'facebook' => ['name' => 'Facebook', 'url_format' => 'https://facebook.com/{username}'],
                'instagram' => ['name' => 'Instagram', 'url_format' => 'https://instagram.com/{username}'],
                'linkedin' => ['name' => 'LinkedIn', 'url_format' => 'https://linkedin.com/in/{username}'],
                'twitter' => ['name' => 'Twitter / X', 'url_format' => 'https://twitter.com/{username}'],
                'youtube' => ['name' => 'YouTube', 'url_format' => 'https://youtube.com/@{username}'],
                'tiktok' => ['name' => 'TikTok', 'url_format' => 'https://tiktok.com/@{username}'],
            ];
            
            $existingLinks = $user->socialMediaLinks()->get()->keyBy('platform');
        @endphp

        <!-- Social Media Fields Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($platforms as $platform => $info)
                @php
                    $link = $existingLinks->get($platform);
                    // Show URL if available, otherwise show username, otherwise empty
                    $value = $link->url ?? ($link->username ?? '');
                @endphp
                <div>
                    <x-input-label for="social_{{ $platform }}" :value="$info['name']" />
                    
                    <input 
                        id="social_{{ $platform }}" 
                        name="social[{{ $platform }}][value]" 
                        type="text" 
                        class="mt-1 block w-full social-media-input bg-white text-gray-900 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-qricket-500 focus:ring-qricket-500 rounded-md shadow-sm" 
                        placeholder="{{ str_replace('{username}', 'username', $info['url_format']) }}"
                        value="{{ old("social.{$platform}.value", $value) }}"
                        autocomplete="off"
                    />
                    <x-input-error :messages="$errors->get('social.'.$platform.'.value')" class="mt-2" />
                    
                    <input type="hidden" name="social[{{ $platform }}][platform]" value="{{ $platform }}">
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'social-media-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    <script>
        // Ensure social media inputs are always enabled and never blocked
        // Run this after everything else to override any disabling
        (function() {
            function ensureSocialMediaEnabled() {
                const socialMediaForm = document.getElementById('social-media-form');
                if (!socialMediaForm) return;
                
                const socialInputs = socialMediaForm.querySelectorAll('input[type="text"]');
                socialInputs.forEach(input => {
                    // Force remove disabled attribute
                    input.removeAttribute('disabled');
                    input.disabled = false;
                    
                    // Force styling to show it's editable
                    input.style.pointerEvents = 'auto';
                    input.style.opacity = '1';
                    input.style.cursor = 'text';
                    input.style.backgroundColor = '';
                    input.classList.remove('disabled', 'opacity-50');
                    
                    // Make sure it's not readonly
                    input.removeAttribute('readonly');
                    input.readOnly = false;
                });
            }
            
            // Run immediately and on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', ensureSocialMediaEnabled);
            } else {
                ensureSocialMediaEnabled();
            }
            
            // Also run after a small delay to catch any late-running scripts
            setTimeout(ensureSocialMediaEnabled, 100);
            setTimeout(ensureSocialMediaEnabled, 500);
            
            // Monitor for any attempts to disable these inputs
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes') {
                        const target = mutation.target;
                        const socialMediaForm = document.getElementById('social-media-form');
                        if (socialMediaForm && socialMediaForm.contains(target)) {
                            if (target.name && target.name.includes('social')) {
                                if (mutation.attributeName === 'disabled' || target.disabled) {
                                    target.removeAttribute('disabled');
                                    target.disabled = false;
                                    target.style.pointerEvents = 'auto';
                                    target.style.opacity = '1';
                                }
                            }
                        }
                    }
                });
            });

            // Observe the entire social media form
            const socialMediaForm = document.getElementById('social-media-form');
            if (socialMediaForm) {
                observer.observe(socialMediaForm, { 
                    attributes: true, 
                    attributeFilter: ['disabled', 'readonly'],
                    childList: true,
                    subtree: true 
                });
            }
        })();
    </script>
</section>
