<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_security_preferences', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');

            // Security & Login Tracking
            $table->integer('login_attempts')->default(0);
            $table->integer('pin_attempts')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_reason')->nullable();
            $table->timestamp('last_failed_login')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamp('pin_changed_at')->nullable();

            // Two Factor
            $table->boolean('two_factor_enabled_email')->default(false);
            $table->boolean('two_factor_enabled_phone')->default(false);
            $table->text('two_factor_secret')->nullable();

            // Status
            $table->boolean('user_status')->default(true);
            $table->string('status_message')->nullable();
            $table->string('referral_code', 20)->nullable();

            // Device Info
            $table->string('device_id', 100);
            $table->string('device_type', 100);
            $table->string('device_name', 150);
            $table->string('device_model', 100);
            $table->string('os', 50);
            $table->string('os_version', 50);
            $table->string('device_fcm_token')->nullable();
            $table->string('app_version', 20)->nullable();

            // Device Usage
            $table->string('ip_address', 45)->nullable();
            $table->string('location')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            // Security Flags
            $table->boolean('is_trusted')->default(false);
            $table->boolean('is_blacklisted')->default(false);
            $table->text('remarks')->nullable();

            // Preferences
            $table->string('timezone', 50)->default('Asia/Dhaka');
            $table->boolean('notification_enabled')->default(true);
            $table->string('language_preference', 10)->default('en');
            $table->string('theme_mode', 10)->default('light');
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('device_model', 'idx_device_model');
            $table->index('is_blacklisted', 'idx_is_blacklisted');
            $table->index('last_login_at', 'idx_last_login');
            $table->index('is_locked', 'idx_is_locked');

            // Foreign Key
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_security_preferences');
    }
};
