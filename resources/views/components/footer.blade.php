<!-- Footer Component -->
<footer class="bg-qricket-500 text-white py-10 px-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10">

        <!-- Logo -->
        <div>
            <h2 class="text-2xl font-bold mb-2">Qricket</h2>
            <p class="text-sm">Your event, your code. Effortless ticketing and QR-based check-ins for everyone.</p>
        </div>

        <!-- Quick Links -->
        <div>
            <h3 class="text-lg font-semibold mb-3">Quick Links</h3>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('welcome') }}" class="hover:underline transition-colors duration-200">Home</a></li>
                <li><a href="{{ route('events.browse') }}" class="hover:underline transition-colors duration-200">Browse Events</a></li>
                <li><a href="{{ route('subscription.index') }}" class="hover:underline transition-colors duration-200">Subscription Plans</a></li>
                <li><a href="{{ route('help.index') }}" class="hover:underline transition-colors duration-200">Help & Support</a></li>
        @if (Route::has('login'))
                @auth
                        <li><a href="{{ route('organizer.dashboard') }}" class="hover:underline transition-colors duration-200">Dashboard</a></li>
                @else
                        <li><a href="{{ route('login') }}" class="hover:underline transition-colors duration-200">Login</a></li>
                    @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="hover:underline transition-colors duration-200">Sign Up</a></li>
                    @endif
                @endauth
        @endif
                </ul>
            </div>

        <!-- Contact + Social Icons -->
        <div>
            <h3 class="text-lg font-semibold mb-3">Contact</h3>
            <p class="text-sm mb-2">Email: support@qricket.com</p>
            <p class="text-sm mb-4">Phone: +63 912 345 6789</p>

            <!-- Social Media Icon Placeholders -->
            <div class="flex space-x-3 mt-2">
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-qricket-500 font-bold text-sm transition-all duration-200 transform hover:scale-110">F</div>
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-qricket-500 font-bold text-sm transition-all duration-200 transform hover:scale-110">T</div>
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-qricket-500 font-bold text-sm transition-all duration-200 transform hover:scale-110">I</div>
            </div>
        </div>

    </div>

    <!-- Bottom bar -->
    <div class="mt-10 text-center text-sm border-t border-white border-opacity-20 pt-4">
        &copy; 2025 Qricket. All rights reserved.
    </div>
</footer>
