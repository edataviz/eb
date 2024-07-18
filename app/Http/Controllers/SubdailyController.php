<?php

namespace App\Http\Controllers;
use App\Jobs\runAllocation;
use App\Models\AllocJob;
use App\Models\CodeAllocType;
use App\Models\CodeAllocFromOption;
use App\Models\CodeAllocValueType;
use App\Models\CodeFlowPhase;
use App\Models\CodeReadingFrequency;
use App\Models\Facility;
use App\Models\Network;
use App\Models\AllocRunner;
use App\Models\AllocRunnerObjects;
use App\Models\Flow;
use App\Models\EnergyUnit;
use App\Models\Tank;
use App\Models\Storage;
use App\Models\AllocCondOut;
use App\Models\AllocCondition;
use App\Models\JobDiagram;

use DB;
use Illuminate\Http\Request;

class SubdailyController extends FlowController {

    public function index(){
		$filterGroups = [
            'productionFilterGroup'	=> [],
            'frequenceFilterGroup'	=> [	["name"			=> "FlowHourly",
                                            "defaultEnable"	=> false,
                                            "getMethod"		=> "loadBy",
                                            "source"		=> ['productionFilterGroup'=>["Facility"]]],
            ],
            'dateFilterGroup'=> [
                    ['id'=>'date_begin','name'=>'Date'],
            ],
            'FacilityDependentMore'	=> ["FlowHourly"],
        ];
        return view ( 'front.subdaily',['filters'=>$filterGroups]);
    }

	
    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
    	$flow 			= Flow::getTableName();
    	$codeReading=CodeReadingFrequency::getTableName();
    	 
    	$where = ["$codeReading.CODE" => "HR"];
		if($postData['FlowHourly'] > 0)
			$where["$dcTable.FLOW_ID"] = $postData['FlowHourly'];
        else
			$where["$flow.FACILITY_ID"] = $facility_id;
        $columns	= $this->extractRespondColumns($dcTable,$properties);
		if (!$columns) $columns = [];
		array_push($columns,"$dcTable.OCCUR_DATE",
							"$flow.name as $dcTable",
			    			"$dcTable.ID as DT_RowId",
			    			"$flow.ID as ".config("constants.flowId"),
			    			"$flow.ID as ID",
							"$dcTable.RECORD_STATUS",
			    			"$flow.phase_id as FL_FLOW_PHASE");
		
		$query 	= $this->buildQuery("Flow",$occur_date,$facility_id,$postData);
    	$dataSet = $query->join($codeReading,"$flow.RECORD_FREQUENCY",'=',"$codeReading.ID")
				    	->where($where)
				    	->whereDate('EFFECTIVE_DATE', '<=', $occur_date)
				    	->leftJoin($dcTable, function($join) use ($flow,$dcTable,$occur_date){
				    		$join->on("$flow.ID", '=', "$dcTable.FLOW_ID");
				    		$join->where('OCCUR_DATE','=',$occur_date." 05:00");
				    	})
				    	->select($columns)
		    			->orderBy($dcTable)
		    			->orderBy('FL_FLOW_PHASE')
		    			->get();
//     	\Helper::setGetterLowerCase();
    	return ['dataSet'=>$dataSet];
    }

/*    
    public function reallocate(Request $request){
        $mdl="\App\Models\\$request->table";

        $data=$mdl::find($request->id);
        $flow_id=$data->FLOW_ID;
        //$baseDate = \Carbon\Carbon::parse($data->OCCUR_DATE)->format('Y-m-d');
		$baseDate = $data->OCCUR_DATE;
        $mdl2="\App\Models\\$request->table"."SubDay";
        $result2=[];
		while($request->timebegin > $request->timeend)
			$request->timeend += 24;
        DB::beginTransaction();
        try{
            $result=$mdl2::where(function($query) use ($baseDate, $request){
				$query->whereBetween('OCCUR_DATE', [
					$baseDate->copy()->addHour($request->timebegin), 
					$baseDate->copy()->addHour($request->timeend)
				])->orWhereDate('PROD_DATE','=',$baseDate);
			})->where('FLOW_ID','=',$flow_id)->delete();

            for($i = $request->timebegin; $i <= $request->timeend; $i++){
                $temp= new $mdl2;
                $temp->OCCUR_DATE=$data->OCCUR_DATE->addHour($i);
                $temp->PROD_DATE=$data->OCCUR_DATE;
                $temp->FLOW_ID=$data->FLOW_ID;
                $temp->ACTIVE_HRS=$data->ACTIVE_HRS;
                $temp->FL_DATA_GRS_VOL=$data->FL_DATA_GRS_VOL/$request->hours;
                $temp->FL_DATA_NET_VOL=$data->FL_DATA_NET_VOL/$request->hours;
                $temp->FL_DATA_GRS_MASS=$data->FL_DATA_GRS_MASS/$request->hours;
                $temp->FL_DATA_NET_MASS=$data->FL_DATA_NET_MASS/$request->hours;
                $temp->FL_DATA_GRS_ENGY=$data->FL_DATA_GRS_ENGY/$request->hours;
                $temp->FL_DATA_GRS_PWR=$data->FL_DATA_GRS_PWR/$request->hours;
                //$temp->NUMBER_1=$data->NUMBER_1/$request->hours;
                //$temp->NUMBER_2=$data->NUMBER_2/$request->hours;
                $temp->save();
            }
			$originAttrCase = \Helper::setGetterUpperCase();
            $result2=$mdl2::whereDate('PROD_DATE','=',$baseDate)->where('FLOW_ID','=',$flow_id)->orderBy('OCCUR_DATE',"ASC")->get();
			\Helper::setGetterCase($originAttrCase);

        }catch (\Exception $e){
            DB::rollback();
            throw $e;
        }
        DB::commit();
        return $result2;
    }
	
    public function deleteallocate(Request $request){
        $mdl="\App\Models\\$request->table";
        $data=$mdl::find($request->id);
        $flow_id=$data->FLOW_ID;
        $date = \Carbon\Carbon::parse($data->OCCUR_DATE)->format('Y-m-d');

        $mdl2=  "\App\Models\\$request->table"."SubDay";
        $result=$mdl2::whereDate('PROD_DATE','=',$date)->where('FLOW_ID','=',$flow_id)->delete();
        return [];
    }
	
    public function saveallocate(Request $request){
        $mdl=  "\App\Models\\$request->table"."SubDay";
        foreach($request->editedData as $key=>$value){
            $mdl::where(['OCCUR_DATE'=>$value['OCCUR_DATE'],'FLOW_ID'=>$value['FLOW_ID']])
            ->update($value);
        }

        return [];
    }
	
    public function loadsubday(Request $request){
        $mdl="\App\Models\\$request->table";
        $data=$mdl::find($request->id);
        $flow_id=$data->FLOW_ID;
        $date = \Carbon\Carbon::parse($data->OCCUR_DATE)->format('Y-m-d');

        $mdl2=  "\App\Models\\$request->table"."SubDay";
		$originAttrCase = \Helper::setGetterUpperCase();
        $result=$mdl2::whereDate('PROD_DATE','=',$date)->where('FLOW_ID','=',$flow_id)->orderBy('OCCUR_DATE',"ASC")->get(["OCCUR_DATE",
				DB::raw("round(FL_DATA_GRS_VOL, 3) as FL_DATA_GRS_VOL"),
				DB::raw("round(FL_DATA_NET_VOL, 3) as FL_DATA_NET_VOL"),
				DB::raw("round(FL_DATA_GRS_MASS, 3) as FL_DATA_GRS_MASS"),
				DB::raw("round(FL_DATA_NET_MASS, 3) as FL_DATA_NET_MASS"),
				DB::raw("round(FL_DATA_GRS_ENGY, 3) as FL_DATA_GRS_ENGY"),
				DB::raw("round(FL_DATA_DENS, 3) as FL_DATA_DENS"),
				DB::raw("round(FL_DATA_SW_PCT, 3) as FL_DATA_SW_PCT"),
				DB::raw("round(NUMBER_1, 3) as NUMBER_1"),
				DB::raw("round(NUMBER_2, 3) as NUMBER_2"),
			]);
		\Helper::setGetterCase($originAttrCase);
        return $result;

    }
*/
}	