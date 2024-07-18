//inline editable
//turn to inline mode
if(typeof($.fn.editable) !== "undefined") $.fn.editable.defaults.mode = 'inline';
var current_td = null;
document.addEventListener('keydown', function (evt) {
    if (evt.keyCode == 9 && current_td!=null){
		evt.preventDefault();
		var tabindex = Number($(current_td).attr('tabindex'));
		if(tabindex>0){
			$('td[tabindex="'+(tabindex+(evt.shiftKey?-1:1))+'"]').click();
		}
	}
	else if(evt.keyCode == 13){
		var o = $(':focus');
		if(o.length>0 && $(o[0]).hasClass('editInline'))
			evt.preventDefault();
			$(o[0]).click();
	} 
});

$.fn.equals = function(compareTo) {
	  if (!compareTo || this.length != compareTo.length) {
	    return false;
	  }
	  for (var i = 0; i < this.length; ++i) {
	    if (this[i] !== compareTo[i]) {
	      return false;
	    }
	  }
	  return true;
	};
	
var ebtoken = $('meta[name="_token"]').attr('content');
$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

function sleep(milliseconds) {
	  var start = new Date().getTime();
	  for (var i = 0; i < 1e7; i++) {
	    if ((new Date().getTime() - start) > milliseconds){
	      break;
	    }
	  }
	}

function arrayUnique(array,equalFunction) {
    var a = array.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j] || (typeof equalFunction == "function" && equalFunction(a[i],a[j])))
                a.splice(j--, 1);
        }
    }

    return a;
}

function getJsDate(dateString){
	date = moment.utc(dateString,configuration.time.DATE_FORMAT_UTC);
	y = date.year();
	m = date.month();
	d = date.date();
	date = Date.UTC(y,m,d);
    return date;
}

function getJsDateTime(dateString){
	date = moment.utc(dateString,configuration.time.DATETIME_FORMAT_UTC);
	y = date.year();
	m = date.month();
	d = date.date();
	hour = date.hours();
	minute = date.minutes();
	date = Date.UTC(y,m,d,hour,minute);
    return date;
}

function isInt(n){
    return Number(n) === n && n % 1 === 0;
}

function isFloat(n){
    return Number(n) === n && n % 1 !== 0;
}

if(typeof filters == "undefined") filters = {};

var renderDependenceHtml = function(elementId,dependenceData) {
	var option 	= $('<option />');
	var name 	= typeof(dependenceData.CODE) 	!== "undefined"?dependenceData.CODE:dependenceData.code;
	var text 	= typeof(dependenceData.NAME) 	!== "undefined"?dependenceData.NAME:dependenceData.name;
	var id 		= typeof(dependenceData.ID) 	!== "undefined"?dependenceData.ID:dependenceData.id;
	id 			= id?id:name;
	option.attr('name', name);
	option.attr('value', id).text(text);
	return option;
};

var enableSelect = function(dependentIds, value,model) {
	for (var i = 0; i < dependentIds.length; i++) {
		$('#'+dependentIds[i]).prop('disabled', value);
		if (!value&&typeof(filters.afterRenderingDependences) == "function") {
			filters.afterRenderingDependences(dependentIds[i],model);
		}
	}
};


var registerDateNavigation = function(aElementId) {
	$('#next_'+aElementId).on("click", function () {
	    var date = $('#'+aElementId).datepicker('getDate');
	    date.setTime(date.getTime() + (1000*60*60*24))
	    $('#'+aElementId).datepicker("setDate", date);
	});
	
	$('#pre_'+aElementId).on("click", function () {
	    var date = $('#'+aElementId).datepicker('getDate');
	    date.setTime(date.getTime() - (1000*60*60*24))
	    $('#'+aElementId).datepicker("setDate", date);
	});
	
	if(aElementId=="date_end"){
		var disableElement = $("<input type='checkbox' style='float:left;width:20px'>").attr("id","disable_"+aElementId);
		disableElement.change(function(e){
			var checked = disableElement.is(":checked");
			if(checked){
			}
			else{
				$('#'+aElementId).val($('#date_begin').val());
				$('#'+aElementId).change();
			}
			$('#'+aElementId).prop('disabled', !checked);
		});
		var checked = disable_date_end!==undefined&&disable_date_end==1;
		disableElement.prop('checked', checked);
		if(!checked) disableElement.change();
		$('#'+aElementId).parent().prepend(disableElement);
	}
}

	
var registerOnChange = function(sourceObject, dependentIds,more) {
	if(typeof more=="string") more = $.parseJSON(more); 
	var model = null;
	var id = sourceObject;
	var dependeceNameFn = function(){};
	var initDependentSelectsFn = function(){};

	if(typeof sourceObject =="string"){
		var partials 	= sourceObject.split("_");
		var prefix 		= partials.length>1?partials[0]+"_":"";
		model 		= partials.length>1?partials[1]:id;
		dependeceNameFn = function(dvalue){
			return prefix+dvalue;
		};
		initDependentSelectsFn = function(tmpDependentIds){
			var dependentSelects = [];
			$.each(tmpDependentIds, function( dindex, dvalue ) {
				if (typeof dvalue === 'string' || dvalue instanceof String){
					var dependeceName = dependeceNameFn(dvalue);
					dependentSelects.push(dependeceName);
				}
				else if(typeof(dvalue["name"]) !== "undefined"
					&&(typeof(dvalue["independent"]) === "undefined")
						||!dvalue["independent"]){
					var dependeceName = dependeceNameFn(dvalue["name"]);
					dependentSelects.push(dependeceName);
				}
			});
			
			return dependentSelects;
		};
	}
	else{
		id 			= sourceObject.id;
		model 		= sourceObject.model;
		prefix 		= sourceObject.valueId;
		dependeceNameFn = function(dvalue){
			if(prefix!="") return dvalue+"-"+prefix;
			return dvalue;
		};
		
		initDependentSelectsFn = function(tmpDependentIds){
			var dependentSelects = [];
			$.each(sourceObject.targets, function( dindex, dvalue ) {
				var dependeceName = dependeceNameFn(dvalue);
				dependentSelects.push(dependeceName);
			});
			return dependentSelects;
		};
	}
	$('#'+id).change(function(e){
		if($.isArray(tmpDependentIds)){
			var tmpDependentIds = $.merge([], dependentIds);
		}
		else {
			var tmpDependentIds = Object.keys(dependentIds).map(function (key) {
				return dependentIds[key];
				/*if(typeof dependentIds[key] == "string") return dependentIds[key]; 
				else if(typeof dependentIds[key] == "object") return dependentIds[key]['name']; */
			});
		}
		
		if (typeof(filters.preOnchange) == "function") {
			filters.preOnchange(id,tmpDependentIds,more,prefix);
		}
		
		var ccontinue = false;
		var currentValue = $(this).val();
		if(typeof filters.moreDependence == 'function') 
			tmpDependentIds = filters.moreDependence(tmpDependentIds,model,currentValue,prefix);
		
		var dependentSelects = initDependentSelectsFn(tmpDependentIds);
		
		$.each(dependentSelects, function( dindex, dvalue ) {
			ccontinue = ccontinue|| $("#"+dvalue).is(":visible");
		});
		if(!ccontinue) return;
		
		enableSelect(dependentSelects,'disabled');
		bundle = {};
		if (more!=null&&more.length>0) {
			$.each(more, function( i, value ) {
				if(typeof value == "object") jQuery.extend(bundle, value);
				else if(typeof value == "string") {
					bundle[value] = {};
					var elementId = dependeceNameFn(value);
					if($("#"+elementId).length==0&&typeof secondaryDependeceNameFn == 'function')
						elementId = secondaryDependeceNameFn(value,elementId);
					var name = $("#"+elementId).find(":selected").attr( "name");
					var val = $("#"+elementId).val();
 					bundle[value]['name'] 	= name;
					bundle[value]['id'] 	= val;
					var exists = 0 != $("#"+elementId+' option[value=0]').length;
					if(exists) bundle[value]['default'] 	= {ID	: 0, NAME: $("#"+elementId+' option[value=0]').text()};
				}
			});
		}
		$.ajax({
			url: '/code/list',
			type: "post",
			data: {	type		: model,
					dependences	: tmpDependentIds,
					value		: currentValue,
					extra		: bundle
				},
			success: function(results){
				$.each(dependentSelects, function( dindex, dvalue ) {
					$('#'+dvalue).html('');   // clear the existing options
				});
				for (var i = 0; i < results.length; i++) {
					var elementId = dependeceNameFn(results[i].id);
					if(typeof results[i]['default']=='object'){
						var option = renderDependenceHtml(results[i]['default'].ID,results[i]['default']);
						$('#'+elementId).append(option);
					}
					$(results[i].collection).each(function(){
						var option = renderDependenceHtml(elementId,this);
						$('#'+elementId).append(option);
					});
					$('#'+elementId).val(results[i].currentId);
					if(typeof onAfterGotDependences == "function") onAfterGotDependences(elementId,$('#'+elementId),results[i].currentId);
				}
				enableSelect(dependentSelects,false,model);
			},
			error: function(data) {
				console.log(data.responseText);
				alert("Could not get dropdown menu");
//				enableSelect(dependentIds,false);
			}
		});
	});
};

var getActiveTabID = function() {
	var activeTabIdx = $("#tabs").tabs('option', 'active');
	var selector = '#tabs > ul > li';
	var activeTabID = $(selector).eq(activeTabIdx).attr('id');
	return activeTabID;
}


var typetoclass = function (data){
	switch(data){
		case 1:
		case "1":
			return "text";
		case 2:
		case "2":
			return "number";
		case 3:
		case "3":
			return "date";
		case 4:
		case "4":
			return "datetimepicker";
		case 5:
		case "5":
			return "checkbox";
		case 6:
		case "6":
			return "timepicker";
		case 7:
		case "7":
			return "EVENT";
		case 8:
		case "8":
			return "textarea";
		default:
			if(data!=null) return data;
	}
	return "text";
};

var source = {
		initRequest	:	 function(tab,columnName,newValue,collection){
			postData = actions.loadedData[tab];
			srcData = {	name : columnName,
						value : newValue,
						Facility : postData['Facility'],
					};
			if(typeof source[columnName] == "object") srcData.target	= source[columnName].dependenceColumnName;
			return srcData;
		}
	};

var actions = {
		
	loadUrl 			: false,
	saveUrl 			: false,
	historyUrl			: false,
	readyToLoad 		: false,
	loadedData 			: {},
	loadPostParams 		: null,
	initData 			: false,
	initSaveData 		: false,
	rights				: [],
	editedData 			: {},
	deleteData 			: {},
	objectIds 			: {},
	extraDataSetColumns : {},
	extraDataSet 		: {},
	type 				: {
							idName			:['ID'],
							keyField		:'ID',
							saveKeyField 	:'ID',
							},
	loadSuccess 		: function(data){/*alert("success");*/},
	loadError 			: function(data){
								var messageText = "not available";
								var detailHtml  = "not available";
								linkText 		= "ERROR! Please <span class='clickable' id='error_click_detail'>click here</span> for details<br>";
								if(typeof data.responseText!="undefined"){
									messageText  	= JSON.stringify(data.responseText);									
									detailHtml		= messageText;
									if(messageText.length>3000) {
										detailHtml	= data.responseText;
										messageText	= "";
									}
								}
								messageText=linkText+messageText;
								alert(messageText);
								$("#error_click_detail").click(function(ev){
									var w = window.open();
									$(w.document.body).html(detailHtml);
								});
							},
	shouldLoad 			: function(data){return false;},
	addingNewRowSuccess	: function(data,table,tab,isAddingNewRow){},
	afterGotSavedData 	: function(data,table,key){},
	dominoColumns 		: function(columnName,newValue,tab,rowData,collection,td){},
	tableIsDragable 	: function(tab){return false;},
	loadNeighbor		: function (){
							if (actions.shouldLoad()) {
								actions.doLoad(false);
							}
							else{
								var activeTabID = getActiveTabID();
								var postData = actions.loadedData[activeTabID];
								actions.updateView(postData);
								var table =$("#table"+activeTabID).DataTable();
//								table.columns.adjust().draw();
//								$("#table"+activeTabID).resize();
								table.draw();
							}
						},
	getFilterValues 	: function (){
							var params = {};
							if (typeof(javascriptFilterGroups) !== "undefined") {
								for (var key in javascriptFilterGroups) {
									filterGroup = javascriptFilterGroups[key];
									for (var jkey in filterGroup) {
										entry = filterGroup[jkey];
										//if($('#'+entry.id).css('display') != 'none' && $('#'+entry.id).is(":visible"))
										{
											   params[entry.id] = $('#'+entry.id).val();
										}
									}
								}
							}
							return params;
						},
	loadParams 			: function (reLoadParams){
							var params;
							if (reLoadParams) {
								params	= actions.getFilterValues();
								actions.loadPostParams = params;
							} else {
								params = actions.loadPostParams;
							}
							if (typeof(actions.initData) == "function") {
								var extras = actions.initData();
								if (extras) {
									jQuery.extend(extras, params);
									return extras;
								}
							}
							return params;
						},
	
	loadSaveParams 		: function (reLoadParams){
							var params = actions.loadParams(reLoadParams);
							if (reLoadParams) {
								if(!jQuery.isEmptyObject(actions.editedData)){
									params['editedData'] = actions.editedData;
								}
								if(!jQuery.isEmptyObject(actions.deleteData)){
									params['deleteData'] = actions.deleteData;
								}
								params['objectIds'] = actions.objectIds;
							} else {
					//			params = actions.loadPostParams;
							}
							return params;
						},
	
	doLoad 				: function (reLoadParams){
							if (actions.loadUrl) {
								var validatingResult = actions.loadValidating(reLoadParams);
								if(validatingResult.state){
									console.log ( "doLoad url: "+actions.loadUrl );
									actions.readyToLoad = true;
									showWaiting();
//									actions.editedData = {};
									$.ajax({
										url: actions.loadUrl,
										type: "post",
										data: validatingResult.params,
										success:function(data){
											hideWaiting();
											if (typeof(actions.preLoadSuccess) == "function") {
												data = actions.preLoadSuccess(reLoadParams,data);
											}
//											if(reLoadParams) actions.editedData = {};
											if (typeof(actions.loadSuccess) == "function") {
												actions.loadSuccess(data);
											}
											else{
												alert("load success");
											}
										},
										error: function(data) {
											console.log ( "doLoad error");
											hideWaiting();
											if (typeof(actions.loadError) == "function") {
												actions.loadError(data);
											}
										}
									});
									return true;
								}
								else if(validatingResult.message !== undefined &&validatingResult.message!=""){
									alert(validatingResult.message);
								}
								else console.log("validate false");
							}
							else{
								alert("init load params");
							}
							return false;
						},
	updateView 			: function(postData){
							var noData = jQuery.isEmptyObject(postData);
							if (!noData&&typeof(javascriptFilterGroups) !== "undefined") {
								for (var key in javascriptFilterGroups) {
									filterGroup = javascriptFilterGroups[key];
									for (var jkey in filterGroup) {
										entry = filterGroup[jkey];
										if(typeof(entry) !== "undefined"&&
												$('.'+entry.id).css('display') != 'none'){
											if ($('#'+entry.id).val()!=postData[entry.id]) {
												$('#'+entry.id).val(postData[entry.id]).trigger('change');
											}
										}
									}
								}
							}
						},
	loadValidating 		: function (reLoadParams){
							var params = actions.loadParams(reLoadParams);
							var state = true;
							var message = "";
							if(params.date_begin == ""){
								state = false;
								message = "Please select date";
							}
							else if(params.date_end == "" && params.date_begin != undefined){
								params.date_end = params.date_begin;
							}
							return {state: state, params: params, message: message};
						},
	validating 			: function (reLoadParams){
							isNoChange = (jQuery.isEmptyObject(actions.editedData))&&(jQuery.isEmptyObject(actions.deleteData));
							if(isNoChange) alert("no change to commit");
							return !isNoChange;
						},
	doSave 				: function (reLoadParams,edittedData){
							if(typeof actions.preDoSave == "function") actions.preDoSave(reLoadParams,edittedData);
							if (this.saveUrl) {
								validated = actions.validating(reLoadParams);
					//			actions.readyToLoad = true;
								if(validated){
									console.log ( "doLoad url: "+this.saveUrl );
									showWaiting();
									var postData	= typeof edittedData == "object"?postData:actions.loadSaveParams(reLoadParams);
									actions.highlights = [];
									$('.editable-unsaved, .highlight-s').each(function(){
										actions.highlights.push('#'+$(this).closest('table').attr('id')+' #'+$(this).closest('tr').attr('id')+' .'+$(this).attr('class').split(' ')[2]);
									})
									$.ajax({
										url	: this.saveUrl,
										type: "post",
										data: postData,
										success:function(data){
											hideWaiting();
											if (typeof(actions.saveSuccess) == "function") {
												actions.saveSuccess(data);
											}
											else{
												alert("save success");
											}
											actions.highlights && actions.highlights.forEach(function(p){
												$(p).addClass('highlight-s');
											})
										},
										error: function(data) {
											console.log ( "doSave error");
											if (typeof(actions.loadError) == "function") {
												actions.loadError(data);
											}
											hideWaiting();
										}
									});
									return true;
								}
								else console.log ( "not validated ");
							}
							else{
								alert("save url not initial");
							}
							return false;
						},
	getExtraDataSetColumn :function(data,cindex,rowData){
							sourceColumn = data.properties[cindex].data;
							ecolumn = actions.extraDataSetColumns[sourceColumn];
					 		ecollectionColumn = rowData[ecolumn];
					 		ecollection = null;
					 		
					 		if(ecollectionColumn!=null&&
					 				ecollectionColumn!=''&&
					 				typeof(actions.extraDataSet[ecolumn]) !== "undefined"){
					 			if(actions.extraDataSet[ecolumn].hasOwnProperty(sourceColumn)){
					 				ecollection = actions.extraDataSet[ecolumn][sourceColumn];
					 			}
					 			else if(typeof(actions.extraDataSet[ecolumn][ecollectionColumn]) !== "undefined"){
						 			ecollection = actions.extraDataSet[ecolumn][ecollectionColumn];
						 		}
							}
					 		if(ecollection == null){
					 			if($.isArray(actions.extraDataSet[ecolumn]))  ecollection = actions.extraDataSet[ecolumn];
					 			else if(typeof(sourceColumn) !== "undefined" && sourceColumn==ecolumn){
					 	 			ecollection = actions.extraDataSet[sourceColumn][ecolumn];
					 	 		}
					 		} 
							if(typeof actions.loadCustomObjectName=="function") ecollection = actions.loadCustomObjectName(sourceColumn,ecollection,rowData);
					 		return ecollection;
						},
	isEditable 			: function (column,rowData,rights){
							actions.rights = rights;
							var field = typeof column.DATA_METHOD!="undefined"?column.DATA_METHOD:column.data_method;
							var rs = field==1||field=='1';
							return rs;
						},
	formatCollection 	: function (ecollection){
							if(ecollection!=null&&$.isArray(ecollection)&&ecollection.length>0){
								$.each(ecollection, function( index, item ) {
									if(item.value==undefined&&(item.ID||item.id)!==undefined) item.value = item.ID||item.id;
									if(item.text==undefined&&(item.NAME||item.name)!==undefined) item.text = item.NAME||item.name;
								});
							}
					 		return ecollection;
						},
	isEditableByRight	: function (rowData,rights){
							var rs = true;
							var rField = typeof rowData.RECORD_STATUS=="string"?rowData.RECORD_STATUS:rowData.record_status;
							if(rField=="A"){
								rs =$.inArray("ADMIN_APPROVE", rights)>=0;
							}
							else if(rField=="V"){
								rs =$.inArray("ADMIN_APPROVE", rights)>=0||$.inArray("ADMIN_VALIDATE", rights)>=0;
							}
							return rs;
						},
	preDataTable 	: function (dataset){
						return dataset;
					},
	containRight 	: function (right){
		return typeof right == "string" && (($.inArray(right, actions.rights)>=0)||($.inArray("_ALL_", actions.rights)>=0));
	},
	afterDataTable : function (table,tab){
		$("#toolbar_"+tab).html('');
	},
	deleteActionColumn : function ( data, type, rowData ) {
		var id = rowData['DT_RowId'];
		var html = '<a id="delete_row_'+id+'" class="actionLink">Delete</a>';
		return html;
	},
	isDisableAddingButton	: function (tab,table) {
		return true;
	},
	renderFirsColumn : function ( data, type, rowData ) {
		return actions.defaultRenderFirsColumn(data, type, rowData);
	},
	renderDatePicker : function (editable,columnName,cellData, rowData){
		editable['viewformat'] = configuration.picker.DATE_FORMAT;
		return editable;
	},
	renderDateFormat : function (data2,type2,row){
		if(typeof data2 == "string") return moment.utc(data2,configuration.time.DATETIME_FORMAT_UTC).format(configuration.time.DATE_FORMAT);
		return moment.utc(data2).format(configuration.time.DATE_FORMAT);
	},
	validates : function (input,value,property){
//		if(property)
	},
	getPropertyValue: function (object,property){
		if(typeof object[property] != "undefined") return object[property] ;
		return object[property.toLowerCase()] ;
	},
	defaultRenderFirsColumn : function ( data, type, rowData ) {
		var html = "<div class='firstColumn'>"+data+"</div>";
		var extraHtml = "<div class='extraFirstColumn'>";
		var phaseName	= rowData.hasOwnProperty('PHASE_NAME')?rowData['PHASE_NAME']:rowData['phase_name'];
		if(rowData.hasOwnProperty('PHASE_CODE')){
			extraHtml += "<div class='phase "+rowData['PHASE_CODE']+"'>"+ phaseName+"</div>";
		}
		else if(rowData.hasOwnProperty('phase_code')){
			extraHtml += "<div class='phase "+rowData['phase_code']+"'>"+phaseName+"</div>";
		}
		else if(rowData.hasOwnProperty('PHASE_NAME')||rowData.hasOwnProperty('phase_name')){
			extraHtml += "<div class='phase "+phaseName+"'>"+phaseName+"</div>";
		}
		if(rowData.hasOwnProperty('STATUS_NAME')){
			extraHtml +="<span class='eustatus'>"+rowData['STATUS_NAME']+"</span>";
		}
		else if(rowData.hasOwnProperty('status_name')){
			extraHtml +="<span class='eustatus'>"+rowData['status_name']+"</span>";
		}
		if(rowData.hasOwnProperty('TYPE_CODE')){
			extraHtml +="<span class='eventType'>"+rowData['TYPE_CODE']+"</span>";
		}
		else if(rowData.hasOwnProperty('type_code')){
			extraHtml +="<span class='eventType'>"+rowData['type_code']+"</span>";
		}
		extraHtml += "</div>";
		return html+extraHtml;
	},
	validateNumberWithRules : function (property,strimValue){
		var minValue	= actions.getRuleValue(property,"VALUE_MIN",-1*Number.MAX_VALUE);
		var maxValue	= actions.getRuleValue(property,"VALUE_MAX",Number.MAX_VALUE);
		
		if(minValue>=maxValue) return;
		if(strimValue < minValue) return 'This field need greater or equal '+ minValue;
		if(strimValue > maxValue) return 'This field need less or equal '+maxValue;

	},
	applyEditable : function (tab,type,td, cellData, rowData, property,collection){
		var columnName = typeof property === 'string'?property:property.data;
		$(td).editable("destroy");
		if(typeof type == "object" && typeof type.applyEditable == "function") return type.applyEditable(tab,type,td, cellData, rowData, property,collection);
		actions.addTabIndex(td,type);
		var successFunction = actions.getEditSuccessfn(property,tab,td, rowData, columnName,collection,type);
		var  editable = {
	    	    title: 'edit',
	    	    emptytext: '&nbsp;',
	    	    onblur			: 'cancel',
//				mode		: "inline",
	    	    showbuttons:false,
	    	    success: successFunction,
	    	};
		
		switch(type){
		case "text":
		case "number":
		case "date":
			editable['type'] = type;
    	    editable['onblur'] = 'submit';
			if (type=='date') {
				editable['onblur'] 		= 'submit';
				editable['format'] 		= configuration.picker.DATE_FORMAT_UTC;
				editable				= actions.renderDatePicker(editable,columnName,cellData, rowData); 
				editable['inputclass'] 	= "datePickerInput";
//				editable['format'] = 'mm/dd/yyyy';
//				editable['viewformat'] = 'mm/dd/yyyy';
			}
			else if(type=='number') {
				editable['type'] = "text";
				if(configuration.number.DECIMAL_MARK=='comma') 
					editable['tpl'] = "<input class='cellnumber' type=\"text\" pattern=\"^[-]?[0-9]+([,][0-9]{1,20})?\">";
				else  
					editable['tpl'] = "<input class='cellnumber' type=\"text\" pattern=\"^[-]?[0-9]+([\.][0-9]{1,20})?\">";
			}
	    	break;
		case "EVENT":
		case "textarea":
			cellData				= cellData==null?(type=="EVENT"?{}:""):cellData;
			editable['type'] 		= type;
			editable['title'] 		= "";
			editable['onblur'] 		= 'cancel';
			editable['value'] 		= cellData;
			editable['mode'] 		= "popup";
			editable['placement'] 	= "bottom";
			editable['showbuttons'] = 'bottom';
			if(type=="textarea")
				editable.display	= function(value) {
									      $(this).html(value.replace(/(?:\r\n|\r|\n)/g, '<br />'));
									} ;
			if(typeof actions.configEventType == "function") actions.configEventType(editable,columnName,cellData,rowData);
	    	break;
		case "datetimepicker":
			editable['onblur'] 	= 'submit';
			editable['type'] 	= 'datetime';
			editable['format'] 	= configuration.picker.DATETIME_FORMAT_UTC;
			editable['viewformat'] = configuration.picker.DATETIME_FORMAT;
			editable['datetimepicker'] 	= 	{
//								          		weekStart: 1,
								          		minuteStep :5,
								          		showMeridian : true,
//								          		minViewMode	:1,
//								          		maxViewMode	:3,
//								          		startView:1
								            };
	    	break;
	    	
		case "timepicker":
			editable['onblur'] = 'submit';
			editable['type'] = 'datetime';
			editable['format'] = configuration.picker.TIME_FORMAT_UTC;
//			editable['format'] = 'hh:ii:ss';
			editable['viewformat'] = configuration.picker.TIME_FORMAT;
//			editable['viewformat'] = 'HH:ii P';
			editable['datetimepicker'] 	= 	{
								          		minuteStep :5,
								          		showMeridian : true,
								          		startView:1,
								          		minView:0,
								          		maxView:1,
								            };
	    	break;
		case "select":
			editable['type'] 		= type;
			collection 				= actions.formatCollection(collection);
			var collectionByRight 	= actions.getUomCollection(collection,columnName,cellData, rowData);
			editable['source'] 		= collectionByRight;
			editable['value'] 		= cellData==null?(collectionByRight!=null&&collectionByRight[0]!=null?
										actions.getPropertyValue(collectionByRight[0],"ID"):0):cellData;
//			$(td).editable(editable);
//			return;
	    	break;
		case "color":
			$(td).addClass( "_colorpicker" );
			$(td).data(cellData);
			$(td).css("background-color",'#'+cellData);
			$(td).css("color",'#'+cellData);
			$(td).ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val(hex);
					$(el).css({"background-color":"#"+hex,"color":"#"+hex});
					$(el).ColorPickerHide();
					rowData[columnName] = hex;
//					$(td).text(hex);
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(rowData[columnName]);
				}
			});
			return;	
		}
		editable['validate'] = function(value) {
			var validateResult	= null;
			var strimValue 		= $.trim(value);
			var objectRules		= actions.getObjectRules(property,rowData);
			switch(type){
			case "text":
			case "number":
			case "date":
//				if(strimValue == '')  validateResult =  'inputted data must not empty';
				if(type=="number"&&typeof property !== 'string'){
					var basicRules	= actions.getBasicRules(property,objectRules);
					validateResult 	= actions.validateNumberWithRules(basicRules,strimValue);
				}
				break;
			}
			if(validateResult==null&&objectRules!=null&&typeof objectRules.advance == "object"){
				var enforceEditNote = (objectRules.advance.ENFORCE_EDIT_NOTE==true)||objectRules.advance.ENFORCE_EDIT_NOTE=="true";
				if(enforceEditNote){
					var auditNote	= prompt("Please input memo","reason for new value "+strimValue);
					if(auditNote!="" && auditNote!=null){
						actions.putModifiedData(tab,"AUDIT_NOTE-"+columnName,auditNote,rowData,"text");
					}
					else validateResult =  'Please input memo';
				}
			}
			return validateResult;
	    };
		$(td).editable(editable);
    	$(td).on("shown", function(e, editable) {
			  current_td = this;
    		  if(typeof actions.preEditableShow == "function") actions.preEditableShow();
//    		  var val = editable.input.$input.val();
//    		  if(val.trim()=="")editable.input.$input.val('');
    		  if(type=="timepicker") $(".table-condensed thead").css("visibility","hidden");
    		  if(type=="timepicker" || type=="datetimepicker") $( ".datetimepicker " ).draggable();
//    		  $(".extension-buttons").css("display","none");
    		  $("#more_actions").html("");
    		  if(typeof editable == "undefined") return;
    		  if(type=="number") {
//					$( editable.input.$input.get(0) ).closest( ".editable-container" ).css("float","right");
					$( editable.input.$input.get(0) ).css("float","right");
					if (actions.historyUrl){
					//						$(".extension-buttons").css("display","block");
						var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
						if( $('#'+hid).length ){
						}
						else{
							var extensionButton = $("<div class=\"extension-buttons\"><img src=\"/common/css/images/hist.png\" height=\"16\" class=\"editable-extension\"></div>");
							extensionButton.css("display","block");
							extensionButton.attr( 'id', hid);
							extensionButton.click(function(e){
								actions.extensionHandle(tab,columnName,rowData,false,successFunction,true);
							});
							$("#more_actions").append(extensionButton);
						}
					}
					
					val = rowData[columnName];
		    		val = Math.floor(val) == val && $.isNumeric(val)?Math.floor(val):val;
		    		val = val!=null?""+val:"";
		    		if(configuration.number.DECIMAL_MARK=='comma') val = val.replace('.',',')
					editable.input.$input.val(val);
    		  }
    		  editable.input.$input.attr('tabindex', -1);
    	});
    	
    	$(td).on('hidden', function(e, reason) {
			if(current_td==this)
				current_td=null;
  		  	if(typeof actions.preEditableHiden == "function") actions.preEditableHiden();
			var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
    		$("#" +hid).remove();
    		if(reason === 'save' || reason === 'cancel') {
    	        //auto-open next editable
    			/*var nextElement = $(this).closest('.editable').nextAll('.editable:first');
    			if(nextElement.length>0) nextElement.editable('show');
    			else {
    				$(this).closest('tr').next().find('.editable:first').editable('show');
    			}
    			 */
    	    } 
    	});
    	
    	$(td).on('save', function(e, params) {
    		if(params.newValue!=null&&$.trim(params.newValue)==""){
    			params.newValue = "";
				params.submitValue = "";
				return;
    		}
    	    if(type=="number") {
    	    	value = params.newValue;
    	    	if(value!=null&&value!=""){
    	    		if(configuration.number.DECIMAL_MARK=='comma') value = parseFloat(value.replace(',','.'));
//					else value = parseFloat(value);
    	    		var pvalue = parseFloat(value);
					if(!isNaN(pvalue)){
						var formatNumberValue = actions.formatNumberDecimal(pvalue,property);
						params.newValue = formatNumberValue;
						params.submitValue = pvalue;
					}
				}
    	    	else {
    	    		/*rowData[columnName]	= " ";
    	    		params.newValue = " ";
					params.submitValue = " ";*/
    	    	}
    	    }
    	});
	},
	renderResultByRule  : function(td, color,cssProperty) {
		if(typeof td == "function") td(color,cssProperty);
		else $(td).css(cssProperty, color);
	},
	
	getRuleValue  : function(property,field,defaultValue){
		var value = typeof(property[field]) !== "undefined"&&
					property[field] != null &&
					property[field] != ""?
					parseFloat(property[field]):
						(typeof(property[field.toLowerCase()]) !== "undefined"&&
								property[field.toLowerCase()] != null &&
								property[field.toLowerCase()] != ""?
								parseFloat(property[field.toLowerCase()]):defaultValue);
		return value;
	},
	
	addCellNumberRules  : function(td, property,newValue,rowData,originColor,phase) {
		if(typeof newValue == "string") newValue = newValue.replace(",",".");
		newValue	= parseFloat(newValue);
		if(isNaN(newValue)) return;
		if(newValue==null||newValue==""||newValue==" ") return;
		
		if(phase=="loading"){
			var minValue	= actions.getRuleValue(property,"VALUE_MIN",-1*Number.MAX_VALUE);
			var maxValue	= actions.getRuleValue(property,"VALUE_MAX",Number.MAX_VALUE);
			
			if(minValue<maxValue && (newValue < 	minValue || newValue > maxValue)) {
//				$(td).css('background-color', 'red');
				actions.renderResultByRule(td,"red",'background-color');
				return;
			}
		}
		var minWarningValue	= actions.getRuleValue(property,"VALUE_WARNING_MIN",-1*Number.MAX_VALUE);
		var maxWarningValue	= actions.getRuleValue(property,"VALUE_WARNING_MAX",Number.MAX_VALUE);
		if(newValue <= minWarningValue || newValue >= maxWarningValue) 
			actions.renderResultByRule(td,"#f1c300",'background-color');
		else {
			actions.renderResultByRule(td,originColor,'background-color');
			var rangePercentValue	= actions.getRuleValue(property,"RANGE_PERCENT",false);
			var rangePercent		= false;
			if(	(typeof property.LAST_VALUES == "object" && typeof property.LAST_VALUES[rowData.DT_RowId] == "object")){
				rangePercent = rangePercentValue;
			}
			
			if(rangePercent!=false && rangePercent>0){
				var entry		= 	typeof property.LAST_VALUES == "object" ?
									property.LAST_VALUES[rowData.DT_RowId] :
									property.last_values[rowData.DT_RowId];
									
				var lastValue	= actions.getRuleValue(entry,property.data,0);

				/*var lastValue		= property.LAST_VALUES[rowData.DT_RowId][property.data];
				lastValue		= parseFloat(lastValue);
				*/
				lastValue		= !isNaN(lastValue)?lastValue:0;
				maxcompareValue	= (rangePercent+100)*lastValue/100;
				mincompareValue	= (-rangePercent+100)*lastValue/100;
				if(lastValue>0&&(newValue>maxcompareValue||newValue<mincompareValue))
//					$(td).css('color', 'blue');
					actions.renderResultByRule(td,"blue",'color');
				else
//					$(td).css('color', '');
					actions.renderResultByRule(td,"",'color');
			}
		}
	},

	getBasicRules  : function(property,objectRules) {
		var rules	= property;
		if((typeof(objectRules) == "object" &&(objectRules.OVERWRITE==true || objectRules.OVERWRITE=='true') 
				&& typeof(objectRules.basic) =="object")){
			
			if(objectRules.basic.IS_MANDATORY===undefined)objectRules.basic.IS_MANDATORY = false;
			rules	= jQuery.extend(jQuery.extend({},property), objectRules.basic);
		}
		return rules;
	},
	
	getEditSuccessfn  : function(property,tab, td, rowData, columnName,collection,type) {
		var originColor		= $(td).css('background-color');
		return function(response, newValue) {
//			if(newValue!=null && $.trim(newValue)!=""){
				if(typeof newValue == "string") newValue = newValue.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
				actions.saveNewValue(newValue,property,tab,td,rowData,columnName,collection,type,originColor);
				if(typeof actions.getSimilarCells == "function"){
					var similarCells =  actions.getSimilarCells(property,tab, td, rowData, columnName,collection,type);
					$.each(similarCells, function( index, cell ) {
						var std 		= cell.td;
						var sRowData 	= cell.rowData;
						var sCollection = cell.collection;
						actions.saveNewValue(newValue,property,tab,std,sRowData,columnName,sCollection,type,originColor);
					});
				}
//			}
	    };
	},
	
	saveNewValue			:	function(newValue,property,tab,td,rowData,columnName,collection,type,originColor){
		rowData = actions.putModifiedData(tab,columnName,newValue,rowData,type);
		rowData[columnName] = newValue;
		var table = $('#table_'+tab).DataTable();
		$(td).css('color', 'black');
		if(type=='number'){
			var objectRules		= actions.getObjectRules(property,rowData);
			var basicRules		= actions.getBasicRules(property,objectRules);
			actions.addCellNumberRules(td,basicRules,newValue,rowData,originColor,"manual");
		}
		table.row( '#'+rowData['DT_RowId'] ).data(rowData);
		if(actions.isRefreshFooter(tab,columnName,newValue,type)) table.columns().footer().draw(); 
		//dependence columns
		actions.dominoColumns(columnName,newValue,tab,rowData,collection,table,td);
	},
	
	isRefreshFooter			:	function(tab,columnName,newValue,type){return false},
	extensionHandle			:	function(tab,columnName,rowData,limit,successFunction){},
	deleteRowFunction		:	function(table,aRowData,tab){
		var id = aRowData['DT_RowId'];
		var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var rowData;
		if (typeof id === 'string' && typeof table == "object" && typeof table.row == "function")
			rowData 	= table.row('#'+id).data();
		if (rowData === undefined) rowData = aRowData;
		
		var recordData 	= actions.deleteData;
   		if (!(tab in recordData)) recordData[tab] = [];
    	//remove in postdata
    	var eData = recordData[tab];
    	if(isAdding) {
	    	
	   	}
    	else{
    		deleteObject = actions.initDeleteObject(tab,id,rowData);
	    	eData.push(deleteObject);
    	}
    	var editedData = actions.editedData[tab];
    	if(editedData !== undefined && editedData != null){
    		var result = $.grep(editedData, function(e){ 
           	 	return e[actions.type.keyField] == rowData[actions.type.keyField];
            });
		    if (result.length > 0) {
		    	editedData.splice( $.inArray(result[0], editedData), 1 );
		    	actions.editedData[tab]	= editedData;
		    }
    	}
        	//remove on table
    	if (typeof id === 'string' && typeof table == "object" && typeof table.row == "function")
    		table.row('#'+id).remove().draw( false );
	},
	createdFirstCellColumnByTable : function(table,rowData,td,tab){
		var id = rowData['DT_RowId'];
		var deleteFunction = function(){
			actions.deleteRowFunction(table,rowData,tab);
			var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
			if(!isAdding && typeof actions.contraintEloquents == "object" && actions.contraintEloquents.length>0
					&& $.inArray(tab, actions.contraintEloquents) >= 0){
				for (var i = 0; i < actions.contraintEloquents.length; i++) {
					var otherTabName 	= actions.contraintEloquents[i];
					if(otherTabName!=tab && typeof actions.getContraintEloquentKeyData == "function"){
						var otherTable;
						if ( $.fn.DataTable.isDataTable( '#table_'+otherTabName) ) {
							  otherTable 	= $('#table_'+otherTabName).DataTable();
						}
						var otherRowData	= actions.getContraintEloquentKeyData(tab,rowData,otherTabName,otherTable);
						if(otherRowData.length>0){
							$.each(otherRowData, function( i, oRowData ) {
								actions.deleteRowFunction(otherTable,oRowData,otherTabName);
							});
						}
					}
				}
			}
		}
//		$(td).find('#delete_row_'+id).click(deleteFunction);
		table.$('#delete_row_'+id).click(deleteFunction);

		var editFunction = function(e){
			e.preventDefault();
//			var r = table.fnGetPosition(td)[0];
//		    var rowData = table.api().data()[ r];
		    var rowData = table.row('#'+id).data();
		    editBox.editRow(id,rowData);
		};
//		$(td).find('#edit_row_'+id).click(editFunction);
		table.$('#edit_row_'+id).click(editFunction);
		if(typeof(actions.addMoreHandle) == "function")actions.addMoreHandle(table,rowData,td,tab);
	},
	dominoColumnSuccess	:	function(data,dependenceColumnNames,rowData,tab){
		var DT_RowId 	= rowData.DT_RowId;
		$.each(dependenceColumnNames, function( i, dependence ) {
			var dependencetd = $('#'+DT_RowId+" ."+dependence);
			if(data.dataSet[dependence]!==undefined){
				dataSet = data.dataSet[dependence].data;
				if(typeof actions.loadCustomObjectName=="function") dataSet = actions.loadCustomObjectName(dependence,dataSet,rowData);
				if(typeof(dataSet) == "object" &&dataSet !=null &&dataSet.length>0){
					sourceColumn = data.dataSet[dependence].sourceColumn;
					ofId = data.dataSet[dependence].ofId;
					cellData	=	actions.getPropertyValue(dataSet[0],"ID");
					rowData[dependence] = cellData;
					if(typeof(actions.extraDataSet[sourceColumn]) == "undefined"){
						actions.extraDataSet[sourceColumn] = [];
					}
					actions.extraDataSet[sourceColumn][ofId] = data.dataSet[dependence].data;
					var editableType = actions.getEditableType(tab,dependence);
					actions.applyEditable(tab,editableType,dependencetd, cellData, rowData, dependence,dataSet);
					actions.putModifiedData(tab,dependence,cellData,rowData);
//				createdFirstCellColumnByTable(table,rowData,dependencetd,tab);
					return;
				}
			}
			$(dependencetd).editable("destroy");
			$(dependencetd).editable("disable");
		});
		console.log ( "success dominoColumns "+data );
	},
	getEditableType : function(tab,columnName){
		return "select";
	},
	getKeyFieldSet : function(tab){
		if(typeof(actions.type.idName) == "function"){
			return actions.type.idName(tab);
		}
		return actions.type.idName;
	},
	putModifiedData : function(tab,columnName,newValue,rowData,type){
		var table = $('#table_'+tab).dataTable();
		var id = rowData['DT_RowId'];
		var isAdding = (typeof id === 'string') && (id.indexOf('NEW_RECORD_DT_RowId') > -1);
		var recordData = actions.editedData;
    	if (!(tab in recordData)) {
    		recordData[tab] = [];
    	}
    	var eData = recordData[tab];
    	var result = $.grep(eData, function(e){ 
						               	 return e[actions.type.keyField] == rowData[actions.type.keyField];
						                });
//    	var columnName = table.settings()[0].aoColumns[col].data;
    	if (newValue!=null) {
    		if (newValue.constructor.name == "Date") {
        		newValue = actions.getTimeValueBy(newValue,columnName,tab);
    		}
    		else if(type == "number" && typeof newValue == "string") {
        		newValue = parseFloat(newValue.replace(',','.'));
        		newValue = isNaN(newValue)?null:newValue;
    		}
		}
    	if(typeof newValue == "string" && newValue.trim() =="") newValue = null;
    	if (result.length == 0) {
        	var editedData = {};
        	idName = actions.getKeyFieldSet(tab);
        	 $.each(idName, function( i, vl ) {
        		 if(typeof rowData[vl]!="undefined") editedData[vl] = rowData[vl];
             });
        	editedData[columnName] = newValue;
        	if(isAdding) {
        		editedData['isAdding'] = true;
        	}
        	editedData['DT_RowId'] = rowData['DT_RowId'];
    		eData.push(editedData);
    	}
    	else{
    		result[0][columnName] = newValue;
    	}
    	return rowData;
	},
	getTimeValueBy : function(newValue,columnName,tab){
		if(columnName=='EFFECTIVE_DATE'||columnName=='OCCUR_DATE'){
			return moment.utc(newValue).format(configuration.time.DATE_FORMAT_UTC);
//			return moment.utc(newValue).format("YYYY-MM-DD HH:mm:ss");
		}
		return moment(newValue).format(configuration.time.DATETIME_FORMAT_UTC);
//		return moment(newValue).format("YYYY-MM-DD HH:mm:ss");
	},
	getDecimalNumber : function(property,rowData){

		var decimalNumber	= 2;
		var objectRules	= actions.getObjectRules(property,rowData);
		if(objectRules!=null&&typeof(objectRules) == "object"&& typeof(objectRules.basic)=="object"&&typeof(objectRules.basic.VALUE_FORMAT) == "string" && objectRules.basic.VALUE_FORMAT!=""){
			var splits = objectRules.basic.VALUE_FORMAT.split(".");
			if(splits.length>1) {
				decimalNumber	= splits[1].length;
				return decimalNumber;
			}
		}
			
		var objectExtension	= property.OBJECT_EXTENSION;
		if(objectExtension!=null&&objectExtension!=""&&rowData!==undefined){
			try {
				var objects = $.parseJSON(objectExtension);
				var objectId = rowData[actions.type.idName[0]];
				var extension = objects[objectId];
				if(typeof(extension) == "object"){
					if(typeof(extension.advance) == "object"
						&&(extension.advance.KEEP_DISPLAY_VALUE==true||extension.advance.KEEP_DISPLAY_VALUE=="true")){
						decimalNumber = -1;
						return decimalNumber;
					}
					else if(typeof(extension.basic) == "object"
						&&typeof extension.basic.VALUE_FORMAT == "string" && extension.basic.VALUE_FORMAT!=""){
						var splits = extension.basic.VALUE_FORMAT.split(".");
						if(splits.length>1) {
							decimalNumber	= splits[1].length;
							return decimalNumber;
						}
					}
				}
			}
			catch(err) {
			    console.log("can not parse ] objectExtension +\n"+err.message);
			}
		}
		if(typeof property.VALUE_FORMAT == "string" && property.VALUE_FORMAT!=""){
			var splits = property.VALUE_FORMAT.split(".");
			if(splits.length>1) decimalNumber	= splits[1].length;
		}
		return decimalNumber;
	},
	getCellType : function(data,type,cindex){
		type = typeof type != "object" && actions.extraDataSetColumns.hasOwnProperty(data.properties[cindex].data)?'select':type;
		return type;
	},
    getCurrentFacilityId : function(){
        var activeTabID = getActiveTabID();
        var postData = actions.loadedData[activeTabID];
        return postData && postData.Facility ?postData.Facility : 0;
    },
	getObjectRules : function(property,rowData){
		var rules;
		var objectExtension	= typeof property !="undefined" ? property.OBJECT_EXTENSION:null;
		if(objectExtension!=null&&objectExtension!=""){
			try {
			    var objects = $.parseJSON(objectExtension);
			    var objectId = rowData[actions.type.idName[0]];
			    switch (objects.version) {
					case 2:
                    case '2':
                    	var facilityId = actions.getCurrentFacilityId();
                    	if (objects[facilityId]){
                    		if (objects[facilityId][objectId]) rules = objects[facilityId][objectId];
                    		else if (objects[facilityId][0]) rules = objects[facilityId][0];
						}
						break;
					default:
                        rules = objects[objectId];
						break;
				}
			}
			catch(err) {
			    console.log("can not parse ] objectExtension +\n"+err.message);
			}
		}
		return rules;
	},
	checkEdittableWithRules	: function(objectRules,isEdittable){
		if(typeof(objectRules) == "object"&& objectRules!=null && typeof objectRules.basic=="object"){
			if(objectRules.OVERWRITE==true||objectRules.OVERWRITE=="true"){
				return objectRules.basic.DATA_METHOD==1||objectRules.basic.DATA_METHOD=='1';
			}
		}
		return isEdittable;
	},

	createCommonCell	: function(td,data,type,property,rowData){
		colName 			= property.data;
		$(td).addClass( "contenBoxBackground");
		if(typeof type == "string") $(td).addClass( "cell"+type );
		$(td).addClass( colName );
		var isEdittable = !data.locked&&actions.isEditable(property,rowData,data.rights);
//		if(isEdittable) $(td).addClass( "editInline" );
		return isEdittable;
	},
	
	getCellProperty : function(data,tab,type,cindex){
		var cell = {"targets"	: cindex};
		type = actions.getCellType(data,type,cindex);
		var property 		= data.properties[cindex];
		const columnName 	= property.data;
		if(typeof type == "function") {
			cell["createdCell"] = type;
			return cell;
		}
		cell["createdCell"] 	= function (td, cellData, rowData, row, col) {
			/*rowData.DT_RowId	= (typeof rowData.DT_RowId) == "undefined" || rowData.DT_RowId ==null || rowData.DT_RowId ==""?
									Math.random().toString(36).substring(10):
									rowData.DT_RowId;*/
									
			var property 		= data.properties[col];
			var isEdittable		= actions.createCommonCell(td,data,type,property,rowData);
			if (type=='checkbox') {
				$(td).click(function(){
					var val = rowData[columnName]==""?false:rowData[columnName];
					val		= !val;
	 				var fn = actions.getEditSuccessfn(property,tab,td, rowData, columnName,collection);
 					fn(null,val?1:0);
 				});
 				return;
			};

			var objectRules		= actions.getObjectRules(property,rowData);
			if(objectRules!=null&&typeof(objectRules) == "object"&& typeof objectRules.advance=="object"){
				//TODO more ruless
				$(td).css("background-color","#"+objectRules.advance.COLOR);
			}
			
			isEdittable = actions.checkEdittableWithRules(objectRules,isEdittable);
			if (isEdittable) isEdittable = actions.isEditableByRight(rowData,data.rights);
			
			$(td).removeClass( "editInline" );
 			if(isEdittable){
 				$(td).addClass( "editInline" );
 	        	var table = $('#table_'+tab).DataTable();
 	        	collection = null;
 	        	if(type=='select'){
 	        		collection = actions.getExtraDataSetColumn(data,cindex,rowData);
 	        	}
 				actions.applyEditable(tab,type,td, cellData, rowData, property,collection);
 			}
 			else if (type=='number'&&actions.historyUrl) {
 				$(td).click(function(e){
 					var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
 					if( $('#'+hid).length ){
 					}
 					else {
 						$("#more_actions").html("");
 						var extensionButton = $("<div class=\"extension-buttons\"><img src=\"/common/css/images/hist.png\" height=\"16\" class=\"editable-extension\"></div>");
 						extensionButton.css("display","block");
 						extensionButton.attr( 'id', hid);
 						extensionButton.attr('tabindex',-1);
 						extensionButton.blur(function() {
 							var hid ='eb_' +tab+"_"+rowData.DT_RowId+"_"+columnName;
 							$("#" +hid).remove();
 						});
 						extensionButton.click(function(e){
 							actions.extensionHandle(tab,columnName,rowData,false,null,true);
//				 		    		$("#" +hid).remove();
 						});
 						$("#more_actions").append(extensionButton);
 						extensionButton.focus();
 					}
 				});
 			}
 			
 			if(type=='number'){
        		var basicRules		= actions.getBasicRules(property,objectRules);
        		var originColor		= $(td).css('background-color');
        		actions.addCellNumberRules(td,basicRules,cellData,rowData,originColor,"loading");
        	}
		};

		if(typeof type == "object" && type!=null && typeof type.render == "function") {
			cell["render"] = type.render(data,cindex);
			return cell;
		}
		
		switch(type){
		case "text":
		case "color":
		case "textarea":
			if(columnName=='UOM'){
				cell["render"] = function ( data2, type2, row ) {
					if (data2==null||data2=='') return "&nbsp";
					var rendered = data2;
					if(data2==null){
						rendered = row.DEFAULT_UOM;
					}
					return rendered;
				};
			}
			else cell["render"] = function ( data2, type2, row ) {
									if (data2==null||data2=='') return "&nbsp";
									if(type=="textarea")data2 = data2.replace(/(?:\r\n|\r|\n)/g, '<br />');
									return data2;
								};
	    	break;
		case "number":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
								var value = actions.getNumberRender(columnName,data,data2, type2, row);
								if(value==null){
									value = data2;
									if(data2!=null&&data2!=''){
										var pvalue = parseFloat(data2);
										if(isNaN(pvalue)) return '';
										value	= actions.formatNumberDecimal(pvalue,property,row);
									}
								}
								return value;
							};
	    	break;
		case "date":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
								return actions.renderDateFormat(data2,type2,row);
							};
	    	break;
		case "datetimepicker":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
								if(typeof data2 == "string") moment.utc(data2,configuration.time.DATETIME_FORMAT_UTC).format(configuration.time.DATETIME_FORMAT);
								return moment(data2).format(configuration.time.DATETIME_FORMAT);
							};
	    	break;
		case "timepicker":
			cell["render"] = function ( data2, type2, row ) {
								if (data2==null||data2=='') return "&nbsp";
								if (data2.constructor.name == "Date") { 
									return moment(data2).format(configuration.time.TIME_FORMAT);
//									return moment(data2).format("hh:mm A");
								}
								return moment.utc(data2,configuration.time.TIME_FORMAT_UTC).format(configuration.time.TIME_FORMAT);
//								return moment(data2,"hh:mm:ss").format("hh:mm A");
							};
	    	break;
		case "checkbox":
//			cell["className"] = 'select-checkbox';

			cell["render"] = function ( data2, type2, row ) {
//								if (data2==null||data2=='') return "&nbsp";
								checked = data2&&data2=="1"||data2==true||data2=="true"?'checked':'';
				 				var disabled = data.locked||!(actions.isEditable(data.properties[cindex],row,data.rights));
				 				disabled = disabled?"disabled":''; 
								return '<div  class="checkboxCell" ><input '+disabled+' class="cellCheckboxInput" type="checkbox" value="'+data2+'"size="15" '+checked+'></div>';
							};
	    	break;
		case "select":
			cell["render"] = function ( data2, type2, row ) {
					if (data2==null||data2=='') return "&nbsp";
 	        		collection = actions.getExtraDataSetColumn(data,cindex,row);
 	        		cellDataText = actions.getValueTextOfSelect(collection,data2,columnName,row);
					return cellDataText;
				};
	    	break;
	    	
		case "EVENT":
			cell["render"] = function ( data2, type2, row ) {
				if(typeof actions.renderEventConfig == "function") return actions.renderEventConfig( columnName,data2, type2, row);
				return "config";
			};
	    	break;

		}
		return cell;
	},

	formatNumberDecimal : function (value,property,row) {
		var decimalNumber 	= actions.getDecimalNumber(property,row);
		if(decimalNumber>=0&&value!=null){
			if(configuration.number.DECIMAL_MARK=='comma') 
				value = value.toLocaleString('de-DE',{ minimumFractionDigits: decimalNumber,maximumFractionDigits : decimalNumber });
			else 
				value = value.toLocaleString("en-US",{ minimumFractionDigits: decimalNumber,maximumFractionDigits : decimalNumber});
			
		}
		return value;
	},
	
	getValueTextOfSelect : function (collection,data2,columnName,row) {
		if(collection!=null){
 			var result = $.grep(collection, function(e){
 				if(typeof(e) !== "undefined"){
 					if(e.hasOwnProperty('ID')) {
 						return e['ID'] == data2;
 					}
 					if(e.hasOwnProperty('id')) {
 						return e['id'] == data2;
 					}
 					else if(e.hasOwnProperty('value')) {
 						return e['value'] == data2;
 					}
 				}
				return false;
 			});
 			if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined"){
 				if(result[0].hasOwnProperty('NAME')){
 	 				return result[0]['NAME'];
 	 			}
 				else if(result[0].hasOwnProperty('name')){
 	 				return result[0]['name'];
 	 			}
 				else if(result[0].hasOwnProperty('TEXT')){
 	 				return result[0]['TEXT'];
 	 			}
 				else if(result[0].hasOwnProperty('text')){
 	 				return result[0]['text'];
 	 			}
 			}
 		}
		return "&nbsp";
	},
	createdFirstCellColumn : function (td, cellData, rowData, row, col) {
		$(td).css('z-index','1');
	},
	getRenderFirsColumnFn : function (tab) {
		return actions.renderFirsColumn;
	},
	getGrepValue : function (data,value,row) {
						return data;
	},
	notUniqueValue : function(uom,rowData){
		return true;
	},
	isShownOf : function(value,postData){
		return true;
	},
	getNumberRender: function(columnName,data,data2, type2, row){
		return null;
	},
	getExtendWidth: function(data,autoWidth,tab){
		return 0;
	},
	enableUpdateView : function(tab,postData){
		return true;
	},
	getTableWidth: function(data,autoWidth,tab,options){
		var  	marginLeft = 0;
		var 	padding = 18;
		var  	tblWdth = 0;
		var		invisible	= typeof options.invisible == "object" &&options.invisible!=null?options.invisible:[];
		$.each(data.properties, function( ip, vlp ) {
			if(autoWidth){
				delete vlp['width'];
			}
			else{
				if(invisible.indexOf(vlp.data) > -1) return;
				if(ip==0){
//  				vlp['className']= 'headcol';
					marginLeft = vlp['width'];
				}
				var iw = (typeof vlp['width'] == "string" ?parseFloat(vlp['width']):vlp['width']);
				iw = isNaN(iw)||iw<=0?100:iw;
				if(ip!=0&&(vlp.title==null||vlp.title=='')) {
//					iw = iw*1.5;
					vlp.title=vlp.data;
				}
				tblWdth+=iw+2*padding;
				vlp['width']= iw+"px";
			}
        });
		tblWdth+=2*padding;
		extendWidth = actions.getExtendWidth(data,autoWidth,tab);
		return tblWdth+extendWidth;
	},
	getTableHeight:function(tab){
		headerOffset = $('#tabs').offset();
		var bonus = $('#ebTabHeader').is(':visible')?100:66;
		hhh = $(document).height() - (headerOffset?(headerOffset.top):0) - $('#ebTabHeader').outerHeight() - $('#ebFooter').outerHeight() - bonus;
		tHeight = ""+hhh+'px';
		return tHeight;
	},
	getTableOption: function(data,tab){
		return {tableOption :{searching: true},
				invisible:[]};
		
	},
	addClass2Header: function(table){
		var columns = table.settings()[0].aoColumns;
		$.each(columns, function( index, column ) {
			var header = table.columns(index).header();
        	var columnName = column.data;
        	$(header).addClass(columnName);
	   	});
		
	},
	getUomCollection: function(collection,columnName,cellData, rowData){
		return collection;
	},
	
	setTableWidth: function(tab,tblWdth){
		if(tblWdth < ($(window).width()-50)){
			$('#container_'+tab).css('min-width',(tblWdth+40)+'px');
	 		$('#container_'+tab).css('width',(tblWdth+40)+"px");
		}
		else {
			$('#container_'+tab).css('min-width',($(window).width()-50)+'px');

			$('#container_'+tab).css('width',($(window).width()-50)+"px");
		}
	},
	
	addTabIndex: function(td,type){
		if(typeof tabIndex == "undefined") 
			tabIndex = 10000;
		else
			tabIndex++;
		if($(td).attr('tabindex') === undefined || $(td).attr('tabindex') == -1) $(td).attr('tabindex', tabIndex);
	},
	
	initTableOption : function (tab,data,options,renderFirsColumn,createdFirstCellColumn){
		$.each(data.properties, function( ip, vlp ) {
			var i = vlp.title?vlp.title.indexOf('#list:'):-1;
			if(i>=0){
				var vlist = vlp.title.substr(i+6).split(';');
				vlp.title = vlp.title.substr(0, i);
				var vdata = [{ID: '', NAME: ''}];
				vlist.forEach(function(vl){
					var vls = vl.split(':');
					vdata.push({ID: vls.length>1?vls[1]:vls[0], NAME: vls[0]});
				});
				data.uoms.push({
					"id": "list_" + vlp.data,
					"targets": ip,
					"COLUMN_NAME": vlp.data,
					"data": vdata
				});
			}
		});
		if(typeof(data.uoms) == "undefined"||data.uoms==null){
			data.uoms = [];
		}
		var uoms = data.uoms;
		var invisible = options!=null&&(typeof(options.invisible) !== "undefined"&&options.invisible!=null)?options.invisible:null;
		var exclude = [0];
		if(typeof(renderFirsColumn) == "function"){
			exclude = [0];
		}
		else exclude = [];

		if(typeof(uoms) !== "undefined"&&uoms!=null){
			$.each(uoms, function( index, value ) {
				exclude.push(uoms[index]["targets"]);
				var collection = value['data'];
				if(value==null||!value.hasOwnProperty('render')) {
					uoms[index]["render"] = function ( data, type, row ) {
						var result = $.grep(collection, function(e){
							id = actions.getGrepValue(data,value,row);
							if(typeof e['ID'] != "undefined") return e['ID'] == id;
							else return e['id'] == id;
						});
						if(typeof(result) !== "undefined" && typeof(result[0]) !== "undefined"){
							if(result[0].hasOwnProperty('NAME')){
								return result[0]['NAME'];
							}
							else if(result[0].hasOwnProperty('name')){
								return result[0]['name'];
							} 
						}
						return data;
					};
				}
	            $.each(collection, function( i, vl ) {
	            	vl['value']	= typeof(vl['ID']) !== "undefined"?vl['ID']:vl['id'];
	            	vl['text']	= typeof(vl['NAME']) !== "undefined"?vl['NAME']:vl['name'];
	            });
	            uoms[index]["createdCell"] = function (td, cellData, rowData, row, col) {
	            	var property = data.properties[col];
	            	columnName = property.data;
	 				$(td).addClass( columnName );
	            	if(!data.locked&&actions.isEditable(data.properties[col],rowData,data.rights)&&actions.notUniqueValue(uoms[index],rowData)){
	            		actions.addTabIndex(td,'select');
		 				$(td).editable({
			        	    type: 'select',
			        	    title: 'edit',
				    	    emptytext: '&nbsp;',
			        	    value:cellData,
			        	    showbuttons:false,
			        	    source: actions.getUomCollection(collection,columnName,cellData, rowData),
			        	    success: actions.getEditSuccessfn(property,tab,td, rowData, columnName,collection),
			        	});
		 			}
	   			}
	            if(invisible!=null&&$.inArray(data.properties[index].data, invisible)>=0){
	            	uoms[index]['visible']=false;
				}
			});
		}

		var original = Array.apply(null, Array(data.properties.length)).map(function (_, i) {return i;});
		var finalArray = $(original).not(exclude).get();
		if(data.hasOwnProperty('extraDataSet')){
			actions.extraDataSet = data.extraDataSet;
		}
		$.each(finalArray, function( i, cindex ) {
			var inputType = typeof data.properties[cindex].INPUT_TYPE != "undefined"?
					data.properties[cindex].INPUT_TYPE:data.properties[cindex].input_type;
			var type = typetoclass(inputType);
			var cell = actions.getCellProperty(data,tab,type,cindex);
			if(invisible!=null&&$.inArray(data.properties[cindex].data, invisible)>=0){
				cell['visible']=false;
			}
    		uoms.push(cell);
        });
		
		if(typeof(renderFirsColumn) == "function"){
			var phase = {"targets": 0,
					"render": renderFirsColumn,
			};
			if(createdFirstCellColumn!=null) phase["createdCell"] = createdFirstCellColumn;
			uoms.push(phase);
		}
		
		var autoWidth = false;
		if( options!=null&&
				(typeof(options.tableOption) !== "undefined"&&
						options.tableOption!=null)&&
						(typeof(options.tableOption.autoWidth) !== "undefined"&&
								options.tableOption.autoWidth!=null)){
			autoWidth = options.tableOption.autoWidth;
		}
		
		var tblWdth = actions.getTableWidth(data,autoWidth,tab,options);
		actions.setTableWidth(tab,tblWdth);
		
		tHeight = actions.getTableHeight(tab);
//		uoms.push( { "orderable": false, "targets": 0 });
		option = {data: data.dataSet===undefined?[]:data.dataSet,
				 "order": [],
		          columns: data.properties,
		          destroy: true,
		          "columnDefs": uoms,
		          "scrollX": true,
		         "autoWidth": false,
//		         "autoWidth": autoWidth,
//		       	"scrollY":        "37vh",
//		         "scrollY":        "250px",
		       	scrollY:        tHeight,
//		                "scrollCollapse": true,
				"paging":         false,
				"dom": '<"#status_'+tab+'">rt<"#toolbar_'+tab+'">p<"bottom"i><"bottom"f><"clear">',
				drawCallback	: function ( settings ) { 
			        var table = $('#table_'+tab).DataTable();
			        $('#table_'+tab+' tbody').on( 'click', 'tr', function () {
		                table.$('tr.selected').removeClass('selected');
			            if ( $(this).hasClass('selected') ) {
//			                 $(this).removeClass('selected');
			            }
			            else {
			                $(this).addClass('selected');
			            }
			        } );
			        
			        actions.addClass2Header(table);
			    },
			    language: {
		            "info": "Showing _TOTAL_ entries",
		        },
				/* initComplete: function () {
					var cls = this.api().columns();
		            cls.every( function () {
		                var column = this;
		                var ft = $(column.footer());
		                ft.html("keke");
		                var select = $('<select><option value=""></option></select>')
		                    .appendTo( $(column.footer()).empty() );
		            } );
		        }, */
		        /* "footerCallback": function ( row, data, start, end, display ) {
		            var cls = this.api().columns();
		            cls.every( function () {
		                var column = this;
		                var ft = $(column.footer());
		                ft.html("keke");
		            } );
		        }, */
//				 "dom": '<"top"i>rt<"bottom"flp><"clear">'
//		           paging: false,
//		          searching: false 
		    };
		    
	    if (options!=null) {
            if(typeof options.resetTableHtml == 'function' && options.resetTableHtml(tab))
            	$('div#container_'+tab ).html('<table border="0" cellpadding="3" id="table_'+tab +'" class="fixedtable nowrap display"></table>');
            else
				if(typeof(options.tableOption) !== "undefined"&&options.tableOption!=null){
					jQuery.extend(option, options.tableOption);
					if(options.tableOption.hasOwnProperty('emptyTable')&&options.tableOption.emptyTable) {
						 $('#container_'+tab ).html('<table border="0" cellpadding="3" id="table_'+tab +'" class="fixedtable nowrap display"></table>');
					}
				}
		}
		
	    /*isDestroyTable = typeof(options.destroy) !== "undefined"&&options.destroy;
	    if (isDestroyTable) {
	    	 $('#table_'+tab).DataTable(option);
	    }*/

		var tbl = $('#table_'+tab).DataTable(option);
		
		return tbl;
	},
	getExistRowId		: function(value,key){
		return value[actions.type.saveKeyField(key)];
	}
}
	