<?php
	$currentSubmenu ='/pd/liftaccdailybalance';
	$tables = ['PdCargo'	=>['name'=>'Data']];
	$isAction = false;
?>

@extends('core.pd')
@section('funtionName')
LIFTING ACCT DAILY BALANCE
@stop

@section('adaptData')
@parent
<script>
	$( document ).ready(function() {
	    console.log( "ready!" );
	    var onChangeFunction = function() {
		    if($('#PdLiftingAccount option').size()>0 ) actions.doLoad(true);
	    };
	    
	    $( "#PdLiftingAccount" ).change(onChangeFunction);
// 		actions.doLoad(true);
	});

	actions.loadUrl = "/liftaccdailybalance/load";
 	actions.saveUrl = "/liftaccmonthlyadjust/save";//for insert monthly balance
	actions.reloadAfterSave	= true;
 	
	actions.type = {
			idName:['ID'],
			keyField:'ID',
			saveKeyField : function (model){
				return 'ID';
				},
			};

// 	actions.renderFirsColumn = null;
	actions.getTableOption	= function(data){
		var monthlyData		= data.monthlyData;
		return {tableOption :{
								searching			: true,
								sort				: false,
								disableLeftFixer	: true,
								drawCallback: function ( settings ) {
						            var api = this.api();
						            var rowDatas	= api.data();
						            var rows = api.rows( {page:'current'} ).nodes();
						            var last=null;
						 
//						             groups = api.data();
						            groups = api.column(2, {page:'current'}).data();
						            var insertValues	= [];
						            groups.each( function ( group, i ) {
						            	var xdate 	= moment.utc(group,configuration.time.DATETIME_FORMAT_UTC);
						                if ( !xdate.isSame( last, 'month')) {
								            var year 		= xdate.year();
								            var month 		= xdate.month();
							                var dateString 	= moment.monthsShort()[month]+" "+year;
							                var monthKey	= xdate.format('YYYY-MM');
		    								var total 		= "";
							                if(typeof monthlyData == "object"){
							                	var result = $.grep(monthlyData, function(e){ 
												               	 return e.MONTH_KEY == monthKey;
												             });
			    								if (result.length >0) {
			    									total 		= 0;
			    									$.each(result, function( index, accountData ) {
			    										var volume	= parseFloat(accountData.BAL_VOL);
			    										volume	= isNaN(volume)?0:volume;
			    										total	+= volume;
			    										totalText = configuration.number.DECIMAL_MARK=='comma'?volume.toLocaleString('de-DE'):volume.toLocaleString("en-US"); 
			    										$(rows).eq( i ).before(
			    						                        '<tr class="group"><td colspan="7"><b>'+(index==0?dateString:"")+
			    						                        '</b></td><td class="cellnumber"> '+accountData.ADJUST_NAME+
			    						                        '</td><td class="cellnumber">'+totalText+'</td></tr>'
			    						                );
		    							            });
				    								if (result.length >1) {
				    									totalText = configuration.number.DECIMAL_MARK=='comma'?total.toLocaleString('de-DE'):total.toLocaleString("en-US"); 
				    									$(rows).eq( i ).before(
			    						                        '<tr class="group"><td colspan="8">'+
			    						                        '</td><td class="cellnumber" style="background-color: #8edee2;"><b>'+totalText+'</b></td></tr>'
			    						                );
				    								}
								                    last = xdate;
								                    return;
			    								}
							                }

							                if(i>0) total = rowDatas[i-1].cal_qty;
						                	var rowTr			= $('<tr class="group"></tr>');
							                $('<td colspan="8"><b>'+dateString+'</td>').appendTo(rowTr);
							                if(total!=""){
								                var insertObject	= {	BALANCE_MONTH	: xdate.format(configuration.time.DATETIME_FORMAT_UTC),
												                		BAL_VOL			: total,
												                		dateString		: dateString};
							                	insertValues.push(insertObject);
	
								                var totalTd			= $('<td class="cellnumber" style="background-color: #8edee2;"></td>');
								                var insertButton 	= $('<button class="floatLeft" style="">Generate L.A.Bal.</button>');
								                insertButton.click(function(){
								                	actions.insertMonthlyBalance([insertObject]);
									            });
								                insertButton.appendTo(totalTd);
								                totalText = configuration.number.DECIMAL_MARK=='comma'?total.toLocaleString('de-DE'):total.toLocaleString("en-US"); 
								                $('<b>'+totalText+'</b>').appendTo(totalTd);
								                totalTd.appendTo(rowTr);
								                
							                }
						                    $(rows).eq( i ).before(rowTr);

						                    last = xdate;
						                }
						            } );
							        var table = $('#table_PdCargo').DataTable();
							        actions.addClass2Header(table);

							        if(insertValues.length>0){
							        	var insertButton 	= $('<button class="" style="margin-right:5px">Generate L.A.Bal.</button>');
						                insertButton.click(function(){
						                	actions.insertMonthlyBalance(insertValues);
							            });
						                insertButton.prependTo($('.dataTables_scrollHeadInner thead th.cal_qty'));
							        }
						        },
							},
				invisible:[],
            	resetTableHtml : function(tabName) { return true},
        };
		
	}

	actions.insertMonthlyBalance	= function(values){
		if(values.length>0){
			var liftingAccount	= $("#PdLiftingAccount option:selected").text();
			var texts			= "";
			$.each(values, function( index, value ) {
		        texts						+= liftingAccount+"  "+value.dateString+"   "+value.BAL_VOL+"\n";
		        value.LIFTING_ACCOUNT_ID 	= $("#PdLiftingAccount option:selected").val();
		        value.ADJUST_CODE 			= 2;
		        value.COMMENT 				= "Generate new Monthly Balance";
		        value.ID					= "NEW_RECORD_DT_RowId"+index++;
		        value.isAdding				= true;
		        
		        
		   	});
	    	if(confirm("Generate new Monthly Balance?\n"+texts)){
				actions.editedData.PdLiftingAccountMthData = values;
		    	actions.doSave(true);
	    	}
		}
		else alert("there is no monthly balance to insert");
	}
</script>
@stop

