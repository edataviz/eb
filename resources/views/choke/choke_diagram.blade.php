<div id="diagramContainer" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
@section("renderChart")
<script type="text/javascript">
	var chartParameter = {url	: "/choke/summary"};
	
	editBox.requestGenDiagram = function (constraintPostData,container,isShowWaiting,successFn){
		container			= typeof container == "object" && container!=null? container:$('#diagramContainer');
		if(isShowWaiting)	showWaiting();
		container.html("");
		$.ajax({
			url			: chartParameter.url,
			type		: "post",
			data		: constraintPostData,
			success		: function(data){
				if(isShowWaiting)	hideWaiting();
				console.log ( "requestGenDiagram success ");
				editBox.genDiagram(data.diagram,container);
				if(typeof successFn == 'function') successFn(data);
			},
			error		: function(data) {
				if(isShowWaiting)	hideWaiting();
				var link = $("<a >error when generate diagram</a>");
				link.appendTo(container);
				var errorBox = $("<div>");
				errorBox.html(data.responseText);
				errorBox.appendTo(container);
				errorBox.hide();
				link.click(function(e){
					if(errorBox.is(':visible')) errorBox.hide();
					else errorBox.show();
				});
				console.log ( "requestGenDiagram error "/* +JSON.stringify(data) */);
// 				container.html("<a >error when generate diagram</a>");
				if(typeof onGenDiagramError == 'function') onGenDiagramError(data);
			}
		});
	}

	editBox.genDiagram = function (diagram,view){
		if(typeof diagram == "undefined") return;
		var series 		= [];
		var groupIndex	= 0;
		var serieGroup;
		for (var group in diagram.series) {
			serieGroup	= diagram.series[group];
			groupIndex = diagram.groups.indexOf(group);
			for (var category in serieGroup) {
				serie		= serieGroup[category];
				serie.type	= 'column';
// 				serie.name	+= ' LIP';
				for(var i = 0; i< groupIndex;i++){
					serie.data.unshift(0);
				}
				series.push(serie);
			}
		}
		
		if(diagram.minY>0){
			var lineData = Array.apply(null, Array(diagram.groups.length)).map(function (_, i) {return diagram.minY;});
			series.push({
				type: 'line',
				color: 'red',
				name: 'LMPP',
				lineWidth: 2,
				showInLegend:false,
				marker: {enabled: false},
				states: {hover: {enabled: false}},
				tooltip: {enabled: false,pointFormat: '{point.y:.2f}'},
				data: lineData,
			});
		}	
		var diagramOption	= {
				chart: {
		            zoomType		: 'xy',
		            backgroundColor	: diagram.bgcolor,
		        },
				credits: false,
		        title: {
		            text: diagram.title,
					style: {
						fontWeight:"bold"
					}
		        },
		        subtitle: {
		            text: null
		        },
		        tooltip: {
		            headerFormat: '<b>{series.name}</b><br>',
		            pointFormat: '{point.x:%e. %b}: {point.y:.2f}'
		        },
		        exporting: {
		            sourceWidth: view.width(),
		            sourceHeight:view.height(),
		            scale: 1,
		            chartOptions: {
		                subtitle: null
		            }
		        },
				plotOptions: {
		            column: {
						stacking: 'normal',
		                pointPadding: 0.2,
		                borderWidth: 0
		            }
        		},
        		series: series,
        		xAxis: {
                    categories:  diagram.groups,
                    crosshair: false
                },
                yAxis: { // Primary yAxis
                    labels: {
                        format: '{value}',
                    },
                    title: {
                        text: diagram.ycaption,
                        style: {
                            fontWeight:"bold"
                        }
                    },
                    opposite: false,
        			endOnTick: false,
                }
        };
		view.highcharts(diagramOption);
	}
	
</script>
@stop

@yield("renderChart")
