document.addEventListener('DOMContentLoaded', () => {
    const frequency = document.getElementById('frequency');
    const weekdays = document.querySelector('[data-weekdays]');
    const dayOfMonth = document.querySelector('[data-day-of-month]');
    const syncScheduleFields = () => {
        const value = frequency?.value;
        if (weekdays) weekdays.hidden = value !== 'weekly';
        if (dayOfMonth) dayOfMonth.hidden = value !== 'monthly';
    };
    frequency?.addEventListener('change', syncScheduleFields);
    syncScheduleFields();

    document.querySelectorAll('[data-confirm-action]').forEach(form => {
        form.addEventListener('submit', e => {
            const message = form.dataset.confirmAction;
            if (message && !window.confirm(message)) e.preventDefault();
        });
    });
});
