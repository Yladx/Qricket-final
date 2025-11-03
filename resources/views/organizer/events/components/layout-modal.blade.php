<!-- Layout Selection Modal -->
<div id="layoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" onclick="if(event.target === this) closeLayoutModal()">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Choose Layout Component</h3>
            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); if(typeof closeLayoutModal === 'function') closeLayoutModal(); return false;" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- All Layouts Combined -->
        @php
            $user = Auth::user();
            $isAdmin = Auth::guard('admin')->check();
            $plan = $user?->currentSubscription()?->getPlan();
            $hasAdvancedLayouts = $isAdmin || ($plan?->advanced_layouts ?? false) || ($plan?->hasFeature('advanced_layouts') ?? false);
            $hasActiveSubscription = $user?->hasActiveSubscription() ?? false;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Event Details Cards -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('event-details-cards')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="grid grid-cols-2 gap-3 h-full">
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3 shadow-sm">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3 shadow-sm">
                            <div class="w-8 h-8 bg-green-500 rounded-lg mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-2/3"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3 shadow-sm">
                            <div class="w-8 h-8 bg-purple-500 rounded-lg mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-4/5"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3 shadow-sm">
                            <div class="w-8 h-8 bg-orange-500 rounded-lg mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event Details Cards</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Grid of information cards highlighting key event details</p>
                </div>
            </div>

            <!-- About Two Images -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('about-two-images')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="flex space-x-3 h-full">
                        <div class="flex-1 bg-gradient-to-br from-green-400 to-blue-500 rounded-lg"></div>
                        <div class="flex-1 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg"></div>
                    </div>
                    <div class="mt-3 bg-white dark:bg-gray-600 rounded-lg p-3">
                        <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-2"></div>
                        <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">About Two Images</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Side-by-side images with descriptive text about the event</p>
                </div>
            </div>

            <!-- Left Text Right Image -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('left-text-right-image')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="flex space-x-3 h-full">
                        <div class="flex-1 bg-white dark:bg-gray-600 rounded-lg p-3">
                            <div class="h-3 bg-gray-300 dark:bg-gray-500 rounded mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                        </div>
                        <div class="flex-1 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-lg"></div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Left Text Right Image</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Text content on the left with image on the right</p>
                </div>
            </div>

            <!-- Right Text Left Image -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('right-text-left-image')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="flex space-x-3 h-full">
                        <div class="flex-1 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg"></div>
                        <div class="flex-1 bg-white dark:bg-gray-600 rounded-lg p-3">
                            <div class="h-3 bg-gray-300 dark:bg-gray-500 rounded mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Right Text Left Image</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Image on the left with text content on the right</p>
                </div>
            </div>

            <!-- Center Text -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('center-text')">
                <div class="h-48 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 p-4">
                    <div class="text-center space-y-3">
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3">
                            <div class="h-4 bg-gray-300 dark:bg-gray-500 rounded w-3/4 mx-auto mb-2"></div>
                            <div class="h-3 bg-gray-300 dark:bg-gray-500 rounded w-1/2 mx-auto mb-1"></div>
                            <div class="h-3 bg-gray-300 dark:bg-gray-500 rounded w-2/3 mx-auto"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Center Text</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Centered text layout with gradient background and emphasis</p>
                </div>
            </div>

            <!-- Speakers Grid -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('speakers-grid')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="grid grid-cols-3 gap-2 h-full">
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-2 text-center">
                            <div class="w-12 h-12 bg-blue-500 rounded-full mx-auto mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-2/3 mx-auto"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-2 text-center">
                            <div class="w-12 h-12 bg-green-500 rounded-full mx-auto mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-2/3 mx-auto"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-2 text-center">
                            <div class="w-12 h-12 bg-purple-500 rounded-full mx-auto mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-2/3 mx-auto"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Speakers Grid</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Showcase event speakers with photos and brief descriptions</p>
                </div>
            </div>

            <!-- Photo Highlights -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('photo-highlights')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="grid grid-cols-2 gap-2 h-full">
                        <div class="bg-gradient-to-br from-red-400 to-pink-500 rounded-lg"></div>
                        <div class="bg-gradient-to-br from-blue-400 to-indigo-500 rounded-lg"></div>
                        <div class="bg-gradient-to-br from-green-400 to-teal-500 rounded-lg"></div>
                        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg"></div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Photo Highlights</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Gallery of photos showcasing event highlights and moments</p>
                </div>
            </div>

            <!-- FAQ List -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('faq-list')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="space-y-3 h-full overflow-hidden">
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3">
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3">
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-2/3"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3">
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-2"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-4/5"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">FAQ List</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Expandable FAQ section to answer common questions</p>
                </div>
            </div>

            <!-- Tickets Pricing -->
            <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="selectLayout('tickets-pricing')">
                <div class="h-48 bg-gray-100 dark:bg-gray-700 p-4">
                    <div class="space-y-3 h-full overflow-hidden">
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3 border-2 border-green-500">
                            <div class="flex justify-between items-center mb-2">
                                <div class="h-3 bg-gray-300 dark:bg-gray-500 rounded w-1/3"></div>
                                <div class="h-4 bg-green-500 rounded w-12"></div>
                            </div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-2/3"></div>
                        </div>
                        <div class="bg-white dark:bg-gray-600 rounded-lg p-3">
                            <div class="flex justify-between items-center mb-2">
                                <div class="h-3 bg-gray-300 dark:bg-gray-500 rounded w-1/2"></div>
                                <div class="h-4 bg-gray-400 rounded w-16"></div>
                            </div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mb-1"></div>
                            <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Tickets Pricing</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Pricing cards for different ticket tiers and options</p>
                </div>
            </div>

            <!-- Premium: Hero Spotlight -->
                <div class="border border-amber-300 dark:border-amber-600 rounded-lg overflow-hidden transition-shadow cursor-pointer relative {{ ($hasAdvancedLayouts || $isAdmin) ? 'hover:shadow-lg' : 'opacity-50 cursor-not-allowed blur-sm' }}" 
                     onclick="{{ ($hasAdvancedLayouts || $isAdmin) ? "selectLayout('premium-hero-spotlight')" : '' }}">
                    <div class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded">Premium</div>
                    <div class="h-48 bg-gradient-to-br from-amber-400 to-pink-500 relative">
                        <div class="absolute inset-0 bg-black/20"></div>
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <div class="bg-white/20 rounded-lg p-3 backdrop-blur-sm">
                                <h4 class="font-semibold text-sm mb-1">Spotlight Title</h4>
                                <p class="text-xs opacity-90">Subtitle & CTA</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hero Spotlight</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Bold hero with spotlight image and call-to-action</p>
                    </div>
                </div>

                <!-- Premium: Gallery Mosaic -->
                <div class="border border-amber-300 dark:border-amber-600 rounded-lg overflow-hidden transition-shadow cursor-pointer relative {{ ($hasAdvancedLayouts || $isAdmin) ? 'hover:shadow-lg' : 'opacity-50 cursor-not-allowed blur-sm' }}" 
                     onclick="{{ ($hasAdvancedLayouts || $isAdmin) ? "selectLayout('premium-gallery-mosaic')" : '' }}">
                    <div class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded">Premium</div>
                    <div class="h-48 bg-gray-200 dark:bg-gray-700 grid grid-cols-3 gap-1 p-1">
                        <div class="bg-gray-400/60"></div>
                        <div class="bg-gray-400/60 col-span-2"></div>
                        <div class="bg-gray-400/60 col-span-2"></div>
                        <div class="bg-gray-400/60"></div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Gallery Mosaic</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Creative mosaic photo gallery for highlights</p>
                    </div>
                </div>

                <!-- Premium: Schedule Timeline -->
                <div class="border border-amber-300 dark:border-amber-600 rounded-lg overflow-hidden transition-shadow cursor-pointer relative {{ ($hasAdvancedLayouts || $isAdmin) ? 'hover:shadow-lg' : 'opacity-50 cursor-not-allowed blur-sm' }}" 
                     onclick="{{ ($hasAdvancedLayouts || $isAdmin) ? "selectLayout('premium-schedule-timeline')" : '' }}">
                    <div class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded">Premium</div>
                    <div class="h-48 bg-gradient-to-b from-slate-600 to-slate-800 relative">
                        <div class="absolute inset-0 flex flex-col justify-center px-6 text-white text-xs space-y-1">
                            <div class="opacity-80">09:00 — Registration</div>
                            <div class="opacity-80">10:00 — Opening Keynote</div>
                            <div class="opacity-80">13:00 — Workshops</div>
                        </div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Timeline</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Vertical timeline for event schedules</p>
                    </div>
                </div>

                <!-- Premium: Testimonials Slider -->
                <div class="border border-amber-300 dark:border-amber-600 rounded-lg overflow-hidden transition-shadow cursor-pointer relative {{ ($hasAdvancedLayouts || $isAdmin) ? 'hover:shadow-lg' : 'opacity-50 cursor-not-allowed blur-sm' }}" 
                     onclick="{{ ($hasAdvancedLayouts || $isAdmin) ? "selectLayout('premium-testimonials-slider')" : '' }}">
                    <div class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded">Premium</div>
                    <div class="h-48 bg-gradient-to-br from-fuchsia-500 to-indigo-600 relative">
                        <div class="absolute inset-0 flex items-center justify-center text-white text-sm">“Amazing event!”</div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Testimonials Slider</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Auto-playing quotes from attendees/speakers</p>
                    </div>
                </div>

                <!-- Premium: Sponsors Grid -->
                <div class="border border-amber-300 dark:border-amber-600 rounded-lg overflow-hidden transition-shadow cursor-pointer relative {{ ($hasAdvancedLayouts || $isAdmin) ? 'hover:shadow-lg' : 'opacity-50 cursor-not-allowed blur-sm' }}" 
                     onclick="{{ ($hasAdvancedLayouts || $isAdmin) ? "selectLayout('premium-sponsors-grid')" : '' }}">
                    <div class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded">Premium</div>
                    <div class="h-48 grid grid-cols-4 gap-2 p-4 bg-gray-100 dark:bg-gray-700">
                        <div class="bg-white/60 dark:bg-white/10 rounded"></div>
                        <div class="bg-white/60 dark:bg-white/10 rounded"></div>
                        <div class="bg-white/60 dark:bg-white/10 rounded"></div>
                        <div class="bg-white/60 dark:bg-white/10 rounded"></div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sponsors Grid</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Logo wall with responsive layout</p>
                    </div>
                </div>

                <!-- Premium: Hero Carousel -->
                <div class="border border-amber-300 dark:border-amber-600 rounded-lg overflow-hidden transition-shadow cursor-pointer relative {{ ($hasAdvancedLayouts || $isAdmin) ? 'hover:shadow-lg' : 'opacity-50 cursor-not-allowed blur-sm' }}" 
                     onclick="{{ ($hasAdvancedLayouts || $isAdmin) ? "selectLayout('hero-carousel')" : '' }}">
                    <div class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2 py-0.5 rounded">Premium</div>
                    @unless($hasAdvancedLayouts || $isAdmin)
                        <div class="absolute inset-0 flex items-center justify-center z-10">
                            <a href="{{ route('organizer.subscription.plans') }}" class="px-3 py-1.5 text-xs rounded bg-qricket-500 text-white shadow hover:bg-qricket-600 transition-colors">
                                Upgrade to unlock
                            </a>
                        </div>
                    @endunless
                    <div class="h-48 bg-gradient-to-br from-blue-500 to-purple-600 relative">
                        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <div class="bg-white bg-opacity-20 rounded-lg p-3 backdrop-blur-sm">
                                <h4 class="font-semibold text-sm mb-1">Event Title</h4>
                                <p class="text-xs opacity-90">Event Date & Location</p>
                            </div>
                        </div>
                        <!-- Carousel indicators -->
                        <div class="absolute top-4 right-4 flex space-x-1">
                            <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                            <div class="w-2 h-2 bg-white rounded-full"></div>
                            <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                        </div>
                    </div>
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hero Carousel</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Eye-catching hero section with image carousel and event details overlay</p>
                    </div>
                </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); if(typeof closeLayoutModal === 'function') closeLayoutModal(); return false;" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Cancel
            </button>
        </div>
    </div>
</div>