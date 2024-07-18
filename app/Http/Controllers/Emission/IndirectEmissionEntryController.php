<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 01-Jun-17
 * Time: 3:10 PM
 */

namespace App\Http\Controllers\Emission;

use App\Http\Controllers\CodeController;
use App\Models\Flow;
use App\Models\CodeProtocol;
use App\Models\CodeEqpGhgRelType;
use App\Models\CodeGhgUom;
use App\Models\CodeReadingFrequency;

class IndirectEmissionEntryController extends CodeController
{
    public function __construct() {
        parent::__construct();
    }

    public function getFirstProperty($dcTable){
        return  ['data'=>$dcTable,'title'=>'','width'=>100];
    }

    public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties)
    {
        $mdlName                = $postData[config("constants.tabTable")];
        $mdl                    = "App\Models\\$mdlName";
        if($mdlName == "EmissionIndirectDataValue"){
            $date_end = array_key_exists('date_end', $postData) ? $postData['date_end'] : null;
            $date_end = $date_end && $date_end != "" ? \Helper::parseDate($date_end) : Carbon::now();

            $codeReadingFrequencyId   = $postData['CodeReadingFrequency']; // RECORD_FREQUENCY
            $codeFlowPhaseId          = $postData['CodeFlowPhase']; // PHASE_ID

            \Log::info("equipmentTypeId $codeReadingFrequencyId equipmentGroupId $codeFlowPhaseId");
            $flow 	            = Flow::getTableName();

            $columns	= $this->extractRespondColumns($dcTable,$properties);
            if (!$columns) $columns = [];
            array_push($columns,
                "$dcTable.ID as $dcTable",
                "$dcTable.ID as DT_RowId");

//     	    \DB::enableQueryLog();
            $dataSet = $mdl::join('flow',function ($query) use ($facility_id,$codeReadingFrequencyId, $codeFlowPhaseId, $dcTable, $flow){
                $query->on("$dcTable.FLOW_ID",'=',"$flow.ID")
                      ->where("$flow.FACILITY_ID",'=',$facility_id);
                if($codeReadingFrequencyId>0) $query->where("$flow.RECORD_FREQUENCY",'=',$codeReadingFrequencyId);
                if($codeFlowPhaseId>0) $query->where("$flow.PHASE_ID",'=',$codeFlowPhaseId);
            })
                ->whereDate("$dcTable.OCCUR_DATE",'<=',$date_end)
                ->whereDate("$dcTable.OCCUR_DATE",'>=',$occur_date)
                ->select($columns)
                ->orderBy("$dcTable")
                ->get();

        }else if($mdlName == "EmissionIndirRelDataValue"){
//            $dataSet = [];

            $code_reading_frequency = CodeReadingFrequency::getTableName();
            $code_protocol          = CodeProtocol::getTableName();
            $code_eqp_ghg_rel_type  = CodeEqpGhgRelType::getTableName();
            $code_ghg_uom           = CodeGhgUom::getTableName();

            $columns = $this->extractRespondColumns($dcTable,$properties);
            if (!$columns) $columns = [];
            array_push(
                $columns,
                "$dcTable.OCCUR_DATE",
                "$dcTable.ID",
                "$dcTable.ID as DT_RowId",
                "$dcTable.PARENT_ID as $dcTable"
            );

            $dataSet = $mdl::join('code_reading_frequency', "$dcTable.RECORD_FREQUENCY", '=', "$code_reading_frequency.id")
                            ->join('code_protocol', "$dcTable.PROTOCOL", '=', "$code_protocol.id")
                            ->join('code_eqp_ghg_rel_type', "$dcTable.EMISSION_GAS", '=', "$code_eqp_ghg_rel_type.id")
                            ->join('code_ghg_uom', "$dcTable.GRS_MASS_UOM", '=', "$code_ghg_uom.id")
                            ->where("$dcTable.PARENT_ID",'=',$postData['id'])
                            ->select($columns)
                            ->get();
        }
//        \Log::info("table $dcTable");
        return ['dataSet' => $dataSet,];
    }
}