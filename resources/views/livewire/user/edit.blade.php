<div>
    <!-- HEADER -->
    <x-header title="Edit Pengguna" subtitle="Edit data pengguna di halaman ini" icon="phosphor.pencil"
        icon-classes="bg-warning rounded-full p-1 w-8 h-8" separator progress-indicator>
        <x-slot:actions>
            <x-button icon="phosphor.arrow-fat-line-left" class="btn-sm btn-outline" link="{{ route('users.index') }}"
                label="Kembali" responsive />
        </x-slot:actions>
    </x-header>

    <!-- Content -->
    <x-card
        class="overflow-x-auto rounded-box border border-base-content/10 p-2 mt-4 bg-base-100 shadow-sm shadow-warning">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input class="input-warning" label="Nama" icon="phosphor.user" wire:model="name" placeholder="Masukkan nama..." required />
            </div>
            <div>
                <x-input class="input-warning" label="Email" icon="phosphor.envelope" wire:model="email" placeholder="Masukkan email..." type="email" required />
            </div>
            <div>
                <x-password class="input-warning" label="Kata Sandi (Kosongkan jika tidak ingin mengubah)" icon="phosphor.lock" wire:model="password" placeholder="Minimal 8 karakter..." right />
            </div>
            <div>
                <x-password class="input-warning" label="Konfirmasi Kata Sandi" icon="phosphor.lock" wire:model="password_confirmation" placeholder="Ulangi kata sandi..." right />
            </div>
            <div>
                <x-datetime class="input-warning" label="Verifikasi Email (Opsional)" wire:model="email_verified_at" type="datetime-local" />
            </div>
            <div>
                <x-select class="input-warning" label="Role Pengguna" wire:model="is_super_admin" :options="$this->getRoleOptions()" icon="phosphor.user-gear" option-value="id" option-label="name" placeholder="Pilih role..." required />
            </div>
        </div>
        <div class="flex gap-4 mt-8 justify-end">
            <x-button icon="phosphor.floppy-disk" class="btn-md btn-warning" label="Update" wire:click="simpan" spinner responsive />
        </div>
    </x-card>
</div>
