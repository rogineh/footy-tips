// Footy Tips Application - Main JS
document.addEventListener('DOMContentLoaded', () => {
    console.log('Footy Tips Application Loaded');
    
    // Auto-fade alerts if any exist
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});
