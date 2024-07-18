<?php
namespace App\Models;

class EnergyUnitStatusAndEuTestDataScssv extends FeatureEuTestModel
{
	public function __construct(array $attributes = []) {
		parent::__construct();
	}
    public static function getKeyColumns(&$newData,$occur_date,$postData){
        $attributes = [];
        $isInsert = true;
        if ( !array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'ID', $newData ) &&!array_key_exists ( 'auto', $newData ) ) {
            $isInsert = false;
            $attributes['ID'] = $newData['ID'];
// 			return $attributes;
        } else if(static::$dateField) {
            $attributes[static::$dateField] = array_key_exists ( static::$dateField, $newData )?$newData[static::$dateField]:$occur_date;
        }

        if( array_key_exists ( 'EnergyUnit', $postData )&&$postData['EnergyUnit']>0&&(!array_key_exists ( 'EU_ID', $newData )||$newData['EU_ID']=='')){
            if ((array_key_exists ( 'isAdding', $newData ) && array_key_exists ( 'EnergyUnit', $postData ))||
                !array_key_exists ( 'EU_ID', $newData )) {
                $newData['EU_ID'] = $postData['EnergyUnit'];
            }
        } else if (array_key_exists ( 'isAdding', $newData ) && (!array_key_exists ( 'EU_ID', $newData )||$newData['EU_ID']=='')) {
            throw new \Exception("Please input Energy Unit dropdown !");
        }
        if($isInsert) $attributes['EU_ID'] = $newData['EU_ID'];
        return $attributes;
    }
}