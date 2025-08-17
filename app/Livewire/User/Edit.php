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

#[Title('Edit Pengguna')]
class Edit extends Component
{
    use Toast;

    public int $userId;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('nullable|string|min:8')]
    public string $password = '';

    #[Validate('nullable|string|same:password')]
    public string $password_confirmation = '';

    #[Validate('required|in:0,1')]
    public string $is_super_admin = '0';

    #[Validate('nullable|date')]
    public ?string $email_verified_at = null;

    public function mount(int $id): void
    {
        $this->userId = $id;
        $this->loadUser();
    }

    public function loadUser(): void
    {
        $user = DB::table('users')->where('id', $this->userId)->first();

        if (!$user) {
            $this->error('Pengguna tidak ditemukan.', redirectTo: route('users.index'));
            return;
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->is_super_admin = (string) (int) $user->is_super_admin;
        $this->email_verified_at = $user->email_verified_at ? 
            date('Y-m-d\TH:i', strtotime($user->email_verified_at)) : null;
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            
            'password.min' => 'Kata sandi minimal 8 karakter.',
            
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

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => 'nullable|string|min:8',
            'password_confirmation' => 'nullable|string|same:password',
            'is_super_admin' => 'required|in:0,1',
            'email_verified_at' => 'nullable|date',
        ];
    }

    public function simpan(): void
    {
        $this->validate();

        try {
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'is_super_admin' => (bool) $this->is_super_admin,
                'email_verified_at' => $this->email_verified_at,
                'updated_at' => now(),
            ];

            // Only update password if provided
            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            DB::table('users')->where('id', $this->userId)->update($userData);

            $this->success('Pengguna berhasil diupdate!', redirectTo: route('users.index'));
        } catch (Exception) {
            $this->error('Gagal mengupdate pengguna. Silakan coba lagi.');
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
        return view('livewire.user.edit');
    }
}