@section('display_diagram')
	<script>
		loadEBjs();
/*
		ed.graph.isAutoSizeCell = function(cell)
		{
		  var state = this.view.getState(cell);
		  var style = (state != null) ? state.style : this.getCellStyle(cell);

		  return this.isAutoSizeCells() || style['autosize'] == 1;
		};

		ed.graph.setAutoSizeCells(true);
		ed.graph.setCellsResizable(true);
*/		
		function getSurveilanceObject(cell){
			var jsonPhaseConfigObject;
			var sur_phase_config	= cell.getAttribute('sur_phase_config');
			if(typeof sur_phase_config =="string"){
				try {
					jsonPhaseConfigObject = $.parseJSON(sur_phase_config);
				}
				catch(err) {
					console.log("error parse phase config json \n"+ err+"\n phaseconfig: \n"+sur_phase_config);
					jsonPhaseConfigObject 	= [];
					var phaseConfig 		= sur_phase_config;
					var cfgs				= phaseConfig.split("!!");
					var dataField			= cfgs.length>1?cfgs[1]:"";
					cfgs					= cfgs[0].split("@@");
					
					for (i = 0; i < cfgs.length; i++) {
						var attrs=cfgs[i].split("^^");
						if(attrs.length>=4){
							var phase_id=attrs[0], eventType=1, prefix=attrs[2], subfix=attrs[3];
							jsonPhaseConfigObject.push({
								phaseId		: phase_id,
								eventType	: eventType,
								prefix		: prefix,
								subfix		: subfix,
								dataField	: dataField,
								value		: "--",
							});
						}
					}
					cell.setAttribute('sur_phase_config',JSON.stringify(jsonPhaseConfigObject));
				}
			}
			return jsonPhaseConfigObject;
		}

		function getSurveilanceTagLabels(cell){
			var tagLabels 			= {};
			var label_subfixId 		= 'label_'+cell.id+"_";
			for (i = 0; i < 30; i++){
				var label=ed.graph.model.getCell(label_subfixId+i);
				if( label!==undefined) {
					var tagId = label.getAttribute('tagId');
					var tagLabel = label.getAttribute('tagName');
					if(tagId!==undefined &&tagId!=null&& tagId!="" && tagLabel!==undefined &&tagLabel!=null &&tagLabel!="" ) tagLabels[tagId] = tagLabel;
				}
			}
			return tagLabels;
		}
		
		function display(){
			var cells=ed.graph.model.cells;
			var param	= getParams();
			var vparam="";
			var sData = [];	
			for(c in cells){
				var cell=cells[c];
				var exp = cell.getAttribute('expression');
				var v;
				if(exp != undefined && exp.length>0){
					v = {
						'ID' 				: cell.getId(),
						'EXP' 				: exp,
					}
				}
				else {
					if(cell.getId().substr(0,5)=='label') continue;			//skip label
					
					var su=cell.getAttribute('surveillance')+"";
					su=su.trim();
					var sur_phase_config=cell.getAttribute('sur_phase_config')+"";
					sur_phase_config=sur_phase_config.trim();
					if(sur_phase_config=="undefined") sur_phase_config="";
					if(su=="undefined") su="";
			
					if(su+sur_phase_config=="") continue;
					var conn_id=cell.getAttribute('conn_id');
					if(conn_id=="undefined") conn_id=""; 
			
					var phaseConfig 		= getSurveilanceObject(cell);
					var tagNames 			= getSurveilanceTagLabels(cell);
					
					v = {
						'ID' 				: cell.getId(),
						'OBJECT_TYPE' 		: cell.getAttribute('object_type'),
						'OBJECT_ID' 		: cell.getAttribute('object_id'),
						'CONN_ID' 			: conn_id,
						'SUR_PHASE_CONFIG' 	: phaseConfig,
						'SU' 				: su,
						"tagNames"			: tagNames
					}
				}
		
				sData.push(v); 
			}
			if(sData!=""){
				param.vparam = sData;
		
				sendAjax('/getValueSurveillance', param, function(respondData){
					var data = respondData.data;
					if(data.substr(0,2)!='ok'){
						alert(data);
						return;
					}
					for(var cellId in respondData.expressionData){
						ed.graph.model.getCell(cellId).setAttribute('label', respondData.expressionData[cellId]);
					}
					for(var cellId in respondData.cellConfigs){
						updateCellLabel(cellId,respondData.cellConfigs[cellId],respondData.properties);
					}
					ed.graph.refresh();
					if(typeof actions.onUpdateValueSurveillanceFinish=="function") actions.onUpdateValueSurveillanceFinish(respondData);
			   }); 
			}
		}
		
		function updateCellLabel(cellId,cellData,properties){

			var model 				= ed.graph.model;
			var index 				= 0;
			var label_subfixId 		= 'label_'+cellId+"_";
			var doc 				= mxUtils.createXmlDocument();
			var cell = ed.graph.model.getCell(cellId);
			var text = "--";
			var oldCell = ed.graph.model.getCell('label_'+cellId);
			if(typeof oldCell!=='undefined') ed.graph.removeCells([oldCell]);
			model.beginUpdate();
			try {
				for(var field in cellData){
					var cellConfig	= cellData[field];
					var newValue	= cellConfig.value;
					if(field!="%SF") {
						var label=ed.graph.model.getCell(label_subfixId+index);
						if(typeof label=='undefined') {
							label = addOrUpdateLabel(doc,cell,text,index);
						}
						var renderFuntion = function(color,cssProperty){
							var style = label.getStyle();
							label.setStyle(style+";fontColor="+color+";fontSize=15;fillColor=transparent;strokeColor=none;resizable=0;autosize=1;");
						}
						var cIndex			= cellConfig.index;
						var valueText 		= newValue !== undefined && newValue != null?newValue:"--";
						if(cIndex!==undefined){
							var rowData			= {DT_RowId				: cellConfig.OBJECT_ID };
							rowData[actions.type.idName[0]] = cellConfig.OBJECT_ID;
							var property 	= properties[cIndex];
							var objectRules	= actions.getObjectRules(property,rowData);
							var basicRules	= actions.getBasicRules(property,objectRules);
							label.setAttribute('label', (property.title)+": "+valueText);
	// 						label.setStyle('text_sur;fillColor=white;');
							actions.addCellNumberRules(renderFuntion,basicRules,newValue,rowData,"","loading");
						}
						else if(cellConfig.tag!==undefined){
							label.setAttribute('label', cellConfig.tagName+" : "+valueText);
							label.setAttribute('tagId', cellConfig.tag);
							label.setAttribute('tagName', cellConfig.tagName);
							var style = label.getStyle();
							label.setStyle(style+";fillColor=transparent;strokeColor=none;resizable=0;autosize=1;");
						}
						ed.graph.updateCellSize(label);
						index++;
					}
					else{
						addOrUpdateSurLabels(doc,cell,cellConfig);
					}
				}
			}
			finally {
			  model.endUpdate();
			}
		}
		
		function getSurveilanceTextId(cellId,dataField,prefix,subfix,phase_id,eventType){
			return  ["sur_val",cellId,dataField,prefix,subfix,phase_id,eventType].join("_");
		}

		function getGeometryOf(cell){
			var cellGeometry =  jQuery.extend({},cell.getGeometry());
			if(cellGeometry===undefined || (cellGeometry.x==0 && cellGeometry.y==0)){
				if(cell.target!==undefined && cell.source!==undefined ){
					var sourceGeometry 		= cell.source.getGeometry();
					var targetGeometry 		= cell.target.getGeometry();
					if(sourceGeometry!==undefined && targetGeometry!==undefined ){
						cellGeometry.x		= (sourceGeometry.x + targetGeometry.x)/2;
						cellGeometry.y		= (sourceGeometry.y + targetGeometry.y)/2;
					}
				}
			}
			return  cellGeometry;
		}


		function addOrUpdateSurLabels(doc,cell,phaseConfig){

			var cellGeometry	=  getGeometryOf(cell);
			var cellX 	= Number(cellGeometry.x);
			var cellY 	= Number(cellGeometry.y-50);
			//var parent = ed.graph.getDefaultParent();
			var parent 	= cell.parent;
			var model 	= ed.graph.model;
			var id		= cell.getId();
			
			//Phase value cells
			var addingField;
			$.each(ed.graph.getChildCells(), function(key, cell) {
				if(cell.id.lastIndexOf("sur_val_"+id+"_", 0) === 0) ed.graph.removeCells([cell]);
		    });

			$.each(phaseConfig, function(s_ind, attrs) {
				var phase_id		= attrs.phaseId;
				var eventType		= attrs.eventType;
				var prefix			= attrs.prefix;
				var subfix			= attrs.subfix;
				var dataField		= attrs.dataField;
				var value			= attrs.value;
				var phase_name		= $("#sur_flow_phase  option[value='"+phase_id+"']").text();
				var eventTypeName	= $("#sur_event_type  option[value='"+eventType+"']").text();
				eventTypeName 		= eventTypeName!=""?" - "+eventTypeName:"";
				
				var labelText		= (prefix+""==""?phase_name:prefix) + eventTypeName+ ": "+value+" " + subfix;
				addingField 		= getSurveilanceTextId(id,dataField,prefix,subfix,phase_id,eventType);
				var label 			= model.getCell(addingField);
				
				if(typeof label==='undefined'){
					model.beginUpdate();
					try {
						var n2 = doc.createElement('MyNode');
						n2.setAttribute('label',labelText);
						var v1=ed.graph.insertVertex(parent, addingField, n2, cellX, cellY + cell.geometry.height+20+(s_ind)*20, 160, 20);
						var fillColor="gray";
						if(phase_id==1) fillColor="#FBD070";
						if(phase_id==2) fillColor="#FF9696";
						if(phase_id==3) fillColor="#62CEFB";
						v1.setStyle('text_sur;dashed=0;strokeColor=none;resizable=0;autosize=1;fontColor=black;fillColor='+fillColor+';');
						v1.setVisible(true);
						v1.setAttribute('parentCelId', id);
						v1.setAttribute('sur_phase_index', s_ind);
						v1.setAttribute('phase_id', phase_id);
						v1.setAttribute('eventType', eventType);
						v1.setAttribute('phase_name', phase_name);
						v1.setAttribute('prefix', prefix);
						v1.setAttribute('subfix', subfix);
						if(s_ind==0){
							e=ed.graph.insertEdge(parent, 'sur_edge_'+id+'_'+phase_id, '', v1, cell);
							e.setStyle('dashed=1;strokeColor=transparent;fontSize=15;resizable=0;autosize=1;');
						}
						ed.graph.updateCellSize(v1);
					}
					finally {
					  model.endUpdate();
					}
				}
				else{
					label.setAttribute('label', labelText);
				}
		    });
		}

		function addOrUpdateLabels(doc,cell,fields){
			var parent 	= cell.parent;
			var model 	= ed.graph.model;
			var id		= cell.getId();
			var oldCell = model.getCell('label_'+id);
			if(typeof oldCell!=='undefined') ed.graph.removeCells([oldCell]);
			for (i = 0; i < 30; i++){
				if(i<fields.length){
					var text = fields[i];
					addOrUpdateLabel(doc,cell,text,i);
				}
				else{
					var c = model.getCell('label_'+id+'_'+i);
					if(typeof c!=='undefined') ed.graph.removeCells([c]);
				}
			}
		}

		function addOrUpdateLabel(doc,cell,textValue,index){
			var parent 				= cell.parent;
			var model 				= ed.graph.model;
			var id					= cell.getId();
			var cellGeometry		= typeof textValue== "object" && typeof textValue.getGeometryOf=="function"?textValue.getGeometryOf(cell):getGeometryOf(cell);
			var width				= typeof textValue== "object" && typeof textValue.width=="function"?textValue.width(cell):160;
			var cellX 				= Number(cellGeometry.x);
			var cellY 				= Number(cellGeometry.y);
			var height				= 15;
			var label_subfixId 		= 'label_'+id+"_";
			var labelCellId			= label_subfixId+index;
			var label 				= model.getCell(labelCellId);
			var text				= typeof textValue== "object"? textValue.label:textValue;
			if(typeof label==='undefined') {
				model.beginUpdate();
				try {
					var node = doc.createElement('MyNode');
					node.setAttribute('label', text);
					var v1=ed.graph.insertVertex(parent, labelCellId, node, cellX, cellY-20-(index+1)*height, width, height);
					v1.setStyle('text_sur;fillColor=transparent;fontSize=15;strokeColor=none;resizable=0;autosize=1;');
					v1.setAttribute('label_index', index);
					v1.setAttribute('label_subfixId', label_subfixId);
					if(index==0){
						e=ed.graph.insertEdge(parent, 'edge_'+id+'_'+index, '', v1, cell);
						e.setStyle('text_sur;dashed=1;strokeColor=none;resizable=0;autosize=1;');
					}
					ed.graph.updateCellSize(v1);
				}
				finally {
				  model.endUpdate();
				}
				label = v1;
			}
			else {
				label.setAttribute('label', text);
// 				label.setStyle('text_sur;');
			}

			if(typeof textValue == "object"){
				for(var field in textValue){
					label.setAttribute(field, textValue[field]);
				}
			}
			return label;
		}

		function removeAllLabelsOf(cell){
			var model 	= ed.graph.model;
			var id		= cell.getId();
			var oldCell = model.getCell('label_'+id);
			if(typeof oldCell!=='undefined') ed.graph.removeCells([oldCell]);
			for (i = 0; i < 30; i++){
				var c = model.getCell('label_'+id+'_'+i);
				if(typeof c!=='undefined') ed.graph.removeCells([c]);
			}
		}

		function showObjectTooltip(cell){			
			if(cell.id!==undefined && cell.id!== "" && cell.id.indexOf("label")==0){
				var tagName	= cell.getAttribute('tagId');
				if(tagName!==undefined&&tagName!=""&&tagName!=null) return '<b>'+tagName+'</b>';
			}
			return cell.getAttribute('Tooltip');
		}
			
	</script>
@stop	
