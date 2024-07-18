<?php
use Carbon\Carbon;
$currentSubmenu = '/am/validatedata';
$configuration	= isset($configuration)?$configuration:auth()->user()->getConfiguration();

$useFeatures[]	= ['name'	=>	"table_status",
					"data"	=>	["selfAction"	=> "_validatedata"]];
$yesterday	= Carbon::yesterday();

$filterbeginDate = [
    				'name'	=> "Begin date",
					'id'	=> "begin_date",
    				'value'	=> $yesterday,
					];
$filterEndDate = [
    				'name'	=> "End date",
					'id'	=> "end_date",
    				'value'	=> $yesterday,
					];
$listControls = [
		'LoProductionUnit' => array (
				'label' => 'Production Unit',
				'ID' => 'LoProductionUnit'
		),

		'LoArea' => array (
				'label' => 'Area',
				'ID' => 'LoArea',
				'fkey' => 'production_unit_id'
		),

		'Facility' => array (
				'label' => 'Facility',
				'ID' => 'Facility',
				'fkey' => 'area_id'
		),

		'DataTableGroup' => array (
				'label' => 'DataTableGroup',
				'ID' => 'DataTableGroup',
				'default' => 'All'
		),

		'IntObjectType' => array (
				'label' => 'Object Type',
				'ID' => 'IntObjectType',
				'default' => 'All'
		),

		'loadData' => array(
				'label' => 'Load data sources',
				'ID' => 'loadData',
				'TYPE' => 'BUTTON',
				'onclick' => '_validatedata.loadData()'
		),
];

?>

@extends('core.bsmain',['subMenus'   => null])

@section('action_extra')
<div style="float: left;">
	<div>
		<input type="button" value="Validate Data" onclick="_validatedata.validateAll('V')" style="width:150px;">
		<input type="button" value="Set back to Provisional" onclick="_validatedata.validateAll('P')" style="width:170px;">
	</div>
	<div style="margin-top: 3px;">
		<input type="button" value="Check Status" id="buttonAdd" name="buttonCheckStatus" onClick="_validatedata.loadChart()" style="width: 150px;background-color: green;">
	</div>
</div>
@stop

@section('content')
	{{--<link rel="stylesheet" href="/common/css/bootstrap.min.css" />--}}
	<link rel="stylesheet" href="/common/css/bootstrap-multiselect.css" />
	{{--<script type="text/javascript" src="/common/js/bootstrap.min.js"></script>--}}
	<script type="text/javascript" src="/common/js/bootstrap-multiselect.js"></script>
	<script src="/common/js/highchartssrc.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var height = $( window ).height();
    var width  = $( window ).width();
	$('#ObjectDataSource').multiselect();
    $("#ObjectDataSource").each(function(){
        $(this).multiselect({
            enableFiltering: true,
            allSelectedText: 'All',
            numberDisplayed: 1,
            nSelectedText: 'Selected',
            nonSelectedText: 'None Selected'
        });
        $(this).parent().find('ul').attr('style','display:block;');
    });
    $("#ebFooter").css({"position":"absolute","bottom":"0","width":"100%"});
    $(".documentBody").css("height",height);
    $("ul.multiselect-container li").css("width","270px");
    $("ul.multiselect-container").css({"overflow":"scroll","width":"270px","height":height-197,"margin":"-13px 0px 0px 5px","z-index":"0"});
    $("#ObjectDataSource").css("display","none");
    $("#CheckStatus").css({"height":height-181,"width":width-313});
    $("#background_obj").css("height",height-158);

    $(".check_all_obj").click(function () {
        if ($(this).prop('checked') == true) {
            $("ul.multiselect-container li a label input").prop('checked', true);
            $('#ObjectDataSource option').prop('selected', true);
            $("ul.multiselect-container > li").addClass("active");
        } else {
            $("ul.multiselect-container li a label input").prop('checked', false);
            $('#ObjectDataSource option').prop('selected', false);
            $("ul.multiselect-container > li").removeClass("active");
        }
    });
    $( window ).resize(function() {
        var h = $( window ).height();
        var w = $( window ).width();
        $("ul.multiselect-container").css({"height":h-197});
        $(".documentBody").css("height",h);
        $("#background_obj").css("height",h-158);
        $("#CheckStatus").css({"height":h-181,"width":w-313});
    });
    $("select#DataTableGroup").attr("onchange","_validatedata.loadData()");
    $(".filterContainer div.product_filter:not(#filterFrequence)").attr("id","product_filter1");
    $("#filterFrequence").insertAfter(".date_filter");
    $(".date_filter").insertAfter("#filterFrequence").insertAfter("#product_filter1");
    $("#filterFrequence").css("width","271px");
    $("#DataTableGroup").css("width","269px");

    $('#ObjectDataSource').change(function(){
		($('#ObjectDataSource').val()).length == $('#ObjectDataSource option').size() ?
			$(".check_all_obj").prop('checked', true) : $(".check_all_obj").prop('checked', false);
    });
});
$(function(){
	$("#checkAll").click(function () {
        $('.chckbox').prop('checked', this.checked);
	});
    //$("#DataTableGroup").val(0);
    //$("#IntObjectType").val(0);
	_validatedata.loadData();
    $(".btn-group button.dropdown-toggle").remove();
    $(".product_filter, .date_filter").css("height","60px");
    $("#date_begin, #date_end").css({"height":"20px","width":"88%"});
});


var _validatedata = {
		loadData : function (){
			param = {
				'FACILITY_ID' : $('#Facility').val(),
				'GROUP_ID' : $('#DataTableGroup').val(),
				'OBJECTTYPE' : $('#IntObjectType').val()
			}

			sendAjax('/am/loadValidateData', param, function(data){
				_validatedata.listData(data.result);
			});
		},

		listData : function(data){
			var str = '';
			$('#ObjectDataSource').html('');

/*			for(var i = 0; i< data.length; i++){
				var id = data[i].ID;
				var k = i+"abc"+data[i].TABLE_NAME;
				var cssClass = "row1";
				if(i%2 == 0){
					cssClass = "row2";
				}
				str += '<tr class='+ cssClass +'>';
				str += '	<td class="vcolumn35"><input class="chckbox" table_name = '+data[i].TABLE_NAME+' name='+data[i].ID +' type="checkbox" ' + (data[i].T_ID) + '></td>';
				str += '	<td class="vcolumn205" id="table_name_'+i+'">'+ checkValue(data[i].TABLE_NAME,'') +'</td>';
				//str += '	<td class="vcolumn205">'+ checkValue(data[i].FRIENDLY_NAME,'') +'</td>';
				//str += '	<td class="vcolumn165"><input type="text" id="txtDateFrom_'+i+'" value="'+formatDate(checkValue(data[i].DATE_FROM,''))+'"/></td>';
				//str += '	<td class="vcolumn165"><input type="text" id="txtDateTo_'+i+'" value="'+formatDate(checkValue(data[i].DATE_TO,''))+'"/></td>';
				//str += '	<td class="vcolumn105"><input type="button" onclick="_validatedata.validateData('+i+')" value="Validate" class="btnValidate"/></td>';
				str += '</tr>';
			}*/
            for(var i = 0; i< data.length; i++){
                str += '<option name="'+ checkValue(data[i]) +'" value="'+ checkValue(data[i]) +'">'+ checkValue(data[i]) +'</option>';
            }

			$('#ObjectDataSource').html(str);

			$( "input[type='text']" ).datepicker({
				changeMonth	:true,
				changeYear	:true,
				dateFormat	:jsFormat
			});

            $('#ObjectDataSource').multiselect('rebuild');
            $(".check_all_obj").prop('checked', false);

            /*for(var j = 0; j< data.length; j++){
				if(data[j]["T_ID"] == "checked"){
				    var name_tb = data[j]["TABLE_NAME"];
				    $("ul.multiselect-container li a label input[value='"+name_tb+"']").prop('checked', true);
                    $("#ObjectDataSource option[value='"+name_tb+"']").prop('selected', true);
				}
            }*/
		},

		validateData : function(index){
			var tableName = $('#table_name_'+index).text();
			var dateFrom = $('#txtDateFrom_'+index).val();
			var dateTo = $('#txtDateTo_'+index).val();

			if(dateFrom == "" || dateTo == ""){
				alert("Please select date range to validate data");
				return;
			}

			param = {
					'FACILITY_ID' : $('#Facility').val(),
					'DATE_FROM' : dateFrom,
					'DATE_TO' : dateTo,
					'TABLE_NAMES' : tableName,
					'GROUP_ID' : $('#DataTableGroup').val(),
					'OBJECTTYPE' : $('#IntObjectType').val()
			}

			sendAjax('/am/validateData', param, function(data){
				alert(data.message);
				if(typeof data.result == "object" && data.result !=null) _validatedata.listData(data.result);
			});
		},

		validateAll : function(prefix){
			var tableName="";
			var table = $("#ObjectDataSource").val();
			$('.chckbox').each(function(){
				if(this.checked) tableName+=(tableName==""?"":",")+$(this).attr("table_name");
			});
			var act = (prefix=='P'?'set back to Provisional':'validate');
			if(table == null)
			{
				alert("Please select tables that you want to "+act+" data");
				return;
			}

			if(!confirm("Are you sure to "+act+" data of facility "+$("#Facility option:selected").text()+"?")) return;

			var param = actions.loadParams(true);
            var dateFrom = $('#date_begin').val();
            var dateTo = $('#date_end').val();
			var param2 = {
					'FACILITY_ID' : $('#Facility').val(),
					'DATE_FROM' : dateFrom,
					'DATE_TO' : dateTo,
					'TABLE_NAMES' : table,
					'GROUP_ID' : $('#DataTableGroup').val(),
                    'OBJECTTYPE' : ($('#IntObjectType').val() == undefined) ? 0 : $('#IntObjectType').val(),
                    'PREFIX' : prefix
			}
            jQuery.extend(param, param2);

			sendAjax('/am/validateData', param, function(data){
				alert(data.message);
				//if(typeof data.result == "object" && data.result !=null) _validatedata.listData(data.result);
			});
		},
}
</script>
	<div id="background_obj" style="width:281px;float:left;background-color: #dcdcdc;">
	<table style="table-layout: fixed; width: 100%;">
		<thead id="table5">
			<tr>
				<td><strong style="margin-left: 5px;">Data table</strong></td>
				<td style="float: right; margin: 7px 7px 3px 0px;"><label class="checkbox-label"><input class="check_all_obj" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;font-size: 80%;">Select all</small></label></td>
			</tr>
		</thead>
	</table>

	{{--<div id="listValidate" style="width:270px">--}}
		{{--<table style="table-layout: fixed;">--}}
			{{--<tbody id="bodyList">--}}
			{{--</tbody>--}}
		{{--</table>--}}
	{{--</div>--}}
	<div class="filter ObjectName" id="container_ObjectName" style="max-width: 300px; width: 270px;">
		<select name="ObjectDataSource" multiple id="ObjectDataSource"></select>
	</div>
</div>
<div id="CheckStatus" style="float: right;padding-top: 23px;">
	{{--check status--}}
</div>
{{--<div style="width:400px;float:left;margin:10px 20px">--}}
{{--{{ Helper::selectDate($filterbeginDate)}}--}}
{{--{{ Helper::selectDate($filterEndDate)}}--}}
{{--<br>--}}
{{--<input type="button" value="Validate data" onclick="_validatedata.validateAll('V')" style="width:230px;margin-top:10px">--}}
{{--<input type="button" value="Set back to Provisional" onclick="_validatedata.validateAll('P')" style="width:230px;margin-top:5px">--}}
{{--<!-- <input type="button" value="Set back to Approved" onclick="_validatedata.validateAll()" style="width:230px;margin-top:5px"> -->--}}
{{--</div>--}}

@stop
