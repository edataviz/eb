<?php
$currentSubmenu 		= '/objectsmanager';
$objectExtension 		= isset($objectExtension)?$objectExtension:[];
?>

@extends('core.bsconfig')
@section('content')
<script src="https://maps.googleapis.com/maps/api/js?libraries=drawing&key=AIzaSyCd5Djci7WNUX-pIbj1RvajAe-2CL3ali8"></script>
<script src="https://googlemaps.github.io/js-map-label/src/maplabel-compiled.js"></script>
<style type="text/css">
#objects_container{
	position:relative;
	float:right;
	height:100%;
	width:600px
}
#toolbar_actions{
	position:absolute;
	top:0px;
	right:10px;
	z-index:200;
}
.tabordion {
  color: #333;
  display: block;
  font-family: arial, sans-serif;
  margin: auto;
  position: relative;
  //padding:20px 10px 10px 10px;
  height:100%;
  box-sizing: border-box;
}

.tabordion input[name="sections"] {
  left: -9999px;
  position: absolute;
  top: -9999px;
}

.tabordion section {
  display: block;
}

.tabordion section label {
  //background: #ccc;
  border-bottom:1px solid #ffffff;
  cursor: pointer;
  display: block;
  font-size: 1.2em;
  font-weight: bold;
  padding: 15px 10px 15px 20px;
  position: relative;
  width: 140px;
  z-index:100;
}

.tabordion section article {
  display: none;
  left: 190px;
  min-width: 300px;
  //padding: 0 0 0 21px;
  position: absolute;  
  top: 29px;
  width: calc(100% - 200px);
  height: calc(100% - 29px);
  overflow: auto;
}
/* 
.tabordion section article:after {
  background-color: #ccc;
  bottom: 0;
  content: "";
  display: block;
  left:-229px;
  position: absolute;
  top: 0;
  width: 220px;
  z-index:1;
} */

.tabordion input[name="sections"]:checked + label { 
  //background: #f0f0f0;
  border-bottom:1px solid #378de5;
  color: #378de5;
}

.tabordion input[name="sections"]:hover + label { 
  background: #f0f0f0;
}

.tabordion input[name="sections"]:checked ~ article {
  display: block;
}
	#panel {
		position: absolute;
		//width: 200px;
		font-size: 13px;
		margin: 4px;
		left:880px;
		z-index:100;
	}

	#color-palette {
		//clear: both;
		float:right;
	}

	.color-button {
		width: 14px;
		height: 14px;
		font-size: 0;
		margin: 2px;
		float: left;
		cursor: pointer;
	}

	#delete-button {
		margin-left: 20px;
		float:right;
	}
	.item_row{
		
	}
	
	article a{
		cursor: pointer;
		display:block;
		//height:35px;
		//line-height:35px;
		text-decoration:none;
		color:#666;
		border-bottom:1px solid #e0e0e0;
		padding:10px;
	}
	article a:hover, article a:active{
		background: #f0f0f0;
		text-decoration:none;
		color:#378de5;
	}
	.item_selected{
		color:#378de5;
		border-bottom:1px solid #378de5;
	}
	.category_sel{
		font-size: 9pt;
		color: #378de5;
		font-weight:normal;
	}
	.category_count{
		display: block;
		float: right;
		border-radius:5px;
		padding:0px 3px;
		font-size:7pt;
		color:white;
		background:#888888;
		margin-top: 3px;
	}
</style>
<script type="text/javascript">
var map;
            var drawingManager;
            var selectedShape;
            var colors = ['#1E90FF', '#FF1493', '#32CD32', '#FF8C00', '#4B0082'];
            var selectedColor;
            var colorButtons = {};

            function clearSelection () {
                if (selectedShape) {
                    if (selectedShape.type !== 'marker') {
                        selectedShape.setEditable(false);
                        //selectedShape.setOptions({draggable: false});
                    }
                    
                    selectedShape = null;
                }
            }

            function setSelection (shape) {
				if(selectedShape == shape)
					return;
				if($(currentItem).hasClass("item_all")){
					if(shape.category != undefined && shape.obj_id != undefined){
						if(shape.category != current_category){
							current_category = shape.category;
							$("input#option"+current_category).prop('checked', true);
						}
						focusToObjectItem(shape.category, shape.obj_id);
					}
				}
				else{
					clearSelection();
					if (shape.type !== 'marker') {
						shape.setEditable(true);
						selectColor(shape.get('fillColor') || shape.get('strokeColor'));
						//shape.setOptions({draggable: true});
					}
					selectedShape = shape;
				}
            }

            function deleteSelectedShape () {
                if (selectedShape) {
                    selectedShape.setMap(null);
					delete selectedShape;
                }
            }

            function selectColor (color) {
                selectedColor = color;
                for (var i = 0; i < colors.length; ++i) {
                    var currColor = colors[i];
                    colorButtons[currColor].style.border = currColor != color ? '2px solid #789' : '2px solid #fff';
                }

                // Retrieves the current options from the drawing manager and replaces the
                // stroke or fill color as appropriate.
                var polylineOptions = drawingManager.get('polylineOptions');
                polylineOptions.strokeColor = color;
                drawingManager.set('polylineOptions', polylineOptions);

                var rectangleOptions = drawingManager.get('rectangleOptions');
                rectangleOptions.fillColor = color;
                rectangleOptions.strokeColor = color;
                drawingManager.set('rectangleOptions', rectangleOptions);

                var circleOptions = drawingManager.get('circleOptions');
                circleOptions.fillColor = color;
                circleOptions.strokeColor = color;
                drawingManager.set('circleOptions', circleOptions);

                var polygonOptions = drawingManager.get('polygonOptions');
                polygonOptions.fillColor = color;
                polygonOptions.strokeColor = color;
                drawingManager.set('polygonOptions', polygonOptions);
            }

            function setSelectedShapeColor (color) {
                if (selectedShape) {
                    if (selectedShape.type == google.maps.drawing.OverlayType.POLYLINE) {
                        selectedShape.set('strokeColor', color);
                    } else {
                        selectedShape.set('strokeColor', color);
                        selectedShape.set('fillColor', color);
                    }
                }
            }

            function makeColorButton (color) {
                var button = document.createElement('span');
                button.className = 'color-button';
                button.style.backgroundColor = color;
                google.maps.event.addDomListener(button, 'click', function () {
                    selectColor(color);
                    setSelectedShapeColor(color);
                });

                return button;
            }

            function buildColorPalette () {
                var colorPalette = document.getElementById('color-palette');
                for (var i = 0; i < colors.length; ++i) {
                    var currColor = colors[i];
                    var colorButton = makeColorButton(currColor);
                    colorPalette.appendChild(colorButton);
                    colorButtons[currColor] = colorButton;
                }
                selectColor(colors[0]);
            }

function setOverlayEditable(newShape, selected){
	if (newShape.type !== google.maps.drawing.OverlayType.MARKER) {
		// Switch back to non-drawing mode after drawing a shape.
		drawingManager.setDrawingMode(null);

		// Add an event listener that selects the newly-drawn shape when the user
		// mouses down on it.
		google.maps.event.addListener(newShape, 'mousedown', function (e) {
			setSelection(newShape);
		});
		google.maps.event.addListener(newShape, 'click', function (e) {
			if (e.vertex !== undefined) {
				if (newShape.type === google.maps.drawing.OverlayType.POLYGON) {
					var path = newShape.getPaths().getAt(e.path);
					path.removeAt(e.vertex);
					if (path.length < 3) {
						newShape.setMap(null);
					}
				}
				if (newShape.type === google.maps.drawing.OverlayType.POLYLINE) {
					var path = newShape.getPath();
					path.removeAt(e.vertex);
					if (path.length < 2) {
						newShape.setMap(null);
					}
				}
			}
		});
		if(selected) setSelection(newShape);
	}
	else {
		google.maps.event.addListener(newShape, 'mousedown', function (e) {
			setSelection(newShape);
		});
		if(selected) setSelection(newShape);
	}
}
function addOverlayToObject(o){
	if(currentItem != undefined && currentItem != null && current_category.length > 0){
		addOverlay(current_category, $(currentItem).data("obj_id"), o);
	}
}
function load() {
	$("#wraper").height($(window).height()-100);
	var myMapOptions = {
		zoom: 2,
		center: new google.maps.LatLng(0, 0),
		//disableDefaultUI: true,
		gestureHandling: 'greedy',
		mapTypeId: 'terrain'
	};
	map = new google.maps.Map(document.getElementById("map"),myMapOptions);
var polyOptions = {
					strokeOpacity: 0.8,
					strokeWeight: 2,
					fillOpacity: 0.35,
                    //editable: true,
                    draggable: true
                };
                // Creates a drawing manager attached to the map that allows the user to draw
                // markers, lines, and shapes.
                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.POLYGON,
                    markerOptions: {
                        draggable: true
                    },
                    polylineOptions: {
                        //editable: true,
                        draggable: true
                    },
                    rectangleOptions: polyOptions,
                    circleOptions: polyOptions,
                    polygonOptions: polyOptions,
                    map: map
                });

                google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {
                    var newShape = e.overlay;
                    newShape.type = e.type;
					setOverlayEditable(newShape, true);
					addOverlayToObject(newShape);
                });

                // Clear the current selection when the drawing mode is changed, or when the
                // map is clicked.
                google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
                google.maps.event.addListener(map, 'click', clearSelection);
                google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
				google.maps.event.addListener(map, 'bounds_changed', function() {
					if(!isMapReady){
						isMapReady = true;
						if(currentItem == null)
							itemClick($("#pu-0"), true);
					}
				});

                buildColorPalette();
}
var isMapReady = false;
var selected_ids = {};
function selectAll(category){
	$("#sel_"+category).html("( All )");
	selected_ids[category] = 0;
}

function save(){
	if(currentItem != null){
		var last_info = getMapObjectsConfig(currentItem);
		if(last_info!=$(currentItem).data("map_info"))
			saveMapInfo(currentItem, last_info);
		else{
			alert("No changed data to save");
		}
	}
	else
		alert("No selected item to save");
}

function saveMapInfo(o, map_info){
	var category = $(o).data("category");
	var obj_id = $(o).data("obj_id");
	$(o).data("map_info", map_info);
	var param = {
		table		: table_map[category],
		obj_id 		: obj_id,
		map_info	: map_info,
	};
	sendAjaxNotMessage('/objectsmanager/savemapinfo', param, function(data){
		if(data == "ok"){
			alert('Save successfully');
		}else{
			_alert("Error while saving data for "+category_title[category].toLowerCase()+" "+$(o).text()+": \n" + data);
		}
	});
}

function removeMapObjects(o){
	var category = $(o).data("category");
	var obj_id = $(o).data("obj_id");
	var key = getKey(category, obj_id);
	if(overlays[key]){
		for(var i=0;i<overlays[key].length;i++){
			if(overlays[key][i] == undefined) continue;
			overlays[key][i].setMap(null);
			delete overlays[key][i];
		}
		delete overlays[key];
	}
}

var currentItem = null;
var autoSave = false;
function itemClick(o, forceSelect){
	var category = $(o).data("category");
	if($(o).hasClass("item_selected") || currentItem==o){
		if(forceSelect == undefined || forceSelect !== true)
			return;
	}
	if(currentItem != null && !isItemAll(currentItem)){
		var last_info = getMapObjectsConfig(currentItem);
		if(last_info!=$(currentItem).data("map_info")){
			console.log("last_info: "+last_info);
			console.log("info: "+$(currentItem).data("map_info"));
			if(autoSave){
				saveMapInfo(currentItem, last_info);				
			}
			else{
				var dialog = $("<div><center>Your map informations has been changed.<br>Do you want to save it for "+category_title[current_category].toLowerCase()+" <b>"+$(currentItem).text()+"</b>?</center></div>").dialog({
					modal: true,
					width: 420,
					//height:240,
					title: "Confirm",
					buttons: {
						"Yes": function(){
							saveMapInfo(currentItem, last_info);
							$(dialog).dialog("close");
							currentItem = null;
							itemClick(o, forceSelect);
						},
						"Auto Save": function(){
							autoSave = true;
							saveMapInfo(currentItem, last_info);
							$(dialog).dialog("close");
							currentItem = null;
							itemClick(o, forceSelect);
						},
						"No": function(){
							removeMapObjects(currentItem); //remove them to be created again next time get back, to get last saved state
							$(dialog).dialog("close");
							currentItem = null;
							itemClick(o, forceSelect);
						},
						"Cancel": function(){
							$(dialog).dialog("close");
						}
					},
				});
				return;
			}
		}
	}
	currentItem = o;
	$("#category_"+category+" .item_selected").removeClass("item_selected");
	$(o).addClass('item_selected');
	if(isItemAll(o)){
		selectAll(category);
	}
	else{
		$("#sel_"+category).html($(o).text());
		selected_ids[category] = Number($(o).data("obj_id"));
		hideOverlays($(o).attr("id"));
	}
	showOverlaysForObject(o);
	applyToChildCategory(category);
}

function applyToChildCategory(category){
	if(category == "facility"){
		onChangedSelectedItem("eu");
		onChangedSelectedItem("flow");
		onChangedSelectedItem("tank");
		onChangedSelectedItem("storage");
		onChangedSelectedItem("equipment");
	}
	else if(child_category[category] != undefined && child_category[category] != null && child_category[category] != "")
		onChangedSelectedItem(child_category[category]);
}

function onChangedSelectedItem(category){
	var parent_ids = "";
	if(category == "area"){
		if(selected_ids["pu"]>0)
			parent_ids += "[parent_id='" + selected_ids["pu"] + "'],";
	}
	else if(category == "facility"){
		if(selected_ids["area"]>0)
			parent_ids += "[parent_id='" + selected_ids["area"] + "'],";
		else{
			if(selected_ids["pu"]>0){
				$("#category_area .item_row[parent_id='"+selected_ids["pu"]+"']").each(function(){
					parent_ids += "[parent_id='" + $(this).data("obj_id") + "'],";
				});
			}
		}
	}
	else if(category == "eu" || category == "flow" || category == "tank" || category == "storage" || category == "equipment"){
		if(selected_ids["facility"]>0)
			parent_ids += "[parent_id='" + selected_ids["facility"] + "'],";
		else{
			if(selected_ids["area"]>0){
				$("#category_facility .item_row[parent_id='"+selected_ids["area"]+"']").each(function(){
					parent_ids += "[parent_id='" + $(this).data("obj_id") + "'],";
				});
			}
			else{
				if(selected_ids["pu"]>0){
					$("#category_area .item_row[parent_id='"+selected_ids["pu"]+"']").each(function(){
						$("#category_facility .item_row[parent_id='"+$(this).data("obj_id")+"']").each(function(){
							parent_ids += "[parent_id='" + $(this).data("obj_id") + "'],";
						});
					});
				}
			}
		}
	}
	if(parent_ids == "")
		$("#category_"+category+" .item_row").attr("show",1).show();
	else{
		$("#category_"+category+" .item_row").not(parent_ids + "[parent_id=0]").attr("show",0).hide();
		$("#category_"+category+" .item_row" + parent_ids + "[parent_id=0]").attr("show",1).show();
	}
	if($("#category_"+category+" .item_selected").attr("show") == 0){
		$("#category_"+category+" .item_selected").removeClass("item_selected");
		$("#category_"+category+" .item_all").addClass("item_selected");
		selectAll(category);
	}
	updateObjectsCount(category);
	applyToChildCategory(category);
}
function focusToObjectItem(category, obj_id){
	var selected_item;
	if(obj_id == undefined){
		selected_item = $("#category_"+category+" .item_selected");
		//force load map objects
		////hideOverlays($(selected_item).attr("id"));
		////showOverlaysForObject(selected_item);
		itemClick(selected_item, true);
	}
	else{
		selected_item = $("#"+getKey(category, obj_id));
	}
		
	var y = selected_item.offset().top - 99;
	if(y + selected_item.height() <= $("#category_"+category).height() && y >= 0){
		selected_item.effect("highlight", {}, 1000);
		return;
	}
	y += $("#category_"+category).scrollTop();
	$("#category_"+category).animate({
		scrollTop: y
	},{
        duration: 300,
        complete: function () {
			selected_item.effect("highlight", {}, 1000);
        }
      });
}
$(function(){
	var ebtoken = $('meta[name="_token"]').attr('content');
	$.ajaxSetup({
		headers: {
			'X-XSRF-Token': ebtoken
		}
	})
	
	$("input[name='sections']").change(function(){
		current_category = $(this).val();
		focusToObjectItem(current_category);
	});
	current_category = "pu";
	$("input#optionpu").prop('checked', true);
	updateObjectsCount();
});
var obj_types = ["pu","area","facility","eu","flow","tank","storage","equipment"];
var child_category = {"pu":"area","area":"facility","facility":"objects"};
function updateObjectsCount(category){
	for(var i=0;i<=obj_types.length;i++)
		if(category == obj_types[i] || category == undefined)
	{
		$("#count_"+obj_types[i]).html($("#category_"+obj_types[i]+" .item_row").not("[show='0']").length - 1);
	}
}

var table_map = {"pu":"LO_PRODUCTION_UNIT","area":"LO_AREA","facility":"FACILITY","eu":"ENERGY_UNIT","flow":"FLOW","tank":"TANK","storage":"STORAGE","equipment":"EQUIPMENT"};
var category_title = {"pu":"Production Unit","area":"Area","facility":"Facility","eu":"Energy Unit","flow":"Flow","tank":"Tank","storage":"Storage","equipment":"Equipment"};
var current_category = "";
function edit(isNew){
	var category = current_category;
	var table = table_map[category];
	var obj_id = Number($(currentItem).data("obj_id"));
	if(!((isNew === true || obj_id > 0) && table.length > 0))
		return;
	var button = {};
	button["Save"] = function(){
					$("#frame_edit")[0].contentWindow.document.forms[0].submit();
					//$("#box_edit").dialog("close");
				};
	if(!isNew)
		button["Save as New"] = function(){
						$("#frame_edit")[0].contentWindow._saveAs();
						//$("#box_edit").dialog("close");
					};
	button["Close"] = function(){
					$("#box_edit").dialog("close");
				};
	$("#frame_edit").hide();
	$("#box_edit").dialog({
			height: 520,
			width: 800,
			modal: true,
			title: (isNew?"Add new ":"Edit ") + category_title[category] + (isNew?"":": " + $(currentItem).text()),
			buttons: button,
			close: function( event, ui ) {
				$("#frame_edit").attr("src","");
			}
		});
	var url = "loadtabledata/edittable?action=edit"+(isNew===true?"":"&ID="+obj_id)+"&table="+table;
	$("#frame_edit").attr("src",url);
	showWaiting();
}

function iframe_loaded(){
	$("#frame_edit").contents().find(".lm_form_button_bar").hide();
	$("#frame_edit").contents().find(".lm_form tr").first().hide();
	$("#frame_edit").show();
	hideWaiting();
}

function getKey(category, obj_id){
	return category+"-"+obj_id;
}
var overlays = {};
function addOverlay(category, obj_id, o){
	var key = getKey(category, obj_id);
	o.category = category;
	o.obj_id = obj_id;
	if(overlays[key] == undefined || overlays[key] == null)
		overlays[key] = [];
	overlays[key].push(o);
	//if(o.editable === true)
	setOverlayEditable(o, false);
	//console.log(o.type);
}
function hideOverlays(except_key){
	//hide all other overlays
	for (var property in overlays) {
		if(overlays[property].length > 0 && property != except_key)
		{
			for(var i=0;i<overlays[property].length;i++){
				if(overlays[property][i] == undefined) continue;
				overlays[property][i].setMap(null);
				
			}
		}
	}
}
function isItemAll(o){
	return $(o).hasClass("item_all");
}
function showOverlaysForObject(o, isShowAll){
	var category = $(o).data("category");
	var ret = null;
	if(isItemAll(o)){
		hideOverlays();
		$("#category_"+category+" .item_row").not("[show='0']").each(function(){
			if(!isItemAll(this))
				showOverlaysForObject(this, true);
		});
		moveTheMap();
		return;
	}
	var obj_id = $(o).data("obj_id");
	var key = getKey(category, obj_id);
	var draggable = (isShowAll !== true);
	if(overlays[key]){
		if(overlays[key].length > 0)
		{
			for(var i=0;i<overlays[key].length;i++){
				if(overlays[key][i] == undefined) continue;
				overlays[key][i].setMap(map);
				overlays[key][i].setOptions({draggable: draggable});
			}
		}
	}
	else{
		var map_info = $(o).data("map_info");
		var arr = JSON.parse("["+map_info+"]");
		var sum_lat = 0, sum_lng = 0, count = 0;
		for(var i=0;i<arr.length;i++)
			if(arr[i].length > 0){
				ret = createOverlay(category, obj_id, arr[i], draggable);
				sum_lat += ret.sum_lat;
				sum_lng += ret.sum_lng;
				count += ret.count;
			}
		var label = $("#"+key).html();
		if(count > 0 && label.length > 0){
			var mapLabel = new MapLabel({
				text: label,
				position: new google.maps.LatLng(sum_lat/count,sum_lng/count),
				map: map,
				fontSize: 15,
				align: 'center',
				fontColor: '#378de5',
				strokeColor: '#ffffff'
			});
			addOverlay(category, obj_id, mapLabel);
		}
	}
	if(isShowAll !== true){
		moveTheMap();
	}
}
function moveTheMap(){
	for (var property in overlays) {
		if(overlays[property].length > 0)
		{
			for(var i=0;i<overlays[property].length;i++){
				if(overlays[property][i] == undefined) continue;
				if(overlays[property][i].map != null){
					var shape = overlays[property][i];
					var xy;
					if(shape.type == google.maps.drawing.OverlayType.POLYLINE || shape.type == google.maps.drawing.OverlayType.POLYGON){
						var vertices = shape.getPath();
						for (var i =0; i < vertices.getLength(); i++) {
							xy = vertices.getAt(i);
							break;
						}
					}
					else if(shape.type == google.maps.drawing.OverlayType.CIRCLE){
						var xy = shape.getCenter();
					}
					else if(shape.type == google.maps.drawing.OverlayType.RECTANGLE){
						var xy = shape.bounds.getCenter();
					}
					else if(shape.type == google.maps.drawing.OverlayType.MARKER){
						var xy = shape.getPosition();
					}
					if(xy != null){
						//lat = xy.lat();
						//lng = xy.lng();
						if(!map.getBounds().contains(xy))
							map.panTo(xy);
					}
					return;
				}
			}
		}
	}
}
function createOverlay(category, obj_id, info, draggable){
	if(draggable == undefined)
		draggable = false;
	var type = info[0];
	var label = info[1];
	var sum_lat = 0, sum_lng = 0;
	var count = 0;
	if(type=="m"){
		sum_lat += Number(info[2]);
		sum_lng += Number(info[3]);
		count++;
		var marker = new google.maps.Marker({
			raiseOnDrag: true,
			map: map,
			draggable: draggable,
			type: google.maps.drawing.OverlayType.MARKER,
			position: new google.maps.LatLng(Number(info[2]),Number(info[3]))
		});
		addOverlay(category, obj_id, marker);
		/* var mapLabel = new MapLabel({
			text: label,
			position: new google.maps.LatLng(Number(info[2]),Number(info[3])),
			map: map,
			fontSize: 13,
			align: 'center',
			fontColor: '#378de5',
			strokeColor: '#ffffff'
		});
		addOverlay(category, obj_id, mapLabel); */
	}
	else if(type=="c"){
		sum_lat += Number(info[4]);
		sum_lng += Number(info[5]);
		count++;
		var circle = new google.maps.Circle({
			strokeColor: info[2],
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: info[3],
			fillOpacity: 0.35,
			map: map,
			draggable: draggable,
			//editable: true,
			type: google.maps.drawing.OverlayType.CIRCLE,
			center: {lat: Number(info[4]), lng: Number(info[5])},
			radius: Number(info[6])
		});	
		addOverlay(category, obj_id, circle);
		/* var mapLabel = new MapLabel({
			text: label,
			position: new google.maps.LatLng(Number(info[4]),Number(info[5])),
			map: map,
			fontSize: 13,
			align: 'center',
			fontColor: '#378de5',
			strokeColor: '#ffffff'
		});
		addOverlay(category, obj_id, mapLabel); */
	}
	else if(type=="r"){
		sum_lat += Number(info[4])+Number(info[5]);
		sum_lng += Number(info[6])+Number(info[7]);
		count += 2;
		var rectangle = new google.maps.Rectangle({
			strokeColor: info[2],
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: info[3],
			fillOpacity: 0.35,
			map: map,
			draggable: draggable,
			//editable: true,
			type: google.maps.drawing.OverlayType.RECTANGLE,
			bounds: {
				north: Number(info[4]),
				south: Number(info[5]),
				east: Number(info[6]),
				west: Number(info[7])
			}
		});
		addOverlay(category, obj_id, rectangle);
		/* var mapLabel = new MapLabel({
			text: label,
			position: new google.maps.LatLng((Number(info[4])+Number(info[5]))/2,(Number(info[6])+Number(info[7]))/2),
			map: map,
			fontSize: 13,
			align: 'center',
			fontColor: '#378de5',
			strokeColor: '#ffffff'
		});
		addOverlay(category, obj_id, mapLabel); */
	}
	else if(type=="p"){
		var coords = [];
		for(var i=4;i<info.length;i+=2){
			coords.push({lat: Number(info[i]), lng: Number(info[i+1])});
			sum_lat += Number(info[i]);
			sum_lng += Number(info[i+1]);
			count++;
		}
		if(count > 0){
			var polygon = new google.maps.Polygon({
				paths: coords,
				type: google.maps.drawing.OverlayType.POLYGON,
				map: map,
				draggable: draggable,
				//editable: true,
				strokeColor: info[2],
				strokeOpacity: 0.8,
				strokeWeight: 2,
				fillColor: info[3],
				fillOpacity: 0.35
			});
			addOverlay(category, obj_id, polygon);
			/* var mapLabel = new MapLabel({
				text: label,
				position: new google.maps.LatLng(sum_lat/count,sum_lng/count),
				map: map,
				fontSize: 13,
				align: 'center',
				fontColor: '#378de5',
				strokeColor: '#ffffff'
			});
			addOverlay(category, obj_id, mapLabel); */
		}
	}
	else if(type=="l"){
		var coords = [];
		for(var i=3;i<info.length;i+=2){
			coords.push({lat: Number(info[i]), lng: Number(info[i+1])});
			sum_lat += Number(info[i]);
			sum_lng += Number(info[i+1]);
			count++;
		}
		if(count > 0){
			var line = new google.maps.Polyline({
				path: coords,
				type: google.maps.drawing.OverlayType.POLYLINE,
				map: map,
				draggable: draggable,
				//editable: true,
				geodesic: true,
				strokeColor: info[2],
				strokeOpacity: 1.0,
				//strokeWeight: 2
			});
			addOverlay(category, obj_id, line);
			/* var mapLabel = new MapLabel({
				text: label,
				position: new google.maps.LatLng(sum_lat/count,sum_lng/count),
				map: map,
				fontSize: 13,
				align: 'center',
				fontColor: '#378de5',
				strokeColor: '#ffffff'
			});
			addOverlay(category, obj_id, mapLabel); */
		}
	}
	return {sum_lat: sum_lat, sum_lng: sum_lng, count: count};
}
function getMapObjectsConfig(o){
	var category = $(o).data("category");
	var obj_id = $(o).data("obj_id");
	var key = getKey(category, obj_id);
	var map_info = "";
	if(overlays[key]){
		var count = 0;
		for(var io=0;io<overlays[key].length;io++){
			var shape = overlays[key][io];
			if(shape == undefined) continue;
			if(shape.map == null){
				delete overlays[key][io];
				continue;
			}
			if(shape.type != undefined && shape.type !=null ){
				var label = "";
				var info="";
				if(shape.type == google.maps.drawing.OverlayType.POLYLINE || shape.type == google.maps.drawing.OverlayType.POLYGON){
					info = '"'+(shape.type == google.maps.drawing.OverlayType.POLYLINE?"l":"p") + '","'+label+'"';
					info += ',"'+shape.get('strokeColor')+'"';
					if(shape.type == google.maps.drawing.OverlayType.POLYGON)
						info += ',"'+shape.get('fillColor')+'"';
					var vertices = shape.getPath();
					for (var i =0; i < vertices.getLength(); i++) {
						var xy = vertices.getAt(i);
						info += "," + xy.lat() + ',' + xy.lng();
					}
				}
				else if(shape.type == google.maps.drawing.OverlayType.CIRCLE){
					info = '"c"' + ',"'+label+'"';
					info += ',"'+shape.get('strokeColor')+'"';
					info += ',"'+shape.get('fillColor')+'"';
					var xy = shape.getCenter();
					info += "," + xy.lat() + ',' + xy.lng() + ',' + shape.getRadius();
				}
				else if(shape.type == google.maps.drawing.OverlayType.RECTANGLE){
					info = '"r"' + ',"'+label+'"';
					info += ',"'+shape.get('strokeColor')+'"';
					info += ',"'+shape.get('fillColor')+'"';
					var bounds = shape.getBounds();
					var NE = bounds.getNorthEast();
					var SW = bounds.getSouthWest();
					info += "," + NE.lat() + ',' + SW.lat() + ',' + NE.lng() + ',' + SW.lng();
				}
				else if(shape.type == google.maps.drawing.OverlayType.MARKER){
					info = '"m"' + ',"'+label+'"';
					var xy = shape.getPosition();
					info += "," + xy.lat() + ',' + xy.lng();
				}
				if(info!=""){
					count++;
					info = "["+info+"]";
					map_info += (map_info==""?"":",") + info;
				}
			}
		}
		if(count == 1){
			map_info += ",[]";
		}
	}
	//console.log(map_info);
	return map_info;
}
</script>

<body onload="load()" style="margin:0; overflow-x:hidden">
<div id="box_edit" style="display:none">
<iframe id="frame_edit" onload="iframe_loaded()" style="display:none;border:0;margin:0;width:100%;height:100%"></iframe>
</div>
<div id="wraper" style="height:1px">
<div id="panel">
<button id="delete-button" style="background-color:#ee0000;border:1px solid white">Delete Selected Shape</button>
	<div id="color-palette"></div>
</div>
<div id="map" style="float:right;background:#666666;height:100%;width:calc(100% - 600px)">
</div>
<div id="objects_container">
<div id="toolbar_actions">
<input type="button" value="Edit" onclick="edit()"/>
<input type="button" value="New" onclick="edit(true)"/>
<input type="button" value="Delete" onclick="delete()"/>
<input type="button" value="Save" onclick="save()"/>
</div>
<div class="tabordion">
@foreach($all_objects as $obj_type => $obj)
  <section id="section{!!$obj_type!!}">
    <input type="radio" name="sections" id="option{!!$obj_type!!}" value="{!!$obj_type!!}">
    <label for="option{!!$obj_type!!}">{!!$obj["TITLE"]!!}<span class="category_count" id="count_{!!$obj_type!!}">0</span><br><span class="category_sel" id="sel_{!!$obj_type!!}">( All )</span></label>
    <article id="category_{!!$obj_type!!}" category="{!!$obj_type!!}">
		<a class="item_row item_all item_selected" onclick="itemClick(this)" id="{!!$obj_type!!}-0" parent_id="0" data-map_info='' data-category="{!!$obj_type!!}" data-obj_id="0">( All )</a>
	@foreach($obj["DATA"] as $item)
		<a class="item_row" onclick="itemClick(this)" id="{!!$obj_type!!}-{!!$item['ID']!!}" parent_id="{!!$item['PARENT_ID']!!}" data-map_info='{!!$item['MAP_INFO']!!}' data-category="{!!$obj_type!!}" data-obj_id="{!!$item['ID']!!}">{!!$item['NAME']!!}</a>
	@endforeach
    </article>
  </section>
@endforeach
</div>
</div>
</div>
</body>
@stop
