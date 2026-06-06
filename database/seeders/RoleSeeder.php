<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['slug' => 'pending'],
            ['name' => 'Pendiente', 'description' => 'Usuario registrado sin rol asignado']
        );

        Role::updateOrCreate(
            ['slug' => 'student'],
            ['name' => 'Estudiante', 'description' => 'Usuario con acceso a su dashboard y matrículas']
        );

        Role::updateOrCreate(
            ['slug' => 'teacher'],
            ['name' => 'Profesor', 'description' => 'Usuario con permisos para gestionar cursos y calificaciones']
        );

        Role::updateOrCreate(
            ['slug' => 'superadmin'],
            ['name' => 'Super Administrador', 'description' => 'Usuario con permisos completos sobre el sistema']
        );

        Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrador', 'description' => 'Usuario con permisos administrativos principales']
        );

        Role::updateOrCreate(
            ['slug' => 'coordinator'],
            ['name' => 'Coordinador', 'description' => 'Usuario con funciones administrativas específicas']
        );
    }
}
