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
                                <div id="pieChart"></div>

                                <script>
                                    document.addEventListener('livewire:init', function() {
                                        new ApexCharts(document.querySelector("#pieChart"), {
                                            series: [{{ $totalDailyIncome }}, {{ $totalDailyExpenditure }}],
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

        </div>
    </section>

</main>
