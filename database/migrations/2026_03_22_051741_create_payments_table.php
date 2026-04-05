<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('method');
            $table->string('gateway_type');
            $table->string('item_type');

            $table->string('phone')->nullable();
            $table->string('account_number')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('address')->nullable();

            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('app_key')->nullable();
            $table->string('secret_key')->nullable();

            $table->decimal('minimum_amount', 10, 2);
            $table->decimal('maximum_amount', 10, 2);

            // FIX: make nullable or default 0
            $table->decimal('fees', 5, 2)->default(0);
            $table->decimal('tax', 5, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0);

            $table->string('processing_time')->nullable();
            $table->string('logo_url')->nullable();

            $table->boolean('is_active')->default(false);
            $table->text('remarks')->nullable();

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