<?php

namespace App\Models;

class Comments extends EbBussinessModel
{
    protected $table 						= 'COMMENTS';
    public $timestamps 						= false;
    public static $localType 				= 'COMMENT_TYPE';
    public static $codeTableType 			= 'CodeCommentType';
//     public  static  $relationStatusField 	= 'DEFER_GROUP_TYPE';
    public  static  $dateField 				= 'CREATED_DATE';
    public  static  $previewNameColumn		= 'ID';

    protected $disableUpdateAudit = false;

    public function __construct(array $attributes = []) {
    	parent::__construct();
    }
    
    public static function getKeyColumns(&$newData,$occur_date,$postData)
    {
    	if (!array_key_exists('COMMENT_TYPE', $newData)||!$newData['COMMENT_TYPE']) {
    		$newData[static::$localType] = $postData[static::$codeTableType];
    	}
    	if (!array_key_exists('FACILITY_ID', $newData)||!$newData['FACILITY_ID']) {
    		$newData['FACILITY_ID'] = $postData['Facility'];
    	}
    	
    	if (array_key_exists('COMMENTS', $newData)&&!$newData['COMMENTS']) {
    		$newData['COMMENTS'] = addslashes($newData['COMMENTS']);
    	}
    	/* 
    	if (array_key_exists('CREATED_DATE', $newData)) {
    		$newData['CREATED_DATE'] = Carbon\Carbon::parse($newData['CREATED_DATE']);
    	} */
    	return [
    			'ID' => $newData['ID'],
    	];
    }

    public static function getEntries($facility_id=null,$product_type = 0){
        $entries = CodeCommentType::where('ACTIVE','=',1)->select('ID','NAME')->orderBy('NAME')->get();
        return $entries;
    }
}
