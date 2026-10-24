<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => User::STATUS_ACTIVE,
        ];
    }

    /**
     * Asigna un rol por su slug, creándolo si la base de pruebas no lo tiene.
     */
    public function role(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)])->id,
        ]);
    }

    public function student(): static
    {
        return $this->role('student');
    }

    public function teacher(): static
    {
        return $this->role('teacher');
    }

    public function admin(): static
    {
        return $this->role('admin');
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
