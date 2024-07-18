<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Jobs\ChangeLocale;
use Illuminate\Foundation\Bus\DispatchesJobs;

class LoginSuccess extends ListenerBase
{
	use DispatchesJobs;
    /**
     * Handle the event.
     *
     * @param  Login  $login
     * @return void
     */
    public function handle(Login $login)
    {
        $this->statut->setLoginStatut($login);
        if ($login->user) {
	        $login->user->updateLoginLog();
	        //set language
	        $lang			= $login->user->language;
	        $lang 			= $lang&&in_array($lang, config('app.languages')) ? $lang : config('app.fallback_locale');
// 	        session()->set('locale', $lang);
	         
	        $changeLocale	= new ChangeLocale;
	        $changeLocale->lang = $lang;
	        $this->dispatch($changeLocale);
        }
    }
}
