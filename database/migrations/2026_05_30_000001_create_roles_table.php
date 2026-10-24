<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['slug' => 'pending', 'name' => 'Sin asignar', 'description' => 'Usuario registrado sin rol asignado', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'admin', 'name' => 'Administrador', 'description' => 'Usuario con permisos de administración', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'teacher', 'name' => 'Docente', 'description' => 'Usuario con rol docente', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'student', 'name' => 'Estudiante', 'description' => 'Usuario con rol estudiante', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'coordinator', 'name' => 'Coordinador', 'description' => 'Usuario con rol coordinador', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
