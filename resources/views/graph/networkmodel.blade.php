<?php
$enableHeader	= false;
$enableFooter	= false;
$useFeatures	= [
					['name'	=>	"display_diagram",
					"data"	=>	[]]
];

$codeFlowPhase	= array("name"			=> "CodeFlowPhase",
						"id"			=> "CodeFlowPhase",
						"modelName"		=> "CodeFlowPhase",
						"enableTitle"	=> false,
						"defaultEnable"	=> false,
						"getMethod" 	=> "loadActive");
?>

@extends('core.bsdiagram')

@section('adaptData')
<script  type="text/javascript"  src="/common/js/base64.js"></script>
<script type="text/javascript">

var diagram_xml="";
<?php
	if($diagram_id>0) echo "diagram_xml='".base64_encode($xml)."';\r\n";
?>

function loadDiagramFromXML(){
	if(diagram_xml=="") return;
	var doc = mxUtils.parseXml(Base64.decode(diagram_xml));
	var dec = new mxCodec(doc);
	clearGraph();
	dec.decode(doc.documentElement, ed.graph.getModel());
	lastObj=null;

	ed.graph.setCellsLocked(ed.graph.model.cells);
	ed.graph.fit(0,false);
	var scale=ed.graph.view.getScale();
	scale=0.5/scale/scale;
	//alert(0.5/scale);
	ed.graph.center(true,true,scale,scale);
	//console.log(xmlcode);
	ed.graph.refresh();
}

    var mxBasePath = '';

    var urlParams = (function(url)
    {
        var result = new Object();
        var params = window.location.search.slice(1).split('&');

        for (var i = 0; i < params.length; i++)
        {
            idx = params[i].indexOf('=');

            if (idx > 0)
            {
                result[params[i].substring(0, idx)] = params[i].substring(idx + 1);
            }
        }

        return result;
    })(window.location.href);

    var mxLanguage = urlParams['lang'];
var ed;
function onInit(editor)
{
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

	editor.graph.dblClick = function(evt, cell)
	{
		var href = cell.getAttribute('href');
		if(href!==undefined)
			window.open(href);
	}

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
	loadDiagramFromXML();
	display();
}

function updateSurPhaseCellPosition(baseCell){
	var ind=Number(baseCell.getAttribute("sur_phase_index"));
	var id1=baseCell.id.substr(0,baseCell.id.lastIndexOf('_')+1);
	for(i=0;i<30;i++){
		var cell=ed.graph.model.getCell(id1+i);
		if(typeof cell!=='undefined'){
			var ind2=Number(cell.getAttribute("sur_phase_index"));
			if(ind2!=ind){
				cell.geometry.y=baseCell.geometry.y+(ind2-ind)*baseCell.geometry.height;
				cell.geometry.x=baseCell.geometry.x;
			}
		}
	}
}

function clearGraph()
{
    ed.graph.removeCells(ed.graph.getChildVertices(ed.graph.getDefaultParent()));
}
window.onbeforeunload = function() { return mxResources.get('changesLost'); };


function getParams(){
	var occur_date="<?php echo $occur_date; ?>";
	var flow_phase= $("#CodeFlowPhase").val();
	flow_phase	= 1;
	return  {
		'occur_date':occur_date,
		'flow_phase':flow_phase
	}
}

</script>

<div id="graph" style="margin-top:15px;position:relative;height:calc(100% - 20px);width:100%;box-sizing: border-box;cursor:default;overflow:hidden;border:0px solid #a0a0a0;">
<?php 
	if(!($diagram_id>0)) 
		echo '<p class="center_content">No diagram</p>';
	else {
// 		echo \Helper::filter($codeFlowPhase);
	}
?>

</div>
<div style="display:none; padding:10px;" id="toolbar" >
</div>

<script>
$(document).ready(function(){
	<?php 
 			if($diagram_id>0) echo "new mxApplication('/config/diagrameditor.xml?11');"; 
// 			if(!($diagram_id>0)) echo '<p class="center_content">No diagram</p>';
	?>
	$('#CodeFlowPhase').change(function(e){
		display()
	});
	$('#CodeFlowPhase').css("display","none");


	@if($refreshInterval>0)
		var seconds 	= <?php echo json_encode($refreshInterval); ?>;
		var miliseconds = seconds*1000;
		var taskRefreshTimer = null;
		function loadRefreshTimer(){
			console.log("taskRefreshTimer");
			if(taskRefreshTimer != null) {
				clearTimeout(taskRefreshTimer);
				taskRefreshTimer=null;
			}
			display();
		}
		actions.onUpdateValueSurveillanceFinish = function(respondData){
			taskRefreshTimer=setTimeout(loadRefreshTimer,miliseconds);
		}
	@endif
});
</script>
@stop