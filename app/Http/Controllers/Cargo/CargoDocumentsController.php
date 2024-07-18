<?php
namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\CodeController;
use App\Models\PdCargo;
use App\Models\PdVoyage;
use App\Models\PdVoyageDetail;
use App\Models\PdDocumentSetData;
use App\Models\PdReportList;
use App\Models\PdDocumentSetList;
use Illuminate\Http\Request;

class CargoDocumentsController extends CodeController {

	public function __construct() {
		parent::__construct();
		$this->detailModel = "PdDocumentSetData";
	}
	
	public function getFirstProperty($dcTable){
		return null;
	}

	public function saveDetail(Request $request){
		$input = $request->all();
		$sqls=[];

		//Update - Delete data
		$ids = explode(",",$input['DETAIL_LIST_ID']);
		foreach($ids as $id) {
			$sql = "";
			if($id>0) {
				
				//Delete pd_document_set_contact_data
				$sql="DELETE FROM pd_document_set_contact_data WHERE DOCUMENT_SET_DATA_ID=$id";
				array_push($sqls,$sql);
				
				if(array_key_exists('DETAIL_ID'.$id, $input) && array_key_exists('DETAIL_DOCUMENT_ID'.$id, $input))
				if($input['DETAIL_ID'.$id] && $input['DETAIL_DOCUMENT_ID'.$id]) {
					//continue;
					//Insert-Update data pd_document_set_contact_data 
					for ($n=1;$n<=10;$n++) {
						//&& ( $input['DETAIL_ORGINAL_ID'.$n.'_'.$id] != '' || $input['DETAIL_NUMBER_COPY'.$n.'_'.$id] != '' ) 
						if(array_key_exists('DETAIL_BA_ADDRESS'.$n, $input))
						if($input['DETAIL_BA_ADDRESS'.$n]) {
							
							$sql = "INSERT INTO pd_document_set_contact_data(DOCUMENT_SET_DATA_ID,CONTACT_ID,ORGINAL_ID,NUMBER_COPY) "
									. "VALUE($id,'".$input['DETAIL_BA_ADDRESS'.$n]."','".$input['DETAIL_ORGINAL_ID'.$n.'_'.$id]."','".$input['DETAIL_NUMBER_COPY'.$n.'_'.$id]."')";
							array_push($sqls,$sql);
						}
					}
				} else {
					//delete data
					$sql="DELETE FROM pd_document_set_data WHERE ID='".$id."';";
					array_push($sqls,$sql);
				}
			}
		}
		
		//get info
		$voyage_id = $input['DETAIL_VOYAGE_ID'];
		$cargo_id = $input['DETAIL_CARGO_ID'];
		$parcel_no = $input['DETAIL_PARCEL_NO'];
		$liffting_account = (int)$input['DETAIL_LIFFTING_ACCOUNT'];
		
		// add new data
		$new_ind = $input['new_ind'];
		for ($i=0; $i<$new_ind; $i++)
		{
			$j = $i + 1 ; $sql = "";
			if($input['DETAIL_DOCUMENT_ID_NEW'.$j] > 0) { 
				\DB::table('pd_document_set_data')->insert([
					'VOYAGE_ID' => $voyage_id,
					'CARGO_ID' => $cargo_id,
					'PARCEL_NO' => $parcel_no,
					'DOCUMENT_ID' => $input['DETAIL_DOCUMENT_ID_NEW'.$j],
					'LIFTING_ACCOUNT' => $liffting_account
				]);
				$newId = \DB::getPdo()->lastInsertId();;
				for ($n=1;$n<=10;$n++) {
					if(array_key_exists('DETAIL_BA_ADDRESS'.$n, $input)) 
					if($input['DETAIL_BA_ADDRESS'.$n] != '') {
						$sql = "INSERT INTO pd_document_set_contact_data(DOCUMENT_SET_DATA_ID,CONTACT_ID,ORGINAL_ID,NUMBER_COPY) "
								. "VALUE($newId,'".$input['DETAIL_BA_ADDRESS'.$n]."','".$input['DETAIL_ORGINAL_ID_NEW'.$n.'_'.$j]."','".$input['DETAIL_NUMBER_COPY_NEW'.$n.'_'.$j]."')";
						array_push($sqls,$sql);
					}
				} 
			}
		}
		
		// Query Insert - Update Data
		foreach($sqls as $sql)
		{
			$sql=str_replace("''","null",$sql);
			//echo $sql.';';
			\DB::statement($sql);
		}
	}
	
	public function applyReportSet(Request $request){
		$set_id=$request->id;
		$sql="select b.ID,b.NAME from pd_document_set_list a, pd_report_list b where a.SET_ID=$set_id and a.DOCUMENT_ID=b.ID order by b.ORDER";
		$rs=\DB::select($sql);
		foreach($rs as $row) {
			echo "$row->ID:$row->NAME;";
		}
	}
	
	public function loadDetail(Request $request){
		$cargoId = $request->cargoId;
		$voyageId = $request->voyageId;
		$parcelNo = trim($request->parcelNo);
		$lifftingAcount=$request->lifftingAcount;
		
		function selectComboTag($combo,$selected_value,$default_value="") {
			//$tmp = str_replace(" selected","", $combo);
			$v=(($selected_value===null || $selected_value==='')?$default_value:$selected_value);
			return str_replace("value='$v'", "value='$v' selected", $combo);
		}
		
		function getOption($table, $selected = null) {
			$sql = "SELECT ID, NAME FROM $table";
			$rs = \DB::select($sql);
			$result = "";
			foreach($rs as $r)
				$result.="<option ".($r->ID==$selected? "selected": "")." value='$r->ID'>$r->NAME</option>";
			return $result;
		}
		$pd_code_orginality=getOption('pd_code_orginality');
		$pd_code_number=getOption('pd_code_number');
		$ba_address=getOption('ba_address',null,false); 
		$sql="SELECT a.DOCUMENT_ID,a.ID,a.CARGO_ID,b.NAME FROM pd_document_set_data a INNER JOIN pd_report_list b WHERE a.DOCUMENT_ID = b.ID AND a.VOYAGE_ID = $voyageId AND a.CARGO_ID = $cargoId AND a.PARCEL_NO = '$parcelNo'";
		
		$rs = \DB::select($sql);

		$strHtml ="";
		$k = 0;	
		$ids = "";
		foreach($rs as $row) { 
			//get data
			$i = $row->ID;
			$aryNumber = [];
			$aryOrginal = [];
			$aryContact = [];
			$k = 0;
			$sql="SELECT * FROM pd_document_set_contact_data WHERE DOCUMENT_SET_DATA_ID = $i";
			$resultCD = \DB::select($sql);
			foreach($resultCD as $rowCD)
			{ 
					$k++; 
					$aryNumber[$k] = $rowCD->NUMBER_COPY;
					$aryOrginal[$k] = $rowCD->ORGINAL_ID;
					$aryContact[$k] = $rowCD->CONTACT_ID;
			}
				
			if($i%2 ==0) $bgcolor="#eeeeee"; else $bgcolor="f8f8f8";
			$ids = ($ids? (string)$ids.",".(string)$i: (string)$i);

			$strHtml .= "<tr class='row_activity' activity_id='$i' id='RD_$i' bgcolor='$bgcolor'>
<td id = 'D{$i}'><a href='javascript:deleteRow($i,true)'>Delete</a></td><td>{$row->NAME}
<input type='hidden' name='DETAIL_DOCUMENT_ID{$i}' id='input_activity_{$row->DOCUMENT_ID}' value='{$row->DOCUMENT_ID}' />
<input type='hidden' name='DETAIL_ID{$i}' id='DETAIL_ID{$i}' value='{$i}' /></td>";
			
			for($n=1;$n<=10;$n++) {
				
				$pd_code_orginality2=selectComboTag($pd_code_orginality, $n > $k ? '' : $aryOrginal[$n]);
				$pd_code_number2=selectComboTag($pd_code_number, $n > $k ? '' : $aryNumber[$n]);
				
				$strHtml .= "<td id='DETAIL_NUMBER_COPY_ORGINAL_ID{$n}_{$i}'".($n > $k ? " style='display:none'" : "").">
<select name='DETAIL_ORGINAL_ID{$n}_{$i}' id='DETAIL_ORGINAL_ID{$n}_{$i}' ><option value=''></option>$pd_code_orginality2</select>
<select name='DETAIL_NUMBER_COPY{$n}_{$i}' id='DETAIL_NUMBER_COPY{$n}_{$i}' ><option value=''></option>$pd_code_number2</select></td>";
			}
			$strHtml .= "</tr>";
		}
				 
		//----------------------------- ------------------------------------------
		$strHtml2 ="";
		$strHtml2 = '<table><thead><tr><th style="width:50px; background-color:#FFF">Delete</th><th style=""><div style="width:100px">Report Name</div></th>';
		for($n=1;$n<=10;$n++) {
			$ba_address2=selectComboTag($ba_address, $n > $k ? '' : $aryContact[$n]);
			$strHtml2 .= "<th".($n > $k ? " style='display:none'" : "")." id='DETAIL_BA_ADDRESS_TH{$n}'><select name='DETAIL_BA_ADDRESS{$n}' id='DETAIL_BA_ADDRESS{$n}' ><option value=''></option>$ba_address2</select></th>";
		}
		$strHtml2 .= '</tr></thead><tbody id="body_data_detail">';

		$strHtml = $strHtml2.$strHtml;
		//--------------------------- new row ------------------------------------------ ------------------------------------------

		$strHtml .= "<tr id = 'newRow0' style='display:none' class='row_activity'>
<td id = 'D0'><a href='javascript:deleteRow(0, false)'>Delete</a></td>
<td><input type='hidden' name='DETAIL_DOCUMENT_ID_NEW0' id='input_activity_-000' value='-000'>activity_text_holder</td>";

		for($n=1;$n<=10;$n++) {
			$strHtml .= "<td id='DETAIL_NUMBER_COPY_ORGINAL_ID{$n}_0'".($n > $k ? " style='display:none'" : "" ).">
<select name='DETAIL_ORGINAL_ID_NEW{$n}_0' id='DETAIL_ORGINAL_ID_NEW{$n}_0' ><option value=''></option>$pd_code_orginality</select>
<select name='DETAIL_NUMBER_COPY_NEW{$n}_0' id='DETAIL_NUMBER_COPY_NEW{$n}_0' ><option value=''></option>$pd_code_number</select></td>";
		}

		$strHtml .= "</tr>
<input type = 'hidden' name = 'DETAIL_LIST_ID' value = '$ids'>
<input type = 'hidden' name = 'DETAIL_VOYAGE_ID' value = '$voyageId'>
<input type = 'hidden' name = 'DETAIL_CARGO_ID' value = '$cargoId'>
<input type = 'hidden' name = 'DETAIL_PARCEL_NO' value = '$parcelNo'>
<input type = 'hidden' name = 'DETAIL_LIFFTING_ACCOUNT' value = '$lifftingAcount'>
</tbody></table>
<script> globalTotalContact = $k; </script>";

		return $strHtml;
	}

	public function getProperties($dcTable,$facility_id=false,$occur_date=null,$postData=null){
		if ($dcTable==PdDocumentSetData::getTableName()) {
			$properties = collect([
					(object)['data' =>	'ID',		'title' => 'Action',		'width'	=>	50,	'INPUT_TYPE'=>3,	'DATA_METHOD'=>2,'FIELD_ORDER'=>1],
					(object)['data' =>	"NAME"	,	'title' => 'Report Name',	'width'	=>	150,'INPUT_TYPE'=>1,	'DATA_METHOD'=>5,'FIELD_ORDER'=>2],
			]);
			$uoms		= [];
			$uoms[]		= \App\Models\PdCodeOrginality::all();
			$uoms[]		= \App\Models\PdCodeNumber::all();
				
			$selects 	= ['BaAddress'		=> \App\Models\BaAddress::all()];
			$results 	= ['properties'		=> $properties,
							'selects'		=> $selects,
 	 						'suoms'			=> $uoms,
			];
			return $results;
		}
		return parent::getProperties($dcTable,$facility_id,$occur_date,$postData);
	}
	
	public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
 	if (!array_key_exists("Storage", $postData)) {
 		return ['dataSet'=>$this->getDetailData($postData["id"], $postData, $properties)];
 	}
		$storageId			= $postData['Storage'];
		$date_end 			= array_key_exists('date_end', $postData)?$postData['date_end']:null;
 	$date_end			= $date_end&&$date_end!=""?\Helper::parseDate($date_end):Carbon::now();
		$pd_voyage 			= PdVoyage::getTableName();
		$pd_cargo 			= PdCargo::getTableName();
		$pd_voyage_detail 	= PdVoyageDetail::getTableName();
		
		$column = array();
		$ObjColumn = $properties['properties'];
		foreach ($ObjColumn as $p){
			array_push($column, "$pd_voyage.$p->data");
		}
		array_push($column, "$pd_voyage_detail.ID AS DT_RowId");
		array_push($column, "$pd_voyage.ID AS VOYAGE_ID");
		array_push($column, "$pd_voyage_detail.PARCEL_NO as MASTER_NAME");
		
		$dataSet = 	PdVoyage::join($pd_cargo, "$pd_voyage.CARGO_ID",		 '=', "$pd_cargo.ID")
					->join($pd_voyage_detail, "$pd_voyage_detail.VOYAGE_ID", '=', "$pd_voyage.ID")
					->where(["$pd_cargo.STORAGE_ID"	=> $storageId])
					->whereDate('SCHEDULE_DATE', '>=', $occur_date)
					->whereDate('SCHEDULE_DATE', '<=', $date_end)
					->orderBy("DT_RowId")
					->get($column);
		
		return ['dataSet'=>$dataSet];
	}
	
	public function getDetailData($id,$postData,$properties){
		$voyageId			= $postData['voyageId'];
		$cargoId			= $postData['cargoId'];
		$parcelNo			= $postData['parcelNo'];
		$lifftingAcount		= $postData['lifftingAcount'];
		
		$pdDocumentSetData	= PdDocumentSetData::getTableName();
		$pdReportList		= PdReportList::getTableName();
		$dataSet 			= PdDocumentSetData::with("PdDocumentSetContactData")
								->join($pdReportList,
									"$pdDocumentSetData.DOCUMENT_ID",
									'=',
									"$pdReportList.ID")
								->where("$pdDocumentSetData.VOYAGE_ID",'=',$voyageId)
								->where("$pdDocumentSetData.CARGO_ID",'=',$cargoId)
								->where("$pdDocumentSetData.PARCEL_NO",'=',$parcelNo)
								->select(
										"$pdDocumentSetData.ID as DT_RowId",
										"$pdDocumentSetData.ID",
										"$pdDocumentSetData.DOCUMENT_ID",
										"$pdDocumentSetData.CARGO_ID",
										"$pdReportList.NAME as NAME",
										"$pdReportList.CODE as CODE"
// 										"$pdReportList.ID as ACTIVITY_ID"
										)
								->get();
		return $dataSet;
	}
	
	public function activities(Request $request){
		$postData 					= $request->all();
		$set_id						= $postData['id'];
		
		$pdDocumentSetList 	= PdDocumentSetList::getTableName();
		$pdReportList 		= PdReportList::getTableName();
		
		$dataSet 					= PdDocumentSetList::join($pdReportList,
											"$pdDocumentSetList.DOCUMENT_ID",
											'=',
											"$pdReportList.ID")
										->where("SET_ID",'=',$set_id)
 										->select("$pdReportList.ID as ID","$pdReportList.CODE as CODE","$pdReportList.NAME")
										->get();
		$results = ['updatedData'	=>[$this->detailModel	=> $dataSet],
					'postData'		=>$postData];
		return response()->json($results);
	}
}
