function createMainMenu(menuConfig){
	const menu = $('<div class="ui compact menu">');
	menuConfig.forEach(_sub => {
		const sub = $('<a class="item">').html(_sub.title);
		const popup = $('<div class="ui popup bottom left transition hidden">');
		const grid = $('<div class="ui relaxed divided grid">');
		_sub.groups.forEach(_column => {
		const column = $('<div class="column">').append($('<h4 class="ui header">').html(_column.title));
		const list = $('<div class="ui link list">');
		_column.items.forEach(_item => {
			const item = $('<a class="item">').attr('code', _item.code).html(_item.title);
			list.append(item);
		});
		grid.append(column.append(list));
		});
		menu.append(sub).append(popup.append(grid));
	});
	$('[main-header]>[main-menu]').append(menu);
	$('[main-header]>[main-menu] .menu>.item')
	.popup({
		inline     : true,
		hoverable  : true,
		position   : 'bottom left',
		delay: {
			show: 100,
			hide: 300
		}
	});
}
  
const ebtoken = $('meta[name="_token"]').attr('content');
$.ajaxSetup({
	headers: {
		'X-XSRF-Token': ebtoken
	}
});

const EB = {
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

	menu: [ {
		name: 'GROUP 1',
		items: [ {
				name: 'GROUP 1-1',
				items: [ {
						name: 'Menu 1-1-1',
						code: 'dc/flow',
					}, {
						name: 'Menu 1-1-2',
						code: 'code2',
					},
				],
			}, {
				name: 'GROUP 1-2',
				items: [ {
						name: 'Menu 1-2-1',
						code: 'dc/flow',
					}, {
						name: 'Menu 1-2-2',
						code: 'code2',
					},
				],
			}, {
				name: 'Menu 1-3',
				code: 'dc/flow',
			}, {
				name: 'Menu 1-4',
				code: 'code2',
			},
		],
	}, {
		name: 'GROUP 2',
		items: [ {
				name: 'GROUP 2-1',
				items: [ {
						name: 'Menu 2-1-1',
						code: 'dc/flow',
					}, {
						name: 'Menu 2-1-2',
						code: 'code2',
					},
				],
			}, {
				name: 'GROUP 2-2',
				items: [ {
						name: 'Menu 2-2-1',
						code: 'dc/flow',
					}, {
						name: 'Menu 2-2-2',
						code: 'code2',
					},
				],
			}, {
				name: 'Menu 2-3',
				code: 'dc/flow',
			}, {
				name: 'Menu 2-4',
				code: 'code2',
			},
		],
	},
	],
	
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

function doLayout(){
	if(!EB.isLoggedIn)
		showLoginLayout();
	else
		showEBLayout();
}

function showLoginLayout(){
	$("#inp-login-password").keyup(function(e){ 
		var code = e.which; // recommended to use e.which, it's normalized across browsers
		if(code==13)e.preventDefault();
		if(code==32||code==13||code==188||code==186){
			login();
		} 
	});
	const bg = Math.floor(Math.random() * 8) + 1;
	//bg=111;
	$('.box-login-container').css('background-image', 'url(/graphic/bg/'+bg+'.png)');
	$('.box-login-container').show();
	$('#inp-login-username').focus();
}

function showEBLayout(data){
    $('.box-main-container').show();
    
    //link actions
    $('[action="user"]').click(function(){

    });
}

function showSideBoxUser(){

}

function onRequestError(data){
	alert("error");
	console.log(data);
}
function sendRequest(path, data, onComplete, onError){
	$.ajax({
		url: path,
		type: "post",
		data: data,
		success: function(data){
			if(typeof onComplete === "function")
				onComplete(data);
		},
		error: function(data){
			if(typeof onError === "function") 
				onError(data)
			else
				onRequestError(data);
		}
	});
}
function processLoginData(data){
	const isLoginSuccess = data.status;
	if(isLoginSuccess){
		const menu = data.menu;
		const dashboard = data.dashboard; //dashboard data
		const user = data.user; // Account info, chat & notification, 
		const env = data.env; //config for working environment: lang, date number format, timezone, theme ...
		//build layout
		location.reload();
	}
	else{
		alert('Can not login.\n'+data.message);
	}
}
function login(){
	sendRequest('/auth/eblogin', {
		username: $("#inp-login-username").val(),
		password: $("#inp-login-password").val(),
		type: 'basic',
	}, processReceivedData);
}
function processReceivedData(data){
	const action = data.action;
	if(action === "login"){
		processLoginData(data);
	}
}

function forgotPassword(){
	$(".box-login").attr('forgot', '1');
	$("#inp-login-email").focus();
}

function cancelForgotPassword(){
	$(".box-login").attr('forgot', '');
	$("#inp-login-username").focus();
}

//$(document).ready(function(){
//});
doLayout();

//loadCSSJS("/js/three.js", "js", {});