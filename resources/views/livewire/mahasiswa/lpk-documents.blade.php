<div class="flex h-full w-full flex-1 flex-col gap-6">
    <flux:card class="flex items-center gap-3">
        <flux:icon.document-check variant="mini" class="text-green-500" />
        <div class="flex-1">
            <flux:heading size="lg">{{ __('Status LPK') }}</flux:heading>
            <flux:text>{{ __('Laporan Pelaksanaan Kegiatan') }}</flux:text>
        </div>
        <flux:badge :color="$allLpkApproved ? 'green' : 'zinc'" size="sm">{{ $allLpkApproved ? __('Siap Cetak') : __('Belum Siap') }}</flux:badge>
    </flux:card>

    @if($allApproved && $allLpkApproved)
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.heading>{{ __('PDF LPK Siap Dicetak!') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Seluruh pelaksanaan program kerja tim telah disetujui DPL.') }}</flux:callout.text>
            <x-slot name="actions">
                <flux:button href="{{ route('lpk.pdf', $group) }}" target="_blank" icon="document-arrow-down">{{ __('Cetak PDF LPK') }}</flux:button>
            </x-slot>
        </flux:callout>
    @else
        <flux:callout variant="warning" icon="information-circle">
            <flux:callout.heading>{{ __('PDF LPK Belum Siap') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Tombol cetak akan aktif setelah seluruh pelaksanaan program kerja disetujui DPL.') }} {{ $lpkApprovedCount }}/{{ $totalPrograms }} {{ __('LPK disetujui') }}.</flux:callout.text>
        </flux:callout>
    @endif

    {{-- LPK Details --}}
    <flux:card>
        <flux:heading size="lg">{{ __('Rincian Pelaksanaan (LPK)') }}</flux:heading>

        <flux:separator />

        @if($programs->isEmpty())
            <x-empty-state icon="document-text" :heading="__('Belum Ada Program')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Status LPK') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($programs as $program)
                        <flux:table.row :key="$program->id">
                            <flux:table.cell variant="strong">{{ $program->title }}</flux:table.cell>
                            <flux:table.cell>{{ $program->student->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$program->lpk_status === \App\Enums\ProgramStatus::Approved ? 'green' : 'zinc'">
                                    {{ $program->lpk_status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
