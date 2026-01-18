<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Contacts
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <form action="{{ route('contacts.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">

        {{-- The @csrf directive generates a security token sent with each POST request to the application,
            and the server verifies its validity before processing the request. This ensures that
            all POST requests sent to the application come from a trusted and authorized source. --}}
            @csrf

            <x-jet-validation-errors class="mb-4"/>

            <div class="mb-4">
                <x-jet-label class="mb-1">
                    Contact Name
                </x-jet-label>

                <x-jet-input type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="w-full"
                            placeholder="Enter the contact name." />

            </div>

            <div class="mb-4">
                <x-jet-label class="mb-1">
                    Email
                </x-jet-label>

                <x-jet-input type="email"
                name="email"
                value="{{ old('email') }}"
                class="w-full"
                placeholder="Enter the email." />
            </div>

            <div class="flex justify-end">
                <x-jet-button>
                    Create Contact
                </x-jet-button>
            </div>
        </form>
    </div>
</x-app-layout>
