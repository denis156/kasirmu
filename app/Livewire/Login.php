<?php

declare(strict_types=1);

namespace App\Livewire;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

#[Title('Login')]
#[Layout('components.layouts.auth')]
class Login extends Component
{
    use Toast;

    #[Validate('required|string')]
    public string $login = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    public bool $remember = false;

    protected function messages(): array
    {
        return [
            'login.required' => 'Email atau nama pengguna wajib diisi.',
            'login.string' => 'Email atau nama pengguna harus berupa teks.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }

    public function authenticate(): void
    {
        $this->validate();

        // Try login with email first, then with name
        $user = User::where('email', $this->login)
                   ->orWhere('name', $this->login)
                   ->first();

        if (!$user || !Hash::check($this->password, $user->password)) {
            $this->error('Email/nama pengguna atau password salah.');
            return;
        }

        Auth::login($user, $this->remember);

        $this->success("Selamat datang, {$user->name}!", redirectTo: route('dashboard'));
    }

    public function render()
    {
        return view('livewire.login');
    }
}
