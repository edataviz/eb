<!DOCTYPE html>
<html>
<head>
<title>Energy Builder</title>
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<meta name="keywords" content="oil,gas,energy,production,HCA" />
<meta name="author" content="eDataViz" />
<meta name="_token" content="{{ app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token()) }}" />
<link rel="shortcut icon" href="/favicon.ico"> 
<link rel="stylesheet" href="/semantic-ui/semantic.min.css">
<link rel="stylesheet" href="/css/base.css">
<script src="/js/jquery-1.9.1.js"></script>
<script src="/js/base.js"></script>
<script>
@if(Auth::check())
	EB.isLoggedIn = true;
	EB.user = {
			name: '{!!$user->FIRST_NAME.($user->MIDDLE_NAME?' '.$user->MIDDLE_NAME.' ':' ').$user->LAST_NAME!!}',
			username: '{{$user->username}}',
			email: '{{$user->EMAIL}}',
			roles: ['Administrator', 'Operator'],
		}	
@else
	EB.isLoggedIn = false;
	EB.user = null;
@endif
</script>
</head>
<body>
@if(Auth::check())
	@include("eb.main")
@else
	@include("eb.login")
@endif
</body>
</html>