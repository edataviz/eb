<?php
$currentSubmenu = '/workreport';
$host = ENV('DB_HOST');
?>
@extends('core.bsdiagram')
@section('title')
@stop 

@section('content')
<link rel="stylesheet" href="/common/css/admin.css">
<link rel="stylesheet" href="/common/monthpicker/MonthPicker.css">
<link rel="stylesheet" href="/common/css/jquery-ui-timepicker-addon.css"/>
<style>
#wraper {width: 531px}
#box_formats label{
	margin: 2px 5px;
}
.caption {
	display: inline-block;
	min-width: 70px;
	margin: 5px 10px 5px 0px;
}
</style>
<script src="/common/js/moment.js"></script>
<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
<script src="/common/monthpicker/MonthPicker.js"></script>
<script type="text/javascript">
function getDefaultDate(month){
	var d = new Date();
	if(month=='y' || month=='year')
		return d.getFullYear();
	var m=(1+d.getMonth());
	if(m<10)m="0"+m;
	if(month) 
		return m+"/"+d.getFullYear();
	var day=d.getDate();
	if(day<10)day="0"+day;
	return d.getFullYear()+"-"+m+"-"+day;//+' '+d.getHours()+':'+d.getMinutes();
}
function getStandardDateString(date, isMonthFirstDay, hasTime){
	var m=(1+date.getMonth());
	if(m<10)m="0"+m;
	if(isMonthFirstDay === true)
		return date.getFullYear()+"-"+m+"-01";
	var day=date.getDate();
	if(day<10)day="0"+day;
	return date.getFullYear()+"-"+m+"-"+day+(hasTime?" "+date.getHours()+':'+date.getMinutes():"");
}
function dropdownParamChanged(o){
	var param = $(o).attr('id').substr(6);
	var value = $(o).val();
	/*
	$("select[parent_param='"+param+"'] > option").each(function(){
		if($(this).attr('parent_value')==value){
			$(this).show();
		}
		else
			$(this).hide();
	});
	$("select[parent_param='"+param+"']").each(function(){
		var x=$(this).find('[parent_value="'+value+'"]')[0];
		$(this).val($(x).attr('value'));
	});
	*/
	$("select[parent_param='"+param+"']").each(function(){
		var id = $(this).attr('id').substr(6);
		var list_html = '';
		for(var i=0;i<_report.childOptions[id].length;i++){
			var list_item = _report.childOptions[id][i];
			if(list_item.PARENT_ID==value || list_item.PARENT_ID=='ALL')
				list_html += '<option value="'+list_item.ID+'">'+list_item.NAME+'</option>';
		}
		$(this).html(list_html);
	});
}
var _report = {
	loadReportUrl: "/report/loadreports",
	loadParamUrl: "/report/loadparams",

	host:'',
	childOptions:{},
	loadReports : function(){
		if(!($("#cboReportGroups").val() > 0)){
			alert("Please select group");
			return;
		}
		showWaiting();
		$("#cboReports").html("");
		$.ajax({
			url: this.loadReportUrl,
			type: "post",
			data: {group_id:$("#cboReportGroups").val()},
			success:function(data){
				hideWaiting();
				var html="";
				data.forEach(function(item, index){
					html += '<option value="'+item.ID+'" data-formats="'+(item.FORMATS?item.FORMATS:'')+'" data-file="'+item.FILE+'">'+item.NAME+'</option>';
				});
				$("#cboReports").html(html);
				$('#cboReports').change();
			},
			error: function(data) {
				console.log ( "load reports error");
				hideWaiting();
			}
		});
	},

	loadParams : function(){
		$("#box_conditions").html("");
		if(!($("#cboReports").val() > 0)){
			//alert("Please select report");
			return;
		}
		showWaiting();
		$("#showReport").hide();
		$.ajax({
			url: this.loadParamUrl,
			type: "post",
			data: {report_id:$("#cboReports").val()},
			success:function(data){
				hideWaiting();
				var html="";
				var jsTimeFormat = configuration['time']['TIME_FORMAT'].replace('A','TT').replace('a','tt');
				data.forEach(function(item, index){
					html += '<span id="condition_'+item.ID+'" class="caption">'+item.NAME+'</span>\n';
					if(item.VALUE_TYPE==1){
						if(item.REF_LIST){
							var list_html = '';
							_report.childOptions[item.CODE] = [];
							item.REF_LIST.forEach(function(list_item, list_index){
								var parent_value='';
								if(item.PARENT_FIELD && item.PARENT_PARAM){
									_report.childOptions[item.CODE].push(list_item);
									//parent_value='parent_value="'+list_item[item.PARENT_FIELD]+'"';
								}
								else
									list_html += '<option '+parent_value+' value="'+list_item.ID+'">'+list_item.NAME+'</option>';
							});
							html += '<select class="param" data-type="1" '+(item.PARENT_PARAM?'parent_param="'+item.PARENT_PARAM+'" ':'')+'name="param_'+item.CODE+'" id="param_'+item.CODE+'" onchange="dropdownParamChanged(this)">'+list_html+'</select>';
						}
						else
							html += '<input class="param" type="number" data-type="1" name="param_'+item.CODE+'">';
					}
					else if(item.VALUE_TYPE==2){
						html += '<input class="param" type="text" data-type="2" name="param_'+item.CODE+'">';
					}
					else if(item.VALUE_TYPE==3){
						html += '<input class="param datepicker" type="text" data-type="3" name="param_'+item.CODE+'" value="'+getDefaultDate()+'">';
					}
					else if(item.VALUE_TYPE==4){
						html += '<input class="param datepicker daterange_from" code="'+item.CODE+'" type="text" data-type="3" name="param_'+item.CODE+'_from" value="'+getDefaultDate()+'"> To <input class="param datepicker daterange_to" code="'+item.CODE+'" type="text" data-type="3" name="param_'+item.CODE+'_to" value="'+getDefaultDate()+'">';
					}
					else if(item.VALUE_TYPE==5){
						html += '<input class="param monthpicker" type="text" data-type="5" name="param_'+item.CODE+'" value="'+getDefaultDate(true)+'">';
					}
					else if(item.VALUE_TYPE==6){
						var cy = Number(getDefaultDate('y'));
						var years= '';
						for(var i=cy-10;i<=cy;i++){
							years += '<option value='+i+(i==cy?' selected':'')+'>'+i+'</option>';
						}
						html += '<select class="param yearpicker" data-type="1" name="param_'+item.CODE+'" id="param_'+item.CODE+'" value="'+cy+'">'+years+'</select>';
					}
					else if(item.VALUE_TYPE==7){
						html += '<input class="param datetimepicker" type="text" data-type="3" name="param_'+item.CODE+'" value="'+moment().format(configuration['time']['DATETIME_FORMAT'])+'">';
					}
					else if(item.VALUE_TYPE==8){
						html += '<input class="param datetimepicker datetimerange_from" code="'+item.CODE+'" type="text" data-type="3" name="param_'+item.CODE+'_from" value="'+moment().format(configuration['time']['DATETIME_FORMAT'])+'"> To <input class="param datetimepicker datetimerange_to" code="'+item.CODE+'" type="text" data-type="3" name="param_'+item.CODE+'_to" value="'+moment().format(configuration['time']['DATETIME_FORMAT'])+'">';
					}
					html += '<br>';
				});
				html!=''?html+='<hr>':null;
				$("#box_conditions").html(html);
				$("#box_conditions .yearpicker").change(function(){
					var y = Number($(this).val());
					var cy = Number(getDefaultDate('y'));
					var years= '';
					for(var i=y-10;i<=cy;i++){
						years += '<option value='+i+(i==y?' selected':'')+'>'+i+'</option>';
					}
					$(this).html(years);
				});
				$("#box_conditions .datepicker").datepicker({
					changeMonth	:	true,
					changeYear	:	true,
					dateFormat	:	jsFormat,
					onSelect: function() {
						var date = $(this).datepicker('getDate');
						if($(this).hasClass("daterange_from")){
							var code = $(this).attr("code");
							$(".param[name='param_"+code+"_to']").datepicker("change",{ minDate: date});
						}
						else if($(this).hasClass("daterange_to")){
							var code = $(this).attr("code");
							$(".param[name='param_"+code+"_from']").datepicker("change",{ maxDate: date});
						}
					}
				});
				$("#box_conditions .datetimepicker").datetimepicker({
					changeMonth: true,
					changeYear:	true,
					timeInput: true,
					dateFormat:	jsFormat,
					timeFormat:	'HH:mm',
					onSelect: function() {
						var date = $(this).datetimepicker('getDate');
						if($(this).hasClass("datetimerange_from")){
							var code = $(this).attr("code");
							$(".param[name='param_"+code+"_to']").datetimepicker("change",{ minDate: date});
						}
						else if($(this).hasClass("datetimerange_to")){
							var code = $(this).attr("code");
							$(".param[name='param_"+code+"_from']").datetimepicker("change",{ maxDate: date});
						}
					}
				});
				var d = new Date();
				d.setMonth(d.getMonth() - 1);
				var m=(1+d.getMonth());
				if(m<10)m="0"+m;
				var SelectedMonth = m+"/"+d.getFullYear();
				$("#box_conditions .monthpicker").MonthPicker({ Button: false, SelectedMonth: SelectedMonth});
				//set default date
				$(".datepicker").each(function(){
					$(this).datepicker("setDate", new Date());
				});
				$("select.param").each(function(){
					dropdownParamChanged($(this));
				});
				var formats = $('#cboReports option:selected').data('formats').toLowerCase();
				formats==''||formats=='undefined'?formats='pdf,html':null;
				var option_first = "";
				$('#box_formats label').each(function(){
					if(formats.indexOf($(this).text().toLowerCase()) !== -1){
						$(this).show();
						if(option_first==""){
							option_first = $(this).text();
						}
					}
					else $(this).hide();
				});
				$('#format_'+option_first).prop('checked', true).change();
				$("#showReport").show();
			},
			error: function(data) {
				console.log ( "load params error");
				console.log (data);
				hideWaiting();
			}
		});
	},

	resetCondition : function(){
		var str = "";
		$('#conScboGroup').css('display','none');
		$('#conFacility').css('display','none');
		$('#condition1').html(str);
		$('#condition2').html(str);
	},

	defaultDate : function(){
		var d = new Date();
		var m=(1+d.getMonth());
		if(m<10)m="0"+m;
		
		$("#date_from, #SstartDate,#Date").val(""+d.getFullYear()+"/"+m+"/01");
		$("#date_to, #SendDate,#Date").val(""+d.getFullYear()+"/"+m+"/"+d.getDate());
	},

	showReport : function(){
		var pexport = $('input[name=reportType]:checked').val();
		var file = $('#cboReports option:selected').data('file');
		var params = "";
		var error = false;
		$(".param[name^='param_']").each(function(){
			var data_type = $(this).data("type");
			if($(this).hasClass("datepicker")){
				var date = $(this).datepicker('getDate');
				if($(this).hasClass("daterange_from")){
					var code = $(this).attr("code");
					var date_to = $(".param[name='param_"+code+"_to']").datepicker("getDate");
					if(date > date_to){
						alert("Date range is not valid");
						error = true;
						return;
					}
				}
				params += '&'+$(this).attr("name").substr(6)+'__T_3='+getStandardDateString(date, (data_type == "5"));
			}
			else if($(this).hasClass("datetimepicker")){
				var date = $(this).datetimepicker('getDate');
				if($(this).hasClass("datetimerange_from")){
					var code = $(this).attr("code");
					var date_to = $(".param[name='param_"+code+"_to']").datetimepicker("getDate");
					if(date > date_to){
						alert("Datetime range is not valid");
						error = true;
						return;
					}
				}
				params += '&'+$(this).attr("name").substr(6)+'__T_3='+getStandardDateString(date, false, true);
			}
			else if($(this).hasClass("monthpicker")){
				var date = $(this).MonthPicker('GetSelectedYear')+"-"+$(this).MonthPicker('GetSelectedMonth')+"-01";
				params += '&'+$(this).attr("name").substr(6)+'__T_3='+date;
			}
			else{
				params += '&'+$(this).attr("name").substr(6)+'__T_'+data_type+'='+encodeURIComponent($(this).val());
				$(this).is("select") && (params += '&'+$(this).attr("name").substr(6)+'_text__T_2'+'='+encodeURIComponent($(this).find('option:selected').text()));
			}
		});
		if(error)
			return;
		//var url = _report.host+'/report/viewreport.php?export='+pexport+'&file='+file+params;
		//var url = _report.host+'/genreport/export='+pexport+'&file='+file+params;
		var url = _report.host+(pexport=='XML'||pexport=='CSV'?'/genreport/':'/report/viewreport.php?')+'export='+pexport+'&file='+file+params;
		window.open(url, '_blank');
	},
}


$(function(){
	$('#pageheader').css('display', 'none');
	$('#cboReports').change();
	
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	$("input[name='reportType']").change(function(){
		var format = $(this).attr('value').toLowerCase();
		$("#showReport").val(format=='csv'||format=='xml'?"Export report data":"Generate report");
	});

	//_report.host = '<?php echo $host;?>';
});

</script>

<body style="margin: 0; overflow-x: hidden">
	<div id="wraper">
		<div style="padding: 10px; background: #eee; border: 1px solid #bbb;">
		<span class="caption"><b>Group</b></span>
		<select size="1" style="font-size: 11pt; margin:5px 0px; width: 400px" name="cboReportGroups"
		id="cboReportGroups" onchange="_report.loadReports()">
				@foreach($rpt_group as $t)
					<option value="{!!$t->ID!!}">{!!$t->NAME!!}</option> 
				@endforeach
		</select>
		<span class="caption"><b>Report</b></span>
		<select size="1" style="font-size: 11pt; margin:5px 0px; width: 400px" name="cboReports" id="cboReports" onchange="_report.loadParams()">
				@foreach($reports as $t)
					<option value="{!!$t->ID!!}" data-formats="{!!$t->FORMATS!!}" data-file="{!!$t->FILE!!}">{!!$t->NAME!!}</option> 
				@endforeach
		</select>
		<hr>
		<span id="box_conditions">
		</span>
		<span class="caption">Format</span>
		<span id="box_formats">
		<label><input type="radio" name="reportType" id="format_PDF" value="PDF" checked>PDF</label>
		<label><input type="radio" name="reportType" id="format_Excel" value="Excel">Excel</label>
		<label><input type="radio" name="reportType" id="format_HTML" value="HTML">HTML</label>
		<label><input type="radio" name="reportType" id="format_CSV" value="CSV">CSV</label>
		<label><input type="radio" name="reportType" id="format_XML" value="XML">XML</label>
		</span>
		<br>
		<input type="button" id="showReport" style="width:160px;margin:20px 82px" value="Generate report" onClick="_report.showReport();">
		</div>
	</div>

</body>
@stop

