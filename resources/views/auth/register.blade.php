<x-guest-layout>
    <div style="margin-bottom:20px">
        <h2 style="font-size:18px;font-weight:800;color:#1a2332;margin:0">Daftar Akun Baru</h2>
        <p style="font-size:12px;color:#6b7280;margin:4px 0 0">Pendaftaran mandiri untuk pekerja & mitra kontraktor</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Name --}}
        <div class="form-group">
            <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-input" required autofocus placeholder="Nama lengkap Anda">
            @error('name') <p class="form-hint" style="color:var(--red)">{{ $message }}</p> @enderror
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label">Alamat Email <span style="color:var(--red)">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-input" required placeholder="email@perusahaan.com">
            @error('email') <p class="form-hint" style="color:var(--red)">{{ $message }}</p> @enderror
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label class="form-label">Password <span style="color:var(--red)">*</span></label>
            <input type="password" name="password" class="form-input" required placeholder="Minimal 8 karakter">
            @error('password') <p class="form-hint" style="color:var(--red)">{{ $message }}</p> @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="form-group">
            <label class="form-label">Konfirmasi Password <span style="color:var(--red)">*</span></label>
            <input type="password" name="password_confirmation" class="form-input" required placeholder="Ulangi password di atas">
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="padding:12px;font-weight:700;margin-top:10px">
            Daftar Sekarang
        </button>

        <div style="text-align:center;margin-top:18px;padding-top:16px;border-top:1px solid #f3f4f6">
            <p style="font-size:12.5px;color:#6b7280;margin:0">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" style="color:#006738;font-weight:700;text-decoration:none">Masuk di sini</a>
            </p>
        </div>
    </form>
</x-guest-layout>
