<?php

namespace App\Http\Controllers;
use App\Models\SqlConditionFilter;
use App\Models\SqlList;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DB;
use Excel;
use Illuminate\Http\Request;

class DataViewController extends EBController {
	
	public function _index() {
		
		$viewslist = SqlList::where(['ENABLE'=>1, 'TYPE'=>2])->get(['ID', 'NAME']);
		
		$sqllist = $this->getSqlList();		
		$user = auth()->user();
		$role = $user->hasRole('ADMIN');
		return view ( 'front.dataview', ['viewslist'=>$viewslist, 'sqllist'=>$sqllist, '_role'=>$role]);
	}
	
	public function tabledata() {
		return view ( 'admin.tabledata');
	}
	
	public function pdtabledata() {
		return view ( 'admin.pdtabledata');
	}
	
	private function getSqlList(){
        \Helper::setGetterUpperCase();
		$sqllist = SqlList::where(['ENABLE'=>1])
		->where ( function ($q) {
			$q->whereNull('TYPE');
			$q->orWhere ( [
					'TYPE' => 0
			] );
		} )
		->get(['ID', 'NAME']);
		
		return $sqllist;
	}
	
	public function getsql(Request $request) {
		$data = $request->all ();
		
		$sql1 = SqlList::where(['ID'=>$data['id']])->select('SQL')->first();
		
		return response ()->json ( $sql1->SQL );
	}
	
	public function loaddata(Request $request) {
	try{
		$data = $request->all ();
		$page=isset($data['page'])?$data['page']:1;
		$rows_in_page=isset($data['rows_in_page'])?$data['rows_in_page']:10000;

		$str = "";
		$option = "";
		if(isset($data['sql']))
		{
			$sql = trim($data['sql']);
			//$sql = strip_tags(trim($data['sql']));
		
			if(strtoupper(substr($sql,0,6))=="SQLID:")
			{
				$ss=explode(";",$sql);
				$id=substr($ss[0],6);
				$tmp = SqlList::where(['ID'=>$id])/* ->select('SQL') */->first();
				if(!$tmp) return response ()->json ( "sql not available" ); 
				$sql = $tmp->SQL;
				for($i=1;$i<count($ss);$i++)
					if($ss[$i])
					{
						$xs=explode(":",$ss[$i]);
						if(count($xs)>=2)
						{
							$lastchar=substr($xs[1],-1);
							$last2char=substr($xs[1],-2);
							if($lastchar=='=' || $lastchar=='<' || $lastchar=='>' || $last2char=="''" || $last2char=="=0")
								$xs[1]="1=1";
							$sql = str_replace($xs[0],$xs[1],$sql);
						}
					}
				if (config('database.default')!=='mysql') {
					if ($tmp->TYPE>0) $sql = str_replace('`', "", $sql);
					else $sql = str_replace('`', "\"", $sql);
				}
			}
			else if(strtoupper(substr($sql,0,6))!=="SELECT")
			{
				if(config('constants.allowSelectOnly',true)){
					$str = "Error: Only accept SELECT statement";
				}
				else{
					try{
						DB::statement($sql);
						$str = "Statement completed ($sql)";
					}
					catch(\Exception $ex){
						$str = "Error: ".$ex->getMessage()." ($sql)";
					}
				}
				$str = "<table border='0' id='data' class='display compact'><tr><td>{$str}</td></tr></table>";
				return response ()->json ( $str );
			}
			else{
				if (config('database.default')!=='mysql') $sql = str_replace('`', "\"", $sql);
			}
			
			while(true){
				$found=false;
				$i1=strpos($sql,'{');
				if($i1>0){
					$i2=strpos($sql,'}',$i1);
					if($i2>$i1){
						$found=true;
						$sql=substr($sql,0,$i1)."1=1".substr($sql,$i2+1);
					}
				}
				if(!$found)
					break;
			}

			$sql_export=$sql;
			$total_row = 0;

			$stmp = strtolower($sql);
			$ind = strpos($stmp,'from');
			if($ind>0){
				//$stmp = 'select count(1) rows_count '.substr($sql,$ind);
				$stmp = "select count(1) rows_count from ($sql) x";
				$total_rows = DB::select($stmp);
				if(count($total_rows)>0)
					$total_row = $total_rows[0]->rows_count;
			}
			
			$total_page = ceil($total_row/$rows_in_page);			//Toal page
		
			$sql_export=$sql;
			if (config('database.default')==='oracle') {
				$_offset = ($page-1)*$rows_in_page;
				$sql = "select * from (select a.*, ROWNUM rownum_ from ($sql) a where ROWNUM <= ".($rows_in_page+$_offset).") where rownum_ > $_offset";
			}
			else if (config('database.default')==='sqlsrv') {
				$_offset = ($page-1)*$rows_in_page;
				//remove order by
				$sql = preg_replace('/order\s+by\s+.+$/i', '', $sql); // remove existing order
				$sql = "SELECT a.* FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (select null)) as rownum_ FROM ($sql) x) a WHERE a.rownum_ > $_offset and a.rownum_ <= ".($rows_in_page+$_offset);
			}
			else{
				$sql.=" LIMIT ".(($page-1)*$rows_in_page).", ".$rows_in_page;
			}

			$re = DB::select($sql);
			
			if(count($re) <= 0) return response ()->json ( $str );
			$fields=collect($re[0])->toArray();
		
			$occur_date_exist=false;
			$str .= "<table border='0' id='data' class='dataViewTable display compact'><thead><tr>";
			$keys = "";
			foreach($fields as $key => $value) if($key!='rownum_')
			{
				$str .= "<th>".$key."</th>";
				$keys .= $key.",";
			}
			$str .= "</tr></thead>";
			$index = explode(",",$keys);
			
			foreach ($re as $ro)
			{
				$str .= "<tr>";
				for($i=0, $l=count($index)-1; $i<$l; $i++)
				{
					$str .= "<td>".$ro->{$index[$i]}."</td>";
				}
				$str .= "</tr>";
			}
		}
		else{
			$view_name=$data['view_name'];
			$object_id=($data['object']==""? NULL: $data['object']);
			$from_date=($data['from_date']==""? NULL: $data['from_date']);		
			$to_date=($data['to_date']==""? NULL: $data['to_date']);
		
			//Check existing
			$occur_date_exist=check_exist_field("OCCUR_DATE", $view_name, 0, NULL);
			$flow_exist=check_exist_field("FLOW_ID", $view_name, 1, $object_id);
			$eu_exist=check_exist_field("EU_ID", $view_name, 1, $object_id);
			$option=($flow_exist!=false? $flow_exist: ($eu_exist!=false? $eu_exist: ""));
		
			if($object_id!=NULL and $object_id !=-1)
			{
				$temp="";
				foreach($object_id as $obj)
				{
					$temp.=($temp? ",": "")."'$obj'";
				}
				$cond=($flow_exist==true? "FLOW_ID IN (".$temp.")": ($eu_exist==true? "EU_ID IN (".$temp.")": ""));
			}
			$cond.=($cond? ($from_date? " AND OCCUR_DATE >= STR_TO_DATE('".$from_date."', '%m/%d/%Y')": ""): ($from_date? "OCCUR_DATE >= STR_TO_DATE('".$from_date."', '%m/%d/%Y')": ""));
			$cond.=($cond? ($to_date? " AND OCCUR_DATE <= STR_TO_DATE('".$to_date."', '%m/%d/%Y')": ""): ($to_date? "OCCUR_DATE <= STR_TO_DATE('".$to_date."', '%m/%d/%Y')": ""));
			$cond=($cond? "WHERE ": "").$cond;
		
			$sql_export="SELECT * FROM ".$view_name." ".$cond;
			$sql="SELECT * FROM ".$view_name." ".$cond." LIMIT ".(($page-1)*$rows_in_page).", ".$rows_in_page;
						\Log::info($sql);

			$re=mysql_query($sql) or die("Error: ".mysql_error());
		
			$total_row = mysql_num_rows(mysql_query("SELECT 1 FROM ".$view_name." ".$cond)); 	//Total record
			$total_page = ceil($total_row/$rows_in_page);			//Toal page
		
			$s_field="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$view_name."'";
			$re_field=mysql_query($s_field) or die("Error: ".mysql_error());
				
			echo "
		<table border='0' id='data' class='display compact'>
		<thead><tr>";
			while($ro_field=mysql_fetch_array($re_field))
			{
				echo ("<th>".$ro_field['COLUMN_NAME']."</th>");
			}
			echo "</tr></thead>";
		
			while($ro=mysql_fetch_array($re))
			{
				echo "<tr>";
				mysql_data_seek($re_field, 0);
				while($ro_field=mysql_fetch_array($re_field))
				{
					echo "<td>".$ro[$ro_field['COLUMN_NAME']]."</td>";
				}
				echo "</tr>";
			}
		}
		$str .= "</table>";
		$pagingDiv = "<div id='paging'>You are here: ";
		$page_list = "";
		$skip=false;
		for($i=1; $i<=$total_page; $i++)
		{
			if(($i-$page>5 || $page-$i>5) && $i<$total_page-1 && $i>2)
			{
				$skip=true;
				continue;
			}
			if($skip==true) $page_list.="...";
			$page_list.=($page_list? "-": "")."<span page='".$i."' ".($i==$page? "class='current_page'": "").">".($i==$page?"[<b>".$i."</b>]": $i)."</span>";
			$skip=false;
		}
		$pagingDiv .= $page_list."<input type='text' value='' size='4' id='txtpage'><input type='button' value='Go' id='go'></div>
				<div style='display:none'>
					<span id='sql_export'>".$sql_export."</span>
					<span id='occurdate_exist'>".$occur_date_exist."</span>
					<span id='option'>".$option."</span>
				<div>";
		return response ()->json ( $str.$pagingDiv );
	}catch (\Exception $ex){
		\Log::info("\n---------------------------------------------------------------\nException when run loaddata\n ");
		\Log::info($ex->getMessage());
		\Log::info($ex->getTraceAsString());
		return response ()->json ( "Error: ".$ex->getMessage() );
	}
	}
	
	private function check_exist_field($field, $table_view, $option, $selected)
	{
		$field_num = DB::statement("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$table_view."' AND COLUMN_NAME='".$field."'");
		/* $field_num=mysql_num_rows(mysql_query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='".$table_view."' AND COLUMN_NAME='".$field."'"));
		if($field_num==1)		//Exist
		{
			if($option==1)
			{
				$re=mysql_query("SELECT id, name FROM ".($field=='EU_ID'? "energy_unit": "flow")) or die("Error: ".mysql_error());
				while($ro=mysql_fetch_array($re))
				{
					$option_s.="<option value='".$ro[id]."' ".($selected? ($selected==-1? "selected": (in_array($ro[id], $selected)? "selected": "")): "").">".$ro[name]."</option>";
				}
				return $option_s;
			}
			else if($option==0)
				return true;
		}
		else
			return false; */
	}
	
	public function checkSQL(Request $request) {
		$data = $request->all ();
		$ret = SqlConditionFilter::where(['SQL_ID'=>$data['id']])->orderBy('ORDER')->select('*')->get();
		$html="";
		foreach ($ret as $row)
		{
			$html.="<div style='margin:5px;font-size:13px'><span class='condition_field' filed_name='$row->FIELD_NAME' IS_DATE_RANGE='$row->IS_DATE_RANGE' FIELD_VALUE_REF_TABLE='$row->FIELD_VALUE_REF_TABLE' style='margin-bottom:8px;font-weight:bold'>".($row->LABEL?$row->LABEL:$row->FIELD_NAME)."</span>";
			if($row->IS_DATE_RANGE=="1")
			{
				$html.=
				" From <input class='datepicker' style='width:80px' id='$row->FIELD_NAME"."_FROM' name='$row->FIELD_NAME"."_FROM'>
				To <input class='datepicker' style='width:80px' id='$row->FIELD_NAME"."_TO' name='$row->FIELD_NAME"."_TO'>
				";
			}
			else if($row->FIELD_VALUE_REF_TABLE)
			{
				$table_name = $row->FIELD_VALUE_REF_TABLE;
				$entity = strtolower(str_replace('_', ' ', $table_name));
				$entity = ucwords($entity);
				$entity = str_replace(' ', '', $entity);
					
				$model = 'App\\Models\\' .$entity;
				
				$childload="";
				if($row->CHILD_LOAD){
					$childload="onchange=_dataview.childLoad('$row->CHILD_LOAD','$row->FIELD_NAME',this)";
				}
				$result = $model::all(['ID', 'NAME']);
				$options = "";
				$options .= "<option value='0'></option>";
				foreach ($result as $row1){
					$options .= "<option value='$row1->ID'>$row1->NAME</option>";
				}
				$html.=" <select $childload id='$row->FIELD_NAME"."_SELECT' table='$row->FIELD_VALUE_REF_TABLE' field='$row->FIELD_NAME'>$options</select>";
			}
			$html.="</div>";
		}
		$str = "";
		if($html==""){
			$sql = SqlList::where(['ID'=>$data['id']])->select('SQL')->first()->SQL;
			if (strpos($sql, '{OCCUR_DATE}') !== false) {
				$html.="<div style='margin:5px;font-size:13px'>
						<input style='height:0px; top:-1000px; position:absolute' type='text' value=''>
						<span class='condition_field' filed_name='OCCUR_DATE' IS_DATE_RANGE='1' FIELD_VALUE_REF_TABLE='' style='margin-bottom:8px;font-weight:bold'>Occur date</span>";
				$html.=
				" From <input class='datepicker' style='width:80px' id='OCCUR_DATE"."_FROM' name='OCCUR_DATE"."_FROM'>
				To <input class='datepicker' style='width:80px' id='OCCUR_DATE"."_TO' name='OCCUR_DATE"."_TO'>
				";
				$html.="</div>";
			}
		}
		if($html) $str = "filter:$html";
		
		return response ()->json ( $str );
			
	}
	
	public function deletesql(Request $request) {
		$data = $request->all ();
		
		SqlList::where(['ID'=>$data['id']])->update(['ENABLE'=>0]);
		
		$sqllist = $this->getSqlList();
		
		return response ()->json ( $sqllist );
	}

	public function downloadExcel($payload)
	{
		$sql = base64_decode(strrev($payload));
		$sql = str_replace(['union','#','--'], ['','',''], $sql);
		if(strtoupper(substr($sql,0,6))=="SQLID:")
		{
			$ss=explode(";",$sql);
			$id=substr($ss[0],6);
			$tmp = SqlList::where(['ID'=>$id])/* ->select('SQL') */->first();
			if(!$tmp) return response ()->json ( "sql not available" );
			$sql = $tmp->SQL;
			for($i=1;$i<count($ss);$i++)
				if($ss[$i])
				{
					$xs=explode(":",$ss[$i]);
					if(count($xs)>=2)
					{
						$lastchar=substr($xs[1],-1);
						$last2char=substr($xs[1],-2);
						if($lastchar=='=' || $lastchar=='<' || $lastchar=='>' || $last2char=="''" || $last2char=="=0")
							$xs[1]="1=1";
						$sql = str_replace($xs[0],$xs[1],$sql);
					}
				}
			if (config('database.default')!=='mysql') {
				if ($tmp->TYPE>0) $sql = str_replace('`', "", $sql);
				else $sql = str_replace('`', "\"", $sql);
			}
		}
		else if(strtoupper(substr($sql,0,6))!=="SELECT")
		{
			echo "Allow SELECT only";
			exit;
		}
		
		while(true){
			$found=false;
			$i1=strpos($sql,'{');
			if($i1>0){
				$i2=strpos($sql,'}',$i1);
				if($i2>$i1){
					$found=true;
					$sql=substr($sql,0,$i1)."1=1".substr($sql,$i2+1);
				}
			}
			if(!$found)
				break;
		}

		/*
		$sql_export=$sql;
		$total_row = 0;

		$stmp = strtolower($sql);
		$ind = strpos($stmp,'from');
		if($ind>0){
			$stmp = 'select count(1) rows_count '.substr($sql,$ind);
			$total_rows = DB::select($stmp);
			if(count($total_rows)>0)
				$total_row = $total_rows[0]->rows_count;
		}
		if($total_row>10000){
			echo "Can not export: Too many data rows ($total_row). Max allowed 10000 rows.";
			return;
		}
		*/
	$response = new StreamedResponse(function() use($sql){
            // Open output stream
            $handle = fopen('php://output', 'w');

            // Get data
			
	$db = DB::connection()->getPdo();
	$query = $db->prepare($sql);
    $query->execute();
	$first_row = true;
    while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
		if($first_row){
			$first_row = false;
			fputcsv($handle, array_keys($row));
		}
		fputcsv($handle, array_values($row));
    }
	/*
			DB::select($sql)->chunk(500, function($rows) use($handle) {
				$first_row = true;
				foreach ($rows as $row) {
					\Log::info($row);
					exit;
					if($first_row){
						$header_cols = [];
						foreach($row as $key => $value)
							$header_cols[] = $key;				
						$first_row = false;
						fputcsv($handle, $header_cols);
					}
					
					// Add a new row with data
					fputcsv($handle, [
					  $user->id,
					  $user->name,
					  $user->email
					]);
				}
			});
*/

            // Close the output stream
            fclose($handle);
        }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="export.csv"',
            ]);
        return $response;
/*
		
		$tmp = DB::select($sql);
		$data = [];
		foreach ($tmp as $t){
			$t = collect($t)->toArray();
			array_push($data, $t);
		}
		return Excel::create('export', function($excel) use ($data) {
			$excel->sheet('Data', function($sheet) use ($data)
			{
				if(count($data) > 0){
					$header_cols = [];
					foreach($data[0] as $key => $value)
						$header_cols[] = $key;
					$sheet->prependRow($header_cols);
				}
				$sheet->rows($data);
			});
		})->download('xlsx');
*/
	}
	
	public function savesql(Request $request) {
		$data = $request->all ();
		
		$name=addslashes($data['name']);
		//$sql=addslashes($data['sql']);
        $sql = $data['sql'];
		$id=$data['id'];
		
		if($id>0){
			SqlList::where(['ID'=>$id])->update(['SQL'=>$sql]);
		}else{
			SqlList:: insert(['SQL'=>$sql, 'NAME'=>$name]);
		}
		return response ()->json ( $this->getSqlList() );
	}
}