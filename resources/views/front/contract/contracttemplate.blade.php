<?php
	$currentSubmenu ='/pd/contracttemplate';
	$tables = ['PdContractTemplate'	=>['name'=>'Load']];
	$detailTableTab = 'PdContractTemplateAttribute';
	$attributeTableTab = 'PdCodeContractAttribute';
	
	$isAction = true;
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	actions.loadUrl = "/contracttemplate/load";
	actions.saveUrl = "/contracttemplate/save";
	actions['idNameOfDetail'] = ['CONTRACT_TEMPLATE','ATTRIBUTE'];
	var contractData;
	templateId = 0;

	editBox['addAttribute'] = function(addingRow,selectRow){
		addingRow['ATTRIBUTE']	= selectRow.ID;
		addingRow['CONTRACT_TEMPLATE'] 	= templateId;
		return addingRow;
	};
	
	editBox.initExtraPostData = function (id,rowData){
									templateId = id;
									contractData = 	{
									 		id			: id,
									 		tabTable	: '{{$detailTableTab}}',
									};
								 	return contractData;
								};

 	actions['initDeleteObject']  = function (tab,id, rowData) {
		 if(tab=='{{$detailTableTab}}') return {'ID':id, CONTRACT_ID : rowData.CONTRACT_ID_INDEX};
		return {'ID':id};
	};

    actions.enableUpdateView = function(tab,postData){
		return tab=='PdContract';
	};

	var oGetTableOption = actions.getTableOption;
	actions.getTableOption = function (data,tab) {
		if (tab == '{{$detailTableTab}}') {
			return {
	 			tableOption :	{
						autoWidth	: false,
						scrollX		: false,
						searching	: false,
						scrollY		: "180px",
				},
                resetTableHtml : function(tabName) { return true},
			};
		}
		return oGetTableOption(data,tab);
	};

	editBox['buildExtraSaveDetailData'] = function(editId,saveUrl) {
  		return {
  			templateId	: contractData.templateId,
	 	};
    };
	
</script>
@stop
<style>
#editBoxContentview .dataTables_scrollBody {
	height: 249px!important;
}
#editBoxContentview .bottom, .dataTables_scrollFoot {
	display: none;
}
</style>
@section('editBoxParams')
@parent
<script>
	editBox.loadUrl = "/contracttemplateattribute/load";
	editBox.saveUrl = '/contracttemplateattribute/save';
	editBox.size = {height:400, width:600};
</script>
@stop
