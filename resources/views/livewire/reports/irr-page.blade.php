<main id="main" class="main">
    <div class="pagetitle">
        <h1>Internal Rate of Return (IRR)</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Analisis IRR</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-graph-up"></i> Analisis Kelayakan Investasi dan Proyeksi Return
                </h5>

                {{-- Filter Section --}}
                <div class="row mt-3">
                    <div class="col-md-2">
                        <label for="filterYear" class="form-label">Tahun Analisis</label>
                        <select wire:model.live="filterYear" class="form-select">
                            <option value="">Pilih Tahun</option>
                            @for($year = 2020; $year <= now()->year + 2; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="discountRate" class="form-label">Discount Rate (%)</label>
                        <input type="number" wire:model.live="discountRate" step="0.1" min="0" max="100" 
                               class="form-control" placeholder="12">
                    </div>
                    <div class="col-md-2">
                        <label for="growthRate" class="form-label">Growth Rate (%)</label>
                        <input type="number" wire:model.live="growthRate" step="0.1" min="0" max="50" 
                               class="form-control" placeholder="5">
                    </div>
                    <div class="col-md-2">
                        <label for="inflationRate" class="form-label">Inflation Rate (%)</label>
                        <input type="number" wire:model.live="inflationRate" step="0.1" min="0" max="20" 
                               class="form-control" placeholder="3">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button wire:click="toggleCalculation" class="btn btn-info btn-sm">
                            <i class="bi bi-calculator"></i>
                            {{ $showCalculation ? 'Sembunyikan' : 'Tampilkan' }} Detail
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                        <button wire:click="exportData" class="btn btn-success btn-sm">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>

                @if($filterYear)
                    {{-- Key Metrics Cards --}}
                    <div class="row mt-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">Internal Rate of Return</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-graph-up-arrow"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>
                                                @if($irr !== null)
                                                    {{ number_format($irr, 2) }}%
                                                @else
                                                    N/A
                                                @endif
                                            </h6>
                                            <span class="{{ $this->getIrrStatus()['class'] ?? '' }}">
                                                {{ $this->getIrrStatus()['message'] ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card info-card customers-card">
                                <div class="card-body">
                                    <h5 class="card-title">Net Present Value</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>
                                                @if($npv !== null)
                                                    Rp {{ number_format($npv / 1000000, 1) }}M
                                                @else
                                                    N/A
                                                @endif
                                            </h6>
                                            <span class="{{ $this->getNpvStatus()['class'] ?? '' }}">
                                                {{ $this->getNpvStatus()['message'] ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card info-card revenue-card">
                                <div class="card-body">
                                    <h5 class="card-title">Payback Period</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>
                                                @if($paybackPeriod !== null)
                                                    {{ number_format($paybackPeriod, 1) }}
                                                @else
                                                    N/A
                                                @endif
                                            </h6>
                                            <span class="{{ $this->getPaybackStatus()['class'] ?? '' }}">
                                                {{ $this->getPaybackStatus()['message'] ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <h5 class="card-title">Profitability Index</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-percent"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>
                                                @if($profitabilityIndex !== null)
                                                    {{ number_format($profitabilityIndex, 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </h6>
                                            <span class="{{ $this->getProfitabilityIndexStatus()['class'] ?? '' }}">
                                                {{ $this->getProfitabilityIndexStatus()['message'] ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Investment Summary --}}
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-bar-chart-line"></i>
                                Ringkasan Investasi Tahun {{ $filterYear }}
                            </h6>
                            
                            <div class="row">
                                <div class="col-md">
                                    <div class="alert alert-light border text-center">
                                        <strong>Modal Awal</strong><br>
                                        <span class="h5 text-dark">Rp {{ number_format($modalAwal, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="alert alert-success text-center">
                                        <strong>Total Pendapatan</strong><br>
                                        <span class="h5">Rp {{ number_format($this->getTotalIncome(), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="alert alert-danger text-center">
                                        <strong>Total Pengeluaran</strong><br>
                                        <span class="h5">Rp {{ number_format($this->getTotalExpenditure(), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="alert alert-warning text-center">
                                        <strong>Total Biaya Tetap</strong><br>
                                        <span class="h5">Rp {{ number_format($this->getTotalFixedCost(), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="alert alert-info text-center">
                                        <strong>Total Modal Tambahan</strong><br>
                                        <span class="h5">Rp {{ number_format($this->getTotalCapital(), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="alert alert-{{ $this->getTotalNetCashFlow() >= 0 ? 'success' : 'danger' }} text-center">
                                        <strong>Net Cash Flow</strong><br>
                                        <span class="h5">Rp {{ number_format($this->getTotalNetCashFlow(), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Yearly Projection Table --}}
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-calendar-range"></i>
                                Proyeksi Cash Flow Tahunan ({{ $projectionYears }} Tahun)
                            </h6>
                            
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-primary">
                                        <tr class="text-center">
                                            <th width="20%">TAHUN</th>
                                            <th width="40%">CASH FLOW</th>
                                            <th width="40%">KETERANGAN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center fw-bold">0</td>
                                            <td class="text-end text-danger fw-bold">
                                                - Rp {{ number_format($modalAwal, 0, ',', '.') }}
                                            </td>
                                            <td class="text-muted">Initial Investment</td>
                                        </tr>
                                        @for($year = 1; $year <= $projectionYears; $year++)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $year }}</td>
                                                <td class="text-end {{ $this->getYearlyCashFlow($year) >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                    {{ $this->getYearlyCashFlow($year) >= 0 ? 'Rp ' : '- Rp ' }}{{ number_format(abs($this->getYearlyCashFlow($year)), 0, ',', '.') }}
                                                </td>
                                                <td class="text-muted">
                                                    @if($year == 1)
                                                        Data Aktual Tahun {{ $filterYear }}
                                                    @else
                                                        Proyeksi (Growth: {{ $growthRate }}%, Inflation: {{ $inflationRate }}%)
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <h6 class="fw-bold">
                                            <i class="bi bi-calculator"></i> Parameter Perhitungan
                                        </h6>
                                        <p class="mb-1">
                                            <strong>Discount Rate:</strong> {{ $discountRate }}% (Risk-free: {{ $riskFreeRate }}% + Risk Premium: {{ $riskPremium }}%)
                                        </p>
                                        <p class="mb-1">
                                            <strong>Growth Rate:</strong> {{ $growthRate }}% per tahun
                                        </p>
                                        <p class="mb-0">
                                            <strong>Inflation Rate:</strong> {{ $inflationRate }}% per tahun
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning">
                                        <h6 class="fw-bold">
                                            <i class="bi bi-info-circle"></i> Keterangan
                                        </h6>
                                        <p class="mb-0 small">
                                            <strong>Cash Flow Negatif:</strong> Uang keluar/modal<br>
                                            <strong>Cash Flow Positif:</strong> Uang masuk/pendapatan<br>
                                            <strong>IRR:</strong> Tingkat pengembalian internal yang membuat NPV = 0
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Monthly Cash Flow Table --}}
                    @if($showCalculation && !empty($monthlyData))
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-table"></i>
                                    Detail Cash Flow Bulanan
                                </h6>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Bulan</th>
                                                <th class="text-end">Pendapatan</th>
                                                <th class="text-end">Pengeluaran</th>
                                                <th class="text-end">Biaya Tetap</th>
                                                <th class="text-end">Modal Tambahan</th>
                                                <th class="text-end">Net Cash Flow</th>
                                                <th class="text-end">Cumulative</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Initial Investment Row --}}
                                            <tr class="table-secondary">
                                                <td><strong>Initial Investment</strong></td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end">-</td>
                                                <td class="text-end text-danger">
                                                    <strong>(Rp {{ number_format($modalAwal, 0, ',', '.') }})</strong>
                                                </td>
                                                <td class="text-end text-danger">
                                                    <strong>(Rp {{ number_format($modalAwal, 0, ',', '.') }})</strong>
                                                </td>
                                            </tr>
                                            
                                            @php $cumulative = -$modalAwal; @endphp
                                            @foreach($monthlyData as $data)
                                                @php $cumulative += $data['net_cash_flow']; @endphp
                                                <tr>
                                                    <td><strong>{{ $data['month_name'] }}</strong></td>
                                                    <td class="text-end text-success">
                                                        Rp {{ number_format($data['income'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-danger">
                                                        Rp {{ number_format($data['expenditure'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-warning">
                                                        Rp {{ number_format($data['fixed_cost'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end text-info">
                                                        Rp {{ number_format($data['capital'], 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-end {{ $data['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                        <strong>
                                                            {{ $data['net_cash_flow'] >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($data['net_cash_flow']), 0, ',', '.') }}{{ $data['net_cash_flow'] < 0 ? ')' : '' }}
                                                        </strong>
                                                    </td>
                                                    <td class="text-end {{ $cumulative >= 0 ? 'text-success' : 'text-danger' }}">
                                                        <strong>
                                                            {{ $cumulative >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($cumulative), 0, ',', '.') }}{{ $cumulative < 0 ? ')' : '' }}
                                                        </strong>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            
                                            {{-- Summary Row --}}
                                            <tr class="table-light border-top border-2">
                                                <td><strong>TOTAL</strong></td>
                                                <td class="text-end text-success">
                                                    <strong>Rp {{ number_format($this->getTotalIncome(), 0, ',', '.') }}</strong>
                                                </td>
                                                <td class="text-end text-danger">
                                                    <strong>Rp {{ number_format($this->getTotalExpenditure(), 0, ',', '.') }}</strong>
                                                </td>
                                                <td class="text-end text-warning">
                                                    <strong>Rp {{ number_format($this->getTotalFixedCost(), 0, ',', '.') }}</strong>
                                                </td>
                                                <td class="text-end text-info">
                                                    <strong>Rp {{ number_format($this->getTotalCapital(), 0, ',', '.') }}</strong>
                                                </td>
                                                <td class="text-end {{ $this->getTotalNetCashFlow() >= 0 ? 'text-success' : 'text-danger' }}">
                                                    <strong>
                                                        {{ $this->getTotalNetCashFlow() >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($this->getTotalNetCashFlow()), 0, ',', '.') }}{{ $this->getTotalNetCashFlow() < 0 ? ')' : '' }}
                                                    </strong>
                                                </td>
                                                <td class="text-end {{ $cumulative >= 0 ? 'text-success' : 'text-danger' }}">
                                                    <strong>
                                                        {{ $cumulative >= 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($cumulative), 0, ',', '.') }}{{ $cumulative < 0 ? ')' : '' }}
                                                    </strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Analysis & Interpretation --}}
                        <div class="card mt-4">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="bi bi-lightbulb"></i>
                                    Analisis & Interpretasi
                                </h6>
                                
                                <div class="row">
                                    {{-- IRR Analysis --}}
                                    <div class="col-md-6">
                                        <div class="alert alert-primary">
                                            <h6 class="alert-heading">
                                                <i class="bi bi-graph-up"></i> Analisis IRR
                                            </h6>
                                            @if($irr !== null)
                                                <p class="mb-2">
                                                    <strong>IRR:</strong> {{ number_format($irr, 2) }}%<br>
                                                    <strong>Discount Rate:</strong> {{ $discountRate }}%
                                                </p>
                                                @if($irr > $discountRate)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Rekomendasi:</strong> Investasi sangat layak dilakukan karena IRR lebih tinggi dari discount rate.
                                                        </div>
                                                    </div>
                                                @elseif($irr > 0)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Rekomendasi:</strong> Investasi cukup baik namun perlu pertimbangan lebih lanjut.
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-x-circle-fill text-danger me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Rekomendasi:</strong> Investasi tidak layak dilakukan karena menghasilkan kerugian.
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="d-flex align-items-start">
                                                    <i class="bi bi-info-circle-fill text-secondary me-2 mt-1"></i>
                                                    <div>IRR tidak dapat dihitung. Periksa kembali data cash flow Anda.</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- NPV Analysis --}}
                                    <div class="col-md-6">
                                        <div class="alert alert-success">
                                            <h6 class="alert-heading">
                                                <i class="bi bi-cash-stack"></i> Analisis NPV
                                            </h6>
                                            @if($npv !== null)
                                                <p class="mb-2">
                                                    <strong>NPV:</strong> Rp {{ number_format($npv, 0, ',', '.') }}
                                                </p>
                                                @if($npv > 0)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> NPV positif menunjukkan investasi akan menghasilkan nilai tambah sebesar Rp {{ number_format($npv, 0, ',', '.') }}.
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-x-circle-fill text-danger me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> NPV negatif menunjukkan investasi akan menghasilkan kerugian.
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <p>NPV tidak dapat dihitung.</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Payback Period Analysis --}}
                                    <div class="col-md-6">
                                        <div class="alert alert-warning">
                                            <h6 class="alert-heading">
                                                <i class="bi bi-clock-history"></i> Analisis Payback Period
                                            </h6>
                                            @if($paybackPeriod !== null)
                                                <p class="mb-2">
                                                    <strong>Payback Period:</strong> {{ number_format($paybackPeriod, 1) }} bulan
                                                </p>
                                                @if($paybackPeriod <= 12)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Modal investasi akan kembali dalam waktu kurang dari 1 tahun. Sangat baik!
                                                        </div>
                                                    </div>
                                                @elseif($paybackPeriod <= 24)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Modal investasi akan kembali dalam waktu 1-2 tahun. Cukup baik.
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-x-circle-fill text-danger me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Modal investasi akan kembali dalam waktu lebih dari 2 tahun. Perlu pertimbangan.
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <p>Modal investasi tidak akan kembali dalam periode yang diamati.</p>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Profitability Index Analysis --}}
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading">
                                                <i class="bi bi-percent"></i> Analisis Profitability Index
                                            </h6>
                                            @if($profitabilityIndex !== null)
                                                <p class="mb-2">
                                                    <strong>PI:</strong> {{ number_format($profitabilityIndex, 2) }}
                                                </p>
                                                @if($profitabilityIndex > 1.5)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Setiap Rp 1 investasi menghasilkan nilai sekarang sebesar Rp {{ number_format($profitabilityIndex, 2) }}. Sangat menguntungkan!
                                                        </div>
                                                    </div>
                                                @elseif($profitabilityIndex > 1)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Setiap Rp 1 investasi menghasilkan nilai sekarang sebesar Rp {{ number_format($profitabilityIndex, 2) }}. Menguntungkan.
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-x-circle-fill text-danger me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Setiap Rp 1 investasi menghasilkan nilai sekarang kurang dari Rp 1. Tidak menguntungkan.
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <p>Profitability Index tidak dapat dihitung.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Empty State --}}
                    <div class="text-center mt-5 mb-5">
                        <div class="alert alert-light border">
                            <i class="bi bi-graph-up display-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">Pilih Tahun untuk Analisis IRR</h5>
                            <p class="text-muted mb-0">
                                Silakan pilih tahun dari dropdown di atas untuk melihat analisis IRR dan kelayakan investasi.
                            </p>
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

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
            <i class="bi bi-info-circle"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
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
    </script>

    {{-- Penjelasan untuk Pelaku UMKM Awam --}}
    <section class="bg-light py-5 mt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-percent text-primary display-4"></i>
                                <h4 class="mt-3 text-primary fw-bold">Panduan Internal Rate of Return (IRR)</h4>
                                <p class="text-muted">Penjelasan sederhana untuk memahami kelayakan investasi UMKM</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-question-circle me-2"></i>
                                            Apa itu IRR?
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-info">IRR:</strong> Tingkat pengembalian investasi dalam bentuk persentase
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-success">Indikator Profitabilitas:</strong> Menunjukkan seberapa menguntungkan investasi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-warning">Perbandingan:</strong> Bisa dibandingkan dengan bunga bank atau investasi lain
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-calculator me-2"></i>
                                            Cara Membaca IRR
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-success">IRR > 15%:</strong> Sangat menguntungkan, layak investasi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-warning">IRR 10-15%:</strong> Menguntungkan, cukup layak
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-danger">IRR < 10%:</strong> Kurang menguntungkan, perlu evaluasi
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-lightbulb me-2"></i>
                                            Komponen Analisis Investasi
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-cash-stack text-success display-6"></i>
                                                    <h6 class="mt-2 text-success">NPV (Net Present Value)</h6>
                                                    <p class="mb-2"><strong>Artinya:</strong></p>
                                                    <p class="mb-2">Nilai bersih investasi saat ini</p>
                                                    <small class="text-muted">Positif = layak, Negatif = tidak layak</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-percent text-info display-6"></i>
                                                    <h6 class="mt-2 text-info">IRR</h6>
                                                    <p class="mb-2"><strong>Artinya:</strong></p>
                                                    <p class="mb-2">Tingkat pengembalian investasi</p>
                                                    <small class="text-muted">Semakin tinggi semakin baik</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-graph-up text-warning display-6"></i>
                                                    <h6 class="mt-2 text-warning">Profitability Index</h6>
                                                    <p class="mb-2"><strong>Artinya:</strong></p>
                                                    <p class="mb-2">Rasio manfaat terhadap biaya</p>
                                                    <small class="text-muted">> 1 = layak, < 1 = tidak layak</small>
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
                                                <h6 class="alert-heading fw-bold">Kapan Harus Menggunakan IRR?</h6>
                                                <ul class="mb-0">
                                                    <li><strong>Memulai Bisnis Baru:</strong> Untuk mengetahui kelayakan investasi</li>
                                                    <li><strong>Ekspansi Bisnis:</strong> Untuk menambah cabang atau produk baru</li>
                                                    <li><strong>Pembelian Mesin:</strong> Untuk investasi peralatan produksi</li>
                                                    <li><strong>Perbandingan Proyek:</strong> Untuk memilih investasi terbaik</li>
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