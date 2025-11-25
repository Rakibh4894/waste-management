<!-- resources/views/components/guest-background.blade.php -->
<div>
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Dhaka Waste Management') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            html, body {
                height: 100%;
                margin: 0;
                font-family: 'Poppins', sans-serif;
            }
            body {
                background: url('{{ asset('website/assets/images/waste-banner-2.jpg') }}') no-repeat center center;
                background-size: cover;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .login-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(6px);
                border-radius: 16px;
                padding: 40px 30px;
                max-width: 400px;
                width: 100%;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .login-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            }
            .login-card h2 {
                color: #1eae63;
                font-weight: 800;
                margin-bottom: 24px;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            {{ $slot }}
        </div>
    </body>
    </html>
</div>
