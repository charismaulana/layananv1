<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ramba Meal System') }} — Pertamina EP</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('layouts._styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .guest-container {
            width: 100%;
            max-width: 440px;
        }
        .guest-card {
            background: #fff;
            border: 1px solid #e8edf2;
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 4px 20px rgba(0,0,0,.04);
        }
    </style>
</head>
<body>
    <div class="guest-container">
        {{-- Brand Header --}}
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:48px;height:48px;background:linear-gradient(135deg, #006738 0%, #004d28 100%);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px;box-shadow:0 4px 12px rgba(0,103,56,.25);font-size:18px;font-weight:900;color:#ffffff;letter-spacing:1px">
                GS
            </div>
            <h1 style="font-size:18px;font-weight:800;color:#1a2332;margin:0">Ramba Meal Planning System</h1>
            <p style="font-size:11.5px;color:#6b7280;margin:2px 0 0">General Services — Field Ramba</p>
        </div>

        <div class="guest-card">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
