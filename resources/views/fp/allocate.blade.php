<?php
	$codeReadingFrequency	= array("name"		=> "CodeReadingFrequency",
									"id"		=> "CodeReadingFrequency",
									"modelName"	=> "CodeReadingFrequency",
									"enableTitle"=>false,
									"getMethod" => "loadActive");
	$tbWidth = config('constants.systemName')!='Santos'?930:530;
	$lang			= session()->get('locale', "en");
	$colspan =1;
?>
@extends('core.fp')

@section('adaptData')
@parent
<script>
	var floatFixed 	= 4;
	var tab			= '{{$key}}';
	actions.type = {
					idName	:	function (){
									var postData = actions.loadedData[tab];
									if(postData.IntObjectTypeName=='FLOW') return ['FLOW_ID','OCCUR_DATE'];
									if(postData.IntObjectTypeName=='ENERGY_UNIT') return ['EU_ID','EU_FLOW_PHASE','OCCUR_DATE'];
									return [''+postData.IntObjectTypeName+'_ID','OCCUR_DATE'];
								},
					keyField:'DT_RowId',
					saveKeyField : function (model){
							return 'ID';
						},
					};
	actions.renderFirsColumn = null;
	actions.getTableHeight	=	function(tab){
		headerOffset = $('#container_{{$key}}').offset();
		hhh = $(document).height() - (headerOffset?(headerOffset.top):0) - $('#ebFooter').outerHeight() -135;
		tHeight = ""+hhh+'px';
		return tHeight;
	};
	/* actions.getExtendWidth	= function(data,autoWidth,tab){
		return 280;
	} */
	var objs="";

	function addObject()
 	{
 		var id=$("").val();
 		var s='<span style="display:block;margin:1px 0px" info="'+
 		$("#IntObjectType option:selected").attr('name')+
 		':'+
 		$("#ObjectName").val()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		'">'+$("#IntObjectType option:selected").text()+
 		':'+
 		$("#ObjectName option:selected").text()+
 		' <img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="../img/x.png">';
 		
 		$("#selected_objects").append(s);
 	}
 	
	actions.initData = function(){

		var tabdata = {'{{config("constants.tabTable")}}'	:	tab,
					IntObjectTypeName :		$("#IntObjectType option:selected").attr('name')
				};
		return tabdata;
	}

	actions.getTableOption	= function(data){
		return {tableOption :	{searching		: false,
								ordering		: false,
								disableLeftFixer: true,
								autoWidth		: false,
// 								scrollY			: '350px',
								},
				invisible:[],
           		resetTableHtml : function(tabName) { return true}
        };
		
	}

	actions.setTableWidth	= function(tab,tblWdth){
// 		$('#container_'+tab).css('min-width',(tblWdth+40)+'px');
 		$('#container_'+tab).css('width',"auto");
	},
	

	actions.afterGotSavedData = function (data,table,tab){
    	var editedData = table.data();
    	 $.each(editedData, function( i, rowData ) {
    		 	var id = rowData['DT_RowId'];
    		 	if ((typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1)) {
    		 		table.row($('#'+id)).remove().draw(false);
			    }
          });

    	 postData = data.postData;
    	 if(postData!=null
    			 &&typeof(postData.deleteData) !== "undefined"
    	    	 &&postData.deleteData.hasOwnProperty(tab)
    	    	 &&postData.deleteData[tab]!=null
    			 &&postData.deleteData[tab].hasOwnProperty('clearTable')
    			 &&typeof(data.postData.deleteData[tab].clearTable) !== "undefined"
        		 &&data.postData.deleteData[tab].clearTable){
    		 	table.clear().draw(false);
			}
	};
	
	function getPreFix(source_type){
		obj_id_prefix	=	source_type;
		field_prefix	=	source_type;
		if(source_type=="ENERGY_UNIT"){
			obj_id_prefix	="EU";
		}
		else if(source_type=="FLOW") {
			obj_id_prefix="FL";
		}
		
		if(source_type=="FLOW"||source_type=="ENERGY_UNIT"){
			field_prefix	= obj_id_prefix+"_DATA";
		}
		return field_prefix+'_';
	}

	function deletePlan()
	{
		var id=$("#ObjectName").val();
		if(id<=0){
			alert("Please select object");
			return;
		}
		if(!confirm("Do you want to delete data?")) return;

		tab = '{{$key}}';
		actions.deleteData[tab] = {source_type	: $("#IntObjectType option:selected").attr('name'),
											clearTable	:true};
		actions.editedData = {tab	: []};
		actions.doSave(true);
	}

	function calculateAllocPlan(){

		var index = 1000;
		
		getRowData = function (date,prefix,postData,source_type,codeReadingFrequency){
			var row = {
					"DT_RowId"			: 'NEW_RECORD_DT_RowId'+(index++),
					"OCCUR_DATE"		: date,
					"RECORD_FREQUENCY"	: codeReadingFrequency,
// 					"FLOW_ID": 434,
				};
			row[prefix+"GRS_VOL"] 		= s_grs_vol;
            row[prefix+"NET_VOL"] 		= s_net_vol;
			row[prefix+"GRS_MASS"] 		= s_grs_mass;
            row[prefix+"NET_MASS"] 		= s_net_mass;
			row[prefix+"GRS_ENGY"] 		= s_grs_energy;
			row[prefix+"GRS_PWR"] 		= s_grs_power;

			if(source_type=="ENERGY_UNIT"){
				row["EU_FLOW_PHASE"] 		= postData.ExtensionPhaseType;
				row["EU_ID"] 				= postData.ObjectName;
			}
			else if(source_type=="FLOW") {
				row["FLOW_ID"] 				= postData.ObjectName;
			}
			else row[""+source_type+"_ID"] 	= postData.ObjectName;
			
			var eData = actions.editedData[tab];
    		eData.push(row);
			return row;
		}
		
		//$(".cal_row").remove();
		var d1 = $("#date_begin").datepicker( 'getDate' );
		var d2 = $("#date_end").datepicker( 'getDate' );
		var c=0;
		var df	= $("#CodeReadingFrequency").find(":selected").attr( "name");
		var codeReadingFrequency	= $("#CodeReadingFrequency").val();
		if(df=="DAY"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 1)) {
				c++;
			}
		}
		else if(df=="WK"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 7)) {
				c++;
			}
		}
		else if(df=="MON"){
			for (var d = d1; d <= d2; d.setMonth(d.getMonth() + 1)) {
				c++;
			}
		}
		if(c<=0){
			alert("No date to calculate");
			return;
		}
		//alert("count="+c); return;
		var t_grs_vol=parseFloat($("#t_grs_vol").val());
        var t_net_vol=parseFloat($("#t_net_vol").val());
		var t_grs_mass=parseFloat($("#t_grs_mass").val());
        var t_net_mass=parseFloat($("#t_net_mass").val());
		var t_grs_energy=parseFloat($("#t_grs_energy").val());
		var t_grs_power=parseFloat($("#t_grs_power").val());
		var s_grs_vol="";
        var s_net_vol="";
		var s_grs_mass="";
        var s_net_mass="";
		var s_grs_energy="";
		var s_grs_power="";
		if(!isNaN(t_grs_vol)) s_grs_vol=""+(t_grs_vol/c).toFixed(floatFixed);
        if(!isNaN(t_net_vol)) s_net_vol=""+(t_net_vol/c).toFixed(floatFixed);
		if(!isNaN(t_grs_mass)) s_grs_mass=""+(t_grs_mass/c).toFixed(floatFixed);
        if(!isNaN(t_net_mass)) s_net_mass=""+(t_net_mass/c).toFixed(floatFixed);
		if(!isNaN(t_grs_energy)) s_grs_energy=""+(t_grs_energy/c).toFixed(floatFixed);
		if(!isNaN(t_grs_power)) s_grs_power=""+(t_grs_power/c).toFixed(floatFixed);
		
		if(isNaN(t_grs_vol) && isNaN(t_net_vol) && isNaN(t_grs_mass) && isNaN(t_net_mass) && isNaN(t_grs_energy) && isNaN(t_grs_power)){
			alert("Please input value to allocate");
			$("#t_grs_vol").focus();
			return;
		}

		if(t_grs_vol<0 || t_net_vol < 0 || t_grs_mass < 0 || t_net_mass < 0 || t_grs_energy<0 ||t_grs_power <0 ){
			alert("Please input value >= 0");
			return;
		}
// 		$("#tableData").html("");
		var dates="";
		d1 = $("#date_begin").datepicker( 'getDate' );
		var dx=d1.getFullYear() + '-' + (d1.getMonth() + 1) + '-' + d1.getDate();
		$("#f_date_from").val(dx);
		dx=d2.getFullYear() + '-' + (d2.getMonth() + 1) + '-' + d2.getDate();
		$("#f_date_to").val(dx);

		/* var postData = actions.loadedData['{{$key}}'];
		postData 	= typeof(postData) !== "undefined"?postData	:	actions.loadParams(true); */
		actions.editedData[tab] = [];
		var postData = actions.loadParams(true);
		actions.loadedData[tab] = postData;
		
		var source_type = postData.IntObjectTypeName;
		var prefix = getPreFix(source_type);
		actions.deleteData[tab] = {source_type	: source_type};
		
		var properties =  [{
		 	"data": "OCCUR_DATE",
		 	"title": "Occur Date",
		 	"width": 100,
		 	"INPUT_TYPE": 3,
		 	"DATA_METHOD": 2,
		 	"FIELD_ORDER": 1
		 }, 
		 @if(config('constants.systemName')!='Santos')
		 {
		 	"data": prefix+"GRS_VOL",
		 	"title": "Gross Vol",
		 	"width": 100,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 2
		 },
			{
		 	"data": prefix+"GRS_MASS",
		 	"title": "Gross Mass",
		 	"width": 100,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 4
		 },
			{
			"data": prefix+"NET_MASS",
			"title": "Net Mass",
			"width": 100,
			"INPUT_TYPE": 2,
			"DATA_METHOD": 1,
			"FIELD_ORDER": 5
		},
		  {
		 	"data": prefix+"GRS_PWR",
		 	"title": "Gross Power",
		 	"width": 100,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 7
		 },
		 @endif
			{
            "data": prefix+"NET_VOL",
            "title": "Net Vol",
            "width": 100,
            "INPUT_TYPE": 2,
            "DATA_METHOD": 1,
            "FIELD_ORDER": 3
		 },
			{
		 	"data": prefix+"GRS_ENGY",
		 	"title": "Gross Energy",
		 	"width": 100,
		 	"INPUT_TYPE": 2,
		 	"DATA_METHOD": 1,
		 	"FIELD_ORDER": 6
		 },
		 ];

		var tableData = {
							properties		: 	properties,
							postData		:	postData,
							removeOldData	:	false
						};
		var dataSet	=	[];
		if(df=="DAY"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 1)) {
				dx=d.getFullYear() + '-' + zeroFill((d.getMonth() + 1),2) + '-' + zeroFill(d.getDate(),2);
				var row = getRowData(dx,prefix,postData,source_type,codeReadingFrequency);
				dataSet.push(row);
			}
		}
		else if(df=="WK"){
			for (var d = d1; d <= d2; d.setDate(d.getDate() + 7)) {
				dx=d.getFullYear() + '-' + zeroFill((d.getMonth() + 1),2) + '-' + zeroFill(d.getDate(),2);
				var row = getRowData(dx,prefix,postData,source_type,codeReadingFrequency);
				dataSet.push(row);
			}
		}
		else if(df=="MON"){
			for (var d = d1; d <= d2; d.setMonth(d.getMonth() + 1)) {
				dx=d.getFullYear() + '-' + zeroFill((d.getMonth() + 1),2) + '-' + zeroFill(d.getDate(),2);
				var row = getRowData(dx,prefix,postData,source_type,codeReadingFrequency);
				dataSet.push(row);
			}
		}
		tableData["dataSet"] = dataSet;
		actions.loadSuccess(tableData);
	}
</script>
@stop

@section('content')
	<style>
 		.dataTables_scrollHeadInner{
    		background-color: rgb(230, 230, 230); 
    	}
    	#CodeReadingFrequency{
    	    width: 100%;
    	    background:#ffff88;
			margin: 0 0 8px 0;
    	}
		#table_allocateforecast_input thead th, #table_allocateforecast_action thead th{
			padding: 10px 18px;
			white-space: nowrap;
			border-bottom: 1px solid #111111;
		}
	</style>
	<div>
		<table border="0" id="table_{{$key}}_input" class="fixedtable nowrap display" cellspacing="0" style="display:inline-block;float:left;width:{{$tbWidth}}px">
			<thead>
				<tr id="_rh" style="background:#E6E6E6;" role="row">
					<th rowspan="1" colspan="1" style="position: relative; left: 0px; background-color: rgb(230, 230, 230);"><b>Occur Date</b>	</th>
					@if(config('constants.systemName')!='Santos')
					<th rowspan="1" colspan="1"><b>Gross Vol</b>	</th>
					@endif
					<th rowspan="1" colspan="{{$colspan}}"><b>{{\Helper::translateText($lang,"Net Vol")}}</b>	</th>
					@if(config('constants.systemName')!='Santos')
					<th rowspan="1" colspan="1"><b>Gross Mass</b>	</th>
					<th rowspan="1" colspan="1"><b>Net Mass</b>	</th>
					@endif
					<th rowspan="1" colspan="{{$colspan}}"><b>{{\Helper::translateText($lang,"Gross Energy")}}</b>	</th>
					@if(config('constants.systemName')!='Santos')
					<th rowspan="1" colspan="1"><b>Gross Power</b>	</th>
					@endif
				</tr>
				<tr style="background:#E6E6E6;height:40px" role="row">
					<th style="position: relative; left: 0px; background-color: rgb(230, 230, 230);" rowspan="1" colspan="1"></th>
					@if(config('constants.systemName')!='Santos')
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_vol" class="_numeric" style="width:100%;background:#ffff88"></th>
					@endif
					<th rowspan="1" colspan="1"><input type="number" id="t_net_vol" class="_numeric" style="width:100%;background:#ffff88"></th>
					@if(config('constants.systemName')!='Santos')
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_mass" class="_numeric" style="width:100%;background:#ffff88"></th>
					<th rowspan="1" colspan="1"><input type="number" id="t_net_mass" class="_numeric" style="width:100%;background:#ffff88"></th>
					@endif
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_energy" class="_numeric" style="width:100%;background:#ffff88"></th>
					@if(config('constants.systemName')!='Santos')
					<th rowspan="1" colspan="1"><input type="number" id="t_grs_power" class="_numeric" style="width:100%;background:#ffff88"></th>
					@endif
				</tr>
				<tr style="background:#E6E6E6;height:40px;display:none">
					<th></th>
					<th></th>
					<th></th>
					@if(config('constants.systemName')!='Santos')
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					@endif
				</tr>
			</thead>
		</table>
		<table border="0" id="table_{{$key}}_action" class="fixedtable nowrap display" style="display:inline-block;float:left" cellspacing="0">
			<thead>
				<tr id="_rh" style="background:#E6E6E6;" role="row">
					<th><b>Record Frequency</b></th>
					<th></th>
				</tr>
				<tr style="background:#E6E6E6;height:40px" role="row">
					<th>
						{{\Helper::filter($codeReadingFrequency)}}
					</th>
					<th style="padding:3px;width:260">
						<input type="button" onClick="calculateAllocPlan()" style="width:80px;height:30px;" value="Calculate">
						<input type="button" onClick="deletePlan()" style="width:80px;height:30px;" value="Delete">
				<!-- <input type="button" onClick="save()" style="width:80px;height:30px;" value="Save">-->
					</th>
				</tr>
			</thead>
		</table>
	</div>
	
	<div id="container_{{$key}}">
	</div>
@stop