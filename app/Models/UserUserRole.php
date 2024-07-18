<?php namespace App\Models;

use App\Models\DynamicModel;

class UserUserRole extends DynamicModel  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_user_role';
	protected $primaryKey = 'ID';
	protected $user_id_col = 'USER_ID';
	protected $role_id_col = 'ROLE_ID';

	/*
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function UserRole() 
	{
		return $this->belongsTo('App\Models\UserRole', $this->role_id_col, $this->primaryKey);
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User',  $this->user_id_col, $this->primaryKey);
	}
	
	public function UserRoleRight()
	{
		return $this->belongsTo ('App\Models\UserRoleRight','ROLE_ID','ROLE_ID');
	}
}
