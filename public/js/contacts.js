
document.addEventListener('DOMContentLoaded', function () {

    // PAGE ELEMENTS
    const table = document.getElementById('contactsTable');
    const tableInfo = document.getElementById('tableInfo');
    const searchInput = document.getElementById('search');
    const clearSortingBtn = document.getElementById('clearSortingBtn');

    // REUSABLE PAGINATION ELEMENTS
    const perPageSelect = document.getElementById('contactsPerPage');
    const pageInfo = document.getElementById('contactsPageInfo');
    const previousBtn = document.getElementById('contactsPreviousBtn');
    const nextBtn = document.getElementById('contactsNextBtn');

    if (!table) return;

    // PAGE SETTINGS
    let currentPage = 1;
    let lastPage = 1;
    let perPage = perPageSelect ? Number(perPageSelect.value) : 25;
    let searchTimer;

    // SORTING SETTINGS
    let sortBy = null;
    let sortDirection = null;


    // ESCAPE HTML
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }


    // UPDATE SORT ICONS
    function updateSortIcons() {
        document.querySelectorAll('.sort-heading').forEach(button => {
            const icon = button.querySelector('.sort-icon');

            button.classList.remove('active');
            icon.className = 'sort-icon';

            if (button.dataset.sort === sortBy) {
                button.classList.add('active');
                icon.classList.add(sortDirection);

                icon.innerHTML = `
                    <span class="line-one"></span>
                    <span class="line-two"></span>
                    <span class="line-three"></span>
                `;
            } else {
                icon.innerHTML = '';
            }
        });

        if (clearSortingBtn) {
            clearSortingBtn.disabled = !sortBy;
            clearSortingBtn.style.opacity = sortBy ? '1' : '0.5';
        }
    }


    // EDIT ICON
    function editIcon(contactId) {
        return `
            <button
                type="button"
                class="action-btn edit-btn"
                data-action="edit"
                data-id="${contactId}"
                data-tooltip="Edit"
                aria-label="Edit contact"
            >
                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                </svg>
            </button>
        `;
    }


    // DELETE ICON
    function deleteIcon(contactId) {
        return `
            <button
                type="button"
                class="action-btn delete-btn"
                data-action="delete"
                data-id="${contactId}"
                data-tooltip="Delete"
                aria-label="Delete contact"
            >
                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="m19 6-1 14H6L5 6"/>
                    <path d="M10 11v5"/>
                    <path d="M14 11v5"/>
                </svg>
            </button>
        `;
    }


    // LOAD CONTACTS
    async function loadContacts(page = 1) {

        currentPage = page;

        table.innerHTML = `
            <tr>
                <td colspan="8" class="message">Loading contacts...</td>
            </tr>
        `;

        tableInfo.textContent = 'Loading contacts...';

        try {

            const params = new URLSearchParams({
                page: currentPage,
                per_page: perPage,
                search: searchInput.value.trim()
            });

            if (sortBy && sortDirection) {
                params.set('sort_by', sortBy);
                params.set('sort_direction', sortDirection);
            }

            const response = await fetch(
                `/api/v1/contacts?${params.toString()}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                }
            );

            if (!response.ok) {
                throw new Error(
                    response.status === 401
                        ? 'Please log in again to view contacts.'
                        : 'Unable to load contacts. Please try again.'
                );
            }

            const result = await response.json();

            currentPage = result.current_page || 1;
            lastPage = result.last_page || 1;

            // NO CONTACTS FOUND
            if (!result.data || result.data.length === 0) {

                table.innerHTML = `
                    <tr>
                        <td colspan="8" class="message">
                            No contacts found.
                        </td>
                    </tr>
                `;

            } else {

                // DISPLAY CONTACTS
                table.innerHTML = result.data.map(contact => {

                    const isActive =
                        contact.broker_status !== 'inactive' &&
                        contact.broker_status !== 'I';

                    return `
                        <tr>
                            <td>${escapeHtml(contact.brokerage_name)}</td>
                            <td>${escapeHtml(contact.contact)}</td>
                            <td>${escapeHtml(contact.email)}</td>
                            <td>${escapeHtml(contact.cell_phone)}</td>
                            <td>${escapeHtml(contact.city)}</td>
                            <td>${escapeHtml(contact.province_state)}</td>

                            <td>
                                <span class="status ${isActive
                                    ? 'status-active'
                                    : 'status-inactive'}">
                                    ${isActive ? 'Active' : 'Inactive'}
                                </span>
                            </td>

                            <td class="actions-cell">
                                ${editIcon(Number(contact.id))}
                                ${deleteIcon(Number(contact.id))}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            // TABLE INFORMATION
            tableInfo.textContent =
                `Showing ${result.from ?? 0}–${result.to ?? 0} of ${result.total} contacts`;

            // PAGINATION INFORMATION
            if (pageInfo) {
                pageInfo.textContent =
                    `Page ${currentPage} of ${lastPage}`;
            }

            if (previousBtn) {
                previousBtn.disabled = !result.prev_page_url;
            }

            if (nextBtn) {
                nextBtn.disabled = !result.next_page_url;
            }

        } catch (error) {

            table.innerHTML = `
                <tr>
                    <td colspan="8" class="message error-message">
                        ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;

            tableInfo.textContent = 'Contacts could not be loaded.';

            if (previousBtn) previousBtn.disabled = true;
            if (nextBtn) nextBtn.disabled = true;
        }
    }


    // HANDLE CONTACT ACTIONS
    table.addEventListener('click', async function (event) {

        const button = event.target.closest('[data-action]');

        if (!button) return;

        const contactId = button.dataset.id;
        const action = button.dataset.action;

        const row = button.closest('tr');

        const brokerageName = row?.querySelector('td')?.textContent.trim()
            || 'this contact';


        // EDIT CONTACT
        if (action === 'edit') {

            ContactForm.openEdit(contactId);

            return;
        }


        // DELETE CONTACT
        if (action === 'delete') {

            const confirmed = await AppConfirmation.confirm({
                title: 'Delete Contact',
                message: `Are you sure you want to delete <strong><u>${escapeHtml(brokerageName)}</u></strong>?`,
                confirmText: 'Delete Contact',
                cancelText: 'Cancel',
                icon: '!',
                type: 'warning'
            });

            if (!confirmed) return;

            button.disabled = true;

            try {

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

                const response = await fetch(
                    `/api/v1/contacts/${encodeURIComponent(contactId)}`,
                    {
                        method: 'DELETE',
                        headers: headers,
                        credentials: 'same-origin'
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'Unable to delete contact.'
                    );
                }

                await AppConfirmation.success(
                    `${brokerageName} has been moved to Deleted Contacts.`,
                    'Contact Deleted'
                );

                await loadContacts(currentPage);

            } catch (error) {

                await AppConfirmation.error(
                    error.message || 'Something went wrong while deleting the contact.'
                );

            } finally {
                button.disabled = false;
            }
        }
    });


    // THREE-CLICK SORTING
    document.querySelectorAll('.sort-heading').forEach(button => {

        button.addEventListener('click', function () {

            const selectedField = this.dataset.sort;

            if (sortBy !== selectedField) {

                sortBy = selectedField;
                sortDirection = 'asc';

            } else if (sortDirection === 'asc') {

                sortDirection = 'desc';

            } else {

                sortBy = null;
                sortDirection = null;
            }

            updateSortIcons();
            loadContacts(1);
        });
    });


    // CLEAR SORTING
    if (clearSortingBtn) {

        clearSortingBtn.addEventListener('click', function () {

            sortBy = null;
            sortDirection = null;

            updateSortIcons();
            loadContacts(1);
        });
    }


    // SEARCH CONTACTS
    searchInput.addEventListener('input', function () {

        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            loadContacts(1);
        }, 350);
    });


    // CHANGE PAGE SIZE
    if (perPageSelect) {

        perPageSelect.addEventListener('change', function () {

            perPage = Number(this.value);

            loadContacts(1);
        });
    }


    // PREVIOUS PAGE
    if (previousBtn) {

        previousBtn.addEventListener('click', function () {

            if (currentPage > 1) {
                loadContacts(currentPage - 1);
            }
        });
    }


    // NEXT PAGE
    if (nextBtn) {

        nextBtn.addEventListener('click', function () {

            if (currentPage < lastPage) {
                loadContacts(currentPage + 1);
            }
        });
    }


    // INITIAL PAGE LOAD
    updateSortIcons();
    loadContacts();

});
