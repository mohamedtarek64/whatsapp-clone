<?php

namespace App\Observers;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class ContactObserver
{
    /**
     * Handle the Contact "created" event.
     */
    public function created(Contact $contact): void
    {
        Log::channel('audit')->info('Contact added', [
            'contact_id' => $contact->id,
            'user_id' => $contact->user_id,
            'contact_user_id' => $contact->contact_id,
            'name' => $contact->name,
        ]);
    }

    /**
     * Handle the Contact "updated" event.
     */
    public function updated(Contact $contact): void
    {
        Log::channel('audit')->info('Contact updated', [
            'contact_id' => $contact->id,
            'changes' => $contact->getChanges(),
            'user_id' => $contact->user_id,
        ]);
    }

    /**
     * Handle the Contact "deleted" event.
     */
    public function deleted(Contact $contact): void
    {
        Log::channel('audit')->info('Contact deleted', [
            'contact_id' => $contact->id,
            'user_id' => $contact->user_id,
        ]);
    }
}
