<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                {{-- Common Navigation --}}
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- Mahasiswa Navigation --}}
                @if(auth()->user()->hasRole(\App\Enums\UserRole::Mahasiswa))
                <flux:sidebar.group :heading="__('Program Kerja')" class="grid">
                    <flux:sidebar.item icon="light-bulb" :current="request()->routeIs('programs.*')">
                        {{ __('Program Saya') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Dokumen')" class="grid">
                    <flux:sidebar.item icon="document-text" :current="request()->routeIs('documents.*')">
                        {{ __('Dokumen Tim (LRK/LPK)') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="book-open" :current="request()->routeIs('daily-logs.*')">
                        {{ __('Logbook Harian') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :current="request()->routeIs('mentoring-logs.*')">
                        {{ __('Buku Pembimbingan') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('KKN')" class="grid">
                    <flux:sidebar.item icon="user-group" :current="request()->routeIs('groups.*')">
                        {{ __('Kelompok Saya') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif

                {{-- DPL Navigation --}}
                @if(auth()->user()->hasRole(\App\Enums\UserRole::Dpl))
                <flux:sidebar.group :heading="__('Bimbingan')" class="grid">
                    <flux:sidebar.item icon="user-group" :current="request()->routeIs('dpl.groups.*')">
                        {{ __('Mahasiswa Bimbingan') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="light-bulb" :current="request()->routeIs('dpl.programs.*')">
                        {{ __('Review Program') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :current="request()->routeIs('dpl.documents.*')">
                        {{ __('Dokumen Tim') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Supervisi')" class="grid">
                    <flux:sidebar.item icon="book-open" :current="request()->routeIs('dpl.daily-logs.*')">
                        {{ __('Logbook Mahasiswa') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :current="request()->routeIs('dpl.mentoring.*')">
                        {{ __('Buku Pembimbingan') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Penilaian')" class="grid">
                    <flux:sidebar.item icon="clipboard-document-check" :current="request()->routeIs('dpl.grades.*')">
                        {{ __('Penilaian Mahasiswa') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif

                {{-- P2KKN Admin Navigation --}}
                @if(auth()->user()->hasRole(\App\Enums\UserRole::P2kkn))
                <flux:sidebar.group :heading="__('Manajemen')" class="grid">
                    <flux:sidebar.item icon="calendar" :current="request()->routeIs('admin.periods.*')">
                        {{ __('Periode KKN') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :current="request()->routeIs('admin.groups.*')">
                        {{ __('Kelompok') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="academic-cap" :current="request()->routeIs('admin.students.*')">
                        {{ __('Peserta KKN') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-circle" :current="request()->routeIs('admin.dpl.*')">
                        {{ __('Daftar DPL') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Dokumen')" class="grid">
                    <flux:sidebar.item icon="document-text" :current="request()->routeIs('admin.documents.*')">
                        {{ __('Rancangan Dokumen') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif

                {{-- Prodi Navigation --}}
                @if(auth()->user()->hasRole(\App\Enums\UserRole::Prodi))
                <flux:sidebar.group :heading="__('Program Studi')" class="grid">
                    <flux:sidebar.item icon="academic-cap" :current="request()->routeIs('prodi.students.*')">
                        {{ __('Mahasiswa KKN') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="light-bulb" :current="request()->routeIs('prodi.programs.*')">
                        {{ __('Program Kerja') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif

                {{-- Fakultas Navigation --}}
                @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
                <flux:sidebar.group :heading="__('Fakultas')" class="grid">
                    <flux:sidebar.item icon="academic-cap" :current="request()->routeIs('fakultas.students.*')">
                        {{ __('Mahasiswa Per Prodi') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="light-bulb" :current="request()->routeIs('fakultas.programs.*')">
                        {{ __('Program Kerja') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
