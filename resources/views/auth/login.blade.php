<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Login</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f6fb;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(1, 29, 72, 0.12);
        }

        .login-header {
            background: linear-gradient(110deg, #011D48, #0561CC);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }

        .login-header img {
            width: 175px;
            max-height: 65px;
            object-fit: contain;
            background: #ffffff;
            border-radius: 6px;
            padding: 5px;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 23px;
            margin-bottom: 7px;
        }

        .login-header p {
            font-size: 13px;
            opacity: 0.85;
        }

        .login-body {
            padding: 35px 30px;
        }

        .login-body h2 {
            color: #011D48;
            font-size: 21px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #778397;
            font-size: 13px;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #25354d;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce3ed;
            border-radius: 7px;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: #0561CC;
            box-shadow: 0 0 0 3px rgba(5, 97, 204, 0.1);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 23px;
            color: #59677b;
            font-size: 13px;
        }

        .remember label {
            margin: 0;
            font-weight: normal;
        }

        .login-button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 7px;
            background: #0561CC;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-button:hover {
            background: #011D48;
        }

        .error {
            color: #c62828;
            background: #fff0f0;
            padding: 11px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .footer {
            text-align: center;
            padding: 18px;
            color: #8792a3;
            font-size: 11px;
            border-top: 1px solid #edf0f5;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Freight. Connected. Simplified.</p>
        </div>

        <div class="login-body">

            <h2>Sign In</h2>
            <p class="subtitle">Enter your account details to continue.</p>

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="remember">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="login-button">
                    Sign In
                </button>
            </form>

        </div>

        <div class="footer">
            &copy; {{ date('Y') }} LOADQUE Technologies. All rights reserved.
        </div>

    </div>

</body>
</html>
