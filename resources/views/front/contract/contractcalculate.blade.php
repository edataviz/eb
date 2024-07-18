<?php
	$currentSubmenu ='/pd/contractcalculate';
	$tables = ['PdContractQtyFormula'	=>['name'=>'Load']];
	$detailTableTab = 'PdContractYear';
	$isAction = true;
	
?>

@extends('core.contract')

@section('adaptData')
@parent
<script>
	$( document ).ready(function() {
	    console.log( "ready!" );
	    var onChangeFunction = function() {
		    if($('#PdContract option').size()>0 ) actions.doLoad(true);
	    };
	    
	    $( "#PdContract" ).change(onChangeFunction);
     	actions.doLoad(true);
	});

	actions.loadUrl = "/contractcalculate/load";
	actions.saveUrl = "/contractcalculate/save";
	actions.type = {
			idName		: function (tab){
							if(tab=='PdContractData') return ['CONTRACT_ID_INDEX', 'ATTRIBUTE_ID_INDEX'];
							return ['ID'];
						},
			keyField:'ID',
			saveKeyField : function (model){
					return 'ID';
				},
			};

	actions.getTableOption	= function(data){
		return {tableOption :	{
									emptyTable			: true,
								},
				invisible:[],
            resetTableHtml : function(tabName) { return true},
        };
		
	}
	actions.renderFirsColumn = actions.defaultRenderFirsColumn;

	actions.isDisableAddingButton	= function (tab,table) {
		return "Add year";
	};

	actions.getAddButtonHandler = function (otable,otab){
                 return function (e){
                                 var dialogOptions = {
                                                 height: 100,
                                                 width: 400,
                                                 position:  {my: 'left+80 bottom-80',at: "left bottom"},
                                                 modal: true,
                                                 of: $('#toolbar_PdContractQtyFormula'),
                                                 title: 'input',
                                         };
                                 $("#floatMoreBox").dialog(dialogOptions);
                         };
	 };
	
	function addYear(){
				showWaiting();
				$("#floatMoreBox").dialog('close');

				params		= actions.loadParams(true);
	            postData  	= {PdContract : $('#PdContract').val(),
	            				year : $('#year_monitoring').val()};
				jQuery.extend(postData, params);
	            $.ajax({
					url: '/contractcalculate/addyear',
					type: "post",
					data: postData,
					success:function(data){
						hideWaiting();
						console.log ( "addyear  success  ");
						alert("add year  success  "/* +JSON.stringify(data) */);
						actions.loadSuccess(data);
					},
					error: function(data) {
						hideWaiting();
						console.log ( "add year error "+JSON.stringify(data));
						alert("addyear  error  ");
						
					}
				});

	}
	
</script>
@stop

@section('floatMoreBoxContent')
    <table id="table_PdContractYear" border='0' style='width:100%;height:50px;' cellpadding='5' cellspacing='0'>
        <tr class='row_activity' style='cursor:pointer' >
            <td>Year</td>
            <td>
                <input id="year_monitoring" class="" type="text" value="" name="year_monitoring" >
            </td>
            <td>
                <input style="width:100px;font-size:10pt;" type="button" onClick="addYear()" value="Save" />
            </td>
        </tr>
    </table>
@stop
