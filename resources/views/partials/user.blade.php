<meta name="_token"
	content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />

<script src="/common/js/jquery-ui.js"></script>
<script type="text/javascript" src="/common/js/utils.js?12"></script>

<script type="text/javascript">

var ebtoken = $('meta[name="_token"]').attr('content');
$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

</script>
<script>
	function loadjscssfile(filename, filetype){
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
	if(!$.ui){
		loadjscssfile("/common/js/jquery-ui.js", "js");
		loadjscssfile("/common/css/jquery-ui.css?2", "css");
	}
	if($.ui){
		$.ui.dialog.prototype._makeDraggable = function() {
		this.uiDialog.draggable({
			containment: false,
			});
		};
	}
	/*
	if (!$("link[href='../common/css/style.css']").length)
		loadjscssfile("../common/css/style.css", "css");
	    //$('<link href="../common/css/style.css" rel="stylesheet">').appendTo("head");
	*/
	</script>

	<?php
		$current_username = '';
		if((auth()->user() != null)) $current_username = auth()->user()->username;
	?>
	<div id="boxUserInfo" style='position:fixed;z-index:2;display:;top:10px;right:10px;font-size:10pt;overflow:none;padding:3px 6px 3px 10px;background:#555555;border:1px solid #505050;border-radius:3px;color:#bbbbbb;font-size:10pt'>
	User <span style="cursor:pointer" onclick="location.href='/me/setting';"><font color="#33b5e8"><span id="textUsername">{{$current_username}}</span></font></span> &nbsp;|&nbsp; <div style="display:none;width:50px;cursor:pointer;padding:2px;color:#33b5e8;margin:2px;font-size:8pt">Alert: 0</div>
	<a style="color:#33b5e8;text-decoration:none" href="/auth/logout">logout</a> &nbsp;&nbsp;

	<img atl="Workflow" src='/img/gear.png' height=16 onclick="showWorkflow()" style="float:right;margin:0px 2px;cursor:pointer">
	<div id="wf_notify_box" onclick="showWorkflow()" style="display:none;position:absolute;right:-5px;top:-5px;width:16px;height:16px;font-family:Arial;background:red;border:2px solid white;border-radius:12px;font-size:6pt;font-weight:bold;color:white;cursor:pointer;text-align:center;line-height:12px;letter-spacing: -1px;text-indent:-1px;box-sizing: border-box;">
	<span id="wf_notify">
	</span>
	</div>
	<img atl="Help" src='/img/help.png' height=16 onclick="showHelp()" style="float:right;cursor:pointer">
	<script>
	var username= '{{$current_username}}';
	</script>
	</div>
		<div id="boxWorkflow" style="display:none;width:100%;height:100%;background:#ffffff;overflow:hidden;">
		    <iframe id="iframeWorkflow" onload="loadTasksCounting()" style="border:none;padding:0px;width:100%;height:100%;box-sizing: border-box;"></iframe>
				<div id="wf_loading_box" style="position:absolute;left:0px;top:0px;width:100%;height:100%;background:white;opacity:0.8"><center id="notify_splash"><img class="center_content" src="/images/loading.gif"></center></div>
		</div>
	<div id="boxHelp" style="display:none;width:100%;height:100%"><img class="center_content" src="/images/loading.gif"></div>
	<div id="boxTaskLog" style="display:none;z-index:100;width:100%;height:100%"></div>
