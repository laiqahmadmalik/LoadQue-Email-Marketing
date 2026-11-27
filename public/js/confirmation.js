(function () {
    const modal = document.getElementById('appModal');

    if (!modal) return;

    const title = document.getElementById('modalTitle');
    const message = document.getElementById('modalMessage');
    const icon = document.getElementById('modalIcon');
    const header = document.getElementById('modalHeader');

    const closeBtn = document.getElementById('modalClose');
    const cancelBtn = document.getElementById('modalCancel');
    const confirmBtn = document.getElementById('modalConfirm');
    const actions = document.getElementById('modalActions');
    const backdrop = modal.querySelector('.modal-backdrop');

    let resolver = null;
    let previousFocus = null;
    let busy = false;

    // Convert field names such as cell_phone into Cell Phone.
    function formatFieldName(field) {
        if (!field) return '';

        return String(field)
            .replace(/_/g, ' ')
            .replace(/\b\w/g, character => character.toUpperCase());
    }

    // Display one message safely.

    function renderSingleMessage(text) {
        message.replaceChildren();

        const messageText = document.createElement('div');
        messageText.className = 'modal-message-text';

        appendSafeFormattedText(
            messageText,
            text || ''
        );

        message.appendChild(messageText);
    }


    // Display multiple messages safely.

    function appendSafeFormattedText(container, rawText) {
        const template = document.createElement('template');
        template.innerHTML = String(rawText || '');

        function appendNodes(source, target) {
            source.childNodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    target.appendChild(
                        document.createTextNode(node.textContent)
                    );
                    return;
                }

                if (node.nodeType !== Node.ELEMENT_NODE) {
                    return;
                }

                const tag = node.tagName.toLowerCase();

                if (tag === 'strong' || tag === 'u') {
                    const safeElement = document.createElement(tag);
                    appendNodes(node, safeElement);
                    target.appendChild(safeElement);
                } else {
                    // Ignore unsupported HTML elements and their attributes.
                    // Preserve their ordinary text content safely.
                    appendNodes(node, target);
                }
            });
        }

        appendNodes(template.content, container);
    }

    function renderMessages(messages) {
        message.replaceChildren();

        messages.forEach(item => {
            const messageItem = document.createElement('div');
            messageItem.className = 'modal-message-item';

            if (item.field) {
                const fieldLabel = document.createElement('span');
                fieldLabel.className = 'modal-message-field';
                fieldLabel.textContent = formatFieldName(item.field);

                messageItem.appendChild(fieldLabel);
            }

            const messageText = document.createElement('div');
            messageText.className = 'modal-message-text';

            appendSafeFormattedText(
                messageText,
                item.message || ''
            );

            messageItem.appendChild(messageText);
            message.appendChild(messageItem);
        });
    }


    function openModal(options = {}) {
        previousFocus = document.activeElement;

        title.textContent = options.title || 'Confirmation';

        const modalType = options.type || 'warning';
        const allowedTypes = ['success', 'error', 'warning', 'info'];
        const safeType = allowedTypes.includes(modalType)
            ? modalType
            : 'warning';

        header.className = 'modal-header ' + safeType;

        icon.textContent = options.icon || '!';

        if (Array.isArray(options.messages)) {
            renderMessages(options.messages);
        } else {
            renderSingleMessage(options.message || '');
        }

        confirmBtn.textContent = options.confirmText || 'Confirm';
        cancelBtn.textContent = options.cancelText || 'Cancel';

        const showCancel = options.showCancel !== false;

        cancelBtn.style.display = showCancel ? '' : 'none';
        actions.style.display = options.showActions === false ? 'none' : '';

        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';

        closeBtn.focus();
    }

    function closeModal(result = false) {
        if (busy) return;

        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';

        if (previousFocus && typeof previousFocus.focus === 'function') {
            previousFocus.focus();
        }

        const callback = resolver;
        resolver = null;

        if (callback) {
            callback(result);
        }
    }

    // Confirmation popup.
    function confirm(options = {}) {
        return new Promise(resolve => {
            resolver = resolve;

            openModal({
                ...options,
                showCancel: true,
                showActions: true
            });
        });
    }

    // Single-message popup.
    function showMessage(options = {}) {
        return new Promise(resolve => {
            resolver = resolve;

            openModal({
                ...options,
                showCancel: false,
                showActions: true,
                confirmText: options.confirmText || 'OK'
            });
        });
    }

    // Multiple-message popup.
    function showMultipleMessages(messages, titleText = 'Please review the following') {
        return showMessage({
            title: titleText,
            messages: messages,
            icon: '!',
            type: 'error'
        });
    }

    // Loading state for actions such as saving or deleting.
    function setLoading(loading, text = 'Processing...') {
        busy = loading;

        confirmBtn.disabled = loading;
        cancelBtn.disabled = loading;
        closeBtn.disabled = loading;

        if (loading) {
            confirmBtn.textContent = text;
        }
    }

    confirmBtn.addEventListener('click', function () {
        if (busy) return;
        closeModal(true);
    });

    cancelBtn.addEventListener('click', function () {
        closeModal(false);
    });

    closeBtn.addEventListener('click', function () {
        closeModal(false);
    });

    backdrop.addEventListener('click', function () {
        closeModal(false);
    });

    document.addEventListener('keydown', function (event) {
        if (!modal.classList.contains('is-visible')) return;

        if (event.key === 'Escape') {
            closeModal(false);
        }
    });

    // Public functions available throughout the application.
    window.AppConfirmation = {
        confirm: confirm,

        alert: showMessage,

        success: function (message, title = 'Success') {
            return showMessage({
                title: title,
                message: message,
                icon: '✓',
                type: 'success'
            });
        },

        error: function (message, title = 'Something went wrong') {
            return showMessage({
                title: title,
                message: message,
                icon: '!',
                type: 'error'
            });
        },

        warning: function (message, title = 'Warning') {
            return showMessage({
                title: title,
                message: message,
                icon: '!',
                type: 'warning'
            });
        },

        info: function (message, title = 'Information') {
            return showMessage({
                title: title,
                message: message,
                icon: 'i',
                type: 'info'
            });
        },

        messages: showMultipleMessages,

        setLoading: setLoading
    };
})();
