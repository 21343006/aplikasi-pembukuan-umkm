<main id="main" class="main">
    {{-- Breadcrumb --}}
    <div class="pagetitle">
        <h1>Modal Tetap</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Modal</li>
                <li class="breadcrumb-item active">Modal Tetap</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-piggy-bank"></i> Kelola Data Modal Tetap
                </h5>

                {{-- Filter Bulan dan Tahun --}}
                <div class="row mt-3">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label for="filterMonth" class="form-label">Lihat Data Bulan</label>
                        <select wire:model.live="filterMonth" class="form-select @error('filterMonth') is-invalid @enderror">
                            @foreach($monthNames as $key => $month)
                                <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label for="filterYear" class="form-label">Lihat Data Tahun</label>
                        <input type="number" wire:model.live="filterYear" class="form-control @error('filterYear') is-invalid @enderror" placeholder="Tahun" min="2000" max="2099">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 d-flex align-items-end">
                        <button class="btn btn-info w-100" wire:click="copyFromPreviousMonth" title="Salin data dari bulan sebelumnya">
                            <i class="bi bi-clipboard"></i> Salin Bulan Lalu
                        </button>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" wire:click="openModal">
                            <i class="bi bi-plus-circle"></i> Tambah Modal Tetap
                        </button>
                    </div>
                </div>

                {{-- Informasi Summary --}}
                @if(count($monthlyTotals) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-graph-up"></i> Ringkasan Modal Tetap</h6>
                                    <div class="row">
                                        @foreach(array_slice($monthlyTotals, 0, 6) as $period => $total)
                                            @php
                                                [$year, $month] = explode('-', $period);
                                                $monthName = $monthNames[intval($month)] ?? '';
                                                $isSelected = $filterMonth == $month && $filterYear == $year;
                                            @endphp
                                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                                <div class="card {{ $isSelected ? 'border-primary bg-primary-subtle' : '' }}">
                                                    <div class="card-body text-center p-2">
                                                        <small class="text-muted">{{ $monthName }} {{ $year }}</small>
                                                        <div class="fw-bold small">
                                                            Rp {{ number_format($total, 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Header dengan informasi bulan dan tombol aksi --}}
                <div class="d-flex justify-content-between align-items-center pt-4">
                    <div>
                        <h5 class="card-title mb-1">
                            <i class="bi bi-table"></i> Data Modal Tetap - {{ $this->monthName }} {{ $filterYear }}
                            @if($jumlahNominal > 0)
                                <span class="badge bg-primary ms-2">
                                    Total: Rp {{ number_format($jumlahNominal, 0, ',', '.') }}
                                </span>
                            @endif
                        </h5>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Data yang ditambahkan akan berlaku untuk bulan ini dan bulan-bulan berikutnya dalam tahun yang sama.
                        </small>
                    </div>
                </div>

                {{-- Tabel Data --}}
                @if (count($fixedCosts) > 0)
                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="40%">Keperluan</th>
                                    <th width="25%" class="text-center">Nominal</th>
                                    <th width="30%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fixedCosts as $index => $fixedCost)
                                    <tr class="table-light">
                                        <td class="text-center fw-bold text-primary">{{ $index + 1 }}</td>
                                        <td>
                                            <div>
                                                <strong class="text-dark">{{ $fixedCost['keperluan'] }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-check text-success"></i>
                                                    Berlaku sejak: {{ \Carbon\Carbon::parse($fixedCost['tanggal'])->format('M Y') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-6 px-3 py-2">
                                                Rp {{ number_format($fixedCost['nominal'], 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-warning"
                                                        wire:click="edit({{ $fixedCost['id'] }})"
                                                        title="Edit (akan mengubah untuk bulan ini dan berikutnya)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete(this.dataset.id)"
                                                        data-id="{{ $fixedCost['id'] }}"
                                                        title="Hapus (akan menghapus untuk bulan ini dan berikutnya)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="2" class="text-end">Total Modal Tetap per Bulan {{ $this->monthName }} {{ $filterYear }}:</th>
                                    <th class="text-center">
                                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                            Rp {{ number_format($jumlahNominal, 0, ',', '.') }}
                                        </span>
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    
                @else
                    <div class="alert alert-primary mt-4">
                        <div class="text-center">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-folder2-open fs-1 text-muted me-3"></i>
                                <div class="text-start">
                                    <h5 class="mb-1">Belum ada data modal tetap</h5>
                                    <p class="mb-0">
                                        Untuk bulan <strong>{{ $this->monthName }} {{ $filterYear }}</strong>.
                                        <br>Tambahkan modal tetap seperti sewa, gaji, listrik, dll.
                                    </p>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-primary me-2" wire:click="openModal">
                                    <i class="bi bi-plus-circle"></i> Tambah Modal Tetap Pertama
                                </button>
                                <button class="btn btn-outline-info" wire:click="copyFromPreviousMonth">
                                    <i class="bi bi-clipboard"></i> Salin dari Bulan Lalu
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-info-circle"></i> Informasi Tambahan
                </h5>
                <div class="alert alert-info">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-lightbulb fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-2"><strong>Tips Penggunaan Modal Tetap:</strong></h6>
                            <ul class="mb-0">
                                <li>Modal tetap adalah biaya yang dikeluarkan secara rutin setiap bulan (sewa, gaji, listrik, dll.)</li>
                                <li>Data yang Anda tambahkan akan otomatis berlaku untuk bulan ini dan bulan-bulan berikutnya</li>
                                <li>Gunakan tombol "Salin Bulan Lalu" untuk menyalin data dari bulan sebelumnya</li>
                                <li>Edit atau hapus data akan mempengaruhi bulan ini dan bulan-bulan berikutnya</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-{{ $isEdit ? 'pencil' : 'plus-circle' }}"></i>
                                {{ $isEdit ? 'Edit' : 'Tambah' }} Modal Tetap - {{ $this->monthName }} {{ $filterYear }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            @if (!$isEdit)
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Informasi Penting:</strong> Data akan ditambahkan untuk bulan <strong>{{ $this->monthName }} {{ $filterYear }}</strong>
                                    dan bulan-bulan berikutnya dalam tahun yang sama.
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('keperluan') is-invalid @enderror"
                                               wire:model.live="keperluan"
                                               placeholder="Contoh: Sewa Tempat, Gaji Karyawan, Listrik, Internet, dll"
                                               maxlength="255">
                                        @error('keperluan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Masukkan jenis modal tetap yang dikeluarkan rutin setiap bulan
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Nominal per Bulan <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-white">Rp</span>
                                            <input type="number"
                                                   class="form-control @error('nominal') is-invalid @enderror"
                                                   wire:model.live="nominal"
                                                   min="0"
                                                   placeholder="0"
                                                   step="1000">
                                            @error('nominal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        @if ($nominal)
                                            <div class="form-text text-success">
                                                <strong>Nominal: Rp {{ number_format($nominal, 0, ',', '.') }}</strong>
                                            </div>
                                        @endif
                                        <div class="form-text">
                                            Jumlah yang dikeluarkan setiap bulan untuk keperluan ini
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($keperluan && $nominal)
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-eye"></i> Preview Data:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Keperluan:</strong><br>
                                                <span class="text-muted">{{ Str::limit($keperluan, 40) }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Nominal per Bulan:</strong><br>
                                                <span class="badge bg-primary">
                                                    Rp {{ number_format($nominal, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <hr>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-range"></i>
                                            Berlaku untuk: {{ $this->monthName }} {{ $filterYear }} - Desember {{ $filterYear }}
                                        </small>
                                    </div>
                                </div>
                            @endif

                            @if ($isEdit)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Perhatian:</strong> Perubahan akan diterapkan untuk bulan <strong>{{ $this->monthName }} {{ $filterYear }}</strong> dan bulan-bulan berikutnya dalam tahun yang sama.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                <i class="bi bi-x-lg"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <div wire:loading.remove>
                                    <i class="bi bi-{{ $isEdit ? 'check-lg' : 'plus-lg' }}"></i>
                                    {{ $isEdit ? 'Perbarui' : 'Simpan' }}
                                </div>
                                <div wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Menyimpan...
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading Overlay --}}
    <div wire:loading.delay class="position-fixed top-0 start-0 w-100 h-100"
         style="background-color: rgba(255,255,255,0.8); z-index: 1040;">
        <div class="position-absolute top-50 start-50 translate-middle">
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted">Memproses data...</div>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 1060; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Error Alert --}}
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 1060; min-width: 300px;">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Info Alert --}}
    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 1060; min-width: 300px;">
            <i class="bi bi-info-circle me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Confirmation and Auto dismiss scripts --}}
    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?\n\nData akan dihapus untuk bulan ini dan bulan-bulan berikutnya dalam tahun yang sama.')) {
                window.Livewire.find('{{ $this->id }}').call('confirmDelete', id);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto dismiss alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Optional: Format number input
            document.addEventListener('input', function(e) {
                if (e.target.matches('input[wire\\:model\\.live="nominal"]')) {
                    // Livewire will handle the reactive updates
                }
            });
        });

        // Optional: Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + N for new modal tetap
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                window.Livewire.find('{{ $this->id }}').call('openModal');
            }

            // Escape to close modal
            if (e.key === 'Escape') {
                window.Livewire.find('{{ $this->id }}').call('closeModal');
            }
        });
    </script>
</main>