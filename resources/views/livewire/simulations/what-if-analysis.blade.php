<main id="main" class="main">
    <div class="pagetitle">
        <h1>Simulasi "Apa Jika?" (What-If Analysis)</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Simulasi "Apa Jika?"</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Simulasi Dampak Perubahan Variabel Bisnis</h5>

                <!-- Filter Periode -->
                <div class="row g-3 align-items-end bg-light p-3 rounded mb-4">
                    <div class="col-md-6">
                        <label for="selectedMonth" class="form-label">Bulan</label>
                        <select id="selectedMonth" wire:model.live="selectedMonth" class="form-select">
                            @foreach($monthNames as $monthNum => $monthName)
                                <option value="{{ $monthNum }}">{{ $monthName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="selectedYear" class="form-label">Tahun</label>
                        <input id="selectedYear" type="number" class="form-control" wire:model.live="selectedYear" min="2000" max="2099" placeholder="Contoh: {{ now()->year }}">
                    </div>
                </div>

                <!-- Data Awal Bisnis Keseluruhan -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Data Awal Bisnis Keseluruhan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Pendapatan Total</th>
                                        <td class="text-end">Rp {{ number_format((float)$pendapatanTotal, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Biaya Variabel Total</th>
                                        <td class="text-end">Rp {{ number_format((float)$biayaVariabelTotal, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Biaya Tetap (Bulanan)</th>
                                        <td class="text-end">Rp {{ number_format((float)$biayaTetapTotal, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Laba Awal</th>
                                        <td class="text-end {{ (float)$labaAwal < 0 ? 'text-danger' : 'text-success' }}">
                                            Rp {{ number_format((float)$labaAwal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>BEP (Rupiah)</th>
                                        <td class="text-end">Rp {{ number_format((float)$bepRupiahAwal, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simulasi 1: Perubahan Harga -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Simulasi 1: Jika Harga Berubah</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Produk (Opsional)</label>
                                <select class="form-select" wire:model.live="selectedProduk">
                                    <option value="">-- Analisis Bisnis Keseluruhan --</option>
                                    @foreach ($produkList as $produk)
                                        <option value="{{ $produk }}">{{ $produk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    @if(!empty($selectedProduk))
                                        Perubahan Harga per Unit (%)
                                    @else
                                        Perubahan Pendapatan Total (%)
                                    @endif
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model.live="simulasiHarga" step="1">
                                    <span class="input-group-text">%</span>
                                    <button class="btn btn-primary" wire:click="simulasiPerubahanHarga">Simulasikan</button>
                                </div>
                                <small class="text-muted">
                                    @if(!empty($selectedProduk))
                                        Nilai positif untuk kenaikan, negatif untuk penurunan harga per unit produk.
                                    @else
                                        Nilai positif untuk kenaikan, negatif untuk penurunan pendapatan total.
                                    @endif
                                </small>
                            </div>
                        </div>

                        @if(!empty($selectedProduk))
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Data Awal Produk: {{ $selectedProduk }}</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm">
                                        <tr>
                                            <th>Harga Jual per Unit</th>
                                            <td class="text-end">Rp {{ number_format((float)$hargaJualAwalProduk, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Biaya Variabel per Unit</th>
                                            <td class="text-end">Rp {{ number_format((float)$biayaVariabelAwalProduk, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Unit Terjual</th>
                                            <td class="text-end">{{ number_format((float)$unitTerjualAwalProduk, 0, ',', '.') }} unit</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Metrik</th>
                                        <th class="text-center">Nilai Awal</th>
                                        <th class="text-center">Nilai Baru</th>
                                        <th class="text-center">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Laba</td>
                                        <td class="text-end">Rp {{ number_format((float)$labaAwal, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format((float)$labaBaru, 0, ',', '.') }}</td>
                                        <td class="text-end {{ (float)$persentasePerubahanLaba >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format((float)$persentasePerubahanLaba, 2, ',', '.') }}%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            @if(!empty($selectedProduk))
                                                Harga Jual Baru
                                            @else
                                                Pendapatan Total Baru
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!empty($selectedProduk))
                                                Rp {{ number_format((float)$hargaJualAwalProduk, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format((float)$pendapatanTotal, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="text-end">Rp {{ number_format((float)$hargaJualBaru, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            @if(!empty($selectedProduk))
                                                @if((float)$hargaJualAwalProduk > 0)
                                                    {{ number_format(((float)$hargaJualBaru - (float)$hargaJualAwalProduk) / (float)$hargaJualAwalProduk * 100, 2, ',', '.') }}%
                                                @else
                                                    -
                                                @endif
                                            @else
                                                @if((float)$pendapatanTotal > 0)
                                                    {{ number_format(((float)$hargaJualBaru - (float)$pendapatanTotal) / (float)$pendapatanTotal * 100, 2, ',', '.') }}%
                                                @else
                                                    -
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            @if(!empty($selectedProduk))
                                                BEP (Unit)
                                            @else
                                                BEP (Rupiah)
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!empty($selectedProduk))
                                                {{ number_format((float)$bepUnitAwalProduk, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format((float)$bepRupiahAwal, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!empty($selectedProduk))
                                                {{ number_format((float)$bepUnitBaru, 0, ',', '.') }}
                                            @else
                                                Rp {{ number_format((float)$bepRupiahBaru, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(!empty($selectedProduk))
                                                @if((float)$bepUnitAwalProduk > 0)
                                                    {{ number_format(((float)$bepUnitBaru - (float)$bepUnitAwalProduk) / (float)$bepUnitAwalProduk * 100, 2, ',', '.') }}%
                                                @else
                                                    -
                                                @endif
                                            @else
                                                @if((float)$bepRupiahAwal > 0)
                                                    {{ number_format(((float)$bepRupiahBaru - (float)$bepRupiahAwal) / (float)$bepRupiahAwal * 100, 2, ',', '.') }}%
                                                @else
                                                    -
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Dampak pada Laba dan BEP:</strong>
                            @php
                                $persentasePerubahanBep = 0;
                                if (!empty($selectedProduk)) {
                                    if ((float)$bepUnitAwalProduk != 0) {
                                        $persentasePerubahanBep = (((float)$bepUnitBaru - (float)$bepUnitAwalProduk) / abs((float)$bepUnitAwalProduk)) * 100;
                                    } else {
                                        $persentasePerubahanBep = (float)$bepUnitBaru > 0 ? 100 : 0;
                                    }
                                } else {
                                    if ((float)$bepRupiahAwal != 0) {
                                        $persentasePerubahanBep = (((float)$bepRupiahBaru - (float)$bepRupiahAwal) / abs((float)$bepRupiahAwal)) * 100;
                                    } else {
                                        $persentasePerubahanBep = (float)$bepRupiahBaru > 0 ? 100 : 0;
                                    }
                                }
                            @endphp
                            @if(!empty($selectedProduk))
                                @if((float)$persentasePerubahanLaba > 0)
                                    Jika harga produk {{ $selectedProduk }} berubah sebesar {{ number_format((float)$simulasiHarga, 2, ',', '.') }}%, laba akan <strong>meningkat</strong> sebesar {{ number_format((float)$persentasePerubahanLaba, 2, ',', '.') }}%
                                    dan BEP (Unit) akan {{ (float)$persentasePerubahanBep >= 0 ? 'meningkat' : 'menurun' }} sebesar {{ number_format(abs((float)$persentasePerubahanBep), 2, ',', '.') }}%.<br>
                                @elseif((float)$persentasePerubahanLaba < 0)
                                    Jika harga produk {{ $selectedProduk }} berubah sebesar {{ number_format((float)$simulasiHarga, 2, ',', '.') }}%, laba akan <strong>menurun</strong> sebesar {{ number_format(abs((float)$persentasePerubahanLaba), 2, ',', '.') }}%
                                    dan BEP (Unit) akan {{ (float)$persentasePerubahanBep >= 0 ? 'meningkat' : 'menurun' }} sebesar {{ number_format(abs((float)$persentasePerubahanBep), 2, ',', '.') }}%.<br>
                                @else
                                    Perubahan harga produk {{ $selectedProduk }} sebesar {{ number_format((float)$simulasiHarga, 2, ',', '.') }}% tidak akan mengubah laba secara signifikan.
                                @endif
                            @else
                                @if((float)$persentasePerubahanLaba > 0)
                                    Jika pendapatan total berubah sebesar {{ number_format((float)$simulasiHarga, 2, ',', '.') }}%, laba akan <strong>meningkat</strong> sebesar {{ number_format((float)$persentasePerubahanLaba, 2, ',', '.') }}%
                                    dan BEP (Rupiah) akan {{ (float)$persentasePerubahanBep >= 0 ? 'meningkat' : 'menurun' }} sebesar {{ number_format(abs((float)$persentasePerubahanBep), 2, ',', '.') }}%.<br>
                                @elseif((float)$persentasePerubahanLaba < 0)
                                    Jika pendapatan total berubah sebesar {{ number_format((float)$simulasiHarga, 2, ',', '.') }}%, laba akan <strong>menurun</strong> sebesar {{ number_format(abs((float)$persentasePerubahanLaba), 2, ',', '.') }}%
                                    dan BEP (Rupiah) akan {{ (float)$persentasePerubahanBep >= 0 ? 'meningkat' : 'menurun' }} sebesar {{ number_format(abs((float)$persentasePerubahanBep), 2, ',', '.') }}%.<br>
                            @else
                                    Perubahan pendapatan total sebesar {{ number_format((float)$simulasiHarga, 2, ',', '.') }}% tidak akan mengubah laba secara signifikan.
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Simulasi 2: Kenaikan Biaya Bahan -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Simulasi 2: Jika Biaya Variabel Total Naik</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kenaikan Biaya Variabel Total (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model.live="persentaseKenaikanBiaya" min="0" step="1">
                                    <span class="input-group-text">%</span>
                                    <button class="btn btn-primary" wire:click="simulasiKenaikanBiaya">Simulasikan</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Biaya Variabel Total Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" value="{{ number_format((float)$biayaVariabelBaru, 0, ',', '.') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Metrik</th>
                                        <th class="text-center">Nilai Awal</th>
                                        <th class="text-center">Nilai Baru</th>
                                        <th class="text-center">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Laba</td>
                                        <td class="text-end">Rp {{ number_format((float)$labaAwal, 0, ',', '.') }}</td>
                                        <td class="text-end {{ (float)$labaSetelahKenaikanBiaya < 0 ? 'text-danger' : 'text-success' }}">
                                            Rp {{ number_format((float)$labaSetelahKenaikanBiaya, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end {{ (float)$persentasePerubahanLabaBiaya >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format((float)$persentasePerubahanLabaBiaya, 2, ',', '.') }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert {{ (float)$masihUntung ? 'alert-success' : 'alert-danger' }} mt-3">
                            <i class="bi {{ (float)$masihUntung ? 'bi-check-circle' : 'bi-exclamation-triangle' }} me-2"></i>
                            <strong>Status Profitabilitas:</strong>
                            @if((float)$masihUntung)
                                Meskipun biaya variabel total naik sebesar {{ number_format((float)$persentaseKenaikanBiaya, 0, ',', '.') }}%, bisnis <strong>masih menguntungkan</strong>.
                            @else
                                Jika biaya variabel total naik sebesar {{ number_format((float)$persentaseKenaikanBiaya, 0, ',', '.') }}%, bisnis <strong>akan merugi</strong>.
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Simulasi 3: Tambah Karyawan -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>Simulasi 3: Jika Tambah Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Jumlah Karyawan Baru</label>
                                <input type="number" class="form-control" wire:model.live="jumlahKaryawanBaru" min="1" step="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gaji per Karyawan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" wire:model.live="gajiPerKaryawan" min="0" step="100000">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary" wire:click="simulasiTambahKaryawan">Simulasikan</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Metrik</th>
                                        <th class="text-center">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Tambahan Biaya Tetap</td>
                                        <td class="text-end">Rp {{ number_format((float)$tambahBiayaTetap, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>BEP Baru (Rupiah)</td>
                                        <td class="text-end">Rp {{ number_format((float)$bepBaruDenganKaryawan, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Estimasi Waktu Balik Modal</td>
                                        <td class="text-end">
                                            @if((float)$estimasiWaktuBalikModal > 0)
                                                {{ number_format((float)$estimasiWaktuBalikModal, 0, ',', '.') }} bulan
                                            @else
                                                <span class="text-danger">Tidak dapat balik modal dengan kondisi saat ini</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            <strong>Insight:</strong>
                            @if((float)$estimasiWaktuBalikModal > 0)
                                Dengan menambah {{ (float)$jumlahKaryawanBaru }} karyawan baru (total biaya Rp {{ number_format((float)$tambahBiayaTetap, 0, ',', '.') }}),
                                diperkirakan akan balik modal dalam {{ number_format((float)$estimasiWaktuBalikModal, 0, ',', '.') }} bulan.
                            @else
                                Dengan kondisi bisnis saat ini, penambahan karyawan tidak disarankan karena tidak dapat balik modal.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>