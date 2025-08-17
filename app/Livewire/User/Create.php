<?php

declare(strict_types=1);

namespace App\Livewire\User;

use Exception;
use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

#[Title('Buat Pengguna')]
class Create extends Component
{
    use Toast;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|string|same:password')]
    public string $password_confirmation = '';

    #[Validate('required|in:0,1')]
    public string $is_super_admin = '0';

    #[Validate('nullable|date')]
    public ?string $email_verified_at = null;

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi kata sandi tidak sama dengan kata sandi.',
            
            'is_super_admin.required' => 'Role pengguna wajib dipilih.',
            'is_super_admin.in' => 'Role pengguna tidak valid.',
            
            'email_verified_at.date' => 'Format tanggal verifikasi email tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'is_super_admin' => 'role pengguna',
            'email_verified_at' => 'tanggal verifikasi email',
        ];
    }

    public function simpan()
    {
        $this->validate();

        try {
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'is_super_admin' => (bool) $this->is_super_admin,
                'email_verified_at' => $this->email_verified_at,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('users')->insert($userData);

            $this->success('Pengguna berhasil dibuat!', redirectTo: route('users.index'));
        } catch (Exception) {
            $this->error('Gagal membuat pengguna. Silakan coba lagi.');
        }
    }

    public function getRoleOptions(): array
    {
        return [
            ['id' => '0', 'name' => 'Kasir'],
            ['id' => '1', 'name' => 'Super Admin'],
        ];
    }

    public function render()
    {
        return view('livewire.user.create');
    }
}
