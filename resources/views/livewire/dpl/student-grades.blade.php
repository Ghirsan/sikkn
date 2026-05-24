<div class="flex h-full w-full flex-1 flex-col gap-6">
    @if(!$periodCompleted)
        <div class="rounded-xl border-2 border-dashed border-amber-300 bg-amber-50/50 p-6 dark:border-amber-700 dark:bg-amber-900/10">
            <div class="flex items-start gap-3">
                <flux:icon name="exclamation-triangle" class="size-6 shrink-0 text-amber-600" />
                <div>
                    <flux:heading size="lg">{{ __('Penilaian Belum Dibuka') }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-neutral-600">{{ __('Formulir penilaian akan terbuka setelah masa pelaksanaan KKN berakhir.') }}</flux:text>
                </div>
            </div>
        </div>
    @endif

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon name="academic-cap" class="size-5 text-blue-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Total Mahasiswa') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['total'] }}</flux:text>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Sudah Dinilai') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['graded'] }}</flux:text>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Belum Dinilai') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['ungraded'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
        </div>
        @if($students->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="academic-cap" class="mx-auto size-12 text-neutral-300" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Mahasiswa') }}</flux:heading>
            </div>
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
    </div>
</div>
