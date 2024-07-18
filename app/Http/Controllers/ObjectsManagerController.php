<?php
namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Models\LoProductionUnit;
use App\Models\LoArea;
use App\Models\Facility;
use App\Models\EnergyUnit;
use App\Models\Flow;
use App\Models\Tank;
use App\Models\Storage;
use App\Models\Equipment;

class ObjectsManagerController extends EBController {
	
	
	public function _index() {
		\Helper::setGetterUpperCase();
		$production_units = LoProductionUnit::all('ID','NAME','MAP_INFO','ID as PARENT_ID')->toArray();
		$areas = LoArea::all('ID','NAME','PRODUCTION_UNIT_ID as PARENT_ID','MAP_INFO')->toArray();
		$facilities = Facility::all('ID','NAME','AREA_ID as PARENT_ID','MAP_INFO')->toArray();
		$energy_units = EnergyUnit::all('ID','NAME','FACILITY_ID as PARENT_ID','MAP_INFO')->toArray();
		$flows = Flow::all('ID','NAME','FACILITY_ID as PARENT_ID','MAP_INFO')->toArray();
		$tanks = Tank::all('ID','NAME','FACILITY_ID as PARENT_ID','MAP_INFO')->toArray();
		$storages = Storage::all('ID','NAME','FACILITY_ID as PARENT_ID','MAP_INFO')->toArray();
		$equipments = Equipment::all('ID','NAME','FACILITY_ID as PARENT_ID','MAP_INFO')->toArray();
		return view ( 'front.objectsmanager',[
				'all_objects'	=> [
					"pu" 		=> ["TITLE" => "Production Unit",	"DATA" => $production_units],
					"area" 		=> ["TITLE" => "Area",			"DATA" => $areas],
					"facility" 	=> ["TITLE" => "Facility",		"DATA" => $facilities],
					"eu" 		=> ["TITLE" => "Energy Unit",	"DATA" => $energy_units],
					"flow" 		=> ["TITLE" => "Flow", 			"DATA" => $flows],
					"tank" 		=> ["TITLE" => "Tank", 			"DATA" => $tanks],
					"storage" 	=> ["TITLE" => "Storage", 		"DATA" => $storages],
					"equipment"	=> ["TITLE" => "Equipment", 	"DATA" => $equipments],
				],
		]);
	}
	
	public function saveMapInfo(Request $request){
		$vdata = $request->all ();
		$tableName = $vdata['table'];
		$objId = $vdata['obj_id'];		
		$mapInfo = $vdata['map_info'];
		
		$mdlName = \Helper::camelize($tableName,'_');
		$mdl = "App\Models\\$mdlName";
		$mdl::where(['ID'=>$objId])->update(['MAP_INFO'=>$mapInfo]);
		return response ()->json ( 'ok' );
	}		
	
	private function getFields($table) {
		//\DB::enableQueryLog ();
		$tmps = DB::table ('INFORMATION_SCHEMA.COLUMNS')
		->where ( ['TABLE_NAME' => $table] )
		->distinct ()
		->select ('COLUMN_NAME')->get();
		//\Log::info ( \DB::getQueryLog () );
		
		return $tmps;
	}
}
