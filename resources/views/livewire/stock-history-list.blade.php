<main id="main" class="main">
    <div class="pagetitle">
        <h1>Riwayat Stok Barang</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stock.page') }}">Stok Barang</a></li>
                <li class="breadcrumb-item active">Riwayat Stok</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">
                        @if($selectedProduct)
                            Riwayat Stok: <span class="text-primary">{{ $selectedProduct->name }}</span>
                        @else
                            Riwayat Stok Barang
                        @endif
                    </h5>
                    <button wire:click="toggleFilters" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i>
                        {{ $showFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}
                    </button>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Entri</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-list-ul"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ number_format($summary['total_entries']) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Stok Masuk</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-arrow-down-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ number_format($summary['total_in']) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Stok Keluar</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-arrow-up-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ number_format($summary['total_out']) }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Perubahan Netto</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6 class="{{ $summary['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $summary['net_change'] >= 0 ? '+' : '' }}{{ number_format($summary['net_change']) }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                @if($showFilters)
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Filter Riwayat</h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="product_filter" class="form-label">Produk</label>
                                    <select wire:model.live="selectedProductId" class="form-select" id="product_filter">
                                        <option value="">Semua Produk</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="type_filter" class="form-label">Tipe</label>
                                    <select wire:model.live="selectedType" class="form-select" id="type_filter">
                                        <option value="">Semua Tipe</option>
                                        <option value="in">Stok Masuk</option>
                                        <option value="out">Stok Keluar</option>
                                        <option value="adjustment">Penyesuaian</option>
                                        <option value="initial">Stok Awal</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="date_from" class="form-label">Dari Tanggal</label>
                                    <input type="date" wire:model.live="dateFrom" class="form-control" id="date_from">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                                    <input type="date" wire:model.live="dateTo" class="form-control" id="date_to">
                                </div>
                            </div>

                            <div class="text-end">
                                <button wire:click="clearFilters" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Produk</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Perubahan</th>
                                <th scope="col">Sebelum</th>
                                <th scope="col">Sesudah</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col">User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $history->created_at->format('d/m/Y') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $history->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $history->product->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $history->type_badge }}">
                                            {{ $history->type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="{{ $history->quantity_change >= 0 ? 'text-success' : 'text-danger' }}">
                                            <strong>{{ $history->formatted_quantity_change }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($history->quantity_before) }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($history->quantity_after) }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($history->description, 50) }}</small>
                                        @if($history->reference_type)
                                            <br>
                                            <small class="text-muted">
                                                Ref: {{ class_basename($history->reference_type) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $history->user->name }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Belum ada riwayat stok
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-sm-flex justify-content-sm-between align-items-sm-center mt-3">
                    <div class="text-muted text-center text-sm-start mb-2 mb-sm-0">
                        <small>
                            Menampilkan {{ $histories->firstItem() ?? 0 }}
                            sampai {{ $histories->lastItem() ?? 0 }}
                            dari {{ $histories->total() ?? 0 }} data
                        </small>
                    </div>

                    {{-- Custom Pagination Links --}}
                    @if ($histories->hasPages())
                        <nav aria-label="Pagination Navigation" class="d-flex justify-content-center">
                            <ul class="pagination pagination-sm mb-0">
                                {{-- Previous Page Link --}}
                                @if ($histories->onFirstPage())
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
                                    $currentPage = $histories->currentPage();
                                    $lastPage = $histories->lastPage();
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
                                @if ($histories->hasMorePages())
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
    </section>
</main>
