<?php 
	$current_username = '';
	$user = auth()->user();
	if($user){
		$current_username = $user->username;
		$rights 		  = $user->role();
	}

	$currentSubmenu	= isset($currentSubmenu)?$currentSubmenu:"";
	$xmenu= \Helper::getUserMenu();
?>

<script src="/js/base.js?1"></script>
<link href="/common/css/header_menu.css?5" rel="stylesheet"/>
<link href="/common/css/modify-ui.css?1" rel="stylesheet"/>

<script>
function genIconText(text){
	let iconText = '';
	//const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', charactersLength = characters.length;
	//item.title = characters.charAt(Math.floor(Math.random() * charactersLength)) + ' ' + characters.charAt(Math.floor(Math.random() * charactersLength));
	text.split(' ').forEach(w => {
		const ch = w.substring(0,1).toUpperCase();
		iconText.length < 2 && ch >= 'A' && ch <= 'Z' && (iconText += ch);
	});
	return iconText;
}
</script> 

<div header>
	<li logo action="menu"></li>
	<li eb><b>Energy Builder&#xae;</b></li>
	<div id="screen_caption">.</div>
	<ul full class="navi" id = "menu_navi"></ul>
    <li action="workflow" onclick="showWorkflow()" title="Workflow"></li>
    <li action="help" onclick="showHelp()" title="Help"></li>
    <li action="user"></li>
</div>
<div aside action="user">
	<close></close>
	<div class="title"></div>
	<!--
	<div action="chat" width="600px" side-title="{{ trans('Chat') }}"></div>
	<div action="setting" width="400px" side-title="{{ trans('Settings') }}"></div>
	<div action="notification" width="400px" side-title="{{ trans('Notifications') }}"></div>
-->
	<div action="help" id="boxHelp" width="600px" side-title="{{ trans('Quick Guide') }}"></div>
	<div action="user" width="350px">
		<div user-item>
			<div avatar><script>document.write(genIconText('{{$user->NAME}}'))</script></div>
			<div name>{{ $user->NAME }}</div>
			<div sub>{{ $user->getUserRoleNames() }}</div>
		</div>
		<div user-action side-sub>
			<button>{{ trans('Change password') }}</button>
			<button onclick="location.href='/auth/logout'">{{ trans('Logout') }}</button>
		</div>
		<hr separator>
		<div user-setting side-sub>
			<div class="ui header">{{ trans('Settings') }}</div>
			<div content>
				<button onclick="location.href='/me/setting'">{{ trans('Preferences') }}</button>
			</div>
		</div>
		<!--
		<hr separator>
		<div user-task side-sub>
			<div class="ui header">{{ trans('Tasks') }}</div>
			<div content>
				<button>{{ trans('Load') }}</button>
			</div>
		</div>
		<hr separator>
		<div user-setting side-sub>
			<div class="ui header">{{ trans('Activities Logs') }}</div>
			<div content>
				<button>{{ trans('Load') }}</button>
			</div>
		</div>
-->
	</div>
</div>
<!--
<div id="header-container">
	<a id="eblogo" href="/home"><img src="/img/eb2.png" height="40"></a>
	<div id="screen_caption">.</div>
	<ul class="navi" id = "menu_navi"></ul>
	<span action="help"></span>
    <span action="user"></span>
	<div id="user_box">
		<span style="cursor:pointer" onclick="location.href='/me/setting';"><font color="#33b5e8"><span id="textUsername">{{$current_username}}</span></font></span>
		<img src="/img/message.png" chat_available="0" onclick="showChat()" id="user_message">
		<img src="/img/settings.png" onclick="showWorkflow()" height="24" width="24" id="user_workflow">
		<img src="/img/help.png" onclick="showHelp()" title="Help on this function" style="">
		<a href="/auth/logout"><img src="/img/logout.png" title="Log out" style=""></a>
		<div id="wf_notify_box" onclick="showWorkflow()" style="display:none">
			<span id="wf_notify">1</span>
		</div>
	</div>
</div>
-->
<div id="wf_notify_box" onclick="showWorkflow()" style="display:none">
	<span id="wf_notify">1</span>
</div>

<style>
[header] [action="workflow"]:before {
    content: '\39';
}
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-thumb {
  background: #e0e0e0;
  border-radius: 10px;
  border: 1px solid #b0b0b0;
}
::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.06);
}

</style>
<div id="chatBoxContainer" class="sideBox" chat_available="0" chat_enable="{{$configuration['chatEnable']?1:0}}">
	<div id="chatBoxTitle">
		<b>Chat</b>
		<div id="chatSwitch" class="toggleWrapper" onoff="true">
			<input type="checkbox" name="chkChatSwitch" class="mobileToggle" id="chkChatSwitch" {{$configuration['chatEnable']?'checked':''}}>
			<label for="chkChatSwitch"></label>
		</div>
	</div>
	<div id="chatBox"></div>
	<i id="buttonCloseChat" class="icon_x"></i>
</div>

<div id="boxWorkflow" style="display:none;width:100%;height:100%;background:#ffffff;overflow:hidden;">
	<iframe id="iframeWorkflow" onload="loadTasksCounting()" style="border:none;padding:0px;width:100%;height:100%;box-sizing: border-box;"></iframe>
	<div id="wf_loading_box" style="position:absolute;left:0px;top:0px;width:100%;height:100%;background:white;opacity:0.8"><center id="notify_splash"><img class="center_content" src="/images/loading.gif"></center></div>
</div>
<div id="boxHelp" style="display:none;width:100%;height:100%"><img class="center_content" src="/images/loading.gif"></div>
<div id="boxTaskLog" style="display:none;z-index:100;width:100%;height:100%"></div>

<script>
var chatUsers = {};

function showChatUserFunc(o){
	o.css('background', '#e0e0e0');
	var pos = o.position();
	$('#chatUserFuncMenu').css({ top: (pos.top + 10) + 'px', left: (pos.left + 180) + 'px' });
	$("#chatUserFuncMenu").show();
	$("#chatUserFuncMenu").attr('username', o.attr('username'));
	event.stopPropagation();
	//$("#chatUserFuncMenu").detach().appendTo(o).show();
}
function getAvatarName(user){
	var ns = user.name.match(/\b(\w)/g).join('');
	return ns[0]+(ns.length>1?ns[ns.length-1]:'');
}
function buildFriendsList(){
	$("#chatFriendsList").html("");
	for(var username in chatUsers){
		var user = chatUsers[username];
        user['avatar'] = getAvatarName(user);
        if(user['chat'] == undefined) user['chat']=[];
		var friendItem = $('<div class="chatUser" status="'+user.status+'" username="'+username+'">'+
				'<div class="avatar">'+user.avatar+'</div>'+
				'<div class="chatUserFuncs" onclick="showChatUserFunc($(this).parent())">&#x1c;</div>'+
				'<div class="chatMsgCount"></div>'+
				'<div class="chatName">'+user.name+'</div>'+
				'<div class="chatText">'+user.text+'</div>'+
			'</div>');
		$("#chatFriendsList").append(friendItem);
		friendItem.on('click', function(){
			$('.chatUser[chat]').removeAttr('chat');
			$(this).attr('chat', '');
			chatWithUser($(this).attr('username'));
		})
		/*
		.hover(function(){
			var pos = $(this).position();
			$('#chatBox .context_icon').show();
			$('#chatBox .context_icon').css({ top: (pos.top + 10) + 'px', left: '220px' });
		}, function(){
			$('#chatBox .context_icon').hide();
		})
		*/
		;
	}
	$("#chatFriendsList").children(":first").click();
}

function focusSearchList(){
	if(!$("#chatBoxSearch").hasClass('chatBoxSearchFocus'))
		$("#chatBoxSearch").addClass('chatBoxSearchFocus');
}

function hideSearchList(){
	$("#chatSearchInput").val("");
	$("#chatBoxSearch").removeClass('chatBoxSearchFocus');
	$("#chatBoxSearch .chatUserSearch").remove();
	$("#chatBoxSearch hr").hide();
}

function appendMsg(msg){
	var msgItem = $('<div dir="'+msg.d+'" time="'+msg.t+'" status="'+msg.s+'" class="chatMessage">'+msg.m.replace('\n','<br>')+'</div>');
	$("#chatDetail").append(msgItem);
	msgItem.hover(function(){
		var pos = $(this).position();
		$('#chatTooltip .chatTooltipText').html(msg.t);
		$('#chatTooltip').css({ top: (pos.top + 0) + 'px', left: (pos.left - 130) + 'px' });
		$('#chatTooltip').show();
	}, function(){
		//$('#chatTooltip .chatTooltipText').html("");
		$('#chatTooltip').hide();
	})
	;
}

function chatWithUser(username){
	$("#chatSearchInput").blur();
	var user = chatUsers[username];
	$("#chatHeader .chatUser").attr({username: username, status: user.status});
	$("#chatHeader .avatar").html(user.avatar);
	$("#chatHeader .chatName").html(user.name);
	$("#chatHeader .chatText").html(user.status?'Online':'Offline');
	$("#chatHeader .chatText").removeClass(user.status?'color_offline':'color_online').addClass(user.status?'color_online':'color_offline');
	
	//load message
	$("#chatDetail").html("");
	user.chat.forEach(function(msg){
		appendMsg(msg);
	});
}

function sendChatMessage(){
	var s=$("#chatMessageInput").val().trim();
	if(s && s!=""){
		appendMsg({
			t: '2020-01-01 10:05:17',
			d: 0,
			s: 1,
			m: s,
		});
	}
	$("#chatMessageInput").val("");
	$("#chatMessageInput").focus();
	var divChat = document.getElementById('chatDetail');
	$('#chatDetail').animate({
		scrollTop: divChat.scrollHeight - divChat.clientHeight
	}, 300);
}
function onTyping(){
	var s = $("#chatSearchInput").val().toLowerCase();
	if(s==""){
		$("#chatBoxSearch .chatUserSearch").remove();
		$("#chatBoxSearch hr").hide();
		return;
	}
	var u = {};
	for(var username in chatUsers){
		if(username.includes(s) || chatUsers[username].name.toLowerCase().includes(s)){
			u[username] = chatUsers[username];
			if(u.length>=10)
				break;
		}
	}
	
/*	
	$("#chatBoxSearch .chatUserSearch").each(function(){
		if(!u.hasOwnProperty($(this).attr('username')))
			$(this).remove();
	});
	
	for(var username in u){
		if($("#chatBoxSearch .chatUserSearch[username='"+username+"']").length == 0){
			var userItem = $("<div class='chatUserSearch' status='"+(u[username].status?1:0)+"' username='"+username+"'>"+u[username].name+"</div>");
			$("#chatBoxSearch").append(userItem);
			userItem.click(function(){
				chatWithUser($(this).attr('username'));
			});
		}
	}
*/	
	//$("#chatBoxSearch .chatUserSearch").length == 0?$("#chatBoxSearch hr").hide():$("#chatBoxSearch hr").show();
}

function checkSocket(showAlert){
	if(!socket){
		if(showAlert === true)
			alert("Message channel not available");
		return false;
	}
	if(!socket.connected){
		if(showAlert === true)
			alert("Message channel not connected");
		return false;
	}
	return true;
}

function sendChatCommand(command, data){
	if(!checkSocket())
		return;
	if(data == undefined)
		data = {};
	data['command'] = command;
	socket.emit('command', data);
}

function initChat(){
    $("#chatUserFuncMenu").hover(function(){}, function(){
		$(this).hide();
		$(".chatUser[username='"+$(this).attr('username')+"']").css('background', '');
	});

    $("#chatDetail").scroll(function(){
        if($(this).scrollTop() === 0){
             //alert("top");
             $("#chatDetail").prepend('<div class="chatLoading">Loading...</div>');
        }
    });
    
    $("#chatSearchInput")
        .on("input", onTyping)
        .on("keyup", function(e) {
            if (e.keyCode == 13) {
                $("#chatBoxSearch .chatUserSearch").first().click();
                hideSearchList();
            }
            else if (e.keyCode == 27) {
                hideSearchList();
            }
        })
        .focus(focusSearchList)
        .blur(function(){
            setTimeout(hideSearchList,200);
		});

    buildFriendsList();
}
</script>

<script>
var username= '{{$current_username}}';
var xmenu = <?php echo json_encode($xmenu); ?>;
var active_link='{{$currentSubmenu}}';
var html_menu="";
activeTitle = "";
for(var i = 0; i < xmenu.length; i++) {
	var menu=xmenu[i];
	var is_menu_active=false;
	var html_group="";
	for(var i2 = 0; i2 < menu.groups.length; i2++) {
		var group=menu.groups[i2];
		var is_group_active=false;
		var html_item="";
		for(var i3 = 0; i3 < group.items.length; i3++) {
			var item=group.items[i3];
			var is_item_active=false;
			if(active_link.endsWith(item.code)){
				is_item_active=true;
				is_group_active=true;
				is_menu_active=true;
			}
			html_item+='<li'+(is_item_active?' class="activex"':'')+'><a href="/'+item.code+'">'+item.title+'</a></li>';
			activeTitle = is_item_active? item.title : activeTitle;
		}
		html_group+='<div class="navi-column"><div class="navi-cell'+(is_group_active?' activex':'')+'"><h3>'+group.title+'</h3><ul>'+html_item+'</ul></div></div>';
	}
	html_menu+='<li'+(is_menu_active?' class="activex"':'')+'><a>'+menu.title+'</a><div>'+html_group+'</div></li>';
}

document.getElementById("menu_navi").innerHTML += html_menu;
$('#screen_caption').html(activeTitle);
// loadTasksCounting();

function newChatMessageReceived(data){
	console.log(data.from + ': ' + data.message);
	var msg = {
				t: (new Date()).toLocaleString(),
				d: 1,
				s: 1,
				m: data.message,
			};
	//chatUsers[data.from].chat.push(msg);
	//var userEle = $("#chatUser[username='"+data.from+"']");
	//userEle.find(".chatText").html(msg.m);
}

var socket = null;
var socketURL = '//{{$_SERVER['HTTP_HOST']}}:8890/chat';
function turnOnChat(){
	sendChatCommand('CHAT_ON');
}

function turnOffChat(){
	sendChatCommand('CHAT_OFF');
}

var chatEnable = {{$configuration['chatEnable']?'true':'false'}};

loadJsCssFile('/css/chat.css', 'css');

function loadChat(){
	showWaiting();
	sendAjaxNotMessage('/loadChatView', {}, function(data){
		hideWaiting();
		$("#chatBox").html(data);
		chatNeedReload = false;
		//loadJsCssFile('/common/js/chat.js', 'js');
		initChat();
	}, function(data){
		hideWaiting();
		alert(data);
	});	
}

function initSocket(){
	if(socket) return;
	socket = io.connect(socketURL);
	
	socket.on('chat', (data) => {
		newChatMessageReceived(data);
	});

	socket.on('notification', (data) => {
		if(data.code == 'REG_USER'){
			if(data.status == true){
				$("#user_message").attr('chat_available', 1);
				$("#chatBoxContainer").attr('chat_available', 1);
			}
			else
				console.log('REG_USER fail. ' + data.message);
		}
		else if(data.code == 'CHAT_ENABLE'){
			if(!chatEnable && data.status)
				isSideBoxVisible($("#chatBoxContainer"))?loadChat():chatNeedReload=true;
			chatEnable = data.status;
			!chatEnable?chatNeedReload = false:null;
			$("#chkChatSwitch").prop('checked', chatEnable);
			$("#chatBoxContainer").attr('chat_enable', chatEnable?1:0);
		}
	});

	socket.on('connect', () => {
		console.log('Socket ID: '+socket.id);
		sendAjaxNotMessage('/registerClient', {socket_id: socket.id}, function(data){
			console.log('registerClient ok', data);
		}, function(data){
			console.log('Error registerClient', data);
		});	
		//socket.emit('register', username);
		/*
		privateChannel = socket.id;
		socket.emit('create_channel', privateChannel);
		socket.on(privateChannel, function (data) {
			console.log('privateChannel: ' + data);
		});
		*/
	});

	socket.on('disconnect', () => {
		$("#user_message").attr('chat_available', 0);
		$("#chatBoxContainer").attr('chat_available', 0);
	});
}

var chatNeedReload = {{$configuration['chatEnable']?'true':'false'}};

function showSideBox(box){
	$(box).css({left: 'calc(100% - ' + $(box).width() + 'px)', opacity: 1});
}

function hideSideBox(box){
	$(box).css({left: '100%', opacity: 0});
}

function isSideBoxVisible(box){
	return ($(box).css('opacity') == 1);
}

function showChat(visible){
	var box = $("#chatBoxContainer");
	if(isSideBoxVisible(box) || visible === false)
		hideSideBox(box);
	else {
		checkSocket(true);
		showSideBox(box);
		if(chatNeedReload)
			loadChat();
	}
}

$("#chkChatSwitch").change(function() {
	this.checked?turnOnChat():turnOffChat();
});

$("#buttonCloseChat").click(() => {
	showChat(false);
});

$(document).ready(function(){
	$('[header] [action]').click(function(){
		EB.doHeaderAction($(this).attr('action'));
	});
	
	$('[header] [eb]').click(function(){
		location.pathname != '/' && (location.href = '/');
	});

	$('close').click(()=>{
		EB.hideSideBox();
	});
});

//initSocket();
</script>
