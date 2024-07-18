<?php namespace App\Services;

class Statut  {

	/**
	 * Set the login user statut
	 * 
	 * @param  Illuminate\Auth\Events\Login $login
	 * @return void
	 */
	public function setLoginStatut($login)
	{
		$loginStatut	= $login->user->role();
		if(!$loginStatut||count($loginStatut)==0) $loginStatut = ["basics"];
		session()->put('statut', $loginStatut);
 		session()->put('configuration', $login->user->configuration());
	}

	/**
	 * Set the visitor user statut
	 * 
	 * @return void
	 */
	public function setVisitorStatut()
	{
		session()->put('statut', 'visitor');
 		session()->put('configuration', null);
	}

	/**
	 * Set the statut
	 * 
	 * @return void
	 */
	public function setStatut()
	{
		if(!session()->has('statut')) 
		{
			session()->put('statut', auth()->check() ?  auth()->user()->role->slug : 'visitor');
		}
	}

}