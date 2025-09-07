<main id="main" class="main">

    <div class="pagetitle">
        <h1>Dashboard</h1>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <!-- Left side columns -->
            <div class="col-lg-8">
                <div class="row">
                    <!-- Welcome Message -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="mb-3 mt-3">Selamat Datang di Aplikasi Pembukuan UMKM</h2>
                                <p>
                                    Aplikasi ini dirancang untuk membantu pelaku Usaha Mikro, Kecil, dan Menengah (UMKM)
                                    dalam melakukan pencatatan keuangan secara sederhana namun efektif.
                                </p>
                            </div>
                        </div>
                    </div><!-- End Welcome Message -->

                    <!-- Saldo Terkini Widget -->
                    <div class="col-12">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Saldo Terkini</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-wallet2"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>Rp {{ number_format($currentBalance, 2, ',', '.') }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Saldo Terkini Widget -->

                    <!-- Quick Actions -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Tambah Cepat</h5>

                                <div class="d-grid gap-2 mt-3">
                                    <a href="{{ route('incomes') }}" class="btn btn-primary"><i
                                            class="bi bi-plus-circle"></i> Tambah Pemasukan</a>
                                    <a href="{{ route('expenditures') }}" class="btn btn-danger"><i
                                            class="bi bi-dash-circle"></i> Tambah Pengeluaran</a>
                                    <a href="{{ route('debt.receivable') }}" class="btn btn-warning"><i
                                            class="bi bi-journal-text"></i> Buku Utang & Piutang</a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Quick Actions -->

                    <!-- Pie Chart -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Perbandingan Total Pendapatan dan Pengeluaran</h5>
                                <!-- Pie Chart -->
                                <div id="pieChart" 
                                     data-daily-income="{{ $totalDailyIncome }}" 
                                     data-daily-expenditure="{{ $totalDailyExpenditure }}">
                                </div>

                                <script>
                                    document.addEventListener('livewire:init', function() {
                                        const pieChartElement = document.querySelector("#pieChart");
                                        const dailyIncome = parseFloat(pieChartElement.dataset.dailyIncome) || 0;
                                        const dailyExpenditure = parseFloat(pieChartElement.dataset.dailyExpenditure) || 0;
                                        
                                        new ApexCharts(pieChartElement, {
                                            series: [dailyIncome, dailyExpenditure],
                                            chart: {
                                                height: 350,
                                                type: 'pie',
                                                toolbar: {
                                                    show: true
                                                }
                                            },
                                            labels: ['Pemasukan', 'Pengeluaran']
                                        }).render();
                                    });
                                </script>
                                <!-- End Pie Chart -->
                            </div>
                        </div>
                    </div><!-- End Pie Chart -->
                </div>
            </div><!-- End Left side columns -->

            <!-- Right side columns -->
            <div class="col-lg-4">
                <!-- Debt & Receivable Summary -->
                <div class="row">
                    <!-- Total Utang -->
                    <div class="col-12">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Utang</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background-color: #dc3545;">
                                        <i class="bi bi-arrow-down-circle text-white"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>Rp {{ number_format($totalDebts, 0, ',', '.') }}</h6>
                                        <span class="text-danger small">Sisa: Rp {{ number_format($totalDebtsRemaining, 0, ',', '.') }}</span>
                                        <br><span class="text-success small">Sudah dibayar: Rp {{ number_format($totalDebtsPaid, 0, ',', '.') }}</span>
                                        @if($overdueDebts > 0)
                                            <br><span class="text-danger small">Jatuh tempo: {{ $overdueDebts }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Piutang -->
                    <div class="col-12">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Piutang</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background-color: #198754;">
                                        <i class="bi bi-arrow-up-circle text-white"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>Rp {{ number_format($totalReceivables, 0, ',', '.') }}</h6>
                                        <span class="text-success small">Sisa: Rp {{ number_format($totalReceivablesRemaining, 0, ',', '.') }}</span>
                                        <br><span class="text-success small">Sudah diterima: Rp {{ number_format($totalReceivablesPaid, 0, ',', '.') }}</span>
                                        @if($overdueReceivables > 0)
                                            <br><span class="text-warning small">Jatuh tempo: {{ $overdueReceivables }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Position -->
                    <div class="col-12">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Posisi Net</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" style="background-color: #0d6efd;">
                                        <i class="bi bi-calculator text-white"></i>
                                    </div>
                                    <div class="ps-3">
                                        @php
                                            $netPosition = $totalReceivablesRemaining - $totalDebtsRemaining;
                                        @endphp
                                        <h6 class="{{ $netPosition >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netPosition >= 0 ? '+' : '' }}Rp {{ number_format($netPosition, 0, ',', '.') }}
                                        </h6>
                                        <span class="small {{ $netPosition >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $netPosition >= 0 ? 'Positif' : 'Negatif' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Right side columns -->

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
                                <i class="bi bi-lightbulb text-primary display-4"></i>
                                <h4 class="mt-3 text-primary fw-bold">Panduan Dashboard UMKM</h4>
                                <p class="text-muted">Penjelasan sederhana untuk memahami informasi keuangan bisnis Anda</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-pie-chart me-2"></i>
                                            Grafik Pemasukan vs Pengeluaran
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-success">Pemasukan (Hijau):</strong> Total uang yang masuk dari penjualan
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-danger">Pengeluaran (Merah):</strong> Total biaya operasional bisnis
                                            </li>
                                            <li class="mb-2">
                                                <strong>Saldo Keuangan:</strong> Sisa uang setelah pengeluaran (Pemasukan - Pengeluaran)
                                            </li>
                                            <li class="mb-2">
                                                <strong>Tips:</strong> Pastikan pemasukan selalu lebih besar dari pengeluaran
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="h-100 p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-credit-card me-2"></i>
                                            Status Utang & Piutang
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <strong class="text-danger">Total Utang:</strong> Semua hutang yang belum dibayar
                                            </li>
                                            <li class="mb-2">
                                                <strong class="text-success">Total Piutang:</strong> Semua piutang yang belum diterima
                                            </li>
                                            <li class="mb-2">
                                                <strong>Posisi Net:</strong> Selisih piutang dan utang (Positif = untung, Negatif = rugi)
                                            </li>
                                            <li class="mb-2">
                                                <strong>Jatuh Tempo:</strong> Utang/piutang yang sudah melewati batas waktu
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="p-3 border rounded bg-white">
                                        <h6 class="fw-bold text-primary mb-3">
                                            <i class="bi bi-shield-check me-2"></i>
                                            Indikator Kesehatan Bisnis
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-check-circle text-success display-6"></i>
                                                    <h6 class="mt-2 text-success">Sehat</h6>
                                                    <small class="text-muted">Saldo positif, utang minimal, piutang lancar</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-exclamation-triangle text-warning display-6"></i>
                                                    <h6 class="mt-2 text-warning">Waspada</h6>
                                                    <small class="text-muted">Saldo menipis, ada utang jatuh tempo</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="text-center p-3 border rounded h-100">
                                                    <i class="bi bi-x-circle text-danger display-6"></i>
                                                    <h6 class="mt-2 text-danger">Kritis</h6>
                                                    <small class="text-muted">Saldo negatif, banyak utang, piutang macet</small>
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
                                                <h6 class="alert-heading fw-bold">Tindakan yang Perlu Dilakukan!</h6>
                                                <ul class="mb-0">
                                                    <li><strong>Setiap Hari:</strong> Periksa saldo kas dan transaksi harian</li>
                                                    <li><strong>Setiap Minggu:</strong> Review utang dan piutang yang jatuh tempo</li>
                                                    <li><strong>Setiap Bulan:</strong> Analisis tren pemasukan dan pengeluaran</li>
                                                    <li><strong>Setiap 3 Bulan:</strong> Evaluasi dan rencana pengembangan bisnis</li>
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
