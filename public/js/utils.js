const showWaiting = function(message){
    return;
}

const hideWaiting = function(){
    return;
}

const cachedAjaxData = {}

const sessionEnded = function(){
    window.sessionEnded = true;
    !$('#session-ended-alert').length && $('body').append('<div id="session-ended-alert"><div><p>Your session was ended. Please login again.</p><button onclick="location.reload()">OK</button></div></div>');
}

const sendAjax = function(url, param, funcSuccess, funcError, funcComplete, waitingMessage){
    if(window.sessionEnded) return;
    var cacheKey;
    if(param.cache === true){
        cacheKey = url.replace(/\//g, '_') + JSON.stringify(param);
        if(cachedAjaxData[cacheKey] != undefined){
            if(typeof(funcSuccess) == "function") 
                funcSuccess(cachedAjaxData[cacheKey]);
            return;
        }
    }
    $('[action="user"]')[0].className = 'eb ui loader';
    let ajaxType = 'post';
    param && param.ajaxType && (ajaxType = param.ajaxType);
    return $.ajax({
            beforeSend: function(){
            if(waitingMessage == undefined || waitingMessage !== false)
                showWaiting(waitingMessage);
            },
        url: url,
        type: ajaxType,
        data: param,
        success: function(data){
            $('[header] [action="user"]').removeAttr('error');
            if(param.cache === true){
                cachedAjaxData[cacheKey] = data;
            }
            if(typeof(funcSuccess) == "function") 
                funcSuccess(data);
        },
        error: function(data){
            if(data && data.status == 401){
                sessionEnded();
            }
            else if(typeof(funcError) == "function") 
                funcError(data);
            else{
                $('[header] [action="user"]').attr('error', '');
                console.log(data);
            }
        },
        complete: function(data){
            $('[action="user"]')[0].className = '';
            if(waitingMessage == undefined || waitingMessage !== false)
                hideWaiting(); 
            if(typeof(funcComplete) == "function") 
                funcComplete(data);
        }
    });    
}

const Utils = {
	
	setCookie: function(cname, cvalue, path) {
		var d = new Date();
		d.setTime(d.getTime() + 31536000000); //365 days in ms
		var expires = "expires="+d.toUTCString();
		!path && (path = '/');
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=" + path;
	},
		
	getCookie: function(cname) {
		var name = cname + "=";
		var ca = document.cookie.split(';');
		for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
			c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
			}
		}
		return "";
	},

    loadCSSJS: function(e,t,o){"use strict";var f="",s;if(f.indexOf("["+e+"]")==-1){f+="["+e+"]";switch(t){case "css":s=document.createElement("link");s.rel=(o.rel||"stylesheet");s.href=e;s.media="only foo";setTimeout(function(){s.media=(o.media||"all");});break;case "js":s=document.createElement("script");if(o.defer)s.defer="defer";else s.async="async";s.src=e;break;}}if("undefined"!=typeof s){var a=document.getElementsByTagName("script")[0];window.setTimeout(function(){if(t=="css")document.getElementsByTagName("head")[0].appendChild(s);else a.parentNode.insertBefore(s,a);},35);window.clearTimeout(a);}else{alert("File: \""+e+"\" already loaded (keep only one)!");}},

	genColorByText: function(text){
		const str = text.trim().substr(0, 2).toUpperCase();
		let r = str.length > 0 ? 128 + (64 - Math.round((str.charCodeAt(0) - 48) / 42 * 128)) : 128;
		let g = str.length > 1 ? 128 + (64 - Math.round((str.charCodeAt(1) - 48) / 42 * 128)) : 128;
		r > 192 && (r = 192);
		g > 192 && (g = 192);
		r < 64 && (r = 64);
		g < 64 && (g = 64);
		const b = 64 + 256 - r - g;
		return 'rgb(' + r + ', ' + g + ', ' + b + ')';
	},

    genIconText: function(text){
        let iconText = '';
        //const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', charactersLength = characters.length;
        //item.title = characters.charAt(Math.floor(Math.random() * charactersLength)) + ' ' + characters.charAt(Math.floor(Math.random() * charactersLength));
        text.split(' ').forEach(w => {
            const ch = w.substring(0,1).toUpperCase();
            iconText.length < 2 && ch >= 'A' && ch <= 'Z' && (iconText += ch);
        });
        return iconText;
    },

    formatNumberInput: function(e) {
        //Check if arrow keys are pressed - we want to allow navigation around textbox using arrow keys
        if (event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40) {
            return;
        }
    },

    checkNumeric: function() {
        return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode == 46;
    },

    sortTable: function(table, n, type, direction, linkTable){
        console.time('sortTable');
        var countSteps = 0, countMoveRow = 0;
        var getCellValue = function(cell){
            const cellData = $(cell).data('dt-data');
            const cellValue = (cellData ? (type == 'list' ? cellData.text : cellData.value) : cell.textContent);
            return (type == 'number' ? (cellValue || cellValue === 0 ? Number(cellValue) : undefined) : cellValue);
        };
        table.style.visibility = "hidden";
        linkTable.style.visibility = "hidden";

        var rows = table.rows, l = rows.length, k = l - 1, i = 0, j, x, y;
        while(i <= k){
            x = getCellValue(rows[i].cells[n]);
            if(x == undefined){
                rows[l - 1].after(rows[i]);
                if(linkTable) linkTable.rows[l - 1].after(linkTable.rows[i]);
                k--;
                countMoveRow++;
            }
            else{
                if(i > 0){
                    for(j = 0; j < i; j++){
                        countSteps++;
                        y = getCellValue(rows[j].cells[n]);
                        if((direction == 'asc' && (y > x)) || (direction == 'des' && (y < x))){
                            rows[j].before(rows[i]);
                            if(linkTable) linkTable.rows[j].before(linkTable.rows[i]);
                            countMoveRow++;
                            break;
                        }
                    }
                }
                i++;
            }
        }

        /*
        var rows = table.rows, row, i, j, k, x, y;
        var frag = document.createDocumentFragment();
    
        for(i = 0, l = rows.length; i < l; i++){
            row = rows[0];
            x = getCellValue(row.cells[n]);
            var index = -1;
            for(j=0, k = frag.children.length; j < k; j++){
                countSteps++;
                y = getCellValue(frag.children[j].cells[n]);
                if((direction == 'asc' && y > x) || (direction == 'des' && y < x)){
                    index = j;
                    break;
                }
            }
            index >= 0 ? frag.children[index].before(row) : frag.appendChild(row);
            countMoveRow++;
        }
        table.tBodies[0].appendChild(frag);
*/
        table.style.visibility = "visible";
        linkTable.style.visibility = "visible";
        console.timeEnd('sortTable');
        console.log(countSteps, countMoveRow);
        return direction;
    },
}

const DataAdapter = {
    InputTypes: ['', 'text', 'number', 'date', 'datetime', 'check', 'time', 'scheduler', 'textblock'],
    convertFromLoadedData: function(data){
        const dataTables = [];
        let dataTable;
        let config = data.properties;
        for (var p in data.configProperties) {
            config = data.configProperties[p];
            break;
        }
        if(config.length > 0){
            config.forEach(function(el, i) {
                if(i == 0){
                    dataTable = {name: el.data, columns: {}, data: [], keyColumns: [], objectTitle: el.title, leftFixedWidth: el.width};
                    dataTables.push(dataTable);
                    return;
                }
                dataTable.columns[el.COLUMN_NAME] = {
                    name: el.title || el.COLUMN_NAME,
                    type: DataAdapter.InputTypes[el.INPUT_TYPE],
                    format: el.VALUE_FORMAT,
                    max: el.VALUE_MAX,
                    min: el.VALUE_MIN,
                    allowEdit: el.DATA_METHOD == 1,
                    allowEmpty: el.IS_MANDATORY == 0,
                    width: el.width,
                };
                el.color && (dataTable.columns[el.COLUMN_NAME].color = el.color); //'blue #ff6600 gray', //text color, background, border
                if(el.OBJECT_EXTENSION){
                    const ex = {};
                    const tmp = JSON.parse(el.OBJECT_EXTENSION);
                    if (tmp.version == 2){
                        for (var t in tmp) {
                            for (var p in tmp[t]) {
                                const e = tmp[t][p];
                                if(e.OVERWRITE == 'true'){
                                    ex[p] = {
                                        format: e.basic.VALUE_FORMAT,
                                        max: e.basic.VALUE_MAX,
                                        min: e.basic.VALUE_MIN,
                                        allowEdit: e.basic.DATA_METHOD == 1,
                                        allowEmpty: e.basic.IS_MANDATORY == 0,
                                        color: e.advance.color,
                                    };
                                }
                            }
                            break;
                        }
                    }
                    dataTable.columns[el.COLUMN_NAME].exceptions = ex;
                }
            });
        }

        dataTable.lists = {};
        data.uoms && data.uoms.forEach(function(el) {
            dataTable.lists[el.id] = {};
            el.data.forEach(function(item){
                dataTable.lists[el.id][item.ID] = {name: item.NAME};
            });
            dataTable.columns[el.COLUMN_NAME] && 
            (dataTable.columns[el.COLUMN_NAME].type = 'list') && 
            (dataTable.columns[el.COLUMN_NAME].list = el.id);
        });

        dataTable.data = data.dataSet;

        return dataTables;
    },
    convertToPostData: function(data){
        return;
    },
}