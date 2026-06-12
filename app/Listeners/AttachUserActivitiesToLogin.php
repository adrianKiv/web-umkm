<?php

namespace App\Listeners;

use App\Models\UserActivity;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class AttachUserActivitiesToLogin
{
    public function handle(Login $event): void
    {
        $sessionId = Session::getId();
        if (!$sessionId) {
            return;
        }

        UserActivity::whereNull('id_user')
            ->where('id_session', $sessionId)
            ->update([
                'id_user' => $event->user->getAuthIdentifier(),
            ]);
    }
}
