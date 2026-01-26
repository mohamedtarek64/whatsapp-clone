<?php

namespace Tests\Unit\Services;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private MessageService $messageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageService = app(MessageService::class);
    }

    public function test_send_message_creates_message()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $chat->users()->attach($user->id);

        $this->actingAs($user);

        $message = $this->messageService->sendMessage($chat, 'Hello World');

        $this->assertNotNull($message);
        $this->assertEqual($message->body, 'Hello World');
        $this->assertEqual($message->type, 'text');
        $this->assertEqual($message->user_id, $user->id);
        $this->assertEqual($message->chat_id, $chat->id);
    }

    public function test_delete_for_me_creates_deletion_record()
    {
        $user = User::factory()->create();
        $message = Message::factory()->for(User::factory())->for(Chat::factory())->create();

        $this->actingAs($user);

        $deleted = $this->messageService->deleteForMe($message);

        $this->assertNotNull($deleted);
        $this->assertEqual($deleted->user_id, $user->id);
        $this->assertEqual($deleted->message_id, $message->id);
    }

    public function test_delete_for_everyone_updates_message()
    {
        $user = User::factory()->create();
        $message = Message::factory()->for($user)->for(Chat::factory())->create();

        $this->actingAs($user);

        $updated = $this->messageService->deleteForEveryone($message);

        $this->assertTrue($updated->deleted_for_everyone);
        $this->assertEqual($updated->body, 'This message was deleted');
        $this->assertNull($updated->file_path);
    }

    public function test_cannot_delete_for_everyone_others_message()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $message = Message::factory()->for($otherUser)->for(Chat::factory())->create();

        $this->actingAs($user);

        $this->expectException(\Exception::class);
        $this->messageService->deleteForEveryone($message);
    }
}
