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
        Schema::table('fwpms', function (Blueprint $table) {
            // Rename existing columns
            $table->renameColumn('name', 'name_german');
            $table->renameColumn('content', 'contents');
        });

        Schema::table('fwpms', function (Blueprint $table) {
            // Add name_english after name_german
            $table->string('name_english')->after('name_german');

            // API reference fields
            $table->integer('fiwis_id')->unique()->after('id');
            $table->string('module_number')->nullable()->after('fiwis_id');

            // Course metadata
            $table->integer('max_participants')->nullable()->after('credits');
            $table->decimal('hours_per_week', 3, 1)->nullable()->after('max_participants');
            $table->string('type_of_class')->nullable()->after('hours_per_week');
            $table->string('recommended_semester')->nullable()->after('type_of_class');

            // Learning content fields
            $table->text('goals')->nullable()->after('contents');
            $table->text('literature')->nullable()->after('goals');
            $table->text('media')->nullable()->after('literature');
            $table->text('tools')->nullable()->after('media');

            // Prerequisites
            $table->text('prerequisite_recommended')->nullable()->after('tools');
            $table->text('prerequisite_formal')->nullable()->after('prerequisite_recommended');

            // Study hours
            $table->integer('total_hours_lectures')->nullable()->after('prerequisite_formal');
            $table->integer('total_hours_self_study')->nullable()->after('total_hours_lectures');

            // Semester link
            $table->foreignId('semester_id')
                ->nullable()
                ->after('professor_id')
                ->constrained('semesters')
                ->nullOnDelete();

            // Lecturer name (in addition to professor_id FK)
            $table->string('lecturer_name')->nullable()->after('professor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fwpms', function (Blueprint $table) {
            // Drop added columns
            $table->dropConstrainedForeignId('semester_id');
            $table->dropColumn([
                'name_english',
                'fiwis_id',
                'module_number',
                'max_participants',
                'hours_per_week',
                'type_of_class',
                'recommended_semester',
                'goals',
                'literature',
                'media',
                'tools',
                'prerequisite_recommended',
                'prerequisite_formal',
                'total_hours_lectures',
                'total_hours_self_study',
                'lecturer_name',
            ]);
        });

        Schema::table('fwpms', function (Blueprint $table) {
            // Rename columns back
            $table->renameColumn('name_german', 'name');
            $table->renameColumn('contents', 'content');
        });
    }
};
