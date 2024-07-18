@section('endDdaptData')
<script>
@parent
if(actions.specialColumns===undefined) actions.specialColumns = [];
actions.getUomCollection = function (collection,columnName,cellData,rowData){
	var collectionByRight = collection;
	var columns = $.grep(actions.specialColumns, function(e){ 
   	 	return e.column == columnName;
    });
    if (columns.length > 0){
    	var sColumn 	= columns[0];
    	var hasRight 	= actions.containRight(sColumn.right);
		if(!hasRight){
			if(cellData==sColumn.columnValue){
				collectionByRight = $.grep(collection, function(e){ 
	           	 	return e.ID == sColumn.columnValue ||e.id == sColumn.columnValue;
	            });
			}
			else{
				collectionByRight = $.grep(collection, function(e){ 
	           	 	return e.ID != sColumn.columnValue && e.id != sColumn.columnValue ;
	            });
			}
		}
	};
	return collectionByRight;
}
</script>
@stop