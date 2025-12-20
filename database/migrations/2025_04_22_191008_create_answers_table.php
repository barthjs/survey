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
        Schema::create('answers', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('question_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUlid('response_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();

            $table->text('answer_text')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_file_name')->nullable();
        });
    }
};
