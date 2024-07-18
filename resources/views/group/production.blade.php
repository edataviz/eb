<?php
$lang			= session()->get('locale', "en");
$dateEndStatus 	= session()->get("disable_date_end", "1");
$dateEndStatus 	= $dateEndStatus!==''&&$dateEndStatus!==null?$dateEndStatus:1;
$request 		= request();
$parameters 	= $request->route()->parameters();
$rightCode		= isset($parameters['rightCode'])?$parameters['rightCode']:"";

if (!isset($filters['extra'])) {
	$filters['extra'] = [];
}
$prefix				= isset($prefix)?$prefix:"";
if(isset($lastFilter)&&$shouldUseLastFilter){
    if(is_string($lastFilter)){
        $lastFilter			= $prefix.$lastFilter;
    }
    else if (is_array($lastFilter)){
        foreach ($lastFilter as $key => $flt){
            $lastFilter[$key]	= $prefix.$flt;
		}
	}
    else{
        $lastFilter = "";
    }
}
else{
    $lastFilter = "";
}
$functionName		= isset($functionName)?$functionName:"";
$enableButton 		= isset($filterGroups['enableButton'])?	$filterGroups['enableButton']	:true;
$enableSaveButton 	= isset($filters['enableSaveButton'])?	$filters['enableSaveButton']	:true;

if (array_key_exists('productionFilterGroup', $filters)) {
	$dependences = isset($filters['FacilityDependentMore'])?
					array_merge($filters['FacilityDependentMore'],$filters['productionFilterGroup'])
					:$filters['productionFilterGroup'];
	$mapping = ['LoProductionUnit'		=> 	array('filterName'	=>'Production Unit',
												'name'			=>'LoProductionUnit',
												'dependences'	=> array_merge(['LoArea','Facility'],$dependences),
	  											'extra'			=>$filters['extra'],
											),
				'LoArea'				=>	array('filterName'	=>'Area',
												'name'			=>'LoArea',
												'dependences'	=> array_merge(['Facility'],$dependences),
	  											'extra'			=>$filters['extra'],
											),
				'Facility'				=>	array('filterName'	=>'Facility',
												'name'			=>'Facility',
												'dependences'	=>$dependences,
	  											'extra'			=>$filters['extra'],
											)
				];
	
	$subMapping = config("constants.subProductFilterMapping");
	$mapping = array_merge($mapping,$subMapping);
}
else{
	$mapping = config("constants.subProductFilterMapping");
}

?>

<script type='text/javascript'>
	loadEBjs();
var javascriptFilterGroups = <?php echo json_encode($filterGroups); ?>;
var lastFilter 			= <?php echo json_encode($lastFilter); ?>;
var disable_date_end 	= '{{$dateEndStatus}}';
</script>
<script>
$( document ).ready(function() {
    console.log( "ready!" );
    var onChangeFunction = function() {
    	if ($('#buttonLoadData').attr('value')=='Refresh') {
	    	actions.doLoad(true);
		}
    };
    
    $( "#date_begin" ).change(onChangeFunction);
    $( "#date_end" ).change(function(e) {
    	if($("#date_end").prop('disabled')) return;
    	onChangeFunction();
    });
    if(typeof lastFilter == "string" && lastFilter !="" && $("#"+lastFilter+" option[value='0']").length > 0){
        $("#"+lastFilter).val(0);
    }
    else if( typeof lastFilter == "object"){
		for (var i = 0 ; i < lastFilter.length ; i++){
            if($("#"+lastFilter[i]+" option[value='0']").length > 0){
                $("#"+lastFilter[i]).val(0);
            }
		}
	}

	var oGetFilterValues = actions.getFilterValues;
	actions.getFilterValues = function(){
		var params = oGetFilterValues();
		if($("#disable_date_end").length>0){
			params.disable_date_end = $("#disable_date_end").is(":checked")?1:0;
		}
		return params;
	}

    @if(count($scopeFacilities)>0)
    	var scopeFacilities = <?php echo json_encode($scopeFacilities); ?>;
    	$( "#Facility" ).change(function(){
        	if($.inArray($(this).val()+"*", scopeFacilities )>=0)
                $( "#buttonSave" ).css("display","none");
        	else
        		$( "#buttonSave" ).css("display","");
        });
        @if(!auth()->user()->checkReadOnly($rightCode,$facility)&&$enableSaveButton)
        	$( "#buttonSave" ).css("display","");
		@else
        	$( "#buttonSave" ).css("display","none");
		@endif

		if(typeof filters == "undefined") filters = {};
		var oAfterRenderingDependences	= filters.afterRenderingDependences;
		filters.afterRenderingDependences	= function(id,sourceModel){
			if(typeof oAfterRenderingDependences == "function") oAfterRenderingDependences(id,sourceModel);
			var partials 		= id.split("_");
			var prefix 			= partials.length>1?partials[0]+"_":"";
			var model 			= partials.length>1?partials[1]:id;
			var currentObject	= "#"+prefix;
			
			if(model=="Facility") {
				if($.inArray($("#Facility").val()+"*", scopeFacilities )>=0)
	                $( "#buttonSave" ).css("display","none");
	        	else
	        		$( "#buttonSave" ).css("display","");
			}
			
		};
		
	@endif
});
</script>
@yield($prefix.'first_filter')
<div id="ebFilters_{{$functionName}}" class="{{$functionName}} filterContainer" style="height:auto">
	@foreach( $filterGroups as $key => $filters )
			@if($key=='productionFilterGroup')
			<div id = "{{$prefix}}filterProduct" class = "product_filter">
				@foreach( $filters as $filter )
				<?php
					$mergedFilter = array_key_exists($filter['modelName'], $mapping)?array_merge($mapping[$filter['modelName']],$filter):$filter;
				?>
					{{ Helper::buildFilter($mergedFilter) }}
				@endforeach
			</div>
			@elseif($key=='dateFilterGroup')
			<div class = "date_filter">
				@foreach( $filters as $filter )
					{{ Helper::selectDate($filter)}}
				@endforeach
			</div>
			@elseif($key=='frequenceFilterGroup')
			<div id = "{{$prefix}}filterFrequence" class = "product_filter">
				@foreach( $filters as $filter )
					{{ Helper::filter(array_key_exists($filter['modelName'], $mapping)?array_merge($mapping[$filter['modelName']],$filter):$filter) }}
				@endforeach
				@yield('frequenceFilterGroupMore')
			</div>
			@endif
	@endforeach
	@if($enableButton)
		<div id="{{$prefix}}_action_filter"class="action_filter floatLeft">
			@if(auth()->user()->hasWritableRight($rightCode)&&$enableSaveButton)
				<input type="button" value="<?php echo \Helper::translateText($lang,"Save"); ?>" name="B3" id = "buttonSave" class="buttonSave" onClick="actions.doSave(true)" style="width: 85px;float:left; height: 26px">
				<br>
			@endif
			<input type="button" value="<?php echo \Helper::translateText($lang,"Load data"); ?>" id="buttonLoadData" name="B33"
				onClick="actions.doLoad(true)" style="width: 85px; height: 26px;float:left;margin-top:7px;clear: both;">
		</div>
	@endif
	@yield($prefix.'action_extra')
</div>
	@yield($prefix.'filter_extra')
