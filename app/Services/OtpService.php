<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Generate a new OTP for the given user and type.
     *
     * @param User|null $user
     * @param string $email
     * @param string $type
     * @param int $length
     * @param int $expiryMinutes
     * @return Otp
     */
    public function generateOtp(?User $user, string $email, string $type, int $length = 6, int $expiryMinutes = 10): Otp
    {
        // Invalidate any existing OTPs of the same type for this email
        Otp::where('email', $email)
            ->where('type', $type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate new OTP
        $otp = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);

        return Otp::create([
            'user_id' => $user?->id,
            'email' => $email,
            'otp' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }

    /**
     * Verify an OTP for the given email and type.
     *
     * @param string $email
     * @param string $otp
     * @param string $type
     * @return bool
     */
    public function verifyOtp(string $email, string $otp, string $type): bool
    {
        $otpModel = Otp::where('email', $email)
            ->where('otp', $otp)
            ->where('type', $type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpModel) {
            return false;
        }

        $otpModel->markAsUsed();
        return true;
    }

    /**
     * Get the remaining time in seconds for an OTP.
     *
     * @param string $email
     * @param string $type
     * @return int|null
     */
    public function getRemainingTime(string $email, string $type): ?int
    {
        $otp = Otp::where('email', $email)
            ->where('type', $type)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return null;
        }

        return max(0, $otp->expires_at->diffInSeconds(now()));
    }
} 