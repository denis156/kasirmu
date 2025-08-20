<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Setting;

class PaymentService
{
    /**
     * Check if payment method requires digital processing
     */
    public static function requiresDigitalProcessing(string $paymentMethod): bool
    {
        return !in_array($paymentMethod, ['tunai']);
    }

    /**
     * Get Midtrans payment method mapping
     */
    public static function getMidtransPaymentMethods(string $method): array
    {
        return match ($method) {
            'digital' => [
                'credit_card',
                'bank_transfer', 
                'permata_va',
                'bca_va',
                'bni_va', 
                'bri_va',
                'other_va',
                'qris',
                'gopay',
                'shopeepay',
                'dana',
                'ovo'
            ],
            default => ['credit_card', 'bank_transfer', 'qris', 'gopay', 'shopeepay', 'dana', 'ovo']
        };
    }

    /**
     * Prepare transaction data for Midtrans modal
     */
    public static function prepareTransactionData(Transaction $transaction): array
    {
        $subtotal = $transaction->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });
        $taxAmount = $subtotal * ($transaction->tax_rate / 100);

        return [
            'transaction_id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
            'items' => $transaction->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,
                    'sku' => $item->product_sku,
                    'price' => $item->unit_price,
                    'quantity' => $item->quantity
                ];
            })->toArray(),
            'total' => $transaction->total_amount,
            'subtotal' => $subtotal,
            'tax_rate' => $transaction->tax_rate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $transaction->discount_amount,
            'payment_method' => $transaction->payment_method,
            'notes' => $transaction->notes ?? ''
        ];
    }

    /**
     * Generate secure payment token
     */
    public static function generatePaymentToken(int $transactionId, string $transactionCode): string
    {
        return hash('sha256', $transactionId . $transactionCode . config('app.key'));
    }

    /**
     * Verify payment token
     */
    public static function verifyPaymentToken(int $transactionId, string $transactionCode, string $token): bool
    {
        $expectedToken = self::generatePaymentToken($transactionId, $transactionCode);
        return hash_equals($expectedToken, $token);
    }
}