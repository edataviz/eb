<?php
	$code_data_method 	= App\Models\CodeDataMethod::where(['ACTIVE'=>1])->orderBy('ORDER')->get(['ID', 'NAME']);
	$cfg_input_type 	= App\Models\CfgInputType::all('ID', 'NAME');
    $cfg_data_source    = isset($cfg_data_source)?$cfg_data_source:App\Models\CfgDataSource::select('NAME','SRC_TYPE')->orderBy('NAME')->get();;

?>

<div id="wraper" style="margin-top: -0px ;width: 100%;background-color: #eeeeee;height: 66px">
    <div class="row show-grid" style="padding-top: 14px; background-color: #eeeeee">
        <div class="span1.5" style="padding-top: 10px;padding-left: 30px">
            <input type="checkbox" checked id="chk_tbl" value="1"  style="margin-bottom: 5px"><b>Tables</b>
            <input type="checkbox" checked id="chk_vie" value="0"  style="margin-bottom: 5px;margin-left: 10px"><b>Views</b>
        </div>
        <div class="span2.5" style="margin-left: 12px">
            <select size="1" name="data_source" id="data_source" style="width: 200px;margin-top: 3px">
                @foreach($cfg_data_source as $re)
                    <option SRC_TYPE="{!!$re->SRC_TYPE!!}" value="{!!$re->NAME!!}">{!!$re->NAME!!}</option>
                @endforeach
            </select>
        </div>
        <div class="span2.5" style="padding-top: 10px; margin-left: 0px">
            <label><input type="checkbox" style="margin-left:20px;margin-top: 0px" id="chk_dc" value="0"><b style="margin-top: 2px; padding-left: 3px;font-size: 12px">Disable in Data Capture</b></label>
        </div>
        <div class="span0.5 selectNames" style="padding-top: 10px;margin-left: 75px">
            <b>Config</b>
        </div>
        <div class="span3" style="margin-left: 10px;width: 205px">
            {{ \Helper::filter(["modelName"=>"CfgConfig",
            'id' =>'CfgConfig',
            'getMethod'=>'loadBy',
            'optionAttributes' => ['FACILITY_ID'],
            'collection' =>[(object)['ID' =>0	,'CODE' =>0	,'NAME' => '(Default)', 'FACILITY_ID' => 0 ]]]) }}
        </div>
        <div class="span2.5" style="margin-left: 0px;padding-top: 3px">
            <button id="addCfgConfig" onclick="editBox.showCfgDialog('insert',this)"> Add</button>
            <button id="editCfgConfig" onclick="editBox.showCfgDialog('update',this)">Edit</button>
            <button id="cloneCfgConfig" onclick="editBox.showCfgDialog('clone',this)">Clone</button>
            <button id="deleteCfgConfig" onclick="editBox.deleteConfig()">Delete</button>
        </div>

        <div class="span1.5" id="saveButton" ></div>
    </div>
    <br>
    <div id="selectedFields" style="display: inline-block;float: left;padding-left: 30px">

        <table style="border:0px; border-collapse:collapse;">
            <tr class="selectNames">
                <td><b>Field</b></td>
                <td style="width: 110px"></td>
                <td><b>Effected field</b></td>
                <td width="40px"></td>

            </tr>
            <tr>
                <td valign="top" >
                    <select size="20" multiple name="data_field" id="data_field" style="width:200px;height: 320px">
                    </select>
                </td>
                <td>
                    <button id="add" value="Set" onclick="_fieldconfig.add();" style="display:block;width: 102px">Add</button>
                    <button id="remove" onclick="_fieldconfig.remove();" style="display:block;width: 102px" value="Unset;">Remove</button>
                    <button id="up" value="Up" onclick="_fieldconfig.up();" style="display:block;width: 102px">Up</button>
                    <button id="down" value="Down" onclick="_fieldconfig.down();" style="display:block;;width: 102px">Down</button>

                </td>
                <td valign="top" >
                    <select size="20" name="data_field_effected" id="data_field_effected" multiple style="width:200px;height: 320px">
                    </select>
                </td>
                <td ><br>
                </td>
            </tr>
        </table>
    </div>
    <div valign="top" rowspan="2" align="left" height="320px" style="display: inline-block;float: left">

        <script type="text/javascript">
jQuery.fn.toggleOption = function( show ) {
    jQuery( this ).toggle( show );
    if( show ) {
        if( jQuery( this ).parent( 'span.toggleOption' ).length )
            jQuery( this ).unwrap( );
    } else {
        if( jQuery( this ).parent( 'span.toggleOption' ).length == 0 )
            jQuery( this ).wrap( '<span class="toggleOption" style="display: none;" />' );
    }
};
            var deletedData;
            var index = 1000;
            var receivedData;
            var isChange="a";
            var dataMethods = <?php echo json_encode($code_data_method); ?>

            var _fieldconfig = {
                    getAddingRowIndex	: function () {
                        return index++;
                    },
                    enableReadyLoad	: function () {
                        return true;
                    },
                    init : function(){
                        deletedData={'CfgFieldProps':[]};
                    },
                    data_source_change : function(configId){
                        param = {
                            'table' : $("#data_source").val(),
                            'configId' : configId
                        }

                        isChange=false;
                        showWaiting();
                        sendAjaxNotMessage('/dataJson', param, function(data){
                            _fieldconfig.renderData(data);
                            hideWaiting();
                        });

                    },
                    renderData : function(data){
                        receivedData=data;
                        _fieldconfig.listField(data);
                        $("#chk_dc").prop('checked', data.dcDisable == 1);
                        var firstValue = $("#data_field_effected option:first").val();
                        $('#data_field_effected').data('previousValue',firstValue);
                        $("#data_field_effected").change();
                    },
                    listField : function(data){
                        var getFields = data.getFields;
                        var getFieldsEffected = data.dataProp;

                        $("#data_field").html('');
                        for(var i = 0; i < getFields.length; i++){
                            var str = '<option value="'+getFields[i]['COLUMN_NAME']+'"><b>'+getFields[i]['COLUMN_NAME']+'</b></option>';
                            $("#data_field").append(str);
                        }

                        $("#data_field_effected").html('');
                        for(var i = 0; i < getFieldsEffected.length; i++){
                            var str = '<option value="'+getFieldsEffected[i]['COLUMN_NAME']+'"><b>'+getFieldsEffected[i]['COLUMN_NAME']+'</b></option>';
                            $("#data_field_effected").append(str);
                        }
                        var firstValue = $("#data_field_effected option:first").val();
                        $("#data_field_effected").val(firstValue);
                    },



                    add:function(){

                        IDnew = 'NEW_RECORD_DT_RowId_'+actions.getAddingRowIndex();
                        var fields=$("#data_field").val();
                        for(var i=0; i<fields.length; i++)
                        {
                            $("#data_field option[value='"+fields[i]+"']").remove();
                            $("#data_field_effected").append("<option value='"+fields[i]+"'>"+fields[i]+"</option>");
                        }
                        $('#data_field_effected').each(function(){
                            var field=document.getElementById('data_field_effected');
                            var fields_effected_str=[];
                            var fields_effected_obj={};
                            for(var i=0; i<field.length; i++)
                            {
                                fields_effected_obj={'COLUMN_NAME':field.options[i].value}
                                fields_effected_str.push(fields_effected_obj);

                            }
                            var config_id=$('#CfgConfig').val();
                            if(config_id==0){
                                config_id=null;
                            }
                            for(var j=0;j<fields.length;j++){
                                var newDataProp={
                                    COLUMN_NAME: fields[j],
                                    CONFIG_ID: config_id,
                                    DATA_METHOD: "",
                                    FDC_WIDTH: null,
                                    FIELD_ORDER: receivedData.dataProp.length+1,
                                    FORMULA: null,
                                    ID: IDnew,
                                    INPUT_ENABLE: "",
                                    INPUT_TYPE: 1,
                                    INPUT_VISIBLE: "0",
                                    IS_MANDATORY: "0",
                                    LABEL: "",
                                    OBJECT_EXTENSION: {},
                                    RANGE_PERCENT: null,
                                    TABLE_NAME: $('#data_source').val() ,
                                    USE_DIAGRAM: "0",
                                    USE_FDC: "0",
                                    USE_GRAPH: "0",
                                    VALUE_FORMAT: null,
                                    VALUE_MAX: null,
                                    VALUE_MIN: null,
                                    VALUE_WARNING_MAX: null,
                                    VALUE_WARNING_MIN: null,
                                    V_BAK:null
                                }

                                receivedData.dataProp.push(newDataProp);
                            }
                            receivedData.getFieldsEffected=fields_effected_str;

                        });

                        $('#data_field').each(function(){
                            var fields=document.getElementById('data_field');
                            var fields_str=[];
                            var fields_obj={};
                            for(var i=0; i<fields.length; i++)
                            {
                                fields_obj={'COLUMN_NAME':fields.options[i].value}
                                fields_str.push(fields_obj);
                            }
                            receivedData.getFields=fields_str;
                        });
                        isChange=true;
                        //2
                    },
                    remove : function(){
                        var removeField=[];
                        var fields=$("#data_field_effected").val();

                        for(var i=0; i<fields.length; i++)
                        {
                            $("#data_field_effected option[value='"+fields[i]+"']").remove();
                            $("#data_field").append("<option value='"+fields[i]+"'>"+fields[i]+"</option>");

                        }
                        $('#data_field_effected').each(function(){
                            var field=document.getElementById('data_field_effected');
                            var fields_effected_str=[];
                            var fields_effected_obj={};
                            for(var i=0; i<field.length; i++)
                            {
                                fields_effected_obj={'COLUMN_NAME':field.options[i].value}
                                fields_effected_str.push(fields_effected_obj);
                                if(receivedData.dataProp[i]==fields){
                                    removeField.push(i);
                                }
                            }
                            for(var i=0; i<receivedData.dataProp.length; i++){
                                for(var j=0;j<fields.length;j++){
                                    if(receivedData.dataProp[i]['COLUMN_NAME']==fields[j]){
                                        removeField=i;
                                        var objID={'ID':receivedData.dataProp[i]['ID']}
                                        deletedData.CfgFieldProps.push(objID);
                                        receivedData.dataProp.splice(removeField,1);
                                    }
                                }
                            }
                            receivedData.getFieldsEffected=fields_effected_str;
                        });

                        $('#data_field').each(function(){
                            var field=document.getElementById('data_field');
                            var fields_str=[];
                            var fields_obj={};
                            for(var i=0; i<field.length; i++)
                            {
                                fields_obj={'COLUMN_NAME':field.options[i].value}
                                fields_str.push(fields_obj);


                            }

                        });
                        isChange=true;
                    },
                    up : function(){

                        var isIgnor=false;
                        var fields=$("#data_field_effected").val();
                        for(var i=0; i<receivedData.dataProp.length; i++){

                            for(var j=0;j<fields.length;j++) {
                                isIgnor==false;
                                if (receivedData.dataProp[i]['COLUMN_NAME'] == fields[j]) {
                                    if(receivedData.dataProp[i-1]){
                                        var temps = receivedData.dataProp[i -1];
                                        receivedData.dataProp[i - 1] = receivedData.dataProp[i];
                                        receivedData.dataProp[i] = temps;
                                        break;
                                    }
                                    else {
                                        isIgnor=true;
                                        break;
                                    }

                                }
                                if(isIgnor) break;
                            }
                        }
                        _fieldconfig.listField(receivedData);
                        $("#data_field_effected").val(fields);
                        isChange=true;


                    },
                    down : function(){
                        var isIgnor=false;
                        var fields=$("#data_field_effected").val();
                        for(var i=receivedData.dataProp.length-1; i>=0; i--){

                            for(var j=fields.length-1;j>=0;j--) {
                                isIgnor==false;
                                if (receivedData.dataProp[i]['COLUMN_NAME'] == fields[j]) {
                                    if(receivedData.dataProp[i+1]){
                                        var temps = receivedData.dataProp[i + 1];
                                        receivedData.dataProp[i + 1] = receivedData.dataProp[i];
                                        receivedData.dataProp[i] = temps;
                                        break;
                                    }
                                    else {
                                        isIgnor=true;
                                        break;
                                    }

                                }
                                if(isIgnor) break;
                            }
                        }
                        _fieldconfig.listField(receivedData);
                        $("#data_field_effected").val(fields);
                        isChange=true;
                    },
                    data_field_effected_change : function()
                    {
                        var fields=$("#data_field_effected").val();
                        if(fields){
                            var previousValues = $("#data_field_effected").data('previousValue');//get the pre data
                            _fieldconfig.saveObjectExtension(previousValues);
                            if (fields.length>1) {
                                if (fields.length>4)
                                    $("#caption").html("["+fields.length+" fields selected]");
                                else
                                    $("#caption").html(fields.join(', '));
								$("#LABEL").val("");
                                $("#LABEL").attr("disabled", "disabled");
                                $("#formula").attr("disabled", "disabled");
                                $("#data_method").val("");
                                $("#input_type").val("");
                                return;
                            }
                            if (fields.length==1){
                                $("#LABEL").removeAttr('disabled');
                                $("#formula").removeAttr('disabled');
                                /* $("#LABEL").attr("disabled", "");
                                 $("#formula").attr("disabled", "");*/
                            }
                            $("#save, #reset").hide();
                            this.setData(fields);
                            $("#data_field_effected").val(fields);
                        }
						else
							this.clearData();

                        //$("#tbl_detail").html("");


                        //Do your work here
                        $("#data_field_effected").data('previousValue', fields);//update the pre data
                        //5

                    },
                    warningChange: function (element,callback) {
                        if(isChange==true) {
                            if(!confirm("If you leave before saving, your changes will be lost, are you sure?")){
                                var currentValue = $.data(element, 'current');
                                currentValue = currentValue?currentValue:0;
                                $(element).val(currentValue);
                                return;
                            }
                        }
                        callback();
                        $.data(element, 'current', $(element).val());
                        isChange = false;
                    },
                    getCurrentColumnData : function(field){
                        for(var i=0; i<receivedData.dataProp.length; i++){
                            if(field==receivedData.dataProp[i].COLUMN_NAME) return receivedData.dataProp[i];
                        }
                        return null;
                    },
                    saveObjectExtension : function(fields){
                        if (fields){
                            var field,columnData;
                            for(var j=0;j<fields.length;j++){
                                field = fields[j];
                                columnData = _fieldconfig.getCurrentColumnData(field);
                                if(columnData) columnData.OBJECT_EXTENSION = _fieldconfig.buildObjectExtension();
                            }
                        }
                    },
					
					clearData: function(){
						$('#caption').html("&nbsp;");
						$('#LABEL').val(null);
						$('#data_method').val(null);
						$('#VALUE_FORMAT').val(null);
						$('#IS_MANDATORY').prop('checked', false);
						$('#FDC_WIDTH').val(null);
						$('#INPUT_TYPE').val(null);
						$('#FORMULA').val(null);
						$('#VALUE_FORMAT').val(null);
						$('#VALUE_MAX').val(null);
						$('#VALUE_MIN').val(null);
						$('#VALUE_WARNING_MAX').val(null);
						$('#VALUE_WARNING_MIN').val(null);
						$('#RANGE_PERCENT').val(null);
						$('#USE_FDC').prop('checked', false);
						$('#USE_GRAPH').prop('checked', false);
						$('#USE_DIAGRAM').prop('checked', false);
					},

                    setData : function(fields){
						if(fields==null){
							this.clearData();
							return;
						}
                        var dataProp = receivedData.dataProp;
                        var foundProperty = null;
                        for(var j=0;j<fields.length;j++){
                            for(var i=0;i<dataProp.length;i++){
                                if(fields[j]==dataProp[i].COLUMN_NAME){
                                    $('#caption').text(dataProp[i].COLUMN_NAME);
                                    $('#LABEL').val(dataProp[i].LABEL);
                                    $('#data_method').val(dataProp[i].DATA_METHOD);
                                    $('#VALUE_FORMAT').val(dataProp[i].VALUE_FORMAT);
                                    if(dataProp[i].IS_MANDATORY == "1"){
                                        $('#IS_MANDATORY').prop('checked', true);
                                    }else{
                                        $('#IS_MANDATORY').prop('checked', false);
                                    }
                                    $('#FDC_WIDTH').val(dataProp[i].FDC_WIDTH);
                                    $('#INPUT_TYPE').val(dataProp[i].INPUT_TYPE);
                                    $('#FORMULA').val(dataProp[i].FORMULA);
                                    $('#VALUE_FORMAT').val(dataProp[i].VALUE_FORMAT);
                                    $('#VALUE_MAX').val(dataProp[i].VALUE_MAX);
                                    $('#VALUE_MIN').val(dataProp[i].VALUE_MIN);
                                    $('#VALUE_WARNING_MAX').val(dataProp[i].VALUE_WARNING_MAX);
                                    $('#VALUE_WARNING_MIN').val(dataProp[i].VALUE_WARNING_MIN);
                                    $('#RANGE_PERCENT').val(dataProp[i].RANGE_PERCENT);

                                    if(dataProp[i].USE_FDC == "1"){
                                        $('#USE_FDC').prop('checked', true);
                                    }else{
                                        $('#USE_FDC').prop('checked', false);
                                    }

                                    if(dataProp[i].USE_GRAPH == "1"){
                                        $('#USE_GRAPH').prop('checked', true);
                                    }else{
                                        $('#USE_GRAPH').prop('checked', false);
                                    }

                                    if(dataProp[i].USE_DIAGRAM == "1"){
                                        $('#USE_DIAGRAM').prop('checked', true);
                                    }else{
                                        $('#USE_DIAGRAM').prop('checked', false);
                                    }
                                    foundProperty = dataProp[i];
                                    break;
                                }
                            }
                        }

                        $("#objectExtension").html("");
                        $("#addObjectBtn").remove();

                        var objectExtensionSource = receivedData.objectExtension?receivedData.objectExtension:null;
                        if(objectExtensionSource!==undefined && objectExtensionSource!=null &&objectExtensionSource.length>0 && foundProperty){
                            var objectExtension	= foundProperty['OBJECT_EXTENSION'];
                            $( "#objectExtension" ).append( "<b>Exceptions</b>" )
                            if(objectExtension!=null&&objectExtension!=""){
                                try {
                                    var objects = typeof objectExtension == 'object'? objectExtension:$.parseJSON(objectExtension);

                                    if (objects.version == 2) {
                                        $.each(objects, function (facilityId, objectDatas) {
                                            if ($.isNumeric(facilityId)) {
                                                $.each(objectDatas, function (objectId, objectItem) {
                                                    // var objectId = Object.keys(objectItem)[0];
                                                    _fieldconfig.addObjectExtension({
                                                        'reciveData': receivedData,
                                                        // 'targets': objectItem[objectId],
                                                        'targets': objectItem,
                                                        'facilityId': facilityId,
                                                        'version': objects.version,
                                                        'objectId': objectId
                                                    });
                                                });
                                            }
                                        });
                                        _fieldconfig.sortExtensionObjects("#objectExtension");
                                    }
                                    else {
                                        $.each(objects, function (objectID, objectItem) {
                                            _fieldconfig.addObjectExtension({
                                                'objectId': objectID,
                                                'objectExtensionSource': objectExtensionSource,
                                                'targets': objectItem,
                                                'reciveData': receivedData
                                            });

                                        });
                                        _fieldconfig.sortExtensionObjects("#objectExtension");
                                    }
                                }

                                catch(err) {
                                    console.log("can not parse  objectExtension +\n"+err.message);
                                }
                            }
                            ;
                            var option={'reciveData':receivedData};
                            var addObjectBtn = $("<button id='addObjectBtn'>Add exception</button>");
                            addObjectBtn.attr("src","/img/plus.png");
                            addObjectBtn.addClass("xclose floatRight");
                            addObjectBtn.click(function() {
                                _fieldconfig.addObjectExtension(option,[],{});
                            });
                            addObjectBtn.appendTo($("#extensionView"));
                        }

                        $("#save, #reset").show();
                    },
                    sortExtensionObjects : function(elementName){
                        var items = $(elementName+' > li').get();
                        items.sort(function(a,b){
                            var keyA = $(a).find("select > option:selected").text();
                            var keyB = $(b).find("select > option:selected").text();

                            if (keyA < keyB) return -1;
                            if (keyA > keyB) return 1;
                            return 0;
                        });
                        var ul = $(elementName);
                        $.each(items, function(i, li){
                            ul.append(li); /* This removes li from the old spot and moves it */
                        });
                    },

                    addObjectExtension : function(option){
                        var facility_id=option.facilityId;
                        var os = (option.objectId+'').split('_');
                        var objectId = os[0];
                        var objectId2 = null;
                        os.length > 1 && (objectId2 = os[1]);
                        var targets = option.targets;
                        var config = option.targets;
                        var cfgConfig = $('#CfgConfig option:selected').data();
                        var currentFacility = cfgConfig?cfgConfig.FACILITY_ID:0;
                        currentFacility = currentFacility?currentFacility:0;


                        targets = targets ?targets:{};
                        var li = $("<li></li>");
                        li.addClass("object_facility");

                        if(receivedData.objectExtension2){
                            var oselect = $("<select></select>");
                            $.each(receivedData.objectExtension2, function(oindex, ovalue ) {
                                var option = $("<option></option>");
                                option.val(ovalue.ID);
                                option.text(ovalue.NAME);
                                option.appendTo(oselect);
                            });
                            oselect.css("width","190px");
                            oselect.appendTo(li);
                            oselect.addClass("object2");
                            oselect.val(objectId2);
                        }

                        var selectObject = null;
                        if(receivedData.objectExtension){
                            selectObject = $("<select></select>");
                            $.each(receivedData.objectExtension, function(oindex, ovalue ) {
                                var option = $("<option></option>");
                                option.val(ovalue.ID);
                                option.text(ovalue.NAME);
                                option.attr('facility_id',ovalue.FACILITY_ID);
                                option.appendTo(selectObject);
                                if (ovalue.ID==objectId&&!facility_id){
                                    facility_id = ovalue.FACILITY_ID;

                                }

                            });

                            selectObject.css("width","190px");
                            selectObject.appendTo(li);
                            selectObject.addClass("object");
                            selectObject.val(objectId);
                        }

                        if(receivedData.facility){
                            var selectFacility = $("<select><option value='0' selected>(None)</option></select>");
                            selectFacility.addClass("facility");
                            $.each(receivedData.facility, function(oindex, ovalue ) {
                                if (currentFacility == 0 || currentFacility == ovalue.ID) {
                                    var option = $("<option></option>");
                                    option.val(ovalue.ID);
                                    option.text(ovalue.NAME);
                                    option.appendTo(selectFacility);
                                }
                            });
                            selectFacility.css("width","120px");
                            selectFacility.prependTo(li);
                            selectFacility.change(function() {
                                if(!selectObject) return;
                                var facilityID=this.value;
                                $.each(selectObject.find('option'), function(index,ovalue) {
                                    if ($(ovalue).val()==0||facilityID==$(ovalue).attr('facility_id')) {
                                        $(ovalue).toggleOption(true);//.removeAttr('hidden');//.css("display","block");
                                    }
                                    else {
                                        $(ovalue).toggleOption(false);//.attr("hidden","");//.css("display","none");
                                    }
                                });
                                selectObject.find('option[value=0]').attr('facility_id',facilityID);
                                $(selectObject).val(0);
                            });

                            if(facility_id>0)
                                selectFacility.val(facility_id);
                            selectFacility.change();
                        }

                        var del = $("<img></img>");
                        del.attr("src","../img/x.png");
                        del.addClass("xclose");
                        del.click(function() {
                            li.remove();
                        });
                        del.prependTo(li);

                        if(targets.OVERWRITE !=true && targets.OVERWRITE !="true") {
                            targets.basic				= {
                                VALUE_MAX			: $("#VALUE_MAX").val()			,
                                VALUE_MIN			: $("#VALUE_MIN").val()			,
                                VALUE_WARNING_MAX	: $("#VALUE_WARNING_MAX").val()	,
                                VALUE_WARNING_MIN	: $("#VALUE_WARNING_MIN").val()	,
                                RANGE_PERCENT		: $("#RANGE_PERCENT").val()		,
                                DATA_METHOD			: $("#data_method").val()		,
                                VALUE_FORMAT		: $("#VALUE_FORMAT").val()		,
                            };
                            targets.OVERWRITE			= false;
                        }
                        else targets.OVERWRITE			= true;

                        targets.basic.dataMethods		= dataMethods;
                        var basic = $("<span></span>");
                        basic.addClass("linkViewer");
                        basic.appendTo(li);
                        basic.editable({
                            type		: 'address',
                            onblur		: 'ignore',
                            placement	: 'left',
                            mode		: "popup",
                            value		: targets,
                        });
                        basic.on('save', function(e, params) {
                            var cellColor 	= params.newValue.advance.COLOR;
                            cellColor		= cellColor==""?"transparent":"#"+cellColor;
                            selectObject.css("background-color",cellColor);
                        });
                        var scolor 	= typeof targets.advance == "object"?targets.advance.COLOR:"";
                        scolor		= scolor==""?"transparent":"#"+scolor;
                        selectObject.css("background-color",scolor);
                        li.appendTo($("#objectExtension"));
                    },

                    buildObjectExtension	:  function(){
                        var objects = {};
                        var version=2;
                        $('#objectExtension li').each(function(i){
                            var facilityID = $(this).find( "select.facility" ).eq(0).val();
                            var objectID = $(this).find( "select.object" ).eq(0).val();
                            var targets = $(this).find( "span.editable" ).eq(0).editable('getValue',true);
                            if(targets && targets.basic && targets.basic.dataMethods) delete targets.basic.dataMethods;
                            if(facilityID!=null&&facilityID!=""){
                                if(typeof(objects[facilityID]) == "undefined") objects[facilityID] ={};
                                if(objectID!=null&&objectID!=""){
                                    objects[facilityID][objectID] = targets;
                                }
                            }
                            else{
                                version = null;
                                var objectID2 = $(this).find( "select.object2" ).eq(0).val();
                                if(objectID!=null&&objectID!=""&&objectID2!=null&&objectID2!="")
                                    objects[objectID+'_'+objectID2] = targets;
                            }
                        });
                        version && (objects.version=version);
                        return objects;
                    },

                    saveprop : function(name)
                    {
                        var table 	= $("#data_source").val();
                        var field	=$("#data_field_effected").val();
                        var tmpValue;
                        for (var i = 0; i < receivedData.dataProp.length; i++) {
                            for (var j = 0; j < field.length; j++) {
                                if (receivedData.dataProp[i].COLUMN_NAME == field[j]) {
                                    var effected_fields = (receivedData.dataProp[i]) ? (receivedData.dataProp[i]) : {};
                                    tmpValue = name.is(':checkbox')?(name.prop('checked')?1:0):name.val();
                                    effected_fields[name.attr('name')] = tmpValue;
                                    receivedData.dataProp[i] = effected_fields;
                                    receivedData.config_id = $('#CfgConfig').val();
                                    receivedData.table = table;
                                    break;
                                }
                            }
                        }
                        isChange=true;
                    },
                    saveallData : function(){
                        var table=$('#data_source').val();
                        var configId=$('#CfgConfig').val();
                        var propData=receivedData.dataProp;

                        var fields=$("#data_field_effected").val();
                        _fieldconfig.saveObjectExtension(fields);
                        var editedData={
                            'CfgFieldProps':propData,
                            CfgDataSource : [{
                                NAME :  $("#data_source").val(),
                                DISABLE_DC: $("#chk_dc").prop('checked')?1:0
                            }]
                        };
                        for (var i = 0; i < editedData.CfgFieldProps.length; i++) {
                            if (typeof editedData.CfgFieldProps[i].OBJECT_EXTENSION == 'object')
                                editedData.CfgFieldProps[i].OBJECT_EXTENSION = JSON.stringify(editedData.CfgFieldProps[i].OBJECT_EXTENSION);
                            editedData.CfgFieldProps[i].FIELD_ORDER=i;
                        }
                        var dataSent={	'tabTable'	 :"CfgFieldProps",
                            'TABLE_NAME' :table,
                            'CONFIG_ID'  :configId,
                            'editedData' :editedData,
                            'deleteData' :deletedData
                        };

                        $.ajax({
                            beforeSend: function(){
                                showWaiting();
                            },
                            url: '/savealldata',
                            type: "post",
                            data: dataSent,
                            // data: JSON.stringify(param),
                            // contentType: "application/json",
                            dataType: 'json',
                            success: function(data){
                                if(dataSent.cache === true){
                                    cachedAjaxData[cacheKey] = _data;
                                }
                                //6
                                if(typeof  data =="object" && data!=null){
                                    $("#x_status").html('Save successfully');
                                    alert('Save successfully');
                                    _fieldconfig.renderData(data);
                                }else{
                                    alert(data);
                                }
                                // _fieldconfig.sortExtensionObjects("#objectExtension");
                                hideWaiting();
                            },
                            error: function(_data){
                                if(typeof(funcError) == "function")
                                    funcError(_data);
                                else{
                                    alert('Error: '+_data);
                                    console.log(_data);
                                }
                                hideWaiting();
                            }
                        });
                        isChange=false;

                    },
                }
            _fieldconfig.init();
            $("#chk_dc").on("click", function (e) {
                var checkbox = $(this);
                if (confirm("Do you really want to change this setting?")){
                    var table 	= $("#data_source").val();
                    if(table == undefined || table == null || table == ""){
                        alert("Unknown table");
                        return;
                    }
                } else {
                    e.preventDefault();
                    return false;
                }
            });
        </script>
        <script>
            $( document ).ready(function() {
                $("#saveButton").append($(".saveBtn"));
                isChange=false;

            });
        </script>


        <table>
            <tr>
                <td style="vertical-align:top">
                    <table border="0" id="tbl_detail" style="width:315px;margin-top: -1px">
                        <tr>
                            <td colspan="2" style="max-width: 316px;word-wrap: break-word;"><b style="font-size:1.1em" id="caption">CAPTION</b></td>
                        </tr>
                        <tr>
                            <td class="field">Label</td>
                            <td>
                                <input type="text" name="LABEL" id="LABEL" size="50"
                                       onchange="_fieldconfig.saveprop($(this))" style="height: 26px;width: 165px;margin-bottom: 0px;" >

                            </td>
                        </tr>
                        <tr>
                            <td class="field">Data method</td>
                            <td>
                                <select size="1" name="DATA_METHOD"  id="data_method" onchange="_fieldconfig.saveprop($(this))" style="margin-bottom: 0px; width: 165px">
                                    <option value="">(none)</option>
                                    @foreach($code_data_method as $re)
                                        <option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field">Input type</td>
                            <td>
                                <select size="1" name="INPUT_TYPE" onchange="_fieldconfig.saveprop($(this))" style=";margin-bottom: 0px;width: 165px" id="INPUT_TYPE">
                                    <option value="">(none)</option>
                                    @foreach($cfg_input_type as $re)
                                        <option value="{!!$re['ID']!!}">{!!$re['NAME']!!}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field">Mandatory</td>
                            <td class="field">
                                <div style="display: inline-block ;width: 28px"><input onchange="_fieldconfig.saveprop($(this))" type='checkbox' name='IS_MANDATORY' id="IS_MANDATORY" style="height: 26px;margin-bottom: 0px"></div>
                                <div style="display: inline-block" >Input width
                                    <input onchange="_fieldconfig.saveprop($(this))" type='text' name='FDC_WIDTH' id='FDC_WIDTH' size='5' value='' style="width: 50%;height: 26px;margin-bottom: 0px">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="field">Formula</td>
                            <td>
                                <textarea onchange="_fieldconfig.saveprop($(this))" name="FORMULA" id="FORMULA"  rows="2" style="width: 165px;margin-bottom: 0px"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="field">Data format</td>
                            <td><input  onchange="_fieldconfig.saveprop($(this))" type="text" name="VALUE_FORMAT" id="VALUE_FORMAT" size="50" style="height: 26px ;width: 165px;margin-bottom: 0px"></td>
                        </tr>
                        <tr>
                            <td class="field">Error Max Value</td>
                            <td><input type="text" onchange="_fieldconfig.saveprop($(this))" name=VALUE_MAX id="VALUE_MAX" size="50" style=" height: 26px;width: 165px;margin-bottom: 0px"></td>
                        </tr>
                        <tr>
                            <td class="field">Error Min value</td>
                            <td><input type="text" onchange="_fieldconfig.saveprop($(this))" name="VALUE_MIN" id="VALUE_MIN" size="50" style="height: 26px ;width: 165px;margin-bottom: 0px"></td>
                        </tr>
                        <tr>
                            <td class="field">Warning Max Value</td>
                            <td><input type="text" onchange="_fieldconfig.saveprop($(this))" name="VALUE_WARNING_MAX" id="VALUE_WARNING_MAX" size="50" style="height: 26px ;width: 165px;margin-bottom: 0px"></td>
                        </tr>
                        <tr>
                            <td class="field">Warning Min value</td>
                            <td><input type="text" onchange="_fieldconfig.saveprop($(this))" name="VALUE_WARNING_MIN" id="VALUE_WARNING_MIN" size="50" style="height: 26px ;width: 165px;margin-bottom: 0px"></td>
                        </tr>
                        <tr>
                            <td class="field">Range %</td>
                            <td><input type="text" onchange="_fieldconfig.saveprop($(this))" name="RANGE_PERCENT" id="RANGE_PERCENT" size="50" style="height: 26px ;width: 165px;margin-bottom: 0px"></td>
                        </tr>
                        <tr style="height:30px">


                        </tr>
                    </table>
                </td>
                <td style="display: block;padding-top: 25px;overflow: initial;">
                    <div>
                        <div class="field" style="font-weight: bold">Applied for</div>
                        <div class="floatLeft" style="width: 100%">
                            <div style="margin-top: 10px;margin-bottom: -20px">
                                <input type="checkbox" name="USE_FDC" id="USE_FDC" onchange="_fieldconfig.saveprop($(this))" style="margin-left: 10px;margin-bottom: 5px"><b style="padding-left: 3px; font-weight: normal">Data capture</b></div><br>
                            <div style="margin-bottom: -20px"><input type="checkbox" name="USE_GRAPH" onchange="_fieldconfig.saveprop($(this))" id="USE_GRAPH" style="margin-left: 10px;margin-bottom: 5px"><b style="padding-left: 3px; font-weight: normal">Graph</b></div><br>
                            <input type="checkbox" name="USE_DIAGRAM" id="USE_DIAGRAM" onchange="_fieldconfig.saveprop($(this))" style="margin-left: 10px;margin-bottom: 5px"><b style="padding-left: 3px; font-weight: normal">Surveillance</b>
                        </div>
                        <div class="floatLeft" id ="extensionView" style="width: 100%;display: inline-block">
                            <ul id="objectExtension" style="list-style-type: none;margin: 0;padding-left: 0px;top: 10px">
                            </ul>

                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-left:100px; margin-top: -25px;padding-top: 10px;">

            <input type="button" onClick="_fieldconfig.saveallData()" style="width:100px;margin-top: 3px" value="Save" class="saveBtn">

        </div>

        <link href="/jqueryui-editable/css/jqueryui-editable.css" rel="stylesheet"/>
        <script src="/jqueryui-editable/js/jqueryui-editable.js"></script>
        <script src="/common/js/extendFieldConfig.js?14 "></script>


        <link rel="stylesheet" media="screen" type="text/css" href="/common/colorpicker/css/colorpicker.css" />
        <script type="text/javascript" src="/common/colorpicker/js/colorpicker.js"></script>

        <style>
            .object_facility{
                height: auto!important;
                margin-bottom: -2px;
            }
            .linkViewer{
                margin-bottom: 2px;
            }
            ._colorpicker{
                border:none;
                cursor:pointer;
                z-index: 10000;
            }
            .colorpicker{
                z-index: 10000;
            }
            .field{
                white-space: nowrap;
                font-weight: normal;
            }
            .editable-address {
                display: block;
                margin-bottom: 5px;
                white-space: nowrap;
            }
            .editable-unsaved{
                margin-left: 10px;
            }
            .editable-address span {
                width: 140px;
                display: inline-block;
            }
            #addObjectBtn{
                float: left !important;
                margin-top: 25px;
                margin-left: 10px;
            }
            #extensionView{
                padding-top: 10px;
            }


            .xclose{
                margin-right: 3px;
                margin-left: 10px;
            }
            .object{
                margin-left: 3px;
                margin-right: 3px;
            }
            .facility{
                margin-left: 1px;
            }
        </style>
    </div>
</div>


@section('script')
    @parent
    <script src="/common/js/js.js"></script>
    <script type="text/javascript">
        $(function(){
            $("#add").button({ icons: { primary: "ui-icon-arrowreturnthick-1-e"} });
            $("#remove").button({ icons: { primary: "ui-icon-arrowreturnthick-1-w"} });
            $("#up").button({ icons: { primary: "ui-icon-arrowthick-1-n"} });
            $("#down").button({ icons: { primary: "ui-icon-arrowthick-1-s"} });
            $("#change_field, #save, #reset").button();



            $("#chk_vie, #chk_tbl").change(function(){
                var val=$(this).val();
                var cv = 0;
                var currentValue = $("#data_source").val();
                var checked = $(this).prop('checked');
                $("#data_source option").each(function(){
                    if($(this).attr("SRC_TYPE")==val){
                        $(this).css("display",checked?"block":"none");
                    }
                    else{
                        cv = cv==0 && !checked?$(this).val():cv;
                        if (!checked && currentValue == $(this).val())  cv = currentValue;
                    }
                });
                if(!checked){
                    $("#chk_vie, #chk_tbl").attr('disabled', true);
                    $(this).attr('disabled', false);
                }
                else{
                    $("#chk_vie, #chk_tbl").attr('disabled', false);
                }
                $("#data_source").val(cv==0?currentValue:cv);
                if(currentValue!=cv)$("#data_source").change();




            });

            $("#data_field_effected").change(function(){

                _fieldconfig.data_field_effected_change();
            });

        });
    </script>
    <script>

        $(document).ready(function(){
            param = {
                'chk_tbl'  :$("#chk_tbl").prop('checked'),
                'chk_vie'  :$("#chk_vie").prop('checked')
            }
        });
    </script>
@stop
<style>
    #CfgConfig{
        width: 200px;
    }
    .date_filter{
        height: 61px;
    }
    .hasDatepicker{
        height: 29px !important;
    }
    #editCfgConfig:disabled{
        background-color: #6c757d;
    }
    #deleteCfgConfig:disabled{
        background-color: #6c757d;
    }
    .navi{
        margin-bottom:0px !important;
    }
    .rootMain{
        padding-left: 0px !important;
    }
</style>

@section('editBoxParams')
    @parent
    <script>
        if(typeof actions == 'object' && actions) actions.enableUpdateView = function(tab,postData){
            return false;
        };

        var cfAction = false;
        editBox.showCfgDialog = function(action,element){
            cfAction = action;
            editBox.editRow($("#CfgConfig").find(":selected"),{CODE:$(element).text()},'/cfgconfig/filter');
            $("#ui-datepicker-div").hide();
        }
        editBox.editSelectedObjects	= function(dataStore,resultText){
            //var inputs = $('#ebFilters_ ').find('.date_filter:visible input[type="text"]');
            var date_begin =  moment($("#config_date_begin").val(),configuration.time.DATETIME_FORMAT);
            var date_end =  moment($("#config_date_end").val(),configuration.time.DATETIME_FORMAT);

            var parse_db = date_begin.isValid()?date_begin.format(configuration.time.DATE_FORMAT_UTC):null;
            var parse_de = date_end.isValid()?date_end.format(configuration.time.DATE_FORMAT_UTC):null;
			var isAdding = (cfAction=='insert'||cfAction=='clone');

            dataStore.TABLE_NAME        = $('#data_source').val();
            dataStore.action            = cfAction;
            dataStore.sourceCfConfig    = $('#CfgConfig').val();

            dataStore.editedData = {CfgConfig: [
                    {
                        ID : isAdding?null:$('#CfgConfig').val(),
                        TABLE_NAME : $('#data_source').val(),
                        FACILITY_ID : dataStore.Facility,
                        END_DATE : parse_de,
                        EFFECTIVE_DATE:parse_db,
						isAdding: isAdding?1:0,
                    },
                ]
            };
            editBox.postUpdateData(dataStore);

            //$("#ui-datepicker-div").attr('disabled','disabled');
            return true;

        }
        editBox.updateCfgConfig	= function(dataSets,keep){
            $.each(dataSets, function( index, item ) {
                var elementId = item.postData.tabTable;
                var currentValue  = $('#'+elementId).val();
                $('#'+elementId).html('');   // clear the existing options
                var found=false;
                $.each(item.dataSet, function( dindex, value ) {
                    var option = renderDependenceHtml(elementId,value);
                    var optionData = {FACILITY_ID: value.FACILITY_ID};
                    optionData.EFFECTIVE_DATE         = value.EFFECTIVE_DATE;
                    optionData.END_DATE           = value.END_DATE;
                    option.data(optionData);
                    $('#'+elementId).append(option);
                    if(currentValue==option.attr('value')) found=true;
                });
                if(!found) currentValue=0;

                if(keep) $('#'+elementId).val(currentValue);
                $.data($('#'+elementId), 'current',$('#'+elementId).val());
                if(!found && currentValue) $('#'+elementId).change();
                var disabled = $('#'+elementId).val()==0;
                $("#editCfgConfig").prop("disabled", disabled);
                $("#deleteCfgConfig").prop("disabled", disabled);
            });

        }

        editBox.deleteConfig=function(){
            var dataStore={};
            dataStore.TABLE_NAME = $('#data_source').val();
            dataStore.deleteData = {CfgConfig: [
                    {
                        ID : $('#CfgConfig').val(),
                    }
                ]
            };
            var ok = confirm("Are you sure?");
            if(ok) editBox.postUpdateData(dataStore);

        }
        editBox.postUpdateData=function(dataStore) {
            showWaiting();
            $.ajax({
                url	: "/cfgconfig/save",
                type: "post",
                data: dataStore,
                success:function(data){
                    if (typeof(actions.saveSuccess) == "function") {
                        actions.saveSuccess(data);
                    }
                    editBox.updateCfgConfig(data.dataSets,true);
					if(dataStore.deleteData!=undefined)
						_fieldconfig.data_source_change($('#CfgConfig').val());
					else
						hideWaiting();
                },
                error: function(data) {
                    console.log ( "doSave error");
                    if (typeof(actions.loadError) == "function") {
                        actions.loadError(data);
                    }
                    hideWaiting();
                }
            });
        }
        editBox.updateConfig    = function (showWaiting) {
            if (showWaiting) showWaiting();
            $.ajax({
                url: "/cfgconfig/load",
                type: "post",
                data: {TABLE_NAME : $("#data_source").val(), Facility : 0, tabTable: 'CfgConfig'},
                success: function (data) {
                    editBox.updateCfgConfig([data],false);
                    if (showWaiting) hideWaiting();
                },
                error: function (data) {
                    console.log("doSave error");
                    if (typeof(actions.loadError) == "function") {
                        actions.loadError(data);
                    }
                    if (showWaiting) hideWaiting();
                }
            });
        }

        editBox.onDataSourceChange  = function (cfgConfig) {
            _fieldconfig.data_source_change(cfgConfig);
        }

        $( document ).ready(function() {
            $.data($("#data_source"), 'current', $("#data_source").val());
            $( "#CfgConfig" ).change(function () {
                var self = this;
                _fieldconfig.warningChange(this,function () {
                    editBox.onDataSourceChange($(self).val());
                    var disabled = $(self).val()==0;
                    $("#editCfgConfig").prop("disabled", disabled);
                    $("#deleteCfgConfig").prop("disabled", disabled);
                })
            });
            $("#data_source").change(function(){
                var self = this;
                _fieldconfig.warningChange(this, function () {
                    var disabled = $(self).val()==0;
                    editBox.onDataSourceChange(0);
                    editBox.updateConfig(false);
                    $("#editCfgConfig").prop("disabled", disabled);
                    $("#deleteCfgConfig").prop("disabled", disabled);
                })
            });
            if(_fieldconfig.enableReadyLoad()) $("#data_source").change();
        });
    </script>
@stop