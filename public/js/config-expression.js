ConfigExpression = {
    html: `<div class="dialog-config box-config-expression">
<div class="ui form">
<div class="ui toggle checkbox chk-tag">
  <input type="checkbox">
  <label>Tag</label>
</div>
  <div class="fields row-1">
    <div class="field select-facility">
      <label>Facility</label>
      <div id="filter-selectFacility" tabIndex=""></div>
    </div>
    <div class="field select-objecttype">
      <label>Type</label>
      <div id="filter-selectObjectType" tabIndex=""></div>
    </div>
    <div class="field select-object">
      <label>Object</label>
      <div id="filter-selectObject" tabIndex=""></div>
    </div>
  </div>
  <div class="fields row-2">
    <div class="field select-datasource">
      <label>Data source</label>
      <div id="filter-selectDataSource" tabIndex=""></div>
    </div>
    <div class="field select-attribute">
      <label>Attribute</label>
      <div id="filter-selectAttribute" tabIndex=""></div>
    </div>
  </div>
  <div class="fields row-3">
    <div class="field select-eventtype">
        <label>Event type</label>
        <div id="filter-selectEventType" tabIndex=""></div>
    </div>
    <div class="field select-flowphase">
        <label>Flow phase</label>
        <div id="filter-selectFlowPhase" tabIndex=""></div>
    </div>
    <div class="field select-alloctype">
      <label>Alloction type</label>
      <div id="filter-selectAllocType" tabIndex=""></div>
    </div>
    <div class="field select-plantype">
      <label>Plan type</label>
      <div id="filter-selectPlanType" tabIndex=""></div>
    </div>
    <div class="field select-forecasttype">
      <label>Forecast type</label>
      <div id="filter-selectForecastType" tabIndex=""></div>
    </div>
  </div>
<button append-expression>Append to expression</button>
<button clear-expression>Clear</button>
<div box-auto-refresh>
  <label>Auto refresh</label>
  <select class="ui dropdown" id="selectAutoRefresh">
  <option value="0">Disabled</option><option value="-1">Default</option><option value="5000">5 seconds</option><option value="10000">10 seconds</option><option value="20000">20 seconds</option><option value="30000">30 seconds</option><option value="60000">1 minute</option><option value="120000">2 minutes</option><option value="300000">5 minutes</option><option value="600000">10 minutes</option><option value="1800000">30 minutes</option><option value="3600000">1 hour</option><option value="10800000">3 hours</option><option value="21600000">6 hours</option><option value="43200000">12 hours</option><option value="86400000">1 day</option>
  </select>
</div>
<textarea value-expression></textarea>
</div>
</div>`,
    isReady: false,
    getCodes: function(source){
        const   objectType = $('#selectObjectType').val(),
                dataSource = $('#selectDataSource').val(),
                facility = $('#selectFacility').val(),
                isTag = $('.box-config-expression .chk-tag').checkbox('is checked');

        isTag ? $('.select-datasource, .select-attribute').hide() : 
            (objectType ? $('.select-datasource, .select-attribute').show() : $('.select-datasource, .select-attribute').hide());
        objectType == 'EnergyUnit' && !isTag ? $('.select-flowphase, .select-eventtype').show() : $('.select-flowphase, .select-eventtype').hide();
        dataSource.endsWith('_ALLOC') ? $('.select-alloctype').show() : $('.select-alloctype').hide();
        dataSource.endsWith('_PLAN') ? $('.select-plantype').show() : $('.select-plantype').hide();
        dataSource.endsWith('_FORECAST') ? $('.select-forecasttype').show() : $('.select-forecasttype').hide();
    
        const affectedModels = {
            'Facility': ['Object'],
            'ObjectType': isTag ? ['Object'] : ['Object', 'DataSource'],
            'DataSource': ['Attribute'],
        };
        affectedModels[source].forEach(model => {
            $('#filter-select' + model).addClass('disabled').dropdown('clear');
        });
        if((source == 'DataSource' && dataSource) || (source != 'DataSource' && objectType && facility)){
            affectedModels[source].forEach(model => {
                $('#filter-select' + model).addClass('loading');
            });
            sendAjax('/eb-get-code', {
                cache: true,
                changed: source,
                affected: affectedModels[source],
                object: $('#selectObject').val(),
                facility: facility,
                objectType: objectType,
                dataSource: dataSource,
                isTag: isTag,
            }, function(data){
                for(let model in data){
                    $('#filter-select' + model).removeClass('loading');
                    //model == 'DataSource' && data[model] && data[model][data[model].length - 1].value != 'tag' && data[model].push({value: "tag", name: "Tag", selected: !data[model].length});
                    data[model].length ? $('#filter-select' + model).dropdown('change values', data[model]).removeClass('disabled') : $('#filter-select' + model).dropdown('change values', [{value: "", name: "Not found", selected: true}]);
                }
            });
        }
    },
    init: function(){
        if(this.isReady) return;
        var self = this;
        $('.box-config-expression .chk-tag').checkbox({onChange: function() {
            $('.select-object label').html($(this).is(':checked') ? 'Tag' : 'Object');
            self.getCodes('ObjectType');
        }});
        $('.select-datasource, .select-attribute, .select-flowphase, .select-eventtype, .select-alloctype, .select-plantype, .select-forecasttype').hide();
        $('.box-config-expression [append-expression]').click(function(){
            const objectType = $('#selectObjectType').val(),
                dataSource = $('#selectDataSource').val(),
                object = $('#selectObject').val(),
                attr = $('#selectAttribute').val(),
                isTag = $('.box-config-expression .chk-tag').checkbox('is checked');
                ;
            let expression = '';
            isTag ? object && (expression = 'TAG:' + object) :
            dataSource && attr && object && (expression = dataSource + '.' + attr + '.' + object +
                (objectType == 'EnergyUnit' ? '.' + $('#selectEventType').val() + '.' + $('#selectFlowPhase').val() : '') +
                (dataSource.endsWith('_ALLOC') ? '.' + $('#selectAllocType').val() : '') +
                (dataSource.endsWith('_PLAN') ? '.' + $('#selectPLanType').val() : '') +
                (dataSource.endsWith('_FORECAST') ? '.' + $('#selectForecastType').val() : '')
            );
        
            expression && $('[value-expression]').val($('[value-expression]').val() + expression);
        });
        $('.box-config-expression [clear-expression]').click(function(){
            $('[value-expression]').val('');
        });
        
        EB.buildDropdown('#filter-selectFacility', {
            id: 'selectFacility',
            list: EB.facility,
            onChange: function(){
                self.getCodes('Facility');
            },
        });
        
        EB.buildDropdown('#filter-selectObjectType', {
            id: 'selectObjectType',
            defaultText: 'Select a type...',
            onChange: function(){
                self.getCodes('ObjectType');
            },
            list: {
                items: [
                    {value: 'EnergyUnit', name: 'Energy Unit'},
                    {value: 'Flow', name: 'Flow'},
                    {value: 'Tank', name: 'Tank'},
                    {value: 'Storage', name: 'Storage'},
                    //{value: 'Keystore', name: 'Keystore'},
                    //{value: 'Deferment', name: 'Deferment'},
                    //{value: 'WellTest', name: 'Well Test'},
                ]
            }
        });
        
        EB.buildDropdown('#filter-selectObject', {
            class: 'search',
            options: {clearable: false, fullTextSearch: true},
            id: 'selectObject',
            defaultText: 'Select an object...',
            onChange: function(){
                //$('#selectDataSource').val() == 'tag' && self.getCodes('DataSource');
            },
            list: {
                items: []
            }
        });
        
        EB.buildDropdown('#filter-selectDataSource', {
            id: 'selectDataSource',
            defaultText: 'Select...',
            onChange: function(){
                self.getCodes('DataSource');
            },
            list: {
                items: []
            }
        });
        
        EB.buildDropdown('#filter-selectAttribute', {
            class: 'search',
            options: {clearable: false, fullTextSearch: true},
            id: 'selectAttribute',
            defaultText: 'Select...',
            list: {
                items: []
            }
        });

        for(let select in EB.extraFilters){
            EB.buildDropdown('#filter-' + select, EB.extraFilters[select]);
        }

        $("#selectAutoRefresh").dropdown();
        
        this.isReady = true;
    },
    show: function(options) {
        var $dialog = $('.box-config-expression');
        if(!$dialog.length){
            $dialog = $(this.html);
            $('body').append($dialog);
        }
        this.init();
        options.autoRefresh === false && $('[box-auto-refresh]').hide();
        $('.box-config-expression [value-expression]').val(options.expression ? options.expression : '');
        $('#selectAutoRefresh').dropdown('set selected', options.autoRefresh ? options.autoRefresh : 0);
        $('.box-config-expression').dialog({
            title: 'Expression configuration',
            modal: true,
            width: 775,
            height: 500,
            buttons: {
                'Apply': function(){
                    $(this).dialog('close');
                    options.onApply && options.onApply($('.box-config-expression [value-expression]').val(), $('#selectAutoRefresh').val());
                },
                'Close': function(){
                    $(this).dialog('close');
                    options.onClose && options.onClose();
                },
            }
        });
    },
}