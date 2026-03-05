<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_projects', function (Blueprint $table): void {
            $table->string('invite_token')->nullable()->unique()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('research_projects', function (Blueprint $table): void {
            $table->dropColumn('invite_token');
        });
    }
};
