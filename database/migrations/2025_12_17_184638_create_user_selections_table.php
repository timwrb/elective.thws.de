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
        Schema::create('user_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->string('elective_type'); // Polymorphic type (Awpf or Fwpm)
            $table->uuid('elective_choice_id'); // Polymorphic ID (UUID)
            $table->unsignedBigInteger('parent_elective_choice_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('enrollment_type')->default('priority');
            $table->timestamps();

            // Self-referential foreign key for ordered choices
            $table->foreign('parent_elective_choice_id')
                ->references('id')
                ->on('user_selections')
                ->cascadeOnDelete();

            // Indexes for performance
            $table->index(['user_id', 'semester_id', 'elective_type']);
            $table->index(['elective_type', 'elective_choice_id']);
            $table->index(['status', 'semester_id']);
            $table->index(['elective_type', 'elective_choice_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_selections');
    }
};
