<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('status')->default('active')->after('role_id');
            });
        }

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (! Schema::hasColumn('courses', 'name')) {
                    $table->string('name')->after('id');
                }

                if (! Schema::hasColumn('courses', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }

                if (! Schema::hasColumn('courses', 'professor_id')) {
                    $table->foreignId('professor_id')->nullable()->constrained('users')->nullOnDelete()->after('description');
                }

                if (! Schema::hasColumn('courses', 'status')) {
                    $table->string('status')->default('active')->after('professor_id');
                }

                if (! Schema::hasColumn('courses', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
                }

                if (! Schema::hasColumn('courses', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
                }
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (! Schema::hasColumn('subjects', 'course_id')) {
                    $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete()->after('id');
                }

                if (! Schema::hasColumn('subjects', 'name')) {
                    $table->string('name')->after('course_id');
                }

                if (! Schema::hasColumn('subjects', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }

                if (! Schema::hasColumn('subjects', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('description');
                }

                if (! Schema::hasColumn('subjects', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
                }
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (! Schema::hasColumn('students', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->after('id');
                }

                if (! Schema::hasColumn('students', 'status')) {
                    $table->string('status')->default('active')->after('user_id');
                }
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (! Schema::hasColumn('teachers', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->after('id');
                }
            });
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (! Schema::hasColumn('attendances', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->after('id');
                }

                if (! Schema::hasColumn('attendances', 'attendance_type')) {
                    $table->string('attendance_type')->default('entry')->after('user_id');
                }

                if (! Schema::hasColumn('attendances', 'recorded_at')) {
                    $table->timestamp('recorded_at')->nullable()->after('attendance_type');
                }

                if (! Schema::hasColumn('attendances', 'note')) {
                    $table->text('note')->nullable()->after('recorded_at');
                }
            });
        }

        if (! Schema::hasTable('enrollments')) {
            Schema::create('enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->date('enrollment_date');
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('schedules')) {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->string('day_of_week');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('classroom');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->decimal('grade', 5, 2);
                $table->text('observations')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('grades')) {
            Schema::dropIfExists('grades');
        }

        if (Schema::hasTable('schedules')) {
            Schema::dropIfExists('schedules');
        }

        if (Schema::hasTable('enrollments')) {
            Schema::dropIfExists('enrollments');
        }

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'note')) {
                    $table->dropColumn('note');
                }
                if (Schema::hasColumn('attendances', 'recorded_at')) {
                    $table->dropColumn('recorded_at');
                }
                if (Schema::hasColumn('attendances', 'attendance_type')) {
                    $table->dropColumn('attendance_type');
                }
                if (Schema::hasColumn('attendances', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }

        if (Schema::hasTable('teachers')) {
            Schema::table('teachers', function (Blueprint $table) {
                if (Schema::hasColumn('teachers', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('students', 'user_id')) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                }
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (Schema::hasColumn('subjects', 'updated_by')) {
                    $table->dropForeign(['updated_by']);
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn('subjects', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn('subjects', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('subjects', 'name')) {
                    $table->dropColumn('name');
                }
                if (Schema::hasColumn('subjects', 'course_id')) {
                    $table->dropForeign(['course_id']);
                    $table->dropColumn('course_id');
                }
            });
        }

        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                if (Schema::hasColumn('courses', 'updated_by')) {
                    $table->dropForeign(['updated_by']);
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn('courses', 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn('courses', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('courses', 'professor_id')) {
                    $table->dropForeign(['professor_id']);
                    $table->dropColumn('professor_id');
                }
                if (Schema::hasColumn('courses', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('courses', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'status')) {
                    $table->dropColumn('status');
                }
            });
        }
    }
};
