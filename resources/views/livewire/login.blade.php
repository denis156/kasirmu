<x-card title="{{ config('app.name') }}" subtitle="Sistem Kasir By Artelia.Dev"
    class="bg-base-100 border border-base-content/10 shadow-sm shadow-primary" separator>
    <!-- Login Form -->
    <form wire:submit.prevent="authenticate" class="space-y-4">
        <div>
            <x-input label="Email atau Nama Pengguna" wire:model="login" icon="phosphor.user"
                placeholder="Masukkan email atau nama pengguna..." required />
        </div>

        <div>
            <x-input label="Password" wire:model="password" type="password" icon="phosphor.lock"
                placeholder="Masukkan password..." right required />
        </div>

        <div>
            <x-checkbox label="Ingat saya" wire:model="remember" />
        </div>

        <div class="pt-4">
            <x-button label="Masuk" icon="phosphor.sign-in" class="btn-primary w-full" type="submit" spinner />
        </div>
    </form>
</x-card>
