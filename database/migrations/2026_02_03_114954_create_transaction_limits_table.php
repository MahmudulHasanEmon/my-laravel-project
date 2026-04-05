<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_limits', function (Blueprint $table) {
            $table->id();

            // ✅ Correct foreign key relationship
            $table->foreignId('role_id')
                ->nullable()
                ->constrained('user_roles') // references id in user_roles
                ->onUpdate('cascade')
                ->onDelete('set null');
                
            $table->string('trx_type'); // send_money / mobile_recharge / pay_bill
            $table->integer('daily_count');
            $table->decimal('daily_amount', 12, 2);
            $table->integer('monthly_count');
            $table->decimal('monthly_amount', 12, 2);

            $table->decimal('per_transaction_min', 12, 2);
            $table->decimal('per_transaction_max', 12, 2);

            $table->decimal('trx_fee', 12, 2)->nullable();
            $table->integer('trx_free_count')->nullable();
            
            $table->string('charge_type')->nullable();
            $table->decimal('charge_value', 12, 2)->nullable();

            $table->string('commission_type')->nullable();
            $table->decimal('commission_value', 12, 2)->nullable();

            $table->timestamps();

            $table->unique(['role_id', 'trx_type']); // unique limit per role & transaction type

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_limits');
    }
};

