<?php namespace App\Models;

use App\Models\DateTimeFormat;
use App\Models\UserRight;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use App\Models\UserUserRole;
use App\Models\UserWorkspace;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends EbBussinessModel implements AuthenticatableContract, CanResetPasswordContract,JWTSubject {

	use Authenticatable, CanResetPassword;
	
	public $timestamps = false;
	protected $primaryKey = 'ID';
	protected $user_id_col = 'USER_ID';
	protected $roles = null;
	protected $rolesScope = null;
	protected $userReports = null;
	const MASTER_RIGHTS = '_ALL_';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'USER';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];
	protected $fillable = ['username', 'email', 'password'];
	

	public function __construct(array $attributes = []) {
		$this->isReservedName = config('database.default')==='oracle';
		parent::__construct($attributes);
	}

    public static function updateOrInsert($attributes, $values){}

    public function userWorkspace()
	{
		return $this->hasOne('App\Models\UserWorkspace',$user_id_col,$primaryKey);
	}
	
	public function workspace()
	{
// 		\DB::enableQueryLog();
	
		$wp = UserWorkspace::join( $this->table, $this->table.'.ID', '=', 'USER_WORKSPACE.USER_ID')
		->join('FACILITY', 'USER_WORKSPACE.W_FACILITY_ID', '=', 'FACILITY.ID')
		->join('LO_AREA', 'FACILITY.AREA_ID', '=', 'LO_AREA.ID')
		->join('LO_PRODUCTION_UNIT', 'LO_AREA.PRODUCTION_UNIT_ID', '=', 'LO_PRODUCTION_UNIT.ID')
		->where( $this->table.'.ID', '=', $this->ID)
		->select('USER_WORKSPACE.*','FACILITY.AREA_ID', 'LO_AREA.PRODUCTION_UNIT_ID')
		->get()->first();
		
		if ($wp) {
			$now		= Carbon::now();
			$shouldSave	= false;
			if (!$wp->W_DATE_BEGIN) {
				$wp->W_DATE_BEGIN = $now;
				$shouldSave	= true;
			}
			if (!$wp->W_DATE_END){
				$wp->W_DATE_END = $now;
				$shouldSave	= true;
			}
			if ($shouldSave)	$wp->save();
		}
		
// 		\Log::info(\DB::getQueryLog());
	
		return $wp;
	}
	
	public function saveWorkspace($date_begin,$facility_id,$date_end=false)
	{
		// 		\DB::enableQueryLog();
		$columns = ['USER_ID'=>$this->ID];
		$newData = ['USER_ID'=>$this->ID,'USER_NAME'=>$this->username];
		if ($date_begin) {
 			$date_begin = \Helper::parseDate($date_begin);
			$newData['W_DATE_BEGIN']=$date_begin;
		}
		else 
			$newData['W_DATE_BEGIN']=null;
        $facility_id = is_numeric($facility_id)?$facility_id:0;
		if ($facility_id) $newData['W_FACILITY_ID']=$facility_id;
		if ($date_end) {
 			$date_end 	= \Helper::parseDate($date_end);
 			$newData['W_DATE_END']=$date_end;
		}
		else
			$newData['W_DATE_END']=null;
		return  UserWorkspace::updateOrCreate($columns, $newData);
	}
	
	public function saveDateTimeFormat($dateformat,$timeformat){
		$columns = ['USER_ID'=>$this->ID];
		$newData = ['USER_ID'=>$this->ID,'USER_NAME'=>$this->username];
		if ($dateformat) $newData['DATE_FORMAT']	=	$dateformat;
		if ($timeformat) $newData['TIME_FORMAT']	=	$timeformat;
		return  UserWorkspace::updateOrCreate($columns, $newData);
	}
	
	public function saveNumberFormat($numberformat){
		$columns = ['USER_ID'=>$this->ID];
		$newData = ['USER_ID'=>$this->ID,'USER_NAME'=>$this->username];
		if (array_key_exists('DECIMAL_MARK', $numberformat)) $newData['DECIMAL_MARK']	=	$numberformat['DECIMAL_MARK'];
		return  UserWorkspace::updateOrCreate($columns, $newData);
	}
	
	
	
	public function configuration(){
		$row	= 	UserWorkspace::where('USER_ID','=',$this->ID)
						->select('DATE_FORMAT','TIME_FORMAT','DECIMAL_MARK')
						->first();
		$formatSetting = [];
		$formatSetting['DATE_FORMAT'] 	= $row&&$row->DATE_FORMAT?$row->DATE_FORMAT:	DateTimeFormat::$defaultFormat['DATE_FORMAT'];
		$formatSetting['TIME_FORMAT'] 	= $row&&$row->TIME_FORMAT?$row->TIME_FORMAT:	DateTimeFormat::$defaultFormat['TIME_FORMAT'];
		$formatSetting['DECIMAL_MARK'] 	= $row&&$row->DECIMAL_MARK?$row->DECIMAL_MARK:	DateTimeFormat::$defaultFormat['DECIMAL_MARK'];
		return $formatSetting;
	}
	
	public function getConfiguration()
	{
// 		$formatSetting 		= 	$this->configuration();//session('configuration');
		$formatSetting 		= 	session('configuration');
		$formatSetting 		= 	$formatSetting?$formatSetting:DateTimeFormat::$defaultFormat;
		$dateFormat 		= 	$formatSetting['DATE_FORMAT']?$formatSetting['DATE_FORMAT']:	DateTimeFormat::$defaultFormat['DATE_FORMAT'];
		$timeFormat 		= 	$formatSetting['TIME_FORMAT']?$formatSetting['TIME_FORMAT']:	DateTimeFormat::$defaultFormat['TIME_FORMAT'];
		$decimalMarkFormat 	= 	$formatSetting['DECIMAL_MARK']?$formatSetting['DECIMAL_MARK']:	DateTimeFormat::$defaultFormat['DECIMAL_MARK'];
		$lowerDateFormat	= 	strtolower($dateFormat);
		$carbonFormat		= 	\Helper::convertDate2CarbonFormat($dateFormat);
		$jqueryFormat		= 	\Helper::convertDate2JqueryFormat($dateFormat);
		$pickerTimeFormat	= 	\Helper::convertTime2PickerFormat($timeFormat);
		$timeFormatSet =  [
				'DATE_FORMAT'				=>		$dateFormat,//'MM/DD/YYYY',
				'TIME_FORMAT'				=>		$timeFormat,//'hh:mm A',
				'DATETIME_FORMAT'			=>		"$dateFormat $timeFormat",// 'MM/DD/YYYY HH:mm',
				'DATE_FORMAT_UTC'			=>		'YYYY-MM-DD',
				'TIME_FORMAT_UTC'			=>		'hh:mm:ss',
				'DATETIME_FORMAT_UTC'		=>		'YYYY-MM-DD HH:mm:ss',
				'DATE_FORMAT_CARBON'		=>		$carbonFormat//'m/d/Y',
		];
		
		$picker =  [
				'DATE_FORMAT'			=>		$lowerDateFormat,//'mm/dd/yyyy',
				'TIME_FORMAT'			=>		$pickerTimeFormat,//'HH:ii P',
				'DATETIME_FORMAT'		=>		"$lowerDateFormat $pickerTimeFormat",/* strtolower($timeFormatSet['DATETIME_FORMAT']),// *///'mm/dd/yyyy hh:ii',
				'DATE_FORMAT_UTC'		=>		'mm/dd/yyyy',
				'TIME_FORMAT_UTC'		=>		'hh:ii:ss',
				'DATETIME_FORMAT_UTC'	=>		'mm/dd/yyyy hh:ii',
				'DATE_FORMAT_JQUERY'	=>		$jqueryFormat//'mm/dd/yy',
		];
		$sample = DateTimeFormat::getSample($formatSetting);
		
		$numberFormat = ['DECIMAL_MARK' =>$decimalMarkFormat];
		return [
				'time'				=>	$timeFormatSet,
				'picker'			=>	$picker,
				'number'			=>	$numberFormat,
				'sample'			=>	$sample,
				'defaultDashboard'	=>	$this->DASHBOARD_ID,
				'chatEnable'		=>	false,
		];
	}
	
	
	

	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function UserDataScope()
	{
		return $this->hasOne('App\Models\UserDataScope', $this->user_id_col, $this->primaryKey);
	}
	
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function role(){
		if($this->rolesScope) return $this->rolesScope;
		$rows = $this->getUserRoles();
		$rs = $rows?$rows->map(function ($item, $key) {
				    			return $item->CODE;
					})->toArray():[];
		$rs = array_unique($rs);
		$this->rolesScope = $rs;
		return $rs;
	}
	
	public function getUserRoles(){
		if($this->roles) return $this->roles;
		$user_user_role = UserUserRole::getTableName();
		$user_role_right = UserRoleRight::getTableName();
		$user_right = UserRight::getTableName();
		
		$rows	= UserUserRole::join($user_role_right,"$user_user_role.ROLE_ID", '=', "$user_role_right.ROLE_ID")
							->join($user_right,"$user_right.ID", '=', "$user_role_right.RIGHT_ID")
							->where("$user_user_role.USER_ID",$this->ID)
							->select("$user_right.CODE","$user_user_role.ROLE_ID","$user_role_right.READ_ONLY")
							->distinct()
							->get();
		$this->roles = $rows;
		return $rows;
	}

	public function getUserReports($groupId = false){
		if($this->userReports){
			if($groupId) return $this->userReports->where('GROUP_ID', $groupId);
			return $this->userReports;
		}
		$originAttrCase = \Helper::setGetterUpperCase();
		$user_user_role = UserUserRole::getTableName();
		$user_role_report = UserRoleReport::getTableName();
		$report = RptReport::getTableName();
		$reportGroup = RptGroup::getTableName();
		$where = ["$report.ACTIVE" => 1, "$reportGroup.ACTIVE" => 1];
		if($groupId) $where["$report.GROUP_ID"] = $groupId;
		$rows = RptReport::join($reportGroup,"$reportGroup.ID", '=', "$report.GROUP_ID");
		if(!$this->isAdmin()){
			$rows = $rows->join($user_role_report,"$user_role_report.REPORT_ID", '=', "$report.ID")
			->join($user_user_role,"$user_user_role.ROLE_ID", '=', "$user_role_report.ROLE_ID");
			$where["$user_user_role.USER_ID"] = $this->ID;
		}
		$rows = $rows->where($where)
			->select("$report.*", "$reportGroup.NAME as GROUP_NAME") 
			->distinct()
			->orderBy('GROUP_NAME')
			->orderBy("$report.NAME")
			->get();
		\Helper::setGetterCase($originAttrCase);
		if(!$groupId) $this->userReports = $rows;
		return $rows;
	}

	protected $userCharts = null;
	public function getUserCharts($groupId = false){
		$sql = "select id,name from chart_group where active=1
		and exists (select 1 from adv_chart, user_role_chart, user_user_role
		where user_user_role.user_id={$this->ID}
		and user_user_role.role_id=user_role_chart.role_id
		and user_role_chart.chart_id=adv_chart.id 
		and adv_chart.group_id=chart_group.id
		and adv_chart.active=1)
		order by name
		";
		if($this->userCharts){
			if($groupId) return $this->userCharts->where('GROUP_ID', $groupId);
			return $this->userCharts;
		}
		$originAttrCase = \Helper::setGetterUpperCase();
		$user_user_role = UserUserRole::getTableName();
		$user_role_chart = UserRoleChart::getTableName();
		$chart = AdvChart::getTableName();
		$chartGroup = ChartGroup::getTableName();
		$where = ["$chart.ACTIVE" => 1];
		if($groupId) $where["$chart.GROUP_ID"] = $groupId;
		$rows = RptChart::join($chartGroup,"$chartGroup.ID", '=', "$chart.GROUP_ID");
		if(!$this->isAdmin()){
			$rows = $rows->join($user_role_chart,"$user_role_chart.CHART_ID", '=', "$chart.ID")
			->join($user_user_role,"$user_user_role.ROLE_ID", '=', "$user_role_chart.ROLE_ID");
			$where["$user_user_role.USER_ID"] = $this->ID;
		}
		$rows = $rows->where($where)
			->select("$chart.ID", "$chart.TITLE as NAME", "$chart.CONFIG", "$chart.GROUP_ID", "$chartGroup.NAME as GROUP_NAME") 
			->distinct()
			->orderBy('GROUP_NAME')
			->orderBy("$chart.NAME")
			->get();
		\Helper::setGetterCase($originAttrCase);
		if(!$groupId) $this->userCharts = $rows;
		return $rows;
	}

	public function getUserRoleNames(){
		$user_user_role = UserUserRole::getTableName();
		$user_role = UserRole::getTableName();		
		$rows	= UserUserRole::join($user_role,"$user_user_role.ROLE_ID", '=', "$user_role.ID")
							->where("$user_user_role.USER_ID",$this->ID)
							->select("$user_role.NAME")
							->distinct()->get()->all();
		$roles = "";
		foreach($rows as $row)
			$roles .= ($roles ? ', ' : '') . $row->NAME;
		return $roles;
	}

	public function getDefaultDashboard(){
	}
	
	public function UserRole(){
		return $this->belongsToMany ('App\Models\UserRole',UserUserRole::getTableName(),$this->user_id_col,'ROLE_ID');
	}

//    public function UserRight($id_user)
//    {
//        $user_user_role = UserUserRole::getTableName();
//        $user_role_right = UserRoleRight::getTableName();
//        $user_right = UserRight::getTableName();
//
//        $rows = UserUserRole::join($user_role_right,"$user_user_role.ROLE_ID", '=', "$user_role_right.ROLE_ID")
//            ->join($user_right,"$user_right.ID", '=', "$user_role_right.RIGHT_ID")
//            ->where("$user_user_role.USER_ID",$id_user)
//            ->select("$user_right.CODE")
//            ->distinct()
//            ->get();
//        $rs = $rows?$rows->map(function ($item, $key) {
//            return $item->CODE;
//        })->toArray():[];
//        return $rows;
//    }

	public function right()
	{
		$uk = $this->UserUserRole();
		$uur = $uk->first();
		$ur = $uur->UserRole()->get(['CODE']);
		return $ur ;
	}

	public function isAdmin(){
		return $this->hasRight(self::MASTER_RIGHTS);
	}
	
	public function hasRight($right){
		$USER_RIGHTS = $this->role();
		$result = $USER_RIGHTS&&count($USER_RIGHTS)>0&&in_array($right, $USER_RIGHTS);
		return $result ;
	}
	
	public function containRight($right){
		return $this->isAdmin() || $this->hasRight($right);
	}
	
	public function checkReadOnly($rightCode,$facility_id = 0){
    	$readOnly 		= !$this->hasWritableRight($rightCode);
		if (!$readOnly&&$facility_id>0) {
    		$readOnly 	= $this->checkFacilityReadOnly($facility_id);
		}
		return $readOnly ;
	}
	
	public function checkFacilityReadOnly($facility_id = 0){
		$readOnly 		= false;
		if ($facility_id>0) {
			$userDataScope	= UserDataScope::where("USER_ID",$this->ID)->first();
			if ($userDataScope) {
				$fidStrings			= 	explode(',', $userDataScope->FACILITY_ID);
				if (count($fidStrings)>0) {
					$readOnly 		= in_array("$facility_id*", $fidStrings);
				}
			}
		}
		return $readOnly ;
	}
	
	public function getScopeFacility($returnList = false){
		$facilityIds 		= null;
		$userDataScope	= UserDataScope::where("USER_ID",$this->ID)->first();
		if ($userDataScope) {
			$facilityIdStrings		= $userDataScope->FACILITY_ID;
			if ($facilityIdStrings) {
				$facilityIdStrings		= str_replace("*", "", $facilityIdStrings);
				$facilityIds			= explode(",", $facilityIdStrings);
			}
		}
		if($returnList && $facilityIds){
			$tableArea = \App\Models\LoArea::getTableName();
			$tableFacility = \App\Models\Facility::getTableName();
			$res = \App\Models\Facility::leftjoin($tableArea, "$tableFacility.AREA_ID", '=', "$tableArea.ID")
				->whereIn("$tableFacility.ID", $facilityIds)
				->select("$tableFacility.ID as value", "$tableFacility.NAME as name", "$tableArea.NAME as group")
				->orderBy('group')
				->orderBy('name')
				->get();
			return $res;
		}
		return $facilityIds;
	}

	public function hasWritableRight($right,$facility_id = 0){
		$result = true;
		if ($right && $right !="") {
			$result = !$this->hasRight("DATA_READONLY");
			if ($result) {
				$userRoles 	= $this->getUserRoles();
				$result = false;
				foreach ($userRoles as $key => $item){
					if ($item->CODE==self::MASTER_RIGHTS) return $item->READ_ONLY==0;
					$result = $result || ($item->CODE==$right&&$item->READ_ONLY==0);
				}
			}
		}
		return $result ;
	}
	
	
	public function updateLogoutLog(){
		$logUser = LogUser::where(['SESSION_ID'=>session()->getId()])->first();
		if ($logUser) {
			$values = [
					'LOGOUT_TIME'	=>	Carbon::now(),
			];
			$logUser->fill($values)->save();
		}
	}
	
	public function updateLoginLog(){
		$attributes = ['SESSION_ID'=>session()->getId()];
		$logUser = LogUser::firstOrNew($attributes);
		$values = [	'USERNAME'		=>	$this->username, 
					'LOGIN_TIME'	=>	Carbon::now(), 
					'IP'			=>	request()->ip(),
					'SESSION_ID'	=> session()->getId()
		];
		$logUser->fill($values)->save();
	}
	
	public function hasRole($roleCode)
	{
		if($this->isAdmin()) return true;
		if($this->ID)
		{
			$user_user_role = UserUserRole::getTableName();
			$user_role = UserRole::getTableName();
			
			$rows= UserUserRole::join($user_role,"$user_user_role.ROLE_ID", '=', "$user_role.ID")			
			->where([$user_role.".CODE"=>$roleCode, $user_user_role.".USER_ID"=>$this->ID])
			->select($user_role.".CODE")
			->distinct()
			->get();
			
			return (count($rows) > 0);
		}
		return false;
	}
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function UserUserRole(){
		return $this->hasMany('App\Models\UserUserRole',"USER_ID", "ID");
	}

	public function UserRoleRight(){
		return $this->hasMany('App\Models\UserRoleRight',$this->user_id_col, $this->primaryKey);
	}
	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function posts() 
	{
	  return $this->hasMany('App\Models\Post');
	}

	/**
	 * One to Many relation
	 *
	 * @return Illuminate\Database\Eloquent\Relations\hasMany
	 */
	public function comments() 
	{
	  return $this->hasMany('App\Models\Comment');
	}

	/**
	 * Check media all access
	 *
	 * @return bool
	 */
	public function accessMediasAll()
	{
	    return $this->role->slug == 'admin';
	}

	/**
	 * Check media access one folder
	 *
	 * @return bool
	 */
	public function accessMediasFolder()
	{
	    return $this->role->slug != 'user';
	}
	
	public function isAuthorizedFor($dataStore){
		$authorized = true;
		$facilityIds = $this->getScopeFacility();
		if ($facilityIds) {
			$authorized = false;
			if (count($facilityIds)>0) {
				$intObjectType = $dataStore["IntObjectType"];
				$objectId 	= $dataStore["ObjectName"];
				$mdlName 	= \Helper::getModelName($intObjectType);
				if (method_exists($mdlName, "isInFacilities"))
					$authorized = $mdlName::isInFacilities($facilityIds,$objectId);
				else
					$authorized = true;
			}
		}
		return $authorized;
	}
	
    public static function loadBy(){
        $entries = User::select("USERNAME as ID", "USERNAME as NAME")->get();
        $entries->each(function ($item, $key) {
            $item->primaryKey = "USERNAME";
        });
        return $entries;
    }
    
    // Rest omitted for brevity
    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
    	return $this->getKey();
    }
    
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
    	return [];
    }
}
