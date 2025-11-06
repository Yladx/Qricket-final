@php
    $items = isset($details['timeline']) && is_array($details['timeline']) ? $details['timeline'] : [];
@endphp
<div class="p-4 md:p-6 w-full">
    <!-- Title (Left-Aligned) -->
    <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4 md:mb-6">
        Schedule Timeline
    </h2>

    <!-- Table (Full Width) -->
    <div class="overflow-x-auto w-full -mx-4 md:mx-0">
        <table class="w-full text-xs md:text-sm text-left text-gray-900 dark:text-gray-300 border-separate border-spacing-y-2 min-w-[400px]">
            <thead>
                <tr class="text-gray-900 dark:text-gray-400 text-xs uppercase tracking-wide font-semibold">
                    <th class="px-3 md:px-4 py-2 text-left">Time</th>
                    <th class="px-3 md:px-4 py-2 text-left">Activity</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $it)
                    <tr class="bg-gray-100 dark:bg-gray-800/50 hover:bg-gray-200 dark:hover:bg-gray-800 transition rounded-lg">
                        <td class="px-3 md:px-4 py-2 font-semibold text-qricket-600 dark:text-qricket-400 whitespace-nowrap">{{ $it['time'] ?? '' }}</td>
                        <td class="px-3 md:px-4 py-2 text-gray-900 dark:text-gray-200">{{ $it['item'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-3 md:px-4 py-3 text-center text-gray-900 dark:text-gray-500 italic">No schedule items available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
