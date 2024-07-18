<?php
use App\Models\CfgDataSource;

	$lang			= session()->get('locale', "en");
	if (isset($tables)) {
		foreach($tables as $index => $table ) {
			$model = 'App\\Models\\' .$index;
			$tableName = $model::getTableName();
			$dcDisable = 0;
			if(isset($table['DISABLE_DC']))
				$dcDisable = $table['DISABLE_DC'];
			else {
				$tmp = CfgDataSource::where(['NAME'=>$tableName])->select(['DISABLE_DC'])->first();
				if($tmp){
					$dcDisable = $tmp["DISABLE_DC"];
				}
			}
			if(!$dcDisable){
				$tmp = \DB::select("select 1 from user_user_role a, user_role_table b where a.user_id=".\Auth::id()." and a.role_id=b.role_id and b.TABLE_NAME='$tableName' and b.ACCESS='0'");
				if(count($tmp)){
					$dcDisable = 1;
				}
			}
			if($dcDisable == 1){
				unset($tables[$index]);
				continue;
			}

			if (Lang::has("front/site.".$table["name"], $lang)) {
				$table["name"] = trans("front/site.".$table["name"]);
			}
			$table["TableName"] = $tableName;
			$tables[$index] = $table;
		}
	}
	else{
		$tables = [];
	}
	
	$enableCopySourceColumn	= env("ENABLE_COPY_SOURCE_COLUMN",false);
?>

@if(isset($tables))
	<div id="tabs">
		<ul id="ebTabHeader">
			@foreach($tables as $key => $table )
			<li id="{{$key}}"><a href="#tabs-{{$key}}"><font size="2">{{$table['name']}}</font></a></li>
			@endforeach
			<div id="more_actions"></div>
		</ul>
		<div id="tabs_contents">
			@foreach($tables as $key => $table )
			<div id="tabs-{{$key}}">
				<div id="container_{{$key}}" style="min-width: 600px;" class="clearfix">
					<table border="0" cellpadding="3" id="table_{{$key}}"
						class="fixedtable nowrap display" style="width: inherit;position:relative;min-width: 600px;">
					</table>
				</div>
			</div>
			@endforeach
		</div>
		@yield('secondaryContent')
	</div>
	@section('script') 
	@parent
		<script>
				$(document).ready(function () {
					$("#tabs").tabs({
						active	:{{$active}},
						create	: function(event,ui){
						        actions.loadNeighbor(event, ui);
					    },
						activate: function(event, ui) {
					        actions.loadNeighbor(event, ui);
					    }
					});
				});
		</script>
	@stop 
@endif
@yield('extraContent')

@section('adaptData')
<script>
	actions.modelTabs 	= <?php echo json_encode($tables); ?>;
	
	actions.initData = function(){
		var activeTabID = getActiveTabID();
		var tab = {'{{config("constants.tabTable")}}':activeTabID}
		return tab;
	}

	actions.loadSuccess =  function(data){
		$('#buttonLoadData').attr('value', 'Refresh');
		postData = data.postData;
		var tab = postData['{{config("constants.tabTable")}}'];
		var removeOldData = true;
		if(typeof data.removeOldData != "undefined" ) removeOldData = data.removeOldData;
		if(removeOldData){
			delete actions.editedData[tab];
			delete actions.deleteData[tab];
		}
		else if(typeof actions.mergeLoadData == "function") actions.mergeLoadData(tab,data);
		
		if(data!=null&&data.hasOwnProperty('objectIds')&&$("#"+tab).length>0){
			jQuery.extend(actions.objectIds, data.objectIds);
			jQuery.extend(actions.editedData, data.objectIds);
		}
		actions.loadedData[tab] = postData;
		options = actions.getTableOption(data,tab);
		var render1stCoumnFn = actions.getRenderFirsColumnFn(tab);
		var tbl = actions.initTableOption(tab,data,options,render1stCoumnFn,actions.createdFirstCellColumn);

		tbl.originData		= data;
		actions.afterDataTable(tbl,tab);
		if(actions.enableUpdateView(tab,postData)) actions.updateView(postData);

		var disableLeftFixer = typeof(options["tableOption"]) !== "undefined" && 
								typeof(options["tableOption"]["disableLeftFixer"]) !== "undefined" &&
								options["tableOption"]["disableLeftFixer"] == true;

		if(!disableLeftFixer){
	 		var tbbody = $('#table_'+tab);
	 		if(data.dataSet!=null&&(data.dataSet.length>0)) tbbody.tableHeadFixer({"left" : 1,head: false,});
	
			var hdt;	
	 		var tblh = $('#container_'+tab ).find('table').eq(0);
		  	hdt = $(tblh).find('th').eq(0);
	 		var tblHeader = hdt.parent().parent();
	 		tblHeader.tableHeadFixer({"left" : 1,head: false,});
	 		var tblScroll = $('#container_'+tab ).find('div.dataTables_scrollBody').eq(0);
	 		tblScroll.on("scroll", function(e) {
	  			hdt.css({'left': $(this).scrollLeft()});
	 		});
		};

		if(actions.tableIsDragable(tab)){
			$('#table_'+tab +" tbody").sortable();
	 		$('#table_'+tab +" tbody").disableSelection();
		}

		var msg = 'Complete\n';
		if(data.hasOwnProperty('lockeds')){
			msg+=JSON.stringify(data.lockeds);
// 			alert();
		}

	}

    actions.getTableOption = function(data,tab){
        return {tableOption :{searching: true},
            invisible:[],
            resetTableHtml : function(tabName) { return true}
        };
    };

	actions.shouldLoad = function(data){
		var activeTabID = getActiveTabID();
		var postData = actions.loadedData[activeTabID];
		var noData = jQuery.isEmptyObject(postData);
		var dataNotMatching = false;
		if (!noData&&actions.loadPostParams) {
			for (var key in actions.loadPostParams) {
				if($('.'+key).css('display') != 'none'){
					dataNotMatching = actions.loadPostParams[key]!=postData[key];
				} 
				if(dataNotMatching) break;
			}
		}
		
		var shouldLoad = actions.readyToLoad&&(noData||dataNotMatching);
		return shouldLoad;
	};

	actions.reloadAfterSave	= false;
	
	actions.saveSuccess =  function(data,noDelete){
		var postData = data.postData;
		if(typeof data.dataSets != "undefined") {
			$.each(data.dataSets, function( index, value) {
				actions.loadSuccess(value);
			});
		}
		else if(!jQuery.isEmptyObject(data.updatedData)){
			for (var key in data.updatedData) {
				if($('#table_'+key).children().length>0){
					table = $('#table_'+key).DataTable();
					$.each(data.updatedData[key], function( index, value) {
						if(actions.isShownOf(value,postData)) {
							row = table.row( '#'+actions.getExistRowId(value,key));
							var tdata = row.data();
							if( typeof(tdata) !== "undefined" && tdata !== null ){
								for (var pkey in value) {
									if(tdata.hasOwnProperty(pkey)){
										tdata[pkey] = value[pkey];
									}
								}
 								row.data(tdata).draw();
 								var otd =  null;
								$.each($(row.node()).find('td'), function( index, td) {
						        	$(td).css('color', '');
						        	otd = td;
						        });
								actions.createdFirstCellColumnByTable(table,tdata,otd,key);
							}
							else{
								value['DT_RowId'] = actions.getExistRowId(value,key);
								table.row.add(value).draw( false );
							}
						}
			        });
					if(typeof(noDelete) === "undefined" || !noDelete ) actions.afterGotSavedData(data,table,key);
				}
				if(typeof(noDelete) === "undefined" || !noDelete ) delete actions.editedData[key];
			}
		}
		else if(typeof(postData) !== "undefined" && (postData.hasOwnProperty('deleteData'))){
			for (var key in postData.deleteData) {
				if(typeof(noDelete) === "undefined" || !noDelete ) {
					table = $('#table_'+key).DataTable();
					actions.afterGotSavedData(data,table,key);
					delete actions.deleteData[key];
				}
			}
		}
		actions.showMessage('Complete\n',data);
		var msg = 'Complete\n';
		if(actions.reloadAfterSave) actions.doLoad(true);
 	};
 	
	actions.showMessage  = function(msg,data){
		if(data.hasOwnProperty('lockeds')){
			msg=JSON.stringify(data.lockeds);
		}
		else if(data.hasOwnProperty('resultTransaction')&&data.resultTransaction.lockeds!==undefined){
			msg=JSON.stringify(data.resultTransaction.lockeds);
		}
		alert(msg);
	};

//  	actions.delay = true;
	var saveBtnPos;
	actions.preEditableShow  = function(){
 		/* $('#buttonSave').attr("disabled", true);
 		$('#buttonSave').prop('disabled', true); */
 		 $('.buttonSave').each(function( index, buttonElement) {
	 		var saveBtnPos      = $.extend({
	 	         width:    $(buttonElement).outerWidth(),
	 	         height:   $(buttonElement).outerHeight()
	 	      }, $(buttonElement).position());
	 		$('<div>', {
	            "class"	: "btnoverlay",
	            css:   {
	               position:         'absolute',
	               top:              saveBtnPos.top,
	               left:             saveBtnPos.left,
	               width:            saveBtnPos.width,
	               height:           saveBtnPos.height,
	               backgroundColor:  '#000',
	               opacity:          0.0
	            }
	        })
	        .click(function(e){
	        	setTimeout(function(){
	        		$(buttonElement).trigger("click");
	        	},600);
	    	})
	        .insertAfter($(buttonElement));
		});
	};
	
	actions.preEditableHiden  = function(){
 		$('.btnoverlay').remove();
	};

	var originAfterDataTable	=  actions.afterDataTable;
	actions.afterDataTable = function (table,tab){
		originAfterDataTable(table,tab);
		var data = table.data();
		var text = "";
		var first = 0;
		var preText = "";
		for (var i = 0; i < data.length; i++) {
			
			var row 	= data[i];
			if(row.RECORD_STATUS===undefined&&row.record_status===undefined) break;
			preText = text;
			switch(row.RECORD_STATUS){
			case "P":
				text = 'Data: Submited';
				break;
			case "V":
				text = 'Data: Validated';
				break;
			case "A":
				text = 'Data: Released';
				break;
			default:
				continue;
// 				text = 'NONE';
// 				break;
			}
			if(i>0&&preText!=""&&text!=preText){
				text = 'Data: Mixed';
				break;
			}
		}
// 		$("#status_"+tab).html(text);
		$("#status_"+tab).addClass("DataStatus");
		if(text!="" && text != 'NONE'){
			var recordStatus	= $("<div>");
			recordStatus.text(text);
			recordStatus.addClass("RecordStatus floatRight");
			recordStatus.appendTo($("#status_"+tab));
		}

		if(table.originData!==undefined && table.originData.tableLock!==undefined && table.originData.tableLock!=null){
			var lockedStatus	= $("<div>");
			lockedStatus.text(" ");
			lockedStatus.addClass("LockStatus floatRight");
			lockedStatus.appendTo($("#status_"+tab));
		}
	};

	var originValidating	= actions.validating;
	actions.validating = function (reLoadParams){
		if(!originValidating(reLoadParams)) return false;
		var validated = true;
		var table;
		var tabName;
		var tableData;
		var properties;
		var rowData;
		var objectRules;
		var basicRules;
		var violence	= false;
		var message		= '';
		var nameField	= "NAME";
		var isModified	= false;

		for (var tab in actions.modelTabs) {
			if ( $.fn.DataTable.isDataTable( '#table_'+tab) ) {
				table 		= $('#table_'+tab).DataTable();
				tableData	= table.data();
				if(tableData.length>0){
					properties	= table.settings()[0].aoColumns;
					for (var j = 0; j < tableData.length; j++) {
						rowData			= tableData[j];
// 						isModified		= actions.isEntryModified(rowData,tab);
// 						if(!isModified) break;
						for (var k = 0; k < properties.length; k++) {
							property		= properties[k];
							objectRules		= actions.getObjectRules(property,rowData);
							basicRules		= actions.getBasicRules(property,objectRules);
							if((basicRules.IS_MANDATORY=="true"||basicRules.IS_MANDATORY==true) 
									&& (rowData[property.data] == null || (typeof rowData[property.data] == "string" && rowData[property.data].trim() == ""))){
								violence 	= true;
								tabName		= actions.modelTabs[tab]!==undefined?actions.modelTabs[tab].name:"";
								nameField	= actions.modelTabs[tab]!==undefined?actions.modelTabs[tab].TableName:"NAME";
								message		= "Please input "+property.title+" value for "+rowData[nameField]+" of "+tabName;
								setTimeout(function(){
									$('#table_'+tab+' tr#'+rowData['DT_RowId'] + ' td.'+property.data).editable('show');
					        	},700);
// 									$(this).editable('show');
								break;
							}
						}
						if(violence) break;
					}
				}
		}
		
			
			if(violence) break;
		}
		if(message!="") alert(message);
		return !violence;
	}

	actions.isEntryModified  = function(rowData,tab){
    	if (!(tab in actions.editedData)) return false;
    	var result = $.grep(actions.editedData[tab], function(e){ 
						               	 return e[actions.type.keyField] == rowData[actions.type.keyField];
						                });
    	return result.length > 0;
	};

	var oLoadParams =actions.loadParams;
	actions.loadParams 			=  function (reLoadParams){
		var params 	= oLoadParams(reLoadParams);
		if(params!==undefined && params!==null&& !$("#disable_date_end").is(":checked"))
			params.date_end = params.date_begin;
		return params;
	}
</script>
@stop

@section('endDdaptData')
@parent
<script>
	actions.enableCopySourceColumn 	= <?php echo json_encode($enableCopySourceColumn); ?>;
	if(!actions.enableCopySourceColumn && typeof addingOptions == "object") addingOptions.keepColumns	= [];
</script>
@stop
