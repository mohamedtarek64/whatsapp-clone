<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        if ($users->count() < 2) {
            $this->command->info('Creating sample users for chats...');
            $users = User::factory(5)->create();
        }

        // Create direct chats between users
        foreach ($users as $user) {
            $contacts = $users->where('id', '!=', $user->id)->take(2);

            foreach ($contacts as $contact) {
                // Check if chat already exists
                $existingChat = Chat::whereHas('users', function ($q) use ($user, $contact) {
                    $q->where('user_id', $user->id);
                })->whereHas('users', function ($q) use ($contact) {
                    $q->where('user_id', $contact->id);
                })->where('is_group', false)->first();

                if (!$existingChat) {
                    $chat = Chat::create(['is_group' => false]);
                    $chat->users()->attach([$user->id, $contact->id]);

                    // Add sample messages
                    Message::factory(5)->for($user)->for($chat)->create();
                    Message::factory(5)->for($contact)->for($chat)->create();
                }
            }
        }

        // Create group chats
        $groups = Chat::factory(3)->create(['is_group' => true]);

        foreach ($groups as $group) {
            $groupUsers = $users->random(min(3, $users->count()));
            $group->users()->attach($groupUsers->pluck('id'));

            // Add messages to groups
            foreach ($groupUsers as $user) {
                Message::factory(3)->for($user)->for($group)->create();
            }
        }

        $this->command->info('Chats and messages seeded successfully!');
    }
}
