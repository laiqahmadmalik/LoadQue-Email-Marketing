
document.addEventListener('DOMContentLoaded', function () {

    function open(modalId) {
        const modal = document.getElementById(modalId);

        if (!modal) {
            console.error(`Popup "${modalId}" was not found.`);
            return;
        }

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function close(modalId) {
        const modal = document.getElementById(modalId);

        if (!modal) return;

        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('[data-popup-overlay].show')) {
            document.body.style.overflow = '';
        }
    }

    function reset(formId) {
        const form = document.getElementById(formId);

        if (form) {
            form.reset();
        }
    }

    window.PopUpForms = {
        open,
        close,
        reset
    };

    // Close buttons
    document.addEventListener('click', function (event) {
        const closeButton = event.target.closest('[data-popup-close]');

        if (closeButton) {
            close(closeButton.dataset.popupClose);
        }
    });

    // Escape key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('[data-popup-overlay].show')
                .forEach(function (modal) {
                    close(modal.id);
                });
        }
    });

});
