<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-admin {--email=} {--password=} {--name=}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Crea un usuario super admin en el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->option('name') ?? $this->ask('¿Nombre del super admin?', 'Super Admin');
        $email = $this->option('email') ?? $this->ask('¿Correo del super admin?', 'admin@northcollege.edu');
        $password = $this->option('password') ?? $this->secret('¿Contraseña del super admin?');

        if (User::where('email', $email)->exists()) {
            $this->error("El usuario con correo {$email} ya existe.");
            return 1;
        }

        $adminRole = Role::firstWhere('slug', 'admin');
        if (! $adminRole) {
            $this->error('El rol admin no existe. Ejecuta primero las migraciones.');
            return 1;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $this->info("✅ Super admin creado exitosamente.");
        $this->line("📧 Email: {$email}");

        return 0;
    }
}
