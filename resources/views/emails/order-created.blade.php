<!DOCTYPE html>
<html>

<head>
    <title>New Order</title>
</head>

<body>
    <h2>New Order Received 🎉</h2>

    <p><strong>Order ID:</strong> {{ $order->id }}</p>

    <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
    <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>

    <h3>Items:</h3>

    <ul>
        @foreach($order->items as $item)
        <li>
            {{ $item->product_name }} -
            Qty: {{ $item->quantity }} -
            Price: {{ $item->price }}
        </li>
        @endforeach
    </ul>

    <h3>Total: {{ $order->total }}</h3>
</body>

</html>
