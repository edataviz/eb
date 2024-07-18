<?php

namespace App\Http\Controllers;
use App\Models\Tank;
use App\Models\Flow;

class ProductManagementController extends EBController {
	 
	/**
	 * Display the home page.
	 *
	 * @return Response
	 */
	function ui($view, $settings = []){
		$min = 60;
		request()->attributes->add(['ui_view' => $view]);
		\Cookie::queue(\Cookie::make('ui_view', $view, $min));
		return view($view, $settings);
	}
	public function flow() {
		$filterGroups = array('productionFilterGroup'	=> [],
							  'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'Date']),
							 'frequenceFilterGroup'		=> ['CodeReadingFrequency','CodeFlowPhase','CodeAllocType',
							 								["name"			=>	"CodePlanType",
															"filterName"	=>	"Plan Type",
 															'default'		=>['ID'=>0,'NAME'=>'All']
															],
															["name"			=>	"CodeForecastType",
															"filterName"	=>	"Forecast Type",
 															'default'		=>['ID'=>0,'NAME'=>'All']
															]]
						);
		
		return $this->ui ( 'front.flow',['filters'=>$filterGroups]);
	}
	
	public function eu() {
		$filterGroups = array('productionFilterGroup'	=>['EnergyUnitGroup'],
							  'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'Date']),
							'frequenceFilterGroup'		=> ['CodeReadingFrequency','CodeFlowPhase',
															'CodeEventType','CodeAllocType',
															["name"			=>	"CodePlanType",
															"filterName"	=>	"Plan Type",
// 															'default'		=>['ID'=>0,'NAME'=>'All']
															],
															["name"			=>	"CodeForecastType",
															"filterName"	=>	"Forecast Type",
// 															'default'		=>['ID'=>0,'NAME'=>'All']
															]]
						);
		return $this->ui ( 'front.eu',['filters'=>$filterGroups]);
	}
	
	public function storage() {
		$filterGroups = array('productionFilterGroup'	=> [],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'Date']),
								'frequenceFilterGroup'	=> ['CodeProductType',
							 								["name"			=>	"CodePlanType",
															"filterName"	=>	"Plan Type",
// 															'default'		=>['ID'=>0,'NAME'=>'All']
															],
															["name"			=>	"CodeForecastType",
															"filterName"	=>	"Forecast Type",
// 															'default'		=>['ID'=>0,'NAME'=>'All']
															]]
						);
		return $this->ui ( 'front.storage',['filters'=>$filterGroups]);
	}
	
	
	public function eutest() {
		$filterGroups = array('productionFilterGroup'	=>['EnergyUnit'],
							'dateFilterGroup'			=> array(['id'=>'date_begin','name'=>'Effective Date'],
																['id'=>'date_end','name'=>'To']),
						);
		return $this->ui ( 'front.eutest',['filters'=>$filterGroups]);
	}
	
	public function wellstatus() {
		$filterGroups = array('productionFilterGroup'	=> [
															['name'				=>'EnergyUnitGroup',
															"source"			=> "Facility",
															'extra' 			=> ['Facility',["secondary"	=> 'Facility']],
															'dependences'		=> ["EnergyUnit"]],
															"EnergyUnitGroup"	=> ['name'			=> 'EnergyUnit',
                                                                                    'default'   	=> ['ID'=>0,'NAME'=>'All'],
																					"source"		=> "EnergyUnitGroup"],
														],
								'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'Effective Date'],
																['id'=>'date_end','name'=>'To']),
				);
		return $this->ui ( 'front.wellstatus',['filters'=>$filterGroups]);
	}

    public function scssv() {
        $filterGroups = array('productionFilterGroup'	=> [
            ['name'				=>'EnergyUnitGroup',
                "source"			=> "Facility",
                'extra' 			=> ['Facility',["secondary"	=> 'Facility']],
                'dependences'		=> ["EnergyUnit"]],
            "EnergyUnitGroup"	=> ['name'			=> 'EnergyUnit',
                'default'   	=> ['ID'=>0,'NAME'=>'All'],
                "source"		=> "EnergyUnitGroup"],
        ],
            'dateFilterGroup'		=> array(['id'=>'date_begin','name'=>'Effective Date'],
                ['id'=>'date_end','name'=>'To']),
        );
        return $this->ui ( 'front.scssv',['filters'=>$filterGroups]);
    }
	
	public function quality() {
		$filterGroups = array('productionFilterGroup'	=> [],
								'frequenceFilterGroup'=> ['CodeQltySrcType'],
								'dateFilterGroup'=> array(
				 						['id'=>'cboFilterBy','name'=>'Filter by'],
										['id'=>'date_begin','name'=>'From Date'],
										['id'=>'date_end','name'=>'To Date'],
						)
		);
		return $this->ui ( 'front.quality',['filters'=>$filterGroups]);
	}
	
	public function deferment() {
		$filterGroups = array('productionFilterGroup'	=> ['CodeDeferGroupType'],
                                /*'productionFilterGroup'	=> [["name"			=> "CodeDeferGroupType",
                                                                "getMethod"		=> "loadActive"],],*/
								'frequenceFilterGroup'	=> [
															['name'			=>'IntObjectType',
															'independent'	=>true,
															'default'  		=>['ID'=>0,'NAME'=>'All']],
															],
								'dateFilterGroup'		=> array(
																['id'=>'date_begin','name'=>'From Date'],
																['id'=>'date_end','name'=>'To Date'],
															),
// 								'FacilityDependentMore'	=> ["CodeDeferGroupType"],
				
		);
		return $this->ui ( 'front.deferment',['filters'=>$filterGroups]);
	}
	
	public function ticket() {
		$filterGroups = array('productionFilterGroup'	=> [],
				'frequenceFilterGroup'	=> [	["name"			=> "Tank",
												"defaultEnable"	=> false,
												"getMethod"		=> "loadBy",
												"source"		=> ['productionFilterGroup'=>["Facility"]]],
				],
				'dateFilterGroup'=> array(
						['id'=>'date_begin','name'=>'From Date'],
						['id'=>'date_end','name'=>'To Date'],
				),
				'FacilityDependentMore'	=> ["Tank"],
		);
		$tanks	= Tank::select("ID","CODE","NAME")->get();
		$flows	= Flow::select("ID","CODE","NAME")->get();
		return $this->ui ( 'front.ticket',
				['filters'		=> $filterGroups,
				'tanks'			=> $tanks,
				'flows'			=> $flows,
		]);
	}
	
}
