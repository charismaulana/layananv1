<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function show()
    {
        return view('auth.password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.min'                       => 'Password minimal 8 karakter.',
            'password.mixed_case'                => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers'                   => 'Password harus mengandung angka.',
            'password.confirmed'                 => 'Konfirmasi password tidak sesuai.',
        ]);

        $user = $request->user();
        $user->update([
            'password'            => Hash::make($request->password),
            'must_change_password'=> false,
        ]);

        $this->audit->log('change_password', 'auth', "Password berhasil diubah", $user);

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah. Selamat datang!');
    }
}
