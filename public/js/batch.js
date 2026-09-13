document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bt-block-form').forEach(form => {
        form.addEventListener('submit', e => {
            const reason = form.querySelector('input[name="reason"]')?.value.trim();
            if (!reason) { e.preventDefault(); alert('Please enter a block reason.'); return; }
            if (!confirm('Block this batch? It will no longer be available for allocation.')) e.preventDefault();
        });
    });
});
