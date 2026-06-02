<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        $existingRoles = DB::table('roles')->pluck('slug')->all();

        $roles = [
            ['slug' => 'pending', 'name' => 'Sin asignar', 'description' => 'Usuario registrado sin rol asignado'],
            ['slug' => 'admin', 'name' => 'Administrador', 'description' => 'Usuario con permisos de administración'],
            ['slug' => 'teacher', 'name' => 'Docente', 'description' => 'Usuario con rol docente'],
            ['slug' => 'student', 'name' => 'Estudiante', 'description' => 'Usuario con rol estudiante'],
            ['slug' => 'coordinator', 'name' => 'Coordinador', 'description' => 'Usuario con rol coordinador'],
        ];

        $insert = [];
        foreach ($roles as $role) {
            if (! in_array($role['slug'], $existingRoles, true)) {
                $insert[] = array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! empty($insert)) {
            DB::table('roles')->insert($insert);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
