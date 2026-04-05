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
        Schema::create('mobile_recharges', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('operator');
            $table->string('request_type');
            $table->json('block_amount');
            $table->json('pending_amount');
            $table->string('ussd');
            $table->decimal('balance', 15, 2);
            $table->boolean('is_pending');
            $table->boolean('is_active');

            $table->decimal('minimum_amount', 15, 2);
            $table->decimal('maximum_amount', 15, 2);
            $table->boolean('is_offer_active')->default(true);

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign Key
            $table->foreign('created_by')
                ->references('admin_id')
                ->on('admins')
                ->onDelete('restrict')
                ->cascadeOnUpdate();

            $table->foreign('updated_by')
                ->references('admin_id')
                ->on('admins')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_recharges');
    }
};
