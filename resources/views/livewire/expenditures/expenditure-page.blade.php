<main id="main" class="main">
    <div class="pagetitle">
        <h1>Pengeluaran</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengeluaran</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-cash-coin"></i> Kelola Data Pengeluaran
                </h5>

                {{-- Filter Bulan dan Tahun --}}
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="filterMonth" class="form-label">Pilih Bulan</label>
                        <select wire:model.live="filterMonth"
                            class="form-select @error('filterMonth') is-invalid @enderror">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach ($monthNames as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterYear" class="form-label">Pilih Tahun</label>

                        <input list="yearOptions" type="number" wire:model.live="filterYear"
                            class="form-control @error('filterYear') is-invalid @enderror"
                            placeholder="Masukkan atau pilih tahun" min="1900" max="{{ date('Y') + 10 }}">

                        <datalist id="yearOptions">
                            @foreach ($this->getAvailableYears() as $year)
                                <option value="{{ $year }}"></option>
                            @endforeach
                        </datalist>

                        @error('filterYear')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                @if (count($monthlyTotals) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-graph-up"></i> Ringkasan Pengeluaran
                                        @if ($filterYear)
                                            <small class="text-muted">(Tahun {{ $filterYear }})</small>
                                        @else
                                            <small class="text-muted">(6 Bulan Terakhir)</small>
                                        @endif
                                    </h6>
                                    <div class="row">
                                        @php
                                            $displayedCount = 0;
                                            $maxDisplay = 6;
                                        @endphp
                                        @foreach (array_slice($monthlyTotals, 0, 6) as $period => $total)
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
                                                <div
                                                    class="card {{ $isSelected ? 'border-danger bg-danger text-white shadow' : 'border-light' }} h-100">
                                                    <div class="card-body text-center p-3">
                                                        <div class="mb-2">
                                                            <small
                                                                class="{{ $isSelected ? 'text-white-50' : 'text-muted' }}">
                                                                {{ $monthName }} {{ $year }}
                                                            </small>
                                                        </div>
                                                        <div class="fw-bold">
                                                            @if ($total >= 1000000)
                                                                <span class="fs-6">Rp
                                                                    {{ number_format($total / 1000000, 1, ',', '.') }}Jt</span>
                                                            @elseif ($total >= 1000)
                                                                <span class="fs-6">Rp
                                                                    {{ number_format($total / 1000, 0, ',', '.') }}Rb</span>
                                                            @else
                                                                <span class="small">Rp
                                                                    {{ number_format($total, 0, ',', '.') }}</span>
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
                                                            <strong><i class="bi bi-info-circle"></i> Total Keseluruhan Pengeluaran
                                                                @if ($filterYear)
                                                                    Tahun {{ $filterYear }}:
                                                                @else
                                                                    :
                                                                @endif
                                                            </strong>
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
                        <i class="bi bi-exclamation-triangle"></i> Data ringkasan pengeluaran belum tersedia atau kosong.
                    </div>
                @endif

                {{-- Jika bulan & tahun sudah dipilih --}}
                @if ($filterMonth && $filterYear)
                    <div class="pt-4">
                        <div class="row gy-2 gx-2 align-items-center">
                            <div class="col-lg-6">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-table"></i> Data Pengeluaran - {{ $this->monthName }} {{ $filterYear }}
                                    @if ($total > 0)
                                        <span class="badge bg-danger ms-2">
                                            Total: Rp {{ number_format($total, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </h5>
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center justify-content-lg-end gap-2">
                                    <div class="d-flex align-items-center">
                                        <label class="form-label mb-0 me-2 d-none d-md-block">Tampilkan:</label>
                                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-primary w-100 w-md-auto" wire:click="openModal">
                                        <i class="bi bi-plus-circle"></i> Tambah Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($paginatedExpenditures->count() > 0)
                        <div class="table-responsive mt-3">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="40%">Keterangan</th>
                                        <th width="20%">Jumlah</th>
                                        <th width="20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($paginatedExpenditures as $expenditure)
                                        <tr>
                                            <td>{{ ($paginatedExpenditures->currentPage() - 1) * $paginatedExpenditures->perPage() + $loop->iteration }}
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ \Carbon\Carbon::parse($expenditure->tanggal)->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;"
                                                    title="{{ $expenditure->keterangan }}">
                                                    {{ $expenditure->keterangan }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger fs-6">
                                                    Rp {{ number_format($expenditure->jumlah, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-warning"
                                                        wire:click="edit({{ $expenditure->id }})" title="Edit Data">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        wire:click="confirmDelete({{ $expenditure->id }})"
                                                        wire:confirm="Apakah Anda yakin ingin menghapus pengeluaran '{{ Str::limit($expenditure->keterangan, 30) }}'?"
                                                        title="Hapus Data">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th colspan="3" class="text-end">Total Pengeluaran {{ $this->monthName }}
                                            {{ $filterYear }}:</th>
                                        <th>
                                            <span class="badge bg-danger fs-6">
                                                Rp {{ number_format($total, 0, ',', '.') }}
                                            </span>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Pagination dengan informasi data --}}
                        <div class="d-sm-flex justify-content-sm-between align-items-sm-center mt-3">
                            <div class="text-muted text-center text-sm-start mb-2 mb-sm-0">
                                <small>
                                    Menampilkan {{ $paginatedExpenditures->firstItem() ?? 0 }}
                                    sampai {{ $paginatedExpenditures->lastItem() ?? 0 }}
                                    dari {{ $paginatedExpenditures->total() }} data
                                </small>
                            </div>

                            {{-- Custom Pagination Links --}}
                            @if ($paginatedExpenditures->hasPages())
                                <nav aria-label="Pagination Navigation" class="d-flex justify-content-center">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($paginatedExpenditures->onFirstPage())
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
                                        @foreach ($paginatedExpenditures->getUrlRange(1, $paginatedExpenditures->lastPage()) as $page => $url)
                                            @if ($page == $paginatedExpenditures->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <button class="page-link"
                                                        wire:click="gotoPage({{ $page }})">
                                                        {{ $page }}
                                                    </button>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($paginatedExpenditures->hasMorePages())
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
                                        Belum ada data pengeluaran untuk bulan <strong>{{ $this->monthName }}
                                            {{ $filterYear }}</strong>.
                                        <br>Klik tombol "Tambah Data" untuk menambahkan data pengeluaran baru.
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
                                    <strong>Silakan pilih bulan dan tahun terlebih dahulu</strong> untuk melihat dan
                                    menambahkan
                                    data pengeluaran.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Info Tambahan --}}
                <div class="alert alert-info mt-3">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-lightbulb fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-2"><strong>Informasi mengenai pengeluaran:</strong></h6>
                            <ul class="mb-0">
                                <li>Jangan tambahkan pengeluaran yang berasal dari modal tetap</li>
                                <li>Hanya untuk pengeluaran variabel (biaya operasional, pemasaran, dll.)</li>
                                <li>Edit atau hapus data jika ingin mengganti data jika ada kesalahan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-{{ $isEdit ? 'pencil' : 'plus-circle' }}"></i>
                                {{ $isEdit ? 'Edit' : 'Tambah' }} Pengeluaran - {{ $this->monthName }}
                                {{ $filterYear }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white"
                                wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror"
                                            wire:model.live="tanggal" min="{{ $this->minDate }}"
                                            max="{{ $this->maxDate }}">
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
                                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-danger text-white">Rp</span>
                                            <input type="number"
                                                class="form-control @error('jumlah') is-invalid @enderror"
                                                wire:model.live="jumlah" min="0" step="0.01"
                                                placeholder="0">
                                            @error('jumlah')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        @if ($jumlah)
                                            <div class="form-text text-success">
                                                <strong>Jumlah: Rp {{ number_format($jumlah, 0, ',', '.') }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" wire:model.live="keterangan" rows="4"
                                    placeholder="Masukkan keterangan pengeluaran (misal: Pembelian bahan baku, Biaya transportasi, dll)"
                                    maxlength="255"></textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <span id="charCount">{{ strlen($keterangan ?? '') }}</span>/255 karakter
                                </div>
                            </div>

                            @if ($tanggal && $keterangan && $jumlah)
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-eye"></i> Preview Data:</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Tanggal:</strong><br>
                                                <span class="badge bg-secondary">
                                                    {{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Jumlah:</strong><br>
                                                <span class="badge bg-danger">
                                                    Rp {{ number_format($jumlah, 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Keterangan:</strong><br>
                                                <span class="text-muted">{{ Str::limit($keterangan, 30) }}</span>
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
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
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
    <div wire:loading.delay class="position-fixed top-0 start-0 w-100 h-100"
        style="background-color: rgba(255,255,255,0.8); z-index: 9998;">
        <div class="position-absolute top-50 start-50 translate-middle">
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Memproses data...</div>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Error Alert --}}
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Auto dismiss alerts and update character count --}}
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

            // Update character count for textarea
            document.addEventListener('input', function(e) {
                if (e.target.matches('textarea[wire\\:model\\.live="keterangan"]')) {
                    const charCount = document.getElementById('charCount');
                    if (charCount) {
                        charCount.textContent = e.target.value.length;
                    }
                }
            });
        });
    </script>
</main>