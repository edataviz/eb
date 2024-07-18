@extends('eb.base')

@section('head')
@parent
<link rel="stylesheet" href="/lib/mxgraph/styles/grapheditor.css">
<link rel="stylesheet" href="/css/diagram.css">
<link rel="stylesheet" href="/css/config-expression.css">

<script src="/js/config-expression.js"></script>
<script src="/js/eb-chart.js"></script>
<script src="/js/eb-datagrid.js?1"></script>

<script src="/lib/mxgraph/js/Init.js?3"></script>
<script src="/lib/mxgraph/deflate/pako.min.js"></script>
<script src="/lib/mxgraph/deflate/base64.js"></script>
<script src="/lib/mxgraph/jscolor/jscolor.js"></script>
<script src="/lib/mxgraph/sanitizer/sanitizer.min.js"></script>
<script src="/lib/mxgraph/src/js/mxClient.js"></script>
<script src="/lib/mxgraph/js/EditorUi.js?4"></script>
<script src="/lib/mxgraph/js/Editor.js"></script>
<script src="/lib/mxgraph/js/Sidebar.js?11"></script>
<script src="/lib/mxgraph/js/Graph.js"></script>
<script src="/lib/mxgraph/js/Format.js?2"></script>
<script src="/lib/mxgraph/js/Shapes.js"></script>
<script src="/lib/mxgraph/js/Actions.js?3"></script>
<script src="/lib/mxgraph/js/Menus.js?2"></script>
<script src="/lib/mxgraph/js/Toolbar.js"></script>
<script src="/lib/mxgraph/js/Dialogs.js?4"></script>

<script src="/lib/highcharts/highcharts.js"></script>
<script src="/lib/highcharts/highcharts-more.js"></script>

<script src="/common/js/svgtopng.js"></script>
<script src="/common/js/skinable_tabs.min.js"></script>
<script src="/js/diagram.js?8"></script>
@stop

@section('main')
<div id="listSavedDiagrams" style="overflow-y: auto; display: none;"></div>
<div class="dialog-config box-config-gauge">
    <table>
    <tr><td>Title</td><td><input id="title"/></td></tr>
    <tr><td>Title distance</td><td><input id="titleDistance"/></td></tr>
    <tr><td>Title size</td><td><input id="titleSize"/></td></tr>
    <tr><td>Title color</td><td><input type="pickcolor" id="titleColor"/></td></tr>
    <tr><td>Value size</td><td><input id="valueSize"/></td></tr>
    <tr><td>Value color</td><td><input type="pickcolor" id="valueColor"/></td></tr>
    <tr><td>Value min</td><td><input id="valueMin"/></td></tr>
    <tr><td>Value max</td><td><input id="valueMax"/></td></tr>
    <tr><td>Property</td><td><input id="property"/></td></tr>
    <tr><td>Property size</td><td><input id="propertySize"/></td></tr>
    <tr><td>Property color</td><td><input type="pickcolor" id="propertyColor"/></td></tr>
    <tr><td>UoM</td><td><input id="uom"/></td></tr>
    <tr><td>UoM size</td><td><input id="uomSize"/></td></tr>
    <tr><td>UoM color</td><td><input type="pickcolor" id="uomColor"/></td></tr>
    <tr><td>Angle begin</td><td><input id="angleBegin"/></td></tr>
    <tr><td>Angle end</td><td><input id="angleEnd"/></td></tr>
    <tr><td>Red-band min</td><td><input id="redBandMin"/></td></tr>
    <tr><td>Red-band max</td><td><input id="redBandMax"/></td></tr>
    <tr><td>Scale color</td><td><input type="pickcolor" id="scaleColor"/></td></tr>
    <tr><td>Scale label color</td><td><input type="pickcolor" id="scaleLabelColor"/></td></tr>
    <tr><td>Dial color</td><td><input type="pickcolor" id="dialColor"/></td></tr>
    </table>
</div>
<div class="dialog-config box-config-chart">
<div class="ui form">
    <div class="field select-chart">
        <label>Chart</label>
        <div id="filter-selectChart" tabIndex=""></div>
    </div>
</div>
</div>
<div class="dialog-config box-config-datagrid">
<div class="ui form">
<div class="field">
        <label>Query</label>
        <textarea id="datagridQuery" rows="12" style="white-space: nowrap;" placeholder="Select ... from ... where ..."></textarea>
    </div>
    <div class="two fields">
    <div class="field">
        <label>Header</label>
        <textarea id="datagridHeader" rows="6" placeholder="Column title 1&#10;Column title 2&#10;Column title 3&#10;..."></textarea>
    </div>
    <div class="field">
        <label>Style</label>
        <textarea id="datagridStyle" rows="6" placeholder="#datagrid td{...}"></textarea>
    </div>
    </div>
</div>
</div>
@stop

@section('script')
@parent
<script>
EB.screenCode = '{{ $diagramType == 2 ? 'CF_DASHBOARD_CONFIG' : ($diagramType == 3 ? 'VIS_WORKFLOW' : ($diagramType == 4 ? 'ALLOC_CONFIG' : 'VIS_NETWORK_MODEL')) }}';
//EB.favState = false;
EB.buildScreen({
    title: '{{ $diagramType == 2 ? 'Config Dashboard' : ($diagramType == 3 ? 'Workflow' : ($diagramType == 4 ? 'Config Allocation' : 'Diagram')) }}',
    items: [
        /*{
            type: 'filter',
            id: 'diagram-type',
            list: {
                defaultValue: {{ $diagramType ? $diagramType : 10 }},
                items: [
                    {value: 1, name: 'General'},
                    {value: 2, name: 'Dashboard'},
                    {value: 4, name: 'Allocation'},
            ]},
        },*/
        {
            type: 'html',
            id: 'box-network-title',
            html: '<span network-title>Network title</span>'
        },
        {
            type: 'html',
            id: 'box-buttons',
            html: `<button class="ui icon button" title="Edit diagram name" btn-edit><i class="edit outline icon"></i></button>
<div class="ui buttons">
<button class="ui icon button" title="Create new diagram" btn-new><i class="file outline icon"></i></button>
<button class="ui icon button" title="Load diagram" btn-open><i class="folder open outline icon"></i></button>
<button class="ui icon button" title="Save diagram" btn-save><i class="save outline icon"></i></button>
<div class="ui floating dropdown icon button ex-menu" tabindex="0">
    <i class="dropdown icon"></i>
    <div class="menu transition hidden" tabindex="-1">
    <div class="item" btn-save-as><i class="save icon"></i>Save as new diagram</div>
    <div class="divider"></div>
    <!--
    <div class="item" btn-print><i class="print icon"></i>Print</div>
    <div class="item" btn-export><i class="share icon"></i>Export</div>
    <div class="item" btn-xml><i class="code icon"></i>Display source</div>
    <div class="divider"></div>
    -->
    <div class="item" btn-menu><i class="bars icon"></i>Extra menu</div>
    </div>
</div>
</div>`,
            actions: [
                {
                    selector: '[btn-edit]',
                    event: 'click',
                    action: function(){
                        const s = prompt("Please input diagram name", currentDiagramName);
                        s && setCurrentDiagramName(s);
                    }
                },
                {
                    selector: '[btn-new]',
                    event: 'click',
                    action: function(){
                        newDiagram();
                    }
                },
                {
                    selector: '[btn-open]',
                    event: 'click',
                    action: function(){
                        loadDiagram();
                    }
                },
                {
                    selector: '[btn-save]',
                    event: 'click',
                    action: function(){
                        saveDiagram();
                    }
                },
                {
                    selector: '[btn-save-as]',
                    event: 'click',
                    action: function(){
                        saveDiagram('save-as');
                    }
                },
                {
                    selector: '[btn-menu]',
                    event: 'click',
                    action: function(){
                        $('.geMenubarContainer').toggle();
                    }
                }
            ],
        },
        {
            type: 'html',
            html: '<div ge-box></div>'
        },
    ],
});

//add element to display network title
//$('<li network-title-box><p network-title contenteditable>Network title</p></li>').insertAfter('[toolbar] #filter-diagram-type');
//$('<table ge-box-container><tr><td ge-box></td></tr></table>').insertAfter('[toolbar] [buttons-box]');
//$('[toolbar]').append('<li><div ge-box></div></li>');

$('.ui.dropdown.ex-menu').dropdown();

const diagramType = {{ $diagramType ? $diagramType : 1 }};
const diagramSource = `{!! str_replace('`', '', $diagram['XML_CODE']) !!}`;
setCurrentDiagramId({{ $diagram['ID'] }});
setCurrentDiagramName('{{ $diagram['NAME'] }}');
window.enableDashboardCategory = (diagramType == 2);

//Graph.prototype.getTooltipForCell = function(cell){}
initGraphEditor(false, function(){
    diagramSource && loadGraphSource(diagramSource);
});

$('.box-config-gauge input[type="pickcolor"]').click(function(){
    const $button = $(this);
    ui.pickColor('FFFFFF', function(color){
        $button.val(color);
        $button.css('backgroundColor', color)
    });
});

</script>	
@stop
