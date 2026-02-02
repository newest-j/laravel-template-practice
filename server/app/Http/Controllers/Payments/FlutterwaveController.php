<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Jobs\ActivatePlanJob;
use App\Jobs\SendPaymentReceiptJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Support\Facades\Bus;

class FlutterwaveController extends Controller
{
    // so for the use of jobs it is what that is not need by the user immediately 
    // like an instance response but run in the background
    // job is the task made queue is to order to run the task
    public function initialize(Request $request)
    {
        // Validate required data
        // the currency and the decription is nullable because they are provided from the backend
        // while the plan_id and the customer_email are from the frontend
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'customer_email' => ['required', 'email'],
            'customer_name' => ['required', 'string', 'max:100'],
        ]);

        // Backend-controlled amount from Plan (prevents frontend tampering)
        $plan = Plan::findOrFail($validated['plan_id']);
        $amount = (int) $plan->price; // e.g., 5000 NGN
        $currency    = $plan->currency ?? 'NGN';
        $description = $plan->description ?? $plan->name ?? 'Payment';

        // Create unique transaction reference
        // this is for id to payment that is store in database it can be use to avoid dupicated payment
        // like a receipt number, trasaction code
        // uuid -- Universally Unique Identifier e.g 8c91b2c0-7d44-4e57-9d7b-4d1b9a9a1c6a
        $reference = 'tx-' . Str::uuid()->toString();

        Transaction::create([
            'user_id' => $request->user()->id, // or pass user_id explicitly
            'plan_id' => $plan->id,
            'tx_ref' => $reference,
            'price' => $amount,
            'currency' => $currency,
            'status' => 'pending',
        ]);

        // Collect payload from frontend
        //         Method	Who sends the payload	Where
        // Backend (v3 Payments API)	Your server	https://api.flutterwave.com/v3/payments
        // Prepare payload for Flutterwave (server-initiated redirect flow)
        $payload = [
            'tx_ref' => $reference,
            'amount' => $amount, // backend-controlled
            'currency' => $currency,
            'redirect_url' => route('flutterwave.callback'),
            'customer' => [
                'email' => $validated['customer_email'],
                'name' => $validated['customer_name'],
            ],
            'customizations' => [
                'title' => $description ?? ($plan->name ?? 'Payment'),
            ],
        ];

        // Send to Flutterwave API
        // the withToken() = adds Authorization: Bearer TOKEN
        $response = Http::withToken(config('services.flutterwave.secret_key'))
            ->post('https://api.flutterwave.com/v3/payments', $payload);

        // Handle failure
        if (!$response->successful()) {
            return response()->json([
                'message' => 'Flutterwave initialization failed',
                'details' => $response->json()
            ], 500);
        }

        // Return payment link to frontend
        return response()->json($response->json(), 200);
    }


    // now there should be a settlement table to show how flutterwave send money 
    // to my bank but this is only for production or live
    //     or do something like this
    //     if (app()->environment('production')) {
    //     // run settlement sync
    // }
    // or in the webhook with   "event": "settlement.completed",
    // or inside the  callback function with this 
    // $response = Http::withToken(config('services.flutterwave.secret_key'))
    //     ->get('https://api.flutterwave.com/v3/settlements');

    // $settlements = $response->json()['data'];
    // this is for one time payment transaction

    public function callback(Request $request)
    {
        // Flutterwave returns transaction_id as a QUERY param
        // so when the callback is called from the flutterwaves and is been redirected to in the browser
        // it quickly used this to get the trasaction_id $transactionID = $request->query('transaction_id');

        $transactionID = $request->query('transaction_id');

        if (!$transactionID) {
            // Redirect to unified result page without separate failed/success pages
            return redirect(env('FRONTEND_ORIGIN') . '/payment/result?status=failed');
        }

        // Verify payment (Flutterwave payments are v3)
        $verifyUrl = "https://api.flutterwave.com/v3/transactions/{$transactionID}/verify";

        $response = Http::withToken(config('services.flutterwave.secret_key'))->get($verifyUrl);

        if (!$response->successful()) {
            return redirect(env('FRONTEND_ORIGIN') . '/payment/result?status=failed');
        }

        // so because this response is  an outgoing http request the json in automaticaly in array
        $data = $response->json();
        $txRef = $data['data']['tx_ref'];

        $transaction = Transaction::where('tx_ref', $txRef)->first();
        $amount = (int) $transaction->price;
        $currency = $transaction->currency;

        if (!$transaction) {
            return redirect(env('FRONTEND_ORIGIN') . '/payment/result?status=failed');
        }


        // Verify response integrity
        $isSuccessful =
            ($data['status'] ?? null) === 'success' &&
            ($data['data']['status'] ?? null) === 'successful' &&
            $data['data']['amount'] == $amount &&
            $data['data']['currency'] === $currency &&
            $transaction->status === 'pending';


        if ($isSuccessful) {
            $transaction->update([
                'flutterwave_id' => $data['data']['id'],
                'status' => 'successful',
                'raw_response' => $data['data'],
            ]);

            /* the bus facade is a helper for dispatching commands and jobs
             with method like chaining
             Laravel now knows: “these jobs must run in this order”.
             The second job only runs if the first job succeeds.*/

            Bus::chain([
                new ActivatePlanJob($transactionID),
                new SendPaymentReceiptJob($transactionID),
            ])->dispatch();

            // Browser redirect to unified result page with transaction id and status
            return redirect(env('FRONTEND_ORIGIN') . "/payment/result?transaction_id={$transactionID}&status=success");
        }

        // Failed flow
        return redirect(env('FRONTEND_ORIGIN') . '/payment/result?status=failed');
    }


    public function getUserTransactionDetails(Request $request)
    {
        $transactionID = $request->query('transaction_id');
        $transaction = Transaction::where('flutterwave_id', $transactionID)->where('user_id', $request->user()->id)->firstOrFail();

        return response()->json([
            'amount' => $transaction->price,
            'currency' => $transaction->currency,
            'tx_ref' => $transaction->tx_ref,
            'status' => $transaction->status,
            'transaction_id' => $transaction->flutterwave_id
        ], 200);
    }

    public function checkSubscriptionStatus(Request $request)
    {
        $transactionID = $request->query('transaction_id');

        $transaction = Transaction::where('flutterwave_id', $transactionID)
            ->where('user_id', $request->user()->id)
            ->with('subscription')
            ->firstOrFail();

        if (!$transaction->subscription) {
            return response()->json([
                'active' => false,
                'status' => 'no_subscription'
            ], 404);
        }

        $subscription = $transaction->subscription;

        return response()->json([
            'active' => $subscription->isActive(),
            'status' => $subscription->status,
            'started_at' => $subscription->started_at,
            'expires_at' => $subscription->expires_at,
        ], 200);
    }

    // this is the webhook for flutterwave but it will only work in production when the site it host
    // and flutterwave can acces the url unlike in the localhost except you expose the localhot to
    // be public by using ngrok 5173 or 8000 for backend or frontend
    // User closes the browser / loses internet / phone dies Flutterwave still calls
    //  your server and your DB is updated (Flutterwave Servers ↔  Your Server )
    //    public function webhook(Request $request)
    // {
    //     // 1️⃣ Verify Flutterwave signature
    // the verif-hash is sent by fluterwave 
    //     $signature = $request->header('verif-hash');
    //     $expected = config('services.flutterwave.secret_hash');

    //     if (!$expected || !$signature || !hash_equals($expected, $signature)) {
    //         return response()->json(['message' => 'Invalid signature'], 401);
    //     }

    //     // 2️⃣ Parse payload
    // so because this response is an incoming http request the json is normal and to make it an array you use all()
    //     $payload = $request->json()->all();
    //     $event = $payload['event'] ?? null;
    //     $data = $payload['data'] ?? [];

    //     // 3️⃣ Only handle successful charges
    //     if ($event === 'charge.completed' && ($data['status'] ?? null) === 'successful') {

    //         $txRef = $data['tx_ref'] ?? null;

    //         if ($txRef) {
    //             $transaction = Transaction::where('tx_ref', $txRef)->first();

    //             if ($transaction && $transaction->status === 'pending') {

    //                 // Extra safety checks
    //                 if (
    //                     (int) $transaction->price === (int) ($data['amount'] ?? 0) &&
    //                     $transaction->currency === ($data['currency'] ?? null)
    //                 ) {
    //                     $transaction->update([
    //                         'flutterwave_id' => $data['id'] ?? null,
    //                         'status' => 'successful',
    //                         'raw_response' => $data,
    //                     ]);

    //                     // OPTIONAL:
    //                     // activate plan / subscription here
    //                 }
    //             }
    //         }
    //     } 

    //     // 4️⃣ Always acknowledge (VERY IMPORTANT)
    // so this is just say that i recetive the event back successful to the flutterwave not the payment was successful
    //     return response()->json(['status' => 'ok'], 200);
    // }
}
