<div class="flex h-full w-full flex-1 flex-col gap-6">
    <flux:card class="flex items-center gap-3">
        <flux:icon.document-text variant="mini" class="text-blue-500" />
        <div class="flex-1">
            <flux:heading size="lg">{{ __('Status LRK') }}</flux:heading>
            <flux:text>{{ __('Laporan Rencana Kegiatan') }}</flux:text>
        </div>
        <flux:badge :color="$allApproved ? 'green' : 'zinc'" size="sm">{{ $allApproved ? __('Siap Cetak') : __('Belum Siap') }}</flux:badge>
    </flux:card>

    @if($allApproved)
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.heading>{{ __('PDF LRK Siap Dicetak!') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Seluruh rencana program kerja tim telah disetujui DPL.') }}</flux:callout.text>
            <x-slot name="actions">
                <flux:button href="{{ route('lrk.pdf', $group) }}" target="_blank" icon="document-arrow-down">{{ __('Cetak PDF LRK') }}</flux:button>
            </x-slot>
        </flux:callout>
    @else
        <flux:callout variant="warning" icon="information-circle">
            <flux:callout.heading>{{ __('PDF LRK Belum Siap') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Tombol cetak akan aktif setelah seluruh rencana program kerja disetujui DPL.') }} {{ $approvedCount }}/{{ $totalPrograms }} {{ __('program disetujui') }}.</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Approved Programs --}}
    <flux:card>
        <flux:heading size="lg">{{ __('Rincian Program (LRK)') }}</flux:heading>

        <flux:separator />

        @if($programs->isEmpty())
            <x-empty-state icon="document-text" :heading="__('Belum Ada Program Disetujui')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($programs as $program)
                        <flux:table.row :key="$program->id">
                            <flux:table.cell variant="strong">{{ $program->title }}</flux:table.cell>
                            <flux:table.cell>{{ $program->student->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $program->type->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
