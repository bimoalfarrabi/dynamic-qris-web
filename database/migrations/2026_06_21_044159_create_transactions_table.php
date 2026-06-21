<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('qrisify_transaction_id')->nullable();
            $table->string('external_id')->nullable();
            $table->unsignedInteger('amount_requested');
            $table->unsignedSmallInteger('unique_code')->nullable();
            $table->unsignedInteger('amount_total')->nullable();
            $table->enum('status', ['PENDING', 'SUCCESS', 'EXPIRED', 'CANCELLED'])->default('PENDING');
            $table->text('qris_string')->nullable();
            $table->enum('webhook_status', ['UNSENT', 'SENT_SUCCESS', 'FAILED'])->default('UNSENT');
            $table->string('payment_provider')->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
