<main id="main" class="main">
    <div class="pagetitle">
        <h1>Pendapatan</h1>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">

                {{-- Filter Bulan dan Tahun --}}
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="filterMonth">Pilih Bulan</label>
                        <select wire:model.live="filterMonth" class="form-control">
                            <option value="">-- Pilih Bulan --</option>
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
                    <div class="col-md-3">
                        <label for="filterYear">Pilih Tahun</label>
                        <select wire:model.live="filterYear" class="form-control">
                            <option value="">-- Pilih Tahun --</option>
                            @for ($y = now()->year; $y >= 2020; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    @if ($filterMonth && $filterYear)
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-secondary" wire:click="clearFilters">Reset Filter</button>
                        </div>
                    @endif
                </div>

                {{-- Jika bulan & tahun sudah dipilih --}}
                @if ($filterMonth && $filterYear)
                    <div class="d-flex justify-content-between align-items-center pt-4">
                        <h5 class="card-title">
                            Data Penjualan - {{ $this->monthName }} {{ $filterYear }}
                        </h5>
                        <button class="btn btn-primary" wire:click="openModal">
                            <i class="bi bi-plus-circle"></i> Tambah Data
                        </button>
                    </div>

                    @if ($incomes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Produk/Jasa</th>
                                        <th>Jumlah Terjual</th>
                                        <th>Harga Satuan</th>
                                        <th>Total Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($incomes as $index => $income)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($income->tanggal)->format('d/m/Y') }}</td>
                                            <td>{{ $income->produk }}</td>
                                            <td>{{ number_format($income->jumlah_terjual, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($income->harga_satuan, 0, ',', '.') }}</td>
                                            <td>Rp
                                                {{ number_format($income->jumlah_terjual * $income->harga_satuan, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning me-1"
                                                    wire:click="edit({{ $income->id }})">
                                                    Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    wire:click="confirmDelete({{ $income->id }})"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus data ini?">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Pendapatan:</th>
                                        <th>Rp {{ number_format($jumlah, 0, ',', '.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i>
                            Belum ada data penjualan untuk bulan {{ $this->monthName }} {{ $filterYear }}.
                            <br>Klik tombol "Tambah Data" untuk menambahkan data baru.
                        </div>
                    @endif
                @else
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-calendar3"></i>
                        <strong>Silakan pilih bulan dan tahun terlebih dahulu</strong> untuk melihat dan menambahkan
                        data pendapatan.
                    </div>
                @endif

            </div>
        </div>
    </section>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $isEdit ? 'Edit' : 'Tambah' }} Pendapatan - {{ $this->monthName }}
                                {{ $filterYear }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                                    wire:model="tanggal"
                                    min="{{ $filterYear }}-{{ str_pad($filterMonth, 2, '0', STR_PAD_LEFT) }}-01"
                                    max="{{ $this->maxDate }}">
                                @error('tanggal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Produk/Jasa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('produk') is-invalid @enderror"
                                    wire:model="produk" placeholder="Masukkan nama produk atau jasa">
                                @error('produk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah Terjual <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('jumlah_terjual') is-invalid @enderror"
                                    wire:model="jumlah_terjual" min="1" placeholder="0">
                                @error('jumlah_terjual')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number"
                                        class="form-control @error('harga_satuan') is-invalid @enderror"
                                        wire:model="harga_satuan" min="0" placeholder="0">
                                </div>
                                @error('harga_satuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if ($jumlah_terjual && $harga_satuan)
                                <div class="alert alert-info">
                                    <strong>Total: Rp
                                        {{ number_format($jumlah_terjual * $harga_satuan, 0, ',', '.') }}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="position-fixed top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999;">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</main>
