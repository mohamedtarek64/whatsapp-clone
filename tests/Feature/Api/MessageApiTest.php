<?php

namespace Tests\Feature\Api;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_get_messages_requires_authentication()
    {
        $chat = Chat::factory()->create();

        $response = $this->getJson("/api/chats/{$chat->id}/messages");

        $response->assertStatus(401);
    }

    public function test_get_messages_requires_authorization()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($otherUser->id);

        $response = $this->actingAs($user)->getJson("/api/chats/{$chat->id}/messages");

        $response->assertStatus(403);
    }

    public function test_get_messages_returns_paginated_messages()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($user->id);
        Message::factory(15)->for($user)->for($chat)->create();

        $response = $this->actingAs($user)->getJson("/api/chats/{$chat->id}/messages");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'pagination' => ['total', 'per_page', 'current_page', 'last_page']
        ]);
    }

    public function test_send_message_requires_authentication()
    {
        $chat = Chat::factory()->create();

        $response = $this->postJson("/api/chats/{$chat->id}/messages", [
            'message' => 'Hello'
        ]);

        $response->assertStatus(401);
    }

    public function test_send_message_validates_input()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($user->id);

        $response = $this->actingAs($user)->postJson("/api/chats/{$chat->id}/messages", [
            'message' => ''
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    public function test_send_message_creates_message()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($user->id);

        $response = $this->actingAs($user)->postJson("/api/chats/{$chat->id}/messages", [
            'message' => 'Hello World!'
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.body', 'Hello World!');
        $response->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('messages', [
            'body' => 'Hello World!',
            'user_id' => $user->id,
            'chat_id' => $chat->id,
        ]);
    }

    public function test_mark_as_read_updates_messages()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach([$user->id, $otherUser->id]);
        Message::factory(5)->for($otherUser)->for($chat)->create(['is_read' => false]);

        $response = $this->actingAs($user)->patchJson("/api/chats/{$chat->id}/messages/read");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'user_id' => $otherUser->id,
            'is_read' => true,
        ]);
    }

    public function test_get_unread_messages()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach([$user->id, $otherUser->id]);
        Message::factory(5)->for($otherUser)->for($chat)->create(['is_read' => false]);
        Message::factory(3)->for($otherUser)->for($chat)->create(['is_read' => true]);

        $response = $this->actingAs($user)->getJson("/api/chats/{$chat->id}/messages/unread");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(5, 'data');
    }
}
