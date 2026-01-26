<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\User;
use App\Rules\InvalidEmail;
use App\Services\ChatService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactApiController extends Controller
{
    use ApiResponse;

    public function __construct(private ChatService $chatService)
    {
    }

    /**
     * Display all contacts of authenticated user
     */
    public function index()
    {
        $contacts = auth()->user()->contacts()
            ->with('contactUser')
            ->paginate(20);

        return $this->paginated(
            $contacts,
            'Contacts retrieved successfully'
        );
    }

    /**
     * Store a newly created contact
     */
    public function store(StoreContactRequest $request)
    {
        try {
            $user = User::findOrFail($request->validated()['contact_id']);

            $contact = Contact::create([
                'name' => $request->validated()['name'],
                'user_id' => auth()->id(),
                'contact_id' => $user->id
            ]);

            // Create or get direct chat
            $this->chatService->getOrCreateDirectChat(auth()->id(), $user->id);

            return $this->success(
                new ContactResource($contact),
                'Contact added successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->error(
                'Failed to add contact',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Display the specified contact
     */
    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);

        return $this->success(
            new ContactResource($contact),
            'Contact retrieved successfully'
        );
    }

    /**
     * Update the specified contact
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        try {
            $contact->update($request->validated());

            return $this->success(
                new ContactResource($contact),
                'Contact updated successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Failed to update contact',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Remove the specified contact
     */
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);

        try {
            $contact->delete();

            return $this->success(
                null,
                'Contact deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Failed to delete contact',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}