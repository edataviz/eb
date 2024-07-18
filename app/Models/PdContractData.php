<?php 
namespace App\Models; 
use App\Models\EbBussinessModel; 

 class PdContractData extends EbBussinessModel 
{ 
	protected $table = 'PD_CONTRACT_DATA';
	protected $dates = ['ATTRIBUTE_DATE'];
	protected $fillable  = ['ATTRIBUTE_ID', 
							'CONTRACT_ID', 
							'ATTRIBUTE_DATE', 
							'ATTRIBUTE_TEXT', 
							'ATTRIBUTE_VALUE', 
							'ATTRIBUTE_UOM', 
							'ATTRIBUTE_COMMENT'];
	
	public static function getKeyColumns(&$newData,$occur_date,$postData) {
		$newData['ATTRIBUTE_ID']	= $newData['ATTRIBUTE_ID_INDEX'];
		$newData['CONTRACT_ID']		= $newData['CONTRACT_ID_INDEX'];
		
		return ['ATTRIBUTE_ID'	=> $newData['ATTRIBUTE_ID'],
				'CONTRACT_ID'	=> $newData['CONTRACT_ID']];
	}
	
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_column($mdlData, 'ID');
		$contractId = $mdlData[0]['CONTRACT_ID'];
		static::where('CONTRACT_ID','=',$contractId)
			->whereIn('ATTRIBUTE_ID', $valuesIds)->delete();
	}
	
	public static function findManyWithConfig($updatedIds)
	{
		$pdContractData 				= static ::getTableName();
		$pdCodeContractAttribute		= PdCodeContractAttribute::getTableName();
		$pdContractQtyFormula			= PdContractQtyFormula::getTableName();
		
		$result = PdContractData::join($pdCodeContractAttribute,
				"$pdContractData.ATTRIBUTE_ID",
				'=',
				"$pdCodeContractAttribute.ID")
				->leftJoin($pdContractQtyFormula,
						"$pdCodeContractAttribute.FORMULA_ID",
						'=',
						"$pdContractQtyFormula.ID")
				->whereIn("$pdContractData.ID",$updatedIds)
				->select(
						"$pdContractData.*",
						"$pdContractData.CONTRACT_ID as CONTRACT_ID_INDEX",
						"$pdContractData.ATTRIBUTE_ID as ATTRIBUTE_ID_INDEX",
						"$pdCodeContractAttribute.ID as DT_RowId",
						"$pdCodeContractAttribute.ID as $pdContractData",
						"$pdCodeContractAttribute.NAME as CONTRACT_ID",
						"$pdCodeContractAttribute.CODE as ATTRIBUTE_ID",
						"$pdCodeContractAttribute.ID as ID",
				    	"$pdContractQtyFormula.NAME as FORMULA"
						)
				->get();
		
		return $result;
	}
} 
