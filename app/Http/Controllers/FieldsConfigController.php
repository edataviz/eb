<?php

namespace App\Http\Controllers;
use App\Models\CfgDataSource;
use App\Models\CfgFieldProps;
use App\Models\Facility;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class FieldsConfigController extends CodeController {
	
	
	public function _index() {
		$cfg_data_source = CfgDataSource::select('NAME','SRC_TYPE')->orderBy('NAME')->get();
		log::info($cfg_data_source);
		return view ( 'front.fieldsconfig',[
				'cfg_data_source' 		=> $cfg_data_source,
		]);
	}
	
	public function getColumn(Request $request){
		$data = $request->all ();
        $cfgConfig 		    = array_key_exists('CfgConfig', $data)&&$data['CfgConfig']?$data['CfgConfig']:null;
        $getFields          = $this->getFields($data['table'],$cfgConfig);
		$getFieldsEffected  = $this->getFieldsEffected($data['table'],$cfgConfig);
		$dcDisable          = 0;
		$tmp                = CfgDataSource::where(['NAME'=>$data['table']])
                                ->select(['DISABLE_DC'])
                                ->first();
		if($tmp){
			$dcDisable = $tmp["DISABLE_DC"];
		}
		
		return response ()->json ( ['getFields' => $getFields,
            'getFieldsEffected'=>$getFieldsEffected,
            'dcDisable' => $dcDisable] );
	}

    public function getDataJson(Request $request){
        $postData           = $request->all ();
        $table 		        = $postData['table'];
        $configId           = array_key_exists('configId', $postData)&&$postData['configId']?$postData['configId']:null;
        $configData         = $this->loadConfigData($table,$configId);
        return response ()->json ($configData);
    }

    public function loadMultiTableData($resultTransaction,$postData,$editedData) {
        $table 		        = $postData['TABLE_NAME'];
        $configId           = array_key_exists('CONFIG_ID', $postData)&&$postData['CONFIG_ID']?$postData['CONFIG_ID']:null;
        $configData         = $this->loadConfigData($table,$configId);
        return $configData;
    }

    public function loadConfigData($table,$configId=null){
        \Helper::setGetterUpperCase();
        $dataProp               = CfgFieldProps::getDataProp($table,$configId);
        $getFields              = $this->getFields($table,$configId);
        $dcDisable              = 0;
        $tmp                    = CfgDataSource::where(['NAME'=>$table])
                                ->select(['DISABLE_DC'])
                                ->first();
        if($tmp) $dcDisable     = $tmp["DISABLE_DC"];
        $mdl					= \Helper::getModelName($table);
        $objectExtension 		= method_exists($mdl,"getObjects")?$mdl::getObjects():[];
        $objectExtensionTarget 	= method_exists($mdl,"getObjectTargets")?$mdl::getObjectTargets():[];
		$objectExtension2 = null;
		if($table == \App\Models\KeystoreInjectionPointDay::getTableName()){
			$facilities = null;
			$objectExtension2 = \App\Models\Keystore::all();
		}
		else
        	$facilities             = Facility::all();

        return [
            "table"             => $table,
            "config_id"         => $configId,
            "dataProp"          => $dataProp,
            "getFields"         => $getFields,
            'dcDisable'         => $dcDisable,
            "objectExtension"		=> $objectExtension,
            "objectExtension2"		=> $objectExtension2,
            'objectExtensionTarget'	=> $objectExtensionTarget,
            'facility'              => $facilities,

        ];
    }

    public function saveDisableDC(Request $request){
		$data = $request->all ();	
		CfgDataSource::where(['NAME'=>$data['table']])->update(['DISABLE_DC'=>$data['disable_dc']]);
		return response ()->json ('OK');
	}
	
	private function getFields($table,$cfgConfig=null) {
		$model 	= \Helper::getModelName($table);
		$mdl 	= new $model;
		$columns = $mdl->getTableColumns();
		sort($columns);
		
		$tmps = $columns;
		
		$cfg_field_props = $this->getFieldsEffected($table,$cfgConfig);
		if ($cfg_field_props) {
// 			$field 		= config('database.default')==='oracle'?'column_name':'COLUMN_NAME';
			$field 		= 'COLUMN_NAME';
			$cColumns 	= $cfg_field_props->pluck($field)->toArray();
			$tmps 		= array_diff($columns, $cColumns);
		}
		
		$result = [];
		foreach($tmps as $tmp){
			$result[] = ['COLUMN_NAME'	=> $tmp];
		}
		
		return $result;
	}	
	
	private function getFieldsEffected($table,$cfgConfig=null) {
		\Helper::setGetterUpperCase();
        $query = CfgFieldProps::where(['TABLE_NAME'=>$table])
                                ->orderBy('FIELD_ORDER');
        if ($cfgConfig)
            $query->where('CONFIG_ID','=',$cfgConfig);
        else
            $query->whereNull('CONFIG_ID');

        $result = $query->get(['COLUMN_NAME']);
		return $result;
	}

	public function saveconfig(Request $request){
		$vdata = $request->all ();
		$table = $vdata['table'];
		$data = $vdata['data'];
        $configId 		= array_key_exists('configId', $vdata)&&$vdata['configId']?$vdata['configId']:null;

		$i=1;
		$fields=explode(",",$data);
		foreach($fields as $field)
		{
			if($field) {
				$type		= \Helper::getDataType($table,$field);
				$re_exist 	= CfgFieldProps::where(['COLUMN_NAME'=>$field, 'TABLE_NAME'=>$table,'CONFIG_ID'=>$configId])->get(['COLUMN_NAME']);
				if(count($re_exist)!=0){
					CfgFieldProps::where(['COLUMN_NAME'=>$field, 'TABLE_NAME'=>$table,'CONFIG_ID'=>$configId])->update(['FIELD_ORDER'=>$i]);
				}else{
					CfgFieldProps::insert(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$field, 'FIELD_ORDER'=>$i, 'DATA_METHOD'=>$type, 'INPUT_TYPE'=>1, 'INPUT_ENABLE'=>1,'CONFIG_ID'=>$configId]);
				}
				$i++;
			}
		}
		
		//Xoa
		CfgFieldProps::where(['TABLE_NAME'=>$table,'CONFIG_ID'=>$configId])->whereNotIn('COLUMN_NAME',$fields)->delete();
		/* $re_full = CfgFieldProps::where(['TABLE_NAME'=>$table])->get(['COLUMN_NAME']);
		foreach ($re_full as $row_full)
		{
			$del = true;
			foreach ( $fields as $field ) {
				if ($row_full->COLUMN_NAME == $field) {
					$del = false;
					break;
				}
			}
			if ($del == true) {
				CfgFieldProps::where(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$row_full->COLUMN_NAME]);
			}
		} */
		
		return response ()->json ( 'ok' );
	}
	
	public function chckChange(Request $request){
		$data = $request->all ();
		$str = "";
		$tbl=$data['chk_tbl'];
		$vie=$data['chk_vie'];
		
		if($tbl=='true' and $vie=='true')
		{
			$w=-1;
		}
		else
		{
			if($vie=='true')
				$w=0;
				else if($tbl=='true')
					$w=1;
		};
		
		$re_tbl = [];
		if($w != -1){
			$re_tbl = CfgDataSource::where(['SRC_TYPE'=>$w])->orderBy('NAME')->get(['NAME']);
		}else{
			$re_tbl = CfgDataSource::orderBy('NAME')->get(['NAME']);
		}
		
		foreach ($re_tbl as $row_tbl)
		{
			$str .= "<option value='".$row_tbl->NAME."'>".$row_tbl->NAME."</option>";
		}
		
		return response ()->json ($str);
	}


	public function getprop(Request $request){
		$data 					= $request->all ();
		$table 					= $data['table'];
		$field 					= $data['field_effected'];
        $configId 		        = array_key_exists('configId', $data)&&$data['configId']?$data['configId']:null;
		\Helper::setGetterUpperCase();
		$respondData			= CfgFieldProps::getFieldProperties($table,$field,$configId);
		return response ()->json ($respondData);
	}


    public function putValue(&$param,$data,$field){
		if (isset ( $data [$field] ))
			$param [$field] = $data [$field]==''?null:$data [$field];
	}
	
	public function saveprop(Request $request){
		$data = $request->all ();


		$vfield = $data ['field'];
		$fields = explode ( ",", $vfield );
		$table = $data ['table'];
        $configId= array_key_exists('configId', $data)&&$data['configId']?$data['configId']:null;
		$param = [ ];
		if (isset ( $data ['data_method'] )) {
			$param ['DATA_METHOD'] = $data ['data_method'];
			$param ['INPUT_ENABLE'] = $data ['data_method'];
		}
		if (isset ( $data ['VALUE_FORMAT'] )) {
			$param ['VALUE_FORMAT'] = $data ['VALUE_FORMAT'];
		}
		$this->putValue($param,$data,'FORMULA');
		$this->putValue($param,$data,'INPUT_TYPE');
		$this->putValue($param,$data,'VALUE_FORMAT');
		$this->putValue($param,$data,'VALUE_MAX');
		$this->putValue($param,$data,'VALUE_MIN');
		$this->putValue($param,$data,'VALUE_WARNING_MAX');
		$this->putValue($param,$data,'VALUE_WARNING_MIN');
		$this->putValue($param,$data,'RANGE_PERCENT');
		$this->putValue($param,$data,'FDC_WIDTH');

		
		if (isset ( $data ['friendly_name'] ) && count ( $fields ) == 1)
			$param ['LABEL'] = $data ['friendly_name'];
		
		$objectExtension = isset ( $data ['objectExtension'] )&&count($data ['objectExtension'])>0?json_encode($data ['objectExtension']):null;
		$param['USE_FDC'] = $data ['us_data'];
		$param['USE_DIAGRAM'] = $data['us_sr'];
		$param['USE_GRAPH'] = $data['us_gr'];
		$param['IS_MANDATORY'] = $data['is_mandatory'];
		$param['OBJECT_EXTENSION'] = $objectExtension;

			//\DB::enableQueryLog ();
		CfgFieldProps::where(['TABLE_NAME'=>$table,'CONFIG_ID'=> $configId])->whereIn('COLUMN_NAME', $fields)->update($param);
		//\Log::info ( \DB::getQueryLog () );
		
		return response ()->json ('OK');
	}
    public function savealldata(Request $request){
        $data = $request->all ();
        $config_id=$data['config_id'];
        $data_prop=$data['dataProp'];
        $table=$data['table'];
//        Log::info($data);
        if($config_id==0)
            $config_id=NULL;
        for( $i=0;$i<count($data_prop);$i++){
            $id=CfgFieldProps::where(['ID'=>$data_prop[$i]['ID']])->first();
            if($id==NULL){
                /*CfgFieldProps::insert(['ID'=>$data_prop[$i]['ID'],'TABLE_NAME'=>$table,'CONFIG_ID'=>$config_id,
                        'FIELD_ORDER'=>$data_prop[$i]['FIELD_ORDER'],'COLUMN_NAME'=>$data_prop[$i]['COLUMN_NAME'],
                        'FDC_WIDTH'=>$data_prop[$i]['FDC_WIDTH'],'LABEL'=>$data_prop[$i]['LABEL'],
                        'DATA_METHOD'=>$data_prop[$i]['DATA_METHOD'],'FORMULA'=>$data_prop[$i]['FORMULA'],
                        'INPUT_INVISIBLE'=>$data_prop[$i]['INPUT_VISIBLE'],'INPUT_ENABLE'=>$data_prop[$i]['INPUT_ENABLE'],
                        'IS_MANDATORY'=>$data_prop[$i]['IS_MANDATORY'],'INPUT_TYPE'=>$data_prop[$i]['INPUT_TYPE'],
                        'VALUE_FORMAT'=>$data_prop[$i]['VALUE_FORMAT'],'VALUE_MAX'=>$data_prop[$i]['VALUE_MAX'],
                        'VALUE_MIN'=>$data_prop[$i]['VALUE_MIN'],'USE_FDC'=>$data_prop[$i]['USE_FDC'],
                        'USE_DIAGRAM'=>$data_prop[$i]['USE_DIAGRAM'],'USE_GRAPH'=>$data_prop[$i]['USE_GRAPH'],
                        'V_BAK'=>$data_prop[$i]['V_BAK'],'OBJECT_EXTENSION'=>$data_prop[$i]['OBJECT_EXTENSION']])*/
            }
        }
        /*CfgFieldProps::where(['TABLE_NAME'=>$table,'CONFIG_ID'=> $config_id])*/
        /*$vfield = $data ['field'];
        $fields = explode ( ",", $vfield );
        $table = $data ['table'];
        $configId= array_key_exists('configId', $data)&&$data['configId']?$data['configId']:null;
        $param = [ ];
        if (isset ( $data ['data_method'] )) {
            $param ['DATA_METHOD'] = $data ['data_method'];
            $param ['INPUT_ENABLE'] = $data ['data_method'];
        }
        if (isset ( $data ['VALUE_FORMAT'] )) {
            $param ['VALUE_FORMAT'] = $data ['VALUE_FORMAT'];
        }
        $this->putValue($param,$data,'FORMULA');
        $this->putValue($param,$data,'INPUT_TYPE');
        $this->putValue($param,$data,'VALUE_FORMAT');
        $this->putValue($param,$data,'VALUE_MAX');
        $this->putValue($param,$data,'VALUE_MIN');
        $this->putValue($param,$data,'VALUE_WARNING_MAX');
        $this->putValue($param,$data,'VALUE_WARNING_MIN');
        $this->putValue($param,$data,'RANGE_PERCENT');
        $this->putValue($param,$data,'FDC_WIDTH');


        if (isset ( $data ['friendly_name'] ) && count ( $fields ) == 1)
            $param ['LABEL'] = $data ['friendly_name'];

        $objectExtension = isset ( $data ['objectExtension'] )&&count($data ['objectExtension'])>0?json_encode($data ['objectExtension']):null;
        $param['USE_FDC'] = $data ['us_data'];
        $param['USE_DIAGRAM'] = $data['us_sr'];
        $param['USE_GRAPH'] = $data['us_gr'];
        $param['IS_MANDATORY'] = $data['is_mandatory'];
        $param['OBJECT_EXTENSION'] = $objectExtension;

        //\DB::enableQueryLog ();
        CfgFieldProps::where(['TABLE_NAME'=>$table,'CONFIG_ID'=> $configId])->whereIn('COLUMN_NAME', $fields)->update($param);
        //\Log::info ( \DB::getQueryLog () );
        CfgDataSource::where(['NAME'=>$data['table']])->update(['DISABLE_DC'=>$data['disable_dc']]);
        $table = $vdata['table'];
        $data = $vdata['data'];
        $configId 		= array_key_exists('configId', $vdata)&&$vdata['configId']?$vdata['configId']:null;

        $i=1;
        $fields=explode(",",$data);
        foreach($fields as $field)
        {
            if($field) {
                $type		= \Helper::getDataType($table,$field);
                $re_exist 	= CfgFieldProps::where(['COLUMN_NAME'=>$field, 'TABLE_NAME'=>$table,'CONFIG_ID'=>$configId])->get(['COLUMN_NAME']);
                if(count($re_exist)!=0){
                    CfgFieldProps::where(['COLUMN_NAME'=>$field, 'TABLE_NAME'=>$table,'CONFIG_ID'=>$configId])->update(['FIELD_ORDER'=>$i]);
                }else{
                    CfgFieldProps::insert(['TABLE_NAME'=>$table, 'COLUMN_NAME'=>$field, 'FIELD_ORDER'=>$i, 'DATA_METHOD'=>$type, 'INPUT_TYPE'=>1, 'INPUT_ENABLE'=>1,'CONFIG_ID'=>$configId]);
                }
                $i++;
            }
        }

        //Xoa
        CfgFieldProps::where(['TABLE_NAME'=>$table,'CONFIG_ID'=>$configId])->whereNotIn('COLUMN_NAME',$fields)->delete();*/
        return response ()->json ('OK');
    }
}