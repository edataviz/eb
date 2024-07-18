@section('table_status')
<script>
function addDefaultOptionTo(elementId){
	var option = renderDependenceHtml(elementId,{ID:0,NAME:"All"});
	$('#'+elementId).prepend(option);
	$('#'+elementId).val(0);
}
	
$(document).ready(function() {
	addDefaultOptionTo("Facility");
});

function onAfterGotDependences(elementId,element,currentId){
   if(elementId.indexOf("Facility") !== -1){
	   addDefaultOptionTo(elementId);
   }
}

var selfActions = {{$selfAction}};
selfActions.loadChart = function () {
	var chart;
	var tables = $("#ObjectDataSource").val();
	if(tables == null)
	{
		alert("Please select data table");
		return;
	}
    var param = actions.loadParams(true);
	var dateFrom = $('#date_begin').val();
	var dateTo = $('#date_end').val();
	var param2 = {
		'tables' : tables,
		'facility_id': $('#Facility').val(),
		'LoArea': $('#LoArea').val(),
		'DATE_FROM' : dateFrom,
		'DATE_TO' : dateTo,
	}
    jQuery.extend(param, param2);
	showWaiting();
	$.get('/recordstatussummarydata', param, function(data){
	    var cate = [];
	    var sum = [];
	    $.each(data.A, function( i, v ) {
		    sum.push(v[1] + data.V[i][1]+ data.P[i][1]);
		});
	    
		hideWaiting();
		if(!chart) {
			Highcharts.setOptions({
				global: {
					useUTC: false
				}
			});
			var option	= {
				chart: {
					zoomType: 'x',
					renderTo : 'CheckStatus',
				},
				title: {
					text: '',
					style: {
						fontSize: '10pt'
					}
				},
				subtitle: {
					text: document.ontouchstart === undefined ?
						'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
				},
				xAxis: {
					"type": "category"
				},
				/* yAxis: {
					title: {
						text: '%'
					},
				}, */
				legend: {
					enabled: true
				},
				plotOptions: {
					/*column: {
						stacking: 'normal'
					},*/
					line: {
						allowPointSelect: false,
						dataLabels: { enabled: false }
					},
					series: {
				            shadow:false,
				            borderWidth:0,
				            dataLabels:{
				                enabled:true,
				                formatter:function() {
					                var percent = this.y/sum[this.point.index]*100;
					                if(percent>0) return Highcharts.numberFormat(percent) + '%';
					                return '';
				                }
				            }
				        }
				},
				series: [
					{
						type: 'column',
						name: 'Provisional',
						data: []
					},
					{
						type: 'column',
						name: 'Validated',
						data: []
					},
                    {
                        type: 'column',
                        name: 'Approved',
                        data: []
                    },
					{
						type: 'line',
						name: 'Locked',
						color: 'red',
						marker:{enabled:false},
						lineWidth: 5,
						enableMouseTracking: false,
						data: []
					},
				]
			};
			chart = new Highcharts.Chart(option);
		}
		chart.setTitle({text: tables.join(", ")});

		chart.series[0].setData(data.P);
		if(data.P.length>0) chart.series[0].show();
		else chart.series[0].hide();

		chart.series[1].setData(data.V);
		if(data.V.length>0) chart.series[1].show();
		else chart.series[1].hide();

		chart.series[2].setData(data.A);
		if(data.A.length>0) chart.series[2].show();
		else chart.series[2].hide();
	});
};
</script>
@stop