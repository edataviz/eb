<?php 
namespace App\Models; 

 class PlotViewConfig extends DynamicModel 
{ 
	protected $table = 'PLOT_VIEW_CONFIG'; 
	
	public $timestamps = false;
	public $primaryKey  = 'ID';
	
	protected $fillable  = ['ID', 'NAME', 'CONFIG', 'TIMELINE', 'CHART_TYPE'];
	
	public static function loadBy($sourceData){
		if(isset($sourceData['Facility'])){
			$facility 		= $sourceData['Facility'];
			if ($facility) {
				$facility_id	= $facility->ID;
				$result			= PlotViewConfig::where("CONFIG",'like',"%#$facility_id:%")->get(); 
				return $result;
				;
			}
		}
		return null;
	}
	
	public function parseViewConfig(){
		$objects	= [];
		$config		= $this->CONFIG;
		if(!$config||$config=="") return null;
		
		$cfs=explode(",",$config);
		$object		= [];
		foreach($cfs as $cf){
			$xs=explode(":",$cf);
			array_shift($xs);
			$pos=strpos($xs[3],"@");
			if($pos>0){
				$xs[3]=substr($xs[3],$pos+1);
			}
			$cls=explode('~',$xs[5]);
			$cal=$cls[0];
			
			$object["IntObjectType"]		= $xs[0];
			$object["ObjectName"]			= $xs[1];
			$object["ObjectDataSource"]		= $xs[2];
			$object[""]	= $xs[3];
			$object["CodeFlowPhase"]		= $xs[4];
			$object["Calculation"]			= $cal;
			$object["text"]					= $xs[count($xs)-1];
				
			/* #18:EU_TEST:230:V_EU_TEST_DATA_VALUE:EU_ID:::East Mereenie 21(V_EU_TEST_DATA_VALUE.EU_ID),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:TEST_HRS:::Test Duration (hrs)(V_EU_TEST_DATA_VALUE.TEST_HRS),
			#18:EU_TEST:230:V_EU_TEST_DATA_FDC_VALUE:OBS_TEMP:::OBS_TEMP(V_EU_TEST_DATA_FDC_VALUE.OBS_TEMP),
			#18:EU_TEST:230:V_EU_TEST_DATA_FDC_VALUE:OBS_PRESS:::OBS_PRESS(V_EU_TEST_DATA_FDC_VALUE.OBS_PRESS),
			#18:EU_TEST:230:V_EU_TEST_DATA_FDC_VALUE:OBS_API:::OBS_API(V_EU_TEST_DATA_FDC_VALUE.OBS_API),
			#18:EU_TEST:230:V_EU_TEST_DATA_FDC_VALUE:CHOKE_SETTING:::CHOKE_SETTING(V_EU_TEST_DATA_FDC_VALUE.CHOKE_SETTING),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:EU_TEST_LIQ_HC_VOL:::OIL (kL)(V_EU_TEST_DATA_VALUE.EU_TEST_LIQ_HC_VOL),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:EU_TEST_WTR_VOL:::WATER (kL)(V_EU_TEST_DATA_VALUE.EU_TEST_WTR_VOL),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:EU_TEST_TOTAL_GAS_VOL:::TOTAL GAS(V_EU_TEST_DATA_VALUE.EU_TEST_TOTAL_GAS_VOL),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:EU_TEST_GAS_LIFT_VOL:::GAS LIFT(V_EU_TEST_DATA_VALUE.EU_TEST_GAS_LIFT_VOL),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:EU_TEST_GAS_HC_VOL:::TEST GAS(V_EU_TEST_DATA_VALUE.EU_TEST_GAS_HC_VOL),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:GOR:::GOR(V_EU_TEST_DATA_VALUE.GOR),
			#18:EU_TEST:230:V_EU_TEST_DATA_VALUE:WATER_CUT:::WATER CUT(V_EU_TEST_DATA_VALUE.WATER_CUT),
			#18:EU_TEST:230:V_EU_TEST_DATA_FDC_VALUE:EU_TEST_SEPARATOR_TEMP:::TEST_SEP_TEMP(V_EU_TEST_DATA_FDC_VALUE.EU_TEST_SEPARATOR_TEMP),
			#18:EU_TEST:230:V_EU_TEST_DATA_FDC_VALUE:EU_TEST_SEPARATOR_PRESS:::TEST_SEP_PRESS(V_EU_TEST_DATA_FDC_VALUE.EU_TEST_SEPARATOR_PRESS),
			#18:EU_TEST:230:V_EU_TEST_DATA_FDC_VALUE:REFERENCE_ID:::REFERENCE ID(V_EU_TEST_DATA_FDC_VALUE.REFERENCE_ID) */
			
			//$sSQL.=($sSQL?" union all ":"")."select $minus($xs[3]$cal) V, DATE_FORMAT($datefield,'%Y,%m-1,%d') D from $xs[2] where $obj_type_id_field=$xs[1] ".(($xs[0]=="ENERGY_UNIT" && !$is_eutest && !$is_defer)?"and FLOW_PHASE=$xs[4] ":"")." and $datefield between '$date_from' and '$date_to' limit 300";
			
			$objects[]	= $object;
		}
		return $objects;
	}
} 
