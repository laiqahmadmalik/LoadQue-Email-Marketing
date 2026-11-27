<aside class="sidebar">
    <div class="brand-logo">
        <img src="{{ asset('images/loadque-logo.png') }}"
             alt="LOADQUE Technologies">
    </div>

    <div class="brand-subtitle">
        {{ strtoupper(config('branding.tagline')) }}
    </div>

    <div class="nav-label">MAIN MENU</div>

    <a href="{{ route('dashboard') }}"
       class="nav-link {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}">
        ▦ &nbsp; Dashboard
    </a>

    <a href="{{ route('contacts.index') }}"
       class="nav-link {{ ($activePage ?? '') === 'contacts' ? 'active' : '' }}">
        ♙ &nbsp; Contacts
    </a>

    <a href="#" class="nav-link">✉ &nbsp; Campaigns</a>
    <a href="#" class="nav-link">▤ &nbsp; Reports</a>

    <div class="nav-label" style="margin-top: 25px;">
        MANAGEMENT
    </div>

    <a href="#" class="nav-link">⚙ &nbsp; Settings</a>
</aside>
