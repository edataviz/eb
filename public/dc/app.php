<!DOCTYPE html>
<html manifest="eb.manifest">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link rel="apple-touch-icon" href="image/ebhome.png" />
	<link rel="apple-touch-startup-image" href="image/home.png" />
	<meta name="apple-mobile-web-app-title" content="Data Capture">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
<style>
*{font-family:Arial}
</style>
</head>
<body style="margin:0">
<div style="position:absolute;width:100%;height:100%">
<table width="100%" height="100%"><tr><td align="center">
<h3>ENERGY BUILDER DATA CAPTURE</h3>
<div id="cache" style="display:none">
<img src="css/images/ajax-loader.gif">
<br>
<span id="message"></span>
</div>
<br>
<br>
<div id="nocache" style="display:none">
You are using a mobile device. It is recommended to create Home Screen shortcut for this.
<br>
<div id="iOS" style="display:none;width:320px;height:120px;overflow-x:auto;overflow-y:hidden;white-space: nowrap;">
<img src="image/ios.png" style="height:100%">
<img src="image/ios3.png" style="height:100%">
<img src="image/ios4.png" style="height:100%">
</div> 
<br>
<button id="goapp" style="display:none" onclick="window.location='main.html';">Skip, go to app now!</button>
</div>
</td></tr></table></div>
<script>
var isStandalone = (window.navigator.standalone==true);
//alert('isStandalone: '+isStandalone);
var isMobile = (typeof window.orientation !== 'undefined');
//alert('isMobile: '+isMobile);
var isIOS = (navigator.userAgent.match(/iPhone|iPad|iPod/i)!==null);
//alert('isIOS: '+isIOS);
var forceCaching = (isStandalone || !isMobile);
//alert('forceCaching: '+forceCaching);

//if(forceCaching)
document.getElementById('cache').style.display="";
if(!isStandalone && isMobile){
	if(isMobile)
		document.getElementById('nocache').style.display="";
	if(isIOS)
		document.getElementById('iOS').style.display="";
}

var message=document.getElementById('message');
var webappCache = window.applicationCache;
var cacheItemsCount = 28;
var count = 0;
function beginApp(){
	document.getElementById('cache').style.display="none";
	if(forceCaching)
		window.location="main.html";
	else
		document.getElementById('goapp').style.display="";
}
function log(s){
	message.innerHTML = s;
}
function noupdateCache() {
	log("No update to cache found");
	beginApp();
}
function doneCache() {
	log("Application has finished downloading");
	beginApp();
}
function progressCache() {
	count++;
	//message.innerHTML = Math.round(count/cacheItemsCount)+'%';
	log("Downloading application... "+Math.round(100*count/cacheItemsCount)+'%');
}
function updateCache() {
	webappCache.swapCache();
	log("Cache has been updated due to a change found in the manifest");
	beginApp();
}
function errorCache() {
	log("You're either offline or something has gone horribly wrong.");
	beginApp();
}
webappCache.addEventListener("progress", progressCache, false);
webappCache.addEventListener("cached", doneCache, false);
webappCache.addEventListener("noupdate", noupdateCache, false);
webappCache.addEventListener("updateready", updateCache, false);
webappCache.addEventListener("error", errorCache, false);
</script>	
</body>
</html>
