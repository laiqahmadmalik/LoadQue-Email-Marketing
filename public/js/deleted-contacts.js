document.addEventListener('DOMContentLoaded', function () {

    // PAGE ELEMENTS
    const tableBody = document.getElementById('deletedContactsTable');
    const searchInput = document.getElementById('deletedSearch');
    const tableInfo = document.getElementById('deletedTableInfo');
    const paginationSummary = document.getElementById('deletedPaginationSummary');

    // PAGINATION ELEMENTS
    const perPageSelect = document.getElementById('deletedPerPage');
    const pageInfo = document.getElementById('deletedPageInfo');
    const previousBtn = document.getElementById('deletedPreviousBtn');
    const nextBtn = document.getElementById('deletedNextBtn');

    // PAGE SETTINGS
    let currentPage = 1;
    let perPage = perPageSelect ? Number(perPageSelect.value) : 25;
    let searchTimeout = null;

    if (!tableBody) return;


    // LOAD DELETED CONTACTS
    async function loadDeletedContacts() {

        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="message">Loading deleted contacts...</td>
            </tr>
        `;

        try {
            const params = new URLSearchParams({
                search: searchInput ? searchInput.value.trim() : '',
                per_page: perPage,
                page: currentPage
            });

            const response = await fetch(
                `/api/v1/contacts/deleted?${params.toString()}`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to load deleted contacts.'
                );
            }

            // UPDATE TABLE INFORMATION
            if (tableInfo) {
                tableInfo.textContent =
                    `Showing ${result.from || 0}–${result.to || 0} of ${result.total} deleted contacts`;
            }

            if (paginationSummary && tableInfo) {
                paginationSummary.textContent =
                    `${tableInfo.textContent} · ${perPage} per page`;
            }
            // UPDATE PAGINATION INFORMATION
            const lastPage = result.last_page || 1;

            if (pageInfo) {
                pageInfo.textContent = `Page ${result.current_page || 1} of ${lastPage}`;
            }

            if (previousBtn) {
                previousBtn.disabled = !result.prev_page_url;
            }

            if (nextBtn) {
                nextBtn.disabled = !result.next_page_url;
            }

            // NO CONTACTS FOUND
            if (!result.data || result.data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="message">
                            No deleted contacts found.
                        </td>
                    </tr>
                `;
                return;
            }

            // DISPLAY CONTACTS
            tableBody.innerHTML = result.data.map(contact => `
                <tr>
                    <td>${escapeHtml(contact.contact_id || '')}</td>
                    <td>${escapeHtml(contact.brokerage_name || '')}</td>
                    <td>${escapeHtml(contact.contact || '')}</td>
                    <td>${escapeHtml(contact.email || '')}</td>
                    <td>${escapeHtml(contact.cell_phone || '')}</td>
                    <td>${formatDate(contact.deleted_at)}</td>
                    <td>${escapeHtml(contact.deleted_by_name || 'Unknown')}</td>

                    <td style="white-space: nowrap;">
                        <button
                            type="button"
                            class="restore-btn"
                            data-id="${contact.id}">
                            Restore
                        </button>

                        <button
                            type="button"
                            class="permanent-delete-btn"
                            data-id="${contact.id}">
                            Delete Permanently
                        </button>
                    </td>
                </tr>
            `).join('');

        } catch (error) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="message">
                        ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;
        }
    }


    // ESCAPE HTML
    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }


    // FORMAT DATE
    function formatDate(value) {
        if (!value) return '';

        const date = new Date(value);

        return date.toLocaleString();
    }


    // CSRF HEADERS
    function getRequestHeaders() {

        const xsrfCookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='));

        const headers = {
            'Accept': 'application/json'
        };

        if (xsrfCookie) {
            headers['X-XSRF-TOKEN'] = decodeURIComponent(
                xsrfCookie.split('=').slice(1).join('=')
            );
        }

        return headers;
    }


    // HANDLE RESTORE BUTTON
    tableBody.addEventListener('click', async function (event) {

        const button = event.target.closest('.restore-btn');

        if (!button) return;

        const contactId = button.dataset.id;
        const row = button.closest('tr');

        const brokerageName = row?.children[1]?.textContent.trim()
            || 'this contact';

        const confirmed = await AppConfirmation.confirm({
            title: 'Restore Contact',
            message: `Are you sure you want to restore <strong><u>${escapeHtml(brokerageName)}</u></strong>?`,
            confirmText: 'Restore Contact',
            cancelText: 'Cancel',
            icon: '✓',
            type: 'success'
        });

        if (!confirmed) return;

        button.disabled = true;

        try {

            const response = await fetch(
                `/api/v1/contacts/${encodeURIComponent(contactId)}/restore`,
                {
                    method: 'POST',
                    headers: getRequestHeaders(),
                    credentials: 'same-origin'
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to restore contact.'
                );
            }

            await AppConfirmation.success(
                `${brokerageName} has been restored successfully.`,
                'Contact Restored'
            );

            await loadDeletedContacts();

        } catch (error) {

            await AppConfirmation.error(error.message);

        } finally {
            button.disabled = false;
        }
    });


    // HANDLE PERMANENT DELETE BUTTON
    tableBody.addEventListener('click', async function (event) {

        const button = event.target.closest('.permanent-delete-btn');

        if (!button) return;

        const contactId = button.dataset.id;
        const row = button.closest('tr');

        const brokerageName = row?.children[1]?.textContent.trim()
            || 'this contact';

        const confirmed = await AppConfirmation.confirm({
            title: 'Permanently Delete Contact',
            message: `Are you sure you want to permanently delete <strong><u>${escapeHtml(brokerageName)}</u></strong>? This action cannot be undone.`,
            confirmText: 'Delete Permanently',
            cancelText: 'Cancel',
            icon: '!',
            type: 'warning'
        });

        if (!confirmed) return;

        button.disabled = true;

        try {

            const response = await fetch(
                `/api/v1/contacts/${encodeURIComponent(contactId)}/permanent`,
                {
                    method: 'DELETE',
                    headers: getRequestHeaders(),
                    credentials: 'same-origin'
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to permanently delete contact.'
                );
            }

            await AppConfirmation.success(
                `${brokerageName} has been permanently deleted.`,
                'Contact Permanently Deleted'
            );

            await loadDeletedContacts();

        } catch (error) {

            await AppConfirmation.error(
                error.message || 'Something went wrong.'
            );

        } finally {
            button.disabled = false;
        }
    });


    // SEARCH DELETED CONTACTS
    if (searchInput) {

        searchInput.addEventListener('input', function () {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(function () {
                currentPage = 1;
                loadDeletedContacts();
            }, 400);

        });
    }


    // CHANGE PAGE SIZE
    if (perPageSelect) {

        perPageSelect.addEventListener('change', function () {

            perPage = Number(this.value);
            currentPage = 1;

            loadDeletedContacts();

        });
    }


    // PREVIOUS PAGE
    if (previousBtn) {

        previousBtn.addEventListener('click', function () {

            if (currentPage > 1) {
                currentPage--;
                loadDeletedContacts();
            }

        });
    }


    // NEXT PAGE
    if (nextBtn) {

        nextBtn.addEventListener('click', function () {

            currentPage++;
            loadDeletedContacts();

        });
    }


    // INITIAL LOAD
    loadDeletedContacts();

});
