<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1><i class="bi bi-bar-chart-line me-2"></i>Laporan Tahunan</h1>
        </div>
        <section class="section dashboard">
            <div class="row">
                <div class="col-12">
                    {{-- Filter Tahun --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body pt-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <label for="tahun-filter" class="form-label fw-bold">Pilih Tahun Laporan</label>
                                    <input id="tahun-filter" type="number" wire:model.live="tahun" class="form-control form-control-lg" min="2000" max="{{ now()->year }}" placeholder="{{ $tahun ?? now()->year }}">
                                </div>
                                <div class="col-md-8">
                                    <div wire:loading wire:target="tahun" class="text-primary">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Memuat...</span>
                                        </div> Memperbarui laporan...
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
                        .info-card, .stats-card {
                            height: 100%;
                        }
                        
                        .info-card .card-body, .stats-card .card-body {
                            display: flex;
                            flex-direction: column;
                            height: 100%;
                        }
                        
                        .info-card .d-flex {
                            flex: 1;
                        }
                    </style>
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card info-card sales-card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-arrow-down-right-circle me-2"></i>Total Pendapatan</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-light">
                                            <i class="bi bi-cash-coin text-success fs-4"></i>
                                        </div>
                                        <div class="ps-3 flex-grow-1">
                                            <h6 class="text-truncate">Rp{{ number_format($yearlySummary['pendapatan'] ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card info-card revenue-card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-arrow-up-right-circle me-2"></i>Biaya Variabel</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-light">
                                            <i class="bi bi-cart-x text-warning fs-4"></i>
                                        </div>
                                        <div class="ps-3 flex-grow-1">
                                            <h6 class="text-truncate">Rp{{ number_format($yearlySummary['pengeluaran_variabel'] ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card info-card customers-card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-building me-2"></i>Biaya Tetap</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info-light">
                                            <i class="bi bi-house-gear text-info fs-4"></i>
                                        </div>
                                        <div class="ps-3 flex-grow-1">
                                            <h6 class="text-truncate">Rp{{ number_format($yearlySummary['pengeluaran_tetap'] ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card info-card customers-card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-calculator me-2"></i>Laba Bersih</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'bg-primary-light' : 'bg-danger-light' }}">
                                            <i class="bi {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'bi-graph-up-arrow text-primary fs-4' : 'bi-graph-down-arrow text-danger fs-4' }}"></i>
                                        </div>
                                        <div class="ps-3 flex-grow-1">
                                            <h6 class="text-truncate {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'text-primary' : 'text-danger' }}">Rp{{ number_format($yearlySummary['laba'] ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Statistik Tambahan --}}
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card stats-card text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-calendar-check me-2"></i>Rata-rata Pendapatan Bulanan</h5>
                                    <p class="card-text fs-4 text-success text-truncate">Rp{{ number_format($stats['rata_rata_pendapatan'] ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card stats-card text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-cart-dash me-2"></i>Rata-rata Biaya Variabel Bulanan</h5>
                                    <p class="card-text fs-4 text-warning text-truncate">Rp{{ number_format($stats['rata_rata_pengeluaran_variabel'] ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card stats-card text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-trophy me-2"></i>Bulan Laba Tertinggi</h5>
                                    <p class="card-text fs-4 text-primary">{{ $stats['bulan_laba_tertinggi'] ?? 'N/A' }}</p>
                                    <span class="text-muted text-truncate d-block"> (Rp{{ number_format($stats['laba_tertinggi'] ?? 0, 0, ',', '.') }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Grafik Garis Tren Bulanan --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Grafik Tren Bulanan ({{ $tahun }})</h5>
                            <div id="monthlyTrendChart"></div>
                        </div>
                    </div>
                    {{-- Grafik Batang Perbandingan Tahunan --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Perbandingan Laporan Tahunan</h5>
                            <div id="yearlyComparisonChart"></div>
                        </div>
                    </div>
                    {{-- Tabel Rekap Tahunan --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Rekap Laporan Tahunan {{ $tahun }}</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Bulan</th>
                                            <th scope="col">Pendapatan</th>
                                            <th scope="col">Biaya Variabel</th>
                                            <th scope="col">Biaya Tetap</th>
                                            <th scope="col">Total Pengeluaran</th>
                                            <th scope="col">Laba</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($laporan as $data)
                                            <tr>
                                                <td>{{ $data['bulan'] }}</td>
                                                <td class="text-success"> Rp{{ number_format($data['pendapatan'], 0, ',', '.') }} </td>
                                                <td class="text-warning"> Rp{{ number_format($data['pengeluaran_variabel'], 0, ',', '.') }} </td>
                                                <td class="text-info"> Rp{{ number_format($data['pengeluaran_tetap'], 0, ',', '.') }} </td>
                                                <td class="text-danger"> Rp{{ number_format($data['pengeluaran'], 0, ',', '.') }} </td>
                                                <td class="fw-bold {{ $data['laba'] >= 0 ? 'text-success' : 'text-danger' }}"> {{ $data['laba'] >= 0 ? '+' : '' }} Rp{{ number_format($data['laba'], 0, ',', '.') }} </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-5">
                                                    <i class="bi bi-inbox fs-1"></i><br> Tidak ada data laporan untuk tahun {{ $tahun }}.
                                                </td>
                                            </tr>
                                        @endforelse
                                        @if (count($laporan) > 0)
                                            <tr class="fw-bold table-light">
                                                <td>Total</td>
                                                <td class="text-success"> Rp{{ number_format($yearlySummary['pendapatan'] ?? 0, 0, ',', '.') }} </td>
                                                <td class="text-warning"> Rp{{ number_format($yearlySummary['pengeluaran_variabel'] ?? 0, 0, ',', '.') }} </td>
                                                <td class="text-info"> Rp{{ number_format($yearlySummary['pengeluaran_tetap'] ?? 0, 0, ',', '.') }} </td>
                                                <td class="text-danger"> Rp{{ number_format($yearlySummary['pengeluaran'] ?? 0, 0, ',', '.') }} </td>
                                                <td class="{{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}"> {{ ($yearlySummary['laba'] ?? 0) >= 0 ? '+' : '' }} Rp{{ number_format($yearlySummary['laba'] ?? 0, 0, ',', '.') }} </td>
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
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:init', () => {
        let monthlyTrendChart;
        let yearlyComparisonChart;

        @php
            $defaultMonthlyData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $pendapatanData = $chartData['pendapatan'] ?? $defaultMonthlyData;
            $biayaVariabelData = $chartData['pengeluaran_variabel'] ?? $defaultMonthlyData;
            $biayaTetapData = $chartData['pengeluaran_tetap'] ?? $defaultMonthlyData;
            $totalPengeluaranData = $chartData['pengeluaran'] ?? $defaultMonthlyData;
            $labaData = $chartData['laba'] ?? $defaultMonthlyData;
            $categoriesData = $chartData['categories'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        @endphp
        const monthlyTrendOptions = {
            series: [{
                name: 'Pendapatan',
                data: @json($pendapatanData)
            }, {
                name: 'Biaya Variabel',
                data: @json($biayaVariabelData)
            }, {
                name: 'Biaya Tetap',
                data: @json($biayaTetapData)
            }, {
                name: 'Total Pengeluaran',
                data: @json($totalPengeluaranData)
            }, {
                name: 'Laba',
                data: @json($labaData)
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            colors: ['#28a745', '#ffc107', '#17a2b8', '#dc3545', '#007bff'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                type: 'category',
                categories: @json($categoriesData)
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp" + new Intl.NumberFormat('id-ID').format(value);
                    }
                },
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return "Rp" + new Intl.NumberFormat('id-ID').format(value);
                    }
                },
                theme: 'light'
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                },
            },
            markers: {
                size: 4,
                hover: {
                    size: 6
                }
            },
            legend: {
                show: true,
                position: 'top'
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "vertical",
                    shadeIntensity: 0.5,
                    gradientToColors: undefined,
                    inverseColors: true,
                    opacityFrom: 0.7,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
        };

        @php
            $previousYearData = [
                $comparisonData['previous_year']['pendapatan'] ?? 0,
                $comparisonData['previous_year']['pengeluaran_variabel'] ?? 0,
                $comparisonData['previous_year']['pengeluaran_tetap'] ?? 0,
                $comparisonData['previous_year']['pengeluaran'] ?? 0,
                $comparisonData['previous_year']['laba'] ?? 0
            ];
            $currentYearData = [
                $comparisonData['current_year']['pendapatan'] ?? 0,
                $comparisonData['current_year']['pengeluaran_variabel'] ?? 0,
                $comparisonData['current_year']['pengeluaran_tetap'] ?? 0,
                $comparisonData['current_year']['pengeluaran'] ?? 0,
                $comparisonData['current_year']['laba'] ?? 0
            ];
        @endphp
        const yearlyComparisonOptions = {
            series: [{
                name: @json($comparisonData['previous_year']['tahun'] ?? 'Tahun Sebelumnya'),
                data: @json($previousYearData)
            }, {
                name: @json($comparisonData['current_year']['tahun'] ?? 'Tahun Ini'),
                data: @json($currentYearData)
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    endingShape: 'rounded',
                    borderRadius: 5,
                    dataLabels: {
                        position: 'top'
                    }
                },
            },
            colors: ['#6c757d', '#0d6efd'],
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#304758"]
                },
                formatter: function (val) {
                    return "Rp" + new Intl.NumberFormat('id-ID').format(val);
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['Pendapatan', 'Biaya Variabel', 'Biaya Tetap', 'Total Pengeluaran', 'Laba'],
            },
            yaxis: {
                title: {
                    text: 'Rupiah'
                },
                labels: {
                    formatter: function (value) {
                        return "Rp" + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                    }
                }
            },
            legend: {
                position: 'top'
            }
        };

        monthlyTrendChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyTrendOptions);
        monthlyTrendChart.render();

        yearlyComparisonChart = new ApexCharts(document.querySelector("#yearlyComparisonChart"), yearlyComparisonOptions);
        yearlyComparisonChart.render();

        Livewire.on('reportUpdated', ({ chartData, comparisonData }) => {
            monthlyTrendChart.updateOptions({
                series: [{
                    name: 'Pendapatan',
                    data: chartData.pendapatan || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                }, {
                    name: 'Biaya Variabel',
                    data: chartData.pengeluaran_variabel || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                }, {
                    name: 'Biaya Tetap',
                    data: chartData.pengeluaran_tetap || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                }, {
                    name: 'Total Pengeluaran',
                    data: chartData.pengeluaran || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                }, {
                    name: 'Laba',
                    data: chartData.laba || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                }],
                xaxis: {
                    categories: chartData.categories || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
                }
            });

            yearlyComparisonChart.updateSeries([{
                name: comparisonData.previous_year.tahun || 'Tahun Sebelumnya',
                data: [
                    comparisonData.previous_year.pendapatan || 0,
                    comparisonData.previous_year.pengeluaran_variabel || 0,
                    comparisonData.previous_year.pengeluaran_tetap || 0,
                    comparisonData.previous_year.pengeluaran || 0,
                    comparisonData.previous_year.laba || 0
                ]
            }, {
                name: comparisonData.current_year.tahun || 'Tahun Ini',
                data: [
                    comparisonData.current_year.pendapatan || 0,
                    comparisonData.current_year.pengeluaran_variabel || 0,
                    comparisonData.current_year.pengeluaran_tetap || 0,
                    comparisonData.current_year.pengeluaran || 0,
                    comparisonData.current_year.laba || 0
                ]
            }]);
        });
    });
</script>