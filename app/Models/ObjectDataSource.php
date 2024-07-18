<?php
namespace App\Models;
use App\Trail\ObjectNameLoad;

class ObjectDataSource extends DynamicModel {
    use ObjectNameLoad;

    protected $table = 'GRAPH_DATA_SOURCE';

    protected $primaryKey = 'ID2';

    public static function loadBy($sourceData){
        $result				= null;
        if ($sourceData!=null&&is_array($sourceData)) {
            $objectType 	= $sourceData['IntObjectType'];
            $code 			= $objectType->CODE;
            if($code=='PD_VOYAGE'){
                return \App\Models\IntMapTable::where('OBJECT_TYPE', $objectType->ID)->select(\DB::raw('TABLE_NAME as ID'), \DB::raw('TABLE_NAME as CODE'), \DB::raw('TABLE_NAME as NAME'))->get();
            }
            $onlyData 	    = array_key_exists('onlyData',$sourceData)?$sourceData['onlyData']:null;
			$user_id 		= \Auth::id();
			$query          = ObjectDataSource::where("SOURCE_TYPE",$code)
			->whereNotExists(function($q) use ($user_id){
				$q->select(\DB::raw(1))
					  ->from('user_user_role')
					  ->join('user_role_table','user_role_table.role_id','=','user_user_role.role_id')
					  ->where('user_user_role.user_id', '=', $user_id)
					  ->where('user_role_table.TABLE_NAME', '=', \DB::raw('graph_data_source.SOURCE_NAME'))
					  ->where('user_role_table.ACCESS', '=', '0');
			})
			->select("SOURCE_NAME as ID","SOURCE_NAME as NAME","SOURCE_ALIAS as ALIAS");
            if ($onlyData&&$code!="DEFERMENT"&&$code!="COMMENTS"&&$code!="ENVIRONMENTAL"&&$code!="LOGISTIC")
                $query->where(function ($q1){
                    $q1->where("SOURCE_NAME",'like',"%_DATA_%")
                        ->orWhere("SOURCE_NAME",'like',"%_data_%");
                });
			//$sss = str_replace('?', "'?'", $query->toSql());
			//$sss = vsprintf(str_replace('?', '%s', $sss), $query->getBindings());
			//\Log::info($sss);
            $collection		= $query->get();
            $result 		= collect();
            $collection->each(function ($item, $key) use(&$result){
                $instance 	= new ObjectDataSource();
                $instance->CODE = $item->ID;
                $instance->ID = $item->ID;
                //$instance->NAME = $item->NAME;
                $instance->NAME = ($item->ALIAS == null) ? $item->NAME : $item->ALIAS;
                $result->push($instance);
            });
        }
        return $result;
    }

    public static function find($id){
        $instance = new ObjectDataSource();
        $instance->CODE = $id;
        $instance->exists = false;
        return $instance;
    }

    public function getSecondaryKeyName(){
        return "CODE";
    }


    public function ObjectTypeProperty(){
        return ObjectTypeProperty::loadBy(['ObjectDataSource'	=> $this]);
    }

    public function ObjectTypePropertyExportData(){
        return ObjectTypePropertyExportData::loadBy(['ObjectDataSource'	=> $this]);
    }

    public function GraphObjectTypeProperty(){
        return GraphObjectTypeProperty::loadBy(['ObjectDataSource'	=> $this]);
    }
}
