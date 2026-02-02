<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ActivatePlanJob implements ShouldQueue
{
    use Queueable;
    // php artisan queue:work redis
    // Laravel’s queue worker automatically reads these properties when executing the job.
    // php artisan queue:work redis

    public $tries = 3;

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
        // Use Flutterwave transaction id to locate our local transaction
        // so if it is not the primary key i should not use
        // the find but the first with the where since they come together
        $transaction = Transaction::with('user')
            ->where('flutterwave_id', $this->transactionId)
            ->firstOrFail();
        $transaction->user->createSubscription($transaction);
    }
}
