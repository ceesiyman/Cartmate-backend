@component('mail::message')
@if($type === 'new_order')
# New Order Received

Thank you for your order! We're excited to process it for you.

@elseif($type === 'status_update')
# Order Status Update

Your order status has been updated.

@elseif($type === 'admin_update')
# Order Update

We have an update regarding your order.

@endif

## Order Details
- Order Number: **#{{ $order->order_number }}**
- Total Amount: **${{ number_format($order->total_amount, 2) }}**
- Status: **{{ ucfirst($order->status) }}**
- Date: **{{ $order->created_at->format('F j, Y H:i') }}**

@if($type === 'status_update')
The status of your order has been updated to: **{{ ucfirst($order->status) }}**
@endif

@if($type === 'admin_update')
**Update Message:**
{{ $message }}
@endif

@if($type === 'new_order' || $type === 'status_update')
@component('mail::button', ['url' => config('app.frontend_url') . '/orders/' . $order->id])
View Order Details
@endcomponent
@else
@component('mail::button', ['url' => config('app.frontend_url') . '/orders/' . $order->id])
View Order
@endcomponent
@endif

If you have any questions, please don't hesitate to contact our support team.

Best regards,<br>
The Cartmate Team

@component('mail::subcopy')
If you're having trouble clicking the button above, copy and paste the URL below into your web browser: [{{ config('app.frontend_url') . '/orders/' . $order->id }}]({{ config('app.frontend_url') . '/orders/' . $order->id }})
@endcomponent
@endcomponent 