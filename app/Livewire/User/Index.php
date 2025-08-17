<?php

declare(strict_types=1);

namespace App\Livewire\User;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

#[Title('Daftar Pengguna')]
class Index extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public int $perPage = 10;

    public bool $deleteModal = false;

    public ?object $userToDelete = null;


    // Show delete modal
    public function showDeleteModal($id): void
    {
        $this->userToDelete = DB::table('users')->where('id', $id)->first();
        $this->deleteModal = true;
    }

    // Confirm delete
    public function confirmDelete(): void
    {
        if ($this->userToDelete) {
            DB::table('users')->where('id', $this->userToDelete->id)->delete();
            $this->success("User '{$this->userToDelete->name}' berhasil dihapus.");
            $this->userToDelete = null;
        }
        $this->deleteModal = false;
    }

    // Cancel delete
    public function cancelDelete(): void
    {
        $this->userToDelete = null;
        $this->deleteModal = false;
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'no', 'label' => 'No.', 'sortable' => false, 'disableLink' => true],
            ['key' => 'name', 'label' => 'Nama'],
            ['key' => 'email', 'label' => 'Email', 'sortable' => false],
            ['key' => 'is_super_admin', 'label' => 'Role', 'disableLink' => true],
        ];
    }

    public function users()
    {
        $query = DB::table('users');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Apply sorting
        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        // Get total count
        $total = $query->count();

        // Get current page
        $currentPage = Paginator::resolveCurrentPage();

        // Get items for current page
        $items = $query->offset(($currentPage - 1) * $this->perPage)
                      ->limit($this->perPage)
                      ->get();

        // Convert to objects with proper boolean casting
        $items = $items->map(function ($item) {
            $item->is_super_admin = (bool) $item->is_super_admin;
            return $item;
        });

        // Return paginator
        return new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function render()
    {
        return view('livewire.user.index');
    }
}
