<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;
use Midtrans\Config;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // Log incoming webhook untuk debug
        Log::info('Midtrans Webhook Received', [
            'request_body' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Handle test requests
            if ($request->has('test')) {
                Log::info('Midtrans Webhook Test', ['data' => $request->all()]);
                return response()->json(['status' => 'test_ok', 'message' => 'Test webhook berhasil']);
            }

            // Configure Midtrans
            $midtrans = app('midtrans');
            Config::$serverKey = $midtrans->getServerKey();
            Config::$isProduction = $midtrans->isProduction();

            // Get notification dari Midtrans
            $notification = new Notification();

            // Extract data
            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? null;
            $paymentType = $notification->payment_type;

            Log::info('Midtrans Webhook Processing', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType
            ]);

            // Cari transaksi berdasarkan transaction_code (untuk Order ID yang sama)
            // atau dari notes untuk backward compatibility
            $transaction = Transaction::where(function($query) use ($orderId) {
                $query->where('transaction_code', $orderId)
                      ->orWhere('notes', 'LIKE', "%Order ID: {$orderId}%")
                      ->orWhere('notes', 'LIKE', "%Midtrans Order ID: {$orderId}%")
                      ->orWhere('notes', 'LIKE', "%Public Order ID: {$orderId}%");
            })->first();

            if (!$transaction) {
                Log::warning('Midtrans Webhook Transaction Not Found', [
                    'order_id' => $orderId,
                    'transaction_status' => $transactionStatus,
                    'search_patterns' => [
                        "midtrans" => "%Midtrans Order ID: {$orderId}%",
                        "public" => "%Public Order ID: {$orderId}%"
                    ]
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Tentukan status berdasarkan response Midtrans
            $newStatus = $this->determineTransactionStatus(
                $transactionStatus, 
                $fraudStatus
            );

            // Update status transaksi
            $this->updateTransactionStatus($transaction, $newStatus, $notification);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // Return 200 untuk Midtrans agar tidak retry terus
            return response()->json([
                'status' => 'error', 
                'message' => 'Webhook processing failed: ' . $e->getMessage()
            ], 200);
        }
    }

    private function determineTransactionStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus == 'capture') {
            return ($fraudStatus == 'accept') ? 'selesai' : 'menunggu';
        } elseif ($transactionStatus == 'settlement') {
            return 'selesai';
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            return 'dibatalkan';
        } elseif ($transactionStatus == 'pending') {
            return 'menunggu';
        }

        return 'menunggu'; // default
    }

    private function updateTransactionStatus(Transaction $transaction, string $newStatus, Notification $notification): void
    {
        DB::beginTransaction();

        try {
            $oldStatus = $transaction->status;
            
            // Update status transaksi
            $transaction->update([
                'status' => $newStatus,
                'notes' => $transaction->notes . " | Webhook: {$notification->transaction_status} at " . now()->format('Y-m-d H:i:s')
            ]);

            // Jika status berubah dari 'menunggu' ke 'selesai', update stock
            if ($oldStatus === 'menunggu' && $newStatus === 'selesai') {
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->stock -= $item->quantity;
                        $product->terjual += $item->quantity;
                        $product->save();
                    }
                }
            }

            // Jika status berubah dari 'selesai' ke 'dibatalkan', kembalikan stock
            if ($oldStatus === 'selesai' && $newStatus === 'dibatalkan') {
                foreach ($transaction->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->stock += $item->quantity;
                        $product->terjual -= $item->quantity;
                        $product->save();
                    }
                }
            }

            DB::commit();


        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}