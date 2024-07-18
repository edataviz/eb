<?php
$xmenu = \Helper::generateHomeMenu();//dd($xmenu);

$SSO_Username =  config('constants.enableSSO')&&array_key_exists('REMOTE_USER',  $_SERVER)?$_SERVER['REMOTE_USER']:"";
$i = strpos($SSO_Username, '\\');
if($i){
	$SSO_Username = substr($SSO_Username, $i+1);
}
$logo = config('constants.logo');
$copyright = config('constants.Copyright');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Energy Builder</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <meta name="keywords" content="oil,gas,energy,production" />
        <meta name="author" content="edataviz" />
		<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
		<link rel="shortcut icon" href="../favicon.ico"> 
		<link rel="stylesheet" href="/common/css/hexagon.css">
		<link rel="stylesheet" href="/common/css/jquery-ui.css">
		<link rel="stylesheet" href="/common/css/style.css">

	    <script src="/common/js/jquery-1.9.1.js"></script> 
		<script src="/common/js/jquery-ui.js"></script>
		<style>
		#boxUserInfo{display:none}
		#bee{
			z-index:1000;
			display:block;
			position: absolute;
			width: 56px;
			height: 54px;
			left:50%;
			margin-left:1px;
			top:490px;
			transition: left 1500ms ease-in, top 1500ms ease-out;
		}
		.alert-box {
		    color:#f5fb00;
		    border-radius:10px;
 		    font-family:Tahoma,Geneva,Arial,sans-serif;font-size:14px;
		    padding:10px 36px 10px 36px;
		    margin:10px;
		}
		
		.warning {
		    display: table;
		    text-align: center;
			margin: 0 auto;
		    background:#756e6e  no-repeat 10px 50%;
		}
		.warning span {
		     display: inline-block;
		}
		</style>
	</head>
<body style="background-image:url('/img/bg2.png')">
<script>var func_code="ROOT";</script>
@include('partials.user')

<div id="hex_logo">
	<img border="0" src="../img/eb2.png?1" >
</div>

<img id="bee" border="0" src="../img/bee.png">

<div id="poweredBy">	
	<div class="hex" style="background:#ffffff">
	<div class="inner" style="color:#333">
<table style="border-collapse: collapse;width: 100%;position:absolute;height:172px;top:-50%;">
  <tbody><tr>
     <td style="text-align: center; vertical-align: middle;padding:0">
	{{ $logo['TextTop'] }}
	<img src="../img/{{ $logo['HomeLogoImage'] }}" style="max-width:150px;max-height:172px">
	<font size="1">{{ $logo['TextBottom'] }}</font>
	</td>
  </tr>
</tbody></table>
	</div>
	<a target="_blank" href="{{ $logo['HomeLogoURL'] }}"></a>
	<div class="corner-1"></div>
	<div class="corner-2"></div>
	</div>	
</div>
<div id="shadow_box">
	<img src="../img/shadow.png">
	<div id="ebFooter" style="text-align: center; padding: 3px; color: #fff;">
		<font face="Arial" size="1">{{ $copyright }}</font>
	</div>
</div>
<div class="hex_container" style="z-index:100" id="boxLogin">
	<div class="hex hex_disabled hex-gap" id="cell1">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>
	
	<div class="hex hex_disabled" id="cell2">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>


	<div class="hex hex_disabled" id="cell3">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled" id="cell7">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled" style="background:#666">
		<div class="inner">
		<h4>Username</h4>
		<input class="r_textbox" type="text" style="width:120px;" id="username" name="username" value="" />
		<div style="margin-left:13px;width:120px;height:3px;border:1px solid #d08924;border-top:none"></div>
		</div>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled" style="background:#666">
		<div class="inner">
		<h4>Password</h4>
		<input class="r_textbox" type="password" style="width:120px;" value="" id="password" name="password" />
		<div style="margin-left:13px;width:120px;height:3px;border:1px solid #d08924;border-top:none"></div>
		</div>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>
	
	<div class="hex hex_disabled" id="cell4">
		<div class="v_top">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	<div class="hex hex_disabled hex-gap" id="cell6">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
	
	<div class="hex hex-login">
		<div class="inner">
			<img style="display:none;position: absolute; z-index: 200;top:80px;left:85px;" width="47" border="0" src="../img/bee.png">
			<h4>LOG IN</h4>
		</div>
		<a href="javascript:logineb()"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>

	
	<div class="hex hex_disabled" id="cell5">
		<div class="inner">
		</div>
		<a href="#"></a>
		<div class="corner-1"></div>
		<div class="corner-2"></div>
	</div>	
	
</div>

<div id="ssoUserDialog" style="display:none;">
<span id="ssoUserContent"  style="font-size: 16pt;"></span>
</div>

@if($action)
<div id="notification" class="alert-box warning">
	<span class="">{{$action['message']}}</span></br>
	<span class="">Click <a href="{{$action['url']}}">here</a> to resolve.</span>
</div>
@endif

<div class="hex_container" style="display:none" id="boxFunctions"></div>
<script>
    var height = $( window  ).height();
    $('#ebFooter').css('margin-top',height - 635);
    $( window ).resize(function() {
        var h = $( window  ).height();
        $('#ebFooter').css('margin-top',h - 635);
    });
</script>
<script>
$.ajaxSetup({
    headers: {
        'X-XSRF-Token': $('meta[name="_token"]').attr('content')
    }
});

$(document).ready(function () {
	var submenu = '{{$menu}}';
    boxFunctions(menu);
    if(typeof(submenu) !== "undefined" && submenu!=''){
        func("#func_"+submenu);
	};
});

	function logout(){
		window.location.href="../auth/logout";
	}
	var is_logging_in=false;
	var curCell=0;
	
	function randomCell(){
		if(curCell>0){
			$( "#cell"+curCell ).css('background-color', '#ccc');
		}
		if(!is_logging_in) { return; }
		curCell++; if(curCell>7) curCell=1;
		$( "#cell"+curCell ).css('background-color', '#d08924');
		setTimeout('randomCell()',200);
	}

$('#username').select();
$('#username').focus();
$("#password").keyup(function(e){ 
    var code = e.which; // recommended to use e.which, it's normalized across browsers
    if(code==13)e.preventDefault();
    if(code==32||code==13||code==188||code==186){
        logineb();
    } 
});

function layoutUserLoggedIn(ani)
{
	$("#notification").css("display","table");
	$('#boxUserInfo').show();
	$("#boxFunctions").show();
	$("#boxLogin").hide();
	//$("#menu_holder").show();
	$("#bee").show();
	if(ani)
	{
		$("#bee").css({left:-100, top: -100});
/* 		$( "#bee" ).stop().animate({
				left: "-=875",
				top: "-=900"
			}, 2000, function() { //animation complete, then rotate
				$("#bee").animate(
				  {rotation:90},
				  {
				    duration: 1000,
				    step: function(now, fx) {
				      $(this).css({"transform": "rotate("+now+"deg)"});
				    }
				  }
				);					
			});
 */	}
	else $( "#bee" ).hide();
}
function layoutUserLoggedOut()
{
	window.location.reload();
}
var menuBox;
var submenu_idx={};
submenu_idx["1"] = [4,5,2,6,8,9,3,10,7];
submenu_idx["2"] = [5,6,1,3,9,4,7,8,10];
submenu_idx["3"] = [6,7,2,5,10,9,1,4,8];
submenu_idx["4"] = [5,8,1,6,9,2,7,10,3];
submenu_idx["5"] = [4,1,8,6,2,9,7,3,10];
submenu_idx["6"] = [5,2,9,7,3,10,1,8,4];
submenu_idx["7"] = [6,10,3,2,9,5,1,8,4];
submenu_idx["8"] = [4,5,9,6,1,2,10,7,3];
submenu_idx["9"] = [5,6,8,10,2,4,7,1,3];
submenu_idx["10"] = [6,7,9,5,3,2,8,1,4];
var menu 			= <?php echo json_encode($xmenu); ?>;
function showMainMenu(ind){
/* 	$( "#boxFunctions" ).fadeIn( 500, function() {
	});
	$( "#boxMenu" ).fadeOut( 500, function() {
	});
 */
	$(".menu").each(function(){
		$(this).attr("class","menu "+$(this).attr("base_class"));
		$(this).find("#menu_text").html($(this).attr("base_text"));
		//$(this).find("a").attr("href","#");
		$(this).attr("url","");
		if($(this).attr("index") != ind)
			$(this).css("background-color", "");
		//$(this).css("opacity","1");
	});
}
var bg_color;
function func(item)
{
	var menu_item = $(item);
	if(menu_item.hasClass("hex_dim") || menu_item.hasClass("hex_disabled"))
		return;
	if(menu_item.attr("back")=='1')
	{
		menu_item.attr("back","");
		menu_item.find("#menu_back").html("");
		showMainMenu(menu_item.attr("index"));
		return;
	}
	
	var url = menu_item.attr("url");
	if(typeof url == "string" && url.length > 0){
		window.location = url;
		return;
	}

	var menuCode=menu_item.attr("code");
	if(menu[menuCode] == undefined) return ;
	if(menu[menuCode].sub == undefined) return ;
	var a=menu[menuCode].sub;
	
	if(a.length > 0){
		var menu_item_index=menu_item.attr("index");
		menu_item.attr("back","1");
		menu_item.find("#menu_back").html("<hr>HOME");
		
		for(var i=0;i<submenu_idx[menu_item_index].length;i++){
			var m = $("#func_"+submenu_idx[menu_item_index][i]);
			if(i < a.length){
				m.removeClass("hex-1").removeClass("hex-2").removeClass("hex-3").removeClass("hex_disabled").addClass("hex-m");
				if(a[i]["text"]){
					m.find("#menu_text").html(a[i]["text"]);
					m.attr("url",a[i]["url"]);
					if(a[i]["url"]==""){
						m.removeClass("hex-m").addClass("hex_disabled");
					}
					else{
						bg_color = shadeBlendConvert(0.2, getBgColor(menu_item));
						m.css("background-color", bg_color);
					}
				}
				else {
					var h4='';
					a[i].forEach(function(item, index) {
						if(item["text"]) {
							h4 += (h4==''?'':'<hr>')+'<span onclick="window.location=\''+item['url']+'\'">'+item['text']+'</span>';
						}
					});
					m.find("#menu_text").html(h4);
					m.attr("url",'#');
					bg_color = shadeBlendConvert(0.2, getBgColor(menu_item));
					m.css("background-color", bg_color);
				}
			}
			else{
				m.removeClass("hex-1").removeClass("hex-2").removeClass("hex-3").removeClass("hex_disabled").addClass("hex_dim");
			}
			//menu.css("opacity","1");
		}
	}
}

function doLogin(usn,pw,type){
	is_logging_in=true;
	randomCell();
	var authentication = {username:usn,password:pw};
	authentication.type = type!==undefined?type:"basic";

		$.ajax({
			url: '/auth/eblogin',
			type: "post",
			data: authentication,
			success: function(data){
				is_logging_in=false;
				var _redirect = false;
				if(_redirect) 
					window.location.href=_redirect;
				else{
					if(data.menu == undefined){
						location.reload();
						return;
					}
					boxFunctions(data.menu);
					layoutUserLoggedIn(true);
					$('#textUsername').html(usn);
					loadTasksCounting();
					language	= data.language;
					oldLanguage	= "{{session('locale')}}";
					if(typeof language == 'string' &&language!=oldLanguage) location.reload();
				}

				if(type=="sso"&&(data.wasRecentlyCreated==true||data.wasRecentlyCreated=='true')){
					$("#ssoUserContent").html("Your account has been created in Engergy Builder system with limited access. Please contact administrator to be granted more rights.");
					var dialogOptions = {
							height	: 280,
							width	: 450,
							position: { my: 'top', at: 'top+150' },
							modal	: true,
							buttons	: {
								"Continue" 	: {
									 text	: "Continue",
									 id		: "ssoUserDialogContinueButton",
									 "class": "dialogButtonCancel",
									 click: function(){
											$("#ssoUserDialog").dialog("close");
											if(data.dashboard) window.location.href='/dashboard';
									 }  
								}
							}
						};
						$("#ssoUserDialog").dialog(dialogOptions);
				}
				else
					if(data.dashboard) window.location.href='/dashboard';
			},
			error: function(data) {
				is_logging_in=false;
				if(data.responseText=='Wrong request'){
					//alert("Retrying...");
					setTimeout(function(){doLogin(usn,pw,type);},1);
					return;
				}
				alert(data.responseText);
			}
		});
}

function logineb(){
	if(!$('#username').val()){
		alert('Please input username');
		$('#username').select();
		$('#username').focus();
		return;
	}
	if(!$('#password').val()){
		alert('Please input password');
		$('#password').select();
		$('#password').focus();
		return;
	}
  	var usn = $('input[name=username]').val();
  	var pw = $('input[name=password]').val();
  	doLogin(usn,pw);
}

function boxFunctions(xmenu){
	if(xmenu == undefined) return;
    $("#boxFunctions").empty();
    menu = xmenu;
    for ( var i = 1; i <= 10; i++ ) {
        var cmenu = xmenu[i-1];
        var text 			= '';
        var enabled 		= false;
        var class_attr 		= "hex ";
        if(cmenu!==undefined){
            text = cmenu["text"];
            enabled = cmenu["display"] == 1;
        }

        if(enabled){
            if(i == 1 || i == 6 || i == 8)
                class_attr += " hex-1";
            else if(i == 2 || i == 4 || i == 7 || i == 9)
                class_attr += " hex-2";
            else
                class_attr += " hex-3";
        }
        else{
            class_attr += " hex_disabled";
        }
      	if(i == 1 || i == 8){
            class_attr += " hex-gap";
		}

        var code  = i - 1;
        var id = "func_" + i;

        var smenu = $('<div class="menu '+class_attr+'" base_class="'+class_attr+'" base_text="'+text+'" id="'+id+'" index="'+i+'" code="'+code+'" onclick="func(this)">');
        var menu_child_1 = $('<div class="inner" id = "'+code+'">');
        var menu_child_2 = $('<div class="corner-1"></div>');
        var menu_child_3 = $('<div class="corner-2"></div>');
        var menu_child_h = $('<h4><span id="menu_text">'+text+'</span><span id="menu_back"></span></h4>');
        menu_child_1.appendTo(smenu);
        menu_child_2.appendTo(smenu);
        menu_child_3.appendTo(smenu);
        menu_child_h.appendTo(menu_child_1);
        smenu.appendTo($("#boxFunctions"));
    }
    $("#boxFunctions > div.menu").each(function(){
        $(this).hover(function(){
            if($(this).hasClass("hex_dim") || $(this).hasClass("hex_disabled")) return;
            $(this).attr("last-color", getBgColor(this));
            $(this).css("background-color", shadeBlendConvert($(this).hasClass("hex-m")?0.2:0.35,$(this).attr("last-color")));
        }, function(){
            if($(this).hasClass("hex_dim") || $(this).hasClass("hex_disabled")) return;
            if($(this).hasClass("hex-m"))
                $(this).css("background-color", $(this).attr("last-color"));
            else
                $(this).css("background-color", "");
        });
    });
}

function shadeBlendConvert(p, from, to) {
    if(typeof(p)!="number"||p<-1||p>1||typeof(from)!="string"||(from[0]!='r'&&from[0]!='#')||(typeof(to)!="string"&&typeof(to)!="undefined"))return null; //ErrorCheck
    if(!this.sbcRip)this.sbcRip=function(d){
        var l=d.length,RGB=new Object();
        if(l>9){
            d=d.split(",");
            if(d.length<3||d.length>4)return null;//ErrorCheck
            RGB[0]=i(d[0].slice(4)),RGB[1]=i(d[1]),RGB[2]=i(d[2]),RGB[3]=d[3]?parseFloat(d[3]):-1;
        }else{
            if(l==8||l==6||l<4)return null; //ErrorCheck
            if(l<6)d="#"+d[1]+d[1]+d[2]+d[2]+d[3]+d[3]+(l>4?d[4]+""+d[4]:""); //3 digit
            d=i(d.slice(1),16),RGB[0]=d>>16&255,RGB[1]=d>>8&255,RGB[2]=d&255,RGB[3]=l==9||l==5?r(((d>>24&255)/255)*10000)/10000:-1;
        }
        return RGB;}
    var i=parseInt,r=Math.round,h=from.length>9,h=typeof(to)=="string"?to.length>9?true:to=="c"?!h:false:h,b=p<0,p=b?p*-1:p,to=to&&to!="c"?to:b?"#000000":"#FFFFFF",f=sbcRip(from),t=sbcRip(to);
    if(!f||!t)return null; //ErrorCheck
    if(h)return "rgb("+r((t[0]-f[0])*p+f[0])+","+r((t[1]-f[1])*p+f[1])+","+r((t[2]-f[2])*p+f[2])+(f[3]<0&&t[3]<0?")":","+(f[3]>-1&&t[3]>-1?r(((t[3]-f[3])*p+f[3])*10000)/10000:t[3]<0?f[3]:t[3])+")");
    else return "#"+(0x100000000+(f[3]>-1&&t[3]>-1?r(((t[3]-f[3])*p+f[3])*255):t[3]>-1?r(t[3]*255):f[3]>-1?r(f[3]*255):255)*0x1000000+r((t[0]-f[0])*p+f[0])*0x10000+r((t[1]-f[1])*p+f[1])*0x100+r((t[2]-f[2])*p+f[2])).toString(16).slice(f[3]>-1||t[3]>-1?1:3);
}
function getBgColor(o){
	var color;
	if($(o).hasClass("hex-1"))
		color = "#d08924";
	else if($(o).hasClass("hex-2"))
		color = "#d46247";
	else if($(o).hasClass("hex-3"))
		color = "#3271b2";
	else
		color = bg_color;
	return color;
}
$(".menu").each(function(){
	$(this).hover(function(){
			if($(this).hasClass("hex_dim") || $(this).hasClass("hex_disabled")) return;
			$(this).attr("last-color", getBgColor(this));
			$(this).css("background-color", shadeBlendConvert($(this).hasClass("hex-m")?0.2:0.35,$(this).attr("last-color")));
		}, function(){
			if($(this).hasClass("hex_dim") || $(this).hasClass("hex_disabled")) return;
			if($(this).hasClass("hex-m"))
				$(this).css("background-color", $(this).attr("last-color"));
			else
				$(this).css("background-color", "");
	});
});
	</script>

	@if((session('statut') != null) && (session('statut') != '') && session('statut') != 'visitor')
		<script type="text/javascript">
			layoutUserLoggedIn();
		</script>
	@else
		<script type="text/javascript">
			$('#boxUserInfo').hide();
			$("#notification").css("display","none");
			
			@if($SSO_Username)
				var SSO_Username 	= <?php echo json_encode($SSO_Username); ?>;
				$("#ssoUserContent").html("System detects logged in user <b> "+SSO_Username+"</b> .<br> Do you want to continue with this user ?");
				var dialogOptions = {
					height	: 250,
					width	: 400,
					position: { my: 'top', at: 'top+150' },
					modal	: true,
					buttons	: {
						"Yes": {
					         text	: "Yes",
					         id		: "ssoUserDialogYesButton",
					         "class": "dialogButtonCancel",
					         click	: function(){
									$("#ssoUserDialog").dialog("close");
					        	 	doLogin(SSO_Username,"","sso");
					         }  
						},
						"Cancel" 	: {
					         text	: "No, login by other user",
					         id		: "ssoUserDialogCancelButton",
					         "class": "dialogButtonCancel",
					         click: function(){
									$("#ssoUserDialog").dialog("close");
					         }  
						}
					}
				};
				$("#ssoUserDialog").dialog(dialogOptions);
			@endif
		</script>
	@endif

</body>
</html>