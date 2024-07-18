<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargo;
use App\Models\PdVoyage;
use App\Models\PdShipOilLpgTankData;
use App\Models\PdCompartment;
use App\Models\PdShipPortInformation;
use App\Models\PdTransportPipelineDetail;
use App\Models\PdTransitCarrierVefData;
use Illuminate\Http\Request;

class CargoMeasurementController extends CodeController {
    
	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdShipOilLpgTankData";
	}
	
    public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=> 100];
	}
/*	
    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
        $prefix 		= 	'';
        $par = [
            (object)['data' =>	'DT_RowId',		'title' => '',	'width'	=>	100,'INPUT_TYPE'=>1,	'DATA_METHOD'=>2,'FIELD_ORDER'=>1],
            (object)['data' =>	'VOYAGE_NO',		'title' => 'Voyage Name',	'width'	=>	100,'INPUT_TYPE'=>1,	'DATA_METHOD'=>2,'FIELD_ORDER'=>2],
            (object)['data' =>	$prefix."CARRIER_ID"	,'title' => 'Carrier'	,	'width'	=>	125,'INPUT_TYPE'=>1,	'DATA_METHOD'=>3,'FIELD_ORDER'=>3],
            (object)['data' =>	$prefix."CARGO_ID"	,'title' => 'Cargo'	,   'width'=>	125,'INPUT_TYPE'=>1,	'DATA_METHOD'=>3,'FIELD_ORDER'=>4],
            (object)['data' =>	$prefix."LIFTING_ACCOUNT"	,'title' => 'Lifting Account',	'width'	=>	125,'INPUT_TYPE'=>1,	'DATA_METHOD'=>3,'FIELD_ORDER'=>5],
            (object)['data' =>	$prefix."SCHEDULE_DATE"	,'title' => 'Scheduled Date', 	'width'	=>	125,'INPUT_TYPE'=>1,	'DATA_METHOD'=>3,'FIELD_ORDER'=>6],
            (object)['data' =>	$prefix."SCHEDULE_QTY"	,'title' => 'Scheduled Qty', 	'width'	=>	125,'INPUT_TYPE'=>1,	'DATA_METHOD'=>3,'FIELD_ORDER'=>6],
        ];

        $properties = collect($par);
        return ['properties'	=>$properties];
	}
*/	
	public function addAllObjects(Request $request){
		$rs = $this->_loadObjects($request, true);
		return "[$rs]";
	}

	public function loadObjects(Request $request){
		return $this->_loadObjects($request);
	}

	public function _loadObjects($request, $json = false){
		$measureType = $request->measureType;
		$carrierId = $request->carrierId;
		$portId = $request->portId;
		//$productType = $request->productType;
		$current = property_exists($request, 'current') ? $request->current : null;
		$result = "";

		if($measureType == 1 && $carrierId > 0){
			//Vessel Tank
			$sql = "SELECT ID, NAME FROM PD_COMPARTMENT where CARRIER_ID=$carrierId";
			$rs = \DB::select($sql);
			$result = $json?"":"<option>(Select a compartment)</option>";
			foreach($rs as $r)
				$result .= $json?(($result?",":"")."[$r->ID,\"$r->NAME\"]"):"<option ".($r->ID==$current? "selected ": "")."value='$r->ID'>$r->NAME</option>";
		}
		else if(($measureType == 2 || $measureType == 4) && $portId > 0){
			$sql = "SELECT ID, NAME FROM PD_SHORE_TANK where PORT_ID=$portId";
			$rs = \DB::select($sql);
			$result = $json?"":"<option>(Select a shore tank)</option>";
			foreach($rs as $r)
				$result .= $json?(($result?",":"")."[$r->ID,\"$r->NAME\"]"):"<option ".($r->ID==$current? "selected": "")." value='$r->ID'>$r->NAME</option>";
		}
		else if($measureType == 3 && $portId > 0){
			$sql = "SELECT ID, NAME FROM PD_SHIPPING_METER where PORT_ID=$portId";//.($productType ? "  PRODUCT_TYPE=$productType" : "");
			$rs = \DB::select($sql);
			$result = $json?"":"<option>(Select a meter)</option>";
			foreach($rs as $r)
				$result .= $json?(($result?",":"")."[$r->ID,\"$r->NAME\"]"):"<option ".($r->ID==$current? "selected": "")." value='$r->ID'>$r->NAME</option>";
		}
		return $result;
	}

	public function saveDetail(Request $request){
		$input = $request->all();
		return json_encode($input);
	}

	public function loadDetailNew(Request $request){
		
		function getInputControl($config, $value, $class_prefix = false)
		{
			$input_type=$config->INPUT_TYPE;
			$data_method=$config->DATA_METHOD;
			$checkValueRange = "";
			$checked = "";

			if(isset($config->FDC_WIDTH))
				$style = "style='width:{$config->FDC_WIDTH}px'";
			else
				$style = "style='width:100px'";
			
			if(isset($config->VALUE_MAX) || isset($config->VALUE_MIN))
			{
				$checkValueRange="need_check='1' max_value='$config->VALUE_MAX' min_value='$config->VALUE_MIN'";
			}
			$value_format="value='$value'";
			$input_pattern="";
			if(isset($config->VALUE_FORMAT) && $config->VALUE_FORMAT!="")
			{
				$format=$config->VALUE_FORMAT;
				if(strpos($format, '{{')!==false){
					$input_pattern="pattern='$format'";
					if($value!="" && is_numeric($value)){
						$value=round($value);
						$c=substr_count($format,"9");
						if($c>strlen($value))
							$value_format="value='".str_pad($value, $c, "0", STR_PAD_LEFT)."'";
					}
				}
				else if($value!=""){
					$separator=strpos($format, '.');		//Get position of separator
					if($separator==false)
						$n_decimal=0;
					else
						$n_decimal=strspn($format, '#', $separator+1);
					$value_format="value='".number_format($value, $n_decimal,'.','')."' or='".$value."'";
				}
			}
			
			if($input_type==5)
			{
				$type="checkbox";
				if($value==1)
					$checked="checked";
				else
					$value="1";
			}
			else
				$type="text";
		
			if($data_method==1)
				$readonly="";
			else
				$readonly="readonly";
			$ret="$style input_type='$input_type' data_method='$data_method' type='$type' $checkValueRange $value_format $input_pattern $readonly $checked class='".($class_prefix?("$class_prefix "):"").typetoclass($input_type)."'";
			return $ret;
		}

		function typetoclass($data)
		{
			switch($data){
				case "1":
					return "";
				case "2":
					return "_numeric";
				case "3":
					return "_datepicker";
				case "4":
					return "_datetimepicker";
				case "6":
					return "_timepicker";
			}
		}
		
		function getInputType($dataType)
		{
			switch($dataType){
				case "varchar":
				case "text":
				case "char":
					return 1;				//Text input
				case "int":
				case "decimal":
				case "tinyint":
				case "float":
				case "double":
					return 2;				//Number input
				case "date":
					return 3;				//Date picker
				case "time":
					return 4;				//Date picker
				case "datetime":
					return 4;				//Datetime picker
			}	
		}
		
		function getFieldsConfig($table)
		{
			$sql="SELECT * FROM cfg_field_props WHERE CONFIG_ID is null and TABLE_NAME='$table' AND USE_FDC='1' ORDER BY FIELD_ORDER";
			$re = \DB::select($sql);
			return $re;
		}

		function selectComboTag($combo,$selected_value,$default_value="") {
			//$tmp = str_replace(" selected","", $combo);
			$v=(($selected_value===null || $selected_value==='')?$default_value:$selected_value);
			return str_replace("value='$v'", "value='$v' selected", $combo);
		}
		
		function getOption($table, $selected = null) {
			$sql = "SELECT ID, NAME FROM $table";
			$rs = \DB::select($sql);
			$result = "<option></option>";
			foreach($rs as $r)
				$result.="<option ".($r->ID==$selected? "selected": "")." value='$r->ID'>$r->NAME</option>";
			return $result;
		}

		$voyageId = $request->voyageId;
		$carrierId = $request->carrierId;
		$date_begin = $request->date_begin;
		$date_end = $request->date_end;
		$prefix = 'MEAS';
		$hi_input = true;

		$MEASURE_TYPE=getOption('pd_trip_meas_code');
		$CARRIER_ID=getOption('pd_transit_carrier');
		$PORT_ID=getOption('pd_port');
		$PRODUCT_NAME=getOption('code_product_type');
		
		$re=getFieldsConfig('PD_SHIP_OIL_LPG_TANK_DATA');
		$fields="";
		foreach($re as $row)
			$fields .= ($fields?",":"").$row->COLUMN_NAME;
			
		$sSQL="SELECT a.ID T_ID, a.VOYAGE_ID OBJ_ID,a.DATE_TIME T_DATE_TIME".($fields?", $fields":"")." FROM PD_SHIP_OIL_LPG_TANK_DATA a where DATE(a.DATE_TIME) between '$date_begin' and '$date_end' and a.VOYAGE_ID='$voyageId' order by a.DATE_TIME";
		$result=\DB::select($sSQL);
		$ids="";
		$i=0;
		$strHtml = "<th></th>";
		foreach($re as $ro){
			$strHtml .= "<th>".($ro->LABEL ? $ro->LABEL : $ro->COLUMN_NAME)."</th>";
		}
		$strHtml = "<table id='tableMeas'><tr>$strHtml<tr>";
		
		$row_count=count($result);
		$break=false;
		while(!$break) {
			$row=($i<$row_count?$result[$i]:null);
			$i++;
			$break=false;
			if(!$row) {
				$break=true;
				$row = [
					'T_ID'=>'_NEW0',
					'OBJ_ID' => null,
					'T_DATE_TIME' => null
				];
				foreach($re as $ro)
					$row[$ro->COLUMN_NAME] = null;
				$row = (object)$row;
			}
			if($i % 2==0) $bgcolor="#eeeeee"; else $bgcolor="#f8f8f8";
			$ids.= ($ids?',':'')."$row->T_ID";

			$xMEASURE_TYPE=selectComboTag($MEASURE_TYPE, $row->MEASURE_TYPE);
			$xCARRIER_ID=selectComboTag($CARRIER_ID, $row->CARRIER_ID ? $row->CARRIER_ID : $carrierId);
			$xPORT_ID=selectComboTag($PORT_ID, $row->PORT_ID);
			$xPRODUCT_NAME=selectComboTag($PRODUCT_NAME, $row->PRODUCT_NAME);

			$xOBJECT = $this->_loadObjects((object)[
				'measureType' => $row->MEASURE_TYPE,
				'carrierId' => $row->CARRIER_ID,
				'portId' => $row->PORT_ID,
				'productType' => $row->PRODUCT_NAME,
				'current' => $row->COMPARTMENT_ID
			]);
			
			$strHtml .= "<tr rid='$row->T_ID' bgcolor='$bgcolor' class='measrow' oid='$row->T_ID' pre='$prefix'>";
			$strHtml .= "<td><a style='color:gray' href=\"javascript:deleteMeas('$row->T_ID')\">Delete</a></td>";
			
			foreach($re as $ro)
			{
				$f=$ro->COLUMN_NAME;
				if($f=='MEASURE_TYPE')
					$strHtml .= "<td><select onchange='loadMeasObject(\"$row->T_ID\")' class='".($ro->DATA_METHOD!=1? "_readonly":"")."' name='$prefix"."_"."$ro->COLUMN_NAME$row->T_ID'>$xMEASURE_TYPE</select></td>";
				else if($f=='CARRIER_ID')
					$strHtml .= "<td><select class='".($ro->DATA_METHOD!=1? "_readonly":"")."' name='$prefix"."_"."$ro->COLUMN_NAME$row->T_ID'>$xCARRIER_ID</select></td>";
				else if($f=='PORT_ID')
					$strHtml .= "<td><select onchange='onPortChanged(\"$row->T_ID\")' class='".($ro->DATA_METHOD!=1? "_readonly":"")."' name='$prefix"."_"."$ro->COLUMN_NAME$row->T_ID'>$xPORT_ID</select></td>";
				else if($f=='PRODUCT_NAME')
					$strHtml .= "<td><select onchange='onProductChanged(\"$row->T_ID\")' class='".($ro->DATA_METHOD!=1? "_readonly":"")."' name='$prefix"."_"."$ro->COLUMN_NAME$row->T_ID'>$xPRODUCT_NAME</select></td>";
				else if($f=='COMPARTMENT_ID')
					$strHtml .= "<td><select class='".($ro->DATA_METHOD!=1? "_readonly":"")."' name='$prefix"."_"."$ro->COLUMN_NAME$row->T_ID'>$xOBJECT</select></td>";
				else
				{
					$val=$row->{$f};
					if($f=='BEGIN_TIME' or $f=='END_TIME')
					{
						if($row->{$f})
						{
							$date=date_create($row->{$f});
							$val=date_format($date, "m/d/Y H:i");
						}
						else
							$val="";
					}
					else if($f=='EFFECTIVE_DATE')
					{
						if($row->{$f})
						{
							$date=date_create($row->{$f});
							$val=date_format($date, "m/d/Y");
						}
						else
							$val="";
					}
					$strHtml .= "<td><input ".getInputControl($ro, $val)." name='$prefix"."_"."$ro->COLUMN_NAME$row->T_ID' id='$prefix"."_"."$ro->COLUMN_NAME$row->T_ID'>";
					if($i==$row_count+1 && $hi_input)
					{
						$hi_input=false;
						$strHtml .= "<input type='hidden' name='FIELDS_$prefix' value='$fields'><input type='hidden' name='IDS_$prefix' value='$ids'>";
					}
					$strHtml .= "</td>\n";
				}
			}
			$strHtml .= "</tr></table>";
		}

		return $strHtml;
	}

	public function loadDetail(Request $request){
		$postData 				= $request->all();
		$id 					= $postData['id'];
		$tab					= isset($postData['tab'])?$postData['tab']:$this->detailModel;
		$detailModel			= "App\Models\\$tab";
		$detailTable	 		= $detailModel::getTableName();
		$results 				= $this->getProperties($detailTable);
		$originAttrCase = \Helper::setGetterUpperCase();
		$dataSet 				= $this->getDetailData($id,$postData,$results['properties']);
		\Helper::setGetterCase($originAttrCase);
		
		$results['dataSet'] 	= $dataSet;
	
		return response()->json([$this->detailModel => $results]);
	}

	public function getCompartments(Request $request){
		//$r = \App\Models\PdCompartment::where('CARRIER_ID', '=', $request->CARRIER_ID)->select()->get();
	}
	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	if (!array_key_exists("Storage", $postData)) {
    		return ['dataSet'=>$this->getDetailData($postData["id"], $postData, $properties)];
    	}
    	$storage_id			= $postData['Storage'];
    	$date_end 			= $postData['date_end'];
    	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
    	
    	$mdlName 			= $postData[config("constants.tabTable")];
    	$mdl 				= "App\Models\\$mdlName";
    	$pdCargo 			= PdCargo::getTableName();
    	 
    	$dataSet = $mdl::join($pdCargo,
			    			"$dcTable.CARGO_ID",
			    			'=',
			    			"$pdCargo.ID")
		    			->whereDate("$dcTable.SCHEDULE_DATE",'>=',$occur_date)
    					->whereDate("$dcTable.SCHEDULE_DATE",'<=',$date_end)
    					->where("$pdCargo.STORAGE_ID",'=',$storage_id)
				    	->select(
				    			"$dcTable.ID as $dcTable",
				    			"$dcTable.ID as DT_RowId",
				    			"$dcTable.*") 
  		    			->get();
 		    			
    	return ['dataSet'=>$dataSet];
    }
    
    public function getDetailData($id,$postData,$properties){
		//\Log::info($postData);
		$vID =$postData['id']; // isset($postData['VOYAGE_ID']) ? $postData['VOYAGE_ID'] : ($postData['editedData']['PdShipOilLpgTankData'][0]['VOYAGE_ID']);
    	$facility	 			= $postData['Facility'];
		$pdVoyage				= PdVoyage::getTableName();
    	$pdDetail				= PdShipOilLpgTankData::getTableName();
    	$dataSet 				= PdShipOilLpgTankData::join($pdVoyage,
						    			"$pdDetail.VOYAGE_ID",
						    			'=',
						    			"$pdVoyage.ID")
				    			->where("$pdVoyage.ID",'=',$vID)
				    			->select(
				    					"$pdDetail.*",
				    					"$pdDetail.ID as DT_RowId",
										"$pdDetail.ID as $pdDetail"
				    					)
		    					->get();
    	return $dataSet;
    }
    
    public function addAllComps(Request $request){
    	$postData 			= $request->all();
		$voyageId			= $postData['VOYAGE_ID'];
		
    	$pdComp		= PdCompartment::getTableName();
    	$pdDetail	= PdShipOilLpgTankData::getTableName();
		$comps 		= PdCompartment::where('active', '=', '1')
		->whereNotExists(function($query) use ($pdComp, $pdDetail, $voyageId){
			$query->select(DB::raw(1))
				->from($pdDetail)
				->whereRaw("$pdComp.ID = $pdDetail.COMPARTMENT_ID and ");
		})->get();

    	$pdVoyage			= PdVoyage::getTableName();
    	$pdTransitCarrier	= PdTransitCarrier::getTableName();
    	$dataSet = PdVoyageDetail::join($pdVoyage,
						    			"$pdVoyageDetail.VOYAGE_ID",
						    			'=',
						    			"$pdVoyage.ID")
				    			->join($pdTransitCarrier,
						    			"$pdVoyage.CARRIER_ID",
						    			'=',
						    			"$pdTransitCarrier.ID")
				    			->where("$pdVoyage.ID",'=',$voyage_id)
				    			->orderBy("$pdVoyageDetail.ID")
				    			->select(
				    					"$pdVoyage.CODE as VOYAGE_CODE",
				    					"$pdVoyage.NAME as VOYAGE_NAME",
				    					"$pdVoyage.QUANTITY_TYPE as VOYAGE_QTY_TYPE",
				    					"$pdTransitCarrier.TRANSIT_TYPE",
				    					"$pdVoyageDetail.*")
		    					->get();
    	
    	try
    	{
    		$resultTransaction = \DB::transaction(function () use ($dataSet,$voyage_id){
    			$attributes = ['VOYAGE_ID'	=> $voyage_id];
    			foreach($dataSet as $ro){
    				$attributes['PARCEL_NO']	= $ro->PARCEL_NO;
    				switch ($ro->TRANSIT_TYPE) {
    					case 3:
    						$pdTransportShipDetail			= PdTransportShipDetail::where($attributes)->first();
    						if (!$pdTransportShipDetail) {
    							$values						= ['VOYAGE_ID'	=> $voyage_id];
    							$values['CODE']				= "SH_$ro->VOYAGE_CODE"."_$ro->PARCEL_NO";
    							$values['NAME']	 			= "SH_$ro->VOYAGE_NAME"."_$ro->PARCEL_NO";
    							$values['CARGO_ID']			= $ro->CARGO_ID;
    							$values['PARCEL_NO']		= $ro->PARCEL_NO;
    							$values['RECEIPT_QTY']		= $ro->LOAD_QTY;
    							$values['QTY_TYPE']			= $ro->VOYAGE_QTY_TYPE;
    							$values['QTY_UOM']			= $ro->LOAD_UOM;
    				    
    							$pdTransportShipDetail		= PdTransportShipDetail::insert($values);
    							$pdShipPortInformation		= PdShipPortInformation::insert($attributes);
    						}
    						break;
    			
    					case 4:
    						$pdTransportPipelineDetail		= PdTransportPipelineDetail::where($attributes)->first();
    						if (!$pdTransportPipelineDetail) {
    							$values						= ['VOYAGE_ID'	=> $voyage_id];
    							$values['CODE']				= "PP_$ro->VOYAGE_CODE"."_$ro->PARCEL_NO";
    							$values['NAME']	 			= "PP_$ro->VOYAGE_NAME"."_$ro->PARCEL_NO";
    							$values['CARGO_ID']			= $ro->CARGO_ID;
    							$values['PARCEL_NO']		= $ro->PARCEL_NO;
    							$values['QUANTITY']			= $ro->LOAD_QTY;
    							$values['QUANTITY_UOM']		= $ro->LOAD_UOM;
    			
    							$pdTransportPipelineDetail	= PdTransportPipelineDetail::insert($values);
    						}
    						break;
    			
    					default:
    						$pdTransportGroundDetail		= PdTransportGroundDetail::where($attributes)->first();
    						if (!$pdTransportGroundDetail) {
    							$values						= ['VOYAGE_ID'	=> $voyage_id];
    							$values['CODE']				= "GR_$ro->VOYAGE_CODE"."_$ro->PARCEL_NO";
    							$values['NAME']	 			= "GR_$ro->VOYAGE_NAME"."_$ro->PARCEL_NO";
    							$values['CARGO_ID']			= $ro->CARGO_ID;
    							$values['PARCEL_NO']		= $ro->PARCEL_NO;
    							$values['QUANTITY']			= $ro->LOAD_QTY;
    							$values['QUANTITY_UOM']		= $ro->LOAD_UOM;
    			
    							$pdTransportGroundDetail	= PdTransportGroundDetail::insert($values);
    						}
    						break;
    				}
    			}
    		});
    	}
	    catch (\Exception $e)
	    {
    		return response()->json('error when insert data');
	    }
	    return response()->json('success');
	}
	
	function genVEF(Request $request){
    	$postData = $request->all();
		$vid = $postData['id'];
		if($vid){
			$re = \DB::select("select b.CARRIER_ID,a.DATE_TIME,a.PRODUCT_NAME PRODUCT_TYPE,a.PORT_ID,max(VOL_UOM) TCV_UOM,
			sum(case when a.MEASURE_TYPE in (1,3) then a.NET_VOL else null end) VESSEL_TCV,
			sum(case when a.MEASURE_TYPE in (2,4) then a.NET_VOL else null end) SHORE_TCV,
			sum(a.CORRECT_VOL) VESSEL_BEGIN_TCV,max(a.VCF_TABLE) VCF_TABLE,max(a.VCF_DECIMAL) VEF_RATIO,sum(a.NUMBER_4) VESSEL_END_TCV
from PD_SHIP_OIL_LPG_TANK_DATA a, PD_VOYAGE b where a.VOYAGE_ID=b.ID and a.VOYAGE_ID=$vid
group by b.CARRIER_ID,a.DATE_TIME,a.PRODUCT_NAME,a.PORT_ID");
			foreach($re as $row){
				$row = (array)$row;
				$id = PdTransitCarrierVefData::where([
					'CARRIER_ID'=>$row['CARRIER_ID'],
					'DATE_TIME'=>$row['DATE_TIME'],
					'PORT_ID'=>$row['PORT_ID'],
					'PRODUCT_TYPE'=>$row['PRODUCT_TYPE'],
				])->select('ID')->first();
				if($id) $id=$id->ID;
				if($id){
					PdTransitCarrierVefData::where('ID', $id)->update([
						'TCV_UOM' => $row['TCV_UOM'],
						'VESSEL_TCV' => $row['VESSEL_TCV'],
						'SHORE_TCV' => $row['SHORE_TCV'],
						'VESSEL_BEGIN_TCV' => $row['VESSEL_BEGIN_TCV'],
						'VCF_TABLE' => $row['VCF_TABLE'],
						'VEF_RATIO' => $row['VEF_RATIO'],
						'VESSEL_END_TCV' => $row['VESSEL_END_TCV'],
					]);
					return "VEF data updated";
				}
				else {
					PdTransitCarrierVefData::create(
						$row
					);
					return "VEF data inserted";
				}
			}
		}
	}
}
