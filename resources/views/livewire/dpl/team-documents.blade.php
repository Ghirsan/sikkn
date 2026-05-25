<div class="flex h-full w-full flex-1 flex-col gap-6">
    @forelse($groupData as $data)
        <flux:card class="!p-0">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ $data->group->name }} — {{ $data->group->village }}</flux:heading>
                    <flux:badge :color="$data->allApproved ? 'green' : 'zinc'">{{ $data->allApproved ? __('Siap PDF') : $data->approvedCount.'/'.$data->totalPrograms.' approved' }}</flux:badge>
                </div>
            </div>
            <div class="grid gap-4 p-6 md:grid-cols-2">
                <flux:card class="border border-neutral-200 dark:border-neutral-700">
                    <flux:heading size="sm">{{ __('LRK') }}</flux:heading>
                    <flux:badge size="sm" class="mt-2" :color="$data->allApproved ? 'green' : 'zinc'">{{ $data->allApproved ? __('Siap Cetak') : __('Belum Siap') }}</flux:badge>
                </flux:card>
                <flux:card class="border border-neutral-200 dark:border-neutral-700">
                    <flux:heading size="sm">{{ __('LPK') }}</flux:heading>
                    <flux:badge size="sm" class="mt-2" color="zinc">{{ __('Belum Dibuat') }}</flux:badge>
                </flux:card>
            </div>
            @if($data->approvedPrograms->isNotEmpty())
                <div class="border-t border-neutral-200 px-6 py-4 dark:border-neutral-700">
                    <flux:text class="mb-2 text-sm font-medium text-neutral-500">{{ __('Program Disetujui:') }}</flux:text>
                    @foreach($data->approvedPrograms as $program)
                        <flux:text class="text-sm">• {{ $program->title }} ({{ $program->student->name }})</flux:text>
                    @endforeach
                </div>
            @endif
        </flux:card>
    @empty
        <flux:card class="text-center">
            <flux:icon name="document-text" class="mx-auto size-12 text-neutral-300" />
            <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Kelompok') }}</flux:heading>
        </flux:card>
    @endforelse
</div>
