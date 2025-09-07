<div data-year="{{ $tahun }}">
    <main id="main" class="main">
        <div class="pagetitle">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="mb-2">
                        <i class="bi bi-graph-up-arrow text-primary me-3"></i>
                        Laporan Tahunan
                    </h1>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item">Laporan</li>
                            <li class="breadcrumb-item active">Laporan Tahunan</li>
                        </ol>
                    </nav>
                    <p class="text-muted mb-0">Analisis komprehensif performa bisnis tahun {{ $tahun }}</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 me-3">
                        <i class="bi bi-calendar-event me-2"></i>
                        {{ $tahun }}
                    </div>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>
                        Cetak Laporan
                    </button>
                </div>
            </div>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Laporan</li>
                    <li class="breadcrumb-item active">Laporan Tahunan</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="row">
                <div class="col-12">
                    {{-- Filter Tahun --}}
                    <div class="card mb-4 border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                            <i class="bi bi-calendar-range text-white fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-white mb-1 fw-bold">Pilih Tahun Laporan</h5>
                                            <p class="text-white-50 mb-0">Pilih tahun untuk melihat analisis performa</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <input id="tahun-filter" type="number" wire:model.live="tahun"
                                            class="form-control form-control-lg border-0 shadow-sm"
                                            min="2000" max="{{ now()->year }}"
                                            placeholder="{{ $tahun ?? now()->year }}"
                                            style="background: rgba(255,255,255,0.95);">
                                        <i class="bi bi-calendar3 position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div wire:loading wire:target="tahun" class="text-center">
                                        <div class="spinner-border spinner-border-sm text-white" role="status">
                                            <span class="visually-hidden">Memuat...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Ringkasan Tahunan --}}
                    <style>
                        /* Styling untuk card ringkasan tahunan */
                        .info-card .card-body {
                            padding: 1.25rem;
                        }

                        .info-card .card-title {
                            font-size: 0.875rem;
                            font-weight: 600;
                            margin-bottom: 0.75rem;
                            color: #6c757d;
                        }

                        .info-card .card-icon {
                            width: 50px;
                            height: 50px;
                            flex-shrink: 0;
                        }

                        .info-card .ps-3 h6 {
                            font-size: 0.875rem;
                            font-weight: 700;
                            margin: 0;
                            line-height: 1.2;
                            word-wrap: break-word;
                            overflow-wrap: break-word;
                            hyphens: auto;
                            max-width: 100%;
                        }

                        /* Responsive text sizing */
                        @media (max-width: 1200px) {
                            .info-card .ps-3 h6 {
                                font-size: 0.8rem;
                            }
                        }

                        @media (max-width: 992px) {
                            .info-card .ps-3 h6 {
                                font-size: 0.75rem;
                            }
                        }

                        @media (max-width: 768px) {
                            .info-card .ps-3 h6 {
                                font-size: 0.7rem;
                            }
                        }

                        /* Stats card styling */
                        .stats-card .card-title {
                            font-size: 0.875rem;
                            font-weight: 600;
                            margin-bottom: 0.75rem;
                            color: #6c757d;
                        }

                        .stats-card .card-text {
                            font-size: 1rem !important;
                            font-weight: 700;
                            margin: 0;
                            line-height: 1.2;
                            word-wrap: break-word;
                            overflow-wrap: break-word;
                        }

                        @media (max-width: 992px) {
                            .stats-card .card-text {
                                font-size: 0.875rem !important;
                            }
                        }

                        @media (max-width: 768px) {
                            .stats-card .card-text {
                                font-size: 0.8rem !important;
                            }
                        }

                        /* Additional responsive fixes */
                        .info-card .d-flex {
                            min-width: 0;
                        }

                        .info-card .ps-3 {
                            min-width: 0;
                            flex: 1;
                        }

                        .info-card .ps-3 h6 {
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }

                        .stats-card .card-text {
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }

                        /* Ensure cards maintain proper height */
                        .info-card,
                        .stats-card {
                            height: 100%;
                        }

                        .info-card .card-body,
                        .stats-card .card-body {
                            display: flex;
                            flex-direction: column;
                            height: 100%;
                        }

                        .info-card .d-flex {
                            flex: 1;
                        }
                    </style>
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 shadow-lg h-100 bg-success text-white">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                            <i class="bi bi-cash-coin text-white fs-3"></i>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-white bg-opacity-20 text-white border-0">+100%</span>
                                        </div>
                                    </div>
                                    <h6 class="text-white-50 mb-2 fw-semibold">Total Pendapatan</h6>
                                    <h4 class="text-white mb-0 fw-bold">Rp{{ number_format($yearlySummary['pendapatan'] ?? 0, 0, ',', '.') }}</h4>
                                    <div class="mt-3">
                                        <div class="progress bg-white bg-opacity-20" style="height: 6px;">
                                            <div class="progress-bar bg-white" data-value="{{ $yearlySummary['pendapatan'] ?? 0 }}" data-max="{{ $yearlySummary['pendapatan'] ?? 1 }}" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 shadow-lg h-100 bg-warning text-white">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                            <i class="bi bi-cart-x text-white fs-3"></i>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-white bg-opacity-20 text-white border-0">Biaya</span>
                                        </div>
                                    </div>
                                    <h6 class="text-white-50 mb-2 fw-semibold">Biaya Variabel</h6>
                                    <h4 class="text-white mb-0 fw-bold">Rp{{ number_format($yearlySummary['pengeluaran_variabel'] ?? 0, 0, ',', '.') }}</h4>
                                    <div class="mt-3">
                                        <div class="progress bg-white bg-opacity-20" style="height: 6px;">
                                            <div class="progress-bar bg-white" data-value="{{ $yearlySummary['pengeluaran_variabel'] ?? 0 }}" data-max="{{ $yearlySummary['pendapatan'] ?? 1 }}" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 shadow-lg h-100 bg-info text-white">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                            <i class="bi bi-house-gear text-white fs-3"></i>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-white bg-opacity-20 text-white border-0">Tetap</span>
                                        </div>
                                    </div>
                                    <h6 class="text-white-50 mb-2 fw-semibold">Biaya Tetap</h6>
                                    <h4 class="text-white mb-0 fw-bold">Rp{{ number_format($yearlySummary['pengeluaran_tetap'] ?? 0, 0, ',', '.') }}</h4>
                                    <div class="mt-3">
                                        <div class="progress bg-white bg-opacity-20" style="height: 6px;">
                                            <div class="progress-bar bg-white" data-value="{{ $yearlySummary['pengeluaran_tetap'] ?? 0 }}" data-max="{{ $yearlySummary['pendapatan'] ?? 1 }}" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 shadow-lg h-100 {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'bg-primary' : 'bg-danger' }} text-white">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                            <i class="bi {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }} text-white fs-3"></i>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-white bg-opacity-20 text-white border-0">
                                                {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'Profit' : 'Loss' }}
                                            </span>
                                        </div>
                                    </div>
                                    <h6 class="text-white-50 mb-2 fw-semibold">Laba Bersih</h6>
                                    <h4 class="text-white mb-0 fw-bold">{{ ($yearlySummary['laba'] ?? 0) >= 0 ? '+' : '' }}Rp{{ number_format($yearlySummary['laba'] ?? 0, 0, ',', '.') }}</h4>
                                    <div class="mt-3">
                                        <div class="progress bg-white bg-opacity-20" style="height: 6px;">
                                            <div class="progress-bar bg-white" data-value="{{ abs($yearlySummary['laba'] ?? 0) }}" data-max="{{ $yearlySummary['pendapatan'] ?? 1 }}" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Analisis Utang dan Piutang Komprehensif --}}
                    <div class="card border-0 shadow-lg mt-4">
                        <div class="card-header bg-gradient-info text-white">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                    <i class="bi bi-credit-card text-white fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">Analisis Utang & Piutang Tahun {{ $tahun }}</h5>
                                    <small class="text-white-50">Ringkasan lengkap transaksi utang dan piutang</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            
                            {{-- Status Saat Ini --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-3">
                                    <div class="card border-danger border-2 h-100">
                                        <div class="card-body text-center p-3">
                                            <div class="text-danger mb-2">
                                                <i class="bi bi-exclamation-triangle fs-2"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Utang Aktif</h6>
                                            <h4 class="text-danger mb-1">Rp{{ number_format($currentDebtReceivableStatus['total_utang_aktif'] ?? 0, 0, ',', '.') }}</h4>
                                            <small class="text-muted">{{ $currentDebtReceivableStatus['jumlah_utang_aktif'] ?? 0 }} transaksi</small>
                                            @if(($currentDebtReceivableStatus['utang_overdue'] ?? 0) > 0)
                                                <div class="mt-2">
                                                    <span class="badge bg-danger">{{ $currentDebtReceivableStatus['utang_overdue'] }} Terlambat</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success border-2 h-100">
                                        <div class="card-body text-center p-3">
                                            <div class="text-success mb-2">
                                                <i class="bi bi-cash-stack fs-2"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Piutang Aktif</h6>
                                            <h4 class="text-success mb-1">Rp{{ number_format($currentDebtReceivableStatus['total_piutang_aktif'] ?? 0, 0, ',', '.') }}</h4>
                                            <small class="text-muted">{{ $currentDebtReceivableStatus['jumlah_piutang_aktif'] ?? 0 }} transaksi</small>
                                            @if(($currentDebtReceivableStatus['piutang_overdue'] ?? 0) > 0)
                                                <div class="mt-2">
                                                    <span class="badge bg-warning">{{ $currentDebtReceivableStatus['piutang_overdue'] }} Terlambat</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info border-2 h-100">
                                        <div class="card-body text-center p-3">
                                            <div class="text-info mb-2">
                                                <i class="bi bi-credit-card fs-2"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Utang Dibayar</h6>
                                            <h4 class="text-info mb-1">Rp{{ number_format($debtReceivableData['yearly_summary']['total_utang_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                                            <small class="text-muted">Tahun {{ $tahun }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning border-2 h-100">
                                        <div class="card-body text-center p-3">
                                            <div class="text-warning mb-2">
                                                <i class="bi bi-wallet2 fs-2"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Piutang Diterima</h6>
                                            <h4 class="text-warning mb-1">Rp{{ number_format($debtReceivableData['yearly_summary']['total_piutang_diterima'] ?? 0, 0, ',', '.') }}</h4>
                                            <small class="text-muted">Tahun {{ $tahun }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Ringkasan Tahunan --}}
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="text-primary mb-3">
                                                <i class="bi bi-plus-circle me-2"></i>Utang Baru vs Dibayar
                                            </h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h5 class="text-danger">Rp{{ number_format($debtReceivableData['yearly_summary']['total_utang_baru'] ?? 0, 0, ',', '.') }}</h5>
                                                        <small class="text-muted">Utang Baru</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h5 class="text-success">Rp{{ number_format($debtReceivableData['yearly_summary']['total_utang_dibayar'] ?? 0, 0, ',', '.') }}</h5>
                                                        <small class="text-muted">Utang Dibayar</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @php
                                                $netDebt = ($debtReceivableData['yearly_summary']['total_utang_baru'] ?? 0) - ($debtReceivableData['yearly_summary']['total_utang_dibayar'] ?? 0);
                                            @endphp
                                            <div class="text-center mt-3">
                                                <small class="text-muted">Net Utang: </small>
                                                <strong class="{{ $netDebt >= 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ $netDebt >= 0 ? '+' : '' }}Rp{{ number_format($netDebt, 0, ',', '.') }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="text-primary mb-3">
                                                <i class="bi bi-arrow-down-circle me-2"></i>Piutang Baru vs Diterima
                                            </h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h5 class="text-success">Rp{{ number_format($debtReceivableData['yearly_summary']['total_piutang_baru'] ?? 0, 0, ',', '.') }}</h5>
                                                        <small class="text-muted">Piutang Baru</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center">
                                                        <h5 class="text-warning">Rp{{ number_format($debtReceivableData['yearly_summary']['total_piutang_diterima'] ?? 0, 0, ',', '.') }}</h5>
                                                        <small class="text-muted">Piutang Diterima</small>
                                                    </div>
                                                </div>
                                            </div>
                                            @php
                                                $netReceivable = ($debtReceivableData['yearly_summary']['total_piutang_baru'] ?? 0) - ($debtReceivableData['yearly_summary']['total_piutang_diterima'] ?? 0);
                                            @endphp
                                            <div class="text-center mt-3">
                                                <small class="text-muted">Net Piutang: </small>
                                                <strong class="{{ $netReceivable >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $netReceivable >= 0 ? '+' : '' }}Rp{{ number_format($netReceivable, 0, ',', '.') }}
                                                </strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Detail Bulanan --}}
                            @if(isset($debtReceivableData['monthly_data']) && count($debtReceivableData['monthly_data']) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Bulan</th>
                                            <th class="text-end">Utang Baru</th>
                                            <th class="text-end">Utang Dibayar</th>
                                            <th class="text-end">Piutang Baru</th>
                                            <th class="text-end">Piutang Diterima</th>
                                            <th class="text-end">Net Cash Flow</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($debtReceivableData['monthly_data'] as $data)
                                        @php
                                            $netCashFlow = ($data['piutang_diterima'] - $data['utang_dibayar']) + ($data['utang_baru'] - $data['piutang_baru']);
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $data['bulan'] }}</td>
                                            <td class="text-end text-danger">
                                                {{ $data['utang_baru'] > 0 ? 'Rp' . number_format($data['utang_baru'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-end text-success">
                                                {{ $data['utang_dibayar'] > 0 ? 'Rp' . number_format($data['utang_dibayar'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-end text-info">
                                                {{ $data['piutang_baru'] > 0 ? 'Rp' . number_format($data['piutang_baru'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-end text-warning">
                                                {{ $data['piutang_diterima'] > 0 ? 'Rp' . number_format($data['piutang_diterima'], 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="text-end fw-bold {{ $netCashFlow >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $netCashFlow >= 0 ? '+' : '' }}Rp{{ number_format($netCashFlow, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                        </div>
                    </div>

                    {{-- Statistik Tambahan --}}
                    <div class="row g-4 mt-4">
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-lg h-100 bg-gradient-success text-white">
                                <div class="card-body p-4 text-center">
                                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                                        <i class="bi bi-calendar-check text-white fs-2"></i>
                                    </div>
                                    <h6 class="text-white-50 mb-2 fw-semibold">Rata-rata Pendapatan Bulanan</h6>
                                    <h3 class="text-white mb-0 fw-bold">Rp{{ number_format($stats['rata_rata_pendapatan'] ?? 0, 0, ',', '.') }}</h3>
                                    <div class="mt-3">
                                        <small class="text-white-50">Per bulan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-lg h-100 bg-gradient-warning text-white">
                                <div class="card-body p-4 text-center">
                                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                                        <i class="bi bi-cart-dash text-white fs-2"></i>
                                    </div>
                                    <h6 class="text-white-50 mb-2 fw-semibold">Rata-rata Biaya Variabel Bulanan</h6>
                                    <h3 class="text-white mb-0 fw-bold">Rp{{ number_format($stats['rata_rata_pengeluaran_variabel'] ?? 0, 0, ',', '.') }}</h3>
                                    <div class="mt-3">
                                        <small class="text-white-50">Per bulan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-lg h-100 bg-gradient-primary text-white">
                                <div class="card-body p-4 text-center">
                                    <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3">
                                        <i class="bi bi-trophy text-white fs-2"></i>
                                    </div>
                                    <h6 class="text-white-50 mb-2 fw-semibold">Bulan Laba Tertinggi</h6>
                                    <h3 class="text-white mb-0 fw-bold">{{ $stats['bulan_laba_tertinggi'] ?? 'N/A' }}</h3>
                                    <div class="mt-3">
                                        <small class="text-white-50">Rp{{ number_format($stats['laba_tertinggi'] ?? 0, 0, ',', '.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Grafik Garis Tren Bulanan --}}
                    <div class="card border-0 shadow-lg mt-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="bi bi-graph-up text-primary me-2"></i>
                                        Grafik Tren Bulanan
                                    </h5>
                                    <p class="text-muted mb-0">Analisis tren pendapatan dan pengeluaran tahun {{ $tahun }}</p>
                                </div>
                                <div class="badge bg-primary-subtle text-primary px-3 py-2">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $tahun }}
                                </div>
                            </div>
                            <div id="monthlyTrendChart"
                                data-pendapatan='{{ implode(',', $chartData['pendapatan'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }}'
                                data-pengeluaran-variabel='{{ implode(',', $chartData['pengeluaran_variabel'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }}'
                                data-pengeluaran-tetap='{{ implode(',', $chartData['pengeluaran_tetap'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }}'
                                data-pengeluaran='{{ implode(',', $chartData['pengeluaran'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }}'
                                data-laba='{{ implode(',', $chartData['laba'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }}'
                                data-categories='{{ implode(',', $chartData['categories'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']) }}'>
                            </div>
                        </div>
                    </div>

                    {{-- Perbandingan Tahunan Komprehensif --}}
                    <div class="card border-0 shadow-lg mt-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                                        <i class="bi bi-bar-chart text-white fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Analisis Perbandingan Tahunan</h5>
                                        <small class="text-white-50">Performa {{ $tahun }} vs {{ $tahun - 1 }}</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-white bg-opacity-20 px-3 py-2">
                                        <i class="bi bi-calendar-minus me-1"></i>
                                        {{ $tahun - 1 }}
                                    </span>
                                    <span class="badge bg-white px-3 py-2 text-primary">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        {{ $tahun }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            
                            {{-- Overall Performance Summary --}}
                            @if(isset($comparisonData['summary']['overall_performance']))
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-{{ $comparisonData['summary']['overall_performance']['color'] }} border-0 shadow-sm">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="{{ $comparisonData['summary']['overall_performance']['icon'] }} fs-2"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">Penilaian Performa Keseluruhan: {{ $comparisonData['summary']['overall_performance']['label'] }}</h6>
                                                <p class="mb-0">{{ $comparisonData['summary']['overall_performance']['description'] }}</p>
                                                <small class="opacity-75">
                                                    {{ $comparisonData['summary']['total_improvement_indicators'] ?? 0 }} indikator membaik, 
                                                    {{ $comparisonData['summary']['total_decline_indicators'] ?? 0 }} indikator menurun
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Growth Metrics Cards --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body text-center p-3">
                                            <div class="mb-2">
                                                <i class="bi bi-graph-up text-success fs-1"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Pertumbuhan Pendapatan</h6>
                                            @php
                                                $revenueGrowth = $comparisonData['growth']['pendapatan'] ?? 0;
                                                $revenueClass = $revenueGrowth >= 0 ? 'text-success' : 'text-danger';
                                                $revenueIcon = $revenueGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                            @endphp
                                            <h4 class="{{ $revenueClass }} mb-1">
                                                <i class="bi {{ $revenueIcon }} me-1"></i>
                                                {{ $revenueGrowth >= 0 ? '+' : '' }}{{ number_format($revenueGrowth, 1) }}%
                                            </h4>
                                            <small class="text-muted">
                                                Rp{{ number_format($comparisonData['current_year']['pendapatan'] ?? 0, 0, ',', '.') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body text-center p-3">
                                            <div class="mb-2">
                                                <i class="bi bi-trophy text-warning fs-1"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Pertumbuhan Laba</h6>
                                            @php
                                                $profitGrowth = $comparisonData['growth']['laba'] ?? 0;
                                                $profitClass = $profitGrowth >= 0 ? 'text-success' : 'text-danger';
                                                $profitIcon = $profitGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                            @endphp
                                            <h4 class="{{ $profitClass }} mb-1">
                                                <i class="bi {{ $profitIcon }} me-1"></i>
                                                {{ $profitGrowth >= 0 ? '+' : '' }}{{ number_format($profitGrowth, 1) }}%
                                            </h4>
                                            <small class="text-muted">
                                                Rp{{ number_format($comparisonData['current_year']['laba'] ?? 0, 0, ',', '.') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body text-center p-3">
                                            <div class="mb-2">
                                                <i class="bi bi-calculator text-danger fs-1"></i>
                                            </div>
                                            <h6 class="text-muted mb-1">Perubahan Pengeluaran</h6>
                                            @php
                                                $expenseGrowth = $comparisonData['growth']['pengeluaran'] ?? 0;
                                                // For expenses, lower growth is better
                                                $expenseClass = $expenseGrowth <= 0 ? 'text-success' : 'text-danger';
                                                $expenseIcon = $expenseGrowth >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                            @endphp
                                            <h4 class="{{ $expenseClass }} mb-1">
                                                <i class="bi {{ $expenseIcon }} me-1"></i>
                                                {{ $expenseGrowth >= 0 ? '+' : '' }}{{ number_format($expenseGrowth, 1) }}%
                                            </h4>
                                            <small class="text-muted">
                                                Rp{{ number_format($comparisonData['current_year']['pengeluaran'] ?? 0, 0, ',', '.') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Detailed Comparison Table --}}
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Kategori</th>
                                            <th class="text-end">{{ $tahun - 1 }}</th>
                                            <th class="text-end">{{ $tahun }}</th>
                                            <th class="text-center">Perubahan</th>
                                            <th class="text-center">Growth %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">
                                                <i class="bi bi-cash-coin text-success me-2"></i>Pendapatan
                                            </td>
                                            <td class="text-end">Rp{{ number_format($comparisonData['previous_year']['pendapatan'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['current_year']['pendapatan'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php 
                                                    $diff = ($comparisonData['current_year']['pendapatan'] ?? 0) - ($comparisonData['previous_year']['pendapatan'] ?? 0);
                                                    $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $diffClass }}">
                                                    {{ $diff >= 0 ? '+' : '' }}Rp{{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $growth = $comparisonData['growth']['pendapatan'] ?? 0; @endphp
                                                <span class="badge bg-{{ $growth >= 0 ? 'success' : 'danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">
                                                <i class="bi bi-cart-x text-warning me-2"></i>Biaya Variabel
                                            </td>
                                            <td class="text-end">Rp{{ number_format($comparisonData['previous_year']['pengeluaran_variabel'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['current_year']['pengeluaran_variabel'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php 
                                                    $diff = ($comparisonData['current_year']['pengeluaran_variabel'] ?? 0) - ($comparisonData['previous_year']['pengeluaran_variabel'] ?? 0);
                                                    $diffClass = $diff <= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $diffClass }}">
                                                    {{ $diff >= 0 ? '+' : '' }}Rp{{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $growth = $comparisonData['growth']['pengeluaran_variabel'] ?? 0; @endphp
                                                <span class="badge bg-{{ $growth <= 0 ? 'success' : 'danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">
                                                <i class="bi bi-house-gear text-info me-2"></i>Biaya Tetap
                                            </td>
                                            <td class="text-end">Rp{{ number_format($comparisonData['previous_year']['pengeluaran_tetap'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['current_year']['pengeluaran_tetap'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php 
                                                    $diff = ($comparisonData['current_year']['pengeluaran_tetap'] ?? 0) - ($comparisonData['previous_year']['pengeluaran_tetap'] ?? 0);
                                                    $diffClass = $diff <= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $diffClass }}">
                                                    {{ $diff >= 0 ? '+' : '' }}Rp{{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $growth = $comparisonData['growth']['pengeluaran_tetap'] ?? 0; @endphp
                                                <span class="badge bg-{{ $growth <= 0 ? 'success' : 'danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="table-secondary">
                                            <td class="fw-bold">
                                                <i class="bi bi-calculator text-danger me-2"></i>Total Pengeluaran
                                            </td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['previous_year']['pengeluaran'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['current_year']['pengeluaran'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php 
                                                    $diff = ($comparisonData['current_year']['pengeluaran'] ?? 0) - ($comparisonData['previous_year']['pengeluaran'] ?? 0);
                                                    $diffClass = $diff <= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $diffClass }} fw-bold">
                                                    {{ $diff >= 0 ? '+' : '' }}Rp{{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $growth = $comparisonData['growth']['pengeluaran'] ?? 0; @endphp
                                                <span class="badge bg-{{ $growth <= 0 ? 'success' : 'danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="table-success">
                                            <td class="fw-bold">
                                                <i class="bi bi-trophy text-warning me-2"></i>Laba Bersih
                                            </td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['previous_year']['laba'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['current_year']['laba'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php 
                                                    $diff = ($comparisonData['current_year']['laba'] ?? 0) - ($comparisonData['previous_year']['laba'] ?? 0);
                                                    $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $diffClass }} fw-bold">
                                                    {{ $diff >= 0 ? '+' : '' }}Rp{{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $growth = $comparisonData['growth']['laba'] ?? 0; @endphp
                                                <span class="badge bg-{{ $growth >= 0 ? 'success' : 'danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">
                                                <i class="bi bi-credit-card text-danger me-2"></i>Utang Dibayar
                                            </td>
                                            <td class="text-end">Rp{{ number_format($comparisonData['previous_year']['utang_dibayar'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['current_year']['utang_dibayar'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php 
                                                    $diff = ($comparisonData['current_year']['utang_dibayar'] ?? 0) - ($comparisonData['previous_year']['utang_dibayar'] ?? 0);
                                                    $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $diffClass }}">
                                                    {{ $diff >= 0 ? '+' : '' }}Rp{{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $growth = $comparisonData['growth']['utang_dibayar'] ?? 0; @endphp
                                                <span class="badge bg-{{ $growth >= 0 ? 'success' : 'danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">
                                                <i class="bi bi-cash-stack text-success me-2"></i>Piutang Diterima
                                            </td>
                                            <td class="text-end">Rp{{ number_format($comparisonData['previous_year']['piutang_diterima'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($comparisonData['current_year']['piutang_diterima'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @php 
                                                    $diff = ($comparisonData['current_year']['piutang_diterima'] ?? 0) - ($comparisonData['previous_year']['piutang_diterima'] ?? 0);
                                                    $diffClass = $diff >= 0 ? 'text-success' : 'text-danger';
                                                @endphp
                                                <span class="{{ $diffClass }}">
                                                    {{ $diff >= 0 ? '+' : '' }}Rp{{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $growth = $comparisonData['growth']['piutang_diterima'] ?? 0; @endphp
                                                <span class="badge bg-{{ $growth >= 0 ? 'success' : 'danger' }}">
                                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Chart Visualization --}}
                            <div class="mt-4">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-bar-chart me-2"></i>Visualisasi Perbandingan
                                </h6>
                                <div id="yearlyComparisonChart"
                                    data-previous-year-tahun='{{ $comparisonData['previous_year']['tahun'] ?? 'Tahun Sebelumnya' }}'
                                    data-previous-year-pendapatan='{{ $comparisonData['previous_year']['pendapatan'] ?? 0 }}'
                                    data-previous-year-pengeluaran-variabel='{{ $comparisonData['previous_year']['pengeluaran_variabel'] ?? 0 }}'
                                    data-previous-year-pengeluaran-tetap='{{ $comparisonData['previous_year']['pengeluaran_tetap'] ?? 0 }}'
                                    data-previous-year-pengeluaran='{{ $comparisonData['previous_year']['pengeluaran'] ?? 0 }}'
                                    data-previous-year-laba='{{ $comparisonData['previous_year']['laba'] ?? 0 }}'
                                    data-previous-year-utang-dibayar='{{ $comparisonData['previous_year']['utang_dibayar'] ?? 0 }}'
                                    data-previous-year-piutang-diterima='{{ $comparisonData['previous_year']['piutang_diterima'] ?? 0 }}'
                                    data-current-year-tahun='{{ $comparisonData['current_year']['tahun'] ?? 'Tahun Ini' }}'
                                    data-current-year-pendapatan='{{ $comparisonData['current_year']['pendapatan'] ?? 0 }}'
                                    data-current-year-pengeluaran-variabel='{{ $comparisonData['current_year']['pengeluaran_variabel'] ?? 0 }}'
                                    data-current-year-pengeluaran-tetap='{{ $comparisonData['current_year']['pengeluaran_tetap'] ?? 0 }}'
                                    data-current-year-pengeluaran='{{ $comparisonData['current_year']['pengeluaran'] ?? 0 }}'
                                    data-current-year-laba='{{ $comparisonData['current_year']['laba'] ?? 0 }}'
                                    data-current-year-utang-dibayar='{{ $comparisonData['current_year']['utang_dibayar'] ?? 0 }}'
                                    data-current-year-piutang-diterima='{{ $comparisonData['current_year']['piutang_diterima'] ?? 0 }}'>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Tabel Rekap Tahunan --}}
                    <div class="card border-0 shadow-lg mt-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <i class="bi bi-table text-info me-2"></i>
                                        Rekap Laporan Tahunan
                                    </h5>
                                    <p class="text-muted mb-0">Detail laporan bulanan tahun {{ $tahun }}</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button wire:click="refreshReport" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>
                                        Refresh
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="exportTableToCSV()">
                                        <i class="bi bi-download me-1"></i>
                                        Export CSV
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="exportTableToPDF()">
                                        <i class="bi bi-file-pdf me-1"></i>
                                        Export PDF
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered" id="reportTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center">
                                                <i class="bi bi-calendar-month me-1"></i>
                                                Bulan
                                            </th>
                                            <th scope="col" class="text-center text-success">
                                                <i class="bi bi-cash-coin me-1"></i>
                                                Pendapatan
                                            </th>
                                            <th scope="col" class="text-center text-warning">
                                                <i class="bi bi-cart-x me-1"></i>
                                                Biaya Variabel
                                            </th>
                                            <th scope="col" class="text-center text-info">
                                                <i class="bi bi-house-gear me-1"></i>
                                                Biaya Tetap
                                            </th>
                                            <th scope="col" class="text-center text-danger">
                                                <i class="bi bi-calculator me-1"></i>
                                                Total Pengeluaran
                                            </th>
                                            <th scope="col" class="text-center">
                                                <i class="bi bi-graph-up-arrow me-1"></i>
                                                Laba
                                            </th>
                                            <th scope="col" class="text-center text-danger">
                                                <i class="bi bi-credit-card me-1"></i>
                                                Utang Dibayar
                                            </th>
                                            <th scope="col" class="text-center text-success">
                                                <i class="bi bi-cash-stack me-1"></i>
                                                Piutang Diterima
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($laporan as $data)
                                        <tr>
                                            <td class="fw-semibold text-center">{{ $data['bulan'] }}</td>
                                            <td class="text-success fw-bold text-center">Rp{{ number_format($data['pendapatan'], 0, ',', '.') }}</td>
                                            <td class="text-warning fw-bold text-center">Rp{{ number_format($data['pengeluaran_variabel'], 0, ',', '.') }}</td>
                                            <td class="text-info fw-bold text-center">Rp{{ number_format($data['pengeluaran_tetap'], 0, ',', '.') }}</td>
                                            <td class="text-danger fw-bold text-center">Rp{{ number_format($data['pengeluaran'], 0, ',', '.') }}</td>
                                            <td class="fw-bold text-center {{ $data['laba'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $data['laba'] >= 0 ? '+' : '' }}Rp{{ number_format($data['laba'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-danger fw-bold text-center">Rp{{ number_format($data['utang_dibayar'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-success fw-bold text-center">Rp{{ number_format($data['piutang_diterima'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                <div class="py-4">
                                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                                    <h6 class="mt-3 text-muted">Tidak ada data laporan</h6>
                                                    <p class="text-muted mb-0">Tidak ada data laporan untuk tahun {{ $tahun }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                        @if (count($laporan) > 0)
                                        <tr class="fw-bold table-primary">
                                            <td class="text-center">Total</td>
                                            <td class="text-success text-center">Rp{{ number_format($yearlySummary['pendapatan'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-warning text-center">Rp{{ number_format($yearlySummary['pengeluaran_variabel'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-info text-center">Rp{{ number_format($yearlySummary['pengeluaran_tetap'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-danger text-center">Rp{{ number_format($yearlySummary['pengeluaran'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-center {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ ($yearlySummary['laba'] ?? 0) >= 0 ? '+' : '' }}Rp{{ number_format($yearlySummary['laba'] ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="text-danger text-center">Rp{{ number_format($yearlySummary['utang_dibayar'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="text-success text-center">Rp{{ number_format($yearlySummary['piutang_diterima'] ?? 0, 0, ',', '.') }}</td>
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

        {{-- Penjelasan untuk Pelaku UMKM Awam --}}
        <section class="bg-light py-5 mt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="text-center mb-4">
                                    <i class="bi bi-question-circle text-primary display-4"></i>
                                    <h4 class="mt-3 text-primary fw-bold">Panduan Memahami Laporan Keuangan</h4>
                                    <p class="text-muted">Penjelasan sederhana untuk membantu Anda memahami laporan keuangan UMKM</p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="h-100 p-3 border rounded bg-white">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="bi bi-lightbulb me-2"></i>
                                                Istilah Penting
                                            </h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <strong class="text-success">Pendapatan:</strong> Total uang yang masuk dari penjualan produk/jasa
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-warning">Pengeluaran Variabel:</strong> Biaya yang berubah sesuai jumlah produksi (bahan baku, tenaga kerja)
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-info">Pengeluaran Tetap:</strong> Biaya yang selalu sama setiap bulan (sewa, gaji tetap, listrik)
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-danger">Total Pengeluaran:</strong> Semua biaya yang dikeluarkan untuk operasional
                                                </li>
                                                <li class="mb-2">
                                                    <strong class="text-success">Laba:</strong> Pendapatan dikurangi pengeluaran (jika positif = untung, negatif = rugi)
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="h-100 p-3 border rounded bg-white">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="bi bi-calculator me-2"></i>
                                                Cara Membaca Laporan
                                            </h6>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <strong>Saldo Keuangan:</strong> Uang yang tersisa setelah semua pengeluaran
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Utang Dibayar:</strong> Total utang yang sudah dilunasi dalam periode tertentu
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Piutang Diterima:</strong> Total piutang yang sudah dibayar pelanggan
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Posisi Net:</strong> Selisih antara piutang dan utang (positif = lebih banyak piutang)
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Jatuh Tempo:</strong> Utang/piutang yang sudah melewati batas waktu pembayaran
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="p-3 border rounded bg-white">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="bi bi-graph-up me-2"></i>
                                                Tips Analisis Keuangan
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <div class="text-center p-3 border rounded h-100">
                                                        <i class="bi bi-arrow-up-circle text-success display-6"></i>
                                                        <h6 class="mt-2 text-success">Tren Positif</h6>
                                                        <small class="text-muted">Jika pendapatan naik dan pengeluaran turun, bisnis Anda berkembang dengan baik</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="text-center p-3 border rounded h-100">
                                                        <i class="bi bi-exclamation-triangle text-warning display-6"></i>
                                                        <h6 class="mt-2 text-warning">Perhatian</h6>
                                                        <small class="text-muted">Jika pengeluaran lebih besar dari pendapatan, perlu evaluasi biaya operasional</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="text-center p-3 border rounded h-100">
                                                        <i class="bi bi-cash-coin text-info display-6"></i>
                                                        <h6 class="mt-2 text-info">Manajemen Kas</h6>
                                                        <small class="text-muted">Selalu siapkan dana darurat untuk biaya tak terduga</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="alert alert-info border-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-info-circle me-3 mt-1"></i>
                                                <div>
                                                    <h6 class="alert-heading fw-bold">Penting untuk Diingat!</h6>
                                                    <p class="mb-2">Laporan keuangan adalah cermin kesehatan bisnis Anda. Periksa secara rutin untuk:</p>
                                                    <ul class="mb-0">
                                                        <li>Mengetahui apakah bisnis untung atau rugi</li>
                                                        <li>Mengidentifikasi biaya yang bisa ditekan</li>
                                                        <li>Merencanakan pengembangan bisnis</li>
                                                        <li>Menyiapkan dana untuk masa sulit</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
<link rel="stylesheet" href="{{ asset('assets/css/reporttahunan.css') }}">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="{{ asset('assets/js/reporttahunan-data.js') }}"></script>
<script src="{{ asset('assets/js/reporttahunan-charts.js') }}"></script>
<script src="{{ asset('assets/js/reporttahunan-export.js') }}"></script>
<script src="{{ asset('assets/js/reporttahunan-progress.js') }}"></script>

<script>
    document.addEventListener('livewire:init', () => {
        // Ambil data dari data attributes HTML menggunakan fungsi helper
        const data = getChartDataFromAttributes();
        if (!data) {
            console.error('Failed to get chart data');
            return;
        }

        const {
            chartData,
            comparisonData
        } = data;

        // Inisialisasi charts menggunakan file JavaScript terpisah
        const charts = initializeReportTahunanCharts(chartData, comparisonData);

        // Simpan referensi charts untuk update
        window.monthlyTrendChart = charts.monthlyTrendChart;
        window.yearlyComparisonChart = charts.yearlyComparisonChart;

        // Event listener untuk update data
        Livewire.on('reportUpdated', (data) => {
            updateReportTahunanCharts(data.chartData, data.comparisonData);
            // Update progress bars setelah chart update
            setTimeout(() => {
                if (window.updateProgressBars) {
                    window.updateProgressBars();
                }
            }, 200);
        });


    });
</script>