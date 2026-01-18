<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Rules\InvalidEmail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // All records from the "Contact" table are retrieved, paginated, and the array is sent to the view.
        $contacts = auth()->user()->contacts()->paginate();
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // Stores a new contact in the database.
    public function store(Request $request)
    {

        // We validate the data sent via HTTP from the form
        $request->validate([
            'name' => 'required', // Mandatory
            'email' => [
                'required',       // Mandatory
                'email',          // Must have email format
                'exists:users,email',   // Must exist in the users table of the database
                Rule::notIn([auth()->user()->email]), // Email cannot be the same as the authenticated user's email
                new InvalidEmail  // Custom Rule in the file: "app\Rules\InvalidEmail.php". This rule validates that the entered email does not already belong to a Contact
            ]
        ], [
            'email.exists' => 'This email is not registered in our system.',
            'email.not_in' => 'You cannot add yourself as a contact.'
        ]);

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

        // flash.banner => It's a reserved Jetstream variable to show the message in a component
        session()->flash('flash.banner', 'The contact has been created successfully');

        // flash.bannerStyle => It's a reserved Jetstream variable to style the component that will show the message
        // There are two style options "success" and "danger"
        session()->flash('flash.bannerStyle', 'success');

        // Redirects to the contact editing section
        return redirect()->route('contacts.edit', $contact);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $contact => Receives an ID as a parameter and automatically relates it to the Contact model, thus obtaining the entire record related to this ID in the Contact table
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        //
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $contact => Receives an ID as a parameter and automatically relates it to the Contact model, thus obtaining the entire record related to this ID in the Contact table
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
                'exists:users,email',   // Must exist in the users table of the database
                Rule::notIn([auth()->user()->email]), // Email cannot be the same as the authenticated user's email
                new InvalidEmail($contact->user->email)  // Custom Rule in the file: "app\Rules\InvalidEmail.php". This rule validates that the entered email does not already belong to a Contact
            ]
        ], [
            'email.exists' => 'This email is not registered in our system.',
            'email.not_in' => 'You cannot add yourself as a contact.'
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

        // flash.banner => It's a reserved Jetstream variable to show the message in a component
        session()->flash('flash.banner', 'The contact has been updated successfully');

        // flash.bannerStyle => It's a reserved Jetstream variable to style the component that will show the message
        // There are two style options "success" and "danger"
        session()->flash('flash.bannerStyle', 'success');

        // Redirects to the contact editing section
        return redirect()->route('contacts.edit', $contact);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $contact => Receives an ID as a parameter and automatically relates it to the Contact model, thus obtaining the entire record related to this ID in the Contact table
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

        // flash.banner => It's a reserved Jetstream variable to show the message in a component
        session()->flash('flash.banner', 'The contact has been deleted successfully');

        // flash.bannerStyle => It's a reserved Jetstream variable to style the component that will show the message
        // There are two style options "success" and "danger"
        session()->flash('flash.bannerStyle', 'success');

        // Redirects to the contact editing section
        return redirect()->route('contacts.index',);
    }
}
