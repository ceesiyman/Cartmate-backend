@component('mail::message')
# Verify Your Email Address

Thank you for registering with CartMate. Please verify your email address by clicking the button below:

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

Alternatively, you can use this OTP code to verify your email: **{{ $otp }}**

This OTP will expire in 10 minutes.

If you did not create an account, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent