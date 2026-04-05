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
        // Schema::create('user_permissions', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name')->unique(); // e.g. view_users
        //     $table->string('display_name')->nullable(); // e.g. View Users
        //     $table->string('group')->nullable(); // e.g. user-management
        //     $table->string('show_in')->nullable();
        //     $table->string('description')->nullable();
        //     $table->enum('is_active', ['active', 'inactive', 'hiden'])->default('active');
        //     $table->timestamp('created_at')
        //         ->useCurrent();

        //     $table->timestamp('updated_at')
        //         ->useCurrent()
        //         ->useCurrentOnUpdate();
        // });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            
            // Permission identity
            $table->string('name')->unique();              // view_users
            $table->string('display_name')->nullable();    // View Users

            // Grouping & UI control
            $table->string('module')->nullable();           // user-management
            $table->string('show_in')->nullable();          // admin, api, both

            // Description
            $table->text('description')->nullable();

            // Status (instead of enum)
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hidden')->default(false);

            // Ordering / Priority
            $table->bigInteger('priority')->default(0);

            // Audit
            $table->timestamp('created_at')
                ->useCurrent();

            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->softDeletes();

            $table->unique(['show_in', 'priority']);

            // Indexes
            $table->index(['module']);
            $table->index(['is_active']);
            $table->index(['priority']);

        });





        Schema::create('user_role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('user_roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('user_permissions')->onDelete('cascade');
            $table->timestamp('created_at')
                ->useCurrent();

            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('user_role_permission');
    }
};
