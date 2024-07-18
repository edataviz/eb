<script>
	var editBox = {	fields : [],
					enableRefresh:false,
					hidenFields : [],
					};
	
	var closeEditWindow = function() {
		$('#divEditGroup').hide('fast');
		$.each(editBox.fields, function( index, value ) {
    		delete actions.editedData[value];
        });
	};

 	editBox.initExtraPostData = function (id,rowData){
 	 		return 	{id:id};
 	 	}
 		
	var editId = false;
 	editBox.editRow = function (id,rowData){
	    	$('#tableEditGroup').html("<p> Loading...</p>");
	    	$('#tableEditGroup').css("display", "block");
	    	$('#contentview').css("display", "none");
	    	$('#divEditGroup').show("fast");
//	    		$('#table_editrow').html("");
	    	$('#cationEditGroup').html(rowData.CODE);
	    	editDataPosting = editBox.initExtraPostData(id,rowData);
	    	$.each(editBox.fields, function( index, value ) {
	    		delete actions.editedData[value];
	        });
	    	$.ajax({
	    		url: editBox.loadUrl,
	    		type: "post",
	    		data: editDataPosting,
	    		success:function(data){
	    			$('#tableEditGroup').css("display", "none");
	    			$('#contentview').css("display", "block");
	    			editId = id;
	    			editBox.editGroupSuccess(data,id);

	    			console.log ( "success:function editRow "+data );
	    		},
	    		error: function(data) {
	    			$('#tableEditGroup').html(JSON.stringify(data));
	    			console.log ( "error editRow "+JSON.stringify(data) );
	    		}
	    	});
	    }

    var saveEditGroup = function() {
    	if(editId&&editId!=null){
    		isEmpty = true;
    		$.each(editBox.fields, function( index, value ) {
    			isEmpty= isEmpty&&(!actions.editedData.hasOwnProperty(value));
       		 });
      		if(isEmpty) {
        		alert('data is empty');
        		return;
        	}
    		showWaiting();
    		editData = {id:editId};
    		$.each(editBox.fields, function( index, value ) {
    			editData[value] = actions.editedData[value];
       		 });
      		 
    		$.ajax({
    			url: editBox.saveUrl,
    			type: "post",
    			data: editData,
    			success:function(data){
    				console.log ( "success saveEditGroup "+JSON.stringify(data) );
    				alert(JSON.stringify(data));
    				hideWaiting();
    				closeEditWindow();
    				if(editBox.enableRefresh) actions.doLoad(true);
    			},
    			error: function(data) {
    				hideWaiting();
    				console.log ( "error saveEditGroup ");
    			}
    		});
    	}
    	else{
    		alert('data is empty');
    	}
    }


    var renderSumRow = function (api,columns){
    	$.each(columns, function( i, column ) {
            total = 0;
            $.each(api.columns(column).data()[0], function( index, value ) {
            	total += intVal(value);
			});
            // Update footer
            $( api.columns(column).footer() ).html(total.toFixed(3));
		});
    }
</script>
@yield('editBoxParams')

<div style="background:#eee;border:2px solid #666;display:none;position: fixed; width: 950px; height: 430px; z-index: 1; left:50%; margin-left:-450px; top:145px" id="divEditGroup">
	<div onClick="saveEditGroup()" style="cursor:pointer; position: absolute; right:72px;top:-27px;border:2px solid #666;background:#eee; width: 82px; height: 23px;line-height:23px; z-index: 1" id="layer1">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font size="2">Save</font></div>
	<div onClick="closeEditWindow()" style="cursor:pointer;position: absolute; right:-2px;top:-27px;border:2px solid #666;background:#eee; width: 75px; height: 23px;line-height:23px; z-index: 1" id="layer1">&nbsp;&nbsp;&nbsp;&nbsp;
		<font size="2">Close</font>
	</div>
	<div id="contentview" style="width:100%;height:100%">
		@yield('editBoxContentview')
	</div>
	<div id="tableEditGroup" style="width:100%;height:100%"></div>
</div>