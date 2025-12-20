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
            $table->ulid('id')->primary();

            $table->foreignUlid('survey_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();

            $table->ipAddress()->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('submitted_at')->useCurrent()->index();
        });
    }
};
