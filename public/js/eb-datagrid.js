const EBDataGrid = {
    
    create: function(container, config){
		config.ajaxType = 'get';
		config.date = $('#filter-date').dateString();
        sendAjax("loaddatagrid", config,
            function(data){
                $(container).html(data);
            },
            function(){
                alert('Error loading data grid!');
            }
    	);
    },
}