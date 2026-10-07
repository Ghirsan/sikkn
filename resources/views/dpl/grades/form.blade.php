<x-layouts::app
    :title="__('Form Penilaian')"
    :breadcrumbs="[
        ['label' => __('Penilaian Mahasiswa'), 'url' => route('dpl.grades.index')],
        ['label' => __('Form Penilaian')],
    ]"
>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Form Penilaian Mahasiswa') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Masukkan nilai (0-100) untuk setiap aspek penilaian.') }}</flux:text>
            </div>
            <flux:button href="{{ route('dpl.grades.index') }}" wire:navigate icon="arrow-left" variant="ghost">{{ __('Kembali') }}</flux:button>
        </div>
        <flux:separator />
        
        <div class="w-full">
            <livewire:dpl.student-grade-form />
        </div>
    </div>
</x-layouts::app>

