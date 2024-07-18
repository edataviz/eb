<?php
if(!isset($nojquery)) $nojquery = false;
?>
<!DOCTYPE html>
<html>
<head>
<title>Energy Builder</title>
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<meta name="keywords" content="oil,gas,energy,production,HCA" />
<meta name="author" content="eDataViz" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="shortcut icon" href="/favicon.ico">
@section('head')
<link rel="stylesheet" href="/css/default.css">
@stop
@if(!$nojquery)
<script src="/lib/jquery/jquery-1.9.1.js"></script>
<script>
$.ajaxSetup({
	headers: {
		'X-XSRF-Token': "{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}"
	}
});
@endif
</script>
@yield('head')
</head>
<body>
@yield('body')
@yield('script')
</body>
</html>