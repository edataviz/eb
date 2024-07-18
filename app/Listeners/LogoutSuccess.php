<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class LogoutSuccess extends ListenerBase
{
    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $this->statut->setVisitorStatut();
        if ($event->user) {
	        $event->user->updateLogoutLog();
	        $event->user->saveWorkspace(null,null);
        }
        
    }
}
