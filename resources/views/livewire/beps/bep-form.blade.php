<main id="main" class="main">
    <div class="pagetitle">
        <h1>Modal Tetap & Titik Balik Modal/BEP (Break Even Point)</h1>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center pt-4">
                    <h5 class="card-title">Data Modal Tetap & Break Even Point</h5>
                    <button class="btn btn-primary" wire:click="openModal">
                        <i class="bi bi-plus-circle"></i> Tambah Data
                    </button>
                </div>

                @if ($fixedCosts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Produk</th>
                                    <th>Modal Tetap</th>
                                    <th>Harga per Barang</th>
                                    <th>Modal per Barang</th>
                                    <th>Keuntungan per Unit</th>
                                    <th>BEP (Unit)</th>
                                    <th>BEP (Rupiah)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fixedCosts as $index => $fixedCost)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $fixedCost->nama_produk }}</td>
                                        <td>Rp {{ number_format($fixedCost->modal_tetap, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($fixedCost->harga_per_barang, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($fixedCost->modal_per_barang, 0, ',', '.') }}</td>
                                        <td>
                                            @if($fixedCost->keuntungan_per_unit > 0)
                                                <span class="text-success">
                                                    Rp {{ number_format($fixedCost->keuntungan_per_unit, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-danger">
                                                    Rp {{ number_format($fixedCost->keuntungan_per_unit, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($fixedCost->bep > 0)
                                                <span class="badge bg-success">{{ number_format($fixedCost->bep, 0, ',', '.') }} unit</span>
                                            @else
                                                <span class="badge bg-danger">Tidak dapat BEP</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($fixedCost->bep > 0)
                                                Rp {{ number_format($fixedCost->bep * $fixedCost->harga_per_barang, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning me-1"
                                                wire:click="edit({{ $fixedCost->id }})">
                                                Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger"
                                                wire:click="confirmDelete({{ $fixedCost->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus data ini?">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i>
                        Belum ada data modal tetap.
                        <br>Klik tombol "Tambah Data" untuk menambahkan data baru.
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $isEdit ? 'Edit' : 'Tambah' }} Data Modal Tetap & BEP
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_produk') is-invalid @enderror"
                                    wire:model="nama_produk" placeholder="Masukkan nama produk">
                                @error('nama_produk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Modal Tetap <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number"
                                                class="form-control @error('modal_tetap') is-invalid @enderror"
                                                wire:model="modal_tetap" min="0" placeholder="0" step="0.01">
                                        </div>
                                        @error('modal_tetap')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Biaya tetap yang harus dikeluarkan (sewa, gaji, dll)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Harga per Barang <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number"
                                                class="form-control @error('harga_per_barang') is-invalid @enderror"
                                                wire:model="harga_per_barang" min="0" placeholder="0" step="0.01">
                                        </div>
                                        @error('harga_per_barang')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Harga jual per unit produk</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Modal per Barang <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number"
                                        class="form-control @error('modal_per_barang') is-invalid @enderror"
                                        wire:model="modal_per_barang" min="0" placeholder="0" step="0.01">
                                </div>
                                @error('modal_per_barang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Biaya variabel per unit (bahan baku, packaging, dll)</div>
                            </div>

                            {{-- Preview Perhitungan --}}
                            @if ($modal_tetap && $harga_per_barang && $modal_per_barang)
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Preview Perhitungan:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <strong>Keuntungan per Unit:</strong>
                                                    @if ($this->keuntunganPreview > 0)
                                                        <span class="text-success">
                                                            Rp {{ number_format($this->keuntunganPreview, 0, ',', '.') }}
                                                        </span>
                                                    @else
                                                        <span class="text-danger">
                                                            Rp {{ number_format($this->keuntunganPreview, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1">
                                                    <strong>BEP (Break Even Point):</strong>
                                                    @if ($this->bepPreview > 0)
                                                        <span class="badge bg-success">
                                                            {{ number_format($this->bepPreview, 0, ',', '.') }} unit
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">Tidak dapat BEP</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        @if ($this->bepPreview > 0)
                                            <p class="mb-0">
                                                <strong>BEP dalam Rupiah:</strong>
                                                Rp {{ number_format($this->bepPreview * $harga_per_barang, 0, ',', '.') }}
                                            </p>
                                        @endif
                                        
                                        @if ($this->keuntunganPreview <= 0)
                                            <div class="alert alert-warning mt-2 mb-0">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                <strong>Peringatan!</strong> Harga per barang harus lebih besar dari modal per barang untuk mendapatkan keuntungan.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $isEdit ? 'Perbarui' : 'Simpan' }}
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