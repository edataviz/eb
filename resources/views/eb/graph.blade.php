@extends('eb.base')

@section('head')
@parent
<link rel="stylesheet" href="css/graph.css">
<link rel="stylesheet" href="lib/colorpicker/wheelcolorpicker.css">
<link rel="stylesheet" href="lib/semantic-ui/ext/range.css">
<link rel="stylesheet" href="css/config-expression.css">

<script src="lib/highcharts/highcharts.js"></script>
<script src="lib/colorpicker/jquery.wheelcolorpicker.js"></script>
<script src="lib/semantic-ui/ext/range.js"></script>
<script src="js/config-expression.js"></script>
@stop

@section('main')
<div btn-show-chart-settings></div>
<div id="chart-container"></div>
<div config class="ui form">

<div class="ui pointing secondary menu chart-config">
    <a class="item active" data-tab="chart">Chart</a>
    <a class="item" data-tab="items">Items</a>
</div>
<div class="ui tab segment config-items" data-tab="items">
<button add-item>Add chart item</button>
<div items-container>
</div>
<div box-edit-item>
    <div class="ui header">Edit chart item</div>
    <button btn-config-exp>Config</button>
    <div class="field">
        <label>Data expression &nbsp;| <a onclick="randomData()">Random data</a></label>
        <textarea name="item-expression" rows="2"></textarea>
    </div>
    <div class="fields">
        <div class="field twelve wide">
            <label>Title</label>
            <input type="text" name="title" placeholder="Title">
        </div>
        <div class="field four wide color item-color">
            <label>Color</label>
            <input type="text" name="title-color" readonly placeholder="Auto" data-wheelcolorpicker="" data-wcp-sliders="wv" data-wcp-preview="true">
        </div>
    </div>
    <div class="two fields">
        <div class="field">
            <label>Type</label>
            <select class="ui dropdown select-item-type">
                <option value="line">Line</option><option value="spline">Curved line</option><option value="column">Column</option><option value="area">Area</option><option value="areaspline">Curved Area</option><option value="pie">Pie</option>
            </select>
        </div>
        <div class="field">
            <label>Dash</label>
            <select class="ui dropdown select-item-dash-type">
            <option value='0'>None</option>
            <option value='Dot'>Dot</option>
            <option value='Dash'>Dash</option>
            <option value='LongDash'>LongDash</option>
            <option value='DashDot'>DashDot</option>
            </select>
        </div>
    </div>
    <div class="fields">
        <div class="field eight wide">
            <label>Y-axis title</label>
            <input type="text" name="item-yaxis-title">
        </div>
        <div class="field four wide">
            <label>Position</label>
            <select class="ui dropdown select-yaxis-position">
                <option value="0">Left</option>
                <option value="1">Right</option>
            </select>
        </div>
        <div class="field four wide color item-color">
            <label>Color</label>
            <input type="text" name="item-yaxis-color" readonly placeholder="Auto" data-wheelcolorpicker="" data-wcp-sliders="wv" data-wcp-preview="true">
        </div>
    </div>
    <div class="fields">
        <div class="field five wide item-marker">
            <label>Marker</label>
            <select class="ui dropdown select-marker">
                <option value='0'>No</option>
                <option value='1'>Point</option>
                <option value='2'>Value</option>
            </select>
        </div>
        <div class="field five wide">
            <label title="Suffix of the tooltip value">Suffix</label>
            <input type="text" name="item-value-suffix">
        </div>
    </div>
</div>
</div>

<div class="ui tab active segment config-chart" data-tab="chart">
    <div class="fields">
        <div class="field twelve wide">
            <label>Title</label>
            <input type="text" name="chart-title" placeholder="Title">
        </div>
        <div class="field four wide color chart-title-color">
            <label>Color</label>
            <input type="text" name="chart-title-color" readonly placeholder="Auto" data-wheelcolorpicker="" data-wcp-sliders="wv" data-wcp-preview="true">
        </div>
    </div>
    <div class="fields">
        <div class="field twelve wide">
            <label>Subtitle</label>
            <input type="text" name="sub-title" placeholder="Title">
        </div>
        <div class="field four wide color sub-title-color">
            <label>Color</label>
            <input type="text" name="sub-title-color" readonly placeholder="Auto" data-wheelcolorpicker="" data-wcp-sliders="wv" data-wcp-preview="true">
        </div>
    </div>
    <div class="fields">
        <div class="field twelve wide">
            <label>X-axis title</label>
            <input type="text" name="xasix-title" placeholder="Title">
        </div>
        <div class="field four wide color xaxis-color">
            <label>Color</label>
            <input type="text" name="xaxis-color" readonly placeholder="Auto" data-wheelcolorpicker="" data-wcp-sliders="wv" data-wcp-preview="true">
        </div>
    </div>
    <div class="fields">
        <div class="field eight wide">
            <label>Y-axis title</label>
            <input type="text" name="yasix-title" placeholder="Title">
        </div>
        <div class="field four wide">
            <label>Position</label>
            <select class="ui dropdown select-chart-yaxis-position">
                <option value="0">Left</option>
                <option value="1">Right</option>
            </select>
        </div>
        <div class="field four wide color">
            <label>Color</label>
            <input type="text" name="yaxis-color" readonly placeholder="Auto" data-wheelcolorpicker="" data-wcp-sliders="wv" data-wcp-preview="true">
        </div>
    </div>
    <div class="fields">
        <div class="field nine wide">
            <label>Time range</label>
            <div class="field box-time-range">
                <input type="number" value="10" name="time-range-amount" placeholder="Amount">
            </div>
            <select class="ui dropdown select-time-range-unit">
            <option value="minute">minute</option><option value="hour">hour</option><option value="day" selected>day</option><option value="week">week</option><option value="month">month</option><option value="year">year</option>
            </select>
        </div>
        <div class="field seven wide">
            <label>Auto refresh</label>
            <select class="ui dropdown select-auto-refresh">
            <option value="0">Disabled</option><option value="5000">5 seconds</option><option value="10000">10 seconds</option><option value="20000">20 seconds</option><option value="30000">30 seconds</option><option value="60000">1 minute</option><option value="120000">2 minutes</option><option value="300000">5 minutes</option><option value="600000">10 minutes</option><option value="1800000">30 minutes</option><option value="3600000">1 hour</option><option value="10800000">3 hours</option><option value="21600000">6 hours</option><option value="43200000">12 hours</option><option value="86400000">1 day</option>
            </select>
        </div>
    </div>
    <br>
    <div class="field">
      <div class="ui toggle checkbox checkbox-column-stacking">
        <input type="checkbox" name="column-stacking" tabindex="0" class="hidden">
        <label>Columns stacking</label>
      </div>
    </div>
    <div class="field">
      <div class="ui toggle checkbox checkbox-show-gridline">
        <input type="checkbox" name="show-gridline" tabindex="0" class="hidden">
        <label>Vertical grid lines</label>
      </div>
    </div>
    <div class="field">
      <div class="ui toggle checkbox checkbox-show-legend">
        <input type="checkbox" name="show-legend" tabindex="0" class="hidden">
        <label>Legend</label>
      </div>
    </div>
    <br>
    <div class="field">
        <label>Size</label>
        <div class="ui grey range" id="range-chart-size"></div>
    </div>
</div>
@stop

@section('script')
@parent
<script>
EB.screenCode = 'VIS_ADVGRAPH';
const screenConfig = {
	title: 'Advance Graph',
    date: {
        css: {'margin-left': 60},
        value: '{!! $date !!}'//Utils.getCookie('graph-date'),
    },
    filters: [
        {
			id: 'chartGroup',
			title: 'Group',
            list: {!! json_encode($chartGroups) !!},
            onChange: function(value, text, $item){
                loadChartsList(value);
            },
        },
        {
			id: 'chart',
            title: 'Chart',
            class: 'select-chart',
            defaultText: 'Please select...',
            list: {!! json_encode($charts) !!},
            onChange: function(value, text, $item){
                if(currentChartID == value) return;
                if($item){
                    if(loadChart(screenConfig.filters[1].list.items[$item.index()].config)){
                        currentChartID = value;
                    } else {
                        $('.select-chart').dropdown('set selected', currentChartID);
                    }
                }
            },
        },
    ],
    buttons: [
        {
            content: 'New',
            tooltip: 'Create new chart',
            click: function(){
                newChart();
            }
        },
        {
            content: 'Save',
            tooltip: 'Save current chart',
            click: function(){
                saveChart();
            }
        },
        {
            content: 'Save as',
            tooltip: 'Save current chart as new',
            click: function(){
                saveChart(true);
            }
        },
        {
            content: 'Delete',
            tooltip: 'Delete current chart',
            click: function(){
                deleteChart();
            }
        },
        {
            content: 'Refresh chart',
            tooltip: 'Draw chart with refreshing data',
			css: {'margin-left': 60},
            click: function(){
                loadGraphData();
            }
        },
    ],
}
 
EB.buildScreen(screenConfig);

$('.ui.checkbox').checkbox();
$('.chart-config .item').tab();
$('select.dropdown').dropdown();

function loadGraphData(){
    //Utils.setCookie('graph-date', $('#filter-date').dateString());
    const expressions = {};
    chartSettings.items.forEach(item => {
        item && item.expression && (expressions[item.index] = item.expression);
    });
    const dateString = $('#filter-date').dateString();
    !jQuery.isEmptyObject(expressions) && 
        sendAjax('/load-graph-data', {
            date: dateString,
            valueExpressions: null,
            chartExpressions: [{
                dateRange: EB.getDateRange($('#filter-date').date(), chartSettings.timeRange, chartSettings.timeRangeUnit, true) + ',' + dateString, 
                expressions: expressions
            }]
        }, function(data){
			if(data && data.chartData && data.chartData.length && data.chartData[0]){
				for (var index in chartSeries) {
					if (Object.prototype.hasOwnProperty.call(chartSeries, index) && data.chartData[0][index]) {
						chartSeries[index].setData(data.chartData[0][index]);
					}
				}
			}
            chartSettings.autoRefresh > 0 && setTimeout(getRealtimeData, chartSettings.autoRefresh);
        }, function(){
            alert('Error');
        }, function(){
        });
}

function updateChartListItems(items){
    screenConfig.filters[1].list.items = items;
    $('.select-chart').dropdown('change values', items);
}

function loadChartsList(groupId){
    updateChartListItems([]);
    sendAjax('/load-chart-list', {groupId: groupId}, function(data){
        updateChartListItems(data.items);
    }, function(){
        alert('Error');
    });
}

function newChart(){
    //currentChartID = null;
    //chartSettings = null;
    loadChart(null) && $('.select-chart').dropdown('clear');
}

function deleteChart(){
    if(!currentChartID) return;
    if(!confirm("Do you want to delete this chart?")) return;
    param = {
            'ID' : currentChartID
    };
    sendAjax('/deleteChart', param, function(data){
        $('.select-chart').dropdown('get item', currentChartID).remove();
        $('.select-chart').dropdown('clear');
        currentChartID = null;
        newChart();
    }, function(){
        alert('Error');
    });    
}

var savedChartSettings = "null";
function isChartNotSaved(){
    return JSON.stringify(chartSettings)!=savedChartSettings;
}
function resetChartSavedStatus(){
    savedChartSettings = JSON.stringify(chartSettings);
}

function saveChart(isAddNew){
    var title = chartSettings.title;
    var oldTitle = title;
    if(title == ""){
        alert("Please input chart's title");
        $("#chartTitle").focus();
        return;
    }
    if(isAddNew == true)
    {
        title = prompt("Please input chart's title",title);
        title = title.trim();
        if(title == "") return;
        chartSettings.title = title;
    }
    var config = JSON.stringify(chartSettings);
    if(config == ""){alert("Chart's settings is not ready");return;}

    param = {
            'id': (isAddNew || !currentChartID ? -1 : currentChartID),
            'title': title,
            'group': $('#chartGroup').val(),
            'config': config
    };
    sendAjax('/saveChart', param, function(data){
        if(data.substr(0,3)=="ok:")
        {
            alert("Chart saved successfully");
            resetChartSavedStatus();
            currentChartID=data.substr(3);
            $('[name="chart-title"]').val(chartSettings.title).blur();
            let item = $('.select-chart').dropdown('get item', currentChartID);
            if(!item){
                screenConfig.filters[1].list.items.push({
                    value: currentChartID,
                    name: chartSettings.title,
                    config: config,
                    selected: true,
                });
                $('.select-chart').dropdown('change values', screenConfig.filters[1].list.items);
                //item = $('.select-chart').dropdown('get item', currentChartID);
            }
            else
                screenConfig.filters[1].list.items[$(item).index()].config = config;
        }
        else{
            alert(data);
        }
    }, function(){
        chartSettings.title = oldTitle;
        alert('error');
    });    
    /*
    var o={};
    for(var x in chartSettings){
        if(x=='items'){
            o[x] = [];
            chartSettings[x].forEach(item => {
                var xo = {};
                for(var i in item)
                    i != 'seri' && (xo[i] = item[i]);
                o[x].push(xo);
            });
        }
        else
            o[x] = chartSettings[x];
    }
    console.log(JSON.stringify(o));
    */
}

let chart;
const baseConfig = {
    chart: {
        zoomType: 'xy',
        events: {
            load: function (){
                $('.highcharts-title').click(function(){
                    //alert($(this).text());
                });
                $('.highcharts-axis-title').click(function(){
                    alert($(this).text());
                console.log(atob(btoa(JSON.stringify($('#container').highcharts().userOptions))));
                });
            },
            redraw: function () {
                updateChartItemsColor();
            },
        }
    },
    credits: {
        enabled: false
    },
    title: {
        text: 'Chart title'
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        type: 'datetime',
        title: {text: ''},
        gridLineWidth: 0,
    },
    yAxis: [{title:{text:''}},],
    series: [],
    plotOptions: {
        pie: {
            dataLabels: {
                formatter: function(){
                    return this.point.options.name ? this.point.name : moment(this.point.x).format(EB.workspace.DATE_FORMAT);
                }
                //format: '{point.name}: {point.y:.3f}'
            },
            tooltip: {
                headerFormat: '',
                pointFormat: '{point.name}: {point.y:.3f}'
            },
        },
        series: {
            point: {
                events: {
                    click: function(e){
                        chartSettings.items.forEach((item, index) => {
                            if(item && chartSeries[index] == e.point.series){
                                $('[chart-item][item-index="'+index+'"]').click();
                                return;
                            }
                        })
                    }
                }
            }
        }
    }
}

let chartTimer;
let lastRealtimeValue = null;
function getRealtimeData(){
    clearTimeout(chartTimer);
    if(buildRealtimeTags()){
        sendAjax('/getrealtimedata',
            {	tags			: chart.options.realtimeTags,
                lastValue 		: 1,
                //timebase 		: 5 * 60 * 1000, //EB.getDateRange($('#filter-date').date(), chartSettings.timeRange, chartSettings.timeRangeUnit) * ,
                //sampleInterval 	: chartSettings.autoRefresh,
                //lastDate 		: null,
            },
            (data) => {
                const rangeLimit = EB.getDateRange($('#filter-date').date(), chartSettings.timeRange, chartSettings.timeRangeUnit) * 864e5;
                data.results.forEach(function(tag){
                    if(tag.index < chart.series.length){
                        const series = chart.series[tag.index];
                        tag.addition.forEach(function(item){
                            if(series.data.length > 0){
                                while(series.data.length > 1 && series.data[0].x <= item.time - rangeLimit)
                                    series.data[0].remove(false);
                                series.data[series.data.length-1].x == item.time ?
                                    series.data[series.data.length-1].update(item.value, false) :
                                    series.addPoint([item.time, item.value], false);
                            }
                            else
                                series.update({data:[[item.time, item.value]]}, false);
                        });
                    }
                });
                chart.redraw();
            },
            null,
            () => {
                chartSettings.autoRefresh > 0 && (chartTimer = setTimeout(getRealtimeData, chartSettings.autoRefresh));
            }
        );
    }
}

function loadChart(settings){
    if(isChartNotSaved()){
        if(!confirm("Current chart is not saved. Do you still want to continue without saving?")){
            return false;
        }
    }

    if(settings == null){//when click button New chart
        chartSettings = {};
        currentChartID = null;
    }

    clearTimeout(chartTimer);
    typeof settings == 'string' && settings.startsWith('{') && (settings = JSON.parse(settings.replaceAll('\\"','"')));
    settings && (chartSettings = settings);
    !chartSettings.title && (chartSettings.title = baseConfig.title.text);
    !chartSettings.timeRange && (chartSettings.timeRange = 10);
    !chartSettings.timeRangeUnit && (chartSettings.timeRangeUnit = 'day');

    let charConfig = $.extend(true, {}, baseConfig);
    chartSettings.title && (charConfig.title.text = chartSettings.title);
    chartSettings.titleColor && (charConfig.title.style = {color: '#' + chartSettings.titleColor});
    chartSettings.subtitle == 'hidden' ? charConfig.title.text = '' : chartSettings.subtitle && (charConfig.subtitle.text = chartSettings.subtitle);
    chartSettings.subtitleColor && (charConfig.subtitle.style = {color: '#' + chartSettings.subtitleColor});
    chartSettings.yAxisTitle == 'hidden' && (charConfig.yAxis[0].visible = false);
    chartSettings.xAxisTitle == 'hidden' ? charConfig.xAxis.visible = false : chartSettings.xAxisTitle && (charConfig.xAxis.title.text = chartSettings.xAxisTitle);
    chartSettings.xAxisColor && $.extend(true, charConfig.xAxis, {title:{style:{color: '#' + chartSettings.xAxisColor}},labels:{style:{color: '#' + chartSettings.xAxisColor}}});// (charConfig.xAxis.title.style = {color: '#' + chartSettings.xAxisColor});
    chartSettings.verticalLines && (charConfig.xAxis.gridLineWidth = 1);
    chartSettings.stacking && (charConfig.plotOptions.column = {stacking: 'normal'});
    chartSettings.legend == false && (charConfig.legend = {enabled: false});

    $.extend(true, charConfig.yAxis[0], {opposite: chartSettings.yAsixPosition == 1, title: {text: chartSettings.yAxisTitle}});
    chartSettings.yAxisColor && $.extend(true, charConfig.yAxis[0], {title:{style:{color: '#' + chartSettings.yAxisColor}},labels:{style:{color: '#' + chartSettings.yAxisColor}}});

    chart && chart.destroy();
    chart = Highcharts.chart('chart-container', charConfig);
    buildItemsList();
    chart.series.length ? $('[box-edit-item]').show() : $('[box-edit-item]').hide();

    $('[name="chart-title"]').val(chartSettings.title);
    $('[name="chart-title-color"]').val(chartSettings.titleColor ? chartSettings.titleColor : '').trigger('change');
    $('[name="sub-title"]').val(chartSettings.subtitle);
    $('[name="sub-title-color"]').val(chartSettings.subtitleColor ? chartSettings.subtitleColor : '').trigger('change');
    $('[name="xasix-title"]').val(chartSettings.xAxisTitle);
    $('[name="xaxis-color"]').val(chartSettings.xAxisColor ? chartSettings.xAxisColor : '').trigger('change');
    $('[name="yasix-title"]').val(chartSettings.yAxisTitle);
    $('[name="yaxis-color"]').val(chartSettings.yAxisColor ? chartSettings.yAxisColor : '').trigger('change');
    $('.select-chart-yaxis-position').dropdown('set selected', chartSettings.yAxisPosition ? chartSettings.yAxisPosition : '0');

    chartSettings.verticalLines ? $('.checkbox-show-gridline').checkbox('set checked') :  $('.checkbox-show-gridline').checkbox('set unchecked');
    chartSettings.legend == false ? $('.checkbox-show-legend').checkbox('set unchecked') :  $('.checkbox-show-legend').checkbox('set checked');
    chartSettings.stacking? $('.checkbox-column-stacking').checkbox('set checked') :  $('.checkbox-column-stacking').checkbox('set unchecked');
    
    $('.select-auto-refresh').dropdown('set selected', chartSettings.autoRefresh ? chartSettings.autoRefresh : '0');
    $('[name="time-range-amount"]').val(chartSettings.timeRange);
    $('.select-time-range-unit').dropdown('set selected', chartSettings.timeRangeUnit ? chartSettings.timeRangeUnit : 'day');
    
    chartSettings.autoRefresh > 0 && setTimeout(getRealtimeData, chartSettings.autoRefresh);
    resetChartSavedStatus();
    return true;
}

$("[items-container]").sortable({stop: function() {
    updateChartItemsZIndex();
}});

$('#range-chart-size').range({
    min: 40,
    max: 100,
    start: 100,
    onChange: function(value) {
        $('#chart-container').css('height', value + '%');
        chart && chart.reflow();
    }
});

$('[btn-show-chart-settings]').click(function(){
    $('[main]').attr('hide-setting') ? $('[main]').removeAttr('hide-setting') : $('[main]').attr('hide-setting', '1');
    chart && chart.reflow();
});

$('.menu.chart-config>.item').click(function(){
    $('[main]').attr('hide-setting') && $('[btn-show-chart-settings]').click();
});

let currentChartID = {{ $chartId }};
let currentItemIndex;
let chartSeries = {};
/*
{
    items: [
        {
            name: 'Chart item 01',
            expression: 'exp1',
            type: 'line',
            color: 'ff6600',
            zIndex: 1,
        },
        {
            name: 'Chart item 02',
            expression: 'exp2',
            type: 'column',
            zIndex: 2,
        },
    ],
    title: 'Title!!!!!!',
    titleColor: 'ff0000',
    subtitle: 'subbb',
    subtitleColor: '00ff00',
    xAxisTitle: 'occur date',
    yAxisTitle: 'Volume',
    yAxisColor: '0000ff',
    verticalLines: false,
    legend: true,
    autoRefresh: 0,
    timeRange: 7,
    timeRangeUnit: 'min',
};
*/

$('[name="title-color"]').on('change', function(){
    var color = $(this).val();
    color ? (color = '#' + color) : (color = null);
    if(chartSeries[currentItemIndex].options.color != color){
        //chart.series[currentItemIndex].options.color = color;
        chartSeries[currentItemIndex].update({color: color});//chart.series[currentItemIndex].options);
        color ? chartSettings.items[currentItemIndex].color = $(this).val() : delete chartSettings.items[currentItemIndex].color;
        //$('[chart-item][item-index="'+currentItemIndex+'"] [item-title]').css('color', color);
    }
})

$('[name="item-yaxis-color"]').on('change', function(){
    var color = $(this).val();
    color ? (color = '#' + color) : (color = null);
    if(chartSeries[currentItemIndex].yAxis && chartSeries[currentItemIndex].yAxis.options.title.style.color != color){
        chartSeries[currentItemIndex].yAxis.update({title:{style:{color: color}},labels:{style:{color: color}}});
        color ? chartSettings.items[currentItemIndex].yAxisColor = $(this).val() : delete chartSettings.items[currentItemIndex].yAxisColor;
        updateYAxis();
    }
})

$('[name="chart-title-color"]').on('change', function(){
    var color = $(this).val();
    color ? (color = '#' + color) : (color = null);
    if(chart.title.styles.color != color){
        chart.update({title:{style:{color:color}}});
        color ? chartSettings.titleColor = $(this).val() : delete chartSettings.titleColor;
    }
})

$('[name="sub-title-color"]').on('change', function(){
    var color = $(this).val();
    color ? (color = '#' + color) : (color = null);
    if(chart.subtitle.styles.color != color){
        chart.update({subtitle:{style:{color:color}}});
        color ? chartSettings.subtitleColor = $(this).val() : delete chartSettings.subtitleColor;
    }
})

$('[name="xaxis-color"]').on('change', function(){
    var color = $(this).val();
    color ? (color = '#' + color) : (color = null);
    if(chart.xAxis[0].options.title.style.color != color){
        chart.xAxis[0].update({title:{style:{color: color}},labels:{style:{color:color}}});
        color ? chartSettings.xAxisColor = $(this).val() : delete chartSettings.xAxisColor;
    }
})

$('[name="yaxis-color"]').on('change', function(){
    var color = $(this).val();
    color ? (color = '#' + color) : (color = null);
    if(chart.yAxis[0].options.title.style.color != color){
        color ? chartSettings.yAxisColor = $(this).val() : delete chartSettings.yAxisColor;
        chart.yAxis[0].update({title:{style:{color: color}},labels:{style:{color:color}}});
        updateYAxis();
    }
})

$('[name="chart-title"]').blur(function(){
    var name = $(this).val();
    chartSettings.title = name;
    chart.update({title: {text: chartSettings.subtitle == 'hidden' ? '': name}});
});

$('[name="sub-title"]').blur(function(){
    var name = $(this).val();
    chartSettings.subtitle = name;
    chart.update(chartSettings.subtitle == 'hidden' ? {title: {text: ''}, subtitle: {text: ''}} : {title: {text: chartSettings.title}, subtitle: {text: name}});
});

$('[name="xasix-title"]').blur(function(){
    var name = $(this).val();
    chartSettings.xAxisTitle = name;
    chart.xAxis[0].update(name == 'hidden' ? {visible: false, title:{text:''}} : {visible: true, title:{text:name}});
});

$('[name="yasix-title"]').blur(function(){
    var name = $(this).val();
    chartSettings.yAxisTitle = name;
    //chart.yAxis[0].update({title:{text:name}});
    chart.yAxis[0].update(name == 'hidden' ? {visible: false, title:{text:''}} : {visible: true, title:{text:name}});
});

$('[name="show-gridline"]').on('change', function(){
    chartSettings.verticalLines = $(this).is(':checked');
    chart.xAxis[0].update({gridLineWidth: chartSettings.verticalLines ? 1 : 0});
});

$('[name="column-stacking"]').on('change', function(){
    chartSettings.stacking = $(this).is(':checked');
    chart.update({plotOptions: {column: {stacking: chartSettings.stacking ? 'normal' : undefined}}});
});

$('[name="show-legend"]').on('change', function(){
    $(this).is(':checked') ? delete chartSettings.legend : chartSettings.legend = false;
    chart.update({legend:{enabled: $(this).is(':checked')}});
});

$('[name="title"]').blur(function(){
    var name = $(this).val().trim();
    if(name){
        chartSettings.items[currentItemIndex].name = name;
        $('[chart-item][item-index="'+currentItemIndex+'"] [item-title]').html(name);
        chartSeries[currentItemIndex].update({name:name}, true);
    }
    else {
        var self = this;
        setTimeout(function() { self.focus(); }, 10);
    }
});

$('[name="item-value-suffix"]').blur(function(){
    var name = $(this).val();
    chartSettings.items[currentItemIndex].valueSuffix = name;
    chartSeries[currentItemIndex].update({tooltip: {valueSuffix: name}}, true);
});

$('[name="item-yaxis-title"]').blur(function(){
    var name = $(this).val();
    chartSettings.items[currentItemIndex].axisTitle = name;
    updateYAxis();
});

$('[name="time-range-amount"]').blur(function(){
    var name = $(this).val();
    chartSettings.timeRange = name;
});

$('[name="item-expression"]').blur(function(){
    var name = $(this).val();
    chartSettings.items[currentItemIndex].expression = name;
});

$('.select-item-type').dropdown('setting', 'onChange', function(val){
    val ? chartSettings.items[currentItemIndex].type = val : delete chartSettings.items[currentItemIndex].type;
    chartSeries[currentItemIndex].update({type: val}, true);
});

$('.select-item-dash-type').dropdown('setting', 'onChange', function(val){
    val ? chartSettings.items[currentItemIndex].dashStyle = val : delete chartSettings.items[currentItemIndex].dashStyle;
    chartSeries[currentItemIndex].update({dashStyle: val}, true);
});

$('.select-marker').dropdown('setting', 'onChange', function(val){
    val != 1 ? chartSettings.items[currentItemIndex].marker = val : delete chartSettings.items[currentItemIndex].marker;
    chartSeries[currentItemIndex].update({marker: {enabled: val != 0}, dataLabels: {enabled : val == 2}}, true);
});

$('.select-yaxis-position').dropdown('setting', 'onChange', function(val){
    val ? chartSettings.items[currentItemIndex].axisPosition = val :  delete chartSettings.items[currentItemIndex].axisPosition;
    updateYAxis();
});

$('.select-chart-yaxis-position').dropdown('setting', 'onChange', function(val){
    val ? chartSettings.yAxisPosition = val : delete chartSettings.yAxisPosition;
    updateYAxis();
});

$('.select-auto-refresh').dropdown('setting', 'onChange', function(val){
    val !=='0' ? chartSettings.autoRefresh = val : delete chartSettings.autoRefresh;
});

$('.select-time-range-unit').dropdown('setting', 'onChange', function(val){
    val !=='0' ? chartSettings.timeRangeUnit = val : delete chartSettings.timeRangeUnit;
});

/*
$('.select-y-axis').dropdown('setting', 'onChange', function(val){
    chartSettings.items[currentItemIndex].dashStyle = val;
    chartSeries[currentItemIndex].update({yAxis: val}, true);
});
*/
$('[add-item]').click(addNewChartItem);
$('[btn-config-exp]').click(function(){
	ConfigExpression.show({
        autoRefresh: false,
        expression: $('[name="item-expression"]').val(),
        onApply: function(expression){
            $('[name="item-expression"]').val(expression);
            chartSettings.items[currentItemIndex].expression = expression;
        }
    });
});

function updateChartItemsZIndex(){
    var z = 0;
    $('[chart-item]').each(function(){
        var index = $(this).attr('item-index');
        chartSettings.items[index].zIndex = z;
        chartSeries[index].update({zIndex: z}, false);
        z++;
    });
    chart.redraw();
}

function updateChartItemsColor(){
    $('[chart-item]').each(function(){
        var index = $(this).attr('item-index');
        $(this).find('[item-color]').css('background-color', chartSeries[index].color);
    })
}

function buildRealtimeTags(){
    chart.options.realtimeTags = [];
    chartSettings.items.forEach(item => {
        item.expression.startsWith('TAG:') && chart.options.realtimeTags.push({tag: item.expression.substr(4), index: item.index});
    });
    return chart.options.realtimeTags.length > 0;
}

function buildItemsList(){
    !chartSettings.items && (chartSettings.items = []);
    currentItemIndex = null;
    $('[items-container]').empty();
    while (chart.series.length) {
        chart.series[0].remove();
    }
    /*
    chart.series.forEach(item => {
        //var item = chartSettings.items[i];
        var $remove = $('<span remove-item>').attr('title', 'Remove this item');
        var $title = $('<span item-title>').html(item.name);
        item.color && $title.css('color', item.color);
        var $item = $('<div chart-item>').attr('item-index',item.index).append($title).append($remove);
        $('[items-container]').append($item);
    })
    */
    var index = 0;
    var yAxis = [];
    chartSettings.items.forEach(item => {if(item){
        item.index = index++;
        item.zIndex = item.index;
        createChartItem(item);
    }});
    updateYAxis();
    //chart.redraw();
    index && $('[chart-item]').first().click();
}

function updateYAxis(){
    let key = (chartSettings.yAxisTitle ? chartSettings.yAxisTitle : '') + '~~~' + (chartSettings.yAsixPosition ? chartSettings.yAsixPosition : '0'),
        opt = {opposite: chartSettings.yAsixPosition == 1, title: {text: chartSettings.yAxisTitle}},
        yAxis = [key];

    chartSettings.yAxisColor && $.extend(true, opt, {title:{style:{color: '#' + chartSettings.yAxisColor}},labels:{style:{color: '#' + chartSettings.yAxisColor}}});
    chartSettings.yAxisTitle == 'hidden' && (opt.visible = false);
    chart.yAxis[0].update(opt, false);

    chartSettings.items.forEach(item => {if(item){
        item.axisPosition == undefined && (item.axisPosition = 0);
        key = (item.axisTitle ? item.axisTitle : (chartSettings.yAxisTitle ? chartSettings.yAxisTitle : '')) + '~~~' + (item.axisPosition ? item.axisPosition : (chartSettings.yAsixPosition ? chartSettings.yAsixPosition : '0'));
        item.yAxis = yAxis.indexOf(key);
        item.yAxis < 0 && yAxis.push(key) && (item.yAxis = yAxis.length - 1);
        if(item.yAxis > 0){
            opt = {opposite: item.axisPosition == 1, title: {text: item.axisTitle}};
            item.axisTitle == 'hidden' && (opt.visible = false);
            //let color = item.yAxisColor ? item.yAxisColor : chartSettings.yAxisColor;
            //color && (color = '#' + color) && $.extend(true, opt, {title: {style: {color: color}}, labels: {style: {color: color}}});
            item.yAxisColor && $.extend(true, opt, {title: {style: {color: '#' + item.yAxisColor}}, labels: {style: {color: '#' + item.yAxisColor}}});
            item.yAxis < chart.yAxis.length ? chart.yAxis[item.yAxis].update(opt, false) : chart.addAxis(opt, false);
        }
    }});
    chartSettings.items.forEach((item, index) => {if(item){
        chartSeries[index].update({yAxis: item.yAxis}, false);
    }});
    while(chart.yAxis.length > yAxis.length && chart.yAxis.length > 1) chart.yAxis[chart.yAxis.length-1].remove(false);
    //yAxis.length == 0 && chart.yAxis[0].update({title: {text: ''}}, false);
    chart.redraw();
}

function addNewChartItem(){
    var index = chartSettings.items.length;
    var item = {
        name: 'Chart item ' + (index + 1),
        type: 'spline',
        index: index,
        zIndex: index,
    }
    chartSettings.items[item.index] = item;
    createChartItem(item)[0].scrollIntoView();
    chart.redraw();
    chart.series.length && $('[box-edit-item]').show();
    $('[chart-item]').last().click();
    $('[name="title"]').focus();
    $('[name="title"]')[0].select();
}

function randomData(){
    if(!currentItemIndex) return;
    var data = [];
    var d = $('#filter-date').date();
    var range = EB.getDateRange(d, chartSettings.timeRange, chartSettings.timeRangeUnit);
    for(var x=0; x < range; x++){
        data.push([Date.UTC(d.getFullYear(),d.getMonth(),d.getDate(),d.getHours(),d.getMinutes(),d.getSeconds()), Math.round(Math.random()*1000)]);
        d.setDate(d.getDate() - 1);
    }
    chartSeries[currentItemIndex].setData(data);
}

function createChartItem(item){
    var $remove = $('<span remove-item>').attr('title', 'Remove this item').click(function(){
        removeChartItem($(this).parent());
    });
    var $title = $('<span item-title>').html(item.name);
    var $color = $('<span item-color>');
    //item.color && $title.css('color', '#' + item.color);
    var $item = $('<div chart-item>').attr('item-index', item.index).append($color).append($title).append($remove).click(function(){
        selectChartItem($(this));
    });
    $('[items-container]').append($item);

    var data = [];
    var seri = {
        name: item.name,
        data: data,
    }
    item.type && (seri.type = item.type);
    item.color && (seri.color = '#' + item.color);
    item.valueSuffix && (seri.tooltip = {valueSuffix: item.valueSuffix});
    item.dashStyle && (seri.dashStyle = item.dashStyle);
    item.marker == 0 && (seri.marker = {enabled: false});
    seri.dataLabels = {enabled : item.marker == 2, style: {fontSize: '9px', color: '#666666'}, formatter : function() {return this.y}};
    item.zIndex && (seri.zIndex = item.zIndex);
    chartSeries[item.index] = chart.addSeries(seri, false);
    return $item;
}

function removeChartItem($el){
    var index = $el.attr('item-index');
    $el.remove();
    chartSeries[index].remove(true);
    delete chartSeries[index];
    //chartSettings.items.splice(index, 1);
    delete chartSettings.items[index];
    index == currentItemIndex && $('[chart-item]').first().click();
    !chart.series.length && $('[box-edit-item]').hide();
}

function selectChartItem($el){
    var index = $el.attr('item-index');
    if(index == currentItemIndex) return;
    $('[chart-item][selected]').removeAttr('selected');
    $el.attr('selected', '');
    currentItemIndex = index;
    var item = chartSettings.items[currentItemIndex];
    //var seri = chart.series[currentItemIndex];
    $('[name="item-expression"]').val(item.expression);
    $('[name="title"]').val(item.name);
    //var color = item.color;
    //color ? (color.startsWith('#') && (color = color.substr(1))) : color = '';
    $('[name="title-color"]').val(item.color ? item.color : '').trigger('change');
    $('[name="item-yaxis-color"]').val(item.yAxisColor ? item.yAxisColor : '').trigger('change');
    $('.select-item-type').dropdown('set selected', item.type ? item.type : 'line');
    $('.select-item-dash-type').dropdown('set selected', item.dashStyle ? item.dashStyle : 'Solid');
    $('.select-marker').dropdown('set selected', !item.marker ? 1 : item.marker);
    //$('.select-y-axis').dropdown('set selected',  item.axis ? item.axis : 'default');
    $('[name="item-value-suffix"]').val(item.valueSuffix);
    $('[name="item-yaxis-title"]').val(item.axisTitle ? item.axisTitle : '');
    $('.select-yaxis-position').dropdown('set selected',  item.axisPosition ? item.axisPosition : '0');
}

const chartData  = {!! json_encode($chartData) !!};
let chartSettings  = {!! json_encode($chartSettings) !!};
loadChart(chartSettings);
//chartSettings ? loadChart(chartSettings) : newChart();
chartData && $('[btn-show-chart-settings]').click() && chartData.forEach((item, index) => {
    chartSeries[index].setData(item);
});

</script>
@stop
