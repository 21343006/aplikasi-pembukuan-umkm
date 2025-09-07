<main id="main" class="main">
    <div class="pagetitle">
        <h1>BEP Calculator Real-time</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">BEP Calculator</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <!-- Mode Selector -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-calculator me-2"></i>Pilih Mode Perhitungan BEP
                </h5>
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="calculationMode" id="period" value="period" 
                           wire:model="calculationMode" {{ $calculationMode === 'period' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="period">
                        <i class="bi bi-calendar-month me-2"></i>BEP Per Periode
                    </label>

                    <input type="radio" class="btn-check" name="calculationMode" id="product" value="product" 
                           wire:model="calculationMode" {{ $calculationMode === 'product' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="product">
                        <i class="bi bi-box me-2"></i>BEP Per Produk
                    </label>

                    <input type="radio" class="btn-check" name="calculationMode" id="custom" value="custom" 
                           wire:model="calculationMode" {{ $calculationMode === 'custom' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="custom">
                        <i class="bi bi-sliders me-2"></i>BEP Custom
                    </label>
                </div>
            </div>
        </div>

        <!-- Data Validation Alert -->
        @if($dataValidation)
            <div class="alert alert-{{ $dataValidation['available'] ? 'info' : 'warning' }} alert-dismissible fade show" role="alert">
                <i class="bi bi-{{ $dataValidation['available'] ? 'info-circle' : 'exclamation-triangle' }} me-2"></i>
                {{ $dataValidation['message'] }}
                @if($dataValidation['available'])
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="bi bi-check-circle me-1"></i>Biaya Tetap: {{ $dataValidation['fixed_cost_count'] ?? 0 }} data
                            <i class="bi bi-check-circle me-1 ms-3"></i>Penjualan: {{ $dataValidation['income_count'] ?? 0 }} data
                            <i class="bi bi-check-circle me-1 ms-3"></i>Pengeluaran: {{ $dataValidation['expenditure_count'] ?? 0 }} data
                        </small>
                    </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Input Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-input-cursor me-2"></i>Input Data
                </h5>

                <div class="row g-3">
                    @if($calculationMode !== 'custom')
                        <div class="col-md-4">
                            <label for="selectedMonth" class="form-label">Bulan</label>
                            <select id="selectedMonth" wire:model="selectedMonth" class="form-select">
                                @foreach($this->monthNames as $monthNum => $monthName)
                                    <option value="{{ $monthNum }}">{{ $monthName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="selectedYear" class="form-label">Tahun</label>
                            <input id="selectedYear" type="number" class="form-control" wire:model.live="selectedYear" 
                                   min="2000" max="2099" placeholder="Contoh: {{ now()->year }}">
                        </div>
                    @endif

                    @if($calculationMode === 'product')
                        <div class="col-md-4">
                            <label for="selectedProduct" class="form-label">Pilih Produk</label>
                            <select id="selectedProduct" wire:model.live="selectedProduct" class="form-select">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($productList as $product)
                                    <option value="{{ $product }}">{{ $product }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($calculationMode === 'custom')
                        <div class="col-md-3">
                            <label for="customSellingPrice" class="form-label">Harga Jual/Unit</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="customSellingPrice" class="form-control" wire:model.live="customSellingPrice" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="customVariableCost" class="form-label">Biaya Variabel/Unit</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="customVariableCost" class="form-control" wire:model.live="customVariableCost" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="customFixedCost" class="form-label">Biaya Tetap (Bulan)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="customFixedCost" class="form-control" wire:model.live="customFixedCost" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="targetProfit" class="form-label">Target Profit (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" id="targetProfit" class="form-control" wire:model.live="targetProfit" 
                                       step="any" min="0" placeholder="0">
                            </div>
                        </div>
                    @endif

                    <!-- Target BEP Input untuk semua mode -->
                    <div class="col-md-3">
                        <label for="targetBepUnits" class="form-label">Target BEP (Unit)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-target"></i></span>
                            <input type="number" id="targetBepUnits" class="form-control" wire:model.live="targetBepUnits" 
                                   step="any" min="0" placeholder="0">
                        </div>
                        <small class="text-muted">Akan dihitung otomatis</small>
                    </div>
                    <div class="col-md-3">
                        <label for="currentUnits" class="form-label">Unit Saat Ini</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-box"></i></span>
                            <input type="number" id="currentUnits" class="form-control" wire:model.live="currentUnits" 
                                   step="any" min="0" placeholder="0" readonly>
                        </div>
                        <small class="text-muted">Dihitung otomatis dari data</small>
                    </div>
                    <div class="col-md-3">
                        <label for="targetBepRevenue" class="form-label">Target BEP (Rupiah)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="targetBepRevenue" class="form-control" wire:model.live="targetBepRevenue" 
                                   placeholder="0" oninput="formatCurrency(this)">
                        </div>
                        <small class="text-muted">Akan dihitung otomatis</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aksi</label>
                        <div class="d-grid">
                            <button type="button" class="btn btn-info btn-sm" wire:click="updateTargetBep">
                                <i class="bi bi-arrow-clockwise me-1"></i>Update Target
                            </button>
                        </div>
                        <small class="text-muted">Update perhitungan manual</small>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button wire:click="calculateBep" class="btn btn-primary" {{ !$dataAvailable && $calculationMode !== 'custom' ? 'disabled' : '' }}>
                            <i class="bi bi-calculator me-2"></i>Hitung BEP
                        </button>
                        <button wire:click="resetCalculation" class="btn btn-secondary ms-2">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        @if($calculationError)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ $calculationError }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Calculation Result -->
        @if($calculationResult)
            {{-- Penjelasan BEP yang Mudah Dipahami --}}
            @if($bepExplanation)
            <div class="card mb-4 border-info">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Apa Arti BEP untuk Bisnis Anda?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="bi bi-info-circle fs-2 text-info"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Penjelasan Sederhana:</h6>
                                <p class="mb-0 fs-5">{{ $bepExplanation['simple_explanation'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="text-primary mb-3">
                                        <i class="bi bi-calendar-day fs-1"></i>
                                    </div>
                                    <h6 class="text-muted">Target Harian</h6>
                                    <div class="h5 text-primary mb-1">{{ $bepExplanation['daily_target']['units'] }}</div>
                                    <div class="h6 text-success">{{ $bepExplanation['daily_target']['revenue'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="text-warning mb-3">
                                        <i class="bi bi-calendar-week fs-1"></i>
                                    </div>
                                    <h6 class="text-muted">Target Mingguan</h6>
                                    <div class="h5 text-warning mb-1">{{ $bepExplanation['weekly_target']['units'] }}</div>
                                    <div class="h6 text-success">{{ $bepExplanation['weekly_target']['revenue'] }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="text-success mb-3">
                                        <i class="bi bi-check-circle fs-1"></i>
                                    </div>
                                    <h6 class="text-muted">Status Saat Ini</h6>
                                    <div class="small">{{ $bepExplanation['practical_meaning'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion" id="bepDetailAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#bepDetailCollapse" aria-expanded="false">
                                    <i class="bi bi-calculator me-2"></i>Bagaimana Cara Menghitungnya?
                                </button>
                            </h2>
                            <div id="bepDetailCollapse" class="accordion-collapse collapse" data-bs-parent="#bepDetailAccordion">
                                <div class="accordion-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="alert alert-light">
                                                <h6><i class="bi bi-1-circle me-2"></i>{{ $bepExplanation['detailed_breakdown']['fixed_cost_explanation'] }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="alert alert-light">
                                                <h6><i class="bi bi-2-circle me-2"></i>{{ $bepExplanation['detailed_breakdown']['contribution_explanation'] }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="alert alert-primary">
                                                <h6><i class="bi bi-3-circle me-2"></i>{{ $bepExplanation['detailed_breakdown']['calculation_explanation'] }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Hasil Perhitungan BEP --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle me-2"></i>Hasil Perhitungan BEP
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- BEP Summary -->
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Break Even Point</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Unit BEP</small>
                                            <div class="h4 text-primary">{{ number_format($calculationResult['bep_units']) }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Revenue BEP</small>
                                            <div class="h4 text-success">Rp {{ number_format($calculationResult['bep_revenue'], 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Target BEP -->
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="bi bi-target me-2"></i>Target BEP
                                        @if(($targetBepUnits ?? 0) > 0 || ($targetBepRevenue ?? 0) > 0)
                                            <span class="badge bg-success ms-2">Otomatis</span>
                                        @endif
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Target Unit</small>
                                            <div class="h4 text-warning">{{ number_format($targetBepUnits ?? 0) }}</div>
                                            @if(($targetBepUnits ?? 0) > 0 && ($calculationResult['bep_units'] ?? 0) > 0)
                                                <small class="text-muted">BEP + {{ number_format((($targetBepUnits - ($calculationResult['bep_units'] ?? 0)) / ($calculationResult['bep_units'] ?? 1)) * 100, 0) }}%</small>
                                            @endif
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Target Revenue</small>
                                            <div class="h4 text-warning">Rp {{ number_format($targetBepRevenue ?? 0, 0, '.', ',') }}</div>
                                            @if(($targetBepRevenue ?? 0) > 0 && ($calculationResult['bep_revenue'] ?? 0) > 0)
                                                <small class="text-muted">BEP + {{ number_format((($targetBepRevenue - ($calculationResult['bep_revenue'] ?? 0)) / ($calculationResult['bep_revenue'] ?? 1)) * 100, 0) }}%</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sisa BEP ke Target -->
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-target me-2"></i>Sisa ke Target
                                        <small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.9;">
                                            Unit/Revenue yang masih perlu dicapai
                                        </small>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Sisa Unit</small>
                                            @php
                                                $remainingUnits = max(0, ($targetBepUnits ?? 0) - ($currentUnits ?? 0));
                                                $remainingUnitsColor = $remainingUnits > 0 ? 'text-danger' : 'text-success';
                                                $remainingUnitsStatus = $remainingUnits > 0 ? 'Masih perlu' : 'Target tercapai';
                                            @endphp
                                            <div class="h4 {{ $remainingUnitsColor }}">{{ number_format($remainingUnits) }}</div>
                                            <small class="text-muted">{{ $remainingUnitsStatus }}</small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Sisa Revenue</small>
                                            @php
                                                $actualRevenue = $calculationResult['total_sales'] ?? 0;
                                                $remainingRevenue = max(0, ($targetBepRevenue ?? 0) - $actualRevenue);
                                                $remainingRevenueColor = $remainingRevenue > 0 ? 'text-danger' : 'text-success';
                                                $remainingRevenueStatus = $remainingRevenue > 0 ? 'Masih perlu' : 'Target tercapai';
                                            @endphp
                                            <div class="h4 {{ $remainingRevenueColor }}">Rp {{ number_format($remainingRevenue, 0, ',', '.') }}</div>
                                            <small class="text-muted">{{ $remainingRevenueStatus }}</small>
                                        </div>
                                    </div>
                                    
                                    @if(($remainingUnits > 0 || $remainingRevenue > 0))
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <strong>Penjelasan:</strong> 
                                            @if($remainingUnits > 0)
                                                Anda masih perlu menjual {{ number_format($remainingUnits) }} unit lagi untuk mencapai target.
                                            @endif
                                            @if($remainingRevenue > 0)
                                                @if($remainingUnits > 0) <br>@endif
                                                Anda masih perlu mencapai penjualan Rp {{ number_format($remainingRevenue, 0, ',', '.') }} lagi untuk mencapai target revenue.
                                            @endif
                                        </small>
                                    </div>
                                    @else
                                    <div class="mt-3 p-2 bg-success bg-opacity-10 rounded">
                                        <small class="text-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            <strong>Selamat!</strong> Target BEP sudah tercapai atau terlampaui.
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar untuk Target BEP -->
                    @if(($targetBepUnits ?? 0) > 0 || ($targetBepRevenue ?? 0) > 0)
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="bi bi-graph-up me-2"></i>Progress Unit ke Target
                                            <small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.9;">
                                                Kemajuan pencapaian target unit
                                            </small>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $unitProgress = ($targetBepUnits ?? 0) > 0 ? min(100, (($currentUnits ?? 0) / ($targetBepUnits ?? 1)) * 100) : 0;
                                            $unitProgressColor = $unitProgress >= 100 ? 'success' : ($unitProgress >= 70 ? 'info' : ($unitProgress >= 40 ? 'warning' : 'danger'));
                                            $unitProgressStatus = $unitProgress >= 100 ? 'Target Tercapai!' : ($unitProgress >= 70 ? 'Sangat Baik' : ($unitProgress >= 40 ? 'Cukup Baik' : 'Perlu Ditingkatkan'));
                                        @endphp
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>
                                                <strong>Progress: {{ number_format($unitProgress, 1) }}%</strong>
                                                <br><small class="text-muted">{{ $unitProgressStatus }}</small>
                                            </span>
                                            <span class="text-end">
                                                <strong>{{ number_format($currentUnits ?? 0) }} / {{ number_format($targetBepUnits ?? 0) }}</strong>
                                                <br><small class="text-muted">unit terjual</small>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-{{ $unitProgressColor }}" role="progressbar" 
                                                 data-progress="{{ $unitProgress }}"
                                                 aria-valuenow="{{ number_format($unitProgress, 1) }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ number_format($unitProgress, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="bi bi-currency-dollar me-2"></i>Progress Revenue ke Target
                                            <small class="d-block mt-1" style="font-size: 0.75rem; opacity: 0.9;">
                                                Kemajuan pencapaian target revenue
                                            </small>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $actualRevenue = $calculationResult['total_sales'] ?? 0;
                                            $revenueProgress = ($targetBepRevenue ?? 0) > 0 ? min(100, ($actualRevenue / ($targetBepRevenue ?? 1)) * 100) : 0;
                                            $revenueProgressColor = $revenueProgress >= 100 ? 'success' : ($revenueProgress >= 70 ? 'info' : ($revenueProgress >= 40 ? 'warning' : 'danger'));
                                            $revenueProgressStatus = $revenueProgress >= 100 ? 'Target Tercapai!' : ($revenueProgress >= 70 ? 'Sangat Baik' : ($revenueProgress >= 40 ? 'Cukup Baik' : 'Perlu Ditingkatkan'));
                                        @endphp
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>
                                                <strong>Progress: {{ number_format($revenueProgress, 1) }}%</strong>
                                                <br><small class="text-muted">{{ $revenueProgressStatus }}</small>
                                            </span>
                                            <span class="text-end">
                                                <strong>Rp {{ number_format($actualRevenue, 0, '.', ',') }} / Rp {{ number_format($targetBepRevenue ?? 0, 0, '.', ',') }}</strong>
                                                <br><small class="text-muted">penjualan aktual</small>
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-{{ $revenueProgressColor }}" role="progressbar" 
                                                 data-progress="{{ $revenueProgress }}"
                                                 aria-valuenow="{{ number_format($revenueProgress, 1) }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ number_format($revenueProgress, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Key Metrics -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Metrik Utama</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Contribution Margin</small>
                                            <div class="h5 text-info">Rp {{ number_format($calculationResult['contribution_margin'], 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Margin of Safety</small>
                                            <div class="h5 text-{{ $calculationResult['margin_of_safety_percentage'] > 0 ? 'success' : 'danger' }}">
                                                {{ number_format($calculationResult['margin_of_safety_percentage'] ?? 0, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Analisis Target</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Status Unit</small>
                                            @php
                                                $unitStatus = ($currentUnits ?? 0) >= ($targetBepUnits ?? 0) ? 'Target Tercapai' : 'Belum Tercapai';
                                                $unitStatusColor = ($currentUnits ?? 0) >= ($targetBepUnits ?? 0) ? 'text-success' : 'text-warning';
                                            @endphp
                                            <div class="h6 {{ $unitStatusColor }}">{{ $unitStatus }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Status Revenue</small>
                                            @php
                                                $revenueStatus = ($calculationResult['total_sales'] ?? 0) >= ($targetBepRevenue ?? 0) ? 'Target Tercapai' : 'Belum Tercapai';
                                                $revenueStatusColor = ($calculationResult['total_sales'] ?? 0) >= ($targetBepRevenue ?? 0) ? 'text-success' : 'text-warning';
                                            @endphp
                                            <div class="h6 {{ $revenueStatusColor }}">{{ $revenueStatus }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Results -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Detail Perhitungan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                @if($calculationResult['mode'] === 'Periode' || $calculationResult['mode'] === 'Produk')
                                                    <tr>
                                                        <td><strong>Mode</strong></td>
                                                        <td>{{ $calculationResult['mode'] }}</td>
                                                        <td><strong>Periode</strong></td>
                                                        <td>{{ $calculationResult['period'] }}</td>
                                                    </tr>
                                                    @if($calculationResult['mode'] === 'Produk')
                                                        <tr>
                                                            <td><strong>Produk</strong></td>
                                                            <td>{{ $calculationResult['product'] }}</td>
                                                            <td><strong>Unit Terjual</strong></td>
                                                            <td>{{ number_format($calculationResult['units_sold']) }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td><strong>Biaya Tetap</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['fixed_cost'], 0, ',', '.') }}</td>
                                                        <td><strong>Total Penjualan</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['total_sales'], 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Biaya Variabel Total</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['variable_cost_total'], 0, ',', '.') }}</td>
                                                        <td><strong>Profit/Loss</strong></td>
                                                        <td class="{{ $calculationResult['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            Rp {{ number_format($calculationResult['profit_loss'], 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Harga Jual/Unit</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['selling_price_per_unit'], 0, ',', '.') }}</td>
                                                        <td><strong>Margin of Safety (Unit)</strong></td>
                                                        <td class="{{ $calculationResult['margin_of_safety_units'] > 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format($calculationResult['margin_of_safety_units']) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Biaya Variabel/Unit</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['variable_cost_per_unit'], 0, ',', '.') }}</td>
                                                        <td><strong>Margin of Safety (%)</strong></td>
                                                        <td class="{{ $calculationResult['margin_of_safety_percentage'] > 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format($calculationResult['margin_of_safety_percentage'], 1) }}%
                                                        </td>
                                                    </tr>
                                                    <!-- Target BEP Analysis -->
                                                    @if(($targetBepUnits ?? 0) > 0 || ($targetBepRevenue ?? 0) > 0)
                                                        <tr class="table-warning">
                                                            <td><strong>Target BEP (Unit)</strong></td>
                                                            <td>{{ number_format($targetBepUnits ?? 0) }}</td>
                                                            <td><strong>Unit Saat Ini</strong></td>
                                                            <td>{{ number_format($currentUnits ?? 0) }}</td>
                                                        </tr>
                                                        <tr class="table-warning">
                                                            <td><strong>Target BEP (Revenue)</strong></td>
                                                            <td>Rp {{ number_format($targetBepRevenue ?? 0, 0, '.', ',') }}</td>
                                                            <td><strong>Revenue Saat Ini</strong></td>
                                                            <td>Rp {{ number_format($calculationResult['total_sales'] ?? 0, 0, '.', ',') }}</td>
                                                        </tr>
                                                        <tr class="table-info">
                                                            <td><strong>Sisa Unit ke Target</strong></td>
                                                            <td class="{{ ($targetBepUnits ?? 0) > ($currentUnits ?? 0) ? 'text-danger' : 'text-success' }}">
                                                                {{ number_format(max(0, ($targetBepUnits ?? 0) - ($currentUnits ?? 0))) }}
                                                            </td>
                                                            <td><strong>Sisa Revenue ke Target</strong></td>
                                                            <td class="{{ ($targetBepRevenue ?? 0) > ($calculationResult['total_sales'] ?? 0) ? 'text-danger' : 'text-success' }}">
                                                                Rp {{ number_format(max(0, ($targetBepRevenue ?? 0) - ($calculationResult['total_sales'] ?? 0)), 0, '.', ',') }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @else
                                                    <tr>
                                                        <td><strong>Mode</strong></td>
                                                        <td>{{ $calculationResult['mode'] }}</td>
                                                        <td><strong>Target Profit</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['target_profit'], 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Biaya Tetap</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['fixed_cost'], 0, ',', '.') }}</td>
                                                        <td><strong>Unit untuk Target Profit</strong></td>
                                                        <td>{{ number_format($calculationResult['target_profit_units']) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Harga Jual/Unit</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['selling_price_per_unit'], 0, ',', '.') }}</td>
                                                        <td><strong>Revenue untuk Target Profit</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['target_profit_revenue'], 0, ',', '.') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Biaya Variabel/Unit</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['variable_cost_per_unit'], 0, ',', '.') }}</td>
                                                        <td><strong>Contribution Margin</strong></td>
                                                        <td>Rp {{ number_format($calculationResult['contribution_margin'], 0, ',', '.') }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Insights Bisnis --}}
            @if($bepInsights && count($bepInsights) > 0)
            <div class="card mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Insights Bisnis Anda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($bepInsights as $insight)
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="{{ $insight['icon'] }} fs-1 
                                            @if($insight['status'] === 'excellent') text-success
                                            @elseif($insight['status'] === 'good') text-info
                                            @elseif($insight['status'] === 'moderate') text-warning
                                            @else text-danger
                                            @endif"></i>
                                    </div>
                                    <h6 class="text-muted mb-2">{{ $insight['title'] }}</h6>
                                    <div class="h4 mb-2
                                        @if($insight['status'] === 'excellent') text-success
                                        @elseif($insight['status'] === 'good') text-info
                                        @elseif($insight['status'] === 'moderate') text-warning
                                        @else text-danger
                                        @endif">
                                        {{ $insight['value'] }}
                                    </div>
                                    <p class="small text-muted mb-0">{{ $insight['description'] }}</p>
                                    <div class="mt-2">
                                        <span class="badge 
                                            @if($insight['status'] === 'excellent') bg-success
                                            @elseif($insight['status'] === 'good') bg-info
                                            @elseif($insight['status'] === 'moderate') bg-warning
                                            @else bg-danger
                                            @endif">
                                            @if($insight['status'] === 'excellent') Sangat Baik
                                            @elseif($insight['status'] === 'good') Baik
                                            @elseif($insight['status'] === 'moderate') Cukup
                                            @else Perlu Perbaikan
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Rekomendasi Bisnis --}}
            @if($bepRecommendations && count($bepRecommendations) > 0)
            <div class="card mb-4">
                <div class="card-header bg-gradient-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>Rekomendasi untuk Bisnis Anda
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($bepRecommendations as $recommendation)
                    <div class="alert alert-{{ $recommendation['type'] }} border-0 shadow-sm mb-3">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="bi 
                                    @if($recommendation['type'] === 'success') bi-check-circle-fill
                                    @elseif($recommendation['type'] === 'warning') bi-exclamation-triangle-fill
                                    @elseif($recommendation['type'] === 'danger') bi-x-circle-fill
                                    @else bi-info-circle-fill
                                    @endif fs-3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-2">{{ $recommendation['title'] }}</h6>
                                <p class="mb-3">{{ $recommendation['description'] }}</p>
                                <div class="accordion" id="recommendation{{ $loop->index }}">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bg-transparent border-0 p-0 shadow-none" 
                                                    type="button" data-bs-toggle="collapse" 
                                                    data-bs-target="#recommendationCollapse{{ $loop->index }}">
                                                <small class="fw-semibold">
                                                    <i class="bi bi-arrow-right me-1"></i>Langkah yang Disarankan
                                                </small>
                                            </button>
                                        </h2>
                                        <div id="recommendationCollapse{{ $loop->index }}" class="accordion-collapse collapse">
                                            <div class="accordion-body p-0 pt-2">
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($recommendation['actions'] as $action)
                                                    <li class="mb-1">
                                                        <i class="bi bi-check2 me-2 text-success"></i>{{ $action }}
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Analisis Produk Khusus (hanya untuk mode produk) --}}
            @if($calculationResult && $calculationResult['mode'] === 'Produk' && isset($calculationResult['product_analysis']))
            <div class="card mb-4">
                <div class="card-header bg-gradient-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Analisis Mendalam Produk: {{ $calculationResult['product'] }}
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Analisis Harga --}}
                    @if(isset($calculationResult['product_analysis']['price_analysis']))
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-info h-100">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bi bi-tag me-2"></i>Analisis Harga Jual</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <small class="text-muted">Harga Min</small>
                                            <div class="h6 text-primary">Rp {{ number_format($calculationResult['product_analysis']['price_analysis']['min_price'], 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Harga Rata-rata</small>
                                            <div class="h6 text-success">Rp {{ number_format($calculationResult['product_analysis']['price_analysis']['avg_price'], 0, ',', '.') }}</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Harga Max</small>
                                            <div class="h6 text-warning">Rp {{ number_format($calculationResult['product_analysis']['price_analysis']['max_price'], 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-center">
                                        <span class="badge bg-{{ $calculationResult['product_analysis']['price_analysis']['price_consistency'] === 'Konsisten' ? 'success' : 'warning' }}">
                                            {{ $calculationResult['product_analysis']['price_analysis']['price_consistency'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Analisis Volume</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <small class="text-muted">Volume Min</small>
                                            <div class="h6 text-primary">{{ number_format($calculationResult['product_analysis']['volume_analysis']['min_volume']) }}</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Volume Rata-rata</small>
                                            <div class="h6 text-success">{{ number_format($calculationResult['product_analysis']['volume_analysis']['avg_volume']) }}</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">Total Transaksi</small>
                                            <div class="h6 text-info">{{ $calculationResult['product_analysis']['volume_analysis']['total_transactions'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Analisis Profitabilitas --}}
                    @if(isset($calculationResult['product_analysis']['profitability']))
                    <div class="alert alert-{{ $calculationResult['product_analysis']['profitability']['contribution_margin_ratio'] >= 30 ? 'success' : ($calculationResult['product_analysis']['profitability']['contribution_margin_ratio'] >= 20 ? 'warning' : 'danger') }} border-0 shadow-sm">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="bi bi-{{ $calculationResult['product_analysis']['profitability']['contribution_margin_ratio'] >= 30 ? 'check-circle-fill' : ($calculationResult['product_analysis']['profitability']['contribution_margin_ratio'] >= 20 ? 'exclamation-triangle-fill' : 'x-circle-fill') }} fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Analisis Profitabilitas Produk</h6>
                                <p class="mb-2">
                                    <strong>Rasio Kontribusi:</strong> {{ number_format($calculationResult['product_analysis']['profitability']['contribution_margin_ratio'], 1) }}%
                                </p>
                                <p class="mb-2">
                                    <strong>Tingkat Profitabilitas:</strong> 
                                    <span class="badge bg-{{ $calculationResult['product_analysis']['profitability']['contribution_margin_ratio'] >= 30 ? 'success' : ($calculationResult['product_analysis']['profitability']['contribution_margin_ratio'] >= 20 ? 'warning' : 'danger') }}">
                                        {{ $calculationResult['product_analysis']['profitability']['profitability_level'] }}
                                    </span>
                                </p>
                                <p class="mb-0">{{ $calculationResult['product_analysis']['profitability']['recommendation'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Tren Produk --}}
                    @if(isset($calculationResult['product_trends']) && count($calculationResult['product_trends']['monthly_data'] ?? []) > 0)
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h6 class="fw-bold mb-3"><i class="bi bi-graph-up me-2"></i>Tren Penjualan 3 Bulan Terakhir</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bulan</th>
                                            <th>Penjualan</th>
                                            <th>Unit</th>
                                            <th>Harga Rata-rata</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($calculationResult['product_trends']['monthly_data'] as $trend)
                                        <tr>
                                            <td>{{ $trend['month'] }}</td>
                                            <td>Rp {{ number_format($trend['sales'], 0, ',', '.') }}</td>
                                            <td>{{ number_format($trend['units']) }}</td>
                                            <td>Rp {{ number_format($trend['avg_price'], 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-arrow-trend-up me-2"></i>Analisis Tren</h6>
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="h4 
                                            @if(($calculationResult['product_trends']['sales_growth'] ?? 0) > 0) text-success
                                            @elseif(($calculationResult['product_trends']['sales_growth'] ?? 0) < 0) text-danger
                                            @else text-muted
                                            @endif">
                                            {{ number_format($calculationResult['product_trends']['sales_growth'] ?? 0, 1) }}%
                                        </div>
                                        <small class="text-muted">Pertumbuhan Penjualan</small>
                                    </div>
                                    <div class="text-center mb-3">
                                        <div class="h5 
                                            @if(($calculationResult['product_trends']['units_growth'] ?? 0) > 0) text-success
                                            @elseif(($calculationResult['product_trends']['units_growth'] ?? 0) < 0) text-danger
                                            @else text-muted
                                            @endif">
                                            {{ number_format($calculationResult['product_trends']['units_growth'] ?? 0, 1) }}%
                                        </div>
                                        <small class="text-muted">Pertumbuhan Unit</small>
                                    </div>
                                    <div class="text-center">
                                        <span class="badge bg-{{ $calculationResult['product_trends']['trend_direction'] === 'Naik' ? 'success' : ($calculationResult['product_trends']['trend_direction'] === 'Turun' ? 'danger' : 'secondary') }}">
                                            {{ $calculationResult['product_trends']['trend_direction'] }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $calculationResult['product_trends']['trend_strength'] }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Perbandingan dengan Produk Lain --}}
                    @if(isset($calculationResult['product_comparison']) && count($calculationResult['product_comparison']) > 0)
                    <div class="alert alert-info border-0 shadow-sm">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="bi bi-trophy fs-3 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Peringkat Produk</h6>
                                <p class="mb-2">
                                    Produk <strong>{{ $calculationResult['product'] }}</strong> berada di peringkat 
                                    <span class="badge bg-primary">{{ $calculationResult['product_comparison']['rank'] }}</span> 
                                    dari {{ $calculationResult['product_comparison']['total_products'] }} produk.
                                </p>
                                <p class="mb-2">
                                    <strong>Tingkat Performa:</strong> 
                                    <span class="badge bg-{{ $calculationResult['product_comparison']['performance_level'] === 'Top Performer' ? 'success' : ($calculationResult['product_comparison']['performance_level'] === 'Above Average' ? 'info' : ($calculationResult['product_comparison']['performance_level'] === 'Average' ? 'warning' : 'danger')) }}">
                                        {{ $calculationResult['product_comparison']['performance_level'] }}
                                    </span>
                                </p>
                                <p class="mb-0">
                                    <strong>Percentile:</strong> {{ number_format($calculationResult['product_comparison']['percentile'], 1) }}%
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Analisis Custom BEP (hanya untuk mode custom) --}}
            @if($calculationResult && $calculationResult['mode'] === 'Custom')
            <div class="card mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-sliders me-2"></i>Analisis Skenario Custom
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Skenario Analisis --}}
                    @if(isset($calculationResult['scenario_analysis']))
                    <div class="row mb-4">
                        @foreach($calculationResult['scenario_analysis'] as $scenario)
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header 
                                    @if($scenario['name'] === 'Skenario Optimis') bg-success text-white
                                    @elseif($scenario['name'] === 'Skenario Pesimis') bg-danger text-white
                                    @else bg-warning text-dark
                                    @endif">
                                    <h6 class="mb-0">{{ $scenario['name'] }}</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted mb-2">{{ $scenario['description'] }}</p>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <small class="text-muted">BEP Unit</small>
                                            <div class="h6">{{ number_format($scenario['bep_units']) }}</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">BEP Revenue</small>
                                            <div class="h6">Rp {{ number_format($scenario['bep_revenue'], 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                    @if(isset($scenario['improvement']) && $scenario['improvement'] > 0)
                                    <div class="text-center mt-2">
                                        <span class="badge bg-success">
                                            <i class="bi bi-arrow-down me-1"></i>{{ number_format($scenario['improvement']) }} unit lebih sedikit
                                        </span>
                                    </div>
                                    @elseif(isset($scenario['deterioration']) && $scenario['deterioration'] > 0)
                                    <div class="text-center mt-2">
                                        <span class="badge bg-danger">
                                            <i class="bi bi-arrow-up me-1"></i>{{ number_format($scenario['deterioration']) }} unit lebih banyak
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- What-If Scenarios --}}
                    @if(isset($calculationResult['what_if_scenarios']) && count($calculationResult['what_if_scenarios']) > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2"></i>Skenario What-If</h6>
                        <div class="accordion" id="whatIfAccordion">
                            @foreach($calculationResult['what_if_scenarios'] as $index => $scenario)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#whatIfCollapse{{ $index }}">
                                        <i class="bi bi-question-circle me-2"></i>{{ $scenario['name'] }}
                                    </button>
                                </h2>
                                <div id="whatIfCollapse{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#whatIfAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-3">{{ $scenario['description'] }}</p>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <small class="text-muted">BEP Unit</small>
                                                <div class="h6 text-primary">{{ number_format($scenario['bep_units']) }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <small class="text-muted">BEP Revenue</small>
                                                <div class="h6 text-success">Rp {{ number_format($scenario['bep_revenue'], 0, ',', '.') }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Dampak</small>
                                                <div class="small">{{ $scenario['impact'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Analisis Risiko --}}
            @if($riskAssessment)
            <div class="card mb-4">
                <div class="card-header 
                    @if($riskAssessment['overall_risk'] === 'high') bg-danger text-white
                    @elseif($riskAssessment['overall_risk'] === 'medium') bg-warning text-dark
                    @else bg-success text-white
                    @endif">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-exclamation me-2"></i>Analisis Risiko Bisnis
                        </h5>
                        <div class="d-flex align-items-center">
                            <span class="badge 
                                @if($riskAssessment['overall_risk'] === 'high') bg-light text-danger
                                @elseif($riskAssessment['overall_risk'] === 'medium') bg-dark text-warning
                                @else bg-light text-success
                                @endif me-2">
                                Skor: {{ $riskAssessment['risk_score'] }}/100
                            </span>
                            <span class="badge 
                                @if($riskAssessment['overall_risk'] === 'high') bg-light text-danger
                                @elseif($riskAssessment['overall_risk'] === 'medium') bg-dark
                                @else bg-light text-success
                                @endif">
                                @if($riskAssessment['overall_risk'] === 'high') Risiko Tinggi
                                @elseif($riskAssessment['overall_risk'] === 'medium') Risiko Sedang
                                @else Risiko Rendah
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($riskAssessment['risk_factors']) > 0)
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-exclamation-triangle me-2 text-warning"></i>Faktor Risiko yang Teridentifikasi:
                        </h6>
                        @foreach($riskAssessment['risk_factors'] as $risk)
                        <div class="alert alert-{{ $risk['level'] === 'high' ? 'danger' : 'warning' }} border-0 shadow-sm mb-2">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-{{ $risk['level'] === 'high' ? 'exclamation-triangle-fill' : 'info-circle-fill' }} fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $risk['factor'] }}</h6>
                                    <p class="mb-1 small">{{ $risk['description'] }}</p>
                                    <small class="text-muted">
                                        <i class="bi bi-arrow-right me-1"></i>{{ $risk['impact'] }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div class="bg-light p-3 rounded">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-shield-check me-2 text-success"></i>Strategi Mitigasi Risiko:
                        </h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($riskAssessment['mitigation_strategies'] as $strategy)
                            <li class="mb-2">
                                <i class="bi bi-check-circle me-2 text-success"></i>{{ $strategy }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        @endif

        <!-- Data Summary -->
        @if($dataSummary)
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Ringkasan Data Periode
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <small class="text-muted">Periode</small>
                                <div class="h5 text-primary">{{ $dataSummary['period'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <small class="text-muted">Biaya Tetap</small>
                                <div class="h5 text-warning">Rp {{ number_format($dataSummary['fixed_cost'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <small class="text-muted">Total Penjualan</small>
                                <div class="h5 text-success">Rp {{ number_format($dataSummary['income'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <small class="text-muted">Profit/Loss</small>
                                <div class="h5 {{ $dataSummary['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($dataSummary['profit'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <script>
        // Set progress bar widths using JavaScript to avoid Blade/CSS conflicts
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar[data-progress]');
            progressBars.forEach(function(bar) {
                const progress = parseFloat(bar.getAttribute('data-progress')) || 0;
                bar.style.width = Math.min(100, Math.max(0, progress)) + '%';
            });
        });

        // Update progress bars when Livewire updates
        document.addEventListener('livewire:navigated', function() {
            const progressBars = document.querySelectorAll('.progress-bar[data-progress]');
            progressBars.forEach(function(bar) {
                const progress = parseFloat(bar.getAttribute('data-progress')) || 0;
                bar.style.width = Math.min(100, Math.max(0, progress)) + '%';
            });
        });

        // Format currency input for target BEP revenue
        function formatCurrency(input) {
            // Remove all non-numeric characters
            let value = input.value.replace(/[^\d]/g, '');
            
            // If empty, set to empty
            if (value === '') {
                input.value = '';
                return;
            }
            
            // Convert to number and format with dots as thousand separators
            let number = parseInt(value);
            let formatted = number.toLocaleString('id-ID');
            
            // Update the input value
            input.value = formatted;
            
            // Trigger Livewire update with the numeric value
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    </script>

    {{-- Penjelasan untuk Pelaku UMKM Awam --}}
    <section class="bg-light py-5 mt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-target text-primary display-4"></i>
                                <h4 class="mt-3 text-primary fw-bold">Panduan Break Even Point (BEP)</h4>
                                <p class="text-muted">Penjelasan sederhana untuk memahami titik impas bisnis UMKM</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-question-circle me-2"></i>
                                            Apa itu BEP?
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-info">Break Even Point:</strong> Titik dimana bisnis tidak untung tidak rugi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-success">Target Minimal:</strong> Jumlah penjualan minimal yang harus dicapai
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-warning">Indikator Penting:</strong> Menunjukkan apakah bisnis layak dijalankan
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-calculator me-2"></i>
                                            Komponen BEP
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-danger">Biaya Tetap:</strong> Biaya yang selalu sama (sewa, gaji, listrik)
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-warning">Biaya Variabel:</strong> Biaya per unit (bahan baku, tenaga kerja)
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-success">Harga Jual:</strong> Harga jual per unit produk/jasa
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
                                            Cara Membaca Hasil BEP
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-target text-success display-6"></i>
                                                    <h6 class="mt-2 text-success">BEP (Unit)</h6>
                                                    <p class="mb-2"><strong>Artinya:</strong></p>
                                                    <p class="mb-2">Jumlah unit yang harus dijual agar tidak rugi</p>
                                                    <small class="text-muted">Contoh: 100 unit = harus jual minimal 100 unit</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-cash-coin text-info display-6"></i>
                                                    <h6 class="mt-2 text-info">BEP (Rupiah)</h6>
                                                    <p class="mb-2"><strong>Artinya:</strong></p>
                                                    <p class="mb-2">Total pendapatan minimal yang harus dicapai</p>
                                                    <small class="text-muted">Contoh: Rp 5.000.000 = minimal pendapatan</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-shield-check text-warning display-6"></i>
                                                    <h6 class="mt-2 text-warning">Margin of Safety</h6>
                                                    <p class="mb-2"><strong>Artinya:</strong></p>
                                                    <p class="mb-2">Seberapa aman bisnis dari titik impas</p>
                                                    <small class="text-muted">Semakin tinggi semakin aman</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Target BEP Section -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-target me-2"></i>
                                            Target BEP dan Progress Tracking
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-target text-warning display-6"></i>
                                                    <h6 class="mt-2 text-warning">Target BEP</h6>
                                                    <p class="mb-2"><strong>Fungsi:</strong></p>
                                                    <p class="mb-2">Menetapkan target unit dan revenue yang ingin dicapai</p>
                                                    <small class="text-muted">Untuk perencanaan dan monitoring bisnis</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-graph-up text-success display-6"></i>
                                                    <h6 class="mt-2 text-success">Progress Tracking</h6>
                                                    <p class="mb-2"><strong>Fungsi:</strong></p>
                                                    <p class="mb-2">Memantau kemajuan menuju target BEP</p>
                                                    <small class="text-muted">Dengan progress bar dan status target</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-warning border-0">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-exclamation-triangle me-3 mt-1"></i>
                                            <div>
                                                <h6 class="alert-heading fw-bold">Kapan Harus Menggunakan BEP?</h6>
                                                <ul class="mb-0">
                                                    <li><strong>Memulai Bisnis:</strong> Untuk mengetahui apakah bisnis layak</li>
                                                    <li><strong>Menambah Produk:</strong> Untuk menghitung harga jual yang tepat</li>
                                                    <li><strong>Evaluasi Bisnis:</strong> Untuk mengetahui efisiensi operasional</li>
                                                    <li><strong>Perencanaan:</strong> Untuk target penjualan yang realistis</li>
                                                    <li><strong>Target Setting:</strong> Untuk menetapkan dan memantau target BEP</li>
                                                    <li><strong>Progress Monitoring:</strong> Untuk tracking kemajuan bisnis</li>
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
