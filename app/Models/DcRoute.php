<?php

namespace App\Models;

class DcRoute extends EbBussinessModel
{
	protected $table = 'dc_route';
	
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function DcPoint($option=null){
		return $this->hasMany('App\Models\DcPoint', 'ROUTE_ID', 'ID');
	}
	
	public static function loadBy($sourceData){
		return static::orderBy("NAME")->get(["ID", "NAME"]);
	}
	
	public static function deleteWithConfig($mdlData) {
		$valuesIds = array_values($mdlData);
		\Helper::setGetterUpperCase();
		$dcPointData = DcPoint::whereIn("ROUTE_ID",$valuesIds)->pluck("ID")->toArray();
		DcPoint::deleteWithConfig($dcPointData);
		DcRouteUser::whereIn('ROUTE_ID', $valuesIds)->delete();
		parent::deleteWithConfig($mdlData);
	}
}
