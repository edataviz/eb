<?php

namespace App\Models;
use App\Models\EbBussinessModel;
use App\Trail\ExtractTagMappingConfig;

class QltyData extends EbBussinessModel
{
	use ExtractTagMappingConfig;
	
	public  static  $relateColumns = ['id'	=> "SRC_ID",'type'	=> "SRC_TYPE"];
	
	public  static  $keyFields = [	'SRC_TYPE'		=> "CodeQltySrcType",
									'SAMPLE_TYPE'	=> "CodeSampleType",
									'SRC_ID'		=> "ExtensionQltySrcObject",];
	
	protected $table = 'QLTY_DATA';
	protected $dates = [/* 'SAMPLE_DATE','TEST_DATE', */'EFFECTIVE_DATE'];
	protected $fillable  = ['CODE',
							'LAB_NAME',
							'NAME',
							'SAMPLE_DATE',
							'TEST_DATE',
							'SAMPLE_TAKER_NAME',
							'LAB_TECHNICIAN_NAME',
							'SRC_TYPE',
							'SRC_ID',
							'SAMPLE_TYPE',
							'EFFECTIVE_DATE',
							'QLTY_VALUE1',
							'QLTY_VALUE2',
							'QLTY_VALUE3',
							'QLTY_VALUE4',
							'QLTY_VALUE5',
							'ENGY_RATE'];
	
	public  static  $idField = 'ID';
	public  static  $typeName = 'QLTY';
	public  static  $dateField = 'EFFECTIVE_DATE';
	
	public static function getSourceModel($columnName){
		return "CodeQltySrcType";
	}
	
	public function CodeQltySrcType(){
		return $this->belongsTo('App\Models\CodeQltySrcType', 'SRC_TYPE', 'ID');
	}
	
	public static function getQualityRow($object_id,$object_type_code,$occur_date){
		return static :: whereHas('CodeQltySrcType',function ($query) use ($object_type_code) {
													$query->where("CODE",$object_type_code );
											})
					->where('SRC_ID',$object_id)
					->whereDate('EFFECTIVE_DATE','<=',$occur_date)
					->orderBy('EFFECTIVE_DATE','desc')
					->first();
	}
	
	public static function getQualityOil($object_id,$object_type_code,$occur_date){
		$row = static ::getQualityRow($object_id,$object_type_code,$occur_date);
		
		if($row){
			$dataID	=$row->ID;
			$querys = [
			'OIL_F' =>QltyDataDetail::whereHas('QltyProductElementType' ,
												function ($query) {
													$query->where("CODE",'OIL_SHRK_F' );
												})
									->where('QLTY_DATA_ID',$dataID)
									->selectRaw("max(VALUE) as VALUE"),
			'GAS_R' =>QltyDataDetail::whereHas('QltyProductElementType' , 
												function ($query) {
													$query->where("CODE",'FLSH_GAS_R' );
												})
									->where('QLTY_DATA_ID',$dataID)->select(\DB::raw("max(VALUE) as VALUE")),
			];
			
			$qdltDatas	= $row;
			foreach($querys as $key => $query ){
				$mValue	= $query->first();
				if ($mValue) 
					$qdltDatas->{$key} = $mValue->VALUE;
				else
					$qdltDatas->{$key} = null;
			}
			return $qdltDatas;
		}
		return null;
	}
	
	public static function getEntries(){
	    $data=CodeQltySrcType::select('ID','NAME')->get();
        return $data;
    }

	public function delete(){
		QltyDataDetail::where("QLTY_DATA_ID", $this->primaryKey)->delete();
		return parent::delete();
	}
}

