<x-filament-panels::page>
    @php
        $projects = $this->getProjects();
    @endphp

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Project</label>
            <select wire:model.live="projectFilter" class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 focus:ring-2 focus:ring-primary-500">
                <option value="">All projects</option>
                @foreach($projects as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Min flakiness score</label>
            <select wire:model.live="minScore" class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 focus:ring-2 focus:ring-primary-500">
                <option value="0">Any</option>
                <option value="10">≥ 10%</option>
                <option value="20">≥ 20%</option>
                <option value="40">≥ 40%</option>
            </select>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
