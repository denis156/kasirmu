<?php

namespace App\Livewire\Components;

use Midtrans\Snap;
use App\Models\User;
use Midtrans\Config;
use App\Models\Setting;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ModalPaymentMidtrans extends Component
{
    public bool $show = false;
    public ?string $snapToken = null;
    public string $paymentStatus = 'waiting'; // waiting, processing, success, failed, closed
    public array $transactionData = [];
    public array $paymentResult = [];

    protected $listeners = [
        'openMidtransModal' => 'openModal',
        'closeMidtransModal' => 'closeModal'
    ];

    public function openModal(array $transactionData): void
    {
        $this->transactionData = $transactionData;
        $this->paymentStatus = 'waiting';
        $this->paymentResult = [];

        // Generate snap token
        $this->snapToken = $this->generateSnapToken();

        if (!$this->snapToken) {
            $this->dispatch('midtransError', 'Gagal membuat token pembayaran. Silakan coba lagi.');
            return;
        }

        $this->show = true;

        // Dispatch untuk trigger snap.pay di frontend
        $this->dispatch('triggerSnapPay', $this->snapToken);
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->snapToken = null;
        $this->paymentStatus = 'waiting';
        $this->paymentResult = [];
        $this->transactionData = [];
    }

    private function generateSnapToken(): ?string
    {
        try {
            // Configure Midtrans
            $midtrans = app('midtrans');
            Config::$serverKey = $midtrans->getServerKey();
            Config::$isProduction = $midtrans->isProduction();
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Validasi input
            if (empty($this->transactionData['total']) || $this->transactionData['total'] <= 0) {
                return null;
            }

            $transactionDetails = [
                'order_id' => 'ORDER-' . time() . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'gross_amount' => (int) $this->transactionData['total'],
            ];

            $itemDetails = [];
            foreach ($this->transactionData['items'] as $item) {
                $itemDetails[] = [
                    'id' => (string) $item['product_id'],
                    'price' => (int) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'name' => (string) $item['name'],
                ];
            }

            // Add tax if exists
            if (!empty($this->transactionData['tax_amount'])) {
                $itemDetails[] = [
                    'id' => 'TAX',
                    'price' => (int) $this->transactionData['tax_amount'],
                    'quantity' => 1,
                    'name' => "Pajak (" . number_format($this->transactionData['tax_rate'], 0) . "%)",
                ];
            }

            // Add discount if exists
            if (!empty($this->transactionData['discount_amount'])) {
                $itemDetails[] = [
                    'id' => 'DISCOUNT',
                    'price' => -(int) $this->transactionData['discount_amount'],
                    'quantity' => 1,
                    'name' => 'Diskon',
                ];
            }


            // Customer details - ambil dari user_id transaksi atau user yang login
            $customerDetails = $this->getCustomerDetails();

            $enabledPayments = $this->getEnabledPayments();

            $params = [
                'transaction_details' => $transactionDetails,
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'enabled_payments' => $enabledPayments,
                'expiry' => [
                    'start_time' => date('Y-m-d H:i:s O'),
                    'unit' => 'minutes',
                    'duration' => 60
                ],
            ];



            return Snap::getSnapToken($params);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getEnabledPayments(): array
    {
        $paymentMethod = $this->transactionData['payment_method'] ?? 'semua';

        // Mapping payment method ke Midtrans payment types
        $paymentMapping = [
            'kartu' => ['credit_card'],
            'transfer' => ['bank_transfer', 'permata_va', 'bca_va', 'bni_va', 'bri_va', 'other_va'],
            'qris' => ['qris'],
            'ewallet' => ['gopay', 'shopeepay'],
            'gopay' => ['gopay'],
            'shopeepay' => ['shopeepay'],
            'dana' => ['dana'],
            'ovo' => ['ovo'],
            'linkaja' => ['linkaja'],
            'semua' => [
                'credit_card',
                'bank_transfer', 
                'permata_va',
                'bca_va',
                'bni_va', 
                'bri_va',
                'other_va',
                'qris',
                'gopay',
                'shopeepay'
                // Dana, OVO, LinkAja bisa ditambah jika sudah didukung merchant
            ]
        ];

        return $paymentMapping[$paymentMethod] ?? $paymentMapping['semua'];
    }

    public function handleSuccess(array $result): void
    {
        try {
            $this->paymentStatus = 'success';
            $this->paymentResult = $result;

            // Update database status untuk existing transaction atau create new
            $transactionId = $this->transactionData['transaction_id'] ?? null;
            $orderId = $result['order_id'] ?? null;

            if ($transactionId) {
                // Update existing transaction status dan simpan Order ID
                $updateData = [
                    'status' => 'selesai',
                    'updated_at' => now()
                ];
                
                // Update notes dengan Order ID jika belum ada
                if ($orderId) {
                    $transaction = DB::table('transactions')->where('id', $transactionId)->first();
                    if ($transaction && !str_contains($transaction->notes, 'Midtrans Order ID:')) {
                        $updateData['notes'] = ($transaction->notes ?? '') . " | Midtrans Order ID: {$orderId}";
                    }
                }

                $updated = DB::table('transactions')
                    ->where('id', $transactionId)
                    ->where('status', 'menunggu')
                    ->update($updateData);

                if ($updated) {
                    $this->dispatch('midtransSuccess', [
                        'message' => 'Pembayaran berhasil! Status akan diperbarui otomatis via webhook.',
                        'transaction_id' => $transactionId
                    ]);
                } else {
                    $this->dispatch('midtransSuccess', [
                        'message' => 'Pembayaran berhasil, tapi transaksi sudah diproses sebelumnya.',
                        'transaction_id' => $transactionId
                    ]);
                }
            } else {
                // Untuk cashier - buat transaksi baru dengan status menunggu
                // Webhook akan update ke selesai
                $this->dispatch('midtransSuccess', [
                    'message' => 'Pembayaran berhasil! Status akan diperbarui otomatis.',
                    'create_transaction' => true,
                    'status' => 'menunggu', // Webhook akan update ke selesai
                    'midtrans_result' => $result
                ]);
            }

            // Auto close modal setelah success
            $this->closeModal();

        } catch (\Exception $e) {
            $this->dispatch('midtransError', 'Pembayaran berhasil, tapi terjadi kesalahan saat memproses.');
            $this->closeModal();
        }
    }

    public function handlePending(array $result): void
    {
        try {
            $this->paymentStatus = 'processing';
            $this->paymentResult = $result;

            $transactionId = $this->transactionData['transaction_id'] ?? null;
            $orderId = $result['order_id'] ?? null;

            if ($transactionId && $orderId) {
                // Update existing transaction dengan Order ID
                $transaction = DB::table('transactions')->where('id', $transactionId)->first();
                if ($transaction && !str_contains($transaction->notes, 'Midtrans Order ID:')) {
                    DB::table('transactions')
                        ->where('id', $transactionId)
                        ->update([
                            'notes' => ($transaction->notes ?? '') . " | Midtrans Order ID: {$orderId}",
                            'updated_at' => now()
                        ]);
                }

                $this->dispatch('midtransPending', [
                    'message' => 'Pembayaran sedang diproses. Status akan diperbarui otomatis via webhook.',
                    'transaction_id' => $transactionId
                ]);
            } else {
                // Untuk cashier - buat transaksi baru dengan status menunggu
                $this->dispatch('midtransPending', [
                    'message' => 'Pembayaran sedang diproses. Status akan diperbarui otomatis.',
                    'create_transaction' => true,
                    'status' => 'menunggu',
                    'midtrans_result' => $result
                ]);
            }

            // Auto close modal setelah pending
            $this->closeModal();

        } catch (\Exception $e) {
            $this->dispatch('midtransError', 'Terjadi kesalahan saat memproses pembayaran pending.');
            $this->closeModal();
        }
    }

    public function handleError(array $result): void
    {
        $this->paymentStatus = 'failed';
        $this->paymentResult = $result;

        // Filter CORS errors - jangan tampilkan toast
        $errorMessage = $result['error'] ?? 'Pembayaran gagal. Silakan coba lagi.';
        
        if (is_string($errorMessage) && (
            str_contains($errorMessage, 'postMessage') ||
            str_contains($errorMessage, 'Cannot read properties of null') ||
            str_contains(strtolower($errorMessage), 'cors')
        )) {
            // CORS error - close modal tanpa error toast
            $this->closeModal();
            return;
        }

        // Dispatch error ke parent component hanya untuk real errors
        $this->dispatch('midtransError', $errorMessage);

        // Auto close modal setelah error
        $this->closeModal();
    }

    public function handleClose(): void
    {
        $this->paymentStatus = 'closed';

        // Dispatch ke parent component - info message, bukan error
        $this->dispatch('midtransClose', 'Popup pembayaran ditutup.');

        $this->closeModal();
    }

    private function getCustomerDetails(): array
    {
        $transactionId = $this->transactionData['transaction_id'] ?? null;

        // Company values dari settings sebagai fallback
        $companyName = Setting::get('business_name') ?? 'KasirMu';
        $companyEmail = Setting::get('contact_email') ?? 'info@kasirmu.com';


        if ($transactionId) {
            // Untuk existing transaction, optimisasi dengan single query
            $user = User::whereHas('transactions', function($query) use ($transactionId) {
                $query->where('id', $transactionId);
            })->first();

            if ($user) {

                return [
                    'first_name' => $user->name ?: $companyName,
                    'last_name' => '',
                    'email' => $user->email ?: $companyEmail
                ];
            }
        }

        // Untuk new transaction (cashier), gunakan user yang sedang login
        $user = Auth::user();


        $firstName = $user?->name ?: $companyName;
        $email = $user?->email ?: $companyEmail;

        // Validasi dan sanitasi email
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : $companyEmail;
        $email = $email ?: 'customer@kasirmu.com'; // final fallback

        // Validasi name tidak kosong
        $firstName = trim($firstName) ?: $companyName;
        $firstName = $firstName ?: 'Customer'; // final fallback

        return [
            'first_name' => $firstName,
            'last_name' => '',
            'email' => $email
        ];
    }

    public function render()
    {
        return view('livewire.components.modal-payment-midtrans');
    }
}
