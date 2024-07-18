<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CodeController;
use App\Models\AuditTrail;
use App\Models\CodeAuditReason;
use App\Models\EnergyUnit;
use App\Models\Equipment;
use App\Models\Flow;
use App\Models\Storage;
use App\Models\Tank;

class AuditController extends CodeController {
    
	
	public function getFirstProperty($dcTable){
		return  null;
	}
	/* public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		$properties = collect([
 				(object)['data' =>	'ACTION',		'title' => 'Action',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>1],
				(object)['data' =>	"WHO",			'title' => 'By',		'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>2],
				(object)['data' =>	"WHEN",			'title' => 'Time',		'width'	=>	0,'INPUT_TYPE'=>4,	'DATA_METHOD'=>5,'FIELD_ORDER'=>3],
				(object)['data' =>	"REASON",		'title' => 'Reason',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>4],
				(object)['data' =>	"OBJECT_DESC",	'title' => 'Object',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>5],
				(object)['data' =>	"RECORD_ID",	'title' => 'Record ID',	'width'	=>	0,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>6],
				(object)['data' =>	"TABLE_NAME",	'title' => 'Table',		'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>7],
				(object)['data' =>	"COLUMN_NAME",	'title' => 'Column',	'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>8],
				(object)['data' =>	"OLD_VALUE",	'title' => 'Old Value',	'width'	=>	0,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>9],
				(object)['data' =>	"NEW_VALUE",	'title' => 'New Value',	'width'	=>	0,'INPUT_TYPE'=>2,	'DATA_METHOD'=>5,'FIELD_ORDER'=>10],
				(object)['data' =>	"AUDIT_NOTE",	'title' => 'MEMO',		'width'	=>	0,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>11],
		]);
		$results 	= ['properties'		=> $properties,
	    				'locked'		=> true,
		];
		return $results;
	} */
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$auditTrail 		= AuditTrail::getTableName();
    	$codeAuditReason 	= CodeAuditReason::getTableName();
    	$date_begin			= $occur_date;
        $date_end           = array_key_exists('date_end', $postData) ? $postData['date_end'] : null;
        $date_end           = $date_end && $date_end != "" ? \Helper::parseDate($date_end) : Carbon::now();
        $tableName 			= $postData['ObjectDataSource'];

        $objectType         = array_key_exists('IntObjectType', $postData) ?$postData['IntObjectType']:0;
        $intObjectType      = \App\Models\IntObjectType::find($objectType);
        if($intObjectType){
            $mdl = \Helper::getModelName($intObjectType->CODE);
            $getNameTbParent = $mdl::getTableName();
            $referenceColumn = isset($mdl::$previewNameColumn)?$mdl::$previewNameColumn:"NAME";
            $dataSet = AuditTrail::join($codeAuditReason, "$auditTrail.REASON", '=', "$codeAuditReason.ID")
                //->join($getNameTbParent, "$auditTrail.RECORD_ID", '=', "$getNameTbParent.ID")
                //->where(["$auditTrail.FACILITY_ID" => $facility_id])
                ->where('TABLE_NAME', '=', $tableName)
                ->whereDate("$auditTrail.OCCUR_DATE", '>=', $date_begin)
                ->whereDate("$auditTrail.OCCUR_DATE", '<=', $date_end)
                ->select(["$auditTrail.*",
                    "$codeAuditReason.NAME AS REASON"])
				->orderBy("$auditTrail.OCCUR_DATE")
				->orderBy('WHEN')
                ->get();
            return ['dataSet'=>$dataSet];
        }
        else
            return[];
    }
}
