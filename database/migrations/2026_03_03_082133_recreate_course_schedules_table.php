<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_schedules', function (Blueprint $table): void {
            if (! Schema::hasColumn('course_schedules', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('schedulable_id');
            }

            if (! Schema::hasColumn('course_schedules', 'location')) {
                $table->string('location')->nullable()->after('duration_minutes');
            }
        });

        if (Schema::hasColumn('course_schedules', 'day_of_week')) {
            DB::table('course_schedules')
                ->whereNull('scheduled_at')
                ->orderBy('id')
                ->each(function (object $row): void {
                    $scheduledAt = Carbon::parse('next '.$row->day_of_week)
                        ->setTimeFromTimeString($row->start_time);

                    DB::table('course_schedules')
                        ->where('id', $row->id)
                        ->update(['scheduled_at' => $scheduledAt]);
                });

            Schema::table('course_schedules', function (Blueprint $table): void {
                $table->dateTime('scheduled_at')->nullable(false)->change();
            });

            Schema::table('course_schedules', function (Blueprint $table): void {
                if (Schema::hasIndex('course_schedules', 'schedulable_day_index')) {
                    $table->dropIndex('schedulable_day_index');
                }

                $table->dropColumn(['day_of_week', 'start_time']);
            });
        }

        if (! Schema::hasIndex('course_schedules', 'schedulable_date_index')) {
            Schema::table('course_schedules', function (Blueprint $table): void {
                $table->index(
                    ['schedulable_type', 'schedulable_id', 'scheduled_at'],
                    'schedulable_date_index'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('course_schedules', function (Blueprint $table): void {
            if (! Schema::hasColumn('course_schedules', 'day_of_week')) {
                $table->string('day_of_week')->nullable()->after('schedulable_id');
            }

            if (! Schema::hasColumn('course_schedules', 'start_time')) {
                $table->time('start_time')->nullable()->after('day_of_week');
            }
        });

        if (Schema::hasColumn('course_schedules', 'scheduled_at')) {
            DB::table('course_schedules')
                ->whereNull('day_of_week')
                ->orderBy('id')
                ->each(function (object $row): void {
                    $date = Carbon::parse($row->scheduled_at);

                    DB::table('course_schedules')
                        ->where('id', $row->id)
                        ->update([
                            'day_of_week' => strtolower($date->englishDayOfWeek),
                            'start_time' => $date->format('H:i:s'),
                        ]);
                });

            Schema::table('course_schedules', function (Blueprint $table): void {
                $table->string('day_of_week')->nullable(false)->change();
                $table->time('start_time')->nullable(false)->change();
            });

            Schema::table('course_schedules', function (Blueprint $table): void {
                if (Schema::hasIndex('course_schedules', 'schedulable_date_index')) {
                    $table->dropIndex('schedulable_date_index');
                }

                $table->dropColumn(['scheduled_at', 'location']);
            });
        }

        if (! Schema::hasIndex('course_schedules', 'schedulable_day_index')) {
            Schema::table('course_schedules', function (Blueprint $table): void {
                $table->index(
                    ['schedulable_type', 'schedulable_id', 'day_of_week'],
                    'schedulable_day_index'
                );
            });
        }
    }
};
