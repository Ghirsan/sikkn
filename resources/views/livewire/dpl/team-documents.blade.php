<div class="flex h-full w-full flex-1 flex-col gap-6">
    @forelse($groupData as $data)
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">{{ $data->group->name }} — {{ $data->group->village }}</flux:heading>
            <flux:badge :color="$data->allApproved ? 'green' : 'zinc'">{{ $data->allApproved ? __('Siap PDF') : $data->approvedCount.'/'.$data->totalPrograms.' approved' }}</flux:badge>
        </div>

        <flux:card class="mb-8">

            <div class="grid gap-4 md:grid-cols-2">
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
                <flux:separator />

                <div>
                    <flux:text variant="strong" class="mb-2 text-sm">{{ __('Program Disetujui:') }}</flux:text>
                    @foreach($data->approvedPrograms as $program)
                        <flux:text class="text-sm">• {{ $program->title }} ({{ $program->student->name }})</flux:text>
                    @endforeach
                </div>
            @endif
        </flux:card>
    @empty
        <flux:card>
            <x-empty-state icon="document-text" :heading="__('Belum Ada Kelompok')" />
        </flux:card>
    @endforelse
</div>
