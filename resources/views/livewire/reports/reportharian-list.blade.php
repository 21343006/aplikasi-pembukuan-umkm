<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Laporan Harian</h1>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">

                    {{-- Filter Tanggal --}}
                    <div class="card">
                        <div class="card-body pt-3">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label for="tanggal" class="form-label">Pilih Tanggal</label>
                                    <input type="date" wire:model="tanggal" wire:change="tanggalChanged"
                                        id="tanggal" class="form-control">
                                </div>
                                <div class="col-md-6 text-end mt-3 mt-md-0">
                                    <a wire:navigate href="/laporan-harian" class="btn btn-primary">
                                        Tambah Data
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Laporan Harian --}}
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Laporan Transaksi Harian</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal</th>
                                            <th>Keterangan</th>
                                            <th>Uang Masuk</th>
                                            <th>Uang Keluar</th>
                                            <th>Saldo</th>
                                            <th>Jenis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reports as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                                <td>{{ $item->keterangan ?? '-' }}</td>
                                                <td>Rp{{ number_format($item->uang_masuk, 0, ',', '.') }}</td>
                                                <td>Rp{{ number_format($item->uang_keluar, 0, ',', '.') }}</td>
                                                <td>Rp{{ number_format($item->saldo, 0, ',', '.') }}</td>
                                                <td>{{ $item->jenis ?? 'Laporan' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">Tidak ada data</td>
                                            </tr>
                                        @endforelse
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
