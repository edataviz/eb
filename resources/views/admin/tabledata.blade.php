<?php
$currentSubmenu 					='/loadtabledata';
if (!isset($subMenus)) $subMenus 	= [];
$enableFilter 						= false;
if (!isset($active)) $active 		= 1;
if (!isset($isAction)) $isAction 	= false;
$tables 							= ['FlowDataFdcValue'	=>['name'=>'FDC VALUE']];
$useBootrapCss						= false;
?>

@extends('core.bsmain',['subMenus' => $subMenus])

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/loadtabledata/load";
	actions.saveUrl 		= "/loadtabledata/save";
	actions.type = {
					idName:['ID'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
						},
					};

	actions.tableChange = function(name_table){
        if(name_table!="" && name_table!="undefined"){
            $("#listTables li").css('background-color','#fff');
            $("#listTables li#"+name_table).css('background-color','#44c7f4');
            $("#tableHeaderName").html(name_table);
            $("#frameEdit").attr('src','loadtabledata/edittable?table='+name_table);
        }
    }

    function exportDataXlsx() {
        var name_table = $("#tableHeaderName").text().trim();
        if (name_table != 'Data table') {
            showWaiting();
            window.open("/exporttabledata/" + encodeURIComponent(name_table));
            hideWaiting();
        } else {
            alert("Please! choose name table");
        }
    }
</script>
<script>
    (function() {
        var searchForm = document.getElementById('search-form'),
            textInput = searchForm.q,
            clearBtn = textInput.nextSibling;
        textInput.onkeyup = function() {
            clearBtn.style.visibility = (this.value.length) ? "visible" : "hidden";
        };
        clearBtn.onclick = function() {
            this.style.visibility = "hidden";
            textInput.value = "";
            scopeChange();
        };
    })();
    $( document ).ready(function() {
        $('#search_table').change( function () {
			var filter = $(this).val();
            filter = filter.toUpperCase();
			if(filter) {
				$("#listTables").find("li:not(:Contains(" + filter + "))").hide();
				$("#listTables").find("li:Contains(" + filter + ")").show();
			} else {
				$("#listTables").find("li").show();
			}
			return false;
		})
		.keyup( function () {
			$(this).change();
		});
    });
</script>
@stop

@section('content')
<script>
var groups={
		all: {label: 'All tables', list: []},
		energy: {label: 'Objects and Data', list: [
			'FACILITY','ENERGY_UNIT','ENERGY_UNIT_HISTORY','ENERGY_UNIT_STATUS','ENERGY_UNIT_GROUP','ENERGY_UNIT_DATA_FDC_VALUE','ENERGY_UNIT_DATA_VALUE','ENERGY_UNIT_DATA_THEOR','ENERGY_UNIT_DATA_ALLOC','ENERGY_UNIT_DATA_FIFO_ALLOC',
			'ENERGY_UNIT_DATA_PLAN','ENERGY_UNIT_DATA_FORECAST','ENERGY_UNIT_COMP_DATA_ALLOC','ENERGY_UNIT_CO_ENT_DATA_ALLOC','ENERGY_UNIT_CO_ENT_DATA_FIFO_ALLOC','ENERGY_UNIT_CO_ENT_COMP_ALLOC','EU_PHASE_CONFIG','EU_PHASE_CONFIG_HISTORY','WELL_COMP',
			'WELL_COMP_DATA_ALLOC','WELL_COMP_INTERVAL','WELL_COMP_INTERVAL_DATA_ALLOC','WELL_COMP_INTERVAL_PERF','WELL_COMP_INT_PERF_DATA_ALLOC','FLOW','FLOW_DATA_FDC_VALUE','FLOW_DATA_VALUE',
			'FLOW_DATA_THEOR','FLOW_DATA_ALLOC','FLOW_DATA_FIFO_ALLOC','FLOW_DATA_PLAN','FLOW_DATA_FORECAST','FLOW_COMP_DATA_ALLOC','FLOW_CO_ENT_DATA_ALLOC','FLOW_CO_ENT_DATA_FIFO_ALLOC','FLOW_CO_ENT_COMP_DATA_ALLOC','FLOW_DATA_FDC_VALUE_SUBDAY','FLOW_DATA_VALUE_SUBDAY',
			'FLOW_DATA_THEOR_SUBDAY','FLOW_DATA_ALLOC_SUBDAY','FLOW_DATA_PLAN_SUBDAY','FLOW_DATA_FORECAST_SUBDAY'
		]},
		code:{label: 'CODE', list: [
			'CODE_ALLOCATION_NODE','CODE_ALLOC_CODE','CODE_ALLOC_TYPE','CODE_BA_TYPE','CODE_BOOLEAN','CODE_COMMENT_CATEGORY','CODE_COMMENT_TYPE','CODE_COMMENT_STATUS',
			'CODE_ENV_CATEGORY','CODE_ENV_TYPE','CODE_ENV_STATUS','CODE_COMPOSITION','CODE_COLLECTION_POINT_FACILITY','CODE_CONTRAINST_SETTING_UOM','CODE_DATA_METHOD',
			'CODE_DEFER_CODE1','CODE_DEFER_CODE2','CODE_DEFER_CODE3','CODE_DEFER_GROUP_TYPE','CODE_DEFER_PLAN','CODE_DEFER_REASON','CODE_DEFER_REASON2','CODE_DEFER_STATUS',
			'CODE_DEFER_THEOR_METHOD','CODE_DEFER_CATEGORY','CODE_DENSITY_METHOD','CODE_ENERGY_METHOD','CODE_ENERGY_UNIT_TYPE','CODE_EQUIPMENT_TYPE','CODE_PLAN_TYPE',
			'CODE_FORECAST_TYPE','CODE_EQP_OFFLINE_REASON','CODE_EQP_FUEL_CONS_TYPE','CODE_EQP_GHG_REL_TYPE','CODE_EU_SUB_TYPE','CODE_EU_SCSSV_TEST_TYPE','CODE_EVENT_TYPE',
			'CODE_FACILITY_TYPE','CODE_FIXED_ADJUST','CODE_FLOW_CATEGORY','CODE_FLOW_DISP','CODE_FLOW_PHASE','CODE_FLOW_SUB_PHASE','CODE_GAS_COMPOSITION_METHOD',
			'CODE_GVOL_METHOD','CODE_GMASS_METHOD','CODE_INJECTING_METHOD','CODE_INJECTION_PLAN_METHOD','CODE_LOCATION','CODE_LIQ_COMP_ANALYSIS_METHOD','CODE_NETWORK_TYPE',
			'CODE_NVOL_METHOD','CODE_NMASS_METHOD','CODE_OBJECT_CLASS','CODE_ON_OFFSHORE','CODE_ONSTREAM_METHOD','CODE_PLAN_METHOD','CODE_POTENTIAL_METHOD','CODE_POWER_METHOD',
			'CODE_PRODUCING_METHOD','CODE_PRODUCT_TYPE','CODE_PRODUCTION_DATA_START','CODE_PRODUCTION_PLAN_METHOD','CODE_PHYSICAL_FORM','CODE_READING_FREQUENCY','CODE_SAFETY_CATEGORY',
			'CODE_SAFETY_SEVERITY','CODE_SAMPLE_TYPE','CODE_SDS_CAPACITY_METHOD','CODE_STATUS','CODE_STORAGE_TYPE','CODE_STRAPPING_MEASURE','CODE_STRAPPING_METHOD','CODE_STRAPPING_UOM',
			'CODE_SW_VOL_METHOD','CODE_TANK_TYPE','CODE_TANK_USAGE','CODE_TICKET_TYPE','CODE_TESTING_METHOD','CODE_TESTING_USAGE','CODE_THEORETICAL_METHOD','CODE_PORTABLE_TANK_LOCATION',
			'CODE_MMR_CLASS','CODE_MMR_ROOT_CAUSE','CODE_MMR_STATUS','CODE_MMR_WO_ACTION','CODE_MMR_REASON','CODE_MMR_CALC_METHOD'
		]},
		uom: {label: 'UOM', list: [
			'STANDARD_UOM','CODE_QUANTITY_TYPE','CODE_UOM_TYPE','CODE_VOL_UOM','CODE_MASS_UOM','CODE_POWER_UOM','CODE_ENERGY_UOM','CODE_DENS_UOM','CODE_TEMP_UOM',
			'CODE_PRESS_UOM','CODE_VOL_RATE_UOM','CODE_MASS_RATE_UOM','CODE_PWR_RATE_UOM','CODE_LENGTH_UOM','CODE_TIME_UOM','CODE_AREA_UOM',
			'CODE_WATER_DENS_UOM','CODE_MOLECULAR_WEIGHT_UOM','CODE_VISCOSITY_UOM','CODE_COMPRESSIBILITY_UOM','CODE_PERMIABILITY_UOM','UOM_CONVERSION','API_6A'
		]},
		storage: {label: 'Tanks & Storage', list: [
			'TANK','TANK_DATA_FDC_VALUE','TANK_DATA_VALUE','TANK_DATA_THEOR','TANK_DATA_ALLOC','TANK_DATA_PLAN','TANK_DATA_FORECAST','STORAGE','STORAGE_DATA_FDC_VALUE',
			'STORAGE_DATA_VALUE','STORAGE_DATA_THEOR','STORAGE_DATA_ALLOC','STORAGE_DATA_PLAN','STORAGE_DATA_FORECAST','STRAPPING_TABLE','STRAPPING_TABLE_DATA','RUN_TICKET_VALUE','RUN_TICKET_FDC_VALUE','TANK_DATA_FIFO_ALLOC','STORAGE_DATA_FIFO_ALLOC','TANK_CO_ENT_DATA_FIFO_ALLOC','STORAGE_CO_ENT_DATA_FIFO_ALLOC','TANK_CO_ENT_DATA_ALLOC','STORAGE_CO_ENT_DATA_ALLOC'
		]},
		reservoir: {label: 'Reservoir', list: ['RESERVOIR','RESERVOIR_BLOCK','RESERVOIR_FORMATION','RESERVOIR_BLOCK_FORMATION','WELL_COMP_INTERVAL_PERF','WELL_COMP_INTERVAL','WELL_COMP','ENERGY_UNIT','WELL_HOLE']},
		chemical: {label: 'Chemical', list: [
			'KEYSTORE','KEYSTORE_TANK','KEYSTORE_STORAGE','KEYSTORE_INJECTION_POINT','KEYSTORE_INJECTION_POINT_DAY','KEYSTORE_INJ_POINT_CHEMICAL','KEYSTORE_TANK_DATA_VALUE',
			'KEYSTORE_STORAGE_DATA_VALUE','CODE_KEYSTORE_TYPE','CODE_KEYSTORE_USAGE','CODE_INJECT_POINT'
		]},
		security: {label: 'Security', list: ['USER','USER_RIGHT','USER_RIGHT_GUI','USER_ROLE','USER_ROLE_RIGHT','USER_USER_ROLE','USER_DATA_SCOPE','LOG_USER']},
		audit: {label: 'Audit', list: ['AUDIT_RECORD','AUDIT_TRAIL','CODE_AUDIT_REASON','CODE_AUDIT_LOCK_STATUS','CODE_AUDIT_RECORD_STATUS','AUDIT_VALIDATE_TABLE','AUDIT_APPROVE_TABLE','LOCK_TABLE']},
		defer: {label: 'Deferment', list: [
			'CODE_DEFER_GROUP_TYPE','CODE_DEFER_THEOR_METHOD','DEFERMENT','DEFERMENT_DETAIL','DEFERMENT_GROUP','DEFERMENT_GROUP_SUB1','DEFERMENT_GROUP_SUB2','DEFERMENT_GROUP_EU',
			'MIS_MEASUREMENT','WORK_ORDER','WORK_ORDER_MMR'
		]},
		test: {label: 'Test', list: ['EU_TEST_DATA_FDC_VALUE','EU_TEST_DATA_STD_VALUE','EU_TEST_DATA_VALUE','EU_TEST_DATA_SCSSV']},
		config: {label: 'Config', list: ['CFG_FIELD_PROPS','CFG_INPUT_TYPE','CFG_DATA_SOURCE','GRAPH','GRAPH_DATA_SOURCE','DATA_TABLE_GROUP']},
		network: {label: 'Network', list: ['ALLOC_JOB','ALLOC_RUNNER','ALLOC_RUNNER_OBJECTS','ALLOC_CONDITION','ALLOC_COND_OUT','NETWORK','NETWORK_SUB','NETWORK_CONNECTION','NETWORK_OBJECT_MAPPING']},
		operation: {label: 'Operation', list: ['SAFETY','FACILITY_SAFETY_CATEGORY','COMMENTS','ENVIRONMENTAL','EQUIPMENT','EQUIPMENT_DATA_VALUE','EQUIPMENT_DATA_PLAN','EQUIPMENT_DATA_FORECAST','EQUIPMENT_GROUP','LOGISTIC']},
		quality: {label: 'Quality', list: ['QLTY_DATA','QLTY_DATA_DETAIL','CODE_QLTY_SRC_TYPE','QLTY_PRODUCT_ELEMENT_TYPE','QLTY_UOM','REFERENCE']},
		logical: {label: 'Logical', list: ['LO_CONTINENT','LO_COUNTRY','LO_STATE_PROVINCE','LO_PRODUCTION_UNIT','LO_AREA','LO_FIELD','LO_REGION','BA_ADDRESS','LICENSE','COST_INT_CTR','COST_INT_CTR_DETAIL','COST_INT_CATEGORY']},
		ce: {label: 'Calculation', list: ['FORMULA','FO_VAR','FO_GROUP']},
		tagmap: {label: 'Tags mapping', list: ['INT_TAG_MAPPING','INT_MAP_TABLE','INT_TABLE_COLUMN','INT_OBJECT_TYPE','INT_SYSTEM','INT_TAG_TRANS','INT_IMPORT_LOG']},
		personnel: {label: 'Personnel', list: ['PERSONNEL','PERSONNEL_SUM_DAY','CODE_PERSONNEL_TYPE','CODE_PERSONNEL_TITLE']},
		report: {label: 'Reports', list: ['RPT_GROUP','RPT_REPORT','RPT_PARAM']},
		datacapture: {label: 'Data capture', list: ['DC_ROUTE','DC_ROUTE_USER','DC_POINT','DC_POINT_FLOW','DC_POINT_EU','DC_POINT_TANK','DC_POINT_EQUIPMENT']},
		ghg: {label: 'GHG', list: [
			'CODE_GHG_PWR_RATE_UOM','CODE_GHG_VOL_RATE_UOM','CODE_GHG_ENGY_RATE_UOM','CODE_GHG_MASS_RATE_UOM','CODE_GHG_UOM','CODE_GHG_HEAT_VALUE','CODE_PROTOCOL','CODE_SECTOR','CODE_SEGMENT',
			'CODE_SOURCE_CATEGORY','CODE_SOURCE_CLASS','CODE_EPA_SOURCE_TYPE','CODE_API_SOURCE_TYPE','REL_SEGMENT_SOURCE_CATEGORY','REL_SOURCE_CLASS_EPA_SRC_TYPE','CODE_EMISSION_CALC_BY','CODE_EMISSION_EVENT_TYPE',
			'CODE_EMISSION_METHOD','CODE_CALC_SECTION','CODE_CALC_OPTION','CODE_EPA_SECTOR','COMBUSTION_EMISSION_GROUP','EMISSION_COMB_CALC_METHOD','INDIRECT_EMISSION_GROUP','INDIRECT_EMISSION_CALC_METHOD',
			'EVENT_EMISSION_GROUP','EMISSION_VENT','EVENT_EMISSION_CALC_METHOD','EMISSION_FACTOR','EMISSION_FACTOR_TABLE','EMISSION_FORMULA','HEAT_FACTOR','REL_EMI_FACTOR_TABLE_CALC_OPT','REL_EMI_FORMULA_CALC_OPTION',
			'GHG_EPA_FRS_ID_XREF','GLOBAL_WARMING_POTENTIAL','GHG_OBJECT_SETTING','EMISSION_EVENT_DATA_VALUE','EMISSION_EVENT_REL_DATA_VALUE','EMISSION_INDIRECT_DATA_VALUE','EMISSION_INDIR_REL_DATA_VALUE',
			'EMISSION_COMB_DATA_VALUE','EMISSION_COMB_REL_DATA_VALUE'
		]},
		pd_code: {label: 'PD Code', list: [
			'PD_CODE_BERTH_CODE','PD_CODE_CARGO_PRIORITY','PD_CODE_CARGO_QTY_TYPE','PD_CODE_CARGO_STATUS','PD_CODE_CARGO_TYPE','PD_CODE_INCOTERM','PD_CODE_LAYTIME_LAYCAN','PD_CODE_LOAD_ACTIVITY',
			'PD_CODE_PARCEL_QTY_TYPE','PD_CODE_PIPELINE_MATERIAL','PD_CODE_PIPELINE_TYPE','PD_CODE_PORT_LOCATION','PD_CODE_PORT_TYPE','PD_CODE_QTY_ADJ','PD_CODE_TANKER_CLASS','PD_CODE_TANK_MEASURE_METHOD',
			'PD_CODE_TERMINAL_TYPE','PD_CODE_TIME_ADJ','PD_CODE_INSPECT_TYPE','PD_CODE_TRANSIT_TYPE','PD_CODE_UNLOAD_ACTIVITY','PD_CODE_MEAS_UOM','PD_CODE_MEAS_ITEM','PD_CODE_LIFT_ACCT_ADJ','PD_CODE_DEMURRAGE_EBO','PD_TRIP_MEAS_CODE'
		]},
		pd_contract: {label: 'PD Contract', list: [
			'PD_CODE_CONTRACT_TYPE','PD_CODE_CONTRACT_ATTRIBUTE','PD_CODE_CONTRACT_PERIOD','PD_CODE_CONTRACT_PARTY_TYPE','PD_CONTRACT','PD_CONTRACT_DATA',
			'PD_CONTRACT_EXPENDITURE','PD_CONTRACT_FORMULA','PD_CONTRACT_PARTIES','PD_CONTRACT_QTY_FORMULA','PD_CONTRACT_TEMPLATE','PD_CONTRACT_TEMPLATE_ATTRIBUTE'
		]},
		pd_document: {label: 'PD Document', list: ['PD_REPORT_LIST','PD_CODE_ORGINALITY','PD_CODE_NUMBER','PD_DOCUMENT_SET','PD_DOCUMENT_SET_LIST','PD_DOCUMENT_SET_CONTACT_DATA','PD_DOCUMENT_SET_DATA']},
		pd_object: {label: 'PD Object', list: [
			'PD_CARGO','PD_CARGO_LOAD','PD_CARGO_UNLOAD','PD_CARGO_NOMINATION','PD_CARGO_SCHEDULE','PD_TRANSIT_CARRIER','PD_PORT','PD_BERTH','PD_VOYAGE','PD_VOYAGE_DETAIL',
			'PD_LIFTING_ACCOUNT','PD_LIFTING_ACCOUNT_MTH_DATA','PD_SHIP_LNG_TANK','PD_SHIP_OIL_LPG_TANK','PD_SHIP_PORT_INFORMATION','PD_TRANSIT_DETAIL','PD_TRANSPORT_GROUND_DETAIL','PD_TRANSPORT_PIPELINE_DETAIL',
			'PD_TRANSPORT_SHIP_DETAIL','PD_TRANSIT_PIPELINE','DEMURRAGE','PROC_TRAIN','PROC_TRAIN_BY_PRODUCT','SHIP_CARGO_BLMR','SHIP_CARGO_BLMR_DATA','PD_SHIPPER','PD_CARGO_SHIPPER','PD_COMPARTMENT','PD_SHIP_COMPART_POSITION','PD_SHIP_OIL_LPG_TANK_DATA','PD_TRANSIT_CARRIER_VEF_DATA','PD_TRANSIT_CARRIER_VCF_DATA'
		]},
		pd_activity: {label: 'PD Activity', list: ['TERMINAL_ACTIVITY_SET','TERMINAL_ACTIVITY_SET_LIST','TERMINAL_TIMESHEET_DATA','GAS_COOLDOWN','GAS_FILLING','COOLDOWN_DETAIL']},
		history: {label: 'History', list: ['ENERGY_UNIT_HISTORY','EU_PHASE_CONFIG_HISTORY','WELL_COMP_HISTORY','WELL_COMP_INTERVAL_HISTORY','WELL_COMP_INT_PERF_HISTORY','FLOW_HISTORY','COST_INT_CTR_DETAIL_HISTORY','TANK_HISTORY','STORAGE_HISTORY','EQUIPMENT_HISTORY']}
};

//create list all tables
var list = [];
for (var group in groups) {
	list = list.concat(groups[group].list);
}

groups.all.list = list;

//sort all lists
for (var group in groups) {
	groups[group].list = groups[group].list.filter( function( item, index, inputArray ) {
		return inputArray.indexOf(item) == index;
	});
	groups[group].list.sort();
}

    function renderListTable(group){
        $("#listTables").html('');
        for(var i = 0 ; i < groups[group].list.length ; i ++){
            var name_tb = groups[group].list[i];
            $("<li onclick=\"actions.tableChange(\'"+name_tb+"')\" value='"+name_tb+"' style='cursor: pointer;padding: 1px 5px;' id='"+name_tb+"'>"+name_tb+"</li>").appendTo( "#listTables" );
        }
	}

	function scopeChange(c){
		var s='';
		if(c) s=c;
		else s=$("#cboObjectScope").val();
		renderListTable(s);
	}
</script>
<table border="0" cellpadding="10" cellspacing="0" width="100%" id="table2">
	<tr>
		<td width="260">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table3">
			<tr>
				<td height="40">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table5">
					<tr>
						<td><b><font size="2">Category</font></b></td>
						<td align="right">
				<select onChange="scopeChange()" name="cboObjectScope" id="cboObjectScope" style="width:190px">
<script>
for (var group in groups) {
	document.write('<option value="'+group+'">'+groups[group].label+'</option>');
}
</script>				
				</select></td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td>
					<strong>Search</strong>
					<form style="float: right;position: relative;" id="search-form" action="" method="get" target="_blank">
						<span class="text-input-wrapper">
							<input style="width: 200px;margin-bottom: 5px;" id="search_table" type="text" name="q" autocomplete="off" size="18"/><span id="clear_value" style="position: absolute; top: 2px; right: 6px; cursor:pointer;color:blue;font-weight:bold;visibility:hidden;" title="Clear">&times;</span></span>
					</form>
				</td>
			</tr>
			<tr>
				<td height="440" valign="top">
					<ul style="width:99%;height:440px; list-style-type: none;padding:0; border: 1px solid rgb(169, 169, 169);overflow: auto;" id="listTables"></ul>
				</td>
			</tr>
		</table>
		</td>
		<td valign="top">
<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table4">
	<tr>
		<td>
		<div style="padding:5px;background:#D4E5EE; border-radius:4px">
			<input onClick="document.getElementById('frameEdit').contentWindow.addRecord();" style="height:30; width:110;" type="button" value="Add Record" name="B33">
			<input onClick="document.getElementById('frameEdit').contentWindow._delete_rows();" style="height:30; width:110;" type="button" value="Delete" name="B33">
			<input onClick="document.getElementById('frameEdit').contentWindow.saveChanges();" style="margin-right:5px;height:30; width:110;" type="button" value="Save Changes">
			<input onClick="exportDataXlsx();" style="float:right;margin-right:5px;height:30; width:130;" type="button" value="Export Excel">
			<input onClick="document.getElementById('frameEdit').contentWindow.genSQL(2);" style="float:right;margin-right:5px;height:30; width:130;" type="button" value="Generate Update">
			<input onClick="document.getElementById('frameEdit').contentWindow.genSQL(1);" style="float:right;margin-right:5px;height:30; width:130;" type="button" value="Generate Insert">
			<span id="tableHeaderName" style="font-size:10pt;font-weight:bold">Data table</span>
		</div>
	</tr>
	<tr>
		<td height="430">
		 <iframe id="frameEdit" style="width:100%;height:100%;padding:0px;border:medium none; " name="I1"></iframe>


		</td>
	</tr>
</table>
		</td>
	</tr>
</table>
<script>
	scopeChange();
</script>
@stop
