<x-filament-panels::page>

    {{-- Step indicator --}}
    <div class="flex items-center justify-center gap-2 mb-8">
        @foreach(['Framework', 'Scenarios', 'Configuration', 'Download'] as $i => $label)
            @php $step = $i + 1; @endphp
            <button
                wire:click="goToStep({{ $step }})"
                class="flex items-center gap-2 group"
            >
                <span @class([
                    'flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold transition-colors',
                    'bg-primary-600 text-white'                                                    => $currentStep === $step,
                    'bg-success-500 text-white'                                                    => $currentStep > $step,
                    'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 group-hover:bg-gray-300 dark:group-hover:bg-gray-600' => $currentStep < $step,
                ])>
                    @if($currentStep > $step)
                        <x-heroicon-s-check class="w-4 h-4" />
                    @else
                        {{ $step }}
                    @endif
                </span>
                <span @class([
                    'hidden sm:block text-sm font-medium transition-colors',
                    'text-primary-600 dark:text-primary-400' => $currentStep === $step,
                    'text-success-600 dark:text-success-400' => $currentStep > $step,
                    'text-gray-500 dark:text-gray-400'       => $currentStep < $step,
                ])>{{ $label }}</span>
            </button>

            @if($step < 4)
                <div @class([
                    'flex-1 h-px max-w-16',
                    'bg-success-500' => $currentStep > $step,
                    'bg-gray-200 dark:bg-gray-700' => $currentStep <= $step,
                ])></div>
            @endif
        @endforeach
    </div>

    {{-- Step content --}}
    @if($currentStep === 1)
        @include('filament.pages.test-generator.step-1-framework')
    @elseif($currentStep === 2)
        @include('filament.pages.test-generator.step-2-scenarios')
    @elseif($currentStep === 3)
        @include('filament.pages.test-generator.step-3-configuration')
    @elseif($currentStep === 4)
        @include('filament.pages.test-generator.step-4-download')
    @endif

</x-filament-panels::page>
