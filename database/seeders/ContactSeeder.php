<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
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
            $this->command->info('Creating sample users for contacts...');
            $users = User::factory(5)->create();
        }

        foreach ($users as $user) {
            $contacts = $users->where('id', '!=', $user->id)->random(min(3, $users->count() - 1));

            foreach ($contacts as $contact) {
                Contact::firstOrCreate([
                    'user_id' => $user->id,
                    'contact_id' => $contact->id,
                ], [
                    'name' => $contact->name,
                ]);
            }
        }

        $this->command->info('Contacts seeded successfully!');
    }
}
