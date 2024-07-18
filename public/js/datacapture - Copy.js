const DataCaptureScreen = {

    updateFixedLeftPanelSize: function(){
        $('[box-h]').each(function(){
            $(this).parent().find('[dt-left]').height($(this).height());
        });
    },
    
    theCellContextMenu: null,
    checkMenuClick: function(menu){
        const code = menu.attr('menu-code');
        //alert(code);
        DataCaptureScreen.theCellContextMenu && DataCaptureScreen.theCellContextMenu.popup('hide');
        switch(code){
            case 'sort-asc':
            case 'sort-des':
                    DataCaptureScreen.theCellContextMenu.attr('dt-sort', code == 'sort-asc' ? 'des' : 'asc');
                    DataCaptureScreen.theCellContextMenu.click();
                break;
            default:
                //alert(code);
        }
        //event.stopPropagation();
    },
    
    builDataGrid: function(dataTable){
        const containerSelector = ($('#tab-' + dataTable.name).length ? '#tab-' + dataTable.name : '[main-body]');
        function _$(x){
            return $(containerSelector + ' ' + x);
        }

        $(containerSelector).append('<div dt-header-container><div dt-fixed dt-header-cell><span content>fixed</span><span tooltiptext>count</span><div dt-nav><div dt-nav-left></div><div dt-nav-right disabled></div><div dt-nav-top></div><div dt-nav-bottom></div></div></div><div dt-header></div></div><div dt-container><div dt-left></div><div dt-body></div></div>');

        const leftFixedWidth = dataTable.leftFixedWidth;
        const objectTitle = dataTable.objectTitle;
        const THOUSAND_SEPARATOR = ',';

        let isFirstRow = true, y = 0;
        const colsCount = Object.keys(dataTable.columns).length;
        const rowsCount = Object.keys(objs).length;
        const tableGrid = $('<table table-grid height="'+(rowsCount*35)+'">');
        const tableLeft = $('<table table-left width="100%" height="'+(rowsCount*35)+'">');
        let headerHtml = "<td dt-header-cell='0' width='" + (leftFixedWidth - 1) + "'></td>";
        
        dataTable.data.forEach(rec => {
            const row = $('<tr dt-row><td dt-cell="0" width="' + (leftFixedWidth - 1) + '">' + rec[dataTable.name] + '</td></tr>');
            let x = 1;
            let isRowContainTextBlock = false;
            for (var cp in dataTable.columns){
                let col = dataTable.columns[cp];
                var cell = $('<td dt-cell="' + cp + '">');
                const colWidth = (col.width == undefined || col.width == 0 ? '120' : col.width);
        
                isFirstRow ? headerHtml += '<td dt-header-cell="' + x + '" dt-column="' + cp + '" dt-type="' + col.type + '" style="width: ' + colWidth + 'px">' + col.name + '</td>' : null;
                var tabindex = 100+x+y*colsCount;
                cell.css({width: colWidth});
                cell.attr({x: x, y: y, 'dt-type': col.type});
        
                !col.allowEdit && cell.attr('readonly', '');
                col.type != 'checkbox' && cell.attr('tabindex', tabindex);
        //col.formula = "hahaha";
                col.formula && cell.attr('formula', col.formula) && (col.allowEdit = false);
        
                cell.data('dt-data', {originValue: rec[cp], value: rec[cp]});
                renderCell(cell, rec[cp]);
                cell.focus(function(){
                    checkCellFocus($(this));
                });
                cell.mousedown(function(){
                    checkCellMouseDown($(this));
                });
        
                if(col.allowEdit) switch(col.type){
                    case 'check':
                        cell.html('<div class="ui checkbox"><input type="checkbox" onfocus="rowClick($(this).parent().parent().attr(\'y\'))" tabindex=' + tabindex + '></div>');
                        break;
                    case 'datetime':
                    case 'date':
                    case 'time':
                        cell.attr({'single-line': true});
                        cell.keydown(function(e) {
                            var code = e.keyCode || e.which;                
                            if (code === 9) {  
                                //e.preventDefault();
                                if($(this).data('dateRangePicker')) $(this).data('dateRangePicker').close();
                            }
                        });
                        break;
                    case 'list':
                        cell.attr({'dt-value': '', 'dt-text': '', 'dt-list': col.list});
                        col.listParent != undefined && cell.attr('dt-list-parent', col.listParent);
                        rec[cp] && (cell.data('dt-data').text = dataTable.lists[col.list][rec[cp]].name);
                        break;
                    case 'number':
                        rec[cp] && (cell.data('dt-data').value = cell.data('dt-data').originValue = Number(rec[cp]));
                        cell.keypress(Utils.checkNumeric).keyup(Utils.formatNumberInput);
                    case 'text':
                    case undefined:
                        cell.attr({'single-line': true});
                    case 'textblock':
                        //col.allowEdit && cell.attr({contenteditable: true});
                        cell.blur(function(){
                            validateInput($(this));
                            $(this).removeAttr('contenteditable');
                        });
                        !isRowContainTextBlock && (isRowContainTextBlock = (col.type == 'textblock'));
                        break;
                }
                /*
                cell.on('input', function(){
                    row = $(this).parent();
                    var h = row.height();
                    var leftRow = $('[table-left] tr[row="'+row.attr('row')+'"]');
                    if(leftRow.height()!=h)
                        leftRow.height(h);
                });
                cell.blur(function(){
                    console.log($(this).text());
                    if(isNaN($(this).text())){
                        $(this).attr('data-error', 'Not a number');
                    }
                    else
                        $(this).attr('data-error', '');
                    setCellBoundColor($(this));
                });
                */
                row.append(cell);
                x++;
            }
            var cell = $('<td dt-cell-fixed="' + y + '"><span>' + rec[dataTable.name] + '</span></td>');
            rec['PHASE_CODE'] && cell.attr('phase-code', rec['PHASE_CODE']);
            rec['STATUS_CODE'] && cell.attr('status-code', rec['STATUS_CODE']);
            rec['TYPE_CODE'] && cell.attr('type-code', rec['TYPE_CODE']);
        
            cell.css({position: 'relative'}).on('mousedown', function(){
                rowClick($(this).attr('dt-cell-fixed'));
            });
            const tbh = 50;
            isRowContainTextBlock && cell.height(tbh);
            tableLeft.append($('<tr>').append(cell));
            row.attr('row', y);
            if(isRowContainTextBlock){
                row.height(tbh);
                row.children().each(function(){
                    $(this).height(tbh);
                });
            }
            tableGrid.append(row);
        
            isFirstRow = false;
            y++;
        });
        tableLeft.append('<tr fake-left-row><td></td></tr>');
        
        _$('[dt-body]').append(tableGrid);
        _$('[dt-left]').append(tableLeft);
        _$('[dt-header]').html('<table><tr>'+headerHtml+'</tr></table>');
        _$('[dt-fixed] [tooltiptext]').html(y + ' rows');
        if(leftFixedWidth) {
            _$('[dt-fixed] [content]').html(objectTitle);
            _$('[dt-fixed]').css('width', leftFixedWidth);
            _$('[dt-left]').css('width', leftFixedWidth);
        }
        else {
            _$('[dt-fixed]').hide();
            _$('[dt-left]').hide();    
        }

        _$('[dt-container]').append($('<div box-h>').css({visibility: 'hidden', position: 'absolute', height: '100%'}));
        
        let focusCell, focusRowIndex;
        const   cellBorderColorNormal = '#d8d8d8',
                cellBorderColorFocus = '#127ab8',
                rowBackColorNormal = 'transparent',
                rowBackColorFocus = '#e8e8e8';
                
        function renderCell(cell, value){
            var content = value, l;
            var config = dataTable.columns[cell.attr('dt-cell')];
            if(config.type == 'number' && value){
                var format = config.format ? config.format.trim() : '',
                    l = format.indexOf('.'),
                    dec = (l >=0 ? format.length - l - 1 : 0);
                content = $.number(value, dec);
            }
            else if(config.type == 'check'){
                content = (value ? 'Yes' : 'No');
            }
            else if(config.type == 'list' && value){
                content = dataTable.lists[config.list][value].name;
            }
            cell.html(content);
        }
        
        function checkCellMouseDown(cell){
            var rightclick;
            var e = window.event;
            if (e.which) rightclick = (e.which == 3);
            else if (e.button) rightclick = (e.button == 2);
            !rightclick && cell.popup('destroy');
        }
        
        function checkCellFocus(cell){
            if(focusCell!=undefined){
                if(cell.attr('input-open')) return;
            }
            focusCell = cell;
            //cell.select();
            rowClick(cell.attr('y'));

            cell.attr('readonly') == undefined && activateCellInput(cell);
        }
        
        function showCellContextMenu(cell){
            if(cell.attr('dt-header-cell') != undefined){
                $('[menu-data-point]').hide();
                $('[menu-field]').show();
                $('[menu-object]').hide();
            }
            else if(cell.attr('dt-cell-fixed') != undefined){
                $('[menu-data-point]').hide();
                $('[menu-field]').hide();
                $('[menu-object]').show();
            }
            else if(cell.attr('dt-cell') != undefined){
                //$('[menu-field-config] .header [column-name]').html($('[dt-header-cell="'+cell.attr('x')+'"]').text());
                //$('[menu-object-management] .header [object-name]').html($('[dt-cell-fixed="'+cell.attr('y')+'"]').text());
                $('[menu-data-point]').show();
                $('[menu-field]').hide();
                $('[menu-object]').hide();
            }
            else
                return false;
            cell.popup({
                popup : $('.custom.popup'),
                on    : 'click',
                movePopup: false,
                position: 'bottom left',
                onShow: function(){
                    //DataCaptureScreen.theCellContextMenu && DataCaptureScreen.theCellContextMenu!=cell && DataCaptureScreen.theCellContextMenu.popup('destroy');
                    DataCaptureScreen.theCellContextMenu = cell;
                },
                onHidden: function(){
                    cell.popup('destroy');
                }
          }).popup('show');
          return true;
        }
        
        function activateCellInput(cell){

            const cellType = cell.attr('dt-type');
            switch(cellType){
                case 'number':
                    let value = cell.data('dt-data').value;
                    value && (value = Number(value));
                    value !== cell[0].innerText && cell.html(value);
                    break;
                    
                case 'check':
                    event.preventDefault();
                    var chk = cell.find('input').focus();
                    break;
        
                case 'textblock':
                    break;
        
                case 'datetime':
                case 'date':
                case 'time':
                    if(cell.data('dateRangePicker')){
                        cell.data('dateRangePicker').open();
                        return;
                    }
                    event.stopPropagation();
                    const timeEnable = (cellType == 'time' || cellType == 'datetime');
                    cell.dateRangePicker({
                        monthSelect: true,
                        yearSelect: function(current) {
                            return [current - 10, current + 10];
                        },
                        autoClose: !timeEnable,
                        showTopbar: timeEnable,
                        singleMonth: true,
                        singleDate : true,
                        timeOnly: (cellType == 'time'),
                        format: timeEnable?'YYYY-MM-DD HH:mm':'YYYY-MM-DD',
                        time: {
                              enabled: timeEnable,
                        },
                        container: 'body',
                        getValue: function() {
                            return $(this).data('dt-data').value;
                        },
                    }).bind('datepicker-closed',function() {
                        $(this).removeAttr('input-open');
                        if($(this).data('dateRangePicker')) $(this).data('dateRangePicker').destroy();
                    }).bind('datepicker-apply',function(event,obj) {
                        validateInput($(this), obj);
                    }).bind('datepicker-change',function(event,obj) {
                        if($(this).attr('dt-type') == 'date')
                            validateInput($(this), obj);
                    });
                    
                    cell.data('dateRangePicker').open();
                    cell.attr('input-open', true);
                    break;
        
                case 'list':
                    const dtList = cell.attr('dt-list'),
                        dtData = cell.data('dt-data');
                    let html = `<div class="ui selection dropdown" tabindex="` + cell.attr('tabindex') + `">
                    <input type="hidden" name="gender" value="` + dtData.value + `">
                    <i class="dropdown icon"></i>
                    <div class="default text">` + (dtData.text ? dtData.text : '') + `</div>
                    <div class="menu">`;
                    for (var v in dataTable.lists[dtList])
                        html += '<div class="item" data-value="' + v + '" data-text="' + dataTable.lists[dtList][v].name + '">' + dataTable.lists[dtList][v].name + '</div>';
                    html += '</div></div>';
                    cell.html(html);
                    cell.children().first().dropdown({
                        onChange: function(value, text, $selectedItem) {
                            validateInput(cell, {value: value, text: text});
                        },
                        onHide: function(){
                            setTimeout(() => {
                                cell.children().remove();
                                cell.html(cell.data('dt-data').text);
                                cell.removeAttr('input-open');
                            }, 0);
                        },
                    }).dropdown('show');
                    cell.attr('input-open', true);
                    event.preventDefault();
                    cell.children().first().focus();
                    break;
            }
            (cellType == 'number' || cellType == 'text' || cellType == 'textblock' || cellType == undefined) && cell.attr({contenteditable: true});
        }

        function validateInput(cell, data){
            const dtType = cell.attr('dt-type');
            const dtData = cell.data('dt-data');
            if(dtData == undefined) dtData = {};
            let newValue = false;
            let isValueAccepted = true;
            switch(dtType){
                case 'number':
                    newValue = cell[0].innerText.trim().replace(THOUSAND_SEPARATOR, '');
                    if(isNaN(newValue)){
                        if(!cell.attr('dt-error')){
                            cell.attr({'dt-error': true});
                            //cell.popup({content: 'The value must be a number', position : 'top right'});
                            cell.append("<span tooltiptext>The value must be a number</span>");
                        }
                        isValueAccepted = false;
                    }
                    else{
                        cell.removeAttr('dt-error');
                        cell.find('[tooltiptext]').remove();
                        renderCell(cell, newValue);
                        //cell.html(newValue);
                        //cell.popup('destroy');
                    }
                    break;
        
                case undefined:
                case 'text':
                case 'textblock':
                    newValue = cell.text();
                    cell.empty();
                    setTimeout(() => {cell.html(newValue)}, 0);
                    break;
        
                case 'date':
                case 'time':
                case 'datetime':
                    newValue = data.value;
                    cell.html(newValue);
                    break;
        
                case 'list':
                    newValue = data.value;
                    dtData.text = data.text;
                    break;
            }
            if(isValueAccepted && newValue && newValue != dtData.value){
                dtData.value = newValue;
                dtData.value != dtData.originValue ? cell.attr('dt-changed', '') : cell.removeAttr('dt-changed');
            }
        }

        function rowClick(rowIndex){
            if(focusRowIndex == rowIndex) return;
            if(focusRowIndex!=undefined){
                _$('[table-grid] tr[row="'+focusRowIndex+'"]').css('background', rowBackColorNormal);
                _$('[table-left] td[dt-cell-fixed="'+focusRowIndex+'"]').css('background', rowBackColorNormal);
            }
            focusRowIndex = rowIndex;
            _$('[table-grid] tr[row="'+focusRowIndex+'"]').css('background', rowBackColorFocus);
            _$('[table-left] td[dt-cell-fixed="'+focusRowIndex+'"]').css('background', rowBackColorFocus);
        }
        
        function checkDataGridScrolling(st, sl){
            const 
                h = _$('[dt-container]').height(),
                w = _$('[dt-container]').width(),
                h0 = _$('[table-grid]').height(),
                w0 = _$('[table-grid]').width();
            st == undefined && (st = _$('[dt-container]').scrollTop());
            sl == undefined && (sl = _$('[dt-container]').scrollLeft());
            Math.round(sl + w) >= w0 ? _$('[dt-nav-right]').attr('disabled','') : _$('[dt-nav-right]').removeAttr('disabled');
            sl <= 0 ? _$('[dt-nav-left]').attr('disabled','') : _$('[dt-nav-left]').removeAttr('disabled');
            Math.round(st + h) >= h0 ? _$('[dt-nav-bottom]').attr('disabled','') : _$('[dt-nav-bottom]').removeAttr('disabled');
            st <= 0 ? _$('[dt-nav-top]').attr('disabled','') : _$('[dt-nav-top]').removeAttr('disabled');
        }
        
        function setEventBindings(){
            $(window).resize(function(){
                DataCaptureScreen.updateFixedLeftPanelSize();
                checkDataGridScrolling();
            });
        
            _$('[dt-left]').on('wheel', function(e){
                e.shiftKey ? _$('[dt-container]')[0].scrollBy(e.originalEvent.deltaY / 3, 0) : _$('[dt-container]')[0].scrollBy(0, e.originalEvent.deltaY / 3);
            });
        
            $(window).mouseup(function(e){
                mousedown = false;
            });
        
            _$('[dt-nav]').click(() => {
                event.stopPropagation();
            });
            _$('[dt-nav-bottom]').click(() => {
                _$('[dt-container]').animate({scrollTop: _$('[table-grid]').height()}, 300);
            });
            _$('[dt-nav-top]').click(() => {
                _$('[dt-container]').animate({scrollTop: 0}, 300);
            });
            _$('[dt-nav-right]').click(() => {
                _$('[dt-container]').animate({scrollLeft: _$('[table-grid]').width()}, 300);
            });
            _$('[dt-nav-left]').click(() => {
                _$('[dt-container]').animate({scrollLeft: 0}, 300);
            });
        
            _$('[dt-header-cell]').click(function(){
                const colIndex = $(this).attr('dt-header-cell') || 0;
                const dtType = $(this).attr('dt-type');
                let direction = $(this).attr('dt-sort');
                direction = (direction == 'asc' ? 'des' : 'asc');
                _$('[dt-header-cell][dt-sort]').removeAttr('dt-sort');
                $(this).attr('dt-sort', direction);
                Utils.sortTable(_$('[table-grid]')[0], colIndex, dtType, direction, _$('[table-left]')[0]);
            });
        
            _$('td, [dt-fixed]').contextmenu(function(e){
                if(!e.ctrlKey && !$(this).attr('input-open')){            
                    showCellContextMenu($(this)) && e.preventDefault();
                }
            });
        
            var isScrolling;
            _$('[dt-container]').scroll(function(e){
                const 
                st = _$('[dt-container]').scrollTop(),
                sl = _$('[dt-container]').scrollLeft();
        
                _$('[dt-left]')[0].scrollTo(0, st);
                _$('[dt-header-container]')[0].scrollTo(sl, 0);
        
                clearTimeout( isScrolling );
                isScrolling = setTimeout(function() {
                checkDataGridScrolling(st, sl);
                }, 100);
            });
            
            _$('.ui.checkbox').checkbox();
        }
        
        setEventBindings();
        
        $(document).ready(function(){
            _$('[dt-header] td:last-child').width(2000);
            $(window).resize();
        });
    },

    buildDropdown(dropdownElement, list){
        let lastGroup, defaultName = '';
        const menu = $('<div class="menu">');
        list.items.forEach(item => {
            if(item.group && (item.group != lastGroup)){
                lastGroup != undefined && menu.append($('<div class="divider">'));
                menu.append($('<div class="header">').html(item.group));
                lastGroup = item.group;
            }
            const menuItem = $('<div class="item">').attr('data-value', item.value).html(item.name);
            list.defaultValue == item.value && (defaultName = item.name) && menuItem.addClass('active selected');
            menu.append(menuItem);
        });
        dropdownElement
            //.empty()
            .append('<div class="text">' + defaultName + '</div>')
            .append('<i class="dropdown icon">')
            .append(menu);
    },

    buildScreen: function(config){
        //setup sub header
        //screen caption
        $('[sub-header]').append($('<li caption>').html(config.title));

        //build facility selector
        const facs = $('<li select-facility class="ui dropdown">');
        this.buildDropdown(facs, config.facilities);
        $('[sub-header]').append(facs);

        //build date selector
        const dateSelector = $('<input id="filter-date">').val(config.date.value);
        var isTimeEnable = (config.date.value && config.date.value.length > 10 ? true : false);
        var isDateRange = (config.date.from ? true : false);
        isTimeEnable && dateSelector.attr('time', '');
        isDateRange && dateSelector.attr('range', '');
        $('[sub-header]').append($('<li filter-date>').append(dateSelector));
        dateSelector.dateRangePicker({
          monthSelect: true,
          yearSelect: function(current) {return [current - 10, current + 10]},
          autoClose: !isTimeEnable,
          showTopbar: isTimeEnable,
          singleMonth: !isDateRange,
          singleDate : !isDateRange,
          format: isTimeEnable ? 'YYYY-MM-DD HH:mm' : 'YYYY-MM-DD',
          time: {enabled: isTimeEnable},
        });

        //build filters
        config.filters && config.filters.forEach(item => {
            const dropdown = $('<div input class="ui dropdown">');
            this.buildDropdown(dropdown, item.list);
            const filterBox = $('<li head-filter horizontal_ gap>')
                .append($('<div title>').html(item.title))
                .append(dropdown);
            $('[sub-header]').append(filterBox);
        });

        //build buttons
        buttonsBox = $('<li buttons-box>');
        config.buttons && config.buttons.forEach(item => {
            buttonsBox.append($('<button button>').html(item.name).attr('code', item.code));
        });
        $('[sub-header]').append(buttonsBox);

        //build tabs
        if(config.tabs && config.tabs.length > 0){
            const ul = $('<ul>');
            const tabs = $('<div>').attr('id', 'dcTabs').append(ul);
            config.tabs.forEach(tab => {
                const tabId = 'tab-' + tab.dataTableName;
                ul.append($('<li>').append($('<a>').attr('href', '#' + tabId).html(tab.title)));
                tabs.append($('<div>').attr('id', tabId));
            });
            $('[main-body]').append(tabs);
            tabs.tabs({
                activate: function(event, ui) {
                    DataCaptureScreen.updateFixedLeftPanelSize();
                },
            });
        }

        //enable dropdowns
        $('.ui.dropdown').dropdown({
            allowCategorySelection: false,
            onChange: function(value, text, $selectedItem) {
                //alert(text);
            },
        });
        
        //build data grids
        config.dataTables.forEach(dataTable => {
            this.builDataGrid(dataTable);
        });

        //screen events binding
        $('[menu-code]').click(function(){
            DataCaptureScreen.checkMenuClick($(this));
        });
    },
}