@props(['label', 'name', 'value' => 0])

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $label }}</label>
    <div data-rating-group class="flex items-center space-x-1">
        <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
        @for($i = 1; $i <= 5; $i++)
            <button type="button" 
                    data-star 
                    value="{{ $i }}" 
                    class="w-6 h-6 text-gray-400 dark:text-gray-500 hover:text-yellow-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-opacity-50 rounded">
                <svg fill="currentColor" viewBox="0 0 20 20" class="w-full h-full">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
            </button>
        @endfor
    </div>
</div>