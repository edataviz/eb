<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 01-Jun-17
 * Time: 3:11 PM
 */
namespace App\Http\Controllers\Emission;

use App\Http\Controllers\QualityController;
use App\Models\CodeEqpGhgRelType;
use App\Models\CodeGhgUom;
use App\Models\CodeProtocol;
use App\Models\CodeReadingFrequency;
use App\Models\Storage;
use App\Models\PdVoyageDetail;
use App\Models\PdVoyage;


class EventsEmissionEntryController extends QualityController {

    public function getFirstProperty($dcTable){
        return  ['data'=>$dcTable,'title'=>'','width'=>100];
    }

    public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties)
    {
        $mdlName                = $postData[config("constants.tabTable")];
        $mdl                    = "App\Models\\$mdlName";
        $extraDataSet           = [];
        if($mdlName == "EmissionEventDataValue"){
            $date_end = array_key_exists('date_end', $postData) ? $postData['date_end'] : null;
            $date_end = $date_end && $date_end != "" ? \Helper::parseDate($date_end) : Carbon::now();

//            \Log::info($dataSet);

            $dataSet = null;

            $uoms = $properties['uoms'];
            $sourceTypekey = array_search('CodeQltySrcType', array_column($uoms, 'id'));
            $sourceTypes = $uoms[$sourceTypekey]['data'];
            $objectType = null;

            $columns	= $this->extractRespondColumns($dcTable,$properties);
            if (!$columns) $columns = [];
            array_push($columns,"$dcTable.ID as $dcTable",
                "$dcTable.ID",
                "$dcTable.ID as DT_RowId");

            $src_type_ids = [1,2,3,4,5,6];
            $isOracle	= config('database.default')==='oracle';
            if ($isOracle)
                $oQueries 	= [];
            else
                $query 		= null;
            // 	    \DB::enableQueryLog();
            foreach($src_type_ids as $srcTypeId ){
                $where = ['OBJECT_TYPE' => $srcTypeId];
                switch ($srcTypeId) {
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                        $objectType = $sourceTypes->find($srcTypeId);
                        $objectType = $objectType->CODE;
                        $cquery = $mdl::join($objectType,function ($query) use ($objectType,$facility_id,$dcTable) {
                            $query->on("$objectType.ID",'=',"$dcTable.OBJECT_ID")
                                  ->where("$objectType.FACILITY_ID",'=',$facility_id);
                        })
                            ->where($where)
                            ->whereDate("$dcTable.BEGIN_DATE", '>=', $occur_date)
                            ->whereDate("$dcTable.BEGIN_DATE", '<=', $date_end)
                            ->select($columns)
                            ->orderBy($dcTable);
                        if ($isOracle)
                            $oQueries[] = $cquery;
                        else
                            $query = $query==null?$cquery:$query->union($cquery);
                        break;
                    case 5:
                        $storage = Storage::getTableName();
                        $pdVoyageDetail = PdVoyageDetail::getTableName();
                        $pdVoyage = PdVoyage::getTableName();

                        $cquery = $mdl::join($pdVoyageDetail, "$dcTable.OBJECT_ID", '=', "$pdVoyageDetail.ID")
                            ->join($pdVoyage, "$pdVoyageDetail.VOYAGE_ID", '=', "$pdVoyage.ID")
                            ->join($storage,function ($query) use ($storage,$facility_id,$pdVoyage) {
                                $query->on("$storage.ID",'=',"$pdVoyage.STORAGE_ID")
                                    ->where("$storage.FACILITY_ID",'=',$facility_id) ;
                            })
                            ->where($where)
                            ->whereDate("$dcTable.BEGIN_DATE", '>=', $occur_date)
                            ->whereDate("$dcTable.BEGIN_DATE", '<=', $date_end)
                            ->select($columns)
                            ->orderBy($dcTable);
                        if ($isOracle)
                            $oQueries[] = $cquery;
                        else
                            $query = $query==null?$cquery:$query->union($cquery);
                        break;
                    case 6:
                        $cquery = $mdl::where($where)
                                        ->whereDate("$dcTable.BEGIN_DATE", '>=', $occur_date)
                                        ->whereDate("$dcTable.BEGIN_DATE", '<=', $date_end)
                                        ->select($columns)
                                        ->orderBy($dcTable);
                        if ($isOracle)
                            $oQueries[] = $cquery;
                        else
                            $query = $query==null?$cquery:$query->union($cquery);
                        break;
                }
            }

            if ($isOracle){
                if (count($oQueries)>0) {
                    foreach($oQueries as $key => $oQuery ){
                        if($dataSet) {
// 						$dataSet	= $dataSet->merge($oQuery->get());
                            $gData		= $oQuery->get();
                            if ($gData) {
                                foreach ($gData as $item){
                                    $dataSet->add($item);
                                }
                            }
                        }
                        else $dataSet 			= $oQuery->get();
                    }
                }
            }
            else{
                if ($query!=null) $dataSet= $query->get();
            }

            // child

            $sourceColumn = 'OBJECT_TYPE';
            if ($dataSet&&$dataSet->count()>0) {

//     			\DB::enableQueryLog();
                $bySrcTypes = $dataSet->groupBy('OBJECT_ID');
// 				\Log::info(\DB::getQueryLog());
                if ($bySrcTypes) {
                    $extraDataSet[$sourceColumn]= [];
                    foreach($bySrcTypes as $key => $srcType ){
                        $srcTypeID = $srcType[0]->OBJECT_TYPE;
                        $table = $sourceTypes->find($srcTypeID);
                        $table = $table->CODE;
                        $srcTypeData = $this->getExtraDatasetBy($table,$facility_id);
                        if ($srcTypeData) {
                            $extraDataSet[$sourceColumn][$srcTypeID] = $srcTypeData;
                        }
                    }
                }
            }

        }else if($mdlName == "EmissionEventRelDataValue"){

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
        return ['dataSet'=>$dataSet,
            'extraDataSet'=>$extraDataSet
        ];
    }
}