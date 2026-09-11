<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal GS — Field Ramba</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts._styles')
    <style>
        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #edf2f7 100%);
            min-height: 100vh;
            color: #1e293b;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .portal-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }
        .service-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1.5px solid #e2e8f0;
            padding: 20px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .service-card:hover, .service-card:active {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.06);
            border-color: #cbd5e1;
        }
        .service-card.active-card {
            border-color: #86efac;
            background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);
        }
        .service-card.active-card:hover, .service-card.active-card:active {
            border-color: #22c55e;
            box-shadow: 0 10px 24px rgba(34,197,94,0.12);
        }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
            padding: 16px;
        }
        .modal-content {
            background: #ffffff;
            border-radius: 18px;
            max-width: 440px;
            width: 100%;
            padding: 24px 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            text-align: center;
            position: relative;
            animation: modalPop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes modalPop {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* ── Mobile-Specific Tweaks ── */
        @media (max-width: 640px) {
            .portal-header {
                padding: 10px 14px;
            }
            .portal-header .brand-title {
                font-size: 13px !important;
            }
            .portal-header .brand-sub {
                font-size: 10px !important;
            }
            .portal-header .user-name {
                display: none;
            }
            .hero-banner {
                padding: 20px 16px !important;
                border-radius: 14px !important;
                margin-bottom: 20px !important;
            }
            .hero-banner h2 {
                font-size: 17px !important;
            }
            .hero-banner p {
                font-size: 12px !important;
            }
            .services-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .service-card {
                padding: 16px;
                border-radius: 14px;
            }
            .portal-main {
                padding: 16px 12px !important;
            }
            .contact-box {
                flex-direction: column;
                align-items: flex-start !important;
                padding: 14px !important;
            }
        }
    </style>
</head>
<body>

    {{-- Top Bar --}}
    <header class="portal-header">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg, #006738 0%, #004d28 100%);border-radius:8px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,103,56,.3);font-size:15px;font-weight:900;color:#ffffff;letter-spacing:1px;flex-shrink:0">
                GS
            </div>
            <div>
                <h1 class="brand-title" style="font-size:15px;font-weight:800;color:#0f172a;margin:0;line-height:1.2">PORTAL GS</h1>
                <p class="brand-sub" style="font-size:11px;color:#006738;font-weight:700;margin:1px 0 0">Field Ramba</p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px">
            <div style="display:flex;align-items:center;gap:7px;background:#f8fafc;padding:4px 10px;border-radius:99px;border:1px solid #e2e8f0">
                <div style="width:26px;height:26px;border-radius:50%;background:#006738;color:#fff;font-size:10.5px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="user-name" style="text-align:left">
                    <p style="font-size:11.5px;font-weight:700;color:#1e293b;margin:0;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->name }}</p>
                    <span style="font-size:9.5px;color:#64748b;font-weight:600">{{ $user->role?->name ?? 'User' }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:6px 10px;border-radius:8px;font-size:11.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:4px">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="portal-main" style="flex:1;max-width:1100px;width:100%;margin:0 auto;padding:28px 16px">

        {{-- Greeting Hero Banner --}}
        <div class="hero-banner" style="background:linear-gradient(135deg, #006738 0%, #004d2a 100%);border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:24px;box-shadow:0 8px 20px rgba(0,103,56,.18);position:relative;overflow:hidden">
            <div style="position:absolute;right:16px;bottom:-18px;opacity:0.08;pointer-events:none;font-size:130px;font-weight:900;line-height:1;user-select:none;color:#fff">
                GS
            </div>
            <div style="position:relative;z-index:1;max-width:680px">
                <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;font-size:10px;font-weight:700;margin-bottom:8px;padding:3px 8px;border-radius:99px;display:inline-block">
                    Sistem Layanan Terintegrasi
                </span>
                <h2 style="font-size:20px;font-weight:800;margin:0 0 4px;line-height:1.25">
                    Selamat Datang, {{ $user->name }}
                </h2>
                <p style="font-size:12.5px;color:rgba(255,255,255,.9);margin:0;line-height:1.45">
                    Pilih layanan General Services (GS) yang Anda butuhkan di bawah ini untuk memulai proses pengajuan atau monitoring.
                </p>
            </div>
        </div>

        {{-- 4 Services Grid --}}
        <div class="services-grid">
            @foreach($services as $srv)
            <div class="service-card {{ $srv['is_active'] ? 'active-card' : '' }}">
                <div>
                    {{-- Card Header --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:12px">
                        <div style="width:44px;height:44px;border-radius:10px;background:{{ $srv['bg_gradient'] }};border:1.5px solid {{ $srv['border'] }};display:flex;align-items:center;justify-content:center;font-size:20px;box-shadow:0 2px 4px rgba(0,0,0,.03);flex-shrink:0">
                            {{ $srv['icon'] }}
                        </div>
                        @if($srv['is_active'])
                        <span class="badge badge-green" style="font-size:9.5px;font-weight:700;padding:2px 7px">
                            ● {{ $srv['badge'] }}
                        </span>
                        @else
                        <span class="badge badge-gold" style="font-size:9.5px;font-weight:700;padding:2px 7px">
                            ⚙️ {{ $srv['badge'] }}
                        </span>
                        @endif
                    </div>

                    {{-- Title & Subtitle --}}
                    <h3 style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 2px;line-height:1.25">
                        {{ $srv['title'] }}
                    </h3>
                    <p style="font-size:11px;font-weight:600;color:{{ $srv['color'] }};margin:0 0 8px">
                        {{ $srv['subtitle'] }}
                    </p>

                    {{-- Description --}}
                    <p style="font-size:11.5px;color:#64748b;line-height:1.45;margin:0 0 16px">
                        {{ $srv['desc'] }}
                    </p>
                </div>

                {{-- Action Button --}}
                <div style="margin-top:auto">
                    @if($srv['action_type'] === 'link')
                    <a href="{{ $srv['url'] }}" style="display:flex;align-items:center;justify-content:center;gap:6px;width:100%;min-height:42px;padding:9px 14px;background:#006738;color:#fff;font-size:12px;font-weight:700;border-radius:10px;text-decoration:none;box-sizing:border-box;box-shadow:0 2px 5px rgba(0,103,56,.25);transition:background .15s">
                        {{ $srv['btn_text'] }}
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    @else
                    <button type="button" onclick="showDevModal('{{ $srv['title'] }}', '{{ $srv['icon'] }}', '{{ $srv['modal_msg'] }}')"
                            style="display:flex;align-items:center;justify-content:center;gap:6px;width:100%;min-height:42px;padding:9px 14px;background:#f8fafc;border:1.5px solid #cbd5e1;color:#334155;font-size:12px;font-weight:700;border-radius:10px;cursor:pointer;transition:all .15s">
                        {{ $srv['btn_text'] }}
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Contact GS Support Box --}}
        <div class="contact-box" style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:24px;flex-shrink:0">🏢</span>
                <div>
                    <p style="font-size:12.5px;font-weight:700;color:#1e293b;margin:0">Pusat Layanan General Services (GS) Field Ramba</p>
                    <p style="font-size:11px;color:#64748b;margin:2px 0 0">Hubungi kantor GS untuk akomodasi, pemesanan kendaraan, dan perbaikan sarana fasilitas.</p>
                </div>
            </div>
            <div style="font-size:11.5px;font-weight:700;color:#006738;background:#f0fdf4;padding:6px 12px;border-radius:8px;border:1px solid #bbf7d0;white-space:nowrap">
                Kontak GS: -
            </div>
        </div>

    </main>

    {{-- Footer --}}
    <footer style="background:#ffffff;border-top:1px solid #e2e8f0;padding:14px 20px;text-align:center;font-size:11.5px;color:#64748b">
        &copy; 2026 GS Ramba
    </footer>

    {{-- Interactive Development Notice Modal --}}
    <div id="devModal" class="modal-backdrop" onclick="if(event.target===this) closeDevModal()">
        <div class="modal-content">
            <div id="modalIcon" style="width:56px;height:56px;margin:0 auto 12px;background:#fef3c7;border:2px solid #fde68a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px">
                ⚙️
            </div>
            <span class="badge badge-gold" style="font-size:10px;font-weight:700;margin-bottom:6px;display:inline-block">
                Tahap Pengembangan
            </span>
            <h3 id="modalTitle" style="font-size:16px;font-weight:800;color:#0f172a;margin:2px 0 8px">
                Layanan Sedang Dikembangkan
            </h3>
            <p id="modalMessage" style="font-size:12.5px;color:#475569;line-height:1.5;margin:0 0 16px">
                Layanan ini masih dalam tahap pengembangan. Untuk kebutuhan mendesak, silakan langsung menghubungi tim General Services (GS).
            </p>

            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;margin-bottom:18px;text-align:left;font-size:11.5px;color:#334155">
                <div style="font-weight:700;color:#0f172a;margin-bottom:4px">📞 Kontak General Services (GS):</div>
                <div>• Kantor GS Main Office</div>
                <div>• Kontak / Extension: <strong>-</strong></div>
            </div>

            <button type="button" onclick="closeDevModal()" class="btn btn-primary btn-full" style="padding:10px;font-weight:700;font-size:12.5px">
                Mengerti & Tutup
            </button>
        </div>
    </div>

    <script>
        function showDevModal(title, icon, message) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalIcon').textContent = icon;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('devModal').style.display = 'flex';
        }
        function closeDevModal() {
            document.getElementById('devModal').style.display = 'none';
        }
    </script>
</body>
</html>
