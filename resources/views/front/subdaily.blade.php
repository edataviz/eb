<?php
	$currentSubmenu ='/dc/subdaily';
	$tables = [
        'FlowDataValueSubday'		=>['name'=>'Hourly Data'],
        'FlowDataTheorSubday'		=>['name'=>'Theor'],
        'FlowDataAllocSubday'		=>['name'=>'Alloc'],
        'FlowDataPlanSubday'		=>['name'=>'Plan'],
        'FlowDataForecastSubday'		=>['name'=>'Forecast'],
	];
 	//$active = 0;
?>

@extends('core.pm')
@section('funtionName')
FLOW DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
    actions.loadUrl 		= "/subdaily/load";
    actions.saveUrl 		= "/subdaily/save";
	
	actions.type = {
        idName:['{{config("constants.flowId")}}','{{config("constants.flFlowPhase")}}'],
        keyField:'{{config("constants.flowId")}}',
        saveKeyField : function (model){
            return '{{config("constants.flowIdColumn")}}';
        },
    };

	var aLoadParams	= actions.loadParams;
	actions.loadParams = function(reLoadParams) {
	  	var params = aLoadParams(reLoadParams);
        params["hour"] 	= $('#FilterHour').val();
	  	return params;
    }
     
    var hours = "";
    for(var i=0;i<24;i++) hours += '<option value="'+i+'"'+(i==6?' selected':'')+'>'+(i<10?'0'+i:i)+'</option>';
    $('.date_filter').append('<div class="filter FilterHour"><div><b>Hour</b></div><select id="FilterHour" name="FilterHour">'+hours+'</select></div>');

	$( document ).ready(function() {
        addDefaultOptionTo("Facility");
	    //$("#FlowDataValue").css( "pointer-events", "none" );
	    //$("#FlowDataValue").css( "display", "none" );
	});

    $("#buttonLoadData").parent().append('<button onclick="viewReport()" style="margin: 10px;position: relative;top: 8px;height: 26px;">View report</button>');

    function getStandardDateString(date){
        var m=(1+date.getMonth());
        if(m<10)m="0"+m;
        var day=date.getDate();
        if(day<10)day="0"+day;
        return date.getFullYear()+"-"+m+"-"+day;
    }

    function viewReport(){
        var runNumber = $('.contenBoxBackground.cellnumber.NUMBER_17').first().html();
        !runNumber && runNumber = 0;
        var date = getStandardDateString($('#date_begin').datepicker('getDate'));
        window.open('/report/viewreport.php?export=PDF&file=hourly_allocation_main&date__T_3='+date+'%20'+$('#FilterHour').text()+':00&run_number__T_1='+runNumber);
    }

    function addDefaultOptionTo(elementId){
        var option = renderDependenceHtml(elementId,{ID:0,NAME:"(All)"});
        $('#'+elementId).prepend(option);
        //$('#'+elementId).val(0);
    }
        
    function onAfterGotDependences(elementId,element,currentId){
        if(elementId.indexOf("Facility") !== -1){
            addDefaultOptionTo(elementId);
        }
    }

    $("#Facility").unbind( "change" );
    $("#Facility").change(function(){
        var facility = $(this).val();
        /*
        if($.inArray(facility+"*", scopeFacilities )>=0)
            $( "#buttonSave" ).css("display","none");
        else
            $( "#buttonSave" ).css("display","");
        */
        if(facility > 0){
            $.ajax({
                url: '/code/list',
                type: "post",
                data: {	type		: 'Facility',
                        dependences	: ['FlowHourly'],
                        value		: facility,
                        extra		: {}
                    },
                success: function(results){
                    $('#FlowHourly').html(''); 
                    for (var i = 0; i < results.length; i++) {
                        var elementId = 'FlowHourly';
                        $(results[i].collection).each(function(){
                            var option = renderDependenceHtml(elementId,this);
                            $('#'+elementId).append(option);
                        });
                        $('#'+elementId).val(results[i].currentId ? results[i].currentId : 0);
                        if(typeof onAfterGotDependences == "function") onAfterGotDependences(elementId,$('#'+elementId),results[i].currentId);
                    }
                },
                error: function(data) {
                    console.log(data.responseText);
                    alert("Could not get dropdown menu");
                }
            });            
        }
        else{
            alert('hehe');
        }
    });

</script>

@stop