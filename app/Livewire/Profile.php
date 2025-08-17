<?php

declare(strict_types=1);

namespace App\Livewire;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

#[Title('Profile')]
class Profile extends Component
{
    use Toast;

    // Profile fields
    public string $name = '';
    public string $email = '';

    // Password fields
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    // Update profile
    public function updateProfile(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'name' => $this->name,
                'email' => $this->email,
                'updated_at' => now(),
            ]);

        $this->success('Profile berhasil diperbarui.');
    }

    // Change password
    public function changePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();

        // Check current password
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password saat ini tidak benar.');
            return;
        }

        // Update password
        DB::table('users')
            ->where('id', Auth::id())
            ->update([
                'password' => Hash::make($this->password),
                'updated_at' => now(),
            ]);

        // Reset password fields
        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->success('Password berhasil diubah.');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
