<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historial de autorizaciones (Ley 1581 de 2012). No se borra al revocar.
        Schema::create('biometric_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('version', 40);
            $table->string('status', 20)->default('granted');
            $table->boolean('is_minor')->default(false);
            $table->string('granted_by_name');
            $table->string('granted_by_document', 30)->nullable();
            $table->string('granted_by_relationship', 40);
            $table->boolean('minor_was_heard')->nullable();
            $table->dateTime('granted_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('revocation_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        // Un solo vector biométrico vigente por estudiante, cifrado con APP_KEY.
        Schema::create('face_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('biometric_consent_id')->constrained('biometric_consents')->cascadeOnDelete();
            $table->text('embedding');
            $table->string('engine', 80);
            $table->decimal('quality', 5, 3)->nullable();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Intentos de reconocimiento: solo el resultado, nunca imágenes ni vectores.
        Schema::create('face_recognition_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->nullable()->constrained('class_sessions')->cascadeOnDelete();
            $table->string('purpose', 20);
            $table->string('result', 30);
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('score', 5, 4)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['class_session_id', 'result']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('face_recognition_attempts');
        Schema::dropIfExists('face_templates');
        Schema::dropIfExists('biometric_consents');
    }
};
