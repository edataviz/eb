<?php
$currentSubmenu = '/am/helpeditor';
?>
@extends('core.bshelpeditor')

@section('content')
{{--<script src="/ckeditor/ckeditor.js"></script>--}}
<script type="text/javascript">

	$(function(){
		$('#submenu').css('display', 'none');

        var height = window.innerHeight;
        CKEDITOR.config.height = (height - 280)+"px";
        CKEDITOR.config.resize_dir = 'vertical';
        $(window).resize(function(){
            var height = window.innerHeight;
            editor.resize('100%',height - 143);
        });

		CKEDITOR.config.width='100%';
		var editor = CKEDITOR.replace( 'editor1' );
	});

	var _help = {
			functionGroupChange : function(){
				var code = $("#cboFunctionGroup").val();

				if(code === "ROOT"){
					$('#cboFunction').html("<option value='ROOT'>Home screen</option>");
                    $('#cboFunction').change();
				}else{
					param = {
						'CODE' : code
					};

					sendAjax('/am/getFunction', param, function(data){
						_help.showFunction(data);
					});
				}
			},
			showFunction : function(data){
				var cbo = '';
				$('#cboFunction').html(cbo);
				cbo += '<option>Select a function</option>';
				for(var v = 0; v < data.length; v++){
					cbo += ' 		<option value="' + data[v].CODE + '">' + data[v].NAME + '</option>';
				}

				$('#cboFunction').html(cbo);
				$('#cboFunction').change();
			},
			save : function(){
				if($("#cboFunction").val()+""!=""){

					param = {
						'func_code' : $("#cboFunction").val(),
						'help' : CKEDITOR.instances.editor1.getData()
					};

					sendAjax('/am/savehelp', param, function(data){
						if(data=="Ok")
							alert("Data saved successfully");
						else
							alert(data);
					});
				}
			},
			functionChange : function(){
				var func_code = $("#cboFunction").val();
				if(func_code + "" != ""){
					param = {
						'func_code' : func_code
					};

					sendAjax('/am/gethelp', param, function(data){
						CKEDITOR.instances.editor1.setData(data);
					});
				}
			}
	}
</script>

<div>
	<select id="cboFunctionGroup" onchange="_help.functionGroupChange()">
		<option>Select a group</option>
		<option value='ROOT'>-- / HOME SCREEN</option>
		<?php \Helper::ebFunctionParent($eb_functions); ?>
	</select>
	<select id="cboFunction" onchange="_help.functionChange();"></select>
	<button onclick="_help.save()" style="width: 100px; margin: 5px;">Save</button>
	<form>
		<textarea name="editor1" id="editor1" style="height: 600px;"></textarea>
	</form>
</div>
@stop
