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