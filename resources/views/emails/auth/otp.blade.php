<x-mail::message>
# Permintaan Reset Password

Halo,

Kami menerima permintaan untuk melakukan reset password akun Anda di Koperasi Lemdiklat Taruna Nusantara Indonesia.
Silakan gunakan kode OTP (One-Time Password) 6-digit di bawah ini untuk melanjutkan proses reset password.

<div style="text-align: center; margin: 30px 0;">
    <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #4F46E5; background: #EEF2FF; padding: 10px 20px; border-radius: 8px;">
        {{ $otp }}
    </span>
</div>

Kode ini hanya berlaku selama 60 menit. Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
