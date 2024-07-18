var object_id;
var list_arr = [];
var objects = readData('objects');
var object_attrs = readData('object_attrs');
var object_details = getObjectDetails();
var point_id = readParameter('point_id');
var obj_type = readParameter('object_type');
var object_types = readData('object_types');
var lists = readData('lists');
var points = readData('points');
var point = points['P_'+point_id];
var editted = '_e';
var chart;

function bindSwipeEvent(){
	//$('#objs_container').css('width',$(window).width()-126);
	$("select").parent().on( "swipeleft", function(e){
		var id=$(this).attr('id');
		prevObject(id.substr(0,id.length-7));
	} );
	$("select").parent().on( "swiperight", function(e){		
		var id=$(this).attr('id');
		nextObject(id.substr(0,id.length-7));
	} );
}

window.onresize = function(event) {
	//$('#objs_container').css('width',$(window).width()-126);
};

function zeroFill( number, width )
{
  width -= number.toString().length;
  if ( width > 0 )
  {
    return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
  }
  return number + ""; // always return a string
}

function renderInputControl(ctl_type, label, name, id, value) {
    var html = '';
    switch (ctl_type) {
        case 'n':
            html = '<label field="'+name+'" onclick="showChart(this)" class="clickable">' + label + '</label><div class="ui-input-text ui-body-inherit ui-corner-all ui-shadow-inset"><input type="number" name="' + name + '" id="' + id + '" value="' + value + '"></div>';
            break;
        case 't':
            html = '<label>' + label + '</label><div class="ui-input-text ui-body-inherit ui-corner-all ui-shadow-inset"><input type="text" name="' + name + '" id="' + id + '" value="' + value + '"></div>';
            break;
        case 'd':
			if(value==""){
				var d=new Date();
				value = d.getFullYear() + "-" + zeroFill(1 + d.getMonth(), 2) + "-" + zeroFill(d.getDate(), 2);

			}
            html = '<label>' + label + '</label><div class="ui-input-text ui-body-inherit ui-corner-all ui-shadow-inset"><input type="date" name="' + name + '" id="' + id + '" value="' + value + '"></div>';
            break;
        case 'l':
			var opts = "";
			for(var opt in lists[value])
				opts += "<option value='"+opt+"'>"+lists[value][opt]+"</option>";
            html = '<label>' + label + '</label><select name="' + name + '" id="' + id + '">' + opts + '</select>';
            break;
    }
    return html;
}

function renderObjectList() {
    var html = '';
	var selected='selected';
	var count=0;
    for (var i=0;i<point.objects.length;i++) {
		var obj = objects[point.objects[i]];
        if (obj.type == obj_type) {
            html = html + '<option value="' + obj.id + '"'+selected+'>' + obj.name + '</option>';
			selected='';
			count++;
        }
    }
	$("#path").html(object_types[obj_type]+" <span id='obj_nav'>1</span>/"+count);
	
	if(html=='') return;
    $("#drp_objects").html(html);
    //$('#drp_objects').change();
	$("#drp_objects").selectmenu('refresh');
    object_id = $('#drp_objects').val();
}

function setListEventPhase(update){
	var listEventPhase={};//{'1_2':'Injecting - Oil','2_1','Producing - Gas'};
	var object = objects[obj_type+'_'+object_id];
	for(var event_type in object.event_phases) if(lists['CODE_EVENT_TYPE'][event_type]!==undefined) {
		var flow_phases = object.event_phases[event_type];
		for(var i=0;i<flow_phases.length;i++) if(lists['CODE_FLOW_PHASE'][flow_phases[i]]!==undefined) {
			var key=flow_phases[i]+'_'+event_type;
			var text = lists['CODE_EVENT_TYPE'][event_type]+' - '+lists['CODE_FLOW_PHASE'][flow_phases[i]];
			listEventPhase[key] = text;
		}
	}
	lists['EVENT_PHASE'] = listEventPhase;
	if(update){
		var opts = "";
		for(var opt in listEventPhase)
			opts += "<option value='"+opt+"'>"+listEventPhase[opt]+"</option>";
		$("#id_EVENT_PHASE").html(opts);
		$("#id_EVENT_PHASE").selectmenu('refresh');
	}
}

function renderProperty() {
    var html = '';
    html = html + renderInputControl('d', 'Occur date', 'OCCUR_DATE', "id_OCCUR_DATE", '');
	if(obj_type == 'EU'){
		setListEventPhase();
		html = html + renderInputControl('l', 'Operation', 'EVENT_PHASE', "id_EVENT_PHASE", 'EVENT_PHASE');
        list_arr.push("id_EVENT_PHASE");
	}
    for (var i=0; i<object_attrs[obj_type].length; i++) {
		var fld = object_attrs[obj_type][i].field;
        var value = '';
        if (object_attrs[obj_type][i].control_type == 'l') {
            list_arr.push("id_" + fld);
            value = object_attrs[obj_type][i].list;
        }
        html = html + renderInputControl(object_attrs[obj_type][i].control_type, object_attrs[obj_type][i].name, fld, "id_" + fld, value);
    }
    $("#details").html(html);
    for (var i=0; i<object_attrs[obj_type].length; i++) {
		var fld = object_attrs[obj_type][i].field;
		if(object_attrs[obj_type][i].control_type=='n'){
			$("#id_" + fld).keydown(function (e) {
				// Allow: backspace, delete, tab, escape, enter and .
				if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
					 // Allow: Ctrl/cmd+A
					(e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
					 // Allow: Ctrl/cmd+C
					(e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
					 // Allow: Ctrl/cmd+X
					(e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
					 // Allow: home, end, left, right
					(e.keyCode >= 35 && e.keyCode <= 39)) {
						 // let it happen, don't do anything
						 return;
				}
				// Ensure that it is a number and stop the keypress
				if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
					e.preventDefault();
				}
			});	
		}
    }
}

function getKey(){
	//return obj_type+"_"+object_id+"_"+$("#id_OCCUR_DATE").val()+(obj_type=='EU'?'_'+$("#id_FLOW_PHASE").val()+'_'+$("#id_EVENT_TYPE").val():'');
	return obj_type+"_"+object_id+"_"+$("#id_OCCUR_DATE").val()+(obj_type=='EU'?'_'+$("#id_EVENT_PHASE").val():'');
}

function bindData() {
	var key = getKey();
    for (var i=0; i<object_attrs[obj_type].length; i++) {
		var fld = object_attrs[obj_type][i].field;
        if (fld == 'id' || fld == 'OCCUR_DATE' || fld == 'FLOW_PHASE' || fld == 'EVENT_TYPE') continue;
        $("#id_" + fld).val(object_details[key]==undefined?'':object_details[key][fld]);
    }
}

function save() {
    var key = getKey();
	if(object_details[key] == undefined)
		object_details[key] = {};
	var isValueChanged = false;
    for (var i=0; i<object_attrs[obj_type].length; i++) {
		var fld = object_attrs[obj_type][i].field;
        if (fld == 'id' || fld == 'OCCUR_DATE' || fld == 'FLOW_PHASE' || fld == 'EVENT_TYPE') continue;
		var v = $("#id_" + fld).val();
		//----- validate number ----------
		if(object_attrs[obj_type][i].mandatory && v===''){
			alert(object_attrs[obj_type][i].name+' must not be empty');
			$("#id_" + fld).select().focus();
			return false;
		}
		if(object_attrs[obj_type][i].control_type=='n'){
			var n=Number(v);
			if(isNaN(n)){
				alert(object_attrs[obj_type][i].name+' must be a number');
				$("#id_" + fld).select().focus();
				return false;
			}
		}
		//---------------------------------
		if(object_details[key][fld] != v){
			object_details[key][fld] = v;
			isValueChanged = true;
		}
    }
	if(isValueChanged){
		object_details[key][editted] = 1;
		saveData('object_details', object_details);
		alert("Data saved!");
	}
	else
		alert("Nothing changed");
	return true;
}

function reset() {
	if(!confirm('Do you really want to clear this data?'))
		return;
    var key = getKey();
	delete object_details[key];
    saveData('object_details', object_details);	

	var points = readData('points');
	var isChanged = false;
	for(var pnt in points)
		if(points[pnt].objects.indexOf(obj_type+'_'+object_id)>=0 && points[pnt].complete){
			points[pnt].complete = false;
			isChanged = true;
			break;
		}
	if(isChanged)
		saveData('points', points);
	
    for (var i=0; i<object_attrs[obj_type].length; i++) {
		var fld = object_attrs[obj_type][i].field;
        if (fld == 'id' || fld == 'OCCUR_DATE' || fld == 'FLOW_PHASE' || fld == 'EVENT_TYPE') continue;
        $("#id_" + fld).val(null);
    }
    for (i = 0; i < list_arr.length; i++)
		if(list_arr[i]!="id_FLOW_PHASE" && list_arr[i]!="id_EVENT_TYPE" && list_arr[i]!="id_EVENT_PHASE"){
			$("#" + list_arr[i]).selectmenu();
			$("#" + list_arr[i]).selectmenu('refresh');
		}
}

function changeObject() {
    object_id = $('#drp_objects').val()
    //renderProperty();
	setListEventPhase(true);
    bindData();
	updateNav();
}

function refeshSelectMenu() {
    for (i = 0; i < list_arr.length; i++) {
        $("#" + list_arr[i]).selectmenu(); // initialize
        $("#" + list_arr[i]).selectmenu('refresh');
    }
}

function registerEvents(){
	$("#id_OCCUR_DATE").change(bindData);
	$("#id_FLOW_PHASE").change(bindData);
	$("#id_EVENT_TYPE").change(bindData);
	$("#id_EVENT_PHASE").change(bindData);
}

function updateNav(){
	$("#obj_nav").html($("#drp_objects")[0].selectedIndex+1);
}

function prevObject(drp){
	$('#'+drp+' option:selected').prev().attr('selected', 'selected');
	$('#'+drp).change();
	updateNav();
}

function nextObject(drp){
	$('#'+drp+' option:selected').next().attr('selected', 'selected');
	$('#'+drp).change();
	updateNav();
}

function showChart(o){
	var field = $(o).attr('field');
	var key = obj_type+"_"+object_id+"_";
	var fe = $("#id_EVENT_PHASE").val();
	var l = key.length;
	var ids=[];
	for(var fld in object_details)
		if(fld.substr(0,l)===key)
			if(isValidNumber(object_details[fld][field])){
				if(obj_type=='EU'){
					if(fld.substr(l+11)==fe)
						ids.push(fld);
				}
				else
					ids.push(fld);
			}
	ids.sort();
	var chartData=[];
	for(var i=0;i<ids.length;i++){
		var ds=ids[i].split('_');
       var point = [];
       point.push(ds[2]);
       point.push(Number(object_details[ids[i]][field]));
       chartData.push(point);		
	}
	$("#chart").show();
	chart.series[0].setData(chartData);
	chart.setTitle({text: $(o).html()});
	$(window).trigger('resize');
}

$(document).ready(function () {
	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});
	var option	= {
		chart: {
			renderTo: 'chart_container',
			zoomType: 'xy',
		},
		credits: false,
		title: {text: 'Title'},
		subtitle: {
			text: null
		},
		xAxis: {
			type: "category",
			title: {text: 'Occur date'}
		},
		legend: {enabled: false},
		plotOptions: {},
		series: [{type: 'line',
			name: 'value',
			color: '#7e6de3',
			data: []}],
		};

	chart = new Highcharts.Chart(option);
    renderObjectList();
    renderProperty();
    bindData();
    refeshSelectMenu();
	registerEvents();
	bindSwipeEvent();
});
