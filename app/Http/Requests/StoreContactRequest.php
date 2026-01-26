<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'contact_id' => 'required|integer|exists:users,id|different:id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Contact name is required',
            'name.max' => 'Contact name cannot exceed 255 characters',
            'contact_id.required' => 'Contact ID is required',
            'contact_id.exists' => 'Selected contact does not exist',
            'contact_id.different' => 'You cannot add yourself as a contact',
        ];
    }
}
