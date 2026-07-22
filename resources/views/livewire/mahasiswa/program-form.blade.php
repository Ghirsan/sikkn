<flux:card>
    <form wire:submit="save" class="flex flex-col gap-4">
        @if($status === \App\Enums\ProgramStatus::NeedsRevision->value && $revision_note)
            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 rounded-lg">
                <flux:text class="text-xs text-red-600 dark:text-red-400 mb-1 font-semibold">{{ __('Catatan Revisi') }}</flux:text>
                <flux:text class="text-red-800 dark:text-red-200" variant="strong">{{ $revision_note }}</flux:text>
            </div>
        @endif
        
        @if($action === 'create' && $type === \App\Enums\ProgramType::Multidisiplin->value)
            <flux:select wire:model.live="programId" label="{{ __('Pilih Tema Multidisiplin') }}" placeholder="{{ __('Pilih tema yang tersedia...') }}">
                @foreach($availableMultidisiplinPrograms as $prog)
                    <flux:select.option value="{{ $prog->id }}">{{ $prog->title ?: '(Tema Belum Diisi)' }}</flux:select.option>
                @endforeach
            </flux:select>
        @endif

        @if($formMode === 'edit_program')
            
            @if(!($action === 'create' && $type === \App\Enums\ProgramType::Multidisiplin->value))
                <div class="p-3 bg-neutral-100 dark:bg-zinc-800 rounded-lg mb-4">
                    <flux:text class="text-xs text-zinc-500 mb-1">{{ __('Tema Multidisiplin') }}</flux:text>
                    <flux:text variant="strong">{{ $title }}</flux:text>
                </div>
            @endif
            
            <flux:input wire:model="participant_title" label="{{ __('Usulan Program (Spesifik)') }}" placeholder="{{ __('Contoh: Penyuluhan Kesehatan Masyarakat') }}" class="mb-4" />
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:textarea wire:model="problem_potential" label="{{ __('Potensi / Permasalahan') }}" placeholder="{{ __('Sebutkan potensi atau masalah') }}" rows="2" />
                <flux:textarea wire:model="location" label="{{ __('Lokasi / Narsum') }}" placeholder="{{ __('Sebutkan lokasi atau narasumber') }}" rows="2" />
            </div>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:textarea wire:model="method" label="{{ __('Metode Pelaksanaan') }}" placeholder="{{ __('Metode yang digunakan') }}" rows="2" />
                <flux:textarea wire:model="target_audience" label="{{ __('Kelompok Sasaran') }}" placeholder="{{ __('Siapa kelompok sasarannya?') }}" rows="2" />
            </div>
            <flux:textarea wire:model="output_target" label="{{ __('Luaran') }}" placeholder="{{ __('Luaran / Output yang diharapkan') }}" rows="2" />
            <flux:input type="date" wire:model="execution_date" label="{{ __('Tanggal Pelaksanaan') }}" />
        @elseif($formMode === 'create_individual')
            @if($type === \App\Enums\ProgramType::SosialKemasyarakatan->value)
                <flux:input wire:model="title" label="{{ __('Nama Program Sosial Kemasyarakatan') }}" placeholder="{{ __('Contoh: Bakti Sosial Soshum') }}" />
            @else
                <flux:input wire:model="title" label="{{ __('Nama Program Lainnya') }}" placeholder="{{ __('Contoh: Lomba 17 Agustus') }}" />
            @endif
            
            <flux:input type="date" wire:model="execution_date" label="{{ __('Tanggal Pelaksanaan') }}" class="mt-2" />
            <flux:input wire:model="role_in_program" label="{{ __('Peran Anda') }}" placeholder="{{ __('Contoh: Koordinator Lapangan, Pemateri, dll.') }}" class="mt-2" />
            <flux:textarea wire:model="responsibility" label="{{ __('Deskripsi Tugas dan Tanggung Jawab') }}" rows="3" />

        @elseif($formMode === 'edit_peran')
            @if(!($action === 'create' && $type === \App\Enums\ProgramType::Multidisiplin->value))
                <div class="p-3 bg-neutral-100 dark:bg-zinc-800 rounded-lg mb-2">
                    <flux:text variant="strong">{{ $title }}</flux:text>
                </div>
            @endif
            <flux:input type="date" wire:model="execution_date" label="{{ __('Tanggal Pelaksanaan') }}" />
            <flux:input wire:model="role_in_program" label="{{ __('Peran Anda') }}" placeholder="{{ __('Contoh: Koordinator Lapangan, Pemateri, dll.') }}" />
            <flux:textarea wire:model="responsibility" label="{{ __('Deskripsi Tugas dan Tanggung Jawab') }}" rows="3" />

        @elseif($formMode === 'lpk')
            <div class="p-3 bg-neutral-100 dark:bg-zinc-800 rounded-lg mb-2">
                <flux:text variant="strong">{{ $title }}</flux:text>
                <flux:text class="text-xs">{{ __('Isi capaian pelaksanaan program, hambatan, dan solusi.') }}</flux:text>
            </div>
            
            @if($isLpkMultidisiplin)
                <flux:textarea wire:model="execution_description" label="{{ __('Pelaksanaan Kegiatan') }}" placeholder="{{ __('Jelaskan pelaksanaan kegiatan secara rinci...') }}" rows="3" />
                <flux:textarea wire:model="achievement" label="{{ __('Ketercapaian') }}" placeholder="{{ __('Apa saja yang berhasil dicapai selama pelaksanaan program?') }}" rows="3" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:textarea wire:model="obstacle" label="{{ __('Hambatan') }}" placeholder="{{ __('Hambatan yang dihadapi') }}" rows="3" />
                    <flux:textarea wire:model="solution" label="{{ __('Tindak Lanjut') }}" placeholder="{{ __('Tindak lanjut atau solusi untuk mengatasi hambatan') }}" rows="3" />
                </div>
            @else
                <flux:textarea wire:model="achievement" label="{{ __('Hasil') }}" placeholder="{{ __('Jelaskan hasil pelaksanaan sesuai peran dan tanggung jawab Anda...') }}" rows="4" />
            @endif

        @endif

        <div class="flex gap-2 justify-end mt-4">
            <flux:button href="{{ route('programs.index') }}" wire:navigate variant="ghost">{{ __('Batal') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
        </div>
    </form>
</flux:card>
