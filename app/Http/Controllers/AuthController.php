<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use App\Services\OtpService;
use App\Mail\VerifyEmail;
use App\Mail\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\Order;


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
     *             required={"name", "email", "password", "password_confirmation"},
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration successful, OTP sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registration successful. Please verify your email."),
     *             @OA\Property(property="user_id", type="integer", example=1)
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
        ]);

        // Generate OTP
        $otpModel = $this->otpService->generateOtp($user, $request->email, 'verification');

        // Generate verification URL
        $verificationUrl = url("/api/auth/verify-email/{$request->email}/{$otpModel->otp}");

        // Send verification email
        Mail::to($request->email)->send(new VerifyEmail($otpModel->otp, $verificationUrl));

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email for verification code.',
            'user' => $user
        ], 201);
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
     *             @OA\Property(property="user_id", type="integer", example=1)
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

        // Generate and send OTP for password reset
        $this->generateAndSendOtp($user, 'password_reset');

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
        // Verify admin token
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
} 