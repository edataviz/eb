<script type="text/javascript">
var ebtoken = $('meta[name="_token"]').attr('content');
// var jsFormat = configuration['picker']['DATE_FORMAT_JQUERY'];//'mm/dd/yy';

$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

$(function(){
	var listControl = <?php echo json_encode($listControls);?>;
	adminControl.init(listControl);	

	//if($('#cboObjectType').length > 0){
		//$('#cboObjectType').change();
		//alert('aaaaaaaaaaaa' + $('#cboObjectType').val());
	//}
}); 

var adminControl = {
	initData : function(data){
		var _data = data.result;
		var strID = '';
		var cbo = '';		
		for(var v in _data){
			var value = _data[v];
			var label = "";
			//cbo += ' <div class="filter">';
			if(v != "begin_date" && v != "end_date"){
				if(value.TYPE != "BUTTON"){
					cbo += ' <div class="filter">';
					if(v == "DataTableGroup"){
						label = "<strong>Group (<a href='{{URL::to('/am/editGroup')}}'>Config</a>)</strong>";
					}else{
						label = v;
					}
					cbo += ' 	<div><b>' + label + '</b></div>';
					cbo += ' 	<select id = ' + v.replace(' ','') + '>';
					
					for(var j = 0; j < value.length; j ++){
						cbo += ' 		<option value="' + value[j].ID + '">' + value[j].NAME + '</option>';
					}
					
					cbo += ' 	</select>';
					cbo += ' </div>';
				}else{
					cbo += ' <div style="width:100; float:left;padding: 3px;">';
					cbo += ' 	<div>&nbsp;</div>';
					cbo += ' 	<input type = "button" value = "'+value.label+'" onclick="'+value.onclick+'">';
					cbo += ' </div>';
				}
			} else{
				cbo += ' <div class="filter">';
				cbo += ' 	<div><b  style="font-size: 10px;">' + value.label + '</b></div>';
				cbo += ' <input id="'+value.ID+'" style="width: 140px; margin-top:0px; height: 21px;" type="text" value="'+value.default+'">';
				cbo += ' </div>';
			} 
		}

		if(typeof _graph !== 'undefined' && _graph.loadObjType == 1){		
			cbo += _graph.loadObjecType();
		}

		if( typeof _viewconfig !== 'undefined' && _viewconfig.loadObjType == 1){
			cbo += _viewconfig.loadObjecType();
		}
		
		$('#control').html(cbo);

		$( "#begin_date , #end_date" ).datepicker({
			changeMonth	:	true,
			changeYear	:	true,
			dateFormat	:	jsFormat
		}); 

		$("#ProductionUnit, #Area").change(function() {   
			var id = this.id;
			var table = ""
			var cboSet = ""; 
				
			if(id == "ProductionUnit"){
				table = "LoArea";
				cboSet = "Area"
			}

			if(id == "Area"){
				table = "Facility";
				cboSet = "Facility";
			}

			if(table != ""){
				value =  $('#'+id).val();
				adminControl.cboOnchange(cboSet, value, table);
			}
		});

		$("#Facility, #cboObjectType, #Product").change(function() {  
			if(typeof _graph !== 'undefined' && _graph.loadObjType == 1){	 
				_graph.cboObjectTypeOnChange();
			}
		});

		if(typeof _graph !== 'undefined' && _graph.loadObjType == 1){		
			_graph.setValueDefault();
		}

		if( typeof _viewconfig !== 'undefined' && _viewconfig.loadObjType == 1){
			$('#cboObjectType').change();
		}
	},
	reloadCbo : function(id, data){
		$('#'+id).empty();

		var _data = data.result;
		var cbo = '';

		//cbo += ' 		<option value="0">All</option>';
		for(var v in _data){
			cbo += ' 		<option value="' + _data[v].ID + '">' + _data[v].NAME + '</option>';
		}

		$('#'+id).html(cbo);
		$("#"+id).prop("disabled", false); 
		$("#"+id).change();
	},

	cboOnchange : function(cboSet, value, table){
		param = {
			'ID' :value,
			'TABLE' : table
		};
		$("#"+cboSet).prop("disabled", true); 
		$.ajax({
	    	url: '/am/selectedID',
	    	type: "post",
	    	dataType: 'json',
	    	data: param,
	    	success: function(_data){
	    		adminControl.reloadCbo(cboSet, _data);
			}
		});
	},

	init : function(listControl){
		$.ajax({
	    	url: '/am/loadData',
	    	type: "post",
	    	dataType: 'json',
	    	data: listControl,
	    	success: function(_data){
	    		adminControl.initData(_data);
			}
		});
	}
}
</script>
<div id="controlSearch">
	<div id="control"></div>
</div>