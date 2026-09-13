document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    const name = document.getElementById('category_name');
    const slug = document.getElementById('category_slug');
    if (name && slug && !slug.value) {
        name.addEventListener('input', () => {
            if (slug.dataset.touched === '1') return;
            slug.value = name.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        });
        slug.addEventListener('input', () => { slug.dataset.touched = '1'; });
    }
});
