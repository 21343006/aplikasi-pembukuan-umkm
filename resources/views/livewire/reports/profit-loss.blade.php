<div>
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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

                    {{-- Filter Section --}}
                    <div class="row mt-3">
                        <div class="col-md-2">
                            <label for="reportType" class="form-label">Jenis Laporan</label>
                            <select wire:model.live="reportType" class="form-select">
                                <option value="all">Semua Data</option>
                                <option value="yearly">Per Tahun</option>
                                <option value="monthly">Per Bulan</option>
                            </select>
                        </div>

                        @if ($reportType !== 'all')
                            <div class="col-md-4">
                                <label for="selectedYear" class="form-label">Pilih Tahun</label>
                                <div class="input-group">
                                    <select wire:model.live="selectedYear" class="form-select">
                                        <option value="">Pilih Tahun</option>
                                        @foreach ($availableYears as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number"
                                           wire:model.blur="selectedYear"
                                           class="form-control"
                                           placeholder="atau ketik tahun"
                                           min="2000"
                                           max="2099"
                                           style="max-width: 150px;">
                                </div>
                                <small class="text-muted">Pilih dari dropdown atau ketik manual</small>
                            </div>
                        @endif

                        @if ($reportType === 'monthly')
                            <div class="col-md-2">
                                <label for="selectedMonth" class="form-label">Pilih Bulan</label>
                                <select wire:model.live="selectedMonth" class="form-select">
                                    <option value="">Pilih Bulan</option>
                                    @foreach ($monthNames as $key => $month)
                                        <option value="{{ $key }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-4 d-flex align-items-end">
                            @if ($reportType === 'yearly' && $selectedYear)
                                <button wire:click="toggleDetails" class="btn btn-info me-2">
                                    <i class="bi bi-{{ $showDetails ? 'eye-slash' : 'eye' }}"></i>
                                    {{ $showDetails ? 'Sembunyikan' : 'Tampilkan' }} Detail
                                </button>
                            @endif

                            @if (($reportType === 'yearly' && count($yearlyData) > 0) || ($reportType === 'monthly' && count($monthlyData) > 0))
                                <button wire:click="exportCSV" class="btn btn-success">
                                    <i class="bi bi-download"></i> Export CSV
                                </button>
                            @endif

                        </div>
                    </div>

                    {{-- Summary Cards --}}
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small>Total Pendapatan</small>
                                            <h4 class="mb-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h4>
                                            <small>
                                                @if ($reportType === 'yearly' && $selectedYear)
                                                    Tahun {{ $selectedYear }}
                                                @elseif($reportType === 'monthly' && $selectedYear && $selectedMonth)
                                                    {{ $selectedMonthName }} {{ $selectedYear }}
                                                @elseif($reportType === 'all')
                                                    Semua Periode
                                                @else
                                                    Pilih periode
                                                @endif
                                            </small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-cash-stack display-6"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small>Total Pengeluaran</small>
                                            <h4 class="mb-1">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h4>
                                            <small>
                                                @if ($reportType === 'yearly' && $selectedYear)
                                                    Tahun {{ $selectedYear }}
                                                @elseif($reportType === 'monthly' && $selectedYear && $selectedMonth)
                                                    {{ $selectedMonthName }} {{ $selectedYear }}
                                                @elseif($reportType === 'all')
                                                    Semua Periode
                                                @else
                                                    Pilih periode
                                                @endif
                                            </small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-credit-card display-6"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card {{ $labaRugi >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small>{{ $labaRugi >= 0 ? 'Laba Bersih' : 'Rugi' }}</small>
                                            <h4 class="mb-1">
                                                @if ($labaRugi >= 0)
                                                    Rp {{ number_format($labaRugi, 0, ',', '.') }}
                                                @else
                                                    (Rp {{ number_format(abs($labaRugi), 0, ',', '.') }})
                                                @endif
                                            </h4>
                                            <small>{{ $labaRugi >= 0 ? 'Menguntungkan' : 'Merugi' }}</small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-graph-{{ $labaRugi >= 0 ? 'up' : 'down' }}-arrow display-6"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-info text-white">
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
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-percent display-6"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Growth Rate Section --}}
                    @if ($reportType === 'yearly' && $selectedYear && $growthRate !== null)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-{{ $growthRate >= 0 ? 'success' : 'danger' }} text-center">
                                    <h6 class="alert-heading">
                                        <i class="bi bi-trending-{{ $growthRate >= 0 ? 'up' : 'down' }}"></i>
                                        Pertumbuhan Pendapatan Tahun {{ $selectedYear }} dibanding {{ $selectedYear - 1 }}
                                    </h6>
                                    <h4 class="mb-0">{{ $growthRate >= 0 ? '+' : '' }}{{ number_format($growthRate, 1) }}%</h4>
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

                    {{-- KPI Section --}}
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-speedometer2"></i>
                                Key Performance Indicators (KPI)
                            </h6>

                            <div class="row">
                                {{-- Profit Margin KPI --}}
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="bi bi-graph-up-arrow display-6 text-primary"></i>
                                        <h5 class="mt-2 mb-1">{{ number_format($marginProfit, 1) }}%</h5>
                                        <small class="text-muted">Profit Margin</small>
                                        <div class="progress mt-2" style="height: 3px;">
                                            <div class="progress-bar bg-{{ $marginProfit >= 15 ? 'success' : ($marginProfit >= 8 ? 'warning' : 'danger') }}"
                                                data-progress="{{ min($marginProfit, 30) * (100 / 30) }}"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Expense Ratio KPI --}}
                                @php
                                    $expenseRatio = $totalPendapatan > 0 ? ($totalPengeluaran / $totalPendapatan) * 100 : 0;
                                @endphp
                                <div class="col-md-3">
                                    <div class="text-center p-3 border rounded">
                                        <i class="bi bi-pie-chart display-6 text-info"></i>
                                        <h5 class="mt-2 mb-1">{{ number_format($expenseRatio, 1) }}%</h5>
                                        <small class="text-muted">Expense Ratio</small>
                                        <div class="progress mt-2" style="height: 3px;">
                                            <div class="progress-bar bg-{{ $expenseRatio <= 70 ? 'success' : ($expenseRatio <= 85 ? 'warning' : 'danger') }}"
                                                data-progress="{{ min($expenseRatio, 100) }}"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Growth Rate KPI --}}
                                @if ($growthRate !== null)
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <i class="bi bi-trending-{{ $growthRate >= 0 ? 'up' : 'down' }} display-6 text-{{ $growthRate >= 0 ? 'success' : 'danger' }}"></i>
                                            <h5 class="mt-2 mb-1">
                                                {{ $growthRate >= 0 ? '+' : '' }}{{ number_format($growthRate, 1) }}%
                                            </h5>
                                            <small class="text-muted">Growth Rate</small>
                                            <div class="progress mt-2" style="height: 3px;">
                                                <div class="progress-bar bg-{{ $growthRate >= 10 ? 'success' : ($growthRate >= 0 ? 'info' : 'danger') }}"
                                                    data-progress="{{ min(abs($growthRate), 50) * 2 }}"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Business Status KPI --}}
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

                    {{-- CHART SECTION --}}
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-bar-chart"></i>
                                Grafik Kinerja Keuangan
                                @php
                                    $chartTitle = '';
                                    switch($reportType) {
                                        case 'all':
                                            $chartTitle = '- Perbandingan Tahunan (Semua Data)';
                                            break;
                                        case 'yearly':
                                            $chartTitle = $selectedYear ? "- Bulanan Tahun {$selectedYear}" : '- Perbandingan Tahunan';
                                            break;
                                        case 'monthly':
                                            if($selectedYear && $selectedMonth) {
                                                $chartTitle = "- Harian {$selectedMonthName} {$selectedYear}";
                                            } elseif($selectedYear) {
                                                $chartTitle = "- Bulanan Tahun {$selectedYear}";
                                            } else {
                                                $chartTitle = '- Perbandingan Tahunan';
                                            }
                                            break;
                                    }
                                @endphp
                                {{ $chartTitle }}
                            </h6>

                            <div id="chart-container" class="mt-3" wire:ignore>
                                <div id="chart-area" class="row">
                                    <div class="col-lg-8 col-md-12">
                                        <div class="chart-wrapper" style="position: relative; height:400px;">
                                            <canvas id="barChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="chart-wrapper" style="position: relative; height:400px;">
                                            <canvas id="pieChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div id="no-chart-message" class="text-center py-5" style="display: none;">
                                    <i class="bi bi-bar-chart text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-3">Grafik Tidak Tersedia</h6>
                                    <p class="text-muted mb-0">Tidak ada data keuangan untuk periode yang dipilih.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Yearly Report Table --}}
                    @if (($reportType === 'all' || $reportType === 'yearly') && count($yearlyData) > 0)
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-calendar-range"></i>
                                    Laporan Rugi Laba Per Tahun
                                </h6>

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
                                                        @if ($data['laba_rugi'] >= 0)
                                                            Rp {{ number_format($data['laba_rugi'], 0, ',', '.') }}
                                                        @else
                                                            (Rp {{ number_format(abs($data['laba_rugi']), 0, ',', '.') }})
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $data['margin'] >= 10 ? 'success' : ($data['margin'] >= 5 ? 'warning' : ($data['margin'] > 0 ? 'info' : 'danger')) }}">
                                                            {{ number_format($data['margin'], 1) }}%
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $data['laba_rugi'] >= 0 ? 'success' : 'danger' }}">
                                                            <i class="bi bi-{{ $data['laba_rugi'] >= 0 ? 'check-circle' : 'x-circle' }}"></i>
                                                            {{ $data['laba_rugi'] >= 0 ? 'Profit' : 'Loss' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Monthly Report Table --}}
                    @if ($reportType === 'yearly' && $selectedYear && $showDetails && count($monthlyData) > 0)
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-calendar-month"></i>
                                    Detail Bulanan Tahun {{ $selectedYear }}
                                </h6>

                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th>Bulan</th>
                                                <th>Pendapatan</th>
                                                <th>Pengeluaran</th>
                                                <th>Biaya Tetap</th>
                                                <th>Laba/Rugi</th>
                                                <th>Margin (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalYearIncome = 0;
                                                $totalYearExpenditure = 0;
                                            @endphp

                                            @foreach ($monthlyData as $data)
                                                @php
                                                    $totalYearIncome += $data['pendapatan'];
                                                    $totalYearExpenditure += $data['pengeluaran'];
                                                @endphp
                                                <tr>
                                                    <td class="fw-bold">{{ $data['month_name'] }}</td>
                                                    <td class="text-end text-success">
                                                        Rp {{ number_format($data['pendapatan'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-danger">
                                                        Rp {{ number_format($data['pengeluaran'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-info">
                                                        Rp {{ number_format($data['biaya_tetap'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end {{ $data['laba_rugi'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                        @if ($data['laba_rugi'] >= 0)
                                                            Rp {{ number_format($data['laba_rugi'], 0, ',', '.') }}
                                                        @else
                                                            (Rp {{ number_format(abs($data['laba_rugi']), 0, ',', '.') }})
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $data['margin'] >= 10 ? 'success' : ($data['margin'] >= 5 ? 'warning' : ($data['margin'] > 0 ? 'info' : 'danger')) }}">
                                                            {{ number_format($data['margin'], 1) }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            {{-- Total Row --}}
                                            @php
                                                $totalYearProfit = $totalYearIncome - $totalYearExpenditure;
                                                $totalYearMargin = $totalYearIncome > 0 ? ($totalYearProfit / $totalYearIncome) * 100 : 0;
                                            @endphp
                                            <tr class="table-secondary border-top border-2">
                                                <td class="fw-bold" colspan="4">TOTAL TAHUN {{ $selectedYear }}</td>
                                                <td class="text-end text-success fw-bold">
                                                    Rp {{ number_format($totalYearIncome, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end text-danger fw-bold">
                                                    Rp {{ number_format($totalYearExpenditure, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end {{ $totalYearProfit >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                    @if ($totalYearProfit >= 0)
                                                        Rp {{ number_format($totalYearProfit, 0, ',', '.') }}
                                                    @else
                                                        (Rp {{ number_format(abs($totalYearProfit), 0, ',', '.') }})
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $totalYearMargin >= 10 ? 'success' : ($totalYearMargin >= 5 ? 'warning' : ($totalYearMargin > 0 ? 'info' : 'danger')) }}">
                                                        {{ number_format($totalYearMargin, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Monthly Data Table for Monthly Report Type --}}
                    @if ($reportType === 'monthly' && $selectedYear && count($monthlyData) > 0)
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-calendar-month"></i>
                                    Laporan Bulanan Tahun {{ $selectedYear }}
                                    @if($selectedMonth)
                                        - {{ $selectedMonthName }}
                                    @endif
                                </h6>

                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-info">
                                            <tr class="text-center">
                                                <th>{{ $selectedMonth ? 'Tanggal' : 'Bulan' }}</th>
                                                <th>Pendapatan</th>
                                                <th>Pengeluaran</th>
                                                <th>Laba/Rugi</th>
                                                <th>Margin (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($selectedMonth)
                                                {{-- Daily data for specific month --}}
                                                @forelse($dailyData as $data)
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $data['day'] }}</td>
                                                        <td class="text-end text-success">
                                                            Rp {{ number_format($data['pendapatan'], 0, ',', '.') }}
                                                        </td>
                                                        <td class="text-end text-danger">
                                                            Rp {{ number_format($data['pengeluaran'], 0, ',', '.') }}
                                                        </td>
                                                        <td class="text-end {{ $data['laba_rugi'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                            @if ($data['laba_rugi'] >= 0)
                                                                Rp {{ number_format($data['laba_rugi'], 0, ',', '.') }}
                                                            @else
                                                                (Rp {{ number_format(abs($data['laba_rugi']), 0, ',', '.') }})
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ $data['margin'] >= 10 ? 'success' : ($data['margin'] >= 5 ? 'warning' : ($data['margin'] > 0 ? 'info' : 'danger')) }}">
                                                                {{ number_format($data['margin'], 1) }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">Tidak ada transaksi pada bulan ini.</td>
                                                    </tr>
                                                @endforelse

                                                {{-- Monthly Total Row --}}
                                                <tr class="table-secondary border-top border-2">
                                                    <td class="fw-bold">TOTAL {{ strtoupper($selectedMonthName) }}</td>
                                                    <td class="text-end text-success fw-bold">
                                                        Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-danger fw-bold">
                                                        Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end {{ $labaRugi >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                        @if ($labaRugi >= 0)
                                                            Rp {{ number_format($labaRugi, 0, ',', '.') }}
                                                        @else
                                                            (Rp {{ number_format(abs($labaRugi), 0, ',', '.') }})
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $marginProfit >= 10 ? 'success' : ($marginProfit >= 5 ? 'warning' : ($marginProfit > 0 ? 'info' : 'danger')) }}">
                                                            {{ number_format($marginProfit, 1) }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @else
                                                {{-- Monthly data when only year is selected --}}
                                                @php
                                                    $yearlyTotal = ['pendapatan' => 0, 'pengeluaran' => 0];
                                                @endphp

                                                @foreach ($monthlyData as $data)
                                                    @php
                                                        $yearlyTotal['pendapatan'] += $data['pendapatan'];
                                                        $yearlyTotal['pengeluaran'] += $data['pengeluaran'];
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
                                                            @if ($data['laba_rugi'] >= 0)
                                                                Rp {{ number_format($data['laba_rugi'], 0, ',', '.') }}
                                                            @else
                                                                (Rp {{ number_format(abs($data['laba_rugi']), 0, ',', '.') }})
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ $data['margin'] >= 10 ? 'success' : ($data['margin'] >= 5 ? 'warning' : ($data['margin'] > 0 ? 'info' : 'danger')) }}">
                                                                {{ number_format($data['margin'], 1) }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                {{-- Yearly Total Row --}}
                                                @php
                                                    $yearlyProfit = $yearlyTotal['pendapatan'] - $yearlyTotal['pengeluaran'];
                                                    $yearlyMargin = $yearlyTotal['pendapatan'] > 0 ? ($yearlyProfit / $yearlyTotal['pendapatan']) * 100 : 0;
                                                @endphp
                                                <tr class="table-secondary border-top border-2">
                                                    <td class="fw-bold">TOTAL TAHUN {{ $selectedYear }}</td>
                                                    <td class="text-end text-success fw-bold">
                                                        Rp {{ number_format($yearlyTotal['pendapatan'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-danger fw-bold">
                                                        Rp {{ number_format($yearlyTotal['pengeluaran'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end {{ $yearlyProfit >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                        @if ($yearlyProfit >= 0)
                                                            Rp {{ number_format($yearlyProfit, 0, ',', '.') }}
                                                        @else
                                                            (Rp {{ number_format(abs($yearlyProfit), 0, ',', '.') }})
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $yearlyMargin >= 10 ? 'success' : ($yearlyMargin >= 5 ? 'warning' : ($yearlyMargin > 0 ? 'info' : 'danger')) }}">
                                                            {{ number_format($yearlyMargin, 1) }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- No Data Message --}}
                    @if (
                        $reportType !== 'all' &&
                            (($reportType === 'yearly' && count($yearlyData) === 0) ||
                                ($reportType === 'monthly' && count($monthlyData) === 0)))
                        <div class="alert alert-warning mt-4 text-center">
                            <i class="bi bi-exclamation-triangle"></i>
                            Tidak ada data ditemukan untuk periode yang dipilih.
                            @if ($reportType === 'yearly' && !$selectedYear)
                                Silakan pilih tahun terlebih dahulu.
                            @elseif($reportType === 'monthly' && (!$selectedYear || !$selectedMonth))
                                Silakan pilih tahun dan bulan terlebih dahulu.
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </section>
    </main>

    {{-- Final, 100% Working Chart Script --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.js"></script>
    <div id="chart-data" 
         data-chartdata="{{ json_encode($chartData) }}"
         data-hasvaliddata="{{ json_encode($hasValidChartData) }}"
         style="display: none;"></div>
    <script>
        document.addEventListener('livewire:init', () => {

            const ChartManager = {
                barChart: null,
                pieChart: null,
                
                elements: {
                    chartArea: document.getElementById('chart-area'),
                    barCanvas: document.getElementById('barChart'),
                    pieCanvas: document.getElementById('pieChart'),
                    noDataMessage: document.getElementById('no-chart-message'),
                },

                init(chartData, hasValidData) {
                    this.update(chartData, hasValidData);
                },

                update(chartData, hasValidData) {

                    this.destroy();

                    if (!hasValidData) {
                        this.showNoDataMessage();
                        return;
                    }

                    this.hideNoDataMessage();
                    this.create(chartData);
                },

                destroy() {
                    if (this.barChart) {
                        this.barChart.destroy();
                        this.barChart = null;
                    }
                    if (this.pieChart) {
                        this.pieChart.destroy();
                        this.pieChart = null;
                    }
                },

                showNoDataMessage() {
                    this.elements.chartArea.style.display = 'none';
                    this.elements.noDataMessage.style.display = 'block';
                },

                hideNoDataMessage() {
                    this.elements.chartArea.style.display = 'flex'; // Use flex for row layout
                    this.elements.noDataMessage.style.display = 'none';
                },

                create(data) {
                    this.createBarChart(data);
                    this.createPieChart(data);
                },

                createBarChart(data) {
                    if (!this.elements.barCanvas) {
                        console.error('❌ Bar Chart canvas not found!');
                        return;
                    }
                    const ctx = this.elements.barCanvas.getContext('2d');
                    this.barChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Pendapatan',
                                data: data.pendapatan,
                                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 2,
                                order: 2
                            }, {
                                label: 'Pengeluaran',
                                data: data.pengeluaran,
                                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 2,
                                order: 3
                            }, {
                                label: 'Laba/Rugi',
                                data: data.profit,
                                type: 'line',
                                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                                borderColor: 'rgba(13, 110, 253, 1)',
                                borderWidth: 3,
                                fill: false,
                                tension: 0.4,
                                order: 1,
                                yAxisID: 'y1'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: { duration: 500 },
                            plugins: {
                                title: { display: true, text: 'Kinerja Keuangan' },
                                legend: { position: 'top' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) { label += ': '; }
                                            const value = context.parsed.y;
                                            const formattedValue = 'Rp ' + Math.abs(value).toLocaleString('id-ID');
                                            return label + (value < 0 ? '(' + formattedValue + ')' : formattedValue);
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    beginAtZero: true,
                                    ticks: { 
                                        callback: (value) => 'Rp ' + Math.abs(value).toLocaleString('id-ID')
                                    },
                                    title: { display: true, text: 'Pendapatan & Pengeluaran' }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    ticks: { 
                                        callback: (value) => 'Rp ' + Math.abs(value).toLocaleString('id-ID')
                                    },
                                    title: { display: true, text: 'Laba/Rugi' }
                                }
                            },
                            interaction: { 
                                mode: 'index', 
                                intersect: false 
                            }
                        }
                    });
                },
            };

            // Initialize chart with the data from the initial page load
            const chartDataElement = document.getElementById('chart-data');
            const chartData = chartDataElement ? JSON.parse(chartDataElement.dataset.chartdata || '{}') : {};
            const hasValidChartData = chartDataElement ? JSON.parse(chartDataElement.dataset.hasvaliddata || 'false') : false;
            ChartManager.init(chartData, hasValidChartData);

            // Listen for updates from the Livewire component
            Livewire.on('update-chart', ({chartData, hasValidChartData}) => {
                ChartManager.update(chartData, hasValidChartData);
            });

            // Set progress bar widths using data attributes
            const progressBars = document.querySelectorAll('.progress-bar[data-progress]');
            progressBars.forEach(function(bar) {
                const progress = parseFloat(bar.getAttribute('data-progress')) || 0;
                bar.style.width = Math.min(100, Math.max(0, progress)) + '%';
            });
        });
    </script>


    {{-- Penjelasan untuk Pelaku UMKM Awam --}}
    <section class="bg-light py-5 mt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-graph-up text-primary display-4"></i>
                                <h4 class="mt-3 text-primary fw-bold">Panduan Laporan Laba Rugi</h4>
                                <p class="text-muted">Penjelasan sederhana untuk memahami untung rugi bisnis UMKM</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Komponen Pendapatan
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-success">Pendapatan:</strong> Total uang dari penjualan produk/jasa
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-success">Modal Awal:</strong> Uang yang disetorkan sebagai modal
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-success">Total Pemasukan:</strong> Pendapatan + Modal Awal
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-dash-circle me-2"></i>
                                            Komponen Pengeluaran
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-danger">Biaya Variabel:</strong> Biaya yang berubah sesuai produksi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-danger">Biaya Tetap:</strong> Biaya yang selalu sama setiap bulan
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-danger">Total Pengeluaran:</strong> Semua biaya operasional
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-calculator me-2"></i>
                                            Cara Menghitung Laba/Rugi
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-arrow-up-circle text-success display-6"></i>
                                                    <h6 class="mt-2 text-success">Laba (Untung)</h6>
                                                    <p class="mb-2"><strong>Rumus:</strong></p>
                                                    <p class="mb-2">Pendapatan > Pengeluaran</p>
                                                    <small class="text-muted">Bisnis Anda menghasilkan keuntungan</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-arrow-down-circle text-danger display-6"></i>
                                                    <h6 class="mt-2 text-danger">Rugi</h6>
                                                    <p class="mb-2"><strong>Rumus:</strong></p>
                                                    <p class="mb-2">Pendapatan < Pengeluaran</p>
                                                    <small class="text-muted">Bisnis Anda mengalami kerugian</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-dash-circle text-warning display-6"></i>
                                                    <h6 class="mt-2 text-warning">Break Even</h6>
                                                    <p class="mb-2"><strong>Rumus:</strong></p>
                                                    <p class="mb-2">Pendapatan = Pengeluaran</p>
                                                    <small class="text-muted">Tidak untung tidak rugi</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-success border-0">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-lightbulb me-3 mt-1"></i>
                                            <div>
                                                <h6 class="alert-heading fw-bold">Tips Meningkatkan Laba!</h6>
                                                <ul class="mb-0">
                                                    <li><strong>Naikkan Pendapatan:</strong> Tingkatkan penjualan atau harga jual</li>
                                                    <li><strong>Turunkan Biaya:</strong> Cari supplier yang lebih murah</li>
                                                    <li><strong>Efisiensi:</strong> Kurangi biaya yang tidak perlu</li>
                                                    <li><strong>Monitoring:</strong> Periksa laporan laba rugi setiap bulan</li>
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
</div>
