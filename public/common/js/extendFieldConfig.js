(function ($) {
    "use strict";
    
    var Address = function (options) {
        this.init('address', options, Address.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(Address, $.fn.editabletypes.abstractinput);

    $.extend(Address.prototype, {
        /**
        Renders input from tpl

        @method render() 
        **/        
        render: function() {
        	this.$input 	= this.$tpl.find('input');
       		this.$select 	= this.$tpl.find('select');
        },
        
        /**
        Default method to show value in element. Can be overwritten by display option.
        
        @method value2html(value, element) 
        **/
        value2html: function(value, element) {
            /*if(!value) {
                $(element).empty();
                return; 
            }
            var html = $('<div>').text(value.VALUE_MAX).html() + ', ' + $('<div>').text(value.street).html() + ' st., bld. ' + $('<div>').text(value.building).html();
            $(element).html(html); */
        	
        	if(!value) {
                $(element).empty();
                return; 
            }
        	var text = "Rules";
        	if(typeof value.advance == "object") {
        		var texts	= [];
        		if(value.advance.KEEP_DISPLAY_VALUE==true||value.advance.KEEP_DISPLAY_VALUE=="true") texts.push("Display origin value");
        		if(value.advance.ENFORCE_EDIT_NOTE==true||value.advance.ENFORCE_EDIT_NOTE=="true") texts.push("Enforce Edit Note");
        		if(texts.length>0) text = texts.join(",");
            }
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
           if(!value) {
             return;
           }
           if(typeof value.basic != "undefined") {
        	   this.$input.filter('[name="OVERWRITE"]').prop('checked', value.OVERWRITE);
        	   this.$input.filter('[name="VALUE_MAX"]').val(value.basic.VALUE_MAX);
        	   this.$input.filter('[name="VALUE_MIN"]').val(value.basic.VALUE_MIN);
        	   this.$input.filter('[name="VALUE_WARNING_MAX"]').val(value.basic.VALUE_WARNING_MAX);
        	   this.$input.filter('[name="VALUE_WARNING_MIN"]').val(value.basic.VALUE_WARNING_MIN);
        	   this.$input.filter('[name="RANGE_PERCENT"]').val(value.basic.RANGE_PERCENT);
        	   this.$input.filter('[name="VALUE_FORMAT"]').val(value.basic.VALUE_FORMAT);
        	   this.$input.filter('[name="IS_MANDATORY"]').prop('checked', value.basic.IS_MANDATORY==true||value.basic.IS_MANDATORY=="true");

        	   var dataMethodSelect = this.$select.filter('[name="DATA_METHOD"]');
        	   this.dataMethods		= typeof value.basic.dataMethods == "object" ? value.basic.dataMethods: this.dataMethods;
			   if(typeof this.dataMethods == "object"){
				   $.each(this.dataMethods, function(key, value) {
					   var id 	= typeof value.ID 	!= "undefined"? value.ID	: value.id;
					   var name = typeof value.NAME != "undefined"? value.NAME	: value.name;
					   dataMethodSelect.append($("<option></option>")
							   .attr("value",id)
							   .text(name)); 
				   });
				   dataMethodSelect.val(value.basic.DATA_METHOD);
			   }

           }
           if(typeof value.advance != "undefined") {
        	   this.$input.filter('[name="KEEP_DISPLAY_VALUE"]').prop('checked', value.advance.KEEP_DISPLAY_VALUE==true||value.advance.KEEP_DISPLAY_VALUE=="true");
        	   this.$input.filter('[name="ENFORCE_EDIT_NOTE"]').prop('checked', value.advance.ENFORCE_EDIT_NOTE==true||value.advance.ENFORCE_EDIT_NOTE=="true");
        	   this.$input.filter('[name="COLOR"]').val(value.advance.COLOR);
           }
       },       
       
       /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() {
    	   var value	= {
			       	   		OVERWRITE	: this.$input.filter('[name="OVERWRITE"]').is(":checked"),
			    	   		basic		: {
						  		        	  VALUE_MAX			: this.$input.filter('[name="VALUE_MAX"]').val(), 
								        	  VALUE_MIN			: this.$input.filter('[name="VALUE_MIN"]').val(), 
								        	  VALUE_WARNING_MAX	: this.$input.filter('[name="VALUE_WARNING_MAX"]').val(),
								        	  VALUE_WARNING_MIN	: this.$input.filter('[name="VALUE_WARNING_MIN"]').val(),
								        	  RANGE_PERCENT		: this.$input.filter('[name="RANGE_PERCENT"]').val(),
								        	  DATA_METHOD		: this.$select.filter('[name="DATA_METHOD"]').val(),
								        	  VALUE_FORMAT		: this.$input.filter('[name="VALUE_FORMAT"]').val(),
								        	  IS_MANDATORY		: this.$input.filter('[name="IS_MANDATORY"]').is(":checked"),
							           },
			    	   		advance		: {
			        		   					KEEP_DISPLAY_VALUE	: this.$input.filter('[name="KEEP_DISPLAY_VALUE"]').is(":checked"),
			        		   					ENFORCE_EDIT_NOTE	: this.$input.filter('[name="ENFORCE_EDIT_NOTE"]').is(":checked"),
			             	  					COLOR				: this.$input.filter('[name="COLOR"]').val(),
			         	  				}
			       		};
           return value;
       },        
       
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
    	   var editableInputs 	= this.$input;
    	   var dataMethodSelect = this.$select.filter('[name="DATA_METHOD"]');
    	   var overwite			= editableInputs.filter('[name="OVERWRITE"]').is(":checked");
    	   var effectFields		= editableInputs.filter('[name="RANGE_PERCENT"],[name="VALUE_WARNING_MIN"],[name="VALUE_WARNING_MAX"],[name="VALUE_MIN"],[name="VALUE_MAX"],[name="VALUE_FORMAT"],[name="IS_MANDATORY"]');
    	   effectFields.prop('disabled', !overwite);
            dataMethodSelect.prop('disabled', overwite?false:'disabled');
            editableInputs.filter('[name="OVERWRITE"]').change(function(e){
            	effectFields.prop('disabled', !this.checked);
            	dataMethodSelect.prop('disabled', this.checked?false:'disabled');
    	   });
    	   
    	   editableInputs.filter('[name="OVERWRITE"]').focus();
    	   var colorPicker	= editableInputs.filter('[name="COLOR"]');
    	   var color		= colorPicker.val()==""?"transparent":"#"+colorPicker.val();
           colorPicker.css("background-color",color);
           colorPicker.css("color",color);
           colorPicker.ColorPicker({
        		onSubmit: function(hsb, hex, rgb, el) {
        			$(el).val(hex);
        			$(el).ColorPickerHide();
        			$(el).css("background-color","#"+hex);
        			$(el).css("color","#"+hex);
        		},
        		onBeforeShow: function () {
        			$(this).ColorPickerSetColor($(this).val());
        		}
        	});
	       colorPicker.parent().next(".removeColor").click(function() {
	        	colorPicker.val("");
	        	colorPicker.css("background-color","transparent");
	       });
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

    Address.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-address"><label><span>Overwrite</span><input type="checkbox" name="OVERWRITE"></label></div>'+
		 	 '<div class="editable-address"><label><span>Data method </span><select class="editable-event" name="DATA_METHOD"></select></label></div>'+
             '<div class="editable-address"><label><span>Mandatory</span><input type="checkbox" name="IS_MANDATORY"></label></div>'+
		 	 '<div class="editable-address"><label><span>Data format </span><input type="text" name="VALUE_FORMAT" class="input-small"></label></div>'+
		 	 '<div class="editable-address"><label><span>Error Max Value </span><input type="number" step="any" name="VALUE_MAX" class="input-small"></label></div>'+
             '<div class="editable-address"><label><span>Error Min Value </span><input type="number" step="any" name="VALUE_MIN" class="input-small"></label></div>'+
             '<div class="editable-address"><label><span>Warning Max Value </span><input type="number" step="any" name="VALUE_WARNING_MAX" class="input-small"></label></div>'+
             '<div class="editable-address"><label><span>Warning Min Value </span><input type="number" step="any" name="VALUE_WARNING_MIN" class="input-small"></label></div>'+
             '<div class="editable-address"><label><span>Range percent </span><input type="number" step="any" name="RANGE_PERCENT" class="input-mini"></label></div>'+
             '<div class="editable-address"><label><span>Display origin value</span><input type="checkbox" name="KEEP_DISPLAY_VALUE"></label></div>'+
             '<div class="editable-address"><label><span>Enforce Edit Note</span><input type="checkbox" name="ENFORCE_EDIT_NOTE"></label></div>'+
             '<div class="editable-address"><label><span>Input color</span><input class="inputColor"  type="text" name="COLOR" maxlength="6" size="6" style="padding:2px;background: rgb(219, 68, 219);"></label><img class="removeColor" name="removeColor" valign="middle" class="xclose" src="/img/x.png"></div>',
        inputclass: ''
    });

    $.fn.editabletypes.address = Address;

}(window.jQuery));