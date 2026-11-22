const table = document.getElementById('contactsTable');
const tableInfo = document.getElementById('tableInfo');
const pageInfo = document.getElementById('pageInfo');
const searchInput = document.getElementById('search');
const previousBtn = document.getElementById('previousBtn');
const nextBtn = document.getElementById('nextBtn');
const clearSortingBtn = document.getElementById('clearSortingBtn');

let currentPage = 1;
let lastPage = 1;
let searchTimer;

let sortBy = null;
let sortDirection = null;

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

    clearSortingBtn.disabled = !sortBy;
    clearSortingBtn.style.opacity = sortBy ? '1' : '0.5';
}

async function loadContacts(page = 1) {
    currentPage = page;

    table.innerHTML = `
        <tr>
            <td colspan="7" class="message">Loading contacts...</td>
        </tr>
    `;

    tableInfo.textContent = 'Loading contacts...';

    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: 25,
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

        lastPage = result.last_page || 1;

        if (!result.data || result.data.length === 0) {
            table.innerHTML = `
                <tr>
                    <td colspan="7" class="message">
                        No contacts found.
                    </td>
                </tr>
            `;
        } else {
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
                    </tr>
                `;
            }).join('');
        }

        tableInfo.textContent =
            `Showing ${result.from ?? 0}–${result.to ?? 0} of ${result.total} contacts`;

        pageInfo.textContent =
            `Page ${result.current_page} of ${result.last_page}`;

        previousBtn.disabled = !result.prev_page_url;
        nextBtn.disabled = !result.next_page_url;

    } catch (error) {
        table.innerHTML = `
            <tr>
                <td colspan="7" class="message error-message">
                    ${escapeHtml(error.message)}
                </td>
            </tr>
        `;

        tableInfo.textContent = 'Contacts could not be loaded.';
        previousBtn.disabled = true;
        nextBtn.disabled = true;
    }
}

// Three-click sorting
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

// Clear sorting
clearSortingBtn.addEventListener('click', function () {
    sortBy = null;
    sortDirection = null;

    updateSortIcons();
    loadContacts(1);
});

// Search
searchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(function () {
        loadContacts(1);
    }, 350);
});

// Pagination
previousBtn.addEventListener('click', function () {
    if (currentPage > 1) {
        loadContacts(currentPage - 1);
    }
});

nextBtn.addEventListener('click', function () {
    if (currentPage < lastPage) {
        loadContacts(currentPage + 1);
    }
});

// Initial page load
updateSortIcons();
loadContacts();
