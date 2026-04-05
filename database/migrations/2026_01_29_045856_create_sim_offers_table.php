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
        Schema::create('sim_offers', function (Blueprint $table) {
            $table->id();
            $table->string('item_type', 50);
            $table->string('type', 50);
            $table->string('operator', 50);
            $table->string('title', 255);
            $table->string('code', 20)->nullable();
            $table->string('validity', 50);
            $table->decimal('price', 8, 2);
            $table->decimal('cashback', 8, 2)->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->text('details')->nullable();
            $table->text('tag')->nullable();
            $table->string('activation_method', 255)->nullable();
            $table->text('eligibility')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->dateTime('cashback_start_at')->nullable();
            $table->decimal('cashback_hold', 8, 2)->nullable();
            $table->dateTime('cashback_end_at')->nullable();
            
            $table->dateTime('offer_start_at')->nullable();
            $table->dateTime('offer_end_at')->nullable();

            $table->bigInteger('offer_stock')->default(0);

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('status', 'idx_status');
            $table->index('cashback', 'idx_cashback');
            $table->index('price', 'idx_price');
            $table->index('type', 'idx_type');
            $table->index('discount', 'idx_discount');
            $table->index('operator', 'idx_operator');
            //$table->index('item_type', 'idx_item_type');

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
        Schema::dropIfExists('sim_offers');
    }
};
