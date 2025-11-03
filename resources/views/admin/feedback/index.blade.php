<x-admin-layout title="Qricket Feedback" page-title="Qricket Feedback">
    @php
        $renderStars = function($value) {
            if (is_null($value)) return '';
            $max = 5; $fill = max(0, min($max, round($value)));
            $html='';
            for ($i=1; $i<=$max; $i++) {
                $on = $i <= $fill;
                $html .= '<svg class="w-4 h-4 '.($on?'text-yellow-400':'text-gray-300 dark:text-gray-600').'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.803 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118L10 13.347l-2.974 2.136c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L3.39 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
            }
            return $html;
        };
    @endphp
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Overview</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-sm">
                <div class="p-3 rounded bg-gray-50 dark:bg-gray-700/40">
                    <div class="text-gray-500 dark:text-gray-400">Total</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</div>
                </div>
                <div class="p-3 rounded bg-gray-50 dark:bg-gray-700/40">
                    <div class="text-gray-500 dark:text-gray-400">Today</div>
                    <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['today'] }}</div>
                </div>
                @foreach([
                    ['label'=>'Navigation','key'=>'avg_navigation'],
                    ['label'=>'Information','key'=>'avg_information'],
                    ['label'=>'Design','key'=>'avg_design'],
                    ['label'=>'Performance','key'=>'avg_performance'],
                    ['label'=>'Process','key'=>'avg_process']
                ] as $m)
                <div class="p-3 rounded bg-gray-50 dark:bg-gray-700/40">
                    <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                        <span>{{ $m['label'] }}</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $stats[$m['key']] }}</span>
                    </div>
                    <div class="mt-1 flex items-center space-x-1">{!! $renderStars($stats[$m['key']]) !!}</div>
                    <div class="mt-2 h-2 bg-gray-200 dark:bg-gray-700 rounded">
                        @php $pct = min(100, max(0, ($stats[$m['key']]/5)*100)); @endphp
                        <div class="h-2 bg-qricket-500 rounded" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Feedback</h3>
                <div class="text-xs text-gray-500 dark:text-gray-400">Page {{ $feedback->currentPage() }} of {{ $feedback->lastPage() }}</div>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($feedback as $item)
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->created_at->format('M j, Y g:i A') }}</div>
                            <div class="flex items-center gap-4">
                                @foreach([
                                    ['label'=>'Nav','key'=>'navigation_ease'],
                                    ['label'=>'Info','key'=>'information_found'],
                                    ['label'=>'Design','key'=>'design_rating'],
                                    ['label'=>'Perf','key'=>'performance_rating'],
                                    ['label'=>'Process','key'=>'process_satisfaction']
                                ] as $m)
                                    @php $val = $item->{$m['key']}; @endphp
                                    @if(!is_null($val))
                                        <div class="flex items-center gap-1 text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">{{ $m['label'] }}</span>
                                            <div class="flex items-center space-x-0.5">{!! $renderStars($val) !!}</div>
                                            <span class="text-gray-600 dark:text-gray-300 font-semibold">{{ round($val,1) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @if($item->additional_comments)
                            <div class="mt-3 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $item->additional_comments }}</div>
                        @else
                            <div class="mt-3 text-sm text-gray-400">No comments</div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">No feedback yet.</div>
                @endforelse
            </div>
            <div class="p-4">{{ $feedback->links() }}</div>
        </div>
    </div>
</x-admin-layout>


