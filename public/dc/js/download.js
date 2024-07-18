function init() {
	var date = getDateFormatted();
	$("#dteFilter").val(date);
	//downloadData();
}

function getDateFormatted() {
	// GET CURRENT DATE
	var date = new Date();
	// GET YYYY, MM AND DD FROM THE DATE OBJECT
	var yyyy = date.getFullYear().toString();
	var mm = (date.getMonth()+1).toString();
	var dd  = date.getDate().toString();
	// CONVERT mm AND dd INTO chars
	var mmChars = mm.split('');
	var ddChars = dd.split('');
	// CONCAT THE STRINGS IN YYYY-MM-DD FORMAT
	var datestring = yyyy + '-' + (mmChars[1]?mm:"0"+mmChars[0]) + '-' + (ddChars[1]?dd:"0"+ddChars[0]);
    return datestring;
}

function downloadData(date) {
	if($('#btn-download').html()!="DOWNLOAD"){
		return;
	}
	if(!checkNetworkStatus())
	{
		return;
	}
	var dc_data_type = $("input[name='dc_data_type']:checked"). val();
	$('#btn-download').html("DOWNLOADING ...");
	doAjax('response.php', "post",
		{
			date_filter: date,
			data_type: dc_data_type
		}, 'Downloading data...',
		function(data) {
			if(getData(data))
			{
				clearOfflineData();
				initOfflineData();
				writeParameter('downloaded',1);
			}
			alert('Configurations downloaded successfully.');
			window.location.assign('routes.html');
		},
		function(data) {
			console.log(data.responseText);
			alert('Download fail. Please check network connection to Energy Builder system');
		},
		function(){
			$('#btn-download').html("DOWNLOAD");
		},
	);
}
