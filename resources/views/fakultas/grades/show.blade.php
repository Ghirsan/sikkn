<x-layouts::app
    :title="__('Detail Nilai')"
    :breadcrumbs="[
        ['label' => __('Penilaian Mahasiswa'), 'url' => route('fakultas.grades.index')],
        ['label' => __('Detail Nilai')],
    ]"
>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Detail Penilaian Mahasiswa') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Rincian aspek penilaian dan konversi nilai SIAP mahasiswa.') }}</flux:text>
            </div>
            <flux:button href="{{ route('fakultas.grades.index') }}" wire:navigate icon="arrow-left" variant="ghost">{{ __('Kembali') }}</flux:button>
        </div>
        <flux:separator />
        
        <div class="w-full">
            <livewire:observer.grade-detail :student="request()->route('student')" />
        </div>
    </div>
</x-layouts::app>

