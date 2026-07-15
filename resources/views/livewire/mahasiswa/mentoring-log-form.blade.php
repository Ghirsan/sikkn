<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="{{ route('dashboard') }}">{{ __('Dashboard') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('mentoring-logs.index') }}">{{ __('Pembimbingan') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $logId ? __('Edit Catatan') : __('Tambah Catatan') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">{{ $logId ? __('Edit Catatan Pembimbingan') : __('Catatan Pembimbingan Baru') }}</flux:heading>
            <flux:subheading>{{ __('Catat detail kegiatan pembimbingan dan diskusi bersama DPL Anda.') }}</flux:subheading>
        </div>
    </div>

    <flux:card>
        <form wire:submit="saveLog" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <flux:input type="date" wire:model="date" label="Tanggal Pembimbingan" :disabled="$logId !== null" />
                </div>
                <div>
                    <flux:select wire:model="program_id" label="Terkait Program (Opsional)" placeholder="Pilih Program">
                        <flux:select.option value="">Umum / Tidak spesifik program</flux:select.option>
                        @foreach($programs as $program)
                            <flux:select.option value="{{ $program->id }}">[{{ $program->type }}] {{ $program->title }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div>
                <flux:input wire:model="topic" label="Kegiatan / Topik" placeholder="Contoh: Konsultasi penyusunan LRK..." />
            </div>

            <div>
                <flux:textarea wire:model="discussion_summary" label="Uraian & Hambatan" placeholder="Tuliskan uraian kegiatan pembimbingan serta hambatan yang dihadapi..." rows="5" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <flux:input wire:model="target_group" label="Kelompok Sasaran" placeholder="Contoh: Perangkat Desa" />
                </div>
                <div>
                    <flux:input type="number" wire:model="student_count" label="Jumlah Mahasiswa Terlibat" placeholder="Contoh: 5" min="1" />
                </div>
                <div>
                    <flux:input wire:model="output" label="Luaran Kegiatan" placeholder="Contoh: Laporan Kegiatan, Video" />
                </div>
            </div>

            <div class="flex gap-2 pt-4">
                <flux:spacer />
                <flux:button variant="ghost" href="{{ route('mentoring-logs.index') }}" wire:navigate>{{ __('Batal') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Simpan Catatan') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
