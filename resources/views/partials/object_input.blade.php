<?php
// 	$isFilterModify	= isset($isFilterModify)	? $isFilterModify	:false;
?>

@section('extraAdaptData')
@parent
<script>
var viewRenderId 		= "inputObjectName";
var inputObjectNameUrl 	="/graph/objects";
editBox.size			= {	height : 350,
							width : 900,
							};

editBox.getSaveDetailUrl = function (url,editId,viewId){
	if(viewId==viewRenderId) 	return inputObjectNameUrl;
	return null;
}

graphAssitance = {};
graphAssitance.isInputObjectsValidated = function(finishCallback,showInputs,oDataStores){
	var isAddObjectInputs = false;
	var dataStores = [];
	if(oDataStores===undefined){
		var unsetSpans = [];
		var oDataStores = [];
		$("#chartObjectContainer .x_item span").each(function(index, spanElement) {
			var dataStore	= $(spanElement).data();
			var shouldCheck = graphAssitance.shouldCheckObjectName(dataStore);
			if(shouldCheck&&(dataStore.ObjectName === undefined || dataStore.ObjectName <= 0 || dataStore.ObjectNameVariable == true)) {
				unsetSpans.push(spanElement);
				oDataStores.push(dataStore);
			}
		});
		isAddObjectInputs = unsetSpans.length >0;
	}
	else{
		$(oDataStores).each(function(index, dataStore) {
            var shouldCheck = graphAssitance.shouldCheckObjectName(dataStore);
            if(shouldCheck&&(dataStore.ObjectName === undefined || dataStore.ObjectName <= 0 || dataStore.ObjectNameVariable == true)) {
				dataStores.push(dataStore);
			}
		});
		
		isAddObjectInputs = dataStores.length >0;
	}

	if( isAddObjectInputs ) {
// 		var dataStores = [];
    	editBox.preSendingRequest = function (viewId){
	    	if(viewId == viewRenderId){
				$("#box_loading").css("display","none");
			    $("#"+viewRenderId).empty();
			    var ul				= $("<ul class='ListStyleNone'>");
			    if(unsetSpans!==undefined){
				    $(unsetSpans).each(function(index, spanElement) {
						var dataStore		= $(spanElement).data();
						var elementId		= Math.random().toString(36).substring(8);
						var li 				= $("<li class='x_item'></li>");
						var sel				= "<select style='width:auto'></select>";
						var select			= $(sel);
						var span 			= $("<span></span>");
						select.attr('id',elementId);
						select.prop('disabled', true);
						span.text($(spanElement).text());
						span.appendTo(li);
						select.appendTo(li);
						li.appendTo(ul);
	
						var dataStore	= $(spanElement).data();
						dataStore.elementId = elementId;
						dataStores.push(dataStore);
						$(spanElement).attr('id',"origin_"+elementId);;
					});
			    }
			    else{
				    $(dataStores).each(function(index, dataStore) {
						var elementId		= Math.random().toString(36).substring(8);
						var li 				= $("<li class='x_item'></li>");
						var sel				= "<select style='width:auto'></select>";
						var select			= $(sel);
						var span 			= $("<span></span>");
						select.attr('id',elementId);
						select.prop('disabled', true);
						span.text(dataStore.text);
						span.appendTo(li);
						select.appendTo(li);
						li.appendTo(ul);
						dataStore.elementId = elementId;
					});
			    }
				ul.appendTo($("#"+viewRenderId));
	    	}
    	}
    	var success = function(iDataStores){
    		for (var i = 0; i < iDataStores.length; i++) {
	    		var dataStore = iDataStores[i];
    			$(dataStore.objects).each(function(index2, object) {
	    			var option = renderDependenceHtml(dataStore.elementId,object);
	    			$("#"+dataStore.elementId).append(option);
				});
    			$("#"+dataStore.elementId).prop('disabled', false);
    			$("#"+dataStore.elementId).val(dataStore.ObjectName);

    			var className = "input_object_"+dataStore.IntObjectType+"_"+dataStore.Facility+"_"+dataStore.CodeProductType;
    			$("#"+dataStore.elementId).addClass(className);
    			$("#"+dataStore.elementId).change(function(e){
    				if (ctrlKeyHeld){
    					ctrlKeyHeld = false;
    					$("."+this.className).val($(this).val());
// 		    			alert("keke");
	    		    }
    			});
    		}
    		
    		editBox.saveDetail = function(editId,success,saveUrl) {
	    		if(saveUrl!=null){
	    			for (var i = 0; i < iDataStores.length; i++) {
			    		var dataStore 			= iDataStores[i];
		    			var tmpSpan 			= $("#"+dataStore.elementId);
		    			currentSpan 			= $("#origin_"+dataStore.elementId);
			    		dataStore.ObjectName 	= tmpSpan.val();
						var x 					= editBox.getObjectValue(dataStore);
						var oldText 			= currentSpan.text();
						oldText					= graphAssitance.getObjectItemName(oldText,tmpSpan.find(":selected").text());
						editBox.editSelectedObjects(dataStore,oldText,x);
			    		dataStore.ObjectNameVariable = true;
		    			currentSpan.data(dataStore);
		    			oDataStores.push(dataStore);
		    		}

	    			var inputedDataStores = oDataStores.filter(function(obj) {
	    			    return obj.ObjectName !== undefined &&  obj.ObjectName != "" && obj.ObjectName>0;
	    			});
	    			
	    			editBox.closeEditWindow(true);
	    			if(typeof finishCallback == "function") finishCallback(inputedDataStores);
	    		}
	    		else{
	    			alert('no item change to commit');
	    		}
	    	}
		}
		
    	option = {
			    	title 		: "Select object name",
			 		url 		: inputObjectNameUrl,
			 		size		: {height: 350,width:600},
			 		viewId 		: viewRenderId,
			 		postData 	: {dataStores	: dataStores},
    	    	};

		editBox.showDialog(option,success);
		return false;
	}
	
	return true;
};

graphAssitance.shouldCheckObjectName = function(dataStore){

    return dataStore!==undefined && dataStore.IntObjectType!='DEFERMENT';
}

graphAssitance.getObjectItemName = function(oldText,preText){
	if(oldText.indexOf("(")>=0){
		oldText				= oldText.substring(oldText.indexOf("("));
		preText				= preText!==undefined?preText:"None";
		oldText				= preText + oldText;
	}
	return oldText;
}


graphAssitance.getChartConfigText = function(dataStores,element){
	var s="";
	$(dataStores).each(function(index, dataStore) {
		var x 				= editBox.getObjectValue(dataStore);
		var objectNameText 	= dataStore.text;
		s += (s==""?"":",")+x+":"+dataStore.chartType+":"+objectNameText+":#"+dataStore.color;
    });
    s = s.replace(/undefined/g, '');
	return s;
}

graphAssitance.getChartConfigById = function(id,element){
	var oDataStores		= $(element).closest( ".container" ).data();
	var dataStores 		= Object.keys(oDataStores).map(function (key) { return oDataStores[key]; });
	if(dataStores.length>0){
		var iframe 	= $(element).closest( ".container" ).find("iframe:first");
		loadChart(iframe);
	}
	else{
		showWaiting();
		$.ajax({
			url			: "/graph/chart/"+id,
			type		: "post",
			data		: {id:id},
			success		: function(data) {
				hideWaiting();
				console.log ( "getChartConfigById success: "/*+JSON.stringify(data)*/);
				console.log ( data);
				var cfgs = graphAssitance.parseChartConfig(data.CONFIG);
				dataStores	= cfgs.dataStores
				console.log ( dataStores);
				$(element).closest( ".container" ).data(dataStores);
				var iframe 	= $(element).closest( ".container" ).find("iframe:first");
				loadChart(iframe);
				
			},
			error		: function(data) {
				hideWaiting();
				console.log ( "extensionHandle error: "/*+JSON.stringify(data)*/);
			}
		});
	}
}

graphAssitance.parseChartConfig = function(config){
	var dataStores 	= [];
	var extension 	= {};
	if(typeof config==="string"&& config != ""){
		var cfgs=config.split("\n");
		var cfs=cfgs[0].split(',');
		var i=0;
		for(i=0;i<cfs.length;i++){
			var configEntry = cfs[i];
			var	vals		= configEntry.split(':');
			if(vals.length>=6){
				var k		= vals[vals.length-1][0]=="#"?3:2;
				var color	= vals[vals.length-1][0]=="#"?vals[vals.length-1].substr(1):"transparent";

				var extra = configEntry.split('~');
				var CodeProductType = extra.length>7?extra[extra.length-2]:0;
				var dataStore		= {	
//							LoProductionUnit	:	$("#LoProductionUnit").val(),
//							LoArea				:	$("#LoArea").val(),
//							Facility			:	$("#Facility").val(),
//							CodeProductType		:	$("#CodeProductType").val(),
						IntObjectType		:	vals[0],
						ObjectName			:	vals[1],
						ObjectDataSource	:	vals[2],
						GraphObjectTypeProperty	:	vals[3],
						CodeProductType		:	CodeProductType,
						chartType			:	vals[vals.length-k],
						text				:	vals[vals.length-k+1],
						color				:	color,
						cboYPos 			:	$("#edit_cboYPos").val(),
						txt_y_unit			:	$("#edit_txt_y_unit").val(),
				};

				var types	= vals[4].split('~');
				if(types.length>0) dataStore.CodeFlowPhase		= types[0];
				if(types.length>1) dataStore.CodeAllocType		= types[1];
				if(types.length>2) dataStore.CodePlanType		= types[2];
				if(types.length>3) dataStore.CodeForecastType	= types[3];
				if(types.length>7) dataStore.CodeEventType		= types[7];
				if(types.length>5){
					dataStore.cboYPos	=$types[4];
					dataStore.txt_y_unit	=$types[5];
				}
				dataStores.push(dataStore);
			}
		}

		if(cfgs.length>1){
			var extentionString	= stripslashes(cfgs[1]);
			try {
				extension = $.parseJSON(extentionString);
			}
			catch(err) {
			    console.log("can not parse  graph extentions +\n"+err.message);
			}
		}
	}
	return {dataStores 	: dataStores,
			extension 	: extension};
}

var ctrlKeyHeld = false;

$(document).keydown(function(e) {
    ctrlKeyHeld = e.ctrlKey;
}).keyup(function(e1) {
	ctrlKeyHeld = e1.ctrlKey;
})
		
</script>
@stop

@section('endDdaptData')
@parent
<script>
	var oEditSelectedObjects = editBox.editSelectedObjects;
	editBox.editSelectedObjects = function (dataStore,resultText,x){
		if(dataStore.ObjectName>0) dataStore.ObjectNameVariable = false;
		oEditSelectedObjects(dataStore,resultText,x);
	};
</script>
@stop