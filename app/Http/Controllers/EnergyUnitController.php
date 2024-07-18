<?php

namespace App\Http\Controllers;

class EnergyUnitController extends EBController {
	
	
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	public function index() {
		return view ( 'front.eu' );
	}
}
