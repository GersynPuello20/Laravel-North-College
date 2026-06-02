<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'name')) {
                $table->string('name')->after('id');
            }
            if (! Schema::hasColumn('courses', 'code')) {
                $table->string('code')->unique()->after('name');
            }
            if (! Schema::hasColumn('courses', 'description')) {
                $table->text('description')->nullable()->after('code');
            }
            if (! Schema::hasColumn('courses', 'capacity')) {
                $table->integer('capacity')->nullable()->after('description');
            }
            if (! Schema::hasColumn('courses', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('capacity')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'teacher_id')) {
                $table->dropForeign(['teacher_id']);
                $table->dropColumn('teacher_id');
            }
            if (Schema::hasColumn('courses', 'capacity')) {
                $table->dropColumn('capacity');
            }
            if (Schema::hasColumn('courses', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('courses', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('courses', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
