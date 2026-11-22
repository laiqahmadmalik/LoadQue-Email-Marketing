<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts - {{ config('branding.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/contacts.css') }}">
</head>

<body>

    <header class="header">
        <h1>{{ config('branding.name') }} - Contacts</h1>
        <p>Manage and organize your business contacts.</p>
    </header>

    <main class="container">

        <a href="{{ route('dashboard') }}" class="back-link">
            ← Back to Dashboard
        </a>

        
        <div class="toolbar">
            <input
                type="search"
                id="search"
                class="search"
                placeholder="Search brokerage, contact, email or phone..."
            >

            <div style="display:flex; gap:10px; align-items:center;">
                <button
                    type="button"
                    id="clearSortingBtn"
                    class="primary-btn"
                    style="background:#64748b;"
                >
                    Clear Sorting
                </button>

                <button type="button" class="primary-btn" disabled>
                    + Add Contact
                </button>
            </div>
        </div>


        <section class="card">

            <div class="table-info" id="tableInfo">
                Loading contacts...
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>
                                <button class="sort-heading" data-sort="brokerage_name">
                                    Brokerage Name <span class="sort-icon"></span>
                                </button>
                            </th>
                            <th>
                                <button class="sort-heading" data-sort="contact">
                                    Contact Person <span class="sort-icon"></span>
                                </button>
                            </th>
                            <th>
                                <button class="sort-heading" data-sort="email">
                                    Email <span class="sort-icon"></span>
                                </button>
                            </th>
                            <th>
                                <button class="sort-heading" data-sort="cell_phone">
                                    Phone <span class="sort-icon"></span>
                                </button>
                            </th>
                            <th>
                                <button class="sort-heading" data-sort="city">
                                    City <span class="sort-icon"></span>
                                </button>
                            </th>
                            <th>
                                <button class="sort-heading" data-sort="province_state">
                                    Province / State <span class="sort-icon"></span>
                                </button>
                            </th>
                            <th>
                                <button class="sort-heading" data-sort="broker_status">
                                    Status <span class="sort-icon"></span>
                                </button>
                            </th>
                        </tr>
                    </thead>


                    <tbody id="contactsTable">
                        <tr>
                            <td colspan="7" class="message">
                                Loading contacts...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <span id="pageInfo">Page 1</span>

                <div>
                    <button type="button" id="previousBtn" disabled>
                        Previous
                    </button>

                    <button type="button" id="nextBtn" disabled>
                        Next
                    </button>
                </div>
            </div>

        </section>

    </main>

   <script src="{{ asset('js/contacts.js') }}" defer></script>

</body>
</html>
