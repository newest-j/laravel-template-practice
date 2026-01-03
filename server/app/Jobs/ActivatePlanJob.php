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
        //
        $transaction = Transaction::with('user')->findOrFail($this->transactionId);
        $transaction->user->createSubscription($transaction);
    }
}
