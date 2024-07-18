<?php
$currentSubmenu ='/dc/subdailyold';

?>

@extends('core.pm')
@section('content')
    <div id="tabs">
        <ul id="ebTabHeader">
            <li id="FlowDataValue"><a href="#tabs-FlowDataValue"><font size="2">Day Value</font></a></li>
            <li id="FlowDataFdcValue"><a href="#tabs-FlowDataFdcValue"><font size="2">FDC Value</font></a></li>
            <li id="FlowDataTheor"><a href="#tabs-FlowDataTheor"><font size="2">Theoretical</font></a></li>
            <li id="FlowDataAlloc"><a href="#tabs-FlowDataAlloc"><font size="2">Allocation</font></a></li>
            <li id="FlowDataPlan"><a href="#tabs-FlowDataPlan"><font size="2">Plan</font></a></li>
<!--			
            <li id="FlowDataForecast"><a href="#tabs-FlowDataForecast"><font size="2">Forecast</font></a></li>
-->			
            <div id="more_actions"></div>
        </ul>
        <div id="tabs_contents">
            <div id="tabs-FlowDataValue">
                <div id="container_FlowDataValue" style="min-width: 600px;" class="clearfix">
                    <table border="0" cellpadding="3" id="table_FlowDataValue"
                           class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;">
                    </table>
                </div>
            </div>
            <div id="tabs-FlowDataFdcValue">
                <div id="container_FlowDataFdcValue" style="min-width: 600px;" class="clearfix">
                    <table border="0" cellpadding="3" id="table_FlowDataFdcValue"
                           class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;">
                    </table>
                </div>
            </div>
            <div id="tabs-FlowDataTheor">
                <div id="container_FlowDataTheor" style="min-width: 600px;" class="clearfix">
                    <table border="0" cellpadding="3" id="table_FlowDataTheor"
                           class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;">

                    </table>
                </div>
            </div>
            <div id="tabs-FlowDataAlloc">
                <div id="container_FlowDataAlloc" style="min-width: 600px;" class="clearfix">
                    <table border="0" cellpadding="3" id="table_FlowDataAlloc"
                           class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;">

                    </table>
                </div>
            </div>
            <div id="tabs-FlowDataPlan">
                <div id="container_FlowDataPlan" style="min-width: 600px;" class="clearfix">
                    <table border="0" cellpadding="3" id="table_FlowDataPlan"
                           class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;">
                    </table>
                </div>
            </div>
<!--			
            <div id="tabs-FlowDataForecast">
                <div id="container_FlowDataForecast" style="min-width: 600px;" class="clearfix">
                    <table border="0" cellpadding="3" id="table_FlowDataForecast"
                           class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;">
                    </table>
                </div>
            </div>
-->			
        </div>
    </div>
    <div id="sub-daily_data" style="display: none;">
        <div id="tableSubday"></div>
        <div class="cke_dialog_footer" >
            <div class="row">
                <div class="span6">
                    <div class="form-group row" style="margin-left: 0px !important;">
                        <div class="span1.5"  style="margin-left: 15px">
                            <label for="fromtime" class="span1 col-form-label timeinput" >From hour</label>
							<select id="fromtime_h"></select>
                        </div>
                        <div class="span1.5"  >
                            <label class="span1 col-form-label timeinput" style=" width: 52px !important;">To hour</label>
							<select id="totime_h"></select>
                        </div>
                        <div class="span1"  >
                            <button  style="width: 150px" onclick="allocate()" id="reallocatebtn">Re-Allocate</button>
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <div class="row">
                        <div class="span1" style="float: right;">
                            <button style="width: 65px;" onclick="$('#sub-daily_data').dialog('close');" id="deleteAllocate">Close</button>
                        </div>
                        <div class="span1" style="margin-left: 10px !important;float: right;">
                            <button style="width: 75px;" onclick="deleteAllocate()" id="deleteAllocate" value="22745">Delete</button>
                        </div>
                        <div class="span1" style="float: right;">
                            <button style="width: 74px;float: right" onclick="saveAllocate()" id="saveAllocate">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .firstColumn{
            display: none;
        }
        #tableSubday{
            height: 400px !important;
        }
        .edit-able{
            text-align: right;
        }
        .timeinput{
            padding-top: 5px;
            margin-left: 5px
        }
		#fromtime_h, #fromtime_m, #totime_h, #totime_m {
			width: initial;
		}
    </style>
    <script>
		for(var i=0;i<24;i++)
			$("#fromtime_h, #totime_h").append('<option value="'+i+'">'+(i<10?'0'+i:i)+'</option>');
		//for(var i=0;i<60;i++)
		//	$("#fromtime_m, #totime_m").append('<option value="">'+(i<10?'0'+i:i)+'</option>');
		$("#fromtime_h").val(0);
		$("#totime_h").val(23);
        $( document ).ready(function() {
            $("#tabs").tabs({
                active	:0,
                create	: function(event,ui){
                    actions.loadNeighbor(event, ui);
                },
                activate: function(event, ui) {
                    actions.loadNeighbor(event, ui);
                }
            });
        });
        var table;
        actions.loadUrl 		= "/subdailyold/load";
        actions.saveUrl 		= "/subdailyold/save";
        actions.type = {
            idName	:	function (){
                return ['FLOW_ID', 'OCCUR_DATE'];
            },
            keyField: 'DT_RowId',
            saveKeyField : function (model){
                return 'ID';
            },
        };

        var renderFirsColumn = actions.renderFirsColumn;
        actions.renderFirsColumn  = function ( data, type, rowData ) {
            var html = renderFirsColumn(data, type, rowData );
            var id = rowData['DT_RowId'];
            html += '<a id="edit_row_'+id+'" onclick="editsubData('+id+')" class="actionLink">Sub-daily Data</a>';
            return html;
        };

        function allocate(){
            var fromtime = $('#fromtime_h').val()+':00';
            var totime = $('#totime_h').val()+':00';
            if(fromtime == "" || totime == ""){
                alert("Please input time");
                return;
            }
            var timeStart = new Date("01/01/2007 " + fromtime).getHours();
            var timeEnd = new Date("01/01/2007 " + totime).getHours();

            /* if(hourDiff<=0){
                alert("From time have to less than to time");
                return;
            } */
			if(timeEnd < timeStart){
				timeStart -= 24;
			}
            var hourDiff = timeEnd - timeStart + 1;

            var activeTab=getActiveTabID()
            var param={
                'id': $('#reallocatebtn').val(),
                'hours': hourDiff,
                'table': activeTab,
                'timebegin': timeStart,
                'timeend': timeEnd
            }
            sendAjax("/reallocateold", param, function(data) {
                renderTableEdit(data)
            })
        }
        function saveAllocate() {
            var emergencyRows =[];
            table.rows().every( function () {
                emergencyRows.push(this.node())
            } )
            var editedData=[];
            var i=0;
            $(emergencyRows).each(function(index,value){
                if($(value).find('.editable-unsaved').length!=0){
                    editedData[i]={};
                    $(value.children).each(function(indexx,valuee){

                        var $td = $(valuee),
                            $th = $(valuee).closest('table').find('th').eq($(valuee).index());
                        var cellValue=$(this).text();
                        if(cellValue==""){
                            cellValue=null
                        }
                        var name="";
                        name=$th.attr('value');
                        editedData[i][name]=cellValue;
                    })
                    i++;

                }
            });
			if(editedData.length == 0){
				alert("There is no changed data to save.");
				return;
			}
            $(editedData).each(function(index,value){
                value['FLOW_ID']= $('#FlowHourly').val()
            });
            var activeTab=getActiveTabID()

            var param={
                "editedData": editedData,
                'table' :activeTab
            }
            sendAjax("/saveallocateold", param, function(data) {
                $('.editable-unsaved').removeClass('editable-unsaved');	
				actions.doLoad(true);
                alert("Complete");
            })
        }
        function deleteAllocate(){
            var activeTab=getActiveTabID()

            var result = confirm("Are you sure to delete this sub-daily data?");
                if(result){
                var param={
                    'id': $('#deleteAllocate').val(),
                    'table': activeTab
                }
                sendAjax("/deleteallocateold", param, function(data) {
                    renderTableEdit(data)
                })
            }

        }
        function renderTableEdit(data){
            $('#tableSubday').html("");
            var str='<table id="subDataTable"><thead><th value="OCCUR_DATE">Time</th>' +
			'<th value="FL_DATA_GRS_VOL">Gross Vol</th>' +
			'<th value="FL_DATA_NET_VOL">Nom. Rate (Sm3)</th>' +
			'<th value="FL_DATA_GRS_MASS">Gross Mass</th>' +
			'<th value="FL_DATA_NET_MASS">Net Mass</th>' +
			'<th value="FL_DATA_GRS_ENGY">Nom. Rate (GWh)</th>' +
			'<th value="FL_DATA_GRS_PWR">Power</th>' +
			'<th value="NUMBER_1">PNQ</th>' +
			'<th value="NUMBER_2">TNQ</th>' +
			'<th value="NUMBER_3">ENQ</th>' +
			'</thead><tbody>';

            $.each(data, function (key, value) {
                $.each(value, function(keyy,valuee) {
                    if(valuee==null){
                        value[keyy]="";
                    }
                });
				str += '<tr><td value="OCCUR_DATE">' + value.OCCUR_DATE.substr(0, value.OCCUR_DATE.lastIndexOf(":")) + 
				'</td><td class="edit-able" value="FL_DATA_GRS_VOL">' + value.FL_DATA_GRS_VOL + 
				'</td><td class="edit-able" value="FL_DATA_NET_VOL" >' + value.FL_DATA_NET_VOL + 
				'</td><td class="edit-able" value="FL_DATA_GRS_MASS">' + value.FL_DATA_GRS_MASS + 
				'</td><td class="edit-able" value="FL_DATA_NET_MASS">' + value.FL_DATA_NET_MASS + 
				'</td><td class="edit-able" value="FL_DATA_GRS_ENGY">' + value.FL_DATA_GRS_ENGY + 
				'</td><td class="edit-able" value="FL_DATA_GRS_PWR">' + value.FL_DATA_GRS_PWR + 
				'</td><td class="edit-able" value="NUMBER_1">' + value.NUMBER_1 + 
				'</td><td class="edit-able" value="NUMBER_2">' + value.NUMBER_2 + 
				'</td><td class="edit-able" value="NUMBER_3">' + value.NUMBER_3 + 
				'</td></tr>';
            });
            str+="</tbody></table>";
            if(data.length==0){
                $('#reallocatebtn').text("Allocate");
            }
            else{
                $('#reallocatebtn').text("Re-Allocate");

            }
            $('#tableSubday').append(str);
            table = $('#subDataTable').DataTable({
                "paging": false,
                "searching": false,
                "scrollY":        "338px",
                "scrollCollapse": true,
                "info":     false,
                "bInfo" : false,
                "selecte": true,
            });
            $('.edit-able').editable({
                onblur		: 'submit',
                placement	: 'left',
                showbuttons		: false,
				emptytext: '',
            });

        }
        editBox.loadUrl 		= "/loadsubday";
        editBox.saveUrl 		= '/savesubday';

        function editsubData(id) {
            var activeTab=getActiveTabID()

            $('#reallocatebtn').attr("value",id);
            $('#deleteAllocate').attr("value",id);
            param = {
                "id":id,
                'table':activeTab
            };
            $('#fromtime').val("00:00");
            $('#totime').val("23:00");

            sendAjax("/loadsubdayold", param, function(data) {
                $('#tableSubday').html("");

                $( "#sub-daily_data" ).dialog({
                    height: 500,
                    width: 1100,
                    modal: true,
                    title: "Sub-daily Data",
                });
                renderTableEdit(data)

            })
        }
    </script>


@stop