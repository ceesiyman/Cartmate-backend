@component('mail::message')
# Reset Your Password

Hello,

We received a request to reset your password. Please use the following OTP code to proceed:

@component('mail::panel')
<div style="text-align: center; font-size: 32px; letter-spacing: 8px; font-weight: bold; padding: 20px;">
{{ $otp }}
</div>
@endcomponent

This code will expire in 10 minutes.

If you did not request a password reset, please ignore this email or contact support if you have concerns.

Thanks,<br>
{{ config('app.name') }}
@endcomponent