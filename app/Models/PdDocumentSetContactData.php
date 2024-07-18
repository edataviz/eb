<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdDocumentSetContactData extends EbBussinessModel 
{ 
	protected $table = 'pd_document_set_contact_data'; 
	
	protected $fillable  = ['DOCUMENT_SET_DATA_ID',
							'CONTACT_ID',
							'ORGINAL_ID',
							'NUMBER_COPY'];
	
			
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_column($mdlData, 'ID');
		static::whereIn('ID', $valuesIds)->delete();
	}
	
	public function isNotAvailable($attributes){
		$entry = $this->find($attributes);
		return $entry->count()<=0;
	}
	
	public function checkAndSave(&$values) {
		if(!isset($values['DOCUMENT_SET_DATA_ID'])||!$values['DOCUMENT_SET_DATA_ID']|| empty($values['DOCUMENT_SET_DATA_ID'])) return null;
		if(!isset($values['CONTACT_ID'])||	!$values['CONTACT_ID']			||empty($values['CONTACT_ID'])) return null;
		if(isset($values['ORGINAL_ID'])&&	(empty($values['ORGINAL_ID'])	||$values['ORGINAL_ID']==" ")) 	$values['ORGINAL_ID'] = NULL;
		if(isset($values['NUMBER_COPY'])&&	(empty($values['NUMBER_COPY'])	||$values['NUMBER_COPY']==" "))	$values['NUMBER_COPY'] = NULL;
		return parent::checkAndSave($values);
	}
} 
