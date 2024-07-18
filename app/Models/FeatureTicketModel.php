<?php

namespace App\Models;
use App\Models\EbBussinessModel;

class FeatureTicketModel extends EbBussinessModel
{
	public  static  $idField = 'TANK_ID';
	public  static  $dateField = 'OCCUR_DATE';
	protected $disableUpdateAudit = false;
	protected $autoFillableColumns 	= true;
	
	public static function getKeyColumns(&$newData,$occur_date,$postData){
		$attributes	= [];
		if ( array_key_exists ( 'ID', $newData )) {
			$attributes	["ID"]	= $newData['ID'];
		}
		if ( array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'Tank', $postData )) {
			$newData['TANK_ID'] = $postData['Tank'];
		}
		return $attributes;
	}
	
	public static function getObjects() {
		return Tank::where("ID",">",0)->orderBy("NAME")->get();
	}
	
	public static function deleteWithConfig($mdlData) {
		if($mdlData&&count($mdlData)>0){
			foreach ($mdlData as $entry){
				if ( array_key_exists ( 'OCCUR_DATE', $entry ) 
						&& array_key_exists ( 'TANK_ID', $entry ) 
						&& array_key_exists ( 'TICKET_NO', $entry )
						&& $entry['TICKET_NO']!=null
						&& $entry['TICKET_NO']!="") {
					static::whereDate('OCCUR_DATE','=' ,$entry['OCCUR_DATE'])
							->where('TANK_ID', $entry['TANK_ID'])
							->where('TICKET_NO', $entry['TICKET_NO'])
							->delete();
				}
				else if (array_key_exists ( 'ID', $entry ) && $entry['ID'] > 0){
					static::where('ID', $entry['ID'])->delete();
				}
			}
		}
	}
	
	/* public static function findManyWithConfig($updatedIds) {
		$table	= static::getTableName();
		$tank 	= Tank::getTableName();
		return parent::join("Tank","$tank.ID","=","$table.TARGET_TANK")->whereIn ("$table.ID", $updatedIds )->select("$table.*","$tank.NAME as TARGET_TANK")->get();
	} */
}
