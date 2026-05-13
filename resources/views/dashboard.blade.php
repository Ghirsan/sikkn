<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        {{-- Role Badge & Welcome --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Selamat Datang, :name', ['name' => auth()->user()->name]) }}</flux:heading>
                <flux:text class="mt-1">{{ __('Dashboard :role — SIKKN', ['role' => auth()->user()->role->shortLabel()]) }}</flux:text>
            </div>
            <flux:badge :color="auth()->user()->role->color()" size="lg" class="w-fit">
                {{ auth()->user()->role->label() }}
            </flux:badge>
        </div>

        <flux:separator />

        {{-- Role-Specific Dashboard Panel --}}
        @include('dashboard.panels.' . auth()->user()->role->value)
    </div>
</x-layouts::app>
