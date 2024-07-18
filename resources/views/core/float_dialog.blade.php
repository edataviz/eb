<?php
	if (!isset($floatContents)) $floatContents = ['editBoxContentview'/* ,'historyContent' */];
 ?>
<script>
	var floatContents = <?php echo json_encode($floatContents); ?>;
	if(typeof(editBox) == "undefined"){
		editBox = {
					fields 			: [],
					enableRefresh	:false,
					hidenFields 	: [],
					size			: {	height : 350,
											width : 900,
										},
					isNotSaveGotData	: function (url,viewId){
				 		return true;
				 	},
				 	gotData			: false,
				};

		editBox.closeEditWindow = function(close) {
			if(close) $('#floatBox').dialog('close');
		};
		
		editBox.initExtraPostData = function (id,rowData,url){
		 		return 	{id:id};
		 }

		editBox.getSaveDetailUrl = function (url,editId,viewId){
	 		return 	editBox.saveUrl;
	 	}
	 	
		editBox['getSaveButton'] = function (id){
			return $("<a id ='"+id+"' class='savebtn' href='#' style='right: 60px;display:block;position: absolute;'>Save</a>")
			.button({/* icons:{primary: "ui-icon-plus"}, */text: true});
	 	};

        editBox.buildActionButtons = function (editId,saveUrl){
            return {};
        }

        editBox.defaultCloseDialogListener = function(event) {
            $.each(editBox.fields, function( index, value ) {
                delete actions.editedData[value];
            });
            if(editBox.enableRefresh) {
                actions.doLoad(true);
                editBox.enableRefresh = false;
            }
        };

		editBox.showDialog = function (option,success,error){
			title 		= option.title;
			postData 	= option.postData;
			url 		= option.url;
			viewId 		= option.viewId;
			editId		= postData.id;
			var dSize	= typeof option.size=='object'?option.size: editBox.size;

            var saveUrl = editBox.getSaveDetailUrl(url,editId,viewId);
            var buttons = editBox.buildActionButtons(editId,saveUrl);
			/* buttons["Cancel"] = function(){
				$("#floatBox").dialog("close");
			}; */

            var closeEvent = typeof editBox.getCloseDialogListener == "function" ? editBox.getCloseDialogListener(url,editId,viewId) : editBox.defaultCloseDialogListener;

			var dialogOpenFunction	= typeof editBox.dialogOpenFunction=='function'?editBox.dialogOpenFunction: function( event, ui ) {};
			var dialogOptions = {
						editId	: editId,
						height	: dSize.height+30,
						width	: dSize.width,
						position: { my: 'top', at: 'top+150' },
						modal	: true,
						title	: title,
						close	: closeEvent,
						buttons	: buttons,
						open	: dialogOpenFunction,
						create	: function() {

						}
					};
			$("#floatBox").dialog(dialogOptions);
			$("#box_loading").html("Loading...");
			$("#box_loading").css("display","block");
			$("#savebtn").css("display","none");

			$.each(floatContents, function( index, value ) {
				$("#"+value).css("display","none");
			 });

			$("#"+viewId).css("display","block");
			if (typeof(editBox.preSendingRequest) == "function") {
				editBox.preSendingRequest(viewId);
			}

			if(typeof(url) != "undefined" && url!=null && url!=""){
				successFn = function(data){
					if(typeof editBox.gotData != "object") editBox.gotData = {};
					editBox.gotData[viewId]	= data;
					$("#history_container").css("display","block");
					$("#savebtn").css("display","block");
					$("#box_loading").css("display","none");

					console.log ( "send "+url+"  success : "/* +JSON.stringify(data) */);
					if (typeof(success) == "function") {
						success(data);
						var buttons = $("#floatBox").dialog("option", "buttons"); 
						if (typeof(editBox.saveDetail) == "function" && typeof saveUrl == "string") {
							if(data.locked!=true){
								$.extend(buttons, { 
								     "Save" : {
								         text	: "Save",
								         id		: "floatDialogSaveButton",
								         "class": "buttonSave",
								         click	: function(){
												editBox.saveDetail(editId,editBox['saveFloatDialogSucess'],saveUrl);
								         }   
						     	}});
							}
						};
						if(buttons && buttons!=null && !$.isEmptyObject(buttons))
							$.extend(buttons, {
							      "Cancel" : {
								         text	: "Cancel",
								         id		: "floatDialogCancelButton",
								         "class": "dialogButtonCancel",
								         click: function(){
												$("#floatBox").dialog("close");
								         }   
								      }
							      });
						$("#floatBox").dialog("option", "buttons", buttons);
					}
				};

				if(editBox.isNotSaveGotData(url,viewId)){
					$.ajax({
						url			: url,
						type		: "post",
						data		: postData,
						success		: successFn,
						error		: function(data) {
							console.log ( "extensionHandle error: "/*+JSON.stringify(data)*/);
							$("#box_loading").html("not availble");
							if (typeof(error) == "function") {
								error(data);
							}
						}
					});
				}
				else successFn(editBox.gotData[viewId]);
			}
		}
				
		editBox.editRow = function (id,rowData,url,viewId){
			var editUrl = typeof url 	== "string"? url	: editBox.loadUrl;
			var vId 	= typeof viewId == "string"? viewId	:'editBoxContentview';
			if (typeof(editBox.preEditHandleAction) == "function") {
				editBox.preEditHandleAction(id,rowData);
			}
			
	    	var dOption = {
				    	title 		: rowData.CODE,
				 		postData 	: editBox.initExtraPostData(id,rowData,editUrl),
				 		url 		: editUrl,
				 		viewId 		: vId,
	    	    	};
	 		success = function(data){
			    if(typeof editBox.editGroupSuccess == "function") editBox.editGroupSuccess(data,id,editUrl);
			}
			editBox.showDialog(dOption,success);
	    }
		
		editBox.renderSumRow = function (api,columns,fixed){
			fixed = typeof(fixed) == "undefined"?3:fixed;
	        var total = 0;
			$.each(columns, function( i, column ) {
				total = 0;
		        $.each(api.columns(column).data()[0], function( index, value ) {
		        	var newValue = value;
		        	if(typeof value == "string") {
			        	newValue = value.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		         		newValue = parseFloat(newValue.replace(',','.'));
		         		newValue = isNaN(newValue)? 0 : newValue;
		        	}
		        	total += newValue;
				});
		        // Update footer
		        var displayValue = total.toFixed(fixed);
		        if(configuration.number.DECIMAL_MARK=='comma') displayValue = displayValue.replace('.',',')
		        $( api.columns(column).footer() ).html(displayValue);
			});
			return total;
		}

		editBox.renderOutputText = function (texts){
		    var suffix = texts.ObjectName?texts.ObjectName:'';
			return 	suffix+"("+
					texts.IntObjectType+"."+
					texts.ObjectDataSource+"."+
					(texts.hasOwnProperty('CodeFlowPhase')? 		(texts["CodeFlowPhase"]+".")	:"")+
					(texts.hasOwnProperty('CodeEventType')? 		(texts["CodeEventType"]+".")	:"")+
					(texts.hasOwnProperty('CodeAllocType')? 		(texts["CodeAllocType"]+".")	:"")+
					(texts.hasOwnProperty('CodePlanType')? 			(texts["CodePlanType"]+".")		:"")+
// 					(texts.hasOwnProperty('CodeProductType')? 		(texts["CodeProductType"]+".")	:"")+
					(texts.hasOwnProperty('CodeForecastType')? 		(texts["CodeForecastType"]+".")	:"")+
					(texts.hasOwnProperty('GraphObjectTypeProperty')? 	texts["GraphObjectTypeProperty"]		:"")+
					")";
		};


		editBox.getObjectValue = function (dataStore){
			var s3	="";
			var d0 	= dataStore.IntObjectType;
			if(d0=="ENERGY_UNIT") s3+=":"+dataStore.CodeFlowPhase;
			var x	= 	d0+":"+
						dataStore.ObjectName+(dataStore.graph_cummulative==1?"(*)":"")+":"+
						dataStore.ObjectDataSource+":"+
						dataStore.GraphObjectTypeProperty+
						s3+"~"+
						dataStore.CodeAllocType+"~"+
						dataStore.CodePlanType+"~"+
						dataStore.CodeForecastType+"~"+
						dataStore.cboYPos+"~"+
						dataStore.txt_y_unit+"~"+
						dataStore.CodeProductType+"~"+
						dataStore.CodeEventType;
			return x;
		};
		
		editBox.editSelectedObjects = function (dataStore,resultText,x){
			if(currentSpan!=null) {
				dataStore.text	= resultText;
				currentSpan.data(dataStore);
				currentSpan.text(resultText);
				var li = currentSpan.closest( "li" );
				editBox.updateObjectAttributes(li,dataStore,x);
			}
		};


		editBox.updateObjectAttributes = function (li,dataStore,x){
			if(typeof x !="string")
				x = editBox.getObjectValue(dataStore);
			li.attr("object_value",x);
		};
		
		editBox.renderEditFilter	= function(objects,viewDialog){
		    $("#box_loading").css("display","none");
		    $("#contrainList").css("display","none");
		    $( "#floatBox > div" ).css("display","none");
		    viewDialog = typeof viewDialog != "undefined"?viewDialog:"editBoxContentview";
		    $("#objectListContainer").css("display","block");
		    $("#"+viewDialog).css("display","block");
		}

		editBox.getShowEditFilterDialogFunction	= function( table,rowData,td,tab,editId){
			var id 				= rowData['DT_RowId'];
			return function(e){
				if(typeof e != "undefined") e.preventDefault();
			    var viewDialog = typeof editId != "undefined"?editId:"editBoxContentview";

				if(typeof editBox.renderEditFilter == "function") editBox.renderEditFilter(rowData.OBJECTS,viewDialog);

			    $("button[class=saveAction]").remove();
			    var saveAsBtn = $("<button id ='actionSaveAsFilter' class='saveAction' style='width: 61px;float:right;margin-left:5px'>Save as</button>");
			    saveAsBtn.click(function() {
			    	alert("please add object!");
				});
			    
			    var actionsBtn = $("<button id ='actionSaveFilter' class='saveAction' style='width: 61px;float:right;margin-left:5px'>Save</button>");
			    actionsBtn.click(function() {
			    	var lis			= $("#objectList ul:first li");
			    	if(lis.length>0){
						var objects		= [];
						$.each(lis, function( index, li) {
							var span = $(li).find("span:first");
							objects.push(span.data());
						});
						rowData.OBJECTS = objects;
						if(typeof editBox.updateMoreObject == "function") editBox.updateMoreObject(rowData);
		 				editBox.closeEditWindow(true);
			    	}
			    	else alert("please add object!");
				});
			    actionsBtn.appendTo($("#objectListContainer"));

			    var height 	= typeof rowData.height != "undefined" ?rowData.height	:editBox.size.height;
			    var width 	= typeof rowData.width 	!= "undefined" ?rowData.width	:editBox.size.width;
			    var title 	= typeof rowData.title 	!= "undefined" ?rowData.title	:"Edit Summary Item";
			    $("#floatBox").dialog( {
					height	: height,
					width	: width, 
					position: { my: 'top', at: 'top+150' },
					modal	: true,
					title	: title,
					close	: function(event) {
								$("#objectList").css('display','none');
								$("button[class=saveAction]").css('display','none');
							    $("button[class=saveAction]").remove();
						   	 },
			   	 	open	: function( event, ui ) {
								$("#objectList").css('display','block');
							},
				});
			    editBox.currentId = id;
			    editBox.currentTable = table;
	 		    if(typeof editBox.renderFilter == "function")  editBox.renderFilter(rowData);
			    currentSpan = null;
			    if(typeof editBox.editObjectMoreHandle == "function") editBox.editObjectMoreHandle(table,rowData,td,tab);
			};
		};
		
		editBox.addMoreHandle	= function ( table,rowData,td,tab,element) {
			var moreFunction	= editBox.getShowEditFilterDialogFunction( table,rowData,td,tab);
			element.click(moreFunction);
		}
	}
</script>
@yield('editBoxParams')

<div id="floatBox" style="display:none;">
		@foreach($floatContents as $key => $content )
			<div id="{{$content}}"  style="display:none;border:none; margin-top: 0;height: 100%;">
				@yield($content)
			</div>
			@yield("extra_".$content)
	 	@endforeach
		<div id="box_loading" >Loading...</div>
</div>

@section('floatMoreBox')
	<div id="floatMoreBox" style="display:none;">
		@yield('floatMoreBoxContent')
	</div>
@stop
@yield('floatMoreBox')


