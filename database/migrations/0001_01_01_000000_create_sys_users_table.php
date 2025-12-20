<?php

declare(strict_types=1);

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
        Schema::create('sys_users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('new_email')->unique()->nullable()->comment('New email until confirmed');
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->rememberToken();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_admin')->default(false);
        });

        Schema::create('sys_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sys_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
};
