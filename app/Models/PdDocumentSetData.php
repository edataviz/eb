<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdDocumentSetData extends EbBussinessModel 
{ 
	protected $table = 'PD_DOCUMENT_SET_DATA'; 
	
	protected $fillable  = ['VOYAGE_ID', 
							'CARGO_ID', 
							'PARCEL_NO', 
							'LIFTING_ACCOUNT', 
							'DOCUMENT_ID', 
							'PARENT_ID'];
	
	public function PdDocumentSetContactData()
	{
		$pdDocumentSetContactData = \App\Models\PdDocumentSetContactData::getTableName();
		return $this->hasMany('App\Models\PdDocumentSetContactData', "DOCUMENT_SET_DATA_ID", "ID")
					->select(["$pdDocumentSetContactData.*",
							"$pdDocumentSetContactData.ID as DT_RowId"
					]);;
	}
	
	public function isNotAvailable($attributes){
		$entry = $this->find($attributes);
		return $entry->count()<=0;
	}
	
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_values($mdlData);
		\App\Models\PdDocumentSetContactData::whereIn('DOCUMENT_SET_DATA_ID', $valuesIds)->delete();		
		parent::deleteWithConfig($mdlData);
	}
	
	public function updateDependRecords($occur_date,$values,$postData) {
		if ($this->wasRecentlyCreated) {
			$cValues 		= [];
			foreach ( $values as $key => $value ) {
				if(0 === strpos($key, 'ORGINAL_ID') || 0 === strpos($key, 'NUMBER_COPY') || 0 === strpos($key, 'CONTACT_ID')){
					$parts = explode("-", $key);
					if(count($parts)>=2){
						$dindex 			= $parts[1];
						$column 			= $parts[0];
						if (!isset($cValues[$dindex])) {
							$cValues[$dindex] = ["DOCUMENT_SET_DATA_ID"	=> $this->ID];
						}
						
						if(($value==" ")|| empty($value)){
	 						$cValues[$dindex][$column]	= NULL;
						}
						else $cValues[$dindex][$column]	= $value;
					}
				}
			}
			
			foreach ( $cValues as $dindex => $value ) {
				if(!isset($value['CONTACT_ID'])||$value['CONTACT_ID']<=0) unset($cValues[$dindex]);
				if(!isset($value['DOCUMENT_SET_DATA_ID'])||$value['DOCUMENT_SET_DATA_ID']<=0) unset($cValues[$dindex]);
			}
			
			if(count($cValues)) {
				$result = \App\Models\PdDocumentSetContactData::insert($cValues);
				return $this;
			}
		}
		else{
		}
		return null;
	}
} 
