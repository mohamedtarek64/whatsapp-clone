<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use App\Rules\InvalidEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = auth()->user()->contacts()->paginate();
        return response()->json($contacts);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // We validate the data sent via HTTP from the form
        $request->validate([
            'name' => 'required', // Mandatory
            'email' => [
                'required',       // Mandatory
                'email',          // Must have email format
                'exists:users',   // Must exist in the users table of the database
                Rule::notIn([auth()->user()->email]), // Email cannot be the same as the authenticated user's email
                new InvalidEmail  // Custom Rule in the file: "app\Rules\InvalidEmail.php". This rule validates that the entered email does not already belong to a Contact
            ]
        ]);
        /* =================================================================================================================================================================== */
        /* LOGIC THAT ADDS A USER AS A CONTACT */

        // The user is searched for in the DB with the email sent from the form
        $user = User::where('email', $request->email)->first();

        // Makes an INSERT into the "Contact" table with the data obtained from the "User" table filtered by Email

        /*
            The SQL Equivalent would be:

            INSERT INTO contacts (name, user_id, contact_id)
            VALUES (<$request->name>, <auth()->id()>, <$user->id>)
        */

        $contact = Contact::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
            'contact_id' => $user->id
        ]);

        /* =================================================================================================================================================================== */
        /* LOGIC THAT CREATES THE CHAT BETWEEN THE AUTHENTICATED USER AND THE ADDED CONTACT */

        // This query gets the individual chat between the two users, if it exists...
        $chatIndividual = DB::table('chat_user as u')
        ->join('chat_user as c', 'u.chat_id', '=', 'c.chat_id')
        ->select('u.*')
        ->where('u.user_id', auth()->id())
        ->where('c.user_id', $user->id)
        ->where(function ($query) {
            // We add a subquery to count the users in the chat
            $query->whereExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('chat_user as uc')
                    ->whereRaw('uc.chat_id = u.chat_id')
                    ->havingRaw('COUNT(uc.user_id) = 2'); // Verify that there are exactly 2 users in the chat
            });
        })
        ->get();

        // We validate if an individual chat already exists between the users
            // If an individual chat between the users does not exist, one is created.

            // SQL Equivalent:
            // INSERT INTO chats (id, created_at, updated_at) VALUES (:id, :created_at, :updated_at)
            $newChat = Chat::create();

            /*
                The attach method in Laravel is used to add records to a pivot table of a many-to- many relationship.
                In this case, $this->chat->users() is an instance of the Laravel query builder for the users relationship on the Chat model.
                The attach method takes an array of user IDs and adds a row for each of them in the pivot table, establishing the relationship
                between the chat and the specified user.

                For example, if $this->chat represents a chat with ID 10 and [auth()->user()->id, $this->contactChat->contact_id] is an array of two user IDs,
                attach would add the following rows to the pivot table:

                    user_id | chat_id
                    --------+--------
                    1     |   10
                    2     |   10

                This would establish a relationship between the chat with ID 10 and users with ID 1 and 2.
            */

            // SQL Equivalent:
            // INSERT INTO chat_user (user_id, chat_id) VALUES (:user_id, :chat_id)
            $newChat->users()->attach([auth()->user()->id, $contact->contact_id]);
        }



        /* =================================================================================================================================================================== */
        /* RESPONSE */

        return response()->json([
            'status' => true,
            'message' => 'Contact added successfully',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        return response()->json([
            'status' => true,
            'data' => $contact
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contact $contact)
    {
        // We validate the data sent via HTTP from the form
        $request->validate([
            'name' => 'required', // Mandatory
            'email' => [
                'required',       // Mandatory
                'email',          // Must have email format
                'exists:users',   // Must exist in the users table of the database
                Rule::notIn([auth()->user()->email]), // Email cannot be the same as the authenticated user's email
                new InvalidEmail($contact->user->email)  // Custom Rule in the file: "app\Rules\InvalidEmail.php". This rule validates that the entered email does not already belong to a Contact
            ]
        ]);

        // The user is searched for in the DB with the email sent from the form
        $user = User::where('email', $request->email)->first();

        // Makes an UPDATE in the "Contacts" table filtered by Contact ID
        /*
            The SQL Equivalent would be:

            UPDATE contacts
            SET name = [$request->name],
                contact_id = [$user->id]
            WHERE id = [$contact->id]
        */
        $contact->update([
            'name' => $request->name,
            'contact_id' => $user->id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Contact updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        // Makes a DELETE from the "Contacts" table filtered by Contact ID

        /*
            The SQL Equivalent would be:

            DELETE FROM contacts
            WHERE id = $contact->id
        */
        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Contact deleted successfully',
        ], 200);
    }
}
