<?php

namespace App\Jobs;

use App\Mail\PayReceiptMail;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceiptJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;


    // SendPaymentReceiptJob::dispatch($transactionId)
//     ->onQueue('receipt');
// // onQueue('receipt');
// so in the job this will be the name given to this queue job

    /**
     * Create a new job instance.
     */
    public function __construct(public int $transactionId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Locate transaction by Flutterwave transaction id
        $transaction = Transaction::with('user')
            ->where('flutterwave_id', $this->transactionId)
            ->firstOrFail();
        $subscription = $transaction->subscription;
        Mail::to($transaction->user->email, $transaction->user->name)->send(new PayReceiptMail($transaction, $subscription));
    }
}
