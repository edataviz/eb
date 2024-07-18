<?php

namespace App\Http\Controllers;
use App\Models\PdCodeContractAttribute;
use App\Models\PdCodeLoadActivity;
use App\Models\TerminalActivitySet;
use App\Models\PdDocumentSet;
use App\Models\PdReportList;

class ProductDeliveryController extends CodeController {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function demurrageebo() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],								
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableSaveButton'		=> 	false,
						);
		return view ( 'front.cargoadmin.demurrageebo',['filters'=>$filterGroups]);
	}
	
	public function cargostatus() {
		$filterGroups = array(	'productionFilterGroup'	=>[/*'Facility'	=>'Storage' */],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableSaveButton'		=> 	false,
		);
		return view ( 'front.cargomanagement.cargostatus',['filters'=>$filterGroups]);
	}
	
	public function cargoentry() {
		$filterGroups = array(	'productionFilterGroup'	=>[],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
		);
		return view ( 'front.cargoadmin.cargoentry',['filters'=>$filterGroups]);
	}
	
	public function cargonomination() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
						);
		return view ( 'front.cargoadmin.cargonomination',['filters'=>$filterGroups]);
	}
	
	public function vef() {
		$filterGroups = array(	
			'productionFilterGroup'	=>[],
			'frequenceFilterGroup'	=> [[
				"name"			=> "PdTransitCarrier",
				"filterName"	=>	"Carrier",
				"source"		=>  ['productionFilterGroup'=>["Facility"]]],
			],

			'dateFilterGroup' => array(
				['id'=>'date_begin','name'=>'From date'],
				['id'=>'date_end','name'=>'To date']),
			);
		return view ( 'front.cargoaction.vef',['filters'=>$filterGroups]);
	}
	
	public function cargoschedule() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
				'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
		);
		return view ( 'front.cargoadmin.cargoschedule',['filters'=>$filterGroups]);
	}
	
	public static function storagedisplayFilter() {
		$filterGroups = array(
							'productionFilterGroup'	=>[
														['name'			=>'Storage',
														"source"		=> "Facility",
														'dependences'	=> ["Tank"]],
														"Storage"		=> ['name'			=>'Tank',
																			"source"		=> "Storage"],
														],
							'frequenceFilterGroup'	=> [["name"			=> "PlotViewConfig",
														"getMethod"		=> "loadBy",
														"filterName"	=>	"Plot item",
														"source"		=>  ['productionFilterGroup'=>["Facility"]]],
														],
							'FacilityDependentMore'	=> [["name"			=> "PlotViewConfig",
														"source"		=> "Facility"]],
							'enableButton'			=> false,
		);
		return $filterGroups;
	}
	
	public function storagedisplay() {
		$filterGroups						= static::storagedisplayFilter();
		$filterGroups['dateFilterGroup']	= [['id'=>'date_begin','name'=>'From date'],
												['id'=>'date_end','name'=>'To date']
											];
		return view ( 'datavisualization.storagedisplay',[
				'filters'		=> $filterGroups,
// 				'editFilters'	=> $filterGroups,
		]);
	}
	
	public function cargodocuments() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
								'enableSaveButton'		=> 	false,
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
		);

		$originAttrCase = \Helper::setGetterUpperCase();
		$contractAttributes = PdReportList::orderBy('ORDER')->get();
    	\Helper::setGetterCase($originAttrCase);
		$activities = PdDocumentSet::select(['ID as SET_ID','NAME as SET_NAME'])->get();
		return view ( 	'front.cargomanagement.cargodocuments',
						['filters'			=> $filterGroups,
 						'contractAttributes'=> $contractAttributes,
						'activities'		=> $activities,
		]);
	}
	
	public function liftaccdailybalance() {
		$filterGroups = array(	'productionFilterGroup'	=>	[	 "Storage",
															],
								'frequenceFilterGroup'	=> [
															["name"			=> "PdLiftingAccount",
															"source"		=>  ['productionFilterGroup'=>["Storage"]]]
								],
										
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableSaveButton'		=> 	false,
								'FacilityDependentMore'	=> [["name"			=> "Storage",
															"source"		=> "Facility"],
															["name"			=> "PdLiftingAccount",
															"source"		=> "Storage"]
								],
		);
		return view ( 'front.cargomonitoring.liftaccdailybalance',['filters'=>$filterGroups]);
	}
	
	public function cargoplanning() {
		$filterGroups = array(	'productionFilterGroup'	=>	[	 "Storage",
															],									
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
								'enableSaveButton'		=> 	false,
								'FacilityDependentMore'	=> [["name"			=> "Storage",
															"source"		=> "Facility"],
								],
		);
		return view ( 'front.cargomonitoring.cargoplanning',['filters'=>$filterGroups]);
	}
	
	public function liftaccmonthlyadjust() {
		$filterGroups = array(	'productionFilterGroup'	=>	[	 "Storage",
															],
								'frequenceFilterGroup'	=> [
															["name"			=> "PdLiftingAccount",
															"source"		=>  ['productionFilterGroup'=>["Storage"]]]
								],
								'dateFilterGroup'		=> array(['id'	=>'date_begin',	'name'=>'From date'],
																['id'	=>'date_end',	'name'=>'To date']),
				'FacilityDependentMore'	=> [["name"			=> "Storage",
						"source"		=> "Facility"],
						["name"			=> "PdLiftingAccount",
								"source"		=> "Storage"]
				],
																// 								'enableSaveButton'		=> 	false,
		);
		return view ( 'front.cargomonitoring.liftaccmonthlyadjust',['filters'=>$filterGroups]);
	}
	
	public function contractdata() {
		$filterGroups = array(	'productionFilterGroup'		=>['Storage'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																	['id'=>'date_end','name'=>'To date']),
		);
		
		$originAttrCase = \Helper::setGetterUpperCase();
		$contractAttributes = PdCodeContractAttribute::all();
		\Helper::setGetterCase($originAttrCase);
		return view ( 'front.contract.contractdata',['filters'=>$filterGroups,
													'contractAttributes'=>$contractAttributes
		]);
	}
	
	public function contractcalculate() {
		$filterGroups = array(
						'frequenceFilterGroup'		=> array([	'id'			=>'PdContract',
																'name'			=>'PdContract',
																'filterName'	=>'Contract',
																'getMethod'		=>'getByDateRange',
																'source'		=>['dateFilterGroup'=>['date_begin','date_end']]
						]),
						'dateFilterGroup'			=> array('date_begin'	=> [	'id'			=>'date_begin',
																					'name'			=>'From date',
																					'dependences'	=>['PdContract'],
																					'extra'			=>['date_end','PdContract']
																				],
															'date_end'		=> [	'id'			=>'date_end',
																					'name'			=>'To date',
																					'dependences'	=>['PdContract'],
																					'extra'			=>['date_begin','PdContract']
																				],
						),
						'enableButton'	=>false
		);
		return view ( 'front.contract.contractcalculate',['filters'=>$filterGroups,
		]);
	}
	
	public function contracttemplate() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
		);
		
		$originAttrCase = \Helper::setGetterUpperCase();
		$contractAttributes = PdCodeContractAttribute::all();
		\Helper::setGetterCase($originAttrCase);
		return view ( 'front.contract.contracttemplate',['filters'=>$filterGroups,
				'contractAttributes'=>$contractAttributes
		]);
	}
	
	public function contractprogram() {
		$filterGroups = array(	'productionFilterGroup'	=> ['Storage'],
								'dateFilterGroup'		=> array(	['id'=>'date_begin','name'=>'From date'	],
																	['id'=>'date_end',	'name'=>'To date'	]),
		);
		return view ( 'front.contract.contractprogram',['filters'=>$filterGroups]);
	}
	
	public function cargovoyage() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'From date'],
																['id'=>'date_end','name'=>'To date']),
		);
	
		return view ( 'front.cargoaction.cargovoyage',['filters'=>$filterGroups,
		]);
	}
	
	public function cargoload() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
				'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
		);

		$originAttrCase = \Helper::setGetterUpperCase();
		$contractAttributes = PdCodeLoadActivity::orderBy('ORDER')->get();
		\Helper::setGetterCase($originAttrCase);
		$activities = TerminalActivitySet::where('LOAD_UNLOAD','=',1)->select(['ID as SET_ID','NAME as SET_NAME'])->get();
		return view ( 'front.cargoaction.cargoload',['filters'=>$filterGroups,
				'contractAttributes'=>$contractAttributes,
				'activities'=>$activities,
		]);
	}
	public function cargounload() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
				'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
		);

		$originAttrCase = \Helper::setGetterUpperCase();
		$contractAttributes = PdCodeLoadActivity::orderBy('ORDER')->get();
		\Helper::setGetterCase($originAttrCase);
		$activities = TerminalActivitySet::where('LOAD_UNLOAD','=',2)->select(['ID as SET_ID','NAME as SET_NAME'])->get();
		return view ( 'front.cargoaction.cargounload',['filters'=>$filterGroups,
				'contractAttributes'=>$contractAttributes,
				'activities'=>$activities,
		]);
	}
	
	public function cargomeasurement() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
				'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
		);
	
		return view ( 'front.cargoaction.cargomeasurement',['filters'=>$filterGroups,
		]);
	}
	
	public function voyagemarine() {
		$filterGroups = array(	'productionFilterGroup'	=>['Storage'],
				'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
						['id'=>'date_end','name'=>'To date']),
		);
	
		return view ( 'front.cargoaction.voyagemarine',['filters'=>$filterGroups,
		]);
	}
	
	public function voyageground() {
		$filterGroups = array(	'productionFilterGroup'		=>['Storage'],
								'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'From date'],
																		['id'=>'date_end','name'=>'To date']),
		);
	
		return view ( 'front.cargoaction.voyageground',['filters'=>$filterGroups,
		]);
	}
	
	public function voyagepipeline() {
		$filterGroups = array(	'productionFilterGroup'		=>['Storage'],
								'dateFilterGroup'			=> array(['id'	=>'date_begin','name'=>'From date'],
																		['id'=>'date_end','name'=>'To date']),
		);
	
		return view ( 'front.cargoaction.voyagepipeline',['filters'=>$filterGroups,
		]);
	}
	
	
	public function shipblmr() {
		$filterGroups = array(	'productionFilterGroup'		=>['Storage'],
								'dateFilterGroup'			=> array(['id'	=>'date_begin',	'name'=>'From date'],
																	['id'	=>'date_end',	'name'=>'To date']),
		);
	
		return view ( 'front.cargoaction.shipblmr',['filters'=>$filterGroups,
		]);
	}
	
}
