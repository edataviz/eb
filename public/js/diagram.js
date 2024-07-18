var ed, ui, graph;
mxLoadResources = false;
window.isDashboard = false;

const defaultGaugeConfig = {
	title: 'Name',
	titleDistance: 20,
	titleSize: 20,
	titleColor: '#666666',
	//valueExpression: '',
	valueSize: 16,
	valueColor: '#C02316',
	valueMin: 0,
	valueMax: 1000,
	property: 'Property',
	propertySize: 13,
	propertyColor: '#888888',
	uom: 'UoM',
	uomSize: 9,
	uomColor: '#888888',
	angleBegin: -120,
	angleEnd: 120,
	redBandMin: 0,
	redBandMax: 100,
	scaleColor: '#888888',
	scaleLabelColor: '#888888',
	dialColor: '#000000',
}

mxCell.prototype.getBoxID = function(hash){
	return (hash ? '#' : '') + 'htmlBox' + this.id
}

function initGraphEditor(isDashboard, funcComplete){
	window.isDashboard = isDashboard;
    var editorUiInit = EditorUi.prototype.init;
    EditorUi.prototype.menubarHeight = 107;
    EditorUi.prototype.toolbarHeight = 0;
	EditorUi.prototype.footerHeight = 0;
	if(isDashboard){
		EditorUi.prototype.splitSize = 0;
		EditorUi.prototype.hsplitPosition = 0;
		EditorUi.prototype.addBeforeUnloadListener = ()=>{};	
	}

    EditorUi.prototype.init = function(){
        editorUiInit.apply(this, arguments);
        this.actions.get('export').setEnabled(false);

        $(".geMenubarContainer").css("display",'none');
		isDashboard ? 
			$(".geToolbarContainer").css("display","none") : 
			$('.geMenubarContainer, .geToolbarContainer').appendTo('[toolbar] [ge-box]');
    };
    
    // Adds required resources (disables loading of fallback properties, this can only
    // be used if we know that all keys are defined in the language specific file)
    mxResources.loadDefaultBundle = false;
    var bundle = mxResources.getDefaultBundle(RESOURCE_BASE, mxLanguage) ||
        mxResources.getSpecialBundle(RESOURCE_BASE, mxLanguage);

    // Fixes possible asynchronous requests
    mxUtils.getAll([bundle, STYLE_PATH + '/default.xml'], function(xhr) {
        // Adds bundle text to resources
        mxResources.parse(xhr[0].getText());
        
        // Configures the default graph theme
		isDashboard && (Editor.prototype.disableUndo = true);
		Editor.useLocalStorage = true;

		var themes = new Object();
		themes[Graph.prototype.defaultThemeName] = xhr[1].getDocumentElement(); 
        ed = new Editor(urlParams['chrome'] == '0', themes);
		ui = new EditorUi(ed);
		graph = ed.graph;

		ui.setPageVisible(false);
		
		if(isDashboard){
			ui.toggleFormatPanel(true);
			ui.setScrollbars(false);
			ui.setFoldingEnabled(false);
			ui.setGridColor('transparent');
			graph.setPanning(false);
			graph.setEnabled(false);
		}
		else {
			graph.model.addListener(mxEvent.CHANGE, function(sender, evt) {
				evt.getProperty('edit').changes.forEach(change => {
					if(change.cell && isDashBoxType(change.cell.getAttribute('type'))){
						if(change.value){
							const newConfig = change.value.getAttribute('config');
							const oldConfig = change.previous.getAttribute('config');
							if(newConfig != oldConfig){
								checkCellAsDashBox(change.cell);
							}
						}
						/*
						if(change.geometry && false){
							const newConfig = change.geometry.width + ':' + change.geometry.height;
							const oldConfig = change.previous.width + ':' + change.previous.height;
							//const objIssue 		= $(change.cell.getBoxID(true)).parent().parent().parent().parent();
							//const objCorrection	= objIssue.parent().parent().children(0).children(0);
							//objIssue.attr('transform', 'translate(' + objCorrection.attr('x') + ',' + objCorrection.attr('y') + ')');
							if(newConfig != oldConfig){
								checkCellAsDashBox(change.cell);
							}
						}
						*/
					}
				})
			});

			graph.addListener(mxEvent.CELLS_RESIZED, function (sender, evt){
				var cells = evt.getProperty('cells');
				for (i = 0; i < cells.length; i++) {
					checkCellAsDashBox(cells[i]);
				}
			});

			graph.addListener(mxEvent.CELLS_ADDED, function (sender, evt){
				var cells = evt.getProperty('cells');
				for (i = 0; i < cells.length; i++) {
					checkCellAsDashBox(cells[i]);
				}
			});

			// Configures automatic expand on mouseover
			graph.popupMenuHandler.autoExpand = true;

			var oFactoryMethod = graph.popupMenuHandler.factoryMethod;
			// Installs context menu
			graph.popupMenuHandler.factoryMethod = function(menu, cell, evt) {
				oFactoryMethod(menu, cell, evt);
				const type = cell.getAttribute('type');
				if (graph.isSelectionEmpty()){
				}
				else{
					(type == 'chart' || type == 'gauge' || type == 'datagrid') && menu.addItem('Config...', null, ()=>{configCell(cell)});
					(type == 'gauge' || type == 'datavalue') && menu.addItem('Expression...', null, ()=>{configExpression(cell)});
				}
			};
		}
		typeof(funcComplete) == "function" && funcComplete();
		EB.screenCode != 'CF_DASHBOARD_CONFIG' && $('#chkAutoUpdate').parent().hide();
    }, 
    function(){
        $('.geEditor').html('<center style="margin-top:10%;">Error loading resource files. Please check browser console.</center>');
    });
}

function configExpression(cell){
	const expression = cell.getAttribute('expression');
	ConfigExpression.show({
		expression: cell.getAttribute('expression'),
		autoRefresh: cell.getAttribute('autoRefresh'),
		onApply: function(expression, autoRefresh){
			cell.setAttribute('expression', expression);
			cell.setAttribute('autoRefresh', autoRefresh);
		}
	});
}

let chartDropdownBuilt = false;
function configCell(cell){
	let type = cell.getAttribute('type');
	if(type == 'gauge'){
		const configValues = Object.assign({}, defaultGaugeConfig);
		const config = cell.getAttribute('config').split('\n');
		config.forEach(item=>{
			const cs = item.split(':');
			if(cs.length > 1){
				const attr = cs[0].trim();
				const value = item.substr(cs[0].length + 1).trim();
				(configValues[attr] !== undefined) && (configValues[attr] = value);
			}
		});
		for(let attr in configValues){
			$('#' + attr).val(configValues[attr]);
			configValues[attr].toString().startsWith('#') && $('#' + attr).css('backgroundColor', configValues[attr]);
		}

		$('.box-config-gauge').dialog({
			title: 'Config Gauge',
			buttons: {
				'Apply': function(){
					let cfg = '';
					for(let attr in configValues){
						const value = $('#' + attr).val();
						(value + '' !== defaultGaugeConfig[attr] + '') && (cfg += (cfg == '' ? '' : '\n') + attr + ':' + $('#' + attr).val());
					}
					cell.setAttribute('config', cfg);
					checkCellAsDashBox(cell);
					$(this).dialog('close')
				},
				'Close': function(){$(this).dialog('close')},
			}
		});
	}
	else if(type == 'chart'){
		!chartDropdownBuilt && (chartDropdownBuilt = true) &&
        EB.buildDropdown('#filter-selectChart', {
            class: 'search',
            id: 'selectChart',
            defaultText: 'Select a chart...',
            options: {clearable: false, fullTextSearch: true},
            onChange: function(){
            },
            list: {
                items: EB['charts']
            }
        });
		let chartId = cell.getAttribute('chartId');
		!chartId && $('#filter-selectChart').dropdown('clear');
		$('#filter-selectChart').dropdown('set selected', chartId);
		$('.box-config-chart').dialog({
			title: 'Config Chart',
			height: 400,
			width: 300,
			buttons: {
				'Apply': function(){
					chartId = $('#filter-selectChart').dropdown('get value');
					const chartName = $('#filter-selectChart').dropdown('get text');
					const item = $('#filter-selectChart').dropdown('get item', chartId);

					cell.setAttribute('chartId', chartId);
					cell.setAttribute('chartName', chartName);
					cell.setAttribute('config', $(item).attr('data-config'));

					checkCellAsDashBox(cell);
					$(this).dialog('close')
				},
				'Close': function(){$(this).dialog('close')},
			}
		});
	}
	else if(type == 'datagrid'){
		$('#datagridQuery').val(cell.getAttribute('datagridQuery'));
		$('#datagridHeader').val(cell.getAttribute('datagridHeader'));
		$('#datagridStyle').val(cell.getAttribute('datagridStyle'));
		$('.box-config-datagrid').dialog({
			title: 'Config Datagrid',
			height: 530,
			width: 680,
			buttons: {
				'Apply': function(){
					cell.setAttribute('datagridQuery', $('#datagridQuery').val());
					cell.setAttribute('datagridHeader', $('#datagridHeader').val());
					cell.setAttribute('datagridStyle', $('#datagridStyle').val());
					$(this).dialog('close')
				},
				'Close': function(){$(this).dialog('close')},
			}
		});
	}
}

function getCellChart(cell){
	let dashBox;
	let tmp = graph.view.getState(cell);
	tmp && tmp.text.node && (tmp = $(tmp.text.node).find('.html-box')) && tmp.length && (dashBox = tmp[0]);
	if(dashBox){
		return $(dashBox).highcharts();
	}
	return null;
}

function showDataGrid(cell){
	let dashBox;
	let tmp = graph.view.getState(cell);
	tmp && tmp.text.node && (tmp = $(tmp.text.node).find('.html-box')) && tmp.length && (dashBox = tmp[0]);
	if(!dashBox){
		const label = '<div class="html-box center-box" id="' + cell.getBoxID() + '" style="width:' + (cell.geometry.width - 2) + 'px;height:' + (cell.geometry.height - 2) + 'px"><p><img height="32" src="' + STENCIL_PATH + '/clipart/grid.png"><br><span class="chart-name">Chart</span></p></div>';
		cell.setAttribute('label', label);
		setTimeout(() => {showDataGrid(cell)}, 0);
		return;
	}
	if(window.isDashboard){
		const config = {
			querystring: cell.getAttribute('datagridQuery'),
			header: cell.getAttribute('datagridHeader'),
			style: cell.getAttribute('datagridStyle'),
			id: cell.id,
		}
		EBDataGrid.create(dashBox, config);
		return;
	}
	const stBoxSize = dashBox.style.width + ':' + dashBox.style.height;
	const stCellSize = (cell.geometry.width - 2) + 'px:' + (cell.geometry.height - 2) + 'px';
	stBoxSize != stCellSize && $(dashBox).css({width: cell.geometry.width - 2, height: cell.geometry.height - 2});
	let chartName = cell.getAttribute('chartName');
	!chartName && (chartName = 'Chart');
	$(dashBox).find('.chart-name').html(chartName);
}

function showChart(cell){
	let dashBox;
	let tmp = graph.view.getState(cell);
	tmp && tmp.text.node && (tmp = $(tmp.text.node).find('.html-box')) && tmp.length && (dashBox = tmp[0]);
	if(!dashBox){
		const label = '<div class="html-box center-box" id="' + cell.getBoxID() + '" style="width:' + (cell.geometry.width - 2) + 'px;height:' + (cell.geometry.height - 2) + 'px"><p><img height="32" src="' + STENCIL_PATH + '/clipart/chart.png"><br><span class="chart-name">Chart</span></p></div>';
		cell.setAttribute('label', label);
		setTimeout(() => {showChart(cell)}, 0);
		return;
	}
	if(window.isDashboard){
		const config = cell.getAttribute('config').trim();
		config && EBChart.create(dashBox, config);
		$(dashBox).css('pointer-events', 'none').parent().dblclick(function(){
			let data = [];
			const chart = $(dashBox).highcharts();
			chart.series.forEach((seri, index) => {
				data.push([]);
				const maxLeng = 60;
				let timeBase;
				for(let i = Math.max(seri.data.length - maxLeng, 0), l = seri.data.length; i < l; i++){
					data[index].length == 0 ?
					(timeBase = seri.data[i].x / 1000) && data[index].push(timeBase, seri.data[i].y) :
					data[index].push(seri.data[i].x / 1000 - timeBase, seri.data[i].y);
				}
			});
			window.open('dv-graph?id=' + cell.getAttribute('chartId') + '&date=' + $('#filter-date').dateString()+'&data=' + JSON.stringify(data));
		});
		return;
	}
	const stBoxSize = dashBox.style.width + ':' + dashBox.style.height;
	const stCellSize = (cell.geometry.width - 2) + 'px:' + (cell.geometry.height - 2) + 'px';
	stBoxSize != stCellSize && $(dashBox).css({width: cell.geometry.width - 2, height: cell.geometry.height - 2});
	let chartName = cell.getAttribute('chartName');
	!chartName && (chartName = 'Chart');
	$(dashBox).find('.chart-name').html(chartName);
}

function showGauge(cell, config){try{
	let dashBox;
	let tmp = graph.view.getState(cell);
	tmp && tmp.text.node && (tmp = $(tmp.text.node).find('.html-box')) && tmp.length && (dashBox = tmp[0]);
	if(!dashBox)
	{
		const type = 'Gauge';
		const label = '<div class="html-box center-box" id="' + cell.getBoxID() + '" style="width:' + (cell.geometry.width - 2) + 'px;height:' + (cell.geometry.height - 2) + 'px">' + type + '</div>';
		cell.setAttribute('label', label);
		setTimeout(() => {showGauge(cell, config)}, 0);
		return;
	}
	const gaugeConfig = {
        chart: {
            type: 'gauge',
            backgroundColor: 'transparent',
            style: {
                fontFamily: 'Arial'
            }
        },
        credits: {
            enabled: false
        },
        title: {
            text: defaultGaugeConfig.title,
            verticalAlign: 'bottom',
			floating: true,
			style: {
				color: defaultGaugeConfig.titleColor,
				fontSize: defaultGaugeConfig.titleSize
			},
			y: defaultGaugeConfig.titleDistance
        },

        pane: [{
            startAngle: defaultGaugeConfig.angleBegin,
            endAngle: defaultGaugeConfig.angleEnd,
            background: null,
        },],

        exporting: {
            enabled: false
        },

        tooltip: {
            enabled: false
        },

        yAxis: {
            min: defaultGaugeConfig.valueMin,
            max: defaultGaugeConfig.valueMax,
            minorTickPosition: 'outside',
			tickPosition: 'outside',
			lineColor: defaultGaugeConfig.scaleColor, 
			tickColor: defaultGaugeConfig.scaleColor, 
			minorTickColor: defaultGaugeConfig.scaleColor,
            labels: {
				rotation: 'auto',
				color: defaultGaugeConfig.scaleLabelColor,
                distance: 20
            },
            plotBands: {
                from: defaultGaugeConfig.redBandMin,
                to: defaultGaugeConfig.redBandMax,
                color: '#C02316',
                innerRadius: '90%',
                outerRadius: '100%'
            },
            pane: 0,
            title: {
                text: '<span style="font-size:' + defaultGaugeConfig.propertySize + 'px;color:' + defaultGaugeConfig.propertyColor + '">' + defaultGaugeConfig.property + '</span><br/><span style="font-size:' + defaultGaugeConfig.uomSize + 'px;color:' + defaultGaugeConfig.uomColor + '">' + defaultGaugeConfig.uom + '</span>',
            }
        },

        plotOptions: {
            gauge: {
                dataLabels: {
                    enabled: true,
                    style:{ 
                        fontSize: defaultGaugeConfig.valueSize,
                        color: defaultGaugeConfig.valueColor,
                        padding: 0,
                    },
                },
                dial: {
					radius: '105%',
					backgroundColor: defaultGaugeConfig.dialColor
                }
            }
        },
		property: defaultGaugeConfig.property,
		propertySize: defaultGaugeConfig.propertySize,
		propertyColor: defaultGaugeConfig.propertyColor,
		uom: defaultGaugeConfig.uom,
		uomSize: defaultGaugeConfig.uomSize,
		uomColor: defaultGaugeConfig.uomColor,
	
        series: [{
			//data: [defaultGaugeConfig.valueMin + Math.round(Math.random()*(defaultGaugeConfig.valueMax - defaultGaugeConfig.valueMin))],
			data: [defaultGaugeConfig.valueMin],
        },]
    }

	try{
		//graph.model.beginUpdate();
		const stBoxSize = dashBox.style.width + ':' + dashBox.style.height;
		const stCellSize = (cell.geometry.width - 2) + 'px:' + (cell.geometry.height - 2) + 'px';
		stBoxSize != stCellSize && $(dashBox).css({width: cell.geometry.width - 2, height: cell.geometry.height - 2});
		!config && (config = cell.getAttribute('config').trim());

		const cgfs = config.split('\n');
		const configObj = {};
		cgfs.forEach(item => {
			const cs = item.split(':');
			if(cs.length > 1){
				const attr = cs[0].trim();
				const value = item.substr(cs[0].length + 1).trim();
				if(defaultGaugeConfig[attr] + '' != value + '')
					switch(attr){
						case 'title': $.extend(true, configObj, {title: {text: value}}); break;
						case 'titleDistance': $.extend(true, configObj, {title: {y: Number(value)}}); break;
						case 'titleSize': $.extend(true, configObj, {title: {style: {fontSize: value}}}); break;
						case 'titleColor': $.extend(true, configObj, {title: {style: {color: value}}}); break;
						case 'valueExpression': $.extend(true, configObj, {valueExpression: value}); break;
						case 'valueSize': $.extend(true, configObj, {plotOptions: {gauge: {dataLabels: {style: {fontSize: Number(value)}}}}}); break;
						case 'valueColor': $.extend(true, configObj, {plotOptions: {gauge: {dataLabels: {style: {color: value}}}}}); break;
						case 'valueMin': $.extend(true, configObj, {yAxis: {min: Number(value)}}); break;
						case 'valueMax': $.extend(true, configObj, {yAxis: {max: Number(value)}}); break;
						case 'property': $.extend(true, configObj, {property: value}); break;
						case 'propertySize': $.extend(true, configObj, {propertySize: Number(value)}); break;
						case 'propertyColor': $.extend(true, configObj, {propertyColor: value}); break;
						case 'uom': $.extend(true, configObj, {uom: value}); break;
						case 'uomSize': $.extend(true, configObj, {uomSize: Number(value)}); break;
						case 'uomColor': $.extend(true, configObj, {uomColor: value}); break;
						case 'angleBegin': $.extend(true, configObj, {pane: {startAngle: Number(value)}}); break;
						case 'angleEnd': $.extend(true, configObj, {pane: {endAngle: Number(value)}}); break;
						case 'redBandMin': $.extend(true, configObj, {yAxis: {plotBands: {from: Number(value)}}}); break;
						case 'redBandMax': $.extend(true, configObj, {yAxis: {plotBands: {to: Number(value)}}}); break;
						case 'scaleColor': $.extend(true, configObj, {yAxis: {lineColor: value, tickColor: value, minorTickColor: value}}); break;
						case 'scaleLabelColor': $.extend(true, configObj, {yAxis: {labels: {style: {color: value}}}}); break;
						case 'dialColor': $.extend(true, configObj, {plotOptions: {gauge: {dial: {backgroundColor: value}}}}); break;
					};
			}	
		});

		let isConfigChanged = !jQuery.isEmptyObject(configObj);
		isConfigChanged && $.extend(true, gaugeConfig, configObj);

		let caption = '';
		gaugeConfig.property && (caption += '<span style="' + (gaugeConfig.propertySize ? 'font-size:' + gaugeConfig.propertySize + 'px;' : '') + (gaugeConfig.propertyColor ? 'color:' + gaugeConfig.propertyColor : '') + '">' + gaugeConfig.property + '</span><br>');
		gaugeConfig.uom && (caption += '<span style="' + (gaugeConfig.uomSize ? 'font-size:' + gaugeConfig.uomSize + 'px;' : '') + (gaugeConfig.uomColor ? 'color:' + gaugeConfig.uomColor : '') + '">' + gaugeConfig.uom + '</span>');
		caption && $.extend(true, gaugeConfig, {yAxis: {title: {text: caption}}});

		const chart = $(dashBox).highcharts();
		if(chart){
			isConfigChanged && chart.update(gaugeConfig);
			stBoxSize != stCellSize && chart.reflow();
		}
		else{
			Highcharts.chart(dashBox, gaugeConfig);
		}
	}catch(e){
		console.log(e);
	}
}
catch(e){
	console.log(e);
}}

function deleteDiagram(sId)
{
    if(!confirm("Are you really want to delete this diagram?")) return;

    param = {
			'ID' : sId
	}

	sendAjax('/deletediagram', param, function(data){
		var str = "";
		if(data.length > 0){
			$("#listSavedDiagrams").html(str);
			for(var v in data){
				str+="<a href=\"javascript:loadSavedDiagram('"+data[v].NAME+"','"+data[v].ID+"')\">"+data[v].NAME+"</a>&nbsp;<a href=\"javascript:deleteDiagram('"+data[v].ID+"')\"><font size='1' color='#ff0000'>[Delete]</font></a>&nbsp;<br>";
			}

			$("#listSavedDiagrams").html(str);
		}
	});
}

function hideBoxDiagrams()
{
	$("#listSavedDiagrams").is(':ui-dialog') && $("#listSavedDiagrams").dialog("close");
}

function isDashBoxType(type){
	return (type == 'gauge' || type == 'chart');// || type == 'workflow' || type == 'chart' || type == 'datagrid' || type == 'datavalue' || type == 'textblock');
}

function checkCellAsDashBox(cell, type){
	!type && (type = cell.getAttribute('type'));
	type=='gauge' && showGauge(cell);
	type=='chart' && showChart(cell);
	isDashboard && type=='datagrid' && showDataGrid(cell);
}

function loadGraphSource(xmlSource){
	const doc = mxUtils.parseXml(xmlSource).documentElement;
	ed.setGraphXml(doc);
	checkDashBoxInDiagram();
	ui.setPageVisible(false);
	const fit = doc.getAttribute('fit') == 1;
	const autoUpdate = doc.getAttribute('autoUpdate') == 1;
	$('.chk-auto-update').checkbox(autoUpdate ? 'check' : 'uncheck');
	if(isDashboard){
		$('.geDiagramContainer').css('margin', fit ? 30 : 0);
	}
	else {
		$('#chkFitOnLoaded').prop('checked', fit);
	}
	fit && graph.fit();
	graph.center(); //Phai goi lenh nay hoac graph.refresh hoac graph.fit, neu khong thi se bi loi khong hien thi 01 gauge?!!!!
	ed.undoManager && ed.undoManager.clear();
	ed.setModified(false);
}

function checkDashBoxInDiagram(){
	const cells = graph.model.cells;
	for(let c in cells)
		checkCellAsDashBox(cells[c]);
}

function loadSavedDiagram(sName, sId, fnLoaded)
{
    hideBoxDiagrams();
	sendAjax("loaddiagram/" + sId, {ajaxType: 'get'},
		function(data){
			clearGraph();
			loadGraphSource(data);
			setCurrentDiagramName(sName);
			setCurrentDiagramId(sId);
			fnLoaded && fnLoaded();
		},
		function(){
			alert('Error loading saved diagram!');
		}
	);
}

var defaultDiagramName="[Untitled Diagram]";
var defaultDiagramId= 0;
var currentDiagramName=defaultDiagramName;
var currentDiagramId = defaultDiagramId;

function setCurrentDiagramName(s)
{
    currentDiagramName=s;
    $('[network-title]').html(currentDiagramName);
}

function setCurrentDiagramId(i){
    currentDiagramId = i;
}

function showBoxDiagrams(){
	$( "#listSavedDiagrams" ).dialog({
			height: 400,
			width: 450,
			modal: true,
			title: "Select a network diagram",
		});
}

var currentSubnetworkID;
var justclicksubnetwork=false;
function listSubnetworkClick()
{
    if(justclicksubnetwork){justclicksubnetwork=false; return;}
    if(currentSubnetworkID)
    {
        graph.setCellStyles("highlight", "0", [graph.model.getCell(currentSubnetworkID)]);
    }
    var elements = document.getElementById("listSubnetwork").options;
    for(var i = 0; i < elements.length; i++){
        elements[i].selected = false;
    }
}

function loadDiagram()
{
	$("#listSavedDiagrams").html("Loading...");
	showBoxDiagrams();

	param = {
		'ID' : -100,
		diagramType: diagramType
	}

	sendAjax('/getdiagram', param, function(data){
		var str = "";
		if(data.length > 0){
			$("#listSavedDiagrams").html(str);
			for(var v in data){
				str+="<a href=\"javascript:loadSavedDiagram('"+data[v].NAME+"','"+data[v].ID+"')\">"+data[v].NAME+"</a>&nbsp;<a href=\"javascript:deleteDiagram('"+data[v].ID+"')\"><font size='1' color='#ff0000'>[Delete]</font></a>&nbsp;<br>";
			}
			$("#listSavedDiagrams").html(str);
		} else {
            $("#listSavedDiagrams").html("No diagram found");
		}
	});
}

function clearGraph()
{
    graph.removeCells(graph.getChildCells(graph.getDefaultParent(), true, true));
}

function newDiagram(){
    clearGraph();
	setCurrentDiagramId(0);
    setCurrentDiagramName(defaultDiagramName);
}

function saveDiagram(saveAs)
{
    try
    {
		if(saveAs) currentDiagramId=0;
        if(currentDiagramName=="" || !currentDiagramName || currentDiagramName ==defaultDiagramName || saveAs)
            currentDiagramName=mxUtils.prompt('Please input diagram name', currentDiagramName);
        if(currentDiagramName=="" || !currentDiagramName){
            return;
        }
        setCurrentDiagramName(currentDiagramName);
        var enc = new mxCodec();
		var node = enc.encode(graph.getModel());
		node.setAttribute('fit', $('#chkFitOnLoaded').is(':checked') ? '1' : '0');
		node.setAttribute('autoUpdate', $('#chkAutoUpdate').is(':checked') ? '1' : '0');

		//correct dashbox size
		for(var i = 0, l = node.children[0].children.length; i < l; i++){
			var cell = node.children[0].children[i];
			var type = cell.getAttribute('type');
			if(type == 'gauge' || type == 'chart' || type == 'datagrid'){
				var label = cell.getAttribute('label');
				var width = Number(cell.children[0].children[0].getAttribute('width'));
				var height = Number(cell.children[0].children[0].getAttribute('height'));
				var p1 = label.indexOf('width:');
				var p2 = label.indexOf('height:', p1);
				var p3 = label.indexOf('px', p2);
				p1 > 0 && p3 > p1 && width > 0 && height > 0 && (label = label.substr(0, p1) + 'width:' + (width - 2) + 'px;height:' + (height - 2) + label.substr(p3));
				cell.setAttribute('label', label);
			}
		}
		
		var source = mxUtils.getPrettyXml(node);

        param = {	
            'ID': currentDiagramId,
			'NAME': currentDiagramName,
			'TYPE': diagramType,
            'KEY': encodeURIComponent(source)
         }

		 $('#main-container').attr('loading', '');
		 sendAjax('/savediagram', param, function(data){
			alert('Saved');
			ed.setModified(false);
			currentDiagramId = data;
    	}, null, function(){
			$('#main-container').removeAttr('loading');
		});	
    }
    catch(err)
    {
        alert(err.message);
    }
}

window.onbeforeunload = function() { return mxResources.get('changesLost'); };