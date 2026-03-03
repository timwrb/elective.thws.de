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
            $table->decimal('credits', 4, 1)->default(2.5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awpfs', function (Blueprint $table) {
            $table->integer('credits')->default(5)->change();
        });
    }
};
