<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="OTP",
 *     description="API Endpoints for OTP management"
 * )
 */
class OtpController extends Controller
{
    protected $otpService;

    /**
     * Constructor
     */
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * @OA\Post(
     *     path="/api/otp/generate",
     *     summary="Generate and send OTP",
     *     tags={"OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "type"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="User's email address",
     *                 example="john@example.com"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"verification", "reset", "login"},
     *                 description="Type of OTP",
     *                 example="verification"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="OTP sent successfully"
     *             ),
     *             @OA\Property(
     *                 property="expires_at",
     *                 type="string",
     *                 format="date-time",
     *                 example="2023-01-01T12:00:00Z"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function generate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|string|in:verification,reset,login',
        ]);

        $email = $request->email;
        $type = $request->type;

        // Find user by email if exists
        $user = User::where('email', $email)->first();

        // Generate OTP
        $otpModel = $this->otpService->generateOtp($user, $email, $type);

        // Send OTP via email
        $this->sendOtpEmail($email, $otpModel->otp, $type);

        return response()->json([
            'message' => 'OTP sent successfully',
            'expires_at' => $otpModel->expires_at,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/otp/verify",
     *     summary="Verify OTP",
     *     tags={"OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "otp", "type"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="User's email address",
     *                 example="john@example.com"
     *             ),
     *             @OA\Property(
     *                 property="otp",
     *                 type="string",
     *                 description="OTP code",
     *                 example="123456"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"verification", "reset", "login"},
     *                 description="Type of OTP",
     *                 example="verification"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="OTP verified successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired OTP"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'type' => 'required|string|in:verification,reset,login',
        ]);

        $isValid = $this->otpService->verifyOtp(
            $request->email,
            $request->otp,
            $request->type
        );

        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid or expired OTP',
            ], 400);
        }

        return response()->json([
            'message' => 'OTP verified successfully',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/otp/resend",
     *     summary="Resend OTP",
     *     tags={"OTP"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "type"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="User's email address",
     *                 example="john@example.com"
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"verification", "reset", "login"},
     *                 description="Type of OTP",
     *                 example="verification"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="OTP sent successfully"
     *             ),
     *             @OA\Property(
     *                 property="expires_at",
     *                 type="string",
     *                 format="date-time",
     *                 example="2023-01-01T12:00:00Z"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|string|in:verification,reset,login',
        ]);

        // Invalidate previous OTPs
        Otp::where('email', $request->email)
            ->where('type', $request->type)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate new OTP
        return $this->generate($request);
    }

    /**
     * Send OTP email
     */
    private function sendOtpEmail(string $email, string $otp, string $type)
    {
        $subject = match($type) {
            'verification' => 'Verify Your Email Address',
            'reset' => 'Reset Your Password',
            default => 'Your OTP Code'
        };
        
        $template = match($type) {
            'verification' => 'emails.verify-email',
            'reset' => 'emails.reset-password',
            default => 'emails.otp'
        };
        
        // In a real application, you would use Laravel's Mail facade to send emails
        // For now, we'll just log the OTP for testing purposes
        \Log::info("OTP for {$email}: {$otp}");
        
        // Uncomment this when you have email configuration set up
        /*
        Mail::send($template, ['otp' => $otp], function($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject);
        });
        */
    }
} 