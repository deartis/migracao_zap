import Chart from 'chart.js/auto';
// Toggle sidebar on desktop
document.querySelector('.menu-toggle').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('collapsed');
    document.querySelector('.main-content').classList.toggle('expanded');
});

// Toggle sidebar on mobile
document.querySelector('.mobile-menu-toggle').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('mobile-active');
    document.querySelector('.sidebar-overlay').classList.toggle('active');
});

// Close sidebar when clicking overlay
document.querySelector('.sidebar-overlay').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.remove('mobile-active');
    document.querySelector('.sidebar-overlay').classList.remove('active');
});

// Handle window resize
function handleResize() {
    if (window.innerWidth < 992) {
        // Mobile view
        document.querySelector('.sidebar').classList.remove('collapsed');
        document.querySelector('.main-content').classList.remove('expanded');
    }
}

// Initial check
handleResize();

// Listen for window resize
window.addEventListener('resize', handleResize);

//=======================================
// Chart.js Configuration
//=======================================
const createDonutChart = (elementId, percentage, colors) => {
    const ctx = document.getElementById(elementId).getContext('2d');

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [percentage, 100 - percentage],
                backgroundColor: colors,
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });
};

// Usage Chart - 30%
createDonutChart('usageChart', window.usoPacote, ['#e9ecef','#0dcaf0']);

// Messages Chart - 10/4000 (0.25%)
createDonutChart('messagesChart', window.usoPacote, ['#e9ecef','#20c997']);

// Errors Chart - 2 errors
createDonutChart('errorsChart', window.totalErros, ['#dc3545','#e9ecef']);
