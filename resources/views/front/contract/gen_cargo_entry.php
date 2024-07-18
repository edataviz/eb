<!DOCTYPE html>
<!-- <script src="/common/js/numericInput.min.js"></script> -->
<div style="padding:10px;float: left;">
<b><font size="2">Cargo No. Nomenclature</font></b>
<table cellpadding="5">
<tr>
<td width="90">1st code</td>
<td><input type="text" style="width:100px" name="txt_1stcode" id="txt_1stcode"></td>
<td>Year</td>
<td><input type="text" style="width:60px" name="txt_year1" id="txt_year1"></td>
<td width="90">2nd code</td>
<td><input type="text" style="width:60px" name="txt_2ndcode" id="txt_2ndcode"></td>
<td>Year</td>
<td><input type="text" style="width:60px" name="txt_year" id="txt_year"></td>
<td>Month</td>
<td><input type="text" style="width:60px" name="txt_month" id="txt_month"></td>
<td>Day</td>
<td><input type="text" style="width:60px" name="txt_day" id="txt_day"></td>
<td width="100">Seq. No. start</td>
<td><input type="text" style="width:60px" name="txt_seqno" id="txt_seqno"></td>
</tr>
</table>
<hr>
<table id="tableInfo" cellpadding="3">
<tr>
	<td><b>Liffting Account</b></td>
	<td><b>Priority</b></td>
	<td><b>Quantity Type</b></td>
	<td><b>1st Cargo Date</b></td>
	<td><b>Avg. Qty. per Cargo</b></td>
	<td><b>UOM</b></td>
	<td><b>Adjust Time</b></td>
	<td><b>Tolerance</b></td>
</tr>
<tr>
	<td><?php Helper::filter(['modelName'=>'PdLiftingAccount']);?>
	</td>
	<td><?php Helper::filter(['modelName'=>'PdCodeCargoPriority']);?>
	</td>
	<td><?php Helper::filter(['modelName'=>'PdCodeCargoQtyType']);?>
	</td>
	<td><?php Helper::selectDate(['id'=>'txtCargoDate','sName'=>'txtCargoDate']);?>
	</td>
	<td><input type="text" style="width:100px" name="txtQty" id="txtQty" class="_numeric"></td>
	<td><?php Helper::filter(['modelName'=>'PdCodeMeasUom']);?>
	</td>
	<td><?php Helper::filter(['modelName'=>'PdCodeTimeAdj']);?>
	</td>
	<td><?php Helper::filter(['modelName'=>'PdCodeQtyAdj']);?>
	</td>
</tr>
</table>
<br>
<div style="float: left;width: 100%;" >
	<div style="margin: auto;display: block;width: 600px;">
		<div class="filter" style="line-height: 30px;">
			Contract attribute
		</div>
		<?php Helper::filter(['modelName'=>'PdContractQtyFormula']);?>
		<div class="filter" style="line-height: 30px;">
			Value
		</div>
		<div class="filter" >
			<input type="text" style="clear: both;width:100px;" name="txtAttrValue" id="txtAttrValue" class="_numeric"> 
		</div>
	</div>
</div>

<div style="float: left;width: 100%;" >
	<button onclick="gen()" style="width:200px;height:40px;margin: auto;display: block;">Generate Cargo Entry</button>
</div>
    
</div>
<script>
function calAttrValue(){
	var formula_id=$("#PdContractQtyFormula").val();
	if(formula_id>0){
		showWaiting();
	    postData = {
						contract_id				:'<?php echo $contract_id;?>',
						PdContractQtyFormula	:formula_id
					 };
	    $.ajax({
			url: '/gen_cargo_entry/calculate',
			type: "post",
			data: postData,
			success:function(data){
				console.log ( "send calAttrValue  success "/* +JSON.stringify(data) */);
				value = ""+data;
				if(configuration.number.DECIMAL_MARK=='comma') 
					value = value.replace('.',',');
				$("#txtAttrValue").val(value);
// 				alert(JSON.stringify(data));
				hideWaiting();
			},
			error: function(data) {
				console.log ( "calAttrValue error ");
				hideWaiting();
				alert("can not calculate attribute value");
			}
		});
	}
}
function gen(){
	/*
	var n=$("#txtNumCargo").val();
	if(!(n>0)){
		alert("Please input number of cargo");
		$("#txtNumCargo").focus();
		return;
	}
	*/
	
	if($("#txt_1stcode").val().trim()==""){
		alert("Please input 1st code");
		$("#txt_1stcode").focus();
		return;
	}
	if($("#txt_seqno").val().trim()==""){
		alert("Please input Seq. No. start");
		$("#txt_seqno").focus();
		return;
	}
	if($("#txtCargoDate").val().trim()==""){
		alert("Please input 1st Cargo Date");
		$("#txtCargoDate").focus();
		return;
	}
	
	var avgqty=parseInt($("#txtQty").val().replace(',','.'));
	if(!(avgqty>0)){
		alert("Please input average quantity");
		$("#txtQty").focus();
		return;
	}
	var qty=parseInt($("#txtAttrValue").val().replace(',','.'));
	if(!(qty>0)){
		alert("Please input quantity");
		$("#txtAttrValue").focus();
		return;
	}
	var n=Math.round(qty/avgqty);
	if(n>10){
		if(!confirm(n+" cargo entries will be generated. Do you want to continue?"))
			return;
	}
	else{
		if(!confirm("Do you want to create cargo entries?"))
			return;
	}

	sendAjax('/gen_cargo_entry/gen',
			{
				 code1st		:$("#txt_1stcode").val(),
				 year1		:$("#txt_year1").val(),
				 code2nd		:$("#txt_2ndcode").val(),
				 year		:$("#txt_year").val(),
				 month		:$("#txt_day").val(),
				 day		:$("#txt_month").val(),
				 seq		:$("#txt_seqno").val(),
				 PdLiftingAccount		:$("#PdLiftingAccount").val(),
				 PdCodeCargoPriority		:$("#PdCodeCargoPriority").val(),
				 PdCodeCargoQtyType		:$("#PdCodeCargoQtyType").val(),
				 date1st		:$("#txtCargoDate").val(),
				 avgqty		:$("#txtQty").val().replace(',','.'),
				 PdCodeMeasUom		:$("#PdCodeMeasUom").val(),
				 PdCodeTimeAdj		:$("#PdCodeTimeAdj").val(),
				 PdCodeQtyAdj						:$("#PdCodeQtyAdj").val(),
				 contract_id			:'<?php echo $contract_id;?>',
				 PdContractQtyFormula	:'<?php echo $storage_id;?>',
				 qty		:$("#txtAttrValue").val().replace(',','.')
			 },
		     function(data) {
				 _alert(data);
		     },
		     function(data) {
				 _alert("generate unsuccessfully");
		     }
     );
}

if(configuration.number.DECIMAL_MARK=='comma') 
	$("._numeric").attr('pattern',"^[-]?[0-9]+([,][0-9]{1,20})?");
else
	$("._numeric").attr('pattern',"^[-]?[0-9]+([\.][0-9]{1,20})?");

$('#PdContractQtyFormula').on('change', function() {
	calAttrValue();
});
$("#PdContractQtyFormula").change();
</script>
