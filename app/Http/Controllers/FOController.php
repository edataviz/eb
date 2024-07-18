<?php

namespace App\Http\Controllers;
 
class FOController extends EBController {
	
	public function safety(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'],
				)
		);
		return view ( 'front.safety',['filters'=>$filterGroups]);
	}
	
	public function comment(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'			=> array(	['id'=>'date_begin','name'=>'From'],
														['id'=>'date_end','name'=>'To']),
				'frequenceFilterGroup'		=> ['CodeCommentType']
		);
		return view ( 'front.comment',['filters'=>$filterGroups]);
	}
	
	public function environmental(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'			=> array(	['id'=>'date_begin','name'=>'From'],
														['id'=>'date_end','name'=>'To']),
				'frequenceFilterGroup'		=> ['CodeEnvType']
		);
		return view ( 'front.environmental',['filters'=>$filterGroups]);
	}
	
	public function equipment(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date']),
				'frequenceFilterGroup'		=> ['EquipmentGroup','CodeEquipmentType']
		);
		return view ( 'front.equipment',['filters'=>$filterGroups]);
	}
	
	public function chemical(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date']),
				'frequenceFilterGroup'		=> [ array("name"		=>"CodeInjectPoint",
														"id"		=> "CodeInjectPoint",
														'getMethod'=> "loadActive",
														'filterData'=> ["CODE as ID", "NAME"],
														"modelName"	=> "CodeInjectPoint")]
		);
		return view ( 'front.chemical',['filters'=>$filterGroups]);
	}
	
	public function personnel(){
		$filterGroups = array('productionFilterGroup'=> [],
				'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date']),
		);
		return view ( 'front.personnel',['filters'=>$filterGroups]);
	}

    public function logistic(){
        $filterGroups = array('productionFilterGroup'=> [],
                                    'dateFilterGroup'=> array(['id'=>'date_begin','name'=>'Date'])
        );
        return view ( 'front.logistic',['filters'=>$filterGroups]);
    }
}
