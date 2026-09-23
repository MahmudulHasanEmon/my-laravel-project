<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up()
    {

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('user_id');
            
            // 🔹 User সম্পর্ক (best practice)
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // 🔹 Credit / Debit
            $table->enum('type', ['credit', 'debit'])->index();

            // 🔹 Transaction identity
            $table->string('trx_id', 50)->index(); // unique transaction identifier (external reference)

            // unique & user_id + trx_id combo for idempotency
            $table->unique(['user_id', 'trx_id']);

            $table->string('trx_type', 50)->index(); // recharge, withdraw, transfer

            // 🔹 Financial অংশ (precision increase for fintech)
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('charge', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);

            $table->string('currency', 10)->default('BDT');

            // 🔹 Balance tracking
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();

            // 🔹 Reference / receiver
            $table->string('trx_ref')->nullable()->index();
            $table->string('receiver', 100);
            $table->json('receiver_meta')->nullable(); // flexible data

            // 🔹 Status (index important for query speed)
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'reversed',
                'cancelled',
                'successful',
                'unsuccessful',
                'refunded',
                'chargeback',
                'disputed',
                'expired',
                'on_hold',
                'under_review',
                'awaiting_payment',
            ])->default('pending')->index();

            // Created by and updated by (for audit)
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();

            $table->foreign('created_by')
                ->references('admin_id')
                ->on('admins')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('updated_by')
                ->references('admin_id')
                ->on('admins')
                ->onUpdate('cascade')
                ->onDelete('set null');

            // 🔹 Approval system
            $table->bigInteger('approved_by')->nullable();

            $table->foreign('approved_by')
                ->references('admin_id')
                ->on('admins')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamp('approved_at')->nullable();

            // 🔹 Flags
            $table->boolean('is_refundable')->default(false);
            $table->boolean('is_flagged')->default(false);

            // 🔹 Security / tracking
            $table->ipAddress('ip_address')->nullable();
            $table->text('device_info')->nullable();

            // 🔹 Notes
            $table->text('remarks')->nullable();

            // 🔹 Indexes (important for performance)
            $table->index(['user_id', 'status']);
            $table->index(['trx_type', 'status']);

            // timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

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
