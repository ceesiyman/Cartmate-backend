<?php

namespace App\Http\Controllers;

use App\Mail\UpdateOfferEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class EmailSubscriptionController extends Controller
{
    /**
     * Send bulk emails to subscribed users.
     * 
     * @OA\Post(
     *     path="/api/emails/bulk",
     *     summary="Send bulk emails to all subscribed users",
     *     description="Sends emails with the specified subject and content to all users who have subscribed to email notifications",
     *     operationId="sendBulkEmails",
     *     tags={"Email Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject", "content"},
     *             @OA\Property(property="subject", type="string", example="Special Offer", description="Email subject line"),
     *             @OA\Property(property="content", type="string", example="<h1>Special Offer</h1><p>Check out our new promotion!</p>", description="Full email body (HTML/Markdown)"),
     *             @OA\Property(property="synchronous", type="boolean", example=false, description="Optional flag for synchronous sending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Emails successfully sent or queued",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Emails queued for 15 users.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No subscribed users found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No subscribed users found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object", example={"subject": {"The subject field is required."}, "content": {"The content field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to send emails."),
     *             @OA\Property(property="error", type="string", example="Error message details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendBulkEmails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string', // Full email body (HTML/Markdown)
            'synchronous' => 'sometimes|boolean', // Optional flag for sync sending
        ]);

        if ($validator->fails()) {
            Log::warning('Bulk email validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $subscribedUsers = User::where('email_subscribed', true)->get();

            if ($subscribedUsers->isEmpty()) {
                Log::info('No subscribed users found for bulk email');
                return response()->json([
                    'success' => false,
                    'message' => 'No subscribed users found.',
                ], 404);
            }

            $sendSynchronously = $request->input('synchronous', false);
            $successfulEmails = 0;

            foreach ($subscribedUsers as $user) {
                try {
                    $mail = Mail::to($user->email);
                    $mailable = new UpdateOfferEmail($request->subject, $request->content);

                    if ($sendSynchronously) {
                        $mail->send($mailable);
                    } else {
                        $mail->queue($mailable);
                    }
                    $successfulEmails++;
                    Log::info('Email processed for user', ['email' => $user->email, 'synchronous' => $sendSynchronously]);
                } catch (\Exception $e) {
                    Log::error('Failed to process email for user', [
                        'email' => $user->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Emails " . ($sendSynchronously ? 'sent' : 'queued') . " for $successfulEmails users.",
            ], 200);

        } catch (\Exception $e) {
            Log::error('Bulk email sending failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send emails.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update email subscription status for a user.
     * 
     * @OA\Put(
     *     path="/api/users/{userId}/subscription",
     *     summary="Update a user's email subscription status",
     *     description="Enables or disables email subscription for a specific user",
     *     operationId="updateSubscription",
     *     tags={"Email Subscription"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email_subscribed"},
     *             @OA\Property(property="email_subscribed", type="boolean", example=true, description="Email subscription status")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription status updated successfully."),
     *             @OA\Property(
     *                 property="data", 
     *                 type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="email_subscribed", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update subscription status.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object", example={"email_subscribed": {"The email subscribed field is required."}})
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSubscription(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'email_subscribed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            Log::warning('Subscription update validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::findOrFail($userId);
            $user->email_subscribed = $request->input('email_subscribed');
            $user->save();

            Log::info('Subscription status updated', ['user_id' => $user->id, 'email_subscribed' => $user->email_subscribed]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription status updated successfully.',
                'data' => [
                    'user_id' => $user->id,
                    'email_subscribed' => $user->email_subscribed,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Subscription update failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription status.',
            ], 404);
        }
    }
}