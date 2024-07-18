<?php

namespace App\Http\Controllers;

use App\Models\RptGroup;
use App\Models\RptParam;
use App\Models\RptReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends EBController {
	
	public function loadReports(Request $request) {
		$data = $request->all ();
		$group_cond = 'GROUP';
		if(isset($data['group_id'])&&is_numeric($data['group_id']))
			$group_cond = $data['group_id'];
		$reports = RptReport::loadByGroupId($group_cond);
		return response ()->json ($reports);
	}
	
	public function loadParams(Request $request) {
		$data = $request->all ();
		$originAttrCase = \Helper::setGetterUpperCase();
		$sql = RptParam::where('REPORT_ID','=', $data['report_id']);
		//if(isset($data['workflow'])) $sql->whereIn('VALUE_TYPE', [1, 2]);
        $reports = $sql->select("ID", "CODE", "NAME", "VALUE_TYPE", "REF_TABLE")
							->orderBy('ORDER')
							->get();
    	for($i=0;$i<count($reports);$i++){
    		if($reports[$i]->REF_TABLE){
				$refConfig = $reports[$i]->REF_TABLE;//preg_replace('/\s+/', '', $reports[$i]->REF_TABLE);
				//find custom filter condition after #...
				$pos = strpos($refConfig, '#');
				if($pos !== false){
					$refFilterEx = substr($refConfig, $pos+1);
					$refConfig = substr($refConfig, 0, $pos);
				}

				$refs = explode('(',$refConfig);
				$tableName = $refs[0];
				
				//find dependency configuration in (...)
				if(isset($refs[1])){
					$ps = explode('=', str_replace(')','',$refs[1]));
					if(count($ps)==2){
						foreach($reports as $rpt)
							if($rpt!=$reports[$i] && $rpt->CODE==$ps[1]){
								$reports[$i]->PARENT_FIELD = $ps[0];
								$reports[$i]->PARENT_PARAM = $ps[1];
								unset($refs[1]);
								$refConfig = implode('(', $refs);
								break;
							}
					}
				}
				$where = [];
				$order = 'NAME';
				if(substr( strtolower($tableName), 0, 5 ) === "code_"){
					$where['ACTIVE'] = 1;
					$order = 'ORDER'.(config ( 'database.default' ) === 'oracle'?'_':'');
				}
				
				$fields = ["ID", "NAME"];
				$search_key = '(VALUE:';
				$p1 = strpos($refConfig, $search_key);
				if ($p1 !== false){
					$p1 = $p1 + strlen($search_key);
					$p2 = strpos($refConfig, ')', $p1);
					if($p2 > $p1){
						$field = trim(substr($refConfig, $p1, $p2-$p1));
						if($field)
							$fields[0] = $field.' AS ID';
					}
				}
				$search_key = '(NAME:';
				$p1 = strpos($refConfig, $search_key);
				if ($p1 !== false){
					$p1 = $p1 + strlen($search_key);
					$p2 = strpos($refConfig, ')', $p1);
					if($p2 > $p1){
						$field = trim(substr($refConfig, $p1, $p2-$p1));
						if($field)
							$fields[1] = $field.' AS NAME';
					}
				}

				if(isset($reports[$i]->PARENT_FIELD)){
					$fields[]=$reports[$i]->PARENT_FIELD." AS PARENT_ID";
				}

				$query = \DB::table($tableName)->select($fields)
					->where($where);
				if(isset($refFilterEx) && $refFilterEx)
					$query->whereRaw($refFilterEx);
				$reports[$i]->REF_LIST = $query->orderBy($order)->get();
				
				$collection = collect([]);
				if (strpos($refConfig, '(NONE)') !== false){
					$collection = $collection->merge([['ID'=>'NONE', 'NAME'=>'(None)', 'PARENT_ID'=>'ALL']]);
				}
				if (strpos($refConfig, '(ALL)') !== false){
					$collection = $collection->merge([['ID'=>'ALL', 'NAME'=>'(All)', 'PARENT_ID'=>'ALL']]);
				}
				$reports[$i]->REF_LIST = $collection->merge($reports[$i]->REF_LIST);
			}
		}
		\Helper::setGetterCase($originAttrCase);
		return response ()->json ($reports);
	}
	
	public function _index(Request $request) {
		$data = $request->all ();
		$userReports = $this->user->getUserReports();
		$rpt_group = $userReports->unique('GROUP_NAME')->map(function ($item, $key) {
			return (object)["ID" => $item->GROUP_ID, "NAME" => $item->GROUP_NAME];
		});
		
		$reports = [];
    	foreach($rpt_group as $row ){
    		if($row->ID){
				$reports = RptReport::loadByGroupId($row->ID);
				break;
			}
		}
		$params = [];
		$viewBlade	= isset($data["type"])?$data["type"]:'front.reports';
		return view ( $viewBlade, ['rpt_group'=>$rpt_group, 'reports' => $reports, 'params' => $params] );
	}
	
	function genXML_SHELL_GAS_DAILY_IMPORT_MAIN($sql){
		$re = \DB::select($sql);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$ds = explode(' ',$row->production_day);
		$date = $ds[0];
		$dateT = str_replace(' ','T',date('Y-m-d H:i:s'));
		$next_date = date('Y-m-d', strtotime($date .' +1 day'));
		$str =
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<MsgList xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"Daily Import Figures TAS.xsd\">
<Msg Type=\"SUR_EXT_TAS_IMP\" System=\"SURDME\" Revision=\"1\" Priority=\"2\" TestFlag=\"N\" SubType=\"\" ReplyOnReference=\"\">
<Header>
<RecipientList TO=\"SHELL_UK\" CC=\"\"/>
<Sender CompanyNumber=\"DANA\" CompanyName=\"Dana Petroleum plc\" ContactCode=\"DANA_TRITON_ALLOCATION\" ContactName=\"Triton Allocation system data\" Ident=\"\" SubIdent=\"\"/>
<Receiver CompanyNumber=\"SHELL_UK\" CompanyName=\"Shell UK Ltd\" ContactCode=\"SHELL_UK\" ContactName=\"Shell UK Ltd\" Ident=\"\" SubIdent=\"\"/>
<Generated Date=\"{$dateT}\" GeneratedBy=\"Triton Allocation system data\"/>
<Subject>Daily Import Figures TAS</Subject>
<Period FromDate=\"{$date}T06:00:00\" ToDate=\"{$next_date}T06:00:00\"/>
</Header>
<Body>
<Date>{$date}</Date>
<SubField>
<Name>TRITON</Name>
<Mass Unit=\"TONNES\">".round($row->fl_data_net_mass,1)."</Mass>
<Volume Unit=\"MSM3\">".round($row->fl_data_net_vol,4)."</Volume>
<Energy Unit=\"GJ\">".round($row->fl_data_grs_engy,1)."</Energy>
<CV Unit=\"MJPERSM3\">".round($row->cv,6)."</CV>
<Composition>
<C1 Unit=\"WTPCT\">".round($row->c1,3)."</C1>
<C2 Unit=\"WTPCT\">".round($row->c2,3)."</C2>
<C3 Unit=\"WTPCT\">".round($row->c3,3)."</C3>
<C4 Unit=\"WTPCT\">".round($row->c4,3)."</C4>
<C5s Unit=\"WTPCT\">".round($row->c5,3)."</C5s>
<N2 Unit=\"WTPCT\">".round($row->n2,4)."</N2>
<CO2 Unit=\"WTPCT\">".round($row->co2,3)."</CO2>
</Composition>
</SubField>
</Body>
<Remarks/>
</Msg>
</MsgList>";
		return $str;
		exit;
	}
	
	function genXML_6AM_SHELL_GAS_EXPORT_DAILY($sql){
		$re = \DB::select($sql);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$ds = explode(' ',$row->production_day);
		$date = $ds[0];
		$dateT = str_replace(' ','T',date('Y-m-d H:i:s'));
		$next_date = date('Y-m-d', strtotime($date .' +1 day'));
		$str =
"<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<MsgList xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"Daily Export Figures TAS.xsd\">
<Msg Type=\"SUR_EXT_TAS_EXP\" System=\"SURDME\" Revision=\"1\" Priority=\"2\" TestFlag=\"N\" SubType=\"\" ReplyOnReference=\"\">
<Header>
<RecipientList TO=\"SHELL_UK\" CC=\"\"/>
<Sender CompanyNumber=\"DANA\" CompanyName=\"Dana Petroleum plc\" ContactCode=\"DANA_TRITON_ALLOCATION\" ContactName=\"Triton Allocation system data\" Ident=\"\" SubIdent=\"\"/>
<Receiver CompanyNumber=\"SHELL_UK\" CompanyName=\"Shell UK Ltd\" ContactCode=\"SHELL_UK\" ContactName=\"Shell UK Ltd\" Ident=\"\" SubIdent=\"\"/>
<Generated Date=\"{$dateT}\" GeneratedBy=\"Triton Allocation system data\"/>
<Subject>Daily Export Figures TAS</Subject>
<Period FromDate=\"{$date}T06:00:00\" ToDate=\"{$next_date}T06:00:00\"/>
</Header>
<Body>
<Date>{$date}</Date>
<Field>
<Name>GUILLEMOT_WEST</Name>
<Owner>
<Name>Tailwind</Name>
<Mass Unit=\"TONNES\">".round($row->gt_mass,1)."</Mass>
<Volume Unit=\"MSM3\">".round($row->gt_vol_msm3,4)."</Volume>
</Owner>
<Owner>
<Name>Others</Name>
<Mass Unit=\"TONNES\">".round($row->go_mass,1)."</Mass>
<Volume Unit=\"MSM3\">".round($row->guillemot_others_vol,4)."</Volume>
</Owner>
</Field>
<Field>
<Name>PICT</Name>
<Owner>
<Name>Others</Name>
<Mass Unit=\"TONNES\">".round($row->pt_mass,1)."</Mass>
<Volume Unit=\"MSM3\">".round($row->pt_vol_msm3,4)."</Volume>
</Owner>
</Field>
<Field>
<Name>BITTERN</Name>
<Owner>
<Name>Tailwind</Name>
<Mass Unit=\"TONNES\">".round($row->bt_mass,1)."</Mass>
<Volume Unit=\"MSM3\">".round($row->bt_vol_msm3,4)."</Volume>
</Owner>
<Owner>
<Name>Others</Name>
<Mass Unit=\"TONNES\">".round($row->bo_mass,1)."</Mass>
<Volume Unit=\"MSM3\">".round($row->bo_vol_msm3,4)."</Volume>
</Owner>
</Field>
<Field>
<Name>Evelyn</Name>
<Owner>
<Name>Tailwind</Name>
<Mass Unit=\"TONNES\">".round($row->evt_mass,1)."</Mass>
<Volume Unit=\"MSM3\">".round($row->evt_vol_msm3,4)."</Volume>
</Owner>
</Field>
<TotalMassComposition>
<C1 Unit=\"WTPCT\">".round($row->c1,3)."</C1>
<C2 Unit=\"WTPCT\">".round($row->c2,3)."</C2>
<C3 Unit=\"WTPCT\">".round($row->c3,3)."</C3>
<C4 Unit=\"WTPCT\">".round($row->c4,3)."</C4>
<C5s Unit=\"WTPCT\">".round($row->c5,3)."</C5s>
<N2 Unit=\"WTPCT\">".round($row->n2,3)."</N2>
<CO2 Unit=\"WTPCT\">".round($row->co2,3)."</CO2>
</TotalMassComposition>
</Body>
<Remarks/>
</Msg>
</MsgList>";

		return $str;
		exit;
	}

	function genXML_DG6AM($sql_e,$sql_i){
		$re = \DB::select($sql_e);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$ds = explode(' ',$row->production_date);
		$date = $ds[0];
		$dateT = str_replace(' ','T',date('Y-m-d H:i:s'));
		$next_date = date('Y-m-d', strtotime($date .' +1 day'));
		$str =
"<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
<MsgList>
<Msg ReplyOnReference=\"\" SubType=\"\" TestFlag=\"N\" Priority=\"2\" Revision=\"0\" System=\"SURDME\" Type=\"SUR_EXT_WESTERN_ISLES_IMP\">
	<Header>
		<RecpientList CC=\"\" TO=\"SHELL_UK\"/>
		<Sender ContactName=\"Western Isles Allocation data\" ContactCode=\"DANA_WESTERN_ISLES_ALLOCATION\" CompanyName=\"Dana Petroleum plc\" CompanyNumber=\"DANA\"/>
		<Receiver ContactName=\"SHELL UK Ltd\" ContactCode=\"SHELL_UK\" CompanyName=\"Shell UK Ltd\" CompanyNumber=\"SHELL_UK\"/>
		<Generated GeneratedBy=\"Western Isle Allocation System data\" Date=\"{$dateT}\"/>
		<Subject>Western Isle Import Export Figures</Subject>
		<Period ToDate=\"{$next_date}T06:00:00\" FromDate=\"{$date}T06:00:00\"/>
	</Header>
	<Body>
			<Date>$date</Date>
			<SubField>
				<Name>WESTERN_ISLES</Name>
				<Export>
					<Mass Unit=\"TONNES\">".round($row->mass_ton,3)."</Mass>
					<Volume Unit=\"KSM3\">".round($row->vol_ksm3,3)."</Volume>
					<MassComposition>
						<C1 Unit=\"WTPCT\">".round($row->c1,3)."</C1>
						<C2 Unit=\"WTPCT\">".round($row->c2,3)."</C2>
						<C3 Unit=\"WTPCT\">".round($row->c3,3)."</C3>
						<C4 Unit=\"WTPCT\">".round($row->nc4+$row->ic4,3)."</C4>
						<C5s Unit=\"WTPCT\">".round($row->nc5+$row->ic5+$row->c6,3)."</C5s>
						<N2 Unit=\"WTPCT\">".round($row->n2,3)."</N2>
						<CO2 Unit=\"WTPCT\">".round($row->c02,3)."</CO2>
					</MassComposition>
				</Export>
";
		$re = \DB::select($sql_i);
		if(count($re)>=1){
			$row = $re[0];
			$str .=
"				<Import>
					<Mass Unit=\"TONNES\">".round($row->mass_ton,3)."</Mass>
					<Volume Unit=\"KSM3\">".round($row->vol_ksm3,3)."</Volume>
					<MassComposition>
						<C1 Unit=\"WTPCT\">".round($row->c1,3)."</C1>
						<C2 Unit=\"WTPCT\">".round($row->c2,3)."</C2>
						<C3 Unit=\"WTPCT\">".round($row->c3,3)."</C3>
						<C4 Unit=\"WTPCT\">".round($row->nc4+$row->ic4,3)."</C4>
						<C5s Unit=\"WTPCT\">".round($row->nc5+$row->ic5+$row->c6,3)."</C5s>
						<N2 Unit=\"WTPCT\">".round($row->n2,3)."</N2>
						<CO2 Unit=\"WTPCT\">".round($row->c02,3)."</CO2>
					</MassComposition>
				</Import>
";
		}
		$str .=
"			</SubField>
		</Body>
	</Msg>
</MsgList>";
		return $str;
		exit;
	}

	function genXML_6AM_GAS_EXPORT_NOTIFICATION($sql){
		$re = \DB::select($sql);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$date = explode(' ',$row->production_date)[0];
		$date = explode('-',$date);
		$str = 
"<tas_6am_gas_export_notification>
<PPRS_RETURN>
	<RETURN_DATE>
		<RETURN_DATE_MONTH>{$date[1]}</RETURN_DATE_MONTH>
	<RETURN_DATE_YEAR>{$date[0]}</RETURN_DATE_YEAR>
	</RETURN_DATE>
	<REPORTING_UNIT>
		<REPORTING_UNIT_NAME>$row->field_name</REPORTING_UNIT_NAME>
	</REPORTING_UNIT>
	<RETURN_UK_SHARE>
		<RETURN_UK_SHARE_OIL>100</RETURN_UK_SHARE_OIL>
		<RETURN_UK_SHARE_GAS>100</RETURN_UK_SHARE_GAS>
	</RETURN_UK_SHARE>
	<TIME_NOT_PRODUCING>
		<DAYS_NOT_PRODUCING>".round($row->non_prod_days)."</DAYS_NOT_PRODUCING>
		<HOURS_NOT_PRODUCING>".round($row->non_prod_hrs)."</HOURS_NOT_PRODUCING>
	</TIME_NOT_PRODUCING>
	<OFFSHORE_TANKER_LOADER>
		<PRODUCED_WATER_VOLUME>".round($row->net_wat_vol,3)."</PRODUCED_WATER_VOLUME>
		<PRODUCED_WATER_TO_SEA_VOLUME>".round($row->net_wat_ovb_vol,3)."</PRODUCED_WATER_TO_SEA_VOLUME>
		<INJECTED_WATER_VOLUME>".round($row->wi_vol,3)."</INJECTED_WATER_VOLUME>
		<RE_INJECTED_PRODUCED_WATER_VOLUME></RE_INJECTED_PRODUCED_WATER_VOLUME>
		<ASSOCIATED_GAS_PRODUCTION>
			<ASSOCIATED_GAS_PRODUCTION_VOLUME>".round($row->net_gas_vol,3)."</ASSOCIATED_GAS_PRODUCTION_VOLUME>
			<ASSOCIATED_GAS_PRODUCTION_DENSITY>".($row->net_gas_vol==0?0:round($row->net_gas_mass/$row->net_gas_vol,3))."</ASSOCIATED_GAS_PRODUCTION_DENSITY>
			<ASSOCIATED_GAS_PRODUCTION_NON_HYDROCARBON_PERCENTAGE></ASSOCIATED_GAS_PRODUCTION_NON_HYDROCARBON_PERCENTAGE>
		</ASSOCIATED_GAS_PRODUCTION>
		<GAS_FLARED_AT_FIELD>
			<GAS_FLARED_AT_FIELD_VOLUME>".round($row->net_flr_vol,3)."</GAS_FLARED_AT_FIELD_VOLUME>
			<GAS_FLARED_AT_FIELD_DENSITY>".($row->net_flr_vol==0?0:round($row->net_flr_mass/$row->net_flr_vol,3))."</GAS_FLARED_AT_FIELD_DENSITY>
			<GAS_FLARED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE></GAS_FLARED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE>
		</GAS_FLARED_AT_FIELD>
		<GAS_INJECTED>
			<GAS_INJECTED_VOLUME></GAS_INJECTED_VOLUME>
			<GAS_INJECTED_CV></GAS_INJECTED_CV>
			<GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE></GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE>
		</GAS_INJECTED>
		<OIL_PRODUCTION>
			<OIL_PRODUCTION_VOLUME>".round($row->net_oil_vol,3)."</OIL_PRODUCTION_VOLUME>
			<OIL_PRODUCTION_DENSITY>".($row->net_oil_vol==0?0:round($row->net_oil_mass/$row->net_oil_vol,3))."</OIL_PRODUCTION_DENSITY>
		</OIL_PRODUCTION>
		<STOCK_OF_OIL_IN_TANKER>
			<STOCK_OF_OIL_IN_TANKER_VOLUME>".round($row->net_oil_vol_offload,3)."</STOCK_OF_OIL_IN_TANKER_VOLUME>
			<STOCK_OF_OIL_IN_TANKER_DENSITY>".($row->net_oil_vol_offload==0?0:round($row->net_oil_mass_offload/$row->net_oil_vol_offload,3))."</STOCK_OF_OIL_IN_TANKER_DENSITY>
		</STOCK_OF_OIL_IN_TANKER>		
	</OFFSHORE_TANKER_LOADER>
</PPRS_RETURN>";
		return $str;
	}

	function genXML_MTHLYPPRS($sql){
		$re = \DB::select($sql);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$date = explode(' ',$row->production_date)[0];
		$date = explode('-',$date);
		$str = 
"<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
<PPRS_RETURN>
	<RETURN_DATE>
		<RETURN_DATE_MONTH>{$date[1]}</RETURN_DATE_MONTH>
	<RETURN_DATE_YEAR>{$date[0]}</RETURN_DATE_YEAR>
	</RETURN_DATE>
	<REPORTING_UNIT>
		<REPORTING_UNIT_NAME>$row->field_name</REPORTING_UNIT_NAME>
	</REPORTING_UNIT>
	<RETURN_UK_SHARE>
		<RETURN_UK_SHARE_OIL>100</RETURN_UK_SHARE_OIL>
		<RETURN_UK_SHARE_GAS>100</RETURN_UK_SHARE_GAS>
	</RETURN_UK_SHARE>
	<TIME_NOT_PRODUCING>
		<DAYS_NOT_PRODUCING>".round($row->non_prod_days)."</DAYS_NOT_PRODUCING>
		<HOURS_NOT_PRODUCING>".round($row->non_prod_hrs)."</HOURS_NOT_PRODUCING>
	</TIME_NOT_PRODUCING>
	<OFFSHORE_TANKER_LOADER>
		<PRODUCED_WATER_VOLUME>".round($row->net_wat_vol,3)."</PRODUCED_WATER_VOLUME>
		<PRODUCED_WATER_TO_SEA_VOLUME>".round($row->net_wat_ovb_vol,3)."</PRODUCED_WATER_TO_SEA_VOLUME>
		<INJECTED_WATER_VOLUME>".round($row->wi_vol,3)."</INJECTED_WATER_VOLUME>
		<RE_INJECTED_PRODUCED_WATER_VOLUME></RE_INJECTED_PRODUCED_WATER_VOLUME>
		<ASSOCIATED_GAS_PRODUCTION>
			<ASSOCIATED_GAS_PRODUCTION_VOLUME>".round($row->net_gas_vol,3)."</ASSOCIATED_GAS_PRODUCTION_VOLUME>
			<ASSOCIATED_GAS_PRODUCTION_DENSITY>".($row->net_gas_vol==0?0:round($row->net_gas_mass/$row->net_gas_vol,3))."</ASSOCIATED_GAS_PRODUCTION_DENSITY>
			<ASSOCIATED_GAS_PRODUCTION_NON_HYDROCARBON_PERCENTAGE></ASSOCIATED_GAS_PRODUCTION_NON_HYDROCARBON_PERCENTAGE>
		</ASSOCIATED_GAS_PRODUCTION>
		<GAS_FLARED_AT_FIELD>
			<GAS_FLARED_AT_FIELD_VOLUME>".round($row->net_flr_vol,3)."</GAS_FLARED_AT_FIELD_VOLUME>
			<GAS_FLARED_AT_FIELD_DENSITY>".($row->net_flr_vol==0?0:round($row->net_flr_mass/$row->net_flr_vol,3))."</GAS_FLARED_AT_FIELD_DENSITY>
			<GAS_FLARED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE></GAS_FLARED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE>
		</GAS_FLARED_AT_FIELD>
		<GAS_INJECTED>
			<GAS_INJECTED_VOLUME></GAS_INJECTED_VOLUME>
			<GAS_INJECTED_CV></GAS_INJECTED_CV>
			<GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE></GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE>
		</GAS_INJECTED>
		<OIL_PRODUCTION>
			<OIL_PRODUCTION_VOLUME>".round($row->net_oil_vol,3)."</OIL_PRODUCTION_VOLUME>
			<OIL_PRODUCTION_DENSITY>".($row->net_oil_vol==0?0:round($row->net_oil_mass/$row->net_oil_vol,3))."</OIL_PRODUCTION_DENSITY>
		</OIL_PRODUCTION>
		<STOCK_OF_OIL_IN_TANKER>
			<STOCK_OF_OIL_IN_TANKER_VOLUME>".round($row->net_oil_vol_offload,3)."</STOCK_OF_OIL_IN_TANKER_VOLUME>
			<STOCK_OF_OIL_IN_TANKER_DENSITY>".($row->net_oil_vol_offload==0?0:round($row->net_oil_mass_offload/$row->net_oil_vol_offload,3))."</STOCK_OF_OIL_IN_TANKER_DENSITY>
		</STOCK_OF_OIL_IN_TANKER>		
	</OFFSHORE_TANKER_LOADER>
</PPRS_RETURN>";
		return $str;
		exit;
	}
	
	function genXML_GTS_MESSAGE($sql, $sql2){
		$re = \DB::select($sql);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$str = 
"<?xml version=\"1.0\" encoding=\"utf-8\"?>
<ENVELOPE xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">
  <MESSAGETYPE>$row->MESSAGETYPE</MESSAGETYPE>
  <MESSAGEID>$row->MESSAGEID</MESSAGEID>
  <EANCODESENDER>$row->EANCODESENDER</EANCODESENDER>
  <EANCODERECEIVER>$row->EANCODERECEIVER</EANCODERECEIVER>
  <TIMESTAMP>$row->TIMESTAMP</TIMESTAMP>
  <HEADER>
    <PERIODSTART>$row->PERIODSTART</PERIODSTART>
    <PERIODEND>$row->PERIODEND</PERIODEND>
    <TIMEZONE>$row->TIMEZONE</TIMEZONE>
    <VERSION>$row->VERSION</VERSION>
    <CODERUN>$row->CODERUN</CODERUN>
    <ENERGYUNIT>$row->ENERGYUNIT</ENERGYUNIT>
    <VOLUMEUNIT>$row->VOLUMEUNIT</VOLUMEUNIT>
    <CALORIFICUNIT>$row->CALORIFICUNIT</CALORIFICUNIT>
  </HEADER>
  <BODY>
";
		$re = \DB::select($sql2);
		foreach($re as $row) {
			$str .=
"    <HDATA>
      <HTIME>$row->HTIME</HTIME>
      <ENERGY>$row->ENERGY</ENERGY>
      <VOLUME>$row->VOLUME</VOLUME>
      <CALORIFIC>$row->CALORIFIC</CALORIFIC>
    </HDATA>
";
		}
		$str .=
"  </BODY>
</ENVELOPE>";
		return $str;
		exit;
	}

	function genXML_CVA($sql, $sql2, $sql3){
		$re = \DB::select($sql);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$str = 
"<?xml version=\"1.0\" encoding=\"utf-8\"?>
<CVA_producer_submit>
    <header>
		<guid>$row->GUID</guid>
		<created_at>$row->CREATED_AT</created_at>
		<organisation_id>$row->ORGINISATION_ID</organisation_id>
		<producer_id>$row->PRODUCER_ID</producer_id>
    </header>
    <transactions>
";
		$re = \DB::select($sql2);
		foreach($re as $row) {
			$str .=
"   <transaction>
		<sequence>$row->SEQUENCE</sequence>
		<gasflowday>$row->GASFLOWDAY</gasflowday>
		<subterminal>$row->SUBTERMINAL</subterminal>
		<transactiontype>$row->TRANSACTIONTYPE</transactiontype>
		<counterparty>$row->COUNTERPARTY</counterparty>
		<field_group>$row->FIELD_GROUP</field_group>
		<field>$row->FIELD</field>
		<amount>$row->AMOUNT</amount>
		<comments>$row->COMMENTS</comments>
		<reporting_for>$row->REPORTING_FOR</reporting_for>
    </transaction>
";
		}
		
		$re = \DB::select($sql3);
		$row = $re[0];
		$str .=
"    </transactions>
	<control>
		<transaction_count>$row->TRANSACTION_COUNT</transaction_count>
		<amount_sum_abs>$row->AMOUNT_SUM_ABS</amount_sum_abs>
    </control>	
</CVA_producer_submit>";
		return $str;
		exit;
	}

	function genXML_PPRS_SEAN($sql){
		$re = \DB::select($sql);
		if(count($re)<1){
			echo "No data";
			exit;
		}
		$row = $re[0];
		$str = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
<PPRS_RETURN>
	<RETURN_DATE>
		<RETURN_DATE_MONTH>$row->rd_return_month</RETURN_DATE_MONTH>
		<RETURN_DATE_YEAR>$row->rd_return_year</RETURN_DATE_YEAR>
	</RETURN_DATE>
	<REPORTING_UNIT>
		<REPORTING_UNIT_NAME>$row->ru_name</REPORTING_UNIT_NAME>
	</REPORTING_UNIT>
	<RETURN_UK_SHARE>
		<RETURN_UK_SHARE_OIL>$row->rus_uk_oil_share</RETURN_UK_SHARE_OIL>
		<RETURN_UK_SHARE_GAS>$row->rus_uk_gas_share</RETURN_UK_SHARE_GAS>
	</RETURN_UK_SHARE>
	<TIME_NOT_PRODUCING>
		<DAYS_NOT_PRODUCING>$row->tnp_days_not_producing</DAYS_NOT_PRODUCING>
		<HOURS_NOT_PRODUCING>$row->tnp_hours_not_producing</HOURS_NOT_PRODUCING>
	</TIME_NOT_PRODUCING>
	<DRY_GAS_FIELD>
		<PRODUCED_WATER_VOLUME>$row->dgf_prod_wat_vol</PRODUCED_WATER_VOLUME>
		<PRODUCED_WATER_TO_SEA_VOLUME>$row->dgf_prod_wat_disp_vol</PRODUCED_WATER_TO_SEA_VOLUME>
		<DRY_GAS_FIELD_PRODUCTION>
			<DRY_GAS_FIELD_PRODUCTION_VOLUME>$row->dgf_gas_prod_vol</DRY_GAS_FIELD_PRODUCTION_VOLUME>
			<DRY_GAS_FIELD_PRODUCTION_DENSITY>$row->dgf_gas_prod_dens</DRY_GAS_FIELD_PRODUCTION_DENSITY>
			<DRY_GAS_FIELD_PRODUCTION_NON_HYDROCARBON_PERCENTAGE>$row->dgf_gas_prod_non_hc</DRY_GAS_FIELD_PRODUCTION_NON_HYDROCARBON_PERCENTAGE>
		</DRY_GAS_FIELD_PRODUCTION>
		<INTER_FIELD_TRANSFER_OF_DRY_GAS_REPEATING_GROUP></INTER_FIELD_TRANSFER_OF_DRY_GAS_REPEATING_GROUP>
		<DRY_GAS_TO_PIPELINE>
			<DRY_GAS_TO_PIPELINE_VOLUME>$row->dgf_gas_pipe_vol</DRY_GAS_TO_PIPELINE_VOLUME>
			<DRY_GAS_TO_PIPELINE_DENSITY>$row->dgf_gas_pipe_dens</DRY_GAS_TO_PIPELINE_DENSITY>
			<DRY_GAS_TO_PIPELINE_CV>$row->dgf_gas_pipe_cv</DRY_GAS_TO_PIPELINE_CV>
			<DRY_GAS_TO_PIPELINE_NON_HYDROCARBON_PERCENTAGE>$row->dgf_gas_pipe_non_hc</DRY_GAS_TO_PIPELINE_NON_HYDROCARBON_PERCENTAGE>
		</DRY_GAS_TO_PIPELINE>
		<GAS_FLARED_AT_FIELD>
			<GAS_FLARED_AT_FIELD_VOLUME>$row->dgf_gas_flare_vol</GAS_FLARED_AT_FIELD_VOLUME>
			<GAS_FLARED_AT_FIELD_DENSITY>$row->dgf_gas_flare_dens</GAS_FLARED_AT_FIELD_DENSITY>
			<GAS_FLARED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE>$row->dgf_gas_flare_non_hc</GAS_FLARED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE>
		</GAS_FLARED_AT_FIELD>
		<GAS_VENTED_AT_FIELD>
			<GAS_VENTED_AT_FIELD_VOLUME>$row->dgf_gas_vent_vol</GAS_VENTED_AT_FIELD_VOLUME>
			<GAS_VENTED_AT_FIELD_DENSITY>$row->dgf_gas_vent_dens</GAS_VENTED_AT_FIELD_DENSITY>
			<GAS_VENTED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE>$row->dgf_gas_vent_non_hc</GAS_VENTED_AT_FIELD_NON_HYDROCARBON_PERCENTAGE>
		</GAS_VENTED_AT_FIELD>
		<GAS_UTILISED_IN_FIELD>
			<GAS_UTILISED_IN_FIELD_VOLUME>$row->dgf_gas_util_vol</GAS_UTILISED_IN_FIELD_VOLUME>
			<GAS_UTILISED_IN_FIELD_DENSITY>$row->dgf_gas_util_dens</GAS_UTILISED_IN_FIELD_DENSITY>
			<GAS_UTILISED_IN_FIELD_NON_HYDROCARBON_PERCENTAGE>$row->dgf_gas_util_non_hc</GAS_UTILISED_IN_FIELD_NON_HYDROCARBON_PERCENTAGE>
		</GAS_UTILISED_IN_FIELD>
		<GAS_INJECTED>
			<GAS_INJECTED_VOLUME>$row->dgf_gas_inj_vol</GAS_INJECTED_VOLUME>
			<GAS_INJECTED_CV>$row->dgf_gas_inj_dens</GAS_INJECTED_CV>
			<GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE>$row->dgf_gas_inj_non_hc</GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE>
		</GAS_INJECTED>
		<GAS_UTILISED_FROM_INTER_FIELD_TRANSFER>
			<GAS_UTILISED_FROM_INTER_FIELD_TRANSFER_VOLUME>0</GAS_UTILISED_FROM_INTER_FIELD_TRANSFER_VOLUME>
			<GAS_UTILISED_FROM_INTER_FIELD_TRANSFER_DENSITY>0</GAS_UTILISED_FROM_INTER_FIELD_TRANSFER_DENSITY>
			<GAS_UTILISED_FROM_INTER_FIELD_TRANSFER_NON_HYDROCARBON_PERCENTAGE>0</GAS_UTILISED_FROM_INTER_FIELD_TRANSFER_NON_HYDROCARBON_PERCENTAGE>
		</GAS_UTILISED_FROM_INTER_FIELD_TRANSFER>
		<DRY_GAS_FIELD_CONDENSATE_PRODUCTION>
			<DRY_GAS_FIELD_CONDENSATE_PRODUCTION_VOLUME>$row->dgf_cond_vol</DRY_GAS_FIELD_CONDENSATE_PRODUCTION_VOLUME>
			<DRY_GAS_FIELD_CONDENSATE_PRODUCTION_DENSITY>$row->dgf_cond_dens</DRY_GAS_FIELD_CONDENSATE_PRODUCTION_DENSITY>
		</DRY_GAS_FIELD_CONDENSATE_PRODUCTION>
		<INTER_FIELD_TRANSFER_OF_CONDENSATE_REPEATING_GROUP></INTER_FIELD_TRANSFER_OF_CONDENSATE_REPEATING_GROUP>
		<SALES_GAS_TO_NTS>
			<GAS_INJECTED_VOLUME>DGF_NTS_SALE_MASS</GAS_INJECTED_VOLUME>
			<GAS_INJECTED_CV>DGF_NTS_SALE_DENS</GAS_INJECTED_CV>
			<GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE>$row->dgf_nts_sale_cv</GAS_INJECTED_NON_HYDROCARBON_PERCENTAGE>
		</SALES_GAS_TO_NTS>
		<INDIVIDUAL_SALES_GAS_NON_NTS_REPEATING_GROUP></INDIVIDUAL_SALES_GAS_NON_NTS_REPEATING_GROUP>
	</DRY_GAS_FIELD>
</PPRS_RETURN>";
		return $str;
		exit;
	}
	
	function fillReportQuery($sql, &$params){
		foreach($params as $arg => $value){
			$param = substr($arg,0,strpos($arg, '__T_'));
			if (strpos($arg, '__T_1') !== false) {
				$sql = str_replace('$P{'.$param.'}',$value,$sql);
			}
			else if (strpos($arg, '__T_2') !== false) {
				$sql = str_replace('$P{'.$param.'}',"'$value'",$sql);
			}
			else if (strpos($arg, '__T_3') !== false && $value) {
				$sql = str_replace('$P{'.$param.'}',"'$value'",$sql);
			}
		}
		return $sql;
	}
	
	function getReportQuery($file,&$args){
		$xml=simplexml_load_file($file);
		$sql=self::fillReportQuery($xml->queryString, $args);
		return $sql;
	}

	public function genReport($str){
		if (strpos($str, 'type=CSV')!==false) {
			exportReportData($str);
			return;
		}

		$ps = explode('&',$str);
		$args = [];
		$reportDate = "";
		foreach($ps as $p){
			$vs = explode('=',$p);
			if(count($vs)>=2){
				$args[$vs[0]] = $vs[1];
				$paramType = explode('__T_', $vs[0]);
				if(!$reportDate && isset($paramType[1]))
					$reportDate = $vs[1];
			}
		}

		$isViewReport = true;
		if(isset($args ["keep_output_file"]))
			if($args ["keep_output_file"] == '1')
				$isViewReport = false;

		$file = $args ["file"];
		$file = str_replace(".jrxml","", $file);
		$root = realpath ( "." ).($isViewReport?"":"\\public")."\\report";
		$in = "$root\\{$file}.jrxml";
		$exportType = $args ["export"];
		$filename = (array_key_exists("filename", $args) ? $args ["filename"] : "") ? $args ["filename"] : $file;
		
		if ($exportType == "XML") {
			$rpt = App\Models\RptReport::where(['FILE'.(config('database.default')==='oracle'?'_':'') => $file])->select("ID", "NAME")->first();
			$reportName = is_object($rpt) ? $rpt->NAME : 'Report';

			$xml="";
			$file = strtoupper($file);
			if($file=='DGE6AM' || $file=='DGI6AM'){
				$sql_e = $this->getReportQuery("$root\\DGE6AM.jrxml",$args);
				$sql_i = $this->getReportQuery("$root\\DGI6AM.jrxml",$args);
				$xml=$this->genXML_DG6AM($sql_e,$sql_i);
			}
			else if($file=='SHELL_GAS_DAILY_IMPORT_MAIN'){
				$sql = $this->getReportQuery($in,$args);
				$xml=$this->genXML_SHELL_GAS_DAILY_IMPORT_MAIN($sql);
				$filename = preg_replace('/[^a-zA-Z0-9\-\s_]/','', $reportName) . "_" . str_replace('-', '', $reportDate);
			}
			else if($file=='6AM_SHELL_GAS_EXPORT_DAILY'){
				$sql = $this->getReportQuery($in,$args);
				$xml=$this->genXML_6AM_SHELL_GAS_EXPORT_DAILY($sql);
				$filename = preg_replace('/[^a-zA-Z0-9\-\s_]/','', $reportName) . "_" . str_replace('-', '', $reportDate);
			}
			else if($file=='MTHLYPPRS'){
				$sql = $this->getReportQuery($in,$args);
				$xml=$this->genXML_MTHLYPPRS($sql);
			}
			else if($file=='6AM_GAS_EXPORT_NOTIFICATION'){
				$sql = $this->getReportQuery($in,$args);
				$xml=$this->genXML_6AM_GAS_EXPORT_NOTIFICATION($sql);
			}
			else if($file=='GTS_MESSAGE'){
				$sql = $this->getReportQuery($in,$args);
				$sql2 = $this->getReportQuery("$root\\GTS_MESSAGE_TABLE.jrxml",$args);
				$xml=$this->genXML_GTS_MESSAGE($sql, $sql2);
			}
			else if($file=='GTS_MESSAGE_STREAM_1'){
				$sql = $this->getReportQuery($in,$args);
				$sql2 = $this->getReportQuery("$root\\GTS_MESSAGE_STREAM_1_TABLE.jrxml",$args);
				$xml=$this->genXML_GTS_MESSAGE($sql, $sql2);
			}
			else if($file=='GTS_MESSAGE_STREAM_2'){
				$sql = $this->getReportQuery($in,$args);
				$sql2 = $this->getReportQuery("$root\\GTS_MESSAGE_STREAM_2_TABLE.jrxml",$args);
				$xml=$this->genXML_GTS_MESSAGE($sql, $sql2);
			}
			else if($file=='CVA'){
				$sql = $this->getReportQuery($in,$args);
				$sql2 = $this->getReportQuery("$root\\CVA_TRANSACTIONS.jrxml",$args);
				$sql3 = $this->getReportQuery("$root\\CVA_SUM.jrxml",$args);
				$xml=$this->genXML_CVA($sql, $sql2, $sql3);
			}
			else if($file=='PPRS_SEAN_MAIN'){
				$sql = $this->getReportQuery($in,$args);
				$xml=$this->genXML_PPRS_SEAN($sql);
			}
			if($xml){
				$out = "$root\\$filename.xml";
				file_put_contents($out, $xml);
				$contentType = "text/xml";
				if($isViewReport) {
					header("Content-Disposition: attachment;filename=$filename.xml");
					header('Content-type: ' . $contentType );
					readfile ( $out );
					unlink($out);
				}
				else return $out;
			}
			else if($isViewReport) echo "No XML template";
			return null;
		}
		
		$varurl = 'http://localhost:8080/JavaBridge/java/Java.inc';
		include $varurl;
		$System = java ( "java.lang.System" );
		try {
			$dbtype = config('database.default');
			$conn = config('database.connections')[$dbtype];
			if($dbtype=='oracle'){
				java ( "java.lang.Class" )->forName ( "oracle.jdbc.driver.OracleDriver" );
				$connection = java ( "java.sql.DriverManager" )->getConnection("jdbc:oracle:thin:@{$conn['host']}:{$conn['port']}:{$conn['database']}", $conn['username'], $conn['password'] );
			}
			else if($dbtype=='sqlsrv'){
				java ( "java.lang.Class" )->forName ( "com.microsoft.sqlserver.jdbc.SQLServerDriver" );
				$connection = java ( "java.sql.DriverManager" )->getConnection ( "jdbc:sqlserver://{$conn['host']};databaseName={$conn['database']}", $conn['username'], $conn['password'] );
			}
			else{
				java ( "java.lang.Class" )->forName ( "com.mysql.jdbc.Driver" );
				$connection = java ( "java.sql.DriverManager" )->getConnection ( "jdbc:mysql://{$conn['host']}/{$conn['database']}", $conn['username'], $conn['password'] );
			}

			$report = java ( "net.sf.jasperreports.engine.JasperCompileManager" )->compileReport ( $in );
			
			$dateFormat = new \java ( "java.text.SimpleDateFormat", "yy-MM-dd" );
			$params = new \java ( "java.util.HashMap" );
			
			foreach($args as $arg => $value){
				$param = substr($arg,0,strpos($arg, '__T_'));
				//echo "$param = $value<br>";
				if (strpos($arg, '__T_1') !== false) {
					$params->put ($param, intval($value));
				}
				else if (strpos($arg, '__T_2') !== false) {
					$params->put ($param, $value);
				}
				else if ((strpos($arg, '__T_3') !== false || strpos($arg, '__T_5') !== false || strpos($arg, '__T_6') !== false) && $value) {
					$datevalue = $dateFormat->parse ( $value );
					$params->put ($param, new \java("java.sql.Date", $datevalue->getTime()));
				}
			}
			$params->put ( "ROOT_DIR", $root );
			
			$print = java ( "net.sf.jasperreports.engine.JasperFillManager" )->fillReport ( $report, $params, $connection );
			$print->setProperty("net.sf.jasperreports.export.xls.ignore.graphics", "true");
			
			$contentType = "text/html";
			$out = "$filename.html";
			if ($exportType == "PDF") {
				java_set_file_encoding ( "ISO-8859-1" );
				$contentType = "application/pdf";
				$out = $root . "/$filename.pdf";
				java ( "net.sf.jasperreports.engine.JasperExportManager" )->exportReportToPdfFile ( $print, $out );
				if($isViewReport) header("Content-Disposition: inline;filename=$filename.pdf");
			} elseif ($exportType == "XML") {
				$xml=simplexml_load_file($in);
				$sql=$xml->queryString;
				return $sql;
				exit;
				/*
				$out = $root . "/$filename.xml";
				$contentType = "text/xml";
				$xmlExporter = new \java ( "net.sf.jasperreports.engine.export.JRXmlExporter" );
				$JRXmlExporterParameter = java ( "net.sf.jasperreports.engine.export.JRXmlExporterParameter" );
				$xmlExporter->setParameter ( $JRXmlExporterParameter->JASPER_PRINT, $print );
				$xmlExporter->setParameter ( $JRXmlExporterParameter->OUTPUT_FILE, new \java ( "java.io.File", $out ) );
				$xmlExporter->exportReport ();
				if($isViewReport) header("Content-Disposition: attachment;filename=$filename.xml");
				*/
			} elseif ($exportType == "Excel") {
				$out = $root . "/$filename.xls";
				$contentType = "application/vnd.ms-excel";
				$xlsExporter = new \java ( "net.sf.jasperreports.engine.export.JRXlsExporter" );
				$JRXlsExporterParameter = java ( "net.sf.jasperreports.engine.export.JRXlsExporterParameter" );
				$xlsExporter->setParameter ( $JRXlsExporterParameter->JASPER_PRINT, $print );
				$xlsExporter->setParameter ( $JRXlsExporterParameter->OUTPUT_FILE, new \java ( "java.io.File", $out ) );
				$xlsExporter->setParameter ( $JRXlsExporterParameter->IS_DETECT_CELL_TYPE, true );
				
				// $xlsExporter->setParameter($JRXlsExporterParameter->IS_WHITE_PAGE_BACKGROUND, true);
				$xlsExporter->exportReport ();
				if($isViewReport) header("Content-Disposition: attachment;filename=$filename.xls");
			} elseif ($exportType == "HTML") {
				$out = $root . "/$filename.html";
				$contentType = "text/html";
				java ( "net.sf.jasperreports.engine.JasperExportManager" )->exportReportToHtmlFile ( $print, $out );
			}
			if($isViewReport){
				header('Content-type: ' . $contentType );
				readfile ( $out );
				unlink($out);
			}
		} catch ( Exception $ex ) {
			$out = "error:".$ex->getCause ();
			if($isViewReport) {
				echo "Can not generate report. Please contact technical support.";
				echo "<b>Error...:</b>" . $ex->getCause ();
			}
		}
		if(!$isViewReport) return $out;
	}
	
	public function exportReportData($str){
		$ps = explode('&',$str);
		$args = [];
		$str_params = "";
		foreach($ps as $p){
			$vs = explode('=',$p);
			if(count($vs)>=2){
				$args[$vs[0]] = $vs[1];
				if($vs[0]!="file" && $vs[0]!="type")
					$str_params .= ($str_params?"_":"").$vs[1];
			}
		}
		$varurl = 'http://localhost:8080/JavaBridge/java/Java.inc';
		require_once $varurl;
		$report_file = $args["file"];
		$file_type = isset($args["type"])?(strtolower($args["type"])=="xml"?"xml":"csv"):"csv";
		$result_file = "{$report_file}_{$str_params}.{$file_type}";
		$root = realpath ( "." )."\\report";
		$report_file = str_replace(".jrxml","", $report_file);
		$in = $root . "\\{$report_file}.jrxml";

		$sqls = [];
		$report = java ( "net.sf.jasperreports.engine.JasperCompileManager" )->compileReport ( $in );
		$sql = $report->getQuery()->getText();
		$sqls[] = $sql;

		$bands = $report->getDetailSection()->getBands();
		foreach($bands as $band){
			$elements = $band->getChildren();
			foreach($elements as $child)
			if(java_instanceof($child, java ( "net.sf.jasperreports.engine.JRSubreport" )))
			{
				try {
					$path = $child->getExpression()->getText();
					if(strpos($path, '.jasper')){
						$path = str_replace('$P{ROOT_DIR}', $root, $path);
						$path = str_replace('+', '', $path);
						$path = str_replace('"', '', $path);
						//echo $path;	echo "<br>";
						$subreport = java ( "net.sf.jasperreports.engine.util.JRLoader" )->loadObjectFromFile($path);
						$sql = $subreport->getQuery()->getText();
						$sqls[] = $sql;
					}
				}
				catch (\Exception $e){}
			}
		}
		
		$response = new StreamedResponse(function() use($sqls, $args){
			// Open output stream
			$handle = fopen('php://output', 'w');
			$db = \DB::connection()->getPdo();
			foreach($sqls as $sql){
				$sql = self::fillReportQuery($sql, $args);
				try{
					$query = $db->prepare($sql);
					$query->execute();
				}catch(\Exception $e){
					fputcsv($handle, ["Error: ".$e->getMessage()]);
					continue;
				}
				$first_row = true;
				while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
					if($first_row){
						$first_row = false;
						fputcsv($handle, array_keys($row));
					}
					fputcsv($handle, array_values($row));
				}
			}

			// Close the output stream
			fclose($handle);
		}, 200, [
				'Content-Type' => 'text/csv',
				'Content-Disposition' => 'attachment; filename="'.$result_file.'"',
			]);
		return $response;
	}
}