function loadJsCssFile(filename, filetype){
    if (filetype=="js"){ //if filename is a external JavaScript file
        var fileref=document.createElement('script')
        fileref.setAttribute("type","text/javascript")
        fileref.setAttribute("src", filename)
    }
    else if (filetype=="css"){ //if filename is an external CSS file
        var fileref=document.createElement("link")
        fileref.setAttribute("rel", "stylesheet")
        fileref.setAttribute("type", "text/css")
        fileref.setAttribute("href", filename)
    }
    if (typeof fileref!="undefined")
        document.getElementsByTagName("head")[0].appendChild(fileref)
}

function downloadFile(url){
	var ev = document.createEvent("MouseEvents");
	ev.initMouseEvent("click", true, false, self, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
	var anchor = document.createElement('a');
	anchor.setAttribute('href', url);
	// Fire event
	anchor.dispatchEvent(ev);
	anchor.remove();
}

/**
 * detect IE
 * returns version of IE or false, if browser is not Internet Explorer
 */
function detectIE() {
    var ua = window.navigator.userAgent;

    var msie = ua.indexOf('MSIE ');
    if (msie > 0) {
        // IE 10 or older => return version number
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
    }

    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
        // IE 11 => return version number
        var rv = ua.indexOf('rv:');
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
    }

    var edge = ua.indexOf('Edge/');
    if (edge > 0) {
       // Edge (IE 12+) => return version number
       return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
    }

    // other browser
    return false;
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

/**
 * Hack in support for Function.name for browsers that don't support it.
 * IE, I'm looking at you.
**/
if (Function.prototype.name === undefined && Object.defineProperty !== undefined) {
    Object.defineProperty(Function.prototype, 'name', {
        get: function() {
            var funcNameRegex = /function\s([^(]{1,})\(/;
            var results = (funcNameRegex).exec((this).toString());
            return (results && results.length > 1) ? results[1].trim() : "";
        },
        set: function(value) {}
    });
}

if(detectIE()) {
	String.prototype.endsWith = function(pattern) {
		  var d = this.length - pattern.length;
		  return d >= 0 && this.lastIndexOf(pattern) === d;
		}
}

function loadEBjs(){
	if (typeof actions == "undefined") {
		 document.write('<script type="text/javascript" src="'
				    + '/common/js/eb.js?44' + '"></scr' + 'ipt>'); 
	}
}

function show_wf_loading(){
	$("#wf_loading_box").show();
}
function hide_wf_loading(){
	$("#wf_loading_box").fadeOut("fast");
}
function showWorkflow(){
	$( "#boxWorkflow" ).dialog({
		height: 520,
		width: 900,
		modal: true,
		position: ['right-4.5', 'top+20'],
		title: "Your tasks",
		close: function( event, ui ) {
			//loadTasksCounting();
		}
	});

	$("#iframeWorkflow").attr("src","data:text/html;charset=utf-8," + escape(''));
	show_wf_loading();
	$("#iframeWorkflow").attr("src","/loadWfShow");
}

var helpLoaded;
function showHelp(){
	if(!helpLoaded){
		if (typeof func_code == 'undefined' || func_code == ""){
			$("#boxHelp").html("No data");
			return;
		}
		$("#boxHelp").html('<img class="center_content" src="/images/loading.gif">');
		$.get("/help/"+func_code,function(data){
 			helpLoaded = true;
			if(typeof data == 'undefined'
					|| data==""
					||jQuery.isEmptyObject(data) 
					|| data.length<=0
					|| typeof data.HELP == 'undefined'
					|| data.HELP == ""
					) 
				data = {HELP	: "No data."};
			$("#boxHelp").html(data['HELP']);
		});
	}
}
function showTaskLog(task_id){
	if(task_id<=0) return;
	if(!$("#boxTaskLog").is(":visible"))
		$( "#boxTaskLog" ).dialog({
			height: 500,
			width: 900,
			modal: true,
			//position: ['right-4.5', 'top+20'],
			title: "Task log"
		});
	$("#boxTaskLog").html('<img class="center_content" src="/images/loading.gif">');
	param = {task_id: task_id};
	sendAjax('/showTaskLog', param, function(data){
		$("#boxTaskLog").html(data.LOG);
	});
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
		var style = '@keyframes slidedown{from{top:-40px;}}@keyframes fadein{from{opacity:0;}}@keyframes rotate{from{transform: rotate(0deg);}to{transform: rotate(360deg);}}#_box_notice{animation: slidedown 0.7s;position:absolute;background:yellow;box-sizing: border-box;box-shadow: 0px 0px 30px #000000;width:300px;height:40px;padding:10px;border-radius: 0px 0px 10px 10px;top:0px;left:50%;margin-left:-150px;color:#666666;text-align:center;font-size:10pt;}#_refresh {width:20px;height:20px;display:inline-block;box-sizing: border-box;margin-left:-110px;border:4px solid gray;animation: rotate 1s linear infinite;}';
		addStyleSheet(style);
		
		var waitingHTML='<div id="_box_notice"><div id="_refresh"></div><div id="_content" style="position:absolute;top:12px;width:100%;font-size:11pt">'+text+'</div></div>';
		$bgBox = $('<div>')
			.attr('id', '_waiting')
			.attr('style',"animation: fadein 0.5s;position: fixed;top:0px;left:0px; width: 100%; height: 100%; z-index: 999;text-align:center;background:rgba(0,0,0,0.1)")
			.html(waitingHTML)
			.appendTo('body');
	}
	else
		$("#_box_notice #_content").html(text);
	$bgBox.show();
}
function showWaiting(text)
{
	if(timerWaiting) clearTimeout(timerWaiting);
	timerWaiting=setTimeout(function(){ _showWaiting(text); },1000);
}
function hideWaiting()
{
	if(timerWaiting) clearTimeout(timerWaiting);
	if($bgBox) $bgBox.fadeOut();
}
var $msgBox;
var timerMsgBox;
function showMessageAutoHide(s,t)
{
	if(!$msgBox)
	{
		var style = '@keyframes slidedown{from{top:-40px;}}#_box_notice_msg{animation: slidedown 0.7s;background:yellow;box-sizing: border-box;box-shadow: 0px 0px 30px #000000;min-width:200px;max-width:800px;padding:15px 20px;border-radius: 0px 0px 10px 10px;top:0px;position: absolute;z-index:10000;top: 0px;left:50%;transform: translate(-50%,0%); color:black;text-align:center;font-size:10pt;}';
		addStyleSheet(style);
		
		$msgBox = $('<div id="_box_notice_msg" style=""></div>').appendTo('body');
		$msgBox.click(function(){___hideMessage();});
	}
	else
		___hideMessage();
	
	$("#_box_notice_msg").html(s);
	setTimeout('$msgBox.show()',500);
//	$msgBox.show();
	if(timerMsgBox) clearTimeout(timerMsgBox);
	timerMsgBox=setTimeout('___hideMessage()',!t?5000:t);
}
function ___hideMessage()
{
	if(timerMsgBox) clearTimeout(timerMsgBox);
	if($msgBox) $msgBox.fadeOut();
}
function postRequest(target,variables,completedFunc,container)
{
    showWaiting();
    $.post(target,variables,function(data){hideWaiting();completedFunc(data);});
}

var cachedAjaxData={};
function sendAjax(url, param, funcSuccess, funcError, waitingMessage){
	var cacheKey=url.replace(/\//g, '_');
	if(param.cache === true){
		for (var key in param) {
			if (param.hasOwnProperty(key) && key!="cache" && (typeof(param[key])=="string" || typeof(param[key])=="number"))
				cacheKey += param[key];
		}
		if(cachedAjaxData[cacheKey] != undefined){
    		if(typeof(funcSuccess) == "function") 
				funcSuccess(cachedAjaxData[cacheKey]);
			return;
		}
	}
    return $.ajax({
  		beforeSend: function(){
			if(waitingMessage == undefined || waitingMessage !== false)
				showWaiting(waitingMessage);
  		},
    	url: url,
    	type: "post",
    	data: param,
    	//dataType: 'json',
    	success: function(_data){
			if(param.cache === true){
				cachedAjaxData[cacheKey] = _data;
			}
			if(waitingMessage == undefined || waitingMessage !== false)
				hideWaiting(); 
    		if(typeof(funcSuccess) == "function") 
				funcSuccess(_data);
		},
		error: function(_data){
			if(waitingMessage == undefined || waitingMessage !== false)
				hideWaiting();
    		if(typeof(funcError) == "function") 
				funcError(_data);
			else if (typeof actions != "undefined") {
				actions.loadError(_data);
			}
    		else{
				alert('Error: '+_data);
				console.log(_data);
			}
		}
	});    
}

function sendAjaxNotMessage(url, param, func, error){
	return sendAjax(url, param, func, error, false);
}
function zeroFill( number, width )
{
  width -= number.toString().length;
  if ( width > 0 )
  {
    return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
  }
  return number + ""; // always return a string
}

function inputNumber(keyEvent){
	if (keyEvent.shiftKey) {
		return false;
	}
	
	var number = [ 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 109, 110];
	var control = [/*backspace*/8, /*del*/ 46, /*tab*/9, /*esc*/ 27, /*enter*/13,/*arrow*/ 37, 38, 39, 40];
	var keyCode = keyEvent.charCode || keyEvent.keyCode || 0;
	
	if ($.inArray(keyCode, number.concat(control)) < 0){
		return false;
	}
	
	return true;
}

function preventDecimalInput(keyEvent, isNeg, left, right) {
	if (keyEvent.shiftKey) {
		return false;
	}
	var number = [ 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 109, 189, 190, 110];
	var control = [/*backspace*/8, /*del*/ 46, /*tab*/9, /*esc*/ 27, /*enter*/13,/*arrow*/ 37, 38, 39, 40];
	var keyCode = keyEvent.charCode || keyEvent.keyCode || 0;

	if ($.inArray(keyCode, number.concat(control)) < 0){
		return false;
	}
	if ($.inArray(keyCode, control) >= 0){
		return true;
	}
	var ctrId = keyEvent.target.id + "";
	
	var index = document.getElementById(ctrId).selectionStart;
	var current =  ($("#" + ctrId).val() + "").replace(",","");
	var willStr = "";
	var charDw = "";
	if (keyCode == 189 || keyCode == 109) {
		charDw = "-";
	} else if (keyCode == 190 || keyCode == 110) {
		charDw = ".";
	} else {
		if((current.length - (current.indexOf("-") + 1)) == left && current.indexOf(".") < 0) {
			return false;
		}
		if (parseInt(keyCode) >= 96 && parseInt(keyCode) <= 105) {
			charDw = (parseInt(keyCode) - 96) + '';
		} else {
			charDw = String.fromCharCode(keyCode);
		}
	}
	if (index > 0) {
		willStr = current.substring(0, index) + charDw + current.substring(index, current.length);
	} else {
		willStr = charDw + current;
	}
	var regexStr = "^";
	if (isNeg) {
		regexStr += "\\-?";
	}
	regexStr += "\\d{0," + left + "}";
	if (right > 0) {
		regexStr += "\\.?\\d{0," + right + "}";
	}
	//regexStr += "$";
	var RE = new RegExp(regexStr);
	if (RE.test(willStr)) {
		return true;
	} else {
		return false;
	}
}

function checkValue(sValue, valueDefault){
	var result = sValue;

	if(sValue === null || sValue === undefined){
		result = valueDefault;
	}
		
	return result;
}

function dateToString(date, format){
	if(format == undefined)
		format = 'Y-M-D';
	var ret = moment(date).format(format);
	return ret;
}

function formatDate(dateString){
	var date = dateString!=""? moment.utc(dateString,configuration.time.DATETIME_FORMAT_UTC)
									.format(configuration.time.DATE_FORMAT)
							:dateString;
	return date;
}

function formatDateTime(dateString){
	var date = dateString!=""? moment.utc(dateString,configuration.time.DATETIME_FORMAT_UTC)
									.format(configuration.time.DATETIME_FORMAT)
							:dateString;
	return date;
}
function formatDateTimeUTC(dateString){
	var date = dateString!=""? moment.utc(dateString,configuration.time.DATETIME_FORMAT)
									.format(configuration.time.DATETIME_FORMAT_UTC)
							:dateString;
	return date;
}

function validateNumber(selector) {
	var regex = /^-?[0-9]{1,5}$/;
	if(!regex.test($(selector).val())) {
		return false;
	}
	else return true;
}


function stripslashes (str) {
	  return (str + '')
	    .replace(/\\(.?)/g, function (s, n1) {
	      switch (n1) {
	        case '\\':
	          return '\\'
	        case '0':
	          return '\u0000'
	        case '':
	          return ''
	        default:
	          return n1
	      }
	    })
	}

var taskCountingTimer = null;
function loadTasksCounting(){
	console.log("loadTasksCounting");
	if(taskCountingTimer != null) {
		clearTimeout(taskCountingTimer);
		taskCountingTimer=null;
	}
	//if(!$("#boxUserInfo").is(':visible'))
	//	return;
	param = {};
	sendAjaxNotMessage('/countWorkflowTask', param, function(data){
		$("#wf_notify").html(data);
		if(data=="0"){
			$("#wf_notify_box").hide();
			$("#user_workflow").removeClass("workflow_task_active");
		}
		else{
			$("#wf_notify_box").show();
			if(!$("#user_workflow").hasClass("workflow_task_active"))
				$("#user_workflow").addClass("workflow_task_active");
		}
		//taskCountingTimer=setTimeout(loadTasksCounting,30000);
	}, function(){});
}

$( document ).ajaxComplete(function( event, xhr, settings ) {
	if(xhr.status=="401" && settings.url!='/countWorkflowTask'){
		alert("Your session was ended. Please <a href='/' target='_blank'>click here</a> to login again.");
	}
});

var _alert;
(function() {
    _alert = window.alert;       // <-- Reference
    window.alert = function(str) {
		showMessageAutoHide(str);
    };
})();
