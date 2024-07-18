<script src="/common/js/highcharts6.js?5"></script>
<script src="/common/js/exporting.js?7"></script>
<script src="/common/js/export-csv.js?9"></script>

<script>

if(typeof $.fn.editableform!="undefined") 
	$.fn.editableform.template = 	'<form class="form-inline editableform">'+
									'<div class="control-group">' + 
									'<div><div class="extension-buttons" style="display:none;"><img src="/common/css/images/hist.png" height="16" class="editable-extension"></div><div class="editable-input"></div><div class="editable-buttons"></div></div>'+
									'<div class="editable-error-block"></div>' + 
									'</div>' + 
									'</form>';

var xchart;
var currentHistory	= {
		tab				:	false,
		columnName		:	false,
		rowData			:	false,
		successFunction	:	false,
		notLocked		:	false,
};

var selectHistoryValue = function(newValue){};

if(typeof actions == "undefined") actions = {};
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

		var submitRowData				= jQuery.extend({},rowData);

		if(typeof submitRowData.OCCUR_DATE == "undefined" 
			|| submitRowData.OCCUR_DATE == null) 
			submitRowData.OCCUR_DATE = $("#date_begin").val();
		else
			submitRowData.OCCUR_DATE = moment.utc(submitRowData.OCCUR_DATE,configuration.time.DATE_FORMAT_UTC).format(configuration.time.DATE_FORMAT);

		$("#boxHistory").dialog({
			height: 350,
			width: 900,
			position: { my: 'top', at: 'top+150' },
			modal: true,
			title: "History data"
		});

//		$("#history_container").html("<br/><br/><br/>Loading...");
//		$("#history_list").html("<br/>Loading...");
		$("#history_loading").html("Loading...");
		$("#history_loading").css("display","block");
		$("#history_container").css("display","none");

		$.ajax({
			url: this.historyUrl,
			type: "post",
			data: {
					tabTable	: tab,	
					field		: columnName,	
					rowData		: submitRowData, 
					limit		: limit,
					filters		: actions.getFilterValues()
				},
			success:function(data){
				$("#history_container").css("display","block");
				$("#history_loading").css("display","none");
				console.log ( "extensionHandle success : "/* +JSON.stringify(data) */);
				historyDataSet = [];
				list = '';
				selectHistoryValue = function(newValue){
					$('#boxHistory').dialog('close');
//					if(notLocked) successFunction(null,newValue);
// 					actions.putModifiedData(tab,columnName,newValue,rowData);
				};
				
				var avg=0, min=false, max=false, median=0, count = 0, numbers=[];
				$.each(data.history.dataSet, function( index, value ) {
//					value['y'] = parseFloat(value['VALUE']);
					day	= parseChartDate(value['OCCUR_DATE']);
					vl = parseFloat(value['VALUE']);
					historyDataSet.push([day.data, vl]);
					list+="<a href='javascript:selectHistoryValue("+vl+")'>"+day.display+" : "+vl+"</a><br>";
					count++;
					avg+=vl;
					max = (max===false?vl:(vl>max?vl:max));
					min = (min===false?vl:(vl<min?vl:min));
					numbers.push(vl);
	            });
				
				numbers.sort();
				if (count % 2 === 0)
					median = (numbers[count / 2 - 1] + numbers[count / 2]) / 2;
				else
					median = numbers[(count - 1) / 2];

				avg = (avg/count).toFixed(2);
				list += "<br>Average: <a href='javascript:selectHistoryValue("+avg+")'>" + avg +"</a>"
					+ "<br>Max: <a href='javascript:selectHistoryValue("+max+")'>" + max +"</a>"
					+ "<br>Min: <a href='javascript:selectHistoryValue("+min+")'>" + min +"</a>"
					+ "<br>Median: <a href='javascript:selectHistoryValue("+median+")'>" + median +"</a>"
				;
				
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
//				                year: '%b'
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
			},
			error: function(data) {
				console.log ( "extensionHandle error: "/*+JSON.stringify(data)*/);
				$("#history_loading").html("not availble");

			}
		});
		
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

<div id="boxHistory" style="display:none;">
	<div id="historyContent" style="width:100%;border:none;height: 100%; margin-top: 0">
		<div style="position:absolute;left:230px;top:10px;z-index:100">
		Chart type <select id="cboChartType" onchange="changeChartType($(this).val())" style="width:100px"><option value="line">Line</option><option value="spline">Curved line</option><option value="column">Column</option><option value="area">Area</option><option value="areaspline">Curved Area</option></select>
		<!--
		  <div id="radio">
		    <input type="radio" id="radio1" name="radio" value="line" checked="checked"><label for="radio1"><img src='../img/chart-line.png' height=16></label>
		    <input type="radio" id="radio2" name="radio" value="area"><label for="radio2"><img src='../img/chart-area.png' height=16></label>
		    <input type="radio" id="radio3" name="radio" value="column"><label for="radio3"><img src='../img/chart-bar.png' height=16></label>
		  </div>
		  -->
		</div>
		<table>
		<tr><td width=200 valign='top' style="border-right:1px solid #dddddd"><b>Last <select id="cboLimit" onchange="limitChange($(this).val())">
		<option>5</option>
		<option selected>10</option>
		<option>15</option>
		<option>20</option>
		<option>30</option>
		<option>50</option>
		</select> values:</b><br><br><div id="history_list" style="max-height:230px;overflow:auto"></div></td>
		<td><div id="history_container" style="width: 650px; height: 300px; margin: 0 auto"></div></td></tr>
		</table>
	</div>
	<div id="history_loading">Loading...</div>
	
</div>
