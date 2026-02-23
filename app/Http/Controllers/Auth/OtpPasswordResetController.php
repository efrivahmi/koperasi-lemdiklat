<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class OtpPasswordResetController extends Controller
{
    /**
     * Send OTP to the user's email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate(
            ['email' => 'required|email|exists:users,email'],
            ['email.exists' => 'Email anda belum terdaftar!']
        );

        $email = $request->email;
        $otp = (string) random_int(100000, 999999);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Save new OTP (we hash it for security, similar to tokens)
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($otp),
            'created_at' => now(),
        ]);

        // Send Email
        Mail::to($email)->send(new SendOtpResetMail($otp));

        // Use Inertia::location or redirect correctly for Vue
        return redirect()->route('password.verify.show', ['email' => $email])
                         ->with('status', 'Kode 6 digit telah dikirim ke email Anda.');
    }

    /**
     * Show the Verify OTP view.
     */
    public function showVerifyForm(Request $request)
    {
        return Inertia::render('Auth/VerifyOtp', [
            'email' => $request->query('email')
        ]);
    }

    /**
     * Verify the OTP and reset the password.
     */
    public function verifyOtpAndReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => [
                'required', 
                'string', 
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.confirmed' => 'Password baru harus sama.',
            'password.min' => 'Password harus memiliki minimal 8 karakter.',
            'password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password harus mengandung angka.',
            'password.symbols' => 'Password harus mengandung simbol khusus.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'otp' => ['Sesi reset password tidak valid atau sudah kadaluarsa.'],
            ]);
        }

        // Check token expiration (e.g., 60 minutes)
        $expiresAt = \Carbon\Carbon::parse($record->created_at)->addMinutes(60);
        if (now()->greaterThan($expiresAt)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP sudah kadaluarsa. Silakan minta ulang.'],
            ]);
        }

        // Verify the PIN
        if (!Hash::check($request->otp, $record->token)) {
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP yang anda masukan salah, cek email kembali!'],
            ]);
        }

        // Update password
        $user = User::where('email', $request->email)->first();

        // Ensure new password is not the same as the old password
        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password baru harus berbeda, tidak boleh menggunakan password sebelumnya.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login.');
    }
}
