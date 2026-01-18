<?php

namespace App\Rules;

use App\Models\Contact;
use Illuminate\Contracts\Validation\Rule;

class InvalidEmail implements Rule
{

    public $email;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($email = null)
    {
        // This attribute is defined to validate that an update is allowed with the email already assigned to the contact
        $this->email = $email;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // We validate if the "email" is already held by another Contact, if it is held "false" is returned, otherwise "true"
        /*
            where() => used to specify the conditions that must be met for a result to be included in the query.
                        In this case, it's searching for a contact that has a user_id property equal to the id of the authenticated user.

            whereHas() => used to specify an additional condition in the query based on the relationship of one model with another.
                            In this case, it's checking if the found contact has an associated user that has a specific email. This function accepts a callback function as an argument, which in turn accepts a query as an argument. The callback function is used to add an additional condition to the original query, in this case checking if the associated user's email matches the specified value.

            count() => used to count how many results meet the conditions specified in the query.
                        If the result of this function is equal to zero, then true is returned, indicating that there is no contact that meets the
                        conditions specified in the query. Otherwise, false is returned, indicating that there is at least one contact that
                        meets the conditions.
        */

        /*
            The SQL Equivalent would be:

            SELECT COUNT(*)
                FROM contacts
                WHERE user_id = [auth()->id()]
                    AND EXISTS (
                            SELECT *
                            FROM users
                            WHERE users.id = contacts.user_id
                            AND  email = [$value]
                            AND  ( email != [$this->email] OR email IS NULL )
                        )
        */
        return Contact::where('user_id', auth()->id()) // In the "Contacts" table it filters by the "user_id" field
                        ->whereHas('user', function($query) use ($value){ // In a subquery it references the "user" table
                            $query->where('email', $value) // It filters by the "email" field of the "user" table
                                ->when($this->email, function($query){ // The condition is added that the query only executes when the "$this->email" parameter is different from NULL
                                    $query->where('email', '!=', $this->email); // It filters by the "email" field in the "user" table and tells it to get the records different from the "$this->email" parameter
                                });
                        })->count() === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */

    // Here will be the validation message in case the "passes" method condition is not met
    public function message()
    {
        return 'The entered email is already registered.';
    }
}
