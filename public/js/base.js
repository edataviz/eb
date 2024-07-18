const EB = {

	getDateRange: function(date, range, unit, returnDate){
		var endDate = moment(date);
		var beginDate = endDate.clone();
		if(range > 0){
			switch(unit){
				case 'minute':
					range = range / 1440;
					break;
				case 'hour':
					range = range / 24;
					break;
				case 'week':
					range = endDate.diff(beginDate.subtract(range - 1, 'weeks').startOf('week'), 'days') + 1;
					break;
					case 'month':
					range = endDate.diff(beginDate.subtract(range - 1, 'months').startOf('month'), 'days') + 1;
					break;
				case 'year':
					range = endDate.diff(beginDate.subtract(range - 1, 'years').startOf('year'), 'days') + 1;
					break;
			}
		}
		else
			range = 1;
		return returnDate ? endDate.subtract(range < 1 ? 0 : range - 1, 'days').format('YYYY-MM-DD') : range;
	},
	
	getStandardDate: function(dateString){
        return moment(dateString, EB.workspace.DATE_FORMAT).format('YYYY-MM-DD');
    },

	showSideBox: function(float, width){
		width == undefined && (width = '300px');
		if($('[aside]').css('margin-right') == '0px' && $('[aside]').css('width') == width) return;
		$('[aside]>[action]').css('visibility', 'hidden');
		width && $('[aside]').css({width: width}); //, 'margin-right': '-' + width
		$('[aside]').css('margin-right', 0);
		float = (float == undefined || float === true || float == 'float');
		$('#main-container').css('grid-template-columns', float ? '' : 'auto ' + width);// : $('#main-container').css('grid-template-columns', );
		//setTimeout(()=>{width && $('#main-container').css('grid-template-columns', 'auto ' + width);}, 500);
		setTimeout(()=>{$('[aside]>[action]').css('visibility', '')}, 400);
	},

	hideSideBox: () => {
		const width = $('[aside]').outerWidth() + 'px';
		$('[aside]').attr('float', '').css({width: width, 'margin-right': '-' + width});
		$('#main-container').css('grid-template-columns', '');
		$('#video-intro')[0] && $('#video-intro')[0].pause();
	},

	loadHelp: function(){
		if(!window.helpLoaded){
			if(EB.screenCode){
				$("#boxHelp").html('<div class="ui active loader">');
				sendAjax("/help/"+EB.screenCode, {ajaxType: 'get'}, function(data){
					if(typeof data == 'undefined'
							|| data==""
							||jQuery.isEmptyObject(data) 
							|| data.length<=0
							|| typeof data.HELP == 'undefined'
							|| data.HELP == ""
							) 
						data = {HELP: "No data."};
					$("#boxHelp").html(data['HELP']);
					let $inds = $('#boxHelp indexes');
					let lastTag = '';
					if($inds.length){
						let indsHtml = '<ul>';
						let id = 0;
						$('#boxHelp h3, #boxHelp h4').each(function(){
							id++;
							$(this).attr('id', 'bm' + id);
							let tag = $(this).prop("tagName");
							if(tag != lastTag && lastTag){
								tag == 'H4' && (indsHtml += '<ul>');
								tag == 'H3' && (indsHtml += '</ul>');
							}
							lastTag = tag;
							indsHtml += '<li><a href="#bm' + id + '">' + $(this).html() + '</a></li>';
							let h4 = '';
							h4 !='' && (indsHtml += '<ul>' + h4 + '</ul>');
						});
						$inds.html(indsHtml + '</ul>');
					}
				});
			}
			else $("#boxHelp").html("No data");
			window.helpLoaded = true;
		}
	},

	doHeaderAction: function(action){
		if(action == 'home'){
			location.href = '/';
		}
		else if(action == 'workflow'){
		}
		else {
			$('[aside]').attr('action', action).css({display: 'unset'});
			const box = $('[aside]>[action="' + action + '"]');
			$('[aside]>.title').html(box.attr('side-title'));
			EB.showSideBox(true, box.attr('width'));
			action == 'help' ?	EB.loadHelp() :	$('#video-intro')[0] && $('#video-intro')[0].pause();
		}
	},

    buildDropdown(dropdownElement, dropdownConfig){
        let lastGroup, selectedItem, values = [];
		const menu = $('<div class="menu">');
		$(dropdownElement).addClass('ui dropdown' + (dropdownConfig.class ? ' ' + dropdownConfig.class : ''));

		!dropdownConfig.isClearBox && $(dropdownElement).addClass('selection');
        dropdownConfig.list.items.forEach(item => {
            if(item.group && (item.group != lastGroup)){
                lastGroup != undefined && menu.append($('<div class="divider">'));
                menu.append($('<div class="header">').html(item.group));
                lastGroup = item.group;
            }
			const menuItem = $('<div class="item">').html(item.name);
			for(const attr in item){
				if(attr != 'name')
					menuItem.attr('data-' + attr, item[attr]);
			}
			//values.push({name: item.name, value: item.value, selected: dropdownConfig.list.defaultValue == item.value});
            (dropdownConfig.list.defaultValue == item.value || item.selected) && (selectedItem = item) && menuItem.addClass('active selected');
            menu.append(menuItem);
		});
		//if(dropdownConfig.class && dropdownConfig.class.includes('selection')){
			$input = $('<input type="hidden">');
			dropdownConfig.inputName && $input.attr('name', dropdownConfig.inputName);
			dropdownConfig.id && $input.attr('id', dropdownConfig.id);
			selectedItem && $input.val(selectedItem.value);
			//$(dropdownElement).append($input);
		//}
		//!dropdownConfig.defaultText && (dropdownConfig.defaultText = '');
		$text = $('<div class="text">');
		selectedItem ? $text.html(selectedItem.name) : $text.addClass('default').html(dropdownConfig.defaultText);
        $(dropdownElement)
			//.empty()
			.append($input)
            .append($text)
            .append('<i class="dropdown icon">')
            .append(menu);
		//enable dropdowns
		const dropdownOptions = Object.assign({
			//values: values,
            allowCategorySelection: false,
            onChange: function(value, text, $selectedItem) {
                if(typeof(dropdownConfig.onChange) == "function"){
					dropdownConfig.onChange(value, text, $selectedItem);
				}
            },
        }, dropdownConfig.options);
        $(dropdownElement).dropdown(dropdownOptions);
    },

	buildItem: function(type, options, notAppend){
		let $item;
		if(type == 'facility'){
			$item = $('<div select-facility>');
			options.isClearBox = true;
			EB.buildDropdown($item, options);
		}
		else if(type == 'date'){
			const $input = $('<input id="filter-date" date-input>').val(
				options.value ? 
					moment(options.value).format(EB.workspace.DATE_FORMAT) : 
					moment().add(-1, 'days').format(EB.workspace.DATE_FORMAT)
			);
			var isTimeEnable = (options.value && options.value.length > 10 ? true : false);
			var isDateRange = (options.from ? true : false);
			isTimeEnable && $input.attr('time', '');
			isDateRange && $input.attr('range', '');
			$item = $('<div filter-date>').append($input);
			options.class && $item.addClass(options.class);
			options.attr && $item.attr(options.attr);
			options.css && $item.css(options.css);
			$input.dateRangePicker({
				monthSelect: true,
				yearSelect: function(current) {return [current - 10, current + 10]},
				autoClose: !isTimeEnable,
				showTopbar: isTimeEnable,
				singleMonth: !isDateRange,
				singleDate : !isDateRange,
				format: EB.workspace.DATE_FORMAT + (isTimeEnable ? ' ' + EB.workspace.TIME_FORMAT : ''),
				time: {enabled: isTimeEnable},
			});	
		}
		else if(type == 'filter'){
			const dropdown = $('<div input>');
			options.isClearBox = true;
			EB.buildDropdown(dropdown, options);
			!options.title && (options.layout = 'horizontal');
			$item = $('<div head-filter gap>');
			options.layout && $item.attr(options.layout, '');
			options.id && !$item.attr('id') && $item.attr('id', 'filter-' + options.id);
			options.title && $item.append($('<div title>').html(options.title));
			$item.append(dropdown);
		}
		else if(type == 'button'){
			$item = $('<button>').html(options.content);
			options.class && $item.addClass(options.class);
			options.tooltip && $item.attr('title', options.tooltip);
			options.css && $item.css(options.css);
			options.click && $item.click(options.click);
		}
		else if(type == 'html'){
			$item = $(options.html);
			options.actions && setTimeout(()=>{
				options.actions.forEach(act => {
					$(act.selector).on(act.event, act.action)
				})
			}, 0);
		}
		!notAppend && $item && $('[toolbar]').append($('<li>').append($item)) && type == 'html' && options.id && $item.parent().attr('id', options.id);
		return $item;
	},
	
	buildScreen: function(config){

		//fav star
		/*
		EB.favState !== undefined && $('[toolbar]').append($('<li fav="' + (EB.favState ? 'on' : '') + '">').click(function(){
			EB.favState = !EB.favState;
			$(this).attr('fav', EB.favState ? 'on' : '');
			let fav = ',' + EB.workspace.FAV + ',';
			let path = ',' + window.location.pathname.substr(1) + ',';
			EB.favState ? fav.indexOf(path) < 0 && (fav += path.substr(1)) : fav.indexOf(path) >= 0 && (fav = fav.replace(path, ','));
			sendAjax('/save-fav', {fav: fav.substr(1, fav.length - 2)});
		}));
		*/

		//screen caption
		$('[toolbar]').append($('<li caption>').html(config.title));
		
		for(const a in config){
			switch(a){
				case 'facility': config.facility && EB.buildItem('facility', config.facility); break;
				case 'date': config.date && EB.buildItem('date', config.date); break;
				case 'filters': config.filters && config.filters.forEach(item => {EB.buildItem('filter', item);}); break;
				case 'buttons': 
					if(config.buttons){
						buttonsBox = $('<li buttons-box>');
						config.buttons.forEach(item => {
							buttonsBox.append(EB.buildItem(item.html ? 'html' : 'button', item, true));
						});
						$('[toolbar]').append(buttonsBox);
					}
					break;
				case 'tabs':
					if(config.tabs && config.tabs.length > 0){
						const ul = $('<div class="ui pointing secondary menu">');
						const tabs = $('<div id="dcTabs">').append(ul);
						config.tabs.forEach(item => {
							const tabId = 'tab-' + item.dataTableName;
							ul.append($('<a class="item' + (item.selected ? ' active' : '') + '">').attr('data-tab', tabId).html(item.title));
							tabs.append($('<div class="ui tab segment' + (item.selected ? ' active' : '') + '">').attr('data-tab', tabId).attr('id', tabId));
						});
						$('[main]').append(tabs);
						$('#dcTabs .item').tab({
							onLoad: function(tabPath) {
								DataCaptureScreen.updateFixedLeftPanelSize();
							},
						});
					}
					break;
				case 'dataTables': 
					config.dataTables && config.dataTables.forEach(dataTable => {
						DataCaptureScreen.builDataGrid(dataTable);
					});
					break;
				case 'items':
					config.items && config.items.forEach(item => {
						EB.buildItem(item.type, item);
					});
					break;
			}
		}
		
		//favourite star
		//if(EB.favState !== undefined){
        //    $('[toolbar]').append($('<div fav-state="' + EB.favState + '">'));
		//}
	},
	
	doMenuClick: function(code, title){
		if(window.isEditingFav){
			EB.buildFavMenuItem(code, title);
			return;
		}
		location.href = '/' + code;
	},

	buildMainMenu: function(){
		const menu = $('<div class="ui compact menu">');
		EB.favMenu = {};
		EB.menu.forEach(_sub => {
			const sub = $('<a class="item">').html(_sub.title);
			const popup = $('<div class="ui popup bottom left transition hidden">');
			const grid = $('<div class="ui relaxed divided grid">');
			_sub.groups.forEach(_column => {
				const column = $('<div class="column">').append($('<h4 class="ui header">').html(_column.title));
				const list = $('<div class="ui link list">');
				_column.items.forEach(_item => {
					const item = $('<a class="item">').attr('code', _item.code).html(_item.title);
					list.append(item);
					item.click(function(){
						EB.doMenuClick($(this).attr('code'), $(this).html());
					});					
					EB.workspace.FAV && EB.workspace.FAV.includes(_item.code) && (EB.favMenu[_item.code] = _item.title);
				});
				grid.append(column.append(list));
			});
			menu.append(sub).append(popup.append(grid));
		});
		$('[header]>[main-menu]').append(menu);
		$('[header]>[main-menu] .menu>.item')
		.popup({
			inline     : true,
			hoverable  : true,
			position   : 'bottom left',
			delay: {
				show: 100,
				hide: 300
			}
		});
	},

	buildFavMenuItem: function(code, title){
		let iconText = Utils.genIconText(title);
		const icon = $('<div fav-icon>').html(iconText).css('background', Utils.genColorByText(iconText));
		const favItem = $('<div fav-item>')
			.append(icon)
			.append($('<div fav-caption>').html(title))
			.append($('<span remove-item>').attr('title', 'Remove this item').click((e)=>{
				e.stopPropagation();
				favItem.remove();
			}))
			.attr('code', code);
		favItem.click(function(){
			$('[fav-menu]').attr('arrange') == undefined && EB.doMenuClick($(this).attr('code'));
		});
		$('[fav-menu]').append(favItem);
	},

	buildFavMenu: function(){
		if(!EB.workspace.FAV) return;
		let ss = EB.workspace.FAV.split(',');
		ss.forEach(code => {
			EB.buildFavMenuItem(code, EB.favMenu[code]);
		})
	},

	buildNotificationBox: function(){
		this.notification.items.forEach(item => {
			const noti = $('<div noti>')
				.append($('<div time>').html(item.time))
				.append(item.content)
				.append($('<span remove-item>').attr('title', 'Remove this notification'))
				.append($('<span remove-back>').attr('title', 'Remove backward'))
			;
			$('[aside] [action="notification"]').append(noti);
		})
	},

	user: {},

	socketID: null,
	
	currentMenuItem: 'home', //dc/flow, network, graph ...
	
	workspace: {
		locale: 'us',
		theme: 'default',
		timeZone: 'default',
		isAutoSaveOnChange: true,
		lastFacilityID: null,
		lastDate: '',
		lastDateTo: '',
		lastFilters: {
			'CodeFlowPhase': 1,
			'CodeEventType': 2,
			'filterName1': 'value1',
			'filterName2': 'value2',
		},
	},

	//fav: [],

	menu: [],
	
	dashboard: {
		'dashboardID1': {
			name: 'Dashboard title',
			items: [ {
					type: 'CHART', //NETWORK, REPORT, DATA_VIEW, TEXT
					style: '<css: color, font-size, border, padding ...>',
					content: {
						title: 'Chart 1',
						subTitle: '',
						legend: false,
						data: [],
						xAxis: [],
						yAxis: [],
						timeSpan: 30, //days
						updateInterval: 0, //no update
					}
				}, {
					type: 'TEXT',
					style: '<css: color, font-size, border ...>',
					content: 'This is a text',
				}, {
					type: 'NETWORK',
					style: '<css: color, font-size, border ...>',
					content: {
						source: '<xml/>',
						data: [],
						updateInterval: 60, //seconds
					}
				}, {
					type: 'REPORT',
					style: '<css: color, font-size, border ...>',
					content: {
						reportFile: 'jrxml_name',
						param: {
							'paramName1': {
								type: 'n', //n: number, s: string, d: date, y: year
								value: 1,
							},
							'paramName2': {
								type: 'd', //n: number, s: string, d: date, y: year
								value: '2020-01-01',
							},
						},
						updateInterval: 300,
					},
				}, {
					type: 'DATA_VIEW',
					style: '<css: color, font-size, border ...>',
					content: {
						source: 'TableOrViewName',
						columnMap: {
							'COLUMN_1': 'COLUMN TITLE 1',
							'COLUMN_2': 'COLUMN TITLE 2',
						},
						data: [
							['ROW_1_COLUMN_1_VALUE', 'ROW_1_COLUMN_2_VALUE', ],
							['ROW_2_COLUMN_1_VALUE', 'ROW_2_COLUMN_2_VALUE', ],
						],
						timeSpan: 30, //days
						updateInterval: 60, //seconds
						tableStyle: '<css>',
					},
				},
			],
		},
	},
	
	workFlowTask: { // active workflow tasks that are related to me
		'workflowTaskID1': {
			workflowID: 1,
			workflowName: 'Workflow Name 1',
			taskName: 'Task 1-1',
			taskType: 'REPORT',
		},
		'workflowTaskID2': {
			workflowID: 1,
			workflowName: 'Workflow Name 1',
			taskName: 'Task 1-2',
			taskType: 'IMP_DATA',
		},
		'workflowTaskID3': {
			workflowID: 2,
			workflowName: 'Workflow Name 2',
			taskName: 'Task 2-1',
			taskType: 'EDIT_DATA',
		},
	},
	
	task: { // Monitor long-running task in the system
		'task1': {
			name: 'Allocation job 1',
			username: 'the-one-who-started-this-task', //empty: run by system scheduler
			timeStart: '2020-01-01 01:01:01',
			timeFinish: null,
			percentCompleted: 25.5,
			lastErrorMessage: '',
			log: [ {
					time: '2020-01-10 01:01:01',
					content: 'Task log content 1',
				}, {
					time: '2020-01-10 01:01:02',
					content: 'Task log content 2',
				},
			],
		},
		'task2': {
			//...
		},
	},
	
	notification: {
		unreadCount: 1,
		items: [ {
				time: '2020-01-10 01:01:01',
				content: 'Notification 1',
				isRead: true,
			}, {
				time: '2020-01-10 01:01:02',
				content: 'Notification 2',
				isRead: true,
			}, {
				time: '2020-01-10 01:01:03',
				content: 'Notification 3',
				isRead: false,
			},
		],
	},
	chat: {
		enable: true,
		friendList: {
			'username1': {
				name: 'Full Name 1',
				isOnline: true,
				lastSeen: '2020-01-10 11:22:33',
				unreadCount: 1,
				messages: [ {
						time: '2020-01-10 01:01:01',
						content: 'Message 1-1',
						isRecieved: true,
					}, {
						time: '2020-01-10 01:01:02',
						content: 'Message 1-2',
						isRecieved: false,
					},
				],
			},
			'username2': {
				name: 'Full Name 2',
				isOnline: false,
				lastSeen: '2020-01-10 11:22:33',
				unreadCount: 2,
				messages: [ {
						time: '2020-01-10 01:01:01',
						content: 'Message 2-1-1',
						isRecieved: true,
					}, {
						time: '2020-01-10 01:01:02',
						content: 'Message 2-1-2',
						isRecieved: true,
					}, {
						time: '2020-01-10 01:01:03',
						content: 'Message 2-2-0',
						isRecieved: false,
					},
				],
			},			
		},
	},
}

jQuery.fn.extend({
	date: function() {
		return moment($(this).val(), EB.workspace.DATE_FORMAT).toDate()
	},
	dateString: function() {
		return EB.getStandardDate($(this).val())
	}
});

$(document).ready(function(){
	$('[header] [action]').click(function(){
		EB.doHeaderAction($(this).attr('action'));
	});
	
	$('[header] [eb]').click(function(){
		location.pathname != '/' && (location.href = '/');
	});

	typeof Utils !== 'undefined' && $('input[type="number"]').keypress(Utils.checkNumeric);

	$('close').click(()=>{
		EB.hideSideBox();
	});
});
