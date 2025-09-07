// Report Tahunan Data Handler

// Fungsi untuk mengambil data dari data attributes
function getChartDataFromAttributes() {
    const monthlyChartElement = document.getElementById('monthlyTrendChart');
    const yearlyChartElement = document.getElementById('yearlyComparisonChart');
    
    if (!monthlyChartElement || !yearlyChartElement) {
        console.error('Chart elements not found');
        return null;
    }
    
    const chartData = {
        pendapatan: monthlyChartElement.dataset.pendapatan ? monthlyChartElement.dataset.pendapatan.split(',').map(Number) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        pengeluaran_variabel: monthlyChartElement.dataset.pengeluaranVariabel ? monthlyChartElement.dataset.pengeluaranVariabel.split(',').map(Number) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        pengeluaran_tetap: monthlyChartElement.dataset.pengeluaranTetap ? monthlyChartElement.dataset.pengeluaranTetap.split(',').map(Number) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        pengeluaran: monthlyChartElement.dataset.pengeluaran ? monthlyChartElement.dataset.pengeluaran.split(',').map(Number) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        laba: monthlyChartElement.dataset.laba ? monthlyChartElement.dataset.laba.split(',').map(Number) : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        categories: monthlyChartElement.dataset.categories ? monthlyChartElement.dataset.categories.split(',') : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des']
    };

    const comparisonData = {
        previous_year: {
            tahun: yearlyChartElement.dataset.previousYearTahun || 'Tahun Sebelumnya',
            data: [
                parseFloat(yearlyChartElement.dataset.previousYearPendapatan) || 0,
                parseFloat(yearlyChartElement.dataset.previousYearPengeluaranVariabel) || 0,
                parseFloat(yearlyChartElement.dataset.previousYearPengeluaranTetap) || 0,
                parseFloat(yearlyChartElement.dataset.previousYearPengeluaran) || 0,
                parseFloat(yearlyChartElement.dataset.previousYearLaba) || 0
            ]
        },
        current_year: {
            tahun: yearlyChartElement.dataset.currentYearTahun || 'Tahun Ini',
            data: [
                parseFloat(yearlyChartElement.dataset.currentYearPendapatan) || 0,
                parseFloat(yearlyChartElement.dataset.currentYearPengeluaranVariabel) || 0,
                parseFloat(yearlyChartElement.dataset.currentYearPengeluaranTetap) || 0,
                parseFloat(yearlyChartElement.dataset.currentYearPengeluaran) || 0,
                parseFloat(yearlyChartElement.dataset.currentYearLaba) || 0
            ]
        }
    };
    
    return { chartData, comparisonData };
}

// Export function
window.getChartDataFromAttributes = getChartDataFromAttributes;
