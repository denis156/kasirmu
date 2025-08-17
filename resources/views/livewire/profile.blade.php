<div>
    <!-- HEADER -->
    <x-header title="Profile" subtitle="Kelola informasi akun Anda" icon="phosphor.user"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator progress-indicator />

    <!-- USER INFO CARD -->
    <x-card title="Informasi Akun" subtitle="Detail akun Anda" shadow
        class="bg-base-100 shadow-sm shadow-primary border border-base-content/10 mt-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-stat
                    title="Role"
                    description="Level akses Anda"
                    value="{{ Auth::user()->is_super_admin ? 'Administrator' : 'Kasir' }}"
                    icon="phosphor.shield-check"
                    class="bg-success/10 text-success border border-success/20 shadow-sm shadow-success" />
            </div>
            <div>
                <x-stat
                    title="Bergabung Sejak"
                    description="Tanggal pembuatan akun"
                    value="{{ Auth::user()->created_at->format('d M Y') }}"
                    icon="phosphor.calendar"
                    class="bg-info/10 text-info border border-info/20 shadow-sm shadow-info" />
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- EDIT PROFILE CARD -->
        <x-card title="Informasi Profile" subtitle="Perbarui informasi dasar akun Anda" shadow
            class="bg-base-100 shadow-sm shadow-primary border border-base-content/10">
            <x-form wire:submit="updateProfile">
                <x-input label="Nama Lengkap" wire:model="name" icon="phosphor.user" required />
                <x-input label="Email" wire:model="email" icon="phosphor.envelope" type="email" required />

                <x-slot:actions>
                    <x-button label="Perbarui Profile" class="btn-success" type="submit" spinner="updateProfile" />
                </x-slot:actions>
            </x-form>
        </x-card>

        <!-- CHANGE PASSWORD CARD -->
        <x-card title="Ubah Password" subtitle="Perbarui password untuk keamanan akun" shadow
            class="bg-base-100 shadow-sm shadow-primary border border-base-content/10">
            <x-form wire:submit="changePassword">
                <x-input label="Password Saat Ini" wire:model="current_password" icon="phosphor.lock" type="password" required />
                <x-input label="Password Baru" wire:model="password" icon="phosphor.lock-simple" type="password" required />
                <x-input label="Konfirmasi Password Baru" wire:model="password_confirmation" icon="phosphor.lock-simple" type="password" required />

                <x-slot:actions>
                    <x-button label="Ubah Password" class="btn-warning" type="submit" spinner="changePassword" />
                </x-slot:actions>
            </x-form>
        </x-card>
    </div>
</div>
