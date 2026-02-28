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
        Schema::create('study_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // BEC, BIN, BWI, etc.
            $table->string('name_german');
            $table->string('name_english');
            $table->string('degree_level'); // bachelor or master
            $table->string('degree_field'); // science, arts, engineering, business
            $table->boolean('is_dual')->default(false); // for BDGD, BISD variants
            $table->foreignId('base_program_id')
                ->nullable()
                ->constrained('study_programs')
                ->nullOnDelete(); // links BDGD -> BDG
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_programs');
    }
};
