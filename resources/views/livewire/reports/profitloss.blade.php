<main id="main" class="main py-4">
    <div class="pagetitle mb-3">
        <h1 class="text-center">Laporan Laba Rugi</h1>
    </div>

    <section class="section dashboard">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <!-- Card Ringkasan -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white text-center">
                            <h4 class="mb-0">Ringkasan Laba Rugi</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped mb-0">
                                <tbody>
                                    <tr>
                                        <th>Total Pendapatan</th>
                                        <td class="text-end text-success fw-semibold">
                                            Rp {{ number_format($pendapatan, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Pengeluaran</th>
                                        <td class="text-end text-danger fw-semibold">
                                            Rp {{ number_format($pengeluaran, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="table-{{ $labaRugi >= 0 ? 'success' : 'danger' }}">
                                        <th>Laba / Rugi</th>
                                        <td class="text-end fw-bold {{ $labaRugi >= 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($labaRugi, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-center small text-muted">
                            Terakhir diperbarui: {{ now()->format('d M Y, H:i') }}
                        </div>
                    </div>

                    <!-- Card Grafik Garis -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white text-center">
                            <h5 class="mb-0">Grafik Garis Laba Rugi</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="profitLineChart" height="100"></canvas>
                        </div>
                    </div>

                    <!-- Card Grafik Pie -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-warning text-dark text-center">
                            <h5 class="mb-0">Diagram Pie Laba Rugi</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="profitPieChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <a href="/dashboard" class="btn btn-secondary mt-2">
                        Kembali
                </a>
            </div>
        </div>
    </section>
</main>

<!-- Script Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Grafik Garis -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const lineCtx = document.getElementById('profitLineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: ['Pendapatan', 'Pengeluaran', 'Laba/Rugi'],
                datasets: [{
                    label: 'Jumlah (Rp)',
                    data: [
                        {{ (float) $pendapatan }},
                        {{ (float) $pengeluaran }},
                        {{ (float) $labaRugi }}
                    ],
                    fill: false,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    tension: 0.3,
                    pointBackgroundColor: [
                        'rgba(25, 135, 84, 1)', // green untuk pendapatan
                        'rgba(220, 53, 69, 1)', // red untuk pengeluaran
                        '{{ $labaRugi >= 0 ? "rgba(13, 110, 253, 1)" : "rgba(255, 193, 7, 1)" }}' // biru atau kuning
                    ],
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });

        // Grafik Pie
        const pieCtx = document.getElementById('profitPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Pendapatan', 'Pengeluaran', 'Laba/Rugi'],
                datasets: [{
                    data: [
                        {{ (float) $pendapatan }},
                        {{ (float) $pengeluaran }},
                        {{ abs((float) $labaRugi) }}
                    ],
                    backgroundColor: [
                        'rgba(25, 135, 84, 0.7)', // hijau
                        'rgba(220, 53, 69, 0.7)', // merah
                        '{{ $labaRugi >= 0 ? "rgba(13, 110, 253, 0.7)" : "rgba(255, 193, 7, 0.7)" }}' // biru / kuning
                    ],
                    borderColor: [
                        'rgba(25, 135, 84, 1)',
                        'rgba(220, 53, 69, 1)',
                        '{{ $labaRugi >= 0 ? "rgba(13, 110, 253, 1)" : "rgba(255, 193, 7, 1)" }}'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return `${label}: Rp ${new Intl.NumberFormat('id-ID').format(value)}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
