<div wire:ignore.self>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Laporan Bulanan</h1>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">

                    {{-- Filter Bulan dan Tahun --}}
                    <div class="card mb-3">
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Bulan</label>
                                    <select class="form-select" wire:model.live="bulan">
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
                                        @foreach (range(1, 12) as $b)
                                            <option value="{{ $b }}"
                                                @if ($bulan == $b) selected @endif>
                                                {{ $namaBulan[$b] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tahun</label>
                                    <input type="number" wire:model.live="tahun" class="form-control" min="2020"
                                        max="{{ now()->year }}" value="{{ $tahun }}">
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
                                        Rp{{ number_format($totalUangKeluar, 0, ',', '.') }}
                                    </strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between border-top-2">
                                    <span><strong>Total Saldo Kumulatif</strong></span>
                                    <strong class="{{ $totalSaldoKumulatif >= 0 ? 'text-success' : 'text-danger' }}">
                                        Rp{{ number_format($totalSaldoKumulatif, 0, ',', '.') }}
                                    </strong>
                                </li>
                            </ul>
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
                                                <td class="{{ $saldoKumulatif >= 0 ? 'text-success' : 'text-danger' }}">
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
                                                        Saldo kumulatif tetap: <strong>Rp{{ number_format($totalSaldoKumulatif, 0, ',', '.') }}</strong>
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforelse

                                        @if (count($rekapHarian) > 0)
                                            <tr class="fw-bold bg-light">
                                                <td colspan="4" class="text-end">Saldo Akhir Bulan</td>
                                                <td class="{{ $totalSaldoKumulatif >= 0 ? 'text-success' : 'text-danger' }}">
                                                    Rp{{ number_format($totalSaldoKumulatif, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>