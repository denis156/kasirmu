<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'user_id',
        'tax_rate',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'notes',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public static function generateTransactionCode(): string
    {
        $prefix = 'TRX';
        $date = now()->format('Ymd');

        $lastTransaction = static::whereDate('created_at', today())
            ->where('transaction_code', 'like', $prefix . '-' . $date . '-%')
            ->orderBy('transaction_code', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_code, -4);
            $newNumber = str_pad((string)($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $date . '-' . $newNumber;
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            'tunai' => 'Tunai',
            'kartu' => 'Kartu Kredit/Debit',
            'transfer' => 'Virtual Account',
            'qris' => 'QRIS & E-Wallet',
            'ewallet' => 'E-Wallet Lengkap',
            'semua' => 'Semua Metode Digital',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'dana' => 'DANA',
            'ovo' => 'OVO',
            'linkaja' => 'LinkAja'
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses(): array
    {
        return [
            'menunggu' => 'Menunggu',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan'
        ];
    }

    /**
     * Check if payment method is valid
     */
    public static function isValidPaymentMethod(string $method): bool
    {
        return array_key_exists($method, self::getPaymentMethods());
    }

    /**
     * Check if status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return array_key_exists($status, self::getStatuses());
    }

    /**
     * Get payment method badge class
     */
    public static function getPaymentMethodBadgeClass(string $method): string
    {
        return match ($method) {
            'tunai' => 'badge-success',
            'kartu' => 'badge-info',
            'transfer' => 'badge-warning',
            'qris' => 'badge-primary',
            'ewallet' => 'badge-accent',
            'semua' => 'badge-secondary',
            'gopay' => 'badge-success',
            'shopeepay' => 'badge-warning',
            'dana' => 'badge-info',
            'ovo' => 'badge-accent',
            'linkaja' => 'badge-error',
            default => 'badge-neutral',
        };
    }

    /**
     * Get status badge class
     */
    public static function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'selesai' => 'badge-success',
            'menunggu' => 'badge-warning',
            'dibatalkan' => 'badge-error',
            default => 'badge-neutral',
        };
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get payment method badge class for instance
     */
    public function getPaymentMethodBadgeClassAttribute(): string
    {
        return self::getPaymentMethodBadgeClass($this->payment_method);
    }

    /**
     * Get status badge class for instance
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return self::getStatusBadgeClass($this->status);
    }
}
