<x-admin-layout title="Qricket Feedback" page-title="Qricket Feedback">
    @php
    $renderStars = function($value) {
    if (is_null($value)) return '';
    $max = 5; $fill = max(0, min($max, round($value)));
    $html='';
    for ($i=1; $i<=$max; $i++) {
        $on=$i <=$fill;
        $html .='<svg class="w-3.5 h-3.5 ' .($on?'text-yellow-400':'text-gray-300 dark:text-gray-600').'" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.803 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118L10 13.347l-2.974 2.136c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L3.39 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>';
        }
        return $html;
        };

        $ratingMetrics = [
        ['label' => 'Navigation', 'key' => 'avg_navigation'],
        ['label' => 'Information', 'key' => 'avg_information'],
        ['label' => 'Design', 'key' => 'avg_design'],
        ['label' => 'Performance', 'key' => 'avg_performance'],
        ['label' => 'Process', 'key' => 'avg_process'],
        ];

        $overallScore = round(collect($ratingMetrics)->avg(fn($metric) => $stats[$metric['key']] ?? 0), 1);
        $overallPercent = min(100, max(0, ($overallScore / 5) * 100));

        $recentComments = collect($feedback->items())
        ->filter(fn($item) => filled($item->additional_comments))
        ->take(4);
        @endphp

        <div class="space-y-6">
            <!-- Snapshot -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Feedback</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['total']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">All time submissions collected</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Today</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($stats['today']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Feedback received in the last 24 hours</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Overall Satisfaction</p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($overallScore, 1) }}</span>
                        <div class="flex items-center space-x-0.5">{!! $renderStars($overallScore) !!}</div>
                    </div>
                    @php $overallStyle = 'style="width: ' . number_format($overallPercent, 2, '.', '') . '%;"'; @endphp
                    <div class="mt-3 h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                        <div class="h-2 bg-qricket-500 rounded-full" {!! $overallStyle !!}></div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Average across all categories</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 shadow-sm">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Latest Submission</p>
                    @php $latest = $feedback->first(); @endphp
                    @if($latest)
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $latest->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Most recent feedback received</p>
                    @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No feedback yet</p>
                    @endif
                </div>
            </div>

            <!-- Ratings + Comments -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="xl:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Category Scores</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Scale 1 – 5</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($ratingMetrics as $metric)
                        @php
                        $value = $stats[$metric['key']] ?? 0;
                        $percent = min(100, max(0, ($value / 5) * 100));
                        $barStyle = 'style="width: ' . number_format($percent, 2, '.', '') . '%;"';
                        @endphp
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-gray-50 dark:bg-gray-800/40">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ $metric['label'] }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($value, 1) }}</span>
                                <div class="flex items-center space-x-0.5">{!! $renderStars($value) !!}</div>
                            </div>
                            <div class="mt-3 h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                                <div class="h-2 bg-qricket-500 rounded-full" {!! $barStyle !!}></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Recent Comments</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">From current page</span>
                    </div>
                    <div class="p-4 space-y-3 text-sm">
                        @forelse($recentComments as $item)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-gray-50 dark:bg-gray-800/40">
                            <p class="text-gray-800 dark:text-gray-200 leading-snug">“{{ \Illuminate\Support\Str::limit($item->additional_comments, 160) }}”</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $item->created_at->timezone(config('app.timezone'))->diffForHumans() }}</p>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No comments on this page.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Feedback Table -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide">All Feedback</h3>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Page {{ $feedback->currentPage() }} of {{ $feedback->lastPage() }}</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="px-4 py-3 text-left">Submitted</th>
                                @foreach($ratingMetrics as $metric)
                                <th class="px-4 py-3 text-left">{{ $metric['label'] }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-left">Overall</th>
                                <th class="px-4 py-3 text-left">Comments</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($feedback as $item)
                            @php
                            $scores = collect($ratingMetrics)
                            ->map(fn($metric) => $item->{$metric['key']} ?? null)
                            ->filter(fn($value) => !is_null($value));
                            $averageScore = $scores->isNotEmpty() ? round($scores->avg(), 1) : null;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->created_at->timezone(config('app.timezone'))->format('M j, Y') }}</div>
                                    <div>{{ $item->created_at->timezone(config('app.timezone'))->format('g:i A') }}</div>
                                </td>
                                @foreach($ratingMetrics as $metric)
                                @php $value = $item->{$metric['key']}; @endphp
                                <td class="px-4 py-3 text-xs">
                                    @if(!is_null($value))
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center rounded-full bg-qricket-500/10 text-qricket-600 dark:text-qricket-400 px-2 py-0.5 text-[11px] font-semibold">{{ number_format($value, 1) }}</span>
                                        <div class="flex items-center space-x-0.5">{!! $renderStars($value) !!}</div>
                                    </div>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                @endforeach
                                <td class="px-4 py-3 text-xs">
                                    @if(!is_null($averageScore))
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 text-[11px] font-semibold">{{ number_format($averageScore, 1) }}</span>
                                        <div class="flex items-center space-x-0.5">{!! $renderStars($averageScore) !!}</div>
                                    </div>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300 max-w-xs">
                                    @if($item->additional_comments)
                                    <p class="line-clamp-3">{{ $item->additional_comments }}</p>
                                    @else
                                    <span class="text-gray-400">No comments</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No feedback recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    {{ $feedback->links() }}
                </div>
            </div>
        </div>
</x-admin-layout>
