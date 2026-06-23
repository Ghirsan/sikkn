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
            <flux:callout.text>{{ __('Tombol cetak akan aktif setelah seluruh rencana program kerja disetujui DPL.') }} {{ $approvedCount }}/{{ $totalParticipants }} {{ __('rencana disetujui') }}.</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Approved Programs --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Rincian Program (LRK)') }}</flux:heading>
    </div>

    <flux:card>

        @if($approvedParticipants->isEmpty())
            <x-empty-state icon="document-text" :heading="__('Belum Ada Program Disetujui')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Kode') }}</flux:table.column>
                    <flux:table.column>{{ __('Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($approvedParticipants as $participant)
                        <flux:table.row :key="$participant->id">
                            <flux:table.cell>{{ $participant->program->getProgramCodeFor($participant->student_id) }}</flux:table.cell>
                            <flux:table.cell variant="strong">{{ $participant->program->title }}</flux:table.cell>
                            <flux:table.cell>{{ $participant->student->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $participant->program->type->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
