<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('student')->after('email');
            });
        }

        $addedCourseCode = false;
        Schema::table('courses', function (Blueprint $table) use (&$addedCourseCode) {
            if (! Schema::hasColumn('courses', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('courses', 'code')) {
                $table->string('code', 20)->nullable();
                $addedCourseCode = true;
            }
            if (! Schema::hasColumn('courses', 'title')) {
                $table->string('title')->nullable();
            }
            if (! Schema::hasColumn('courses', 'units')) {
                $table->unsignedTinyInteger('units')->default(3);
            }
            if (! Schema::hasColumn('courses', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        if ($addedCourseCode) {
            $ownerId = DB::table('users')->orderBy('id')->value('id');

            DB::table('courses')->orderBy('id')->get()->each(function ($course) use ($ownerId) {
                DB::table('courses')->where('id', $course->id)->update([
                    'user_id' => $course->user_id ?? $ownerId,
                    'code' => $course->code ?? 'COURSE-'.$course->id,
                    'title' => $course->title ?? 'Imported course '.$course->id,
                ]);
            });

            Schema::table('courses', function (Blueprint $table) {
                $table->unique('code');
            });
        }

        $addedPivotColumns = false;
        Schema::table('course_user', function (Blueprint $table) use (&$addedPivotColumns) {
            if (! Schema::hasColumn('course_user', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
                $addedPivotColumns = true;
            }
            if (! Schema::hasColumn('course_user', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $addedPivotColumns = true;
            }
        });

        if ($addedPivotColumns) {
            Schema::table('course_user', function (Blueprint $table) {
                $table->unique(['course_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
    }
};