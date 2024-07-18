<?php

namespace App\Models;


class LogUser extends EbBussinessModel
{
	protected $table = 'LOG_USER';
	protected $autoFillableColumns = false;
	
	protected $fillable  = ['USERNAME', 
							'LOGIN_TIME', 
							'LOGOUT_TIME', 
							'SESSION_ID', 
							'IP'];
	
}
