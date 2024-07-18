<?php
$currentSubmenu ='/fo/personnel';
$tables = ['Personnel'	=>['name'=>'PERSONNEL']];
$isAction = true;
?>
@extends('core.fo')

@section('funtionName')
PERSONNEL DATA
@stop

@section('adaptData')
@parent
<style type="text/css">
#containerSecondaryContent{
	width: 30%;
	float: right;
}
#tabs_contents{
	width: 70%;
	float: left;
}
</style>
<script>
	actions.loadUrl = "/personnel/load";
	actions.saveUrl = "/personnel/save";
	actions.type = {
					idName:['ID', 'TYPE', 'TITLE'],
					keyField:'DT_RowId',
					saveKeyField : function (model){
						return 'ID';
					},
				};
	actions.getExistRowId = function(value,key){
		if(key=='PersonnelSumDay') return ''+value.TYPE+value.TITLE;
		return value[actions.type.saveKeyField(key)];
	}

	render2rdTable = function(data){
		options = {
				tableOption :{
									searching		: false,
									ordering		: false,
									scrollY			: "400px",
									bInfo			: false,
 									drawCallback 	: drawCallback,
									footerCallback 	: footerCallback
								},
					invisible:['TYPE','TYPE_NAME']
			};
		actions.initTableOption('PersonnelSumDay',data.secondaryData,options,null,actions.createdFirstCellColumn);
	}

	actions.isRefreshFooter	= function(tab,columnName,newValue,type){
		return tab=="PersonnelSumDay";
	};


	osetTableWidth			= actions.setTableWidth;
	actions.setTableWidth = function(tab,tblWdth){
		if(tab=="PersonnelSumDay") return osetTableWidth(tab,tblWdth);
// 		$('#container_'+tab).css('min-width',($(window).width()-50)+'px');
		$('#container_'+tab).css('width',tblWdth+"px");
	}

	
	actions.addingNewRowSuccess = function(data,table,tab,isAddingNewRow){
		if(tab=='Personnel'){
			srcData = data.postData;
			srcData['{{config("constants.tabTable")}}'] = 'PersonnelSumDay';
			$.ajax({
				url: '/personnel/load',
				type: "post",
				data: srcData,
				success:function(data){
					render2rdTable(data);
					console.log ( "success addingNewRowSuccess "+data );
				},
				error: function(data) {
					console.log ( "error addingNewRowSuccess "+data );
				}
			});
		}
	}
	
	
	actions.extraDataSetColumns = {'BA_ID':'TITLE'};
	
	source['TITLE']	={	dependenceColumnName	:	['BA_ID'],
						url						: 	'/personnel/loadsrc'
					};

	drawCallback = function ( settings ) { 
        	var table = $('#table_PersonnelSumDay').dataTable();
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
 
//             groups = api.data();
            groups = api.column(4, {page:'current'}).data();
            
            groups.each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group"><td colspan="2">'+group+'</td></tr>'
                    );
 
                    last = group;
                }
            } );
        };

        footerCallback = function ( row, data3, start, end, display ) {
		      var api = this.api();
	          var last=null;
	          totals = {};
	          groups = api.data();
		      groups.each( function ( group, i ) {
	              if ( typeof(totals[group.TITLE_NAME]) == "undefined") {
	          		totals[group.TITLE_NAME] = 0;
	              }
	              if ( last !== group.TITLE) {
	              	totals[group.TITLE_NAME] += intVal(group.NUMBER);
	                  last = group.TITLE;
	              }
	          } );
		      // Update footer
		      totalsHtml = '<tr><td>TOTAL</td>TOTAL</tr>';
		      for (var key in totals) {
		    	  totalsHtml += '<tr><td>'+key+'</td>'+
			      				'<td colspan="">'+totals[key]+
			      				'</td></tr>';
			  }
	          $( api.columns(1).footer() ).html(totalsHtml); 
		};
        
	superLoadSuccess = actions.loadSuccess;
	actions.loadSuccess =  function(data){
		superLoadSuccess(data);
		render2rdTable(data);
	}
</script>
@stop

@section('secondaryContent')
@parent
<div id="containerSecondaryContent" style="overflow-x:hidden">
	<div id="container_PersonnelSumDay" style="overflow-x:hidden;float: right; padding-top: 1em">
		<table border="0" cellpadding="3" id="table_PersonnelSumDay" class="fixedtable nowrap display">
		<tfoot>
								<tr>
									<td style="text-align:left"></td>
									<td style="text-align:left"></td>
									<td style="text-align:left" colspan="1"></td>
								</tr>
							</tfoot>
		</table>
	</div>
</div>
@stop

