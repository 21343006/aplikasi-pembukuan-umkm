<div wire:ignore.self>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Laporan Keuangan</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Laporan</li>
                    <li class="breadcrumb-item active">Laporan Keuangan</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    @php
                    // Hitung saldo kumulatif berdasarkan saldo awal + saldo akhir bulan ini
                    $calculatedSaldoKumulatif = $saldoAwal + $saldoAkhirBulanIni;
                    @endphp

                    {{-- Filter Bulan dan Tahun --}}
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Filter Laporan Bulanan</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="bulan" class="form-label">Bulan</label>
                                    <select wire:model.live="bulan" class="form-select" id="bulan">
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <select wire:model.live="tahun" class="form-select" id="tahun">
                                        @for ($i = 2020; $i <= 2030; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Export dan Print Buttons --}}
                            <div class="row mt-3 no-print">
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button wire:click="exportCSV" class="btn btn-success">
                                            <i class="bi bi-download"></i> Export CSV
                                        </button>
                                        <button onclick="printReport()" class="btn btn-primary">
                                            <i class="bi bi-printer"></i> Cetak Laporan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Area yang akan dicetak --}}
                    <div id="printable-area">

                    {{-- Ringkasan Saldo --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Ringkasan Bulan Ini</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Uang Masuk</span>
                                    <strong class="text-success">
                                        Rp{{ number_format($totalUangMasuk, 0, ',', '.') }}
                                    </strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Total Uang Keluar</span>
                                    <strong class="text-danger">
                                        Rp{{ number_format($totalUangKeluar + $totalModalTetap, 0, ',', '.') }}
                                    </strong>
                                </li>
                                {{-- Detail breakdown untuk uang keluar --}}
                                <li class="list-group-item ps-4">
                                    <small class="text-muted">
                                        <div class="d-flex justify-content-between">
                                            <span>• Pengeluaran Operasional:</span>
                                            <span>Rp{{ number_format($totalUangKeluar, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>• Modal Tetap:</span>
                                            <span>Rp{{ number_format($totalModalTetap, 0, ',', '.') }}</span>
                                        </div>
                                    </small>
                                </li>
                                {{-- Tambahkan informasi modal awal --}}
                                @php
                                $modalBreakdown = $this->getModalBreakdown();
                                $modalAwalBulan = collect($modalBreakdown['modal_awal'])->sum('modal_awal');
                                $modalKeluarBulan = collect($modalBreakdown['modal_keluar'])->sum('nominal');
                                @endphp
                                @if($modalAwalBulan > 0 || $modalKeluarBulan > 0)
                                <li class="list-group-item ps-4">
                                    <small class="text-muted">
                                        <div class="d-flex justify-content-between">
                                            <span>• Modal Awal:</span>
                                            <span class="text-success">+Rp{{ number_format($modalAwalBulan, 0, ',', '.') }}</span>
                                        </div>
                                        @if($modalKeluarBulan > 0)
                                        <div class="d-flex justify-content-between">
                                            <span>• Modal Keluar:</span>
                                            <span class="text-danger">-Rp{{ number_format($modalKeluarBulan, 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                    </small>
                                </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between border-top-2">
                                    <span><strong>Total Saldo Kumulatif</strong></span>
                                    <strong class="{{ $calculatedSaldoKumulatif >= 0 ? 'text-success' : 'text-danger' }}">
                                        Rp{{ number_format($calculatedSaldoKumulatif, 0, ',', '.') }}
                                    </strong>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Rekap Modal Awal dan Data Modal --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-cash-stack me-2 text-primary"></i>
                                Rekap Modal & Keuangan
                            </h5>

                            <div class="row">
                                {{-- Modal Awal --}}
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="bi bi-plus-circle me-2"></i>
                                                Modal Awal Bulan Ini
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                            $modalBreakdown = $this->getModalBreakdown();
                                            $modalAwalBulan = collect($modalBreakdown['modal_awal'])->sum('modal_awal');
                                            @endphp

                                            @if($modalAwalBulan > 0)
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="fw-bold">Total Modal Awal:</span>
                                                <span class="h5 text-success mb-0">
                                                    Rp{{ number_format($modalAwalBulan, 0, ',', '.') }}
                                                </span>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Tanggal</th>
                                                            <th>Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($modalBreakdown['modal_awal'] as $modal)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($modal['tanggal_input'])->format('d/m/Y') }}</td>
                                                            <td class="text-success">
                                                                Rp{{ number_format($modal['modal_awal'], 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-info-circle fs-4"></i>
                                                <p class="mb-0">Tidak ada modal awal untuk bulan ini</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Keluar --}}
                                <div class="col-md-6">
                                    <div class="card border-danger">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="mb-0">
                                                <i class="bi bi-dash-circle me-2"></i>
                                                Modal Keluar Bulan Ini
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @php
                                            $modalKeluarBulan = collect($modalBreakdown['modal_keluar'])->sum('nominal');
                                            @endphp

                                            @if($modalKeluarBulan > 0)
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="fw-bold">Total Modal Keluar:</span>
                                                <span class="h5 text-danger mb-0">
                                                    Rp{{ number_format($modalKeluarBulan, 0, ',', '.') }}
                                                </span>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Tanggal</th>
                                                            <th>Keperluan</th>
                                                            <th>Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($modalBreakdown['modal_keluar'] as $modal)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($modal['tanggal'])->format('d/m/Y') }}</td>
                                                            <td>
                                                                <div class="fw-bold">{{ $modal['keperluan'] }}</div>
                                                                <small class="text-muted">{{ $modal['keterangan'] }}</small>
                                                            </td>
                                                            <td class="text-danger">
                                                                Rp{{ number_format($modal['nominal'], 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-info-circle fs-4"></i>
                                                <p class="mb-0">Tidak ada modal keluar untuk bulan ini</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Biaya Tetap --}}
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="bi bi-calendar-check me-2"></i>
                                                Biaya Tetap Bulan Ini
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($totalModalTetap > 0)
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="fw-bold">Total Biaya Tetap:</span>
                                                <span class="h5 text-warning mb-0">
                                                    Rp{{ number_format($totalModalTetap, 0, ',', '.') }}
                                                </span>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Tanggal</th>
                                                            <th>Keperluan</th>
                                                            <th>Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($modalBreakdown['modal_tetap'] as $biaya)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($biaya['tanggal'])->format('d/m/Y') }}</td>
                                                            <td>{{ $biaya['keperluan'] }}</td>
                                                            <td class="text-warning">
                                                                Rp{{ number_format($biaya['nominal'], 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="text-center text-muted py-3">
                                                <i class="bi bi-info-circle fs-4"></i>
                                                <p class="mb-0">Tidak ada biaya tetap untuk bulan ini</p>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Ringkasan Modal --}}
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-lightbulb fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-2"><strong>Ringkasan Modal Bulan Ini:</strong></h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="text-center p-2">
                                                            <div class="h4 text-success mb-1">
                                                                Rp{{ number_format($modalAwalBulan, 0, ',', '.') }}
                                                            </div>
                                                            <small class="text-muted">Modal Masuk</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center p-2">
                                                            <div class="h4 text-danger mb-1">
                                                                Rp{{ number_format($modalKeluarBulan, 0, ',', '.') }}
                                                            </div>
                                                            <small class="text-muted">Modal Keluar</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-center p-2">
                                                            <div class="h4 text-warning mb-1">
                                                                Rp{{ number_format($totalModalTetap, 0, ',', '.') }}
                                                            </div>
                                                            <small class="text-muted">Biaya Tetap</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <strong>Net Modal: </strong>
                                                    <span class="h5 {{ ($modalAwalBulan - $modalKeluarBulan - $totalModalTetap) >= 0 ? 'text-success' : 'text-danger' }}">
                                                        Rp{{ number_format($modalAwalBulan - $modalKeluarBulan - $totalModalTetap, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Utang & Piutang --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-credit-card me-2 text-info"></i>
                                Informasi Utang & Piutang
                            </h5>

                            {{-- Ringkasan Total --}}
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded">
                                        <div class="h6 text-danger mb-1">Rp{{ number_format($totalUtang, 0, ',', '.') }}</div>
                                        <small class="text-muted">Utang Belum Dibayar</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded">
                                        <div class="h6 text-success mb-1">Rp{{ number_format($totalPiutang, 0, ',', '.') }}</div>
                                        <small class="text-muted">Piutang Belum Diterima</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded bg-light">
                                        <div class="h6 text-primary mb-1">Rp{{ number_format($totalUtangDibayarBulanIni, 0, ',', '.') }}</div>
                                        <small class="text-muted">Utang Dibayar Bulan Ini</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center p-3 border rounded bg-light">
                                        <div class="h6 text-secondary mb-1">Rp{{ number_format($totalPiutangDiterimaBulanIni, 0, ',', '.') }}</div>
                                        <small class="text-muted">Piutang Diterima Bulan Ini</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                {{-- Utang Belum Dibayar --}}
                                <div class="col-md-6">
                                    <div class="card border-danger">
                                        <div class="card-header bg-danger text-white">
                                            <h6 class="mb-0">
                                                <i class="bi bi-exclamation-triangle me-2"></i>Utang Belum Dibayar
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="text-danger mb-3">Rp{{ number_format($totalUtang, 0, ',', '.') }}</h4>
                                            @if(count($utangDetails) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Kreditur</th>
                                                            <th>Sisa</th>
                                                            <th>Jatuh Tempo</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($utangDetails as $utang)
                                                        <tr class="{{ isset($utang['is_overdue']) && $utang['is_overdue'] ? 'table-danger' : '' }}">
                                                            <td class="small">
                                                                <strong>{{ $utang['creditor_name'] }}</strong>
                                                                <br><small class="text-muted">{{ Str::limit($utang['description'], 30) }}</small>
                                                            </td>
                                                            <td class="text-danger fw-bold">Rp{{ number_format($utang['remaining_amount'] ?? $utang['amount'], 0, ',', '.') }}</td>
                                                            <td class="small">
                                                                {{ isset($utang['due_date']) ? \Carbon\Carbon::parse($utang['due_date'])->format('d/m/Y') : '-' }}
                                                                @if(isset($utang['is_overdue']) && $utang['is_overdue'])
                                                                    <br><small class="text-danger">Terlambat {{ $utang['days_overdue'] ?? 0 }} hari</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(isset($utang['is_overdue']) && $utang['is_overdue'])
                                                                    <span class="badge bg-danger">Overdue</span>
                                                                @else
                                                                    <span class="badge bg-warning">Belum Bayar</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <p class="text-muted small mb-0">Tidak ada utang untuk bulan ini</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Piutang Belum Diterima --}}
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">
                                                <i class="bi bi-hand-holding-dollar me-2"></i>Piutang Belum Diterima
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="text-success mb-3">Rp{{ number_format($totalPiutang, 0, ',', '.') }}</h4>
                                            @if(count($piutangDetails) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Debitur</th>
                                                            <th>Sisa</th>
                                                            <th>Jatuh Tempo</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($piutangDetails as $piutang)
                                                        <tr class="{{ isset($piutang['is_overdue']) && $piutang['is_overdue'] ? 'table-warning' : '' }}">
                                                            <td class="small">
                                                                <strong>{{ $piutang['debtor_name'] }}</strong>
                                                                <br><small class="text-muted">{{ Str::limit($piutang['description'], 30) }}</small>
                                                            </td>
                                                            <td class="text-success fw-bold">Rp{{ number_format($piutang['remaining_amount'] ?? $piutang['amount'], 0, ',', '.') }}</td>
                                                            <td class="small">
                                                                {{ isset($piutang['due_date']) ? \Carbon\Carbon::parse($piutang['due_date'])->format('d/m/Y') : '-' }}
                                                                @if(isset($piutang['is_overdue']) && $piutang['is_overdue'])
                                                                    <br><small class="text-warning">Terlambat {{ $piutang['days_overdue'] ?? 0 }} hari</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(isset($piutang['is_overdue']) && $piutang['is_overdue'])
                                                                    <span class="badge bg-warning">Overdue</span>
                                                                @else
                                                                    <span class="badge bg-info">Belum Terima</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <p class="text-muted small mb-0">Tidak ada piutang untuk bulan ini</p>
                                            @endif
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rekap Harian --}}
                    <div class="card" wire:key="rekap-{{ $bulan }}-{{ $tahun }}">
                        <div class="card-body">
                            <h5 class="card-title">
                                @php
                                $namaBulan = [
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                                ];
                                @endphp
                                Rekap Harian Bulan
                                {{ isset($bulan) && isset($namaBulan[$bulan]) ? $namaBulan[$bulan] : 'Unknown' }}
                                {{ $tahun }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Total Pemasukan</th>
                                            <th>Total Pengeluaran</th>
                                            <th>Utang Dibayar</th>
                                            <th>Piutang Diterima</th>
                                            <th>Saldo Harian</th>
                                            <th>Saldo Kumulatif</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        // Mulai dari saldo awal (saldo kumulatif sebelum bulan ini)
                                        $saldoKumulatif = $saldoAwal;
                                        @endphp

                                        @forelse ($rekapHarian as $tanggal => $data)
                                        @php
                                        $pemasukan = (float) $data['masuk'];
                                        $pengeluaran = (float) $data['keluar'];
                                        $utangDibayar = (float) ($data['utang_dibayar'] ?? 0);
                                        $piutangDiterima = (float) ($data['piutang_diterima'] ?? 0);
                                        // Saldo harian = pemasukan - pengeluaran - utang dibayar + piutang diterima
                                        $saldoHarian = $pemasukan - $pengeluaran - $utangDibayar + $piutangDiterima;
                                        $saldoKumulatif += $saldoHarian;
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</td>
                                            <td class="text-success">Rp{{ number_format($pemasukan, 0, ',', '.') }}
                                            </td>
                                            <td class="text-danger">
                                                Rp{{ number_format($pengeluaran, 0, ',', '.') }}</td>
                                            <td class="text-warning">
                                                @if($utangDibayar > 0)
                                                    Rp{{ number_format($utangDibayar, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-info">
                                                @if($piutangDiterima > 0)
                                                    Rp{{ number_format($piutangDiterima, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="{{ $saldoHarian >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $saldoHarian >= 0 ? '+' : '' }}Rp{{ number_format($saldoHarian, 0, ',', '.') }}
                                            </td>
                                            <td
                                                class="{{ $saldoKumulatif >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp{{ number_format($saldoKumulatif, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox"></i><br>
                                                Tidak ada data laporan untuk bulan
                                                {{ $namaBulan[$bulan] ?? 'Unknown' }} {{ $tahun }}
                                                <br><br>
                                                <small class="text-info">
                                                    Saldo kumulatif tetap:
                                                    <strong>Rp{{ number_format($calculatedSaldoKumulatif, 0, ',', '.') }}</strong>
                                                </small>
                                            </td>
                                        </tr>
                                        @endforelse

                                        @if (count($rekapHarian) > 0)
                                        <tr class="fw-bold bg-light">
                                            <td colspan="6" class="text-end">Saldo Akhir Bulan</td>
                                            <td
                                                class="{{ $calculatedSaldoKumulatif >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp{{ number_format($calculatedSaldoKumulatif, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        
                                        {{-- Summary row for debt and receivable totals --}}
                                        <tr class="fw-bold bg-info text-white">
                                            <td colspan="3" class="text-end">Total Bulan Ini</td>
                                            <td>Rp{{ number_format($totalUtangDibayarBulanIni, 0, ',', '.') }}</td>
                                            <td>Rp{{ number_format($totalPiutangDiterimaBulanIni, 0, ',', '.') }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                                {{-- Info Tambahan --}}
                                <div class="alert alert-info mt-3">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-lightbulb fs-4 me-3"></i>
                                        <div>
                                            <h6 class="mb-2"><strong>Informasi mengenai laporan:</strong></h6>
                                            <ul class="mb-0">
                                                <li>Modal awal akan dihitung sebagai total pemasukan diawal list yang akan mempengaruhi saldo kumulatif</li>
                                                <li>Modal keluar dari pengelolaan modal akan dihitung sebagai total pengeluaran di awal list</li>
                                                <li>Biaya tetap akan dihitung sebagai pengeluaran di awal list setiap awal bulan dan <strong>sudah termasuk dalam Total Uang Keluar</strong></li>
                                                <li><strong>Utang dibayar</strong> akan mengurangi saldo karena uang keluar untuk membayar utang</li>
                                                <li><strong>Piutang diterima</strong> akan menambah saldo karena uang masuk dari penagihan piutang</li>
                                                <li>Saldo harian = Pemasukan - Pengeluaran - Utang dibayar + Piutang diterima</li>
                                                <li>Saldo kumulatif = Saldo awal + semua transaksi harian (termasuk utang & piutang)</li>
                                                <li>Net Modal = Modal awal - Modal keluar - Biaya tetap (menunjukkan kontribusi modal terhadap saldo)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            </div> {{-- End of printable-area --}}
        </section>

        {{-- Penjelasan untuk Pelaku UMKM Awam --}}
        <section class="bg-light py-5 mt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="text-center mb-4">
                                    <i class="bi bi-calendar-month text-primary display-4"></i>
                                    <h4 class="mt-3 text-primary fw-bold">Panduan Laporan Bulanan</h4>
                                    <p class="text-muted">Penjelasan sederhana untuk memahami laporan keuangan bulanan UMKM</p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="h-100 p-3 border rounded bg-white">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="bi bi-cash-coin me-2"></i>
                                                Komponen Pemasukan
                                            </h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <strong class="text-success">Modal Awal:</strong> Uang yang disetorkan sebagai modal usaha
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-success">Pemasukan Harian:</strong> Uang dari penjualan produk/jasa setiap hari
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-success">Total Pemasukan:</strong> Jumlah semua uang yang masuk dalam satu hari
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-info">Piutang Diterima:</strong> Uang yang diterima dari penagihan piutang
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="h-100 p-3 border rounded bg-white">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="bi bi-credit-card me-2"></i>
                                                Komponen Pengeluaran
                                            </h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <strong class="text-danger">Modal Keluar:</strong> Uang yang ditarik dari usaha
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-danger">Biaya Tetap:</strong> Biaya bulanan seperti sewa, listrik, gaji
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-danger">Pengeluaran Harian:</strong> Biaya operasional harian
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-warning">Utang Dibayar:</strong> Uang yang keluar untuk membayar utang
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="p-3 border rounded bg-white">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="bi bi-calculator me-2"></i>
                                                Cara Menghitung Saldo
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="p-3 border rounded h-100">
                                                        <h6 class="fw-bold text-success">Saldo Harian</h6>
                                                        <p class="mb-2"><strong>Rumus:</strong></p>
                                                        <p class="mb-2">Pemasukan - Pengeluaran - Utang Dibayar + Piutang Diterima</p>
                                                        <small class="text-muted">Menunjukkan perubahan kas harian yang sesungguhnya</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="p-3 border rounded h-100">
                                                        <h6 class="fw-bold text-info">Saldo Kumulatif</h6>
                                                        <p class="mb-2"><strong>Rumus:</strong></p>
                                                        <p class="mb-2">Saldo Awal + Modal + Pemasukan - Pengeluaran</p>
                                                        <small class="text-muted">Menunjukkan total uang yang tersisa</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="alert alert-info border-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-info-circle me-3 mt-1"></i>
                                                <div>
                                                    <h6 class="alert-heading fw-bold">Tips Membaca Laporan Bulanan!</h6>
                                                    <ul class="mb-0">
                                                        <li><strong>Setiap Pagi:</strong> Periksa saldo harian kemarin</li>
                                                        <li><strong>Setiap Minggu:</strong> Review tren pemasukan dan pengeluaran</li>
                                                        <li><strong>Setiap Bulan:</strong> Bandingkan dengan bulan sebelumnya</li>
                                                        <li><strong>Perhatian:</strong> Jika saldo kumulatif turun terus, perlu evaluasi biaya</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- Print Styles --}}
    <style media="print">
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body * {
            visibility: hidden;
        }
        
        #printable-area, #printable-area * {
            visibility: visible;
        }
        
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        .btn, .breadcrumb, .card-title .bi, .no-print {
            display: none !important;
        }
        
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 12px;
        }
        
        .table th, .table td {
            padding: 0.3rem !important;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #000 !important;
        }
        
        .text-success, .text-danger, .text-warning, .text-info {
            color: #000 !important;
        }
    </style>

    {{-- JavaScript untuk Print --}}
    <script>
        function printReport() {
            // Simple print using browser's print function
            window.print();
        }

        // Livewire hook untuk menangani export success
        document.addEventListener('livewire:init', function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.on('export-success', function(message) {
                    console.log('Export completed successfully');
                });
            }
        });
    </script>
</div>