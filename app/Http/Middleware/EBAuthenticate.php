<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class EBAuthenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	protected $C_ID_PHASE_OIL=1;
	protected $C_ID_PHASE_GAS=2;
	protected $C_ID_PHASE_WATER=3;
	protected $C_ID_PHASE_GASLIFT=21;
	
	protected $current_username="";
	
	protected $DATA_SCOPE_PU="";
	protected $DATA_SCOPE_AREA="";
	protected $DATA_SCOPE_FACILITY="";
	
	protected $vvv="done";
	protected $is_logged_in = false;
	
	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
		$this->is_logged_in = false;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
// 		if ($this->auth->guest())
		if (!$this->ebauth())
		{
			if ($request->ajax())
			{
				return response('Unauthorized.', 401);
			}
			else
			{
				return redirect()->guest('auth/login');
			}
		}

		return $next($request);
	}
	
	
	public function ebauth()
	{
		$this->is_logged_in= $this->isLoggedIn();
		if($RIGHT_CODE)
			checkRight($RIGHT_CODE);
		
		$session_id = session_id();
		if(empty($session_id)) {session_start();$session_id = session_id();}
		
			$sSQL="select username from user a where session_id='$session_id'";
			$result=mysql_query($sSQL) or die (mysql_error());
			$row=mysql_fetch_array($result);
		
		$current_username=$row["username"];
		return $this->is_logged_in;
	}
	
	
	
	function upload_to_server( $path, $name, $old=''){
		if( $old!=''){
			@unlink( $path.$old);
		}
		if( preg_match('/\.jpg|\.gif|\.jpeg|\.png$/i', $_FILES[$name]['name'])){
			$des_file = $_FILES[$name]['name'];
			copy( $_FILES[$name]['tmp_name'], $path.$des_file) or die("Couldn't copy file.");
			return $des_file;
		}
	}
	
	function isLoggedIn()
	{
		global $current_username,$current_user_id,$DATA_SCOPE_PU,$DATA_SCOPE_AREA,$DATA_SCOPE_FACILITY,$vvv;
		$sid = session_id();
		if(empty($sid)) {session_start();$sid = session_id();}
	
		$sSQL="select a.*,b.PU_ID,b.AREA_ID,b.FACILITY_ID from user a left join user_data_scope b on a.ID=b.USER_ID where session_id='$sid'";
		$result=mysql_query($sSQL) or die (mysql_error());
		$row=mysql_fetch_array($result);
	
		$b_logged_in=false;
		if($row)
		{
			$current_username=$row['username'];
			$current_user_id=$row[ID];
	
			$DATA_SCOPE_PU=$row[PU_ID];
			$DATA_SCOPE_AREA=$row[AREA_ID];
			$DATA_SCOPE_FACILITY=$row[FACILITY_ID];
	
			$b_logged_in=true;
			$vvv="***";
		}
		return $b_logged_in;
	}
	
	function loadRights()
	{
		global $USER_RIGHTS, $current_user_id;
		if(!$USER_RIGHTS)
		{
			$USER_RIGHTS=array();
			if($current_user_id)
			{
				$sSQL="select distinct c.CODE from user_user_role a, user_role_right b, user_right c where a.user_id=$current_user_id and a.role_id=b.role_id and b.right_id=c.id";
				$result=mysql_query($sSQL) or die (mysql_error());
				while($row=mysql_fetch_array($result))
				{
					array_push($USER_RIGHTS, $row[CODE]);
				}
			}
		}
	}
	function checkRight($rightCode)
	{
		global $USER_RIGHTS;
		loadRights();
		if(in_array("_ALL_", $USER_RIGHTS))
		{
			return;
		}
		if(!in_array($rightCode, $USER_RIGHTS))
		{
			header('Location: ../home/error.php');
			exit();
		}
	}
	

}
