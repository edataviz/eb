<?php
	$currentSubmenu ='/routeconfig';
	$tables = ['DcPointFlow'	=>['name'=>'Routes Config']];
	$isAction = true;
	$useFeatures	= isset($useFeatures)?$useFeatures:
						[
								['name'	=>	"filter_modify",
								"data"	=>	["isFilterModify"	=> true,
											"isAction"			=> $isAction]],
						];
						
						
	$filterGroups = array('productionFilterGroup'	=>[
																['name'			=>'IntObjectType',
																'independent'	=> true,
																"getMethod"		=> "loadBasic",
																'extra'			=> ["Facility"],
																],
															],
								'frequenceFilterGroup'	=> [	["name"			=> "ObjectName",
																"getMethod"		=> "loadBy",
																"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType"]],
															]],
								'FacilityDependentMore'	=> ["ObjectName"],
								'extra' 				=> ['IntObjectType',"ObjectName"]
						);
	
	if(isset($filterGroups['dateFilterGroup'])) unset($filterGroups['dateFilterGroup']);
	$filterGroups['enableButton']	= false;
?>

@extends('core.sc')

@section('editBoxContentview')
	@include('editfilter.route_editfilter',['filters'			=> $filterGroups,
							    			'prefix'			=> "secondary_",
									    	])
@stop

@section('adaptData')
@parent
    <link rel="stylesheet" href="/common/css/bootstrap-multiselect.css" />
    <script type="text/javascript" src="/common/js/bootstrap-multiselect.js?3"></script>
<style>
table.dataTable thead .sorting,
table.dataTable thead .sorting_asc,
table.dataTable thead .sorting_desc {
    text-align: left;
}
.multiselect-container input,
button.multiselect,
li.multiselect-filter,
#filterProduct
{
    display: none
}

#select_container_DcRoute,
#select_container_DcPoint{
	width: auto;
    min-width: 220px;
}
.dropdown-menu {
    min-width: 220px;
    z-index : 1;
}
.xclose{
	margin-top: 5px;
}

</style>
<script>
	actions.loadUrl = "/routeconfig/load";
	actions.saveUrl = "/routeconfig/save";
	actions.type = {
					idName:['DT_RowId','POINT_TYPE','ID','NAME'],
					keyField:'DT_RowId',
					saveKeyField : function (model){return 'DT_RowId';},
					};
	actions.extraDataSetColumns = {'NAME'					:'POINT_TYPE'};

	source['POINT_TYPE']		={	dependenceColumnName	:	['NAME'],
								url						: 	'/sourceconfig/loadsrc'
	};

	actions.enableUpdateView = function(tab,postData){
		return false;
	};
	
	$( document ).ready(function() {
	     $("#DcRoute,#DcPoint").each(function(){
             $(this).multiselect({
                 enableFiltering: false,
                 multiple : false,
                 allSelectedText: 'All',
                 numberDisplayed: 1,
                 nSelectedText: 'Selected',
                 nonSelectedText: 'None Selected',
                 onChange: function(option, checked) {
                     this.$select.change();
                 },
                 templates: {
                     li: '<li><a style="padding: 5px;" href="javascript:void(0);">'+
                         '<label style="display: inline-table;width: 45%"></label>'+
                         '<img class="xclose floatRight" valign="middle" onclick="actions.deleteElement(this)" src="/img/x.png">'+
                         '<img valign="middle" class="xclose floatRight" onclick="actions.editElementName(this,false)" src="/img/edit.png">'+
                         '</a></li>',
                 }
             });
             $(this).parent().find('ul').attr('style','display:block;');
         });
	     $("#mainContent").css("width","");
	     $("#mainContent").css("clear","");
	     $("#mainContent").css("margin-left","5px");
	     $("#mainContent").addClass("floatLeft");

	     $("#title_DcRoute").append('<img valign="middle" class="xclose" onclick="actions.editElementName(this,true,\'DcRoute\')" src="/img/plus.png">');
	     $("#title_DcPoint").append('<img valign="middle" class="xclose" onclick="actions.editElementName(this,true,\'DcPoint\')" src="/img/plus.png">');
	     $("#_action_filter ").insertAfter("#DcPointFlow");
	     $("#_action_filter br").remove();
	     $("#buttonSave,#buttonLoadData ").css("float","");
	     $("#buttonSave,#buttonLoadData ").css("margin-top","3px");
	     $("#filterFrequence ").css("height",($(window).height()-$("#menu-wrapper").height()-32) +"px");

	     $('#DcPoint').change(function(){
	 		actions.doLoad(true);
         });
// 	     $('#DcPoint').change();
	     actions.doLoad(true);
// 	     $('#secondary_buttonLoadData').val("Select");
	});

	 var exAfterRenderingDependences = filters.afterRenderingDependences;
     filters.afterRenderingDependences = function (id,sourceModel) {
         exAfterRenderingDependences(id,sourceModel);
         var partials = id.split("_");
         var prefix = partials.length > 1 ? partials[0] + "_" : "";
         var model = partials.length > 1 ? partials[1] : id;
         if (model == "DcPoint") {
             $('#DcPoint').multiselect('rebuild');
             actions.doLoad(true);
//              $('#DcPoint').change();
         }
     }

     actions.updateDcFilter = function(data,forceUpdate) {
    	$.each(data.dataSets, function( index, item ) {
        	var elementId = item.postData.tabTable;
			var currentValue  = $('#'+elementId).val();
			$('#'+elementId).html('');   // clear the existing options
			 $.each(item.dataSet, function( dindex, value ) {
				var option = renderDependenceHtml(elementId,value);
				$('#'+elementId).append(option);
			});
			 if(!forceUpdate) $('#'+elementId).val(currentValue);
			 $('#'+elementId).multiselect('rebuild');
			 if(forceUpdate) $('#'+elementId).change();
		});
     }
         
    actions.editElementName = function(element,isNew,filterName) {
        var newName=prompt("Please input the new name",$(element.parentElement).text().trim());
		if(newName=="" || newName == null) return;
        var liElement = $(element.parentElement.parentElement);
        var editedData = {};
        filterName = filterName?filterName:liElement.attr("filterName");
        var currentValue = isNew?null:liElement.attr("optionValue");
        var item = {NAME 	: newName,
        			ID 		: currentValue
                	};
    	if(filterName=="DcPoint") item.ROUTE_ID = $('#DcRoute').val();
        editedData[filterName] = [item];
        showWaiting();
		var postData	= {
				editedData: editedData,
				{{config("constants.tabTable")}} : filterName,
				DcRoute : $('#DcRoute').val()
		};
		$.ajax({
			url	: this.saveUrl,
			type: "post",
			data: postData,
			success:function(data){
			    actions.updateDcFilter(data,false);
				hideWaiting();
			},
			error: function(data) {
				console.log ( "doSave error");
				hideWaiting();
				if (typeof(actions.loadError) == "function") actions.loadError(data);
			}
		});
 	}

    actions.deleteElement = function(element) {
	    var liElement = $(element.parentElement.parentElement);
    	if(!confirm(liElement.text()+" will be deleted. Do you want to continue?")) return;
        var deleteData = {};
        var filterName = liElement.attr("filterName");
        var currentValue = liElement.attr("optionValue");
        deleteData[filterName] = [currentValue];
        showWaiting();
		var postData	= {
				deleteData: deleteData,
				{{config("constants.tabTable")}} : filterName,
				DcRoute : $('#DcRoute').val()
		};
			$.ajax({
				url	: this.saveUrl,
				type: "post",
				data: postData,
				success:function(data){
				    actions.updateDcFilter(data,currentValue==$('#'+filterName).val());
				    $(element.parentElement).remove();
					hideWaiting();
				},
				error: function(data) {
					console.log ( "Delete error");
					hideWaiting();
					if (typeof(actions.loadError) == "function") actions.loadError(data);
				}
			});
	}

    var oLoadSaveParams = actions.loadSaveParams;
    actions.loadSaveParams 	= function (reLoadParams){
		var params 			= oLoadSaveParams(reLoadParams);
		var mapping 		= {	FLOW 		: { field : 'FLOW_ID' ,eloquent : 'DcPointFlow'},
								ENERGY_UNIT : { field : 'EU_ID' ,eloquent : 'DcPointEu',       },
								TANK 		: { field : 'TANK_ID' ,eloquent : 'DcPointTank',     },
								EQUIPMENT 	: { field : 'EQUIPMENT_ID' ,eloquent : 'DcPointEquipment',},
							};
		var tmpEditedData 	= {	DcPointFlow 		: [],
								DcPointEu 			: [], 
								DcPointTank 		: [], 
								DcPointEquipment 	: [], 
				};
		if(params.editedData!==undefined){
			$.each(params.editedData, function( index, rowDatas ) {
				$.each(rowDatas, function( key, rowData ) {
					rowData[mapping[rowData.POINT_TYPE].field] = rowData.NAME;
					rowData.POINT_ID = $("#DcPoint").val();
					tmpEditedData[mapping[rowData.POINT_TYPE].eloquent].push(rowData);
		        });
	        });
			params.editedData = tmpEditedData;
		}
		var tmpDeleteData 	= {	DcPointFlow 		: [],
								DcPointEu 			: [], 
								DcPointTank 		: [], 
								DcPointEquipment 	: [], 
		};
		if(params.deleteData!==undefined){
			$.each(params.deleteData, function( index, rowDatas ) {
				$.each(rowDatas, function( key, rowData ) {
					tmpDeleteData[mapping[rowData.POINT_TYPE].eloquent].push(rowData.ID);
				});
			});
			params.deleteData = tmpDeleteData;
		}
		return params;
	};

    /* var oIsEditable = actions.isEditable;
	actions.isEditable 	= function (column,rowData,rights){
		var id = rowData['DT_RowId'];
		var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var rs =  isAdding || oIsEditable(column,rowData,rights);
		return rs;
	}; */

	actions['initDeleteObject']  = function (tab,id, rowData) {
    	return {'ID':rowData.ID,'POINT_TYPE':rowData.POINT_TYPE};
    };

    var oGetAddButtonHandler = actions.getAddButtonHandler;
    actions.getAddButtonHandler = function (otable,otab,doMore){
        return function (e){
                        editBox.editRow({data:{}},{CODE:"Select object name"});
                        $("#box_loading").css("display","none");
                };
	};

	var oLoadSuccess = actions.loadSuccess;
	actions.loadSuccess =  function(data){
		actions.editedData = {};
		actions.deleteData = {};
		oLoadSuccess(data);
	}

	var oGetValueTextOfSelect 		= actions.getValueTextOfSelect;
	actions.getValueTextOfSelect	= function(collection,data2,columnName,row){
		var text 	= oGetValueTextOfSelect(collection,data2,columnName);
		if(columnName == "NAME" && text == "&nbsp" &&row.TEXT!==undefined) {
			text	= row.TEXT;
		}
		return text;
	}
</script>
@stop

@section('editBoxParams')
@parent
<script>
	editBox.size	= {	height 	: 130,
						width 	: 740,
					};
</script>
@stop

@section('endDdaptData')
@parent
<script>
	editBox.renderOutputText = function (texts){
		return 	texts.ObjectName ;
	};
	
	editBox.editSelectedObjects	= function(dataStore,resultText){
		var table = $("#table_DcPointFlow").DataTable();
		var fn = oGetAddButtonHandler(table,"DcPointTab",function(addingRow){
            addingRow['POINT_TYPE'] = dataStore.IntObjectType;
            addingRow['NAME'] 		= dataStore.ObjectName;
            addingRow['TEXT'] 		= editBox.buildFilterText();
            return addingRow;
        });
		fn();
	}
</script>
@stop

