<flux:card>
    <form wire:submit="save" class="flex flex-col gap-4">
        
        @if($formMode === 'edit_program')
            
            <flux:input wire:model="title" label="{{ __('Usulan Program') }}" placeholder="{{ __('Contoh: Penyuluhan Kesehatan Masyarakat') }}" />
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:textarea wire:model="problem_potential" label="{{ __('Potensi / Permasalahan') }}" placeholder="{{ __('Sebutkan potensi atau masalah') }}" rows="2" />
                <flux:textarea wire:model="location" label="{{ __('Lokasi / Narsum') }}" placeholder="{{ __('Sebutkan lokasi atau narasumber') }}" rows="2" />
            </div>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:textarea wire:model="method" label="{{ __('Metode Pelaksanaan') }}" placeholder="{{ __('Metode yang digunakan') }}" rows="2" />
                <flux:textarea wire:model="target_audience" label="{{ __('Kelompok Sasaran') }}" placeholder="{{ __('Siapa kelompok sasarannya?') }}" rows="2" />
            </div>
            
            <flux:textarea wire:model="output_target" label="{{ __('Luaran') }}" placeholder="{{ __('Luaran / Output yang diharapkan') }}" rows="2" />

        @elseif($formMode === 'create_individual')
            @if($type === \App\Enums\ProgramType::SosialKemasyarakatan->value)
                <flux:input wire:model="title" label="{{ __('Nama Program Sosial Kemasyarakatan') }}" placeholder="{{ __('Contoh: Bakti Sosial Soshum') }}" />
            @else
                <flux:input wire:model="title" label="{{ __('Nama Program Lainnya') }}" placeholder="{{ __('Contoh: Lomba 17 Agustus') }}" />
            @endif
            
            <flux:input wire:model="role_in_program" label="{{ __('Peran Anda') }}" placeholder="{{ __('Contoh: Koordinator Lapangan, Pemateri, dll.') }}" class="mt-2" />
            <flux:textarea wire:model="responsibility" label="{{ __('Deskripsi Tugas dan Tanggung Jawab') }}" rows="3" />

        @elseif($formMode === 'edit_peran')
            <div class="p-3 bg-neutral-100 dark:bg-zinc-800 rounded-lg mb-2">
                <flux:text variant="strong">{{ $title }}</flux:text>
                <flux:text class="text-xs">{{ __('Silakan isi peran spesifik Anda di dalam program ini.') }}</flux:text>
            </div>
            
            <flux:input wire:model="role_in_program" label="{{ __('Peran Anda') }}" placeholder="{{ __('Contoh: Koordinator Lapangan, Pemateri, dll.') }}" />
            <flux:textarea wire:model="responsibility" label="{{ __('Deskripsi Tugas dan Tanggung Jawab') }}" rows="3" />

        @elseif($formMode === 'lpk')
            <div class="p-3 bg-neutral-100 dark:bg-zinc-800 rounded-lg mb-2">
                <flux:text variant="strong">{{ $title }}</flux:text>
                <flux:text class="text-xs">{{ __('Isi capaian pelaksanaan program, hambatan, dan solusi.') }}</flux:text>
            </div>
            
            <flux:textarea wire:model="achievement" label="{{ __('Capaian Pelaksanaan') }}" placeholder="{{ __('Apa saja yang berhasil dicapai selama pelaksanaan program?') }}" rows="3" />
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:textarea wire:model="obstacle" label="{{ __('Hambatan') }}" placeholder="{{ __('Hambatan yang dihadapi') }}" rows="3" />
                <flux:textarea wire:model="solution" label="{{ __('Solusi') }}" placeholder="{{ __('Solusi untuk mengatasi hambatan') }}" rows="3" />
            </div>

        @endif

        <div class="flex gap-2 justify-end mt-4">
            <flux:button href="{{ route('programs.index') }}" wire:navigate variant="ghost">{{ __('Batal') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
        </div>
    </form>
</flux:card>
