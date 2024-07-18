<html>
<head>
	<title>Diagram Test</title>
 	<link rel="stylesheet" href="/common/css/wordpress.css" type="text/css" media="screen" />
 	<link rel="stylesheet" href="/common/css/common.css" type="text/css" media="screen" />
 	<script type="text/javascript" src="/common/js/jquery-2.1.3.js"></script> 
	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<style type="text/css" media="screen">
		body { background: url("/images/draw/drawbg.jpg") repeat-y top; border: none; }
.abutton{
cursor:pointer;
padding:0px;
font-size:8pt;
background:#ffffff;
border:1px solid #666;
text-align:center;
color:#666;
-moz-border-radius: 2px;
border-radius: 2px; 
}		
.abutton:hover{background:#88BCD9;}
.abutton:active{background:#6CADD0;}
.xbutton{
cursor:pointer;
padding:0px;
font-size:8pt;
background:#666;
border:0px solid #666;
text-align:center;
color:#ffffff;
-moz-border-radius: 3px;
border-radius: 3px; 
}		
.xbutton:hover{background:#88BCD9;}
.xbutton:active{background:#6CADD0;}
	</style>
    <script src="/common/js/mxClient.js"></script>
	<script src="/common/js/mxApplication.js"></script>
	<script type="text/javascript">
	var ed;
		// Program starts here. The document.onLoad executes the
		// mxApplication constructor with a given configuration.
		// In the config file, the mxEditor.onInit method is
		// overridden to invoke this global function as the
		// last step in the editor constructor.
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
			editor.graph.addListener(mxEvent.CELLS_REMOVED, cellRemovedListener);
			
			// Displays information about the session
			// in the status bar
			editor.addListener(mxEvent.SESSION, function(editor, evt)
			{
				var session = evt.getProperty('session');
				
				if (session.connected)
				{
					var tstamp = new Date().toLocaleString();
					editor.setStatus(tstamp+':'+
						' '+session.sent+' bytes sent, '+
						' '+session.received+' bytes received');
				}
				else
				{
					editor.setStatus('Not connected');
				}
			});
			
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
			
		    // Changes the zoom on mouseWheel events
		    mxEvent.addMouseWheelListener(function (evt, up)
		    {
			    if (!mxEvent.isConsumed(evt))
			    {
			    	if (up)
					{
			    		editor.execute('zoomIn');
					}
					else
					{
						editor.execute('zoomOut');
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
			loadSavedDiagram("");
		}
	////////// XU LY DOUBLE CLICK
	//mxClient.js ---> mxEditor.prototype.dblClickAction = "edit";
	
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
	function deleteDiagram(sName)
	{
		if(!confirm("Are you really want to delete this diagram?")) return;
		//hideBoxDiagrams();
		    mxUtils.get("deletediagram.php?name="+sName, function(req)
		    {
		      	loadDiagram();
		    },function(){alert('Error while deleting diagram "'+sName+'"')});
			
	}
	var currentCEID="";
	function loadSavedDiagram(ceid)
	{
		//if(ceid=="") ceid=parent.getCEID();
		//if(ceid==currentCEID) return;
		//currentCEID=ceid;
		hideBoxDiagrams();
		   //mxUtils.get("   getjobdiagram.php?job_id="+<?php echo $job_id; ?>, function(req)
		   mxUtils.get("/loadjobdiagram/"+<?php echo $job_id; ?>, function(req)
		    {
		    	clearGraph();
		      var node = req.getDocumentElement();
//alert(node.innerHTML);
		      var dec = new mxCodec(node.ownerDocument);
		      dec.decode(node, ed.graph.getModel());
		      
		      ed.graph.refresh();
		      updateSubnetworksList();
		      
		      setCurrentDiagramName(sName=null);

			//update subnetworks list				
			var c;
			for (c in ed.graph.model.cells)
			{
				var cell=ed.graph.model.cells[c];
				if(ed.graph.isSwimlane(cell))
				{
					addSubnetworkListItem(cell);
				}
			}
		      
		    },function(){alert('Error loading saved diagram!')});
			
	}
	var defaultDiagramName="[Untitled Diagram]";
	var currentDiagramName=defaultDiagramName;
	
	function setCurrentDiagramName(s)
	{
		currentDiagramName=s;
		document.getElementById("diagramName").innerHTML=currentDiagramName;
	}
	
	function saveDiagram()
	{
		var enc = new mxCodec();
		var node = enc.encode(ed.graph.getModel());
	    mxUtils.post("savejobdiagram.php", 'job_id=<?php echo $job_id; ?>&key='+encodeURIComponent(mxUtils.getPrettyXml(node)), function(req)
		    {
		    	alert("Saved successfully");
		    },
		    function (){
		    	//document.getElementById("buttonSave_text").innerHTML="error!";
		    	alert("Error while saving diagram");
		    });
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
	function buttonActionClick(act)
	{
		ed.execute(act);
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
						//ed.graph.model.cells[c].fillbg=a;
						//alert(ed.graph.model.getParent(ed.graph.model.cells[c]).fillbg);
						ed.graph.setCellStyles("highlight", a?"1":"0", [ed.graph.model.cells[c]]);
						//ed.graph.model.cells[c].redrawSvg();
						//mxUtils.setCellStyles(editor.graph.model, [editor.graph.selectionModel.cells[c]],"strokeColor", color);
						//editor.graph.setCellStyles("strokeColor", color, [editor.graph.selectionModel.cells[c]]);
	
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
			$("#boxSavedDiagrams").show();
			$("#splash_load_diagrams_list").show();
		    mxUtils.get("getdiagram.php?name=~~GETLIST", function(req)
		    {
				$("#splash_load_diagrams_list").hide();
		      var s = req.getDocumentElement().innerHTML;
		      if(s.substr(0,3)=="%%%")
		      {
		      	savedDiagrams=s.substr(3).split("~~");
		      	var sHTML="";
		      	for(var i=0;i<savedDiagrams.length;i++)
		      	{
		      		sHTML+="<a href=\"javascript:loadSavedDiagram('"+savedDiagrams[i]+"')\">"+savedDiagrams[i]+"</a>&nbsp;<a href=\"javascript:deleteDiagram('"+savedDiagrams[i]+"')\"><font size='1' color='#ff0000'>[Delete]</font></a>&nbsp;<br>";
		      	}
				document.getElementById("listSavedDiagrams").innerHTML=sHTML;
		      }
		      else
		      {
			      alert('Wrong data!'+s)
		      }
		    },function(){alert('Error loading list saved diagrams!');$("#splash_load_diagrams_list").hide();});
		}
		
		function newDiagram()
		{
			clearGraph();
			setCurrentDiagramName(defaultDiagramName);
		}
		
		function hideBoxDiagrams()
		{
			document.getElementById("boxSavedDiagrams").style.display="none";
		}		

		window.onbeforeunload = function() { return mxResources.get('changesLost'); };
	</script>
</head>
<body onLoad="new mxApplication('/config/diagrameditor_job.xml?4');" style="margin:0px;background:#eeeeee;">

<table border="0" cellpadding="0" cellspacing="0">
	<tr style="display:none">
		<td height="120" valign="top">
		<div id="pageheader"></div>
		</td>
	</tr>
	<tr>
		<td valign="top" align="center">
		
	<table border="0" cellpadding="0" cellspacing="0" id="table1">
		<tr>
			<td style="display:none" height="20">		
			<div style="display:none" id="header_">&nbsp;</div>
		
			
		
			<div id="mainActions"			style="display:none;width:100%;padding-top:8px;padding-left:24px;padding-bottom:8px;">
		</div>

			<div id="selectActions" style="display:none;width:100%;padding-left:54px;padding-bottom:4px;">
		</div>
</td>
		</tr>
		<tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" id="table2" width="100%">
				<tr style="display:none">
					<td height="20">&nbsp;</td>
					<td style="border:none">
					<span style="font-size:10pt" id="diagramName">[Untitled Diagram]</span></td>
					<td>&nbsp;</td>
					<td>
		
									<table border="0" cellpadding="0" id="table17" cellspacing="4" height="30">
										<tr>
											<td onclick="newDiagram()" width="60" class="xbutton">
											New</td>
											<td onclick="loadDiagram()" width="60" class="xbutton">
											Load</td>
											<td id="buttonSave" onmouseout="$('#buttonSaveAs').hide();" onmouseover="$('#buttonSaveAs').show();" width="60" class="xbutton">
											<span id="buttonSave_text" onclick="saveDiagram()" >Save</span>
											<div class="xbutton" style="padding:5px; display:none; position: absolute; width: 64px; z-index: 101; margin-left:0px; margin-top:4px;border:2px solid #666" id="buttonSaveAs">
											<span onclick="saveDiagram('a')" >Save As</span></div>
											</td>
											<td onclick="buttonActionClick('print')" width="60" class="xbutton">Print</td>
											<td style="display:none" onclick="buttonActionClick('exportImage')" width="60" class="xbutton">Export</td>
											<td align="right" width="70">
											<span style="font-size: 8pt">
											Flowline</span></td>
											<td onclick="changeLineColor('red')" width="40" class="xbutton" style="background-color: #FF0000">
											Gas</td>
											<td onclick="changeLineColor('blue')" width="40" class="xbutton" style="background-color: #0066CC">
											Water</td>
											<td onclick="changeLineColor('#CC6600')" width="40" class="xbutton" style="background-color: #CC6600">
											Oil</td>
											<td style="display:; text-align:center" width="100" class="xbutton">
											<span onclick="$('#boxSubnetworks').toggle();">Subnetworks</span>
											<div style="display:none; position: absolute; width: 174px; height: 133px; z-index: 100; margin-left:-0px; margin-top:5px;border:2px solid #666" id="boxSubnetworks">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table22" height="100%">
								<tr>
									<td bgcolor="#c0c0c0" style="border:1px solid #666">
									<select onclick="listSubnetworkClick()" id="listSubnetwork" style="width:100%;height:100%;border:0px solid #ffffff;overflow:auto;background:#c0c0c0;font-family:Verdana;font-size:8pt;color:#000" name="sometext" multiple="multiple">
 </select></td>
								</tr>
							</table>
							</div></td>
											<td width="50">&nbsp;</td>
											<td id="buttonZoomIn0" onclick="buttonActionClick('copy')" width="50" class="xbutton">
											Copy</td>
											<td onclick="buttonActionClick('cut')" width="50" class="xbutton">
											Cut</td>
											<td onclick="buttonActionClick('paste')" width="50" class="xbutton">
											Paste</td>
											<td onclick="buttonActionClick('delete')" width="50" class="xbutton">
											Delete</td>
											<td onclick="buttonActionClick('undo')" width="50" class="xbutton">Undo</td>
											<td onclick="buttonActionClick('redo')" width="50" class="xbutton">Redo</td>
										</tr>
									</table>
		
											</td>
					<td>
		
									&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td width="260" valign="top" style="border:none">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table3" height="100%">
						<tr style="display:none">
							<td height="100" style="border:1px solid #666" bgcolor="#C0C0C0">
							<div style="width:260px;height:100%;overflow:auto;">
								<div style="padding:10px;" id="toolbar" ></div>
									</div>
									</td>
						</tr>
						<tr>
							<td height="10"></td>
						</tr>
						<tr>
							<td valign="top">
							<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table20">
								<tr>
									<td height="20" bgcolor="#666">
									<table border="0" cellpadding="0" width="100%" id="table21" cellspacing="1">
										<tr>
											<td>
									<p align="left"><font size="1" color="#F8F8F8">
									&nbsp;<b>Zoom</b></font></td>
											<td act="zoomIn" id="buttonZoomIn" onclick="buttonActionClick('zoomIn')" width="30" height="15" class="abutton">
											in</td>
											<td act="zoomOut" onclick="buttonActionClick('zoomOut')" width="30" height="15" class="abutton">
											out</td>
											<td act="actual" onclick="buttonActionClick('actualSize')" width="30" height="15" class="abutton">
											1:1</td>
											<td act="fit" onclick="buttonActionClick('fit')" width="30" height="15" class="abutton">
											fit</td>
										</tr>
									</table>
									</td>
								</tr>
								<tr>
									<td style="display:;background:#fff;border:1px solid #666">
									<div id="outlineContainer" style="background:#fff;width:250px;height:195px;">
									</div>
									</td>
								</tr>
							</table>
<br>
<input type=button value="Save diagram" style="width:150px;margin:3px;" onclick="saveJobDiagram()">
<input type=button value="Reset diagram" style="width:150px;margin:3px;" onclick="resetJobDiagram()">
<input type=button value="Print" style="width:150px;margin:3px;" onclick="ed.execute('print');">
<script>
function saveJobDiagram()
{
	saveDiagram();
}
function resetJobDiagram()
{
	if(!confirm("Are you sure you want to reset this diagram to default?")) return;
		    mxUtils.get("getjobdiagram.php?act=reset&job_id="+<?php echo $job_id; ?>, function(req)
		    {
		    	clearGraph();
		      var node = req.getDocumentElement();
		      var dec = new mxCodec(node.ownerDocument);
		      dec.decode(node, ed.graph.getModel());
		      
		      ed.graph.refresh();
		    },function(){alert('Error resetJobDiagram!')});
}
</script>
							</td>
						</tr>
						</table>
					</td>
					<td width="10"></td>
					<td width="750" height="450">
					<div id="graph" style="position:relative;height:470px;width:750px;cursor:default;overflow:hidden;border:1px solid #666;background-image:url('/images/bg.png')">
						<!-- Graph Here -->
						<center id="splash" style="padding-top:230px;">
							<img src="/images/loading.gif">
						</center>
					</div></td>
					<td>
					&nbsp;</td>
				</tr>
			</table>
			</td>
		</tr>
		</table>
</td>
	</tr>
	</table>
<div style="display:none;position: absolute; width: 300px; height: 220px; z-index: 101; left: 50%; margin-left:-150px; top: 50%;margin-top:-80px;border:2px solid #666; background:#999" id="boxSavedDiagrams">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" id="table16" height="100%">
		<tr>
			<td height="20" bgcolor="#666" width="82%"><b>
			<font color="#FFFFFF" size="1">&nbsp;Select a diagram</font></b></td>
			<td style="cursor:pointer" onclick="hideBoxDiagrams()" height="20" bgcolor="#666" width="18%">
			<p align="center"><b><font color="#FFFFFF" size="1">Close</font></b></td>
		</tr>
		<tr>
			<td id="listSavedDiagrams" bgcolor="#DDDDDD" colspan="2" valign="top">
			<center id="splash_load_diagrams_list" style="display:none;padding-top:60px;">
							<img src="/images/loading.gif"><p><font size="2">Loading...</font></p>
						</center>
						&nbsp;</td>
		</tr>
	</table>
</div>
		<div style="display:none;float:right;padding-right:36px;">
			<input id="source" type="checkbox"/>Source
			
			<textarea id="xml" rows="2" name="S1" cols="20"></textarea></div>
</body>
</html>