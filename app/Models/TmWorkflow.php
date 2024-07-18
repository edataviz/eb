<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

 class TmWorkflow extends DynamicModel 
{ 
	protected $table = 'TM_WORKFLOW'; 
	public $timestamps = false;
	
	protected $fillable  = ['ID', 'NAME', 'INTRO', 'CDATE', 'AUTHOR', 'RUN_TASK', 'ISRUN', 'DATA', 'STATUS'];
	
	public static function loadActive($extraData = null){
		return TmWorkflow::where ( ['STATUS' => 1] )
						->get ( [
									'id',
									'name',
									'isrun',
									'id as ID',
									'name as NAME',
							] );
	}
} 
