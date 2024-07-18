<?php
$currentSubmenu = '/allocrun';
?>
@extends('core.bsmain',['subMenus'   => null])
@section('action_extra')
	<div style="float: left;">
		<input type="button" value="Run all allocation jobs" onclick="_runallocation.runAllAllocJob();" style="width:150px;margin-top: 21px;">
	</div>
@stop

@section('content')
	<link rel="stylesheet" href="/common/css/bootstrap.min.css" />
	<script type="text/javascript" src="/common/js/bootstrap.min.js"></script>
	<script type="text/javascript">
        $(function(){
            $("#Network").prepend("<option value=0>(Select a network)</option>");
            $("#Network").val(0);
            $("#Network").attr("onclick","_runallocation.getListAllocJob();");
            $(".date_filter").insertAfter("#filterFrequence").insertAfter("#product_filter1");
            $("#filterFrequence").css("width","280px");
            $("#Network").css("width","269px");
            $(".product_filter, .date_filter").css("height","60px");
            $("#date_begin, #date_end").css({"height":"30px","width":"100%"});
            var ebtoken = $('meta[name="_token"]').attr('content');
            $.ajaxSetup({
                headers: {
                    'X-XSRF-Token': ebtoken
                }
            })
            $( "#date_begin, #date_end" ).datepicker({
                changeMonth:true,
                changeYear:true,
                dateFormat:jsFormat,
            }).datepicker("setDate", -1 );
            $('#Network').change();
        });

        var _runallocation = {
            runningAllocID : 0,
            next_job: {},
            getListAllocJob : function(){
                var network = $('#Network').val();
                if(network == 0) return;
                param = {
                    'NETWORK_ID' : network
                };
                sendAjaxNotMessage('/getJobsRunAlloc', param, function(data){
                    _runallocation.listAllocJob(data);
                });
            },

            listAllocJob : function (data){
                var bgcolor="";
                var str = "";
                $('#bodyJobsList').html(str);
                for(var i = 0; i < data.length; i++){
                    if(i%2==0){
                        bgcolor="#eeeeee";
                    }else{
                        bgcolor="#f8f8f8";
                    }
                    str += '<tr bgcolor="'+ bgcolor +'" job_id="'+ data[i]['ID'] +'" id="Qrowjob_'+ data[i]['ID'] +'">';
                    str += '	<td><span style="color:black;font-weight: normal;" id="QjobName_'+ data[i]['ID'] +'">'+ data[i]['NAME'] +'</span></td>';
                    str += '	<td><input type="text" id="from_date'+ data[i]['ID'] +'" style="width:100px"></td>';
                    str += '	<td><input type="text" id="to_date'+ data[i]['ID'] +'" style="width:100px"></td>';
                    str += '	<td align="center"><input type="button" value="Run allocation" style="width:120px" onclick="_runallocation.runAllocJob('+ data[i]['ID'] +')"></td>';
                    str += '</tr>';
                }
                $('#bodyJobsList').html(str);
                $("#allocLog").html("");
                $( "#bodyJobsList input[type='text']" ).datepicker({
                    changeMonth:true,
                    changeYear:true,
                    dateFormat:jsFormat
                }).datepicker("setDate", -1 );
            },
            checkAllocDate : function(d1,d2)
            {
                var d = new Date("January 01, 2016 00:00:00");
                if(d1<d || d2<d){
                    alert("Can not run allocation for the date earlier than 01/01/2016.");
                    return false;
                }
                return true;
            },
            runAllocJob : function(job_id, is_run_all)
            {
                if(_runallocation.runningAllocID==job_id)
                {
                    _alert("Allocation job is in progress. Please wait until it was completed.");
                    return;
                }
                var d1 = $("#from_date"+job_id).datepicker('getDate');
                var d2 = $("#to_date"+job_id).datepicker('getDate');
                if(!_runallocation.checkAllocDate(d1,d2)){
                    return;
                }
                _runallocation.runningAllocID = job_id;
                var jobname=$("#QjobName_"+job_id).html();
                if(is_run_all)
                    $("#allocLog").append("Allocation job '"+jobname+"' has started. Please wait...<br>");
                else{
                    $("#allocLog").html("Allocation job '"+jobname+"' has started. Please wait...<br>");
                    _runallocation.next_job={};
                }

                param = {
                    'act' : 'run',
                    'job_id' : job_id,
                    'from_date' : dateToString($("#from_date"+job_id).datepicker('getDate')),
                    'to_date' : dateToString($("#to_date"+job_id).datepicker('getDate'))
                };

                sendAjax('/run_runner', param, function(data){
                    if(is_run_all)
                        $("#allocLog").append(data);
                    else
                        $("#allocLog").html(data);
                    _runallocation.runningAllocID = 0;
                    if(_runallocation.next_job[job_id]>0)
                        _runallocation.runAllocJob(_runallocation.next_job[job_id], is_run_all);
                    else{
                        alert("Allocation job completed");
                        _runallocation.next_job={};
                    }
                });
            },

            runAllAllocJob : function()
            {
                var count=$("#bodyJobsList tr").length;
                if(count>0)
                {
                    var d1 = $("#date_begin").datepicker('getDate');
                    var d2 = $("#date_end").datepicker('getDate');
                    if(!_runallocation.checkAllocDate(d1,d2)){
                        return;
                    }
                    $("#bodyJobsList input[id^='from']").val($("#date_begin").val());
                    $("#bodyJobsList input[id^='to']").val($("#date_end").val());
                    if(!confirm("Do you want to run all "+count+" allocation job"+(count>1?"s":"")+" in the list?")) return;
                    $("#allocLog").html("");
                    _runallocation.next_job={};
                    var last_job_id=0;
                    var first_job_id=0;
                    $("#bodyJobsList tr").each(function(){
                        var job_id=$(this).attr("job_id");
                        if(last_job_id>0)
                            _runallocation.next_job[last_job_id]=job_id;
                        else
                            first_job_id=job_id;
                        last_job_id = job_id;
                    });
                    if(first_job_id>0)
                        _runallocation.runAllocJob(first_job_id, true);
                }
                else
                    alert("No allocation job to run");
            }
        }
	</script>

	<body style="margin: 0px">
	<div id="container" style="width:100%">
		<div id="boxJobsList"
			 style="background: #f8f8f8; padding: 0px; border-bottom: 0px solid #ccc">
			<table border="0" cellpadding="4" cellspacing="0" id="table5"
				   width="100%">
				<thead>
				<tr>
					<td bgcolor="#609CB9"><b><font color="#FFFFFF">Job name</font></b></td>
					<td width="100" bgcolor="#609CB9"><b><font color="#FFFFFF">From
								date</font></b></td>
					<td width="100" bgcolor="#609CB9"><b><font color="#FFFFFF">To date</font></b></td>
					<td width="120" bgcolor="#609CB9"></td>
				</tr>
				</thead>
				<tbody id="bodyJobsList">
				</tbody>
			</table>
			<div style="box-sizing: border-box; padding: 10px; position: relative; width: 100%; height: 400px; border: 1px solid #bbbbbb; background: #ffffff; overflow: auto" id="boxRunAlloc">
				<b><span style="font-size: 13pt">Allocation log:</span></b><br>
				<div id="allocLog">....</div>
			</div>
		</div>
	</body>
@stop
