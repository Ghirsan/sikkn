<div class="flex h-full w-full flex-1 flex-col gap-6">
    <flux:card>
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                <flux:icon name="document-check" class="size-5 text-green-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Status LPK') }}</flux:heading>
                <flux:text class="text-sm text-neutral-500">{{ __('Laporan Pelaksanaan Kegiatan') }}</flux:text>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
            <flux:text class="text-sm">{{ __('Status Keseluruhan') }}</flux:text>
            <flux:badge color="{{ $allLpkApproved ? 'green' : 'zinc' }}" size="sm">{{ $allLpkApproved ? __('Siap Cetak') : __('Belum Siap') }}</flux:badge>
        </div>
        <flux:text class="mt-3 text-sm text-neutral-500">
            {{ $lpkApprovedCount }}/{{ $totalPrograms }} {{ __('LPK disetujui') }}
        </flux:text>
    </flux:card>

    @if($allApproved && $allLpkApproved)
        <flux:card class="border-2 border-green-300 bg-green-50/50 dark:border-green-700 dark:bg-green-900/10">
            <div class="flex items-start gap-3">
                <flux:icon name="check-circle" class="size-6 shrink-0 text-green-600" />
                <div class="w-full">
                    <flux:heading size="lg">{{ __('PDF LPK Siap Dicetak!') }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-neutral-600">{{ __('Seluruh pelaksanaan program kerja tim telah disetujui DPL.') }}</flux:text>
                    <div class="mt-3">
                        <flux:button href="{{ route('lpk.pdf', $group) }}" target="_blank" variant="filled" color="green" icon="document-arrow-down" size="sm">{{ __('Cetak PDF LPK') }}</flux:button>
                    </div>
                </div>
            </div>
        </flux:card>
    @else
        <flux:card class="border-2 border-dashed border-blue-300 bg-blue-50/50 dark:border-blue-700 dark:bg-blue-900/10">
            <div class="flex items-start gap-3">
                <flux:icon name="information-circle" class="size-6 shrink-0 text-blue-600" />
                <div>
                    <flux:heading size="lg">{{ __('PDF LPK Belum Siap') }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-neutral-600">{{ __('Tombol cetak akan aktif setelah seluruh pelaksanaan program kerja disetujui DPL.') }}</flux:text>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- LPK Details --}}
    <flux:card class="!p-0">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Rincian Pelaksanaan (LPK)') }}</flux:heading>
        </div>
        @if($programs->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="document-text" class="mx-auto size-12 text-neutral-300" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Program') }}</flux:heading>
            </div>
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
