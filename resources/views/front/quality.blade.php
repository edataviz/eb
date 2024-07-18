<?php
	$currentSubmenu ='/dc/quality';
	$tables = ['QltyData'	=>['name'=>'QUALITY DATA']];
 	$active = 0;
	$isAction = true;
	$detailTableTab	= "QltyDataDetail";
 ?>

@extends('core.pm')
@section('funtionName')
QUALITY DATA CAPTURE
@stop

@section('adaptData')
@parent
<script>
	actions.loadUrl 		= "/quality/load";
	actions.saveUrl			= "/quality/save";
	actions.historyUrl 		= "/quality/history";
	actions.type = {
					idName:['ID'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'ID';
						},
					};
	actions.extraDataSetColumns = {'SRC_ID':'SRC_TYPE'};
	source['SRC_TYPE']={dependenceColumnName	:	['SRC_ID'],
						url						: 	'/quality/loadsrc'
						};
	source['FLOW_PHASE']={dependenceColumnName	:	['SRC_ID'],
			};
	
	source.initRequest = function(tab,columnName,newValue,collection){
		postData = actions.loadedData[tab];
		var srcType = null;
		var result = $.grep(collection, function(e){ 
          	 return e['ID'] == newValue||e['id'] == newValue;
           });
		if (result.length > 0) {
			srcType = typeof result[0]['CODE'] != "undefined"?result[0]['CODE'] :result[0]['code'];
		}
		else return null;
		srcData = {name : columnName,
					value : newValue,
					srcType : srcType,
					Facility : postData['Facility']};
		return srcData;
	}
	addingOptions.keepColumns = ['SAMPLE_DATE','TEST_DATE','EFFECTIVE_DATE','SAMPLE_TYPE','SRC_ID','SRC_TYPE'];
	var renderFirsColumn = actions.renderFirsColumn;
	actions.renderFirsColumn  = function ( data, type, rowData ) {
		var html = renderFirsColumn(data, type, rowData );
		var id = rowData['DT_RowId'];
		isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		if(!isAdding){
			html += '<a id="edit_row_'+id+'" class="actionLink">Edit</a>';
		}
		return html;
	};
	actions.enableUpdateView = function(tab,postData){
		return tab=='QltyData';
	};
	actions.isDisableAddingButton	= function (tab,table) {
		return tab=="oil"||tab=="gas";
	};
	actions.loadCustomObjectName 	= function(column,ecollection,rowData){
		if(column=="SRC_ID"){
			var flowPhaseField 	= rowData.FLOW_PHASE!==undefined?"FLOW_PHASE":"flow_phase";
			if(ecollection!=null&&ecollection.length>0&&ecollection[0]!==undefined&&(ecollection[0][flowPhaseField]!==undefined)){
				var flowPhase 	 	= ecollection[0][flowPhaseField];
				if(rowData[flowPhaseField]!=""){
					ecollection = $.grep(ecollection, function(e){
						return e[flowPhaseField] == rowData[flowPhaseField];
					});
				}
			}
		}
 		return ecollection;
	};
	actions.localDominoColumns = function(columnName,newValue,dependenceColumnNames,rowData,tab){
		if(columnName=="FLOW_PHASE") {
			var sourceColumn	= 'SRC_TYPE';
			var ofId			= rowData[sourceColumn];
			var data = {
                    dataSet	: {SRC_ID	:	{	data		: actions.extraDataSet[sourceColumn][ofId],
                            ofId		: ofId,
                            sourceColumn: sourceColumn}}
                };
			actions.dominoColumnSuccess(data,dependenceColumnNames,rowData,tab);
		}
	}
</script>
@stop


@section('editBoxParams')
@parent
<script>
	editBox.fields = {	gas		:	'gas',
						oil		:	'oil'
					};
	
	editBox.loadUrl = "/quality/load";
	editBox.saveUrl = '/quality/edit/saving';
	editBox.size = {height:480, width:1040};
	actions.generateTableFoot  = function ( tab, properties, dataSet ) {
		$("#table_"+tab+"_containerdiv").html("<table id='table_"+tab+"' class='fixedtable nowrap display'>"); 
		if(typeof properties == 'object'){
			var tfoot = $('<tfoot></tfoot>'); 
			var foot = $('<tr></tr>'); 
			foot.appendTo(tfoot);
			var sum = {};
			dataSet.forEach(function(row){
				properties.forEach(function(p){
					if(p.INPUT_TYPE==2){
						sum[p.NAME] == undefined && (sum[p.NAME] = 0);
						sum[p.NAME] += isNaN(row[p.NAME]) ? 0: Number(row[p.NAME]);
					}
				})
			});
			for (var i = 0; i < properties.length; i++) {
				if(i==0) footColumn	= $('<td style="text-align:left">Sum:</td>');
				else footColumn	= $('<td style="text-align:right">'+(properties[i].INPUT_TYPE==2?sum[properties[i].NAME].toFixed(2):'')+'</td>');
			    foot.append(footColumn);
			}
			tfoot.appendTo("#table_"+tab); 
		}
	};
	actions.isRefreshFooter	= function(tab,columnName,newValue,type){
		return tab=="oil"||tab=="gas";
	};
	var oInitExtraPostData		= editBox.initExtraPostData;
	editBox.initExtraPostData 	= function (id,rowData){
		var params 		= oInitExtraPostData(id,rowData);
		params.tabTable	= '{{$detailTableTab}}';
 		return 	params;
 	};
 	actions.getRenderFirsColumnFn = function (tab) {
        if(tab=='oil'||tab=='gas') return actions.renderFirsEditColumn;
		return actions.renderFirsColumn;
	}
 	var oGetTableOption = actions.getTableOption;
	actions.getTableOption = function (data,tab) {
		if (tab == 'oil') {
			return {
				tableOption :{	searching	: false,
					autoWidth	: false,
					bInfo 		: false,
					"order"		: [],
					ordering	: false,
					scrollY		: "200px",
					footerCallback : function ( row, data3, start, end, display ) {
										var api = this.api();
							            columns = [1];
							            editBox.renderSumRow(api,columns);
						       		}
				}
			};
		}
		else if (tab == 'gas') {
			return {
					tableOption :	{
							searching	: false,
							autoWidth	: false,
							bInfo 		: false,
							"order"		: [],
							ordering	: false,
							scrollY		: "200px",
							footerCallback : function ( row, data3, start, end, display ) {
										            var api = this.api();
										            columns = [1,2];
										            editBox.renderSumRow(api,columns);
						        			}
					}
			};
		}
		return oGetTableOption(data,tab);
	};

	var loadSuccessOri = actions.loadSuccess;
	actions.loadSuccess = function(data){
		loadSuccessOri(data);
		
	}
	
	editBox.editGroupSuccess = function(data,id){
		var subData;
		$("#table_oil_containerdiv").html("<table id='table_oil' class='fixedtable nowrap display secondaryTable'>"); 
		$("#table_gas_containerdiv").html("<table id='table_gas' class='fixedtable nowrap display secondaryTable'>");
		//$("#table_oil_containerdiv").css("width","0"); 
		//$("#table_gas_containerdiv").css("width","0"); 
		if(typeof data['NONE_MOLE_FACTION'] != "undefined"){
			$("#table_oil_containerdiv").css("width", data['MOLE_FACTION'] ? (1000-10)/2 : 1000); 
			!data['MOLE_FACTION'] && $("#table_gas_containerdiv").css("width", 0);
			subData = {
					dataSet 		: data['NONE_MOLE_FACTION']['dataSet'],
					locked			: data.lock,
					postData		: {tabTable	: 'oil'},
					properties		: data['NONE_MOLE_FACTION']['properties'],
					rights			: data.rights,
					secondaryData	: data.secondaryData,
			};
    		actions.generateTableFoot('oil',subData.properties, subData.dataSet);
			actions.loadSuccess(subData);
		}
		
		if(typeof data['MOLE_FACTION'] != "undefined"){
			//$("#table_gas_containerdiv").css("width","435px"); 
			$("#table_gas_containerdiv").css("width", data['NONE_MOLE_FACTION'] ? (1000-10)/2 : 1000); 
			!data['NONE_MOLE_FACTION'] && $("#table_oil_containerdiv").css("width", 0);
			subData = {
					dataSet 		: data['MOLE_FACTION']['dataSet'],
					locked			: data.lock,
					postData		: {tabTable	: 'gas'},
					properties		: data['MOLE_FACTION']['properties'],
					rights			: data.rights,
					secondaryData	: data.secondaryData,
			};
    		actions.generateTableFoot('gas',subData.properties, subData.dataSet);
			actions.loadSuccess(subData);
		}
	}
	editBox['saveFloatDialogSucess'] = function(data,saveUrl){
		editBox.editGroupSuccess(data,id);
	}
	</script>
	
@stop

@section('editBoxContentview')
@parent
<style>
#editBoxContentview .dataTables_scrollBody {
	height: 340px!important;
}
#editBoxContentview .bottom, .dataTables_scrollFoot {
	/*display: none;*/
}
#editBoxContentview .dataTables_wrapper table {
    table-layout: initial!important;
}
#editBoxContentview .dataTables_filter, #editBoxContentview .dataTables_info {
	display: none;
}
</style>
<table border='0' cellpadding='0' style='width:100%;height:100%'>
			<tr>
				<td valign='top'>
					<div id="table_oil_containerdiv" class="secondaryTable" style='height:100%;overflow:auto'>
					</div>
				</td>
				<td valign='top' width="10">
					<div class="paddingOfTable" style='width:10px;overflow:auto'>
					</div>
				</td>
				<td valign='top'>
					<div id="table_gas_containerdiv" class="secondaryTable" style='height:100%;overflow:auto'>
					</div>
				</td>
			</tr>
</table>
@stop