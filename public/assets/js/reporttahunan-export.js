// Report Tahunan Export Functions

// Fungsi export ke CSV
function exportTableToCSV() {
    const table = document.getElementById('reportTable');
    if (!table) {
        alert('Tabel tidak ditemukan!');
        return;
    }
    
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    
    // Get current year from page title or data attribute
    const currentYear = document.querySelector('[data-year]')?.dataset.year || new Date().getFullYear();
    link.setAttribute('download', `laporan_tahunan_${currentYear}.csv`);
    
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success message
    showNotification('Export CSV berhasil!', 'success');
}

// Fungsi export ke PDF (placeholder)
function exportTableToPDF() {
    showNotification('Fitur export PDF akan segera tersedia!', 'info');
    // TODO: Implement PDF export using jsPDF or similar library
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Print functionality
function printReport() {
    window.print();
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add print button event listener if exists
    const printBtn = document.querySelector('[onclick="window.print()"]');
    if (printBtn) {
        printBtn.onclick = printReport;
    }
});
