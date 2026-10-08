<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Buku Pembimbingan') }}</flux:heading>
        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" placeholder="{{ __('Semua Program Studi') }}" size="sm" class="w-48">
                <option value="">{{ __('Semua Program Studi') }}</option>
                @foreach($studyPrograms as $prodi)
                    <option value="{{ $prodi->id }}">{{ $prodi->name }}</option>
                @endforeach
            </flux:select>
            @endif

            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari topik, nama...') }}" size="sm" class="w-64" />
        </div>
    </div>

    <flux:card>
        @if($logs->isEmpty())
            <x-empty-state icon="clipboard-document-list" :heading="__('Tidak Ada Data Pembimbingan')" />
        @else
            <flux:table :paginate="$logs">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Topik Pembimbingan') }}</flux:table.column>
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
                                <div>{{ \Carbon\Carbon::parse($log->date)->translatedFormat('d M Y') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="line-clamp-2 max-w-sm text-sm" title="{{ $log->topic }}">
                                    {{ $log->topic }}
                                </div>
                                @if($log->program)
                                    <div class="mt-1 text-xs text-neutral-500 line-clamp-1" title="{{ $log->program->title }}">
                                        Prog: {{ $log->program->title }}
                                    </div>
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

