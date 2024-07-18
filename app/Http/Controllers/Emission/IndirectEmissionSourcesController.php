<?php

namespace App\Http\Controllers\Emission;

use App\Http\Controllers\CodeController;
use App\Models\Flow;
use App\Models\RelEmiFormulaCalcOption;
use Illuminate\Http\Request;

class IndirectEmissionSourcesController extends CodeController
{
    public function __construct() {
        parent::__construct();
        $this->extraDataSetColumns = [

            // Parent
            'SOURCE_CATEGORY_ID'	=>	[	'column'	=>'SOURCE_CLASS_ID',
                                         'model'		=>'CodeSourceClass'],

            'SECTOR_ID'	            =>	[	'column'	=>'SEGMENT_ID',
                                            'model'		=>'CodeSegment'],
            // Child
            'CALC_SECTION_ID'	    =>	[	'column'	=>'CALC_OPTION_ID',
                                            'model'		=>'CodeCalcOption'],

            'CALC_OPTION_ID'	    =>	[	'column'	=>'EMISSION_FORMULA_ID',
                                            'model'		=>'EmissionFormula'],
//
//            'EMISSION_FORMULA_ID'	    =>	[	'column'	=>'EMISSION_FACTOR_TABLE_ID',
//                                            'model'		=>'EmissionFactorTable']
        ];

        $this->keyColumns = [$this->idColumn,$this->phaseColumn];
    }

    public function getFirstProperty($dcTable){
        return  ['data'=>$dcTable,'title'=>'','width'=>100];
    }

    public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties)
    {
        $mdlName = $postData[config("constants.tabTable")];
        $mdl = "App\Models\\$mdlName";
        $extraDataSet	= [];

        if ($mdlName == "IndirectEmissionGroup") {
            $date_end = array_key_exists('date_end', $postData) ? $postData['date_end'] : null;
            $date_end = $date_end && $date_end != "" ? \Helper::parseDate($date_end) : Carbon::now();

            $codeReadingFrequencyId   = $postData['CodeReadingFrequency']; // RECORD_FREQUENCY
            $codeFlowPhaseId          = $postData['CodeFlowPhase']; // PHASE_ID

            \Log::info("equipmentTypeId $codeReadingFrequencyId equipmentGroupId $codeFlowPhaseId");
            $flow 	            = Flow::getTableName();

            $dataSet = $mdl::join('flow', function ($query) use ($dcTable,$codeReadingFrequencyId, $codeFlowPhaseId,$flow,$facility_id){
                $query->on("$dcTable.FLOW_ID",'=',"$flow.ID")
                    ->where("$flow.FACILITY_ID",'=',$facility_id);
                if($codeReadingFrequencyId>0) $query->where("$flow.RECORD_FREQUENCY",'=',$codeReadingFrequencyId);
                if($codeFlowPhaseId>0) $query->where("$flow.PHASE_ID",'=',$codeFlowPhaseId);
            })
                ->whereDate("$dcTable.EFFECTIVE_DATE", '<=', $date_end)
                ->whereDate("$dcTable.EFFECTIVE_DATE", '>=', $occur_date)
                ->select(
                    "$dcTable.*",
                    "$dcTable.ID as $dcTable",
                    "$dcTable.ID as DT_RowId"
                )
                ->orderBy("$dcTable")
                ->get();

            $extraDataSet 	= $this->getExtraDataSet($dataSet, null);

        } else if ($mdlName == "IndirectEmissionCalcMethod") {
//            $dataSet = '';
            $columns = $this->extractRespondColumns($dcTable,$properties);
            if (!$columns) $columns = [];
            array_push(
                $columns,
                "$dcTable.INDIRECT_EMISSION_GROUP_ID",
                "$dcTable.ID",
                "$dcTable.ID as DT_RowId",
                "$dcTable.INDIRECT_EMISSION_GROUP_ID as $dcTable"
            );
            $dataSet = $mdl::where("$dcTable.INDIRECT_EMISSION_GROUP_ID",'=',$postData['id'])
                ->select($columns)
                ->get();

            $extraDataSet 	= $this->getExtraDataSet($dataSet, null);
        }
        return ['dataSet' => $dataSet,
            'extraDataSet'	=>$extraDataSet];
    }


    public function loadsrc(Request $request){
        $postData = $request->all();
        $sourceColumn = $postData['name'];
        $sourceColumnValue = $postData['value'];
        $bunde = [];
        $dataSet = [];
        $loopIndex = 0;
        while($loopIndex<5){
            $loopIndex++;
            if (!array_key_exists($sourceColumn, $this->extraDataSetColumns)) break;
            $extraDataSetColumn = $this->extraDataSetColumns[$sourceColumn];
            $targetColumn = $extraDataSetColumn['column'];
            $data = $this->loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde);
            $dataSet[$targetColumn] = [
                'data'			=>	$data,
                'ofId'			=>	$sourceColumnValue,
                'sourceColumn'	=>	$sourceColumn
            ];

            $sourceColumn = $targetColumn;
            $sourceColumnValue = $data&&$data->count()>0?$data[0]->ID:null;
            if (!$sourceColumnValue) break;
        }
        return response()->json(['dataSet'=>$dataSet,
            'postData'=>$postData]);
    }

    public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde){
        $data = null;
        $isOracle		= config('database.default')==='oracle';
        $field			= $isOracle?'NAME as "text"':'NAME as text';
        switch ($sourceColumn) {
            case 'SOURCE_CATEGORY_ID':
                $targetModel = $extraDataSetColumn['model'];
                $targetEloquent = "App\Models\\$targetModel";
                $data = $targetEloquent::where('SOURCE_CATEGORY_ID','=',$sourceColumnValue)
                    ->select(
                        "ID","NAME",
                        "ID as value",
                        $field
                    )
                    ->get();
                break;

            case 'SECTOR_ID':
                $targetModel = $extraDataSetColumn['model'];
                $targetEloquent = "App\Models\\$targetModel";
                $data = $targetEloquent::where('SECTOR_ID','=',$sourceColumnValue)
                    ->select(
                        "ID","NAME",
                        "ID as value",
                        $field
                    )
                    ->get();
                break;

            case 'CALC_SECTION_ID':
                $targetModel = $extraDataSetColumn['model'];
                $targetEloquent = "App\Models\\$targetModel";
                $data = $targetEloquent::where('CALC_SECTION_ID','=',$sourceColumnValue)
                    ->select(
                        "ID","NAME",
                        "ID as value",
                        $field
                    )
                    ->get();
                break;

            case 'CALC_OPTION_ID':
                $targetModel = $extraDataSetColumn['model'];
                $targetEloquent = "App\Models\\$targetModel";
                $rel_emi = RelEmiFormulaCalcOption::getTableName();
                $emission_formula = $targetEloquent::getTableName();

                $data = $targetEloquent::join($rel_emi,function($query) use ($sourceColumnValue,$rel_emi,$emission_formula){
                    $query->on("$emission_formula.ID",'=' ,"$rel_emi.EMISSION_FORMULA_ID")
                        ->where("$rel_emi.CODE_CALC_OPTION_ID",'=',$sourceColumnValue);
                })
                    ->select(
                        "$emission_formula.ID",
                        "$emission_formula.NAME",
                        "$emission_formula.ID as value",
                        $field
                    )
                    ->get();
                break;
        }
        return $data;
    }
}