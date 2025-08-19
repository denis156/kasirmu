<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

    {{--  Currency  --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js">
    </script>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-base-100 font-sans antialiased">

    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav sticky full-width class="bg-base-300 shadow-sm shadow-primary rounded-b-xl">

        {{-- Left side brand and drawer toggle --}}

        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="phosphor.list" class="cursor-pointer mb-[2px]" />
            </label>

            {{-- Brand --}}
            <div class="text-md sm:text-lg md:text-xl font-bold">{{ config('app.name') }}</div>
            <x-theme-toggle class="btn btn-circle btn-ghost ml-2" @theme-changed="console.log($event.detail)" />
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <!-- Live Clock -->
            <div class="hidden md:flex badge badge-primary badge-md rounded items-center">
                <x-icon name="phosphor.clock" class="w-4 h-4" />
                <span id="live-clock">{{ now()->format('H:i:s') }}</span>
            </div>
            <x-button label="Github" icon="phosphor.github-logo" external link="https://github.com/denis156" class="btn-primary btn-dash btn-xs" responsive />
            <x-button label="Instagram" icon="phosphor.instagram-logo" external link="https://www.instagram.com/artelia_development" class="btn-primary btn-dash btn-xs" responsive />

        </x-slot:actions>
    </x-nav>

    {{-- The main content with `full-width` --}}
    <x-main with-nav full-width>

        {{-- This is a sidebar that works also as a drawer on small screens --}}
        {{-- Notice the `main-drawer` reference here --}}
        <x-slot:sidebar drawer="main-drawer" collapse-text="Sembunyikan" collapsible class="bg-base-100">

            {{-- User --}}
            @if ($user = Auth::user())
                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="pt-2">
                    <x-slot:actions>
                        <x-button icon="phosphor.sign-out" class="btn-circle btn-error btn-soft btn-sm" tooltip-left="Keluar"
                            no-wire-navigate link="{{ route('logout') }}" />
                    </x-slot:actions>
                </x-list-item>

                <x-menu-separator />
            @endif

            {{-- Activates the menu item when a route matches the `link` property --}}
            <x-menu activate-by-route active-bg-color="font-bold bg-primary/40 shadow-sm shadow-primary" class="space-y-1">
                <!-- DASHBOARD -->
                <x-menu-item title="Beranda" icon="phosphor.gauge" link="{{ route('dashboard') }}" exact />

                <!-- OPERASIONAL POS -->
                <x-menu-separator />
                <x-menu-item title="Kasir" icon="phosphor.cash-register" link="{{ route('cashier') }}" exact />

                @if(Auth::user()->is_super_admin)
                    <!-- ADMIN: TRANSAKSI & REPORTS -->
                    <x-menu-item title="Transaksi" icon="phosphor.receipt" link="{{ route('transactions.index') }}" exact />

                    <!-- LAPORAN & ANALISIS -->
                    <x-menu-separator />
                    <x-menu-sub title="Laporan" icon="phosphor.chart-bar">
                        <x-menu-item title="Laporan Penjualan" icon="phosphor.chart-line" link="{{ route('reports.sales') }}" exact />
                        <x-menu-item title="Laporan Stok" icon="phosphor.package" link="{{ route('reports.stock') }}" exact />
                        <x-menu-item title="Laporan Kasir" icon="phosphor.user" link="{{ route('reports.cashier') }}" exact />
                        {{-- <x-menu-item title="Export Data" icon="phosphor.download" link="###" exact /> --}}
                    </x-menu-sub>

                    <!-- MANAJEMEN INVENTORY -->
                    <x-menu-separator />
                    <x-menu-sub title="Inventory" icon="phosphor.package">
                        <x-menu-item title="Daftar Produk" icon="phosphor.package" link="{{ route('products.index') }}" exact />
                        <x-menu-item title="Tambah Produk" icon="phosphor.plus" link="{{ route('products.create') }}" exact />
                    </x-menu-sub>

                    <!-- MANAJEMEN USER -->
                    <x-menu-separator />
                    <x-menu-sub title="Manajemen User" icon="phosphor.users-four">
                        <x-menu-item title="Daftar Pengguna" icon="phosphor.users-four" link="{{ route('users.index') }}" exact />
                        <x-menu-item title="Tambah Pengguna" icon="phosphor.user-plus" link="{{ route('users.create') }}" exact />
                    </x-menu-sub>
                @endif

                <!-- AKUN & PENGATURAN -->
                <x-menu-separator />
                <x-menu-item title="Profile" icon="phosphor.user" link="{{ route('profile') }}" />

                @if(Auth::user()->is_super_admin)
                    <x-menu-item title="Pengaturan" icon="phosphor.gear" link="{{ route('settings') }}" />
                @endif
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    {{-- Live Clock Script --}}
    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });

            const clockElement = document.getElementById('live-clock');
            if (clockElement) {
                clockElement.textContent = timeString;
            }
        }

        // Update clock immediately
        updateClock();

        // Update clock every second
        setInterval(updateClock, 1000);
    </script>
</body>

</html>
