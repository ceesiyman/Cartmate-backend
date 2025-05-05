@component('mail::message')
# Cartmate Updates & Offers

Dear valued customer,

{!! nl2br(e($content)) !!}

@component('mail::button', ['url' => config('app.url')])
Visit Cartmate
@endcomponent

You are receiving this email because you subscribed to updates from Cartmate. To unsubscribe, update your preferences in your account settings.

Best regards,<br>
The Cartmate Team

@component('mail::subcopy')
If you prefer not to receive these updates, you can <a href="{{ config('app.url') }}/profile">unsubscribe here</a>.
@endcomponent
@endcomponent