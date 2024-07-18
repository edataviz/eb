<?php

namespace App\Models;

class Dashboard extends EbBussinessModel {
	protected $table = 'DASHBOARD';
	
	protected $fillable = [ 
							'NAME',
							'TYPE',
							'BACKGROUND',
							'CONFIG',
							'USER_NAME',
							'IS_DEFAULT'
					];
	public function afterSaving($postData) {
		if ($this->wasRecentlyCreated) {
			$user = auth()->user();
			$this->USER_NAME = $user->username;
			$this->save();
		}
		
		if ($this->IS_DEFAULT==1) {
			Dashboard::where(["USER_NAME"	=> $this->USER_NAME])
					->where("ID","<>", $this->ID)
					->update(["IS_DEFAULT"	=> 0]);
		}
	}
} 
