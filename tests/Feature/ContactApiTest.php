<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_contact()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/contacts', [
                'name' => 'Friend',
                'contact_id' => $other->id,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Friend']);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $user->id,
            'contact_id' => $other->id,
            'name' => 'Friend',
        ]);
    }

    public function test_cannot_add_self_as_contact()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/contacts', [
                'name' => 'Me',
                'contact_id' => $user->id,
            ])
            ->assertStatus(422);
    }
}
