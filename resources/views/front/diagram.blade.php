<?php
use App\Models\IntObjectType;

$currentSubmenu = '/diagram';
$cur_diagram_id = 0;

$floatContents = ['editBoxContentview','objectMappingView'];
$useFeatures	= [
				['name'	=>	"filter_modify",
				"data"	=>	["isFilterModify"	=> true,
							"isAction"			=> false]],
				['name'	=>	"display_diagram",
				"data"	=>	[]]
];
$objectTypes = IntObjectType::all();
?>


@extends('core.bsdiagram')

@section('editBoxContentview')
@parent
<div id="fieldsConfigContainer" style='height:100%;width: 100%;overflow:auto'>
	@include('core.fields_config')
</div>
@stop

@section('adaptData')
<style>
.ui-dialog-titlebar-close{
	float	: right;
}
</style>

<script type="text/javascript">
$(document).ready(function () {
    $(window).resize(function(){
        var height = $(window).height();
        var width = $(window).width();
		$("#groupTabele").css('height',height - 135);
        $("#icons").css('height',height - 340);
        $("#graph").css('height',height - 130);
        $("#graph").css('width',width - 282);
    });
});

var objectTypes = <?php echo json_encode($objectTypes); ?>;
$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	});

	$('#cboProdUnit').on('change', function() {
		diagram.change('cboProdUnit');
// 		diagram.change('cboArea');
// 		diagram.change('cboFacility');
	});

	$('#cboArea').on('change', function() {
		diagram.change('cboArea');
	});

	$('select').on('change', function() {
		if(this.id+"" === "cboFacility"){
			diagram.change('cboFacility');
		}
	});

	$('#surveillanceSetting').css('height', 'auto');

	$("#tdShowHideToolBox").click(function(){
		if($("#tdToolBox").is(":visible"))
		{
			$("#tdToolBox").hide();
			$("#imgShowHideToolBox").attr("src","/images/arrow_right.png");
			$("#graph").css("width",$(window).width()-30);
		}
		else
		{
			$("#tdToolBox").show();
			$("#imgShowHideToolBox").attr("src","/images/arrow_left.png");
			$("#graph").css("width",$(window).width()-$("#tdToolBox").width()-30);
		}
	});

	$("#Qoccurdate" ).datepicker({
		changeMonth	:true,
		changeYear	:true,
		dateFormat	:jsFormat
	}); 

	$("[name=RR]").change(function()
		{
			var visibleLabel=$("#fp2").prop('checked');
			
			//Get all cells
			var cells=ed.graph.model.cells; //ed.graph.model.getChildVertices(ed.graph.getDefaultParent());
			for(c in cells)
			{
				var id=cells[c].getId();
				if(id.substr(0,5)=='label')
					cells[c].setVisible(visibleLabel);
			}
			ed.graph.refresh();
	});

	$('.sur_tabs_holder').skinableTabs({
		effect: 'basic_display',
		skin: 'skin4',
		position: 'top'
	});

	$("#sur_flow_phase").html($("#Qflowphase").html());	

	$("#outlineContainer").css("height",150);	
	
});

var diagram = {

		change: function(id,params){
			var table = ""
			var cboSet = "";
			var value = -1;
			var keysearch = "";
			params = params!==undefined?params:null;
			if(id == "cboProdUnit"){
				table = "LoArea";
				cboSet = "cboArea";
				keysearch = 'PRODUCTION_UNIT_ID';
				value =  $('#'+id).val();
			}

			if(id == "cboArea"){
				table = "Facility";
				cboSet = "cboFacility";
				keysearch = 'AREA_ID';
				value =  $('#'+id).val();
			}

			if(id == "cboFacility" || id == "cboObjType"){			
				table = $("#cboObjType option:selected").text();
				cboSet = "cboObjs";
				keysearch = 'FACILITY_ID';
				value =  $('#cboFacility').val();

				$('#txtObjType').text(table);
			}		

			if(table != ""){
				param = {
						'TABLE' : table,
						'value': value,
						'keysearch' : keysearch
				}
				
				$("#"+cboSet).prop("disabled", true);  
				sendAjax('/onChangeObj', param, function(data){
					diagram.loadCbo(cboSet, data);
					if(typeof params == "object" &&params!=null){
						if(cboSet=="cboProdUnit"){
							$('#cboProdUnit').val(params.cboProdUnit);
							$('#cboArea').val(params.cboArea);
							diagram.change("cboArea",params);
						}
						else if(cboSet=="cboArea"){
							$('#cboArea').val(params.cboArea);
							$('#cboFacility').val(params.cboFacility);
							diagram.change("cboFacility",params);
						}
						else if(cboSet=="cboFacility"){
							$('#cboFacility').val(params.cboFacility);
							diagram.change("cboObjType",params);
						}
						else if(cboSet=="cboObjType"){
							$('#cboObjType').val(params.objtype);
							$('#cboObjs').val(params.cboObjs);
						}
					}
					else{
						if(cboSet=="cboArea") diagram.change("cboArea");
						else if(cboSet=="cboFacility") diagram.change("cboFacility"); 
					}
				});
			}
		},
		
		loadCbo : function(id, data){
			var cbo = '';
			$('#'+id).html(cbo);
			for(var v in data){
				cbo +='<option value="'+data[v].ID+'">'+data[v].NAME+'</option>';
			}

			$('#'+id).html(cbo);
			$("#"+id).prop("disabled", false);  

			if(id == "cboObjs"){
				if(currentObjectMapping.getAttribute('object_id') > 0){
					$("#cboObjs").val(currentObjectMapping.getAttribute('object_id'));
				}
			}
		},

		loadSurveillanceSetting : function(data){
			var strCbo = '';
			var strCheck = '';
			var strConnection = '';
			var strTag = '';
			var cfgFieldProps = data.cfgFieldProps;
			var intConnection = data.intConnection;
			var tags = data.tags;

			$('#sur_fields').html(strCheck);
			$('#sur_fields_select').html(strCbo);
			$('#cboConnection').html(strConnection);

			if(cfgFieldProps.length > 0){
				for(var v in cfgFieldProps){
					strCheck +='<input type="checkbox" style="width:18px; height:15px;" surveilance_settings="' + cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME +'" '+ cfgFieldProps[v].CHECK+' value="'+checkValue(cfgFieldProps[v].LABEL, cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME) + '">'+ cfgFieldProps[v].TABLE_NAME +'.<font color="#378de5"><b>'+cfgFieldProps[v].COLUMN_NAME+'</b></font>('+checkValue(cfgFieldProps[v].LABEL,'')+') <br>';
					strCbo +='<option value="' + checkValue(cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME, '') +'">'+ checkValue(cfgFieldProps[v].TABLE_NAME + '/' + cfgFieldProps[v].COLUMN_NAME, '') +'</option>';
				}
			}

			if(intConnection.length > 0){
				for(var z in intConnection){
					strConnection +='<option value="' + intConnection[z].ID +'">'+ intConnection[z].NAME +'</option>';
				}
			}

			var surveillance = currentObjectMapping.getAttribute('surveillance');
			var otherTags = [];
			if(surveillance!=null&&surveillance!=''){
				var splits = surveillance.split('@');
				$.each(splits, function(key, split) {
					var pair = split.split(":");
					if(pair.length>1&&pair[0]=='TAG'){
						otherTags.push(pair[1])
					}
			    });
			}

			if(tags.length > 0){
				for(var x in tags){
					var checked = "";
					if(otherTags.length>0){
						var foundIndex = $.inArray(tags[x].TAG_ID, otherTags);
						if(foundIndex>=0){
							checked = "checked";
							otherTags.splice(foundIndex, 1);
						}
					}
					tagNameInput = tags[x].TAG_ID==tags[x].NAME?tags[x].TAG_ID:tags[x].TAG_ID+ " ("+tags[x].NAME+")";
					tagNameLabel = tags[x].NAME;
					strTag +='<input type="checkbox" style="width:18px; height:15px;" '+checked
					+' surveilance_settings="'+ tags[x].TAG_ID +'"'
					+' surveilance_tagName="'+ tagNameLabel +'"'
					+ tags[x].CHECK+' value='
					+tags[x].TAG_ID +'>'+ tagNameInput +'<br>';
				}
			}

			$('#sur_fields').html(strCheck);
			$('#sur_fields_select').html(strCbo);
			$('#cboConnection').html(strConnection);
			
			if(tags.length > 0){
				$('#sur_tag_content').html(strTag);
			}else{
				$('#sur_tag_content').html(data.strMessage);
			}

			$('#openSurveillanceSetting').click(function(){
				$('#surveillanceSetting').dialog('close');
				showObjectMapping();
			})

			var phaseConfig = getSurveilanceObject(currentObjectMapping);
			var conn_id		= currentObjectMapping.getAttribute('conn_id');
			if(conn_id != 0){
				$('#cboConnection').val(conn_id);
			}
			$('#txt_sur_other_tag').val(otherTags.join(','));

			$('#sur_phase_list').html('');
			if(typeof phaseConfig == "object" &&phaseConfig.length>0){
				for (i = 0; i < phaseConfig.length; i++) {
					var attrs		= phaseConfig[i];
					var phase_id	= attrs.phaseId;
					var eventType	= attrs.eventType;
					var prefix		= attrs.prefix;
					var subfix		= attrs.subfix;
					var dataField	= attrs.dataField;
					if(i==0) $("#sur_fields_select").val(dataField);
					this.addPhaseObject(phase_id,eventType,prefix,subfix,dataField);
				}
			}

			$('#btnTagsMapping').click(function(){
				window.open("/tagsMapping", '_blank');
			});
			
		},
		addPhaseObject : function(phase_id,eventType,prefix,subfix,dataField){
			var addingField 	= getSurveilanceTextId("tmp",dataField,prefix,subfix,phase_id,eventType);
			addingField = addingField.replace(/\//g,'kkkk');
			addingField = addingField.replace(/\s/g,'');
			addingField = addingField.replace(/[^\w\s]/gi, '');
			addingField = addingField.replace("kkkk", '-');
			
			if($("#"+addingField).length>0){
				$("#"+addingField).effect("highlight", {}, 2000);
				return;
			};

			var surveilanceData = {
									phaseId		: phase_id,
									eventType	: eventType,
									prefix		: prefix,
									subfix		: subfix,
									dataField	: dataField,
									addingField	: addingField,
									value		: "--",
								};
			var phase_name		= $("#sur_flow_phase  option[value='"+phase_id+"']").text();
			var eventTypeName	= $("#sur_event_type  option[value='"+eventType+"']").text();
			var li 				= $("<li class='x_item sur_phase_item'></li>");
			var span 			= $("<span></span>");
			var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');
			span.text('['+prefix+'] '+phase_name+" - "+eventTypeName+' ['+subfix+']'+' ['+dataField+']');
			li.attr("id",addingField);
			span.appendTo(li);
			del.appendTo(li);
			li.data(surveilanceData);
			li.appendTo($("#sur_phase_list"));
		},

		add_sur_phase : function(){
			var phase_id, eventType, prefix, subfix, dataField;
			phase_id	=$('#sur_flow_phase').val();
			eventType	=$('#sur_event_type').val();
			prefix		=$('#txt_sur_phase_prefix').val();
			subfix		=$('#txt_sur_phase_subfix').val();
			dataField	=$('#sur_fields_select').val();
			this.addPhaseObject(phase_id,eventType,prefix,subfix,dataField);
		},
		
		del_sur_phase : function(addingField){
			$('#'+addingField).remove();
		},

		exportImage : function(type){
			saveSvgAsPng($('#graph svg')[0], 'diagram.png');
		}
}

var ed;
function onInit(editor)
{
    ed=editor;
    // Enables rotation handle
    mxVertexHandler.prototype.rotationEnabled = true;

    // Enables guides
    mxGraphHandler.prototype.guidesEnabled = true;

    // Alt disables guides
    mxGuide.prototype.isEnabledForEvent = function(evt)
    {
        return !mxEvent.isAltDown(evt);
    };

    // Enables snapping waypoints to terminals
    mxEdgeHandler.prototype.snapToTerminals = true;

    // Defines an icon for creating new connections in the connection handler.
    // This will automatically disable the highlighting of the source vertex.
    mxConnectionHandler.prototype.connectImage = new mxImage('/images/connector.gif', 16, 16);

    // Enables connections in the graph and disables
    // reset of zoom and translate on root change
    // (ie. switch between XML and graphical mode).
    editor.graph.setConnectable(true);

    editor.graph.setPanning(true);
    //editor.graph.panningHandler.useLeftButtonForPanning = true;


    // Clones the source if new connection has no target
    editor.graph.connectionHandler.setCreateTarget(false);
    editor.graph.setAllowDanglingEdges(false);

    var cellAddedListener = function(sender, evt)
    {
        var cells = evt.getProperty('cells');
        var cell = cells[0];
        if(editor.graph.isSwimlane(cell)){
            var DiagramName = mxUtils.prompt('Enter subnetwork name', 'Subnetwork');
            if(!DiagramName)
            {
                editor.graph.removeCells([cell]);
                return;
            }
            cell.setAttribute("label",DiagramName);
            addSubnetworkListItem(cell);
        }
    };

    var cellRemovedListener=function(sender, evt)
    {
        updateSubnetworksList();
    }
    editor.graph.addListener(mxEvent.CELLS_ADDED, cellAddedListener);
    editor.graph.addListener(mxEvent.CELLS_MOVED, cellMovedListener);
    editor.graph.addListener(mxEvent.CELLS_REMOVED, cellRemovedListener);

    // Updates the title if the root changes
    var title = document.getElementById('title');

    if (title != null)
    {
        var f = function(sender)
        {
            title.innerHTML = 'mxDraw - ' + sender.getTitle();
        };

        editor.addListener(mxEvent.ROOT, f);
        f(editor);
    }

	document.getElementById("graph").addEventListener("click", function(evt) {
		if (evt && (evt.which == 2 || evt.button == 4 )) {
			if(evt.shiftKey) {
				editor.execute('actualSize');
			}
		}
	});
	
	document.getElementById("graph").addEventListener("wheel", function(evt) {
		if(evt.shiftKey) {
			if(evt.deltaY > 0)
				editor.execute('zoomIn');
			else
				editor.execute('zoomOut');
		}
		else if(evt.altKey) {
			var p = editor.graph.view.getTranslate();
			editor.graph.view.setTranslate(p.x-evt.deltaY/2, p.y);
		}
		else {
			var p = editor.graph.view.getTranslate();
			editor.graph.view.setTranslate(p.x, p.y-evt.deltaY/2);
		}
	});

    mxEvent.addMouseWheelListener(function (evt, up)
    {
		return;
		//if(!evt.shiftKey) return;
        if (!mxEvent.isConsumed(evt))
        {
            if (up)
            {
				var p = editor.graph.view.getTranslate();
				editor.graph.view.setTranslate(p.x, p.y+40);
				//editor.graph.panGraph(0,10);
                //editor.execute('zoomIn');
            }
            else
            {
				var p = editor.graph.view.getTranslate();
				editor.graph.view.setTranslate(p.x, p.y-40);
				//editor.graph.panGraph(0,-10);
                //editor.execute('zoomOut');
            }

            mxEvent.consume(evt);
        }
    });

    // Defines a new action to switch between
    // XML and graphical display
    var textNode = document.getElementById('xml');
    var graphNode = editor.graph.container;
    var sourceInput = document.getElementById('source');
    sourceInput.checked = false;

    var funct = function(editor)
    {
        if (sourceInput.checked)
        {
            graphNode.style.display = 'none';
            textNode.style.display = 'inline';

            var enc = new mxCodec();
            var node = enc.encode(editor.graph.getModel());

            textNode.value = mxUtils.getPrettyXml(node);
            textNode.originalValue = textNode.value;
            textNode.focus();
        }
        else
        {
            graphNode.style.display = '';

            if (textNode.value != textNode.originalValue)
            {
                var doc = mxUtils.parseXml(textNode.value);
                var dec = new mxCodec(doc);
                dec.decode(doc.documentElement, editor.graph.getModel());
            }

            textNode.originalValue = null;

            // Makes sure nothing is selected in IE
            if (mxClient.IS_IE)
            {
                mxUtils.clearSelection();
            }

            textNode.style.display = 'none';

            // Moves the focus back to the graph
            textNode.blur();
            editor.graph.container.focus();
        }
    };

    editor.addAction('switchView', funct);

    // Defines a new action to switch between
    // XML and graphical display
    mxEvent.addListener(sourceInput, 'click', function()
    {
        editor.execute('switchView');
    });

    // Create select actions in page
    var node = document.getElementById('mainActions');
    var buttons = ['group', 'ungroup', 'cut', 'copy', 'paste', 'delete', 'undo', 'redo'];

    // Only adds image and SVG export if backend is available
    // NOTE: The old image export in mxEditor is not used, the urlImage is used for the new export.
    if (editor.urlImage != null)
    {
        // Client-side code for image export
        var exportImage = function(editor)
        {
            var graph = editor.graph;
            var scale = graph.view.scale;
            var bounds = graph.getGraphBounds();

            // New image export
            var xmlDoc = mxUtils.createXmlDocument();
            var root = xmlDoc.createElement('output');
            xmlDoc.appendChild(root);

            // Renders graph. Offset will be multiplied with state's scale when painting state.
            var xmlCanvas = new mxXmlCanvas2D(root);
            xmlCanvas.translate(Math.floor(1 / scale - bounds.x), Math.floor(1 / scale - bounds.y));
            xmlCanvas.scale(scale);

            var imgExport = new mxImageExport();
            imgExport.drawState(graph.getView().getState(graph.model.root), xmlCanvas);

            // Puts request data together
            var w = Math.ceil(bounds.width * scale + 2);
            var h = Math.ceil(bounds.height * scale + 2);
            var xml = mxUtils.getXml(root);

            // Requests image if request is valid
            if (w > 0 && h > 0)
            {
                var name = 'export.png';
                var format = 'png';
                var bg = '&bg=#FFFFFF';

                new mxXmlRequest(editor.urlImage, 'filename=' + name + '&format=' + format +
                    bg + '&w=' + w + '&h=' + h + '&xml=' + encodeURIComponent(xml)).
                    simulate(document, '_blank');
            }
        };

        editor.addAction('exportImage', exportImage);

        // Client-side code for SVG export
        var exportSvg = function(editor)
        {
            var graph = editor.graph;
            var scale = graph.view.scale;
            var bounds = graph.getGraphBounds();

            // Prepares SVG document that holds the output
            var svgDoc = mxUtils.createXmlDocument();
            var root = (svgDoc.createElementNS != null) ?
                svgDoc.createElementNS(mxConstants.NS_SVG, 'svg') : svgDoc.createElement('svg');

            if (root.style != null)
            {
                root.style.backgroundColor = '#FFFFFF';
            }
            else
            {
                root.setAttribute('style', 'background-color:#FFFFFF');
            }

            if (svgDoc.createElementNS == null)
            {
                root.setAttribute('xmlns', mxConstants.NS_SVG);
            }

            root.setAttribute('width', Math.ceil(bounds.width * scale + 2) + 'px');
            root.setAttribute('height', Math.ceil(bounds.height * scale + 2) + 'px');
            root.setAttribute('xmlns:xlink', mxConstants.NS_XLINK);
            root.setAttribute('version', '1.1');

            // Adds group for anti-aliasing via transform
            var group = (svgDoc.createElementNS != null) ?
                svgDoc.createElementNS(mxConstants.NS_SVG, 'g') : svgDoc.createElement('g');
            group.setAttribute('transform', 'translate(0.5,0.5)');
            root.appendChild(group);
            svgDoc.appendChild(root);

            // Renders graph. Offset will be multiplied with state's scale when painting state.
            var svgCanvas = new mxSvgCanvas2D(group);
            svgCanvas.translate(Math.floor(1 / scale - bounds.x), Math.floor(1 / scale - bounds.y));
            svgCanvas.scale(scale);

            var imgExport = new mxImageExport();
            imgExport.drawState(graph.getView().getState(graph.model.root), svgCanvas);

            var name = 'export.svg';
            var xml = encodeURIComponent(mxUtils.getXml(root));

            new mxXmlRequest(editor.urlEcho, 'filename=' + name + '&format=svg' + '&xml=' + xml).simulate(document, "_blank");
        };

        editor.addAction('exportSvg', exportSvg);

        buttons.push('exportImage');
        buttons.push('exportSvg');
    };

    //Begin: Them combo thay doi stroke color
    var colors = ['','red','green','blue'];
    var selectTag = document.createElement('select');
    node.appendChild(selectTag);
    var factoryColor = function(){
        return function(){
            var color = selectTag.options[selectTag.selectedIndex].value;
            editor.graph.model.beginUpdate();
            try
            {
                //alert(editor.graph.model.cells.);
                //if(!editor.graph.selectionModel.cells[0].isEdge()) return;
                var c;
                for (c in editor.graph.selectionModel.cells)
                {
                    if(editor.graph.selectionModel.cells[c].isEdge())
                    {
                        if (color=='') color='black';
                        //mxUtils.setCellStyles(editor.graph.model, [editor.graph.selectionModel.cells[c]],"strokeColor", color);
                        editor.graph.setCellStyles("strokeColor", color, [editor.graph.selectionModel.cells[c]]);

                    }
                }
                /*
                 if (color!='')
                 editor.graph.setCellStyles("strokeColor", color);
                 else
                 editor.graph.setCellStyles("strokeColor", 'black');
                 */
            }
            finally
            {
                editor.graph.model.endUpdate();
            }
        };
    };
    mxEvent.addListener(selectTag, 'change', factoryColor());
    for (var i=0;i<colors.length;i++)
    {
        var colorOption = document.createElement('option');
        mxUtils.write(colorOption, mxResources.get(colors[i]));
        colorOption.style.background = colors[i]
        colorOption.innerHTML="<span>"+colors[i]+"</span>";
        selectTag.appendChild(colorOption);
    }
    //End: Them combo thay doi stroke color

    for (var i = 0; i < buttons.length; i++)
    {
        var button = document.createElement('button');
        mxUtils.write(button, mxResources.get(buttons[i]));
        button.innerHTML=buttons[i];

        var factory = function(name)
        {
            return function()
            {
                //alert(name);
                editor.execute(name);
            };
        };

        mxEvent.addListener(button, 'click', factory(buttons[i]));
        node.appendChild(button);
    }

    // Create select actions in page
    var node = document.getElementById('selectActions');
    /*
     mxUtils.write(node, 'Select: ');
     mxUtils.linkAction(node, 'All', editor, 'selectAll');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'None', editor, 'selectNone');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Vertices', editor, 'selectVertices');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Edges', editor, 'selectEdges');
     */

    // Create select actions in page
    /*
     var node = document.getElementById('zoomActions');
     mxUtils.write(node, 'Zoom: ');
     mxUtils.linkAction(node, 'In', editor, 'zoomIn');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Out', editor, 'zoomOut');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Actual', editor, 'actualSize');
     mxUtils.write(node, ', ');
     mxUtils.linkAction(node, 'Fit', editor, 'fit');
     */

    //load diagram
    //loadSavedDiagram();

    //outlineContainer
    if(!outline)
    {
        outline = document.getElementById('outlineContainer');
        if (mxClient.IS_IE)
        {
            new mxDivResizer(outline);
        }

        // Creates the outline (navigator, overview) for moving
        // around the graph in the top, right corner of the window.
        var outln = new mxOutline(editor.graph, outline);
    }

    //loadListSavedDiagrams();
}

function buttonActionClick(act){
	if(act=="print"){
// 		applySurveilanceTags();return;
		var pageCount=1;
		var scale = mxUtils.getScaleForPageCount(pageCount, ed.graph);
		var preview = new mxPrintPreview(ed.graph, scale);
		var oldRenderPage = mxPrintPreview.prototype.renderPage;
		
		var title=$("#diagramName").text();
		var sur_date=$("#Qoccurdate").val()+"";
		preview.title=title+(sur_date!=""?" - Surveillance date: "+sur_date:"");
		preview.print();
		preview.close();
	}
    else{
    	if(act=="rotate"){
    		ed.graph.toggleCellStyles(mxConstants.STYLE_HORIZONTAL,"1",ed.graph.selectionModel.cells);
    	}else{ 
        	ed.execute(act);
    	}
    } 
}

function changeLineColor(color)
{
    ed.graph.model.beginUpdate();
    try
    {
        var c;
        for (c in ed.graph.selectionModel.cells)
        {
            if(ed.graph.selectionModel.cells[c].isEdge())
            {
                if (color=='') color='black';
                ed.graph.setCellStyles("strokeColor", color, [ed.graph.selectionModel.cells[c]]);
            }
        }
    }
    finally
    {
        ed.graph.model.endUpdate();
    }
}

function cellMovedListener(sender, evt){
	var cells = evt.getProperty('cells');
 	for (i = 0; i < cells.length; i++) {
	  	var cell=cells[i];
	  	if(cell.id!==null)
	  	if(cell.id.substr(0,8)=='sur_val_'){
	   		updateSurPhaseCellPosition(cell);
	  	}
	  	else if(cell.id.substr(0,6)=='label_'){
	   		updateLabelCellPosition(cell);
	  	}
 	}
	 //ed.graph.model.endUpdate();
	 ed.graph.refresh();
}

function updateSurPhaseCellPosition(baseCell){
	var id 	= 	baseCell.getAttribute("parentCelId");
	var ind	=	Number(baseCell.getAttribute("sur_phase_index"));
	$.each(ed.graph.getChildCells(), function(key, cell) {
		if(cell.id.lastIndexOf("sur_val_"+id+"_", 0) === 0 && cell.id != baseCell.id){
			var ind2=Number(cell.getAttribute("sur_phase_index"));
			if(ind2!=ind){
				cell.geometry.y=baseCell.geometry.y+(ind2-ind)*baseCell.geometry.height;
				cell.geometry.x=baseCell.geometry.x;
			}
		}
    });
	/* 
	var ind=Number(baseCell.getAttribute("sur_phase_index"));
	var id1=baseCell.id.substr(0,baseCell.id.lastIndexOf('_')+1);
	for(i=0;i<30;i++){
		var id		= baseCell.getId();
		var cell=ed.graph.model.getCell(id1+i);
		if(typeof cell!=='undefined'){
			var ind2=Number(cell.getAttribute("sur_phase_index"));
			if(ind2!=ind){
				cell.geometry.y=baseCell.geometry.y+(ind2-ind)*baseCell.geometry.height;
				cell.geometry.x=baseCell.geometry.x;
			}
		}
	} */
}

function updateLabelCellPosition(baseCell){
	var ind=Number(baseCell.getAttribute("label_index"));
	var id1=baseCell.getAttribute("label_subfixId");
	for(i=0;i<30;i++){
		var cell=ed.graph.model.getCell(id1+i);
		if(typeof cell!=='undefined'){
			var ind2=Number(cell.getAttribute("label_index"));
			if(ind2!=ind){
				cell.geometry.y=baseCell.geometry.y - (ind2-ind)*baseCell.geometry.height;
				cell.geometry.x=baseCell.geometry.x;
			}
		}
	}
}

function highlightContainer(a)
{
    //alert(graph);
    ed.graph.model.beginUpdate();
    try
    {
        var c;
        for (c in ed.graph.model.cells)
        {
            if(ed.graph.isSwimlane(ed.graph.model.cells[c]))
            {
                ed.graph.setCellStyles("highlight", a?"1":"0", [ed.graph.model.cells[c]]);
            }
        }
    }
    catch(err)
    {
        alert(err.message);
    }
    finally
    {
        ed.graph.model.endUpdate();
    }
}

function addSubnetworkListItem(cell)
{
    var DiagramName=cell.getAttribute("label");
    var list=document.getElementById("listSubnetwork");
    var entry = document.createElement('option');
    entry.appendChild(document.createTextNode(DiagramName));
    entry.setAttribute("cell_id",cell.id);
    entry.addEventListener('click',function(){
        //alert(cell.id);

        for(var i=0;i<entry.parentElement.children.length;i++)
        {
            var cID=entry.parentElement.children[i].getAttribute("cell_id");
            if(cID!=cell.id)
            {
                ed.graph.setCellStyles("highlight", "0", [ed.graph.model.getCell(cID)]);
            }
        }

        currentSubnetworkID=cell.id;
        justclicksubnetwork=true;
        ed.graph.setCellStyles("highlight", true, [cell]);
    },false);
    list.appendChild(entry);
}

function updateSubnetworksList()
{
    var elements = document.getElementById("listSubnetwork").options;
    for(var i = 0; i < elements.length; i++)
    {
        var cID=elements[i].getAttribute("cell_id");
        if(!ed.graph.model.getCell(cID))
        {
            document.getElementById("listSubnetwork").remove(i);
        }
    }
}

function clearGraph()
{
    ed.graph.removeCells(ed.graph.getChildVertices(ed.graph.getDefaultParent()));
}

var outline;
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
	$("#listSavedDiagrams").dialog("close");
}

function loadSavedDiagram(sName,sId)
{
    hideBoxDiagrams();
	if(showWaiting) showWaiting();
	
    mxUtils.get("loaddiagram/" + sId, 
		function(req){
			if(hideWaiting) hideWaiting();
			clearGraph();
			var node = req.getDocumentElement();

			var dec = new mxCodec(node.ownerDocument);
			dec.decode(node, ed.graph.getModel());

			ed.graph.refresh();
			updateSubnetworksList();

			setCurrentDiagramName(sName);
			setCurrentDiagramId(sId);

			var c;
			for (c in ed.graph.model.cells)
			{
				var cell=ed.graph.model.cells[c];
				if(ed.graph.isSwimlane(cell))
				{
					addSubnetworkListItem(cell);
				}
			}

		},
		function(){
			if(hideWaiting) hideWaiting();
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
    document.getElementById("diagramName").innerHTML=currentDiagramName;
}

function setCurrentDiagramId(i){
    currentDiagramId = i;
}


function getParams(){
	var occur_date=$("#Qoccurdate").val();
	var flow_phase=$("#Qflowphase").val();
	return  {
		'occur_date':occur_date,
		'flow_phase':flow_phase
	}
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
        ed.graph.setCellStyles("highlight", "0", [ed.graph.model.getCell(currentSubnetworkID)]);
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
			'ID' : -100
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
            currentDiagramName=mxUtils.prompt('Enter Diagram name', 'Untitled Diagram');
        if(currentDiagramName=="" || !currentDiagramName)
        {
            return;
        }
        setCurrentDiagramName(currentDiagramName);
        document.getElementById("buttonSave_text").innerHTML="Saving...";
        var enc = new mxCodec();
        var node = enc.encode(ed.graph.getModel());

        param = {	
            'ID':currentDiagramId,
            'NAME':currentDiagramName,
            'KEY':encodeURIComponent(mxUtils.getPrettyXml(node))
         }

    	sendAjax('/savediagram', param, function(data){
    		document.getElementById("buttonSave_text").innerHTML="Save";
			currentDiagramId=data;
    	});	
    }
    catch(err)
    {
        alert(err.message);
    }
}

window.onbeforeunload = function() { return mxResources.get('changesLost'); };
</script>
<body onLoad="new mxApplication('/config/diagrameditor.xml?13');"
	style="margin: 0px; background: #eeeeee;">
	
	
	<div id="box_cell_image" style="display: none">
			<span id="box_cell_image_input"> <br> Input image URL <input
				type="text" id="txt_cell_image_url" style="width: 470px"> <br> <br>
				or Upload from your computer <input type="file" name="files[]" multiple id="file_cell_image_url" style="width: 390px"> <br> <br> or <input
				type="button" onclick="pick_cell_image()"
				value="Pick available image">
			</span>
			<div id="box_pick_cell"	style="display: none; width: 100%; height: 100%"></div>
	</div>

	<div id="expressionSetting" style="display:none; padding:20px">
	<style>
	.exp_red{font-family:Courier New;color:red}
	.exp_blue{font-family:Courier New;color:blue}
	.exp_green{font-family:Courier New;color:green}
	.exp_magenta{font-family:Courier New;color:magenta}
	.exp_bold{font-family:Courier New;color:brown;font-weight:bold}
	</style>
		<textarea id="txt_expressionSetting" style="width: 100%;height: 270px;box-sizing: border-box;font-family:Courier New;font-size:9pt"></textarea>
		<br>
		<div style="line-height:30px"><b>Examples:</b></div>
		<span style="font-family:Courier New;font-size:8pt">
		Pressure: <span class="exp_blue">{Tag</span>(<span class="exp_red">PRESSURE TAG NAME</span>)} pa, Temp: {<span class="exp_bold">round</span>(<span class="exp_blue">FLOW_DATA_FDC_VALUE</span>.<span class="exp_green">OBS_TEMP</span>(<span class="exp_red">FLOW_ID=1</span>),2)}&#186;C<br>
		Volume {<span class="exp_blue">FLOW_DATA_FDC_VALUE</span>.<span class="exp_green">OBS_TEMP</span>(<span class="exp_red">FLOW_ID=1</span>)+<span class="exp_blue">ENERGY_UNIT_DATA_VALUE</span>.<span class="exp_green">EU_DATA_GRS_VOL</span>(<span class="exp_red">EU_ID=10,FLOW_PHASE=1,EVENT_TYPE=1</span>)}bbls
		</span>
	</div>
	
	<div id="surveillanceSetting"
		style="display: none; padding: 0; margin: 5px">
		<div class="sur_tabs_holder">
			<ul>
				<li><a href="#sur_fields">Fields</a></li>
				<li><a href="#sur_tag">Tags</a></li>
				<li><a href="#sur_phase">Phase config</a></li>
			</ul>
			<div class="sur_tab_content_holder" style="margin: 5px">
				<div id="sur_fields"
					style="overflow-y: auto; height: 318px; line-height: 18px"></div>
				<div id="sur_tag" style="height: 320px">
					<div id="sur_tag_content"
						style="overflow-y: auto; height: 250px; line-height: 18px"></div>
					<div
						style="position: absolute; left: 0px; bottom: 0px; width: 100%; height: 70px; background: #e8e8e8; padding: 10px 5px; box-sizing: border-box">
						<table>
							<tr>
								<td width="100"><b>Connection</b></td>
								<td><select id="cboConnection" style="min-width: 185px"></select></td>
							</tr>
							<tr>
								<td><b>Other tags</b></td>
								<td><input type="text" id="txt_sur_other_tag"
									style="width: 370px; height: 18px;"></td>
							</tr>
						</table>
					</div>
				</div>
				<div id="sur_phase"
					style="overflow-y: auto; height: 300px; line-height: 18px; padding: 10px;">
					Data field <select id="sur_fields_select"></select>
					<table>
						<tr>
							<td>Flow phase</td>
							<td>Event type</td>
							<td>Prefix</td>
							<td>Subfix</td>
							<td></td>
						</tr>
						<tr>
							<td><select id="sur_flow_phase"></select></td>
							<td>{{ \Helper::filter(["modelName"=>"CodeEventType",'id' =>'sur_event_type']) }}</td>
							<td><input id="txt_sur_phase_prefix" style="width: 100px; height: 18px;"></td>
							<td><input id="txt_sur_phase_subfix" style="width: 100px; height: 18px;"></td>
							<td><input type="button" style="width: 60px; height: 16px;" value="Add" onclick="diagram.add_sur_phase()"></input></td>
						</tr>
					</table>
					<hr>
					<b>Added flow phases:</b>
					<div><ul id="sur_phase_list" style="list-style-type: none;padding-left: 0px;"></ul></div>
				</div>
			</div>
		</div>
	</div>
	<td valign="top" align="center">
		<!-- Object mapping -->
		<div id="objectMapping" style="display: none">

			<br>
			<table border="0" cellpadding="3" id="table2">
				<tr>
					<td style=""><b style=""> <font size="2">Production Unit</font></b></td>
					<td style=""><b style=""> <font size="2">Area</font></b></td>
					<td style=""><b style=""> <font size="2">Facility</font></b></td>
				</tr>
				<tr>
					<td width="140" style=""><select style="width: 100%;"
						id="cboProdUnit" size="1" name="cboProdUnit">
							@foreach($loProductionUnit as $lo)
							<option value="{!!$lo->ID!!}">{!!$lo->NAME!!}</option>
							@endforeach
					</select></td>
					<td width="140" style=""><select style="width: 100%;" id="cboArea"
						onchange="diagram.change('cboArea');" size="1" name="cboArea">
							@foreach($loArea as $area)
							<option value="{!!$area->ID!!}">{!!$area->NAME!!}</option>
							@endforeach
					</select></td>
					<td width="140" style=""><select style="width: 100%;"
						onchange="diagram.change('cboFacility');" id="cboFacility"
						size="1" name="cboFacility"> @foreach($facility as $fa)
							<option value="{!!$fa->ID!!}">{!!$fa->NAME!!}</option>
							@endforeach
					</select></td>
				</tr>
			</table>
			<br>
			<table border="0" cellpadding="0" cellspacing="4" width="400"
				id="table1">
				<tr>
					<td>Object type</td>
					<td width="250"><select style="width: 240px;" id="cboObjType"
						size="1" name="cboObjType" onchange="diagram.change('cboObjType')"> @foreach($intObjectType as $iot)
							<option value="{!!$iot->CODE!!}">{!!$iot->NAME!!}</option>
							@endforeach
					</select></td>
				</tr>
				<tr>
					<td id="txtObjType">Flow</td>
					<td><select style="width: 240px;" id="cboObjs" size="1"
						name="cboObjs"> @foreach($type as $t)
							<option value="{!!$t->ID!!}">{!!$t->NAME!!}</option> @endforeach
					</select></td>
				</tr>
				<tr id="flow_direction_tr" style="display: none">
					<td>Flow Direction</td>
					<td><input type="radio" value="in" name="flow_direction"
						id="fpdir1">In &nbsp; <input type="radio" value="out"
						name="flow_direction" id="fpdir2" checked>Out</td>
				</tr>
			</table>
		</div>

		<table border="0" cellpadding="0" cellspacing="0" id="table1"
			width="100%">
			<tr>
				<td style="display: none" height="20">
					<div style="display: none" id="header_">&nbsp;</div>



					<div id="mainActions"
						style="display: none; width: 100%; padding-top: 8px; padding-left: 24px; padding-bottom: 8px;">
					</div>
					<div style="display: none; float: right; padding-right: 36px;">
						<input id="source" type="checkbox" />Source
					</div>
					<div id="selectActions"
						style="display: none; width: 100%; padding-left: 54px; padding-bottom: 4px;">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<table border="0" cellpadding="0" cellspacing="0" id="table2"
						width="100%">
						<tr>
							<td style="border: none; width: 280px;"><span
								style="font-size: 10pt; padding-left: 10px;" id="diagramName">
									{{--[Untitled Diagram]--}}
								</span></td>
							<td>

								<table border="0" cellpadding="0" id="table17" cellspacing="4"
									height="30">
									<tr>
										<td onClick="newDiagram()" width="60" class="xbutton">New</td>
										<td onClick="loadDiagram()" width="60" class="xbutton">Load</td>
										<td id="buttonSave" onMouseOut="$('#buttonSaveAs').hide();"
											onMouseOver="$('#buttonSaveAs').show();" width="60"
											class="xbutton">
											<span class="xbutton" id="buttonSave_text" onClick="saveDiagram()">Save</span>
											<div class="xbutton"
												style="padding: 5px; display: none; position: absolute; width: 64px; z-index: 101; margin-left: 0px; margin-top: 4px; border: 2px solid #666"
												id="buttonSaveAs">
												<span class="xbutton" onClick="saveDiagram('a')">Save As</span>
											</div></td>
										<td onClick="buttonActionClick('print')" width="60"
											class="xbutton">Print</td>
										<td style="display: none"
											onClick="buttonActionClick('exportImage')" width="60"
											class="xbutton">Export</td>
										<td align="right" width="70"><span style="font-size: 8pt">
												Flowline</span></td>
										<td onClick="changeLineColor('red')" width="40"
											class="xbutton" style="background-color: #FF0000">Gas</td>
										<td onClick="changeLineColor('blue')" width="40"
											class="xbutton" style="background-color: #0066CC">Water</td>
										<td onClick="changeLineColor('#CC6600')" width="40"
											class="xbutton" style="background-color: #CC6600">Oil</td>
										<td style="display:; text-align: center" width="100"
											class="xbutton"><span
											onClick="$('#boxSubnetworks').toggle();">Subnetworks</span>
											<div
												style="display: none; position: absolute; width: 174px; height: 133px; z-index: 100; margin-left: -0px; margin-top: 5px; border: 2px solid #666"
												id="boxSubnetworks">
												<table border="0" cellpadding="0" cellspacing="0"
													width="100%" id="table22" height="100%">
													<tr>
														<td bgcolor="#c0c0c0" style="border: 1px solid #666"><select
															onclick="listSubnetworkClick()" id="listSubnetwork"
															style="width: 100%; height: 100%; border: 0px solid #ffffff; overflow: auto; background: #c0c0c0; font-family: Verdana; font-size: 8pt; color: #000"
															name="sometext" multiple="multiple">
														</select></td>
													</tr>
												</table>
											</div></td>
										<td width="50">&nbsp;</td>
										<td class="ebutton" onClick="buttonActionClick('copy')"><img
											src="/images/copy.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('cut')"><img
											src="/images/cut.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('paste')"><img
											src="/images/paste.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('delete')"><img
											src="/images/delete.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('undo')"><img
											src="/images/undo.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('redo')"><img
											src="/images/redo.gif"></td>
										<td class="ebutton" onClick="buttonActionClick('rotate')"><img
											src="/images/rotate.png"></td>
										<td width="70" align="right">Export as</td>
										<td class="xbutton" width="30" onClick="diagram.exportImage()">PNG</td>
									</tr>
								</table>

							</td>
							<td>&nbsp;</td>
						</tr>
					</table>
					<table id="groupTabele" border="0" cellpadding="0" cellspacing="0" id="table21" width="100%" height="100%">
						<tr>
							<td style="width: 5px;" >&nbsp</td>
							<td id="tdToolBox" width="260" valign="top" style="border: none">
<script>
	var filesToUpload = [];
	$('input[type=file]').on('change', prepareUpload);
	function prepareUpload(event)
	{
	  var files = event.target.files || event.originalEvent.dataTransfer.files;
	    // Itterate thru files (here I user Underscore.js function to do so).
	    // Simply user 'for loop'.
	    $.each(files, function(key, value) {
	        filesToUpload.push(value);
	    });
	}
	function pick_cell_image(){
		$("#box_cell_image_input").hide();
		$("#box_pick_cell").show();
		$("#box_pick_cell").html("Loading...");
		
		$("#box_pick_cell").html('No available image');
	}
	function set_cell_image(cell,url){
		cell.style="shape=image;html=1;verticalLabelPosition=bottom;verticalAlign=top;imageAspect=1;image="+url;
		ed.graph.refresh();
	}
	function setCellImage(cell){
		$("#box_cell_image_input").show();
		$("#box_pick_cell").hide();
		$("#txt_cell_image_url").val("");
		$("#file_cell_image_url").val("");
		$( "#box_cell_image" ).dialog({
			height: 300,
			width: 600,
			modal: true,
			title: "Set Image",
			buttons: {
				"OK": function(){
					var url=$("#txt_cell_image_url").val().trim();
					if(url!=""){
						set_cell_image(cell,url);
						$("#box_cell_image").dialog("close");
					}else{			
						if(filesToUpload){
						    var formData = new FormData();

						    // Add selected files to FormData which will be sent
						    if (filesToUpload) {
						        $.each(filesToUpload, function(key, value){
						            formData.append(key, value);
						        });        
						    }
						    
						    showWaiting("Uploading image...");
							$.ajax({
						        type: "POST",
						        url: '/uploadImg',
						        data: formData,
						        processData: false,
						        contentType: false,
						        dataType: 'json',
						        cache: false,
						        success: function(data, textStatus, jqXHR)
								{
									hideWaiting();
									if(typeof data.error === 'undefined')
									{
										// Success so call function to process the form
										set_cell_image(cell,data.files);
										filesToUpload = [];
										$("#box_cell_image").dialog("close");
									}
									else
									{
										// Handle errors here
										alert('Error: ' + data.error);
										console.log('ERRORS: ' + data.error);
									}
								},
								error: function(jqXHR, textStatus, errorThrown)
								{
									filesToUpload = [];
									hideWaiting();
									// Handle errors here
									alert('Error: ' + textStatus);
									console.log('ERRORS: ' + textStatus);
									// STOP LOADING SPINNER
								}
						        
						    });
						}
					}
				},
				"Cancel": function(){
					$("#box_cell_image").dialog("close");
					filesToUpload = [];
				}
			}
		});
	}
	function showIcons()
	{
		$("#buttonShowIcons").attr('class','tabselected');
		$("#buttonShowProperties").attr('class','tabnormal');
		$("#properties").hide();
		$("#icons").show();
	}
	function showProperties()
	{
		$("#buttonShowIcons").attr('class','tabnormal');
		$("#buttonShowProperties").attr('class','tabselected');
		$("#icons").hide();
		$("#properties").show();
	}
	
	function showObjectMapping(){
		$( "#objectMapping" ).dialog({
			height: 300,
			width: 450,
			modal: true,
			title: "Object mapping",
			buttons: {
				"OK": function(){ 
					mappingObject();
					$("#objectMapping").dialog("close");
				},
				"Cancel": function(){
					$("#objectMapping").dialog("close");
				}
			}			
		});
	}
	function mappingObject()
	{
		if(currentObjectMapping)
		{
			currentObjectMapping.setAttribute('object_id',$("#cboObjs").val());
			currentObjectMapping.setAttribute('object_type',$("#cboObjType").val());
			currentObjectMapping.setAttribute('cboProdUnit',$("#cboProdUnit").val());
			currentObjectMapping.setAttribute('cboArea',$("#cboArea").val());
			currentObjectMapping.setAttribute('cboFacility',$("#cboFacility").val());
		}
	}
	var currentObjectMapping,currentObjectID;
	
	function objectMapping()
	{
		ed.graph.model.beginUpdate();
		try
		{
			var c;
			for (c in ed.graph.selectionModel.cells)
			{
				currentObjectMapping = ed.graph.selectionModel.cells[c];
				currentObjectID = checkValue(currentObjectMapping.getAttribute('object_id'),'');

				$("#flow_direction").val(currentObjectMapping.getAttribute('flow_direction'));
				var objtype = checkValue(currentObjectMapping.getAttribute('object_type'),'');

				if (true || objtype=='ENERGY_UNIT' || objtype=='FLOW' || objtype=='TANK' || objtype=='EQUIPMENT' || objtype=='ENERGY_UNIT_GROUP')
				{
					/* if(objtype != ''){
						$("#cboObjType").val(objtype);
						$("#cboObjType").change();
					} */
					var params = {
							cboProdUnit	: currentObjectMapping.getAttribute('cboProdUnit'),
							cboArea		: currentObjectMapping.getAttribute('cboArea'),
							cboFacility	: currentObjectMapping.getAttribute('cboFacility'),
							objtype		: objtype,
							cboObjs		: currentObjectMapping.getAttribute('object_id'),
							};
					
// 					$("#cboProdUnit").val(currentObjectMapping.getAttribute('cboProdUnit'));
// 					diagram.change("cboProdUnit",params);
// 					showObjectMapping();
					var dataStore = {
							LoProductionUnit	: currentObjectMapping.getAttribute('LoProductionUnit'),
							LoArea				: currentObjectMapping.getAttribute('LoArea'),
							Facility			: currentObjectMapping.getAttribute('Facility'),
							IntObjectType		: editBox.getObjectTypeId(objtype),
							ObjectName			: currentObjectMapping.getAttribute('object_id'),
							};

			    	var option = {
						    	title 		: "Object Mapping",
						 		postData 	: dataStore,
						 		url 		: "/diagram/filter",
						 		viewId 		: "objectMappingView",
						 		size		: {height:180,width:650}
			    	    	};
					editBox.showDialog(option,function(data){
						oEditGroupSuccess(data,{viewId: "objectMappingView"});
// 						$(".ui-dialog-titlebar-close").addClass("floatRight");
// 					    $("button[class=dialogButtonCancel]").css("display","none");
					});
					
					/* var moreFunction	= editBox.getShowEditFilterDialogFunction( null,
							{	DT_RowId	: currentObjectMapping,
								OBJECTS		: {},
								height 		: 170,
								width 		: 620,
								title		: "Object Mapping",
							},
							null,null,"objectMappingView");
					moreFunction(); */
				}
				else if (objtype=='FLOW')
				{
					//$("#flow_direction_tr").show();
					$("#cboObjType").prop('selectedIndex',0);
					$("#cboObjType").change();
					showObjectMapping();
				}
				else if (objtype=='SUR')
				{
					$("#surMapping").show();
				}
				break;
			}
		}
		finally
		{
			ed.graph.model.endUpdate();
		}
	}

    _fieldconfig.enableReadyLoad	= function () {
        return false;
    }
	
	editBox.size = {	height 	: 530,
						width 	: 950,
					};

	editBox.getObjectTypeId = function (objectType){
		if(typeof objectType == "string" && objectType!=""){
			var result = $.grep(objectTypes, function(e){ 
	       	 	return e.CODE== objectType || e.code== objectType;
	        });
		    if (result.length > 0){
				if(typeof result[0].ID != "undefined") return result[0].ID;
				return result[0].id;
		    }
		}
		return 0;
	};

	editBox.getObjectTypeCode = function (objectType){
		if(typeof objectType != "undefined"){
			var result = $.grep(objectTypes, function(e){ 
	       	 	return e.ID== objectType || e.id== objectType;
	        });
		    if (result.length > 0){
				if(typeof result[0].CODE != "undefined") return result[0].CODE;
				return result[0].code;
		    }
		}
		return 0;
	};

	var currentTable,currentField;
	editBox.initExtraPostData = function (id,rowData,url){
		var c;
		//Get all medel selsected
		for (c in ed.graph.selectionModel.cells)
		{
			currentObjectMapping	=	ed.graph.selectionModel.cells[c];
		}
		if(currentObjectMapping){
			var surveillance = currentObjectMapping.getAttribute('surveillance');
			if(surveillance.length>0){
				var objects	= surveillance.split("@");
				if(objects.length>0){
					var splits	= objects[0].split("/");
					if(splits.length>1){
						var table			= splits[0];
						var field_effected	= splits[1];
                        currentTable = table;
                        currentField = field_effected;
                        return 	{	table			: table,
				 					field_effected	: field_effected,
                            		configId 		: null,
				 		};
					}
				}
			}
		}
// 		alert("please add surveillance to object");
		throw new Error("no surveillance");
	};

	var ofRenderData = _fieldconfig.renderData;
    _fieldconfig.renderData = function(data){
        ofRenderData(data);
        $('#data_field_effected').val(currentField);
        $('#data_field_effected').data('previousValue',currentField);
        $("#data_field_effected").change();
    };

    editBox.isNotSaveGotData	= function (url,viewId){
        return url!="/dataJson";
    },

	editBox.dialogOpenFunction	=	function( event, ui ) {
		$("#floatBox").closest(".ui-dialog").removeClass("ui-dialog");
	};

	oEditGroupSuccess = editBox.editGroupSuccess;
	editBox.editGroupSuccess = function(data,span){
        // _fieldconfig.renderData(data);
        $("#chk_tbl").parent().hide();
        $("#addCfgConfig").parent().hide();
        $("#add").parent().hide();
        $("#chk_dc").parent().hide();
        $("#data_field").hide();
        $(".selectNames").hide();
        $("#data_source").prop('disabled', 'disabled');
        $("#saveButton").css('position','absolute').css('right','50px').css('top','50px');
        $("#selectedFields").css('padding-left','0');
        $("#select_container_CfgConfig").css('float','none');
        $("#data_field_effected").prop('disabled', 'disabled');;
        $("#data_source").val(currentTable).change();

        // _fieldconfig.setData(data);
	};

	editBox.editSelectedObjects	= function(dataStore,resultText){
		currentObjectMapping.setAttribute('object_id',			dataStore.ObjectName);
		currentObjectMapping.setAttribute('object_type',		editBox.getObjectTypeCode(dataStore.IntObjectType));
		currentObjectMapping.setAttribute('LoProductionUnit',	dataStore.LoProductionUnit);
		currentObjectMapping.setAttribute('LoArea',				dataStore.LoArea);
		currentObjectMapping.setAttribute('Facility',			dataStore.Facility);
	};
	
	function setAlert(){
		var id 			= 1;
		var rowData 	= {};
		var url 		= "/dataJson";
// 		var viewId 		= "";
		editBox.editRow(id,rowData,url/* ,viewId */);
	}

	function applySurveilanceTags(){
		var tags	=  [
		        		{	parentCellId	: "135", 
			        		fields			: [	{tagId		: "9811-PI-00601.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TI-00601.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "134", 
			        		fields			: [	{tagId		: "9811-PI-00101.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TI-00101.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "136", 
			        		fields			: [	{tagId		: "9811-PI-00401.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TI-00401.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "137", 
			        		fields			: [	{tagId		: "9811-PI-00501.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TI-00501.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "138", 
			        		fields			: [	{tagId		: "9811-PI-00701.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TI-00701.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "139", 
			        		fields			: [	{tagId		: "9811-PI-00801.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TI-00801.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "140", 
			        		fields			: [	{tagId		: "9811-PI-00901.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TI-00901.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "141", 
			        		fields			: [	{tagId		: "9810-PIT-00114.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9810-TIT-00114.RS_PV",	tagName	: "T"}]
		        		},
		        		//
		        		{	parentCellId	: "47",
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PT-10206.RS_PV",	tagName	: "P"}]
		        		},
		        		{	parentCellId	: "48", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PT-10508.RS_PV",	tagName	: "P"}]
		        		},
		        		{	parentCellId	: "49", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PT-10908.RS_PV",	tagName	: "P"}]
		        		},
		        		//
		        		{	parentCellId	: "77", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PI-HX107.RS_PV",	tagName	: "PDI"}]
		        		},
		        		{	parentCellId	: "75", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PT_SCL-HX107.RS_OUT",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TT-11703.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "206", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PI-10603.RS_PV",	tagName	: "P"}]
		        		},
		        		{	parentCellId	: "225", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PDT-11600.RS_PV",	tagName	: "PDI"}]
		        		},
		        		{	parentCellId	: "226", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PI-11601.RS_PV",	tagName	: "P"},
			        		      			 	{tagId		: "9811-TT-11603.RS_PV",	tagName	: "T"}]
		        		},
		        		{	parentCellId	: "219", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PI-10702.RS_P",	tagName	: "P"}]
		        		},
		        		{	parentCellId	: "231", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PDT-11800.RS_PV",	tagName	: "PDI"}]
		        		},
		        		{	parentCellId	: "87", 
			        		type			: "rect",
			        		fields			: [	{tagId		: "9811-PT-10400.RS_PV",	tagName	: "P"}]
		        		},
		        		///
		        		{	parentCellId	: "144",
			        		type			: "line",
			        		fields			: [	{tagId		: "9811-FT_SCL-HX108.RS_OUT",	tagName	: "F"}]
		        		},
		        		{	parentCellId	: "205",
			        		type			: "line",
			        		fields			: [	{tagId		: "9811-FQI-10309.RS_PV",	tagName	: "F"},
			        		      			  	{tagId		: "9811-FQI-10310.RS_PV",	tagName	: "F"}]
		        		},

		        		
		];

		var aGetGeometryOf = function(cell){
			var cellGeometry =  jQuery.extend({},cell.getGeometry());
			cellGeometry.x		= cellGeometry.x+cellGeometry.width+10;
			cellGeometry.y		= cellGeometry.y+70;
			return  cellGeometry;
		}

		var fWidth = function(cell){
			return  140;
		}
		
		var doc 	= mxUtils.createXmlDocument();
		var model 	= ed.graph.model;
		$.each(tags, function(index, tag) {
			var cell = model.getCell(tag.parentCellId);
			if(cell!==undefined){
				var s="";
				removeAllLabelsOf(cell);
				$.each(tag.fields, function(i, field) {
					field.label			= field.tagName+" : " + field.tagId;
					if(tag.type===undefined) field.getGeometryOf	= aGetGeometryOf;
					field.width	= fWidth;
					s+=(s==""?"":"@")+"TAG:"+field.tagId;
			    });
				addOrUpdateLabels(doc,cell,tag.fields);
				cell.setAttribute('conn_id',4);
				cell.setAttribute('surveillance',s);
			}
	    });

		ed.graph.refresh();
	}
	
	function expressionSetting(){
		var cell = ed.graph.selectionModel.cells[Object.keys(ed.graph.selectionModel.cells)[0]];
		$("#txt_expressionSetting").val(cell.getAttribute('expression'));
		$( "#expressionSetting" ).dialog({
			height: 480,
			width: 900,
			modal: true,
			title: "Expression settings",
			buttons: {
				"Apply": function(){
					cell.setAttribute('expression', $("#txt_expressionSetting").val());
					$("#expressionSetting").dialog("close");
				},
				"Test": function(){
					var param = {vparam: [{'ID':1, 'EXP': $("#txt_expressionSetting").val()}]};
					sendAjax('/getValueSurveillance', param, function(respondData){
						var data = respondData.data;
						if(data.substr(0,2)!='ok'){
							_alert(data);
							return;
						}
						for(var cellId in respondData.expressionData){
							_alert(respondData.expressionData[cellId]);
						}
					}); 
				},
				"Cancel": function(){
					$("#expressionSetting").dialog("close");
				}
			}
		});
	}
	
	function surveillanceSetting()
	{
		$('#sur_fields').html('Loading...');								
		$('#sur_tag_content').html('Loading...');								
		$("#txt_sur_other_tag").val("");
		$( "#surveillanceSetting" ).dialog({
			height: 485,
			width: 660,
			modal: true,
			title: "Surveillance settings",
			buttons: {
				"Apply": applySurveillance,
				"Cancel": function(){
					$("#surveillanceSetting").dialog("close");
				}
			}
		});
		var c;
		//Get all medel selsected
		for (c in ed.graph.selectionModel.cells)
		{
			currentObjectMapping=ed.graph.selectionModel.cells[c];
		
			var sur=currentObjectMapping.getAttribute('surveillance');
			var object_type=currentObjectMapping.getAttribute('object_type');
			var object_id=currentObjectMapping.getAttribute('object_id');
			var conn_id=currentObjectMapping.getAttribute('conn_id');
			
			$("#cboConnection").val(conn_id);
			var tagNames 			= getSurveilanceTagLabels(currentObjectMapping);
			param = {
				'SUR'				: sur,
				'OBJECT_ID' 		: object_id,
			   	'OBJECT_TYPE' 		: object_type,
				"tagNames"			: tagNames
			}
			//TODO optimise by use only 1 request
			sendAjax('/getSurveillanceSetting', param, function(data){
				diagram.loadSurveillanceSetting(data);
			});
			
			break;
		}
	}
	
	function applySurveillance(){
		var s="";
		var phase_config="";
		var phaseConfig	= [];
		
		$("#sur_phase_list .sur_phase_item").each(function(){
			phase_config+=(phase_config==""?"":"@@")+$(this).attr('sur');
			phaseConfig.push($(this).data());
		});
		phaseConfigString	= JSON.stringify(phaseConfig);
		phase_config+="!!"+$("#sur_fields_select").val();
		var fields = [];
		
		$("#sur_fields :checked").each(function(){
			s+=(s==""?"":"@")+$(this).attr("surveilance_settings");
			fields.push($(this).val());
		});
		$("#sur_tag_content :checked").each(function(){
			s+=(s==""?"":"@")+"TAG:"+$(this).attr("surveilance_settings");
			fields.push({
				label : $(this).attr("surveilance_tagName"),
				tagId : $(this).attr("surveilance_settings"),
				tagName : $(this).attr("surveilance_tagName"),
				});
		});
		var other_tag=$("#txt_sur_other_tag").val().trim();
		if(other_tag!=""){
			var splits = other_tag.split(",");
			$.each(splits, function(key, split) {
				s+=(s==""?"":"@")+"TAG:"+split;
				fields.push({
					label 	: split,
					tagId 	: split,
					tagName : split,
					});
		    });
		}
		
		if(currentObjectMapping){
			currentObjectMapping.setAttribute('surveillance',s);
			currentObjectMapping.setAttribute('sur_phase_config',phaseConfigString);
			currentObjectMapping.setAttribute('conn_id',$("#cboConnection").val());
		}
		
		//Create label or edit label
		var cell;
		for (c in ed.graph.selectionModel.cells){
			cell = ed.graph.selectionModel.cells[c];
		}
				
// 		var cell = ed.graph.getSelectionCell();
		var doc 	= mxUtils.createXmlDocument();

		removeAllLabelsOf(cell);
		addOrUpdateSurLabels(doc,cell,phaseConfig);
		addOrUpdateLabels(doc,cell,fields);
		ed.graph.refresh();
		$("#surveillanceSetting").dialog("close");
	}
	function Qaddlabel(){
		var cell = ed.graph.getSelectionCell();
		cell.setGeometry('100', '200', '250', '84');
		ed.graph.refresh();
	}
	
</script>
								<table border="0" cellpadding="0" cellspacing="0" width="100%"
									id="table3" height="100%">
									<tr>
										<td height="20" bgcolor="#666">
											<table border="0" cellpadding="0" width="100%" id="table10"
												cellspacing="1" height="100%">
												<tr>
													<td id="buttonShowIcons" width="46" onClick="showIcons()"
														class="tabselected" bgcolor="#959596">Items</td>
													<td id="buttonShowProperties" class="tabnormal"
														onClick="showProperties()" width="79">&nbsp; Preview</td>
													<td>&nbsp;</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="border: 1px solid #666; height: 100%;"
											bgcolor="#C0C0C0" valign="top">
											<div id="properties" style="display: none;">
												<div style="margin: 10px auto; width: 94%; height: 100%;">
													
													<table>
														<tr>
															<td style="width: 35%">Date</td>
															<td colspan="2"><input type="text" id="Qoccurdate"
																style="width: 150px"></td>
														</tr>
														<tr>
															<td>Flow phase</td>
															<td colspan="2"><select id="Qflowphase"
																style="width: 150px"> @foreach($codeFlowPhase as
																	$flowphase)
																	<option value="{!!$flowphase->ID!!}">{!!$flowphase->NAME
																		!!}</option> @endforeach
															</select></td>
														</tr>
														<tr>
															<td></td>
															<td style="width: 50px"></td>
															<td class="xbutton" style="height: 25px"
																onClick="display()"><span>Display data</span></td>
														</tr>
													</table>
													<script>
												//$("#display").button();
											
											</script>
												</div>
											</div>
											<div id="icons"
												style="width: 260px; height: 100%; overflow: auto;">
												<div style="padding: 10px;" id="toolbar"></div>
											</div>
										</td>
									</tr>
									<tr>
										<td height="10"></td>
									</tr>
									<tr>
										<td>
											<table border="0" cellpadding="0" cellspacing="0"
												width="100%" id="table20" height="100%">
												<tr>
													<td height="15px" bgcolor="#666">
														<table border="0" cellpadding="0" width="100%"
															id="table21" cellspacing="1">
															<tr style="height: 10px;">
																<td><font size="1" color="#F8F8F8"> &nbsp;<b>Zoom</b></font></td>
																<td act="zoomIn" id="buttonZoomIn"
																	onClick="buttonActionClick('zoomIn')" width="30"
																	height="15" class="abutton">in</td>
																<td act="zoomOut" onClick="buttonActionClick('zoomOut')"
																	width="30" height="15" class="abutton">out</td>
																<td act="actual"
																	onClick="buttonActionClick('actualSize')" width="30"
																	height="15" class="abutton">1:1</td>
																<td act="fit" onClick="buttonActionClick('fit')"
																	width="30" height="15" class="abutton">fit</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td
														style="display:; background: #fff; border: 1px solid #666">
														<div id="outlineContainer"
															style="background: #fff; width: 248px; height: 99px;"></div>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table></td>
							<td width="10" style="cursor: pointer" id="tdShowHideToolBox"><img
								id="imgShowHideToolBox" width=10 src='/images/arrow_left.png'></td>
							<td style="width: 100%;">
								<div id="graph"
									style="position: relative; height: 100%; cursor: default; overflow: hidden; border: 1px solid #666; background-image: url('/images/bg.png')">
									<!-- Graph Here -->
									<center id="splash" style="padding-top: 230px;">
										<img src="/images/loading.gif">
									</center>
								</div>
							</td>
							<td style="width: 5px;">&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
	</div>

	<div id="listSavedDiagrams" style="overflow-y: auto; display: none;"></div>

</body>
<script>
    var hei = $(window).height();
    var wid = $(window).width();
    $("#groupTabele").css('height',hei - 135);
    $("#icons").css('height',hei - 340);
    $("#graph").css('height',hei - 130);
    $("#graph").css('width',wid - 282);
</script>
@stop
