@section('sliding_menu')
<script>
$( document ).ready(function() {
	$("#ebfilter").height($(document).height());
	$("#ebfilter").width(151);
	$('#ebfilter').css('left','-160px');
    setTimeout( function(){
        $('#ebfilter').css('left','-160px');
    },10000); <!-- Change 'left' to 'right' for panel to appear to the right -->
});

</script>

<style>

/*****ANIMATONS (optional)*****/

#ebfilter, #ebfilter .arrow {
	transition: all 0.4s;
	-o-transition: all 0.4s;
	-moz-transition: all 0.4s;
	-webkit-transition: all 0.4s;
}

/*****END ANIMATONS*****/


/*****PANEL*****/

#ebfilter {
    background-color: #E6E6E6;
	padding: 5px;
	position: fixed;
	z-index: 100000;
	
	box-shadow: 4px 0 10px rgba(0,0,0,0.25);
	-moz-box-shadow: 4px 0 10px rgba(0,0,0,0.25);
	-webkit-box-shadow: 4px 0 10px rgba(0,0,0,0.25);
	left: 0; /* Change to right: 0; if you want the panel to display on the right side. */
}

#ebfilter:hover, #ebfilter:focus {
	left: 0 !important; /* Change to right: 0 !important; if you want the panel to display on the right side. */
}

#ebfilter .arrow {
	right: 2px; /* Change to left: 2px; if you want the panel to display on the right side. */
}

#ebfilter .arrow {
	font: normal 400 25px/25px 'Acme', Helvetica, Arial, sans-serif; /* Acme font is required for .arrow */
	color: rgba(0,0,0,0.75); /* Arrow color */
	width: 16px;
	height: 25px;
	display: block;
	position: absolute;
	top: 20px;
	cursor: default;
}

#ebfilter:hover .arrow {
	transform: rotate(-180deg) translate(6px,-3px);
	-moz-transform: rotate(-180deg) translate(6px,-3px);
	-webkit-transform: rotate(-180deg) translate(6px,-3px);
}

/*****END PANEL*****/
</style>
@stop