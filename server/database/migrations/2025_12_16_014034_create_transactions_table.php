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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            //     same as this
            //      $table->foreign('user_id')
            // ->references('id')
            // ->on('users')
            // ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('tx_ref')->unique();
            $table->string('flutterwave_id')->nullable()->unique();
            $table->unsignedInteger('price');
            $table->string('currency');
            $table->string('status');
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
