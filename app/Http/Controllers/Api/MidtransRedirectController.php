<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MidtransRedirectController extends Controller
{
    /**
     * Handle finish redirect from Midtrans
     * Customer will be redirected here when payment is successful
     */
    public function finish(Request $request): View
    {
        $orderId = $request->get('order_id');
        $statusCode = $request->get('status_code');
        $transactionStatus = $request->get('transaction_status');

        return view('payment.finish', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'transaction_status' => $transactionStatus,
            'success' => true
        ]);
    }

    /**
     * Handle unfinish redirect from Midtrans
     * Customer will be redirected here when they click 'Back to Order Website'
     */
    public function unfinish(Request $request): View
    {
        $orderId = $request->get('order_id');

        return view('payment.unfinish', [
            'order_id' => $orderId,
            'message' => 'Pembayaran belum selesai. Anda dapat melanjutkan pembayaran kapan saja.'
        ]);
    }

    /**
     * Handle error redirect from Midtrans
     * Customer will be redirected here when payment encounters an error
     */
    public function error(Request $request): View
    {
        $orderId = $request->get('order_id');
        $statusCode = $request->get('status_code');
        $statusMessage = $request->get('status_message');

        return view('payment.error', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'status_message' => $statusMessage,
            'error' => true
        ]);
    }

    /**
     * Handle recurring notification from Midtrans
     * Same as webhook but for recurring payments
     */
    public function recurring(Request $request)
    {
        // For now, use the same webhook handler
        // In the future, can be customized for recurring-specific logic
        $webhookController = new MidtransWebhookController();
        return $webhookController->handle($request);
    }

    /**
     * Handle Pay Account notification from Midtrans
     * For Pay Account related notifications
     */
    public function payAccount(Request $request)
    {
        // For now, use the same webhook handler
        // In the future, can be customized for Pay Account-specific logic
        $webhookController = new MidtransWebhookController();
        return $webhookController->handle($request);
    }
}