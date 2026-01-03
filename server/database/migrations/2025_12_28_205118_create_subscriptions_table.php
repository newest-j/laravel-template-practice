<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete();

            // Transaction details
            $table->string('tx_ref')->unique();               // transaction reference
            $table->string('flutterwave_id')->nullable()->unique(); // Flutterwave transaction id
            $table->unsignedInteger('price');                 // price paid
            $table->string('currency');                       // currency code (NGN, USD, etc.)

            // Subscription duration
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Subscription status (active, pending, cancelled, expired, etc.)
            $table->string('status')->nullable();

            // Optional raw response from payment provider
            $table->json('raw_response')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
