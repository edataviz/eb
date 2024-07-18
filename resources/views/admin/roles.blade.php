<?php
$currentSubmenu ='/am/roles';
$listControls = [ 
		'UserRole' => array (
				'label' => 'User Roles',
				'ID' => 'UserRole' 
		) 
];?>

@extends('core.am', ['listControls' => $listControls])

@section('group')
<div id="controlSearch">
	<div class="role_title">
		<b>Role </b>
	</div>
	<div class="div_roleselect">
		<select id="Roles" onchange="_roles.loadData();">
			@foreach($userRole as $role)
				<option value="{!! $role->ID !!}">{!! $role->NAME !!}</option>
			@endforeach
		</select>
	</div>

	<div class="div_actRole">
		<button type="button" onclick="_roles.addRole();">New Role</button>
		<button type="button" onclick="_roles.editRole();">Rename</button>
		<button type="button" onclick="_roles.deleteRole();">Delete</button>
		<button type="button" onclick="_roles.saveReadOnly();">Save</button>
	</div>
	<div class="box-search">
		<strong style="margin-right: 10px;">Search</strong>
		<span class="text-input-wrapper" style="position: relative">
			<input style="width: 180px; display: inline-block" id="search_table" type="text" name="q" autocomplete="off" size="18"/>
			<span id="clear_value" style="position: absolute; top: 0; right: 6px; cursor:pointer;color:blue;font-weight:bold;display:none;" title="Clear">&times;</span>
		</span>
	</div>
</div>
@stop 
@section('content')
	<style>
		#plugins1 html { margin:0; padding:0; font-size:62.5%; }
		#plugins1 body { max-width:800px; min-width:300px; margin:0 auto; padding:20px 10px; font-size:14px; font-size:1.4em; }
		#plugins1 h1 { font-size:1.8em; }
		#plugins1 .demo { overflow:auto; border:1px solid silver; min-height:100px; }
		
		.boxContext {margin-top: 0;}
		.table-name {
			text-align: left!important;
		}
		#tabs-tables th {
			color: white;
		}
		#tabs-tables td, #tabs-tables th {
			padding: 3px;
			text-align: center;
		}
		#tabs-tables table {
			border-collapse: collapse;
		}
	</style>
<link rel="stylesheet" href="/tree/dist/themes/default/style.min.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script src="/common/js/jquery-ui.min.js"></script>

	<script src="/tree/dist/jstree.min.js"></script>

<script type="text/javascript">

var ebtoken = $('meta[name="_token"]').attr('content');

$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});
$(function(){	
	_roles.loadData();
});

$(function () {
    $("#plugins1").jstree({
        "checkbox" : {
            "keep_selected_style" : false
        },
        "plugins" : [ "checkbox" ]
    });
    $("#plugins1 > ul > li > a > i.jstree-themeicon").remove();
    $("#plugins1 > ul > li > i.jstree-ocl").remove();
});
var deletedUserRoleRight = [];
var _roles = {
		editRole : function(){
			$("#d_group_name").val($("#Roles option:selected").text());
			var id=$("#Roles").val();

			$( "#dialog" ).dialog({
				width: 370,
				modal: true,
				title: "Rename role",
				buttons: {
					"Save": function(){
						var group_name=$("#d_group_name").val();
																				
						param = {
								'ID' : id,
								'NAME' : group_name
						}
                        sendAjax('/am/editRoles', param, function(data){
                            $("#Roles option:selected").text(group_name);
                            $("#dialog").dialog("close");
                        });

                    },
					"Cancel": function (){
						$("#dialog").dialog("close");
					}
				}
			});
		},

		addRole : function(){
            $("#d_group_name").val('');
            $( "#dialog" ).dialog({
				width: 370,
				modal: true,
				title: "New role",
				buttons: {
					"Save": function(){
						var group_name=$("#d_group_name").val();

						param = {
								'NAME' : group_name
						}

						sendAjax('/am/addRoles', param, function(data){
							_roles.loadCbo(data.userRole);
							$("#Roles option:last-child").attr("selected","selected");
                            _roles.loadData();
				    		$("#dialog").dialog("close");
						});
					},
					"Cancel": function (){
						$("#dialog").dialog("close");
					}
				}
			});
		},

		deleteRole : function(){
			var id=$("#Roles").val();
			if(!confirm("Are you sure to delete this group and all roles belong to it?")) return;
			param = {
					'ID' : id
			}

			sendAjax('/am/deleteRoles', param, function(data){
				_roles.loadCbo(data.userRole);
				_roles.loadData();
	    		$("#dialog").dialog("close");
			});				
		},
		loadCbo : function(data){
			var str = '';
			$('#Roles').val(str);
			for(var i = 0; i < data.length; i++){
				str += '<option value="'+ data[i].ID +'">'+ data[i].NAME +'</option>'
			}
			$('#Roles').html(str);
		},
		
		loadData : function(){
            deletedUserRoleRight = [];
			var roleID = $("#Roles").val();

			param = {
				'ROLE_ID' : roleID
			}

			$('#body_left').html('');
			$('#body_right').html('');
			$("#body_tables").html('');
			
			sendAjax('/am/loadRightsList', param, function(data){
				_roles.showData(data);
			});
		},
		showTables: function(tables){
			tables.forEach(function(t, i) {
				var tr = '<tr bgcolor="'+(i%2==0?'#eeeeee':'#f8f8f8')+'" t-id="'+t.ID+'" table="'+t.code+'">'+
				'<td class="table-name">'+(t.name==null?t.code:t.name)+'</td>'+
				'<td><input type="radio" name="table-'+t.code+'" value="0" '+(t.access==0?'checked':'')+'></td>'+
				'<td><input type="radio" name="table-'+t.code+'" value="1" '+(t.access==1?'checked':'')+'></td>'+
				'<td><input type="radio" name="table-'+t.code+'" value="2" '+(t.access==2 || t.access==null?'checked':'')+'></td>'+
				'</tr>';
				$("#body_tables").append(tr);
			});
		},
		showData : function(data){
			_roles.showTables(data.tables);
			var roleLeft = data.roleLeft;
			var roleRight = data.roleRight;
			var strLeft = '';
			var strRight = '';

			for(var i = 0; i < roleLeft.length; i++){
				var cssClass = "row1 ";
				if(i%2 == 0){
					cssClass = "row2 ";
				}
				var id_remove = "id_tr_remove_"+roleLeft[i].ID;
				var checked = (roleLeft[i].READ_ONLY == 1) ? "checked" : "";
                var read_only = '<input '+checked+' type="checkbox" class="ReadOnly" id="Read_Only_'+roleLeft[i].ID+'" name="ReadOnly" value="'+roleLeft[i].ID+'">';
				strLeft +='<tr class="'+cssClass+''+id_remove+'" index="'+i+'" search="'+roleLeft[i].NAME+'">';
				strLeft +='<td class="left_search" id="right_remove_'+roleLeft[i].ID+'" >' + roleLeft[i].NAME + '</td>';
                strLeft +='<td >' + read_only + '</td>';
				strLeft +='<td ><a style="cursor:pointer" onclick="_roles.removeOrGrant('+roleLeft[i].ID+',1);">Remove</a></td>';
				strLeft +='</tr>';
			}
			$('#body_left').html(strLeft);

			for(var j = 0; j < roleRight.length; j++){
				
				var cssClass = "row1 ";
				if(j%2 == 0){
					cssClass = "row2 ";
				}
                var id_grant = "id_tr_grant_"+roleRight[j].ID;
				strRight +='<tr class="'+cssClass+''+id_grant+'" index="'+j+'">';
				strRight +='<td class="right_search" id="right_grant_'+roleRight[j].ID+'" >' + roleRight[j].NAME + '</td>';
				strRight +='<td><a value="'+ roleRight[j].NAME +'" style="cursor:pointer" onclick="_roles.removeOrGrant('+roleRight[j].ID+',0);">Grant</a></td>';
				strRight +='</tr>';
			}
			$('#body_right').html(strRight);
		},
		removeOrGrant :function(right_id, romove){
		    var id_tr_remove = ".id_tr_remove_"+right_id;
            var id_tr_grant = ".id_tr_grant_"+right_id;

            var id_remove_1 = "id_tr_remove_"+right_id;
            var id_grant_1 = "id_tr_grant_"+right_id;

		    var id_remove = "#right_remove_"+right_id;
		    var id_grant = "#right_grant_"+right_id;

		    var name_remove = $(id_remove).text();
            var name_grant = $(id_grant).text();

            var id_read_only = "Read_Only_"+right_id;
            var id_read = "#Read_Only_"+right_id;

            var class_parent_remove = $( "#body_left tr:last-child" ).attr('index');
            var class_parent_grant = $( "#body_right tr:last-child" ).attr('index');
            var c_parent_remove = parseInt(class_parent_remove) + 1;
            var c_parent_grant = parseInt(class_parent_grant) + 1;

		    var right_remove = $('<tr class="'+id_remove_1+'" index="'+c_parent_remove+'"><td id="right_remove_'+right_id+'">'+name_grant+'</td><td><input type="checkbox" class="ReadOnly" id="'+id_read_only+'" name="ReadOnly" value="'+right_id+'"></td><td><a style="cursor:pointer" onclick="_roles.removeOrGrant('+right_id+',1);">Remove</a></td></tr>');
		    var right_grant = $('<tr class="'+id_grant_1+'" index="'+c_parent_grant+'"><td id="right_grant_'+right_id+'">'+name_remove+'</td><td><a style="cursor:pointer" onclick="_roles.removeOrGrant('+right_id+',0);">Grant</a></td></tr>');
            var checked = ($(id_read).is(':checked')) ? 1 : 0;
		    if (romove == 1) {
                right_grant.appendTo($("#body_right"));
				(c_parent_grant % 2 == 0) ? $(id_tr_grant).addClass('row2') : $(id_tr_grant).addClass('row1');
                $(id_tr_remove).remove();
                deletedUserRoleRight.push({	ROLE_ID		: $("#Roles").val(),
                    						RIGHT_ID	: right_id,
				});
			}else{
                right_remove.appendTo($("#body_left"));
                (c_parent_remove % 2 == 0) ? $(id_tr_remove).addClass('row2') : $(id_tr_remove).addClass('row1');
                $(id_tr_grant).remove();
			}

//			var roleID = $("#Roles").val();
//			param = {
//					'ROLE_ID' : roleID,
//					'RIGHT_ID' : right_id,
//					'TYPE' : romove
//				};
//				sendAjax('/am/removeOrGrant', param, function(data){
//					_roles.showData(data);
//				});
		},
		saveReadOnly : function(){
            var read_only = [];
            var roleID = $("#Roles").val();
            $('.ReadOnly').each(function() {
                read_only.push({
                    ROLE_ID		: roleID,
                    RIGHT_ID	: $(this).attr("value"),
                    GRANTED		: 1,
                    READ_ONLY 	: ($(this).is(':checked')) ? 1 : 0
                });
            });
			var tables = [];
			var deleted_tables = [];
			$('#body_tables tr').each(function() {
				var table = $(this).attr('table');
				var access = $('input[name="table-'+table+'"]:checked').val();
				if(access!=='2')
					tables.push({
						ROLE_ID		: roleID,
						TABLE_NAME	: table,
						ACCESS		: access,
					});
				else if($(this).attr('t-id')!=='null')
					deleted_tables.push({
						ROLE_ID		: roleID,
						TABLE_NAME	: table,
					});
            });
            param = {
                	editedData 	: {
						UserRoleRight	: read_only,
						UserRoleTable	: tables
					},
                	deleteData 	: {
						UserRoleRight	: deletedUserRoleRight,
						UserRoleTable	: deleted_tables
					},
            	};
				sendAjax('/am/savereadonly', param, function(data){
                    alert("Update successfully");
                    _roles.loadData();
				});
        }
}
function checkAllTables(c){
	$('#body_tables tr').each(function() {
		var table = $(this).attr('table');
		$('input[name="table-'+table+'"][value="'+c+'"]').prop("checked", true);
		$("#radio_1").prop("checked", true);
	});
}
</script>
<script>
	(function() {
		var textInput = $("#search_table").val(),
			clearBtn = textInput.nextSibling;
		textInput.onkeyup = function() {
			clearBtn.style.visibility = (this.value.length) ? "visible" : "hidden";
		};
	})();
	$( document ).ready(function() {
		$('#search_table').on('keyup', function () {
			var search = $(this).val();
            search = search.toLowerCase();
            $("#clear_value").show();
			var sfn = function () {
                var val = $(this).text();
                val = val.toLowerCase();
                /*$(this).toggle( !! val.match(search)).html(
                    val.replace(search, function (match) {
                        return '<mark>' + match + '</mark>'
                    })
                );*/
                $(this).closest("tr").css('display', val.match(search)?'':'none');
            };
            $('#body_left tr td.left_search').each(sfn);
            $('#body_right tr td.right_search').each(sfn);
            $('#body_tables tr td.table-name').each(sfn);

            $("#clear_value").click(function(){
                $(this).hide();
                $("#search_table").val('');
                search = '';
                $('#body_left tr td.left_search').each(sfn);
                $('#body_right tr td.right_search').each(sfn);
				$('#body_tables tr td.table-name').each(sfn);
			});

            $(this).keypress(function(event){
                if (event.keyCode === 10 || event.keyCode === 13) event.preventDefault();
            });
		});
		
		$("#tabs").tabs();
	});
</script>
<div id="dialog" style="display: none; height: 35px">
	<div id="chart_change">
		<table>
			<tr>
				<td>Group name:</td>
				<td><input type="text" size="" value="" id="d_group_name" style="width: 250px"></td>
			</tr>
		</table>
	</div>
</div>

<div id="tabs">
<ul>
	<li><a href="#tabs-rights">RIGHTS</a></li>
	<li><a href="#tabs-tables">DATA TABLES</a></li>
</ul>
<div id="tabs-rights" class="boxContext">
	<div class="boxContext_left">
		<table class="roleTable" >
			<thead>
				<tr>
					<td><b>Rights list</b></td>
					<td></td>
				</tr>
				<tr>
					<td class="roleColumn1"><b>Name</b></td>
					<td class="roleColumn2"><b>Read-only</b></td>
					<td class="roleColumn2"></td>
				</tr>
			</thead>
			<tbody id="body_left">
			</tbody>
		</table>
	</div>
	<div class="boxContext_left">
		<table class="roleTable">
			<thead>
				<tr>
					<td><b>Available rights</b></td>
					<td></td>
				</tr>
				<tr>
					<td class="roleColumn1"><b>Name</b></td>
					<td class="roleColumn2"></td>
				</tr>
			</thead>
			<tbody id="body_right">
			</tbody>
		</table>
	</div>
</div>
<div id="tabs-tables" class="boxContext">
	<table style="width: 600px">
		<thead>
			<tr style="background:rgb(96, 156, 185)">
				<th style="text-align:left">Name</th>
				<th style="width: 100px">Forbiden <button style="height: 16px;font-size: 10px!important;padding: 0 5px;border: 1px solid white;" onclick="checkAllTables(0)">All</button></th>
				<th style="width: 100px">Read-only <button style="height: 16px;font-size: 10px!important;padding: 0 5px;border: 1px solid white;" onclick="checkAllTables(1)">All</button></th>
				<th style="width: 100px">Full access <button style="height: 16px;font-size: 10px!important;padding: 0 5px;border: 1px solid white;" onclick="checkAllTables(2)">All</button></th>
			</tr>
		</thead>
	</table>
	<div style="width: 600px; height: 330px; overflow: auto">
		<table id="table-tables" style="width: 100%">
			<thead>
				<tr style="visibility: collapse">
					<th>Name</th>
					<th style="width: 100px">Forbiden</th>
					<th style="width: 100px">Read-only</th>
					<th style="width: 100px">Full access</th>
				</tr>
			</thead>
			<tbody id="body_tables">
			</tbody>
		</table>
	</div>
</div>
</div>
@stop
