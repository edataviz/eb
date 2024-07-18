<?php

namespace App\Http\Controllers;
use App\Models\Formula;
use App\Models\FoGroup;
use App\Models\FoVar;
use App\Models\Facility;
use App\Models\LoArea;
use App\Models\CodeFlowPhase;
use App\Models\CodeAllocType;
use App\Models\CodeEventType;
use App\Models\LoProductionUnit;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class FormulaController extends EBController {
	
	public function _index() {
		
		$fo_group = $this->getFoGroup();
		$code_flow_phase = CodeFlowPhase::all(['ID', 'NAME']);
		$code_alloc_type = CodeAllocType::all(['ID', 'NAME']);
		$code_event_type = CodeEventType::all(['ID', 'NAME']);
		$loProductionUnit = LoProductionUnit::all(['ID', 'NAME']);
		
		return view ( 'front.formula', ['fo_group'=>$fo_group,
										'code_flow_phase'=>$code_flow_phase, 
										'code_alloc_type'=>$code_alloc_type,
										'code_event_type'=>$code_event_type,
										'loProductionUnit'=>$loProductionUnit
				
		]);
	}
	
	public function editGroupName(Request $request){
        \Helper::setGetterUpperCase();
		$data = $request->all ();	
		
		FoGroup::where(['ID'=>$data['id']])->update(['NAME'=>$data['groupName']]);
		
		$fo_group = $this->getFoGroup();
		
		return response ()->json ( $fo_group );
	}
	
	private function getFoGroup(){
		return FoGroup::all(['ID', 'NAME']);
	}
	
	public function addGroupName(Request $request){
        \Helper::setGetterUpperCase();
		$data = $request->all ();
	
		FoGroup::insert(['NAME'=>$data['groupName']]);
	
		$fo_group = $this->getFoGroup();

		return response ()->json ( $fo_group );
	}
	
	public function deleteGroup(Request $request){
        \Helper::setGetterUpperCase();
		$data = $request->all ();
		
		DB::beginTransaction ();
		try {
			//\DB::enableQueryLog ();
			FoVar::whereIn('FORMULA_ID', function($query) use ($data)
		    {
		        $query->select('ID')
		              ->from('FORMULA')
		              ->where(['GROUP_ID' => $data['id']]);
		    })->delete();
			//\Log::info ( \DB::getQueryLog () );
			
		    Formula::where(['GROUP_ID'=>$data['id']])->delete();
		    
		    FoGroup::where(['ID'=>$data['id']])->delete();
		
			$fo_group = $this->getFoGroup();
		} catch ( \Exception $e ) {
			\Log::info ( $e->getMessage() );
			DB::rollback ();
		}
			
		DB::commit ();
	
		return response ()->json ( $fo_group );
	}
	
	private function getFormula($param){
		\Helper::setGetterUpperCase();
		$formula = Formula::getTableName();
		$code_flow_phase = CodeFlowPhase::getTableName();
		$code_event_type = CodeEventType::getTableName();
		
		$formulas = Formula::leftjoin ( $code_flow_phase, "$formula.flow_phase", '=', "$code_flow_phase.ID" )
							->leftjoin ( $code_event_type, "$formula.event_type", '=', "$code_event_type.ID" )
							->where($param)
							->select("$formula.*","$code_flow_phase.NAME as FLOW_PHASE_NAME","$code_event_type.NAME as EVENT_TYPE_NAME")
							->orderBy("$formula.ORDER".($this->isReservedName?'_':''))
							->orderBy("$formula.ID")
							->get();
		$html="";
		$result = [];
		 $i = 0;
		foreach ($formulas as $row)
		{
			$r1 = [];
			$table_name=$row->OBJECT_TYPE;
			$entity = strtolower(str_replace('_', ' ', $table_name));
			$entity = ucwords($entity);
			$entity = str_replace(' ', '', $entity);
			
			$model = 'App\\Models\\' .$entity;
			$tablea = $model::getTableName ();
			$facility = Facility::getTableName ();
			$lo_area = LoArea::getTableName ();
			//\DB::enableQueryLog ();
			
			$key=explode(',',$row->OBJECT_ID);
			$tmp = DB::table ( $tablea )
			->join ( $facility, "$facility.ID", '=', "$tablea.FACILITY_ID" )
			->join ( $lo_area, "$lo_area.ID", '=', "$facility.AREA_ID" )
			//->where(DB::Raw(','.$row->OBJECT_ID.','), 'like', DB::Raw('%,'.$x.',%'))
			->whereIn("$tablea.ID", $key)
			->select("$tablea.NAME AS OBJECT_NAME", "$tablea.FACILITY_ID", "$facility.AREA_ID", "$lo_area.PRODUCTION_UNIT_ID")
			->get();
			//\Log::info ( \DB::getQueryLog () );
			$sLO="";
			$rowLO = null;
			foreach ($tmp as $rowXO)
			{
				$rowLO=$rowXO;
				$sLO.=($sLO==""?"":"<br>").$rowLO->OBJECT_NAME;
			}
			
			if($rowLO){
				$row->PRODUCTION_UNIT_ID = $rowLO->PRODUCTION_UNIT_ID;
				$row->AREA_ID= $rowLO->AREA_ID;
				$row->FACILITY_ID= $rowLO->FACILITY_ID;
				$row->sLO = $sLO;
			}else{
				$row->PRODUCTION_UNIT_ID = "";
				$row->AREA_ID= "";
				$row->FACILITY_ID= "";
				$row->sLO= "";
			}
// 			array_push($result, $row);
		}
		return $formulas;
	}
	
	public function getformulaslist(Request $request){
		$data = $request->all ();
		$result = self::getFormula(['GROUP_ID'=>$data['group_id']]);
		return response ()->json ( $result );
	}
	
	public function getVarList(Request $request){
		$data = $request->all ();
		$result = self::getVar(['FORMULA_ID'=>$data['formula_id']]);
		return response ()->json ( $result );
	}
	
	public function getVar($condition){
		\Helper::setGetterUpperCase();
		$tmp = FoVar::where($condition)->orderBy('ORDER'.($this->isReservedName?'_':''))->select('*')->get();
		$s="";
		$i=0;
		$html="";
		$result = [];
		foreach ($tmp as $row)
		{
			$rowLO=null;
			$r = [];
			$table_name=$row->OBJECT_TYPE;
			$entity = strtolower(str_replace('_', ' ', $table_name));
			$entity = ucwords($entity);
			$entity = str_replace(' ', '', $entity);
				
			$model = 'App\\Models\\' .$entity;
			$tablea = $model::getTableName ();
			$facility = Facility::getTableName ();
			$lo_area = LoArea::getTableName ();
				
			$rowLO = DB::table ( $tablea )
			->join ( $facility, "$facility.ID", '=', "$tablea.FACILITY_ID" )
			->join ( $lo_area, "$lo_area.ID", '=', "$facility.AREA_ID" )
			->where(["$tablea.ID"=>$row->OBJECT_ID] )
			->select("$tablea.NAME AS OBJECT_NAME", "$tablea.FACILITY_ID", "$facility.AREA_ID", "$lo_area.PRODUCTION_UNIT_ID")
			->first();
			
			if($rowLO){
				$row['PRODUCTION_UNIT_ID'] = $rowLO->PRODUCTION_UNIT_ID;
				$row['AREA_ID'] = $rowLO->AREA_ID;
				$row['FACILITY_ID'] = $rowLO->FACILITY_ID;
				$row['OBJECT_NAME'] = $rowLO->OBJECT_NAME;
			}else{
				$row['PRODUCTION_UNIT_ID'] = "";
				$row['AREA_ID'] = "";
				$row['FACILITY_ID'] = "";
				$row['OBJECT_NAME'] = "";
			}
			
			array_push($result, $row);
		}
		return $result;
	}
	
	public function deleteformula(Request $request){
		$data = $request->all ();
		$formula_id = $data['formula_id'];
		Formula::where(['ID'=>$formula_id])->delete();
        FoVar::where('FORMULA_ID',$formula_id)->delete();
		return response ()->json ( "ok");
	}
	
	public function saveFormulaOrder(Request $request){
		$data = $request->all ();
		$orders=$data['orders'];
		foreach($orders as $order){			
			Formula::where(['ID'=>$order[0]])->update(['ORDER'.($this->isReservedName?'_':'')=>$order[1]]);
		}
	
		return response ()->json ( "ok");
	}
	
	public function saveformula(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all ();
		$new_formula_id = 0;
		$formula_id = 0;
		$new_var_id = 0;
		$var_id = 0;
		
		$objname = "";
		if(is_array($data['cboObjName'])&&count($data['cboObjName']) > 0){
			foreach ($data['cboObjName'] as $selectedOption)
			    $objname.= ($objname==""?"":",").$selectedOption;
		}
		$objname = trim($objname);
		if($data['isvar'] == 0)
		{
			$formula_id=$data['formula_id'];
			$saveAsNew = $data['asnew'];
			
			if($formula_id<=0 || $saveAsNew==1 || $saveAsNew==2)
			{
				$param = [];
				$param['NAME'] = $data['txtFormulaName'];
				$param['COMMENT'.($this->isReservedName?'_':'')] =  $data['txtComment'];
				$param['GROUP_ID'] = $data['group_id'];
				$param['OBJECT_TYPE'] = $data['cboObjType'];
				$param['OBJECT_ID'] = $objname;
				$param['TABLE_NAME'] = $data['txtTableName'];
				$param['VALUE_COLUMN'] = $data['txtValueColumn'];
				$param['OBJ_ID_COLUMN'] = $data['txtIDColumn'];
				$param['DATE_COLUMN'] = $data['txtDateColumn'];
				$param['FLOW_PHASE'] = $data['cboFlowPhase'];
				$param['EVENT_TYPE'] = $data['cboEventType'];
				$param['ALLOC_TYPE'] = $data['cboAllocType'];
				$param['FORMULA'] = $data['txtFormula'];
				$param['APPLY_EMPTY'] = $data['applyEmpty'];
				$asnew = $data['asnew'];
				
				$begin_date = $data ['txtBeginDate'];
				if ($begin_date != "") $param['BEGIN_DATE'] = \Helper::parseDate($begin_date);
				else  $param['BEGIN_DATE'] = null;
				$end_date = $data ['txtEndDate'];
				if ($end_date != "") $param['END_DATE'] = \Helper::parseDate($end_date);
				else  $param['END_DATE'] = null;
				
				if ($asnew) {
					$ins = Formula::create($param);
				}
				else{
					$ins = Formula::find($data['formula_id'] );
				}
				
				$ins->update($param );
// 				$ins = Formula::updateOrCreate($condition, $param );
//                 $ins = Formula::firstOrCreate($param);
				$new_formula_id=$ins->ID;
	
				if($formula_id>0 && $new_formula_id>0 && $saveAsNew!=2){
					$tmps = [];
					$tmps = FoVar::where(['FORMULA_ID'=>$formula_id])
					->select('NAME','STATIC_VALUE','ORDER'.($this->isReservedName?'_':''), 'FORMULA_ID', 'OBJECT_TYPE', 'OBJECT_ID', 'TABLE_NAME', 'VALUE_COLUMN', 'OBJ_ID_COLUMN', 'DATE_COLUMN', 'FLOW_PHASE', 'EVENT_TYPE', 'ALLOC_TYPE', 'COMMENT'.($this->isReservedName?'_':''))
					->get ();
					foreach($tmps as $tmp){
						$tmp ['FORMULA_ID'] = $new_formula_id;
						$tmp = json_decode ( json_encode ( $tmp ), true );
						
						$condition = array (
								'ID' => - 1 
						);
						//$tmp = FoVar::updateOrCreate ( $condition, $tmp );
                        FoVar::firstOrCreate($tmp);
					}
				}
			} else {
				if ($objname)
					$str = $objname;
				else
					$str = "";
				
				
				$p = [
					'NAME'=>$data['txtFormulaName'],
					'COMMENT'.($this->isReservedName?'_':'') => $data['txtComment'],
					'GROUP_ID'=>$data['group_id'],
					'OBJECT_TYPE'=>$data['cboObjType'],
					'TABLE_NAME'=>$data['txtTableName'],
					'VALUE_COLUMN'=>$data['txtValueColumn'],
					'OBJ_ID_COLUMN'=>$data['txtIDColumn'],
					'DATE_COLUMN'=>$data['txtDateColumn'],
					'FLOW_PHASE'=>$data['cboFlowPhase'],
					'EVENT_TYPE'=>$data['cboEventType'],
					'ALLOC_TYPE'=>$data['cboAllocType'],
					'FORMULA'=>$data['txtFormula'],
					'APPLY_EMPTY' => $data['applyEmpty'],
// 					'BEGIN_DATE'=>$begin_date,
// 					'END_DATE'=>$end_date
				];
				if($str) $p['OBJECT_ID'] =$str;

				$begin_date = $data ['txtBeginDate'];
				if ($begin_date != "") $p['BEGIN_DATE'] = \Helper::parseDate($begin_date);
				else  $p['BEGIN_DATE'] = null;
				
				$end_date = $data ['txtEndDate'];
				if ($end_date != "") $p['END_DATE'] = \Helper::parseDate($end_date);
				else  $p['END_DATE'] = null;
				
				Formula::where(['ID'=>$formula_id])->update($p);
			}
		}
		else if($data['isvar']==1)
		{
			$var_id=$data['var_id'];
			$saveAsNew=$data['asnew'];
			if($objname)
				$str = $objname;
			else
				$str = "";
			
			if($var_id<=0 || $saveAsNew==1 || $saveAsNew==2)
			{			
				$param = [
					'NAME' => $data['txtFormulaName'],
					'STATIC_VALUE' => $data['txtStaticValue'],
					//'ORDER'.($this->isReservedName?'_':'') => $data['txtOrder'],
					'FORMULA_ID' => $data['formula_id'],
					'OBJECT_TYPE' => $data['cboObjType'],
					'OBJECT_ID'  => $str,
					'TABLE_NAME' => $data['txtTableName'],
					'VALUE_COLUMN' => $data['txtValueColumn'],
					'OBJ_ID_COLUMN' => $data['txtIDColumn'],
					'DATE_COLUMN' => $data['txtDateColumn'],
					'FLOW_PHASE' => $data['cboFlowPhase'],
					'EVENT_TYPE' => $data['cboEventType'],
					'ALLOC_TYPE' => $data['cboAllocType'],
					'COMMENT'.($this->isReservedName?'_':'') => $data['txtComment']
				];
				
				$condition = array (
						'ID' => -1
				);
				
				//$ins = FoVar::updateOrCreate ( $condition, $param );
                $ins = FoVar::firstOrCreate($param);
                $new_var_id= is_object($ins) ? $ins->ID : null;
			}
			else
			{
				$param = [
					'NAME' => $data['txtFormulaName'],
					'STATIC_VALUE' => $data['txtStaticValue'],
					//'ORDER'.($this->isReservedName?'_':'') => $data['txtOrder'],
					'FORMULA_ID' => $data['formula_id'],
					'OBJECT_TYPE' => $data['cboObjType'],
					'TABLE_NAME' => $data['txtTableName'],
					'VALUE_COLUMN' => $data['txtValueColumn'],
					'OBJ_ID_COLUMN' => $data['txtIDColumn'],
					'DATE_COLUMN' => $data['txtDateColumn'],
					'FLOW_PHASE' => $data['cboFlowPhase'],
					'EVENT_TYPE' => $data['cboEventType'],
					'ALLOC_TYPE' => $data['cboAllocType'],
					'COMMENT'.($this->isReservedName?'_':'') => $data['txtComment']
				];
				
				if($str) $param['OBJECT_ID'] =$str;
				
				FoVar::where(['ID'=>$var_id])->update($param);
			}
		}
		
		if($new_formula_id > 0 || $formula_id > 0){
			$objs = self::getFormula(['FORMULA.ID'=>($new_formula_id > 0?$new_formula_id:$formula_id)]);
			return response ()->json (['success' => true, 'data' => $objs]);
		}
		else if($new_var_id > 0 || $var_id > 0){
			$objs = self::getVar(['ID'=>($new_var_id > 0?$new_var_id:$var_id)]);
			return response ()->json (['success' => true, 'data' => $objs]);
		}
		return response ()->json ("error");
	}
	
	public function testformula(Request $request){
		$data = $request->all ();
		$str = "";
		$fid=$data['fid'];
		$occur_date=$data['occur_date'];
		if(!$occur_date)
		{
			$result = FoVar::where(['formula_id'=>$fid])->orderBy('ORDER'.($this->isReservedName?'_':''))->select('*')->get();			
			$need_occur_date=false;
			
			foreach ($result as $row)
			{
				if (strpos($row->STATIC_VALUE,'@OCCUR_DATE') !== false) {
					$need_occur_date=true;
					break;
				}
			}
			if($need_occur_date)
			{
				$str = "need_occur_date";
				return response ()->json ( $str);
			}
		}
		
		if(!$occur_date) $occur_date	=	Carbon::now();
		else $occur_date	=	\Helper::parseDate($occur_date);
		
		$param = Formula::find($fid);
		
		$v = \FormulaHelpers::evalFormula($param, $occur_date,true);
		
		//echo $v;
		return response ()->json ( $v);
	}
	
	public function deletevar(Request $request){
		$data = $request->all ();
		
		FoVar::where(['ID'=>$data['var_id']])->delete();

		return response ()->json ( "ok");
	}
	
	public function savevarsorder(Request $request){
		$data = $request->all ();
		$orders = $data['orders'];
		
		foreach($orders as $order){			
			FoVar::where(['ID'=>$order[0]])->update(['ORDER'.($this->isReservedName?'_':'')=>$order[1]]);
		}
		
		return response ()->json ( "ok");
	}
}