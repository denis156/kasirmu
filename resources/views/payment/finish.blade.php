<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-success/10 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <!-- Success Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-success rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Pembayaran Berhasil!
            </h1>
            
            <p class="text-gray-600 mb-6">
                Terima kasih! Transaksi Anda telah berhasil diproses.
            </p>

            <!-- Order Details -->
            @if($order_id)
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="text-sm text-gray-500">Order ID</div>
                <div class="font-mono font-medium text-gray-900">{{ $order_id }}</div>
                
                @if($transaction_status)
                <div class="text-sm text-gray-500 mt-2">Status</div>
                <div class="badge badge-success">{{ ucfirst($transaction_status) }}</div>
                @endif
            </div>
            @endif

            <!-- Actions -->
            <div class="space-y-3">
                <button onclick="window.close()" class="w-full btn btn-primary">
                    Tutup Halaman
                </button>
                
                <a href="{{ route('dashboard') }}" class="w-full btn btn-outline">
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Bukti pembayaran telah dikirim ke email Anda.<br>
                    Jika ada pertanyaan, silakan hubungi customer service kami.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto close after 30 seconds if opened in new window/tab
        setTimeout(function() {
            if (window.opener) {
                window.close();
            }
        }, 30000);
        
        // Try to close the window after 3 seconds
        setTimeout(function() {
            try {
                window.close();
            } catch(e) {
                // If can't close, show message
                console.log('Window cannot be closed automatically');
            }
        }, 3000);
    </script>
</body>
</html>