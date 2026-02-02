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

            // Subscription duration
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Subscription status (active, pending, cancelled, expired, etc.)
            $table->string('status')->default('pending');

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
