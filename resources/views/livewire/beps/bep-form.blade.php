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
        <!-- Mode Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Pilih Mode Analisis</h5>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="mode" id="calculator" value="calculator" 
                           wire:model="mode" {{ $mode === 'calculator' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="calculator">
                        <i class="bi bi-calculator me-2"></i>Kalkulator BEP
                    </label>

                    <input type="radio" class="btn-check" name="mode" id="perProduct" value="perProduct" 
                           wire:model="mode" {{ $mode === 'perProduct' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="perProduct">
                        <i class="bi bi-box me-2"></i>BEP Per Produk
                    </label>

                    <input type="radio" class="btn-check" name="mode" id="perPeriod" value="perPeriod" 
                           wire:model="mode" {{ $mode === 'perPeriod' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="perPeriod">
                        <i class="bi bi-calendar-month me-2"></i>BEP Per Periode
                    </label>
                </div>
            </div>
        </div>

        @if($mode === 'calculator')
            <!-- Kalkulator BEP -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-calculator me-2"></i>Kalkulator BEP Bulanan
                    </h5>

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
                            <input id="selectedYear" type="number" class="form-control" wire:model.live="selectedYear" 
                                   min="2000" max="2099" placeholder="Contoh: {{ now()->year }}">
                            <small class="text-muted">Ketik tahun (2000–2099)</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Produk (opsional)</label>
                            <select class="form-select" wire:model.live="calcSelectedProduk">
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($produkList as $produk)
                                    <option value="{{ $produk }}">{{ $produk }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Unit terjual otomatis dari incomes pada periode terpilih.</small>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Harga Jual/Unit</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" wire:model.live="calcSellingPrice" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Biaya Variabel (Bulan)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" wire:model.live="calcVariableCost" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Biaya Tetap (Bulan)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" wire:model.live="calcFixedCost" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Target Profit (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" wire:model.live="targetProfit" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-hover table-bordered">
                            <thead class="table-primary">
                                <tr class="text-center">
                                    <th>Metrik</th>
                                    <th>Nilai</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold">Unit Terjual (Periode Terpilih)</td>
                                    <td class="text-end">{{ number_format($this->calcUnitsSold) }} unit</td>
                                    <td><small class="text-muted">Total unit terjual dari incomes</small></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Perkiraan Penjualan (Bulan)</td>
                                    <td class="text-end">Rp {{ number_format($this->calcMonthlySales, 0, ',', '.') }}</td>
                                    <td><small class="text-muted">Harga × Unit terjual</small></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Biaya Variabel (Bulan)</td>
                                    <td class="text-end">Rp {{ number_format($calcVariableCost, 0, ',', '.') }}</td>
                                    <td><small class="text-muted">Total pengeluaran bulan ini</small></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Margin Kontribusi (Total)</td>
                                    <td class="text-end">Rp {{ number_format($this->calcContributionMarginTotal, 0, ',', '.') }}</td>
                                    <td><small class="text-muted">Penjualan - Biaya Variabel</small></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Rasio Margin Kontribusi</td>
                                    <td class="text-end">{{ number_format($this->calcContributionMarginRatio, 2, ',', '.') }}%</td>
                                    <td><small class="text-muted">(Margin / Penjualan) × 100%</small></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Biaya Tetap (Bulan)</td>
                                    <td class="text-end">Rp {{ number_format($calcFixedCost, 0, ',', '.') }}</td>
                                    <td><small class="text-muted">Total biaya tetap bulan ini</small></td>
                                </tr>
                                <tr class="table-warning">
                                    <td class="fw-bold">BEP (Pendapatan Minimum/Bulan)</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($this->calcBepRupiah, 0, ',', '.') }}</td>
                                    <td><small class="text-muted">Biaya Tetap ÷ Rasio Margin</small></td>
                                </tr>
                                <tr class="table-warning">
                                    <td class="fw-bold">BEP (Unit)</td>
                                    <td class="text-end fw-bold">{{ number_format($this->calcBepUnits) }} unit</td>
                                    <td><small class="text-muted">BEP (Rp) ÷ Harga per unit</small></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Sisa ke BEP</td>
                                    <td class="text-end {{ $this->calcUnitsRemaining > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($this->calcUnitsRemaining) }} unit
                                    </td>
                                    <td><small class="text-muted">BEP Unit - Unit terjual</small></td>
                                </tr>
                                <tr class="table-info">
                                    <td class="fw-bold">Margin of Safety</td>
                                    <td class="text-end fw-bold">{{ number_format($this->calcMarginOfSafety, 2, ',', '.') }}%</td>
                                    <td><small class="text-muted">(Penjualan - BEP) ÷ Penjualan × 100%</small></td>
                                </tr>
                                @if($targetProfit > 0)
                                    <tr class="table-success">
                                        <td class="fw-bold">BEP + Target Profit</td>
                                        <td class="text-end fw-bold">
                                            Rp {{ number_format($this->calculateTargetProfitBep(), 0, ',', '.') }}
                                        </td>
                                        <td><small class="text-muted">(Biaya Tetap + Target Profit) ÷ Rasio Margin</small></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Advanced Analysis Toggle -->
                    <div class="mt-3">
                        <button wire:click="toggleAdvancedAnalysis" class="btn btn-outline-info">
                            <i class="bi bi-graph-up me-2"></i>
                            {{ $showAdvancedAnalysis ? 'Sembunyikan' : 'Tampilkan' }} Analisis Lanjutan
                        </button>
                    </div>

                    @if($showAdvancedAnalysis)
                        <!-- Sensitivity Analysis -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i class="bi bi-activity me-2"></i>Analisis Sensitivitas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Perubahan Biaya Tetap</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Perubahan</th>
                                                        <th>Biaya Tetap</th>
                                                        <th>BEP Baru</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($sensitivityAnalysis['fixed_cost']))
                                                        @foreach($sensitivityAnalysis['fixed_cost'] as $change => $data)
                                                            <tr>
                                                                <td>{{ $change >= 0 ? '+' : '' }}{{ $change }}%</td>
                                                                <td>Rp {{ number_format($data['fixed_cost'], 0, ',', '.') }}</td>
                                                                <td>Rp {{ number_format($data['bep'], 0, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Perubahan Margin Kontribusi</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Perubahan</th>
                                                        <th>Margin</th>
                                                        <th>BEP Baru</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($sensitivityAnalysis['contribution_margin']))
                                                        @foreach($sensitivityAnalysis['contribution_margin'] as $change => $data)
                                                            <tr>
                                                                <td>{{ $change >= 0 ? '+' : '' }}{{ $change }}%</td>
                                                                <td>{{ number_format($data['margin'], 2, ',', '.') }}%</td>
                                                                <td>Rp {{ number_format($data['bep'], 0, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-info-circle"></i> Penjelasan Tabel</h6>
                            <ul class="mb-0">
                                <li><strong>Unit Terjual (Periode Terpilih)</strong>: total kuantitas terjual dari tabel pendapatan (incomes) pada bulan dan tahun yang dipilih.</li>
                                <li><strong>Perkiraan Penjualan (Bulan)</strong>: estimasi total pendapatan bulan ini = harga jual per unit × unit terjual.</li>
                                <li><strong>Biaya Variabel (Bulan)</strong>: total pengeluaran bulan ini (diambil dari pengeluaran/Expenditure pada periode yang sama).</li>
                                <li><strong>Margin Kontribusi (Total)</strong>: selisih antara penjualan dan biaya variabel bulan ini.</li>
                                <li><strong>Rasio Margin Kontribusi</strong>: margin kontribusi total dibagi penjualan bulan ini, dinyatakan dalam persen.</li>
                                <li><strong>Biaya Tetap (Bulan)</strong>: total biaya tetap bulan ini (diambil dari FixedCost pada periode yang sama).</li>
                                <li><strong>BEP (Pendapatan Minimum/Bulan)</strong>: titik impas dalam rupiah = biaya tetap ÷ rasio margin kontribusi.</li>
                                <li><strong>BEP (Unit)</strong>: estimasi jumlah unit yang harus dijual untuk mencapai BEP = BEP (Rp) ÷ harga jual per unit.</li>
                                <li><strong>Sisa ke BEP</strong>: selisih antara BEP (unit) dengan unit yang sudah terjual pada periode terpilih.</li>
                                <li><strong>Margin of Safety</strong>: persentase keamanan dari BEP, menunjukkan seberapa jauh penjualan saat ini dari titik impas.</li>
                                <li><strong>BEP + Target Profit</strong>: pendapatan yang diperlukan untuk mencapai target profit tertentu.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($mode === 'perProduct')
            <!-- BEP Per Produk -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">
                            <i class="bi bi-box me-2"></i>BEP Per Produk
                        </h5>
                        <button wire:click="openModal" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Tambah BEP
                        </button>
                    </div>

                    @if(session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga Jual</th>
                                    <th>Biaya Variabel</th>
                                    <th>Biaya Tetap</th>
                                    <th>Margin Kontribusi</th>
                                    <th>BEP (Unit)</th>
                                    <th>BEP (Rp)</th>
                                    <th>Unit Terjual</th>
                                    <th>Sisa ke BEP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($beploadbep as $bep)
                                    <tr>
                                        <td>{{ $bep->nama_produk }}</td>
                                        <td>Rp {{ number_format($bep->harga_per_barang, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($bep->modal_per_barang, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($bep->modal_tetap, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($bep->harga_per_barang - $bep->modal_per_barang, 0, ',', '.') }}</td>
                                        <td>{{ number_format(ceil($bep->modal_tetap / ($bep->harga_per_barang - $bep->modal_per_barang))) }}</td>
                                        <td>Rp {{ number_format(ceil($bep->modal_tetap / ($bep->harga_per_barang - $bep->modal_per_barang)) * $bep->harga_per_barang, 0, ',', '.') }}</td>
                                        <td>{{ number_format($this->getUnitsSoldForProduct($bep->nama_produk)) }}</td>
                                        <td>
                                            @php
                                                $bepUnits = ceil($bep->modal_tetap / ($bep->harga_per_barang - $bep->modal_per_barang));
                                                $unitsSold = $this->getUnitsSoldForProduct($bep->nama_produk);
                                                $remaining = $bepUnits - $unitsSold;
                                            @endphp
                                            <span class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($remaining > 0 ? $remaining : 0) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button wire:click="edit({{ $bep->id }})" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:click="delete({{ $bep->id }})" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox fs-1"></i>
                                            <p class="mb-0">Belum ada data BEP</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($mode === 'perPeriod')
            <!-- BEP Per Periode -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-calendar-month me-2"></i>BEP Per Periode
                    </h5>

                    <div class="row g-3 align-items-end bg-light p-3 rounded mb-3">
                        <div class="col-md-4">
                            <label for="periodMonth" class="form-label">Bulan</label>
                            <select id="periodMonth" wire:model="selectedMonth" class="form-select">
                                @foreach($this->monthNames as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}">{{ $monthName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="periodYear" class="form-label">Tahun</label>
                            <input id="periodYear" type="number" class="form-control" wire:model.live="selectedYear" 
                                   min="2000" max="2099" placeholder="Contoh: {{ now()->year }}">
                        </div>
                        <div class="col-md-4">
                            <button wire:click="calculatePeriodBep" class="btn btn-primary w-100">
                                <i class="bi bi-calculator me-2"></i>Hitung BEP
                            </button>
                        </div>
                    </div>

                    @if($calculationError)
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ $calculationError }}
                        </div>
                    @endif

                    @if($periodDataLoaded)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Data Periode</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Biaya Tetap</small>
                                                <div class="h5 text-primary">Rp {{ number_format($totalFixedCost, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Total Penjualan</small>
                                                <div class="h5 text-success">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6">
                                                <small class="text-muted">Biaya Variabel</small>
                                                <div class="h5 text-danger">Rp {{ number_format($totalVariableCost, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Margin Kontribusi</small>
                                                <div class="h5 text-info">{{ number_format($contributionMarginRatio, 2, ',', '.') }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">Hasil Analisis BEP</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">BEP (Rp)</small>
                                                <div class="h5 text-warning">Rp {{ number_format($bepRupiahPeriod, 0, ',', '.') }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Margin of Safety</small>
                                                <div class="h5 text-{{ $marginOfSafety > 0 ? 'success' : 'danger' }}">
                                                    {{ number_format($marginOfSafety, 2, ',', '.') }}%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Status:</small>
                                            <div class="badge {{ $totalSales > $bepRupiahPeriod ? 'bg-success' : 'bg-danger' }} fs-6">
                                                {{ $totalSales > $bepRupiahPeriod ? 'DI ATAS BEP' : 'DI BAWAH BEP' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <!-- Modal untuk BEP Per Produk -->
    @if($showModal)
        <div class="modal fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEdit ? 'Edit' : 'Tambah' }} Data BEP</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="selectedProduk" class="form-label">Produk</label>
                                <select id="selectedProduk" wire:model="selectedProduk" class="form-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produkList as $produk)
                                        <option value="{{ $produk }}">{{ $produk }}</option>
                                    @endforeach
                                </select>
                                @error('selectedProduk') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="avgSellingPrice" class="form-label">Harga Jual Rata-rata</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="avgSellingPrice" class="form-control" 
                                           wire:model="avgSellingPrice" step="any" min="0" required>
                                </div>
                                @error('avgSellingPrice') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="modal_per_barang" class="form-label">Biaya Variabel per Unit</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="modal_per_barang" class="form-control" 
                                           wire:model="modal_per_barang" step="any" min="0" required>
                                </div>
                                @error('modal_per_barang') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="totalFixedCost" class="form-label">Biaya Tetap (Bulan)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="totalFixedCost" class="form-control" 
                                           wire:model="totalFixedCost" step="any" min="0" required>
                                </div>
                                @error('totalFixedCost') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            @if($selectedProduk && $avgSellingPrice > 0 && $modal_per_barang > 0)
                                <div class="alert alert-info">
                                    <h6>Preview Perhitungan:</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <small>Margin Kontribusi:</small>
                                            <div class="fw-bold">Rp {{ number_format($this->contributionMargin, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small>BEP (Unit):</small>
                                            <div class="fw-bold">{{ number_format($this->bepUnit) }}</div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-6">
                                            <small>BEP (Rp):</small>
                                            <div class="fw-bold">Rp {{ number_format($this->bepRupiah, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small>Unit Terjual:</small>
                                            <div class="fw-bold">{{ number_format($this->unitsSoldInPeriod) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $isEdit ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</main>