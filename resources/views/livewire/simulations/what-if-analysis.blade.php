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
                                                        <h4 class="text-success mb-1">Rp {{ number_format($analysisResults['what_if']['revenue'], 0, ',', '.') }}</h4>
                                                        <small class="{{ $analysisResults['what_if']['revenue_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $analysisResults['what_if']['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($analysisResults['what_if']['revenue_change_percent'], 1) }}%
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="text-center p-3 border rounded mb-3">
                                                        <h6 class="text-muted">Laba Bersih</h6>
                                                        <h4 class="{{ $analysisResults['what_if']['profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-1">
                                                            Rp {{ number_format($analysisResults['what_if']['profit'], 0, ',', '.') }}
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
                                                        <h6 class="text-warning">Rp {{ number_format($analysisResults['what_if']['break_even_revenue'], 0, ',', '.') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-2 border rounded">
                                                        <h6 class="text-muted">Break-even Units</h6>
                                                        <h6 class="text-warning">{{ number_format($analysisResults['what_if']['break_even_units'], 0, ',', '.') }}</h6>
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
                                                                <td><small>Rp {{ number_format($data['aktual'], 0, ',', '.') }}</small></td>
                                                                <td><small>Rp {{ number_format($data['what_if'], 0, ',', '.') }}</small></td>
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

                            {{-- Chart Visualisasi --}}
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-pie-chart me-2"></i>
                                                Perbandingan Pendapatan
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="revenueChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-bar-chart me-2"></i>
                                                Perbandingan Laba
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="profitChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Breakdown Biaya --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-pie-chart me-2"></i>
                                                Breakdown Biaya
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="costsChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

    @if(!empty($chartData))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('livewire:init', () => {
                // Revenue Chart
                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                new Chart(revenueCtx, {
                    type: 'bar',
                    data: @json($chartData['revenue']),
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Profit Chart
                const profitCtx = document.getElementById('profitChart').getContext('2d');
                new Chart(profitCtx, {
                    type: 'bar',
                    data: @json($chartData['profit']),
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Costs Chart
                const costsCtx = document.getElementById('costsChart').getContext('2d');
                new Chart(costsCtx, {
                    type: 'bar',
                    data: @json($chartData['costs']),
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</div>
