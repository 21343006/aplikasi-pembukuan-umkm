<main id="main" class="main">
    <div class="pagetitle">
        <h1>Pengeluaran</h1>
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
                            Data Pengeluaran - {{ $this->monthName }} {{ $filterYear }}
                        </h5>
                        <button class="btn btn-primary" wire:click="openModal">
                            <i class="bi bi-plus-circle"></i> Tambah Data
                        </button>
                    </div>

                    @if (count($expenditures) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Keterangan</th>
                                        <th>Jumlah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expenditures as $index => $expenditure)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($expenditure['tanggal'])->format('d/m/Y') }}</td>
                                            <td>{{ $expenditure['keterangan'] }}</td>
                                            <td>Rp {{ number_format($expenditure['jumlah'], 0, ',', '.') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-warning me-1"
                                                    wire:click="edit({{ $expenditure['id'] }})">
                                                    Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    wire:click="confirmDelete({{ $expenditure['id'] }})"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus data ini?">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total Pengeluaran:</th>
                                        <th>Rp {{ number_format($total, 0, ',', '.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle"></i>
                            Belum ada data pengeluaran untuk bulan {{ $this->monthName }} {{ $filterYear }}.
                            <br>Klik tombol "Tambah Data" untuk menambahkan data baru.
                        </div>
                    @endif
                @else
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-calendar3"></i>
                        <strong>Silakan pilih bulan dan tahun terlebih dahulu</strong> untuk melihat dan menambahkan
                        data pengeluaran.
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
                                {{ $isEdit ? 'Edit' : 'Tambah' }} Pengeluaran - {{ $this->monthName }}
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
                                <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror"
                                    wire:model="keterangan" rows="3" 
                                    placeholder="Masukkan keterangan pengeluaran"></textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number"
                                        class="form-control @error('jumlah') is-invalid @enderror"
                                        wire:model="jumlah" min="0" step="0.01" placeholder="0">
                                </div>
                                @error('jumlah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if ($jumlah)
                                <div class="alert alert-info">
                                    <strong>Jumlah: Rp {{ number_format($jumlah, 0, ',', '.') }}</strong>
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