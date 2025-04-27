@component('mail::message')
# New Customer Registration

A new customer has registered on the Cartmate platform.

## Customer Details
- Name: **{{ $customer->name }}**
- Email: **{{ $customer->email }}**
- Registration Date: **{{ $customer->created_at->format('F j, Y H:i') }}**

@component('mail::button', ['url' => config('app.admin_url') . '/customers/' . $customer->id])
View Customer Profile
@endcomponent

Please review the customer's details and take any necessary actions.

Best regards,<br>
The Cartmate Team

@component('mail::subcopy')
If you're having trouble clicking the button above, copy and paste the URL below into your web browser: [{{ config('app.admin_url') . '/customers/' . $customer->id }}]({{ config('app.admin_url') . '/customers/' . $customer->id }})
@endcomponent
@endcomponent 