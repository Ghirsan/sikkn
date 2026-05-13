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
                <flux:sidebar.group :heading="__('Dokumen')" class="grid">
                    <flux:sidebar.item icon="document-text" :current="request()->routeIs('documents.*')">
                        {{ __('Dokumen Saya') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="book-open" :current="request()->routeIs('daily-logs.*')">
                        {{ __('Catatan Harian') }}
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
                        {{ __('Kelompok Bimbingan') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="inbox" :current="request()->routeIs('dpl.reviews.*')">
                        {{ __('Review Dokumen') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" :current="request()->routeIs('dpl.mentoring.*')">
                        {{ __('Buku Pembimbingan') }}
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
                    <flux:sidebar.item icon="users" :current="request()->routeIs('admin.users.*')">
                        {{ __('Pengguna') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Verifikasi')" class="grid">
                    <flux:sidebar.item icon="shield-check" :current="request()->routeIs('admin.verifications.*')">
                        {{ __('Verifikasi Dokumen') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif

                {{-- Prodi Navigation --}}
                @if(auth()->user()->hasRole(\App\Enums\UserRole::Prodi))
                <flux:sidebar.group :heading="__('Program Studi')" class="grid">
                    <flux:sidebar.item icon="academic-cap" :current="request()->routeIs('prodi.students.*')">
                        {{ __('Mahasiswa KKN') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :current="request()->routeIs('prodi.documents.*')">
                        {{ __('Monitor Dokumen') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :current="request()->routeIs('prodi.reports.*')">
                        {{ __('Laporan') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif

                {{-- Fakultas Navigation --}}
                @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
                <flux:sidebar.group :heading="__('Fakultas')" class="grid">
                    <flux:sidebar.item icon="building-library" :current="request()->routeIs('fakultas.prodi.*')">
                        {{ __('Program Studi') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="document-text" :current="request()->routeIs('fakultas.documents.*')">
                        {{ __('Monitor Dokumen') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :current="request()->routeIs('fakultas.reports.*')">
                        {{ __('Laporan') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-down-tray" :current="request()->routeIs('fakultas.exports.*')">
                        {{ __('Export') }}
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
