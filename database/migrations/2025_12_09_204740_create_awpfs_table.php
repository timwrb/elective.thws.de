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
        Schema::create('awpfs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('content')->nullable();
            $table->integer('credits')->default(5);
            $table->string('language');
            $table->string('exam_type');
            $table->string('status')->default(ElectiveStatus::Draft->value);
            $table->foreignUuid('professor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awpfs');
    }
};
