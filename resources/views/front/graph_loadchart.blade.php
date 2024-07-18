<?php 
$enableHeader		= false;
$enableFooter		= false;
$timebase			= isset($timebase)?$timebase:$sampleInterval*$defaultNumber;
$sampleInterval		= isset($sampleInterval)?$sampleInterval:$timebase/$defaultNumber;
$maxOfPoint			= $timebase/$sampleInterval;
$lastDate			= isset($lastDate)?$lastDate:'';
?>

@extends('core.bstemplate',['subMenus' => array('pairs' => [], 'currentSubMenu' => [])])
@section('main')
	<div id="container" style="min-width: 400px; height: 380px; margin: 0 auto"></div>
@stop

@section('script')
<script src="/common/js/highcharts6.js?5"></script>
<script src="/common/js/exporting.js?7"></script>
<script src="/common/js/export-csv.js?9"></script>
<script type='text/javascript'>
    function getRealtimeData(chart,lastValue){
        if(chart.options.realtimeTags.length > 0){
            $.ajax({
                url			: '/getrealtimedata',
                type		: "post",
                data		: {	tags			: chart.options.realtimeTags,
                    lastValue 		: lastValue,
                    timebase 		: {{$timebase}},
                    sampleInterval 	: {{$sampleInterval}},
                    lastDate 		: '{{$lastDate}}',
                },
                success		: function(data){
                    data.results.forEach(function(tag){
                        if(tag.index < chart.series.length){
                            var series;
                            var shift;
                            var mm;
                            tag.addition.forEach(function(item){
                                series = chart.series[tag.index];
                                shift = series.data.length > {{$maxOfPoint}};
                                mm	= moment(item.time,configuration.time.DATETIME_FORMAT_UTC);
                                x	= mm.unix()*1000;
                                /* var startMoment = moment().subtract({{$timebase}}/1000, 'seconds');
									var shift = mm.isBefore(startMoment); */
                                if(series.data.length > 0){
                                    if(series.data[series.data.length-1].x == x){
                                        series.data[series.data.length-1].update(item.value);
                                        return;
                                    }
                                }
                                series.addPoint({x:x, y:item.value,unit:item.unit}, false, shift);
                            });
// 								chart.tooltip.options.pointFormat = '{point.x:%e. %b %H:%M:%S}: {point.y:.8f} '+unit;

                            chart.tooltip.options.formatter = function() {
                                var xyArr=[];
                                xyArr.push('<b> ' + this.series.name + '</b><br>'+
                                    ( Highcharts.dateFormat('%e-%b-%y %H:%M:%S',new Date(this.x))) +
                                    ' <b> '+ this.y+' </b> ' +' '+this.point.unit );
                                return xyArr.join('<br/>');
                            }

                            chart.redraw();
                        }
                    });
                },
                complete	: function(data) {
					var lastDateValue = '{{$lastDate}}';
					var lastDate	= moment(lastDateValue,configuration.time.DATE_FORMAT+" "+configuration.time.TIME_FORMAT);
					if (lastDate.isAfter(moment().subtract(5, 'minutes'))){
						setTimeout(function(){getRealtimeData(chart,1);}, {{$sampleInterval}});
					}
                }
            });
        }
    }
    $(document).ready(function () {
        Highcharts.setOptions({
            global: {
                useUTC: false
            }
        });
        var option	= {
            chart: {
                renderTo: 'container',
                zoomType: 'xy',
                events: {
                    load: function () {
                        getRealtimeData(this,0);
                    }
                }
            },
            realtimeTags: [<?php echo $realtimeTagsArray; ?>],
            credits: false,
            title: {
                text: <?php echo ($title?"'$title'":"null"); ?>
            },
            subtitle: {
                text: null
            },
            xAxis: {
                type: 'datetime',
				gridLineWidth: 1,
                dateTimeLabelFormats: { // don't display the dummy year
                    month: '%e. %b',
                    year: '%b'
                },
                title: {
                    text: 'Occur date'
                }
            },
            legend: {
                enabled: <?php echo ($nolegend?"false":"true"); ?>
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
                pointFormat: '{point.x:%e. %b %H:%M:%S}: {point.y:.8f} '
            },

            plotOptions: {
				{!!$stackedColumnOption!!}
                spline: {
                    marker: {
                        enabled: true
                    }
                },
                pie: {
                    dataLabels: {
                        //	                    distance: -50,
                        format: '{point.name}: {point.y:.3f}'
                    },
                    tooltip: {
                        headerFormat: '',
                        pointFormat: '{point.name}: {point.y:.3f}'
                    },
                }
            },
            series: [
                <?php echo preg_replace('/_@/', ' ',$series);?>
            ],
        };

				@if($no_yaxis_config)
        var yAxis = [{ // Primary yAxis
                <?php echo ((!$min1&&!$max1)||$stackedColumnOption?"":"min: $min1, max: $max1,");?>
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

            },
                { // Secondary yAxis
                    <?php echo ((!$min2&&!$max2)||$stackedColumnOption?"":"min: $min2, max: $max2,");?>
                    labels: {
                        format: '{value}',
                        style: {
                            color: Highcharts.getOptions().colors[0]
                        }
                    },
                    title: {
                        text: '',
                        style: {
                            color: 'green'
                        }
                    },
                    opposite: true

                }
            ];
				@else
        var yAxis = <?php echo json_encode($yaxis); ?>
						@endif

                option.yAxis = yAxis;
        chart = new Highcharts.Chart(option);
// 	    $('#container').highcharts(option);



        function exportTableToCSV(filename) {
            var  csv = '"aaa,bbb,ccc"';
            if (window.navigator.msSaveBlob) {
                var blob = new Blob([decodeURIComponent(csv)], {
                    type: 'text/csv;charset=utf8'
                });

                // Crashes in IE 10, IE 11 and Microsoft Edge
                // See MS Edge Issue #10396033: https://goo.gl/AEiSjJ
                // Hence, the deliberate 'false'
                // This is here just for completeness
                // Remove the 'false' at your own risk
                window.navigator.msSaveBlob(blob, filename);

            } else if (window.Blob && window.URL) {
                // HTML5 Blob
                var blob = new Blob([csv], { type: 'text/csv;charset=utf8' });
                var csvUrl = URL.createObjectURL(blob);
                $(this).attr({'download': filename,
                    'href': csvUrl
                });
            } else {
                // Data URI
                var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
                $(this).attr({'download': filename,
                    'href': csvData,
                    'target': '_blank'
                });
            }
        }

        // This must be a hyperlink
        $(".export").on('click', function (event) {
            exportTableToCSV('export.csv');
        });
    });

</script>
<script src="/common/js/moment.js"></script>
@stop
