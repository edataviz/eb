@extends('core.float_dialog')

@section('editBoxParams')
<script>
	editBox['initSavingDetailData'] = function(editId,saveUrl) {
		var editData = {id:editId};
		$.each(editBox.fields, function( index, value ) {
			editData[value] = actions.editedData[value];
   		 });
  		 return editData;
	};

    editBox['notValidatedData'] = function(editId) {
        return false;
    };

    editBox['checkEmptyEditData'] = function(editId,saveUrl) {
        var isEmpty = true;
        $.each(editBox.fields, function( index, value ) {
            isEmpty= isEmpty&&(!actions.editedData.hasOwnProperty(value))&&(!actions.deleteData.hasOwnProperty(value));
        });
        return isEmpty;
    };

    editBox.saveDetail = function(editId,success,saveUrl) {
    	if(editId&&editId!=null){
    	    var isEmpty = editBox.checkEmptyEditData(editId,saveUrl);
      		if(isEmpty) {
        		alert('no change to commit');
        		return;
        	}
    		var editData = editBox.initSavingDetailData(editId,saveUrl);

    		if(!editData) {
        		alert('no change to commit');
        		return;
        	}
        	if(editBox.notValidatedData(editId)) return;
    		showWaiting();
    		saveUrl = typeof saveUrl == "string"? saveUrl	:editBox.saveUrl;
    		$.ajax({
    			url		: saveUrl,
    			type	: "post",
    			data	: editData,
    			success:function(data){
    				hideWaiting();
    				console.log ( "success saveDetail "/* +JSON.stringify(data)  */);
//     				alert("success");
//     				if(editBox.enableRefresh) actions.doLoad(true);
    				var close = true
    				if (typeof(success) == "function") {
    					close = success(data,saveUrl);
					}
    				else if (typeof(actions.saveSuccess) == "function") {
    					actions.saveSuccess(data);
    					close = false;
					}
    				editBox.closeEditWindow(close);
    			},
    			error: function(data) {
    				hideWaiting();
    				alert("error!");
    				console.log ( "error saveDetail ");
    			}
    		});
    	}
    	else{
    		alert('no item change to commit');
    	}
    }
</script>
@stop