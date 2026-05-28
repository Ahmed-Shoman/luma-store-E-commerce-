<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>New Order</title>
</head>

<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
        <tr>
            <td align="center">

                <!-- Card -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#111827;padding:20px;text-align:center;color:#fff;">
                            <h2 style="margin:0;font-size:20px;">🛍 New Order Received</h2>
                            <p style="margin:5px 0 0;font-size:12px;opacity:0.8;">LUMA WEAR Notification System</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:25px;color:#111827;">

                            <p style="margin:0 0 10px;font-size:14px;">
                                A new order has been placed successfully.
                            </p>

                            <div style="background:#f9fafb;padding:15px;border-radius:8px;margin:15px 0;">
                                <p style="margin:5px 0;"><strong>Order ID:</strong> #{{ $order->id }}</p>
                                <p style="margin:5px 0;"><strong>Customer:</strong> {{ $order->customer_name }}</p>
                                <p style="margin:5px 0;"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                                <p style="margin:5px 0;"><strong>Total:</strong> {{ $order->total }} EGP</p>
                            </div>

                            <h3 style="margin:20px 0 10px;font-size:16px;">Items</h3>

                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                @foreach($order->items as $item)
                                <tr>
                                    <td style="padding:10px;border-bottom:1px solid #eee;">
                                        <strong>{{ $item->product_name }}</strong><br>
                                        <span style="font-size:12px;color:#6b7280;">
                                            Qty: {{ $item->quantity }} • Price: {{ $item->price }} EGP
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </table>

                            <!-- CTA -->
                            <div style="text-align:center;margin:25px 0;">
                                <a href="https://lumaawear.com/admin/orders"
                                    style="background:#111827;color:#ffffff;padding:12px 20px;
                                          text-decoration:none;border-radius:8px;
                                          display:inline-block;font-size:14px;font-weight:bold;">
                                    View All Orders →
                                </a>
                            </div>

                            <p style="font-size:12px;color:#6b7280;text-align:center;">
                                You can manage order status, delivery, and customer updates from the admin panel.
                            </p>

                        </td>
                    </tr>

                </table>

                <!-- Footer -->
                <p style="font-size:11px;color:#9ca3af;margin-top:15px;">
                    © {{ date('Y') }} LUMA WEAR. All rights reserved.
                </p>

            </td>
        </tr>
    </table>

</body>

</html>
