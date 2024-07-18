<?php
$currentSubmenu 	= '/exportdata';
if (!isset($subMenus)) $subMenus = [];
if (!isset($active)) $active =0;
if (!isset($isAction)) $isAction =false;
$useFeatures	= isset($useFeatures)?$useFeatures:
    [
        ['name'	=>	"filter_modify",
            "data"	=>	["isFilterModify"	=> true,
                "isAction"			=> $isAction]],
    ];
$lastFilter	=  "CodePlanType";
?>
@extends('core.bsmain',['subMenus' 		=> $subMenus,
						'useFeatures'	=> $useFeatures])

@section('content')

    <style>
        #filterFrequence {
            clear: both;
        }
        .alloc_type {
            display: none
        }

        .plan_type {
            display: none
        }

        .forecast_type {
            display: none
        }

        #chartObjectContainer {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        small {
            font-size: 80%;
        }
        #OptionExport{
            margin-bottom: 0px;
            font-size: 12px;
        }
        /*.product_filter{*/
            /*padding-bottom: 10px;*/
        /*}*/
        /*#date_end, #date_begin{*/
            /*height: 30px;*/
            /*width: 98% !important;*/
        /*}*/

    </style>
    {{--<link rel="stylesheet" href="/common/css/bootstrap.min.css" />--}}
    <link rel="stylesheet" href="/common/css/bootstrap-multiselect.css" />
    {{--<script type="text/javascript" src="/common/js/bootstrap.min.js"></script>--}}
    <script type="text/javascript" src="/common/js/bootstrap-multiselect.js"></script>
    <select name="OptionExport" id="OptionExport">
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
    </select>
    <input type="button" value="Export excel" id="export_data_excel" onclick="exportDataExcel();">
    <script>
        $(function(){
            $('#txtObjectName').val('Flow');
            $('#CodeDeferGroupType').val(0);
            $("#chartObjectContainer").sortable();

            $(".phase_type").hide();
            $("#select_container_CodeProductType, #select_container_CodeDeferGroupType, #select_container_Keystore").css("display","none");

            $("#ObjectName,#ObjectDataSource,#ObjectDataSource, #ObjectTypePropertyExportData, #CodeFlowPhase, #CodeEventType, #CodeAllocType, #CodeTestingMethod, #CodePlanType, #Keystore").each(function(){
                $(this).multiselect({
                    enableFiltering: true,
                    allSelectedText: 'All',
                    numberDisplayed: 1,
                    nSelectedText: 'Selected',
                    nonSelectedText: 'None Selected'
                });
                $(this).parent().find('ul').attr('style','display:block;');
            });

            $("#select_container_ObjectName").insertAfter("#select_container_ObjectDataSource");

        });
        $("#CodeTestingMethod, #CodePlanType").attr('multiple','multiple');
        // Add check all
        var check_all_obj      = $('<div style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_obj" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        var check_all_property = $('<div style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_property" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        var check_all_flow     = $('<div class="check_all_radio" style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_flow" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        var check_all_event    = $('<div class="check_all_radio" style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_event" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        var check_all_allocation = $('<div class="check_all_radio" style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_allocation" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        var check_all_testing = $('<div style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_testing" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        var check_all_plan = $('<div style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_plan" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        var check_all_keystore = $('<div style="float: right; margin-right: 10px;margin-top: 4px;"><label class="checkbox-label"><input class="check_all_keystore" style="margin: 0 2px 0;" type="checkbox" value="115"><small style="font-weight: bold;">Select all</small></label></div>');
        check_all_obj.appendTo($("#select_container_ObjectName div"));
        check_all_property.appendTo($("#select_container_ObjectTypePropertyExportData div"));
        check_all_flow.appendTo($("#select_container_CodeFlowPhase div"));
        check_all_event.appendTo($("#select_container_CodeEventType div"));
        check_all_allocation.appendTo($("#select_container_CodeAllocType div"));
        check_all_testing.appendTo($("#select_container_CodeTestingMethod div"));
        check_all_plan.appendTo($("#select_container_CodePlanType div"));
        check_all_keystore.appendTo($("#select_container_Keystore div"));
        // style Title
        $("#title_ObjectName,#title_ObjectDataSource,#title_ObjectTypePropertyExportData,#title_CodeFlowPhase,#title_CodeEventType,#title_CodeAllocType,#title_CodeTestingMethod, #title_CodePlanType,#title_Keystore").css({"display":"inline-block","padding-top": "4px"});

        // _data_
        $("#ObjectDataSource option").each(function(){
            var value = $(this).attr("value");
            if(value.indexOf("_DATA_") == -1){
                $(this).remove();
            }
        })

        var height = $( window ).height();
        $('#filterFrequence').css("width", "100%").css("height",height - 200);
        $('#ObjectName,#ObjectTypePropertyExportData,#CodeFlowPhase,#CodeEventType,#CodeAllocType,#CodeTestingMethod, #CodePlanType,#Keystore').css("height", "300px");
        $('.ObjectName,.ObjectTypePropertyExportData,.CodeFlowPhase,.CodeEventType,.CodeAllocType,.CodeTestingMethod, .CodePlanType,.Keystore').css("max-width", "300px").css("width","270px");
        $('#ObjectDataSource').css({"margin-top":"10px","width":"260px"});
        $('.ObjectDataSource').css("max-width", "300px").css("width","270px");
        $('.CodeFlowPhase,.CodeEventType,.CodeAllocType,.CodeTestingMethod, .CodePlanType,.Keystore').hide();
//        $('.CodeFlowPhase,.CodeEventType option').removeAttr('selected');
        $('#ObjectName,#ObjectDataSource,#ObjectTypePropertyExportData,#CodeFlowPhase,#CodeEventType,#CodeAllocType,#CodeTestingMethod, #CodePlanType, #Keystore').multiselect();

        $( document ).ready(function() {
            $("button.multiselect").remove();
            /*$("#select_container_ObjectName .btn-group ul li input").attr('checked', false);
            $("#select_container_ObjectTypeProperty .btn-group ul li input").attr('checked', false);
            $("#select_container_CodeFlowPhase .btn-group ul li input").attr('checked', false);
            $("#select_container_CodeEventType .btn-group ul li input").attr('checked', false);
            $("#select_container_ObjectName .btn-group ul li").removeClass("active").css("overflow","hidden");
            $("#select_container_ObjectTypeProperty .btn-group ul li").removeClass("active").css("overflow","hidden");
            $("#select_container_CodeFlowPhase .btn-group ul li").removeClass("active").css("overflow","hidden");
            $("#select_container_CodeEventType .btn-group ul li").removeClass("active").css("overflow","hidden");*/
            $(".btn-group ul li").css({"width":"240px"});
            $(".btn-group ul").css({"overflow":"scroll","height":height - 237,"z-index":"0"});
            $( window ).resize(function() {
                var h = $( window ).height();
                $('#filterFrequence').css("height",h - 200);
                $(".btn-group ul").css({"overflow":"scroll","height":h - 237});

                if($("#ObjectDataSource").val() == "ENERGY_UNIT_DATA_ALLOC"){
                    $(".exportdata").css("overflow-x","scroll");
                    $('#filterFrequence').css("width","1640px");
                }
            });
            /*$("#ObjectName").val([]);
            $("#").val([]);

            $("#CodeFlowPhase").val([]);
            $("#CodeEventType").val([]);*/

            // Count item check all
            $('#ObjectName').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_obj").prop('checked', true) : $(".check_all_obj").prop('checked', false);
            });
            $('#ObjectTypePropertyExportData').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_property").prop('checked', true) : $(".check_all_property").prop('checked', false);
            });
            $('#CodeFlowPhase').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_flow").prop('checked', true) : $(".check_all_flow").prop('checked', false);
            });
            $('#CodeEventType').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_event").prop('checked', true) : $(".check_all_event").prop('checked', false);
            });
            $('#CodeAllocType').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_allocation").prop('checked', true) : $(".check_all_allocation").prop('checked', false);
            });
            $('#CodeTestingMethod').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_testing").prop('checked', true) : $(".check_all_testing").prop('checked', false);
            });
            $('#CodePlanType').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_plan").prop('checked', true) : $(".check_all_plan").prop('checked', false);
            });
            $('#Keystore').change(function(){
                ($(this).val()).length == $(this).children("option").size() ?
                    $(".check_all_keystore").prop('checked', true) : $(".check_all_keystore").prop('checked', false);
            });
			
            // Check All
            $(".check_all_obj").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_ObjectName span div ul li a label input').prop('checked', true);
                    $('#select_container_ObjectName span div ul li').addClass("active");
                    //$('#ObjectName option').attr("selected","selected");
                    $('#ObjectName option').prop('selected', true);
                }else{
                    $('#select_container_ObjectName span div ul li a label input').prop('checked', false);
                    $('#select_container_ObjectName span div ul li').removeClass("active");
                    //$('#ObjectName option').removeAttr("selected");
                    $('#ObjectName option').prop('selected', false);
                }
            });
            $(".check_all_property").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_ObjectTypePropertyExportData span div ul li a label input').prop('checked', true);
                    $('#select_container_ObjectTypePropertyExportData span div ul li').addClass("active");
                    //$('#ObjectTypePropertyExportData option').attr("selected","selected");
                    $('#ObjectTypePropertyExportData option').prop('selected', true);
                }else{
                    $('#select_container_ObjectTypePropertyExportData span div ul li a label input').prop('checked', false);
                    $('#select_container_ObjectTypePropertyExportData span div ul li').removeClass("active");
                    //$('#ObjectTypePropertyExportData option').removeAttr("selected");
                    $('#ObjectTypePropertyExportData option').prop('selected', false);
                }
            });
            $(".check_all_keystore").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_Keystore span div ul li input').prop('checked', true);
                    $('#select_container_Keystore span div ul li').addClass("active");
                    //$('#ObjectTypePropertyExportData option').attr("selected","selected");
                    $('#Keystore option').prop('selected', true);
                }else{
                    $('#select_container_Keystore span div ul li input').prop('checked', false);
                    $('#select_container_Keystore span div ul li').removeClass("active");
                    //$('#ObjectTypePropertyExportData option').removeAttr("selected");
                    $('#Keystore option').prop('selected', false);
                }
            });
            $(".check_all_flow").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_CodeFlowPhase span div ul li a label input').prop('checked', true);
                    $('#select_container_CodeFlowPhase span div ul li').addClass("active");
                    //$('#CodeFlowPhase option').attr("selected","selected");
                    $('#CodeFlowPhase option').prop('selected', true);
                }else{
                    $('#select_container_CodeFlowPhase span div ul li a label input').prop('checked', false);
                    $('#select_container_CodeFlowPhase span div ul li').removeClass("active");
                    //$('#CodeFlowPhase option').removeAttr("selected");
                    $('#CodeFlowPhase option').prop('selected', false);
                }
            });
            $(".check_all_event").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_CodeEventType span div ul li a label input').prop('checked', true);
                    $('#select_container_CodeEventType span div ul li').addClass("active");
                    //$('#CodeEventType option').attr("selected","selected");
                    $('#CodeEventType option').prop('selected', true);
                }else{
                    $('#select_container_CodeEventType span div ul li a label input').prop('checked', false);
                    $('#select_container_CodeEventType span div ul li').removeClass("active");
                    //$('#CodeEventType option').removeAttr("selected");
                    $('#CodeEventType option').prop('selected', false);
                }
            });
            $(".check_all_allocation").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_CodeAllocType span div ul li a label input').prop('checked', true);
                    $('#select_container_CodeAllocType span div ul li').addClass("active");
                    //$('#CodeAllocType option').attr("selected","selected");
                    $('#CodeAllocType option').prop('selected', true);
                }else{
                    $('#select_container_CodeAllocType span div ul li a label input').prop('checked', false);
                    $('#select_container_CodeAllocType span div ul li').removeClass("active");
                    //$('#CodeAllocType option').removeAttr("selected");
                    $('#CodeAllocType option').prop('selected', false);
                }
            });
            $(".check_all_testing").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_CodeTestingMethod span div ul li a label input').prop('checked', true);
                    $('#select_container_CodeTestingMethod span div ul li').addClass("active");
                    $('#CodeTestingMethod option').prop('selected', true);
                }else{
                    $('#select_container_CodeTestingMethod span div ul li a label input').prop('checked', false);
                    $('#select_container_CodeTestingMethod span div ul li').removeClass("active");
                    $('#CodeTestingMethod option').prop('selected', false);
                }
            });
            $(".check_all_plan").click(function(){
                if ($(this).prop('checked') == true) {
                    $('#select_container_CodePlanType span div ul li a label input').prop('checked', true);
                    $('#select_container_CodePlanType span div ul li').addClass("active");
                    $('#CodePlanType option').prop('selected', true);
                }else{
                    $('#select_container_CodePlanType span div ul li a label input').prop('checked', false);
                    $('#select_container_CodePlanType span div ul li').removeClass("active");
                    $('#CodePlanType option').prop('selected', false);
                }
            });
            // Chang Option
            $("#OptionExport").change(function() {
                var option = $(this).val();
                if (option == 2) {
                    //$(".check_all_obj").prop('checked', false);
                    $(".check_all_radio").hide();
                    $("#select_container_CodeFlowPhase span div ul li").removeClass("active");
                    $("#select_container_CodeFlowPhase span div ul li a label input").attr("type","radio").prop('checked', false);
                    $("#select_container_CodeFlowPhase span div ul li a label").attr("class","radio");
                    $("#CodeFlowPhase").removeAttr("multiple").multiselect('rebuild');

                    $("#select_container_CodeEventType span div ul li").removeClass("active");
                    $("#select_container_CodeEventType span div ul li a label input").attr("type","radio").prop('checked', false);
                    $("#select_container_CodeEventType span div ul li a label").attr("class","radio");
                    $("#CodeEventType").removeAttr("multiple").multiselect('rebuild');

                    $("#select_container_CodeAllocType span div ul li").removeClass("active");
                    $("#select_container_CodeAllocType span div ul li a label input").attr("type","radio").prop('checked', false);
                    $("#select_container_CodeAllocType span div ul li a label").attr("class","radio");
                    $("#CodeAllocType").removeAttr("multiple").multiselect('rebuild');

                    $("#select_container_CodeTestingMethod span div ul li").removeClass("active");
                    $("#select_container_CodeTestingMethod span div ul li a label input").attr("type","radio").prop('checked', false);
                    $("#select_container_CodeTestingMethod span div ul li a label").attr("class","radio");
                    $("#CodeTestingMethod").removeAttr("multiple").multiselect('rebuild');
                }else{
                    $(".check_all_flow").prop('checked', false);
                    $(".check_all_event").prop('checked', false);
                    $(".check_all_allocation").prop('checked', false);
                    $(".check_all_testing").prop('checked', false);
                    $(".check_all_radio").show();

                    $("#select_container_CodeFlowPhase span div ul li a label input").attr("type","checkbox");
                    $("#select_container_CodeFlowPhase span div ul li a label").attr("class","checkbox");
                    $("#CodeFlowPhase").attr('multiple','multiple').multiselect('rebuild');
                    $("#check_all_flow input").prop('checked', false);

                    $("#select_container_CodeEventType span div ul li a label input").attr("type","checkbox");
                    $("#select_container_CodeEventType span div ul li a label").attr("class","checkbox");
                    $("#CodeEventType").attr('multiple','multiple').multiselect('rebuild');
                    $("#check_all_event input").prop('checked', false);

                    $("#select_container_CodeAllocType span div ul li a label input").attr("type","checkbox");
                    $("#select_container_CodeAllocType span div ul li a label").attr("class","checkbox");
                    $("#CodeAllocType").attr('multiple','multiple').multiselect('rebuild');
                    $("#check_all_allocation input").prop('checked', false);

                    $("#select_container_CodeTestingMethod span div ul li a label input").attr("type","checkbox");
                    $("#select_container_CodeTestingMethod span div ul li a label").attr("class","checkbox");
                    $("#CodeTestingMethod").attr('multiple','multiple').multiselect('rebuild');
                    $("#check_all_testing input").prop('checked', false);
                }
                $(".btn-group ul li").css("width","240px");
            });

            $("#IntObjectType").change(function() {
                if ($(this).val() == "EU_TEST" || $(this).val() == "KEYSTORE" || $(this).val() == "DEFERMENT"||
                    $(this).val() == "LOGISTIC") $("#OptionExport option[value='2']").hide();
                else $("#OptionExport option[value='2']").show();
                $(".check_all_obj").prop('checked', false);
                $(".check_all_property").prop('checked', false);
                $(".check_all_flow").prop('checked', false);
                $(".check_all_event").prop('checked', false);
                $(".check_all_allocation").prop('checked', false);
                $(".check_all_testing").prop('checked', false);
				$(".check_all_keystore").prop('checked', false);
                //$(".check_all_plan").prop('checked', false);

                if($(this).val() == "DEFERMENT"){
                    $("#select_container_CodeDeferGroupType").show();
                    $("#CodeDeferGroupType").change();
                }else $("#select_container_CodeDeferGroupType").hide();
            });

            function renderObjectName(data){
                $("#ObjectName").html('');
                $("#ObjectName").multiselect('rebuild');
                for (var i = 0 ; i<data.length ; i++){
                    $("#ObjectName").append("<option value="+data[i]["ID"]+">"+data[i]["NAME"]+"</option>");
                }
                $('#ObjectName option').prop('selected', true);
                //$("#ObjectName").val(data[0]["ID"]);
                //$("#ObjectName").multiselect('rebuild');
                //$(".btn-group ul li").css("width", "240px");
            }

            // Comments
            /*$("#ObjectDataSource").change(function() {
                var data_source = $(this).val();
                if(data_source == "COMMENTS" || data_source == "ENVIRONMENTAL"){
                    param = {'DATA_SOURCE'   : data_source};
                    $.ajax({
                        type: "POST",
                        url: '/changeobjdata',
                        data: param,
                        success: function(data){
                            renderObjectName(data);
                        }
                    });
                }
            });*/

            // CodeDeferGroupType
            var arr_def = [];
//            $("#CodeDeferGroupType").change(function() {
//                var defer_group_type = $(this).val();
//                var defer_group_type_text = $('#CodeDeferGroupType option:selected').attr('name');
//                var facility = $("#Facility").val();
//                var size =  $('#CodeDeferGroupType option').size();
//                param = {
//                    'DEFER_GROUP_TYPE'      : defer_group_type,
//                    'DEFER_GROUP_TYPE_TEXT' : defer_group_type_text,
//                    'FACILITY'              : facility
//                };
//                if(size > 1){
//                    sendAjax('/changedefergroup', param, function(data){
//                        /*arr_def = [];
//                         for (var i = 0 ; i < data.length ; i++ ){
//                         arr_def.push(data[i].ID);
//                         }*/
//                        renderObjectName(data);
//                    });
//                }else renderObjectName([]);
//            });

            $("#IntObjectType, #ObjectDataSource").change(function(){
                showWaiting();
            });

            $("#Facility").change(function(){
                $(".check_all_obj").prop('checked', false);
                if($("#IntObjectType").val() == "KEYSTORE"){
                    param = {
                        'FACILITY': $("#Facility").val(),
                        'NAME_TABLE': $("#ObjectDataSource").val()
                    };
                    sendAjax('/changefacilitykeystore', param, function(data){
                        setTimeout( function(){
                            $("#ObjectName").html('');
                            for (var i = 0 ; i<data.length ; i++){
                                $("#ObjectName").append("<option value="+data[i]["ID"]+">"+data[i]["NAME"]+"</option>");
                            }
                            $('#ObjectName option').prop('selected', true);
                            $("#ObjectName").val(data[0]["ID"]);
                            $("#ObjectName").multiselect('rebuild');
                            $(".btn-group ul li").css("width", "240px");
                        }  , 1500 );
                    });
                }
            });
        });

        function exportDataExcel(){
            var obj_type = $('#IntObjectType').val();
            var facility = $('#Facility').val();
            var flow_id = $('#ObjectName').val();
            var data_source = $('#ObjectDataSource').val();
            var property_column = $('#ObjectTypePropertyExportData').val();
            var flow_phase_id = $('#CodeFlowPhase').val();
            var event_type_id = $('#CodeEventType').val();
            var alloc_type_id = $('#CodeAllocType').val();
            var testing_id    = $('#CodeTestingMethod').val();
            var plan_type_id    = $('#CodePlanType').val();
            var option  = $("#OptionExport").val();
            var string  = '';
            var text_obj = $("#IntObjectType option[value='"+obj_type+"']").text();
            var name_dgt =  (typeof $('#CodeDeferGroupType option:selected').attr('name') !== "undefined") ? $('#CodeDeferGroupType option:selected').attr('name') : 'not';
            var def_group_type = name_dgt +','+ $('#CodeDeferGroupType').val()+','+$('#CodeDeferGroupType option').size();

            if(option == 2){
                if(obj_type == "ENERGY_UNIT"){
                    if (data_source == 'ENERGY_UNIT_DATA_ALLOC')
                        string = $("#CodeFlowPhase option[value='"+flow_phase_id+"']").text() +" ,"+
                            $("#CodeEventType option[value='"+event_type_id+"']").text() +" ,"+ $("#CodeAllocType option[value='"+alloc_type_id+"']").text();
                    else string = $("#CodeFlowPhase option[value='"+flow_phase_id+"']").text() +" ,"+ $("#CodeEventType option[value='"+event_type_id+"']").text();
                }else if(obj_type == "EU_TEST") string = $("#CodeTestingMethod option[value='"+testing_id+"']").text();
            }else string = '';

            /*param = {
                'RENDER_TYPE'   : option,
                'FACILITY'      : facility,
                'DATE_BEGIN'    : '',
                'DATE_END'      : '',
                'OBJ_TYPE'      : obj_type,
                'SELECT_COLUMN' : property_column,
                'NAME_TABLE'    : data_source,
                'WHERE_ID'      : flow_id,
                'WHERE_FLOW'    : (option == 2) ? [flow_phase_id] : flow_phase_id,
                'WHERE_EVENT'   : (option == 2) ? [event_type_id] : event_type_id,
                'WHERE_ALLOC'   : (option == 2) ? [alloc_type_id] : alloc_type_id,
                'STRING'        : string
            }*/

            // Windows open
			if(obj_type == "LOGISTIC" || obj_type == "DEFERMENT") flow_id = [0];
            if (flow_id != null && property_column != null && flow_phase_id != null && event_type_id != null){
                var date_begin =  moment($("#date_begin").val(),configuration.time.DATETIME_FORMAT);
                var date_end =  moment($("#date_end").val(),configuration.time.DATETIME_FORMAT);
                var parse_db = date_begin.format(configuration.time.DATE_FORMAT_UTC);
                var parse_de = date_end.format(configuration.time.DATE_FORMAT_UTC);
                var parse_pc = property_column.toString();
                var parse_flow_id = flow_id.toString();
                if (option == 1){
                    flow_phase_id = flow_phase_id.toString();
                    event_type_id = event_type_id.toString();
                    alloc_type_id = alloc_type_id.toString();
                    testing_id    = testing_id!=null?testing_id.toString():'';
                    plan_type_id  = plan_type_id.toString();
                }
                var str = 	option+"."
			                +facility+"."
			                +parse_db+"."
			                +parse_de+"."
			                +obj_type+"."
			                +parse_pc+"."
			                +data_source+"."
			                +parse_flow_id+"."
			                +flow_phase_id+"."
			                +event_type_id+"."
			                +alloc_type_id+"."
			                +string+"."
			                +testing_id+"."
			                +text_obj+"."
			                +def_group_type+"."
			                +plan_type_id;

                showWaiting();
                if(option == 2) (flow_id.length > 10) ? alert('Maximum '+text_obj+' select 10 objects') : window.open("/exportexcel/"+encodeURIComponent(str));
                else window.open("/exportexcel/"+encodeURIComponent(str));
                hideWaiting();
            }else{
                if (obj_type == "ENERGY_UNIT"){
                    if(flow_id == null && property_column == null && flow_phase_id == null && event_type_id == null) alert("Please choose "+text_obj+", Property, Flow phase, Event type");
                    else if(flow_id == null && property_column == null && flow_phase_id == null) alert("Please choose "+text_obj+", Property, Flow phase");
                    else if(property_column == null && flow_phase_id == null && event_type_id == null) alert("Please choose Property, Flow phase, Event type");
                    else if(flow_id == null && flow_phase_id == null && event_type_id == null) alert("Please choose "+text_obj+", Flow phase, Event type");
                    else if(flow_id == null && property_column == null && event_type_id == null) alert("Please choose "+text_obj+", Property, Event type");
                    else if(property_column == null && flow_phase_id == null) alert("Please choose Property, Flow phase");
                    else if(property_column == null && event_type_id == null) alert("Please choose Property, Event type");
                    else if(flow_phase_id == null && event_type_id == null) alert("Please choose Flow phase, Event type");
                    else if(flow_id == null && property_column == null) alert("Please choose "+text_obj+", Property");
                    else if(flow_id == null && flow_phase_id == null) alert("Please choose "+text_obj+", Flow phase");
                    else if(flow_id == null && event_type_id == null) alert("Please choose "+text_obj+", Event type");
                    else if(property_column == null) alert("Please choose Property");
                    else if(flow_phase_id == null) alert("Please choose Flow phase");
                    else if(event_type_id == null) alert("Please choose Event type");
                    else if(flow_id == null) alert("Please choose "+text_obj);
                }else if(obj_type == "EU_TEST"){
                    if(flow_id == null) alert("Please choose "+text_obj);
                    else if(property_column == null) alert("Please choose Property");
                }else{
                    if(flow_id == null && property_column == null) alert("Please choose "+text_obj+", Property");
                    else if(flow_id == null) (obj_type == "DEFERMENT") ? alert("No data to export") : alert("Please choose "+text_obj);
                    else if(property_column == null) alert("Please choose Property");
                }
            }
        }
    </script>
@stop

@section('endDdaptData')
    <script>
        if(typeof filters == "undefined") filters = {};
        filters.preOnchange		= function(id, dependentIds,more){
            var partials 		= id.split("_");
            var prefix 			= partials.length>1?partials[0]+"_":"";
            var model 			= partials.length>1?partials[1]:id;
            var selectedObject	= "#select_container_"+prefix;
            var currentObject	= "#"+prefix;

            if (model == "IntObjectType"){
                var a = $(currentObject+model).find(":selected").attr( "name");
                if($(currentObject+model).find(":selected").attr( "name")=="ENERGY_UNIT"){
                    $(selectedObject+'CodeFlowPhase').css("display","block");
                    $(selectedObject+'CodeEventType').css("display","block");
                    $(selectedObject+'CodeTestingMethod').hide();
                    $(selectedObject+'CodePlanType').hide();
                }else if($(currentObject+model).find(":selected").attr( "name")=="EU_TEST"){
//                     $(selectedObject+'CodeTestingMethod').css("display","block");
                    $(selectedObject+'CodeFlowPhase').hide();
                    $(selectedObject+'CodeEventType').hide();
                    $(selectedObject+'CodeAllocType').hide();
                    $(selectedObject+'CodePlanType').hide();
                }else {
                    $(selectedObject+'CodeFlowPhase').hide();
                    $(selectedObject+'CodeEventType').hide();
                    $(selectedObject+'CodeAllocType').hide();
                    $(selectedObject+'CodeTestingMethod').hide();
                    $(selectedObject+'CodePlanType').hide();
                }
            }

        };

        var exAfterRenderingDependences = filters.afterRenderingDependences;
        filters.afterRenderingDependences = function (id,sourceModel) {
            exAfterRenderingDependences(id,sourceModel);
            var partials = id.split("_");
            var prefix = partials.length > 1 ? partials[0] + "_" : "";
            var model = partials.length > 1 ? partials[1] : id;
            var selectedObject = "#select_container_" + prefix;
            var currentObject = "#" + prefix;
            var int_obj = $('#IntObjectType').val();
            var height_browser  = $( window ).height();

            if (model == "ObjectName") {
                $('#title_' + id).text($(currentObject + "IntObjectType").find(":selected").text());
                $('#ObjectName, #ObjectDataSource, #CodeFlowPhase, #CodeEventType, #CodeAllocType, #CodeTestingMethod').multiselect('rebuild');
                $("#ObjectName, #ObjectDataSource, #CodeFlowPhase, #CodeEventType, #CodeAllocType, #CodeTestingMethod").parent().find('ul').attr('style', 'display:block;');
                $(".btn-group ul li").css("width", "240px");
                $(".btn-group ul").css({"overflow": "scroll", "height": height - 235, "z-index": "0"});
                if (int_obj == "DEFERMENT"){
                    //$("#ObjectTypePropertyExportData option:first").remove();
                    $('#ObjectTypePropertyExportData option:first').prop('selected', true);
                    //$("#CodeDeferGroupType").change();
                }else if (int_obj == "KEYSTORE" && sourceModel == (prefix+"IntObjectType")){
                    $("#ObjectDataSource").change();
                }else if(int_obj == "KEYSTORE"){
                    if($("#ObjectDataSource").val()=='QLTY_DATA'){
                        $('#title_ObjectName').text("Source Type")
                        $("#select_container_Keystore").css("display","none");
                    }
                    else if($("#ObjectDataSource").val()=='KEYSTORE_INJECTION_POINT_DAY'){
                        $('#title_ObjectName').text("Injection Point")
                        $("#select_container_Keystore").css("display","block");
                    }
                    else $("#select_container_Keystore").css("display","none");
                }
                else{
                    $("#select_container_Keystore").css("display","none");
                }				
            }
            else if (model == "ObjectTypePropertyExportData") {
                $('#ObjectTypePropertyExportData').multiselect('rebuild');
                $("#ObjectTypePropertyExportData").parent().find('ul').attr('style', 'display:block;');
                $(".btn-group ul li").css("width", "240px");
                $(".btn-group ul").css({"overflow": "scroll", "height": height - 235, "z-index": "0"});
                $(".check_all_property").prop('checked', false);

                var obj_type = $('#IntObjectType').val();
                var obj_name = $("#ObjectDataSource").val();
                if (obj_type == "FLOW") {
                    $(selectedObject + 'ObjectName').css("display", "block");
                    if ($("#ObjectDataSource").val() == "FLOW_DATA_PLAN") $(selectedObject + 'CodePlanType').css("display", "block");
                    else $(selectedObject + 'CodePlanType').hide();
                }else if (obj_type == "ENERGY_UNIT") {
                    if ($("#ObjectDataSource").val() == "ENERGY_UNIT_DATA_ALLOC") {
                        $(selectedObject + 'CodeAllocType').css("display", "block");
                        $(".exportdata").css("overflow-x", "scroll");
                        $('#filterFrequence').css("width", "1640px");
                    } else {
                        $(selectedObject + 'CodeAllocType').hide();
                        $(selectedObject + 'ObjectName').css("display", "block");
                        $('#filterFrequence').css("width", "1360px");
                    }
                }else{
					if (obj_name == "LOGISTIC" || obj_name == "DEFERMENT") $(selectedObject + 'ObjectName').hide();
					else if(obj_type == "KEYSTORE"){
                        $(".check_all_obj").prop('checked', false);
                        $(selectedObject + 'ObjectName').css("display", "block");
					}else $(selectedObject + 'ObjectName').css("display", "block");
				}
                hideWaiting();
            }else if(model == "Facility"){
                if(int_obj == "KEYSTORE"){
                    $("#Facility").change();
                }
            }
            $(".btn-group ul").css({"overflow":"scroll","height":height_browser - 237,"z-index":"0"});
        };
</script>
@stop
