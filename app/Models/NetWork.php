<?php

namespace App\Models;
use App\Models\DynamicModel;

class NetWork extends DynamicModel
{
	protected $table = 'NETWORK';
	
	public $timestamps = false;
	
	protected $fillable  = ['ID', 'CODE', 'NAME', 'START_DATE', 'END_DATE', 'NETWORK_TYPE', 'XML_CODE', 'DATA_CONNECTION'];
	
	
	public function AllocJob($option=null){
		return AllocJob::where("NETWORK_ID","=",$this->ID)->select('ID', 'NAME')->get();
	}
	
	public static function getDataWithNetworkType($network_type = 0){
        \Helper::setGetterUpperCase();
        $user = auth()->user();
		$sql = "select ID, NAME from network where network_type=$network_type ".($user->isAdmin()?"":"and ID in (select distinct user_role_dashboard.dashboard_id from user_role_dashboard, user_user_role
where user_user_role.user_id={$user->ID}
and user_user_role.role_id=user_role_dashboard.role_id)")." order by NAME";
        $ds = \DB::select($sql);
		return $ds;
	}

    public static function loadBy($sourceData){
        if ($sourceData!=null&&is_array($sourceData)) {
            \Helper::setGetterUpperCase();
            $network = static::where(['NETWORK_TYPE'=>1])->get(['ID', 'NAME']);
            foreach ($network as $n){
                $count = AllocJob::where(['NETWORK_ID'=>$n->ID])->count();
                if($count > 0){
                    $n->NAME = $n->NAME.' ('.$count.')';
                }else{
                    $n->NAME = $n->NAME;
                }
            }
            return $network;
        }
        return null;
    }
}
