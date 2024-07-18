@extends('core.float_dialog')

@section('editBoxParams')
<script src="/common/js/highcharts.js"></script>
<script src="/common/js/highcharts_exporting.js?1"></script>
<script src="/common/js/highcharts-offline-exporting.js?1"></script>

<script>

if(typeof $.fn.editableform!="undefined") 
	$.fn.editableform.template = 	'<form class="form-inline editableform">'+
									'<div class="control-group">' + 
									'<div><div class="extension-buttons" style="display:none;"><img src="/common/css/images/hist.png" height="16" class="editable-extension"></div><div class="editable-input"></div><div class="editable-buttons"></div></div>'+
									'<div class="editable-error-block"></div>' + 
									'</div>' + 
									'</form>';

editBox.preSendingRequest = function(viewId) {
	$("#history_container").css("display","none");
} 

var xchart;
var currentHistory	= {
		tab				:	false,
		columnName		:	false,
		rowData			:	false,
		successFunction	:	false,
		notLocked		:	false,
};

var selectHistoryValue = function(newValue){};


var parseChartDate	= typeof(actions.parseChartDate) == "function"? actions.parseChartDate	: function(datetime){
																			date = moment.utc(datetime,configuration.time.DATE_FORMAT_UTC);
																			y = date.year();
																			m = date.month();
																			d = date.date();
																			day = Date.UTC(y,m,d);
																			return {data	: day,
																					display	: date.format(configuration.time.DATE_FORMAT)};
																		};

																		
actions.extensionHandle	 = function(tab,columnName,rowData,limit,successFunction,notLocked){
	if (this.historyUrl) {
		console.log ( "extensionHandle url: "+this.historyUrl );
		limit = typeof(limit) !== "undefined"&&limit!==false?limit:$('#cboLimit').val();
		
		currentHistory = typeof(currentHistory) !== "undefined"?currentHistory:{
																					tab				:	false,
																					columnName		:	false,
																					rowData			:	false,
																					successFunction	:	false,
																					notLocked		:	false,
																			};
		currentHistory.tab				= tab;
		currentHistory.columnName		= columnName;
		currentHistory.rowData			= rowData;
		currentHistory.successFunction	= successFunction;
		currentHistory.notLocked		= notLocked;

		success = function(data){
			$("#history_container").css("display","block");
			console.log ( "extensionHandle success : "/* +JSON.stringify(data) */);
			historyDataSet = [];
			list = '';
			selectHistoryValue = function(newValue){
				editBox.closeEditWindow(true);
// 				$('#boxHistory').dialog('close');
				if(notLocked) successFunction(null,newValue);
//					actions.putModifiedData(tab,columnName,newValue,rowData);
			};
			
			$.each(data.history.dataSet, function( index, value ) {
//				value['y'] = parseFloat(value['VALUE']);
				day	= parseChartDate(value['OCCUR_DATE']);
				vl = Math.ceil(parseFloat(value['VALUE']) * 10) / 10;
				historyDataSet.push([day.data, vl]);
				list+="<a href='javascript:selectHistoryValue("+vl+")'>"+day.display+" : "+vl+"</a><br>";;
				
            });
			
			$("#history_list").html(list);
			
			 xchart=new Highcharts.Chart({
			        chart: {
			            zoomType: 'xy',
						renderTo: 'history_container'
			        },
					credits: false,
			        title: {
			            text: data.history.name        },
			        subtitle: {
			            text: data.history.fieldName 
			        },
			        xAxis: {
			            type: 'datetime',
			            dateTimeLabelFormats: { // don't display the dummy year
			            	month: '%e. %b',
//			                year: '%b'
			            },
			            title: {
			                text: 'Occur date'
			            }
			        },
			        tooltip: {
			            headerFormat: '',// '<b>{series.name}</b><br>',
			            pointFormat: '{point.x:%e. %b}: {point.y:.2f}'
			        },

			        plotOptions: {
			            spline: {
			                marker: {
			                    enabled: true
			                }
			            }
			        },
			        exporting: {
			            sourceWidth: $('#history_container').width(),
			            sourceHeight: $('#history_container').height(),
			            scale: 1,
			        },
			        series: [
						{
			            type: $('#cboChartType').val(),
						showInLegend: false,
			            data: historyDataSet}
			        ],
			        yAxis: [{ // Primary yAxis
			            labels: {
			                format: '{value}',
			                style: {
			                    color: Highcharts.getOptions().colors[0]
			                }
			            },
			            title: {
			                text: '',
			                style: {
			                    color: Highcharts.getOptions().colors[0]
			                }
			            },
			            opposite: false

			        }
					]
			    });
		}

		error =  function(data) {
			console.log ( "extensionHandle error: "/*+JSON.stringify(data)*/);
			$("#history_loading").html("not availble");
		};
		
    	option = {
			    	title 		: "History data",
			 		postData 	:  {
										tabTable	: tab,	
										field		: columnName,	
										rowData		: rowData, 
										limit		: limit
									},
			 		url 		: this.historyUrl,
			 		viewId 		: 'historyContent',
    	    	};
 		
		editBox.showDialog(option,success,error);
		return true;
	}
	else{
		console.log ( "init historyUrl ");
	}
	return false;
}

function limitChange(limit){
	actions.extensionHandle(currentHistory.tab,currentHistory.columnName,currentHistory.rowData,limit,currentHistory.successFunction,currentHistory.notLocked);
}
function changeChartType(type){
	xchart.series[0].update({
                type: type
            });
}
</script>

@stop

@section('historyContent')
@parent
	<div style="position:absolute;left:230px;top:10px;z-index:100">
		Chart type <select id="cboChartType" onchange="changeChartType($(this).val())" style="width:100px"><option value="line">Line</option><option value="spline">Curved line</option><option value="column">Column</option><option value="area">Area</option><option value="areaspline">Curved Area</option></select>
	</div>
	<table>
		<tr>
			<td width=200 valign='top' style="border-right:1px solid #dddddd">
				<b>Last<select id="cboLimit" onchange="limitChange($(this).val())">
					<option>5</option>
					<option selected>10</option>
					<option>15</option>
					<option>20</option>
					<option>30</option>
					<option>50</option>
				</select> values:</b><br><br>
				<div id="history_list" style="max-height:230px;overflow:auto"></div>
			</td>
		<td><div id="history_container" style="width: 650px; height: 300px; margin: 0 auto"></div></td></tr>
	</table>
@stop