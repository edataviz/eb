<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 19-May-17
 * Time: 2:44 PM
 */

namespace App\Http\Controllers\Emission;

use App\Http\Controllers\CodeController;
use App\Models\CodeMassUom;
use App\Models\CodeProtocol;
use App\Models\Flow;
use App\Models\PdCargoNomination;
use App\Models\Storage;
use App\Models\Equipment;
use App\Models\EmissionCombDataValue;
use App\Models\CodeGhgUom;
use App\Models\CodeEqpFuelConsType;
use Carbon\Carbon;
use App\Models\CodeEqpGhgRelType;


class CombustionEmissionEntryController extends CodeController
{
    public function __construct() {
        parent::__construct();
        $this->detailModel = "Equipment";
    }

    public function getFirstProperty($dcTable){
        return  ['data'=>$dcTable,'title'=>'','width'=>100];
    }

    public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties)
    {
        $mdlName                = $postData[config("constants.tabTable")];
        $mdl                    = "App\Models\\$mdlName";
        if($mdlName == "EmissionCombDataValue"){
            $date_end = array_key_exists('date_end', $postData) ? $postData['date_end'] : null;
            $date_end = $date_end && $date_end != "" ? \Helper::parseDate($date_end) : Carbon::now();

            $equipmentGroupId   = $postData['EquipmentGroup'];
            $equipmentTypeId    = $postData['CodeEquipmentType'];

            \Log::info("equipmentTypeId $equipmentTypeId equipmentGroupId $equipmentGroupId");
            $equipment 	        = Equipment::getTableName();
            $fow                = Flow::getTableName();

//     	\DB::enableQueryLog();
            // new code
            $dataSet = $mdl::join('equipment',function ($query) use ($equipmentGroupId,$equipmentTypeId,
                $dcTable,$equipment,$fow,$facility_id){
                $query->on("$dcTable.EQUIPMENT_ID",'=',"$equipment.ID")
                      ->where("$equipment.FACILITY_ID",'=',$facility_id);
                if($equipmentGroupId>0) $query->where("$equipment.EQUIPMENT_GROUP",'=',$equipmentGroupId);
                if($equipmentTypeId>0) $query->where("$equipment.EQUIPMENT_TYPE",'=',$equipmentTypeId);
            })
                ->whereDate("$dcTable.OCCUR_DATE",'<=',$date_end)
                ->whereDate("$dcTable.OCCUR_DATE",'>=',$occur_date)
                ->select(
                    "$dcTable.*",
                    "$dcTable.ID as $dcTable",
                    "$dcTable.ID as DT_RowId"
                )
                ->orderBy("$dcTable")
                ->get();

        }else if($mdlName == "EmissionCombRelDataValue"){
//            $dataSet = [];
            $code_eqp_ghg_rel_type = CodeEqpGhgRelType::getTableName();
            $code_mass_uom = CodeMassUom::getTableName();
            $code_protocol = CodeProtocol::getTableName();

            $columns = $this->extractRespondColumns($dcTable,$properties);
            if (!$columns) $columns = [];
            array_push(
                $columns,
                "$dcTable.OCCUR_DATE",
                "$dcTable.ID",
                "$dcTable.ID as DT_RowId",
                "$dcTable.PARENT_ID as $dcTable"
            );

            $dataSet = $mdl::join('code_eqp_ghg_rel_type', "$dcTable.EMISSION_GAS", '=', "$code_eqp_ghg_rel_type.id")
                            ->join('code_mass_uom', "$dcTable.GRS_MASS_UOM", '=', "$code_mass_uom.id")
                            ->join('code_protocol', "$dcTable.PROTOCOL", '=', "$code_protocol.id")
                            ->where("$dcTable.PARENT_ID",'=',$postData['id'])
                            ->select($columns)
                            ->get();
        }
//        \Log::info("table $dcTable");
        return ['dataSet' => $dataSet,];
    }
}