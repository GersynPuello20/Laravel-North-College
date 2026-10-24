<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_sends_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_root_sends_users_to_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/')->assertRedirect(route('dashboard'));
    }
}
