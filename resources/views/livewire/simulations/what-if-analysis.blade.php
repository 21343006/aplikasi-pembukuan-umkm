<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-graph-up-arrow me-2"></i>Analisis What If</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Analisis What If</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    
                    {{-- Filter Periode --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Filter Periode Analisis</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="selectedMonth" class="form-label">Bulan</label>
                                    <select wire:model.live="selectedMonth" class="form-select" id="selectedMonth">
                                        @foreach($monthNames as $monthNum => $monthName)
                                            <option value="{{ $monthNum }}">{{ $monthName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="selectedYear" class="form-label">Tahun</label>
                                    <select wire:model.live="selectedYear" class="form-select" id="selectedYear">
                                        @for ($i = 2020; $i <= 2030; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($actualData))
                        {{-- Data Aktual --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Data Aktual Bulan {{ $monthNames[$selectedMonth] }} {{ $selectedYear }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <h4 class="text-success mb-1">Rp {{ number_format($actualData['revenue'], 0, ',', '.') }}</h4>
                                            <small class="text-muted">Total Pendapatan</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <h4 class="text-primary mb-1">{{ number_format($actualData['units'], 0, ',', '.') }}</h4>
                                            <small class="text-muted">Total Unit Terjual</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <h4 class="text-danger mb-1">Rp {{ number_format($actualData['total_cost'], 0, ',', '.') }}</h4>
                                            <small class="text-muted">Total Biaya</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center p-3 border rounded">
                                            <h4 class="{{ $actualData['profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                                                Rp {{ number_format($actualData['profit'], 0, ',', '.') }}
                                            </h4>
                                            <small class="text-muted">Laba Bersih</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Skenario What If --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    Skenario What If
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="selectedScenario" class="form-label">Pilih Skenario</label>
                                        <select wire:model.live="selectedScenario" class="form-select" id="selectedScenario">
                                            @foreach($scenarios as $key => $scenario)
                                                <option value="{{ $key }}">{{ $scenario['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @if(isset($scenarios[$selectedScenario]))
                                            <small class="text-muted">{{ $scenarios[$selectedScenario]['description'] }}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <button wire:click="resetParameters" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Reset Parameter
                                        </button>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="priceChangePercent" class="form-label">Perubahan Harga (%)</label>
                                        <input wire:model.live="priceChangePercent" type="number" class="form-control" 
                                               id="priceChangePercent" step="0.1" placeholder="0">
                                        <small class="text-muted">Contoh: 10 = naik 10%, -5 = turun 5%</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="volumeChangePercent" class="form-label">Perubahan Volume (%)</label>
                                        <input wire:model.live="volumeChangePercent" type="number" class="form-control" 
                                               id="volumeChangePercent" step="0.1" placeholder="0">
                                        <small class="text-muted">Contoh: 20 = naik 20%, -15 = turun 15%</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="costChangePercent" class="form-label">Perubahan Biaya Variabel (%)</label>
                                        <input wire:model.live="costChangePercent" type="number" class="form-control" 
                                               id="costChangePercent" step="0.1" placeholder="0">
                                        <small class="text-muted">Contoh: -10 = turun 10%, 5 = naik 5%</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fixedCostChangePercent" class="form-label">Perubahan Biaya Tetap (%)</label>
                                        <input wire:model.live="fixedCostChangePercent" type="number" class="form-control" 
                                               id="fixedCostChangePercent" step="0.1" placeholder="0">
                                        <small class="text-muted">Contoh: -5 = turun 5%, 8 = naik 8%</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hasil Analisis --}}
                        @if(!empty($analysisResults))
                            {{-- Business Insights untuk UMKM --}}
                            @if(!empty($businessInsights))
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-lightbulb me-2"></i>
                                            Insights Bisnis Anda
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($businessInsights as $key => $insight)
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-0 shadow-sm h-100">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-shrink-0">
                                                                    <i class="{{ $insight['icon'] }} fs-3"></i>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="fw-bold mb-2">{{ $insight['title'] }}</h6>
                                                                    <p class="mb-2 small text-muted">{{ $insight['description'] }}</p>
                                                                    <span class="badge 
                                                                        @if($insight['level'] === 'excellent') bg-success
                                                                        @elseif($insight['level'] === 'good') bg-primary
                                                                        @elseif($insight['level'] === 'fair' || $insight['level'] === 'stable') bg-warning
                                                                        @else bg-danger
                                                                        @endif">
                                                                        @if($insight['level'] === 'excellent') Sangat Baik
                                                                        @elseif($insight['level'] === 'good') Baik
                                                                        @elseif($insight['level'] === 'fair' || $insight['level'] === 'stable') Cukup
                                                                        @elseif($insight['level'] === 'poor' || $insight['level'] === 'declining') Perlu Perbaikan
                                                                        @elseif($insight['level'] === 'needs_improvement') Butuh Peningkatan
                                                                        @elseif($insight['level'] === 'risky') Berisiko
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                {{-- Perbandingan Utama --}}
                                <div class="col-lg-8">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-bar-chart me-2"></i>
                                                Perbandingan Skenario
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="text-center p-3 border rounded mb-3">
                                                        <h6 class="text-muted">Pendapatan</h6>
                                                        <h4 class="text-success mb-1">Rp {{ number_format($analysisResults['what_if']['revenue'], 0, '.', ',') }}</h4>
                                                        <small class="{{ $analysisResults['what_if']['revenue_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $analysisResults['what_if']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($analysisResults['what_if']['revenue_change_percent'], 1) }}%
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-center p-3 border rounded mb-3">
                                                        <h6 class="text-muted">Laba Bersih</h6>
                                                        <h4 class="{{ $analysisResults['what_if']['profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                                                            Rp {{ number_format($analysisResults['what_if']['profit'], 0, '.', ',') }}
                                                        </h4>
                                                        <small class="{{ $analysisResults['what_if']['profit_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $analysisResults['what_if']['profit_change'] >= 0 ? '+' : '' }}{{ number_format($analysisResults['what_if']['profit_change_percent'], 1) }}%
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Break-even Analysis --}}
                                            <div class="row mt-3">
                                                <div class="col-md-4">
                                                    <div class="text-center p-2 border rounded">
                                                        <h6 class="text-muted">Break-even Revenue</h6>
                                                        <h6 class="text-warning">Rp {{ number_format($analysisResults['what_if']['break_even_revenue'], 0, '.', ',') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-2 border rounded">
                                                        <h6 class="text-muted">Break-even Units</h6>
                                                        <h6 class="text-warning">{{ number_format($analysisResults['what_if']['break_even_units'], 0, '.', ',') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-2 border rounded">
                                                        <h6 class="text-muted">Margin of Safety</h6>
                                                        <h6 class="text-info">{{ number_format($analysisResults['what_if']['margin_of_safety'], 1) }}%</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tabel Perbandingan --}}
                                <div class="col-lg-4">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-table me-2"></i>
                                                Detail Perbandingan
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Metrik</th>
                                                            <th>Aktual</th>
                                                            <th>What If</th>
                                                            <th>Δ%</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($comparisonTable['metrics'] as $metric => $data)
                                                            <tr>
                                                                <td><small>{{ $metric }}</small></td>
                                                                <td><small>Rp {{ number_format($data['aktual'], 0, '.', ',') }}</small></td>
                                                                <td><small>Rp {{ number_format($data['what_if'], 0, '.', ',') }}</small></td>
                                                                <td>
                                                                    <small class="{{ $data['change_percent'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                        {{ $data['change_percent'] >= 0 ? '+' : '' }}{{ number_format($data['change_percent'], 1) }}%
                                                                    </small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            {{-- Rekomendasi untuk UMKM --}}
                            @if(!empty($recommendations))
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-lightbulb me-2"></i>
                                            Rekomendasi untuk Bisnis Anda
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        @foreach($recommendations as $recommendation)
                                            <div class="alert alert-{{ $recommendation['type'] }} border-0 mb-3">
                                                <div class="d-flex align-items-start">
                                                    <i class="bi 
                                                        @if($recommendation['type'] === 'success') bi-check-circle
                                                        @elseif($recommendation['type'] === 'warning') bi-exclamation-triangle
                                                        @elseif($recommendation['type'] === 'danger') bi-x-circle
                                                        @else bi-info-circle
                                                        @endif me-3 mt-1"></i>
                                                    <div class="flex-grow-1">
                                                        <h6 class="alert-heading fw-bold">{{ $recommendation['title'] }}</h6>
                                                        <p class="mb-2">{{ $recommendation['description'] }}</p>
                                                        <div class="accordion" id="recommendation{{ $loop->index }}">
                                                            <div class="accordion-item border-0">
                                                                <h2 class="accordion-header">
                                                                    <button class="accordion-button collapsed bg-transparent border-0 p-0" type="button" 
                                                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}" 
                                                                            aria-expanded="false">
                                                                        <small class="text-muted">Lihat Saran Tindakan</small>
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" 
                                                                     data-bs-parent="#recommendation{{ $loop->index }}">
                                                                    <div class="accordion-body p-0 pt-2">
                                                                        <ul class="mb-0">
                                                                            @foreach($recommendation['actions'] as $action)
                                                                                <li class="mb-1"><small>{{ $action }}</small></li>
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

                            {{-- Risk Assessment --}}
                            @if(!empty($riskAssessment))
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-shield-exclamation me-2"></i>
                                            Penilaian Risiko Bisnis
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center p-3 border rounded">
                                                    <h6 class="text-muted">Skor Risiko</h6>
                                                    <h4 class="
                                                        @if($riskAssessment['level'] === 'low') text-success
                                                        @elseif($riskAssessment['level'] === 'medium') text-warning
                                                        @elseif($riskAssessment['level'] === 'high') text-danger
                                                        @else text-danger
                                                        @endif">
                                                        {{ $riskAssessment['score'] }}/10
                                                    </h4>
                                                    <span class="badge 
                                                        @if($riskAssessment['level'] === 'low') bg-success
                                                        @elseif($riskAssessment['level'] === 'medium') bg-warning
                                                        @elseif($riskAssessment['level'] === 'high') bg-danger
                                                        @else bg-danger
                                                        @endif">
                                                        @if($riskAssessment['level'] === 'low') Rendah
                                                        @elseif($riskAssessment['level'] === 'medium') Sedang
                                                        @elseif($riskAssessment['level'] === 'high') Tinggi
                                                        @else Kritis
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <h6 class="fw-bold mb-2">Penilaian Keseluruhan</h6>
                                                <p class="mb-3">{{ $riskAssessment['overall_assessment'] }}</p>
                                                
                                                @if(!empty($riskAssessment['factors']))
                                                    <h6 class="fw-bold mb-2">Faktor Risiko yang Ditemukan:</h6>
                                                    @foreach($riskAssessment['factors'] as $factor)
                                                        <div class="alert alert-light border mb-2">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong>{{ $factor['factor'] }}</strong>
                                                                    <br><small class="text-muted">{{ $factor['impact'] }}</small>
                                                                    <br><small class="text-info"><i class="bi bi-lightbulb me-1"></i>{{ $factor['mitigation'] }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Action Plan --}}
                            @if(!empty($actionPlan))
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-list-check me-2"></i>
                                            Rencana Tindakan
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @if(!empty($actionPlan['immediate']))
                                                <div class="col-md-4">
                                                    <div class="card border-danger h-100">
                                                        <div class="card-header bg-danger text-white">
                                                            <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Segera (0-1 Bulan)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <ul class="mb-0">
                                                                @foreach($actionPlan['immediate'] as $action)
                                                                    <li class="mb-2"><small>{{ $action }}</small></li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if(!empty($actionPlan['short_term']))
                                                <div class="col-md-4">
                                                    <div class="card border-warning h-100">
                                                        <div class="card-header bg-warning text-dark">
                                                            <h6 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Jangka Pendek (1-6 Bulan)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <ul class="mb-0">
                                                                @foreach($actionPlan['short_term'] as $action)
                                                                    <li class="mb-2"><small>{{ $action }}</small></li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if(!empty($actionPlan['long_term']))
                                                <div class="col-md-4">
                                                    <div class="card border-success h-100">
                                                        <div class="card-header bg-success text-white">
                                                            <h6 class="mb-0"><i class="bi bi-calendar-month me-2"></i>Jangka Panjang (6+ Bulan)</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <ul class="mb-0">
                                                                @foreach($actionPlan['long_term'] as $action)
                                                                    <li class="mb-2"><small>{{ $action }}</small></li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- Buat Skenario Custom --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Buat Skenario Custom
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="scenarioName" class="form-label">Nama Skenario</label>
                                        <input wire:model="scenarioName" type="text" class="form-control" 
                                               id="scenarioName" placeholder="Contoh: Skenario Ekspansi">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="scenarioDescription" class="form-label">Deskripsi</label>
                                        <input wire:model="scenarioDescription" type="text" class="form-control" 
                                               id="scenarioDescription" placeholder="Deskripsi singkat skenario">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button wire:click="createCustomScenario" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>Buat Skenario
                                    </button>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- Tidak ada data --}}
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada data untuk periode yang dipilih</h5>
                                <p class="text-muted">Silakan pilih periode lain atau pastikan ada data transaksi</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </section>
    </main>


    {{-- Penjelasan untuk Pelaku UMKM Awam --}}
    <section class="bg-light py-5 mt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-lightbulb text-primary display-4"></i>
                                <h4 class="mt-3 text-primary fw-bold">Panduan Analisis What If</h4>
                                <p class="text-muted">Penjelasan sederhana untuk memahami simulasi skenario bisnis UMKM</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-question-circle me-2"></i>
                                            Apa itu Analisis What If?
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-info">Simulasi:</strong> Mencoba berbagai skenario bisnis
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-success">Perencanaan:</strong> Memprediksi hasil sebelum eksekusi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-warning">Pengambilan Keputusan:</strong> Membantu pilih strategi terbaik
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-gear me-2"></i>
                                            Jenis Skenario
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-success">Skenario Optimis:</strong> Kondisi terbaik yang bisa terjadi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-info">Skenario Realistis:</strong> Kondisi yang paling mungkin terjadi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-danger">Skenario Pesimis:</strong> Kondisi terburuk yang bisa terjadi
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-primary">Strategi Bisnis:</strong> Optimasi Biaya, Pertumbuhan, Ekspansi
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
                                            Komponen yang Dianalisis
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-cash-coin text-success display-6"></i>
                                                    <h6 class="mt-2 text-success">Pendapatan</h6>
                                                    <p class="mb-2"><strong>Yang Dihitung:</strong></p>
                                                    <p class="mb-2">Total uang dari penjualan</p>
                                                    <small class="text-muted">Bisa naik/turun sesuai skenario</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-credit-card text-danger display-6"></i>
                                                    <h6 class="mt-2 text-danger">Biaya</h6>
                                                    <p class="mb-2"><strong>Yang Dihitung:</strong></p>
                                                    <p class="mb-2">Total pengeluaran operasional</p>
                                                    <small class="text-muted">Bisa naik/turun sesuai skenario</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-graph-up text-info display-6"></i>
                                                    <h6 class="mt-2 text-info">Laba</h6>
                                                    <p class="mb-2"><strong>Yang Dihitung:</strong></p>
                                                    <p class="mb-2">Pendapatan dikurangi biaya</p>
                                                    <small class="text-muted">Menunjukkan untung/rugi</small>
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
                                                <h6 class="alert-heading fw-bold">Kapan Harus Menggunakan What If?</h6>
                                                <ul class="mb-0">
                                                    <li><strong>Sebelum Investasi:</strong> Untuk memprediksi hasil investasi</li>
                                                    <li><strong>Perubahan Harga:</strong> Untuk melihat dampak kenaikan/penurunan harga</li>
                                                    <li><strong>Ekspansi Bisnis:</strong> Untuk merencanakan pertumbuhan</li>
                                                    <li><strong>Manajemen Risiko:</strong> Untuk menyiapkan dana darurat</li>
                                                </ul>
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
                                                <h6 class="alert-heading fw-bold">Penjelasan Skenario Detail</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <ul class="mb-0">
                                                            <li><strong class="text-success">Skenario Optimis:</strong> Harga +15%, Volume +30%, Biaya -10%</li>
                                                            <li><strong class="text-info">Skenario Realistis:</strong> Harga +5%, Volume +10%, Biaya stabil</li>
                                                            <li><strong class="text-danger">Skenario Pesimis:</strong> Harga -10%, Volume -20%, Biaya +15%</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <ul class="mb-0">
                                                            <li><strong class="text-primary">Optimasi Biaya:</strong> Fokus kurangi biaya -20%</li>
                                                            <li><strong class="text-warning">Strategi Pertumbuhan:</strong> Harga +8%, Volume +35%</li>
                                                            <li><strong class="text-secondary">Ekspansi Pasar:</strong> Volume +50%, Investasi +25%</li>
                                                        </ul>
                                                    </div>
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
                                                <h6 class="alert-heading fw-bold">Tips Menggunakan What If!</h6>
                                                <ul class="mb-0">
                                                    <li><strong>Mulai Sederhana:</strong> Coba 2-3 skenario dulu</li>
                                                    <li><strong>Gunakan Data Nyata:</strong> Berdasarkan kondisi bisnis saat ini</li>
                                                    <li><strong>Pertimbangkan Eksternal:</strong> Faktor ekonomi, kompetisi, trend</li>
                                                    <li><strong>Update Berkala:</strong> Sesuaikan dengan perubahan bisnis</li>
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
