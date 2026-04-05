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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('admin_id')->unique();
            $table->string('name');
            $table->string('password');

            // Balance related
            $table->decimal('available_balance', 15, 2)->default(0.00);
            $table->decimal('hold_balance', 10, 2)->default(0.00);
            $table->decimal('stock_balance', 8, 2)->default(0.00);

            // Profile and device
            $table->text('profile_url');
            $table->string('fcm_token')->nullable();
            $table->string('app_version')->nullable();
            $table->boolean('admin_status')->default(true);
            $table->string('status_message')->nullable();
            $table->string('device_id')->nullable();
            $table->string('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            // Security and login tracking
            $table->integer('login_attempts')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->text('locked_reason')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();

            // User preferences
            $table->boolean('notification_enabled')->default(true);
            $table->json('notification_category')->nullable();
            $table->string('language_preference', 10)->default('en');
            $table->string('theme_mode', 10)->default('light');

            // Verification info
            $table->string('nid_number', 30)->nullable();
            $table->text('verification_doc')->nullable();
            $table->timestamp('verified_at')->nullable();
            
            // Role & permissions
            $table->foreignId('role_id')->nullable()
                ->constrained('admin_roles')
                ->onDelete('set null');
            $table->boolean('is_active')->default(true);

            // Audit trail
            $table->foreignId('created_by')->nullable()
                ->constrained('admins')
                ->onDelete('set null');

            $table->foreignId('updated_by')->nullable()
                ->constrained('admins')
                ->onDelete('set null');

            $table->text('remarks')->nullable();

            // Optional enhancements
            $table->string('timezone', 50)->default('Asia/Dhaka');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
