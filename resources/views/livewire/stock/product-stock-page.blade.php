<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Stok Barang</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Stok Barang</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-box-seam"></i> Kelola Stok Barang</h5>
                        <div>
                            <a href="{{ route('stock.history') }}" class="btn btn-outline-info me-2">
                                <i class="bi bi-clock-history"></i> Riwayat Stok
                            </a>
                            <button wire:click="create()" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Tambah Produk</button>
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive mt-3">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Produk</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr class="{{ $product->quantity <= $product->low_stock_threshold ? 'table-danger' : '' }}">
                                        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->quantity }}</td>
                                        <td>
                                            @if($product->quantity <= $product->low_stock_threshold)
                                                <span class="badge bg-danger">Stok Rendah</span>
                                            @else
                                                <span class="badge bg-success">Aman</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button wire:click="addStock({{ $product->id }})" class="btn btn-sm btn-success" title="Tambah Stok"><i class="bi bi-plus-circle"></i></button>
                                                <button wire:click="adjustStock({{ $product->id }})" class="btn btn-sm btn-info" title="Sesuaikan Stok"><i class="bi bi-arrow-left-right"></i></button>
                                                <button wire:click="edit({{ $product->id }})" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></button>
                                                <button wire:click="delete({{ $product->id }})" wire:confirm="Anda yakin ingin menghapus produk ini?" class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{-- Custom Pagination --}}
                        <div class="d-sm-flex justify-content-sm-between align-items-sm-center">
                            <div class="text-muted text-center text-sm-start mb-2 mb-sm-0">
                                <small>
                                    Menampilkan {{ $products->firstItem() ?? 0 }}
                                    sampai {{ $products->lastItem() ?? 0 }}
                                    dari {{ $products->total() ?? 0 }} data
                                </small>
                            </div>

                            {{-- Custom Pagination Links --}}
                            @if ($products->hasPages())
                                <nav aria-label="Pagination Navigation" class="d-flex justify-content-center">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($products->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="bi bi-chevron-left"></i>
                                                </span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <button class="page-link" wire:click="previousPage" rel="prev">
                                                    <i class="bi bi-chevron-left"></i>
                                                </button>
                                            </li>
                                        @endif

                                        {{-- Smart Pagination Elements --}}
                                        @php
                                            $currentPage = $products->currentPage();
                                            $lastPage = $products->lastPage();
                                            $maxVisible = 7;
                                            
                                            if ($lastPage <= $maxVisible) {
                                                $links = range(1, $lastPage);
                                            } else {
                                                $links = [];
                                                $links[] = 1;
                                                
                                                $start = max(2, $currentPage - floor(($maxVisible - 4) / 2));
                                                $end = min($lastPage - 1, $start + $maxVisible - 5);
                                                
                                                if ($end == $lastPage - 1) {
                                                    $start = max(2, $end - $maxVisible + 5);
                                                }
                                                
                                                if ($start > 2) {
                                                    $links[] = 'ellipsis';
                                                }
                                                
                                                for ($i = $start; $i <= $end; $i++) {
                                                    $links[] = $i;
                                                }
                                                
                                                if ($end < $lastPage - 1) {
                                                    $links[] = 'ellipsis';
                                                }
                                                
                                                if ($lastPage > 1) {
                                                    $links[] = $lastPage;
                                                }
                                            }
                                        @endphp
                                        
                                        @foreach ($links as $page)
                                            @if ($page === 'ellipsis')
                                                <li class="page-item ellipsis">
                                                    <span class="page-link"></span>
                                                </li>
                                            @elseif ($page == $currentPage)
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <button class="page-link" wire:click="gotoPage({{ $page }})">
                                                        {{ $page }}
                                                    </button>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($products->hasMorePages())
                                            <li class="page-item">
                                                <button class="page-link" wire:click="nextPage" rel="next">
                                                    <i class="bi bi-chevron-right"></i>
                                                </button>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="bi bi-chevron-right"></i>
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($isOpen)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit.prevent="store">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $productId ? 'Edit Produk' : 'Tambah Produk' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal()"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk</label>
                                <input type="text" wire:model="name" id="name" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Jumlah Stok</label>
                                <input type="number" wire:model="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" required>
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="low_stock_threshold" class="form-label">Ambang Stok Rendah</label>
                                <input type="number" wire:model="low_stock_threshold" id="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror" required>
                                @error('low_stock_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal()">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Add Stock Modal -->
        @if($showAddStockModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit.prevent="storeAddStock">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Stok - {{ $selectedProduct->name ?? '' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeAddStockModal()"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>Stok Saat Ini:</strong> {{ $selectedProduct->quantity ?? 0 }}
                            </div>
                            <div class="mb-3">
                                <label for="add_quantity" class="form-label">Jumlah yang Ditambahkan</label>
                                <input type="number" wire:model="addQuantity" id="add_quantity" class="form-control @error('addQuantity') is-invalid @enderror" min="1" required>
                                @error('addQuantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="add_description" class="form-label">Keterangan</label>
                                <textarea wire:model="addDescription" id="add_description" class="form-control @error('addDescription') is-invalid @enderror" rows="3" placeholder="Contoh: Restock dari supplier, Pembelian baru, dll"></textarea>
                                @error('addDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeAddStockModal()">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle me-1"></i>
                                Tambah Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Adjust Stock Modal -->
        @if($showAdjustStockModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit.prevent="storeAdjustStock">
                        <div class="modal-header">
                            <h5 class="modal-title">Sesuaikan Stok - {{ $selectedProduct->name ?? '' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeAdjustStockModal()"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <strong>Stok Saat Ini:</strong> {{ $selectedProduct->quantity ?? 0 }}
                            </div>
                            <div class="mb-3">
                                <label for="adjust_quantity" class="form-label">Stok Baru</label>
                                <input type="number" wire:model="adjustQuantity" id="adjust_quantity" class="form-control @error('adjustQuantity') is-invalid @enderror" min="0" required>
                                @error('adjustQuantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="adjust_description" class="form-label">Keterangan Penyesuaian</label>
                                <textarea wire:model="adjustDescription" id="adjust_description" class="form-control @error('adjustDescription') is-invalid @enderror" rows="3" placeholder="Contoh: Koreksi stok, Stok rusak, dll"></textarea>
                                @error('adjustDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeAdjustStockModal()">Batal</button>
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-arrow-left-right me-1"></i>
                                Sesuaikan Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </main>
</div>