var ed;
var task_code="";
var task_id="";
function onInit(editor){
    ed=editor;
    editor.graph.setPanning(true);
    editor.graph.panningHandler.useLeftButtonForPanning = true;
	editor.graph.setConnectable(false);
	editor.graph.swimlaneSelectionEnabled=false;
	//mxGraphHandler.prototype.highlightEnabled=true;

	//editor.graph.setEnabled(false);
	//mxGraph.setCellsSelectable(false);
	mxGraph.prototype.maxFitScale=1;
	mxGraph.prototype.isCellSelectable = function(cell)
	{
		var task_code=cell.getAttribute('task_code',"");
		var task_id=cell.getAttribute('task_id',0);
		var isrun=cell.getAttribute('isrun',0);
		//return (task_code!="" || isrun==2);
		return (task_id>0);
	};

	//mxPopupMenu.prototype.showMenu = function(){}
	
	editor.graph.selectionModel.addListener(mxEvent.CHANGE, function(){
		if(ed.graph.selectionModel.cells.length==1){
			onObjectSelected(ed.graph.selectionModel.cells[0]);
		}
	});

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
	var xml=$("#wf_xml").html();
	if(xml && xml!=""){
		//alert("loadDiagramFromXML: "+xml);
		loadDiagramFromXML(Base64.decode(xml));
	}
	else
		loadWorkflow();
	//if(parent) setTimeout(function(){parent.hide_wf_loading();},200);
}
function clearGraph(){
    ed.graph.removeCells(ed.graph.getChildVertices(ed.graph.getDefaultParent()));
	$("#cmd_open_task").hide();
	$("#cmd_finish_task").hide();
	$('#task_process .name').attr('task_id',0);
}
function showTaskLog(task_id){
	parent.showTaskLog(task_id);
}
function loadDiagramFromXML(xmlcode){
	var doc = mxUtils.parseXml(xmlcode);
	var dec = new mxCodec(doc);
	clearGraph();
	dec.decode(doc.documentElement, ed.graph.getModel());
	lastObj=null;
	for (var c in ed.graph.model.cells){
		var obj=ed.graph.model.cells[c];
		var isrun=obj.getAttribute('isrun',0);
		var has_log=obj.getAttribute('has_log',0);
		var autorun=obj.getAttribute('autorun',0);
		var color="";
		if(isrun==1)
			color="green";
		else if(isrun==2){
			color="#ffbb00";
			var b=ed.graph.view.getBounds([obj]);
			var iw=b.width-10;
			var ih=iw/9;
			var overlay = new mxCellOverlay(new mxImage('images/running.gif',iw,ih), (autorun==1?'This task is running':''),mxConstants.ALIGN_CENTER,mxConstants.ALIGN_BOTTOM,new mxPoint(0,-ih/2-1),"default");
			overlay.cell=obj;
			overlay.addListener(mxEvent.CLICK, function(sender, evt2)
			{
				onObjectSelected(sender.cell);
			});
			ed.graph.addCellOverlay(obj, overlay);
			if(!lastObj) onObjectSelected(obj);
		}
		else if(isrun==3)
			color="red";
		if(has_log==1){
			var overlay = new mxCellOverlay(new mxImage('images/preview.gif',16,16), 'Click to show task\'s last log',mxConstants.ALIGN_RIGHT,mxConstants.ALIGN_BOTTOM,null,"pointer");
			overlay.cell=obj;
			overlay.addListener(mxEvent.CLICK, function(sender, evt2)
			{
				showTaskLog(sender.cell.getAttribute('task_id'),0);
			});
			ed.graph.addCellOverlay(obj, overlay);
		}
		if(autorun==1){
			var overlay = new mxCellOverlay(new mxImage('images/autorun.png',16,16), 'This is autorun task',mxConstants.ALIGN_RIGHT,mxConstants.ALIGN_TOP,null,"default");
			overlay.cell=obj;
			overlay.addListener(mxEvent.CLICK, function(sender, evt2)
			{
				onObjectSelected(sender.cell);
			});
			ed.graph.addCellOverlay(obj, overlay);
		}
		if(color!="") {
			ed.graph.setCellStyles("fillColor", color, [obj]);
		}
	}
	ed.graph.setCellsLocked(ed.graph.model.cells);
	ed.graph.fit(0,false);
	var scale=ed.graph.view.getScale();
	scale=0.5/scale/scale;
	//alert(0.5/scale);
	ed.graph.center(true,true,scale,scale);
	//console.log(xmlcode);
	ed.graph.refresh();
	parent.hide_wf_loading();
}

function loadSavedDiagram(sId){
	//_alert("loadSavedDiagram "+sId);
	if(parent) parent.show_wf_loading();
	
	param = {
			'ID' : sId,
			'readonly':1
	} 

 	sendAjax('/getXMLCodeWF', param, function(data){
		if(data.result){
			if(checkValue(data.result['DATA'],-1) != -1){
				loadDiagramFromXML(data.result['DATA']);
			}
		}
		if(parent) {
			parent.hide_wf_loading(); 		
			parent.loadTasksCounting();
		}
	});
}
var lastObj;
var highlight;
function onObjectSelected(obj){
	if(ed.graph.isSwimlane(obj)) return;
	if(obj.isEdge()) return;

	var isrun=obj.getAttribute('isrun',0);
	var granted=obj.getAttribute('granted',0);
	task_code=obj.getAttribute('task_code',"");
	task_id=obj.getAttribute('task_id',0);
	
	//alert("task_id="+task_id); 
	
	//if(task_code=="" && isrun!=2) return;

	if(lastObj){
		ed.graph.setCellStyles("strokeColor", "#aecbe0", [lastObj]);
		ed.graph.setCellStyles("strokeWidth", "1", [lastObj]);
		lastObj=null;
		if(highlight) highlight.hide();
	}
	
	if((isrun==2 || isrun==3) && granted!=0)
	{
		$('#cmd_finish_task').show();
		//$('#cmd_finish_task').effect("highlight", 500);
		$('#task_process .name').attr('task_id',task_id);
		if(task_code!="" && task_code!="-")
			$('#cmd_open_task').show();
	}
	else{
		$('#cmd_finish_task').hide();
		$('#cmd_open_task').hide();
		$('#task_process .name').attr('task_id',0);
	}
	lastObj=obj;
	if(lastObj){
		highlight = new mxCellHighlight(ed.graph, isrun==3?'red':'#378de5', 5);
		highlight.highlight(ed.graph.view.getState(lastObj));
		ed.graph.clearSelection();
	}
}
window.onbeforeunload = function() { if (typeof mxResources != 'undefined') return mxResources.get('changesLost'); };

function openTask(){
	if(task_code && task_code!=""){
		param = {
				'act' : 'opentask',
				'taskcode' : task_code
		} 

	 	sendAjax('/openTask', param, function(data){
	 		parent.window.location.href = data['url']+"?open_task_id="+task_id;
		});
	}
}
function refreshPage(){
	if(parent) parent.show_wf_loading();
	location.href="wfshow.blade.php?wf_id="+$('#cbo_wflist').val();
	if(parent) parent.loadTasksCounting();
}
function loadWorkflow(){
	var wfid=$('#cbo_wflist').val();
	if(wfid>0){
		lastObj=null;
		task_code="";
		task_id = "";
		$('#cmd_open_task').hide();
		$('#cmd_finish_task').hide();
		$('#task_process .name').attr('task_id',0);
		loadSavedDiagram(wfid);
	}
}