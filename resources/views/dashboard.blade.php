
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('branding.name') }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI",
                         sans-serif;
            background: #f4f6fa;
            color: #17243b;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #011d48;
            color: white;
            padding: 28px 18px;
            flex-shrink: 0;
        }

        .brand-logo {
            background: #ffffff;
            border-radius: 8px;
            padding: 5px 10px;
            margin: 0 auto 10px;
            max-width: 220px;
            overflow: hidden;
        }

        .brand-logo img {
            display: block;
            width: 100%;
            max-height: 85px;
            object-fit: contain;
        }

        .brand-subtitle {
            color: #9db6d0;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            text-align: center;
            padding: 5px 0 35px;
        }
        .nav-label {
            color: #8199b6;
            font-size: 11px;
            font-weight: 700;
            padding: 12px;
            letter-spacing: 1.5px;
        }

        .nav-link {
            display: block;
            color: #d5e1ef;
            text-decoration: none;
            padding: 13px 14px;
            margin: 5px 0;
            border-radius: 7px;
            font-size: 14px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #0561cc;
            color: white;
        }

        .main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 76px;
            background: white;
            border-bottom: 1px solid #e8edf4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 35px;
        }

        .topbar h2 {
            font-size: 17px;
            color: #011d48;
        }

        .profile {
            background: #e9f1fa;
            color: #011d48;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logout-btn {
            background: #0561cc;
            color: #ffffff;
            border: none;
            padding: 10px 17px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #011d48;
        }
        .content {
            padding: 35px;
        }

        .welcome h1 {
            font-size: 27px;
            color: #011d48;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #77849a;
            font-size: 14px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            padding: 23px;
            border-radius: 10px;
            border: 1px solid #e8edf4;
        }

        .stat-title {
            color: #78869a;
            font-size: 13px;
        }

        .stat-number {
            font-size: 29px;
            font-weight: 750;
            color: #011d48;
            margin-top: 13px;
        }

        .stat-note {
            color: #8b98aa;
            font-size: 12px;
            margin-top: 7px;
        }

        .section {
            background: white;
            border: 1px solid #e8edf4;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 22px;
        }

        .section h3 {
            color: #011d48;
            font-size: 17px;
            margin-bottom: 18px;
        }

        .empty-state {
            text-align: center;
            padding: 35px 10px;
            color: #8491a4;
            font-size: 14px;
        }

        .primary-btn {
            display: inline-block;
            background: #0561cc;
            color: white;
            padding: 11px 17px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-top: 15px;
        }

        @media (max-width: 1000px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .layout {
                display: block;
            }

            .sidebar {
                width: 100%;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 20px;
            }

            .topbar {
                padding: 0 20px;
            }
        }
    </style>
</head>

<body>
<div class="layout">

    <aside class="sidebar">
        <div class="brand-logo">
    <img src="{{ asset('images/loadque-logo.png') }}"
         alt="LOADQUE Technologies">
</div>

<div class="brand-subtitle">{{ strtoupper(config('branding.tagline')) }}</div>

        <div class="nav-label">MAIN MENU</div>

        <a href="/" class="nav-link active">▦ &nbsp; Dashboard</a>
        <a href="#" class="nav-link">♙ &nbsp; Contacts</a>
        <a href="#" class="nav-link">✉ &nbsp; Campaigns</a>
        <a href="#" class="nav-link">▤ &nbsp; Reports</a>

        <div class="nav-label" style="margin-top: 25px;">
            MANAGEMENT
        </div>

        <a href="#" class="nav-link">⚙ &nbsp; Settings</a>
    </aside>

    <main class="main">

        <header class="topbar">
            <h2>Dashboard</h2>

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

        <div class="content">

            <div class="welcome">
                <h1>Welcome to {{ config('branding.name') }}</h1>
                <p>Manage your contacts and email marketing campaigns.</p>
            </div>

            <div class="stats">

                <div class="stat-card">
                    <div class="stat-title">Total Contacts</div>
                    <div class="stat-number">0</div>
                    <div class="stat-note">Contacts in your database</div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Total Campaigns</div>
                    <div class="stat-number">0</div>
                    <div class="stat-note">Campaigns created</div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Emails Sent</div>
                    <div class="stat-number">0</div>
                    <div class="stat-note">Emails successfully accepted for sending</div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Delivery Rate</div>
                    <div class="stat-number">—</div>
                    <div class="stat-note">Delivery statistics will appear here</div>
                </div>

            </div>

            <section class="section">
                <h3>Recent Campaigns</h3>

                <div class="empty-state">
                    No campaigns created yet.
                    <br>
                    Your recent campaigns will appear here.
                    <br>
                    <a href="#" class="primary-btn">Create Your First Campaign</a>
                </div>
            </section>

            <section class="section">
                <h3>Getting Started</h3>

                <div class="empty-state">
                    Your {{ config('branding.name') }} workspace is ready.
                    <br>
                    Start by importing your contacts.
                    <br>
                    <a href="#" class="primary-btn">Import Contacts</a>
                </div>
            </section>

        </div>
    </main>

</div>
</body>
</html>
