<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-journal-text me-2"></i>Laporan Harian</h1>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">

                    <!-- Filter Section -->
                    <div class="card mb-3 modern-card">
                        <div class="card-body pt-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="tanggal_filter" class="form-label">
                                        <i class="bi bi-calendar3 me-2"></i>Pilih Tanggal
                                    </label>
                                    <input type="date" wire:model="tanggal_filter" wire:change="tanggalChanged"
                                           class="form-control modern-input" id="tanggal_filter">
                                </div>
                                <div class="col-md-6">
                                    <div class="stats-card h-100">
                                        <div class="d-flex justify-content-between align-items-center h-100">
                                            <div>
                                                <small class="stats-label">Total Transaksi Hari Ini</small>
                                                <h4 class="stats-value mb-0">{{ count($reports) }} Data</h4>
                                            </div>
                                            <i class="bi bi-graph-up stats-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="card modern-card">
                        <div class="card-body">
                            <!-- Header dan Tombol Tambah -->
                            <div class="section-header">
                                <div class="section-title-wrapper">
                                    <h5 class="card-title section-title">
                                        <i class="bi bi-table me-2 text-primary"></i>
                                        Data Transaksi - {{ \Carbon\Carbon::parse($tanggal_filter)->format('d M Y') }}
                                    </h5>
                                </div>
                                <div class="action-buttons">
                                    <button class="btn btn-primary modern-btn" wire:click="openModal">
                                        <i class="bi bi-plus-circle me-2"></i>Tambah Data
                                    </button>
                                </div>
                            </div>

                            <!-- Tabel Laporan Harian -->
                            @if (!empty($reports) && count($reports) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered modern-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="25%">Keterangan</th>
                                                <th width="15%">Uang Masuk</th>
                                                <th width="15%">Uang Keluar</th>
                                                <th width="15%">Saldo</th>
                                                <th width="8%">Jenis</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reports as $index => $item)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                                    <td class="text-end money-{{ $item->uang_masuk > 0 ? 'positive' : 'neutral' }}">
                                                        Rp {{ number_format($item->uang_masuk, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end money-{{ $item->uang_keluar > 0 ? 'negative' : 'neutral' }}">
                                                        Rp {{ number_format($item->uang_keluar, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end money-{{ $item->saldo > 0 ? 'positive' : ($item->saldo < 0 ? 'negative' : 'neutral') }}">
                                                        Rp {{ number_format($item->saldo, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge modern-badge bg-{{ $item->jenis == 'Modal' ? 'modal' : 'laporan' }}">
                                                            {{ $item->jenis ?? 'Laporan' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($item->jenis !== 'Modal' && isset($item->raw_id))
                                                            <div class="btn-group" role="group">
                                                                <button class="btn btn-warning modern-btn btn-sm"
                                                                        wire:click="edit({{ $item->raw_id }})"
                                                                        title="Edit">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <button class="btn btn-danger modern-btn btn-sm"
                                                                        wire:click="confirmDelete({{ $item->raw_id }})"
                                                                        wire:confirm="Apakah Anda yakin ingin menghapus data ini?"
                                                                        title="Hapus">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <h5 class="empty-state-title">Belum ada data transaksi</h5>
                                    <p class="empty-state-text">
                                        Belum ada data transaksi untuk tanggal {{ \Carbon\Carbon::parse($tanggal_filter)->format('d M Y') }}.
                                    </p>
                                    <button class="btn btn-primary modern-btn" wire:click="openModal">
                                        <i class="bi bi-plus-circle me-2"></i>Tambah Data Pertama
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Form -->
        @if ($showModal)
            <div class="modal fade modern-modal show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form wire:submit.prevent="save">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="bi bi-{{ $isEdit ? 'pencil' : 'plus' }}-circle me-2"></i>
                                    {{ $isEdit ? 'Edit' : 'Tambah' }} Laporan Harian
                                </h5>
                                <button type="button" class="btn-close" wire:click="closeModal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="date" class="form-control @error('tanggal_input') is-invalid @enderror"
                                                   wire:model="tanggal_input" id="tanggal_input">
                                            <label for="tanggal_input">Tanggal *</label>
                                            @error('tanggal_input')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control @error('keterangan') is-invalid @enderror"
                                                   wire:model="keterangan" id="keterangan" placeholder="Masukkan keterangan">
                                            <label for="keterangan">Keterangan</label>
                                            @error('keterangan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="number" class="form-control @error('uang_masuk') is-invalid @enderror"
                                                   wire:model="uang_masuk" id="uang_masuk" min="0" step="0.01" placeholder="0">
                                            <label for="uang_masuk">Uang Masuk *</label>
                                            @error('uang_masuk')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="number" class="form-control @error('uang_keluar') is-invalid @enderror"
                                                   wire:model="uang_keluar" id="uang_keluar" min="0" step="0.01" placeholder="0">
                                            <label for="uang_keluar">Uang Keluar *</label>
                                            @error('uang_keluar')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                @if ($uang_masuk || $uang_keluar)
                                    <div class="alert alert-info alert-modern">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Selisih: 
                                                <span class="money-{{ (($uang_masuk ?? 0) - ($uang_keluar ?? 0)) > 0 ? 'positive' : ((($uang_masuk ?? 0) - ($uang_keluar ?? 0)) < 0 ? 'negative' : 'neutral') }}">
                                                    Rp {{ number_format(($uang_masuk ?? 0) - ($uang_keluar ?? 0), 0, ',', '.') }}
                                                </span>
                                            </strong>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary modern-btn" wire:click="closeModal">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </button>
                                <button type="submit" class="btn btn-primary modern-btn">
                                    <i class="bi bi-check-circle me-2"></i>{{ $isEdit ? 'Update' : 'Simpan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Loading indicator -->
        <div wire:loading class="loading-overlay">
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        @if (session()->has('message'))
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast toast-modern show" role="alert">
                    <div class="toast-header bg-success text-white">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong class="me-auto">Berhasil</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        {{ session('message') }}
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
                <div class="toast toast-modern show" role="alert">
                    <div class="toast-header bg-danger text-white">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <strong class="me-auto">Error</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif
    </main>

    <!-- CSS Styles -->
    <style>
        :root {
            --primary-color: #435ebe;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #fd7e14;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #495057;
            --border-radius: 8px;
            --border-radius-lg: 12px;
            --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --box-shadow-lg: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.15s ease-in-out;
        }

        /* Page Title */
        .pagetitle {
            margin-bottom: 1.5rem;
        }

        .pagetitle h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
            display: flex;
            align-items: center;
        }

        /* Modern Card */
        .modern-card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            overflow: hidden;
        }

        .modern-card:hover {
            box-shadow: var(--box-shadow-lg);
            transform: translateY(-1px);
        }

        .modern-card .card-body {
            padding: 1.5rem;
        }

        /* Filter Section */
        .modern-input {
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            padding: 0.5rem 0.75rem;
            transition: var(--transition);
        }

        .modern-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
            outline: 0;
        }

        /* Stats Card */
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #364574 100%);
            color: white;
            border-radius: var(--border-radius-lg);
            padding: 1.25rem;
            height: 100%;
            min-height: 80px;
            transition: var(--transition);
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow-lg);
        }

        .stats-label {
            font-size: 0.875rem;
            opacity: 0.9;
            display: block;
            margin-bottom: 0.25rem;
        }

        .stats-value {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .stats-icon {
            font-size: 2rem;
            opacity: 0.6;
        }

        /* Section Header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-title-wrapper {
            flex: 1;
            min-width: 250px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            color: var(--dark-color);
        }

        .action-buttons {
            flex-shrink: 0;
        }

        /* Modern Button */
        .modern-btn {
            border: none;
            border-radius: var(--border-radius);
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .modern-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--box-shadow);
        }

        .btn-primary.modern-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary.modern-btn:hover {
            background-color: #364574;
            color: white;
        }

        .btn-warning.modern-btn {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-warning.modern-btn:hover {
            background-color: #e86100;
            color: white;
        }

        .btn-danger.modern-btn {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger.modern-btn:hover {
            background-color: #b02a37;
            color: white;
        }

        .btn-secondary.modern-btn {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary.modern-btn:hover {
            background-color: #545b62;
            color: white;
        }

        /* Modern Table */
        .modern-table {
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 1px solid #dee2e6;
            margin: 0;
        }

        .modern-table thead th {
            background-color: var(--light-color);
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark-color);
            padding: 0.75rem;
        }

        .modern-table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        .modern-table tbody tr:hover {
            background-color: rgba(67, 94, 190, 0.05);
        }

        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge */
        .modern-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 70px;
            text-align: center;
            display: inline-block;
        }

        .bg-modal {
            background-color: var(--primary-color) !important;
            color: white;
        }

        .bg-laporan {
            background-color: var(--success-color) !important;
            color: white;
        }

        /* Money Colors */
        .money-positive {
            color: var(--success-color);
            font-weight: 500;
        }

        .money-negative {
            color: var(--danger-color);
            font-weight: 500;
        }

        .money-neutral {
            color: var(--dark-color);
            font-weight: 500;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6c757d;
        }

        .empty-state-icon i {
            font-size: 3rem;
            opacity: 0.5;
            margin-bottom: 1rem;
        }

        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .empty-state-text {
            margin-bottom: 1.5rem;
            color: #6c757d;
        }

        /* Modal */
        .modern-modal .modal-content {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-lg);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.125rem;
        }

        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
        }

        /* Form Floating */
        .form-floating .form-control {
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
        }

        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
        }

        .form-floating label {
            color: #6c757d;
        }

        /* Alert Modern */
        .alert-modern {
            border: none;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-top: 1rem;
        }

        .alert-info.alert-modern {
            background-color: rgba(13, 202, 240, 0.1);
            color: #055160;
            border-left: 4px solid var(--info-color);
        }

        /* Loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Toast */
        .toast-modern {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-lg);
        }

        /* Button Group */
        .btn-group .modern-btn {
            margin: 0;
        }

        .btn-group .modern-btn:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .btn-group .modern-btn:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                align-items: stretch;
            }

            .section-title-wrapper,
            .action-buttons {
                width: 100%;
            }

            .action-buttons {
                text-align: center;
            }

            .modern-table {
                font-size: 0.875rem;
            }

            .modern-table thead th,
            .modern-table tbody td {
                padding: 0.5rem 0.25rem;
            }

            .stats-card {
                margin-top: 1rem;
            }

            .btn-group {
                display: flex;
                flex-direction: column;
                width: 100%;
            }

            .btn-group .modern-btn {
                border-radius: var(--border-radius) !important;
                margin-bottom: 0.25rem;
            }

            .btn-group .modern-btn:last-child {
                margin-bottom: 0;
            }

            .empty-state {
                padding: 2rem 1rem;
            }
        }

        @media (max-width: 576px) {
            .pagetitle h1 {
                font-size: 1.5rem;
            }

            .modern-card .card-body {
                padding: 1rem;
            }

            .stats-value {
                font-size: 1.25rem;
            }

            .stats-icon {
                font-size: 1.5rem;
            }

            .modal-dialog {
                margin: 0.5rem;
            }

            .table-responsive {
                border-radius: var(--border-radius);
            }
        }
    </style>

    <!-- Auto-hide toasts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(function(toast) {
                setTimeout(function() {
                    toast.classList.remove('show');
                    setTimeout(function() {
                        toast.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</div>