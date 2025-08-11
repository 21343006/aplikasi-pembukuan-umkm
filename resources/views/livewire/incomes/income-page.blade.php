<main id="main" class="main">
    <div class="pagetitle">
        <h1>Pendapatan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pendapatan</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-currency-dollar"></i> Kelola Data Pendapatan
                </h5>

                {{-- Filter Bulan dan Tahun --}}
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="filterMonth" class="form-label">Pilih Bulan</label>
                        <select wire:model.live="filterMonth" class="form-select @error('filterMonth') is-invalid @enderror">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach($monthNames as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterYear" class="form-label">Pilih Tahun</label>
                        <select wire:model.live="filterYear" class="form-select @error('filterYear') is-invalid @enderror">
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($this->getAvailableYears() as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($filterMonth && $filterYear)
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-secondary" wire:click="clearFilters">
                                <i class="bi bi-arrow-clockwise"></i> Reset Filter
                            </button>
                        </div>
                    @endif
                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        <button wire:click="exportCSV" class="btn btn-success">
                            Export CSV
                        </button>
                    </div>
                </div>

                {{-- Informasi Summary --}}
                @if(count($monthlyTotals) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-graph-up"></i> Ringkasan Pendapatan
                                        <small class="text-muted">(6 Bulan Terakhir)</small>
                                    </h6>
                                    <div class="row">
                                        @php
                                            $displayedCount = 0;
                                            $maxDisplay = 6;
                                        @endphp
                                        @foreach(array_slice($monthlyTotals, 0, 6) as $period => $total)
                                            @php
                                                // Perbaikan: Validasi yang lebih aman
                                                if (!is_string($period) || empty($period)) {
                                                    continue;
                                                }
                                                
                                                $periodParts = explode('-', $period);
                                                if (count($periodParts) !== 2) {
                                                    continue;
                                                }
                                                
                                                $year = (int) $periodParts[0];
                                                $month = (int) $periodParts[1];
                                                
                                                if ($month < 1 || $month > 12 || $year < 1900 || $year > 2100) {
                                                    continue;
                                                }
                                                
                                                // Perbaikan: Akses array yang aman
                                                $monthName = $monthNames[$month] ?? 'Unknown';
                                                
                                                $isSelected = $filterMonth == $month && $filterYear == $year;
                                                $total = is_numeric($total) ? (float) $total : 0;
                                                $displayedCount++;
                                            @endphp
                                            <div class="col-lg-2 col-md-4 col-sm-6 col-6 mb-3">
                                                <div class="card {{ $isSelected ? 'border-success bg-success text-white shadow' : 'border-light' }} h-100">
                                                    <div class="card-body text-center p-3">
                                                        <div class="mb-2">
                                                            <small class="{{ $isSelected ? 'text-white-50' : 'text-muted' }}">
                                                                {{ $monthName }} {{ $year }}
                                                            </small>
                                                        </div>
                                                        <div class="fw-bold">
                                                            @if ($total >= 1000000)
                                                                <span class="fs-6">Rp {{ number_format($total / 1000000, 1, ',', '.') }}Jt</span>
                                                            @elseif ($total >= 1000)
                                                                <span class="fs-6">Rp {{ number_format($total / 1000, 0, ',', '.') }}Rb</span>
                                                            @else
                                                                <span class="small">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                                            @endif
                                                        </div>
                                                        @if ($isSelected)
                                                            <small class="text-white-50">
                                                                <i class="bi bi-check-circle"></i> Dipilih
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if (count($monthlyTotals) > 0)
                                            <div class="col-12 mt-3">
                                                <div class="alert alert-info mb-0">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-6">
                                                            <strong><i class="bi bi-info-circle"></i> Total Keseluruhan Pendapatan:</strong>
                                                        </div>
                                                        <div class="col-md-6 text-md-end">
                                                            @php
                                                                $grandTotal = 0;
                                                                foreach ($monthlyTotals as $value) {
                                                                    if (is_numeric($value)) {
                                                                        $grandTotal += (float) $value;
                                                                    }
                                                                }
                                                            @endphp
                                                            <span class="badge bg-primary fs-6 px-3 py-2">
                                                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle"></i> Data ringkasan pendapatan belum tersedia atau kosong.
                    </div>
                @endif

                {{-- Jika bulan & tahun sudah dipilih --}}
                @if ($filterMonth && $filterYear)
                    <div class="d-flex justify-content-between align-items-center pt-4">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table"></i> Data Pendapatan - {{ $this->monthName }} {{ $filterYear }}
                            @if ($jumlah > 0)
                                <span class="badge bg-success ms-2">
                                    Total: Rp {{ number_format($jumlah, 0, ',', '.') }}
                                </span>
                            @endif
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                            {{-- Dropdown untuk memilih jumlah data per halaman --}}
                            <div class="d-flex align-items-center">
                                <label class="form-label mb-0 me-2">Tampilkan:</label>
                                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                <span class="ms-2 text-muted">data per halaman</span>
                            </div>
                            <button class="btn btn-primary" wire:click="openModal">
                                <i class="bi bi-plus-circle"></i> Tambah Data
                            </button>
                        </div>
                    </div>

                    @if ($paginatedIncomes->count() > 0)
                        <div class="table-responsive mt-3">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="12%">Tanggal</th>
                                        <th width="25%">Produk/Jasa</th>
                                        <th width="12%">Jumlah Terjual</th>
                                        <th width="15%">Harga Satuan</th>
                                        <th width="15%">Total Harga</th>
                                        <th width="16%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($paginatedIncomes as $income)
                                        @if($income)
                                            <tr>
                                                <td>{{ ($paginatedIncomes->currentPage() - 1) * $paginatedIncomes->perPage() + $loop->iteration }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ \Carbon\Carbon::parse($income->tanggal)->format('d/m/Y') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $income->produk ?? '' }}">
                                                        {{ $income->produk ?? '' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ number_format($income->jumlah_terjual ?? 0, 0, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning text-dark">
                                                        Rp {{ number_format($income->harga_satuan ?? 0, 0, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success fs-6">
                                                        Rp {{ number_format(($income->jumlah_terjual ?? 0) * ($income->harga_satuan ?? 0), 0, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-warning" wire:click="edit({{ $income->id }})" title="Edit Data">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" wire:click="confirmDelete({{ $income->id }})" wire:confirm="Apakah Anda yakin ingin menghapus data '{{ \Illuminate\Support\Str::limit($income->produk ?? '', 30) }}'?" title="Hapus Data">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Pendapatan {{ $this->monthName }} {{ $filterYear }}:</th>
                                        <th>
                                            <span class="badge bg-success fs-6">
                                                Rp {{ number_format($jumlah, 0, ',', '.') }}
                                            </span>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Pagination dengan informasi data --}}
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                <small>
                                    Menampilkan {{ $paginatedIncomes->firstItem() ?? 0 }}
                                    sampai {{ $paginatedIncomes->lastItem() ?? 0 }}
                                    dari {{ $paginatedIncomes->total() ?? 0 }} data
                                </small>
                            </div>
                            
                            {{-- Custom Pagination Links --}}
                            @if ($paginatedIncomes->hasPages())
                                <nav aria-label="Pagination Navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($paginatedIncomes->onFirstPage())
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

                                        {{-- Pagination Elements --}}
                                        @foreach ($paginatedIncomes->getUrlRange(1, $paginatedIncomes->lastPage()) as $page => $url)
                                            @if ($page == $paginatedIncomes->currentPage())
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
                                        @if ($paginatedIncomes->hasMorePages())
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
                    @else
                        <div class="alert alert-info mt-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle fs-4 me-3"></i>
                                <div>
                                    <h6 class="mb-1">Belum Ada Data</h6>
                                    <p class="mb-0">
                                        Belum ada data pendapatan untuk bulan <strong>{{ $this->monthName }} {{ $filterYear }}</strong>.
                                        <br>Klik tombol "Tambah Data" untuk menambahkan data pendapatan baru.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-primary mt-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar3 fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-1">Pilih Periode</h6>
                                <p class="mb-0">
                                    <strong>Silakan pilih bulan dan tahun terlebih dahulu</strong> untuk melihat dan menambahkan data pendapatan.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-{{ $isEdit ? 'pencil' : 'plus-circle' }}"></i>
                                {{ $isEdit ? 'Edit' : 'Tambah' }} Pendapatan - {{ $this->monthName }} {{ $filterYear }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror" wire:model.live="tanggal" min="{{ $this->minDate }}" max="{{ $this->maxDate }}">
                                        @error('tanggal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Harus dalam rentang {{ $this->monthName }} {{ $filterYear }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Produk/Jasa <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('produk') is-invalid @enderror" wire:model.live="produk" placeholder="Masukkan nama produk atau jasa" maxlength="255">
                                        @error('produk')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jumlah Terjual <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('jumlah_terjual') is-invalid @enderror" wire:model.live="jumlah_terjual" min="1" placeholder="0">
                                        @error('jumlah_terjual')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-success text-white">Rp</span>
                                            <input type="number" class="form-control @error('harga_satuan') is-invalid @enderror" wire:model.live="harga_satuan" min="0" step="0.01" placeholder="0">
                                            @error('harga_satuan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($jumlah_terjual && $harga_satuan)
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-calculator"></i> Perhitungan:</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Jumlah:</strong><br>
                                                <span class="badge bg-info">
                                                    {{ number_format($jumlah_terjual, 0, ',', '.') }} unit
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Harga Satuan:</strong><br>
                                                <span class="badge bg-warning text-dark">
                                                    Rp {{ number_format($harga_satuan, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total:</strong><br>
                                                <span class="badge bg-success">
                                                    Rp {{ number_format($jumlah_terjual * $harga_satuan, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Produk:</strong><br>
                                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($produk ?? '', 20) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($tanggal && $produk && $jumlah_terjual && $harga_satuan)
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-eye"></i> Preview Data:</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Tanggal:</strong><br>
                                                <span class="badge bg-secondary">
                                                    {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Produk/Jasa:</strong><br>
                                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($produk ?? '', 15) }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Qty x Harga:</strong><br>
                                                <span class="badge bg-info">
                                                    {{ number_format($jumlah_terjual, 0, ',', '.') }} x Rp {{ number_format($harga_satuan, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Pendapatan:</strong><br>
                                                <span class="badge bg-success">
                                                    Rp {{ number_format($jumlah_terjual * $harga_satuan, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                <i class="bi bi-x-lg"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                                <div wire:loading.remove>
                                    <i class="bi bi-{{ $isEdit ? 'check-lg' : 'plus-lg' }}"></i>
                                    {{ $isEdit ? 'Perbarui' : 'Simpan' }}
                                </div>
                                <div wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Menyimpan...
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading Overlay --}}
    <div wire:loading.delay class="position-fixed top-0 start-0 w-100 h-100" style="background-color: rgba(255,255,255,0.8); z-index: 9998;">
        <div class="position-absolute top-50 start-50 translate-middle">
            <div class="text-center">
                <div class="spinner-border text-success mb-2" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Memproses data...</div>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Error Alert --}}
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Auto dismiss alerts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto dismiss alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</main>