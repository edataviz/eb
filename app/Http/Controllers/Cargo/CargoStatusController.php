<?php
namespace App\Http\Controllers\Cargo;

use App\Models\PdCargoNomination;
use App\Models\PdCargo;
use App\Models\TerminalTimesheetData;
use Illuminate\Http\Request;

class CargoStatusController extends CargoEntryController {
    
    public function getFirstProperty($dcTable){
		return  ['data'=>'ID','title'=>'','width'=>60];
	}
	
	public function isLocked($dcTable,$occur_date,$facility_id){
		return true;
	}
	
	public function loadDetail(Request $request){
		$postData 				= $request->all();
		$id 					= $postData['id'];
		$tabs 					= $postData['tabs'];
		$response				= [];
		foreach ($tabs as $tab){
			$rTab 				= $tab;
			$tab 				= ($tab=="PdCargoEntry")?"PdCargo":$tab;
			$results			= $this->loadCargoDetail($tab,$id,$postData);
			$response[$rTab]	= $results;
		}
		return response()->json($response);
	}
	
	public function loadCargoDetail($tab,$id,$postData){
		$detailModel			= "App\Models\\$tab";
		$columnModel			= $tab=="PdCargoLoad"||$tab=="PdCargoUnload"?"App\Models\TerminalTimesheetData":$detailModel;
		$detailTable	 		= $detailModel::getTableName();
		$columnTable	 		= $columnModel::getTableName();
		$results 				= $this->getProperties($columnTable,null,null,$postData);
		$dataSet				= [];
		switch ($tab) {
			case "PdCargoNomination":
			case "PdCargoEntry":
			case "PdVoyageDetail":
			case "PdTransportShipDetail":
			case "PdTransportGroundDetail":
			case "PdTransportPipelineDetail":
			case "ShipCargoBlmr":
				$originAttrCase = \Helper::setGetterUpperCase();
				$dataSet 		= $detailModel::where("CARGO_ID",$id)
												->select(
									    			"$detailTable.ID as $detailTable",
									    			"$detailTable.ID as DT_RowId",
									    			"$detailTable.*")
								    			->get();
				\Helper::setGetterCase($originAttrCase);
				break;
			case "PdCargoLoad":
			case "PdCargoUnload":
				$terminalTimesheetData 			= TerminalTimesheetData::getTableName();
				$isLoad							= $tab=="PdCargoLoad";
				$originAttrCase = \Helper::setGetterUpperCase();
				$dataSet = $detailModel::join($terminalTimesheetData,
											function($join) use ($detailTable,$terminalTimesheetData,$isLoad){
												$join->on("$terminalTimesheetData.PARENT_ID",'=',"$detailTable.ID");
												if ($isLoad) $join->where("$terminalTimesheetData.IS_LOAD",'=',1);
												else {
													$join->where("$terminalTimesheetData.IS_LOAD",'=',0)
														->orWhere(function ($query) use ($terminalTimesheetData) {
														                $query->whereNull("$terminalTimesheetData.IS_LOAD");
														            });
												}
											}
										)
										->where("$detailTable.CARGO_ID",'=',$id)
										->select(
												"$terminalTimesheetData.ID as $detailTable",
												"$terminalTimesheetData.ID as DT_RowId",
												"$terminalTimesheetData.*")
										->get();
				\Helper::setGetterCase($originAttrCase);
				break;
			default:
				;
			break;
		}
		$results['dataSet'] 	= $dataSet;
		
		return  $results;
	}
}
