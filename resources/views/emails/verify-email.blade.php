@component('mail::message')
# Welcome to Cartmate!

Thank you for registering with Cartmate. To complete your registration, please verify your email address.

Your verification code is: **{{ $otp }}**

@component('mail::button', ['url' => $verificationUrl])
Verify Email Address
@endcomponent

If you did not create an account, no further action is required.

Best regards,<br>
The Cartmate Team

@component('mail::subcopy')
If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser: [{{ $verificationUrl }}]({{ $verificationUrl }})
@endcomponent
@endcomponent 