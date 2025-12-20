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
        Schema::create('surveys', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->foreignUlid('user_id')
                ->index()
                ->constrained('sys_users')
                ->cascadeOnDelete();

            $table->string('title')->index();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->dateTime('end_date')->nullable()->index();
            $table->dateTime('auto_closed_at')->nullable();
        });
    }
};
