@props(['currentStep' => 1])


@php
$steps = [
    1 => ['label' => 'Basic Info', 'short_label' => 'Info'],
    2 => ['label' => 'Design & Layout', 'short_label' => 'Design'],
    3 => ['label' => 'Ticket Pricing', 'short_label' => 'Tickets'],
    4 => ['label' => 'Certificate Template', 'short_label' => 'Certificate'],
    5 => ['label' => 'Staff Management', 'short_label' => 'Staff'],
    6 => ['label' => 'Review & Publish', 'short_label' => 'Review'],
];
@endphp

<div class="mb-6">
    <!-- Desktop Progress Bar -->
    <div class="hidden md:block">
        <div class="flex items-center justify-between">
            @foreach($steps as $stepNumber => $step)
                <div class="flex items-center {{ $stepNumber < count($steps) ? 'flex-1' : '' }}">
                    <!-- Step Circle -->
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 
                        @if($stepNumber < $currentStep) bg-qricket-600 border-qricket-600 text-white
                        @elseif($stepNumber === $currentStep) bg-qricket-600 border-qricket-600 text-white
                        @else bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 @endif">
                        @if($stepNumber < $currentStep)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <span class="text-sm font-medium">{{ $stepNumber }}</span>
                        @endif
                    </div>

                    <!-- Step Label -->
                    <div class="ml-3">
                        <p class="text-sm font-medium 
                            @if($stepNumber <= $currentStep) text-qricket-600 dark:text-qricket-400
                            @else text-gray-500 dark:text-gray-400 @endif">
                            {{ $step['label'] }}
                        </p>
                    </div>

                    <!-- Connector Line -->
                    @if($stepNumber < count($steps))
                        <div class="flex-1 mx-3">
                            <div class="h-0.5 bg-gray-200 dark:bg-gray-700">
                                <div class="h-full bg-qricket-600 transition-all duration-300 
                                    @if($stepNumber < $currentStep) w-full @else w-0 @endif"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Mobile Progress Bar -->
    <div class="md:hidden">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Step {{ $currentStep }} of {{ count($steps) }}
            </span>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $steps[$currentStep]['label'] }}
            </span>
        </div>
        
        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div class="bg-qricket-600 h-2 rounded-full transition-all duration-300" 
                 style="width: {{ ($currentStep / count($steps)) * 100 }}%"></div>
        </div>

        <!-- Step Indicators -->
        <div class="flex justify-between mt-3">
            @foreach($steps as $stepNumber => $step)
                <div class="flex flex-col items-center">
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center text-xs font-medium
                        @if($stepNumber < $currentStep) bg-qricket-600 border-qricket-600 text-white
                        @elseif($stepNumber === $currentStep) bg-qricket-600 border-qricket-600 text-white
                        @else bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 @endif">
                        @if($stepNumber < $currentStep)
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            {{ $stepNumber }}
                        @endif
                    </div>
                    <span class="text-xs mt-1 text-center
                        @if($stepNumber <= $currentStep) text-qricket-600 dark:text-qricket-400
                        @else text-gray-500 dark:text-gray-400 @endif">
                        {{ $step['short_label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>








