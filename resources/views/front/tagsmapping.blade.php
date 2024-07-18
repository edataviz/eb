<?php
	$currentSubmenu ='/tagsMapping';
	$tables = ['IntTagMapping'	=>['name'=>'Tags Mapping']];
 	$active = 0;
	$isAction = true;
	$lastFilter	=  "ObjectName";
	$qltyProductElementType		= \App\Models\QltyProductElementType::loadActive();
	$codeQltySrcType			= \App\Models\CodeQltySrcType::loadActive();
	$codeSampleType				= \App\Models\CodeSampleType::loadActive();
	$keystore					= \App\Models\Keystore::loadBy(null);
	$keystoreInjectionPoint		= \App\Models\KeystoreInjectionPoint::loadBy(null);
	
	?>

@extends('core.sc')
@section('funtionName')
TAG MAPPING CONFIG
@stop

@section('adaptData')
@parent
<script>
var qltyProductElementType		= <?php echo json_encode($qltyProductElementType);		?>;
var codeQltySrcType				= <?php echo json_encode($codeQltySrcType);		?>;
var codeSampleType				= <?php echo json_encode($codeSampleType);		?>;
var keystore					= <?php echo json_encode($keystore);		?>;
var keystoreInjectionPoint		= <?php echo json_encode($keystoreInjectionPoint);		?>;
var configType					= "QLTY_DATA";

var buildMoreEditableType 		= function(types){
		types['KEYSTORE_INJECTION_POINT_DAY']	=  [	
							    	{
										type		: "select",
										name		: "Keystore",
										label		: "Keystore",
										collection	: "keystore",
									},
							    	{
										type		: "select",
										name		: "KeystoreInjectionPoint",
										label		: "Keystore Inj Point",
										collection	: "keystoreInjectionPoint",
									},
	 							];
		types[configType]	=  [	
							    	{
										type		: "select",
										name		: "CodeSampleType",
										label		: "Sample Type",
										collection	: "codeSampleType",
									},
							    	{
							    		type		: "select",
							    		name		: "CodeQltySrcType",
							    		label		: "Sample Source",
							    		collection	: "codeQltySrcType",
										id			: "CodeQltySrcType",
										dependence	: "ExtensionQltySrcObject",
										extra		: ["Facility"]
							    	},
							    	{
										type		: "select",
										name		: "ExtensionQltySrcObject",
										id			: "ExtensionQltySrcObject",
							    		display		: true,
										label		: "Object Name",
									},
	 							];
			types["QLTY_DATA_DETAIL"]	= [	
									    	{
												type		: "select",
												name		: "CodeSampleType",
												id			: "detail_CodeSampleType",
												label		: "Sample Type",
												collection	: "codeSampleType",
											},
									    	{
									    		type		: "select",
									    		name		: "CodeQltySrcType",
									    		label		: "Sample Source",
									    		collection	: "codeQltySrcType",
												id			: "detail_CodeQltySrcType",
												dependence	: "ExtensionQltySrcObject",
												valueId 	: "",
												extra		: ["Facility"]
									    	},
									    	{
												type		: "select",
												name		: "ExtensionQltySrcObject",
												id			: "detail_ExtensionQltySrcObject",
									    		display		: true,
												label		: "Object Name",
											},
											{
									    		type		: "select",
									    		name		: "QltyProductElementType",
									    		label		: "Element Type",
									    		collection	: "qltyProductElementType",
									    	}
			 							];
		return types;
	}

var checkDependenceLoading = function(elementId,element,currentId){
	return elementId.indexOf("ExtensionQltySrcObject") !== -1;
}

var secondaryDependeceNameFn = function(value,elementId){
	if(value=="Facility") return value;
	return elementId;
}

$(document).ready(function () {
	$('#IntObjectType').change(function(e){
		if($('#IntObjectType > option[value="'+$(this).val()+'"]').attr("name")=="QLTY_DATA"){
	        $( "#select_container_ObjectName" ).css("position","fixed");
	        $( "#select_container_ObjectName" ).css("top","-100px");
		}
		else{
			$( "#select_container_ObjectName" ).css("position","");
	        $( "#select_container_ObjectName" ).css("top","");
		}
	});
});

</script>
<script src="/common/edittable/event.js?52"></script>
<script>
	actions.loadUrl = "/tagsMapping/load";
	actions.saveUrl = "/tagsMapping/save";
	actions.type = {
					idName:['ID','OBJECT_TYPE'],
					keyField:'ID',
					saveKeyField : function (model){
						return 'ID';
						},
					};
	actions.extraDataSetColumns = {
									'OBJECT_ID':'OBJECT_ID',
									'TABLE_NAME':'TABLE_NAME',
									'COLUMN_NAME':'TABLE_NAME'
								};
	
	source['TABLE_NAME']	=	{	dependenceColumnName	:	['COLUMN_NAME'],
 									url						: 	'/tagsMapping/loadsrc'
								};

	addingOptions.keepColumns = ['TABLE_NAME','COLUMN_NAME','OBJECT_TYPE'];

	actions['doMoreAddingRow'] = function(addingRow){
		if(typeof addingRow['OBJECT_TYPE'] == "undefined") addingRow['OBJECT_TYPE'] 	= $('#IntObjectType').val();
		addingRow['OBJECT_ID'] 		= $('#ObjectName').val();
		addingRow['SYSTEM_ID'] 		= 1;
		addingRow['FREQUENCY'] 		= 1;
		addingRow['FLOW_PHASE'] 	= 1;
		addingRow['EVENT_TYPE'] 	= 1;
		return addingRow;
	}

	actions.configEventType = function (editable,columnName,cellData,rowData){
		if(columnName=="configs"||columnName=="CONFIGS") {
			if(cellData!=null) {
				cellData.qltyProductElementType = qltyProductElementType;
				cellData.codeQltySrcType 		= codeQltySrcType;
				cellData.codeSampleType 		= codeSampleType;
				cellData.keystore 				= keystore;
				cellData.keystoreInjectionPoint = keystoreInjectionPoint;
			}
			editable.configType = rowData.TABLE_NAME;
			editable.placement 	= "bottom";
		}
	}

	oPreEditableShow = actions.preEditableShow;
	actions.preEditableShow  = function(){
		oPreEditableShow();
		firstTime = true;
	};

	actions.renderEventConfig = function( columnName,data2, type2, row){
		if(data2==null) return "config";
		if(columnName=="config"||columnName=="CONFIGS") {
			return typeof data2=="object"&&typeof data2.name != "undefined"? data2.name:
				(typeof data2.NAME != "undefined"? data2.NAME:"config");
		}
		return "config";
	}

	var odominoColumns	= actions.dominoColumns;
	actions.dominoColumns = function(columnName,newValue,tab,rowData,collection,table,td){
		odominoColumns(columnName,newValue,tab,rowData,collection,table,td);
		if(columnName=="table_name"||columnName=="TABLE_NAME"){
			var dependence = typeof rowData.configs != "undefined"? 'configs':'CONFIGS';
			var DT_RowId = rowData['DT_RowId'];
			var dependencetd = $('#'+DT_RowId+" ."+dependence);
			actions.applyEditable(tab,"EVENT",dependencetd, null, rowData, dependence);
			actions.createdFirstCellColumnByTable(table,rowData,td,tab);
		}
	}
</script>
@stop
