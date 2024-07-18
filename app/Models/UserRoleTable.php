<?php namespace App\Models;

class UserRoleTable extends EbBussinessModel  {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_role_table';


    public static function getKeyColumns(&$newData,$occur_date,$postData){
        $attributes	= [ 'ROLE_ID'       =>  $newData['ROLE_ID'],
                        'TABLE_NAME'      =>  $newData['TABLE_NAME']
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
	public function UserTables()
	{
		return $this->belongsTo('App\Models\GraphDataSource','TABLE_NAME', 'SOURCE_NAME');
	}

    public static function deleteWithConfig($mdlData) {
        if($mdlData&&count($mdlData)>0){
            foreach ($mdlData as $entry){
                if ( array_key_exists ( 'ROLE_ID', $entry )
                    && array_key_exists ( 'TABLE_NAME', $entry )
                    && $entry['ROLE_ID']!=null
                    && $entry['TABLE_NAME']!="") {
                    static::where('ROLE_ID', $entry['ROLE_ID'])
                        ->where('TABLE_NAME', $entry['TABLE_NAME'])
                        ->delete();
                }
                else if (array_key_exists ( 'ID', $entry ) && $entry['ID'] > 0){
                    static::where('ID', $entry['ID'])->delete();
                }
            }
        }
    }
	
}
