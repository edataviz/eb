<?php

namespace App\Http\Controllers;
use App\Models\SqlList;
use DB;
use Illuminate\Http\Request;

class DataViewControllerNoAuth extends Controller {
	public function loaddata(Request $request) {
	try{
		$data = $request->all ();
		$page=isset($data['page'])?$data['page']:1;
		$rows_in_page=isset($data['rows_in_page'])?$data['rows_in_page']:1000;

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
				$str .= "<table border='0' id='data' class='display compact'><thead><tr><th>Error</th></tr></thead><tr><td>Only accept SELECT statement</td></tr></table>";
				exit();
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
				$sql = "SELECT a.* FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (select null)) as row FROM ($sql) x) a WHERE a.row > $_offset and a.row <= ".($rows_in_page+$_offset);
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
			foreach($fields as $key => $value)
			{
				$str .= "<th>".$key."</th>";
				$keys .= $key.",";
			}
			$str .= "</tr></thead>";
			$index = explode(",",$keys);
			
			foreach ($re as $ro)
			{
				$str .= "<tr>";
				for($i=0; $i<count($index)-1; $i++)
				{
					$str .= "<td>".$ro->$index[$i]."</td>";
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
		return $str;
	}catch (\Exception $exp){
		\Log::info("\n---------------------------------------------------------------\nException when run loaddata\n ");
		\Log::info($exp->getMessage());
		\Log::info($exp->getTraceAsString());
		return response ()->json ( $exp->getMessage() );
	}
	}
}