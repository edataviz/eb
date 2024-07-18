<?php
Route::get('/', [
		'uses' => 'HomeController@index',
]);
Route::get('language/{lang}', 'HomeController@language')->where('lang', '[A-Za-z_-]+');

//new EB
Route::get('pm-flow',		['uses' =>'ViewController@flow',		'middleware' => 'checkRight:FDC_FLOW']);
Route::get('dv-dashboard',	['uses' =>'ViewController@dashboard',	'middleware' => 'checkRight:VIS_DASHBOARD']);
Route::get('cf-dashboard',  ['uses' =>'ViewController@configDashboard',	'middleware' => 'checkRight:CF_DASHBOARD_CONFIG']);
Route::get('dv-diagram',	['uses' =>'ViewController@configDiagram',		'middleware' => 'checkRight:VIS_NETWORK_MODEL']);
Route::get('dv-graph',	['uses' =>'ViewController@graph',	'middleware' => 'checkRight:VIS_ADVGRAPH']);

Route::post('eb-get-code', ['uses' =>'ViewController@getCodesChanged']);
Route::post('get-model-data', ['uses' =>'ViewController@getModelData']);
Route::post('load-graph-data', ['uses' =>'ViewController@loadGraphData']);
Route::post('load-chart-list', ['uses' =>'ViewController@loadChartsList']);
Route::post('save-fav', ['uses' =>'ViewController@saveFav']);

Route::post('cargodocument_loaddetail', 'Cargo\CargoDocumentsController@loadDetail');
Route::post('cargodocument_savedetail', 'Cargo\CargoDocumentsController@saveDetail');
Route::post('cargodocument_applyreportset', 'Cargo\CargoDocumentsController@applyReportSet');

Route::get('pd-cargomeasurement', ['uses' =>'ProductDeliveryController@cargomeasurement',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('cargomeasurement/load',		['uses' =>'Cargo\CargoMeasurementController@load',			'middleware' => 'saveWorkspace']);
Route::post('cargomeasurement/save', 		['uses' =>'Cargo\CargoMeasurementController@save',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('cargomeasurement_loaddetail', 'Cargo\CargoMeasurementController@loadDetailNew');
Route::post('cargomeasurement_loadobjects', 'Cargo\CargoMeasurementController@loadObjects');

Route::post('cargomeasurement_savedetail', 'Cargo\CargoMeasurementController@saveDetail');
Route::post('cargomeasurement_addallobjects', 'Cargo\CargoMeasurementController@addAllObjects');
Route::post('cargomeasurement/loaddetail',	['uses' =>'Cargo\CargoMeasurementController@load', 'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('cargomeasurement/savedetail',	['uses' =>'Cargo\CargoMeasurementController@save', 'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('cargomeasurement/gen-vef',	['uses' =>'Cargo\CargoMeasurementController@genVEF', 'middleware' => 'checkRight:PD_CARGO_ACTION']);

Route::get('pd-vef',			['uses' =>'ProductDeliveryController@vef'	,'middleware' => 'checkRight:PD_CARGO_ADMIN']);
Route::post('vef-load',			['uses' =>'Cargo\VefController@load'		,'middleware' => 'saveWorkspace']);
Route::post('vef-save', 		'Cargo\VefController@save');
Route::post('vef-history', 		'Cargo\VefController@history');
Route::post('vef-analysis', 	'Cargo\VefController@analysis');

// Authentication routes...
Route::get('auth/login', 'HomeController@index');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::post('auth/eblogin',['uses' =>'Auth\AuthController@postEBLogin' ,	'middleware' =>  ['EBVerifyCsrfToken']]);
Route::get('auth/logout', 'Auth\AuthController@getLogout');
Route::get('auth/confirm/{token}', 'Auth\AuthController@getConfirm');
Route::get('login/success', 'HomeController@loginSuccess');
Route::get('dclogin', 'Auth\AuthController@postDCLogin');
Route::post('dclogin', 'Auth\AuthController@postDCLogin');
Route::get('refreshtoken', ['uses' =>'Auth\AuthController@refresh' ,'middleware' =>  ['auth:api']]);
Route::post('dcloadconfig', ['uses' =>'Config\DCController@dcLoadConfig']);
Route::get('dcloadconfig', ['uses' =>'Config\DCController@dcLoadConfig']);
Route::post('dcsavedata', 'Config\DCController@dcSave');
Route::get('dcsavedata', 'Config\DCController@dcSave');

// Route::post('simplelogin','HomeController@simplelogin');

Route::get('home/{menu?}', 'HomeController@index');

//Route::get('eb', function () {    return redirect('http://eb.co');});

Route::get('exportexcel', 'ExportExcelController@linkView');
//Route::get('exportexcel', 'ExportExcelCheckLoginController@View');




// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');


//-----EB
Route::post('code/list', 'CodeController@getCodes');

Route::get('dc/flow',['uses' =>'ProductManagementController@flow','middleware' => ['checkRight:FDC_FLOW']]);
Route::post('code/load',['uses' =>'FlowController@load',	'middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('code/save',['uses' =>'FlowController@save',	'middleware' => 'checkRight:FDC_FLOW']);
Route::post('code/history',['uses' =>'FlowController@history',	'middleware' => 'checkRight:FDC_FLOW']);
Route::post('flow/filter', ['uses' =>'FlowController@filter',	'middleware' => 'checkRight:VIS_WORKFLOW']);

// Route::get('dc/eu', 'ProductManagementController@eu');
Route::get('dc/eu',['uses' =>'ProductManagementController@eu','middleware' => 'checkRight:FDC_EU']);
Route::post('eu/load',['uses' =>'EuController@load',	'middleware' =>  ['checkRight:FDC_EU','saveWorkspace']]);
Route::post('eu/save',['uses' =>'EuController@save',	'middleware' => 'checkRight:FDC_EU']);
Route::post('eu/history',['uses' =>'EuController@history',	'middleware' => 'checkRight:FDC_EU']);

Route::get('dc/storage',['uses' =>'ProductManagementController@storage','middleware' => 'checkRight:FDC_STORAGE']);
Route::post('storage/load',['uses' =>'StorageController@load',	'middleware' =>  ['checkRight:FDC_STORAGE','saveWorkspace']]);
Route::post('storage/save',['uses' =>'StorageController@save',	'middleware' => 'checkRight:FDC_STORAGE']);
Route::post('storage/history',['uses' =>'StorageController@history',	'middleware' => 'checkRight:FDC_STORAGE']);


Route::get('dc/eutest',			['uses' =>'ProductManagementController@eutest','middleware' => 'checkRight:FDC_EU_TEST']);
Route::post('eutest/load',		['uses' =>'EuTestController@load',		'middleware' =>  ['checkRight:FDC_EU_TEST','saveWorkspace']]);
Route::post('eutest/save',		['uses' =>'EuTestController@save',		'middleware' => 'checkRight:FDC_EU_TEST']);
Route::post('eutest/history',	['uses' =>'EuTestController@history',	'middleware' => 'checkRight:FDC_EU_TEST']);

Route::get('dc/wellstatus',			['uses' =>'ProductManagementController@wellstatus','middleware' => 'checkRight:FDC_WELL_STATUS']);
Route::post('wellstatus/load',		['uses' =>'EuStatusController@load',		'middleware' =>  ['checkRight:FDC_WELL_STATUS','saveWorkspace']]);
Route::post('wellstatus/save',		['uses' =>'EuStatusController@save',		'middleware' => 'checkRight:FDC_WELL_STATUS']);

Route::get('dc/scssv',			['uses' =>'ProductManagementController@scssv','middleware' => 'checkRight:FDC_SCSSV']);
Route::post('scssv/load',		['uses' =>'EuScssvController@load',		'middleware' =>  ['checkRight:FDC_SCSSV','saveWorkspace']]);
Route::post('scssv/save',		['uses' =>'EuScssvController@save',		'middleware' => 'checkRight:FDC_SCSSV']);

Route::get('dc/quality',			['uses' =>'ProductManagementController@quality','middleware' => 'checkRight:FDC_QUALITY']);
Route::post('quality/load',			['uses' =>'QualityController@load',			'middleware' =>  ['checkRight:FDC_QUALITY','saveWorkspace']]);
Route::post('quality/save',			['uses' =>'QualityController@save',			'middleware' => 'checkRight:FDC_QUALITY']);
Route::post('quality/history',		['uses' =>'QualityController@history',		'middleware' => 'checkRight:FDC_QUALITY']);
Route::post('quality/loadsrc',		['uses' =>'QualityController@loadsrc',		'middleware' =>  ['checkRight:FDC_QUALITY']]);
Route::post('quality/edit',			['uses' =>'QualityController@edit',			'middleware' => 'checkRight:FDC_QUALITY']);
Route::post('quality/edit/saving',	['uses' =>'QualityController@editSaving',	'middleware' => 'checkRight:FDC_QUALITY']);


Route::get('dc/deferment',				['uses' =>'ProductManagementController@deferment','middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/load',			['uses' =>'DefermentController@load',			'middleware' =>  ['checkRight:FDC_DEFER','saveWorkspace']]);
Route::post('deferment/save',			['uses' =>'DefermentController@save',			'middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/history',		['uses' =>'DefermentController@history',		'middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/loadsrc',		['uses' =>'DefermentController@loadsrc',		'middleware' =>  ['checkRight:FDC_DEFER']]);
Route::post('deferment/detail/load',	['uses' =>'DefermentController@load',			'middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/detail/save',	['uses' =>'DefermentController@editSaving',		'middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/wo/load',		['uses' =>'DefermentController@load',			'middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/wo/save',		['uses' =>'DefermentController@save',			'middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/wommr/load',		['uses' =>'DefermentController@load',			'middleware' => 'checkRight:FDC_DEFER']);
Route::post('deferment/wommr/save',		['uses' =>'DefermentController@save',			'middleware' => 'checkRight:FDC_DEFER']);

Route::get('dc/ticket',				['uses' => 'ProductManagementController@ticket','middleware' => 'checkRight:FDC_TICKET']);
Route::post('ticket/load',			['uses' =>'TicketController@load',			'middleware' =>  ['checkRight:FDC_TICKET','saveWorkspace']]);
Route::post('ticket/save',			['uses' =>'TicketController@save',			'middleware' => 'checkRight:FDC_TICKET']);
Route::post('ticket/history',		['uses' =>'TicketController@history',		'middleware' => 'checkRight:FDC_TICKET']);
Route::post('ticket/loadsrc',		['uses' =>'TicketController@loadsrc',		'middleware' =>  ['checkRight:FDC_TICKET']]);

//---------
Route::get('fo/safety',			['uses' =>'FOController@safety','middleware' => 'checkRight:FOP_SAFETY']);
Route::post('safety/load',		['uses' =>	'SafetyController@load','middleware' => 'saveWorkspace']);
Route::post('safety/save', 		'SafetyController@save');

Route::get('fo/comment',			['uses' =>'FOController@comment','middleware' => 'checkRight:FOP_COMMENT']);
Route::post('comment/load',			['uses' =>	'CommentController@load','middleware' => 'saveWorkspace']);
Route::post('comment/save', 		'CommentController@save');

Route::get('fo/env',			['uses' =>'FOController@environmental','middleware' => 'checkRight:FOP_ENV']);
Route::post('env/load',			['uses' =>	'EnvironmentalController@load','middleware' => 'saveWorkspace']);
Route::post('env/save', 		'EnvironmentalController@save');

Route::get('fo/equipment',			['uses' =>'FOController@equipment','middleware' => 'checkRight:FOP_EQUIP']);
Route::post('equipment/load',		['uses' =>	'EquipmentController@load','middleware' => 'saveWorkspace']);
Route::post('equipment/save', 		'EquipmentController@save');

Route::get('fo/chemical',			['uses' =>'FOController@chemical','middleware' => 'checkRight:FOP_CHEMICAL']);
Route::post('chemical/load',		['uses' =>	'ChemicalController@load','middleware' => 'saveWorkspace']);
Route::post('chemical/save', 		'ChemicalController@save');

Route::get('fo/personnel',			['uses' =>'FOController@personnel','middleware' => 'checkRight:FOP_PERSONNEL']);
Route::post('personnel/load',		['uses' =>	'PersonnelController@load','middleware' => 'saveWorkspace']);
Route::post('personnel/save', 		'PersonnelController@save');
Route::post('personnel/loadsrc', 	'PersonnelController@loadsrc');

Route::get('fo/logistic',			['uses' =>'FOController@logistic','middleware' => 'checkRight:FOP_LOGISTIC']);
Route::post('logistic/load',		['uses' =>	'LogisticController@load','middleware' => 'saveWorkspace']);
Route::post('logistic/save', 		'LogisticController@save');

Route::get('tagsMapping',			['uses' => 'SystemConfigController@tagsmapping','middleware' => 'checkRight:CONFIG_TAGS_MAPPING']);
Route::post('tagsMapping/load',		['uses' => 'TagsMappingController@load','middleware' => ['checkRight:CONFIG_TAGS_MAPPING','saveWorkspace']]);
Route::post('tagsMapping/save',		['uses' => 'TagsMappingController@save','middleware' => ['checkRight:CONFIG_TAGS_MAPPING']]);
Route::post('tagsMapping/loadsrc',	['uses' => 'TagsMappingController@loadsrc','middleware' => ['checkRight:CONFIG_TAGS_MAPPING']]);
Route::post('tags/all',				['uses' => 'TagsMappingController@all']);

Route::get('routeconfig',			['uses' => 'SystemConfigController@routeconfig','middleware' => 'checkRight:CONFIG_TAGS_MAPPING']);
Route::post('routeconfig/load',		['uses' => 'Config\RouteConfigController@load','middleware' => ['checkRight:CONFIG_TAGS_MAPPING']]);
Route::post('routeconfig/save',		['uses' => 'Config\RouteConfigController@save','middleware' => ['checkRight:CONFIG_TAGS_MAPPING']]);
Route::post('sourceconfig/loadsrc',	['uses' => 'Config\RouteConfigController@loadsrc',	'middleware' =>  ['checkRight:CONFIG_TAGS_MAPPING']]);

Route::get('fp/forecast',			['uses' =>'ForecastPlanningController@forecast','middleware' => 'checkRight:FP_WELLFORECAST']);
Route::post('forecast/load',		['uses' =>	'EnergyUnitForecastController@load','middleware' => 'saveWorkspace']);
Route::post('forecast/run', 		'EnergyUnitForecastController@run');

Route::get('fp/preos',			['uses' =>'ForecastPlanningController@preos','middleware' => 'checkRight:FP_PREOS']);
Route::post('preos/load',		['uses' =>	'PreosController@load','middleware' => 'saveWorkspace']);
Route::post('preos/run', 		'PreosController@run');

Route::get('fp/allocateplan',		['uses' =>'ForecastPlanningController@allocateplan','middleware' => 'checkRight:FP_ALLOCATE_PLAN']);
Route::post('allocateplan/load',	['uses' =>'AllocatePlanController@load', 'middleware' =>  ['checkRight:FP_ALLOCATE_PLAN','saveWorkspace']]);
Route::post('allocateplan/save', 	['uses' => 'AllocatePlanController@save','middleware' =>  ['checkRight:FP_ALLOCATE_PLAN']]);

Route::get('fp/allocateforecast',	['uses' =>'ForecastPlanningController@allocateforecast','middleware' => 'checkRight:FP_ALLOCATE_PLAN']);
Route::post('allocateforecast/load',['uses' =>'AllocateForecastController@load','middleware' =>  ['checkRight:FP_ALLOCATE_PLAN','saveWorkspace']]);
Route::post('allocateforecast/save',['uses' =>'AllocateForecastController@save','middleware' =>  ['checkRight:FP_ALLOCATE_PLAN']]);

Route::get('me/setting',			['uses' =>'UserSettingController@index'/* ,'middleware' => 'checkRight:FP_ALLOCATE_PLAN' */]);
Route::post('me/setting/save', 		'UserSettingController@saveSetting');
Route::post('me/changepass', 		'UserSettingController@changePass');

Route::get('fp/loadplanforecast',	['uses' =>'ForecastPlanningController@loadplan'	,'middleware' => 'checkRight:FP_LOAD_PLAN_FORECAST']);
Route::get('fp/choke',				['uses' =>'ForecastPlanningController@choke'	,'middleware' => 'checkRight:VIS_CHOKE_MODEL']);
Route::post('choke/load',			['uses' =>'Forecast\ChokeController@load'		,'middleware' => 'checkRight:VIS_CHOKE_MODEL']);
Route::post('choke/save',			['uses' =>'Forecast\ChokeController@save'		,'middleware' => 'checkRight:VIS_CHOKE_MODEL']);
Route::post('choke/filter', 		['uses' =>'Forecast\ChokeController@filter'		,'middleware' => 'checkRight:VIS_CHOKE_MODEL']);
Route::post('choke/summary', 		['uses' =>'Forecast\ChokeController@summary'	,'middleware' => ['checkRight:VIS_CHOKE_MODEL','saveWorkspace']]);

Route::get('dv/taskman',			['uses' =>'DVController@taskman',						'middleware' => 'checkRight:VIS_TASKMAN']);
Route::post('taskman/load',			['uses' =>'DataVisualization\TaskManController@load',	'middleware' =>  ['checkRight:VIS_TASKMAN','saveWorkspace']]);
Route::post('taskman/save',			['uses' =>'DataVisualization\TaskManController@save',	'middleware' =>  ['checkRight:VIS_TASKMAN']]);
Route::post('taskman/loadsrc',		['uses' =>'DataVisualization\TaskManController@loadsrc','middleware' =>  ['checkRight:VIS_TASKMAN']]);
Route::post('taskman/update/{command}/{id}',	['uses' =>'DataVisualization\TaskManController@update',	'middleware' =>  ['checkRight:VIS_TASKMAN']]);

Route::get('pd/cargoentry',			['uses' =>'ProductDeliveryController@cargoentry','middleware' => 'checkRight:PD_CARGO_ADMIN']);
Route::post('cargoentry/load',		['uses' =>	'Cargo\CargoEntryController@load'	,'middleware' => 'saveWorkspace']);
Route::post('cargoentry/save', 		'Cargo\CargoEntryController@save');
Route::post('cargoentry/nominate', 	'Cargo\CargoEntryController@nominate');

Route::get('pd/cargonomination',			['uses' =>'ProductDeliveryController@cargonomination'	,'middleware' => 'checkRight:PD_CARGO_ADMIN']);
Route::post('cargonomination/load',			['uses' =>'Cargo\CargoNominationController@load'		,'middleware' => 'saveWorkspace']);
Route::post('cargonomination/save', 		'Cargo\CargoNominationController@save');
Route::post('cargonomination/loadsrc', 		'Cargo\CargoNominationController@loadsrc');
Route::post('cargonomination/confirm', 		'Cargo\CargoNominationController@confirm');
Route::post('cargonomination/reset', 		'Cargo\CargoNominationController@reset');

Route::get('pd/cargoschedule',			['uses' =>'ProductDeliveryController@cargoschedule','middleware' => 'checkRight:PD_CARGO_ADMIN']);
Route::post('cargoschedule/load',		['uses' =>	'Cargo\CargoScheduleController@load','middleware' => 'saveWorkspace']);
Route::post('cargoschedule/save', 		'Cargo\CargoScheduleController@save');

Route::get('pd/storagedisplay',			['uses' =>'ProductDeliveryController@storagedisplay','middleware' => 'checkRight:VIS_STORAGE_DISPLAY']);
Route::post('storagedisplay/filter', 	['uses' =>'Cargo\StorageDisplayController@filter'	,'middleware' => 'checkRight:VIS_STORAGE_DISPLAY']);
Route::post('storagedisplay/loadchart', ['uses' =>'Cargo\StorageDisplayController@summary'	,'middleware' => 'checkRight:VIS_STORAGE_DISPLAY']);
Route::post('storagedisplay/load',		['uses' =>'Cargo\StorageDisplayController@load'		,'middleware' => 'checkRight:VIS_STORAGE_DISPLAY']);
Route::post('storagedisplay/save',		['uses' =>'Cargo\StorageDisplayController@save'		,'middleware' => 'checkRight:VIS_STORAGE_DISPLAY']);
Route::get('storagedisplay/diagram', 	['uses' =>'Cargo\StorageDisplayController@diagram'	,'middleware' => 'checkRight:VIS_STORAGE_DISPLAY']);
Route::post('viewconfig/{id}',			['uses' =>'ViewConfigController@getViewConfigById'	,'middleware' => 'checkRight:CF_VIEW_CONFIG']);


Route::get('pd/cargovoyage',			['uses' =>'ProductDeliveryController@cargovoyage',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('cargovoyage/load',			['uses' =>'Cargo\CargoVoyageController@load',		'middleware' => 'saveWorkspace']);
Route::post('cargovoyage/save', 		'Cargo\CargoVoyageController@save');
Route::post('voyage/load', 				'Cargo\CargoVoyageController@load');
Route::post('voyage/save', 				'Cargo\CargoVoyageController@save');
Route::post('voyage/gentransport', 		'Cargo\CargoVoyageController@gentransport');

Route::get('pd/cargoload',				['uses' =>'ProductDeliveryController@cargoload',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('cargoload/load',			['uses' =>'Cargo\CargoLoadController@load',			'middleware' => 'saveWorkspace']);
Route::post('cargoload/save', 			'Cargo\CargoLoadController@save');
Route::post('timesheet/load',			['uses' =>'Cargo\CargoLoadController@load']);
Route::post('timesheet/save', 			'Cargo\CargoLoadController@save');
Route::post('timesheet/activities', 	'Cargo\CargoLoadController@activities');

Route::get('pd/cargounload',			['uses' =>'ProductDeliveryController@cargounload',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('cargounload/load',			['uses' =>'Cargo\CargoUnLoadController@load',			'middleware' => 'saveWorkspace']);
Route::post('cargounload/save', 		['uses' =>'Cargo\CargoUnLoadController@save', 'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('timesheet/unload',			['uses' =>'Cargo\CargoUnLoadController@load', 'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('timesheet/save_unload', 	['uses' =>'Cargo\CargoUnLoadController@save', 'middleware' => 'checkRight:PD_CARGO_ACTION']);

Route::get('pd/voyagemarine',			['uses' =>'ProductDeliveryController@voyagemarine',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('voyagemarine/load',		['uses' =>'Cargo\VoyageMarineController@load',			'middleware' => 'saveWorkspace']);
Route::post('voyagemarine/save', 		['uses' =>'Cargo\VoyageMarineController@save',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('voyagemarine/gen', 		['uses' =>'Cargo\VoyageMarineController@genBLMR',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('shipport/load',			['uses' =>'Cargo\VoyageMarineController@load', 'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('shipport/save', 			['uses' =>'Cargo\VoyageMarineController@save', 'middleware' => 'checkRight:PD_CARGO_ACTION']);

Route::get('pd/voyageground',			['uses' =>'ProductDeliveryController@voyageground',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('voyageground/load',		['uses' =>'Cargo\VoyageGroundController@load',		'middleware' => 'saveWorkspace']);
Route::post('voyageground/save', 		['uses' =>'Cargo\VoyageGroundController@save',		'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('voyageground/gen', 		['uses' =>'Cargo\VoyageGroundController@genBLMR',	'middleware' => 'checkRight:PD_CARGO_ACTION']);

Route::get('pd/voyagepipeline',			['uses' =>'ProductDeliveryController@voyagepipeline',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('voyagepipeline/load',		['uses' =>'Cargo\VoyagePipelineController@load',		'middleware' => 'saveWorkspace']);
Route::post('voyagepipeline/save', 		['uses' =>'Cargo\VoyagePipelineController@save',		'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('voyagepipeline/gen', 		['uses' =>'Cargo\VoyagePipelineController@genBLMR',		'middleware' => 'checkRight:PD_CARGO_ACTION']);

Route::get('pd/shipblmr',				['uses' =>'ProductDeliveryController@shipblmr',			'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('shipblmr/load',			['uses' =>'Cargo\CargoShipblmrController@load',			'middleware' => 'saveWorkspace']);
Route::post('shipblmr/save', 			['uses' =>'Cargo\CargoShipblmrController@save',			'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('shipblmrdetail/load',		['uses' =>'Cargo\CargoShipblmrController@load',	'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('shipblmrdetail/save',		['uses' =>'Cargo\CargoShipblmrController@save',			'middleware' => 'checkRight:PD_CARGO_ACTION']);
Route::post('shipblmrdetail/cal',		['uses' =>'Cargo\CargoShipblmrController@cal',			'middleware' => 'checkRight:PD_CARGO_ACTION']);

Route::get('pd/contractdata',			['uses' =>'ProductDeliveryController@contractdata','middleware' => 'checkRight:PD_CONTRACT_ADMIN']);
Route::post('contractdata/load',		['uses' =>'Contract\ContractDataController@load','middleware' => 'saveWorkspace']);
Route::post('contractdata/save', 		'Contract\ContractDataController@save');
Route::post('contractdetail/load',		'Contract\ContractDataController@load');
Route::post('contractdetail/save', 		'Contract\ContractDataController@save');

Route::get('pd/contractcalculate',			['uses' =>'ProductDeliveryController@contractcalculate','middleware' => 'checkRight:PD_CONTRACT_ADMIN']);
Route::post('contractcalculate/load',		['uses' =>'Contract\ContractCalculateController@load','middleware' => 'saveWorkspace']);
Route::post('contractcalculate/save', 		'Contract\ContractCalculateController@save');
Route::post('contractcalculate/addyear', 		'Contract\ContractCalculateController@addyear');

Route::get('pd/contracttemplate',					['uses' =>'ProductDeliveryController@contracttemplate','middleware' => 'checkRight:PD_CONTRACT_ADMIN']);
Route::post('contracttemplate/load',				['uses' =>'Contract\ContractTemplateController@load','middleware' 	=> 'saveWorkspace']);
Route::post('contracttemplate/save', 				'Contract\ContractTemplateController@save');
Route::post('contracttemplateattribute/load',		['uses' =>'Contract\ContractTemplateController@load','middleware' 	=> 'saveWorkspace']);
Route::post('contracttemplateattribute/save', 		'Contract\ContractTemplateController@save');

Route::get('pd/contractprogram',			['uses' =>'ProductDeliveryController@contractprogram',		'middleware' => 'checkRight:PD_CONTRACT_ADMIN']);
Route::post('contractprogram/load',			['uses' =>'Contract\ContractProgramController@load',		'middleware' => 'saveWorkspace']);
Route::post('contractprogram/save', 		['uses' =>'Contract\ContractProgramController@save',		'middleware' => 'checkRight:PD_CONTRACT_ADMIN']);
Route::post('contractprogram/open',			['uses' =>'Contract\ContractProgramController@open',		'middleware' => 'checkRight:PD_CONTRACT_ADMIN']);
Route::post('gen_cargo_entry/calculate', 	['uses' =>'Contract\ContractProgramController@calculate']);
Route::post('gen_cargo_entry/gen', 			['uses' =>'Contract\ContractProgramController@gen',			'middleware' => 'checkRight:PD_CONTRACT_ADMIN']);


Route::get('pd/demurrageebo',			['uses' =>'ProductDeliveryController@demurrageebo','middleware' => 'checkRight:PD_CARGO_MAN']);
Route::post('demurragreebo/load',		['uses' =>'Cargo\DemurrageeboController@load','middleware' => 'saveWorkspace']);
Route::post('demurragreebo/save', 		'Cargo\DemurrageeboController@saveDemurrage');
Route::post('demurragreebo/loadsrc', 	'Cargo\DemurrageeboController@loadsrc');

Route::get('pd/cargodocuments',			['uses' =>'ProductDeliveryController@cargodocuments',	'middleware' => 'checkRight:PD_CARGO_MAN']);
Route::post('cargodocuments/load',		['uses' =>'Cargo\CargoDocumentsController@load',		'middleware' => 'saveWorkspace']);
Route::post('documentset/load', 		['uses' =>'Cargo\CargoDocumentsController@load',		'middleware' => 'checkRight:PD_CARGO_MAN']);
Route::post('documentset/save', 		['uses' =>'Cargo\CargoDocumentsController@save',		'middleware' => 'checkRight:PD_CARGO_MAN']);
Route::post('documentset/activities', 	['uses' =>'Cargo\CargoDocumentsController@activities',	'middleware' => 'checkRight:PD_CARGO_MAN']);

Route::get('pd/cargostatus',			['uses' =>'ProductDeliveryController@cargostatus',	'middleware' => 'checkRight:PD_CARGO_MAN']);
Route::post('cargostatus/load',			['uses' =>'Cargo\CargoStatusController@load',		'middleware' => 'saveWorkspace']);
Route::post('cargostatus/detail', 		['uses' =>'Cargo\CargoStatusController@loadDetail',	'middleware' => 'checkRight:PD_CARGO_MAN']);


Route::get('pd/liftaccdailybalance',			['uses' =>'ProductDeliveryController@liftaccdailybalance',	'middleware' => 'checkRight:PD_CARGO_MON']);
Route::post('liftaccdailybalance/load',			['uses' =>'Cargo\LiftDailyController@load',					'middleware' => 'saveWorkspace']);

Route::get('pd/cargoplanning',			['uses' =>'ProductDeliveryController@cargoplanning','middleware' => 'checkRight:PD_CARGO_MON']);
Route::post('cargoplanning/load',		['uses' =>'Cargo\CargoPlanningController@load',		'middleware' => ['checkRight:PD_CARGO_MON','saveWorkspace']]);
Route::post('cargoplanning/gen',		['uses' =>'Cargo\CargoPlanningController@gen',		'middleware' => ['checkRight:PD_CARGO_MON','saveWorkspace']]);

Route::get('pd/liftaccmonthlyadjust',			['uses' =>'ProductDeliveryController@liftaccmonthlyadjust',		'middleware' => 'checkRight:PD_CARGO_MON']);
Route::post('liftaccmonthlyadjust/load',		['uses' =>'Cargo\LiftMonthlyController@load',				'middleware' => 'saveWorkspace']);
Route::post('liftaccmonthlyadjust/save', 		['uses' =>'Cargo\LiftMonthlyController@save',				'middleware' => 'checkRight:PD_CARGO_MON']);

Route::get('help/{name}',			['uses' =>'CodeController@help']);

//----------admin
Route::get('am/users',	['uses' =>'AdminController@_index',	'middleware' => 'checkRight:ADMIN_USERS']);
Route::post('am/load', ['uses' =>'AdminUserController@load','middleware' => 'checkRight:ADMIN_USERS']);
Route::post('am/loadData', 'AdminController@getData');
Route::post('am/selectedID', 'AdminController@selectedID');
Route::post('am/loadUserList', 'AdminController@getUsersList');
Route::get('am/delete', 'AdminController@deleteUser');
Route::post('am/save', 'AdminController@addNewUser');
Route::post('am/updateUser', 'AdminController@updateUser');
Route::post('am/saveuser', 'AdminController@save');

Route::get('am/roles',	['uses' =>'AdminController@_indexRoles',	'middleware' => 'checkRight:ADMIN_ROLES']);
Route::post('am/editRoles', 'AdminController@editRole');
Route::post('am/addRoles', 'AdminController@addRole');
Route::post('am/deleteRoles', 'AdminController@deleteRole');
Route::post('am/loadRightsList', 'AdminController@loadRightsList');
Route::post('am/removeOrGrant', 'AdminController@removeOrGrant');
Route::post('am/savereadonly', 'AdminController@save');

Route::get('am/audittrail',		['uses' =>'AdminController@_indexAudittrail',	'middleware' => 'checkRight:ADMIN_AUDIT']);
Route::post('am/loadAudittrail',['uses' =>'Admin\AuditController@load',			'middleware' => 'saveWorkspace']);

Route::get('am/validatedata',	['uses' =>'AdminController@_indexValidatedata',	'middleware' => 'checkRight:ADMIN_VALIDATE']);
Route::post('am/loadValidateData', 'AdminController@loadValidateData2');
Route::post('am/validateData',['uses' =>'AdminController@validateData','middleware' => ['checkRight:ADMIN_VALIDATE','saveWorkspace']]);

Route::get('am/approvedata',	['uses' =>'AdminController@_indexApprove',	'middleware' => 'checkRight:ADMIN_APPROVE']);
Route::post('am/loadApproveData', 'AdminController@loadApproveData2');
//Route::post('am/approveData', 'AdminController@ApproveData');
Route::post('am/approveData',['uses' =>'AdminController@ApproveData','middleware' => ['checkRight:ADMIN_APPROVE','saveWorkspace']]);

Route::get('am/lockdata',	['uses' =>'AdminController@_indexLockData',	'middleware' => 'checkRight:ADMIN_DATA_LOCKING']);
Route::post('am/loadLockData', 'AdminController@loadLockData');
Route::post('am/lockData', 'AdminController@lockData');

Route::get('am/userlog',	['uses' =>'AdminController@_indexUserlog',	'middleware' => 'checkRight:ADMIN_USER_LOG']);
Route::post('am/loaduserlog', ['uses' =>'AdminLogUserController@load','middleware' => 'checkRight:ADMIN_USER_LOG']);
Route::post('am/loadUserLog', 'AdminController@loadUserLog');

Route::get('am/editGroup', 'AdminController@_indexEditGroup');
Route::post('am/loadGroup', 'AdminController@loadGroup');
Route::post('am/saveGroup', 'AdminController@saveGroup');
Route::post('am/deleteGroup', 'AdminController@deleteGroup');

Route::get('am/helpeditor', 'AdminController@_helpEditor');
Route::post('am/getFunction', 'AdminController@getFunction');
Route::post('am/gethelp', 'AdminController@gethelp');
Route::post('am/savehelp', 'AdminController@savehelp');

//========== DATA VISUALIZATION
Route::get('diagram',['uses' =>'DVController@_indexDiagram','middleware' => 'checkRight:VIS_NETWORK_MODEL']);
Route::post('getdiagram', 'DVController@getdiagram');
Route::get('loaddiagram/{id}', 'DVController@loaddiagram');
Route::post('savediagram', 'DVController@savediagram');
Route::post('deletediagram', 'DVController@deletediagram');
Route::get('diagram/networkmodel', 'DVController@loadNetworkModel');
Route::get('/diagram/editor', 'DVController@editor');
Route::post('diagram/filter', ['uses' =>'DVController@filter','middleware' => 'checkRight:VIS_NETWORK_MODEL']);

Route::post('onChangeObj', 'DVController@onChangeObj');
Route::post('getSurveillanceSetting', 'DVController@getSurveillanceSetting');
Route::post('getValueSurveillance', 'DVController@getValueSurveillance');
Route::post('uploadImg', 'DVController@uploadImg');

Route::get('workflow',['uses' =>'DVController@_indexWorkFlow','middleware' => 'checkRight:VIS_WORKFLOW']);
Route::post('getListWorkFlow', 'DVController@getListWorkFlow');
Route::post('getXMLCodeWF', 'DVController@getXMLCodeWF');
Route::post('workflowSave', 'DVController@workflowSave');
//Route::post('workflowSaveAs', 'DVController@workflowSaveAs');
Route::post('loadConfigTask', 'DVController@loadConfigTask');
Route::post('changeRunTask', 'DVController@changeRunTask');
Route::post('loadFormSetting', 'DVController@loadFormSetting');
Route::post('getEntity', 'DVController@getEntity');
Route::post('workflowSaveTask', 'DVController@workflowSaveTask');
Route::post('deleteWorkFlow', 'DVController@deleteWorkFlow');
Route::post('stopWorkFlow', 'DVController@stopWorkFlow');
Route::post('runWorkFlow', 'DVController@runWorkFlow');
Route::post('showTaskLog', 'DVController@showTaskLog');
Route::post('getKey', 'DVController@getKey');
Route::resource('runAlloc', 'RunController@runAlloc');

Route::get('workreport/{type?}',['uses' =>'ReportController@_index','middleware' => 'checkRight:VIS_REPORT']);
Route::post('report/loadreports','ReportController@loadReports');
Route::post('report/loadparams','ReportController@loadParams');
Route::get('loadWfShow', 'wfShowController@loadData');
Route::post('reLoadtTmworkflow', 'wfShowController@reLoadtTmworkflow');
Route::post('finish_workflowtask', 'wfShowController@finish_workflowtask');
Route::post('upFile', 'DVController@uploadFile');
Route::post('openTask', 'wfShowController@openTask');
Route::post('countWorkflowTask', 'wfShowController@countWorkflowTask');

Route::get('graph',['uses' =>'graphController@_index','middleware' => 'checkRight:VIS_ADVGRAPH']);
Route::post('loadVizObjects', 'graphController@loadVizObjects');
Route::post('loadEUPhase', 'graphController@loadEUPhase');
Route::get('loadchart', ['uses' =>'graphController@loadChart','middleware' => ['checkRight:VIS_ADVGRAPH','saveWorkspace']]);
Route::post('listCharts', 'graphController@getListCharts');
Route::post('deleteChart', 'graphController@deleteChart');
Route::post('saveChart', 'graphController@saveChart');
Route::post('getProperty', 'graphController@getProperty');
Route::post('graph/filter', ['uses' =>'graphController@filter','middleware' => 'checkRight:VIS_ADVGRAPH']);
Route::post('graph/objects', ['uses' =>'graphController@objects','middleware' => 'checkRight:VIS_ADVGRAPH']);
Route::post('graph/chart/{id}',	['uses' =>'graphController@getChartById',	'middleware' =>  ['checkRight:VIS_ADVGRAPH']]);


Route::get('dashboard',			['uses' =>'DVController@dashboard',			'middleware' => ['checkRight:VIS_DASHBOARD','saveWorkspace']]);
Route::post('dashboard/all',	['uses' =>'DataVisualization\DashboardController@all']);
Route::get('config/dashboard',	['uses' =>'DVController@dashboardConfig',	'middleware' => 'checkRight:CF_DASHBOARD_CONFIG']);
Route::post('dashboard/save',	['uses' =>'DataVisualization\DashboardController@save',	'middleware' => 'checkRight:CF_DASHBOARD_CONFIG']);

Route::get('viewconfig',		['uses' =>'ViewConfigController@_indexViewConfig','middleware' => 'checkRight:CF_VIEW_CONFIG']);
Route::post('loadPlotObjects', 'ViewConfigController@loadPlotObjects');
Route::post('getTableFields', 'ViewConfigController@getTableFields');
Route::post('getListPlotItems', 'ViewConfigController@getListPlotItems');
Route::post('deletePlotItems', 'ViewConfigController@deletePlotItems');
Route::post('savePlotItems', 'ViewConfigController@savePlotItems');
Route::post('genView', 'ViewConfigController@genView');

Route::get('allocrun',['uses' =>'AllocationController@_index','middleware' => 'checkRight:ALLOC_RUN']);
Route::post('getJobsRunAlloc', 'AllocationController@getJobsRunAlloc');
Route::post('run_runner', 'AllocationController@run_runner');

Route::get('allocset',['uses' =>'AllocationController@_indexconfig','middleware' => 'checkRight:ALLOC_CONFIG']);
Route::post('addJob', 'AllocationController@addJob');
Route::post('addrunner', 'AllocationController@addrunner');
Route::post('getrunnerslist', 'AllocationController@getrunnerslist');
Route::post('getconditionslist', 'AllocationController@getconditionslist');
Route::post('deletejob', 'AllocationController@deletejob');
Route::post('savecondition', 'AllocationController@savecondition');
Route::post('deleterunner', 'AllocationController@deleterunner');
Route::post('clonenetwork', 'AllocationController@clonenetwork');
Route::post('renameallocationgroup', 'AllocationController@renameAllocationGroup');
Route::post('deleteallocationgroup', 'AllocationController@deleteAllocationGroup');
Route::post('newallocationgroup', 'AllocationController@newAllocationGroup');
Route::get('jobdiagram/{job_id}', 'AllocationController@jobdiagram');
Route::get('loadjobdiagram/{id}', 'AllocationController@loaddiagram');
Route::post('editJob', 'AllocationController@editJob');
Route::post('saveEditRunner', 'AllocationController@saveEditRunner');
Route::get('genTemplateFile/{ids}', 'AllocationController@genTemplateFile');
Route::get('downloadAllocResultFile/{file}', 'AllocationController@downloadExcelAllocFile');
Route::get('downloadAllocTemplateFile/{file}', 'AllocationController@downloadAllocTemplateFile');

Route::get('fieldsconfig',['uses' =>'FieldsConfigController@_index','middleware' => 'checkRight:CONFIG_FIELDS']);
Route::post('getColumn', 'FieldsConfigController@getColumn');
Route::post('saveDisableDC', 'FieldsConfigController@saveDisableDC');
Route::post('saveconfig', 'FieldsConfigController@saveconfig');
Route::post('chckChange', 'FieldsConfigController@chckChange');
Route::post('getprop', 'FieldsConfigController@getprop');
Route::post('saveprop', 'FieldsConfigController@saveprop');
Route::post('dataJson', 'FieldsConfigController@getDataJson');
Route::post('savealldata', 'FieldsConfigController@save');

Route::get('dc/subdaily', ['uses' =>'SubdailyController@index','middleware' => ['checkRight:FDC_FLOW']]);
Route::post('subdaily/load',['uses' =>'SubdailyController@load','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('subdaily/save',['uses' =>'SubdailyController@save','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);

Route::get('dc/subdailyold', ['uses' =>'SubdailyControllerOld@index','middleware' => ['checkRight:FDC_FLOW']]);
Route::post('subdailyold/load',['uses' =>'SubdailyControllerOld@load','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('subdailyold/save',['uses' =>'SubdailyControllerOld@save','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);

Route::post('loadsubday',['uses' =>'SubdailyController@loadsubday','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('reallocate',['uses' =>'SubdailyController@reallocate','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('deleteallocate',['uses' =>'SubdailyController@deleteallocate','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('saveallocate',['uses' =>'SubdailyController@saveallocate','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);

Route::post('loadsubdayold',['uses' =>'SubdailyControllerOld@loadsubday','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('reallocateold',['uses' =>'SubdailyControllerOld@reallocate','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('deleteallocateold',['uses' =>'SubdailyControllerOld@deleteallocate','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);
Route::post('saveallocateold',['uses' =>'SubdailyControllerOld@saveallocate','middleware' =>  ['checkRight:FDC_FLOW','saveWorkspace']]);

Route::get('objectsmanager',['uses' =>'ObjectsManagerController@_index','middleware' => 'checkRight:OBJECT_MANAGER']);
Route::post('objectsmanager/getobjectsinfo', 'ObjectsManagerController@getObjectsInfo');
Route::post('objectsmanager/savemapinfo', 'ObjectsManagerController@saveMapInfo');

Route::get('loadtabledata',['uses' =>'DataViewController@tabledata','middleware' => 'checkRight:CONFIG_TABLE_DATA']);
Route::get('loadtabledata/edittable',['uses' =>'Config\TableDataController@edittable','middleware' => 'checkRight:CONFIG_TABLE_DATA']);
Route::post('loadtabledata/edittable',['uses' =>'Config\TableDataController@edittable','middleware' => 'checkRight:CONFIG_TABLE_DATA']);
Route::post('loadtabledata/delete',['uses' =>'Config\TableDataController@delete','middleware' => 'checkRight:CONFIG_TABLE_DATA']);
Route::post('loadtabledata/gensql',['uses' =>'Config\TableDataController@genSql','middleware' => 'checkRight:CONFIG_TABLE_DATA']);
Route::get('pdtabledata',['uses' =>'DataViewController@pdtabledata','middleware' => 'checkRight:CONFIG_TABLE_DATA']);
Route::get('exporttabledata/{str}', 'Config\TableDataController@exportDataXlsx');

Route::get('formula',['uses' =>'FormulaController@_index','middleware' => 'checkRight:CONFIG_FORMULA']);
Route::post('editgroupname', 'FormulaController@editGroupName');
Route::post('addgroupname', 'FormulaController@addGroupName');
Route::post('deletegroup', 'FormulaController@deleteGroup');
Route::post('getformulaslist', 'FormulaController@getformulaslist');
Route::post('getvarlist', 'FormulaController@getVarList');
Route::post('deleteformula', 'FormulaController@deleteformula');
Route::post('saveformulaorder', 'FormulaController@saveFormulaOrder');
Route::post('saveformula', 'FormulaController@saveformula');
Route::post('testformula', 'FormulaController@testformula');
Route::post('deletevar', 'FormulaController@deletevar');
Route::post('savevarsorder', 'FormulaController@saveVarsOrder');

Route::get('dataview',['uses' =>'DataViewController@_index','middleware' => 'checkRight:VIS_DATA_VIEW']);
Route::post('getsql', 'DataViewController@getsql');
Route::post('loaddataview', 'DataViewController@loaddata');
Route::get('loaddataview', 'DataViewControllerNoAuth@loaddata');
Route::post('deletesql', 'DataViewController@deletesql');
Route::post('checksql', 'DataViewController@checkSQL');
Route::get('downloadExcel/{sql}', 'DataViewController@downloadExcel');
Route::post('savesql', 'DataViewController@savesql');

Route::get('genreport/{params}', 'ReportController@genReport');
Route::get('recordstatussummarydata', ['uses' =>'DVController@getRecordStatusSummaryData','middleware' => ['checkRight:ADMIN_VALIDATE','saveWorkspace']]);

Route::get('exportdata',['uses' =>'InterfaceController@_exportData','middleware' => 'checkRight:INT_EXPORT_DATA']);
Route::get('export/excel', 'ExportDataController@exportDataExcel');
Route::get('exportexcel/{str}', 'ExportDataController@exportDataExcelNew');
Route::post('changeobjdata', 'ExportDataController@changeObjectDataSource');
Route::post('changedefergroup', 'ExportDataController@changeCodeDeferGroupType');
Route::post('changefacilitykeystore', 'ExportDataController@changeFacilityKeystore');

Route::get('importdata',['uses' =>'InterfaceController@_index','middleware' => 'checkRight:INT_IMPORT_DATA']);
Route::post('getimportsetting', 'InterfaceController@getImportSetting');
Route::post('doimport', 'InterfaceController@doImport');
Route::post('getrealtimedata', 'InterfaceController@getRealtimeData');

Route::get('sourceconfig',['uses' =>'InterfaceController@_indexConfig','middleware' => 'checkRight:INT_SOURCE_CONFIG']);
Route::post('saveimportsetting', 'InterfaceController@saveImportSetting');
Route::post('deletesetting', 'InterfaceController@deleteSetting');
Route::post('renamesetting', 'InterfaceController@renameSetting');
Route::post('loadintservers', 'InterfaceController@loadIntServers');
Route::post('detailsconnection', 'InterfaceController@detailsConnection');
Route::post('saveconn', 'InterfaceController@saveConn');
Route::post('renameconn', 'InterfaceController@renameConn');
Route::post('deleteconn', 'InterfaceController@deleteConn');
Route::post('loadtagset', 'InterfaceController@loadTagSet');
Route::post('savetagset', 'InterfaceController@saveTagSet');
Route::post('loadtagsets', 'InterfaceController@loadTagSets');
Route::post('renametagset', 'InterfaceController@renameTagSet');
Route::post('deletetagset', 'InterfaceController@deleteTagSet');
Route::post('importnetworkdata', 'InterfaceController@importNetworkData');

Route::get('dataloader',['uses' =>'InterfaceController@_indexDataloader','middleware' => 'checkRight:INT_DATA_LOADER']);
Route::post('gettablefieldsall', 'InterfaceController@getTableFieldsAll');
Route::post('doimportdataloader', 'InterfaceController@doImportDataLoader');

Route::get('test/runschedule','TestController@runSchedule');
Route::get('test/gpm','TestController@gitPullMaster');
Route::get('test/showdb','TestController@showdb');
Route::get('test','TestController@test');
Route::get('convertcharts','TestController@convertCharts');
Route::get('convertdashboards','TestController@convertDashboards');

// Ghg Combustion EMISSION ENTRY
//mTODO update right later checkRight EMISSION SOURCES
Route::get('ghg/ee/combustion',['uses' =>'GhgController@combustionEmissionEntry','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('combustion/ee/load', ['uses' =>'Emission\CombustionEmissionEntryController@load','middleware' => ['checkRight:PD_CARGO_ADMIN_ENTRY','saveWorkspace']]);
Route::post('combustion/ee/save', 'Emission\CombustionEmissionEntryController@save');
Route::post('eec/load','Emission\CombustionEmissionEntryController@load');
Route::post('eec/save','Emission\CombustionEmissionEntryController@save');

// Ghg Indirect
Route::get('ghg/ee/indirect',['uses' =>'GhgController@indirectEmissionEntry','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('indirect/ee/load', ['uses' =>'Emission\IndirectEmissionEntryController@load','middleware' => ['checkRight:PD_CARGO_ADMIN_ENTRY','saveWorkspace']]);
Route::post('indirect/ee/save', 'Emission\IndirectEmissionEntryController@save');
Route::post('eei/load','Emission\IndirectEmissionEntryController@load');
Route::post('eei/save','Emission\IndirectEmissionEntryController@save');

// Ghg Events
Route::get('ghg/ee/events',['uses' =>'GhgController@eventsEmissionEntry','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('events/ee/load', ['uses' =>'Emission\EventsEmissionEntryController@load','middleware' => ['checkRight:PD_CARGO_ADMIN_ENTRY','saveWorkspace']]);
Route::post('events/ee/save', 'Emission\EventsEmissionEntryController@save');
Route::post('events/ee/loadsrc', 'Emission\EventsEmissionEntryController@loadsrc');
Route::post('eee/load','Emission\EventsEmissionEntryController@load');
Route::post('eee/save','Emission\EventsEmissionEntryController@save');

// EMISSION SOURCES
//TODO update right later checkRight EMISSION ENTRY
Route::get('ghg/es/combustion',['uses' =>'GhgController@combustionEmissionSources','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('combustion/es/load', ['uses' =>'Emission\CombustionEmissionSourcesController@load','middleware' => ['checkRight:PD_CARGO_ADMIN_ENTRY','saveWorkspace']]);
Route::post('combustion/es/save', 'Emission\CombustionEmissionSourcesController@save');
Route::post('combustion/es/loadsrc', 'Emission\CombustionEmissionSourcesController@loadsrc');
Route::post('esc/load','Emission\CombustionEmissionSourcesController@load');
Route::post('esc/save','Emission\CombustionEmissionSourcesController@save');

// Indirect
Route::get('ghg/es/indirect',['uses' =>'GhgController@indirectEmissionSources','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('indirect/es/load', ['uses' =>'Emission\IndirectEmissionSourcesController@load','middleware' => ['checkRight:PD_CARGO_ADMIN_ENTRY','saveWorkspace']]);
Route::post('indirect/es/save', 'Emission\IndirectEmissionSourcesController@save');
Route::post('indirect/es/loadsrc', 'Emission\IndirectEmissionSourcesController@loadsrc');
Route::post('esi/load','Emission\IndirectEmissionSourcesController@load');
Route::post('esi/save','Emission\IndirectEmissionSourcesController@save');

// Events
Route::get('ghg/es/events',['uses' =>'GhgController@eventsEmissionSources','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('events/es/load', ['uses' =>'Emission\EventsEmissionSourcesController@load','middleware' => ['checkRight:PD_CARGO_ADMIN_ENTRY','saveWorkspace']]);
Route::post('events/es/save', 'Emission\EventsEmissionSourcesController@save');
Route::post('events/es/loadsrc', 'Emission\EventsEmissionSourcesController@loadsrc');
Route::post('ese/load','Emission\EventsEmissionSourcesController@load');
Route::post('ese/save','Emission\EventsEmissionSourcesController@save');

// EMISSION RELEASE
//TODO update right later checkRight EMISSION RELEASE
Route::get('ghg/er/combustion',['uses' =>'GhgController@combustionEmissionRelease','middleware' => 'checkRight:PD_CARGO_ADMIN_ENTRY']);
Route::post('combustion/er/load', ['uses' =>'Emission\CombustionEmissionReleaseController@load','middleware' => ['checkRight:PD_CARGO_ADMIN_ENTRY','saveWorkspace']]);
Route::post('combustion/er/save', 'Emission\CombustionEmissionReleaseController@save');
Route::post('combustion/er/loadsrc', 'Emission\CombustionEmissionReleaseController@loadsrc');
Route::post('erc/load','Emission\CombustionEmissionReleaseController@load');
Route::post('erc/save','Emission\CombustionEmissionReleaseController@save');

Route::post('submitkey', 'EBController@submitkey');
Route::get('submitkey', 'EBController@licensekey');

Route::post('cfgconfig/save', 'Config\CfgController@save');
Route::post('cfgconfig/load', 'Config\CfgController@load');
Route::post('cfgconfig/filter', 'Config\CfgController@filter');

Route::post('loadChatView', 'SocketController@loadChatView');
Route::post('registerClient', 'SocketController@registerClient');
Route::get('loadChatView', 'SocketController@loadChatView');

Route::get('sendtestemail',			['uses' =>'TestController@sendTestEmail']);
Route::get('dbdiff',			['uses' =>'TestController@dbdiff']);
Route::get('loaddatagrid',	['uses' =>'TestController@loaddatagrid01']);
Route::get('sendreportemail/{filepath}/{emails}',	['uses' =>'TestController@sendReportEmail']);