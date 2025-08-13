<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rugi Laba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        
        .card {
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: none;
        }
        
        .card-title {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .summary-card {
            transition: transform 0.2s;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
        }
        
        .progress-mini {
            height: 3px;
            border-radius: 2px;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
        }
        
        .debug-info {
            font-size: 0.8rem;
            color: #666;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Laporan Rugi Laba</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Laporan</li>
                <li class="breadcrumb-item active">Rugi Laba</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-graph-up"></i>
                    Analisis Kinerja Keuangan dan Profitabilitas
                </h5>

                {{-- Debug Info (hanya tampil di development) --}}
                @if (config('app.debug'))
                    <div class="debug-info">
                        <strong>Debug Info:</strong> 
                        Report Type: {{ $reportType }} | 
                        Selected Year: {{ $selectedYear ?: 'None' }} | 
                        Selected Month: {{ $selectedMonth ?: 'None' }} | 
                        User ID: {{ Auth::id() }}
                        <button wire:click="debugData" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="bi bi-bug"></i> Debug Log
                        </button>
                    </div>
                @endif

                {{-- Filter Section --}}
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="reportType">Jenis Laporan</label>
                        <select wire:model.live="reportType" class="form-control">
                            <option value="all">Semua Data</option>
                            <option value="yearly">Per Tahun</option>
                            <option value="monthly">Per Bulan</option>
                        </select>
                    </div>

                    @if (in_array($reportType, ['yearly', 'monthly']))
                        <div class="col-md-3">
                            <label for="selectedYear">Pilih Tahun</label>
                            <select wire:model.live="selectedYear" class="form-control">
                                <option value="">Pilih Tahun</option>
                                @forelse ($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @empty
                                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                                @endforelse
                            </select>
                            @if (empty($availableYears))
                                <small class="text-warning">Tidak ada data tahun tersedia</small>
                            @endif
                        </div>
                    @endif

                    @if ($reportType === 'monthly' && $selectedYear)
                        <div class="col-md-3">
                            <label for="selectedMonth">Pilih Bulan</label>
                            <select wire:model.live="selectedMonth" class="form-control">
                                <option value="">Pilih Bulan</option>
                                @foreach ($monthNames as $monthNumber => $monthName)
                                    <option value="{{ $monthNumber }}">{{ $monthName }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-3 d-flex align-items-end">
                        <button wire:click="toggleDetails" class="btn btn-info me-2">
                            <i class="bi bi-eye{{ $showDetails ? '-slash' : '' }}"></i>
                            {{ $showDetails ? 'Sembunyikan' : 'Tampilkan' }} Detail
                        </button>
                        <button wire:click="exportData" class="btn btn-success">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>

                {{-- Summary Cards with enhanced display --}}
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-success text-white summary-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small>Total Pendapatan</small>
                                        <h4 class="mb-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h4>
                                        <small>
                                            @if ($reportType === 'yearly' && $selectedYear)
                                                Tahun {{ $selectedYear }}
                                            @elseif($isMonthSelected)
                                                {{ $selectedMonthName }} {{ $selectedYear }}
                                            @else
                                                Semua Periode
                                            @endif
                                        </small>
                                        @if (config('app.debug'))
                                            <div class="debug-info mt-1">Raw: {{ $totalPendapatan }}</div>
                                        @endif
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-cash-stack display-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-danger text-white summary-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small>Total Pengeluaran</small>
                                        <h4 class="mb-1">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h4>
                                        <small>
                                            @if ($reportType === 'yearly' && $selectedYear)
                                                Tahun {{ $selectedYear }}
                                            @elseif($isMonthSelected)
                                                {{ $selectedMonthName }} {{ $selectedYear }}
                                            @else
                                                Semua Periode
                                            @endif
                                        </small>
                                        @if (config('app.debug'))
                                            <div class="debug-info mt-1">Raw: {{ $totalPengeluaran }}</div>
                                        @endif
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-credit-card display-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-{{ $labaRugi >= 0 ? 'primary' : 'warning' }} text-white summary-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small>{{ $labaRugi >= 0 ? 'Laba Bersih' : 'Rugi' }}</small>
                                        <h4 class="mb-1">
                                            {{ $labaRugi >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($labaRugi), 0, ',', '.') }}{{ $labaRugi < 0 ? ')' : '' }}
                                        </h4>
                                        <small>
                                            {{ $labaRugi >= 0 ? 'Menguntungkan' : 'Merugi' }}
                                        </small>
                                        @if (config('app.debug'))
                                            <div class="debug-info mt-1">Raw: {{ $labaRugi }}</div>
                                        @endif
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-{{ $labaRugi >= 0 ? 'graph-up' : 'graph-down' }}-arrow display-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-info text-white summary-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small>Margin Keuntungan</small>
                                        <h4 class="mb-1">{{ number_format($marginProfit, 1) }}%</h4>
                                        <small>
                                            @if ($marginProfit >= 20)
                                                Sangat Baik
                                            @elseif($marginProfit >= 10)
                                                Baik
                                            @elseif($marginProfit >= 5)
                                                Cukup
                                            @elseif($marginProfit > 0)
                                                Rendah
                                            @else
                                                Merugi
                                            @endif
                                        </small>
                                        @if (config('app.debug'))
                                            <div class="debug-info mt-1">Raw: {{ $marginProfit }}</div>
                                        @endif
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-percent display-6"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Status Indicator --}}
                <div class="row mt-3">
                    <div class="col-12">
                        @if ($totalPendapatan == 0 && $totalPengeluaran == 0)
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Tidak ada data ditemukan</strong>
                                @if ($reportType === 'yearly' && $selectedYear)
                                    untuk tahun {{ $selectedYear }}.
                                @elseif ($reportType === 'monthly' && $selectedYear && $selectedMonth)
                                    untuk {{ $selectedMonthName }} {{ $selectedYear }}.
                                @else
                                    untuk periode yang dipilih.
                                @endif
                                Silakan periksa filter atau tambahkan data pendapatan/pengeluaran terlebih dahulu.
                            </div>
                        @elseif ($totalPendapatan == 0)
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Tidak ada data <strong>pendapatan</strong> untuk periode yang dipilih.
                            </div>
                        @elseif ($totalPengeluaran == 0)
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Tidak ada data <strong>pengeluaran</strong> untuk periode yang dipilih.
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i>
                                Data berhasil dimuat untuk periode yang dipilih.
                                @if (config('app.debug'))
                                    <br><small>Yearly Data: {{ count($yearlyData) }} records | Monthly Data: {{ count($monthlyData) }} records</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Interactive Charts Section --}}
                @if (!empty($chartData['labels']) && (array_sum($chartData['pendapatan']) > 0 || array_sum($chartData['pengeluaran']) > 0))
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-bar-chart"></i>
                                Grafik Kinerja Keuangan
                                @if ($reportType === 'yearly' && $selectedYear)
                                    - {{ $selectedYear }}
                                @elseif ($reportType === 'all')
                                    - 5 Tahun Terakhir
                                @endif
                            </h6>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="chart-container">
                                        <canvas id="profitLossChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="chart-container">
                                        <canvas id="pieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @if ($totalPendapatan > 0 || $totalPengeluaran > 0)
                        <div class="card mt-4">
                            <div class="card-body text-center py-4">
                                <i class="bi bi-bar-chart display-1 text-muted"></i>
                                <h5 class="text-muted mt-2">Grafik tidak dapat ditampilkan</h5>
                                <p class="text-muted">Data chart tidak tersedia untuk periode ini</p>
                                @if (config('app.debug'))
                                    <small class="debug-info">
                                        Chart labels: {{ count($chartData['labels'] ?? []) }} | 
                                        Pendapatan sum: {{ array_sum($chartData['pendapatan'] ?? []) }} | 
                                        Pengeluaran sum: {{ array_sum($chartData['pengeluaran'] ?? []) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Growth Rate Card --}}
                @if ($reportType === 'yearly' && $selectedYear && $growthRate !== null)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-{{ $growthRate >= 0 ? 'success' : 'danger' }} text-center">
                                <h6 class="alert-heading">
                                    <i class="bi bi-trending-{{ $growthRate >= 0 ? 'up' : 'down' }}"></i>
                                    Pertumbuhan Pendapatan Tahun {{ $selectedYear }} dibanding {{ $selectedYear - 1 }}
                                </h6>
                                <h4 class="mb-0">
                                    {{ $growthRate >= 0 ? '+' : '' }}{{ number_format($growthRate, 1) }}%
                                </h4>
                                <small>
                                    @if ($growthRate >= 20)
                                        Pertumbuhan sangat tinggi
                                    @elseif($growthRate >= 10)
                                        Pertumbuhan tinggi
                                    @elseif($growthRate >= 5)
                                        Pertumbuhan sedang
                                    @elseif($growthRate > 0)
                                        Pertumbuhan rendah
                                    @elseif($growthRate == 0)
                                        Stagnan
                                    @else
                                        Mengalami penurunan
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Yearly Report Table --}}
                @if ($reportType === 'yearly' || $reportType === 'all')
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-calendar-range"></i>
                                Laporan Rugi Laba Per Tahun
                                @if (config('app.debug'))
                                    <small class="debug-info">({{ count($yearlyData) }} records)</small>
                                @endif
                            </h6>

                            @if (!empty($yearlyData))
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-primary">
                                            <tr class="text-center">
                                                <th>Tahun</th>
                                                <th>Total Pendapatan</th>
                                                <th>Total Pengeluaran</th>
                                                <th>Laba/Rugi</th>
                                                <th>Margin Keuntungan</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($yearlyData as $data)
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $data['year'] }}</td>
                                                    <td class="text-end text-success">
                                                        Rp {{ number_format($data['pendapatan'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-danger">
                                                        Rp {{ number_format($data['pengeluaran'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end {{ $data['laba_rugi'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                        {{ $data['laba_rugi'] >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($data['laba_rugi']), 0, ',', '.') }}{{ $data['laba_rugi'] < 0 ? ')' : '' }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $data['margin'] >= 10 ? 'success' : ($data['margin'] >= 5 ? 'warning' : ($data['margin'] > 0 ? 'info' : 'danger')) }}">
                                                            {{ number_format($data['margin'], 1) }}%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($data['laba_rugi'] >= 0)
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle"></i> Profit
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="bi bi-x-circle"></i> Loss
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox display-1 text-muted"></i>
                                    <p class="text-muted mt-2">Belum ada data untuk ditampilkan</p>
                                    @if (empty($availableYears))
                                        <small class="text-warning">Tidak ada data tahun yang tersedia di database</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Monthly Report Table --}}
                @if ($reportType === 'yearly' && $selectedYear && $showDetails)
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-calendar-month"></i>
                                Detail Bulanan Tahun {{ $selectedYear }}
                                @if (config('app.debug'))
                                    <small class="debug-info">({{ count($monthlyData) }} records)</small>
                                @endif
                            </h6>

                            @if (!empty($monthlyData))
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th>Bulan</th>
                                                <th>Pendapatan</th>
                                                <th>Pengeluaran</th>
                                                <th>Laba/Rugi</th>
                                                <th>Margin (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalYearlyIncome = 0;
                                                $totalYearlyExpenditure = 0;
                                            @endphp
                                            @foreach ($monthlyData as $data)
                                                @php
                                                    $totalYearlyIncome += $data['pendapatan'];
                                                    $totalYearlyExpenditure += $data['pengeluaran'];
                                                @endphp
                                                <tr>
                                                    <td class="fw-bold">{{ $data['month_name'] }}</td>
                                                    <td class="text-end text-success">
                                                        Rp {{ number_format($data['pendapatan'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-danger">
                                                        Rp {{ number_format($data['pengeluaran'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end {{ $data['laba_rugi'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                        {{ $data['laba_rugi'] >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($data['laba_rugi']), 0, ',', '.') }}{{ $data['laba_rugi'] < 0 ? ')' : '' }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $data['margin'] >= 10 ? 'success' : ($data['margin'] >= 5 ? 'warning' : ($data['margin'] > 0 ? 'info' : 'danger')) }}">
                                                            {{ number_format($data['margin'], 1) }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            {{-- Total Row --}}
                                            <tr class="table-secondary border-top border-2">
                                                <td class="fw-bold">TOTAL TAHUN {{ $selectedYear }}</td>
                                                <td class="text-end text-success fw-bold">
                                                    Rp {{ number_format($totalYearlyIncome, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end text-danger fw-bold">
                                                    Rp {{ number_format($totalYearlyExpenditure, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end {{ $totalYearlyIncome - $totalYearlyExpenditure >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                    @php $totalProfit = $totalYearlyIncome - $totalYearlyExpenditure; @endphp
                                                    {{ $totalProfit >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($totalProfit), 0, ',', '.') }}{{ $totalProfit < 0 ? ')' : '' }}
                                                </td>
                                                <td class="text-center">
                                                    @php $totalMargin = $totalYearlyIncome > 0 ? ($totalProfit / $totalYearlyIncome) * 100 : 0; @endphp
                                                    <span class="badge bg-{{ $totalMargin >= 10 ? 'success' : ($totalMargin >= 5 ? 'warning' : ($totalMargin > 0 ? 'info' : 'danger')) }}">
                                                        {{ number_format($totalMargin, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                                    <p class="text-muted mt-2">Tidak ada data bulanan untuk tahun {{ $selectedYear }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- KPI Cards --}}
                @if ($totalPendapatan > 0 || $totalPengeluaran > 0)
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-speedometer2"></i>
                                Key Performance Indicators (KPI)
                            </h6>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="bi bi-graph-up-arrow display-6 text-primary"></i>
                                        <h5 class="mt-2 mb-1">{{ number_format($marginProfit, 1) }}%</h5>
                                        <small class="text-muted">Profit Margin</small>
                                        <div class="progress progress-mini mt-2">
                                            <div class="progress-bar bg-{{ $marginProfit >= 15 ? 'success' : ($marginProfit >= 8 ? 'warning' : 'danger') }}"
                                                style="width: {{ min($marginProfit, 30) * (100 / 30) }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                @if ($totalPendapatan > 0)
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <i class="bi bi-pie-chart display-6 text-info"></i>
                                            <h5 class="mt-2 mb-1">{{ number_format(($totalPengeluaran / $totalPendapatan) * 100, 1) }}%</h5>
                                            <small class="text-muted">Expense Ratio</small>
                                            @php $expRatio = ($totalPengeluaran / $totalPendapatan) * 100; @endphp
                                            <div class="progress progress-mini mt-2">
                                                <div class="progress-bar bg-{{ $expRatio <= 70 ? 'success' : ($expRatio <= 85 ? 'warning' : 'danger') }}"
                                                    style="width: {{ min($expRatio, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($growthRate !== null)
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <i class="bi bi-trending-{{ $growthRate >= 0 ? 'up' : 'down' }} display-6 text-{{ $growthRate >= 0 ? 'success' : 'danger' }}"></i>
                                            <h5 class="mt-2 mb-1">{{ $growthRate >= 0 ? '+' : '' }}{{ number_format($growthRate, 1) }}%</h5>
                                            <small class="text-muted">Growth Rate</small>
                                            <div class="progress progress-mini mt-2">
                                                <div class="progress-bar bg-{{ $growthRate >= 10 ? 'success' : ($growthRate >= 0 ? 'info' : 'danger') }}"
                                                    style="width: {{ min(abs($growthRate), 50) * 2 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="bi bi-{{ $labaRugi >= 0 ? 'check-circle' : 'x-circle' }} display-6 text-{{ $labaRugi >= 0 ? 'success' : 'danger' }}"></i>
                                        <h5 class="mt-2 mb-1">{{ $labaRugi >= 0 ? 'Profit' : 'Loss' }}</h5>
                                        <small class="text-muted">Business Status</small>
                                        <div class="mt-2">
                                            <span class="badge bg-{{ $labaRugi >= 0 ? 'success' : 'danger' }}">
                                                {{ $labaRugi >= 0 ? 'Profitable' : 'Unprofitable' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Chart.js Implementation --}}
    @if (!empty($chartData['labels']) && (array_sum($chartData['pendapatan']) > 0 || array_sum($chartData['pengeluaran']) > 0))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data dari backend
            const chartData = @json($chartData);
            
            console.log('Chart data received:', chartData); // Debug log
            
            // Bar Chart - Profit Loss Comparison
            const ctx1 = document.getElementById('profitLossChart');
            if (ctx1) {
                new Chart(ctx1.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Pendapatan',
                            data: chartData.pendapatan,
                            backgroundColor: 'rgba(40, 167, 69, 0.8)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2
                        }, {
                            label: 'Pengeluaran',
                            data: chartData.pengeluaran,
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 2
                        }, {
                            label: 'Keuntungan/Kerugian',
                            data: chartData.profit,
                            type: 'line',
                            backgroundColor: 'rgba(13, 110, 253, 0.2)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Perbandingan Pendapatan vs Pengeluaran',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }

            // Pie Chart - Income vs Expenditure Ratio
            const totalIncome = chartData.pendapatan.reduce((a, b) => a + b, 0);
            const totalExpenditure = chartData.pengeluaran.reduce((a, b) => a + b, 0);
            
            if (totalIncome > 0 || totalExpenditure > 0) {
                const ctx2 = document.getElementById('pieChart');
                if (ctx2) {
                    new Chart(ctx2.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Pendapatan', 'Pengeluaran'],
                            datasets: [{
                                data: [totalIncome, totalExpenditure],
                                backgroundColor: [
                                    'rgba(40, 167, 69, 0.8)',
                                    'rgba(220, 53, 69, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(40, 167, 69, 1)',
                                    'rgba(220, 53, 69, 1)'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Proporsi Pendapatan vs Pengeluaran',
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                },
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            const percentage = ((value / (totalIncome + totalExpenditure)) * 100).toFixed(1);
                                            return label + ': Rp ' + new Intl.NumberFormat('id-ID').format(value) + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
    @endif

    <script>
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Debug function for Livewire
        window.debugProfitLoss = function() {
            console.log('Debug info:', {
                totalPendapatan: {{ $totalPendapatan }},
                totalPengeluaran: {{ $totalPengeluaran }},
                labaRugi: {{ $labaRugi }},
                reportType: '{{ $reportType }}',
                selectedYear: '{{ $selectedYear }}',
                selectedMonth: '{{ $selectedMonth }}',
                yearlyDataCount: {{ count($yearlyData) }},
                monthlyDataCount: {{ count($monthlyData) }}
            });
        };

        @if (config('app.debug'))
            // Auto debug di console saat development
            console.log('Profit Loss Debug Info:', {
                totalPendapatan: {{ $totalPendapatan }},
                totalPengeluaran: {{ $totalPengeluaran }},
                labaRugi: {{ $labaRugi }},
                reportType: '{{ $reportType }}',
                selectedYear: '{{ $selectedYear }}',
                selectedMonth: '{{ $selectedMonth }}',
                availableYears: @json($availableYears),
                chartData: @json($chartData)
            });
        @endif
    </script>
</main>
</body>
</html>