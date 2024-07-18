<?php
	$isFilterModify	= isset($isFilterModify)	? $isFilterModify	:false;
?>

@if(isset($isAction)&&$isAction)
	@section('script')
		@parent
		<script src="/common/js/eb_table_action.js?19"></script>
	@stop
@endif

@section('extraAdaptData')
@parent
	@section('floatWindow')
		@yield('editBox')
		@include('core.edit_dialog')
	@stop
	
	@if(isset($isFilterModify)&&$isFilterModify)
		<script>
			if(typeof filters == "undefined") filters = {};
			filters.afterRenderingDependences	= function(id){
				var partials 		= id.split("_");
				var prefix 			= partials.length>1?partials[0]+"_":"";
				var model 			= partials.length>1?partials[1]:id;
				var currentObject	= "#"+prefix;

				if(model=="ObjectName"){
                    $('#title_'+id).text($(currentObject+"IntObjectType").find(":selected").text());
                    filters.preOnchange(prefix+"ObjectName");
                }
				else if(model=="ObjectDataSource")
					filters.preOnchange(prefix+"ObjectDataSource");

			};
			filters.preOnchange		= function(id, dependentIds,more){
				var partials 		= id.split("_");
				var prefix 			= partials.length>1?partials[0]+"_":"";
				var model 			= partials.length>1?partials[1]:id;
				var selectedObject	= "#select_container_"+prefix;
				var currentObject	= "#"+prefix;
				switch(model){
					case "IntObjectType":
                        var objectType = $(currentObject+model).find(":selected").attr( "name");
						if(objectType=="ENERGY_UNIT"||objectType=="EU_TEST"){
							$(selectedObject+'CodeFlowPhase').css("display","block");
							$(selectedObject+'CodeEventType').css("display","block");
						}
						else {
							$(selectedObject+'CodeFlowPhase').hide();
							$(selectedObject+'CodeEventType').hide();
							$(selectedObject+'CodeForecastType').hide();
							$(selectedObject+'CodePlanType').hide();
							$(selectedObject+'CodeAllocType').hide();
						}
                        // $(selectedObject+'ObjectName').css("display",objectType=="DEFERMENT"?"none":"block");
						break;
					case "ObjectDataSource":
						var objectDataSource 	= $(currentObject+'ObjectDataSource').val();
						if(typeof objectDataSource == "undefined") break;
						objectDataSource 		= objectDataSource.replace(/_/g," ");
						objectDataSource 		= objectDataSource.toLowerCase().replace(/\b[a-z]/g, function(letter) {
						    return letter.toUpperCase();
						});
						objectDataSource 		= objectDataSource.replace(/ /g,"");
						if(objectDataSource!=null){
							objectDataSource=='EnergyUnitDataAlloc'?$(selectedObject+'CodeAllocType').show():$(selectedObject+'CodeAllocType').hide();
							objectDataSource.endsWith("Plan")?$(selectedObject+'CodePlanType').show():$(selectedObject+'CodePlanType').hide();
							objectDataSource.endsWith("Forecast")?$(selectedObject+'CodeForecastType').show():$(selectedObject+'CodeForecastType').hide();
							if($(currentObject+"CodeFlowPhase").is(":visible")) {
								$(selectedObject+'CodePlanType').removeClass("clearBoth");
								$(selectedObject+'CodeAllocType').removeClass("clearBoth");
								$(selectedObject+'CodeForecastType').removeClass("clearBoth");
							}
							else {
								$(selectedObject+'CodePlanType').addClass("clearBoth");
								$(selectedObject+'CodeAllocType').addClass("clearBoth");
								$(selectedObject+'CodeForecastType').addClass("clearBoth");
							}
							if($(currentObject+"IntObjectType").find(":selected").attr( "name")=="ENERGY_UNIT"||
									$(currentObject+"IntObjectType").find(":selected").attr( "name")=="EU_TEST"){
								if(objectDataSource=='EnergyUnit'||
										objectDataSource=='Deferment'||
										objectDataSource=='Storage'||
										objectDataSource=='Tank'||
										objectDataSource=='Flow'){
									 $(selectedObject+'CodeFlowPhase').hide();
									 $(selectedObject+'CodeEventType').hide();
								}
								else {
									$(selectedObject+'CodeFlowPhase').show();
									$(selectedObject+'CodeEventType').show();
								}
							}
							
						}
						if(prefix=="") $("#tdObjectContainer").css({'height':($("#filterFrequence").height()+'px')});
						break;
				}
			};
		
			
			filters.moreDependence	= function(dependentIds,model,currentValue,prefix){
				if(model=="ObjectDataSource"&&$("#"+prefix+"IntObjectType").val()=="KEYSTORE"){
					if(isFirstDisplay&&prefix!="") {
						dependentIds = [{"name":"ObjectName","source":"ObjectDataSource"}];
						isFirstDisplay = false;
					}
					else dependentIds.push({"name":"ObjectName","source":"ObjectDataSource"});
				}
				return dependentIds;
			};
		</script>
		
		<script>
			var currentSpan = null;
			editBox.initExtraPostData = function (span,rowData){
			 						isFirstDisplay = false;
		 							currentSpan = span;
		 							var postData	= typeof span.data == "function"?span.data():{};
		 							return 	postData;
		 	};
		 	isFirstDisplay = false;
		 	editBox.editGroupSuccess = function(data,span){
		 		var viewId = typeof span=="object"&&typeof span.viewId!="undefined"?span.viewId:"editBoxContentview"; 
		 		$("#"+viewId).html(data);
		 		editBox.renderFilter(span);
		 		if(typeof editBox.updateExtraFilterData == "function"){
		 			var dataStore = typeof span.data == "function"? span.data():{};
					editBox.updateExtraFilterData(dataStore);
		 		}
			};

			editBox.renderFilter = function(rowData){
		 		filters.afterRenderingDependences("secondary_ObjectName");
		 		filters.preOnchange("secondary_IntObjectType");
		 		filters.preOnchange("secondary_ObjectDataSource");
		 		isFirstDisplay = true;
		 		if($("#secondary_IntObjectType").val()=="KEYSTORE") $("#secondary_ObjectDataSource").change();
			};
		

			 editBox.buildFilterText = function(){
					var resultText 	= "";
					var texts = {};
					var selects = $('#ebFilters_ ').find('.filter:visible select');
					selects.each(function(index, element) {
						texts[element.name]		= $("#"+element.id+" option:selected").text();
					});
					if(typeof editBox.addMoreFilterText == 'function') editBox.addMoreFilterText(texts);
					if(typeof editBox.renderOutputText == "function")
						resultText	= editBox.renderOutputText(texts);
					else 
						resultText	= JSON.stringify(texts);
					return resultText;
				}
		
			editBox.addObjectItem 	= function (color,dataStore,texts,x){
				var dcolor			= color=="transparent"?color:"#"+color;
				var li 				= $("<li class='x_item'></li>");
				var sel				= "<select class='x_chart_type' style='width:100px'><option value='line'>Line</option><option value='spline'>Curved line</option><option value='column'>Column</option><option value='area'>Area</option><option value='stacked'>Stacked</option><option value='areaspline'>Curved Area</option><option value='pie'>Pie</option></select>";
				var inputColor 		= "<input type='text' maxlength='6' size='6' style='background:"+dcolor+";color:"+dcolor+";' class='_colorpicker' value='"+(color=="transparent"?"7e6de3":color.replace("#", ""))+"'>";
				var select			= $(sel);
				var colorSelect		= $(inputColor);
				var span 			= $("<span></span>");
				var edit 			= $("<img valign='middle' class='xclose' src='/img/edit.png'>");
				var del				= $('<img valign="middle" onclick="$(this.parentElement).remove()" class="xclose" src="/img/x.png">');

				if(dataStore.hasOwnProperty('chartType')) select.val(dataStore.chartType);
				select.appendTo(li);
				colorSelect.appendTo(li);
				span.appendTo(li);
				edit.appendTo(li);
				del.appendTo(li);

				edit.click(function() {
					var title=prompt("Item title",span.text());
					if(title=="" || title == null) return;
					span.text(title);
				});
				
				currentSpan 		= span;
				span.click(function() {
					if(dataStore.IntObjectType == 'TAG'){}
					else
						editBox.editRow(span,{CODE:span.text()});
				});
				span.addClass("clickable");
				var rstext 			= typeof texts =="string"? texts:editBox.renderOutputText(texts);
				editBox.editSelectedObjects(dataStore,rstext,x);
				
				li.appendTo($("#chartObjectContainer"));
				setColorPicker();
			}
		
		</script>
	@endif
@stop
