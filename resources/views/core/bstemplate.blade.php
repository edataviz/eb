<?php 
$configuration	= isset($configuration)?$configuration:auth()->user()->getConfiguration();
$request 		= request();
$parameters 	= $request->route()->parameters();
$rightCode		= isset($parameters['rightCode'])?$parameters['rightCode']:"";
$enableHeader	= isset($enableHeader)	? $enableHeader	:true;
$enableFooter	= isset($enableFooter)	? $enableFooter	:true;
$loadJQuery		= isset($loadJQuery)	? $loadJQuery	:true;

?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->

<head>

<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<title>ENERGY BUILDER</title>
<meta name="description" content=""/>
<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
<link rel="stylesheet" href="/common/css/jquery-ui.css" />
<link rel="stylesheet" href="/common/css/jquery.dataTables.css"/>
@if($loadJQuery)
<script>
	if(!window.jQuery){
		document.write('<script type="text/javascript" src="'+ '/common/js/jquery-2.1.3.js' + '"></scr' + 'ipt>'); 
	}
</script>
@endif
<script type="text/javascript" src="/common/js/jquery.dataTables.js"></script> 
<script type="text/javascript" src="/common/js/dataTables.fixedColumns.min.js"></script>
<script type='text/javascript'>
var configuration =  <?php echo json_encode($configuration); ?>;
var func_code='{{$rightCode}}';
var jsFormat = configuration['picker']['DATE_FORMAT_JQUERY'];//'mm/dd/yy';
var ebtoken = $('meta[name="_token"]').attr('content');
$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

</script>
<script src="/common/js/moment.js"></script>
<script type="text/javascript" src="/common/js/utils.js?19"></script>
<script src="/common/js/jquery-ui.js"></script>
<script src="/common/js/socket.io.js"></script>

@yield('script')
<link rel="stylesheet" href="/common/css/style.css?9" />
</head>
@section('extensionCss')
<style>
.documentBody{
 	overflow-x:hidden
 }
</style>
@stop

@yield('extensionCss')

<body class="documentBody" style="margin:0;background-color: #f6f6f6">
	@yield('floatWindow')
	@if($enableHeader)
		<header role="banner">
			@include('partials.header')
			@yield('header')
		</header>
	@endif
	
	<main role="main" class="contentContainer"> 
		@yield('main') 
	 </main>

	@if($enableFooter)
		@include('partials.footer')
	@endif
	@yield('modalWindow')
</body>
</html>