document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('contactForm');
    const addButton = document.getElementById('addContactBtn');
    const modalId = 'contactModal';

    if (!form || !addButton) return;

    const contactIdInput = document.getElementById('contactId');
    const modalTitle = document.getElementById('contactModalTitle');
    const saveButton = document.getElementById('saveContactBtn');
    const errorBox = document.getElementById('contactFormErrors');

    const fields = [
        'brokerage_name',
        'contact',
        'authorizing_authority',
        'email',
        'cell_phone',
        'address',
        'city',
        'province_state',
        'zip_code'
    ];

    // Clear general form errors.
    function clearErrors() {
        errorBox.replaceChildren();
        errorBox.style.display = 'none';
    }

    // Clear validation error from one field.
    function clearFieldError(field) {
        const input = document.getElementById(field);
        const errorElement = document.getElementById(field + '_error');

        if (input) {
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
        }

        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.remove('is-visible');
        }
    }

    // Clear validation errors from all fields.
    function clearAllFieldErrors() {
        fields.forEach(field => clearFieldError(field));
    }

    // Display an error underneath its input.
    function showFieldError(field, message) {
        const input = document.getElementById(field);
        const errorElement = document.getElementById(field + '_error');

        if (!input || !errorElement) return;

        input.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');

        errorElement.textContent = message;
        errorElement.classList.add('is-visible');
    }

    // Display all validation errors in the form and popup.
    function displayValidationErrors(validationErrors) {
        clearAllFieldErrors();
        clearErrors();

        validationErrors.forEach(error => {
            const field = error.field;

            if (field && fields.includes(field)) {
                showFieldError(field, error.message);
            }
        });
    }

    // Convert Laravel's traditional validation response.
    function convertLaravelErrors(errors) {
        const converted = [];

        Object.entries(errors).forEach(([field, messages]) => {
            const messageList = Array.isArray(messages)
                ? messages
                : [messages];

            messageList.forEach(message => {
                converted.push({
                    field: field,
                    message: message
                });
            });
        });

        return converted;
    }

    // Convert our new structured response.
    function convertStructuredMessages(messages) {
        return messages
            .filter(item => item && item.message)
            .map(item => ({
                field: item.field || null,
                message: item.message
            }));
    }

    // Extract validation messages from either response format.
    function getValidationErrors(result) {
        if (Array.isArray(result.messages)) {
            const errorMessages = result.messages.filter(
                item => item.type === 'error'
            );

            if (errorMessages.length > 0) {
                return convertStructuredMessages(errorMessages);
            }
        }

        if (result.errors && typeof result.errors === 'object') {
            return convertLaravelErrors(result.errors);
        }

        return [];
    }

    function getXsrfToken() {
        const cookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='));

        return cookie
            ? decodeURIComponent(cookie.split('=').slice(1).join('='))
            : null;
    }

    function getHeaders() {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };

        const token = getXsrfToken();

        if (token) {
            headers['X-XSRF-TOKEN'] = token;
        }

        return headers;
    }

    function resetForm() {
        form.reset();

        contactIdInput.value = '';

        clearErrors();
        clearAllFieldErrors();

        modalTitle.textContent = 'Add New Contact';
        saveButton.textContent = 'Save Contact';
        saveButton.disabled = false;
    }

    function openAddForm() {
        resetForm();
        PopUpForms.open(modalId);
    }

    async function openEditForm(id) {
        resetForm();

        modalTitle.textContent = 'Edit Contact';
        saveButton.textContent = 'Update Contact';

        PopUpForms.open(modalId);

        saveButton.disabled = true;
        saveButton.textContent = 'Loading...';

        try {
            const response = await fetch(
                `/api/v1/contacts/${encodeURIComponent(id)}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to load contact details.'
                );
            }

            const contact = result.data;

            contactIdInput.value = contact.id;

            fields.forEach(field => {
                const input = document.getElementById(field);

                if (input) {
                    input.value = contact[field] ?? '';
                }
            });

        } catch (error) {
            AppConfirmation.error(error.message, 'Unable to Load Contact');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Update Contact';
        }
    }

    // Open Add Contact popup.
    addButton.addEventListener('click', openAddForm);

    // Allow contacts.js to open the Edit popup.
    window.ContactForm = {
        openAdd: openAddForm,
        openEdit: openEditForm
    };

    // Remove an individual field error when the user edits that field.
    fields.forEach(field => {
        const input = document.getElementById(field);

        if (input) {
            input.addEventListener('input', function () {
                clearFieldError(field);
            });
        }
    });

    // Save or update contact.
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        clearErrors();
        clearAllFieldErrors();

        const id = contactIdInput.value.trim();
        const isEditing = id !== '';

        const payload = {};

        fields.forEach(field => {
            payload[field] = document.getElementById(field).value.trim();
        });

        saveButton.disabled = true;
        saveButton.textContent = isEditing ? 'Updating...' : 'Saving...';

        try {
            const response = await fetch(
                isEditing
                    ? `/api/v1/contacts/${encodeURIComponent(id)}`
                    : '/api/v1/contacts',
                {
                    method: isEditing ? 'PUT' : 'POST',
                    headers: getHeaders(),
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                }
            );

            const result = await response.json();

            if (!response.ok) {
                const validationErrors = getValidationErrors(result);

                if (response.status === 422 && validationErrors.length > 0) {
                    displayValidationErrors(validationErrors);
                    return;
                }

                const serverMessages = Array.isArray(result.messages)
                    ? result.messages.filter(item => item.message)
                    : [];

                if (serverMessages.length > 0) {
                    AppConfirmation.messages(
                        serverMessages,
                        'Unable to Save Contact'
                    );
                } else {
                    AppConfirmation.error(
                        result.message || 'Unable to save contact.',
                        'Something went wrong'
                    );
                }

                return;
            }

            PopUpForms.close(modalId);
            resetForm();

            const successMessage = Array.isArray(result.messages)
                ? result.messages.find(item => item.type === 'success')
                : null;

            await AppConfirmation.success(
                successMessage?.message ||
                result.message ||
                'Contact saved successfully.',
                isEditing ? 'Contact Updated' : 'Contact Created'
            );

            // Refresh the contacts table.
            if (typeof window.loadContacts === 'function') {
                await window.loadContacts(1);
            } else {
                window.location.reload();
            }

        } catch (error) {
            AppConfirmation.error(
                error.message || 'Unable to complete your request.',
                'Something went wrong'
            );
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = isEditing
                ? 'Update Contact'
                : 'Save Contact';
        }
    });

});
