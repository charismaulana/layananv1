@extends('layouts.app')
@section('title','Ganti Password')
@section('page-title','Ganti Password')
@section('content')
<div style="max-width:440px">
<div class="card">
    <div class="card-header"><h3>Ganti Password</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Password Lama</label>
                <input type="password" name="current_password" class="form-input" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-input" required autocomplete="new-password">
                <p class="form-hint">Minimal 8 karakter, kombinasi huruf dan angka</p>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Password Baru
            </button>
        </form>
        <div style="margin-top:12px;text-align:center">
            <a href="{{ route('profile.edit') }}" style="font-size:12px;color:var(--muted);text-decoration:none">← Kembali ke Profil</a>
        </div>
    </div>
</div>
</div>
@endsection
