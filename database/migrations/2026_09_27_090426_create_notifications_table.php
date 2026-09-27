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
        Schema::create('notifications', function (Blueprint $table) {
            // Receiver Info
            $table->id();

            // Identifiers
            $table->bigInteger('user_id')->nullable();


            // Target Group
            $table->string('recipient_type')->default('admin');

            // Notification Content
            $table->string('title');
            $table->text('body');
            $table->text('image_url')->nullable();

            // Type & Metadata
            $table->string('type', 50)->nullable();
            // এখানে slider বা অন্যান্য সব মাধ্যম যুক্ত করে ENUM আপডেট করা হয়েছে
            // এখানে নতুন চ্যানেলগুলো যুক্ত করে মোট ১৫টির মতো অপশন করা হয়েছে
            $table->enum('channel', [
                'PUSH',
                'EMAIL',
                'SMS',
                'IN_APP',
                'SLIDER',
                'ALL',
                'WHATSAPP',
                'TELEGRAM',
                'WEB_HOOK',
                'BANNER',
                'POPUP',
                'BROADCAST',
                'SLACK',
                'FCM',
            ])->default('IN_APP');

            $table->text('action_url')->nullable();

            // Priority and Sender
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->string('sender', 50)->default('system');

            // Status Flags
            $table->boolean('seen')->default(false);
            $table->boolean('delivered')->default(false);

            // Expiration Time for Auto Delete
            $table->timestamp('expire_at')->nullable();

            $table->string('created_by', 15)->nullable();
            $table->bigInteger('updated_by')->nullable();

            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('user_id', 'idx_notification_user');
            $table->index('seen', 'idx_notification_seen');
            $table->index('type', 'idx_notification_type');
            $table->index('channel', 'idx_notification_channel');
            $table->index('recipient_type', 'idx_recipient_type');
            $table->index('expire_at', 'idx_expire_at');
            $table->index('priority', 'idx_priority');

            // Foreign Keys
            $table->foreign('user_id', 'fk_notifications_user')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('updated_by', 'fk_notifications_updated_by')
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
        Schema::dropIfExists('notifications');
    }
};