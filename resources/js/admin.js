document.addEventListener('DOMContentLoaded', () => {

    const app = document.getElementById('adminApp');

    const sidebar = document.getElementById('adminSidebar');

    const overlay = document.getElementById('sidebarOverlay');

    const mobileMenuBtn =
        document.getElementById('mobileMenuBtn');

    const collapseBtn =
        document.getElementById('sidebarCollapse');

    const userMenu =
        document.getElementById('userMenu');

    const userTrigger =
        userMenu?.querySelector(
            '.user-menu-trigger'
        );

    const searchBtn =
        document.getElementById('globalSearchBtn');

    const searchModal =
        document.getElementById('searchModal');

    const searchInput =
        document.getElementById('globalSearchInput');

    const notificationBtn =
        document.getElementById(
            'notificationBtn'
        );

    const notificationPanel =
        document.getElementById(
            'notificationPanel'
        );


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    */

    collapseBtn?.addEventListener(
        'click',
        () => {

            app?.classList.toggle(
                'sidebar-collapsed'
            );

        }
    );


    mobileMenuBtn?.addEventListener(
        'click',
        () => {

            sidebar?.classList.add(
                'mobile-open'
            );

            overlay?.classList.add(
                'open'
            );

        }
    );


    overlay?.addEventListener(
        'click',
        () => {

            sidebar?.classList.remove(
                'mobile-open'
            );

            overlay?.classList.remove(
                'open'
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | USER MENU
    |--------------------------------------------------------------------------
    */

    userTrigger?.addEventListener(
        'click',
        (event) => {

            event.stopPropagation();

            userMenu?.classList.toggle(
                'open'
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    notificationBtn?.addEventListener(
        'click',
        (event) => {

            event.stopPropagation();

            notificationPanel?.classList.toggle(
                'open'
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    function openSearch() {

        searchModal?.classList.add(
            'open'
        );

        setTimeout(() => {
            searchInput?.focus();
        }, 100);

    }


    function closeSearch() {

        searchModal?.classList.remove(
            'open'
        );

        if (searchInput) {
            searchInput.value = '';
        }

    }


    searchBtn?.addEventListener(
        'click',
        openSearch
    );


    searchModal
        ?.querySelector(
            '.search-modal-backdrop'
        )
        ?.addEventListener(
            'click',
            closeSearch
        );


    /*
    |--------------------------------------------------------------------------
    | KEYBOARD SHORTCUTS
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        (event) => {

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                openSearch();

            }


            if (
                event.key === 'Escape'
            ) {

                closeSearch();

                userMenu?.classList.remove(
                    'open'
                );

                notificationPanel?.classList.remove(
                    'open'
                );

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | OUTSIDE CLICK
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        () => {

            userMenu?.classList.remove(
                'open'
            );

            notificationPanel?.classList.remove(
                'open'
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | PREVENT DROPDOWN CLOSING
    |--------------------------------------------------------------------------
    */

    userMenu?.addEventListener(
        'click',
        (event) => {
            event.stopPropagation();
        }
    );

    notificationPanel?.addEventListener(
        'click',
        (event) => {
            event.stopPropagation();
        }
    );

});
/* =========================================================
 | PRODUCT MODULE
========================================================= */

(() => {
    const input = document.querySelector('[data-product-image-input]');
    const upload = document.querySelector('[data-product-upload]');
    const preview = document.querySelector('[data-product-preview]');

    upload?.addEventListener('click', () => input?.click());

    input?.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file || !preview) return;
        const url = URL.createObjectURL(file);
        preview.innerHTML = `<img src="${url}" alt="Product preview">`;
    });

    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmMessage || 'Are you sure?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll('.product-filter-form input[name="search"]').forEach((search) => {
        let timer;
        search.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (search.value.length >= 3 || search.value.length === 0) {
                    search.form?.requestSubmit();
                }
            }, 500);
        });
    });

    document.querySelectorAll('[data-price-edit-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.priceEditToggle);
            if (!target) return;
            target.classList.toggle('open');
            button.textContent = target.classList.contains('open') ? 'Close' : 'Edit';
        });
    });

    document.querySelectorAll('[data-price-form]').forEach((form) => {
        const min = form.querySelector('[name="min_quantity"]');
        const max = form.querySelector('[name="max_quantity"]');
        max?.addEventListener('blur', () => {
            if (min?.value && max.value && Number(max.value) < Number(min.value)) {
                max.setCustomValidity('Maximum quantity must be greater than or equal to minimum quantity.');
            } else {
                max.setCustomValidity('');
            }
        });
    });
})();
