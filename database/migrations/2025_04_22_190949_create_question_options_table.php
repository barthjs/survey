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
        Schema::create('question_options', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('question_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('option_text');
            $table->integer('order_index');
        });
    }
};
