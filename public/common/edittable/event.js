var firstTime = true;
function onAfterGotDependences(elementId,element,currentId){
   if(elementId.indexOf("AllocJob") !== -1
		   ||elementId.indexOf("IntTagSet") !== -1
		   ||(typeof checkDependenceLoading == "function")&&checkDependenceLoading(elementId,element,currentId)){
	   if(firstTime) {
		   var originValue = element.attr("originValue");
		   element.val(originValue);
		   firstTime = false;
	   }
   }
}

(function ($) {
    "use strict";
    
    var timeConfigEditableTpl	= '<table class="eventTable EVENT_TABLE TIMECONFIG" style="width:inherit;"><tbody>'+
     '<tr><td><label><span>Recurring</span></label></td><td><select class="editable-event" name="FREQUENCEMODE"><option value="ONCETIME">ONCETIME</option><option value="DAILY">DAILY</option><option value="WEEKLY">WEEKLY</option><option value="MONTHLY">MONTHLY</option></select><span class="editable-event clickable" name="DailyTime">set time</span></td></tr>'+
	 '<tr class="INTERVALROW" ><td><label><span>Recur every</span></label></td><td><input class="editable-event" type="number" name="INTERVALDAY"><label><span> day(s)</span></label></td></tr>'+
	 '<tr class="STARTTIMEROW" ><td><label><span>Valid from</span></label></td><td><span class="editable-event clickable" name="StartTime">set date</span></td></tr>'+
	 '<tr class="ENDTIMEROW" ><td><label><span>To</span></label></td><td><span class="editable-event clickable" type="text" name="EndTime">set date</span></td></tr>'+
	 '<tr class="DATAROW WEEKDAYROW" ><td><label><span> Week days</span></label></td><td colspan="3"> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="1">Monday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="2">Tuesday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="3">Wednesday</label> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="4">Thursday </label><br><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="5">Friday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="6">Saturday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="0">Sunday</label></td></tr>'+
	 '<tr class="DATAROW MONTHROW" ><td><label><span> Month</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_month[]" value="1"> 1</label><label><input type="checkbox" name="chk_month[]" value="2"> 2</label><label><input type="checkbox" name="chk_month[]" value="3"> 3</label><label><input type="checkbox" name="chk_month[]" value="4"> 4</label><label><input type="checkbox" name="chk_month[]" value="5"> 5</label><label><input type="checkbox" name="chk_month[]" value="6"> 6</label><label><input type="checkbox" name="chk_month[]" value="7"> 7</label><label><input type="checkbox" name="chk_month[]" value="8"> 8</label><label><input type="checkbox" name="chk_month[]" value="9"> 9</label><label><input type="checkbox" name="chk_month[]" value="10"> 10</label><label><input type="checkbox" name="chk_month[]" value="11"> 11</label><label><input type="checkbox" name="chk_month[]" value="12"> 12</label></td></tr>'+
	 '<tr class="DATAROW DAYROW" ><td> <label><span>Day</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_day[]" value="1"> 1</label><label><input type="checkbox" name="chk_day[]" value="2"> 2</label><label><input type="checkbox" name="chk_day[]" value="3"> 3</label><label><input type="checkbox" name="chk_day[]" value="4"> 4</label><label><input type="checkbox" name="chk_day[]" value="5"> 5</label><label><input type="checkbox" name="chk_day[]" value="6"> 6</label><label><input type="checkbox" name="chk_day[]" value="7"> 7</label><label><input type="checkbox" name="chk_day[]" value="8"> 8</label><label><input type="checkbox" name="chk_day[]" value="9"> 9</label><label><input type="checkbox" name="chk_day[]" value="10"> 10</label><label><input type="checkbox" name="chk_day[]" value="11"> 11</label><label><input type="checkbox" name="chk_day[]" value="12"> 12</label><label><input type="checkbox" name="chk_day[]" value="13"> 13</label><label><input type="checkbox" name="chk_day[]" value="14"> 14</label><label><input type="checkbox" name="chk_day[]" value="15"> 15</label><label><input type="checkbox" name="chk_day[]" value="16"> 16</label><br>'+
	 '<label><input type="checkbox" name="chk_day[]" value="17"> 17</label><label><input type="checkbox" name="chk_day[]" value="18"> 18</label><label><input type="checkbox" name="chk_day[]" value="19"> 19</label><label><input type="checkbox" name="chk_day[]" value="20"> 20</label><label><input type="checkbox" name="chk_day[]" value="21"> 21</label><label><input type="checkbox" name="chk_day[]" value="22"> 22</label><label><input type="checkbox" name="chk_day[]" value="23"> 23</label><label><input type="checkbox" name="chk_day[]" value="24"> 24</label><label><input type="checkbox" name="chk_day[]" value="25"> 25</label><label><input type="checkbox" name="chk_day[]" value="26"> 26</label><label><input type="checkbox" name="chk_day[]" value="27"> 27</label><label><input type="checkbox" name="chk_day[]" value="28"> 28</label><label><input type="checkbox" name="chk_day[]" value="29"> 29</label><label><input type="checkbox" name="chk_day[]" value="30"> 30</label><label><input type="checkbox" name="chk_day[]" value="31"> 31</label></td></tr>'+
	 '</tbody></table>';
    
    var event			= [{	type		: "select",
								name		: "FREQUENCEMODE",
								label		: "Recurring",
								collection	: [	{ID	: "ONCETIME"	, NAME	: "ONCETIME" },
									          	{ID	: "DAILY"		, NAME	: "DAILY" },
									          	{ID	: "WEEKLY"		, NAME	: "WEEKLY" },
									          	{ID	: "MONTHLY"		, NAME	: "MONTHLY" },
									          	],
								display		: true,
							},
							{	type		: "input",
					    		name		: "INTERVALDAY",
					    		width		: "200px",
					    		label		: "Recur every day(s)"
			    			}
    						];
    var datetimeValues	= [
                      	   	{ID	: "THIS_DAY"			, NAME	: "THIS DAY" },
				          	{ID	: "MONTH_BEGIN_DAY"		, NAME	: "MONTH_BEGIN_DAY" },
				          	{ID	: "MONTH_END_DAY"		, NAME	: "MONTH_END_DAY" },
				          	{ID	: "WEEK_BEGIN_DAY"		, NAME	: "WEEK_BEGIN_DAY" },
				          	{ID	: "WEEK_END_DAY"		, NAME	: "WEEK_END_DAY" },
				          	{ID	: "SPECIFIC_DAY"		, NAME	: "SPECIFIC_DAY" },
				      	   ];
    var endTimeValues	= datetimeValues.slice();
    
    endTimeValues.unshift({ID	: "NONE"			, NAME	: "none" });
    var startDate	= {	type		: "datetime",
			    		name		: "STARTTIME",
			    		collection	: "datetimeValues",
			    		label		: "Begin time"};
    var endDate		= {	type		: "datetime",
			    		name		: "ENDTIME",
			    		collection	: "endTimeValues",
			    		label		: "End time"};
    var beginTime	= {	type		: "input",
			    		name		: "DATA_BEGINTIME",
			    		width		: "100px",
			    		label		: "Begin time"};
    var endTime		= {	type		: "input",
			    		name		: "DATA_ENDTIME",
			    		width		: "100px",
			    		label		: "End time"};
    var sendLog		= {	type		: "input",
			    		name		: "SENDLOG",
			    		width		: "200px",
			    		label		: "emails"};
    var facility	= {	type		: "select",
			    		name		: "Facility",
			    		label		: "Facility",
			    		collection	: "facility",
						display		: true,
	    				};
    var network		= {
						type		: "select",
						name		: "Network",
						id			: "run_Network",
						label		: "Network",
						dependence	: "AllocJob",
			    		collection	: "networks",
					};
    var allocJob	= {
						type		: "select",
						name		: "AllocJob",
						id			: "run_AllocJob",
						label		: "Job",
						display		: true,
					};
    var runAllocation	= [	network,
                  	   	allocJob,
						startDate,
						endDate,
						sendLog
						];
    var checkAllocation	= [	
                       	   	jQuery.extend(jQuery.extend({},network), {id	: "check_Network"}),
                       	   	jQuery.extend(jQuery.extend({},allocJob), {id	: "check_AllocJob"}),
    						startDate,
    						endDate,
    						sendLog
    						];
    var codeReadingFrequency = {
								type		: "select",
								name		: "CodeReadingFrequency",
								label		: "Record Frequency",
					    		collection	: "codeReadingFrequency",
					    		hasAll		: true,
						    };
    var codeFlowPhase = {
    		type		: "select",
    		name		: "CodeFlowPhase",
    		label		: "Phase Type",
    		hasAll		: true,
    		collection	: "codeFlowPhase",
	    };
    var codeEventType = {
    		type		: "select",
    		name		: "CodeEventType",
    		label		: "Event Type",
    		hasAll		: true,
    		collection	: "codeEventType",
	    };
    
    var intConnection = {
    		type		: "select",
    		name		: "IntConnection",
    		label		: "Connection ",
    		hasAll		: false,
    		dependence	: "IntTagSet",
			id			: "run_IntConnection",
    		collection	: "intConnection",
	    };
    
    var intTagSet = {
    		type		: "select",
    		name		: "IntTagSet",
    		label		: "Tag set",
    		hasAll		: false,
			id			: "run_IntTagSet",
			display		: true,
	    };
    var calMethod = {
    		type		: "select",
    		name		: "CalMethod",
    		label		: "Method",
    		hasAll		: false,
    		collection	: "calMethod",
	    };
    
    var types = {
//		EVENT			: event,
		ALLOC_CHECK		: checkAllocation,
    	ALLOC_RUN		: runAllocation,
    	VIS_WORKFLOW	: [
//    	            	   facility,
					    	{
					    		type		: "select",
					    		name		: "TmWorkflow",
					    		label		: "Workflow",
					    		collection	: "tmWorkflows",
								display		: true,
					    	},
//					    	startDate,
//					    	endDate,
//					    	sendLog
				    	],
				    	
    	FDC_EU			:   [	facility,
	 					    	{
						    		type		: "select",
						    		name		: "EnergyUnitGroup",
						    		label		: "Eu group",
						    		collection	: "energyUnitGroup",
						    		hasAll		: true,
									display		: true,
							    },
							    codeReadingFrequency,
							    codeFlowPhase,
							    codeEventType,
							    {
						    		type		: "select",
						    		name		: "CodeAllocType",
						    		label		: "Alloc Type",
						    		collection	: "codeAllocType",
						    		hasAll		: true,
							    },
							    {
						    		type		: "select",
						    		name		: "CodePlanType",
						    		label		: "Plan Type",
						    		collection	: "codePlanType",
						    		hasAll		: true,
							    },
							    {
						    		type		: "select",
						    		name		: "CodeForecastType",
						    		label		: "Forecast Type",
						    		collection	: "codeForecastType",
						    		hasAll		: true,
							    },
						    	startDate,
						    	endDate,
						    	sendLog
							], 
    	FDC_EU_TEST			:   [	facility,
    	 					    	{
							    		type		: "select",
							    		name		: "EnergyUnit",
							    		label		: "Energy Unit",
							    		collection	: "energyUnit",
							    		hasAll		: true,
										display		: true,
								    },
							    	startDate,
							    	endDate,
							    	sendLog
								], 
    	FDC_FLOW  		:   [	facility,
							    codeReadingFrequency,
							    codeFlowPhase,
						    	startDate,
						    	endDate,
						    	sendLog
							], 
    	FDC_STORAGE 		:   [	facility,
    	            		     	{
							    		type		: "select",
							    		name		: "CodeProductType",
							    		label		: "Product",
							    		collection	: "codeProductType",
							    		hasAll		: true,
								    },
    						    	startDate,
    						    	endDate,
    						    	sendLog
    							], 
		INT_IMPORT_DATA		:   [	intConnection,
		               		     	intTagSet,
		               		     	calMethod,
    						    	beginTime,
    						    	endTime,
    							], 
    };
    
    if(typeof buildMoreEditableType == "function") types = buildMoreEditableType(types);
    var builInputElement = function (type,element,name) {
    	var input = "";
    	var idAttr = typeof element.id == "string" ? 'id="'+element.id+'"':'';
    	if(type=="select"||type=="datetime")
    		input = '<select '+idAttr+' class="editable-event" name="'+element.name+'"></select>';
    	else if(type=="input")
    		input = '<input class="editable-event eventTaskInput" name="'+element.name+'"></input>';
    	if(type=="datetime")
    		input += '<span class="editable-event clickable" name="'+element.name+'_PICKER_'+name+'">set '+type+'</span>';
    	else if(type=="date"){
    		var idAttribute = element.id!==undefined?' id="'+element.id+'"':"";
    		input += '<span class="editable-event clickable" name="'+element.name+'"'+idAttribute+'>set '+type+'</span>';
    	}
    	return input;
    };
    
    var buildEditableTemplate = function (types) {
    	var html = timeConfigEditableTpl;
    	for (var name in types) {
    		var dependences	= [];
    		html+='<table class="eventTable '+name+'_TABLE TASKCONFIG" style="width:inherit;"><tbody>';
    		var preRow 			= '<tr>';
    		var afterRow 		= '</tr>';
    		var columnNumber	= 1;
    		if(types[name].length>5){
    			columnNumber = 2;
    		}
    		$.each(types[name], function(key, element) {
    	    	var widthAttr = typeof element.width == "string" ? 'width="'+element.width+'"':'';
    			if(columnNumber>1){
    				if(key%columnNumber==0){
        				preRow		=  '<tr>';
        				afterRow	=  '';
        			}
    				else if(key%columnNumber==(columnNumber-1)){
    					preRow		=  '';
    					afterRow	=  '</tr>';
    				}
    				else{
    					preRow		=  '';
    					afterRow	=  '';
    				}
    			}
    			html+=preRow+'<td><label><span>'+element.label+'</span></label></td><td '+widthAttr+'>'+builInputElement(element.type,element,name)+'</td>'+afterRow;
    			if(typeof element.dependence != "undefined" && typeof element.id != "undefined" ){
    				dependences.push({	source	: element.id,
										targets	: element.dependence,
										extra	: element.extra !== undefined?element.extra:null
									});
    			}
    		});
    		html+='</tbody></table>';
    		for (var i = 0; i < dependences.length; i++) {
    			html+="<script>registerOnChange('"+dependences[i].source+"',['"+dependences[i].targets+"'],'"+JSON.stringify(dependences[i].extra) +"')<\/script>";
			}
		}
    	return html;
    };
    
    var tpl	= buildEditableTemplate(types);
    
    var EVENT = function (options) {
        this.init('EVENT', options, EVENT.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(EVENT, $.fn.editabletypes.abstractinput);

    $.extend(EVENT.prototype, {
        /**
        Renders input from tpl

        @method render() 
        **/        
        render: function() {
        	this.$input 		= this.$tpl.find('input');
        	this.$select 		= this.$tpl.find('select');
        	this.$span 			= this.$tpl.find('span');
        	this.$rows 			= this.$tpl.find('.DATAROW');
        	this.$tables 		= this.$tpl.filter("table");
        	this.datetimeValues	= {STARTTIME	: datetimeValues,ENDTIME	: endTimeValues,};
        },
        /**
        Default method to show value in element. Can be overwritten by display option.
        
        @method value2html(value, element) 
        **/
        value2html: function(value, element) {
        	if(!value) {
                $(element).empty();
                return; 
            }
        	var text = "";
        	switch(this.options.configType){
        	case "EVENT" :
        		text = value.FREQUENCEMODE;
        		break;
        	default:
        		text = value.name;
        		break;
        	}
        	text	= typeof text == "string" && text!=""?text:"config";
        	var html = '<b>' + text+ '</b>';
            $(element).html(html); 
        },
        
        /**
        Gets value from element's html
        
        @method html2value(html) 
        **/        
        html2value: function(html) {        
          /*
            you may write parsing method to get value by element's html
            e.g. "Moscow, st. Lenina, bld. 15" => {city: "Moscow", street: "Lenina", building: "15"}
            but for complex structures it's not recommended.
            Better set value directly via javascript, e.g. 
            editable({
                value: {
                    city: "Moscow", 
                    street: "Lenina", 
                    building: "15"
                }
            });
          */ 
          return null;  
        },
      
       /**
        Converts value to string. 
        It is used in internal comparing (not for sending to server).
        
        @method value2str(value)  
       **/
       value2str: function(value) {
           var str = '';
           if(value) {
               /*for(var k in value) {
                   str = str + k + ':' + value[k] + ';';  
               }*/
        	   str = JSON.stringify(value);
           }
           return str;
       }, 
       
       /*
        Converts string to value. Used for reading value from 'data-value' attribute.
        
        @method str2value(str)  
       */
       str2value: function(str) {
           /*
           this is mainly for parsing value defined in data-value attribute. 
           If you will always set value by javascript, no need to overwrite it
           */
           return str;
       },                
       
       /**
        Sets value of input.
        
        @method value2input(value) 
        @param {mixed} value
       **/         
       value2input: function(value) {
			if(typeof value == "undefined") return;
			switch(this.options.configType){
				case "EVENT" :
					if(value==null) value = {};
					this.$select.filter('[name="FREQUENCEMODE"]').val(value.FREQUENCEMODE);
					this.$input.filter('[name="INTERVALDAY"]').val(value.INTERVALDAY);
					this.originRenderDateTimePicker('StartTime',value.StartTime,'date');
					this.originRenderDateTimePicker('EndTime',value.EndTime,'date');
					this.originRenderDateTimePicker('DailyTime',value.DailyTime,'datetime');
					this.renderValue('.WEEKDAYROW',value.WEEKDAY);
					this.renderValue('.DAYROW',value.MONTHDAY);
					this.renderValue('.MONTHROW',value.MONTH);
				break;
				default :
					if(!value) return;
					var elements = types[this.options.configType];
					if(elements!==undefined)
						for (var int = 0; int < elements.length; int++) {
							this.renderElement(value,elements[int]);
						}
					break;
				break;
			}
       },       
       renderElement: function(value,element) {
    	   switch(element.type){
			case "select" :
				var select 		= this.$select.filter('[name="'+element.name+'"]');
				var collection 	= [];
				if(typeof this.collection =="undefined" ) this.collection = [];
				this.collection[element.collection]	= typeof value[element.collection] == "object" ? value[element.collection]: this.collection[element.collection];
				if(element.hasAll==true)
					select.append($("<option></option>")
						.attr("value",0)
						.text("(All)"));
				if(typeof this.collection[element.collection] == "object"){
					collection	= this.collection[element.collection];
				}
				$.each(collection, function(key, item) {
					var valueId = typeof item.ID !="undefined"?item.ID:item.id;
					var text = typeof item.NAME !="undefined"?item.NAME:item.name;
					select.append($("<option></option>")
							.attr("value",valueId)
							.text(text)); 
				});
				select.val(value[element.name]);
				select.attr("originValue",value[element.name]);
				break;
			case "datetime" :
				this.renderDatetimeInput(element.name,value[element.name]);
				break;
			case "date" :
				this.originRenderDateTimePicker(element.name,value[element.name],element.type);
				break;
			case "input" :
				this.$input.filter('[name="'+element.name+'"]').val(value[element.name]);
				break;
    	   }
       },
       /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() {
			var value	= {};
			switch(this.options.configType){
				case "EVENT" :
					var weekdays 	= this.extractValue('.WEEKDAYROW');
					var months 		= this.extractValue('.MONTHROW');
					var days 		= this.extractValue('.DAYROW');
					var startTime	= this.originGetDatetimeValue('StartTime','date');
					var endTime		= this.originGetDatetimeValue('EndTime','date');
					var dailyTime	= this.originGetDatetimeValue('DailyTime','time');
					value	= {
							FREQUENCEMODE	: this.$select.filter('[name="FREQUENCEMODE"]').val(),
							INTERVALDAY		: this.$input.filter('[name="INTERVALDAY"]').val(),
							StartTime		: startTime,
							EndTime			: endTime,
							DailyTime		: dailyTime,
							WEEKDAY			: weekdays,
							MONTHDAY		: days,
							MONTH			: months,
					};
				break;
				default:
					var elements = types[this.options.configType];
					if(elements===undefined) break;
					for (var int = 0; int < elements.length; int++) {
						value[elements[int].name] = this.getElementValue(elements[int]);
						if(elements[int].display==true) 
							value.name = this.$select.filter('[name="'+elements[int].name+'"]:visible').find(":selected").text();
					}
					break;
			}
			return value;
       },
       
       getElementValue: function(element) {
    	   var value = "";
    	   switch(element.type){
			case "select" :
				value = this.$select.filter('[name="'+element.name+'"]:visible').val();
				break;
			case "datetime" :
				value = {	
						type	: this.$select.filter('[name="'+element.name+'"]:visible').val(),
						value	: this.getDatetimeValue(element.name)
				}
				break;
			case "date" :
				if(element.id!==undefined) value = this.originGetDatetimeValue(element.id,'date','id');
				else value = this.originGetDatetimeValue(element.name,'date');
				break;
			case "input" :
				value = this.$input.filter('[name="'+element.name+'"]:visible').val();
				break;
    	   }
    	   return value;
       },
       
       getDatetimeValue: function(filterName) {
    	   var timeText	= this.$span.filter('[name="'+filterName+'_PICKER_'+this.options.configType+'"]').text();
    	   var datetime	= moment.utc(timeText,configuration.time.DATETIME_FORMAT);
    	   var value= datetime.isValid()?datetime.format(configuration.time.DATETIME_FORMAT_UTC):null;
           return value;
       },
       originGetDatetimeValue: function(filterName,type,selectType) {
    	   if(selectType===undefined) selectType = "name";
    	   var timeText	= this.$span.filter('['+selectType+'="'+filterName+'"]').text();
    	   var datetime;
    	   var tFormat;
    	   if(type=="date"){
    		   tFormat	= configuration.time.DATE_FORMAT_UTC;
    		   datetime	= moment.utc(timeText,configuration.time.DATE_FORMAT);
    	   }
    	   else {
    		   tFormat	= configuration.time.DATETIME_FORMAT;
    		   datetime	= type=="time"?	moment.utc(timeText,configuration.time.TIME_FORMAT)
    				   :moment.utc(timeText,configuration.time.DATETIME_FORMAT);
    	   }
    	   var value= datetime.isValid()?datetime.format(configuration.time.DATETIME_FORMAT_UTC):null;
           return value;
       },
       extractValue: function(filterName) {
    	   var weekdays =$.grep(this.$rows.filter(filterName).find('td input'), function(object){ 
             	 return $(object).is(":checked");
    	   });
    	   var value = $.map( weekdays, function( object,key ) {
    		   return object.value;
    	   });
    	   
           return value;
       },
       renderDatetimeValue: function(filterName,value) {
    	   var datetime	= moment.utc(value,configuration.time.DATE_FORMAT_UTC);
    	   if(datetime.isValid()) this.$span.filter('[name="'+filterName+'_PICKER_'+this.options.configType+'"]').text(datetime.format(configuration.time.DATE_FORMAT));
    	   else  this.$span.filter('[name="'+filterName+'_PICKER_'+this.options.configType+'"]').text('');
    	   
       },
       originRenderDatetimeValue: function(filterName,value,type) {
    	   	var mommentFormat	= type=='datetime'?configuration.time.DATETIME_FORMAT_UTC:configuration.time.DATE_FORMAT_UTC;
    	   	var textFormat		= type=='datetime'?configuration.time.TIME_FORMAT:configuration.time.DATE_FORMAT;
    	   	var datetime		=  type=='datetime'?moment.utc(value,mommentFormat):moment(value,mommentFormat);
    	   	if(datetime.isValid()) {
    	   		this.$span.filter('[name="'+filterName+'"]').text(datetime.format(textFormat));
    	   		this.$span.filter('[name="'+filterName+'_PICKER_'+this.options.configType+'"]').text(datetime.format(textFormat));
    	   	}
       },
       renderDatetimeInput: function(filterName,datetimeValue) {
//    	   this.datetimeValues		= typeof value.datetimeValues == "object" ? value.datetimeValues: this.datetimeValues;
    	   var collection	= this.datetimeValues[filterName];
		   if(typeof collection == "object"){
			   var select = this.$select.filter('[name="'+filterName+'"]');
			   $.each(collection, function(key, value) {   
				   select.append($("<option></option>")
						   .attr("value",value.ID)
						   .text(value.NAME)); 
			   });
			   var selectValue = typeof datetimeValue == "object" && datetimeValue!=null?datetimeValue.type:"";
			   select.val(selectValue);
			   var spans = this.$span;
			   if(selectValue=="SPECIFIC_DAY") this.renderDatetimeValue(filterName,datetimeValue.value);
			   select.change(function(e){
				   if(this.value=="SPECIFIC_DAY") spans.filter('[name="'+filterName+'_PICKER_'+this.options.configType+'"]:visible').click();
				   else spans.filter('[name="'+filterName+'_PICKER"]:visible').text("");
			   });
		   }
		   this.renderDateTimePicker(filterName);
       },
       renderValue: function(filterName,value) {
    	   if(typeof value== 'object'){
    		   $.grep(this.$rows.filter(filterName).find('td input'), function(object){
    			   var checked	= $.inArray($(object).val(),value)>-1;
    			   $(object).prop('checked', checked);
    			   return checked;
    		   });
    	   }
       },
       activateTimeEvent: function() {
    	   var editableInputs 	= this.$input;
    	   var frequenceMode = this.$select.filter('[name="FREQUENCEMODE"]');
    	   frequenceMode.change(function() {
    		   $(".INTERVALROW").hide();
    		   $(".MONTHROW").hide();
    		   $(".DAYROW").hide();
    		   $(".WEEKDAYROW").hide();
    		   
    		   switch ($(this).val()){
    		   case	"ONCETIME":
    			   $(".INTERVALROW").hide();
    			   break;
    		   case	"DAILY":
        		   $(".INTERVALROW").show();
    			   break;
    		   case	"WEEKLY":
    			   $(".INTERVALROW").hide();
    			   $(".WEEKDAYROW").show();
    			   break;
    		   case	"MONTHLY":
    			   $(".INTERVALROW").hide();
    			   $(".DAYROW").show();
    			   $(".WEEKDAYROW").show();
    			   $(".MONTHROW").show();
    			   break;
    		   }
    	   });
    	   frequenceMode.change();
       },
       renderDateTimePicker: function(filterQuery) {
    	   var  editable = {
//    			   title			: 'edit',
    			   clear			: false,
    			   emptytext		: '',
    			   onblur			: 'submit',
    			   showbuttons		: true,
    			   mode				: 'popup',
    			   placement 		: "bottom",
    			   type				: 'date',
    			   format			: configuration.picker.DATE_FORMAT_UTC,
    			   viewformat		: configuration.picker.DATE_FORMAT,
    			   /*datetimepicker	: {
    				   minuteStep :5,
    				   showMeridian : true,
    			   },*/
    	   };
    	   var datetimeInputs	= this.$span.filter('[name="'+filterQuery+'_PICKER_'+this.options.configType+'"]');
    	   datetimeInputs.editable(editable);
    	   
       },
       originRenderDateTimePicker: function(filterQuery,value,type) {
    	   var  editable = {
    			   title			: 'edit',
    			   clear			: false,
    			   emptytext		: '',
    			   onblur			: 'submit',
    			   showbuttons		: true,
    			   mode				: 'popup',
    			   placement 		: "bottom",
    			   type				: type,
    			   format			: configuration.picker.DATE_FORMAT_UTC,
    			   viewformat		: configuration.picker.DATE_FORMAT,
    	   };
    	   	if(type=='datetime'){
    	   		editable['format'] 			= configuration.picker.TIME_FORMAT_UTC;
    	   		editable['viewformat'] 		= configuration.picker.TIME_FORMAT;
    	   		editable['datetimepicker'] 	= 	{
    	   				minuteStep :5,
    	   				showMeridian : true,
    	   				startView:1,
    	   				minView:0,
    	   				maxView:1,
    	   		};
    	   		
    	   	}
			
    	   var datetimeInputs	= this.$span.filter('[name="'+filterQuery+'"]');
    	   datetimeInputs.editable(editable);
    	   this.originRenderDatetimeValue(filterQuery,value,type);
    	   
       },
       activateJobEvent: function(name) {
    	   this.$select.filter('[name="'+name+'"]').change();
       },
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
		   this.$tables.css("display","none");
    	   this.$tables.filter('.'+this.options.configType+"_TABLE").css("display","block");
			switch(this.options.configType){
			case "EVENT" :
				this.activateTimeEvent();
				break;
			case "TASK" :
			case "ALLOC_CHECK" :
			case "ALLOC_RUN" :
				this.activateJobEvent("Network");
				break;
			case "INT_IMPORT_DATA" :
				this.activateJobEvent("IntConnection");
				break;
			case "QLTY_DATA" :
			case "QLTY_DATA_DETAIL" :
				this.activateJobEvent("CodeQltySrcType");
				break;
			case "WORKFLOW" :
				break;
			}
	       $( ".editable-container" ).draggable();
       },  
       
       /**
        Attaches handler to submit form in case of 'showbuttons=false' mode
        
        @method autosubmit() 
       **/       
       autosubmit: function() {
           this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
           });
       }       
    });

    
    EVENT.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: tpl,
        inputclass	: '',
        configType	: 'EVENT',
    });
    $.fn.editabletypes.EVENT = EVENT;
}(window.jQuery));