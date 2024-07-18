@extends('eb.base')

@section('head')
@parent
<link rel="stylesheet" href="/css/main.css">
<link rel="stylesheet" href="/css/dashboard.css">
<link rel="stylesheet" href="/lib/mxgraph/styles/grapheditor.css">

<script src="/lib/mxgraph/js/Init.js?3"></script>
<script src="/lib/mxgraph/deflate/pako.min.js"></script>
<script src="/lib/mxgraph/deflate/base64.js"></script>
<script src="/lib/mxgraph/jscolor/jscolor.js"></script>
<script src="/lib/mxgraph/sanitizer/sanitizer.min.js"></script>
<script src="/lib/mxgraph/src/js/mxClient.js"></script>
<script src="/lib/mxgraph/js/EditorUi.js?4"></script>
<script src="/lib/mxgraph/js/Editor.js"></script>
<script src="/lib/mxgraph/js/Sidebar.js?12"></script>
<script src="/lib/mxgraph/js/Graph.js"></script>
<script src="/lib/mxgraph/js/Format.js?2"></script>
<script src="/lib/mxgraph/js/Shapes.js"></script>
<script src="/lib/mxgraph/js/Actions.js?3"></script>
<script src="/lib/mxgraph/js/Menus.js?2"></script>
<script src="/lib/mxgraph/js/Toolbar.js"></script>
<script src="/lib/mxgraph/js/Dialogs.js?4"></script>

<script src="/lib/highcharts/highcharts.js"></script>
<script src="/lib/highcharts/highcharts-more.js"></script>
<script src="/js/diagram.js?8"></script>
<script src="/js/eb-chart.js?1"></script>
<script src="/js/eb-datagrid.js?1"></script>
@stop

@section('main')
@stop

@section('script')
@parent
<script>
function loadCurrentDashboard(isReloaded){
    loadSavedDiagram(currentDiagramName, currentDiagramId, function(){
        if(isReloaded){
            loadGraphData();
        }
        else{
            $('[buttons-box]').nextAll().remove();
            if(ed.graph.model.getCell(0).value){
                var attrs = ed.graph.model.getCell(0).value.attributes;
                var models = {};
                for(var i=0, l=attrs.length; i<l; i++){
                    if(attrs[i].name.startsWith('select_')){
                        var selectId = attrs[i].name;
                        var model = attrs[i].name.substr(7);
                        var title = attrs[i].value;
                        var options = {
                            type: 'filter',
                            id: selectId,
                            title: title,
                            layout: 'horizontal',
                            list: {
                                defaultValue: 'EnergyUnit',
                                items: [                    
                                ]
                            },
                            onChange: function(value, text, $item){
                                //alert(value+':'+text);
                                //loadGraphData();
                            }
                        };
                        EB.buildItem('filter', options);
                        models[selectId] = model;
                    }
                }
                if(!$.isEmptyObject(models)){
                    sendAjax('/get-model-data', {
                        models: models,
                        cache: true
                    }, function(data){
                        for(var selectId in data){
                            data[selectId].length && (data[selectId][0].selected = true);
                            $('#filter-' + selectId + '>.ui.dropdown').dropdown('change values', data[selectId])
                        }
                    });
                }
            }
            else
                loadGraphData();
        }
    });
}
EB.screenCode = 'VIS_DASHBOARD';
EB.buildScreen({
    title: 'Dashboard',
    items: [
        {
            type: 'filter',
            id: 'DashboardList',
            list: {!! json_encode($dashboards) !!},
            onChange: function(value, text, $item){
                setCurrentDiagramId(value);
                setCurrentDiagramName(text);
                loadCurrentDashboard();
            },
        },
        {
            type: 'html',
            html: `<div class="ui toggle checkbox chk-auto-update"><input type="checkbox" checked><label>Auto update</label></div>`
        },
        {
            type: 'date',
            css: {'margin-left': 25},
            value: null,
            from: null,
        },
        {
            type: 'button',
            content: 'Edit',
            tooltip: 'Edit current dashboard',
            click: function(){
                location.href = 'cf-dashboard?id=' + currentDiagramId;
            }
        },
        {
            type: 'button',
            class: 'btn-reload-data',
            content: 'Refresh',
            tooltip: 'Reload all displayed data',
            click: function(){
                //loadGraphData();
                loadCurrentDashboard(true);
            }
        },
    ],
    date: null,
    filters: [],
    buttons: [],
});

//$('.btn-reload-data')[0].after($('[filter-date]')[0]);
$('[filter-date]').hide();
$('.chk-auto-update').checkbox({onChange: function() {
    if($(this).is(':checked')){
        $('[filter-date]').hide();
        checkRefreshTiming('force');
    }
    else {
        $('[filter-date]').show();
        checkRefreshTiming();
    }
}});

const diagramSource = `{!! str_replace('`', '', $diagram->XML_CODE) !!}`;
setCurrentDiagramId({{ $diagram->ID }});
setCurrentDiagramName('{{ $diagram->NAME }}');

//disable tooltip
Graph.prototype.getTooltipForCell = function(cell){}

initGraphEditor(true, function(){
    if(diagramSource){
        loadGraphSource(diagramSource);
        //ed.graph.fit();
        ed.graph.setPanning(false);
        loadGraphData();
    }
});

const checkTimeInterval = 5000;
let checkRefreshTimeout;
function checkRefreshTiming(force){
    checkRefreshTimeout && clearTimeout(checkRefreshTimeout);
    $('.chk-auto-update').checkbox('is checked') && !$.isEmptyObject(window.autoRefreshCells) && (checkRefreshTimeout = setTimeout(() => {
        loadGraphData(true);
    }, force ? 0 : checkTimeInterval));
}

function loadGraphData(isAutoRefresh){
    if(window.loadingData) return;

    !isAutoRefresh && (window.autoRefreshCells = {});
    const cells = isAutoRefresh ? window.autoRefreshCells : ed.graph.model.cells;

    const valueExpressions = {};
    const chartExpressions = {};
    const dateString = $('#filter-date').dateString();
    const checkTimeNow = Date.now();
    
	for(const c in cells){
        const cell = cells[c];
        if(!cell) continue;
        let isOkay = !isAutoRefresh;
        if(isAutoRefresh){
            const checkTime = Number(cell.getAttribute('checkTime'));
            const autoRefresh = Number(cell.getAttribute('autoRefresh'));
            if(checkTime && checkTimeNow - checkTime >= autoRefresh){
                cell.setAttribute('checkTime', checkTimeNow);
                isOkay = true;
            }
        }
        const type = cell.getAttribute('type');
        if(type == 'chart'){
            const config = cell.getAttribute('config').trim();
            if(config){
                let chartSettings = false;
                try{
                    chartSettings = JSON.parse(config);
                } catch(err){
                    console.log(config);
                    console.log(err);
                }
                if(chartSettings){
                    if(!isAutoRefresh){
                        if(chartSettings.autoRefresh > 0 || chartSettings.autoRefresh == -1){
                            cell.setAttribute('checkTime', checkTimeNow);
                            cell.setAttribute('autoRefresh', chartSettings.autoRefresh);
                            cell.setAttribute('rangeLimit', EB.getDateRange(new Date(), chartSettings.timeRange, chartSettings.timeRangeUnit) * 864e5);
                            window.autoRefreshCells[c] = cell;
                        }
                    }
                    if(isOkay){
                        const expressions = {};
                        chartSettings.items.forEach((item, index) => {
                            item.expression && (expressions[index] = item.expression);
                        });
                        !$.isEmptyObject(expressions) && (chartExpressions[c] = {
                            dateRange: EB.getDateRange($('#filter-date').date(), chartSettings.timeRange, chartSettings.timeRangeUnit, true) + ',' + dateString, 
                            expressions: expressions
                        });
                    }
                }
            }
        }
        else{
            if(!isAutoRefresh){
                const autoRefresh = Number(cell.getAttribute('autoRefresh'));
                (autoRefresh > 0 || autoRefresh == -1) && (cell.setAttribute('checkTime', checkTimeNow) | 1) && (window.autoRefreshCells[c] = cell);
            }
            if(isOkay){
                const expression = cell.getAttribute('expression');
                expression && (valueExpressions[c] = expression);
            }
        }
    }

    if(!$.isEmptyObject(valueExpressions) || !$.isEmptyObject(chartExpressions)){
        window.loadingData = true;
        let filters = {};
        $('[id^=select_]').each(function(){
            filters[$(this).attr('id').substr(7)] = $(this).val();
        });
        sendAjax('/load-graph-data', {
                date: dateString,
                filters: filters,
                valueExpressions: valueExpressions,
                chartExpressions: chartExpressions,
            }, function(data){
                const valueData = data.valueData;
                for(let c in valueData){
                    const cell = cells[c];
                    if(cell){
                        const type = cell.getAttribute('type');
                        if(type == 'gauge'){
                            const chart = getCellChart(cell);
                            chart && chart.series[0].setData([Number(valueData[c])], true)
                        }
                        else {
                            //cell.setAttribute('label', valueData[c]);
                            ed.graph.model.setValue(cell, valueData[c]);
                        }
                    }
                }
                const chartData = data.chartData;
                for(let c in chartData){
                    const cell = cells[c];
                    if(cell){
                        const chart = getCellChart(cell);
                        chart && chartData[c].forEach((seriData, index) => {
                            if(isAutoRefresh){
                                if(seriData.length > 0){
                                    const rangeLimit = Number(cell.getAttribute('rangeLimit'));
                                    if(index < chart.series.length){
                                        const series = chart.series[index];
                                        const item = seriData[seriData.length - 1]; //only use the last data item
                                        //seriData.forEach(function(item){
                                        if(series.data.length > 0){
                                            while(series.data.length > 1 && series.data[0].x <= item[0] - rangeLimit)
                                                series.data[0].remove(false);
                                            series.data[series.data.length-1].x == item[0] ?
                                                series.data[series.data.length-1].update(item[1], false) :
                                                series.addPoint([item[0], item[1]], false);
                                        }
                                        else
                                            series.update({data:[[item[0], item[1]]]}, false);
                                        //});
                                    }
                                    chart.redraw();
                                }
                            }
                            else{
                                chart.series[index].setData(seriData);
                            }
                        })
                    }
                }
            }, null, () => {
                window.loadingData = false;
                checkRefreshTiming();
            }
        )
    }
    else {
        checkRefreshTiming();
    }
}
</script>	
@stop
