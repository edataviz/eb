<?php
namespace App\Http\Controllers\Config;
use DB;
use App\Http\Controllers\CodeController;
use App\Services\TableDataService;
use App\Services\TableDataServiceOracle;
use App\Services\TableDataServiceSqlServer;
use Illuminate\Http\Request;
use Excel;

class TableDataController extends CodeController {

    public function edittable(){
    	$tablename 	= \Input::get('table');
    	$action 	= \Input::get('action');
    	$dbh 		= \DB::connection()->getPdo();
		if(config('database.default')!='sqlsrv')
			$dbh->setAttribute (\PDO::ATTR_EMULATE_PREPARES, false);
		if(config('database.default')==='oracle'){
            $lm = new TableDataServiceOracle($dbh);
            $lm->identity_name = 'id';
        } else if(config('database.default')==='sqlsrv'){
            $lm = new TableDataServiceSqlServer($dbh);
            $lm->identity_name = 'ID';
        } else{
            $lm = new TableDataService($dbh);
            $lm->identity_name = 'ID';
        }
        $lm->setModelName($tablename);
    	return view ( 'tableData.ebedittable',['tablename'=>$tablename,
							    			'action'	=>$action,
							    			'lm'		=>$lm
    	]);
    }
	
    public function genSql(Request $request){
    	$postData 	= $request->all();
		$table 	= $postData["table"];
		$ids 		= $postData["ids"];
		$type 		= $postData["type"];
		$result = DB::table ( $table )->whereIn("ID",$ids)->select('*')->get();
			//\Log::info($result);
		$sss="";
		foreach ($result as $r){
			$row = get_object_vars($r);
			$f="";
			$v="";
			$s="";
			foreach ($row as $key => $value) {
				$f.=($f?",":"")."$key";
				$v.=($v?",":"")."'".addslashes($value)."'";
				$s.=($s?",":"")."$key='".addslashes($value)."'";
			}
			if($type==1)
				$sss.="insert into $table($f) values($v);\n";
			else if($type==2)
				$sss.="update $table set $s where id=".(array_key_exists("ID",$row)?$row["ID"]:$row["id"]).";\n";
			else
				$sss.="insert into $table($f) values($v);~@^@~update $table set $s where id=".(array_key_exists("ID",$row)?$row["ID"]:$row["id"]).";";
		}

		$sss=str_replace("''","null", $sss);
		return $sss;
	}
	
    public function delete(Request $request){
    	$postData 		= $request->all();
    	$results		= "no data to delete";
    	try {
     		$results 	= \DB::transaction(function () use ($postData){
		    	$tablename 	= $postData['table'];
		    	$ids 		= explode(",",$postData['ids']);
     			$mdl		= \Helper::getModelName($tablename);
		    	if (count($ids)>0&&$mdl) {
		    		$mdl::whereIn("ID",$ids)->delete();
	     			$results= "successful";
		    	}
 		     	return $results;
	      	});
     	}
     	catch (\Exception $e){
      		\Log::info("\n----------------------delete error--------------------------------------------------------------------------\nException wher run transation\n ");
			$results = "unsuccessful";
			throw $e;
     	}
    	return response()->json($results);
    }

    public function exportDataXlsx($str){
        $mdl = \Helper::getModelName($str);
        $dataSet = $mdl::get();
        $data = [];
        foreach ($dataSet as $t){
            $t = collect($t)->toArray();
            array_push($data, $t);
        }
        Excel::create($str, function($excel) use ($data) {
            $excel->sheet('Data', function($sheet) use ($data){
                if (count($data) > 0){
                    $header_cols = [];
                    foreach($data[0] as $key => $value)
                        $header_cols[] = $key;
                    $sheet->prependRow($header_cols);
                    $sheet->rows($data);
                }
            });
        })->download('xlsx');
    }
}
