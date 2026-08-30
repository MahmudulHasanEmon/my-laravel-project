<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            // Payment request information
            $table->string('request');
            $table->string('method');
            $table->enum('gateway_type', [
                'bank',
                'card',
                'mfs',
                'qrcode',
                'other'
            ])->default('other');
            
            $table->string('item_type');

            // Account information
            $table->string('phone')->nullable();
            $table->string('account_number')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('address')->nullable();

            // Gateway credentials
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('app_key')->nullable();
            $table->string('secret_key')->nullable();

            // Amount limits
            $table->decimal('minimum_amount', 12, 2);
            $table->decimal('maximum_amount', 12, 2);

            // Charges
            $table->decimal('fees', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);

            // Additional information
            $table->string('processing_time')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->text('remarks')->nullable();

            // Audit
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign Keys
            $table->foreign('created_by')
                ->references('admin_id')
                ->on('admins')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('updated_by')
                ->references('admin_id')
                ->on('admins')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
