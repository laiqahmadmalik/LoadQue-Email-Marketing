
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Contacts - {{ config('branding.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contacts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/confirmation.css') }}">
</head>

<body>

<div class="app-layout">

    @include('components.sidebar', ['activePage' => 'contacts'])

    <main class="main-content">

        <header class="topbar">
            <h2>Deleted Contacts</h2>

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
                    id="deletedSearch"
                    class="search"
                    placeholder="Search brokerage, contact, email, phone or Contact ID..."
                >

                <a href="{{ route('contacts.index') }}" class="primary-btn"
                   style="text-decoration:none;">
                    ← Back to Contacts
                </a>
            </div>

            <section class="card">

                <div class="table-info" id="deletedTableInfo">
                    Deleted Contacts
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Contact ID</th>
                                <th>Brokerage Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Deleted At</th>
                                <th>Deleted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="deletedContactsTable">
                            <tr>
                                <td colspan="8" class="message">
                                    Loading deleted contacts...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @include('components.pagination', ['idPrefix' => 'deleted'])

            </section>

        </div>

    </main>

</div>

@include('components.confirmation')

<script src="{{ asset('js/confirmation.js') }}" defer></script>
<script src="{{ asset('js/deleted-contacts.js') }}" defer></script>

</body>
</html>
