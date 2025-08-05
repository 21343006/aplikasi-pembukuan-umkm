<main id="main" class="main">
    <div class="pagetitle">
        <h1>Modal Tetap</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Manajemen Keuangan</li>
                <li class="breadcrumb-item active">Modal Tetap</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">

                {{-- Filter Bulan dan Tahun --}}
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="filterMonth">Lihat Data Bulan</label>
                        <select wire:model.live="filterMonth" class="form-control">
                            @foreach($monthNames as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterYear">Lihat Data Tahun</label>
                        <select wire:model.live="filterYear" class="form-control">
                            @for ($y = now()->year + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-info me-2" wire:click="copyFromPreviousMonth">
                            <i class="bi bi-clipboard"></i> Salin Bulan Lalu
                        </button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        <button class="btn btn-primary" wire:click="openModal">
                            <i class="bi bi-plus-circle"></i> Tambah Modal Tetap
                        </button>
                    </div>
                </div>

                {{-- Header dengan informasi bulan dan tombol aksi --}}
                <div class="d-flex justify-content-between align-items-center pt-4">
                    <div>
                        <h5 class="card-title mb-1">
                            Data Modal Tetap - {{ $this->monthName }} {{ $filterYear }}
                        </h5>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Data yang ditambahkan akan berlaku untuk bulan ini dan bulan-bulan berikutnya dalam tahun yang sama.
                        </small>
                    </div>
                </div>

                {{-- Tabel Data --}}
                @if ($fixedCosts->count() > 0)
                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="40%">Keperluan</th>
                                    <th width="25%">Nominal</th>
                                    <th width="30%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fixedCosts as $index => $fixedCost)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $fixedCost->keperluan }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                Berlaku sejak: {{ \Carbon\Carbon::parse($fixedCost->tanggal)->format('M Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">
                                                Rp {{ number_format($fixedCost->nominal, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning me-1"
                                                wire:click="edit({{ $fixedCost->id }})"
                                                title="Edit (akan mengubah untuk bulan ini dan berikutnya)">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDelete({{ $fixedCost->id }})"
                                                title="Hapus (akan menghapus untuk bulan ini dan berikutnya)">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Total Modal Tetap per Bulan:</th>
                                    <th class="text-primary">
                                        Rp {{ number_format($jumlahNominal, 0, ',', '.') }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Info Tambahan --}}
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-lightbulb"></i>
                        <strong>Tips:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Modal tetap adalah biaya yang dikeluarkan secara rutin setiap bulan (sewa, gaji, listrik, dll.)</li>
                            <li>Data yang Anda tambahkan akan otomatis berlaku untuk bulan ini dan bulan-bulan berikutnya</li>
                            <li>Gunakan tombol "Salin Bulan Lalu" untuk menyalin data dari bulan sebelumnya</li>
                            <li>Edit atau hapus data akan mempengaruhi bulan ini dan bulan-bulan berikutnya</li>
                        </ul>
                    </div>
                @else
                    <div class="text-center mt-5 mb-5">
                        <div class="alert alert-light border">
                            <i class="bi bi-folder2-open display-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">Belum ada data modal tetap</h5>
                            <p class="text-muted mb-4">
                                Untuk bulan {{ $this->monthName }} {{ $filterYear }}.
                                <br>Tambahkan modal tetap seperti sewa, gaji, listrik, dll.
                            </p>
                            <button class="btn btn-primary" wire:click="openModal">
                                <i class="bi bi-plus-circle"></i> Tambah Modal Tetap Pertama
                            </button>
                            <button class="btn btn-outline-info ms-2" wire:click="copyFromPreviousMonth">
                                <i class="bi bi-clipboard"></i> Salin dari Bulan Lalu
                            </button>
                        </div>
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
                                {{ $isEdit ? 'Edit' : 'Tambah' }} Modal Tetap
                            </h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            @if (!$isEdit)
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Data akan ditambahkan untuk bulan <strong>{{ $this->monthName }} {{ $filterYear }}</strong> 
                                    dan bulan-bulan berikutnya dalam tahun yang sama.
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('keperluan') is-invalid @enderror"
                                       wire:model.live="keperluan" 
                                       placeholder="Contoh: Sewa Tempat, Gaji Karyawan, Listrik, Internet, dll">
                                @error('keperluan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Masukkan jenis modal tetap yang dikeluarkan rutin setiap bulan
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nominal per Bulan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number"
                                           class="form-control @error('nominal') is-invalid @enderror"
                                           wire:model="nominal" 
                                           min="0" 
                                           placeholder="0" 
                                           step="1000">
                                </div>
                                @error('nominal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Jumlah yang dikeluarkan setiap bulan untuk keperluan ini
                                </div>
                            </div>

                            @if ($isEdit)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Perhatian:</strong> Perubahan akan diterapkan untuk bulan ini dan bulan-bulan berikutnya.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i>
                                {{ $isEdit ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            <i class="bi bi-check-circle"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            <i class="bi bi-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            <i class="bi bi-info-circle"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?\n\nData akan dihapus untuk bulan ini dan bulan-bulan berikutnya.')) {
                @this.call('delete', id);
            }
        }

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</main>