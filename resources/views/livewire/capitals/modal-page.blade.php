<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1 class="text-gradient-primary">
                <i class="bi bi-wallet2 me-2"></i>Pengelolaan Modal
            </h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#" class="text-muted">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">Modal</li>
                    <li class="breadcrumb-item active">Pengelolaan Modal</li>
                </ol>
            </nav>
        </div>

        <section class="section">

            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="card-header bg-gradient-primary text-white border-0">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-graph-up-arrow me-2 fs-4"></i>
                        <h5 class="mb-0 fw-bold">Filter & Laporan Modal</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    {{-- Summary Cards --}}
                    <div class="row g-4 mb-4">
                        @if ($hasJenisColumn)
                            {{-- Modal Masuk Card --}}
                            <div class="col-md-3 col-sm-6">
                                <div class="summary-card bg-gradient-success">
                                    <div class="summary-content">
                                        <div class="summary-info">
                                            <h6 class="fw-light mb-2 opacity-75">Modal Masuk</h6>
                                            <h4 class="fw-bold mb-0">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</h4>
                                        </div>
                                        <div class="summary-icon">
                                            <i class="bi bi-arrow-up-circle fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Modal Keluar Card --}}
                            <div class="col-md-3 col-sm-6">
                                <div class="summary-card bg-gradient-danger">
                                    <div class="summary-content">
                                        <div class="summary-info">
                                            <h6 class="fw-light mb-2 opacity-75">Modal Keluar</h6>
                                            <h4 class="fw-bold mb-0">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</h4>
                                        </div>
                                        <div class="summary-icon">
                                            <i class="bi bi-arrow-down-circle fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Total Modal Card (Backward Compatibility) --}}
                            <div class="col-md-4 col-sm-6">
                                <div class="summary-card bg-gradient-primary">
                                    <div class="summary-content">
                                        <div class="summary-info">
                                            <h6 class="fw-light mb-2 opacity-75">Total Modal</h6>
                                            <h4 class="fw-bold mb-0">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</h4>
                                        </div>
                                        <div class="summary-icon">
                                            <i class="bi bi-wallet2 fs-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        {{-- Saldo Modal Card --}}
                        <div class="col-md-3 col-sm-6">
                            <div class="summary-card bg-gradient-{{ $saldo >= 0 ? 'info' : 'warning' }}">
                                <div class="summary-content">
                                    <div class="summary-info">
                                        <h6 class="fw-light mb-2 opacity-75">
                                            {{ $hasJenisColumn ? 'Saldo Modal' : 'Total Modal' }}
                                        </h6>
                                        <h4 class="fw-bold mb-0">Rp {{ number_format($saldo, 0, ',', '.') }}</h4>
                                    </div>
                                    <div class="summary-icon">
                                        <i class="bi bi-{{ $saldo >= 0 ? 'check-circle' : 'exclamation-triangle' }} fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Action Buttons Card --}}
                        <div class="col-md-3 col-sm-6">
                            <div class="card border-0 shadow-lg h-100 bg-white">
                                <div class="card-body p-4 d-flex flex-column justify-content-center">
                                    @if ($hasJenisColumn)
                                        <button class="btn btn-success shadow-sm mb-2 btn-hover" wire:click="openModal('masuk')">
                                            <i class="bi bi-plus-circle me-2"></i>Modal Masuk
                                        </button>
                                        <button class="btn btn-danger shadow-sm btn-hover" wire:click="openModal('keluar')">
                                            <i class="bi bi-dash-circle me-2"></i>Modal Keluar
                                        </button>
                                    @else
                                        <button class="btn btn-primary shadow-sm btn-hover" wire:click="openModal('masuk')">
                                            <i class="bi bi-plus-circle me-2"></i>Tambah Modal
                                        </button>
                                        <div class="alert alert-warning mt-2 mb-0 border-0 p-2">
                                            <small>
                                                <i class="bi bi-info-circle me-1"></i>
                                                Mode kompatibilitas aktif
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Modal Masuk dan Keluar (hanya jika ada kolom jenis) --}}
                    @if ($hasJenisColumn)
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="card-title mb-3 fw-bold text-dark">
                                    <i class="bi bi-arrow-up-circle me-2 text-success"></i>Modal Masuk
                                </h5>
                                <div class="table-responsive shadow-sm rounded-4 overflow-hidden">
                                    <table class="table table-hover mb-0 modern-table">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="border-0 py-3"><i class="bi bi-hash me-1"></i>No</th>
                                                <th class="border-0 py-3"><i class="bi bi-calendar-date me-1"></i>Tanggal</th>
                                                <th class="border-0 py-3"><i class="bi bi-currency-dollar me-1"></i>Nominal</th>
                                                <th class="border-0 py-3 text-center"><i class="bi bi-gear me-1"></i>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($modalMasuk as $index => $capital)
                                                <tr class="table-row-hover">
                                                    <td class="py-3 fw-semibold">
                                                        <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-calendar3 text-muted me-2"></i>
                                                            <span class="fw-medium">{{ \Carbon\Carbon::parse($capital['tanggal'])->format('d/m/Y') }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="fw-bold text-success fs-6">
                                                            + Rp {{ number_format($capital['nominal'], 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-center">
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-warning btn-hover" 
                                                                wire:click="edit({{ $capital['id'] }})" title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger btn-hover" 
                                                                wire:click="confirmDelete({{ $capital['id'] }})"
                                                                wire:confirm="Apakah Anda yakin ingin menghapus data ini?" title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada data modal masuk.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="card-title mb-3 fw-bold text-dark">
                                    <i class="bi bi-arrow-down-circle me-2 text-danger"></i>Modal Keluar
                                </h5>
                                <div class="table-responsive shadow-sm rounded-4 overflow-hidden">
                                    <table class="table table-hover mb-0 modern-table">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="border-0 py-3"><i class="bi bi-hash me-1"></i>No</th>
                                                <th class="border-0 py-3"><i class="bi bi-calendar-date me-1"></i>Tanggal</th>
                                                <th class="border-0 py-3"><i class="bi bi-currency-dollar me-1"></i>Nominal</th>
                                                <th class="border-0 py-3 text-center"><i class="bi bi-gear me-1"></i>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($modalKeluar as $index => $capital)
                                                <tr class="table-row-hover">
                                                    <td class="py-3 fw-semibold">
                                                        <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-calendar3 text-muted me-2"></i>
                                                            <span class="fw-medium">{{ \Carbon\Carbon::parse($capital['tanggal'])->format('d/m/Y') }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="fw-bold text-danger fs-6">
                                                            - Rp {{ number_format($capital['nominal'], 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                    <td class="py-3 text-center">
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-warning btn-hover" 
                                                                wire:click="edit({{ $capital['id'] }})" title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger btn-hover" 
                                                                wire:click="confirmDelete({{ $capital['id'] }})"
                                                                wire:confirm="Apakah Anda yakin ingin menghapus data ini?" title="Hapus">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada data modal keluar.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                    @endif

                    {{-- Tabel Data Modal Lengkap --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="card-title mb-1 fw-bold text-dark">
                                <i class="bi bi-table me-2 text-primary"></i>Data Modal
                            </h5>
                            @if ($filterJenis && $hasJenisColumn)
                                <small class="text-muted">
                                    <i class="bi bi-filter me-1"></i>Filter: {{ $filterJenis == 'masuk' ? 'Modal Masuk' : 'Modal Keluar' }}
                                </small>
                            @endif
                        </div>
                        @if (count($capitals) > 0)
                            <div class="badge bg-primary fs-6 px-3 py-2">
                                <i class="bi bi-list-ol me-1"></i>{{ count($capitals) }} Transaksi
                            </div>
                        @endif
                    </div>

                    @if (count($capitals) > 0)
                        <div class="table-responsive shadow-sm rounded-4 overflow-hidden">
                            <table class="table table-hover mb-0 modern-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="border-0 py-3"><i class="bi bi-hash me-1"></i>No</th>
                                        <th class="border-0 py-3"><i class="bi bi-calendar-date me-1"></i>Tanggal</th>
                                        <th class="border-0 py-3"><i class="bi bi-person me-1"></i>User</th>
                                        @if ($tableStructure['has_keperluan'])
                                            <th class="border-0 py-3"><i class="bi bi-clipboard me-1"></i>Keperluan</th>
                                        @endif
                                        @if ($tableStructure['has_keterangan'])
                                            <th class="border-0 py-3"><i class="bi bi-journal-text me-1"></i>Keterangan</th>
                                        @endif
                                        @if ($hasJenisColumn)
                                            <th class="border-0 py-3"><i class="bi bi-tags me-1"></i>Jenis</th>
                                        @endif
                                        <th class="border-0 py-3"><i class="bi bi-currency-dollar me-1"></i>Nominal</th>
                                        <th class="border-0 py-3 text-center"><i class="bi bi-gear me-1"></i>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($capitals as $index => $capital)
                                        <tr class="table-row-hover">
                                            <td class="py-3 fw-semibold">
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-calendar3 text-muted me-2"></i>
                                                    <span class="fw-medium">{{ \Carbon\Carbon::parse($capital['tanggal'])->format('d/m/Y') }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <span class="fw-medium">{{ auth()->user()->name ?? 'Unknown' }}</span>
                                                </div>
                                            </td>
                                            @if ($tableStructure['has_keperluan'])
                                                <td class="py-3">
                                                    <span class="{{ isset($capital['keperluan']) && $capital['keperluan'] ? 'text-dark' : 'text-muted' }}">
                                                        {{ $capital['keperluan'] ?? '-' }}
                                                    </span>
                                                </td>
                                            @endif
                                            @if ($tableStructure['has_keterangan'])
                                                <td class="py-3">
                                                    <span class="{{ isset($capital['keterangan']) && $capital['keterangan'] ? 'text-dark' : 'text-muted' }}">
                                                        @if(isset($capital['keterangan']) && $capital['keterangan'])
                                                            {{ strlen($capital['keterangan']) > 30 ? substr($capital['keterangan'], 0, 30) . '...' : $capital['keterangan'] }}
                                                        @else
                                                            -
                                                        @endif
                                                    </span>
                                                </td>
                                            @endif
                                            @if ($hasJenisColumn)
                                                <td class="py-3">
                                                    @php
                                                        $jenis = $capital['jenis'] ?? 'masuk';
                                                    @endphp
                                                    <span class="badge bg-{{ $jenis == 'masuk' ? 'success' : 'danger' }} px-3 py-2">
                                                        <i class="bi bi-{{ $jenis == 'masuk' ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                        {{ ucfirst($jenis) }}
                                                    </span>
                                                </td>
                                            @endif
                                            <td class="py-3">
                                                @php
                                                    $jenis = $hasJenisColumn ? ($capital['jenis'] ?? 'masuk') : 'masuk';
                                                    $color = $jenis == 'masuk' ? 'success' : 'danger';
                                                    $sign = $jenis == 'masuk' ? '+' : '-';
                                                @endphp
                                                <span class="fw-bold text-{{ $color }} fs-6">
                                                    {{ $sign }} Rp {{ number_format($capital['nominal'], 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-center">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-warning btn-hover" 
                                                        wire:click="edit({{ $capital['id'] }})" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger btn-hover" 
                                                        wire:click="confirmDelete({{ $capital['id'] }})"
                                                        wire:confirm="Apakah Anda yakin ingin menghapus data ini?" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        @php
                                            $colspan = 3; // No, Tanggal, User
                                            if ($tableStructure['has_keperluan']) $colspan++;
                                            if ($tableStructure['has_keterangan']) $colspan++;
                                            if ($hasJenisColumn) $colspan++;
                                        @endphp
                                        <th colspan="{{ $colspan }}" class="text-end border-0 py-3 fw-bold">
                                            <i class="bi bi-calculator me-2"></i>{{ $hasJenisColumn ? 'Saldo Modal:' : 'Total Modal:' }}
                                        </th>
                                        <th class="text-{{ $saldo >= 0 ? 'success' : 'danger' }} border-0 py-3">
                                            <span class="fs-5 fw-bold">Rp {{ number_format($saldo, 0, ',', '.') }}</span>
                                        </th>
                                        <th class="border-0"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="empty-state text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-3">Belum Ada Data Modal</h5>
                            <p class="text-muted mb-4">
                                Belum ada data modal untuk <strong>{{ $this->monthName }} {{ $filterYear }}</strong>.
                                <br>Klik tombol di bawah untuk menambahkan data baru.
                            </p>
                            @if ($hasJenisColumn)
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-success shadow-sm btn-hover" wire:click="openModal('masuk')">
                                        <i class="bi bi-plus-circle me-2"></i>Modal Masuk
                                    </button>
                                    <button class="btn btn-danger shadow-sm btn-hover" wire:click="openModal('keluar')">
                                        <i class="bi bi-dash-circle me-2"></i>Modal Keluar
                                    </button>
                                </div>
                            @else
                                <button class="btn btn-primary shadow-sm btn-hover" wire:click="openModal('masuk')">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Modal
                                </button>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </section>

        {{-- Modal Form dengan Backward Compatibility --}}
        @if ($showModal)
            <div class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1055;">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form wire:submit.prevent="save">
                            @php
                                $modalColor = ($hasJenisColumn && $jenis == 'keluar') ? 'danger' : 'success';
                            @endphp
                            <div class="modal-header bg-gradient-{{ $modalColor }} text-white">
                                <h5 class="modal-title fw-bold">
                                    <i class="bi bi-{{ $isEdit ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                                    {{ $isEdit ? 'Edit' : 'Tambah' }} Modal
                                    @if ($hasJenisColumn)
                                        {{ ucfirst($jenis) }}
                                    @endif
                                </h5>
                                <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                            </div>
                            
                            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                @if ($hasJenisColumn)
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-tags me-1 text-primary"></i>Jenis Transaksi <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('jenis') is-invalid @enderror" wire:model="jenis">
                                            <option value="masuk">Modal Masuk</option>
                                            <option value="keluar">Modal Keluar</option>
                                        </select>
                                        @error('jenis')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <div class="alert alert-info border-0 mb-4">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Mode Kompatibilitas:</strong> Semua data akan disimpan sebagai modal masuk.
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-calendar-date me-1 text-primary"></i>Tanggal <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                                                wire:model="tanggal">
                                            @error('tanggal')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                @if ($tableStructure['has_keperluan'])
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-clipboard me-1 text-primary"></i>Keperluan
                                        </label>
                                        <input type="text" class="form-control @error('keperluan') is-invalid @enderror"
                                            wire:model="keperluan" placeholder="Untuk keperluan apa">
                                        @error('keperluan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                @if ($tableStructure['has_keterangan'])
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-journal-text me-1 text-primary"></i>Keterangan
                                        </label>
                                        <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                            wire:model="keterangan" rows="3" placeholder="Keterangan tambahan"></textarea>
                                        @error('keterangan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-currency-dollar me-1 text-primary"></i>Nominal <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><strong>Rp</strong></span>
                                        <input type="number" class="form-control @error('nominal') is-invalid @enderror"
                                            wire:model="nominal" min="0" placeholder="0">
                                    </div>
                                    @error('nominal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if ($nominal)
                                    @php
                                        $previewColor = ($hasJenisColumn && $jenis == 'keluar') ? 'danger' : 'success';
                                        $previewIcon = ($hasJenisColumn && $jenis == 'keluar') ? 'arrow-down-circle' : 'arrow-up-circle';
                                        $previewSign = ($hasJenisColumn && $jenis == 'keluar') ? '-' : '+';
                                        $previewText = ($hasJenisColumn && $jenis == 'keluar') ? 'Modal akan berkurang' : 'Modal akan bertambah';
                                    @endphp
                                    <div class="alert alert-{{ $previewColor }} border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-{{ $previewIcon }} me-2 fs-4"></i>
                                            <div>
                                                <strong class="fs-5">
                                                    {{ $previewSign }} Rp {{ number_format($nominal, 0, ',', '.') }}
                                                </strong>
                                                <br>
                                                <small class="opacity-75">{{ $previewText }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="modal-footer" style="border-top: 1px solid #dee2e6; background-color: #f8f9fa;">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                    <i class="bi bi-x-circle me-1"></i>Batal
                                </button>
                                <button type="submit" class="btn btn-{{ $modalColor }}">
                                    <i class="bi bi-{{ $isEdit ? 'check-circle' : 'plus-circle' }} me-1"></i>
                                    {{ $isEdit ? 'Update' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Loading indicator --}}
        <div wire:loading class="loading-overlay">
            <div class="loading-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-primary fw-medium">Memproses...</p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast show border-0 shadow-lg bg-success text-white">
                    <div class="toast-body p-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle me-2 fs-4"></i>
                            <div class="flex-grow-1">
                                <strong>Berhasil!</strong><br>{{ session('message') }}
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast show border-0 shadow-lg bg-danger text-white">
                    <div class="toast-body p-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle me-2 fs-4"></i>
                            <div class="flex-grow-1">
                                <strong>Error!</strong><br>{{ session('error') }}
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>
</div>

{{-- Styles - Enhanced with backward compatibility features --}}
<style>
    /* Core Gradient Styles */
    .text-gradient-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #0056b3); }
    .bg-gradient-success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .bg-gradient-danger { background: linear-gradient(135deg, #dc3545, #bd2130); }
    .bg-gradient-info { background: linear-gradient(135deg, #17a2b8, #117a8b); }
    .bg-gradient-warning { background: linear-gradient(135deg, #ffc107, #d39e00); }

    /* Enhanced Components */
    .summary-card {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        height: 100%;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }

    .summary-content {
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: start;
        color: white;
    }

    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Filter Section */
    .filter-section {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 1px solid #dee2e6;
    }

    /* Table Styles */
    .modern-table {
        --bs-table-bg: transparent;
    }

    .modern-table thead th {
        background: linear-gradient(135deg, #343a40, #495057) !important;
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .table-row-hover:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.005);
        transition: all 0.3s ease;
    }

    .avatar-circle {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 12px;
    }

    /* Button Enhancements */
    .btn {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
    }

    .btn-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .btn-group .btn {
        border-radius: 0.375rem !important;
    }

    .btn-group .btn:hover {
        transform: translateY(-1px);
    }

    /* Modal Styles */
    .modal {
        backdrop-filter: blur(5px);
    }

    .modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        overflow: hidden;
    }

    .modal-header {
        border-bottom: none;
        padding: 1.5rem 2rem;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        padding: 1.5rem 2rem;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9998;
        backdrop-filter: blur(2px);
    }

    .loading-content {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }

    /* Toast Styles */
    .toast-container .toast {
        min-width: 350px;
        border-radius: 1rem;
    }

    /* Form Enhancements */
    .form-control:focus,
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
        font-weight: 600;
    }

    /* Alert & Badge */
    .alert {
        border-radius: 0.75rem;
    }

    .badge {
        font-size: 0.75em;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 0.5rem;
    }

    /* Empty States */
    .empty-state, .welcome-state {
        padding: 3rem 1rem;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .modal-dialog {
            width: 95%;
            margin: 1rem;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            padding: 1rem !important;
        }

        .modal-body {
            max-height: 60vh;
        }

        .summary-content {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .filter-section {
            padding: 1.5rem !important;
        }

        .table-responsive {
            font-size: 0.875rem;
        }

        .avatar-circle {
            width: 30px;
            height: 30px;
            font-size: 10px;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .toast-container .toast {
            min-width: 300px;
        }
    }

    /* Accessibility improvements */
    .btn:focus-visible,
    .form-control:focus-visible,
    .form-select:focus-visible {
        outline: 2px solid #007bff;
        outline-offset: 2px;
    }

    /* Print styles */
    @media print {
        .btn, .modal, .loading-overlay, .toast-container {
            display: none !important;
        }
        
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
        
        .summary-card {
            background: #f8f9fa !important;
            color: #212529 !important;
        }
    }
</style>