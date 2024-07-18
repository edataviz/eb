<?php
	$saml2_controller = config('saml2_settings.saml2_controller', 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller');
	$prefix = '{idpName}'.'/';
	$middleWare = null;//config('saml2_settings.routesMiddleware');
	Route::get('/login',['as'=>'saml2_login','uses' => $saml2_controller.'@login']);
	Route::get($prefix.'logout',['as'=>'saml2_logout','uses' => $saml2_controller.'@logout']);
	Route::get($prefix.'metadata',['as'=>'saml2_metadata','uses' => $saml2_controller.'@metadata']);
	Route::post($prefix.'acs',['as'=>'saml2_acs','uses' => $saml2_controller.'@acs']);
	Route::get($prefix.'sls',['as'=>'saml2_sls','uses' => $saml2_controller.'@sls']);

