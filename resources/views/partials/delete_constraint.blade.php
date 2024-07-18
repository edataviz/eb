@section('delete_constraint')
<script>

actions.contraintEloquents 	= <?php echo json_encode($tab); ?>;
var keyColumns 	= <?php echo json_encode($keyColumns); ?>;

actions.preLoadSuccess = function (reLoadParams,data){
	if(!reLoadParams) {
		data.removeOldData = false;
	}
	return data;
};


var grepFunction 		= function (e,rowData){
	var found = true;
	$.each(keyColumns, function( i, column ) {
		found = found && e[column] !== undefined 
				&& (e[column] == rowData[column]
					|| (column.indexOf("DATE")>=0
						&& moment(e[column]).isSame(rowData[column])));
	});
	return found;
}

actions.mergeLoadData	= function (tab,data){
	delete actions.editedData[tab];

	var deleteData = actions.deleteData[tab];
	if(deleteData !== undefined && deleteData != null && deleteData.length > 0){
		for (var i = data.dataSet.length-1; i >= 0; i--) {
    		var rowData = data.dataSet[i];
    		var result = $.grep(deleteData, function(e){
        		return grepFunction(e,rowData);
            });
		    if (result.length > 0) data.dataSet.splice( i, 1 );
		}
		
		if(data!=null&&data.hasOwnProperty('objectIds')&&data.objectIds[tab]!==undefined){
			var ds  = data.objectIds[tab];
			for (var i = ds.length-1; i >= 0; i--) {
	    		var rowData = ds[i];
	    		var result = $.grep(deleteData, function(e){
	        		return grepFunction(e,rowData);
	            });
			    if (result.length > 0) ds.splice( i, 1 );
			}
		}
	}

}

actions.initDeleteObject  = function (tab,id, rowData) {
	var entry =  { ID			: id };
	$.each(keyColumns, function( i, column ) {
		entry[column]	= rowData[column];
	});
	return entry;
};

actions.getContraintEloquentKeyData	= function(tab,rowData,otherTabName,otherTable){
	var entry = [];
	if( $.inArray(tab,actions.contraintEloquents) >= 0 ){
		if(otherTable !== undefined){
			var editedData = otherTable.data();
	    	if(editedData !== undefined && editedData != null){
	    		var result = $.grep(editedData, function(e){ 
	        		return grepFunction(e,rowData);
	            });
			    if (result.length > 0) {
			    	entry = result;
			    }
	    	}
		}
		if(entry.length==0) entry =  [actions.initDeleteObject(otherTabName,0,rowData)];
	}
	return entry;
};
</script>
@stop