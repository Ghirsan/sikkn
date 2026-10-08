{{--  --}}<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Logbook Mahasiswa') }}</flux:heading>
        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" placeholder="{{ __('Semua Program Studi') }}" size="sm" class="w-48">
                <option value="">{{ __('Semua Program Studi') }}</option>
                @foreach($studyPrograms as $prodi)
                    <option value="{{ $prodi->id }}">{{ $prodi->name }}</option>
                @endforeach
            </flux:select>
            @endif

            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM...') }}" size="sm" class="w-64" />
        </div>
    </div>

    <flux:card>
        @if($logs->isEmpty())
            <x-empty-state icon="book-open" :heading="__('Tidak Ada Data Logbook')" />
        @else
            <flux:table :paginate="$logs">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Kegiatan') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $log->student->name }}</div>
                                <div class="text-xs text-neutral-500">{{ $log->student->nim }} &middot; {{ $log->student->prodi }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>{{ \Carbon\Carbon::parse($log->date)->translatedFormat('l, d M Y') }}</div>
                                <div class="text-xs text-neutral-500">{{ $log->activities->count() }} aktivitas</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($log->activities->isNotEmpty())
                                    <div class="line-clamp-2 max-w-sm text-sm" title="{{ $log->activities->first()->activity_description }}">
                                        &bull; {{ $log->activities->first()->activity_description }}
                                        @if($log->activities->count() > 1)
                                            <span class="text-xs text-neutral-500">(+{{ $log->activities->count() - 1 }} lainnya)</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-neutral-400">Tidak ada deskripsi</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $log->status->color() }}">{{ $log->status->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>

