<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — Ramba Meal Planning System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --pep-green: #006738; --pep-green-dark: #004d28; }
        body { font-family: 'Inter', sans-serif; }
        .login-bg {
            min-height: 100vh;
            background: #f4f6f8;
            display: flex;
        }
        /* Left panel — desktop only */
        .login-left {
            display: none;
            width: 420px; flex-shrink: 0;
            background: var(--pep-green);
            position: relative; overflow: hidden;
            flex-direction: column; justify-content: space-between; padding: 48px;
        }
        /* Decorative pattern */
        .login-left::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        @media (min-width: 900px) {
            .login-left { display: flex; }
            .login-right { flex: 1; }
        }
        .login-right {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .login-card {
            width: 100%; max-width: 400px;
        }
        .form-input {
            width: 100%;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            color: #1a2332;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            font-family: 'Inter', sans-serif;
        }
        .form-input:focus {
            border-color: var(--pep-green);
            box-shadow: 0 0 0 3px rgba(0,103,56,.12);
        }
        .btn-primary {
            width: 100%;
            background: var(--pep-green);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, transform .1s;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary:hover { background: var(--pep-green-dark); }
        .btn-primary:active { transform: scale(.99); }
    </style>
</head>
<body>
<div class="login-bg">

    {{-- LEFT PANEL (Desktop) --}}
    <div class="login-left">
        {{-- Logo --}}
        <div>
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:44px">
                <div style="width:48px;height:48px;background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.35);backdrop-filter:blur(6px);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:19px;font-weight:900;color:#ffffff;letter-spacing:1.5px;box-shadow:0 4px 12px rgba(0,0,0,.1);flex-shrink:0">
                    GS
                </div>
                <div>
                    <div style="font-size:18px;font-weight:800;color:#ffffff;line-height:1.2;margin:0;letter-spacing:-0.2px">
                        Ramba Meal
                    </div>
                    <div style="font-size:11.5px;font-weight:600;color:rgba(255,255,255,.8);line-height:1.2;margin:2px 0 0;letter-spacing:0.5px;text-transform:uppercase">
                        Planning System
                    </div>
                </div>
            </div>

            <h2 style="font-size:28px;font-weight:800;color:white;line-height:1.3;margin-bottom:16px">
                Sistem Perencanaan<br>Makan Field Ramba
            </h2>
            <p style="font-size:14px;color:rgba(255,255,255,.75);line-height:1.6">
                Digitalisasi planning catering untuk 4 wilayah operasi Field Ramba.
            </p>
        </div>

        {{-- Features --}}
        <div>
            @foreach(['Roster otomatis → Meal Plan', 'Approval Movement & Outside Meal', 'Manifest PDF siap cetak'] as $f)
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                <div style="width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="11" height="11" fill="white" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </div>
                <span style="font-size:13px;color:rgba(255,255,255,.85)">{{ $f }}</span>
            </div>
            @endforeach

            <div style="margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,.15)">
                <p style="font-size:11px;color:rgba(255,255,255,.5)">Phase 1</p>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL (Login Form) --}}
    <div class="login-right">
        <div class="login-card">

            {{-- Mobile logo (only shown < 900px) --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:28px" class="lg:hidden">
                <div style="width:44px;height:44px;background:linear-gradient(135deg, #006738 0%, #004d28 100%);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:900;color:#ffffff;letter-spacing:1px;box-shadow:0 4px 12px rgba(0,103,56,.3);flex-shrink:0">
                    GS
                </div>
                <div style="text-align:left">
                    <div style="font-size:17px;font-weight:800;color:#0f172a;line-height:1.2;margin:0;letter-spacing:-0.2px">
                        Ramba Meal
                    </div>
                    <div style="font-size:11px;font-weight:700;color:#006738;line-height:1.2;margin:2px 0 0;letter-spacing:0.5px;text-transform:uppercase">
                        Planning System
                    </div>
                </div>
            </div>

            <h1 style="font-size:22px;font-weight:800;color:#1a2332;margin-bottom:6px">Selamat Datang</h1>
            <p style="font-size:13px;color:#6b7280;margin-bottom:28px">Masuk dengan akun yang diberikan oleh GS.</p>

            {{-- Error --}}
            @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 14px;margin-bottom:20px">
                @foreach($errors->all() as $error)
                <p style="font-size:13px;color:#dc2626">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px">
                        Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="nama@gsramba.com"
                           class="form-input" required autofocus
                           autocomplete="email">
                </div>

                <div style="margin-bottom:20px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                        <label style="font-size:13px;font-weight:600;color:#374151">Password</label>
                    </div>
                    <div style="position:relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'"
                               name="password"
                               placeholder="••••••••"
                               class="form-input"
                               required autocomplete="current-password"
                               style="padding-right:44px">
                        <button type="button" @click="show = !show"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);border:none;background:none;cursor:pointer;color:#9ca3af;padding:2px">
                            <svg x-show="!show" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:8px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="remember"
                               style="width:15px;height:15px;accent-color:var(--pep-green)">
                        <span style="font-size:13px;color:#6b7280">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary" :disabled="loading">
                    <span x-show="!loading">Masuk</span>
                    <span x-show="loading" style="display:flex;align-items:center;justify-content:center;gap:8px">
                        <svg style="animation:spin 1s linear infinite" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Memproses...
                    </span>
                </button>
            </form>

            <div style="text-align:center;margin-top:24px;line-height:1.6">
                <p style="font-size:12px;color:#475569;margin:0 0 4px">
                    Lupa password? Hubungi GS untuk reset password.
                </p>
                <p style="font-size:11.5px;color:#9ca3af;margin:0">
                    Belum punya akun? Hubungi GS.
                </p>
            </div>

            <div style="margin-top:24px;padding-top:20px;border-top:1px solid #f3f4f6;text-align:center">
                <p style="font-size:11.5px;color:#94a3b8">© 2026 GS Ramba</p>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
</body>
</html>
