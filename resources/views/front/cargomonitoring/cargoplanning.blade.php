<?php
	$currentSubmenu ='/pd/cargoplanning';
	$tables = ['PdCargo'	=>['name'=>'Data']];
	$isAction = false;
?>

@extends('datavisualization.storagedisplay')
@section('funtionName')
CARGO PLANNING
@stop

@section('adaptData')
@parent
<div id="confirm_cargo" style="display:none"><table width='100%'><thead><tr><th>Storage</th><th>Shipper</th><th>Lifting Account</th><th>Request Date</th><th>Quantity</th></tr></thead><tbody></tbody></table></div>
<style>
#table_quality th div{width:100px;font-size:10pt}
#table_quality thead td{text-align:center}
#table_quality td {background:white}
#table_quality td {border:1px solid #aaaaaa;padding-left:5px;padding-right:5px;}
#table_quality th {border:0px solid #aaaaaa;padding-left:5px;padding-right:5px;}
#table_quality .group1_th {background:rgb(146,208,80)}
#table_quality .group1_td {background:rgb(216,228,188)}
#table_quality .group2_th {background:rgb(255,255,0)}
#table_quality .group2_td {background:rgb(255,255,153)}
#table_quality .group3_th {background:rgb(204,192,218)}
#table_quality .group3_td {background:rgb(221,213,231)}
#table_quality tbody td {text-align:right}
.td_highlight {color: #378de5;font-weight:bold;}
#table_quality .td_plan {text-align:center;font-weight:bold}
#table_quality .td_cal_balance {color:orange}
#table_quality .td_monnth_bal {color:#360046; background:#f2c6ff}
#table_quality .td_gen_cargo {text-align:center;color:white;cursor:pointer;background:#378de5}
#table_quality .td_has_plan {border:1px solid #378de5}
#table_quality .box_gen_cargo {cursor:pointer;position:absolute;background:#378de5;color:white;width:25px;text-align:center}
#confirm_cargo td{padding-right:30px}
</style>
<script>
$(document).ready(function(){
	$("#date_begin, #date_end").off('change');
	$('.addButton').trigger("click");
	$('.addButton').trigger("click");
	$("#diagramTableAction").hide();
	//loadOpenningBalanceConfig();
	$("#menu_navi .active_item").html("Cargo Planning");
});

	var isLoadPlanningData = false;
	var last_storage_id = 0;
	var formData="", balanceData={};
	function genCargoEntry(obj){
		var values = [];
		if(obj == undefined){
			$(".box_gen_cargo").each(function(){
				var x = $(this).attr('gen_cargo');
				if(x != undefined && x != "" && x != null){
					//console.log(x);
					values.push(JSON.parse($(this).attr('gen_cargo')));
				}
			});
		}
		else{
			values.push(JSON.parse($(obj).attr('gen_cargo')));
		}
		if(values.length>0){
			var texts = "";
			var storage_name = $("#Storage option:selected").text();
			$.each(values, function( index, value ) {
				texts += "<tr><td>"+storage_name+"</td><td>"+value.shipper_name+"</td><td>"+value.la_name+"</td><td>"+value.req_date_disp+"</td><td>"+value.qty+"</td></tr>";
				//texts += storage_name+" \t\t\t  "+value.shipper_name+" \t\t\t  "+value.la_name+" \t\t\t  "+value.req_date+" \t\t  "+value.qty+"\n";
			});
			$( "#confirm_cargo tbody" ).html(texts);
			$( "#confirm_cargo" ).dialog({
				  title: "Confirm Generate Cargo Entry",
				  modal: true,
				  width:700,
				  buttons: {
					"Generate": function(){
						var post_data = {cargo_data	: values};
// 						"cargo_data="+JSON.stringify(values);
						//console.log(post_data);
						$.post("/cargoplanning/gen",
						post_data,
						function(data, status){
							alert(data==""?"Success":JSON.stringify(data));
						}).fail(function(data) {
							alert("Can not generate Cargo Entry");
						});
						$( this ).dialog( "close" );
					},
					Cancel: function() {
					  $( this ).dialog( "close" );
					}
				  }
				});	
	/*
			if(confirm("Generate new Cargo Entry?\n"+texts)){
				var post_data = "cargo_data="+JSON.stringify(values);
				//console.log(post_data);
				$.post("/pd/cargoplanning_gen_cargo.php",
				post_data,
				function(data, status){
					alert(data==""?"Success":data);
				}).fail(function(data) {
					alert("Can not generate Cargo Entry");
				});
			}
			*/
		}
	}

	function txt_balance_keypress(e){
		if (e.keyCode == 13) {
			actions.doLoad(true);
		}
	}
	$( document ).ready(function() {
		$("#mainContent").html("<form name='form_fdc' id='form_fdc'><div style='width:100%;overflow-x: auto;'><table border='0' cellpadding='2' cellspacing='2' id='table_quality' class='display compact'></table></div></form>");
	    var onChangeFunction = function() {
		    if($('#Storage option').size()>0 ) actions.doLoad(true);
	    };
	    $( "#Storage" ).change(onChangeFunction);
	});

	actions.loadUrl = "/cargoplanning/load";

	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

	var ignoreGen		= false;
	var oloadValidating	= actions.loadValidating;
	actions.loadValidating = function (reLoadParams){
		var storage_id = $("#Storage").val();
		if($("#txt_balance").val()==="" && storage_id==last_storage_id){
			$("#txt_balance").focus();
			alert("Please enter balance");
			return false;
		}
		last_storage_id = storage_id;
		$("#buttonLoadData").hide();
		if(currentRowData!=null){
			var day1 		= $("#date_begin").datepicker('getDate').getDate();                 
            var month1 		= $("#date_begin").datepicker('getDate').getMonth() + 1;             
            var year1 		= $("#date_begin").datepicker('getDate').getFullYear();
            var dateFrom 	= year1 + "-" + month1 + "-" + day1;
            day1 			= $("#date_end").datepicker('getDate').getDate();                 
            month1 			= $("#date_end").datepicker('getDate').getMonth() + 1;             
            year1			= $("#date_end").datepicker('getDate').getFullYear();
            var dateTo 		= year1 + "-" + month1 + "-" + day1;
            
			currentRowData.TO_DATE 		= dateTo;
			currentRowData.FROM_DATE 	= dateFrom;
			if(!ignoreGen){
				$('#genChartBtn').trigger("click");
				return {	state		: false};
			}
		}
		else{
			balanceData = {};
		}
		var rs 					= oloadValidating(reLoadParams);
		rs.params.txt_balance	= $("#txt_balance").val();
		jQuery.extend(rs.params, balanceData);
		return rs;
	}

	var oLoadError = actions.loadError;
	actions.loadError =  function(data){
		oLoadError(data);
		$("#buttonLoadData").show();
// 		isLoadPlanningData = false;
	}

	var oLoadSuccess	= actions.loadSuccess;
	actions.loadSuccess =  function(data){
		if(isLoadPlanningData){
			$("#table_quality").html(data);
			$("#buttonLoadData").show();
			if($("#txt_balance").val()==="") $("#txt_balance").focus();
// 			isLoadPlanningData = false;
		}
		else oLoadSuccess(data);
	}

	var oDoLoad	= actions.doLoad;
	actions.doLoad = function(reLoadParams){
		isLoadPlanningData 	= true;
		ignoreGen	= false;
		return oDoLoad(reLoadParams);
	}
	
	/* actions.doLoad = function(){
            var day1 = $("#date_begin").datepicker('getDate').getDate();                 
            var month1 = $("#date_begin").datepicker('getDate').getMonth() + 1;             
            var year1 = $("#date_begin").datepicker('getDate').getFullYear();
            var dateFrom = year1 + "-" + month1 + "-" + day1;
            day1 = $("#date_end").datepicker('getDate').getDate();                 
            month1 = $("#date_end").datepicker('getDate').getMonth() + 1;             
            year1 = $("#date_end").datepicker('getDate').getFullYear();
            var dateTo = year1 + "-" + month1 + "-" + day1;
			var storage_id = $("#Storage").val();
		if($("#txt_balance").val()==="" && storage_id==last_storage_id){
			$("#txt_balance").focus();
			alert("Please enter balance");
			return;
		}
		last_storage_id = storage_id;
		$("#buttonLoadData").hide();
		//if($('#dateFrom').val()!="" && $('#dateTo').val()!="" && $("#cboFacility").val()>0)
		{
			formData = $("#form_fdc").serialize();
			if(!formData)
				formData = "";
			formData += (formData==""?"":"&")+"dateFrom="+dateFrom+"&dateTo="+dateTo+"&cboFacility="+$("#Facility").val()+"&cboStorage="+storage_id+"&dateformat="+jsFormat;
			balanceData = {};
			if(currentRowData!=null){
				currentRowData.TO_DATE = dateTo;
				currentRowData.FROM_DATE = dateFrom;
				$('#genChartBtn').trigger("click");
			}
			else
				loadData();
		}
	}
	 */
	function loadData(){
		$.post("/pd/cargoplanning_load.php?"+formData,
			balanceData,
			function(data, status){
					$("#table_quality").html(data);
					$("#buttonLoadData").show();
					if($("#txt_balance").val()==="")
						$("#txt_balance").focus();
			});
	}
	var PlotViewConfigID=-1;
	function loadOpenningBalanceConfig(){
		$.get("/pd/cargoplanning_load_ob_config.php",
			null,
			function(data, status){
				PlotViewConfigID = Number(data);
			});
	}
	function saveOpenningBalanceConfig(config){
		$.get("/pd/cargoplanning_save_ob_config.php",
			config,
			function(data, status){
				alert("Save configuration successfully!");
			});
	}
</script>
@stop
@section('endDdaptData')
@parent
<script>
var currentRowData;
 editBox.updateMoreObject = function (rowData){
  rowData.viewName = $("#viewName").val();
  //rowData.FROM_DATE = "";
  //rowData.TO_DATE = "";
  //saveOpenningBalanceConfig(rowData.OBJECTS);
  console.log(rowData);
  currentRowData = rowData;
 }
 
editBox.genDiagram = function (diagram,view){
  	if(typeof diagram == "undefined") return;
  	console.log(diagram.series);
  	if(diagram.series.length > 0) balanceData.balanceData = diagram.series[0].data;
  	if(diagram.series.length > 1) balanceData.laData = diagram.series[1].data;
  //balanceData={'balanceData':diagram.series[0].data};
  	ignoreGen	= true;
  	isLoadPlanningData 	= true;
  	oDoLoad(true);
//   loadData();
/*
	var series   = diagram.series;
	console.log(series);
	$.each(series, function( index, value ) {
	$.each(value.data, function( index, data ) {
    day = getJsDate(data.D);
    pvalue = parseFloat(data.V);
    pvalue = isNaN(pvalue)?null:pvalue;
    value.data[index] = [day,pvalue];
          });
}); 
*/
}

oEditObjectMoreHandle = editBox.editObjectMoreHandle;
editBox.editObjectMoreHandle = function (table,rowData,td,tab) {
	rowData.PlotViewConfig = PlotViewConfigID;
 //rowData.OBJECTS = JSON.parse(viewConfig);
 oEditObjectMoreHandle(table,rowData,td,tab);
};
 function showConfig(i){
/* 	 if(!(PlotViewConfigID>0))
	 {
		 alert("Openning Balance configuration not ready");
		 return;
	 } */
	 $('a[id^="item_edit_"]').eq(i).trigger("click");
 }
 
 function onGenDiagramError(data){
	 _alert("Error happened. Please make sure both Openning balance and Entitlement were configured correctly.");
	 $("#buttonLoadData").show();
 }
</script>
@stop
