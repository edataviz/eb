<?php

namespace App\Http\Controllers\DataVisualization;
use App\Models\Dashboard;
use App\Models\DashboardGroup;
use App\Http\Controllers\CodeController;

class DashboardController extends CodeController {
	
	public function all(){
		$originAttrCase = \Helper::setGetterUpperCase();
		$dashboard = Dashboard::getTableName ();
		$dashboardGroup = DashboardGroup::getTableName ();
		$results 		= Dashboard::leftjoin($dashboardGroup, "$dashboard.DASHBOARD_GROUP_ID", '=', "$dashboardGroup.ID")
		->orderBy("$dashboardGroup.NAME", "DESC")
		->orderBy("$dashboard.NAME")
		->get([
			"$dashboard.ID",
			"$dashboard.NAME",
			"$dashboard.TYPE",
			"$dashboard.BACKGROUND",
			"$dashboard.CONFIG",
			"$dashboard.IS_DEFAULT",
			"$dashboard.DASHBOARD_GROUP_ID",
			"$dashboardGroup.NAME AS GROUP_NAME",			
		]);
    	return response()->json($results);
	}
}
