<link rel="stylesheet" href="/semantic-ui/semantic.min.css">
<link rel="stylesheet" href="/css/daterangepicker.css">
<link rel="stylesheet" href="/css/main.css">

<script src="/js/moment.js"></script> 
<script src="/js/jquery.daterangepicker.js"></script> 
<script src="/js/jquery.number.min.js"></script> 
<script src="/semantic-ui/semantic.min.js"></script> 

<div class="box-main-container" style="display:none">
  <ul main-header>
    <li logo action="home"></li>
    <li eb><b>Energy Builder&#xae;</b></li>
    <li full main-menu></li>
    <li action="chat"></li>
    <li action="setting"></li>
    <li action="workflow"></li>
    <li action="notification"></li>
    <li action="user"></li>
  </ul>
  <ul sub-header>
  </ul>

  <div side-box user>
  </div>

  <div main-body>
  </div>

</div>

<script src="/js/utils.js"></script>
<script src="/js/datacapture.js"></script>
<script src="/json-data-test.js"></script>
<script>

const screenConfig = {
    title: 'Energy Unit data capture',
    facilities: {
        defaultValue: 2,
        items: [
            {value: 1, name: 'Facility 1', group: 'Area 1'},
            {value: 2, name: 'Facility 2', group: 'Area 1'},
            {value: 3, name: 'Facility 3', group: 'Area 1'},
            {value: 4, name: 'Facility 4', group: 'Area 2'},
            {value: 5, name: 'Facility 5', group: 'Area 2'},
    ]},
    date: {
        value: '2020-02-01',
        from: null,
    },
    filters: [
        {
            id: 'FlowPhase',
            title: 'Flow phase',
            list: {
                defaultValue: 2,
                items: [
                    {value: 1, name: 'Option 1'},
                    {value: 2, name: 'Option 2'},
                    {value: 3, name: 'Option 3'},
            ]},
        },
    ],
    buttons: [
        {name: 'Load', code: 'load'},
        {name: 'Save', code: 'save'},
    ],
    tabs: [
        {
            title: 'FDC',
            dataTableName: 'ENERGY_UNIT_DATA_FDC_VALUE',
        },
        {
            title: 'Standard',
            dataTableName: 'ENERGY_UNIT_DATA_VALUE',
            selected: true
        },
        {
            title: 'Theoretical',
            dataTableName: 'ENERGY_UNIT_DATA_THEOR',
        },
    ],
    dataTables: [DataAdapter.convertFromLoadedData(jsondata)[0],DataAdapter.convertFromLoadedData(json2)[0]]
};

DataCaptureScreen.buildScreen(screenConfig);

</script>
