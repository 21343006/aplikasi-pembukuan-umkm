// Report Tahunan Progress Bar Functions

// Fungsi untuk mengatur progress bar
function updateProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar[data-value][data-max]');
    progressBars.forEach(bar => {
        const value = parseFloat(bar.dataset.value) || 0;
        const max = parseFloat(bar.dataset.max) || 1;
        const percentage = Math.min((value / Math.max(max, 1)) * 100, 100);
        
        // Animate progress bar
        bar.style.transition = 'width 0.8s ease-in-out';
        bar.style.width = percentage + '%';
        
        // Add tooltip with percentage
        if (!bar.title) {
            bar.title = `${percentage.toFixed(1)}%`;
        }
    });
}

// Jalankan progress bar update setelah DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    updateProgressBars();
    
    // Add hover effect to progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        bar.addEventListener('mouseenter', function() {
            this.style.transform = 'scaleY(1.2)';
        });
        
        bar.addEventListener('mouseleave', function() {
            this.style.transform = 'scaleY(1)';
        });
    });
});

// Export function untuk digunakan di file lain
window.updateProgressBars = updateProgressBars;
