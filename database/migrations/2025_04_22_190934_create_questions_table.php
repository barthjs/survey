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
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->uuid('survey_id');

            $table->text('question_text');
            $table->enum('type', array_column(QuestionType::cases(), 'value'));
            $table->boolean('is_required')->default(false);
            $table->integer('order_index');

            $table->foreign('survey_id')->references('id')->on('surveys')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
