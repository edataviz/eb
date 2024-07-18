<?php namespace App\Models;

class UserRoleRight extends EbBussinessModel  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_role_right';


    public static function getKeyColumns(&$newData,$occur_date,$postData){
        $attributes	= [ 'ROLE_ID'       =>  $newData['ROLE_ID'],
                        'RIGHT_ID'      =>  $newData['RIGHT_ID']
        ];
        return $attributes;
    }

	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function user_role() 
	{
		return $this->belongsTo('App\Models\UserRole', 'ROLE_ID', 'ID');
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function UserRight()
	{
		return $this->belongsTo('App\Models\UserRight','RIGHT_ID', 'ID');
	}

    public static function deleteWithConfig($mdlData) {
        if($mdlData&&count($mdlData)>0){
            foreach ($mdlData as $entry){
                if ( array_key_exists ( 'ROLE_ID', $entry )
                    && array_key_exists ( 'RIGHT_ID', $entry )
                    && $entry['ROLE_ID']!=null
                    && $entry['RIGHT_ID']!="") {
                    static::where('ROLE_ID', $entry['ROLE_ID'])
                        ->where('RIGHT_ID', $entry['RIGHT_ID'])
                        ->delete();
                }
                else if (array_key_exists ( 'ID', $entry ) && $entry['ID'] > 0){
                    static::where('ID', $entry['ID'])->delete();
                }
            }
        }
    }
	
}
