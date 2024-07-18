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

class SubdailyControllerOld extends CodeController {

    public function index(){
        $filterGroups = array(
			'productionFilterGroup' => [
				'FlowHourly', [
					"filterName"	=>	"Flow hourly",
					'name'=>'FlowHourly'
				]],
            'dateFilterGroup' => [
				['id'=>'date_begin','name'=>'Effective Date'],
                ['id'=>'date_end','name'=>'To']],
        );
		
        return view ( 'front.subdailyold',['filters'=>$filterGroups]);
    }

    public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){

        $prefix 		= 	'FL_DATA_';
        $par = [
            (object)['data' =>	'DT_RowId',		'title' => '',	'width'	=>	100,'INPUT_TYPE'=>3,	'DATA_METHOD'=>2,'FIELD_ORDER'=>1],
            (object)['data' =>	'OCCUR_DATE',		'title' => 'Occur Date',	'width'	=>	100,'INPUT_TYPE'=>3,	'DATA_METHOD'=>2,'FIELD_ORDER'=>2],
            (object)['data' =>	$prefix."GRS_VOL"	,'title' => 'Gross Vol'	,	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>3],
            (object)['data' =>	$prefix."NET_VOL"	,'title' => 'Net Vol'	,   'width'=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>4],
            (object)['data' =>	$prefix."GRS_MASS"	,'title' => 'Gross Mass',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>5],
            (object)['data' =>	$prefix."NET_MASS"	,'title' => 'Net Mass', 	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>6],
            (object)['data' =>	$prefix."GRS_ENGY"	,'title' => 'Energy',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>7],
            (object)['data' =>	$prefix."GRS_PWR"	,'title' => 'Power',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>8],
            (object)['data' =>	"NUMBER_1"	,'title' => 'PNQ',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>9],
            (object)['data' =>	"NUMBER_2"	,'title' => 'TNQ',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>10],
            (object)['data' =>	"NUMBER_3"	,'title' => 'ENQ',	'width'	=>	125,'INPUT_TYPE'=>2,	'DATA_METHOD'=>1,'FIELD_ORDER'=>11],
        ];

        $properties = collect($par);
        return ['properties'	=>$properties];
    }

    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
        $flow=Flow::getTableName();
        $codeReading=CodeReadingFrequency::getTableName();

        $date_end 	= $postData['date_end'];
        $date_end	= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
        $date_begin 	= $postData['date_begin'];
        $date_begin	= $date_begin&&$date_begin!=""?\Helper::parseDate($date_begin):Carbon::now();
        $result=DB::table($dcTable)->join($flow,"$flow.ID",'=',"$dcTable.FLOW_ID")
            ->join($codeReading,"$flow.RECORD_FREQUENCY",'=',"$codeReading.ID")
            ->where(["$dcTable.FLOW_ID"=>$postData['FlowHourly'],"$codeReading.CODE"=>"HR"])
            ->whereBetween("$dcTable.OCCUR_DATE",[$date_begin,$date_end])
            ->get(["$dcTable.OCCUR_DATE","$dcTable.ID AS DT_RowId","$dcTable.FLOW_ID", "$dcTable.FL_DATA_GRS_VOL","$dcTable.FL_DATA_NET_VOL","$dcTable.FL_DATA_GRS_MASS","$dcTable.FL_DATA_NET_MASS","$dcTable.FL_DATA_GRS_ENGY","$dcTable.FL_DATA_GRS_PWR","$dcTable.NUMBER_1","$dcTable.NUMBER_2","$dcTable.NUMBER_3"]);
        return ['dataSet'=>$result];
    }

    public function reallocate(Request $request){
        $mdl="\App\Models\\$request->table";

        $data=$mdl::find($request->id);
        $flow_id=$data->FLOW_ID;
        $date = \Carbon\Carbon::parse($data->OCCUR_DATE)->format('Y-m-d');
        $mdl2="\App\Models\\$request->table"."SubDay";
        $result2=[];
        DB::beginTransaction();
        try{
            $result=$mdl2::whereDate('PROD_DATE','=',$date)->where('FLOW_ID','=',$flow_id)->delete();

            for($i=0;$i<=$request->hours;$i++){
                $temp= new $mdl2;
                $temp->OCCUR_DATE=$data->OCCUR_DATE->addHour($request->timebegin+$i);
                $temp->PROD_DATE=$data->OCCUR_DATE;
                $temp->FLOW_ID=$data->FLOW_ID;
                $temp->ACTIVE_HRS=$data->ACTIVE_HRS;
                $temp->FL_DATA_GRS_VOL=$data->FL_DATA_GRS_VOL/$request->hours;
                $temp->FL_DATA_NET_VOL=$data->FL_DATA_NET_VOL/$request->hours;
                $temp->FL_DATA_GRS_MASS=$data->FL_DATA_GRS_MASS/$request->hours;
                $temp->FL_DATA_NET_MASS=$data->FL_DATA_NET_MASS/$request->hours;
                $temp->FL_DATA_GRS_ENGY=$data->FL_DATA_GRS_ENGY/$request->hours;
                $temp->FL_DATA_GRS_PWR=$data->FL_DATA_GRS_PWR/$request->hours;
                $temp->save();
            }
            $result2=$mdl2::whereDate('PROD_DATE','=',$date)->where('FLOW_ID','=',$flow_id)->orderBy('OCCUR_DATE',"ASC")->get();
            //\Log::info($mdl2::getTableName());
            //\Log::info($result2);

        }catch (\Exception $e){
            DB::rollback();
            //\Log::info("123123");
            return $e;
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

        \Log::info($data);
        $flow_id=$data->FLOW_ID;
        $date = \Carbon\Carbon::parse($data->OCCUR_DATE)->format('Y-m-d');

        $mdl2=  "\App\Models\\$request->table"."SubDay";
        \Log::info($date);
        $result=$mdl2::whereDate('PROD_DATE','=',$date)->where('FLOW_ID','=',$flow_id)->orderBy('OCCUR_DATE',"ASC")->get();
        return $result;

    }

}