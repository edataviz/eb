<?php
$currentSubmenu ='/am/userlog';
$mainTab        = 'LogUser';
$tables         = [$mainTab	=>['name'=>'List Log']];
$lastFilter	    =  "User";
?>

@extends('core.admin')

@section('adaptData')
    @parent
    <script>
        actions.loadUrl = "/am/loaduserlog";
        $(function() {
            $(".date_input").css("width","122px");
        });
    </script>
@stop


