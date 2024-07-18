<?php

namespace App\Models;

use App\Models\DynamicModel;

class UserRight extends DynamicModel
{
	protected $table = 'user_right';
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function UserRoleRight()
	{
		return $this->hasMany('App\Models\UserRoleRight','RIGHT_ID', 'ID');
	}
}
