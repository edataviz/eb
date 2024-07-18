<?php
$currentSubmenu ='/am/users';
$mainTab        = 'User';
$tables         = [$mainTab	=>['name'=>'List Users']];
$isAction = true;
$detailTableTab = 'EmissionIndirRelDataValue';
$userRole = \App\Models\UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
$loProductionUnit = \App\Models\LoProductionUnit::all(['ID', 'NAME']);
$loArea = \App\Models\LoArea::all(['ID', 'NAME', 'PRODUCTION_UNIT_ID']);
$facility = \App\Models\Facility::all(['ID', 'NAME','AREA_ID']);
$lastFilter	=  "UserRole";
?>

@extends('core.pd')
@section('adaptData')
    @parent
    <meta name="_token"
          content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
    <style>
        #boxEditUser html { margin:0; padding:0; font-size:62.5%; }
        #boxEditUser body { max-width:900px; min-width:300px; margin:0 auto; padding:20px 10px; font-size:14px; font-size:1.4em; }
        #boxEditUser h1 { font-size:1.8em; }
        #boxEditUser .demo { overflow:auto; border:1px solid silver; min-height:100px; }
    </style>
    <link rel="stylesheet" href="/common/css/admin.css">
    <link rel="stylesheet" href="/common/css/jquery-ui.css">
    <link rel="stylesheet" href="/tree/dist/themes/default/style.min.css" />
    <script src="/tree/dist/jstree.min.js"></script>
    <link rel="stylesheet" href="/common/css/style.css" />

    <script>
        actions.loadUrl = "/am/load";
        actions.saveUrl = "/am/saveuser";

        actions['idNameOfDetail'] = ['PARENT_ID', 'ID','OCCUR_DATE'];

        actions.type = {
            idName:['ID'],
            keyField:'ID',
            saveKeyField : function (model){
                return 'ID';
            },
        };

        actions.renderFirsColumn = function ( data, type, rowData ) {
            var id = rowData['DT_RowId'];
            isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
            var html = '';
            if(isAdding){
                html += '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
            }else{
                html += '<a id="delete_row_' + id + '" class="actionLink">&nbsp;Delete</a>';
                html += '<a id="edit_row_' + id + '" class="actionLink">Edit</a>';
            }
            return html;
        };
        actions.getRenderFirsColumnFn = function (tab) {
            if(tab=='{{$detailTableTab}}') return actions.renderFirsEditColumn;
            return actions.renderFirsColumn;
        }

        var pid;
        var date_parent;
        var detail_user = [];
        var oInitExtraPostData = editBox.initExtraPostData;
        editBox.initExtraPostData = function (id,rowData){
            var idata = oInitExtraPostData(id,rowData);
            detail_user = rowData;
            pid = id;
            date_parent = rowData['OCCUR_DATE'];
            idata.tabTable  = '{{$detailTableTab}}';
            return 	idata;
        };

        actions.enableUpdateView = function(tab,postData){
            return tab=='{{$mainTab}}';
        };

        var oGetTableOption = actions.getTableOption;
        actions.getTableOption = function (data,tab) {
            if (tab == '{{$detailTableTab}}') {
                return {
                    tableOption :	{
                        autoWidth	: false,
                        scrollX		: false,
                        searching	: false,
                        scrollY		: "200px",
                    },
                    resetTableHtml : function(tabName) { return true}
                };
            }
            return oGetTableOption(data,tab);
        };
        actions.getAddButtonHandler = function(table,tab,doMore){
            return function(e){
                $("#plugins1").jstree('deselect_all');
                $("#plugins1").jstree('close_all');
                $("#plugins2").jstree('deselect_all');
                $("#plugins2").jstree('close_all');
                $("#plugins3").jstree('deselect_all');
                $("#plugins3").jstree('close_all');
                $("#txtPassword").val("");
                editBox.editRow(-1,{ROLES_ID:[],FACILITIES:[],CODE:"Add User"});
            };
        };

        var delete_user_item = actions.deleteRowFunction;
        actions.deleteRowFunction = function(table,aRowData,tab){
            if(!confirm("Are you sure to delete this user?")) return;
            var param = {
                    'ID' : aRowData.ID
            }
            $.ajax({
                url: '/am/delete',
                type: "GET",
                data: param,
                success: function(_data){
                    alert(_data.Message);
                    delete_user_item(table,aRowData,tab);
                }
            });
        }

        // Hiện ra cửa sổ child
        var read_only_read = [];
        editBox.preSendingRequest = function (viewId){
            read_only_read = [];
            $("#box_loading").css("display","none");
            var facility = detail_user['DATA_SCOPE_FA'];
            facility = facility===undefined?[]:facility;
            var roles = detail_user['ROLES_ID'];
            var user_name = detail_user['username'];
            var pass = '';
            var last_name = detail_user['LAST_NAME'];
            var middle_name = detail_user['MIDDLE_NAME'];
            var first_name = detail_user['FIRST_NAME'];
            var email = detail_user['EMAIL'];
            //var ex_date = detail_user['expire_date'];
            var active = detail_user['ACTIVE'];
            var id_user = detail_user['ID'];

            $("#txtUsername").val(user_name);
            $("#txtPassword").val(pass);
            $("#txtLastName").val(last_name);
            $("#txtMiddleName").val(middle_name);
            $("#txtFirstName").val(first_name);
            $("#txtEmail").val(email);
            $("#UserID").val(id_user);
            //$("#txtExpireDate").val(ex_date);
            // Status
            $("#plugins3").jstree('deselect_all');
            $("#plugins3").jstree('close_all');
            if (active == "Active" || active == "Active, Expired") $('#plugins3').jstree('select_node', "active_plugin3");
            // Roles
            $("#plugins2").jstree('deselect_all');
            $("#plugins2").jstree('close_all');
            for(var j = 0; j < roles.length; j++) {
                $('#plugins2').jstree('select_node', "#role_"+roles[j]);
            }
            // facility
            $("#plugins1").jstree('deselect_all');
            $("#plugins1").jstree('close_all');
            for(var j = 0; j < facility.length; j++) {
                var str_facility = facility[j];
                if(str_facility.indexOf("*") != -1){
                    var fa = str_facility.replace("*","");
                    $('#plugins1').jstree('select_node', "#facility_check_"+fa);
                    var id = "#readonly-facility_check_"+fa+" input";
                    $(id).prop('checked', true);
                }else{
                    $('#plugins1').jstree('select_node', "#facility_check_"+str_facility);
                }
            }
            $(".read-only-facility").css({"margin":"0 0 0 4px"});
        }

        // tree
        $(function () {
            $("#plugins1").jstree({
                "checkbox" : {
                    "keep_selected_style" : false
                },
                "plugins" : [ "checkbox" ],
                "checkbox": { "two_state" : true }
            });

            // read-only jstree
            $("#plugins1").on("changed.jstree", function(e, data) {
                if(data.node){
                    var is_checked = data.node.state.selected;
                    if(data.node.li_attr.level=="1" || data.node.li_attr.level=="2" || data.node.li_attr.level=="3"){ //is facility
                        var id = data.node.id;
                        var parent_id = data.node.id;
                        var children_d = data.node.children_d;
                        if(is_checked){
                            if(children_d.length > 0){
                                for (var i = 0 ; i<children_d.length ; i++){
                                    $(this).jstree(true)._open_to(children_d[i]);
                                    var str = children_d[i].indexOf("facility_check");
                                    if(str != -1){
                                        var find = $("#"+children_d[i]+" span").attr("id");
                                        if (typeof find == "undefined") {
                                            $("#"+children_d[i]).append("<span id='readonly-"+children_d[i]+"'><input class='read-only-facility' value='"+children_d[i]+"' type='checkbox'> Read-only</span>");
                                        }
                                    }
                                }
                            }else {
                                if(id.indexOf("facility_check") != -1) $("#"+id).append("<span id='readonly-"+id+"'><input class='read-only-facility' value='"+id+"' type='checkbox'> Read-only</span>");
                            }
                            $(".read-only-facility").css({"margin":"0 0 0 4px"});
                        }else{
                            $(this).jstree("close_node", "#"+parent_id);
                            if (children_d.length > 0){
                                for (var i = 0 ; i<children_d.length ; i++){
                                    var str = children_d[i].indexOf("facility_check");
                                    if(str != -1){
                                        $("#readonly-"+children_d[i]).remove();
                                    }
                                }
                            }else $("#readonly-"+id).remove();
                        }
                    }
                    console.log(data.node.li_attr.level);
                }
            });

            $("#plugins1").on("open_node.jstree", function(e, data) {
                var children_d = data.node.children_d;
                if (data.node.state.selected){
                    if(children_d.length > 0){
                        for (var i = 0 ; i<children_d.length ; i++){
                            $(this).jstree(true)._open_to(children_d[i]);
                            var str = children_d[i].indexOf("facility_check");
                            if(str != -1){
                                var find = $("#"+children_d[i]+" span").attr("id");
                                if (typeof find == "undefined") {
                                    $("#"+children_d[i]).append("<span id='readonly-"+children_d[i]+"'><input class='read-only-facility' value='"+children_d[i]+"' type='checkbox'> Read-only</span>");
                                }
                            }
                        }
                    }else {
                        if(id.indexOf("facility_check") != -1) $("#"+id).append("<span id='readonly-"+id+"'><input class='read-only-facility' value='"+id+"' type='checkbox'> Read-only</span>");
                    }
                    $(".read-only-facility").css({"margin":"0 0 0 4px"});


                    var facility = detail_user['DATA_SCOPE_FA'];
                    facility = facility===undefined?[]:facility;
                    for(var j = 0; j < facility.length; j++) {
                        var str_facility = facility[j];
                        if(str_facility.indexOf("*") != -1){
                            var fa = str_facility.replace("*","");
                            $('#plugins1').jstree('select_node', "#facility_check_"+fa);
                            var id = "#readonly-facility_check_"+fa+" input";
                            $(id).prop('checked', true);
                        }else{
                            $('#plugins1').jstree('select_node', "#facility_check_"+str_facility);
                        }
                    }
                }
            });

            $("#plugins1").on("close_node.jstree", function (e, data) {
                //read_only_read = [];
                var selectedElmsIdsRead = [];
                var selectedElmsRead = $('#plugins1').jstree("get_selected", true);
                $.each(selectedElmsRead, function () {
                    if (this.li_attr.level == 3) {
                        selectedElmsIdsRead.push(this.li_attr.value);
                    }
                });

                for (var i = 0; i < selectedElmsIdsRead.length; i++) {
                    var id = "#readonly-facility_check_" + selectedElmsIdsRead[i] + " input";
                    if ($(id).prop('checked') == true) {
                        var read = $(id).val();
                        var sub_read = read.substr(15, 10);
                        read_only_read.push(sub_read);
                    }
                }
            });
        });
        $(function () {
            $("#plugins2").jstree({
                "checkbox" : {
                    "keep_selected_style" : false
                },
                "plugins" : [ "checkbox" ]
            });
            $("#plugins2 > ul > li > a > i.jstree-themeicon").remove();
        });
        $(function () {
            $("#plugins3").jstree({
                "checkbox" : {
                    "keep_selected_style" : false
                },
                "plugins" : [ "checkbox" ]
            });
            $("#plugins3 > ul > li > a > i.jstree-themeicon").remove();//jstree-icon
            $("#plugins3 > ul > li > i.jstree-ocl").remove();
        });
        /*$( "#txtExpireDate" ).datepicker({
            changeMonth:true,
            changeYear:true,
            dateFormat:"yy/mm/dd"
        });*/
    </script>
@stop

@section('editBoxParams')
    @parent
    <script>
//        editBox.saveUrl = "/am/updateUser";
        editBox.saveUrl = "/am/save";
        editBox.enableRefresh 	= false;

        editBox['size'] = {
            height : 515,
            width : 960
        };

        editBox.getSaveDetailUrl = function (url,editId,viewId){
            if (url == "/am/save") return url;
            return 	editBox.saveUrl;
        }

        editBox['checkEmptyEditData'] = function(editId,saveUrl) {
            var user = $('#txtUsername').val();
            if (saveUrl == "/am/save") {
                if(user == ""){
                    alert("Username is not null");
                    $('#txtUsername').focus();
                    throw "Username is not null";
//                    return true;
                }
            }else if (saveUrl == "/am/updateUser"){
                if(user == ""){
                    alert("Username is not null");
                    $('#txtUsername').focus();
                    throw "Username is not null";
                }
            }
            return false;
        };

        editBox['saveFloatDialogSucess'] = function(data,saveUrl){
            alert(data.Message);
            close = true;
            actions.doLoad(true);
            /* setTimeout(function(){
                $('#floatBox').dialog('close');
            }, 2000); */
            return close;
        }

        editBox.buildActionButtons = function (editId,saveUrl){
            read_only_read = [];
            var buttons = {};
            if (editId == -1) {
                buttons["Save "] =  {
                    text	: "Save",
                    id		: "floatDialogSaveButton",
                    "class": "buttonSave",
                    click	: function(){
                        editBox.saveDetail(-1,editBox['saveFloatDialogSucess'],editBox.saveUrl);
                    }
                };
            } else {
                buttons["Save "] =  {
                    text	: "Save",
                    id		: "floatDialogSaveButton",
                    "class": "buttonSave",
                    click	: function(){
                        editBox.saveDetail(editId,editBox['saveFloatDialogSucess'],"/am/updateUser");
                    }
                };
            }
            if (editId != -1){
                buttons["Save as"] = function(){
                    editBox.saveDetail(-1,editBox['saveFloatDialogSucess'],editBox.saveUrl);
                };
            }

            buttons["Cancel "] =  {
		         text: "Cancel",
		         id: "floatDialogCancelButton",
		         click: function(){
						$("#floatBox").dialog("close");
		         }   
	      	};
            return buttons;
        }

        function unique(list) {
            var result = [];
            $.each(list, function(i, e) {
                if ($.inArray(e, result) == -1) result.push(e);
            });
            return result;
        }

        editBox['initSavingDetailData'] = function(editId,saveUrl) {
            var active = 0;
            var selectedElmsIds1 = [];
            var selectedElmsIds2 = [];
            var selectedElmsIds3 = [];
            var read_only        = [];

            var selectedElms = $('#plugins1').jstree("get_selected", true);
            $.each(selectedElms, function() {
                if (this.li_attr.level == 1){
                    selectedElmsIds1.push(this.li_attr.value);
                }else if (this.li_attr.level == 2){
                    selectedElmsIds2.push(this.li_attr.value);
                }else if (this.li_attr.level == 3){
                    selectedElmsIds3.push(this.li_attr.value);
                }
            });

            // read-only
            for (var i = 0; i<selectedElmsIds3.length; i++){
                var id = "#readonly-facility_check_"+selectedElmsIds3[i]+" input";
                if($(id).prop('checked') == true){
                    var read = $(id).val();
                    var sub_read = read.substr(15,10);
                    read_only.push(sub_read);
                }
            }
            read_only_read = unique(read_only_read);
            if (read_only_read.length > 0 && read_only.length > 0){
                var arr = read_only_read.concat(read_only);
                var unionSet = unique(arr);
            }
            else if(read_only_read.length == 0 && read_only.length > 0) var unionSet = read_only;
            else if(read_only_read.length > 0 && read_only.length == 0) var unionSet = unique(read_only_read);

            selectedElmsIds1 = selectedElmsIds1.length>0?selectedElmsIds1.join(','):null;
            selectedElmsIds2 = selectedElmsIds2.length>0?selectedElmsIds2.join(','):null;
            //selectedElmsIds3 = selectedElmsIds3.length>0?selectedElmsIds3.join(','):null;

            var role = [];
            var selectedElmsIdsRoles = $('#plugins2').jstree("get_selected", true);
            $.each(selectedElmsIdsRoles, function() {
                role.push(this.li_attr.value);
            });
            var roles = role.length>0?role.join(','):null;

            if($("#plugins3 > ul > li").attr("aria-selected") == "true") {
                active = 1;
            }

            param = {
                'username' : $('#txtUsername').val(),
                'pass' : $('#txtPassword').val(),
                'lastname' : $('#txtLastName').val(),
                'middlename' : $('#txtMiddleName').val(),
                'firstname' : $('#txtFirstName').val(),
                'email' : $('#txtEmail').val(),
//                'expireDate' : $('#txtExpireDate').val().replace('-', '/'),
                'roles' : roles,
                'pu_id' : selectedElmsIds1,
                'area_id' : selectedElmsIds2,
                'fa_id' : selectedElmsIds3,
                'read_only' : unionSet,
                'active' : active
            };
            if(saveUrl == "/am/updateUser"){
                param.ID = $('#UserID').val();
                param.isUpdate = 1;
            }
            return param;
        };
    </script>
@stop

@section('editBoxContentview')
    <div id="boxEditUser" class="context_iframe" style="height:auto; overflow: hidden;margin-left: 7px;">
        <form name="frmUser" id="frmUser" action="" method="POST">
            <input type="hidden" value="" id="UserID">
            <div id="divleft" class="div_left" style="padding: 0px; width: 300px;">
                <td width="100"><strong>Informations</strong></td>
                <table border="0" cellpadding="0" id="table7" style="margin-top: 10px;">
                    <tbody>
                        <tr>
                            <td>User ID</td>
                            <td><input id="txtUsername" style="width: 174px" type="text" name="txtUsername" value="" size="20"></td>
                        </tr>
                        <tr>
                            <td>Password</td>
                            <td><input id="txtPassword" style="width: 174px" type="password"
                                       name="txtPassword" value="" size="20"></td>
                        </tr>
                        <tr>
                            <td>Last name</td>
                            <td><input id="txtLastName" style="width: 174px; height: 22px;"
                                       type="text" name="txtLastName" value=""
                                       size="20"></td>
                        </tr>
                        <tr>
                            <td>Middle name</td>
                            <td><input id="txtMiddleName" style="width: 174px; height: 22px;"
                                       type="text" name="txtMiddleName"
                                       value="" size="20"></td>
                        </tr>
                        <tr>
                            <td>First name</td>
                            <td><input id="txtFirstName" style="width: 174px; height: 22px;"
                                       type="text" name="txtFirstName" value=""
                                       size="20"></td>
                        </tr>
                        <tr>
                            <td>Email address</td>
                            <td><input id="txtEmail" style="width: 174px; height: 22px;"
                                       type="text" name="txtEmail" value=""
                                       size="20"></td>
                        </tr>
                        {{--<tr>
                            <td>Expire date</td>
                            <td><input id="txtExpireDate" style="width: 174px; height: 22px;"
                                       type="text" name="txtExpireDate"
                                       value="" size="20"></td>
                        </tr>--}}
                        <tr>
                            <td>Active</td>
                            <td>
                                <div id="plugins3" style="border: none;">
                                    <ul>
                                        <li id="active_plugin3"></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="divright1" style="width: 300px; float: left;">
                <strong style="margin-left: 9px;">Roles</strong>
                <div id="plugins2" class="demo" style="height: 100%; border: none; margin-top: 10px;">
                    <ul>
                        @foreach($userRole as $role)
                            <li id="role_{!! $role->ID !!}" value="{!! $role->ID !!}">{!! $role->NAME !!}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div id="divright" style="float: left; height: 100%; width: 310px;">
                <strong style="margin-left: 9px;">Data scope</strong>
                <tr>
                    <div id="plugins1" class="demo" style="height: 100%; border: none; margin-top: 10px;">
                        <ul>
                            @foreach($loProductionUnit as $unit)
                                <li value="{!! $unit->ID !!}" level="1">{!!$unit->NAME!!}
                                    <ul>
                                        @foreach($loArea as $area)
                                            @if($area->PRODUCTION_UNIT_ID == $unit->ID)
                                                <li value="{!! $area->ID !!}" level="2">{!!$area->NAME!!}
                                                    <ul>
                                                        @foreach($facility as $fa)
                                                            @if($fa->AREA_ID == $area->ID)
                                                                <li id="facility_check_{!! $fa->ID !!}" value="{!! $fa->ID !!}" level="3">{!!$fa->NAME!!}</li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </tr>
            </div>
        </form>
    </div>
@stop
