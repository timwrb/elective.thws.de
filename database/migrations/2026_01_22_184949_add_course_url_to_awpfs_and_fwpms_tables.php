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
        Schema::table('awpfs', function (Blueprint $table) {
            $table->string('course_url')->nullable();
        });

        Schema::table('fwpms', function (Blueprint $table) {
            $table->string('course_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awpfs', function (Blueprint $table) {
            $table->dropColumn('course_url');
        });

        Schema::table('fwpms', function (Blueprint $table) {
            $table->dropColumn('course_url');
        });
    }
};
