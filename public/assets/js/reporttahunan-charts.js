// Report Tahunan Charts JavaScript
// Data akan di-inject dari Blade template

function initializeReportTahunanCharts(chartData, comparisonData) {
    let monthlyTrendChart;
    let yearlyComparisonChart;

    const monthlyTrendOptions = {
        series: [{
            name: 'Pendapatan',
            data: chartData.pendapatan
        }, {
            name: 'Biaya Variabel',
            data: chartData.pengeluaran_variabel
        }, {
            name: 'Biaya Tetap',
            data: chartData.pengeluaran_tetap
        }, {
            name: 'Total Pengeluaran',
            data: chartData.pengeluaran
        }, {
            name: 'Laba',
            data: chartData.laba
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
            categories: chartData.categories
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return "Rp" + new Intl.NumberFormat('id-ID').format(value);
                }
            },
        },
        tooltip: {
            y: {
                formatter: function(value) {
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
            name: comparisonData.previous_year.tahun,
            data: comparisonData.previous_year.data
        }, {
            name: comparisonData.current_year.tahun,
            data: comparisonData.current_year.data
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
            formatter: function(val) {
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
                formatter: function(value) {
                    return "Rp" + new Intl.NumberFormat('id-ID').format(value);
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                }
            }
        },
        legend: {
            position: 'top'
        }
    };

    // Render charts
    monthlyTrendChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyTrendOptions);
    monthlyTrendChart.render();

    yearlyComparisonChart = new ApexCharts(document.querySelector("#yearlyComparisonChart"), yearlyComparisonOptions);
    yearlyComparisonChart.render();

    // Return chart instances for potential updates
    return {
        monthlyTrendChart,
        yearlyComparisonChart
    };
}

// Function to update charts when data changes
function updateReportTahunanCharts(chartData, comparisonData) {
    // Update monthly trend chart
    if (window.monthlyTrendChart) {
        window.monthlyTrendChart.updateOptions({
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
    }

    // Update yearly comparison chart
    if (window.yearlyComparisonChart) {
        window.yearlyComparisonChart.updateSeries([{
            name: comparisonData.previous_year.tahun || 'Tahun Sebelumnya',
            data: comparisonData.previous_year.data || [0, 0, 0, 0, 0]
        }, {
            name: comparisonData.current_year.tahun || 'Tahun Ini',
            data: comparisonData.current_year.data || [0, 0, 0, 0, 0]
        }]);
    }
}
