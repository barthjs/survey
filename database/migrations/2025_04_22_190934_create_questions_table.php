<?php

declare(strict_types=1);

use App\Enums\QuestionType;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('survey_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();

            $table->text('question_text');
            $table->enum('type', array_column(QuestionType::cases(), 'value'));
            $table->boolean('is_required')->default(false);
            $table->integer('order_index');
        });
    }
};
