<?php
$currentSubmenu = '/dataview';
$role = $_role;
?>
@extends('core.bsdiagram')
@section('title')
@stop 
@section('group')
@stop
@section('content')

<link href="/common/css/jquery.dataTables.css" rel="stylesheet">
<link href="/common/css/jquery.multiselect.css" rel="stylesheet">
<link rel="stylesheet" href="/common/css/jquery-ui.css" />
<link rel="stylesheet" href="/common/css/style.css" />

<script src="/common/js/jquery-2.1.3.js"></script>
<script src="/common/js/numericInput.min.js"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/jquery.dataTables.js"></script>
<script src="/common/js/dataTables.fixedColumns.js"></script>
<script src="/common/js/jquery.multiselect.js"></script>
<script type="text/javascript">
$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	$("#object").multiselect();
	$("#data_container").width($(window).width()-270);
	//$("#data_container").height($(window).height()-200);
	var h=($(window).height()-255)/2;
	$("#listTables").height(h);
	$("#listSQLs").height(h);

	$("#import_sql").on("click", function(){
		_dataview.show_edit_sql("","","Add");
	})

	$("#delete_sql").click(function(){
		_dataview.delete_sql();
	})

	$("#export").on("click", function(){
		var sql="";
		if(_dataview.lastSQLCondition!="")
			sql = _dataview.lastSQLCondition;
		else
			sql=$("#sql_export").text();
		if(typeof(sql)==="undefined" || sql=="") return;
		window.open("/downloadExcel/"+window.btoa(sql).split("").reverse().join(""));
	})

	$("#rows_in_page").on("change", function(){
		if(run_sql==true)
		{
			var sql="";
			if(_dataview.lastSQLCondition!="")
				sql=_dataview.lastSQLCondition;
			else
				sql=$("#sql_statement").val(); 
			var rows_in_page=$("#rows_in_page").val();
			_dataview.run_sql_import(sql, rows_in_page, 1);
		}
		else
		{								 
			view_name=$("#listTables").val();
			if(view_name == null) return;
			
			object_id=$("#object").val();
			from_date=$("#from_date").val();
			to_date=$("#to_date").val();
			rows_in_page=$("#rows_in_page").val();
			
			_dataview.getdata(view_name, 1, object_id, from_date, to_date, rows_in_page);
		}
	})

	$("#listTables").on("click", function(){
		_dataview.exec_sql('listTables');
		return;
		if(!confirm("Do you want to show this view or table?")) return;
		$("#from_date, #to_date").val('');
		
		_dataview.run_sql=false;
		_dataview.getdata($(this).val(), 1, -1, "", "", $("#rows_in_page").val());
	})
});

var _dataview = {
		edit_sql_dialog : null,
		lastSQLCondition : "",
		last_data_source_name : "",
		lastSQLID : -1,
		run_sql:false,
		show_edit_sql : function(sql, sqlid, action)
		{
			if(action===undefined) action = null;
			if(action == "Edit"){
                if(sql) {
                    $("#sql_statement").val(sql);
                }

                if(sqlid) {
                    $("#sqlid").val(sqlid);
                }
			}else if(action == "Add"){
                $("#sql_statement").val('');
                $("#sqlid").val(0);
			}

			$( "#dialog" ).dialog({
					height: 230,
					width: 376,
					modal: true,
					title: action +" SQL",
					buttons: {
						"Run": function(){
							var sql=$("#sql_statement").val();
							sql=sql.trim();
							if(!_dataview.checkSQL(sql))
							{
								return;
							}
							
							if(sql=="") {$("#dialog").dialog("close"); return;}
							
							var rows_in_page=$("#rows_in_page").val();
							
							_dataview.run_sql_import(sql, rows_in_page, 1);
							$("#object, #from_date, #to_date").attr("disabled", true);
							$("#from_date, #to_date").html('');
							$("#dialog").dialog("close");
							
						},
						<?php if($role) 
								echo '"Save":function(){
									var sql=$("#sql_statement").val();
									var sqlid = $("#sqlid").val();
									sql=sql.trim();
									if(!_dataview.checkSQL(sql))
									{
										return;
									}
									var name="";
									if(sqlid>0){
										name="%%";
									}
									else{
										name=prompt("Save with name");
									}
									if(name.trim()!="" && name!=null)
									{
										param = {
											"name" : name,
											"sql" : sql,
											"id" : sqlid
										};
										
										sendAjax("/savesql", param, function(data){
											_dataview.showSqlList(data);
											$("#sqlid").val(0);
											$("#dialog").dialog("close");
										});				
									}
								},'
					
						?>
						"Cancel": function(){
							$("#dialog").dialog("close");
						}
					}
			});
		},

		edit_sql : function()
		{
			var sqlid=$("#listSQLs").val();
			if(!sqlid>0){_alert("Please select a SQL command"); return;}

			param = {
				'id' : sqlid
			};
			
			sendAjax('/getsql', param, function(data){
				_dataview.show_edit_sql(data,sqlid, "Edit");
			});
		},
		checkSQL:function(sql)
		{
			if(sql=="")
			{
				alert("Please type SQL command");
				$("#sql_statement").focus();
				return false;
			}
@if (config('constants.allowSelectOnly',true))
			if(sql.substr(0,6).toUpperCase()!="SELECT")
			{
				alert("Only accept SELECT statement");
				return false;
			}
@endif
			return true;
		},
		run_sql_import : function(sql, rows_in_page, page)
		{
			if(sql.substr(0,5)=="SQLID")
				_dataview.lastSQLCondition=sql;
			else
				_dataview.lastSQLCondition="";
			$("#loading").show();
			$.ajax({
				   url: "/loaddataview",
				   data:{
					   'sql': sql,
					   'rows_in_page': rows_in_page,
					   'page': page
				   },
				   type: "POST",
				   success: function(re){
					   $("#loading").hide();
					   $("#data_source_name").html(_dataview.last_data_source_name);
					   $("#data_container").html(re);

					   $('#example').DataTable( {
					        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
					    } );
					   
					   table=$("#data").dataTable({
							scrollY: $("#data_container").height()-90,
							scrollX: true,
							searching: false,
							paging: false,
							info: false,
							destroy: true
						});
					   new $.fn.dataTable.FixedColumns(table,{leftColumns: 1});
					   run_sql=true;
					   $("#import_sql").removeClass("import").addClass("import_active");
						$("#paging").find("span").on("click", function(){
							_dataview.run_sql_import(sql, rows_in_page, $(this).attr("page"));
						})
						$("#go").click(function()
						{
							var pi=Number($("#txtpage").val());
							var pr=Number($("#paging span:last").attr("page"));
							if(pi<=pr && pi>0)
								{_dataview.run_sql_import(sql, rows_in_page, pi)}
							else
								{alert("Invalid page");}
								
						})
						$("#txtpage").numericInput();
						$(".current_page").unbind();
						
						$("#from_date, #to_date").prop("disabled", !$("#occurdate_exist").html());
				   },
				   error: function(xhr, status, errorThrown){
					   console.log(xhr);
					   alert('Ajax call failed: '+status+' Error: '+errorThrown);
					   run_sql=false;
					   $("#loading").hide();
				   },
				   complete: function(){
						$("#loading").hide();
				   }
				   });
		},
		showSqlList : function (data){
			var cbo = '';
			$('#listSQLs').html(cbo);
			for(var v = 0; v < data.length; v++){
				cbo += ' 		<option value="' + data[v].ID + '">' + data[v].NAME + '</option>';
			}

			$('#listSQLs').html(cbo);
		},
		delete_sql : function(){
			if($("#listSQLs").val()>0)
			{
				if(!confirm("Do you want to delete this SQL command?"))
					return;

				param = {
					'id' : $("#listSQLs").val()
				};
				
				sendAjax('/deletesql', param, function(data){
					_dataview.showSqlList(data);
				});
			}
		},
		exec_sql : function(o_id)
		{
			_dataview.last_data_source_name=$("#"+o_id+" option:selected").text();
			var sqlid=$("#"+o_id).val();
			if(!sqlid>0){_alert("Please select a data source"); return;}

			param = {
				'id' : sqlid
			};
			
			sendAjax('/checksql', param, function(data){

				if(data=="")
				{
					if(!confirm("Do you want to execute this SQL command?"))
						return;
					var rows_in_page=$("#rows_in_page").val();	
					_dataview.run_sql_import("SQLID:"+sqlid, rows_in_page, 1);
					_dataview.lastSQLID = sqlid;
				}
				else if(data.substr(0,7)=="filter:")
				{
					if(_dataview.lastSQLID==sqlid){
						if ($("#dialog_condition").hasClass('ui-dialog-content')) {
							$("#dialog_condition").dialog("open");
							return;
						}
					}
					$("#dialog_condition").html(data.substr(7));
					$("#dialog_condition .datepicker").datepicker({
					     changeMonth:true,
					     changeYear:true,
					     dateFormat:"mm/dd/yy"
					});
					$( "#dialog_condition" ).dialog({
						height: 250,
						width: 400,
						modal: true,
						title: "Data query condition",
						buttons: {
							"Run": function(){
								var cond="SQLID:"+sqlid;
								$(".condition_field").each(function(){
									var f=$(this).attr("filed_name");
									if($(this).attr("IS_DATE_RANGE")=="1")
									{
										cond+=";{"+f+"}:"+f+" between '"+_dataview.correctDateValue($("#"+f+"_FROM").val())+"' and '"+_dataview.correctDateValue($("#"+f+"_TO").val())+"'";
									}
									else if($(this).attr("FIELD_VALUE_REF_TABLE")!="")
									{
										cond+=";{"+f+"}:"+f+"="+$("#"+f+"_SELECT").val();
									}
								});
								var rows_in_page=$("#rows_in_page").val();
								_dataview.run_sql_import(cond, rows_in_page, 1);
								$("#object, #from_date, #to_date").attr("disabled", true);
								$("#from_date, #to_date").html('');
								$("#dialog_condition").dialog("close");
								_dataview.lastSQLID = sqlid;
							},
							"Cancel": function(){
								$("#dialog_condition").dialog("close");
							}
						}
					})
				}
			});
		},
		correctDateValue : function(s)
		{
			var ss=s.split('/'); //mm/dd/yyyy
			if(ss.length>=3)
				return ss[2]+"-"+ss[0]+"-"+ss[1]; //yyyy-mm-dd
			return "";
		},
		childLoad : function(key,field,obj){
			var table=$("#"+key+"_SELECT").attr("table");
			var cond=field+"="+$(obj).val();
			if(table!="" && $(obj).val()!="")
				postRequest( 
				             "../common/getcodelist.php",
				             {table:table,where:cond,first_blank:true},
				             function(data) {
				             	$("#"+key+"_SELECT").html(data);
				             }
				          );
		},
		getdata : function(view_name, page, object_id, from_date, to_date, rows_in_page)
		{
			_dataview.lastSQLCondition="";
			$("#loading").show();
			$.ajax({
			   url: "/loaddata",
			   data: {
				   view_name: view_name,
				   page: page,
				   object: object_id,
				   from_date: from_date,
				   to_date: to_date,
				   rows_in_page: rows_in_page
			   },
			   type: "POST",
			   success: function(re){
				   $("#loading").hide();
				   $("#data_container").html(re);
				   table=$("#data").dataTable({
						scrollY: $("#data_container").height()-50,
						scrollX: true,
						lengthMenu: [ 50, 75, 100 ],
						searching: false,
						paging: false,
						info: false,
						destroy: true
					});
				   new $.fn.dataTable.FixedColumns(table,{leftColumns: 2});
				   
				   if(run_sql==false) $("#import_sql").removeClass("import_active").addClass("import");

					$("#paging").find("span").on("click", function(){
						_dataview.getdata(view_name, $(this).attr("page"), object_id, from_date, to_date, rows_in_page);
					})
					$("#go").click(function()
					{
						var pi=Number($("#txtpage").val());
						var pr=Number($("#paging span:last").attr("page"));
						if(pi<=pr && pi>0)
							{_dataview.getdata(view_name, pi, object_id, from_date, to_date, rows_in_page);}
						else
							{alert("Invalid page");}
							
					})
					$("#txtpage").numericInput();
					//remove event listener on //curent page
					$(".current_page").unbind();
					
					$("#object").attr("disabled", false);
					$("#object").multiselect("destroy");
					$("#object").html($("#option").html());
					$("#object").multiselect();
					
					$("#from_date, #to_date").prop("disabled", !$("#occurdate_exist").html());
			   },
			   error: function(xhr, status, errorThrown){
				   alert('Ajax call failed: '+status+' ERROR: '+errorThrown);
				   $("#loading").hide();
			   },
			   complete: function(){
				   $("#loading").hide();
			   }
			   });
			}
}
</script>

<style>
.ui-widget{font-size:10pt;}
#data{text-align:center;}
thead{background-color:#f5f5f5;}
#paging{margin-top:10px}
#paging span{cursor:pointer}
#paging input:first-of-type{margin-left:25px}
#paging input{margin-left:4px}
#paging span:not(.current_page):hover{cursor:pointer; text-decoration:underline}
.current_page{color:#F00}
#number_row{float:left; margin-left:0px !important}
#control span{margin-left: 15px}

#loading{
	display:none;
	width:200px;
	height:200px;
	position:fixed;
	left:50%;
	top:50%;
    margin-top: -100px;
    margin-left: -100px;
	background:#000;
	opacity:0.3;
	z-index:99;
}
#loading img{
	margin:70px;
	position:absolute;
}
#from_date, #to_date{width:100px; text-align:right;}
#dialog{font-size:12px}
</style>

<body style="margin:0;">
<div id="loading">
	<img src="/img/loading.gif" width="54" height="55">
</div>

<div id="dialog" style="display:none">
	SQL Statement:<br>
    <textarea cols="3" rows="5" id="sql_statement" style="width:338px"></textarea>
    <input type="hidden" value="0" id="sqlid">
</div>

<div id="dialog_condition" style="display:none">
</div>

<table border="0" cellpadding="10" cellspacing="0" width="100%" id="table2">
	<tr>
		<td width="250" valign="top">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" height="400" id="table3">
			<tr>
                <td height=22><b><font size="2">Views list</font></b></td>
			</tr>
			<tr>
				<td>
				<SELECT style="width:100%;height:250px" SIZE=5 name="listTables" id="listTables">
               		@foreach($viewslist as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
					@endforeach
                </SELECT>
                </td>
			</tr>
			<tr>
                <td height=22><b><font size="2">SQL list</font></b></td>
			</tr>
			<tr>
				<td>
				<SELECT style="width:100%;height:160px" SIZE=5 name="listSQLs" id="listSQLs">
	               	@foreach($sqllist as $re)
						<option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
					@endforeach
                </SELECT>
                </td>
			</tr>
			<tr><td height=30>
                <input type="button" onclick="_dataview.exec_sql('listSQLs')" class="import" value="Execute command" style="width:100%;margin-top:3px">
                <?php if($role) echo '<input type="button" id="import_sql" class="import" value="New" style="width:30%;margin-top:3px;">'; ?>
                <input type="button" onclick="_dataview.edit_sql()" class="import" value="Edit" style="width:30%;margin-top:3px">
                <?php if($role) echo '<input type="button" id="delete_sql" class="import" value="Delete" style="width:30%;margin-top:3px">'; ?>
				</td>
			</tr>
		</table>
		</td>
		<td valign="top">
        <table style = "height:500px;">
        <tr style="height: 40px;vertical-align: top"><td style="text-align:left">
        <div id="control">
        	<span id="number_row">
        	Number of record: 
        	<select id="rows_in_page">
            	<option value="25">25</option>
                <option value="50" selected>50</option>
                <option value="100">100</option>
                <option value="500">500</option>
            </select>
            </span>
            <!--
            <span>
            Object: 
        	<select id="object" multiple>
            </select>
            </span>
            <span>From date: <input type="text" value="" id="from_date"></span>
            <span>To date: <input type="text" value="" id="to_date"></span>
            <span><input type="button" value="Search" id="search"></span>
			-->
            <span id="data_source_name" style="font-weight:bold"></span>
            <span><input type="button" value="Export" id="export"></span>
        </div>
        </td></tr>
        <tr><td>
        	<div style="width:1060; height:600px; overflow: auto" id="data_container">
            </div>
         </td></tr>
         </table>
		</td>
	</tr>
</table>

<input type="hidden" value="run ok" onClick="abc()">
</body>
@stop