<?php

namespace App\Providers;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use App\Observers\ChatObserver;
use App\Observers\ContactObserver;
use App\Observers\MessageObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register model observers for logging
        Message::observe(MessageObserver::class);
        Contact::observe(ContactObserver::class);
        Chat::observe(ChatObserver::class);
    }
}
