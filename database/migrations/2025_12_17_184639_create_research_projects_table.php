<?php

use App\Enums\ElectiveStatus;
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
        Schema::create('research_projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignUuid('professor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignUuid('creator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->integer('credits')->default(5);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('semester_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('max_students')->default(1);
            $table->string('status')->default(ElectiveStatus::Draft->value);
            $table->timestamps();

            $table->index(['semester_id', 'creator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_projects');
    }
};
