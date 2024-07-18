<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Facility;
use App\Models\LoArea;
use App\Models\LoProductionUnit;
use App\Models\UserDataScope;
use App\Models\UserRole;
use App\Models\UserUserRole;
use Carbon\Carbon;

class AdminUserController extends CodeController
{
    public function getFirstProperty($dcTable){
        return  ['data'=>$dcTable,'title'=>'','width'=>100];
    }
    
    public function getDataSet($postData, $dcTable, $facility_id, $occur_date, $properties){
        $configuration	= isset($configuration)?$configuration:auth()->user()->getConfiguration();
        $formatting = $configuration["time"]["DATE_FORMAT_CARBON"];
        $mdlName                = $postData[config("constants.tabTable")];
        $mdl                    = "App\Models\\$mdlName";
        $dataSet                = [];
        if($mdlName == "User") {
            $role_id = $postData["UserRole"];
            //$facility_id = $postData["Facility"];
            $facility_id = '';
            $user = User::getTableName();
            $userUserRole = UserUserRole::getTableName();
            $query = User::orderBy("$user.USERNAME");
            /* with(["UserUserRole.UserRole","UserDataScope"])
// 		                ->distinct("$user.ID")
		                ->orderBy("$user.USERNAME"); */
            if ($role_id > 0) {
            	/* $query->join($userUserRole, function ($query) use ($user, $userUserRole, $role_id) {
            		$query->on("$userUserRole.USER_ID", '=', "$user.ID");
            		$query->where("$userUserRole.ROLE_ID", '=', $role_id);
            	}); */
            	
            	$query->whereHas("UserUserRole", function ($query) use ( $userUserRole, $role_id) {
            			$query->where("$userUserRole.ROLE_ID", '=', $role_id);
            	});
            }
            $dataSet = $query->get();

            $dataSet = $dataSet->filter(function ($data, $key) use ($role_id, $userUserRole, $facility_id,$dcTable,$formatting) {
            	$userRoles 					= UserUserRole::where("USER_ID","=",$data->ID)->get();
//                 $userRoles 					= $data->UserUserRole;
                $sRole 						= $userRoles->pluck("UserRole.NAME")->toArray();
                $sRole_Id 					= $userRoles->pluck("UserRole.ID")->toArray();
                $data->ROLES 				= implode("<br>", $sRole);
                $data->ROLES_ID 			= $sRole_Id;

//                 $userScope 					= $data->UserDataScope;
                $userScope 					= UserDataScope::where("USER_ID","=",$data->ID)->first();
                if ($userScope) {
	                $facilityIds			= $this->getUnitIds($data,$userScope->FACILITY_ID, "\App\Models\Facility","FACILITY");
	                $data->FACILITIES 		= $facilityIds;
                    $data->DATA_SCOPE_FA    = explode(",",$userScope->FACILITY_ID);
                    $data->FACILITY         = $this->getNameFacility($userScope->FACILITY_ID,"\App\Models\Facility","FACILITY");
	                $pus 					= $this->getUnitIds($data,$userScope->PU_ID, "\App\Models\LoProductionUnit","PRODUCTION_UNIT");
	                $areas 					= $this->getUnitIds($data,$userScope->AREA_ID, "\App\Models\LoArea","AREA");
	                if ($facility_id > 0 && count($facilityIds)>0){
	                	$index = array_search($facility_id, $facilityIds);
	                	if ($index === FALSE ) return false;
	                }
               	}
               	else{
               		$data->PRODUCTION_UNIT = 0;
               		$data->AREA = 0;
               		$data->FACILITY = 0;
               	}

               	if ($data->PASSWORD_CHANGED != null){
                    $parse = Carbon::parse($data->PASSWORD_CHANGED);
                    $data->PASSWORD_CHANGED = $parse->format($formatting." H:i:s");
                }else{
                    $parse = Carbon::parse('1970/01/01 00:00:00');
                    $data->PASSWORD_CHANGED = $parse->format($formatting." H:i:s");
                }
                //$data->PASSWORD = $data->password;
                $status = [];
                $now = date('d/m/Y', strtotime(Carbon::now('Europe/London')));
                $status[] = ($data->ACTIVE == 1) ? 'Active' : 'Not Active';
                //if (date('d/m/Y', strtotime($data->expire_date)) < $now) $status[] = 'Expired';
                $data->ACTIVE = implode(", ", $status);
                $data->expire_date = date('Y/m/d', strtotime($data->expire_date));
                $data->CODE = "Edit User";
                if(config('database.default')==='oracle') $data->username = $data->USERNAME;
                $data->$dcTable = $data->ID;
                return true;
            });
            $dataSet =  $dataSet->values();
        }

        return ['dataSet' => $dataSet];
    }
    

    public function getUnitIds($data,$idsString, $eloquent,$field){
    	$units = [];
    	if ($idsString && $idsString!= 0 && $idsString != "0" && $idsString != "") {
    		$units = explode(",", str_replace('*','',$idsString));
    		if (count($units) > 0) {
    			$unitEntries = $eloquent::whereIn("ID", $units)->select("NAME")->get();
    			$unitEntries = $unitEntries->pluck("NAME")->toArray();
    			if ($unitEntries && count($unitEntries) > 0) {
    				$data->$field = implode("<br>", $unitEntries);
    				return $units;
    			}
    		}
    	}
    	$data->$field = 0;
    	return $units;
    }

    public function getNameFacility($str_read,$model){
        $name_facility = [];
        $arr = explode(",",$str_read);
        if ($str_read != ''){
            foreach ($arr as $value){
                if(strpos($value,"*") == false){
                    $val = $model::where("ID", $value)->select("NAME")->first();
                    $name = is_object($val) ? $val->NAME : '';
                }else{
                    $value_rep = str_replace('*','', $value);
                    $val = $model::where("ID", $value_rep)->select("NAME")->first();
                    $name = is_object($val) ? $val->NAME." (Read-only)" : '';
                }
                $name_facility[]=$name;
            }
        }
        return implode("<br>",$name_facility);
    }
}