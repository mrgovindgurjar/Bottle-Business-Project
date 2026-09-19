document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.report-filters').forEach(form => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.textContent = 'Loading...';
            }
        });
    });
});
