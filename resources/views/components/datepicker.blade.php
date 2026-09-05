@props([
    'label' => null,
    'min' => null,
    'max' => null,
    'placeholder' => 'Pilih tanggal...',
])

<div
    x-data="{
        open: false,
        dropUp: false,
        value: @entangle($attributes->wire('model')),
        viewMonth: null,
        viewYear: null,
        minDate: '{{ $min }}' ? new Date('{{ $min }}' + 'T00:00:00') : null,
        maxDate: '{{ $max }}' ? new Date('{{ $max }}' + 'T00:00:00') : null,
        dayNames: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],

        init() {
            if (this.value) {
                const d = new Date(this.value + 'T00:00:00');
                this.viewMonth = d.getMonth();
                this.viewYear = d.getFullYear();
            } else {
                const now = new Date();
                this.viewMonth = now.getMonth();
                this.viewYear = now.getFullYear();
            }
        },

        get displayValue() {
            if (!this.value) return '';
            const d = new Date(this.value + 'T00:00:00');
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        get monthYearLabel() {
            const d = new Date(this.viewYear, this.viewMonth, 1);
            return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        },

        get calendarDays() {
            const days = [];
            const firstDay = new Date(this.viewYear, this.viewMonth, 1);
            const lastDay = new Date(this.viewYear, this.viewMonth + 1, 0);
            const startPad = firstDay.getDay();

            const prevLast = new Date(this.viewYear, this.viewMonth, 0);
            for (let i = startPad - 1; i >= 0; i--) {
                const date = prevLast.getDate() - i;
                const full = this.formatDate(new Date(this.viewYear, this.viewMonth - 1, date));
                days.push(this.makeDayObj(date, full, false));
            }

            for (let d = 1; d <= lastDay.getDate(); d++) {
                const full = this.formatDate(new Date(this.viewYear, this.viewMonth, d));
                days.push(this.makeDayObj(d, full, true));
            }

            const remaining = 42 - days.length;
            for (let d = 1; d <= remaining; d++) {
                const full = this.formatDate(new Date(this.viewYear, this.viewMonth + 1, d));
                days.push(this.makeDayObj(d, full, false));
            }

            return days;
        },

        makeDayObj(date, fullDate, currentMonth) {
            const today = this.formatDate(new Date());
            const isDisabled =
                (this.minDate && new Date(fullDate + 'T00:00:00') < this.minDate) ||
                (this.maxDate && new Date(fullDate + 'T00:00:00') > this.maxDate);
            return {
                date,
                fullDate,
                currentMonth,
                isToday: fullDate === today,
                isSelected: fullDate === this.value,
                selectable: !isDisabled,
            };
        },

        formatDate(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + day;
        },

        toggle() {
            if (!this.open) {
                this.$nextTick(() => {
                    const btn = this.$refs.trigger;
                    const rect = btn.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - rect.bottom;
                    this.dropUp = spaceBelow < 340;
                });
            }
            this.open = !this.open;
        },

        prevMonth() {
            if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
            else { this.viewMonth--; }
        },

        nextMonth() {
            if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
            else { this.viewMonth++; }
        },

        selectDay(day) {
            this.value = day.fullDate;
            this.open = false;
        },
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative"
    {{ $attributes->whereDoesntStartWith('wire:model') }}
>
    @if($label)
        <flux:label class="mb-2">{{ $label }}</flux:label>
    @endif

    {{-- Trigger Input --}}
    <button
        type="button"
        x-ref="trigger"
        x-on:click="toggle()"
        class="flex items-center w-full rounded-lg border shadow-xs px-3 py-2 text-sm transition
               border-zinc-200 bg-white text-zinc-800 placeholder-zinc-400
               dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-white/50
               hover:border-zinc-300 dark:hover:border-white/20
               focus:outline-none focus:ring-2 focus:ring-zinc-900/10 dark:focus:ring-white/20"
    >
        <flux:icon.calendar-days variant="mini" class="mr-2 size-4 shrink-0 text-zinc-400 dark:text-white/50" />
        <span x-text="displayValue || '{{ $placeholder }}'" :class="displayValue ? '' : 'text-zinc-400 dark:text-white/50'"></span>
    </button>

    {{-- Calendar Popup --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        x-cloak
        class="absolute z-50 w-72 rounded-xl border shadow-lg
               border-zinc-200 bg-white
               dark:border-white/10 dark:bg-zinc-800"
        :class="dropUp ? 'bottom-full mb-2' : 'top-full mt-2'"
    >
        {{-- Header: Month/Year + Navigation --}}
        <div class="flex items-center justify-between px-4 pt-4 pb-2">
            <span class="text-sm font-semibold text-zinc-800 dark:text-white" x-text="monthYearLabel"></span>
            <div class="flex items-center gap-1">
                <button type="button" x-on:click="prevMonth()"
                    class="rounded-md p-1 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:text-white/50 dark:hover:text-white dark:hover:bg-white/10 transition">
                    <flux:icon.chevron-left variant="mini" class="size-4" />
                </button>
                <button type="button" x-on:click="nextMonth()"
                    class="rounded-md p-1 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:text-white/50 dark:hover:text-white dark:hover:bg-white/10 transition">
                    <flux:icon.chevron-right variant="mini" class="size-4" />
                </button>
            </div>
        </div>

        {{-- Day-of-week headers --}}
        <div class="grid grid-cols-7 px-3 pb-1">
            <template x-for="day in dayNames" :key="day">
                <div class="text-center text-xs font-medium py-1 text-zinc-400 dark:text-white/40" x-text="day"></div>
            </template>
        </div>

        {{-- Day grid --}}
        <div class="grid grid-cols-7 gap-y-0.5 px-3 pb-4">
            <template x-for="(day, index) in calendarDays" :key="index">
                <button
                    type="button"
                    x-on:click="day.selectable && selectDay(day)"
                    :disabled="!day.selectable"
                    class="relative flex flex-col items-center justify-center h-9 w-full rounded-lg text-sm transition"
                    :class="{
                        'font-semibold bg-zinc-900 text-white dark:bg-white dark:text-zinc-900': day.isSelected,
                        'text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-white/10': !day.isSelected && day.currentMonth && day.selectable,
                        'text-zinc-300 dark:text-white/20 cursor-not-allowed': !day.selectable,
                        'text-zinc-400 dark:text-white/30': !day.currentMonth && day.selectable,
                    }"
                >
                    <span x-text="day.date"></span>
                    <span
                        x-show="day.isToday"
                        class="absolute bottom-0.5 size-1 rounded-full"
                        :class="day.isSelected ? 'bg-white dark:bg-zinc-900' : 'bg-zinc-400 dark:bg-white/50'"
                    ></span>
                </button>
            </template>
        </div>
    </div>
</div>
