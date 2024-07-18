var dialog_edit_condition;

$(function() {
    dialog_edit_condition = $( "#dialog_edit_condition" ).dialog({
      autoOpen: false,
      height: 400,
      width: 400,
      modal: true,
      buttons: {
        "Save": saveCondition,
        Cancel: function() {
          dialog_edit_condition.dialog( "close" );
        }
      },
      close: function() {
        //$("#dialog_edit_condition form").reset();
      }
    });
});
function saveCondition()
{
	var s="";
	$("#ele_container .brc_out").each(function(){
		s +=(s==""?"":",")+$(this).attr('condition_when')+":"+$(this).attr('runner_id');			
	});
	
	param = {
			"job_id":current_job_id,
			"condition_id":current_condition_id,
			"condition": s,
			"name": $("#cond_name").val(),
			"expression": $("#cond_exp").val(),
			"from_runner_id":$("#cond_from_runner").val()
	}
	
	sendAjaxNotMessage('/savecondition', param, function(data){
		if(data!='ok')
			alert(data);
		else
		{
			loadConditionsList(current_job_id);
			dialog_edit_condition.dialog( "close" );
		}
	});
	
	/*postRequest(
				"index.php?act=savecondition",
				{
					"job_id":current_job_id,
					"condition_id":current_condition_id,
					"condition": s,
					"name": $("#cond_name").val(),
					"expression": $("#cond_exp").val(),
					"from_runner_id":$("#cond_from_runner").val()
				},
				function(data){
					if(data!='ok')
						alert(data);
					else
					{
						loadConditionsList(current_job_id);
						dialog_edit_condition.dialog( "close" );
					}
				}
				);*/
}
var newConditionID=0;
function addConditionBlock()
{
	newConditionID++;

  	var v = $("#cond_to_when").val();
   	var id = $("#cond_to_runner").val();
   	var r = $("#cond_to_runner option:selected").text();

	var ele=$("#ele_x").clone();
	ele.removeAttr("id").attr("id", "ele_n"+newConditionID).css("display", "block");
	ele.attr('runner_id',id);
	ele.attr('condition_when',v);
	ele.find(".brc_text").html(v+":"+r);
	ele.appendTo("#ele_container");
}
function parseConditions(sx)
{
	$("#ele_container").html("");
	var ss=sx.split(",");
	for (i=0;i<ss.length;i++) 
	{
		var index=i;
		var xx=ss[index].split(":");
		if(xx.length>2)
		{
	    	var v = xx[0].trim();
	    	var id = xx[1].trim();
	    	var r = xx[2].trim();

			var ele=$("#ele_x").clone();
			ele.removeAttr("id").attr("id", "ele_"+index).css("display", "block");
			ele.attr('runner_id',id);
			ele.attr('condition_when',v);
			ele.find(".brc_text").html(v+":"+r);
			ele.appendTo("#ele_container");
		}
	}
}