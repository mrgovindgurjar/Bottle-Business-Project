document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.attendance-table select[name$="[status]"]').forEach(select => {
        const row = select.closest('tr');
        const inputs = row ? row.querySelectorAll('input[type="time"]') : [];
        const sync = () => {
            const disabled = ['absent','leave','holiday'].includes(select.value);
            inputs.forEach(input => input.disabled = disabled);
        };
        select.addEventListener('change', sync); sync();
    });
});
