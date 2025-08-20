<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Gagal - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-error/10 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <!-- Error Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-error rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Pembayaran Gagal
            </h1>
            
            <p class="text-gray-600 mb-6">
                Maaf, terjadi kesalahan saat memproses pembayaran Anda.
            </p>

            <!-- Order Details -->
            @if($order_id)
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-gray-500">Order ID</div>
                <div class="font-mono font-medium text-gray-900">{{ $order_id }}</div>
                
                <div class="text-sm text-gray-500 mt-2">Status</div>
                <div class="badge badge-error">Failed</div>
                
                @if($status_code)
                <div class="text-sm text-gray-500 mt-2">Error Code</div>
                <div class="font-mono text-sm text-gray-700">{{ $status_code }}</div>
                @endif
            </div>
            @endif

            <!-- Error Message -->
            @if($status_message)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 text-sm text-red-800">
                        <p><strong>Error:</strong> {{ $status_message }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Help Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 text-sm">
                        <p class="text-blue-800">
                            <strong>Apa yang bisa dilakukan?</strong><br>
                            • Periksa koneksi internet Anda<br>
                            • Pastikan saldo atau limit kartu mencukupi<br>
                            • Coba gunakan metode pembayaran lain<br>
                            • Hubungi customer service jika masalah berlanjut
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="{{ route('dashboard') }}" class="w-full btn btn-primary">
                    Coba Lagi
                </a>
                
                <button onclick="window.close()" class="w-full btn btn-outline">
                    Tutup Halaman
                </button>
            </div>

            <!-- Footer -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Untuk bantuan lebih lanjut, silakan hubungi customer service kami di:<br>
                    <strong>Email:</strong> support@kasirmu.com<br>
                    <strong>WhatsApp:</strong> +62 812-3456-7890
                </p>
            </div>
        </div>
    </div>

    <script>
        // Try to close the window after 3 seconds if opened in popup
        setTimeout(function() {
            if (window.opener) {
                try {
                    window.close();
                } catch(e) {
                    console.log('Window cannot be closed automatically');
                }
            }
        }, 3000);
    </script>
</body>
</html>