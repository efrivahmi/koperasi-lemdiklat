<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            // Use stateless to avoid InvalidStateException caused by 127.0.0.1 vs localhost mismatches
            $socialiteDriver = Socialite::driver('google')->stateless();
            
            // Bypass SSL verification for local development (fixing cURL error 60)
            if (app()->environment('local')) {
                $socialiteDriver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            
            $googleUser = $socialiteDriver->user();

            // Find existing user by google_id or email
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // If user exists but doesn't have google_id, update it
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
                
                Auth::login($user);
                return redirect()->intended(route('dashboard', absolute: false));
            }

            // User not found in system - prevent registration
            return redirect('/login')->with('status', 'Akun Anda belum terdaftar di sistem. Silakan hubungi Administrator.');

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            \Log::error('Google OAuth Client Error: ' . $e->getMessage());
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            return redirect('/login')->with('status', 'Gagal konfigurasi Google: ' . $e->getMessage() . '. Detail: ' . $responseBody);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            \Log::error('Google OAuth InvalidState Error: ' . $e->getMessage());
            return redirect('/login')->with('status', 'Sesi login tidak valid. Pastikan domain URL sama dengan yang didaftarkan (HTTPS).');
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect('/login')->with('status', 'Gagal terhubung dengan Google. Error: ' . $e->getMessage());
        }
    }
}
