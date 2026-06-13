<div class="flex h-full w-full flex-1 flex-col gap-6">
    @if(!$periodCompleted)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Penilaian Belum Dibuka') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Formulir penilaian akan terbuka setelah masa pelaksanaan KKN berakhir.') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Dinilai')" :value="$stats['graded']" />
        <x-stat-card icon="clock" color="amber" :label="__('Belum Dinilai')" :value="$stats['ungraded']" />
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
    </div>

    <flux:card>

        @if($students->isEmpty())
            <x-empty-state icon="academic-cap" :heading="__('Belum Ada Mahasiswa')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Nilai Akhir') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$student->name" :initials="$student->initials()" size="sm" />
                                <span class="font-medium">{{ $student->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $student->nim }}</flux:table.cell>
                            <flux:table.cell>{{ $student->prodi }}</flux:table.cell>
                            <flux:table.cell>
                                @if($student->grade)
                                    <flux:badge color="green" inset="top bottom">{{ $student->grade->grade_letter }} ({{ $student->grade->final_grade }})</flux:badge>
                                @else
                                    <flux:badge color="zinc" inset="top bottom">{{ __('Belum Dinilai') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
