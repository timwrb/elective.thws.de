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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('start_semester_id')
                ->nullable()
                ->after('email')
                ->constrained('semesters')
                ->nullOnDelete();

            $table->index('start_semester_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['start_semester_id']);
            $table->dropColumn('start_semester_id');
        });
    }
};
