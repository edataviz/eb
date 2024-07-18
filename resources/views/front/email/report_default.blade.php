{{--$subject='[Report] '.$data['ReportName'].' - '.date('d F Y');--}}
<html>
<head>
<style>
* {font-weight: normal}
</style>
</head>
<body>
Report: <b>{{$ReportName}}</b><br>
<i>Generated at: <?php echo date('Y-m-d H:i:s') ?></i>
</body>
</html>