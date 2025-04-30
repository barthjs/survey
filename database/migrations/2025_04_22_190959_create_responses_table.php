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
        Schema::create('responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('survey_id');

            $table->ipAddress()->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('submitted_at')->useCurrent()->index();

            $table->foreign('survey_id')->references('id')->on('surveys')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
