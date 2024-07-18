<?php namespace App\Models;

class UserRoleReport extends EbBussinessModel  {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_role_report';



    public function user_role()
    {
        return $this->belongsTo('App\Models\UserRole', 'ROLE_ID', 'ID');
    }
}