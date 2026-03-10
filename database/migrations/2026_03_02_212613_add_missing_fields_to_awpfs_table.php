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
            $table->integer('max_participants')->nullable()->after('credits');
            $table->decimal('hours_per_week', 3, 1)->nullable()->after('max_participants');
            $table->string('type_of_class')->nullable()->after('hours_per_week');
            $table->text('goals')->nullable()->after('content');
            $table->text('literature')->nullable()->after('goals');
            $table->string('lecturer_name')->nullable()->after('professor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awpfs', function (Blueprint $table) {
            $table->dropColumn([
                'max_participants',
                'hours_per_week',
                'type_of_class',
                'goals',
                'literature',
                'lecturer_name',
            ]);
        });
    }
};
