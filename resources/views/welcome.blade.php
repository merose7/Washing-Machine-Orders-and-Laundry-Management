<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welcome - The Daily Wash</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #ff0000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
        }

        h1 {
            font-size: 48px;
            color: white;
            margin-bottom: 8px; /* Lebih kecil agar dekat dengan h2 */
        }

        h2 {
            font-size: 24px;
            color: white;
            margin-top: 0;
            margin-bottom: 30px;
        }

        .logo {
            width: 450px;
            height: 450px;
            background: url('/images/Logo_The_Daily_Wash-removebg-preview.png') no-repeat center center;
            background-size: contain;
            margin-bottom: 30px;
        }

        .buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 16px;
            z-index: 1000;
        }

        a.button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            padding: 0;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        a.button:hover {
            color: #ffcccc;
        }
        
        @media (max-width: 600px) {
            .logo {
                width: 90vw;
                height: auto;
            }
            .buttons {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <h1>Welcome</h1>
    <h2>Laundry The Daily Wash</h2>
    <div class="logo" aria-label="The Daily Wash Logo"></div>
    <div class="buttons">
        <a href="{{ route('login') }}" class="button">Login</a>
        <a href="{{ route('register') }}" class="button">Register</a>
    </div>
</body>
</html>
