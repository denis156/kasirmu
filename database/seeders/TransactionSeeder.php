<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_super_admin', false)->get();
        $products = Product::all();
        
        // Generate beberapa transaksi sample
        for ($i = 1; $i <= 10; $i++) {
            $user = $users->random();
            $transactionDate = now()->subDays(rand(0, 30));
            
            // Generate unique transaction code dengan timestamp
            $transactionCode = 'TRX-' . $transactionDate->format('Ymd') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
            
            $transaction = Transaction::create([
                'transaction_code' => $transactionCode,
                'user_id' => $user->id,
                'tax_rate' => 10.00, // 10% tax
                'discount_amount' => rand(0, 5) > 3 ? rand(5000, 20000) : 0,
                'total_amount' => 0, // akan dihitung nanti
                'paid_amount' => 0, // akan dihitung nanti
                'change_amount' => 0, // akan dihitung nanti
                'payment_method' => collect([
                    'tunai', 
                    'kartu', 
                    'transfer', 
                    'qris', 
                    'ewallet', 
                    'semua',
                    'gopay',
                    'shopeepay'
                ])->random(),
                'status' => collect(['selesai', 'menunggu', 'dibatalkan'])->random(),
                'notes' => rand(0, 5) > 3 ? 'Transaksi normal' : null,
                'transaction_date' => $transactionDate,
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);

            // Generate items untuk transaksi ini
            $numberOfItems = rand(1, 5);
            $subtotal = 0;
            
            for ($j = 1; $j <= $numberOfItems; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $itemTotal = $product->price * $quantity;
                $subtotal += $itemTotal;
                
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                ]);
                
                // Update stock dan terjual product
                $product->decrement('stock', $quantity);
                $product->increment('terjual', $quantity);
            }
            
            // Hitung total dengan tax dan discount
            $taxAmount = ($subtotal * $transaction->tax_rate) / 100;
            $totalAmount = $subtotal + $taxAmount - $transaction->discount_amount;
            
            // Generate paid amount (kadang lebih dari total untuk kembalian)
            $paidAmount = $totalAmount + rand(0, 20000);
            $changeAmount = max(0, $paidAmount - $totalAmount);
            
            $transaction->update([
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
            ]);
        }
    }
}