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
                <h5 class="card-title">Kalkulator BEP Bulanan</h5>

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
                        <input id="selectedYear" type="number" class="form-control" wire:model.live="selectedYear" min="2000" max="2099" placeholder="Contoh: {{ now()->year }}">
                        <small class="text-muted">Ketik tahun (2000–2099)</small>
                    </div>
                    <div class="col-md-4"></div>

                    <div class="col-md-4 mt-2">
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
                    <div class="col-md-4">
                        <label class="form-label">Harga Jual/Unit</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" wire:model.live="calcSellingPrice" step="any" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Biaya Variabel (Bulan)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" wire:model.live="calcVariableCost" step="any" min="0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Biaya Tetap (Bulan)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" wire:model.live="calcFixedCost" step="any" min="0">
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-hover table-bordered">
                        <thead class="table-primary">
                            <tr class="text-center">
                                <th>Metik</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">Unit Terjual (Periode Terpilih)</td>
                                <td class="text-end">{{ number_format($this->calcUnitsSold) }} unit</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Perkiraan Penjualan (Bulan)</td>
                                <td class="text-end">Rp {{ number_format($this->calcMonthlySales, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Biaya Variabel (Bulan)</td>
                                <td class="text-end">Rp {{ number_format($calcVariableCost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Margin Kontribusi (Total)</td>
                                <td class="text-end">Rp {{ number_format($this->calcContributionMarginTotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Rasio Margin Kontribusi</td>
                                <td class="text-end">{{ number_format($this->calcContributionMarginRatio, 2, ',', '.') }}%</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Biaya Tetap (Bulan)</td>
                                <td class="text-end">Rp {{ number_format($calcFixedCost, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-secondary">
                                <td class="fw-bold">BEP (Pendapatan Minimum/Bulan)</td>
                                <td class="text-end fw-bold">Rp {{ number_format($this->calcBepRupiah, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">BEP (Unit)</td>
                                <td class="text-end">{{ number_format($this->calcBepUnits) }} unit</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Sisa ke BEP</td>
                                <td class="text-end {{ $this->calcUnitsRemaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($this->calcUnitsRemaining) }} unit</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

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
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>