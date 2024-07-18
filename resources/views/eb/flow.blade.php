@extends('eb.base')

@section('head')
@parent
<link rel="stylesheet" href="/css/main.css">
<script src="/js/datacapture.js"></script>
<script src="/json-data-test.js"></script>
@stop

@section('script')
@parent
<script>
EB.screenCode = 'FDC_FLOW';
EB.loadData = function(){
    $('#main-container').attr('loading', '');
    //$('[main]').append('<div loading class="ui active inverted dimmer"><div class="ui text loader">Loading</div></div>');
    sendAjax('/code/load',
            {
                CodeFlowPhase: "0",
                CodeForecastType: "1",
                CodePlanType: "1",
                CodeReadingFrequency: "0",
                Facility: "24",
                LoArea: "24",
                LoProductionUnit: "11",
                date_begin: "08/01/2016",
                date_end: "08/01/2016",
                tabTable: "FlowDataValue",
            },
            function(data){
                DataCaptureScreen.builDataGrid(DataAdapter.convertFromLoadedData(data)[0]);
            },
            function(data){
                $('[dt-wrapper]').parent().empty();
                console.log(data);
                alert('Error');
            },
            function(){
                //$('[main] [loading]').remove();
                $('#main-container').removeAttr('loading');
            }
        );
}
EB.buildScreen({
    title: 'Flow data capture',
    facility: {
        id: 'Facility',
        list: EB.facility,
        onChange: function(value, text, $item){
            alert(value + ':' + text);
        },
    },
    date: {
        value: '2020-02-01',
        from: null,
    },
    filters: EB.filters,
    buttons: [
        {
            content: 'Load', 
            click: function(){
                EB.loadData();
            }
        },
        {
            content: 'Save', 
            click: function(){
                alert('Save');
            }
        },
    ],
    tabs: [
        {
            title: 'FlowDataValue',
            dataTableName: 'FLOW_DATA_VALUE',
            selected: true
        },
        {
            title: 'Standard',
            dataTableName: 'ENERGY_UNIT_DATA_VALUE',
        },
        {
            title: 'Theoretical',
            dataTableName: 'ENERGY_UNIT_DATA_THEOR',
        },
    ],
    dataTables: []
});
</script>
@stop
