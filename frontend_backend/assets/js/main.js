// IVOR Paine Memorial Hospital — Main JS
// Activates Bootstrap tooltips and marks active nav link

document.addEventListener('DOMContentLoaded', function () {

    // Activate Bootstrap tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));

    // Mark active nav link based on current URL
    const path = window.location.pathname;
    document.querySelectorAll('.navbar .nav-link, .navbar .dropdown-item').forEach(link => {
        if (link.getAttribute('href') && path.endsWith(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });

    // Auto-dismiss alerts after 6 seconds
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 6000);
    });
});
