
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts - {{ config('branding.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contacts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/confirmation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pop-up-forms.css') }}">
</head>

<body>

<div class="app-layout">

    @include('components.sidebar', ['activePage' => 'contacts'])

    <main class="main-content">

        <header class="topbar">
            <h2>Contacts</h2>

            <div class="topbar-actions">
                <div class="profile">
                    {{ Auth::user()->name }}
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <div class="container">

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

                    @if(auth()->user()->isAdmin())
                        <a
                            href="{{ route('contacts.deleted') }}"
                            class="primary-btn"
                            style="background:#011d48; text-decoration:none;"
                        >
                            Deleted Contacts
                        </a>
                    @endif

                    <button type="button" class="primary-btn" id="addContactBtn">
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
                                <th class="actions-heading">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="contactsTable">
                            <tr>
                                <td colspan="8" class="message">
                                    Loading contacts...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @include('components.pagination', ['idPrefix' => 'contacts'])

            </section>

        </div>

    </main>

</div>
@include('components.add-edit-contact')
@include('components.confirmation')

<script src="{{ asset('js/confirmation.js') }}" defer></script>
<script src="{{ asset('js/pop-up-forms.js') }}" defer></script>
<script src="{{ asset('js/contact-form.js') }}" defer></script>
<script src="{{ asset('js/contacts.js') }}" defer></script>

</body>
</html>
