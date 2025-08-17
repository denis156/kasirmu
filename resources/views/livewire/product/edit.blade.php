<div>
    <!-- HEADER -->
    <x-header title="Edit Produk" subtitle="Edit data produk di halaman ini" icon="phosphor.pencil"
        icon-classes="bg-warning rounded-full p-1 w-8 h-8" separator progress-indicator>
        <x-slot:actions>
            <x-button icon="phosphor.arrow-fat-line-left" class="btn-sm btn-outline" link="{{ route('products.index') }}"
                label="Kembali" responsive />
        </x-slot:actions>
    </x-header>

    <!-- Content -->
    <x-card
        class="overflow-x-auto rounded-box border border-base-content/10 p-2 mt-4 bg-base-100 shadow-sm shadow-warning">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input class="input-warning" label="Nama Produk" icon="phosphor.package" wire:model="name"
                    placeholder="Masukkan nama produk..." required />
            </div>
            <div>
                <x-select class="input-warning" label="Status Produk" wire:model="is_active" :options="$this->getStatusOptions()"
                    icon="phosphor.check-circle" option-value="id" option-label="name" placeholder="Pilih status..."
                    required />
            </div>
            <div>
                <x-input class="input-warning" label="Harga" wire:model="price" prefix="Rp" locale="id-ID" money
                    required />
            </div>
            <div>
                <x-input class="input-warning" label="Barcode (Opsional)" icon="phosphor.barcode" wire:model="barcode"
                    placeholder="Masukkan barcode..." />
            </div>
            <div>
                <x-input class="input-warning" label="Stok" icon="phosphor.stack" wire:model="stock" placeholder="0"
                    type="number" min="0" required />
            </div>
            <div>
                <x-input class="input-warning" label="Stok Minimum" icon="phosphor.warning" wire:model="min_stock"
                    placeholder="0" type="number" min="0" required />
            </div>
            <div class="col-span-full">
                <x-textarea class="input-warning" label="Deskripsi (Opsional)" wire:model="description"
                    placeholder="Masukkan deskripsi produk..." rows="3" />
            </div>
            <div class="col-span-full">
                <x-select class="input-warning" label="Kategori" wire:model="category_id" :options="$this->getCategoryOptions()"
                    icon="phosphor.tag" option-value="id" option-label="name" placeholder="Pilih kategori...">
                    <x-slot:append>
                        <x-button label="Kategori" icon="phosphor.plus" class="join-item btn-primary"
                            wire:click="showCreateCategoryModal" responsive />
                    </x-slot:append>
                </x-select>
            </div>
        </div>
        <div class="flex gap-4 mt-8 justify-end">
            <x-button icon="phosphor.floppy-disk" class="btn-md btn-warning" label="Update" wire:click="simpan" spinner
                responsive />
        </div>
    </x-card>

    <!-- Create Category Modal -->
    <x-modal wire:model="createCategoryModal" class="modal-bottom sm:modal-middle backdrop-blur"
        title="Buat Kategori Baru" persistent>
        <div class="space-y-4">
            <x-input label="Nama Kategori" wire:model="newCategoryName" placeholder="Masukkan nama kategori..."
                required />
            <x-textarea label="Deskripsi (Opsional)" wire:model="newCategoryDescription"
                placeholder="Masukkan deskripsi kategori..." rows="3" />
        </div>

        <x-slot:actions>
            <x-button label="Batal" icon="phosphor.x" class="btn-soft" @click="$wire.createCategoryModal = false" />
            <x-button label="Simpan" icon="phosphor.check" class="btn-success" wire:click="saveCategory" />
        </x-slot:actions>
    </x-modal>
</div>
