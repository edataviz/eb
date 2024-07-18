<?php 
namespace App\Models;

class ObjectTypeProperty extends DynamicModel {
	public static function loadBy($sourceData){
		$objectType 	= $sourceData['ObjectDataSource'];
		if (!$objectType)  return null;
		$id 			= $objectType->ID;	
		$code 			= $id&&$id!==0?$objectType->ID:$objectType->CODE;
		if (strpos($code, 'V_') === 0 || strpos($code, 'VV_') === 0){
			$db_schema = ENV('DB_DATABASE');
			if (config('database.default')==='oracle'){
				$tmp 		= \DB::table('user_tab_cols')
								//->where('TABLE_SCHEMA','=',$db_schema)
								->where('TABLE_NAME','=',$code)
								->where('COLUMN_NAME','<>',"ID")
								->whereIn('DATA_TYPE',['NUMBER','FLOAT','DOUBLE'])
								->select(['COLUMN_NAME AS ID','COLUMN_NAME AS CODE', 'COLUMN_NAME AS NAME'])
								->get();
			}
			else{
				$tmp 		= \DB::table('INFORMATION_SCHEMA.COLUMNS')
								->where('TABLE_SCHEMA','=',$db_schema)
								->where('TABLE_NAME','=',$code)
								->where('COLUMN_NAME','<>',"ID")
								->whereIn('DATA_TYPE',['decimal','int','double'])
								->select(['COLUMN_NAME AS ID','COLUMN_NAME AS CODE', 'COLUMN_NAME AS NAME'])
								->get();
			}
		}
		else {
			if (strpos($code, '_') !== false){
				$model		= \Helper::getModelName($code);
			}
			else $model 			= 'App\\Models\\' .trim($code);
			$tableName 		= $model::getTableName ();

            $tmp            = static::buildQuery($model)
                                    ->where('TABLE_NAME','=',$tableName)
                                    ->orderBy('FIELD_ORDER')
                                    ->get(['COLUMN_NAME AS ID','COLUMN_NAME AS CODE', 'LABEL AS NAME','COLUMN_NAME']);
			$tmp 			= $tmp->each(function ($item, $key){
								if($item->NAME == '' || is_null($item->NAME)){
									$item->NAME = $item->CODE;
								}
							});
		}
		return $tmp;
	}
	
     public static function buildQuery($model) {
         $dates 			= $model::getDateFields();
         return GraphCfgFieldProps::where("USE_FDC",1)->where("INPUT_TYPE",2)->whereNull("CONFIG_ID")
             ->whereNotIn("COLUMN_NAME",$dates);
     }
} 
