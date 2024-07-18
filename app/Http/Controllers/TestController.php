<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class TestController extends Controller {

    function cvChart($input){

        $cfg = ['items' => []];

    	$configs	= explode("\r\n",$input);
		$ss=explode(",",$configs[0]);
		if (count($configs)>1) {
			try {
				$oConfigs = json_decode(str_replace("\\","",$configs[1]));
				if (isset($oConfigs->SampleInterval)) $cfg["autoRefresh"] = $oConfigs->SampleInterval;
				if (isset($oConfigs->Timebase)){
                    $cfg["timeRange"]	= $oConfigs->Timebase / 60000;
                    $cfg["timeRangeUnit"] = "minute";
                }
			}
			catch (\Exception $e){
				\Log::info($e->getMessage());
				\Log::info($e->getTraceAsString());
			}
		}
		$seriesIndex=0;
		$maxV=0;
		$minV=PHP_INT_MAX;
		$strData = "";
		$pies="";
        $defermentPies= [];
        $pieSize = 100;
		$stackedColumnOption="";
		$realtimeTagsArray = "";
		
		$ya=array();
		$no_yaxis_config=true;
		
		$originAttrCase 	= \Helper::setGetterUpperCase();
        $colors             = \Helper::$colors;
        $color              = $colors[0];
        foreach($ss as $sIndex => $s)
		{
			$tmp = [];
			$yaIndex=-1;
			$phase_type = -1;
			$eventType = -1;
			$s = str_replace("undefined", "", $s);
			$xs=explode(":",$s);
			$tag = '';
			$is_eutest=false;
			$is_deferment=false;
            $is_cummulative = false;
            
            $item = [];
            $expression = "";
            
			if(count($xs)<6) continue;
			$obj_type = $xs[0];
			$object_value = $xs[1];				
			$data_table = $xs[2];
			$chart_type = $xs[4];
			$chart_name = $xs[5];
			$chart_color = $xs[count($xs)-1];
			if(!(substr($chart_color,0,1)=="#" && strlen($chart_color)>1)) $chart_color="";
			if(strpos($object_value, "(*)")!==false){
				$object_value = str_replace("(*)", "", $object_value);
				$is_cummulative = true;
			}
			
			if($obj_type=="TAG"){
				$tag = $object_value;
				$realtimeTagsArray .= ($realtimeTagsArray?",":"")."{tag: '$tag', index: $seriesIndex}";
                $expression = "TAG:$tag";
			}
			else {
				$types=explode("~",$xs[3]);
				$vfield=$types[0];
				
				$datefield="OCCUR_DATE";
				$obj_type_id_field  = null;
				
				if($obj_type=="TANK") 			$obj_type_id_field="TANK_ID";
				else if($obj_type=="STORAGE") 	$obj_type_id_field="STORAGE_ID";
				else if($obj_type=="EQUIPMENT") $obj_type_id_field="EQUIPMENT_ID";
				else if($obj_type=="FLOW")		$obj_type_id_field="FLOW_ID";
				else if($obj_type=="EU_TEST") 	$obj_type_id_field="EU_ID";
				else if($obj_type=="KEYSTORE")	$obj_type_id_field="KEYSTORE";
				else if($obj_type=="DEFERMENT") $obj_type_id_field="DEFER_GROUP_TYPE";
                else if($obj_type=="EQUIPMENT")	$obj_type_id_field="EQUIPMENT_ID";				
				else if($obj_type=="ENERGY_UNIT"){
					$obj_type_id_field="EU_ID";
					$chart_type=$xs[5];
					$chart_name=$xs[6];
					$vfield=$xs[3];
					$types=explode("~",$xs[4]);
					$phase_type=$types[0];
					$eventType	= $types[count($types)-1];
                }

				if (!$obj_type_id_field || !$vfield || $vfield == "null") continue;
				
				if(count($types)>5){
					$ypos	=$types[4];
					$ytext	=$types[5];
				}
				else {
					$ytext	= "";
					$ypos	= "";
				}
				
				if($ytext=="") $ypos="";
				if($ypos!="" && $ytext!=""){
					$yatext=$ypos."^^^".$ytext;
					for($i=0;$i<count($ya);$i++)
						if($ya[$i]==$ytext){
							$yaIndex=$i;
							break;
					}
					if($yaIndex<0){
						array_push($ya,$yatext);
						$yaIndex=count($ya)-1;
					}
					$no_yaxis_config=false;
				}
                if($ypos == 'R') $item['axisPosition'] = 1;
                if($ytext) $item['axisTitle'] = $ytext;

				$model = \Helper::getModelName($data_table);
				
				if(substr($data_table, 0, 7) == "EU_TEST"){
					$is_eutest=true;
					$datefield="EFFECTIVE_DATE";
				}
				else if($obj_type=="DEFERMENT"){
					$is_deferment=true;
					$datefield="BEGIN_TIME";
					$obj_type_id_field="DEFER_TARGET";
				}
				
				$hasPhase = true;
				if (strpos($data_table, "V_")===0) {
				}				
						
				if ($obj_type_id_field=="KEYSTORE") {
					if (isset($model::$foreignKeystore)&&$model::$foreignKeystore) {
						$obj_type_id_field	=	$model::$foreignKeystore;
					}
					else continue;
				}
                
                $expression = "$data_table.$vfield.$object_value";
							
				$wheres	= [];
				
				if ($eventType&&$obj_type == "ENERGY_UNIT" && !$is_eutest && !$is_deferment&&$eventType>0) {
                    $wheres["EVENT_TYPE"]	= $eventType;
                    $expression.= ".$eventType";
				}
				
				if ($obj_type == "ENERGY_UNIT" && !$is_eutest && !$is_deferment&&$hasPhase&&$phase_type&&$phase_type>0) {
					$wheres["FLOW_PHASE"]	= $phase_type;
                    $expression.= ".$phase_type";
				}
				
				if(substr($data_table, -4) === 'PLAN' && count($types)>2 &&$types[2]>0){
					$wheres["PLAN_TYPE"]	= $types[2];
                    $expression.= ".{$types[2]}";
				}
				else if(substr($data_table, -8) === 'FORECAST' && count($types)>3&&$types[3]>0){
					$wheres["FORECAST_TYPE"]	= $types[3];
                    $expression.= ".{$types[3]}";
				}
				else if($obj_type == "ENERGY_UNIT" && substr($data_table, -5) === 'ALLOC' && count($types)>1 &&$types[1]>0){
					$wheres["ALLOC_TYPE"]	= $types[1];
                    $expression.= ".{$types[1]}";
				}
				else if($data_table=='KEYSTORE_INJECTION_POINT_DAY' && count($types>8)){
					if($vfield=='MIN_QTY_DAY' || $vfield=='MAX_QTY_DAY' || $vfield=='RECOMMEND_QTY_DAY'){
					}
					else{
						$wheres["KEYSTORE_ID"]	= $types[8];
                        $expression.= ".{$types[8]}";
                    }
				}				
            }
            $item['name'] = $chart_name;//preg_replace('/\s+/', '_@', $chart_name);
            $item['expression'] = $expression;
            if($chart_type == 'stacked'){
                $cfg["verticalLines"] = true;
                $chart_type = 'column';
            }
            if($chart_type) $item['type'] = $chart_type;
            if($chart_color) $item['color'] = str_replace('#', '', $chart_color);
            $cfg['items'][] = $item;
        }
        return $cfg;
    }

    public function convertCharts(){
        $charts = \DB::select("select id,OLD_CONFIG,title from ADV_CHART where OLD_CONFIG not like '{%'");
        foreach($charts as $chart){
            $config = $this->cvChart($chart->OLD_CONFIG);
            $config['title'] = $chart->title;
            \DB::statement("update ADV_CHART set CONFIG='".json_encode($config)."' where id={$chart->id}");
        }
    }

    public function convertDashboards(){
        $dbs = \DB::select("select id,name,config from dashboard");
        foreach($dbs as $db){
            $cs = json_decode($db->config);
			$obj = "";
			foreach($cs as $c){
				if($c->type!=1) continue;
				$id++;
				$ch = \DB::select("select title, config from adv_chart where id={$c->obj}")[0];
				$chartConfig = $ch->config;
				$chartConfig = str_replace(['<','>','"'],['&tt;','&gt;','&quot;'],$chartConfig);
				$obj .= '<UserObject id="'.$id.'" type="chart" chartId="'.$c->obj.'" chartName="'.$ch->title.'" label="&lt;div class=&quot;html-box center-box&quot; id=&quot;htmlBox89&quot; style=&quot;width:'.($c->size[0]-2).'px;height:'.($c->size[1]-2).'px&quot;&gt;&lt;p&gt;&lt;img height=&quot;32&quot; src=&quot;lib/mxgraph/stencils/clipart/chart.png&quot;&gt;&lt;br&gt;&lt;span class=&quot;chart-name&quot;&gt;Chart&lt;/span&gt;&lt;/p&gt;&lt;/div&gt;" config="'.$chartConfig.'">
<mxCell style="html=1;fillColor=none;strokeColor=none;" parent="1" vertex="1">
	<mxGeometry x="'.round($c->pos[0]).'" y="'.round($c->pos[1]).'" width="'.($c->size[0]).'" height="'.($c->size[1]).'" as="geometry"/>
</mxCell>
</UserObject>
';
			}
			if(!$obj) continue;
			$xml = '<mxGraphModel fit="1" autoUpdate="0">
<root>
<mxCell id="0"/>
<mxCell id="1" parent="0"/>
'.$obj.'</root>
</mxGraphModel>';
			$networkCode = 'DAS_'.$db->id;
			$attributes = ['CODE' => $networkCode];
			$item = ['CODE' => $networkCode, 'NAME' => $db->name, 'NETWORK_TYPE' => 2, 'XML_CODE' => $xml];
			\App\Models\Network::updateOrCreate($attributes,$item);
			echo "Dashboard {$db->name} converted<br>";
        }
    }

    public function runSchedule(){
    	$output = shell_exec('cd .. & php artisan schedule:run');
    	return response ()->json ($output);
    }
    public function gitPullMaster(){
    	$output = shell_exec('cd .. & git pull origin master');
    	return response ()->json ($output);
    }

    public function dbdiff(){
        function zz($database){
            $sql = "SELECT c.TABLE_NAME,c.COLUMN_NAME,c.COLUMN_TYPE,c.IS_NULLABLE,c.COLUMN_DEFAULT,d.CONSTRAINT_NAME,d.REFERENCED_TABLE_NAME,d.REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.TABLES a 
join INFORMATION_SCHEMA.COLUMNS c on a.TABLE_NAME=c.TABLE_NAME and a.TABLE_SCHEMA=c.TABLE_SCHEMA
left join INFORMATION_SCHEMA.KEY_COLUMN_USAGE d on d.TABLE_NAME=c.TABLE_NAME and d.COLUMN_NAME=c.COLUMN_NAME and d.TABLE_SCHEMA=c.TABLE_SCHEMA
where a.table_schema='$database' and a.TABLE_TYPE='BASE TABLE'";
			$db = \DB::connection()->getPdo();
			$query = $db->prepare($sql);
			$query->execute();
			$cols = [];
			while ($column = $query->fetch(\PDO::FETCH_ASSOC)) {
				$tableName = $column['TABLE_NAME'];
				$columnName = $column['COLUMN_NAME'];
				$def = ($column['COLUMN_DEFAULT']===''?"''":($column['COLUMN_DEFAULT']===0?'0':($column['COLUMN_DEFAULT']==='NULL'?'NULL':'')));
				$cols[$tableName][$columnName] = [
					'type' => $column['COLUMN_TYPE'].($column['IS_NULLABLE']=='YES'?' NULL':' NOT NULL').($def?" DEFAULT $def":''), 
					'ref_name' => $column['CONSTRAINT_NAME'], 
					'ref_table' => $column['REFERENCED_TABLE_NAME'],
					'ref_col' => $column['REFERENCED_COLUMN_NAME']
				];
			}
            return $cols;
        }
		
		$db1 = 'eb-tn-test-dev';
		$db2 = 'eb-basic';
		$sql = "SELECT a.TABLE_SCHEMA,a.TABLE_NAME,c.COLUMN_NAME,c.COLUMN_TYPE,c.IS_NULLABLE,c.COLUMN_DEFAULT,d.CONSTRAINT_NAME,d.REFERENCED_TABLE_NAME,d.REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.TABLES a 
join INFORMATION_SCHEMA.COLUMNS c on a.TABLE_NAME=c.TABLE_NAME and a.TABLE_SCHEMA=c.TABLE_SCHEMA
left join INFORMATION_SCHEMA.KEY_COLUMN_USAGE d on d.TABLE_NAME=c.TABLE_NAME and d.COLUMN_NAME=c.COLUMN_NAME and d.TABLE_SCHEMA=c.TABLE_SCHEMA
where a.TABLE_SCHEMA in ('$db1','$db2') and a.TABLE_TYPE='BASE TABLE'";
		$db = \DB::connection()->getPdo();
		$query = $db->prepare($sql);
		$query->execute();
		$cols = [];
		while ($column = $query->fetch(\PDO::FETCH_ASSOC)) {
			$tableName = $column['TABLE_NAME'];
			$columnName = $column['COLUMN_NAME'];
			$def = "{$column['COLUMN_DEFAULT']}";//($column['COLUMN_DEFAULT']===''?"''":($column['COLUMN_DEFAULT']===0?'0':($column['COLUMN_DEFAULT']===null?'':$column['COLUMN_DEFAULT'])));
			$cols[$column['TABLE_SCHEMA']][$tableName][$columnName] = [
				'type' => $column['COLUMN_TYPE'].($column['IS_NULLABLE']=='YES'?' NULL':' NOT NULL').(strlen($def)?" DEFAULT $def":''), 
				'ref_name' => $column['CONSTRAINT_NAME'], 
				'ref_table' => $column['REFERENCED_TABLE_NAME'],
				'ref_col' => $column['REFERENCED_COLUMN_NAME']
			];
		}
        $cols1 = $cols[$db1];
        $cols2 = $cols[$db2];
		//print_r($cols1);
        $newTables = [];
        $drops = [];
        $actions = [];
        foreach($cols2 as $table=>$tab){
            if(isset($cols1[$table])){
                foreach($tab as $column=>$col){
                    if(isset($cols1[$table][$column])){
                        if($cols1[$table][$column]['type'] != $col['type'] 
							&& strpos($cols1[$table][$column]['type'], '(20,7)')===false
							&& (strpos($cols1[$table][$column]['type'], 'double')===false || strpos($col['type'], 'decimal')===false)
							&& (strpos($cols1[$table][$column]['type'], 'int(10)')===false || strpos($col['type'], 'int(11)')===false)
						){
                            //change column type
                            $actions[$table][] = "alter table $table CHANGE `$column` `$column` {$col['type']};#{$cols1[$table][$column]['type']}";
                        }
                        if(
                            $cols1[$table][$column]['ref_name'] != $col['ref_name'] ||
                            $cols1[$table][$column]['ref_table'] != $col['ref_table'] ||
                            $cols1[$table][$column]['ref_col'] != $col['ref_col']
                        ){
                            //change column ref_name
                            if($cols1[$table][$column]['ref_table']){
                                //drop old ref
                                $actions[$table][] = "alter table $table DROP FOREIGN KEY {$cols1[$table][$column]['ref_name']}";
                            }
                            if($col['ref_table']){
                                //create new ref
                                $actions[$table][] = "alter table $table ADD CONSTRAINT {$col['ref_name']} FOREIGN KEY ({$column}) REFERENCES {$col['ref_table']}({$col['ref_col']})";
                            }
                        }
                    }
                    else{
                        //new column
						if(strpos($col['type'], ',3)')!==false){
							$col['type'] = str_replace('10,3', '20,7', $col['type']);
							$col['type'] = str_replace('12,3', '20,7', $col['type']);
						}
                        $actions[$table][] = "alter table $table ADD `$column` {$col['type']}";
						if($col['ref_name']){
							//create new ref
							$actions[$table][] = "alter table $table ADD CONSTRAINT {$col['ref_name']} FOREIGN KEY ({$column}) REFERENCES {$col['ref_table']}({$col['ref_col']})";
						}
                    }
                }
            }
            else
                $newTables[] = $table;
        }

        echo "\n#NEW TABLES:\n";
        foreach($newTables as $table)
            echo "#$table\n";

        foreach($actions as $table=>$sqls){
            echo "\n#TABLE $table:\n";
            foreach($sqls as $sql)
                echo "$sql;\n";
        }

        echo "\n#DROP TABLE:\n";
        foreach($cols1 as $table=>$tab){
            if(!isset($cols2[$table])){
                echo("-- drop table $table;\n");
            }
        }

        echo "\n#DROP COLUMN:\n";
        foreach($cols1 as $table=>$tab){
            if(isset($cols2[$table])){
                foreach($tab as $column=>$col){
                    if(!isset($cols2[$table][$column])){
                        echo("-- alter table $table drop $column;\n");
                    }
                }
            }
        }
    }

    public function showdb(){
        $tables = [];
        $driver = config('database.default');
        switch ($driver){
            case 'mysql':
                $tables = \DB::select('SHOW TABLES');
                break;
            case 'oracle':
                $tables = \DB::table('user_tables')->select('table_name')->orderBy('table_name')->get();
                break;
        }
//        $tables = \DB::connection()->getDoctrineSchemaManager()->listTableNames();
        return view ( 'admin.table_structure_'.$driver,
            ['tables' => $tables,
            'driver' => $driver,
            "database" => \DB::connection()->getDatabaseName()]);
    }
	public function sendTestEmail(){
		$subjectName 	= "Test email";
		$data 			= ['content' => 'Test content '.date('Y-m-d H:i:s')];
		$emails			= ['tung0980@gmail.com'];
		return \Helper::sendEmail($emails,$subjectName,$data);
		
	}
	public function test(){
        echo '\"';
        return;
        $d = \Carbon\Carbon::parse('2020-05-26')->subWeeks(1)->startOfYear();
		print_r($d);
	}
	public function loaddatagrid01(Request $request){
		$date = $request->date;
		$prevDate = \Carbon\Carbon::parse($date)->addDays(-1)->format('Y-m-d');
		$nextDate = \Carbon\Carbon::parse($date)->addDays(1)->format('Y-m-d');
		$query = $request->querystring;
		$query = str_replace(['$date(-1)', '$date(+1)', '$date'], [$prevDate, $nextDate, $date], $query);
		$tmp = \DB::select($query);
		if(count($tmp))
		{
			$style = $request->style;
			if($style){
				$style = str_replace('#datagrid', '.datagrid01', $style);
			}
			$header = "";
			$hh= (array) $tmp[0];
			$attributes = array_keys($hh);
			if($request->header){
				$hs = explode("\n", $request->header);
			}
			else{
				$hs = $attributes;
			}
			foreach($hs as $h)
				$header .= "<th>$h</th>";
			$html = "<style>
			.datagrid01 {
				border-collapse: collapse;
			}
			.datagrid01 th, .datagrid01 td {
				border: 1px solid gray;
				padding: 3px 8px;
				text-align: right;
			}
			$style</style>
			<table class='datagrid01'><tr>$header</tr>";
			foreach($tmp as $row){
				$html .= "<tr>";
				foreach($attributes as $a){
					$html .= "<td>{$row->$a}</td>";
				}
				$html .= "</tr>";
			}
			$html .= "</table>";
			return $html;
		}
		else
			return "";
	}
}
