<?php

namespace App\Http\Controllers;
use App\Models\IntImportSetting; 

class ForecastPlanningController extends EBController {
	
	public static $filterGroups = array(	'productionFilterGroup'	=>[
																		['name'			=>'IntObjectType',
																				'independent'	=>true,
																				'extra' 		=> 	["Facility",'ExtensionPhaseType'],
																				'getMethod'=>'getPreosObjectType'],
																		['name'			=>'ExtensionPhaseType',
																				'independent'=>true,
																				'getMethod'=>'loadActive'],
																		],
											'frequenceFilterGroup'	=> [["name"			=> "ObjectName",
																		"getMethod"		=> "loadBy",
																		"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType","ExtensionPhaseType"]]],
																		],
											'dateFilterGroup'		=> array(	['id'=>'date_begin',	'name'=>'From date'],
																				['id'=>'date_end',		'name'=>'To date']),
											'extra' 				=> 	["Facility",'IntObjectType','ExtensionPhaseType'],
											'FacilityDependentMore'	=> ["ObjectName"],
	);


	public function forecast(){
		$filterGroups = array(	'productionFilterGroup'	=>['EnergyUnit'],
								'frequenceFilterGroup'=> [['name'=>'ExtensionPhaseType','single'=> true],
															'ExtensionValueType',
															'ExtensionDataSource'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableButton'		=> false
						);
		return view ( 'fp.forecast',['filters'=>$filterGroups]);
	}
	
	public function preos(){
		$filterGroups = array(	'productionFilterGroup'	=>[
																['name'			=>'IntObjectType',
																'independent'	=>true,
																"getMethod"		=> "getPreosObjectType",
																'extra'			=> ["Facility","ExtensionPhaseType"],
																'dependences'	=> ["ObjectName"]
																],
																['name'=>'ExtensionPhaseType',	'independent'=>true,'getMethod'=>'getPreosPhaseType'],
				
															],
								'frequenceFilterGroup'	=> [
																["name"			=> "ObjectName",
																"getMethod"		=> "loadBy",
																"defaultEnable"	=> false,
																"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType","ExtensionPhaseType"]]],
																['name'=>'ExtensionValueType','getMethod'=>'getPreosObjectType'],
																['name'=>'ExtensionDataSource','getMethod'=>'getPreosObjectType'],
															],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'Date']),
								'enableButton'			=> false,
								'extra' 				=> ['IntObjectType','ExtensionPhaseType'],
								'FacilityDependentMore'	=> ["ObjectName"],
		);
		return view ( 'fp.preos',['filters'=>$filterGroups]);
	}
	
	public function allocateplan(){
		$filterGroups	= static ::$filterGroups;
		$filterGroups['frequenceFilterGroup'][]	= ["name"			=>	"CodePlanType",
													"filterName"	=>	"Plan Type",
													];
		return view ( 'fp.allocateplan',
				['filters'		=> $filterGroups,
		]);
	}
	
	public function allocateforecast(){
		$filterGroups	= static ::$filterGroups;
		$filterGroups['frequenceFilterGroup'][]	= ["name"			=>	"CodeForecastType",
													"filterName"	=>	"Forecast Type",
		];
		return view ( 'fp.allocateforecast', ['filters'		=> $filterGroups,]);
	}
	
	
	public function loadplan(){
		$filterGroups = array(
				'productionFilterGroup'	=>[		['name'			=>'ExtensionPhaseType',
												'independent'	=> true,
												"getMethod"		=> "getPreosPhaseType",
												'extra'			=> ["Facility","ExtensionPhaseType","IntObjectType"],
												'dependences'	=>["ObjectName"]
												],
												['name'			=>'IntObjectType',
												'independent'	=>true,
												"getMethod"		=> "getPreosObjectType",
												'extra'			=> ["Facility","ExtensionPhaseType","IntObjectType"],
												'dependences'	=> ["ObjectName"]
												]
										],
				'frequenceFilterGroup'	=> [["name"			=> "ObjectName",
											"getMethod"		=> "loadBy",
											"defaultEnable"	=> false,
											"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType","ExtensionPhaseType"]]]],
				'dateFilterGroup'		=> array(	['id'=>'date_begin','name'=>'From date'],
													['id'=>'date_end','name'=>'To date']),
				'extra' 				=> ['IntObjectType','ExtensionPhaseType'],
				'FacilityDependentMore'	=> ["ObjectName"],
		);
		
		$int_import_setting = IntImportSetting::all('ID', 'NAME');
		return view ( 'fp.loadplanforecast',['filters'=>$filterGroups, 'int_import_setting'=>$int_import_setting]);
	}
	
	public function choke(){
		$filterGroups = array(	'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableButton'			=> 	false,
						);
	
		return view ( 'fp.choke',['filters'=>$filterGroups]);
	}
}
