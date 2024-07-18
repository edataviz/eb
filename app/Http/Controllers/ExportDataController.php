<?php

namespace App\Http\Controllers;

use App\Models\CfgFieldProps;
use App\Models\CodeAllocType;
use App\Models\CodeCommentCategory;
use App\Models\CodeCommentType;
use App\Models\CodeDeferCategory;
use App\Models\CodeDeferCode1;
use App\Models\CodeDeferCode2;
use App\Models\CodeDeferCode3;
use App\Models\CodeDeferGroupType;
use App\Models\CodeDeferPlan;
use App\Models\CodeDeferStatus;
use App\Models\CodeEnvCategory;
use App\Models\CodeEnvType;
use App\Models\CodeEventType;
use App\Models\CodeFlowPhase;
use App\Models\CodePlanType;
use App\Models\CodeQltySrcType;
use App\Models\CodeTestingMethod;
use App\Models\CodeTestingUsage;
use App\Models\DefermentGroup;
use App\Models\EnergyUnit;
use App\Models\Equipment;
use App\Models\Flow;
use App\Models\Keystore;
use App\Models\KeystoreInjectionPoint;
use App\Models\KeystoreStorage;
use App\Models\KeystoreTank;
use App\Models\Storage;
use App\Models\Tank;
use Excel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportDataController extends CodeController
{
    public function exportDataExcel(Request $request){
        $postData 		= $request->all();
        $facility_id    = $postData['FACILITY'];
        $date_begin     = date("Y-m-d", strtotime($postData['DATE_BEGIN']));
        $date_end       = date("Y-m-d", strtotime($postData['DATE_END']));
        $obj_type       = $postData['OBJ_TYPE'];
        $select_column  = $postData['SELECT_COLUMN'];
        $name_table     = $postData['NAME_TABLE'];
        $where_id       = $postData['WHERE_ID'];
        $where_flow     = $postData['WHERE_FLOW'];
        $where_event    = $postData['WHERE_EVENT'];
        $where_alloc    = (isset($postData['WHERE_ALLOC'])) ? $postData['WHERE_ALLOC'] : [];
        $string         = $postData['STRING'];
        $mdl            = \Helper::getModelName($name_table);
        $column         = [];

        $code_flow_phase = CodeFlowPhase::getTableName();
        $code_event_type = CodeEventType::getTableName();
        $code_alloc_type = CodeAllocType::getTableName();

        $renderType = $postData['RENDER_TYPE'];

        $alpha = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $a = [];
        $c = [];
        $c = [];
        $d = [];
        foreach ($alpha as $val){
            $a[] = "A".$val;
            $b[] = "B".$val;
            $c[] = "C".$val;
            $d[] = "D".$val;
        }
        $alphabet = array_merge($alpha,$a,$b,$c,$d);

        switch ($obj_type) {
            case "FLOW":
                $where_in = "FLOW_ID";
                $table_parent = Flow::getTableName();
                break;
            case "ENERGY_UNIT":
                $where_in = "EU_ID";
                $table_parent = EnergyUnit::getTableName();
                break;
            case "TANK":
                $where_in = "TANK_ID";
                $table_parent = Tank::getTableName();
                break;
            case "STORAGE":
                $where_in = "STORAGE_ID";
                $table_parent = Storage::getTableName();
                break;
            case "EU_TEST":
                $where_in = "EU_ID";
                $table_parent = EnergyUnit::getTableName();
                break;
            case "KEYSTORE":
                $where_in = "";
                $table_parent = Keystore::getTableName();
                break;
        }

        $column[] = $name_table."."."OCCUR_DATE as DATETIME";
        $column[] = $table_parent."."."NAME as OBJECT_NAME";
        if ($obj_type == "ENERGY_UNIT" || $obj_type == "EU_TEST" && $renderType == 1 ){
            $column[]= $code_flow_phase."."."NAME as FLOW_PHASE";
            $column[]= $code_event_type."."."NAME as EVENT_TYPE";
            if ($name_table == "ENERGY_UNIT_DATA_ALLOC") $column[]= $code_alloc_type."."."NAME as ALLOC_TYPE";
        }
        foreach ($select_column as $value){
            $column[] = $name_table.".".$value;
        }

        $sql = $mdl::join($table_parent, "$name_table.$where_in",'=',"$table_parent.ID");
            if ($obj_type == "ENERGY_UNIT" || $obj_type == "EU_TEST"){
                 $sql->join($code_flow_phase, "$name_table.FLOW_PHASE", '=', "$code_flow_phase.ID")
                     ->join($code_event_type, "$name_table.EVENT_TYPE", '=', "$code_event_type.ID");
                 if ($name_table == "ENERGY_UNIT_DATA_ALLOC") $sql->join($code_alloc_type, "$name_table.ALLOC_TYPE", '=', "$code_alloc_type.ID");
                 $sql->whereIn("$name_table.FLOW_PHASE",$where_flow)
                     ->whereIn("$name_table.EVENT_TYPE",$where_event);
                 if ($name_table == "ENERGY_UNIT_DATA_ALLOC") $sql->whereIn("$name_table.ALLOC_TYPE",$where_alloc);
            }
            $sql->where("$table_parent.FACILITY_ID",'=',$facility_id)
            ->whereIn("$name_table.$where_in",$where_id)
            ->whereDate("$name_table.OCCUR_DATE",'<=',$date_end)
            ->whereDate("$name_table.OCCUR_DATE",'>=',$date_begin)
            ->select($column)
            ->orderBy("$name_table.OCCUR_DATE")
            ->orderBy("$table_parent.NAME");
            if ($obj_type == "ENERGY_UNIT" || $obj_type == "EU_TEST") $sql->orderBy("$name_table.FLOW_PHASE")->orderBy("$name_table.EVENT_TYPE");
        $dataSet = $sql->get();

        $data = [];
        $headerData = [];
        $childHeader = [];
        //$renderType = $postData['RENDER_TYPE'];
        if($renderType==1){
            foreach ($dataSet as $t){
                $t = collect($t)->toArray();
                array_push($data, $t);
            }
        }
        else{
            $groupedOccurDate = $dataSet->groupBy('DATETIME');
            $headerData[] = ($obj_type == "ENERGY_UNIT" || $obj_type == "EU_TEST") ? $string : '';
            $headerAvailable = true;
            foreach ($groupedOccurDate as $date => $dataByDates){
                $row = [];
                $row['OCCUR_DATE'] = $date;
                $childHeader['OCCUR_DATE'] = 'OCCUR_DATE';
                $groupedObjectName    = $dataByDates->groupBy('OBJECT_NAME');
                foreach ($groupedObjectName as $objectName => $values){
                    if($headerAvailable) $hvalues = [];

                    foreach ($select_column as $column){
                        if($headerAvailable) $hvalues[$column] = $objectName;
                        $entry = $values[0];
                        $row["$objectName.$column"] = $entry->{$column};
                        $childHeader["$objectName.$column"] = $column;
                    }
                    if($headerAvailable){
                        $hvalues = array_values($hvalues);
                        $headerData = array_merge($headerData, $hvalues);
                    }
                }
                $headerAvailable = false;
                array_push($data, $row);
            }
        }

        if (count($data) > 0){
            $count_col = count($select_column);
            $myFile= Excel::create('export', function($excel) use ($data,$renderType,$headerData,$alphabet,$count_col,$childHeader) {
                $excel->sheet('Data', function($sheet) use ($data,$renderType,$headerData,$alphabet,$count_col,$childHeader){
                    $renderData = $data;
                    if(count($data) > 0){
                        $header_cols = [];
                        if($renderType==1){
                            foreach($data[0] as $key => $value)
                                $header_cols[] = $key;
                            $sheet->prependRow($header_cols);
                        }
                        else{
                            /*foreach($data[0] as $key => $value)
                                $header_cols[] = $key;*/
                            $sheet->prependRow($header_cols);
                            $sheet->prependRow($childHeader);
                            $sheet->prependRow($headerData);
                            $union = array_values(array_unique($headerData));
                            for ($i=1 ; $i<=count($union); $i++){
                                $before = $alphabet[$i*$count_col-$count_col+1]."1";
                                $after  = $alphabet[$i*$count_col]."1";
                                $sheet->mergeCells($before.":".$after);
                                $sheet->getStyle($before.":".$after)->getAlignment()->applyFromArray(
                                    array('horizontal' => 'center')
                                );
                            }
                        }
                    }
                    /*$sheet->prependRow($childHeader);
                    $sheet->prependRow($headerData);
                    $union = array_values(array_unique($headerData));
                    for ($i=1 ; $i<=count($union); $i++){
                        $before = $alphabet[$i*$count_col-$count_col+1]."1";
                        $after  = $alphabet[$i*$count_col]."1";
                        $sheet->mergeCells($before.":".$after);
                        $sheet->getStyle($before.":".$after)->getAlignment()->applyFromArray(
                            array('horizontal' => 'center')
                        );
                    }*/
                    $sheet->rows($renderData);
                });
            });

            $myFile = $myFile->string('xlsx'); //change xlsx for the format you want, default is xls
            $response =  array(
                'name' => $name_table, //no extention needed
                'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($myFile) //mime type of used format
            );
        }else{
            $response = [];
        }
        return response()->json($response);
    }

    public function exportDataExcelNew($str){
        $postData       = explode(".",$str);
        $renderType     = $postData[0];
        $facility_id    = $postData[1];
        $date_begin     = $postData[2];
        $date_end       = $postData[3];
        $obj_type       = $postData[4];
        $select_column  = explode(",",$postData[5]);
        $name_table     = $postData[6];
        $where_id       = explode(",",$postData[7]);
        $where_flow     = explode(",",$postData[8]);
        $where_event    = explode(",",$postData[9]);
        $where_alloc    = (isset($postData[10])) ? explode(",",$postData[10]) : [];
        $string         = $postData[11];
//         $where_test     = (isset($postData[12])&&$postData[12]) ? explode(",",$postData[12]) : null;
        $where_test 	= null;
        $text_obj       = $postData[13];
        $def_group_type = (isset($postData[14])) ? explode(",",$postData[14]) : [];
        $plan_type_id   = (isset($postData[15])) ? explode(",",$postData[15]) : [];
        $lable          = [];
        if($name_table != "COMMENTS" && $name_table != "ENVIRONMENTAL" && $name_table != "LOGISTIC" && $obj_type != "DEFERMENT") $lable[] = "Date Time";
        if($name_table != "LOGISTIC" && $name_table != "DEFERMENT") $lable[] = $text_obj;

        $mdl            = \Helper::getModelName($name_table);
        $column         = [];

        if ($obj_type == "DEFERMENT"){
            $date_begin     = $date_begin." 00:00:00";
            $date_end       = $date_end." 23:59:59";
        }

        $code_flow_phase = CodeFlowPhase::getTableName();
        $code_event_type = CodeEventType::getTableName();
        $code_alloc_type = CodeAllocType::getTableName();
        $code_testing = CodeTestingMethod::getTableName();
        $code_dgt = CodeDeferGroupType::getTableName();
        $code_comment_cate = CodeCommentCategory::getTableName();
        $code_env_cate = CodeEnvCategory::getTableName();
        $code_defer_plan = CodeDeferPlan::getTableName();
        $code_defer_code1 = CodeDeferCode1::getTableName();
        $code_defer_code2 = CodeDeferCode2::getTableName();
        $code_defer_code3 = CodeDeferCode3::getTableName();
        $code_defer_status = CodeDeferStatus::getTableName();
        $code_defer_category = CodeDeferCategory::getTableName();
        $code_plan_type = CodePlanType::getTableName();
        $code_testing_usage = CodeTestingUsage::getTableName();

        $alpha = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $a = [];
        $c = [];
        $c = [];
        $d = [];
        $e = [];
        $f = [];
        $g = [];
        $h = [];
        $i = [];
        $j = [];
        $k = [];
        $l = [];
        $m = [];
        $n = [];
        $o = [];
        $p = [];
        $q = [];
        $r = [];
        $s = [];
        $t = [];
        $u = [];
        $v = [];
        $w = [];
        $x = [];
        $y = [];
        $z = [];

        foreach ($alpha as $val){
            $a[] = "A".$val;
            $b[] = "B".$val;
            $c[] = "C".$val;
            $d[] = "D".$val;
            $e[] = "E".$val;
            $f[] = "F".$val;
            $g[] = "G".$val;
            $h[] = "H".$val;
            $i[] = "I".$val;
            $j[] = "J".$val;
            $k[] = "K".$val;
            $l[] = "L".$val;
            $m[] = "M".$val;
            $n[] = "N".$val;
            $o[] = "O".$val;
            $p[] = "P".$val;
            $q[] = "Q".$val;
            $r[] = "R".$val;
            $s[] = "S".$val;
            $t[] = "T".$val;
            $u[] = "U".$val;
            $v[] = "V".$val;
            $w[] = "W".$val;
            $x[] = "X".$val;
            $y[] = "Y".$val;
            $z[] = "Z".$val;
        }
        $alphabet = array_merge($alpha,$a,$b,$c,$d,$e,$f,$g,$h,$i,$j,$k,$l,$m,$n,$o,$p,$q,$r,$s,$t,$u,$v,$w,$x,$y,$z);

        switch ($obj_type) {
            case "FLOW":
                $where_in = "FLOW_ID";
                $table_parent = Flow::getTableName();
                $date1 = "OCCUR_DATE";
                break;
            case "ENERGY_UNIT":
                $where_in = "EU_ID";
                $table_parent = EnergyUnit::getTableName();
                $date1 = "OCCUR_DATE";
                break;
            case "TANK":
                $where_in = "TANK_ID";
                $table_parent = Tank::getTableName();
                $date1 = "OCCUR_DATE";
                break;
            case "STORAGE":
                $where_in = "STORAGE_ID";
                $table_parent = Storage::getTableName();
                $date1 = "OCCUR_DATE";
                break;
            case "COMMENTS":
                $where_in = "COMMENT_TYPE";
                $table_parent = CodeCommentType::getTableName();
                $date1 = "CREATED_DATE";
                $cate_col = "COMMENT_CATEGORY";
                $mdl2 = \Helper::getModelName("CODE_COMMENT_CATEGORY");
                $mdl3 = \Helper::getModelName("CODE_COMMENT_STATUS");
                $column[] = $code_comment_cate."."."NAME as OBJECT_NAME";
                break;
            case "ENVIRONMENTAL":
                $where_in = "ENV_TYPE";
                $table_parent = CodeEnvType::getTableName();
                $date1 = "CREATED_DATE";
                $cate_col = "ENV_CATEGORY";
                $mdl2 = \Helper::getModelName("CODE_ENV_CATEGORY");
                $mdl3 = \Helper::getModelName("CODE_ENV_STATUS");
                $column[] = "$code_env_cate.NAME as OBJECT_NAME";
                break;
            case "LOGISTIC":
                $date1 = "CREATED_DATE";
                break;
            case "EU_TEST":
                $where_in = "EU_ID";
                $table_parent = EnergyUnit::getTableName();
                $date1 = "EFFECTIVE_DATE";
                break;
            case "KEYSTORE":
                if($name_table == "KEYSTORE_TANK_DATA_VALUE"){
                    $where_in = "KEYSTORE_TANK_ID";
                    $table_parent = KeystoreTank::getTableName();
                    $date1 = "OCCUR_DATE";
                }else if($name_table == "KEYSTORE_STORAGE_DATA_VALUE"){
                    $where_in = "KEYSTORE_STORAGE_ID";
                    $table_parent = KeystoreStorage::getTableName();
                    $date1 = "OCCUR_DATE";
                }
                else if($name_table == "KEYSTORE_INJECTION_POINT_DAY"){
                    $where_in = "INJECTION_POINT_ID";
                    $table_parent = KeystoreInjectionPoint::getTableName();
                    $date1 = "OCCUR_DATE";
                }
                else if($name_table == "QLTY_DATA"){
                    $where_in = "SRC_TYPE";
                    $table_parent = CodeQltySrcType::getTableName();
                    $date1 = "EFFECTIVE_DATE";
                }
                break;
            case "EQUIPMENT":
                $where_in = "EQUIPMENT_ID";
                $table_parent = Equipment::getTableName();
                $date1 = "OCCUR_DATE";
                $cate_col = "OFFLINE_REASON_CODE";
                $mdl2 = \Helper::getModelName("CODE_EQP_OFFLINE_REASON");
                $mdl3 = \Helper::getModelName("CODE_EQP_FUEL_CONS_TYPE");
                break;
            case "DEFERMENT":
                $where_in = "DEFER_TARGET";
                $planned  = \Helper::getModelName('CODE_DEFER_PLAN');
                $code1  = \Helper::getModelName('CODE_DEFER_CODE1');
                $code2  = \Helper::getModelName('CODE_DEFER_CODE2');
                $code3  = \Helper::getModelName('CODE_DEFER_CODE3');
                $defer_status  = \Helper::getModelName('CODE_DEFER_STATUS');
                $defer_category  = \Helper::getModelName('CODE_DEFER_CATEGORY');

                if($def_group_type[2] > 1 && $def_group_type[0] != 'not') $table_parent = ($def_group_type[0] == 'WELL') ? EnergyUnit::getTableName() : DefermentGroup::getTableName();
                $date1 = "BEGIN_TIME";
                break;
        }
        if(($name_table != "LOGISTIC" && $obj_type != "DEFERMENT"&&$obj_type != "COMMENTS" && $obj_type != "ENVIRONMENTAL")
        		||($renderType==2&&($obj_type == "COMMENTS" || $obj_type == "ENVIRONMENTAL")))
        	$column[] = $name_table."."."$date1 as DATETIME";
        if($obj_type != "LOGISTIC" && $obj_type != "DEFERMENT"&&$obj_type != "COMMENTS" && $obj_type != "ENVIRONMENTAL")
        	$column[] = $table_parent."."."NAME as OBJECT_NAME";
        if($renderType==2 && $obj_type == "EU_TEST") $column[] = "$name_table.$where_in as OBJECT_ID";
        if($obj_type == "FLOW" && $renderType == 1 ){
            if($name_table == "FLOW_DATA_PLAN") {
                $column[]= $code_plan_type."."."NAME as PLAN_TYPE";
                $lable[] = "Plan Type";
            }
        }else if($obj_type == "ENERGY_UNIT" && $renderType == 1 ){
            $column[]= $code_flow_phase."."."NAME as FLOW_PHASE";
            $column[]= $code_event_type."."."NAME as EVENT_TYPE";
            $lable[] = "Flow Fhase";
            $lable[] = "Event Type";
            if ($name_table == "ENERGY_UNIT_DATA_ALLOC"){
                $column[]= $code_alloc_type."."."NAME as ALLOC_TYPE";
                $lable[] = "Alloc Type";
            }
        }else if($obj_type == "EU_TEST" && $renderType == 1){
            $lable[] = "Testing Method";
            $column[]= $code_testing."."."NAME as TEST_METHOD";
        }

		$tmp_columns = [];
        foreach ($select_column as $value){
			if($value == 'OCCUR_DATE' || ($value == 'ALLOC_TYPE' && $name_table == "ENERGY_UNIT_DATA_ALLOC"))
				continue;
            $tmp_columns[]= $value;
            if($obj_type == "DEFERMENT"){
                if($def_group_type[2] > 1){
                    if($value == "COMMENT_CATEGORY")  $column[] = $code_comment_cate."."."NAME as OBJECT_NAME";
                    else if($value == "ENV_CATEGORY") $column[] = $code_env_cate."."."NAME as OBJECT_NAME";
                    else if($value == "DEFER_GROUP_TYPE") $column[] = $code_dgt."."."NAME as DEFER_GROUP";
                    else if($value == "DEFER_TARGET" && $def_group_type[1] != 0) $column[] = $table_parent."."."NAME as DEFER_TARGET";
                    else $column[] = $name_table.".".$value;
                }
             }
             else if($obj_type == "ENVIRONMENTAL"&&$value == "ENV_CATEGORY")
             	$column[] = "$code_env_cate.NAME as ENV_CATEGORY";
             else if($obj_type == "COMMENTS"&&$value == "COMMENT_CATEGORY")
             	$column[] = "$code_comment_cate.NAME as COMMENT_CATEGORY";
             else if($obj_type == "EU_TEST"){
                if($value != "TEST_METHOD" && $value != "EU_ID"){
                    if($value == "TEST_USAGE") $column[] = $code_testing_usage."."."NAME as TEST_USAGE";
                    else $column[] = $name_table.".".$value;
                }
            }else $column[] = $name_table.".".$value;
        }
		$select_column = $tmp_columns;

        foreach ($select_column as $val){
            if($val != "TEST_METHOD" && $val != "EU_ID") {
                $get_lable = CfgFieldProps::where('TABLE_NAME', 'like', '%' . $name_table . '%')->where('COLUMN_NAME', 'like', '%' . $val . '%')->select('LABEL')->first();
                $lable1[] = $get_lable->LABEL != null ? $get_lable->LABEL : $val;
            }
        }
        $lable1_cfg = array_merge($lable, $lable1);

        // Query
        $arr_cate = []; // Array category COMMENTS & ENVIRONMENTAL

        if($obj_type == "LOGISTIC" || $obj_type == "DEFERMENT") $sql = $mdl::where([]);
        else $sql = $mdl::join($table_parent, "$name_table.$where_in",'=',"$table_parent.ID");

        if($obj_type == "FLOW"){
            if ($name_table == "FLOW_DATA_PLAN"){
                $sql->whereNotNull('PLAN_TYPE')
                    ->join($code_plan_type, "$name_table.PLAN_TYPE", '=', "$code_plan_type.ID")
                    ->whereIn("$name_table.PLAN_TYPE",$plan_type_id);
            }
        }else if($obj_type == "ENERGY_UNIT"){
            $sql->join($code_flow_phase, "$name_table.FLOW_PHASE", '=', "$code_flow_phase.ID")
                ->join($code_event_type, "$name_table.EVENT_TYPE", '=', "$code_event_type.ID");
            if ($name_table == "ENERGY_UNIT_DATA_ALLOC") $sql->join($code_alloc_type, "$name_table.ALLOC_TYPE", '=', "$code_alloc_type.ID");
            $sql->whereIn("$name_table.FLOW_PHASE",$where_flow)
                ->whereIn("$name_table.EVENT_TYPE",$where_event);
            if ($name_table == "ENERGY_UNIT_DATA_ALLOC") $sql->whereIn("$name_table.ALLOC_TYPE",$where_alloc);
        }else if($obj_type == "EU_TEST"){
            $sql->leftjoin($code_testing, "$name_table.TEST_METHOD", '=', "$code_testing.ID")
                ->leftjoin($code_testing_usage, "$name_table.TEST_USAGE", '=', "$code_testing_usage.ID");
            if ($where_test) 
            	$sql->whereIn("$name_table.TEST_METHOD",$where_test);
//             else $sql->whereNull("$name_table.TEST_METHOD");
        }else if($obj_type == "DEFERMENT"){
            if($def_group_type[2] > 1){
                //$arr_defer_group_type = [];
                $arr_planned = [];
                $arr_code1 = [];
                $arr_code2 = [];
                $arr_code3 = [];
                $arr_defer_status = [];
                $arr_category = [];
                //$defer_group_type_data = $defer_group_type::where('ACTIVE', 1)->select('ID','NAME')->get();
                $planned_data = $planned::where('ACTIVE', 1)->select('ID','NAME')->get();
                $code1_data = $code1::where('ACTIVE', 1)->select('ID','NAME')->get();
                $code2_data = $code2::where('ACTIVE', 1)->select('ID','NAME')->get();
                $code3_data = $code3::where('ACTIVE', 1)->select('ID','NAME')->get();
                $defer_status_data = $defer_status::where('ACTIVE', 1)->select('ID','NAME')->get();
                $defer_category_data = $defer_category::where('ACTIVE', 1)->select('ID','NAME')->get();
                //foreach ($defer_group_type_data as $value) $arr_defer_group_type[$value->ID] = $value->NAME;
                foreach ($planned_data as $value) $arr_planned[$value->ID] = $value->NAME;
                foreach ($code1_data as $value) $arr_code1[$value->ID] = $value->NAME;
                foreach ($code2_data as $value) $arr_code2[$value->ID] = $value->NAME;
                foreach ($code3_data as $value) $arr_code3[$value->ID] = $value->NAME;
                foreach ($defer_status_data as $value) $arr_defer_status[$value->ID] = $value->NAME;
                foreach ($defer_category_data as $value) $arr_category[$value->ID] = $value->NAME;
                //$sql->join($code_dgt,"$name_table.DEFER_GROUP_TYPE",'=',"$code_dgt.ID");
                $sql->join($code_dgt,function ($query) use ($name_table,$code_dgt,$facility_id) {
                    $query->on("$name_table.DEFER_GROUP_TYPE",'=',"$code_dgt.ID");
                    if ($facility_id>0) $query->where("$code_dgt.FACILITY_ID",'=',$facility_id);
                });
                if($def_group_type[0] != 'not') $sql->leftJoin($table_parent,"$name_table.DEFER_TARGET",'=',"$table_parent.ID")->where("$name_table.DEFER_GROUP_TYPE",'=',$def_group_type[1]);
            }
        }else if($obj_type == "COMMENTS" || $obj_type == "ENVIRONMENTAL"){
            $joinTable = $mdl2::getTableName();
            $sql->join($mdl2::getTableName(), "$name_table.$cate_col", '=', "$joinTable.ID");
        }
        else if($obj_type == "EQUIPMENT"){
        	$joinTable = $mdl2::getTableName();
        	$sql->leftJoin($mdl2::getTableName(), "$name_table.$cate_col", '=', "$joinTable.ID");
        }

        if($obj_type == "COMMENTS" || $obj_type == "ENVIRONMENTAL" || $name_table == "LOGISTIC" || $obj_type == "DEFERMENT") $sql->where("$name_table.FACILITY_ID",'=',$facility_id);
        else if($name_table=="KEYSTORE_INJECTION_POINT_DAY"||$name_table=="QLTY_DATA"){}
        else $sql->where("$table_parent.FACILITY_ID",'=',$facility_id);
        if($name_table != "LOGISTIC" && $name_table != "DEFERMENT") $sql->whereIn("$name_table.$where_in",$where_id);

        $sql->whereDate("$name_table.$date1",'<=',$date_end)
            ->whereDate("$name_table.$date1",'>=',$date_begin)
            ->orderBy("$name_table.$date1")
            ->select($column);
        if($obj_type != "LOGISTIC" && $obj_type != "DEFERMENT") $sql->orderBy("$table_parent.NAME");
        else if($obj_type == "ENERGY_UNIT") $sql->orderBy("$name_table.FLOW_PHASE")->orderBy("$name_table.EVENT_TYPE");
        else if ($obj_type == "EU_TEST") $sql->orderBy("$name_table.TEST_METHOD");
        else if ($obj_type == "LOGISTIC"){
        	if (array_search("$name_table.VESSEL_NAME", $column)) $sql->orderBy("$name_table.VESSEL_NAME");
        	if (array_search("$name_table.MASTER_NAME", $column)) $sql->orderBy("$name_table.MASTER_NAME");
        }
        $dataSet = $sql->get();

        // Deferment All
        if ($obj_type == "DEFERMENT" && $def_group_type[2] > 1){
            foreach ($dataSet as $value){
                if ($def_group_type[0] == 'not'){
                    $id_dt = $value->DEFER_TARGET;
                    if($id_dt != null){
                        if($value->DEFER_GROUP == "Well"){
                            $eu = EnergyUnit::where("ID","=",$id_dt)->select("NAME")->first();
                            $value->DEFER_TARGET = isset($eu) ? $eu->NAME : '';
                        }else{
                            $def = DefermentGroup::where("ID","=",$id_dt)->select("NAME")->first();
                            $value->DEFER_TARGET = isset($def) ? $def->NAME : '';
                        }
                    }
                }
                //$value->DEFER_GROUP_TYPE = $value->DEFER_GROUP_TYPE!=null ? $arr_defer_group_type[$value->DEFER_GROUP_TYPE] : '';
                $value->PLANNED = $value->PLANNED!=null ? $arr_planned[$value->PLANNED] : '';
                $value->CODE1 = $value->CODE1!=null ? $arr_code1[$value->CODE1] : '';
                $value->CODE2 = $value->CODE2!=null ? $arr_code2[$value->CODE2] : '';
                $value->CODE3 = $value->CODE3!=null ? $arr_code3[$value->CODE3] : '';
                $value->DEFER_STATUS = $value->DEFER_STATUS!=null ? $arr_defer_status[$value->DEFER_STATUS] : '';
                $value->DEFER_CATEGORY = $value->DEFER_CATEGORY!=null ? $arr_category[$value->DEFER_CATEGORY] : '';
            }
        }else if($obj_type == "EU_TEST"){
            $arr_choke_uom = [1 => ['ID'=>1,'NAME'=>'%'], 2 => ['ID'=>2,'NAME'=>'/64']];
            foreach ($dataSet as $value)
                if(isset($value->CHOKE_UOM)) $value->CHOKE_UOM = ($value->CHOKE_UOM == '1') ? $arr_choke_uom[1]['NAME'] : $arr_choke_uom[2]['NAME'];
        }

        // Parse CREATED_DATE Date/Time Comments, Env, Logistic
        if (config('database.default')==='sqlsrv'){
        	$tmp = null;
            foreach ($dataSet as $val){
                if($obj_type == "LOGISTIC"){
                    $val->ARRIVE_DATE = ($val->ARRIVE_DATE) ? str_replace( '.000', '', $val->ARRIVE_DATE ) : '';
                    $val->DEPART_DATE = ($val->DEPART_DATE) ? str_replace( '.000', '', $val->DEPART_DATE ) : '';
                }else if($obj_type == "EU_TEST"){
                    $val->BEGIN_TIME = ($val->BEGIN_TIME) ? str_replace( '.000', '', $val->BEGIN_TIME ) : '';
                    $val->END_TIME = ($val->END_TIME) ? str_replace( '.000', '', $val->END_TIME ) : '';
                }else if($obj_type == "DEFERMENT"){
                    $val->BEGIN_TIME = ($val->BEGIN_TIME) ? str_replace( '.000', '', $val->BEGIN_TIME ) : '';
                    $val->END_TIME = ($val->END_TIME) ? str_replace( '.000', '', $val->END_TIME ) : '';
                }
                if ($renderType==1) {
                	$val->setDatesPropery([]);
                }
            }
        }

        // Parse data
        $data = [];
        $headerData = [];
        $childHeader = [];
        $columnFormats = [];
        if($renderType==1){
        }
        else{
            $data2 = [];
            if (count($dataSet) > 0) {
            	$groupingDateField = /* $name_table == "COMMENTS" || $name_table== "ENVIRONMENTAL"?"CREATED_DATE": */'DATETIME';
                $groupedOccurDate = $dataSet->groupBy($groupingDateField);
                $groupedByObjectName = $dataSet->groupBy('OBJECT_NAME');
                $objectNames 	= $groupedByObjectName->keys()->toArray();
                $headerData[] = ($obj_type == "ENERGY_UNIT" || $obj_type == "EU_TEST") ? $string : '';
                
                foreach ($objectNames as $objectName) {
                	$names = [];
                	foreach ($select_column as $column) $names[] =  $objectName;
                	$headerData = array_merge($headerData, $names);
                }
                
                $alphabetIndex = 0;
                $dateFormat = 'mm-dd-yy';
                $columnFormats[$alphabet[$alphabetIndex]] = $dateFormat;
                foreach ($groupedOccurDate as $date => $dataByDates) {
                    $row 	= ['OCCUR_DATE' => \PHPExcel_Shared_Date::PHPToExcel(\Carbon\Carbon::parse($date))];
                    $childHeader['OCCUR_DATE'] = 'Occur Date';
                    $groupedByObjectName = $dataByDates->groupBy('OBJECT_NAME');
                    $alphabetIndex = 1;
                    foreach ($objectNames as $objectName) {
                    	$entries = $groupedByObjectName->get($objectName);
                    	$entry = $entries&&$entries->count()>0?$entries[0]:null;
                    	foreach ($select_column as $key => $column) {
                    		if ($entry&&($column=='OCCUR_DATE'||$column=='ARRIVE_DATE'||$column=='DEPART_DATE')) {
                    			$vl = \PHPExcel_Shared_Date::PHPToExcel($entry->{$column});
                    			$row["$objectName.$column"] = $vl;
                    			if (!array_key_exists($alphabet[$alphabetIndex], $columnFormats))
                    				$columnFormats[$alphabet[$alphabetIndex]] = $dateFormat;
                    		}
                    		else
                    			$row["$objectName.$column"] = $entry?$entry->{$column}:'';
                    		if (!isset($childHeader["$objectName.$column"]))
                    			$childHeader["$objectName.$column"] = $lable1[$key];
                    		$alphabetIndex++;
                    	}
                    }
                    array_push($data2, $row);
                }
            }
            array_push($data, $data2);
        }
        // Export Excel
        $count_col = count($select_column);
        if ($renderType==2) 
        	$this->ExcelCreate($name_table,$data,$renderType,$headerData,$alphabet,$count_col,$childHeader,$lable1_cfg,$columnFormats);
        else{
            $response = new StreamedResponse(function() use($dataSet,$lable1_cfg,$name_table) {
                $handle = fopen('php://output', 'w');

                $header_cols = [];
                foreach($lable1_cfg as $value){
                    $header_cols[] = $value;
                }
                fputcsv($handle, $header_cols);

                foreach ($dataSet as $value) {
                    $t = collect($value)->toArray();
                    fputcsv($handle, $t);
                }
                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename='.$name_table.".csv",
            ]);
            return $response;
        }
    }

    public function ExcelCreate($name_table,$data,$renderType,$headerData,$alphabet,$count_col,$childHeader,$lable1_cfg,$columnFormats){
        Excel::create($name_table, function($excel) use ($data,$renderType,$headerData,$alphabet,$count_col,$childHeader,$lable1_cfg,$columnFormats) {
            foreach ($data as $key => $val){
                $key = $key+1;
                $excel->sheet('Page '.$key, function($sheet) use ($val,$renderType,$headerData,$alphabet,$count_col,$childHeader,$lable1_cfg,$columnFormats){
                    $renderData = $val;
                    if(count($val) > 0){
                        $header_cols = [];
                        if($renderType==1){
                            foreach($lable1_cfg as $value)
                                $header_cols[] = $value;
                            $sheet->prependRow($header_cols);
                        }
                        else{
                            $sheet->prependRow($header_cols);
                            $sheet->prependRow($childHeader);
                            $sheet->prependRow($headerData);
                            $union = array_values(array_unique($headerData));
                            for ($i=1 ; $i<=count($union); $i++){
                                $before = $alphabet[$i*$count_col-$count_col+1]."1";
                                $after  = $alphabet[$i*$count_col]."1";
                                $sheet->mergeCells($before.":".$after);
                                $sheet->getStyle($before.":".$after)->getAlignment()->applyFromArray(
                                    array('horizontal' => 'center')
                                );
                            }
                        }
                    }
                    if (count($columnFormats)>0) {
	                    $sheet->setColumnFormat($columnFormats);
                    }
                    $sheet->rows($renderData);
                });
            }
        })->download('xlsx');
    }

    public function changeObjectDataSource(Request $request){
        $postData 		= $request->all();
        $data_source    = $postData['DATA_SOURCE'];
        $name_table     = ($data_source == "COMMENTS") ? "CODE_COMMENT_TYPE" : "CODE_ENV_TYPE";
        $mdl            = \Helper::getModelName($name_table);
        $dataSet        =  $mdl::where('ACTIVE','=',1)->select('ID','NAME')->orderBy('NAME')->get();
        return response()->json($dataSet);
    }

    public function changeCodeDeferGroupType(Request $request){
        $postData 		  = $request->all();
        $facility              = $postData['FACILITY'];
        $defer_group_type_text = isset($postData['DEFER_GROUP_TYPE_TEXT']) ? $postData['DEFER_GROUP_TYPE_TEXT'] : '';
        $defer_group_type      = $postData['DEFER_GROUP_TYPE'];

        if($defer_group_type == 0){
            $code_defer_group_type = CodeDeferGroupType::where("FACILITY_ID",$facility)->whereIn('ACTIVE',[NULL,1])->select('ID')->orderBy('NAME')->get()->toArray();
            $obj_data1 = EnergyUnit::where("FACILITY_ID",$facility)->select('ID','NAME')->orderBy('NAME')->get();
            $obj_data2 = DefermentGroup::whereIn('DEFER_GROUP_TYPE',$code_defer_group_type)->select("ID","NAME")->orderBy('NAME')->get();
            $obj_data = $obj_data1->merge($obj_data2);
        }else{
            if($defer_group_type_text == "WELL") $obj_data = EnergyUnit::where("FACILITY_ID",$facility)->select('ID','NAME')->orderBy('NAME')->get();
            else $obj_data = DefermentGroup::where(['DEFER_GROUP_TYPE'=>$defer_group_type])->select("ID","NAME")->orderBy('NAME')->get();
        }
        return response()->json($obj_data);
    }

    public function changeFacilityKeystore(Request $request){
        $postData = $request->all();
        $facility = $postData['FACILITY'];
        $name_table = $postData['NAME_TABLE'];
        if($name_table == "KEYSTORE_TANK_DATA_VALUE") $sql = KeystoreTank::where("FACILITY_ID",$facility);
        else $sql = KeystoreStorage::where("FACILITY_ID",$facility);
        $data = $sql->select('ID','NAME')->orderBy('NAME')->get();
        return response()->json($data);
    }
}