<?php
use App\Models\PdLiftingAccountMthData;
use App\Models\StorageDataValue;

$configuration 	= auth()->user()->getConfiguration();
$format 		= $configuration['time']['DATE_FORMAT_CARBON'];//'m/d/Y';
$isOracle		= config('database.default')==='oracle';
$balance 		= isset($postData['txt_balance'])?$postData['txt_balance']:"";
$cargoSize 		= isset($postData['cargoSize'])?$postData['cargoSize']:0;

//if(!is_numeric($balance))
//	$balance = 0;
if(!is_numeric($cargoSize)) $cargoSize = -1;

if(!$storage_id){
	echo "No data ++";
	exit;
}

if ($pdLiftingAccounts->count()<=0) {
	echo "No data --";
	exit;
}

$shipper_id = [];
$shipper_r1 = [];
$shipper_r2 = [];
$cargo_sizes = [];
$shipper_la = [];
$shipper_total_pct = [];

if ($liftingShippers&&$liftingShippers->count()>0) {
	foreach($liftingShippers as $row ){
		$shipperId 						= $row->SHIPPER_ID;
		$laId 							= $row->LA_ID;
		$shipper_r1[$shipperId] 		= $row->SHIPPER_NAME;
		$shipper_r2[$shipperId] 		= (array_key_exists($shipperId,$shipper_r2)?$shipper_r2[$shipperId]."+":"").$ent_r2[$laId];
		$shipper_total_pct[$shipperId] 	= array_key_exists($shipperId,$shipper_total_pct)?
											$shipper_total_pct[$shipperId]	+ $interest_percents[$laId]:
											$interest_percents[$laId];
		$cargoSize 						= array_key_exists("cargo_size_$shipperId",$postData)?$postData["cargo_size_$shipperId"]:0;
		$cargo_sizes[$shipperId] 		= ($cargoSize>0)?$cargoSize:$row->CARGO_SIZE;
		$shipper_la[$shipperId][]		= $laId;
	}
}

echo '<thead><tr>
	<td colspan="3" rowspan="2" style="background:#dddddd"><b>Open balance &gt; </b><input id="txt_balance" name="txt_balance" value="'.$balance.'" style="width:100px" onkeypress="return txt_balance_keypress(event)"></td>
	<td colspan="'.count($ent_r1).'" class="group1_th"><b>Entitlement</b><br> <input type="button" style="font-weight:normal;font-size:8pt;height:25px" onclick="showConfig(1)" value="Config"></td>
	<td colspan="'.count($shipper_r1).'" class="group2_th"><b>Plan Cargo</b><br><input type="button" style="font-weight:normal;font-size:8pt;height:25px" onclick="genCargoEntry()" value="Generate All Cargo Entry"></td>
	<td colspan="'.count($ent_r1).'" class="group3_th"><b>Schedule Cargo</b></td>
</tr><tr>';
foreach($ent_r1 as $id => $name){
	echo "<td id='ent_la_{$id}' class='group1_th'>$name</td>";
}
foreach($shipper_r1 as $id => $name){
	echo "<td id='shipper_{$id}' class='group2_th'>$name</td>";
}
foreach($ent_r1 as $id => $name){
	echo "<td id='sche_la_{$id}' class='group3_th'>$name</td>";
}
echo '</tr><tr>
	<td rowspan="2" style="background:#dddddd"><b>Date</b></td>
	<td rowspan="2" style="background:#dddddd"><b>Openning balance</b><br> <input type="button" style="font-weight:normal;font-size:8pt;height:25px" onclick="showConfig(0)" value="Config"></td>
	<td rowspan="2" style="background:#dddddd"><b>Plan cargo</b></td>
';
foreach($ent_r2 as $id => $name){
	echo "<td id='ent_ba_{$id}' class='group1_th'>$name</td>";
}
foreach($shipper_r2 as $id => $name){
	echo "<td id='shipper_ba_{$id}' class='group2_th'>$name</td>";
}
foreach($ent_r2 as $id => $name){
	echo "<td rowspan='2' id='sche_ba_{$id}' class='group3_th'>$name</td>";
}
echo '</tr><tr>';
foreach($interest_percents as $id => $interest_rate){
	echo "<td class='group1_th'>{$interest_rate}%</td>";
}
foreach($cargo_sizes as $id => $cargo_size){
	echo "<td class='group2_th'><input name='cargo_size_{$id}' style='width:100px;text-align:center' value='$cargo_size'></td>";
}
/*
foreach($interest_percents as $id => $name){
	echo "<td class='group3_th'></td>";
}
*/
echo '</tr></thead>';
if(!$balance) exit;
echo '<tbody>';
//if(count($la_data)==0) {echo "<tr><td colspan='6' style='color:orange;text-align:center'>No entitlement data</td></tr></tbody>"; exit;}

$vals = [];
$last_value = null;
$last_date = null;
$last_minus = 0;

$d1 = $date_from;// date ("Y-m-d", strtotime($date_from));
$d2 = $date_to;

if($bal_data == null){
	$storageDataValues	= 	StorageDataValue::where("STORAGE_ID","=",$storage_id)
							->whereDate("OCCUR_DATE",">=",$date_from)
							->whereDate("OCCUR_DATE","<=",$date_to)
							->select("OCCUR_DATE",\DB::raw("max(AVAIL_SHIPPING_VOL) as OPENING_BALANCE "))
							->groupBy("OCCUR_DATE")
							->orderBy("OCCUR_DATE")
							->get();
	
	foreach($storageDataValues as $rowx ){
		if($bal_data == null) $bal_data = [];
		$bal_data[$rowx->OCCUR_DATE] = $rowx->OPENING_BALANCE;
	}
}
$log="";
$monthly_balance	= [];

// while(strtotime($d1) <= strtotime($d2)){
while($d1->lte($d2)){
	$row = [];
	$dateString			= $d1->toDateString();
	$dateStringDisplay	= $d1->format($format);
	$row["OCCUR_DATE"] = $dateString;
	if(isset($bal_data[$dateString])){
		$row["OPENING_BALANCE"] = $bal_data[$dateString];
	}
	else{
		$row["OPENING_BALANCE"] = "";
	}
	
	$v = $row["OPENING_BALANCE"];
	$rowvals = [];
	$rowvals["OCCUR_DATE"] = $dateStringDisplay;
	$rowvals["BALANCE"] = ($v?$v:$last_value);
	$rowvals["PLAN"] = "";
	
	//Calculate by adding Monthly balance
	$startOfMonth		= $d1->copy()->startOfMonth();
	$first_day_month 	= $startOfMonth->toDateString();
// 	$first_day_month 	= date ("Y-m-01", strtotime($d1));
// 	$is_begin_month = ($d1 == $first_day_month);
	$is_begin_month 	= $d1->eq($startOfMonth);
	
	$has_month_bal = false;
	$has_month_adj = false;
	
	foreach($interest_percents as $id => $val){
		$add_val = 0;
		if(count($la_data)==0){
/* 			if(!isset($vals["ENT_LA_$id"]))
				$vals["ENT_LA_$id"] = $val;
			$vals["ENT_LA_$id"] += 200;
			$v = $vals["ENT_LA_$id"];
			$add_val = 200; */
		}
		else{
			$vals["ENT_LA_$id"]	= array_key_exists("ENT_LA_$id", $vals)?$vals["ENT_LA_$id"]:0;
			if(array_key_exists($dateString, $la_data)) $vals["ENT_LA_$id"] += $la_data[$dateString]["$id"];
			$v = $vals["ENT_LA_$id"];
			if($last_date&&array_key_exists($last_date, $la_data)) $add_val = $la_data[$last_date]["$id"];
		}
		$month_bal["ENT_LA_$id"] = "";
		$month_adj["ENT_LA_$id"] = "";
		if($is_begin_month){
			if(!isset($monthly_balance["$id"][$first_day_month])){
				$val2		= $isOracle?\DB::raw("NVL(BAL_VOL,0)+NVL(ADJUST_VOL,0) VAL"):
										\DB::raw("ifnull(BAL_VOL,0)+ifnull(ADJUST_VOL,0) VAL");
				$r_bal		= PdLiftingAccountMthData::where("LIFTING_ACCOUNT_ID",'=',$id)
								->where("ADJUST_CODE",'=',2)
								->whereDate("BALANCE_MONTH",'=',$startOfMonth)
								->select($val2)
								->first();
				$r_bal		= $r_bal?$r_bal->VAL:0;
				
				$val3		= $isOracle?\DB::raw("NVL(BAL_VOL,0)+NVL(ADJUST_VOL,0) VAL"):
										\DB::raw("ifnull(BAL_VOL,0)+ifnull(ADJUST_VOL,0) VAL");
				$r_adj		= PdLiftingAccountMthData::where("LIFTING_ACCOUNT_ID",'=',$id)
							->where("ADJUST_CODE",'=',3)
							->whereDate("BALANCE_MONTH",'=',$startOfMonth)
							->select($val3)
							->first();
				
				$r_adj		= $r_adj?$r_adj->VAL:0;
				$month_bal["ENT_LA_$id"] = $r_bal;
				$month_adj["ENT_LA_$id"] = $r_adj;
				if($month_bal["ENT_LA_$id"]) $has_month_bal = true;
				if($month_adj["ENT_LA_$id"]) $has_month_adj = true;
				$monthly_balance["$id"][$first_day_month] 	= $r_bal	+ $r_adj;
			}
		}
		$rowvals["ENT_LA_$id"] = $v + (array_key_exists($id, $monthly_balance) && array_key_exists($first_day_month, $monthly_balance["$id"])?
										$monthly_balance["$id"][$first_day_month]:0);
		if(!$row["OPENING_BALANCE"])
			$rowvals["BALANCE"] += $add_val;
		//echo "<td id='ent_val_{$id}_{$row[ID]}' class='group1_td'>$v</td>";
	}
	if(!$row["OPENING_BALANCE"])
		$rowvals["BALANCE"] -= $last_minus;
	foreach($shipper_r1 as $id => $val){
		$v = 0;
		foreach($shipper_la["$id"] as $key => $la_id){
			$v += $rowvals["ENT_LA_$la_id"];
		}
		$rowvals["SHIPPER_$id"] = $v;
		//echo "<td id='shipper_val_{$id}_{$row[ID]}' class='group2_td'>$v</td>";
	}
	foreach($ent_r2 as $id => $val){
		//echo "<td id='sche_val_{$id}_{$row[ID]}' class='group3_td'></td>";
		$rowvals["SCHE_LA_$id"] = "";
	}
	$shipper_max_id = -1;
	$highlight = [];
	$last_minus = 0;
	$rowvals["GEN_CARGO"] = "";
	if($balance > 0){
		if($rowvals["BALANCE"] > $balance){
			$rowvals["PLAN"] = "Y";
			//find max shipper
			$max = -1;
			foreach($shipper_r1 as $id => $val){
				$v = 0;
				if($rowvals["SHIPPER_$id"] > $max){
					$max = $rowvals["SHIPPER_$id"];
					$shipper_max_id = $id;
				}
			}
			if($shipper_max_id > 0){
				$highlight[] = "SHIPPER_$shipper_max_id";
				$max = -1;
				$max_la_id = -1;
				$sum = 0;
				foreach($shipper_la["$shipper_max_id"] as $key => $la_id){
					//$dx = $cargo_sizes["$shipper_max_id"]/count($shipper_la["$shipper_max_id"]);
					$dx = round($cargo_sizes["$shipper_max_id"]*$interest_percents["$la_id"]/$shipper_total_pct["$shipper_max_id"],2);
					$rowvals["SCHE_LA_$la_id"] = $dx;
					$highlight[] = "ENT_LA_$la_id";
					$sum += $dx;
					if($dx > $max){
						$max = $dx;
						$max_la_id = $la_id;
					}
				}
				$rowvals["GEN_CARGO"] = "{\"la_name\":\"".$ent_r1[$max_la_id]."\",\"shipper_name\":\"".$shipper_r1[$shipper_max_id]."\",\"la_id\":\"$max_la_id\",\"storage_id\":\"$storage_id\",\"req_date\":\"$dateString\",\"req_date_disp\":\"$dateStringDisplay\",\"qty\":\"$sum\"}";
				$last_minus = $cargo_sizes["$shipper_max_id"];
			}
		}
	}
	if($has_month_bal){
		echo "<tr><td colspan='3' class='td_monnth_bal'>Monthly Balance</td>";
		foreach($rowvals as $key => $value){
			$html = "";
			if(substr($key,0,4) == "ENT_"){
				$class = 'td_monnth_bal';
				$html = $month_bal[$key];
			}
			else if(substr($key,0,4) == "SHIP")
				$class = 'group2_td';
			else if(substr($key,0,4) == "SCHE")
				$class = 'group3_td';
			else
				$class = "";
			if($class)
				echo "<td class='$class'><b>$html</b></td>";
		}
		echo "</tr>";
	}
	if($has_month_adj){
		echo "<tr><td colspan='3' class='td_monnth_bal'>Monthly Adjust</td>";
		foreach($rowvals as $key => $value){
			$html = "";
			if(substr($key,0,4) == "ENT_"){
				$class = 'td_monnth_bal';
				$html = $month_adj[$key];
			}
			else if(substr($key,0,4) == "SHIP")
				$class = 'group2_td';
			else if(substr($key,0,4) == "SCHE")
				$class = 'group3_td';
			else
				$class = "";
			if($class)
				echo "<td class='$class'><b>$html</b></td>";
		}
		echo "</tr>";
	}
	echo "<tr>";
	foreach($rowvals as $key => $value){
		if($key=="GEN_CARGO") continue;
		$rowspan = "";
		if(substr($key,0,4) == "ENT_"){
			$class = 'group1_td';
		}
		else if(substr($key,0,4) == "SHIP")
			$class = 'group2_td';
		else if(substr($key,0,4) == "SCHE")
			$class = 'group3_td';
		else
			$class = "";
		/*if($rowvals["PLAN"]=="Y" && $rowvals["GEN_CARGO"]){
			$class .= ($class==""?"":" ")."td_has_plan";
			if($key != "BALANCE" && $key != "PLAN")
				$rowspan = 2;
		}*/
		if($rowvals["PLAN"]=="Y"){
			$class .= ($class==""?"":" ")."td_has_plan";
		}
		if($key=="BALANCE" && !$row["OPENING_BALANCE"])
			$class .= ($class==""?"":" ")."td_cal_balance";
		if($key == "PLAN")
			$class .= ($class==""?"":" ")."td_plan";
		if (in_array($key, $highlight))
			$class .= ($class==""?"":" ")."td_highlight";
		if($key=="SHIPPER_$shipper_max_id"){
			$value = "<div title='Generate Cargo Entry' gen_cargo='$rowvals[GEN_CARGO]' onclick=\"genCargoEntry(this)\" class='box_gen_cargo'>GC</div>".$value;
		}
		echo "<td class='$class'".($rowspan>1?" rowspan='$rowspan'":"").">$value</td>";
	}
	echo "</tr>";
/*	if($rowvals["GEN_CARGO"]){
		echo "<tr><td class='td_gen_cargo' gen_cargo='$rowvals[GEN_CARGO]' colspan='2' onclick=\"genCargoEntry(this)\">Create Cargo Entry</td></tr>";
	}*/
	if($shipper_max_id > 0){
		foreach($shipper_la["$shipper_max_id"] as $key => $la_id){
			//$dx = $cargo_sizes["$shipper_max_id"]/count($shipper_la["$shipper_max_id"]);
			$dx = round($cargo_sizes["$shipper_max_id"]*$interest_percents["$la_id"]/$shipper_total_pct["$shipper_max_id"],2);
			$rowvals["ENT_LA_$la_id"] -= $dx;
			$vals["ENT_LA_$la_id"] -= $dx;
		}
	}
	$last_value = $rowvals["BALANCE"];
	$last_date = $dateString;
	$d1->addDay();
// 	$d1 = date ("Y-m-d", strtotime("+1 day", strtotime($d1)));
}
//echo "<tr><td colspan='6' style='color:orange;text-align:center'>$log</td></tr>";
echo "</tbody>";
?>
