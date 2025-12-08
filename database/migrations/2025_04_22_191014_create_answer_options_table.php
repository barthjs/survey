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
            $table->ulid('id')->primary();

            $table->foreignUlid('answer_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUlid('question_option_id')
                ->index()
                ->constrained('question_options')
                ->cascadeOnDelete();
        });
    }
};
