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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Identifiers
            $table->bigInteger('user_id')->unique();

            // Personal Info
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('occupation')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_phone', 15)->nullable();
            $table->string('income_source')->nullable();
            $table->unsignedBigInteger('nid_number')->nullable();
            $table->string('address')->nullable();

            // Security
            $table->string('password')->nullable();
            $table->string('pin');

            // Balances
            $table->decimal('available_balance', 15, 2)->default(0.00);
            $table->decimal('hold_balance', 15, 2)->default(0.00);
            $table->decimal('stock_balance', 15, 2)->default(0.00);

            // Profile & Account
            $table->text('profile_url');
            // ✅ Correct foreign key relationship
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('user_roles') // references id in user_roles
                ->onUpdate('cascade')
                ->onDelete('set null');
                
            $table->string('referral_code', 20)->nullable();
            $table->string('account_reference')->nullable();

            // KYC
            $table->enum('kyc_verified', ['pending', 'verified', 'rejected'])->nullable();
            $table->json('kyc_document_url')->nullable();

            $table->boolean('incoming_trans')->default(true); // Allow incoming transactions
            $table->boolean('outgoing_trans')->default(true); // Allow outgoing transactions
            
            // Timestamps
            $table->timestamp('created_at') ->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('gender', 'idx_user_gender');
            $table->index('available_balance', 'idx_user_available_balance');
            $table->index('stock_balance', 'idx_user_stock_balance');
            $table->index('nid_number', 'idx_user_nid');
            $table->index(['kyc_verified'], 'idx_user_status_kyc');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
