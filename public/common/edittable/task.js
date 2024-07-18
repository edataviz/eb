(function ($) {
    "use strict";
    
    var TASK = function (options) {
        this.init('TASK', options, TASK.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(TASK, $.fn.editabletypes.abstractinput);

    $.extend(TASK.prototype, {
        /**
        Renders input from tpl

        @method render() 
        **/        
        render: function() {
           this.$input = this.$tpl.find('input');
           this.$select = this.$tpl.find('select');
           this.$rows = this.$tpl.find('.DATAROW');
           this.$span = this.$tpl.find('span');
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
        	var html = '<b>' + value.FREQUENCEMODE+ '</b>';
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
           if(typeof value == "undefined" || !value) return;
           
    	   this.$select.filter('[name="FREQUENCEMODE"]').val(value.FREQUENCEMODE);
    	   this.$input.filter('[name="INTERVALDAY"]').val(value.INTERVALDAY);
    	   this.renderDatetimeValue('STARTTIME',value.STARTTIME);
    	   this.renderDatetimeValue('ENDTIME',value.ENDTIME);
    	   this.renderValue('.WEEKDAYROW',value.WEEKDAY);
    	   this.renderValue('.DAYROW',value.MONTHDAY);
    	   this.renderValue('.MONTHROW',value.MONTH);
       },       
       
       /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() {
    	   var weekdays = this.extractValue('.WEEKDAYROW');
    	   var months 	= this.extractValue('.MONTHROW');
    	   var days 	= this.extractValue('.DAYROW');
    	   var startTime= this.getDatetimeValue('STARTTIME');
    	   var endTime	= this.getDatetimeValue('ENDTIME');

    	   var value	= {
		    			   FREQUENCEMODE	: this.$select.filter('[name="FREQUENCEMODE"]').val(),
		    			   INTERVALDAY		: this.$input.filter('[name="INTERVALDAY"]').val(),
		    			   STARTTIME		: startTime,
		    			   ENDTIME			: endTime,
		    			   WEEKDAY			: weekdays,
		    			   MONTHDAY			: days,
		    			   MONTH			: months,
			       		};
           return value;
       },
       
       getDatetimeValue: function(filterName) {
    	   var timeText	= this.$span.filter('[name="'+filterName+'"]').text();
    	   var datetime	= moment.utc(timeText,configuration.time.DATETIME_FORMAT);
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
    	   var datetime	= moment.utc(value,configuration.time.DATETIME_FORMAT_UTC);
    	   if(datetime.isValid()) this.$span.filter('[name="'+filterName+'"]').text(datetime.format(configuration.time.DATETIME_FORMAT));
    	   
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
       
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
    	   var editableInputs 	= this.$input;
    	   var frequenceMode = this.$select.filter('[name="FREQUENCEMODE"]');
    	   frequenceMode.change(function() {
    		   $(".INTERVALROW").show();
    		   $(".MONTHROW").hide();
    		   $(".DAYROW").hide();
    		   $(".WEEKDAYROW").hide();
    		   
        	   switch ($(this).val()){
        	   case	"ONCETIME":
        		   $(".INTERVALROW").hide();
        		   break;
        	   case	"DAILY":
        		   break;
        	   case	"WEEKLY":
        		   $(".WEEKDAYROW").show();
        		   break;
        	   case	"MONTHLY":
        		   $(".DAYROW").show();
        		   $(".WEEKDAYROW").show();
        		   $(".MONTHROW").show();
        		   break;
        	   }
	       });
    	   frequenceMode.change();
    	   
	       var  editable = {
		    	    title			: 'edit',
		    	    clear			: false,
		    	    emptytext		: '',
		    	    onblur			: 'submit',
		    	    showbuttons		: false,
		    	    mode			: 'popup',
		    	    type			: 'datetime',
		    	    format			: configuration.picker.DATETIME_FORMAT_UTC,
		    	    viewformat		: configuration.picker.DATETIME_FORMAT,
		    	    datetimepicker	: {
						          		minuteStep :5,
						          		showMeridian : true,
						            },
		    	};
	       var datetimeInputs	= this.$span.filter('[name="STARTTIME"],[name="ENDTIME"]');
	       datetimeInputs.editable(editable);
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

    
    TASK.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<table class="eventTable" style="width:inherit;"><tbody><tr><td><label><span>Recurring</span></label></td><td><select class="editable-event" name="FREQUENCEMODE"><option value="ONCETIME">ONCETIME</option><option value="DAILY">DAILY</option><option value="WEEKLY">WEEKLY</option><option value="MONTHLY">MONTHLY</option></select></td></tr>'+
   	 		 '<tr class="INTERVALROW" ><td><label><span>Recur every</span></label></td><td><input class="editable-event" type="number" name="INTERVALDAY"><label><span> day(s)</span></label></td></tr>'+
        	 '<tr class="STARTTIMEROW" ><td><label><span>Start Date</span></label></td><td><span class="editable-event clickable" name="STARTTIME">set datetime</span></td></tr>'+
        	 '<tr class="ENDTIMEROW" ><td><label><span>End Date</span></label></td><td><span class="editable-event clickable" type="text" name="ENDTIME">set datetime</span></td></tr>'+
        	 '<tr class="DATAROW WEEKDAYROW" ><td><label><span> Week days</span></label></td><td colspan="3"> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="1">Monday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="2">Tuesday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="3">Wednesday</label> <label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="4">Thursday </label><br><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="5">Friday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="6">Saturday </label><label class="weekDayLabel"><input class="dhx_repeat_checkbox" type="checkbox" name="week_day" value="0">Sunday</label></td></tr>'+
        	 '<tr class="DATAROW MONTHROW" ><td><label><span> Month</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_month[]" value="1"> 1</label><label><input type="checkbox" name="chk_month[]" value="2"> 2</label><label><input type="checkbox" name="chk_month[]" value="3"> 3</label><label><input type="checkbox" name="chk_month[]" value="4"> 4</label><label><input type="checkbox" name="chk_month[]" value="5"> 5</label><label><input type="checkbox" name="chk_month[]" value="6"> 6</label><label><input type="checkbox" name="chk_month[]" value="7"> 7</label><label><input type="checkbox" name="chk_month[]" value="8"> 8</label><label><input type="checkbox" name="chk_month[]" value="9"> 9</label><label><input type="checkbox" name="chk_month[]" value="10"> 10</label><label><input type="checkbox" name="chk_month[]" value="11"> 11</label><label><input type="checkbox" name="chk_month[]" value="12"> 12</label></td></tr>'+
        	 '<tr class="DATAROW DAYROW" ><td> <label><span>Day</span></label></td><td colspan="3"> <label><input type="checkbox" name="chk_day[]" value="1"> 1</label><label><input type="checkbox" name="chk_day[]" value="2"> 2</label><label><input type="checkbox" name="chk_day[]" value="3"> 3</label><label><input type="checkbox" name="chk_day[]" value="4"> 4</label><label><input type="checkbox" name="chk_day[]" value="5"> 5</label><label><input type="checkbox" name="chk_day[]" value="6"> 6</label><label><input type="checkbox" name="chk_day[]" value="7"> 7</label><label><input type="checkbox" name="chk_day[]" value="8"> 8</label><label><input type="checkbox" name="chk_day[]" value="9"> 9</label><label><input type="checkbox" name="chk_day[]" value="10"> 10</label><label><input type="checkbox" name="chk_day[]" value="11"> 11</label><label><input type="checkbox" name="chk_day[]" value="12"> 12</label><label><input type="checkbox" name="chk_day[]" value="13"> 13</label><label><input type="checkbox" name="chk_day[]" value="14"> 14</label><label><input type="checkbox" name="chk_day[]" value="15"> 15</label><label><input type="checkbox" name="chk_day[]" value="16"> 16</label><br>'+
        	 '<label><input type="checkbox" name="chk_day[]" value="17"> 17</label><label><input type="checkbox" name="chk_day[]" value="18"> 18</label><label><input type="checkbox" name="chk_day[]" value="19"> 19</label><label><input type="checkbox" name="chk_day[]" value="20"> 20</label><label><input type="checkbox" name="chk_day[]" value="21"> 21</label><label><input type="checkbox" name="chk_day[]" value="22"> 22</label><label><input type="checkbox" name="chk_day[]" value="23"> 23</label><label><input type="checkbox" name="chk_day[]" value="24"> 24</label><label><input type="checkbox" name="chk_day[]" value="25"> 25</label><label><input type="checkbox" name="chk_day[]" value="26"> 26</label><label><input type="checkbox" name="chk_day[]" value="27"> 27</label><label><input type="checkbox" name="chk_day[]" value="28"> 28</label><label><input type="checkbox" name="chk_day[]" value="29"> 29</label><label><input type="checkbox" name="chk_day[]" value="30"> 30</label><label><input type="checkbox" name="chk_day[]" value="31"> 31</label></td></tr>'+
            '</tbody></table>',
        inputclass: ''
    });
    $.fn.editabletypes.TASK = TASK;
}(window.jQuery));