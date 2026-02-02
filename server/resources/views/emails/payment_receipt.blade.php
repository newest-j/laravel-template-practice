<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    @vite('resources/css/app.css') <!-- If using Tailwind via Vite -->
</head>
<body class="bg-gray-50 font-sans p-6">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">Payment Receipt</h1>

        <p>Hi {{ $transaction->user->name }},</p>
        <p>Thank you for your payment. Here are the details:</p>

        <ul class="mt-4 space-y-2">
            <li><strong>Transaction Ref:</strong> {{ $transaction->tx_ref }}</li>
            <li><strong>Amount:</strong> ${{ number_format($transaction->price, 2) }}</li>
            <li><strong>Date:</strong> {{ $transaction->created_at->format('d M Y H:i') }}</li>
        </ul>

        <p class="mt-6">Thanks,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
