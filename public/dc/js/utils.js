var serverAddress = readParameter('server');
if(!serverAddress) serverAddress='';
if(serverAddress.substr(0,4).toLowerCase()!='http')
	serverAddress='http://'+serverAddress;
var editted = '_e';

function onInit() {
    try {
        if (!window.openDatabase) {} else {
            initDB();
        }
    } catch (e) {
        console.log("onInit - catch: " + e);
        return;
    }
}

function initDB() {
    var shortName = 'offlineDB';
    var version = '1.0';
    var displayName = 'offlineDB';
    var maxSize = 65536; // in bytes
    localDB = window.openDatabase(shortName, version, displayName, maxSize);
}

function createTable(query) {
    //var query = 'CREATE TABLE IF NOT EXISTS authentication(id VARCHAR NOT NULL PRIMARY KEY, user VARCHAR NOT NULL, pass VARCHAR NOT NULL);';
    try {
        localDB.transaction(function(transaction) {
            transaction.executeSql(query, [], nullDataHandler, errorHandler);
            console.log("createTable: Create table successful.");
        });
    } catch (e) {
        console.log("createTable - catch: " + e);
        return;
    }
}

nullDataHandler = function(transaction, results) {

}

errorHandler = function(transaction, error) {
    console.log("Error: " + error.message);
    return true;
}

function checkNetworkStatus() {
    if (navigator.onLine) {
        return true;
    } else {
		alert('Please check network connection to Energy Builder system');
        return false;
    }
}

function generateID() {
    var d = new Date().getTime();
    if (window.performance && typeof window.performance.now === "function") {
        d += performance.now();; //use high-precision timer if available
    }
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
    return uuid;
};

function click_menu(x) {
    x.classList.toggle("change");
	writeParameter('showBack',1);
	window.location.assign('main.html');
}

function logout() {
	writeParameter('username','');
	checkLoginTo('','');
}


function clearData() {
	if(confirm('Do you really want clear all offline data?')){
		removeData('object_details');
		//reset points status
		var points = readData('points');
		for(var pnt in points)
			points[pnt].complete = false;
		saveData('points', points);
		alert('Clear data complete');
	}
}

function checkLoginTo(whatToDo, gotoPage){
	var v=readParameter('username');
	if(v==null || v==""){
		writeParameter('loginToDo',whatToDo);
		writeParameter('loginGotoPage',gotoPage);
		window.location.assign('login.html');
	}
	else if(!gotoPage || gotoPage=='')
		window.location.assign('main.html');
	else if(gotoPage=="download")
		doDownloadData();
	else if(gotoPage=="upload")
		doUploadData();
	else
		window.location.assign(gotoPage);
}

function isValidNumber(v){
	return !isNaN(v) && v!==null && v!=='';
}

function validNullObject(obj) {
    return obj && obj !== 'null' && obj !== 'undefined';
}

function initOfflineData() {
    saveData('routes', routes);
    saveData('points', points);
    saveData('objects', objects);
    saveData('object_types', object_types);
    saveData('data_types', data_types);
    saveData('control_types', control_types);
    saveData('lists', lists);
    saveData('object_attrs', object_attrs);
    saveData('object_details', object_details);
    saveData('data_store', data_store);
}

function clearOfflineData() {
	removeData('routes');
	removeData('points');
	removeData('objects');
	removeData('object_types');
	removeData('data_types');
	removeData('control_types');
	removeData('lists');
	removeData('object_attrs');
	removeData('object_details');
}

function saveData(key, data) {
	localStorage.setItem(key, JSON.stringify(data));
}

function readData(key) {
	return JSON.parse(localStorage.getItem(key));
}

function writeParameter(key, value) {
	localStorage.setItem(key, value);
}

function readParameter(key) {
	return localStorage.getItem(key);
}

function isParamNotSet(param){
	var v=readParameter(param);
	return (v==null || v=="null" || v==0 || v=="0" || v=="");
}

function removeData(key) {
	localStorage.removeItem(key);
}

function parseObject(data) {
	//data= data.replace(/'/g, '"');
	result = JSON.parse(data);
	return result;
}

function getData(response) {
	var data = response; //JSON.parse(response);
	if(data != null)
	{
		routes = {};
		for(var i=0;i<data['routes'].length;i++){
			routes[data['routes'][i].key] = data['routes'][i];
		}

		points = {};
		for(var i=0;i<data['points'].length;i++){
			points[data['points'][i].key] = data['points'][i];
		}

		objects = data['objects'];
		object_types = data['object_types'];
		data_types = data['data_types'];
		control_types = data['control_types'];

		//convert lists from array to object
		lists = {};
		for(var list in data['lists']){
			lists[list] = {};
			for(var i=0;i<data['lists'][list].length;i++)
				lists[list][data['lists'][list][i].value] = data['lists'][list][i].text;
		}

		object_attrs = data['object_attrs'];
		object_details = data['object_details'];
		data_store = data['data_store'];
		
		return true;
	}
}

function getObjectDetails(){
	var object_details = readData('object_details');
	if(object_details==null || object_details==undefined) object_details = {};
	return object_details;
}

var $bgBox;
var timerWaiting;
function addStyleSheet(css)
{
	var style = document.createElement('style');
	style.type = 'text/css';
	style.innerHTML = css;
	document.getElementsByTagName('head')[0].appendChild(style);
}
function showEBMessage(msg)
{
	
}
function _showWaiting(text)
{
	if(text == undefined)
		text = 'Loading...';
	if(!$bgBox)
	{
		var style = '@keyframes slidedown{from{top:-40px;}}@keyframes fadein{from{opacity:0;}}@keyframes rotate{from{transform: rotate(0deg);}to{transform: rotate(360deg);}}#_box_notice{animation: slidedown 0.7s;position:absolute;background:yellow;box-sizing: border-box;box-shadow: 0px 0px 30px #000000;width:300px;height:40px;padding:10px;border-radius: 0px 0px 10px 10px;top:0px;left:50%;margin-left:-150px;color:#666666;text-align:center;font-size:10pt;}#_refresh {width:20px;height:20px;display:inline-block;box-sizing: border-box;margin-left:-250px;border:4px solid gray;animation: rotate 1s linear infinite;}';
		addStyleSheet(style);
		
		var waitingHTML='<div id="_box_notice"><div id="_refresh"></div><div id="_content" style="position:absolute;top:12px;width:100%;font-size:11pt">'+text+'</div></div>';
		$bgBox = $('<div>')
			.attr('id', '_waiting')
			.attr('style',"animation: fadein 0.5s;position: fixed;top:0px;left:0px; width: 100%; height: 100%; z-index: 100000;text-align:center;background:rgba(0,0,0,0.1)")
			.html(waitingHTML)
			.appendTo('body');
	}
	else
		$("#_box_notice #_content").html(text);
	$bgBox.show();
}
function showWaiting(text, noWait)
{
	if(timerWaiting) clearTimeout(timerWaiting);
	if(noWait===true)
		_showWaiting(text);
	else
		timerWaiting=setTimeout(function(){ _showWaiting(text); },1000);
}
function hideWaiting()
{
	if(timerWaiting) clearTimeout(timerWaiting);
	if($bgBox) $bgBox.fadeOut();
}

function doAjax(route, type, data, waitingMessage, success, error, complete){
	$.ajax({
  		beforeSend: function(){
			showWaiting(waitingMessage);
  		},
		url: serverAddress+'/'+route,
		type: type,
		data: data,
		success: function(_data){
			hideWaiting();
    		if(typeof(success) == "function") 
				success(_data);
		},
		error: function(_data){
			hideWaiting();
    		if(typeof(error) == "function") 
				error(_data);
		},
		complete: function(_data){
    		if(typeof(complete) == "function") 
				complete(_data);
		},
	});	
}

function doDownloadData(){
	var v=readParameter('downloaded');
	if(v==1 || v=="1"){
		if(!confirm('Warning: All your data and configurations will be replaced. Do you really want to continue?'))
			return;
	}
	if(!checkNetworkStatus()){
		window.location.assign("main.html");
		return;
	}
	var dc_data_type = readParameter('dc_data_type');
	var days = readParameter('days');
	doAjax('dc/response.php', 'post',
		{date_filter: '',data_type: dc_data_type, days: days}, 'Downloading data...',
		function(data) {
			if(getData(data))
			{
				clearOfflineData();
				initOfflineData();
				writeParameter('downloaded',1);
			}
			alert('Configurations downloaded successfully.');
			window.location.assign('routes.html');
		},
		function(data) {
			console.log(data.responseText);
			alert('Download fail. Please check network connection to Energy Builder system');
		},
	);
}

function doUploadData(){
	var data_store = JSON.parse(readParameter("data_store"));
	var object_details = JSON.parse(readParameter("object_details"));
	var editted_objs = {};
	for(var fld in object_details){
		if(object_details[fld][editted]==1){
			delete object_details[fld][editted];
			editted_objs[fld] = object_details[fld];
		}
	}
	if(jQuery.isEmptyObject(editted_objs)){
		alert("No data changed to upload.");
		return;
	}
	if(!confirm('Warning: All your offline data will replace server\'s data if exists. Do you really want to continue?'))
		return;
	if(!checkNetworkStatus()){
		//alert('Please connect to the internet');
		window.location.assign("main.html");
		return;
	}
	var data = {data_store:data_store, object_details:editted_objs};
	doAjax('dc/upload.php','post',data, 'Uploading data...',
		function(data) {
			if (data == 'OK') {
				saveData('object_details', object_details);
				alert('Data uploaded successfully!');
			} else {
				alert('Can not upload data.\n'+data);
			}
		},
		function(data) {
			alert('Upload fail. Please check network connection to Energy Builder system.\n\n'+data.responseText);
		},
	);
}

function checkDefaultSettingValues(){
	var v;
	v=readParameter('server');
	if(!validNullObject(v)){
		if(location.protocol=='https:')
			v=location.protocol+'//'+location.host;
		else
			v=location.host;
		writeParameter('server',v);
	}
	v=readParameter('days');
	if(!validNullObject(v)){
		v=7;
		writeParameter('days',v);
	}
	v=readParameter('dc_data_type');
	if(!validNullObject(v)){
		v='fdc';
		writeParameter('dc_data_type',v);
	}
}
