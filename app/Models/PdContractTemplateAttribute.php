<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdContractTemplateAttribute extends EbBussinessModel 
{ 
	protected $table = 'PD_CONTRACT_TEMPLATE_ATTRIBUTE';
	
	protected $fillable  = ['CONTRACT_TEMPLATE', 
							'ATTRIBUTE', 
							'ATTRIBUTE_VALUE_FORMULA', 
							'ATTRIBUTE_UOM', 
							'ACTIVE', 
							'ORDER'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData) {
		return ['ATTRIBUTE'	=> $newData['ATTRIBUTE'],
				'CONTRACT_TEMPLATE'	=> $newData['CONTRACT_TEMPLATE']];
	}
	
	public static function findManyWithConfig($updatedIds)
	{
		$pdContractTemplateAttribute	= PdContractTemplateAttribute::getTableName();
		$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
		 
		$dataSet 						= PdContractTemplateAttribute
											::join($pdCodeContractAttribute,
													"$pdContractTemplateAttribute.ATTRIBUTE",
													'=',
													"$pdCodeContractAttribute.ID")
													->whereIn("$pdContractTemplateAttribute.ID",$updatedIds)
													->where("$pdContractTemplateAttribute.ACTIVE",'=',1)
													->select(
															"$pdContractTemplateAttribute.ID as DT_RowId",
															"$pdContractTemplateAttribute.ID",
															"$pdContractTemplateAttribute.ID as $pdCodeContractAttribute",
															"$pdContractTemplateAttribute.CONTRACT_TEMPLATE",
															"$pdContractTemplateAttribute.ATTRIBUTE",
															"$pdCodeContractAttribute.NAME",
															"$pdCodeContractAttribute.CODE"
															)
															->get();
		return $dataSet;
	}
	
/* 	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_column($mdlData, 'ID');;
		$templateId = $mdlData[0]['CONTRACT_TEMPLATE'];
		
		static::where('CONTRACT_TEMPLATE','=', $templateId)->whereIn('ATTRIBUTE', $valuesIds)->delete();
	} */
} 
