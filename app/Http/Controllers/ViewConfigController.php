<?php

namespace App\Http\Controllers;
use App\Models\EuPhaseConfig;
use App\Models\GraphDataSource;
use App\Models\CodeForecastType;
use App\Models\CodePlanType;
use App\Models\CodeAllocType;
use App\Models\PlotViewConfig;
use App\Models\SqlList;
use App\Models\SqlConditionFilter;

use Illuminate\Http\Request;

use DB;
use Schema;

class ViewConfigController extends EBController {
	
	public function _indexViewConfig() {
		
		$code_forecast_type = CodeForecastType::all(['ID', 'NAME']);
		$code_plan_type = CodePlanType::all(['ID', 'NAME']);
		$code_alloc_type = CodeAllocType::all(['ID', 'NAME']);
		
		return view ( 'front.viewconfig', ['code_forecast_type'=>$code_forecast_type, 'code_plan_type'=>$code_plan_type, 'code_alloc_type'=>$code_alloc_type]);
	}
	
	
	public function getViewConfigById($plotViewConfigId){
		$plotViewConfig		= PlotViewConfig::find($plotViewConfigId);
		$objects			= $plotViewConfig?$plotViewConfig->parseViewConfig():[];
		return response ()->json (["PlotViewConfig"		=> $plotViewConfigId,
									"objects"			=> $objects
		]);
	}
	
	public function loadPlotObjects(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all ();	
		
		$tmp = $this->loadPlotObjectname($data);
		$graphDataSource = $this->graphDataSource($data['object_type']);
		
		return response ()->json ( ['objectName' => $tmp, 'graphDataSource'=>$graphDataSource] );
	}
	
	private function loadPlotObjectname($data){
	
		$facility_id = $data['facility_id'];
		$product_type = $data['product_type'];
		$object_type = $data['object_type'];
	
		$table_name =$object_type;
		$entity = strtolower(str_replace('_', ' ', $table_name));
		$entity = ucwords($entity);
		$entity = str_replace(' ', '', $entity);
	
		$tmp = [];
		$model = 'App\\Models\\' . $entity;
		//\DB::enableQueryLog ();
		switch ($table_name){
			case "TANK":
			case "STORAGE":
				$tmp = $model::where(['FACILITY_ID'=>$facility_id])
				->where ( function ($q) use ($product_type) {
					if ($product_type != 0) {
						$q->where ( [
								'PRODUCT' => $product_type
						] );
					}
				})->get(['ID', 'NAME']);
				break;
					
			case "FLOW":
				$tmp = $model::where(['FACILITY_ID'=>$facility_id])
				->where ( function ($q) use ($product_type) {
					if ($product_type != 0) {
						$q->where ( [
								'PHASE_ID' => $product_type
						] );
					}
				})->get(['ID', 'NAME']);
				break;
					
			case "ENERGY_UNIT":
				$tableName = $model::getTableName ();
				$euPhaseConfig = EuPhaseConfig::getTableName ();
				$tmp = DB::table($tableName)
				->where(['FACILITY_ID'=>$facility_id])
				->whereNotExists(function($query) use ($euPhaseConfig, $product_type, $tableName){
					$query->select(DB::raw("$tableName.ID"))
					->from($euPhaseConfig)
					->whereRaw("$euPhaseConfig.EU_ID = $tableName.ID")
					->where(["$euPhaseConfig.FLOW_PHASE"=>$product_type]);
				})->get(['ID', 'NAME']);
				break;
			case "KEYSTORE_TANK":
            case "EQUIPMENT":
			case "KEYSTORE_STORAGE":
				$mdlName = \Helper::getModelName($table_name);
				$tmp = $mdlName::where(['FACILITY_ID'=>$facility_id])->get(['ID', 'NAME']);
				break;
		}
		//\Log::info ( \DB::getQueryLog () );
	
		return $tmp;
	}
	
	private function graphDataSource($obj_types){
		
		$tmp = GraphDataSource::where(['SOURCE_TYPE'=>$obj_types])->select('SOURCE_NAME AS ID', 'SOURCE_NAME AS NAME')->get();
		
		return $tmp;
	}
	
	public function getTableFields(Request $request){
		
		$data = $request->all ();			
		$tmp = Schema::getColumnListing($data['TABLE_NAME']);
		$result = [];
		foreach ($tmp as $t){
			array_push($result, $t);
		}
		return response ()->json ( $result );
	}
	
	public function getListPlotItems(){		
		$plot_view_config = $this->getPlotItems();
		
		return response ()->json ( $plot_view_config );
	}
	
	private function getPlotItems(){
		$originAttrCase 		= \Helper::setGetterUpperCase();
		$plot_view_config 		= PlotViewConfig::orderBy('NAME')->get(['ID', 'NAME', 'CONFIG', 'TIMELINE', 'CHART_TYPE']);
		\Helper::setGetterCase($originAttrCase);
		return $plot_view_config;
	}
	
	public function deletePlotItems(Request $request){
		$data = $request->all();
	
		PlotViewConfig::where(['ID'=>$data['ID']])->delete();
		
		$plot_view_config = $this->getPlotItems();
		
		return response ()->json ( $plot_view_config );
	}
	
	public function savePlotItems(Request $request){
		$data = $request->all();
	
		$id = $data['id'];
		$title = addslashes($data["title"]);
		$config = addslashes($data["config"]);
		$timeline = $data["timeline"];
		$charttype = "line";//$data["charttype"];
		
		$condition = array (
				'ID' => $id
		);
		
		$obj ['NAME'] = $title;
		$obj ['CONFIG'] = $config;
		$obj ['TIMELINE'] = $timeline;
		$obj ['CHART_TYPE'] = $charttype;
		
		//$result = PlotViewConfig::updateOrCreate ( $condition, $obj );
		$result = ($id == 0 || $id == -1) ? PlotViewConfig::firstOrCreate($obj) : PlotViewConfig::updateOrCreate ( $condition, $obj );
		$id = $result->ID;
	
		return response ()->json ( "ok:$id" );
	}

	public function genView(Request $request){
		$id=$_REQUEST["id"];
		$view_name=addslashes($_REQUEST["view_name"]);
		$overwrite_id=$_REQUEST["overwrite_id"];
		$stmp="";
		$sql_id="";
		$insert = 0;
		$mes = "";
		if($overwrite_id>0){
			$sql_id=$overwrite_id;
			$stmp="update sql_list set `NAME`='$view_name',`SQL`='@SQL', ENABLE=1 where id=$overwrite_id";
			$action = 2;
		}
		else{
			//$x=getOneValue("select id from sql_list where `NAME`='$view_name'");
			$sql_list = SqlList::where(['NAME'=>$view_name])->select('ID')->first();
			$x = $sql_list['ID'];
			if($x>0){
				$mes = "CONFIRM_OVERWRITE:$x";
				return response ()->json ( $mes );
				exit();
			}
			else{
				$stmp="insert into sql_list(`NAME`,`SQL`) values('$view_name','@SQL')";
				$action = 1;
			}
		}
		if($stmp){
			$s=$_REQUEST["config"];
			$sql="";
			if($s){
				$xs=explode(",",$s);
				$arr_nulls=array();
				for($i=0;$i<count($xs);$i++)
					$arr_nulls[$i]="null v$i";
					$i=0;
					$cmm="";
					$sel="";
					foreach($xs as $s){
						$ps=explode(":",$s);
						$table=$ps[3];
						$field=$ps[4];
						$objid=$ps[2];
						$flowphase=$ps[5];
						$params=explode("~",$ps[6]);
						$calc=$params[0];
						$table_p=$ps[1];
						$field_p=$table_p."_ID";
						if($table_p=="ENERGY_UNIT" || $table_p=="EU_TEST") $field_p="EU_ID";
						$label=$ps[7];
						if (strpos($label,'(') !== false)
							$label=substr($label,0,strpos($label,'('));
							$nulls=$arr_nulls;
							$nulls[$i]="$field v$i";
							$cmm.=($cmm?" union all ":"")."select occur_date,".join(",",$nulls)." from $table where `$field_p`=$objid and {OCCUR_DATE}".($flowphase?" and FLOW_PHASE=$flowphase":"");
							$sel.=",sum(v$i)$calc `$label`";
							$i++;
					}
					$sql="select x.occur_date `Occur date`$sel from ($cmm) x group by x.occur_date".(config('database.default')==='sqlsrv'?"":" order by x.occur_date");
			}
			
			if($action == 1){
				$sqlList = SqlList::insert(['NAME'=>$view_name,'SQL'=>addslashes($sql)]);
			}else if($action == 2){
				$sqlList = SqlList::where(['ID'=>$overwrite_id])->update(['NAME'=>$view_name,'SQL'=>addslashes($sql), 'ENABLE'=>1]);
			}
			
			if(!$sql_id){
				$sql_id = $sqlList['ID'];
			}
			
			$id = SqlConditionFilter::where(['SQL_ID'=>$sql_id])->get(['ID']);
			if(!$id){				
				SqlConditionFilter::insert(['SQL_ID'=>$sql_id, 'LABEL'=>'Occur date', 'FIELD_NAME'=>'OCCUR_DATE', 'FIELD_VALUE_TYPE'=>'DATE', 'IS_DATE_RANGE'=>1, 'IS_MANDATORY'=>1]);
			}		
		}
		
		return response ()->json ( $mes );
	}
}