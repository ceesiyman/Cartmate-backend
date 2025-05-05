<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use App\Services\OtpService;
use App\Mail\VerifyEmail;
use App\Mail\ResetPassword;
use App\Mail\NewCustomerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
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
 *     path="/api/auth/register",
 *     summary="Register a new user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "password_confirmation", "customer_type"},
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 description="User's full name",
 *                 example="John Doe"
 *             ),
 *             @OA\Property(
 *                 property="email",
 *                 type="string",
 *                 format="email",
 *                 description="User's email address",
 *                 example="john@example.com"
 *             ),
 *             @OA\Property(
 *                 property="password",
 *                 type="string",
 *                 format="password",
 *                 description="User's password",
 *                 example="password123"
 *             ),
 *             @OA\Property(
 *                 property="password_confirmation",
 *                 type="string",
 *                 format="password",
 *                 description="Password confirmation",
 *                 example="password123"
 *             ),
 *             @OA\Property(
 *                 property="customer_type",
 *                 type="string",
 *                 description="Type of customer",
 *                 enum={"employee", "individual", "company"},
 *                 example="individual"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Registration successful, OTP sent",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Registration successful. Please verify your email."),
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="email", type="string", example="john@example.com"),
 *                 @OA\Property(property="customer_type", type="string", example="individual")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="The email has already been taken.")
 *         )
 *     )
 * )
 */
public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'customer_type' => 'required|string|in:employee,individual,company',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Create user with unverified email
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'customer_type' => $request->customer_type,
    ]);

    // Generate OTP
    $otpModel = $this->otpService->generateOtp($user, $request->email, 'verification');
    
    // Generate verification URL
    $verificationUrl = url("/api/auth/verify-email/{$request->email}/{$otpModel->otp}");

    // Send verification email to customer
    Mail::to($request->email)->send(new VerifyEmail($otpModel->otp, $verificationUrl));

    // Notify admins about new customer registration
    $adminUsers = User::where('role', 'ADMIN')->get();
    foreach ($adminUsers as $admin) {
        Mail::to($admin->email)->send(new NewCustomerNotification($user));
    }

    return response()->json([
        'success' => true,
        'message' => 'Registration successful. Please check your email for verification code.',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'customer_type' => $user->customer_type,
        ]
    ], 201);
}

private function sendOtpEmail(string $email, string $otp, string $type, string $verificationUrl = null)
{
    $subject = match($type) {
        'verification' => 'Verify Your Email Address',
        'reset' => 'Reset Your Password',
        default => 'Your OTP Code'
    };
    
    \Log::info("OTP for {$email}: {$otp}");
    
    // Use the appropriate mailable class based on type
    if ($type === 'verification') {
        Mail::to($email)->send(new \App\Mail\VerifyEmail($otp, $verificationUrl));
    } else {
        // Handle other email types or use the original approach
        $template = match($type) {
            'reset' => 'emails.reset-password',
            default => 'emails.otp'
        };
        
        $data = ['otp' => $otp];
        if ($verificationUrl) {
            $data['verificationUrl'] = $verificationUrl;
        }
        
        Mail::send($template, $data, function($message) use ($email, $subject) {
            $message->to($email)->subject($subject);
        });
    }
}

    /**
     * @OA\Post(
     *     path="/api/auth/verify-email",
     *     summary="Verify user email with OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "otp"},
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
     *                 description="OTP code sent to email",
     *                 example="123456"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email verified successfully."),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP or user not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP or OTP has expired.")
     *         )
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $isValid = $this->otpService->verifyOtp(
            $request->email,
            $request->otp,
            'verification'
        );

        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // Update user's email verification status
        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }

    public function verifyEmailLink($email, $otp)
    {
        $isValid = $this->otpService->verifyOtp(
            $email,
            $otp,
            'verification'
        );

        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // Update user's email verification status
        $user = User::where('email', $email)->first();
        $user->email_verified_at = now();
        $user->save();

        // Redirect to frontend with success message
        return redirect()->away(config('app.frontend_url') . '/email-verified');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="User's email address",
     *                 example="john@example.com"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 description="User's password",
     *                 example="password123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials.")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Temporarily bypass email verification
        // if (!$user->email_verified_at) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Please verify your email first'
        //     ], 403);
        // }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/admin/login",
     *     summary="Admin login",
     *     description="Login with admin credentials to get access token",
     *     operationId="adminLogin",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin credentials",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@cartmate.com", description="Admin email"),
     *             @OA\Property(property="password", type="string", format="password", example="admin123", description="Admin password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Admin User"),
     *                 @OA\Property(property="email", type="string", example="admin@cartmate.com"),
     *                 @OA\Property(property="role", type="string", example="ADMIN")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The provided credentials are incorrect.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Admin access only.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The email field is required."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Unauthorized. Admin access only.'
            ], 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/forgot-password",
     *     summary="Send password reset OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="User's email address",
     *                 example="john@example.com"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset OTP sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset OTP has been sent to your email."),
     *             @OA\Property(property="user_id", type="string", example="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found.',
        ], 404);
    }

    // Generate OTP
    $otpModel = $this->otpService->generateOtp($user, $request->email, 'password_reset');
    
    // Send password reset email
    Mail::to($request->email)->send(new \App\Mail\ResetPassword($otpModel->otp));

    return response()->json([
        'success' => true,
        'message' => 'Password reset OTP has been sent to your email.',
        'user_id' => $user->id,
    ]);
}

    /**
     * @OA\Post(
     *     path="/api/auth/reset-password",
     *     summary="Reset password with OTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "otp", "password", "password_confirmation"},
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
     *                 description="OTP code sent to email",
     *                 example="123456"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 description="New password",
     *                 example="newpassword123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 description="Password confirmation",
     *                 example="newpassword123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid OTP or user not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid OTP or OTP has expired.")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Verify OTP using the service
        $isValid = $this->otpService->verifyOtp(
            $request->email,
            $request->otp,
            'password_reset'
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP or OTP has expired.',
            ], 400);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/user",
     *     summary="Get authenticated user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Generate and send OTP to user
     *
     * @param User $user
     * @param string $type
     * @return void
     */
    private function generateAndSendOtp(User $user, string $type)
    {
        try {
            // Generate OTP using the service
            $otpModel = $this->otpService->generateOtp($user, $user->email, $type);
            
            // Send OTP via email
            if ($type === 'password_reset') {
                Mail::to($user->email)->send(new ResetPassword($otpModel->otp));
            } else {
                // Generate verification URL for email verification
                $verificationUrl = url("/api/auth/verify-email/{$user->email}/{$otpModel->otp}");
                Mail::to($user->email)->send(new VerifyEmail($otpModel->otp, $verificationUrl));
            }
            
            \Log::info("OTP generated and sent for {$type} to {$user->email}");
        } catch (\Exception $e) {
            \Log::error("Failed to generate/send OTP: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/api/admin/dashboard/stats",
     *     summary="Get admin dashboard statistics",
     *     description="Get statistics for total orders, revenue, customers, and pending orders with week-over-week changes",
     *     operationId="getDashboardStats",
     *     tags={"Admin Dashboard"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="total_orders",
     *                 type="object",
     *                 @OA\Property(property="count", type="integer", example=1248),
     *                 @OA\Property(property="percentage_change", type="number", format="float", example=12.5),
     *                 @OA\Property(property="trend", type="string", example="increase")
     *             ),
     *             @OA\Property(
     *                 property="total_revenue",
     *                 type="object",
     *                 @OA\Property(property="amount", type="number", format="float", example=283945.78),
     *                 @OA\Property(property="percentage_change", type="number", format="float", example=8.3),
     *                 @OA\Property(property="trend", type="string", example="increase")
     *             ),
     *             @OA\Property(
     *                 property="total_customers",
     *                 type="object",
     *                 @OA\Property(property="count", type="integer", example=856),
     *                 @OA\Property(property="percentage_change", type="number", format="float", example=5.2),
     *                 @OA\Property(property="trend", type="string", example="increase")
     *             ),
     *             @OA\Property(
     *                 property="pending_orders",
     *                 type="object",
     *                 @OA\Property(property="count", type="integer", example=42),
     *                 @OA\Property(property="percentage_change", type="number", format="float", example=-3.1),
     *                 @OA\Property(property="trend", type="string", example="decrease")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not an admin",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Admin access only.")
     *         )
     *     )
     * )
     */
    public function getStats(Request $request)
    {
        // Check if user is admin using token
        $user = $request->user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Unauthorized. Admin access only.'
            ], 403);
        }

        // Get date ranges
        $now = Carbon::now();
        $currentWeekStart = $now->copy()->startOfWeek();
        $currentWeekEnd = $now->copy()->endOfWeek();
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();

        // Get total orders statistics
        $currentWeekOrders = Order::whereBetween('created_at', [$currentWeekStart, $currentWeekEnd])->count();
        $lastWeekOrders = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();
        $ordersPercentageChange = $this->calculatePercentageChange($lastWeekOrders, $currentWeekOrders);

        // Get total revenue from completed orders
        $currentWeekRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$currentWeekStart, $currentWeekEnd])
            ->sum('total_amount');
        $lastWeekRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('total_amount');
        $revenuePercentageChange = $this->calculatePercentageChange($lastWeekRevenue, $currentWeekRevenue);

        // Get total customers (excluding admins)
        $currentWeekCustomers = User::where('role', '!=', 'ADMIN')
            ->whereBetween('created_at', [$currentWeekStart, $currentWeekEnd])
            ->count();
        $lastWeekCustomers = User::where('role', '!=', 'ADMIN')
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();
        $customersPercentageChange = $this->calculatePercentageChange($lastWeekCustomers, $currentWeekCustomers);

        // Get pending orders
        $currentWeekPendingOrders = Order::where('status', 'pending')
            ->whereBetween('created_at', [$currentWeekStart, $currentWeekEnd])
            ->count();
        $lastWeekPendingOrders = Order::where('status', 'pending')
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();
        $pendingOrdersPercentageChange = $this->calculatePercentageChange($lastWeekPendingOrders, $currentWeekPendingOrders);

        return response()->json([
            'total_orders' => [
                'count' => $currentWeekOrders,
                'percentage_change' => $ordersPercentageChange,
                'trend' => $ordersPercentageChange >= 0 ? 'increase' : 'decrease'
            ],
            'total_revenue' => [
                'amount' => $currentWeekRevenue,
                'percentage_change' => $revenuePercentageChange,
                'trend' => $revenuePercentageChange >= 0 ? 'increase' : 'decrease'
            ],
            'total_customers' => [
                'count' => $currentWeekCustomers,
                'percentage_change' => $customersPercentageChange,
                'trend' => $customersPercentageChange >= 0 ? 'increase' : 'decrease'
            ],
            'pending_orders' => [
                'count' => $currentWeekPendingOrders,
                'percentage_change' => $pendingOrdersPercentageChange,
                'trend' => $pendingOrdersPercentageChange >= 0 ? 'increase' : 'decrease'
            ]
        ]);
    }

    /**
     * Calculate percentage change between two values
     *
     * @param float $oldValue
     * @param float $newValue
     * @return float
     */
    private function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return round((($newValue - $oldValue) / $oldValue) * 100);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/dashboard/recent-orders",
     *     summary="Get recent orders for admin dashboard",
     *     tags={"Admin Dashboard"},
     *      security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of recent orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="ORD-2024-001"),
     *                     @OA\Property(property="customer", type="string", example="John Doe"),
     *                     @OA\Property(property="date", type="string", example="April 1, 2024"),
     *                     @OA\Property(property="amount", type="number", format="float", example=99.99),
     *                     @OA\Property(property="status", type="string", example="processing"),
     *                     @OA\Property(property="items", type="integer", example=2),
     *                     @OA\Property(property="hasIssue", type="boolean", example=false)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function recentOrders(Request $request)
    {
        // Check if user is admin using token
        $user = $request->user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Unauthorized. Admin access only.'
            ], 403);
        }

        $orders = Order::with(['user', 'items'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->order_number,
                    'order_id'=> $order->id,
                    'customer' => $order->user ? $order->user->name : 'Unknown Customer',
                    'date' => $order->created_at->format('F j, Y'),
                    'amount' => $order->total_amount,
                    'status' => $order->status,
                    'items' => $order->items->count(),
                    'hasIssue' => $order->status === 'cancelled' || $order->status === 'pending',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/dashboard/pending-actions",
     *     summary="Get pending actions for admin dashboard",
     *     tags={"Admin Dashboard"},
     *      security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of pending actions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="ACT-2024-001"),
     *                     @OA\Property(property="type", type="string", example="order_issue"),
     *                     @OA\Property(property="title", type="string", example="Order requires attention"),
     *                     @OA\Property(property="description", type="string", example="Order #ORD-2024-001 has been pending for more than 24 hours"),
     *                     @OA\Property(property="priority", type="string", example="high"),
     *                     @OA\Property(property="created_at", type="string", example="2024-04-01T10:00:00Z"),
     *                     @OA\Property(property="related_id", type="string", example="ORD-2024-001")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function pendingActions(Request $request)
    {
        // Check if user is admin using token
        $user = $request->user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Unauthorized. Admin access only.'
            ], 403);
        }

        // Get pending orders that need attention
        $pendingOrders = Order::where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(24))
            ->get()
            ->map(function ($order) {
                return [
                    'id' => 'ACT-' . $order->id,
                    'type' => 'order_issue',
                    'title' => 'Order requires attention',
                    'description' => "Order #{$order->order_number} has been pending for more than 24 hours",
                    'priority' => 'high',
                    'created_at' => $order->created_at->toIso8601String(),
                    'related_id' => $order->order_number
                ];
            });

        // Get cancelled orders that need review
        $cancelledOrders = Order::where('status', 'cancelled')
            ->where('created_at', '>=', now()->subHours(48))
            ->get()
            ->map(function ($order) {
                return [
                    'id' => 'ACT-' . $order->id,
                    'type' => 'order_cancelled',
                    'title' => 'Order cancelled',
                    'description' => "Order #{$order->order_number} was cancelled by the customer",
                    'priority' => 'medium',
                    'created_at' => $order->created_at->toIso8601String(),
                    'related_id' => $order->order_number
                ];
            });

        // Combine all actions and sort by priority and creation date
        $actions = $pendingOrders->concat($cancelledOrders)
            ->sortByDesc('priority')
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $actions
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/dashboard/analytics",
     *     summary="Get order analytics for admin dashboard",
     *     tags={"Admin Dashboard"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="time_range",
     *         in="query",
     *         description="Time range for analytics",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"week", "month", "year"},
     *             default="week"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order analytics data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="orderVolume",
     *                 type="object",
     *                 @OA\Property(property="labels", type="array", @OA\Items(type="string")),
     *                 @OA\Property(
     *                     property="datasets",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="data", type="array", @OA\Items(type="integer"))
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="revenueByCategory",
     *                 type="object",
     *                 @OA\Property(property="labels", type="array", @OA\Items(type="string")),
     *                 @OA\Property(
     *                     property="datasets",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="data", type="array", @OA\Items(type="integer"))
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function orderAnalytics(Request $request)
    {
        // Check if user is admin using token
        $user = $request->user();
        if (!$user || $user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Unauthorized. Admin access only.'
            ], 403);
        }

        $timeRange = $request->query('time_range', 'week');
        $now = Carbon::now();

        // Get date range based on time_range parameter
        switch ($timeRange) {
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $interval = 'day';
                $format = 'D';
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $interval = 'month';
                $format = 'M';
                break;
            default: // week
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $interval = 'day';
                $format = 'D';
        }

        // Get order volume data
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($order) use ($interval) {
                return $order->created_at->startOf($interval)->format('Y-m-d');
            });

        $orderVolumeLabels = [];
        $orderVolumeData = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $orderVolumeLabels[] = $currentDate->format($format);
            $orderVolumeData[] = $orders->has($dateKey) ? $orders[$dateKey]->count() : 0;
            $currentDate->add(1, $interval);
        }

        // Get revenue by category
        $revenueByCategory = Order::whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category_name',
                DB::raw('ROUND(SUM(order_items.price * order_items.quantity)) as revenue')
            )
            ->groupBy('categories.name')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category_name ?? 'Uncategorized',
                    'revenue' => (int)$item->revenue
                ];
            });

        // Format the data for the response
        $formattedData = [
            'orderVolume' => [
                'labels' => $orderVolumeLabels,
                'datasets' => [
                    [
                        'data' => $orderVolumeData
                    ]
                ]
            ],
            'revenueByCategory' => [
                'labels' => $revenueByCategory->pluck('category')->toArray(),
                'datasets' => [
                    [
                        'data' => $revenueByCategory->pluck('revenue')->toArray()
                    ]
                ]
            ]
        ];

        return response()->json($formattedData);
    }
} 