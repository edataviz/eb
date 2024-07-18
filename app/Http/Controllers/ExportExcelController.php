<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 23/07/2018
 * Time: 2:49 CH
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExportExcelController extends Controller
{
    public function linkView(Request $request){
        $postData = $request->all();
        $table = $postData['table'];
        unset($postData['table']);
        $ds = \DB::table($table);
		if(isset($postData['fromdate'])){
			$ds = $ds->where('OCCUR_DATE','>=',$postData['fromdate']);
	        unset($postData['fromdate']);
		}
		if(isset($postData['todate'])){
			$ds = $ds->where('OCCUR_DATE','<=',$postData['todate']);
	        unset($postData['todate']);
		}
		$header = true;
		if(isset($postData['header'])){
			if($postData['header']==0)
				$header = false;
	        unset($postData['header']);
		}
		if(isset($postData['dataviewname'])){
			$dataviewname = $postData['dataviewname'];
			$sql = \DB::table('sql_list')->where('name','=',$dataviewname)->select('sql')->first();
			if($sql){
				$sql = strtoupper($sql->sql);
				$sqls = explode('SELECT ', $sql);
				if(count($sqls)>1){
					$selects = array_map('trim', explode(',', explode(' FROM ', $sqls[1])[0]));
					$ss = [];
					foreach($selects as $sel)
						$ss[] = \DB::raw($sel);
					$ds = $ds->select($ss);
				}
			}
			unset($postData['dataviewname']);
		}
		if(count($postData)>0)
			$ds = $ds->where($postData);

        return view('excel_export.export',['datas'=>$ds->get(), 'header'=>$header]);
    }
	
    public function V_KERENDAN_WELL_PERFORMANCE(Request $request){
        $postData = $request->all();
        $fromdate = $postData['fromdate'];
		$header = true;
        $todate = $postData['todate'];
		if(isset($postData['header'])){
			if($postData['header']==0)
				$header = false;
		}
        $datas = \DB::table('V_KERENDAN_WELL_PERFORMANCE')
			->where('OCCUR_DATE','>=',$fromdate)
			->where('OCCUR_DATE','<=',$todate)
			->get();

        return view('excel_export.export',['datas'=>$datas, 'header'=>$header]);
    }
}