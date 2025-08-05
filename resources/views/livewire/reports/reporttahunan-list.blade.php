<div wire:ignore.self>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Laporan Tahunan</h1>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">

                    {{-- Filter Tahun --}}
                    <div class="card mb-3">
                        <div class="card-body pt-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pilih Tahun</label>
                                    <input type="number" wire:model="tahun" class="form-control"
                                        min="2000" max="{{ now()->year }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Rekap Tahunan --}}
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Rekap Laporan Tahunan {{ $tahun }}</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Pendapatan</th>
                                            <th>Pengeluaran</th>
                                            <th>Laba</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($laporan as $data)
                                            <tr>
                                                <td>{{ $data['bulan'] }}</td>
                                                <td class="text-success">
                                                    Rp{{ number_format($data['pendapatan'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-danger">
                                                    Rp{{ number_format($data['pengeluaran'], 0, ',', '.') }}
                                                </td>
                                                <td class="{{ $data['laba'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $data['laba'] >= 0 ? '+' : '' }}
                                                    Rp{{ number_format($data['laba'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox"></i><br>
                                                    Tidak ada data laporan untuk tahun {{ $tahun }}.
                                                </td>
                                            </tr>
                                        @endforelse

                                        @if (count($laporan) > 0)
                                            @php
                                                $totalPendapatan = array_sum(array_column($laporan, 'pendapatan'));
                                                $totalPengeluaran = array_sum(array_column($laporan, 'pengeluaran'));
                                                $totalLaba = $totalPendapatan - $totalPengeluaran;
                                            @endphp
                                            <tr class="fw-bold bg-light">
                                                <td>Total</td>
                                                <td class="text-success">
                                                    Rp{{ number_format($totalPendapatan, 0, ',', '.') }}
                                                </td>
                                                <td class="text-danger">
                                                    Rp{{ number_format($totalPengeluaran, 0, ',', '.') }}
                                                </td>
                                                <td class="{{ $totalLaba >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $totalLaba >= 0 ? '+' : '' }}
                                                    Rp{{ number_format($totalLaba, 0, ',', '.') }}
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
