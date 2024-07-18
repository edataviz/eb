<?php

namespace App\Http\Controllers;
 
class SystemConfigController extends EBController {
	
	public function tagsmapping(){
		$item			= ['ID'=>0,'NAME'=>'All'];
		$objectName		= ["ObjectName" => ["default" => $item]];
		$filterGroups = array('productionFilterGroup'	=>[
																['name'			=>'IntObjectType',
																'independent'	=> true,
																'extra'			=> ["Facility"],
																],
															],
								'frequenceFilterGroup'	=> [	["name"			=> "ObjectName",
																"getMethod"		=> "loadBy",
																"source"		=> ['productionFilterGroup'=>["Facility","IntObjectType"]],
															]],
								'FacilityDependentMore'	=> ["ObjectName"],
								'extra' 				=> ['IntObjectType',$objectName]
						);
		
		$filterGroups['productionFilterGroup'][0]["extra"][] 		= $objectName;
		$filterGroups['frequenceFilterGroup'][0]["default"] 		= $item;
		$filterGroups['frequenceFilterGroup'][0]["defaultEnable"] 	= true;
// 		$filterGroups['extra'][] 		= $objectName;
		return view ( 'front.tagsmapping',['filters'=>$filterGroups]);
	}
	
	public function routeconfig(){
		$filterGroups = array(	'productionFilterGroup'		=> [],
								'frequenceFilterGroup'		=> [	["name"			=> "DcRoute",
																	'filterName'	=> 'Route',
																	"getMethod"		=> "loadBy",
																	'dependences'	=> ["DcPoint"]],
																	["name"			=> "DcPoint",
																	'filterName'	=> 'Point',
																	"getMethod"		=> "loadBy",
																	"source"		=>  ['frequenceFilterGroup'=>["DcRoute"]]],
																]
		);
	
		return view ( 'config.routeconfig',['filters'=>$filterGroups]);
	}
}
