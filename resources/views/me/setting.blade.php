<?php
if (!isset($subMenus)) $subMenus = [];
if (!isset($active)) $active =1;
if (!isset($currentSubmenu)) $currentSubmenu ='';
$configuration				=	$user->getConfiguration();
$df 						= 	new \App\Models\DateTimeFormat;
$dateformatSource			=	$df->getFormat('DATE_FORMAT');
$timeformatSource			=	$df->getFormat('TIME_FORMAT');
$decimalMarkFormatSource	=	$df->getFormat('DECIMAL_MARK');

$currentSubmenu 			='/me/setting';
$lang						= session()->get('locale', "en");

$userName 					= $user->username;

$dashboards					= array("name" => "Default Dashboard",
								"id" => "Dashboard",
								"modelName"	=> "NetWork",
								'default' =>['ID'=>0,'NAME'=>'None'],
								"getMethod" => "getDataWithNetworkType",
								"filterData" => 2
							);
?>
@extends('core.bstemplate',['subMenus' => array('pairs' => $subMenus, 'currentSubMenu' => $currentSubmenu)])

@section('main')
<div class="rootMain {{$currentSubmenu}}">
	<div style="margin-left:10px;">
		<table style="float:left">
			<tr>
				<td colspan="2"><p class="function_title"><?php echo \Helper::translateText($lang,"USER INFORMATION"); ?></p>	</td>
			</tr>
			<tr>
				<td><font color="gray"><?php echo \Helper::translateText($lang,"Full Name"); ?></font></td>
				<td><b>{{$user->FIRST_NAME}} {{$user->MIDDLE_NAME}} {{$user->LAST_NAME}}</b></td>
			</tr>
			<tr>
				<td><font color="gray"><?php echo \Helper::translateText($lang,"Title"); ?></font></td>
				<td><b>{{$user->NAME}}</b></td>
			</tr>
			<tr>
				<td><font color="gray"><?php echo \Helper::translateText($lang,"Email"); ?></font></td>
				<td><b>{{$user->EMAIL}}</b></td>
			</tr>
			<tr>
				<td colspan="2"><p class="function_title"><?php echo \Helper::translateText($lang,"Change password"); ?></p></td>
			</tr>
			<tr>
				<td><?php echo \Helper::translateText($lang,"Old password"); ?></td>
				<td><input type="password" name="txt_old_password" id="txt_old_password" style="width:200px"></td>
			</tr>
			<tr>
			<td><?php echo \Helper::translateText($lang,"New password"); ?></td>
			<td><input type="password" name="txt_new_password" id="txt_new_password" style="width:200px"></td>
			</tr>
			<tr>
			<td><?php echo \Helper::translateText($lang,"Confirm password"); ?></td>
			<td><input type="password" name="txt_confirm_password" id="txt_confirm_password" style="width:200px"></td>
			</tr>
			<tr>
			<td></td>
			<td><input type="button" style="width:120px;margin-top:5px" value="<?php echo \Helper::translateText($lang,"Apply"); ?>" onclick="submit()"></td>
			</tr>
			<tr>
				<td colspan="2"><p class="function_title"><?php echo \Helper::translateText($lang,"Change Date/Time Format"); ?></p></td>
			</tr>			
			<tr>
				<td><?php echo \Helper::translateText($lang,"Date format"); ?></td>
				<td><a href="#" id="dateformat">{{$configuration["sample"]["DATE_FORMAT"]}}</a></td>
			</tr>
			<tr>
				<td width="120"><?php echo \Helper::translateText($lang,"Time format"); ?></td>
				<td><a href="#" id="timeformat">{{$configuration["sample"]["TIME_FORMAT"]}}</a></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="button" style="width:120px;margin-top:5px" value="<?php echo \Helper::translateText($lang,"Apply"); ?>" onclick="submitDateTimeFormat()"></td>
			</tr>
			
			<tr>
				<td colspan="2"><p class="function_title"><?php echo \Helper::translateText($lang,"Change Decimal Mark"); ?></p></td>
			</tr>
			
			<tr>
				<td ><?php echo \Helper::translateText($lang,"Decimal mark"); ?></td>
				<td><a href="#" id="decimalMark">{{$configuration["sample"]["DECIMAL_MARK"]}}</a></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="button" style="width:120px;margin-top:5px" value="<?php echo \Helper::translateText($lang,"Apply"); ?>" onclick="submitDecimalMarkConfiguration()"></td>
			</tr>
		</table>
		<div style="float:left;margin-left:30px">
			<table style="float:left">
				<tr>
					<p class="function_title"><?php echo \Helper::translateText($lang,"Change Language"); ?></p>
				</tr>
				<tr>
					<div class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#">
							<img width="32" height="32" alt="{{ session('locale') }}"  src="{!! asset('img/' . session('locale') . '-flag.png') !!}" />
							&nbsp; {{ Config::get('app.languageNames')[session('locale')] }}
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
						@foreach ( config('app.languages') as $user)
							@if($user !== config('app.locale'))
								<li><a href="{!! url('language') !!}/{{ $user }}">
									<img width="32" height="32" alt="{{ $user }}" src="{!! asset('img/' . $user . '-flag.png') !!}">
									&nbsp; {{ Config::get('app.languageNames')[$user] }}
								</a></li>
							@endif
						@endforeach
						</ul>
					</div>
				</tr>
				<tr>
					</br>
				</tr>
				<tr>
					{{\Helper::filter($dashboards)}}
				</tr>
			</table>
		</div>
	</div>
</div>
	
<script>
function submit(){
	if($("#txt_old_password").val()==""){
		alert("Please input old password");
		$("#txt_old_password").focus();
		return;
	}
	if($("#txt_new_password").val()==""){
		alert("Please input new password");
		$("#txt_new_password").focus();
		return;
	}
	if($("#txt_confirm_password").val()!=$("#txt_new_password").val()){
		alert("Confirm password does not match");
		$("#txt_confirm_password").focus();
		return;
	}
	sendAjax("/me/changepass",
			 {username:'{{$userName}}',password:$("#txt_old_password").val(),newPassword:$("#txt_new_password").val()},
			 function(data) {alert(data); }
			 );
}

$(function() {
	boxEditUserInfo_html = $('#boxEditUserInfo').html();
	$('#boxEditUser').hide();
	$("#pageheader").load("../home/header.php?menu=user");
	var d = new Date();
	$("#txtExpireDate").val("12/31/"+d.getFullYear());
	$( "#txtExpireDate" ).datepicker({
	    changeMonth:true,
	     changeYear:true,
	     dateformat:"mm/dd/yy"
	});

	var dateformatSource =  <?php echo json_encode($dateformatSource); ?>;
	$('#dateformat').editable({
    	type : 'checklist',
//     	name : 'dateformat',
        value: ["{{$configuration['time']['DATE_FORMAT']}}"],
        source:    dateformatSource, 
    });
    
	var timeformatSource =  <?php echo json_encode($timeformatSource); ?>;
	$('#timeformat').editable({
    	type : 'checklist',
        value: ["{{$configuration['time']['TIME_FORMAT']}}"],
        source:    timeformatSource, 
    });

	var decimalMarkFormatSource =  <?php echo json_encode($decimalMarkFormatSource); ?>;
	$('#decimalMark').editable({
    	type : 'checklist',
        value: ["{{$configuration['number']['DECIMAL_MARK']}}"],
        source:    decimalMarkFormatSource, 
    });
});

function submitDateTimeFormat(){
	showWaiting();
	dateformat = $('#dateformat').editable('getValue', true);
	dateformat = dateformat[0];
	if(dateformat==null){
		dateformat = ["{{$configuration['time']['DATE_FORMAT']}}"];
	}

	timeformat = $('#timeformat').editable('getValue', true);
	timeformat = timeformat[0];
	if(timeformat==null){
		timeformat = ["{{$configuration['time']['TIME_FORMAT']}}"];
	}
	
	$.ajax({
		url: '/me/setting/save',
		type: "post",
		data: 	{	dateformat	:	dateformat,
					timeformat	:	timeformat
				},
		success:function(data){
			hideWaiting();
			alert("update success");
		},
		error: function(data) {
			hideWaiting();
		}
	});
}

function submitDecimalMarkConfiguration(){
	showWaiting();
	numberformat = $('#decimalMark').editable('getValue',true);
	numberformat = numberformat[0];
	if(numberformat==null||numberformat==''){
		numberformat = ["{{$configuration['number']['DECIMAL_MARK']}}"];
	}
	
	$.ajax({
		url: '/me/setting/save',
		type: "post",
		data: 	{
					numberformat	:	{ DECIMAL_MARK	: numberformat }
				},
		success:function(data){
			hideWaiting();
			alert("update success");
		},
		error: function(data) {
			hideWaiting();
			console.log ( "submitDecimalMarkConfiguration error " );
		}
	});
}

$( document ).ready(function() {
    $("#title_Dashboard").addClass( "function_title" );
    $("#select_container_Dashboard").css( "max-width",'300px' );

    $('#Dashboard').val({{$configuration['defaultDashboard']}});
    
    $('#Dashboard').change(function(e){
        showWaiting();
		$.ajax({
			url: '/me/setting/save',
			type: "post",
			data: 	{
						defaultDashboard	:	$('#Dashboard').val()
					},
			success: function(results){
				hideWaiting();
		    	$('#Dashboard').attr("disable","");
				console.log("save default dashboard successfully !");
				alert("update success");
			},
			error: function(data) {
				hideWaiting();
		    	$('#Dashboard').attr("disable","");
				console.log(data.responseText);
				alert("Could not save Dashboard");
			}
		});
    	$('#Dashboard').attr("disable","disabled");
	});
});
</script>
@stop


@section('script')
	<link href="/common/css/bootstrap.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-responsive.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-datetimepicker.css" rel="stylesheet"/>
	<link href="/common/css/bootstrap-editable.css" rel="stylesheet"/>
	
	<link href="/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
	<link href="/common/css/fixedHeader.dataTables.min.css" rel="stylesheet"/>
<!-- 	<link href="/common/css/select.dataTables.min.css" rel="stylesheet"/>
 -->	
	<script src="/common/js/jquery-ui-timepicker-addon.js"></script>
	<!-- <script src="/jqueryui-editable/js/jqueryui-editable.js"></script> -->
	<script src="/common/js/tableHeadFixer.js"></script>

	<script src="/common/js/moment.js"></script>
	<script src="/common/js/bootstrap.js"></script>
	<script src="/common/js/bootstrap-datetimepicker.js"></script>
	<script src="/common/js/bootstrap-editable.js"></script>
	<!-- <script src="/common/js/datetime.js"></script> -->
	
<!-- 	<script src="/common/js/dataTables.select.min.js"></script>
 -->	
@stop


