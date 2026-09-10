<?php

namespace App\Listeners;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;

class UserLoginAt
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Login $event)
    {
        // dd($event->user);
        $event->user->update([
            'last_login_at' => Carbon::now(),
            'last_login_ip_address' => request()->getClientIp(),
        ]);
    }
}
