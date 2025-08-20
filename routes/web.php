<?php

use App\Livewire\Login;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', Login::class)
    ->name('login')
    ->middleware('guest');

Route::get('/phpinfo', function () {
    return phpinfo();
});

// Logout route
Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

// Admin routes (protected with auth middleware)
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/', \App\Livewire\Dashboard::class)
        ->name('dashboard');

    Route::get('/pengguna', \App\Livewire\User\Index::class)
        ->name('users.index');

    Route::get('/pengguna/buat', \App\Livewire\User\Create::class)
        ->name('users.create');

    Route::get('/pengguna/{id}/edit', \App\Livewire\User\Edit::class)
        ->name('users.edit');

    Route::get('/produk', \App\Livewire\Product\Index::class)
        ->name('products.index');

    Route::get('/produk/buat', \App\Livewire\Product\Create::class)
        ->name('products.create');

    Route::get('/produk/{id}/edit', \App\Livewire\Product\Edit::class)
        ->name('products.edit');

    Route::get('/kasir', \App\Livewire\Transaction\Cashier::class)
        ->name('cashier');

    Route::get('/transaksi', \App\Livewire\Transaction\Index::class)
        ->name('transactions.index');

    Route::get('/profile', \App\Livewire\Profile::class)
        ->name('profile');

    // Reports
    Route::get('/laporan/penjualan', \App\Livewire\Report\SalesReport::class)
        ->name('reports.sales');

    Route::get('/laporan/stok', \App\Livewire\Report\StockReport::class)
        ->name('reports.stock');

    Route::get('/laporan/kasir', \App\Livewire\Report\CashierReport::class)
        ->name('reports.cashier');

    // Settings (Admin only)
    Route::get('/pengaturan', \App\Livewire\Settings::class)
        ->name('settings');
});

// Midtrans Webhook & Redirects (tanpa auth middleware)
Route::post('/webhook/midtrans', [App\Http\Controllers\Api\MidtransWebhookController::class, 'handle'])
    ->name('webhook.midtrans');

Route::post('/webhook/midtrans/recurring', [App\Http\Controllers\Api\MidtransRedirectController::class, 'recurring'])
    ->name('webhook.midtrans.recurring');

Route::post('/webhook/midtrans/pay-account', [App\Http\Controllers\Api\MidtransRedirectController::class, 'payAccount'])
    ->name('webhook.midtrans.payaccount');

// Midtrans Redirect URLs
Route::get('/payment/finish', [App\Http\Controllers\Api\MidtransRedirectController::class, 'finish'])
    ->name('payment.finish');

Route::get('/payment/unfinish', [App\Http\Controllers\Api\MidtransRedirectController::class, 'unfinish'])
    ->name('payment.unfinish');

Route::get('/payment/error', [App\Http\Controllers\Api\MidtransRedirectController::class, 'error'])
    ->name('payment.error');
