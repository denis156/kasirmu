<div>
    <!-- HEADER -->
    <x-header title="Daftar Pengguna" subtitle="Kelola pengguna di halaman ini" icon="phosphor.users-four"
        icon-classes="bg-primary rounded-full p-1 w-8 h-8" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input class="input-sm input-primary" placeholder="Search..." wire:model.live.debounce="search" clearable
                icon="phosphor.magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="phosphor.plus" class="btn-sm btn-success btn-outline" link="{{ route('users.create') }}" label="Tambah Pengguna" responsive />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card
        class="overflow-x-auto rounded-box border border-base-content/10 p-2 mt-4 bg-base-100 shadow-sm shadow-primary">
        <x-table :headers="$this->headers()" :rows="$this->users()" with-pagination striped>
            <x-slot:empty>
                <div class="text-center py-16">
                    <x-icon name="phosphor.users-four" class="w-16 h-16 mx-auto mb-4 text-base-content/40" />
                    <h3 class="text-lg font-semibold text-base-content mb-2">Belum ada pengguna</h3>
                    <p class="text-base-content/60 mb-6">Mulai dengan menambahkan pengguna pertama</p>
                    <x-button label="Tambah Pengguna" icon="phosphor.plus" class="btn-success btn-sm"
                        link="{{ route('users.create') }}" />
                </div>
            </x-slot:empty>
            @scope('cell_no', $user)
                {{ ($this->users()->currentPage() - 1) * $this->users()->perPage() + $loop->iteration }}
            @endscope
            @scope('cell_is_super_admin', $user)
                @if ($user->is_super_admin)
                    <x-badge value="Admin" class="badge-sm badge-soft badge-success" />
                @else
                    <x-badge value="Kasir" class="badge-sm badge-soft badge-info" />
                @endif
            @endscope
            @scope('actions', $user)
                <div class="flex gap-2">
                    <x-button icon="phosphor.pencil" link="{{ route('users.edit', $user->id) }}"
                        class="btn-sm btn-warning" label="Edit" responsive />
                    <x-button icon="phosphor.trash" wire:click="showDeleteModal({{ $user->id }})" spinner
                        class="btn-sm btn-outline btn-error" label="Hapus" responsive />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DELETE CONFIRMATION MODAL -->
    <x-modal wire:model="deleteModal" class="modal-bottom sm:modal-middle backdrop-blur" title="Konfirmasi Hapus"
        persistent>
        @if ($userToDelete)
            <p class="text-gray-600">Apakah Anda yakin ingin menghapus pengguna
                <strong>{{ $userToDelete->name }}</strong>?
            </p>
            <p class="text-sm text-gray-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
        @endif

        <x-slot:actions>
            <x-button icon="phosphor.x" label="Batal" class="btn-soft" wire:click="cancelDelete" responsive />
            <x-button icon="phosphor.check" label="Hapus" class="btn-error" wire:click="confirmDelete" responsive />
        </x-slot:actions>
    </x-modal>
</div>
