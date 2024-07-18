<?php

namespace App\Http\Controllers;

use App\Models\AuditApproveTable;
use App\Models\AuditValidateTable;
use App\Models\DataTableGroup;
use App\Models\EbFunctions;
use App\Models\Facility;
use App\Models\IntMapTable;
use App\Models\LoArea;
use App\Models\LockTable;
use App\Models\LogUser;
use App\Models\LoProductionUnit;
use App\Models\User;
use App\Models\UserDataScope;
use App\Models\UserRight;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use App\Models\UserUserRole;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AdminController extends CodeController {


	public function _index() {
        $filterGroups = array(
            //'productionFilterGroup' => [],
            'enableSaveButton'		=> 	false,
            'frequenceFilterGroup'	=> [
                ["name" => "UserRole",
                'default'	=> ['ID'=>0,'NAME'=>'All'],
                "filterName"	=>"User Role"]
            ]
        );
		return view ( 'admin.usersparent', ['filters' => $filterGroups]);
	}

	public function getData(Request $request) {
		\Helper::setGetterUpperCase();
		$listControls = $request->all ();
		$result = array ();

		$records = array (
				'ID' => 0,
				'NAME' => 'All'
		);

		$LoProductionUnitID = 0;
		$LoAreaID = 0;

		foreach ( $listControls as $listControl ) {

			if(!isset($listControl['TYPE'])){

				$ID = $listControl ['ID'];

				$tmp = array ();

				$model = 'App\\Models\\' . $listControl ['ID'];

				if (isset ( $listControl ['default'] )) {
					array_push ( $tmp, $records );
				}

				if ($ID == "LoProductionUnit" || $ID == "LoArea" || $ID == "Facility") {
					if ($ID == "LoProductionUnit") {
						$tmps = $model::all ( [
								'ID',
								'NAME'
						] );
						if (! isset ( $listControl ['default'] )) {
							$LoProductionUnitID = $tmps [0]->ID;
						}
					} else {
						if ($ID == "LoArea") {
							if ($LoProductionUnitID != 0) {
								$tmps = $model::where ( [
										'PRODUCTION_UNIT_ID' => $LoProductionUnitID
								] )->select ( 'ID', 'NAME' )->get ();
							} else {
								$tmps = $model::all ( [
										'ID',
										'NAME'
								] );
							}

							if (! isset ( $listControl ['default'] )) {
								$LoAreaID = $tmps [0]->ID;
							}
						} else {
							if ($ID == "Facility") {
								if ($LoAreaID != 0) {
									$tmps = $model::where ( [
											'AREA_ID' => $LoAreaID
									] )->select ( 'ID', 'NAME' )->get ();
								} else {
									$tmps = $model::all ( [
											'ID',
											'NAME'
									] );
								}
							}
						}
					}
				} else {
					if($ID != "USER"){
						$listColumn = ['ID','NAME'];
						$tmps = $model::all ($listColumn);
						/* if($ID == 'IntObjectType'){
							$tmps = $model::where(['ACTIVE'=>1])->orderBy('ORDER','ASC')->get ($listColumn);
						}else{
							$tmps = $model::all ($listColumn);
						} */
					}else{
						$listColumn = ['ID','USERNAME'];
						$tmps = $model::all ($listColumn);
					}
				}

				foreach ( $tmps as $v ) {
					if($ID == "USER"){
						$v->NAME = $v->USERNAME;
					}
					array_push ( $tmp, $v );
				}

				$result [$listControl ['label']] = $tmp;

			}else{
				if($listControl['TYPE'] == 'DATE'){
// 					$value = Carbon::now('Europe/London');
					$value 	= Carbon::now();
					if (isset($listControl['FORMAT'])) {
						$format = $listControl['FORMAT'];
						$value	=$value->format($format);
						$listControl['default'] = $value;
					}
					else $listControl['default'] = date('m/d/Y', strtotime($value));
					$result [$listControl ['ID']] = $listControl;
				}else{
					$result [$listControl ['ID']] = $listControl;
				}
			}
		}

		return response ()->json ( array (
				'result' => $result
		) );
	}
	public function selectedID(Request $request) {
		\Helper::setGetterUpperCase();
		$id = $request->input ( 'ID' );
		$table = $request->input ( 'TABLE' );

		$model = 'App\\Models\\' . $table;

		if ($id != 0) {
			if ($table == "LoArea") {
				$where = [
						'PRODUCTION_UNIT_ID' => $id
				];
			} else if ($table == "Facility") {
				$where = [
						'AREA_ID' => $id
				];
			}

			$tmps = $model::where ( $where )->select ( 'ID', 'NAME' )->get();
		} else {
			$tmps = $model::all ( [
					'ID',
					'NAME'
			] );
		}

		return response ()->json ( array (
				'result' => $tmps
		) );
	}

	// Start admin user
	public function getUsersList(Request $request) {
		\Helper::setGetterUpperCase();
		$role_id = $request->input ( 'ROLES_ID' );
		$production_unit_id = $request->input ( 'PRODUCTION_ID' );
		$area_id = $request->input ( 'AREA_ID' );
		$facility_id = $request->input ( 'FACILITY' );

		$result = array();

		$userDataScope 		= UserDataScope::getTableName();
		$loProductionUnit 	= LoProductionUnit::getTableName();
		$loArea 			= LoArea::getTableName();
		$facility 			= Facility::getTableName();
		$user				= User::getTableName();
		$userUserRole 		= UserUserRole::getTableName();
		$userRole 			= UserRole::getTableName();
		
		if (config('database.default')==='oracle') {
			$listColumn = [
					"$user.ID", "$user.USERNAME","$user.PASSWORD_CHANGED","$userDataScope.PU_ID" , "$userDataScope.AREA_ID",
					"$userDataScope.FACILITY_ID", "$user.ID AS ROLE",
	// 				"$loProductionUnit.NAME AS PU_NAME","$loArea.NAME AS AREA_NAME","$facility.NAME AS FACILITY_NAME",
					"$user.EXPIRE_DATE", "$user.ACTIVE", "$user.ACTIVE AS STATUS", "$user.expire_date AS expire_status"
			];
			$query = User::leftJoin($userDataScope, "$user.id", "=", "$userDataScope.user_id")
				// 		->leftJoin($loProductionUnit, "$loProductionUnit.id", '=', "$userDataScope.PU_ID")
				// 		->leftJoin($loArea, "$loArea.id", '=', "$userDataScope.AREA_ID")
						->leftJoin($facility, "$userDataScope.FACILITY_ID", 'like', "$facility.ID")
						->distinct("$user.ID")
						//->groupBy("$user.ID")
						->select($listColumn)
						->orderBy ( "$user.id", 'asc' );
		}
		else{
			$listColumn = [
					"$user.ID", "$user.USERNAME","$user.PASSWORD_CHANGED","$userDataScope.PU_ID" , "$userDataScope.AREA_ID",
					"$userDataScope.FACILITY_ID", "$user.ID AS ROLE",
					"$loProductionUnit.NAME AS PU_NAME","$loArea.NAME AS AREA_NAME","$facility.NAME AS FACILITY_NAME",
					"$user.EXPIRE_DATE", "$user.ACTIVE", "$user.ACTIVE AS STATUS", "$user.expire_date AS expire_status"
			];
			$query = User::leftJoin($userDataScope, "$user.id", "=", "$userDataScope.user_id")
						->leftJoin($loProductionUnit, "$loProductionUnit.id", '=', "$userDataScope.PU_ID")
						->leftJoin($loArea, "$loArea.id", '=', "$userDataScope.AREA_ID")
						->leftJoin($facility, "$userDataScope.FACILITY_ID", 'like', "$facility.ID")
						->distinct("$user.ID")
						//->groupBy("$user.ID")
						->select($listColumn)
						->orderBy ( "$user.id", 'asc' );
		}
				

		$listData = $query->get ();

		foreach ( $listData as $data ) {
			$sRole = "";

			$subList = DB::table ( $userUserRole)
			->join ( $userRole, "$userUserRole.role_id", '=', "$userRole.id" )
			->where ( ["$userUserRole.user_id" => $data->ID] )

			->where(function($q) use ($role_id, $userUserRole) {
				$q->where(function($query) use ($role_id, $userUserRole){

					if($role_id != 0){
						$query->where(["$userUserRole.role_id" => $role_id]);
					}
				});
			})

			->select ("$userRole.NAME")
			->get();

			if(count($subList) > 0){
				foreach ($subList as $sub){
					$sRole.=($sRole==""?"":"<br>").$sub->NAME;
				}
			}

			if ($role_id > 0 && $sRole == ""){
				continue;
			}

			if ($sRole == "")
				$sRole = "(No role)";

			if ($production_unit_id != 0 && $production_unit_id != $data->PU_ID){
				continue;
			}

			if ($area_id != 0 && $area_id != $data->AREA_ID){
				continue;
			}

			if ($facility_id != 0 && $facility_id != $data->FACILITY_ID){
				continue;
			}

			if ($data->FACILITY_ID && $data->FACILITY_ID!=0 && $data->FACILITY_ID!="0") {
				$facilityIds = explode(",", str_replace('*','',$data->FACILITY_ID));
				if (count($facilityIds)>0) {
					$facilities = Facility::whereIn("ID",$facilityIds)->select("NAME")->get();
					$facilities	= $facilities->pluck("NAME")->toArray();
					if ($facilities&&count($facilityIds)>0) {
						$data->FACILITY_NAME	= implode("<br>", $facilities);
					}
				}
			}

			$data->ROLE = $sRole;

			if($data->ACTIVE == 1){
				$data->STATUS = 'Active';
			}else {
				$data->STATUS = 'Not Active';
			}

			$now = Carbon::now('Europe/London');

			if($data->EXPIRE_DATE > $now){
				$data->EXPIRE_STATUS = "";
			}else {
				$data->EXPIRE_STATUS = 'Expired';
			}

			$data->EXPIRE_DATE = date('m/d/Y',strtotime($data->EXPIRE_DATE));
			$data->PASSWORD_CHANGED = date('m/d/Y H:i:s', strtotime($data->PASSWORD_CHANGED));

			 if($data->EXPIRE_STATUS == ''){
				$data->EXPIRE_STATUS = '';
			}else {
				$data->EXPIRE_STATUS = ', '.$data->EXPIRE_STATUS;
			}

			$data->STATUS = $data->STATUS.$data->EXPIRE_STATUS;

			array_push ( $result, $data );
		}
		\Log::info($result);
		return response ()->json ( array (
				'result' => $result
		) );
	}

	public function addNewUser(Request $request){

		$data = $request->all();
		$obj = new CommonController();

        $fa_id = (isset($data['fa_id'])) ? $data['fa_id'] : [];
        $read_only = (isset($data['read_only'])) ? $data['read_only'] : [];
        if (count($read_only) > 0){
            foreach ($read_only as $value){
                $key = array_search($value, $fa_id);
                unset($fa_id[$key]);
                $fa_id[]=$value."*";
            }
        }
        sort($fa_id);
        $str_facility = implode(",",$fa_id);

		DB::beginTransaction();
		try {
			$check = User::where(['USERNAME'=>$data['username']])->get();

			if(count($check) <=0 ){

				$user = new User;
				$user->USERNAME = $data['username'];
				$user->PASSWORD = $obj->myencrypt($data['pass']);
				$user->LAST_NAME = $data['lastname'];
				$user->MIDDLE_NAME = $data['middlename'];
				$user->FIRST_NAME = $data['firstname'];
				$user->EMAIL = $data['email'];
//				$user->EXPIRE_DATE = date('Y/m/d', strtotime($data['expireDate']));
				$user->ACTIVE = $data['active'];
				$user->save();

				$userDataScope = new UserDataScope;
				$userDataScope->USER_ID = $user->ID;
				$userDataScope->PU_ID = ($data['pu_id']==0)?null:$data['pu_id'];
				$userDataScope->AREA_ID = ($data['area_id'] == 0)?null:$data['area_id'];
				$userDataScope->FACILITY_ID = $str_facility;
				UserDataScope::insert(json_decode(json_encode($userDataScope), true));

				$ro = $data['roles'];
				if(!empty($ro)){
                    $roles = explode(',',$ro);
                    foreach ($roles as $role){
                        $userUserRole = new UserUserRole;
                        $userUserRole->USER_ID = $user->ID;
                        $userUserRole->ROLE_ID = $role;
                        UserUserRole::insert(json_decode(json_encode($userUserRole), true));
                    }
                }
			}
		} catch(\Exception $e)
		{
			DB::rollback();
		}

		DB::commit();

		return response ()->json ( array (
				'Message' => 'Insert successfully'
		) );
	}

	public function deleteUser(Request $request){

		$id = $request->input('ID');
		$error	 = false;
		DB::beginTransaction();
		try {
			UserDataScope::where(['USER_ID'=>$id])->delete();

			UserUserRole::where(['USER_ID'=>$id])->delete();

			User::where(['ID'=>$id])->delete();

		} catch(\Exception $e){
			DB::rollback();
			$error = true;
			if($e){
				$error = $e->getMessage();
				\Log::info($error);
				\Log::info($e->getTraceAsString());
			}
		}
		DB::commit();

		if ($error) return response ()->json ( array (
				'Message' => "Delete error: $error"
		) );

		return response ()->json ( array (
				'Message' => 'Delete successfully'
		) );
	}

	public function updateUser(Request $request){

		$data = $request->all();
		$obj = new CommonController();

		$fa_id = (isset($data['fa_id'])) ? $data['fa_id'] : [];
		$read_only = (isset($data['read_only'])) ? $data['read_only'] : [];
		if (count($read_only) > 0){
            foreach ($read_only as $value){
                $key = array_search($value, $fa_id);
                unset($fa_id[$key]);
                $fa_id[]=$value."*";
            }
        }
        sort($fa_id);
        $str_facility = implode(",",$fa_id);

		DB::beginTransaction();
		$msg = 'no update to database';
		try {
			$isUpdate 				= $data['isUpdate'];
			$doUpdate				= false;
			$userName				= array_key_exists('username', $data)?$data['username']:null;
			$id                     = $data['ID'];
			if ($userName&&$userName!="") {
				$attributes			= ["username"	=> $userName];
				$values				= $attributes;
				//$user				= User::updateOrInsert($attributes, $values )->first();
                $user				= User::find($id);
				$user->USERNAME 	= $userName;
				$user->LAST_NAME 	= $data['lastname'];
				$user->MIDDLE_NAME 	= $data['middlename'];
				$user->FIRST_NAME 	= $data['firstname'];
				$user->EMAIL 		= $data['email'];
				//$user->EXPIRE_DATE 	= date('Y/m/d', strtotime($data['expireDate']));
				$user->ACTIVE 		= $data['active'];
				$user->save();
				if($data['pass'] != ""){
					$now = Carbon::now('Europe/London');
					$user->PASSWORD_CHANGED = date('Y-m-d H:i:s', strtotime($now));
					$user->PASSWORD 		= $obj->myencrypt($data['pass']);
					$user->save();
				}
				$userId				= $user->ID;

				UserDataScope::where(['USER_ID'=>$userId])->delete();
				UserUserRole::where(['USER_ID'=>$userId])->delete();

                $userDataScope = new UserDataScope();
				$userDataScope->USER_ID     = $userId;
				$userDataScope->PU_ID       = ($data['pu_id']==0)?null:$data['pu_id'];
				$userDataScope->AREA_ID     = ($data['area_id'] == 0)?null:$data['area_id'];
				//$userDataScope->FACILITY_ID = ($data['fa_id'] == 0)?null:$data['fa_id'];
                $userDataScope->FACILITY_ID = $str_facility;
				UserDataScope::insert(json_decode(json_encode($userDataScope), true));

				$roles = explode(',',$data['roles']);
				if(count($roles) > 0){
					foreach ($roles as $role){
						$userUserRole = new UserUserRole;
						$userUserRole->USER_ID = $userId;
						$userUserRole->ROLE_ID = $role;
						UserUserRole::insert(json_decode(json_encode($userUserRole), true));
					}
				}
				$msg = $user->wasRecentlyCreated?'add new user successfully':'Update user successfully';
			}
			else $msg = "user name must empty";
        } catch(\Exception $e){
            DB::rollback();
            $msg = 'error when update database';
            \Log::info($e->getMessage());
            \Log::info($e->getTraceAsString());
        }
		DB::commit();
		return response ()->json ( array (
				'Message' => $msg
		) );
	}

	public function _indexRoles() {

		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);

		return view ( 'admin.roles', ['userRole'=>$userRole]);
	}

	public function editRole(Request $request){
		$data = $request->all();

		UserRole::where(['ID'=>$data['ID']])->update(['NAME'=>$data['NAME']]);

		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);

        return response ()->json ( array (
            'userRole' => $userRole
        ) );
	}

	public function addRole(Request $request){
        \Helper::setGetterUpperCase();
		$data = $request->all();

		UserRole::insert(['NAME'=>$data]);

		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);

		return response ()->json ( array (
				'userRole' => $userRole
		) );
	}

	public function deleteRole(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();

		DB::beginTransaction();
		try {

			UserRoleRight::where(['ROLE_ID'=>$data['ID']])->delete();
			UserUserRole::where(['ROLE_ID'=>$data['ID']])->delete();
			UserRole::where(['ID'=>$data['ID']])->delete();

		} catch(\Exception $e){
			DB::rollback();
		}

		DB::commit();

		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);

		return response ()->json ( array (
				'userRole' => $userRole
		) );
	}

	public function loadRightsList(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();
		$userRoleRight = UserRoleRight::getTableName();
		$userRight = UserRight::getTableName();

		$roleLeft = DB::table($userRoleRight)
		->join($userRight, "$userRoleRight.RIGHT_ID", '=', "$userRight.ID")
		->where(["$userRoleRight.ROLE_ID" => $data['ROLE_ID']])
		->select(["$userRight.ID", "$userRight.NAME","$userRoleRight.READ_ONLY","$userRoleRight.ID as USER_RR"])
		->get();

		$roleRight = DB::table($userRight)
		->whereNotExists(function($query) use ($userRoleRight, $userRight, $data){
			$query->select(DB::raw("$userRight.ID"))
				  ->from($userRoleRight)
				  ->whereRaw("$userRoleRight.RIGHT_ID = $userRight.ID")
				  ->where(["$userRoleRight.ROLE_ID"=>$data['ROLE_ID']]);
		})
		->select(["$userRight.ID", "$userRight.NAME"])->orderBy("$userRight.NAME")
		->get();
		
		$tables = \DB::select("select b.ID, a.SOURCE_ALIAS name, a.SOURCE_NAME code, b.ACCESS access from graph_data_source a left join user_role_table b on b.ROLE_ID={$data['ROLE_ID']} and a.SOURCE_NAME=b.TABLE_NAME where 1=1 order by a.SOURCE_NAME");
		//$tables = count($re)?$re[0]:[];

		return response ()->json ( array (
			'role_id' => $data['ROLE_ID'],
			'roleLeft' => $roleLeft,
			'roleRight' => $roleRight,
			'tables' => $tables
		) );
	}

	public function removeOrGrant(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();

		if($data['TYPE'] == 1) { // remove
			UserRoleRight::where(['ROLE_ID'=>$data['ROLE_ID'], 'RIGHT_ID'=> $data['RIGHT_ID']])->delete();
		}else{
			UserRoleRight::insert(['ROLE_ID'=>$data['ROLE_ID'], 'RIGHT_ID'=> $data['RIGHT_ID']]);
		}

		$userRoleRight = UserRoleRight::getTableName();
		$userRight = UserRight::getTableName();

		$roleLeft = DB::table($userRoleRight)
		->join($userRight, "$userRoleRight.RIGHT_ID", '=', "$userRight.ID")
		->where(["$userRoleRight.ROLE_ID" => $data['ROLE_ID']])
		->select(["$userRight.ID", "$userRight.NAME", "$userRoleRight.READ_ONLY"])
		->get();

// 		\DB::enableQueryLog();
		$roleRight = DB::table($userRight)
		->whereNotExists(function($query) use ($userRoleRight, $userRight, $data){
			$query->select(DB::raw("$userRight.ID"))
			->from($userRoleRight)
			->whereRaw("$userRoleRight.RIGHT_ID = $userRight.ID")
			->where(["$userRoleRight.ROLE_ID"=>$data['ROLE_ID']]);
		}) ->get();
// 		\Log::info(\DB::getQueryLog());

		return response ()->json ( array (
				'roleLeft' => $roleLeft, 'roleRight' => $roleRight
		) );
	}

	public function _indexAudittrail() {
		$userRole = UserRole::where(['ACTIVE'=>1])->get(['ID','NAME']);
		$filterGroups = array(
								'productionFilterGroup'	=> [['name'			=>'IntObjectType',
															'independent'	=>true,
// 															'default'	=> ['ID'=>0,'NAME'=>'All'],
															// 															"getMethod"		=> "getGraphObjectType",
// 															'extra'			=> ["Facility","CodeProductType","IntObjectType","ObjectDataSource"],
															'dependences'	=> [
																					["name"		=>	"ObjectDataSource"],
																				]
															]],
								'dateFilterGroup'		=> array(
																['id'=>'date_begin','name'=>'From Date'],
																['id'=>'date_end','name'=>'To Date'],
															),
								'frequenceFilterGroup'	=> [
															["name"			=> "ObjectDataSource",
															"getMethod"		=> "loadBy",
															"filterName"	=>	"Table Name",
															"source"		=> ['productionFilterGroup'=>["IntObjectType"]]]
								],
								'enableSaveButton'		=> 	false,
		);
		return view ( 'admin.audittrail',['filters'=>$filterGroups,
										'userRole'=>$userRole
		]);
	}

	public function _indexValidatedata(){

		/*$filterGroups = array(
		    'productionFilterGroup'	=> [],
            'frequenceFilterGroup'		=> [
                [   "name"			=>	"DataTableGroup",
                    "filterName"	=>	'Group(<a href="/am/editGroup">Config</a>)',
                ],
                [
                    "name"			=>	"IntObjectType",
                    "filterName"	=>	"Object Type",
                    'default'		=>['ID'=>0,'NAME'=>'All']
                ]
            ],
            'enableButton' => false
        );*/

        $filterGroups = array(
            'productionFilterGroup'	=> [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup'		=> [
                [
                    "name"			=>	"DataTableGroup",
                    "filterName"	=>	'Group (<a href="/am/editGroup">Config</a>)',
                ]
            ],
            'enableButton' => false
        );

		return view ( 'admin.validatedata',['filters'=>$filterGroups]);
	}

	public function loadValidateData(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();

		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();

		$intMapTable = IntMapTable::getTableName();
		$auditValidateTable = AuditValidateTable::getTableName();

		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}

// 		\DB::enableQueryLog();
		$loadValidateData = DB::table($intMapTable)
		->leftjoin($auditValidateTable, function ($join) use ($intMapTable, $auditValidateTable, $facility_id){
			$join->on("$intMapTable.TABLE_NAME", '=', "$auditValidateTable.TABLE_NAME")
			->where("$auditValidateTable.FACILITY_ID", '=', $facility_id);
		})
		->where(function($q) use ($intMapTable, $objType_id) {
			if($objType_id != 0){
				$q->where(["$intMapTable.OBJECT_TYPE" => $objType_id]);
			}
		})
		->select(["$auditValidateTable.ID AS T_ID", "$intMapTable.ID", "$intMapTable.TABLE_NAME", "$intMapTable.FRIENDLY_NAME", "$auditValidateTable.DATE_FROM", "$auditValidateTable.DATE_TO"])
		->orderBy('TABLE_NAME')
		->get();
// 		\Log::info(\DB::getQueryLog());

		foreach ($loadValidateData as $v){

			if($group){

				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}

			$v->T_ID = ($v->T_ID?"checked":"");

			array_push($result, $v);
		}

		return response ()->json ( array (
				'result' => $result
		) );
	}

    public function loadValidateData2(Request $request){
        \Helper::setGetterUpperCase();
        $data = $request->all();
        $group_id = $data['GROUP_ID'];
        $dataSet = \App\Models\DataTableGroup::where("ID","=",$group_id)->select("TABLES")->first();
        $result = explode("\n",$dataSet["TABLES"]);
        $result = array_values( array_filter($result) );
        return response ()->json ( array (
            'result' => $result
        ));
    }

	public function validateData(Request $request){
		try{
			\Helper::setGetterUpperCase();
			$data 		= $request->all();
			$respond 	= \DB::transaction(function () use ($data){
//				$table_names 		= explode(',',$data['TABLE_NAMES']);
                $table_names 		= $data['TABLE_NAMES'];
				$current_username 	= '';
				$userId				= null;
				if((auth()->user() != null)){
					$current_username = auth()->user()->username;
					$userId			= auth()->user()->ID;
				}
				$dateFrom			= \Helper::parseDate($data['DATE_FROM']);
				$dateTo				= \Helper::parseDate($data['DATE_TO']);
				$facility_id 		= $data['FACILITY_ID'];
				$obj['DATE_FROM'] 	= $dateFrom;
				$obj['DATE_TO'] 	= $dateTo;
				$obj['USER_ID'] 	= $userId;
				$facilityIds		= \Helper::getFacilityIds($data,$facility_id);
				
				foreach ($table_names as $table){
					foreach ($facilityIds as $facilityId){
						$condition = array(
								'TABLE_NAME'	=>$table,
								'FACILITY_ID'	=>$facilityId
						);
						$obj['FACILITY_ID'] = $facilityId;
						$obj['TABLE_NAME'] 	= $table;
						AuditValidateTable::updateOrCreate($condition,$obj);
						$this->updateRecordStatus($data['PREFIX'],$table,$facilityId,$dateFrom,$dateTo,$current_username);
					}
				}

				$objType_id = $data['OBJECTTYPE'];
				$group_id = $data['GROUP_ID'];
				//$result = array();

				$intMapTable = IntMapTable::getTableName();
				$auditValidateTable = AuditValidateTable::getTableName();

				if($group_id != 0){
					$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
					$group = $datatablegroup->TABLES;
					$group=str_replace("\r","",$group);
					$group=str_replace(" ","",$group);
					$group=str_replace("\t","",$group);
					$group=",".str_replace("\n",",",$group).",";
				}else{
					$group = '';
				}
                $dataSet = \App\Models\DataTableGroup::where("ID","=",$group_id)->select("TABLES")->first();
                $result = explode("\n",$dataSet["TABLES"]);
                $result = array_values( array_filter($result) );
				$message = "Completed";
				return array (
							'result' 	=> $result,
							'message' 	=> $message,
					);
			});
		}
		catch (\Exception $e){
			$message = "Exception wher run transation: {$e->getMessage()}";
			\Log::info($message);
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
			$respond	= [	'result' => "error",
							'message' => $message
			];
		}
		return response ()->json ($respond);
	}

	public function _indexApprove() {

		/*$filterGroups = array('productionFilterGroup'	=> [],
							 'frequenceFilterGroup'		=> [
							 								["name"			=>	"DataTableGroup",
															"filterName"	=>	'Group(<a href="/am/editGroup">Config</a>)',
 															//'default'		=>['ID'=>0,'NAME'=>'All']
															],
															["name"			=>	"IntObjectType",
															"filterName"	=>	"Object Type",
 															'default'		=>['ID'=>0,'NAME'=>'All']
                                                        ]],
                             'enableButton' => false
						);*/
        $filterGroups = array(
            'productionFilterGroup'	=> [],
            'dateFilterGroup' => array(
                ['id' => 'date_begin', 'name' => 'From date'],
                ['id' => 'date_end', 'name' => 'To date'],
            ),
            'frequenceFilterGroup'		=> [
                ["name"			=>	"DataTableGroup",
                    "filterName"	=>	'Group (<a href="/am/editGroup">Config</a>)',
                ]
            ],
            'enableButton' => false
        );

		return view ( 'admin.approvedata',['filters'=>$filterGroups]);
	}

	public function loadApproveData(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();

		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();

		$intMapTable = IntMapTable::getTableName();
		$auditApproveTable = AuditApproveTable::getTableName();

		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}

// 		\DB::enableQueryLog();
		$loadApproveData = DB::table($intMapTable)
		->leftjoin($auditApproveTable, function ($join) use ($intMapTable, $auditApproveTable, $facility_id){
			$join->on("$intMapTable.TABLE_NAME", '=', "$auditApproveTable.TABLE_NAME")
			->where("$auditApproveTable.FACILITY_ID", '=', $facility_id);
		})
		->where(function($q) use ($intMapTable, $objType_id) {
			if($objType_id != 0){
				$q->where(["$intMapTable.OBJECT_TYPE" => $objType_id]);
			}
		})
		->select(["$auditApproveTable.ID AS T_ID", "$intMapTable.ID", "$intMapTable.TABLE_NAME", "$intMapTable.FRIENDLY_NAME", "$auditApproveTable.DATE_FROM", "$auditApproveTable.DATE_TO"])
		->orderBy('TABLE_NAME')
		->get();
// 		\Log::info(\DB::getQueryLog());

		foreach ($loadApproveData as $v){

			if($group){

				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}

			$v->T_ID = ($v->T_ID?"checked":"");

			array_push($result, $v);
		}

		return response ()->json ( array (
				'result' => $result
		) );
	}

    public function loadApproveData2(Request $request){
        \Helper::setGetterUpperCase();
        $data = $request->all();
        $group_id = $data['GROUP_ID'];
        $dataSet = \App\Models\DataTableGroup::where("ID","=",$group_id)->select("TABLES")->first();
        $result = explode("\n",$dataSet["TABLES"]);
        $result = array_values( array_filter($result) );
        return response ()->json ( array (
            'result' => $result
        ) );
    }
    
	public function ApproveData(Request $request){
		try{
			\Helper::setGetterUpperCase();
			$data = $request->all();
			$respond = \DB::transaction(function () use ($data){
				//$table_names = explode(',',$data['TABLE_NAMES']);
                $table_names = $data['TABLE_NAMES'];
				$current_username 	= '';
				$userId				= null;
				if((auth()->user() != null)){
					$current_username = auth()->user()->username;
					$userId			= auth()->user()->ID;
				}
				$dateFrom			= \Helper::parseDate($data['DATE_FROM']);
				$dateTo				= \Helper::parseDate($data['DATE_TO']);
				$facility_id 		= $data['FACILITY_ID'];
				$obj['DATE_FROM'] 	= $dateFrom;
				$obj['DATE_TO'] 	= $dateTo;
				$obj['USER_ID'] 	= $userId;
				$facilityIds		= \Helper::getFacilityIds($data,$facility_id);

				foreach ($table_names as $table){
					foreach ($facilityIds as $facilityId){
						$condition = array(
								'TABLE_NAME'	=>$table,
								'FACILITY_ID'	=>$facilityId
						);
						$obj['FACILITY_ID'] = $facilityId;
						$obj['TABLE_NAME'] 	= $table;
						AuditApproveTable::updateOrCreate($condition,$obj);
						$this->updateRecordStatus($data['PREFIX'],$table,$facilityId,$dateFrom,$dateTo,$current_username);
					}
				}
				$objType_id 	= $data['OBJECTTYPE'];
				$group_id 		= $data['GROUP_ID'];
				//$result 		= array();

				$intMapTable = IntMapTable::getTableName();
				$auditApproveTable = AuditApproveTable::getTableName();

				if($group_id != 0){
					$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
					$group = $datatablegroup->TABLES;
					$group=str_replace("\r","",$group);
					$group=str_replace(" ","",$group);
					$group=str_replace("\t","",$group);
					$group=",".str_replace("\n",",",$group).",";
				}else{
					$group = '';
				}
                $dataSet = \App\Models\DataTableGroup::where("ID","=",$group_id)->select("TABLES")->first();
                $result = explode("\n",$dataSet["TABLES"]);
                $result = array_values( array_filter($result) );
				$message = "Completed";
				return array (
								'result' 	=> $result,
								'message' 	=> $message,
						);
			});
		}
		catch (\Exception $e){
			$message = "Exception wher run transation : {$e->getMessage()}";
			\Log::info($message);
			\Log::info($e->getMessage());
			\Log::info($e->getTraceAsString());
			$respond	= [	'result' => "error",
							'message' => $message
			];
		}

		return response ()->json ($respond);
	}

	public function updateRecordStatus($value,$table,$facility_id,$dateFrom,$dateTo,$current_username=null){
		if(strtolower($table) == 'ship_cargo_blmr_data'){
			DB::update('update ship_cargo_blmr_data set RECORD_STATUS=?, STATUS_BY=?, STATUS_DATE=? where BLMR_ID in 
(select a.ID from ship_cargo_blmr a, pd_voyage b where a.VOYAGE_ID=b.ID and b.SCHEDULE_DATE between ? and ?)', 
				[$value, $current_username, Carbon::now(), $dateFrom, $dateTo]);
			return;
		}
		$mdl			= \Helper::getModelName($table);
		if (method_exists($mdl, "buildQueryBy")) {
			$rquery		= $mdl::buildQueryBy($facility_id,["$table.ID","$table.RECORD_STATUS","$table.STATUS_BY","$table.STATUS_DATE"],$dateFrom,$dateTo,
						[	"RECORD_STATUS" 	=> $value,
							"STATUS_BY" 		=> $current_username,
							"STATUS_DATE" 		=> Carbon::now(),
						]);
			return ;
		}
		
		$mtableRecord	= IntMapTable::where("TABLE_NAME",strtoupper($table))->select("MASTER_TABLE")->first();
		if($mtableRecord){
			$mtable			= $mtableRecord->MASTER_TABLE;
			$dbtable		= $mdl::getTableName();
			if (strtoupper($mtable)==strtoupper($table)) {
				$queryUpdate	= $mdl::where("$dbtable.FACILITY_ID",'=',$facility_id);
			}
			else{
				$queryUpdate	= $mdl::whereExists(function ($query) use ($mtable,$dbtable,$facility_id,$mdl){
										$query->select("$mtable.ID")
										->from($mtable)
										->where("$mtable.FACILITY_ID",'=',\DB::raw("$facility_id"));
										$relationField	= isset($mdl::$relationStatusField)?$mdl::$relationStatusField:$mdl::$idField;
										$query->where("$mtable.ID",'=',\DB::raw("$dbtable.".$relationField));
									});
			}
		}
		else{
			$queryUpdate	= app($mdl);//::where("FACILITY_ID",'=',$facility_id);
		}
		$dateColumn	=	isset($mdl::$dateField)?$mdl::$dateField:"OCCUR_DATE";
		$queryUpdate->whereDate("$table.$dateColumn" ,">=", $dateFrom)
		->whereDate("$table.$dateColumn" ,"<=", $dateTo)
		->update([	"$table.RECORD_STATUS" 	=> $value,
					"$table.STATUS_BY" 		=> $current_username,
					"$table.STATUS_DATE" 	=> Carbon::now(),
		]);
	}

	public function _indexLockData() {
		return view ( 'admin.lockdata');
	}

	public function loadLockData(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();

		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();

		$intMapTable = IntMapTable::getTableName();
		$lockTable = LockTable::getTableName();

		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();	;
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}

		$loadlockTable = DB::table($intMapTable)
		->leftjoin($lockTable, function ($join) use ($intMapTable, $lockTable, $facility_id){
			$join->on("$intMapTable.TABLE_NAME", '=', "$lockTable.TABLE_NAME")
			->where("$lockTable.FACILITY_ID", '=', $facility_id);
		})
		->where(function($q) use ($intMapTable, $objType_id) {
			if($objType_id != 0){
				$q->where(["$intMapTable.OBJECT_TYPE" => $objType_id]);
			}
		})
		->select(["$lockTable.ID AS T_ID", "$intMapTable.ID", "$intMapTable.TABLE_NAME", "$intMapTable.FRIENDLY_NAME", "$lockTable.LOCK_DATE"])
		->orderBy('TABLE_NAME')
		->get();

		foreach ($loadlockTable as $v){
			if($group){

				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}

			$v->T_ID = ($v->T_ID?"checked":"");

			array_push($result, $v);
		}

		return response ()->json ( array (
				'result' => $result
		) );
	}

	public function lockData(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();
		$table_names = explode(',',$data['TABLE_NAMES']);

		$obj['LOCK_DATE'] 	= \Helper::parseDate($data['DATE_FROM']);
		$userId				= null;
		if((auth()->user() != null)){
			$current_username = auth()->user()->username;
			$userId			= auth()->user()->ID;
		}
		$obj['USER_ID'] 	= $userId;
		$obj['FACILITY_ID'] = $data['FACILITY_ID'];
		foreach ($table_names as $table){
			$condition = array(
					'TABLE_NAME'=>$table,
					'FACILITY_ID'=>$data['FACILITY_ID']
			);
			$obj['TABLE_NAME'] = $table;
			LockTable::updateOrCreate($condition,$obj);
		}

		$objType_id = $data['OBJECTTYPE'];
		$facility_id = $data['FACILITY_ID'];
		$group_id = $data['GROUP_ID'];
		$result = array();

		$intMapTable = IntMapTable::getTableName();
		$lockTable = LockTable::getTableName();

		if($group_id != 0){
			$datatablegroup = DataTableGroup::where(['ID'=>$group_id])->select('TABLES')->first();
			$group = $datatablegroup->TABLES;
			$group=str_replace("\r","",$group);
			$group=str_replace(" ","",$group);
			$group=str_replace("\t","",$group);
			$group=",".str_replace("\n",",",$group).",";
		}else{
			$group = '';
		}

		$loadlockTable = DB::table($intMapTable)
		->leftjoin($lockTable, function ($join) use ($intMapTable, $lockTable, $facility_id){
			$join->on("$intMapTable.TABLE_NAME", '=', "$lockTable.TABLE_NAME")
			->where("$lockTable.FACILITY_ID", '=', $facility_id);
		})
		->where(function($q) use ($intMapTable, $objType_id) {
			if($objType_id != 0){
				$q->where(["$intMapTable.OBJECT_TYPE" => $objType_id]);
			}
		})
		->select(["$lockTable.ID AS T_ID", "$intMapTable.ID", "$intMapTable.TABLE_NAME", "$intMapTable.FRIENDLY_NAME", "$lockTable.LOCK_DATE"])
		->orderBy('TABLE_NAME')
		->get();

		foreach ($loadlockTable as $v){

			if($group){

				if (strpos($group,",$v->TABLE_NAME,") === false)
					continue;
			}

			$v->T_ID = ($v->T_ID?"checked":"");

			array_push($result, $v);
		}

		return response ()->json ( array (
				'result' => $result
		) );
	}

    public function _indexUserlog() {
        $filterGroups = array(
            'dateFilterGroup'       => array(['id' => 'date_begin', 'name' => 'Login from date'],['id' => 'date_end', 'name' => 'To date']),
            'enableSaveButton'		=> 	false,
            'frequenceFilterGroup'	=> [
                                        [
                                            'default'	=> ['ID'=>0,'NAME'=>'All'],
                                            "name"          => "User",
                                            "getMethod"		=> "loadBy"
                                        ]
            ]
        );
        return view ( 'admin.userlogparent', ['filters' => $filterGroups]);
    }

	public function loadUserLog(Request $request){
		\Helper::setGetterUpperCase();
		$data = $request->all();
		$date_from 	= \Helper::parseDate($data['DATE_FROM']);
		$date_to 	= \Helper::parseDate($data['DATE_TO']);
		$username = trim($data['USERNAME']);
		$result = array();

		$logUser = LogUser::getTableName();

		$loadUserLog = DB::table($logUser)
		->whereDate('LOGIN_TIME', '>=', $date_from)
		->whereDate('LOGIN_TIME', '<=', $date_to)
		->where(function($q) use ($username) {
			if($username != "All"){
				$q->where(['USERNAME' => $username]);
			}
		})
		->select(['USERNAME', 'LOGIN_TIME', 'LOGOUT_TIME', 'IP'])
		->get();

		return response ()->json ( array (
				'result' => $loadUserLog
		) );
	}

	public function _indexEditGroup() {
		$data = DataTableGroup::all(['ID', 'NAME']);

		$datatablegroup = DataTableGroup::where(['ID'=>$data[0]->ID])->select('TABLES')->first();

		return view ( 'admin.edit_data_table_group', ['datas'=>$data, 'datatablegroup'=>$datatablegroup]);
	}

	public function loadGroup(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all();
		$datatablegroup = DataTableGroup::where(['ID'=>$data['GROUP_ID']])->select('TABLES')->first();

		return response ()->json ( array (
				'result' => $datatablegroup
		) );
	}

	public function deleteGroup(Request $request) {
		$data = $request->all();
		$datatablegroup = DataTableGroup::where(['ID'=>$data['GROUP_ID']])->delete();

		$data = DataTableGroup::all(['ID', 'NAME']);

		$datatablegroup = DataTableGroup::where(['ID'=>$data[0]->ID])->select('TABLES')->first();

		return response ()->json ( array (
				'result' => $datatablegroup, 'datatablegroup'=>$data
		) );
	}

	public function saveGroup(Request $request) {
		$data = $request->all();
        $id_group = $data['GROUP_ID'];
		$condition = array(
				'ID'=>$id_group
		);

		$obj['NAME'] = $data['NAME'];
		$obj['TABLES'] = $data['TABLES'];

// 		\DB::enableQueryLog();
		//DataTableGroup::updateOrCreate($condition,$obj);
		($id_group == -1) ? DataTableGroup::firstOrCreate($obj) : DataTableGroup::updateOrCreate($condition,$obj);
// 		\Log::info(\DB::getQueryLog());

		$data = DataTableGroup::all(['ID', 'NAME']);

		$datatablegroup = DataTableGroup::where(['ID'=>$data[0]->ID])->select('TABLES')->first();

		return response ()->json ( array (
				'result' => $datatablegroup, 'datatablegroup'=>$data
		) );
	}

	public function _helpEditor() {
		\Helper::setGetterUpperCase();
		$eb_functions = EbFunctions::where('USE_FOR', 'like', '%TASK_GROUP%')->select('ID', 'CODE', 'NAME', 'LEVEL'.($this->isReservedName?'_':''), 'PARENT_CODE')->orderBy('CODE')->get()->toArray();
		return view ( 'admin.helpeditor', ['eb_functions'=>$eb_functions]);
	}

	public function getFunction(Request $request) {
		\Helper::setGetterUpperCase();
		$data = $request->all();
		$subEbFunctions = EbFunctions::where(['PARENT_CODE'=>$data['CODE']])->select('ID','CODE','NAME')->orderBy('CODE')->get();
		return response ()->json ($subEbFunctions);
	}

	public function gethelp(Request $request) {
		$data = $request->all();
		\Helper::setGetterUpperCase();
        $tmp = EbFunctions::where(['CODE'=>$data['func_code']])->select('HELP')->first();
		return response ()->json ($tmp['HELP']);
	}

	public function savehelp(Request $request) {
		$data = $request->all();
		EbFunctions::where(['CODE'=>$data['func_code']])->update(['HELP'=>$data['help']]);
		return response ()->json ("Ok");
	}
}

