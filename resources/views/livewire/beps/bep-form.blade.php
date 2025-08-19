<main id="main" class="main">
    <div class="pagetitle">
        <h1>Analisis Titik Impas atau BEP (Break-Even Point)</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Analisis BEP</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pilih Mode Analisis BEP</h5>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="bepTabs" role="tablist">
                    <li class="nav-item flex-fill" role="presentation">
                        <button class="nav-link w-100 {{ $mode == 'perProduct' ? 'active' : '' }}" wire:click="switchMode('perProduct')" type="button" role="tab">BEP Per Produk</button>
                    </li>
                    <li class="nav-item flex-fill" role="presentation">
                        <button class="nav-link w-100 {{ $mode == 'perPeriod' ? 'active' : '' }}" wire:click="switchMode('perPeriod')" type="button" role="tab">BEP Per Periode (Bulanan)</button>
                    </li>
                </ul>

                <div class="tab-content pt-2">
                    {{-- =================================================================== --}}
                    {{-- ======================= TAB 1: BEP PER PRODUK ======================= --}}
                    {{-- =================================================================== --}}
                    <div class="tab-pane fade {{ $mode == 'perProduct' ? 'show active' : '' }}" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center pt-4">
                            <h5 class="card-title">Data Perhitungan BEP Tersimpan</h5>
                            <button class="btn btn-primary" wire:click="openModal">
                                <i class="bi bi-plus-circle"></i> Hitung BEP Baru
                            </button>
                        </div>

                        @if (count($produkList) == 0)
                            <div class="alert alert-warning mt-3">
                                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Data Produk Tidak Ditemukan</h5>
                                <p>Untuk menggunakan fitur "BEP Per Produk", Anda harus memiliki data pendapatan yang mencantumkan nama produk/jasa.</p>
                                <hr>
                                <p class="mb-0">
                                    Silakan buka halaman <a href="{{ route('incomes') }}" class="alert-link">Pendapatan</a>, lalu edit transaksi Anda atau buat transaksi baru dan pastikan untuk mengisi kolom <strong>"Produk/Jasa"</strong>.
                                </p>
                            </div>
                        @endif

                        @if (count($beploadbep) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Produk</th>
                                            <th>Biaya Tetap</th>
                                            <th>Harga Jual Rata-Rata</th>
                                            <th>Biaya Variabel/Unit</th>
                                            <th>BEP (Unit)</th>
                                            <th>BEP (Rupiah)</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($beploadbep as $index => $bep)
                                            @php
                                                $keuntunganPerUnit = $bep->harga_per_barang - $bep->modal_per_barang;
                                                $bepUnit = $keuntunganPerUnit > 0 ? ceil($bep->modal_tetap / $keuntunganPerUnit) : 0;
                                                $bepRupiah = $bepUnit > 0 ? $bepUnit * $bep->harga_per_barang : 0;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="fw-bold">{{ $bep->nama_produk }}</td>
                                                <td class="text-end">Rp {{ number_format($bep->modal_tetap, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($bep->harga_per_barang, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($bep->modal_per_barang, 0, ',', '.') }}</td>
                                                <td class="text-center"><span class="badge bg-success fs-6">{{ number_format($bepUnit) }} u</span></td>
                                                <td class="text-end"><span class="fw-bold">Rp {{ number_format($bepRupiah, 0, ',', '.') }}</span></td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-warning" wire:click="edit({{ $bep->id }})"><i class="bi bi-pencil"></i></button>
                                                    <button class="btn btn-sm btn-danger" wire:click="delete({{ $bep->id }})" wire:confirm="Yakin hapus? {{ $bep->nama_produk }}"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-secondary mt-4 text-center">
                                <i class="bi bi-calculator me-2"></i>
                                Belum ada data perhitungan BEP per produk yang tersimpan.
                            </div>
                        @endif
                    </div>

                    {{-- =================================================================== --}}
                    {{-- ==================== TAB 2: BEP PER PERIODE ======================= --}}
                    {{-- =================================================================== --}}
                    <div class="tab-pane fade {{ $mode == 'perPeriod' ? 'show active' : '' }}" role="tabpanel">
                        <div class="pt-4">
                            <h5 class="card-title">Analisis Titik Impas Bulanan</h5>
                            <p>Pilih periode untuk menghitung BEP berdasarkan data keuangan pada bulan tersebut.</p>
                            
                            <!-- Filter Periode -->
                            <div class="row g-3 align-items-end bg-light p-3 rounded">
                                <div class="col-md-4">
                                    <label for="selectedMonth" class="form-label">Bulan</label>
                                    <select id="selectedMonth" wire:model="selectedMonth" class="form-select">
                                        @foreach($this->monthNames as $monthNum => $monthName)
                                            <option value="{{ $monthNum }}">{{ $monthName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="selectedYear" class="form-label">Tahun</label>
                                    <select id="selectedYear" wire:model="selectedYear" class="form-select">
                                        @foreach($this->availableYears as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100" wire:click="calculatePeriodBep">
                                        <span wire:loading.remove wire:target="calculatePeriodBep"><i class="bi bi-calculator-fill"></i> Hitung BEP</span>
                                        <span wire:loading wire:target="calculatePeriodBep">
                                            <span class="spinner-border spinner-border-sm"></span> Menghitung...
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div wire:loading.delay.long wire:target="calculatePeriodBep" class="text-center my-3">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Menganalisis data periode terpilih...</p>
                            </div>

                            <div wire:loading.remove wire:target="calculatePeriodBep">
                                @if($periodDataLoaded)
                                    @if($calculationError)
                                        <div class="alert alert-danger mt-4">
                                            <h5 class="alert-heading"><i class="bi bi-x-octagon-fill"></i> Gagal Menghitung BEP</h5>
                                            <p>{{ $calculationError }}</p>
                                        </div>
                                    @else
                                        <div class="row mt-4">
                                            <div class="col-md-4">
                                                <div class="card card-body shadow-sm">
                                                    <small class="text-muted">Biaya Tetap (Bulan Ini)</small>
                                                    <h4 class="text-danger">Rp {{ number_format($totalFixedCost, 0, ',', '.') }}</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-body shadow-sm">
                                                    <small class="text-muted">Biaya Variabel (Bulan Ini)</small>
                                                    <h4 class="text-warning">Rp {{ number_format($totalVariableCost, 0, ',', '.') }}</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-body shadow-sm">
                                                    <small class="text-muted">Penjualan (Bulan Ini)</small>
                                                    <h4 class="text-success">Rp {{ number_format($totalSales, 0, ',', '.') }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card border-primary mt-3">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0"><i class="bi bi-clipboard2-data-fill"></i> Hasil Analisis BEP untuk {{ $this->monthNames[$selectedMonth] }} {{ $selectedYear }}</h5>
                                            </div>
                                            <div class="card-body p-4">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">Rasio Margin Kontribusi<span class="badge bg-info fs-6">{{ number_format($contributionMarginRatio, 2, ',', '.') }} %</span></li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong class="text-primary">Pendapatan Minimum (BEP)</strong><span class="badge bg-primary fs-5">Rp {{ number_format($bepRupiahPeriod, 0, ',', '.') }}</span></li>
                                                </ul>
                                                <div class="alert alert-success mt-4 mb-0">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    <strong>Kesimpulan:</strong> Anda perlu mencapai total pendapatan <strong>Rp {{ number_format($bepRupiahPeriod, 0, ',', '.') }}</strong> pada bulan {{ $this->monthNames[$selectedMonth] }} {{ $selectedYear }} untuk menutupi semua biaya di bulan tersebut.
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info mt-4 text-center">
                                        <i class="bi bi-arrow-up-circle-fill"></i>
                                        <h6 class="mt-2">Pilih periode dan klik tombol "Hitung BEP" untuk memulai analisis.</h6>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal untuk BEP Per Produk --}}
        @if ($showModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-calculator"></i> {{ $isEdit ? 'Edit' : 'Hitung' }} BEP Per Produk</h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Langkah 1: Pilih Produk</label>
                                <select class="form-select @error('selectedProduk') is-invalid @enderror" wire:model.live="selectedProduk">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($produkList as $produk)
                                        <option value="{{ $produk }}">{{ $produk }}</option>
                                    @endforeach
                                </select>
                                @error('selectedProduk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            @if($selectedProduk)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Langkah 2: Verifikasi Data Otomatis</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Total Biaya Tetap</label>
                                        <div class="input-group"><span class="input-group-text">Rp</span><input type="text" class="form-control bg-light" wire:model="totalFixedCost" readonly></div>
                                        <div class="form-text">Ini adalah total biaya tetap keseluruhan.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Rata-rata Harga Jual/Unit</label>
                                        <div class="input-group"><span class="input-group-text">Rp</span><input type="text" class="form-control bg-light" wire:model="avgSellingPrice" readonly></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="modal_per_barang" class="form-label fw-bold">Langkah 3: Masukkan Biaya Variabel per Unit</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input id="modal_per_barang" type="number" class="form-control @error('modal_per_barang') is-invalid @enderror" wire:model.live="modal_per_barang" min="0" placeholder="Contoh: 5000" step="any">
                                    @error('modal_per_barang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @endif

                            @if ($this->contributionMargin > 0 && $this->bepUnit > 0)
                                <div class="alert alert-success mt-4">
                                    <h6 class="alert-heading"><i class="bi bi-check-circle"></i> Hasil Analisis</h6>
                                    Titik Impas (BEP) tercapai jika Anda menjual <strong>{{ number_format($this->bepUnit) }} unit</strong> senilai <strong>Rp {{ number_format($this->bepRupiah, 0, ',', '.') }}</strong>.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal"><i class="bi bi-x-lg"></i> Batal</button>
                            <button type="submit" class="btn btn-primary" @if(!$this->bepUnit > 0) disabled @endif wire:loading.attr="disabled">
                                <i class="bi bi-save"></i> {{ $isEdit ? 'Perbarui' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    </section>
</main>