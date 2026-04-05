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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('session_token')->unique(); // random UUID for ticket
            $table->string('otp_code')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('device_id')->nullable();      // optional device fingerprint
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('attempts')->default(0);     // OTP try count
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();    // when ticket consumed (PIN/login/register done)
            $table->timestamp('created_at')
                ->useCurrent();

            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->index(['phone', 'session_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
