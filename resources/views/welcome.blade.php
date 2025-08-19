<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KasirMu By ArteliaDev') }} - Sistem POS Modern</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

    {{-- Mary UI Currency --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px oklch(var(--color-primary) / 0.3); }
            50% { box-shadow: 0 0 40px oklch(var(--color-primary) / 0.6), 0 0 60px oklch(var(--color-primary) / 0.4); }
        }

        .float-animation { animation: float 6s ease-in-out infinite; }
        .glow-animation { animation: glow 3s ease-in-out infinite; }

        /* Using theme colors for consistency */
        .theme-gradient-bg {
            background: linear-gradient(135deg,
                oklch(var(--color-primary) / 0.1),
                oklch(var(--color-secondary) / 0.1),
                oklch(var(--color-accent) / 0.1)
            );
        }
    </style>
</head>

<body class="bg-gradient-to-br from-base-100 via-base-200 to-base-300 min-h-screen overflow-x-hidden">
    <!-- NAVIGATION -->
    <nav class="navbar bg-base-100/90 backdrop-blur-lg border-b border-primary/20 sticky top-0 z-50 shadow-xl">
        <div class="navbar-start">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center shadow-lg glow-animation">
                    <x-icon name="phosphor.cash-register" class="w-6 h-6 text-primary-content" />
                </div>
                <div>
                    <div class="text-xl font-black bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent">
                        {{ config('app.name') }}
                    </div>
                    <div class="text-xs text-base-content/60 font-medium">By ArteliaDev</div>
                </div>
            </div>
        </div>

        <div class="navbar-end">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                    <x-icon name="phosphor.house" class="w-4 h-4" />
                    Dashboard
                </a>
            @else
                <div class="flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm hover:bg-primary/10">
                        Masuk
                    </a>
                </div>
            @endauth
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero min-h-screen theme-gradient-bg relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-4 -right-4 w-96 h-96 bg-primary/20 rounded-full mix-blend-multiply filter blur-3xl animate-pulse float-animation"></div>
            <div class="absolute -bottom-8 -left-4 w-96 h-96 bg-secondary/20 rounded-full mix-blend-multiply filter blur-3xl animate-pulse" style="animation-delay: 2s"></div>
            <div class="absolute top-1/3 left-1/3 w-80 h-80 bg-accent/20 rounded-full mix-blend-multiply filter blur-3xl animate-pulse" style="animation-delay: 4s"></div>
        </div>

        <!-- Floating Particles -->
        <div class="absolute inset-0">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-primary/40 rounded-full animate-ping" style="animation-delay: 1s"></div>
            <div class="absolute top-3/4 right-1/4 w-3 h-3 bg-secondary/40 rounded-full animate-ping" style="animation-delay: 3s"></div>
            <div class="absolute top-1/2 right-1/3 w-2 h-2 bg-accent/40 rounded-full animate-ping" style="animation-delay: 5s"></div>
        </div>

        <div class="hero-content text-center relative z-10 py-16 md:py-24">
            <div class="max-w-6xl px-4">
                <!-- Main Icon with Enhanced Animation -->
                <div class="mb-12 relative">
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-primary via-secondary to-accent rounded-3xl flex items-center justify-center mb-6 shadow-2xl glow-animation float-animation">
                        <x-icon name="phosphor.cash-register" class="w-20 h-20 text-primary-content" />
                    </div>
                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-gradient-to-br from-success to-success/80 rounded-full animate-pulse flex items-center justify-center">
                        <x-icon name="phosphor.check" class="w-4 h-4 text-success-content" />
                    </div>
                    <div class="absolute -bottom-3 -left-3 w-6 h-6 bg-gradient-to-br from-warning to-warning/80 rounded-full animate-bounce">
                        <div class="w-2 h-2 bg-warning-content rounded-full mx-auto mt-2"></div>
                    </div>
                </div>

                <!-- Main Title -->
                <h1 class="text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-black mb-6 bg-gradient-to-r from-primary via-secondary to-accent bg-clip-text text-transparent leading-tight tracking-tight">
                    {{ config('app.name') }}
                </h1>

                <!-- Subtitle with Enhanced Styling -->
                <div class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold mb-8">
                    <span class="bg-gradient-to-r from-secondary via-accent to-primary bg-clip-text text-transparent">By ArteliaDev</span>
                </div>

                <!-- Enhanced Badge -->
                <div class="inline-flex items-center gap-3 bg-gradient-to-r from-primary/10 to-secondary/10 border-2 border-primary/20 rounded-full px-8 py-3 mb-12 shadow-lg hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 bg-success rounded-full animate-pulse"></span>
                        <span class="w-1.5 h-1.5 bg-success/70 rounded-full animate-pulse" style="animation-delay: 0.2s"></span>
                        <span class="w-1 h-1 bg-success/50 rounded-full animate-pulse" style="animation-delay: 0.4s"></span>
                    </div>
                    <span class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">Sistem POS Terdepan</span>
                    <x-icon name="phosphor.lightning" class="w-5 h-5 text-warning animate-pulse" />
                </div>

                <!-- Enhanced Description -->
                <div class="mb-16 max-w-5xl mx-auto">
                    <p class="text-2xl md:text-3xl text-base-content/70 leading-relaxed mb-8">
                        Transformasi digital untuk bisnis Anda dengan sistem Point of Sale yang
                    </p>

                    <!-- Highlighted Features -->
                    <div class="flex flex-wrap justify-center gap-6 md:gap-8 mb-8">
                        <span class="inline-flex items-center font-bold text-primary relative px-4 py-2 bg-primary/10 rounded-xl border border-primary/20">
                            <x-icon name="phosphor.lightning-fill" class="w-5 h-5 mr-2" />
                            powerful
                        </span>
                        <span class="inline-flex items-center font-bold text-secondary relative px-4 py-2 bg-secondary/10 rounded-xl border border-secondary/20">
                            <x-icon name="phosphor.heart-fill" class="w-5 h-5 mr-2" />
                            intuitif
                        </span>
                        <span class="inline-flex items-center font-bold text-accent relative px-4 py-2 bg-accent/10 rounded-xl border border-accent/20">
                            <x-icon name="phosphor.star-fill" class="w-5 h-5 mr-2" />
                            modern
                        </span>
                    </div>
                </div>

                <!-- Enhanced CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-6 md:gap-8 justify-center mb-16 px-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-12 py-4 text-xl shadow-2xl hover:shadow-primary/50 transform hover:-translate-y-2 hover:scale-105 transition-all duration-500 glow-animation">
                            <x-icon name="phosphor.rocket-launch" class="w-7 h-7" />
                            Masuk Dashboard
                            <x-icon name="phosphor.arrow-right" class="w-6 h-6 ml-2" />
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-12 py-4 text-xl shadow-2xl hover:shadow-primary/50 transform hover:-translate-y-2 hover:scale-105 transition-all duration-500 glow-animation">
                            <x-icon name="phosphor.rocket-launch" class="w-7 h-7" />
                            Mulai Sekarang
                            <x-icon name="phosphor.arrow-right" class="w-6 h-6 ml-2" />
                        </a>
                    @endauth

                    <button class="btn btn-outline btn-lg px-12 py-4 text-xl border-3 hover:bg-gradient-to-r hover:from-primary hover:to-secondary hover:text-white hover:border-transparent transform hover:-translate-y-2 hover:scale-105 transition-all duration-500" onclick="document.getElementById('features').scrollIntoView({behavior: 'smooth'})">
                        <x-icon name="phosphor.play-circle" class="w-7 h-7" />
                        Lihat Demo
                        <x-icon name="phosphor.video" class="w-6 h-6 ml-2" />
                    </button>
                </div>

                <!-- Enhanced Stats Preview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 max-w-6xl mx-auto mt-16">
                    <div class="bg-base-100/80 backdrop-blur-sm rounded-3xl p-6 md:p-8 border border-primary/20 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl md:text-4xl font-black text-primary">{{ number_format(\DB::table('products')->where('is_active', true)->count()) }}+</div>
                            <x-icon name="phosphor.package" class="w-10 h-10 text-primary/50" />
                        </div>
                        <div class="text-base md:text-lg text-base-content/70 font-medium">Produk Aktif</div>
                    </div>

                    <div class="bg-base-100/80 backdrop-blur-sm rounded-3xl p-6 md:p-8 border border-secondary/20 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl md:text-4xl font-black text-secondary">{{ number_format(\DB::table('users')->whereNotNull('email_verified_at')->count()) }}+</div>
                            <x-icon name="phosphor.users" class="w-10 h-10 text-secondary/50" />
                        </div>
                        <div class="text-base md:text-lg text-base-content/70 font-medium">Pengguna Aktif</div>
                    </div>

                    <div class="bg-base-100/80 backdrop-blur-sm rounded-3xl p-6 md:p-8 border border-accent/20 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl md:text-4xl font-black text-accent">99.9%</div>
                            <x-icon name="phosphor.check-circle" class="w-10 h-10 text-accent/50" />
                        </div>
                        <div class="text-base md:text-lg text-base-content/70 font-medium">Uptime Sistem</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <x-icon name="phosphor.caret-down" class="w-8 h-8 text-primary/60" />
        </div>
    </section>

    <!-- ENHANCED FEATURES SECTION -->
    <section id="features" class="py-20 md:py-32 bg-gradient-to-br from-base-100 to-base-200 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgb(0,0,0) 1px, transparent 0); background-size: 50px 50px;"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <div class="inline-flex items-center gap-2 bg-primary/10 rounded-full px-6 py-2 mb-6">
                    <x-icon name="phosphor.star" class="w-5 h-5 text-primary" />
                    <span class="text-primary font-semibold">Fitur Unggulan</span>
                </div>
                <h2 class="text-5xl md:text-6xl font-black mb-6 bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                    Semua Yang Anda Butuhkan
                </h2>
                <p class="text-xl text-base-content/70 max-w-3xl mx-auto">
                    Solusi lengkap untuk mengelola bisnis modern dengan teknologi terdepan
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <!-- Enhanced Feature Cards -->
                <div class="group card bg-base-100 shadow-xl hover:shadow-2xl border border-primary/10 hover:border-primary/30 transform hover:-translate-y-2 transition-all duration-500">
                    <div class="card-body p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <x-icon name="phosphor.cash-register" class="w-8 h-8 text-primary-content" />
                        </div>
                        <h3 class="card-title text-2xl mb-4 group-hover:text-primary transition-colors">Kasir Modern</h3>
                        <p class="text-base-content/70 leading-relaxed">
                            Interface kasir yang intuitif dan responsif untuk proses transaksi yang cepat dan efisien dengan dukungan barcode scanner.
                        </p>
                        <div class="flex items-center mt-4 text-primary group-hover:translate-x-2 transition-transform duration-300">
                            <span class="text-sm font-semibold">Pelajari lebih lanjut</span>
                            <x-icon name="phosphor.arrow-right" class="w-4 h-4 ml-2" />
                        </div>
                    </div>
                </div>

                <div class="group card bg-base-100 shadow-xl hover:shadow-2xl border border-success/10 hover:border-success/30 transform hover:-translate-y-2 transition-all duration-500">
                    <div class="card-body p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-success to-success/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <x-icon name="phosphor.package" class="w-8 h-8 text-success-content" />
                        </div>
                        <h3 class="card-title text-2xl mb-4 group-hover:text-success transition-colors">Smart Inventory</h3>
                        <p class="text-base-content/70 leading-relaxed">
                            Manajemen stok otomatis dengan alert real-time, tracking pergerakan, dan prediksi kebutuhan restock.
                        </p>
                        <div class="flex items-center mt-4 text-success group-hover:translate-x-2 transition-transform duration-300">
                            <span class="text-sm font-semibold">Pelajari lebih lanjut</span>
                            <x-icon name="phosphor.arrow-right" class="w-4 h-4 ml-2" />
                        </div>
                    </div>
                </div>

                <div class="group card bg-base-100 shadow-xl hover:shadow-2xl border border-info/10 hover:border-info/30 transform hover:-translate-y-2 transition-all duration-500">
                    <div class="card-body p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-info to-info/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <x-icon name="phosphor.chart-line" class="w-8 h-8 text-info-content" />
                        </div>
                        <h3 class="card-title text-2xl mb-4 group-hover:text-info transition-colors">Analytics Pro</h3>
                        <p class="text-base-content/70 leading-relaxed">
                            Dashboard analitik mendalam dengan visualisasi data, tren penjualan, dan insights bisnis yang actionable.
                        </p>
                        <div class="flex items-center mt-4 text-info group-hover:translate-x-2 transition-transform duration-300">
                            <span class="text-sm font-semibold">Pelajari lebih lanjut</span>
                            <x-icon name="phosphor.arrow-right" class="w-4 h-4 ml-2" />
                        </div>
                    </div>
                </div>

                <div class="group card bg-base-100 shadow-xl hover:shadow-2xl border border-warning/10 hover:border-warning/30 transform hover:-translate-y-2 transition-all duration-500">
                    <div class="card-body p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-warning to-warning/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <x-icon name="phosphor.users" class="w-8 h-8 text-warning-content" />
                        </div>
                        <h3 class="card-title text-2xl mb-4 group-hover:text-warning transition-colors">Multi User Access</h3>
                        <p class="text-base-content/70 leading-relaxed">
                            Sistem role-based dengan kontrol akses granular untuk admin, kasir, dan supervisor dengan audit trail lengkap.
                        </p>
                        <div class="flex items-center mt-4 text-warning group-hover:translate-x-2 transition-transform duration-300">
                            <span class="text-sm font-semibold">Pelajari lebih lanjut</span>
                            <x-icon name="phosphor.arrow-right" class="w-4 h-4 ml-2" />
                        </div>
                    </div>
                </div>

                <div class="group card bg-base-100 shadow-xl hover:shadow-2xl border border-error/10 hover:border-error/30 transform hover:-translate-y-2 transition-all duration-500">
                    <div class="card-body p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-error to-error/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <x-icon name="phosphor.shield-check" class="w-8 h-8 text-error-content" />
                        </div>
                        <h3 class="card-title text-2xl mb-4 group-hover:text-error transition-colors">Enterprise Security</h3>
                        <p class="text-base-content/70 leading-relaxed">
                            Keamanan tingkat enterprise dengan enkripsi data, backup otomatis, dan compliance standar industri.
                        </p>
                        <div class="flex items-center mt-4 text-error group-hover:translate-x-2 transition-transform duration-300">
                            <span class="text-sm font-semibold">Pelajari lebih lanjut</span>
                            <x-icon name="phosphor.arrow-right" class="w-4 h-4 ml-2" />
                        </div>
                    </div>
                </div>

                <div class="group card bg-base-100 shadow-xl hover:shadow-2xl border border-secondary/10 hover:border-secondary/30 transform hover:-translate-y-2 transition-all duration-500">
                    <div class="card-body p-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-secondary to-secondary/80 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <x-icon name="phosphor.devices" class="w-8 h-8 text-secondary-content" />
                        </div>
                        <h3 class="card-title text-2xl mb-4 group-hover:text-secondary transition-colors">Cross Platform</h3>
                        <p class="text-base-content/70 leading-relaxed">
                            Akses dari berbagai perangkat dengan sinkronisasi real-time: desktop, tablet, smartphone, dan web browser.
                        </p>
                        <div class="flex items-center mt-4 text-secondary group-hover:translate-x-2 transition-transform duration-300">
                            <span class="text-sm font-semibold">Pelajari lebih lanjut</span>
                            <x-icon name="phosphor.arrow-right" class="w-4 h-4 ml-2" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ENHANCED CTA SECTION -->
    <section class="py-20 md:py-32 bg-gradient-to-br from-primary via-secondary to-accent relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary/90 via-secondary/90 to-accent/90"></div>
            <div class="absolute top-0 left-0 w-full h-full">
                <div class="absolute top-10 left-10 w-20 h-20 bg-base-content/10 rounded-full animate-pulse"></div>
                <div class="absolute top-40 right-20 w-32 h-32 bg-base-content/5 rounded-full animate-pulse" style="animation-delay: 2s"></div>
                <div class="absolute bottom-20 left-1/3 w-16 h-16 bg-base-content/10 rounded-full animate-pulse" style="animation-delay: 4s"></div>
            </div>
        </div>

        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="max-w-4xl mx-auto text-primary-content">
                <div class="inline-flex items-center gap-2 bg-base-content/20 rounded-full px-6 py-2 mb-8">
                    <x-icon name="phosphor.rocket-launch" class="w-5 h-5" />
                    <span class="font-semibold">Siap Meluncur</span>
                </div>

                <h2 class="text-5xl md:text-6xl font-black mb-6 leading-tight">
                    Wujudkan Bisnis Impian Anda!
                </h2>

                <p class="text-xl md:text-2xl mb-12 opacity-90 leading-relaxed">
                    Bergabunglah dengan ribuan bisnis yang telah bertransformasi digital bersama {{ config('app.name') }}.
                    <br class="hidden md:block">
                    Mulai gratis hari ini dan rasakan perbedaannya!
                </p>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-accent btn-lg px-16 py-6 text-xl shadow-2xl hover:shadow-accent/50 transform hover:-translate-y-2 hover:scale-105 transition-all duration-500 glow-animation">
                        <x-icon name="phosphor.lightning" class="w-8 h-8" />
                        Masuk Dashboard
                        <x-icon name="phosphor.arrow-right" class="w-6 h-6 ml-2" />
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-accent btn-lg px-16 py-6 text-xl shadow-2xl hover:shadow-accent/50 transform hover:-translate-y-2 hover:scale-105 transition-all duration-500 glow-animation">
                        <x-icon name="phosphor.lightning" class="w-8 h-8" />
                        Mulai Gratis Sekarang
                        <x-icon name="phosphor.arrow-right" class="w-6 h-6 ml-2" />
                    </a>
                @endauth

                <div class="mt-12 grid grid-cols-3 gap-8 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-base-content/20 rounded-2xl flex items-center justify-center mx-auto mb-2">
                            <x-icon name="phosphor.gift" class="w-6 h-6 text-primary-content" />
                        </div>
                        <div class="text-sm opacity-80 mt-1">Gratis Trial</div>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-base-content/20 rounded-2xl flex items-center justify-center mx-auto mb-2">
                            <x-icon name="phosphor.rocket-launch" class="w-6 h-6 text-primary-content" />
                        </div>
                        <div class="text-sm opacity-80 mt-1">Setup Cepat</div>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-base-content/20 rounded-2xl flex items-center justify-center mx-auto mb-2">
                            <x-icon name="phosphor.headset" class="w-6 h-6 text-primary-content" />
                        </div>
                        <div class="text-sm opacity-80 mt-1">Support 24/7</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ENHANCED FOOTER -->
    <footer class="footer footer-center p-16 bg-gradient-to-br from-base-300 to-base-200 text-base-content relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgb(0,0,0) 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-primary to-secondary rounded-2xl flex items-center justify-center shadow-lg">
                    <x-icon name="phosphor.cash-register" class="w-8 h-8 text-primary-content" />
                </div>
                <div>
                    <div class="text-2xl font-black bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                        {{ config('app.name') }}
                    </div>
                    <div class="text-sm text-base-content/60">By ArteliaDev</div>
                </div>
            </div>

            <p class="text-base-content/70 max-w-md mb-8 leading-relaxed">
                Sistem Point of Sale modern yang membantu Anda mengelola bisnis dengan lebih efisien, profesional, dan menguntungkan.
            </p>

            <!-- Social Links -->
            <div class="flex gap-4 mb-8">
                <a href="https://github.com/denis156" target="_blank" class="btn btn-circle btn-outline hover:btn-primary transition-all duration-300">
                    <x-icon name="phosphor.github-logo" class="w-5 h-5" />
                </a>
                <a href="https://www.instagram.com/artelia_development" target="_blank" class="btn btn-circle btn-outline hover:btn-secondary transition-all duration-300">
                    <x-icon name="phosphor.instagram-logo" class="w-5 h-5" />
                </a>
                <a href="mailto:contact@arteliadev.com" class="btn btn-circle btn-outline hover:btn-accent transition-all duration-300">
                    <x-icon name="phosphor.envelope" class="w-5 h-5" />
                </a>
            </div>

            <div class="text-sm text-base-content/60">
                <p class="mb-2">&copy; {{ date('Y') }} {{ config('app.name') }} By ArteliaDev. Semua hak cipta dilindungi.</p>
                <div class="flex items-center gap-2 justify-center flex-wrap">
                    <span class="badge badge-success badge-sm">Beta Version</span>
                    <span>•</span>
                    <span>Powered by Laravel & Livewire</span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        Made with <x-icon name="phosphor.heart-fill" class="w-3 h-3 text-error" /> in Indonesia
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Enhanced Scripts -->
    <script>
        // Enhanced smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe cards for scroll animations
            document.querySelectorAll('.card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease-out';
                observer.observe(card);
            });
        });

        // Add parallax effect to hero background
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.float-animation');
            parallaxElements.forEach(element => {
                const speed = 0.5;
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    </script>
</body>

</html>
