<div wire:ignore.self>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-journal-text me-2"></i>Laporan Bulanan</h1>
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
                        </div>
                    </div>

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
                                                $saldoHarian = $pemasukan - $pengeluaran;
                                                $saldoKumulatif += $saldoHarian;
                                            @endphp
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}</td>
                                                <td class="text-success">Rp{{ number_format($pemasukan, 0, ',', '.') }}
                                                </td>
                                                <td class="text-danger">
                                                    Rp{{ number_format($pengeluaran, 0, ',', '.') }}</td>
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
                                                <td colspan="5" class="text-center text-muted py-4">
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
                                                <td colspan="4" class="text-end">Saldo Akhir Bulan</td>
                                                <td
                                                    class="{{ $calculatedSaldoKumulatif >= 0 ? 'text-success' : 'text-danger' }}">
                                                    Rp{{ number_format($calculatedSaldoKumulatif, 0, ',', '.') }}
                                                </td>
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
                                                <li>Saldo kumulatif = Saldo awal + Modal awal + Total pemasukan - Total pengeluaran - Modal keluar - Biaya tetap</li>
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
        </section>
    </main>
</div>