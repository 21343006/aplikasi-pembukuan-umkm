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
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="card info-card sales-card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-arrow-down-right-circle me-2"></i>Total Pendapatan</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-light">
                                            <i class="bi bi-cash-coin text-success fs-4"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>Rp{{ number_format($yearlySummary['pendapatan'] ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="card info-card revenue-card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-arrow-up-right-circle me-2"></i>Total Pengeluaran</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-danger-light">
                                            <i class="bi bi-cart-x text-danger fs-4"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>Rp{{ number_format($yearlySummary['pengeluaran'] ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <div class="card info-card customers-card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-calculator me-2"></i>Laba Bersih</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'bg-primary-light' : 'bg-danger-light' }}">
                                            <i class="bi {{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'bi-graph-up-arrow text-primary fs-4' : 'bi-graph-down-arrow text-danger fs-4' }}"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6 class="{{ ($yearlySummary['laba'] ?? 0) >= 0 ? 'text-primary' : 'text-danger' }}">Rp{{ number_format($yearlySummary['laba'] ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Statistik Tambahan --}}
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card stats-card text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-calendar-check me-2"></i>Rata-rata Pendapatan Bulanan</h5>
                                    <p class="card-text fs-4 text-success">Rp{{ number_format($stats['rata_rata_pendapatan'] ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card stats-card text-center border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-trophy me-2"></i>Bulan Laba Tertinggi</h5>
                                    <p class="card-text fs-4 text-primary">{{ $stats['bulan_laba_tertinggi'] ?? 'N/A' }}</p>
                                    <span class="text-muted"> (Rp{{ number_format($stats['laba_tertinggi'] ?? 0, 0, ',', '.') }})</span>
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
                                            <th scope="col">Pengeluaran</th>
                                            <th scope="col">Laba</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($laporan as $data)
                                            <tr>
                                                <td>{{ $data['bulan'] }}</td>
                                                <td class="text-success"> Rp{{ number_format($data['pendapatan'], 0, ',', '.') }} </td>
                                                <td class="text-danger"> Rp{{ number_format($data['pengeluaran'], 0, ',', '.') }} </td>
                                                <td class="fw-bold {{ $data['laba'] >= 0 ? 'text-success' : 'text-danger' }}"> {{ $data['laba'] >= 0 ? '+' : '' }} Rp{{ number_format($data['laba'], 0, ',', '.') }} </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-5">
                                                    <i class="bi bi-inbox fs-1"></i><br> Tidak ada data laporan untuk tahun {{ $tahun }}.
                                                </td>
                                            </tr>
                                        @endforelse
                                        @if (count($laporan) > 0)
                                            <tr class="fw-bold table-light">
                                                <td>Total</td>
                                                <td class="text-success"> Rp{{ number_format($yearlySummary['pendapatan'] ?? 0, 0, ',', '.') }} </td>
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

        const monthlyTrendOptions = {
            series: [{
                name: 'Pendapatan',
                data: @json($chartData['pendapatan'] ?? [])
            }, {
                name: 'Pengeluaran',
                data: @json($chartData['pengeluaran'] ?? [])
            }, {
                name: 'Laba',
                data: @json($chartData['laba'] ?? [])
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
            colors: ['#28a745', '#dc3545', '#007bff'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                type: 'category',
                categories: @json($chartData['categories'] ?? [])
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

        const yearlyComparisonOptions = {
            series: [{
                name: @json($comparisonData['previous_year']['tahun'] ?? ''),
                data: @json([ $comparisonData['previous_year']['pendapatan'] ?? 0, $comparisonData['previous_year']['pengeluaran'] ?? 0, $comparisonData['previous_year']['laba'] ?? 0 ])
            }, {
                name: @json($comparisonData['current_year']['tahun'] ?? ''),
                data: @json([ $comparisonData['current_year']['pendapatan'] ?? 0, $comparisonData['current_year']['pengeluaran'] ?? 0, $comparisonData['current_year']['laba'] ?? 0 ])
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
                categories: ['Pendapatan', 'Pengeluaran', 'Laba'],
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
                    data: chartData.pendapatan
                }, {
                    name: 'Pengeluaran',
                    data: chartData.pengeluaran
                }, {
                    name: 'Laba',
                    data: chartData.laba
                }],
                xaxis: {
                    categories: chartData.categories
                }
            });

            yearlyComparisonChart.updateSeries([{
                name: comparisonData.previous_year.tahun,
                data: [comparisonData.previous_year.pendapatan, comparisonData.previous_year.pengeluaran, comparisonData.previous_year.laba]
            }, {
                name: comparisonData.current_year.tahun,
                data: [comparisonData.current_year.pendapatan, comparisonData.current_year.pengeluaran, comparisonData.current_year.laba]
            }]);
        });
    });
</script>