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
        Schema::create('answer_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('answer_id');
            $table->uuid('question_option_id');

            $table->foreign('answer_id')->references('id')->on('answers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('question_option_id')->references('id')->on('question_options')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_options');
    }
};
