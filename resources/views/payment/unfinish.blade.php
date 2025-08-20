<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Belum Selesai - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-warning/10 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-warning rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Pembayaran Belum Selesai
            </h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message ?? 'Anda belum menyelesaikan proses pembayaran.' }}
            </p>

            <!-- Order Details -->
            @if($order_id)
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-gray-500">Order ID</div>
                <div class="font-mono font-medium text-gray-900">{{ $order_id }}</div>
                
                <div class="text-sm text-gray-500 mt-2">Status</div>
                <div class="badge badge-warning">Pending</div>
            </div>
            @endif

            <!-- Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 text-sm">
                        <p class="text-blue-800">
                            <strong>Jangan khawatir!</strong><br>
                            Transaksi Anda masih dapat dilanjutkan. Silakan kembali ke aplikasi untuk melanjutkan pembayaran.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="{{ route('dashboard') }}" class="w-full btn btn-primary">
                    Lanjutkan Pembayaran
                </a>
                
                <button onclick="window.close()" class="w-full btn btn-outline">
                    Tutup Halaman
                </button>
            </div>

            <!-- Footer -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Jika mengalami kesulitan, silakan hubungi customer service kami untuk bantuan.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto redirect to dashboard after 10 seconds
        setTimeout(function() {
            window.location.href = '{{ route("dashboard") }}';
        }, 10000);
        
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