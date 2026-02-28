<?php

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
        Schema::create('fwpm_study_program', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('fwpm_id')
                ->constrained('fwpms')
                ->cascadeOnDelete();
            $table->foreignId('study_program_id')
                ->constrained('study_programs')
                ->cascadeOnDelete();
            $table->integer('approval_status')->default(0); // -1, 0, 1, 2
            $table->timestamps();

            $table->unique(['fwpm_id', 'study_program_id']);
            $table->index('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fwpm_study_program');
    }
};
