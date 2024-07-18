<?php

namespace App\Models;

class FeatureStorageModel  extends FeatureTankModel {
	public  static  $enableLevelCalculating = false;
	public  static  $idField = 'STORAGE_ID';
	public  static  $typeName = 'STORAGE';
	public  static  $dateField = 'OCCUR_DATE';
	protected $objectModel = 'Storage';
	protected $excludeColumns = ['STORAGE_ID','OCCUR_DATE'];
	protected $disableUpdateAudit = false;
	
	public static function getObjects() {
		return Storage::where("ID",">",0)->orderBy("NAME")->get();
	}
}
