<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte `students` en el perfil académico del usuario estudiante (los
 * datos de contacto ya viven en `users`) y evita matrículas duplicadas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            foreach (['students_email_unique', 'students_enrollment_number_unique'] as $index) {
                if (Schema::hasIndex('students', $index)) {
                    $table->dropUnique($index);
                }
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'course', 'phone']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('enrollment_number')->nullable()->change();
            $table->unique('enrollment_number');
            $table->unique('user_id');
            $table->string('document_number', 30)->nullable()->after('enrollment_number');
            $table->date('birth_date')->nullable()->after('document_number');
            $table->string('guardian_name')->nullable()->after('birth_date');
            $table->string('guardian_document', 30)->nullable()->after('guardian_name');
            $table->string('guardian_email')->nullable()->after('guardian_document');
            $table->string('guardian_phone', 30)->nullable()->after('guardian_email');
        });

        // Elimina matrículas repetidas antes de exigir unicidad.
        $duplicates = DB::table('enrollments')
            ->select('student_id', 'course_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('student_id', 'course_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('enrollments')
                ->where('student_id', $duplicate->student_id)
                ->where('course_id', $duplicate->course_id)
                ->where('id', '<>', $duplicate->keep_id)
                ->delete();
        }

        Schema::table('enrollments', function (Blueprint $table) {
            $table->unique(['student_id', 'course_id']);
            $table->index(['course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'course_id']);
            $table->dropIndex(['course_id', 'status']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropUnique(['enrollment_number']);
            $table->dropColumn(['document_number', 'birth_date', 'guardian_name', 'guardian_document', 'guardian_email', 'guardian_phone']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('course')->nullable();
            $table->string('phone')->nullable();
        });
    }
};
