<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('course_schedules');

        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('schedulable');
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index(['schedulable_type', 'schedulable_id', 'scheduled_at'], 'schedulable_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_schedules');

        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('schedulable');
            $table->string('day_of_week');
            $table->time('start_time');
            $table->unsignedInteger('duration_minutes');
            $table->timestamps();

            $table->index(['schedulable_type', 'schedulable_id', 'day_of_week'], 'schedulable_day_index');
        });
    }
};
