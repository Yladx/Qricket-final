<x-unified-layout :layout-type="$layoutType" :title="'Feedback Management - ' . $event->title" :page-title="'Feedback Management'">
    <x-slot name="header">
        <x-manage-header :event="$event" />
    </x-slot>
    
    <div class="py-4">
        <div class="max-w-6xl mx-auto px-4 sm:px-4 lg:px-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-base font-bold text-gray-900 dark:text-white">Event Feedback</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">View and analyze feedback from {{ $event->title }} attendees</p>
                </div>
                @if(!$isAdmin)
                <div class="flex items-center gap-2">
                    @if(optional($event)->end_date && optional($event)->end_date->isPast())
                        <form method="POST" action="{{ route('events.feedback.notify', $event) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm transition text-xs">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Request Feedback
                            </button>
                        </form>
                    @endif
                </div>
                @endif
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Submissions</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $totalSubmissions }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Attendees</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $totalAttendees }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Response Rate</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $responseRate }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Averages with Collapsible Details -->
            @if(!empty($averageRatings))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-3 mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Average Ratings by Category</h3>
                
                @foreach($averageRatings as $categoryKey => $categoryData)
                    @php
                        $categoryLabels = [
                            'overall_experience' => 'Overall Experience',
                            'logistics' => 'Logistics & Organization'
                        ];
                        $categoryLabel = $categoryLabels[$categoryKey] ?? ucfirst(str_replace('_', ' ', $categoryKey));
                    @endphp
                    
                    <div class="mb-3 last:mb-0">
                        <!-- Category Header (Collapsible) -->
                        <button type="button" class="w-full flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition" onclick="toggleCategory('{{ $categoryKey }}')">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $categoryLabel }}</span>
                                <div class="flex items-center ml-3">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white mr-2">{{ $categoryData['average'] }}</span>
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3 h-3 {{ $i <= $categoryData['average'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            <svg id="icon-{{ $categoryKey }}" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Category Details (Collapsible Content) -->
                        <div id="content-{{ $categoryKey }}" class="hidden mt-2 pl-2">
                            <div class="space-y-1">
                                @foreach($categoryData['subcategories'] as $subKey => $subValue)
                                    @php
                                        $subLabels = [
                                            'experience' => 'Overall Experience',
                                            'expectations' => 'Met Expectations',
                                            'organization' => 'Organization',
                                            'attend_again' => 'Would Attend Again',
                                            'recommend' => 'Would Recommend',
                                            'schedule' => 'Schedule & Timing',
                                            'registration' => 'Registration Process',
                                            'venue' => 'Venue/Platform Quality',
                                            'technical' => 'Technical Setup',
                                            'staff' => 'Staff Helpfulness'
                                        ];
                                        $subLabel = $subLabels[$subKey] ?? ucfirst(str_replace('_', ' ', $subKey));
                                    @endphp
                                    <div class="flex items-center justify-between py-1 px-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                        <span class="text-xs text-gray-700 dark:text-gray-300">{{ $subLabel }}</span>
                                        <div class="flex items-center">
                                            <span class="text-xs font-medium text-gray-900 dark:text-white mr-1">{{ $subValue }}</span>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-2 h-2 {{ $i <= $subValue ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Feedback Submissions -->
            @if($feedbackSubmissions->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Feedback Submissions</h3>
                            
                            <!-- Filter Buttons -->
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="filterFeedback('all')" class="filter-btn active px-2 py-1 text-xs font-medium rounded-md bg-qricket-600 text-white" data-filter="all">
                                    All
                                </button>
                                <button type="button" onclick="filterFeedback('liked')" class="filter-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600" data-filter="liked">
                                    Liked
                                </button>
                                <button type="button" onclick="filterFeedback('improvements')" class="filter-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600" data-filter="improvements">
                                    Improvements
                                </button>
                                <button type="button" onclick="filterFeedback('additional')" class="filter-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600" data-filter="additional">
                                    Additional
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading Indicator -->
                    <div id="feedback-loading" class="hidden px-3 py-4 text-center">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-qricket-600"></div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2">Loading feedback...</p>
                    </div>
                    <div id="feedback-list-container" class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($feedbackSubmissions as $feedback)
                            <div class="p-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <div class="flex-shrink-0">
                                                @php
                                                    $userName = $feedback->user->full_name ?? ($feedback->user->first_name . ' ' . $feedback->user->last_name) ?? ($feedback->user->email ?? 'Anonymous');
                                                    $userInitial = !empty($feedback->user->first_name) ? substr($feedback->user->first_name, 0, 1) : (isset($feedback->user->email) ? substr($feedback->user->email, 0, 1) : 'A');
                                                @endphp
                                                @if($feedback->user->profile_picture_url)
                                                    <img class="w-6 h-6 rounded-full" src="{{ $feedback->user->profile_picture_url }}" alt="{{ $userName }}">
                                                @else
                                                    <div class="w-6 h-6 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                                            {{ strtoupper($userInitial) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-2">
                                                <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $userName }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $feedback->created_at->format('M j, Y g:i A') }}</p>
                                            </div>
                                        </div>
                                        
                                        @php
                                            $responses = is_string($feedback->responses) ? json_decode($feedback->responses, true) : $feedback->responses;
                                        @endphp
                                        
                                        @if($responses)
                                        <div class="mt-2 space-y-2">
                                            @if($canViewDetailedRatings)
                                            <!-- Overall Experience -->
                                            @if(isset($responses['overall_experience']))
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2">
                                                <h4 class="text-xs font-medium text-gray-900 dark:text-white mb-1">Overall Experience</h4>
                                                <div class="grid grid-cols-2 md:grid-cols-5 gap-1">
                                                    @foreach(['experience' => 'Experience', 'expectations' => 'Expectations', 'organization' => 'Organization', 'attend_again' => 'Attend Again', 'recommend' => 'Recommend'] as $key => $label)
                                                        @if(isset($responses['overall_experience'][$key]))
                                                        <div class="text-center">
                                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</p>
                                                            <div class="flex items-center justify-center">
                                                                <span class="text-xs font-semibold text-gray-900 dark:text-white mr-1">{{ $responses['overall_experience'][$key] }}</span>
                                                                <div class="flex">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        <svg class="w-2 h-2 {{ $i <= $responses['overall_experience'][$key] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                        </svg>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                            
                                            <!-- Logistics -->
                                            @if(isset($responses['logistics']))
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-2">
                                                <h4 class="text-xs font-medium text-gray-900 dark:text-white mb-1">Logistics & Organization</h4>
                                                <div class="grid grid-cols-2 md:grid-cols-5 gap-1">
                                                    @foreach(['schedule' => 'Schedule', 'registration' => 'Registration', 'venue' => 'Venue', 'technical' => 'Technical', 'staff' => 'Staff'] as $key => $label)
                                                        @if(isset($responses['logistics'][$key]))
                                                        <div class="text-center">
                                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</p>
                                                            <div class="flex items-center justify-center">
                                                                <span class="text-xs font-semibold text-gray-900 dark:text-white mr-1">{{ $responses['logistics'][$key] }}</span>
                                                                <div class="flex">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        <svg class="w-2 h-2 {{ $i <= $responses['logistics'][$key] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                        </svg>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                            @endif
                                            
                                            <!-- Comments Section - Always show regardless of subscription -->
                                            @if(isset($responses['comments']) && (isset($responses['comments']['liked']) || isset($responses['comments']['improvements']) || isset($responses['comments']['additional'])))
                                            <div class="mt-3 space-y-3">
                                                @if(!empty($responses['comments']['liked']) && trim($responses['comments']['liked']) !== '')
                                                <div class="flex items-start space-x-3 feedback-comment" data-comment-type="liked">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl px-3 py-2">
                                                            <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $responses['comments']['liked'] }}</p>
                                                        </div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-3">What they liked</p>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if(!empty($responses['comments']['improvements']) && trim($responses['comments']['improvements']) !== '')
                                                <div class="flex items-start space-x-3 feedback-comment" data-comment-type="improvements">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl px-3 py-2">
                                                            <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $responses['comments']['improvements'] }}</p>
                                                        </div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-3">Suggestions for improvement</p>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                @if(!empty($responses['comments']['additional']) && trim($responses['comments']['additional']) !== '')
                                                <div class="flex items-start space-x-3 feedback-comment" data-comment-type="additional">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl px-3 py-2">
                                                            <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $responses['comments']['additional'] }}</p>
                                                        </div>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-3">Additional thoughts</p>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div id="pagination-container" class="px-3 py-2 border-t border-gray-200 dark:border-gray-700">
                        {{ $feedbackSubmissions->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-6">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No feedback submissions yet</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Individual feedback submissions will appear here once attendees start submitting their responses.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleCategory(categoryKey) {
            const content = document.getElementById('content-' + categoryKey);
            const icon = document.getElementById('icon-' + categoryKey);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
        
        // AJAX Filtering for Feedback Comments
        let currentFilter = 'all';
        let currentPage = 1;
        
        function filterFeedback(filter) {
            currentFilter = filter;
            currentPage = 1;
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.getAttribute('data-filter') === filter) {
                    btn.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                    btn.classList.add('bg-qricket-600', 'text-white');
                    btn.classList.remove('hover:bg-gray-300', 'dark:hover:bg-gray-600');
                } else {
                    btn.classList.remove('bg-qricket-600', 'text-white');
                    btn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                    btn.classList.add('hover:bg-gray-300', 'dark:hover:bg-gray-600');
                }
            });
            
            loadFilteredFeedback();
        }
        
        function loadFilteredFeedback(page = 1) {
            currentPage = page;
            
            // Show loading indicator
            document.getElementById('feedback-loading').classList.remove('hidden');
            document.getElementById('feedback-list-container').style.display = 'none';
            
            // AJAX request
            fetch(`{{ route($isAdmin ? 'admin.event.feedback' : 'organizer.events.feedback', $event) }}?filter=${currentFilter}&page=${page}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                // Parse the HTML and extract only the feedback list
                const parser = new DOMParser();
                const doc = parser.parseFromString(data.html, 'text/html');
                const newFeedbackList = doc.querySelector('.divide-y');
                const newPagination = doc.querySelector('.px-3.py-2.border-t');
                
                if (newFeedbackList) {
                    document.getElementById('feedback-list-container').innerHTML = newFeedbackList.innerHTML;
                    if (newPagination) {
                        const paginationContainer = document.getElementById('pagination-container');
                        if (paginationContainer) {
                            paginationContainer.innerHTML = newPagination.innerHTML;
                        }
                    }
                }
                
                document.getElementById('feedback-loading').classList.add('hidden');
                document.getElementById('feedback-list-container').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading feedback:', error);
                document.getElementById('feedback-loading').classList.add('hidden');
                alert('Failed to load feedback. Please try again.');
            });
        }
        
        // Pagination click handler
        document.addEventListener('click', function(e) {
            if (e.target.matches('.pagination a') || e.target.closest('.pagination a')) {
                e.preventDefault();
                const url = e.target.href || e.target.closest('a').href;
                const page = new URL(url).searchParams.get('page') || 1;
                loadFilteredFeedback(page);
            }
        });
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Mark initial state
            currentFilter = 'all';
        });
    </script>
</x-unified-layout>
