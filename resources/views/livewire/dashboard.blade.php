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

</main>
