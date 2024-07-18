<?php

namespace App\Models;

use App\Models\EbBussinessModel;

class Safety extends EbBussinessModel
{
    protected $table = 'SAFETY';
    public $timestamps = false;
    public $primaryKey  = 'ID';
    
    protected $fillable  = ['FACILITY_ID', 'CATEGORY_ID', 'COUNT', 'COMMENTS', 'CREATED_DATE', 'SEVERITY_ID'];
    
    public static function getKeyColumns(&$newData,$occur_date,$postData)
    {
    	$newData['CATEGORY_ID'] = $newData['X_CATEGORY_ID'];
    	$newData['FACILITY_ID'] = $postData['Facility'];
    	if (!array_key_exists('CREATED_DATE', $newData)||!$newData['CREATED_DATE']) {
    		$newData['CREATED_DATE'] = $occur_date;
    	}
    	return [
    			'CATEGORY_ID' => $newData['X_CATEGORY_ID'],
    			'FACILITY_ID' => $postData['Facility'],
    			'CREATED_DATE' => $occur_date,
    	];
    }
    
}
