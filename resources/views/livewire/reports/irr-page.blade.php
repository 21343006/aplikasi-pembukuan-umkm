<main id="main" class="main">
    <div class="pagetitle">
        <h1>Internal Rate of Return (IRR)</h1>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Analisis Kelayakan Investasi dan Proyeksi Return</h5>

                {{-- Filter Section --}}
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="filterYear">Pilih Tahun Analisis</label>
                        <select wire:model.live="filterYear" class="form-control">
                            <option value="">Pilih Tahun</option>
                            @for($year = 2020; $year <= now()->year + 2; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="discountRate">Discount Rate (%)</label>
                        <input type="number" wire:model.live="discountRate" step="0.1" min="0" max="100" 
                               class="form-control" placeholder="Contoh: 10">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button wire:click="toggleCalculation" class="btn btn-info">
                            <i class="bi bi-calculator"></i>
                            {{ $showCalculation ? 'Sembunyikan' : 'Tampilkan' }} Detail Perhitungan
                        </button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        <button wire:click="exportData" class="btn btn-success">
                            <i class="bi bi-download"></i> Export Data
                        </button>
                    </div>
                </div>

                @if($filterYear)
                    {{-- Key Metrics Cards --}}
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small>Internal Rate of Return</small>
                                            <h3 class="mb-1">
                                                @if($irr !== null)
                                                    {{ number_format($irr, 2) }}%
                                                @else
                                                    N/A
                                                @endif
                                            </h3>
                                            <small class="{{ $this->getIrrStatus()['class'] ?? '' }}">
                                                {{ $this->getIrrStatus()['message'] ?? '' }}
                                            </small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-graph-up-arrow display-6"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small>Net Present Value</small>
                                            <h3 class="mb-1">
                                                @if($npv !== null)
                                                    Rp {{ number_format($npv / 1000000, 1) }}M
                                                @else
                                                    N/A
                                                @endif
                                            </h3>
                                            <small class="{{ $this->getNpvStatus()['class'] ?? '' }}">
                                                {{ $this->getNpvStatus()['message'] ?? '' }}
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
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <small>Payback Period</small>
                                            <h3 class="mb-1">
                                                @if($paybackPeriod !== null)
                                                    {{ number_format($paybackPeriod, 1) }}
                                                @else
                                                    N/A
                                                @endif
                                            </h3>
                                            <small>
                                                @if($paybackPeriod && $paybackPeriod <= 12)
                                                    Bulan (Dalam 1 tahun)
                                                @elseif($paybackPeriod && $paybackPeriod <= 24)
                                                    Bulan (Dalam 2 tahun)
                                                @else
                                                    Bulan (Lebih dari 2 tahun)
                                                @endif
                                            </small>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clock-history display-6"></i>
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
                                            <small>Profitability Index</small>
                                            <h3 class="mb-1">
                                                @if($profitabilityIndex !== null)
                                                    {{ number_format($profitabilityIndex, 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </h3>
                                            <small>
                                                @if($profitabilityIndex && $profitabilityIndex > 1)
                                                    Menguntungkan
                                                @elseif($profitabilityIndex && $profitabilityIndex == 1)
                                                    Break Even
                                                @else
                                                    Tidak Menguntungkan
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
                                        <strong>Total Modal Tetap</strong><br>
                                        <span class="h5">Rp {{ number_format($this->getTotalFixedCost(), 0, ',', '.') }}</span>
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
                                Proyeksi Cash Flow Tahunan (IRR Analysis)
                            </h6>
                            
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-primary">
                                        <tr class="text-center">
                                            <th width="50%">TAHUN</th>
                                            <th width="50%">Uang Masuk/Keluar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center fw-bold">0</td>
                                            <td class="text-end text-danger fw-bold">
                                                - {{ number_format($modalAwal, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-bold">1</td>
                                            <td class="text-end text-success fw-bold">
                                                {{ number_format($this->getTotalNetCashFlow(), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                        @for($year = 2; $year <= 4; $year++)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $year }}</td>
                                                <td class="text-end text-success fw-bold">
                                                    {{ number_format($this->getTotalNetCashFlow(), 2, ',', '.') }}
                                                    <small class="text-muted d-block">*Proyeksi berdasarkan tahun 1</small>
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
                                            <i class="bi bi-calculator"></i> IRR Calculation
                                        </h6>
                                        <p class="mb-2">
                                            <strong>IRR:</strong> 
                                            @if($irr !== null)
                                                {{ number_format($irr, 0) }}%
                                            @else
                                                Tidak dapat dihitung
                                            @endif
                                        </p>
                                        <p class="mb-0">
                                            <strong>Tingkat Keuntungan per Tahun:</strong> 
                                            @if($irr !== null)
                                                {{ number_format($irr, 0) }}%
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning">
                                        <h6 class="fw-bold">
                                            <i class="bi bi-info-circle"></i> Keterangan
                                        </h6>
                                        <p class="mb-0 small">
                                            <strong>Info:</strong> Untuk tanda (-) itu adalah uang keluar/modal, 
                                            dan untuk angka yang positif itu adalah uang masuk/pendapatan
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
                                                <th class="text-end">Modal Tetap</th>
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
                                                @if($profitabilityIndex > 1)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Setiap Rp 1 investasi menghasilkan nilai sekarang sebesar Rp {{ number_format($profitabilityIndex, 2) }}. Menguntungkan!
                                                        </div>
                                                    </div>
                                                @elseif($profitabilityIndex == 1)
                                                    <div class="d-flex align-items-start">
                                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1"></i>
                                                        <div>
                                                            <strong>Interpretasi:</strong> Investasi berada pada titik impas (break even).
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
</main>