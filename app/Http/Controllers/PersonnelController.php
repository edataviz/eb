<?php

namespace App\Http\Controllers;
use App\Models\CodeBaType;
use App\Models\CodePersonnelTitle;
use App\Models\CodePersonnelType;
use App\Models\Personnel;
use App\Models\PersonnelSumDay;
use App\Models\BaAddress;

class PersonnelController extends CodeController {
	
	public function __construct() {
		parent::__construct();
		$this->extraDataSetColumns = [	'TITLE'				=>	[	'column'	=>'BA_ID',
																	'model'		=>'BaAddress'],
		];
	}
	
	public function getFirstProperty($dcTable){
		return  ['data'=>$dcTable,'title'=>'','width'=>50];
	}
	
    public function getDataSet($postData,$table,$facility_id,$occur_date,$properties){
    	if($table!=PersonnelSumDay::getTableName()){
	//     	$personnel = Personnel::getTableName();
	    	$codePersonnelTitle = CodePersonnelTitle::getTableName();
	    	//      	\DB::enableQueryLog();
	    	$dataSet = Personnel::join($codePersonnelTitle,"$codePersonnelTitle.ID", '=', "$table.TITLE")
	    					->where("FACILITY_ID","=",$facility_id)
					    	->where("OCCUR_DATE","=",$occur_date)
					    	->select(
					    			"$table.*",
					    			"$table.ID as DT_RowId",
					    			"$table.ID as $table",
					    			"$codePersonnelTitle.CODE"
					    			)
	 		    			->orderBy("$table.ID")
			    			->get();
	    	//  		\Log::info(\DB::getQueryLog());
			    			
	    	$extraDataSet 	= $this->getExtraDataSet($dataSet);
	    	
	    	return ['dataSet'		=> $dataSet,
	    			'extraDataSet'	=> $extraDataSet,
	    	];
    	}
    	return null;
    }
    
    public function getSecondaryData($postData,$dcTable,$facility_id,$occur_date,$results){
    	$personnelSumDay = PersonnelSumDay::getTableName();
    	$personnelSumDayProperties = $this->getOriginProperties($personnelSumDay);
    	$originAttrCase = \Helper::setGetterUpperCase();
    	$secondaryEntries = $this->getSecondaryDataSet($postData,$personnelSumDay,$facility_id,$occur_date,$personnelSumDayProperties);
    	$uoms = $this->getUoms($personnelSumDayProperties,$facility_id,$personnelSumDay,$postData);
    	\Helper::setGetterCase($originAttrCase);
    	$secondaryData = [	'dataSet'		=>$secondaryEntries,
			    			'properties'	=>$personnelSumDayProperties,
			    			'uoms'			=>$uoms,
			    			'postData'		=>['tabTable'=>'PersonnelSumDay'],
    	];
    	return $secondaryData;
    }
    
    public function getSecondaryDataSet($postData,$table,$facility_id,$occur_date,$properties){
    	
    	$personnel = Personnel::getTableName();
    	$codePersonnelTitle = CodePersonnelTitle::getTableName();
    	$codePersonnelType = CodePersonnelType::getTableName();
    	$personnelSumDay = PersonnelSumDay::getTableName();
//     	\DB::enableQueryLog();
    	$query = Personnel::where("$personnel.FACILITY_ID", '=', $facility_id)
    						->where("$personnel.OCCUR_DATE", '=', $occur_date)
    						->whereRaw(\DB::raw("$personnel.TYPE = $codePersonnelType.ID"))
    						->whereRaw(\DB::raw("$personnel.TITLE = $codePersonnelTitle.ID"))
    						->select(\DB::raw('count(*)'));
    	
    	$entryCount = $query->toSql();
    	$binding 	= $query->getBindings();
    	$rowId		= config('database.default')==='sqlsrv'?
				    	\DB::raw("('' + $codePersonnelType.ID + $codePersonnelTitle.ID ) as DT_RowId"):
				    	\DB::raw("CONCAT($codePersonnelType.ID,$codePersonnelTitle.ID) as DT_RowId");
    	
    	$dataSet = CodePersonnelTitle::join($codePersonnelType,"$codePersonnelType.ID",'=',"$codePersonnelType.ID")
							    	->select(
							    			$rowId,
							    			"$codePersonnelType.ID as TYPE",
							    			"$codePersonnelTitle.ID as TITLE",
							    			"$codePersonnelType.NAME as TYPE_NAME",
							    			"$codePersonnelTitle.NAME as TITLE_NAME",
							    			"$personnelSumDay.NOTE",
							    			\DB::raw("($entryCount) as \"NUMBER\"")
							    			)
					    			->addBinding($binding)
					    			->leftJoin($personnelSumDay, function($join) use ($personnelSumDay,$codePersonnelType,$codePersonnelTitle,$facility_id,$occur_date){
					    				$join->on("$personnelSumDay.TYPE", '=', "$codePersonnelType.ID");
					    				$join->on("$personnelSumDay.TITLE", '=', "$codePersonnelTitle.ID");
					    				$join->where('FACILITY_ID','=',$facility_id);
					    				$join->where('OCCUR_DATE','=',$occur_date);
					    			})
					    			->orderBy("$codePersonnelType.ID")
					    			->orderBy("$codePersonnelTitle.ID")
					    			->get();
// 		\Log::info(\DB::getQueryLog());
					    			
    	return $dataSet;
    }
    

	public function loadTargetEntries($sourceColumnValue,$sourceColumn,$extraDataSetColumn,$bunde = null){
    	$data = null;
    	switch ($sourceColumn) {
    		case 'TITLE':
    			$targetColumn	= is_array($extraDataSetColumn)&&array_key_exists('column', $extraDataSetColumn)?$extraDataSetColumn['column']:$extraDataSetColumn;
    			if ($targetColumn=="BA_ID") {
			    	$codePersonnelTitle = CodePersonnelTitle::getTableName();
			    	$codeBaType 	= CodeBaType::getTableName();
			    	$targetTable 	= BaAddress::getTableName();
			    	$field			= config('database.default')==='oracle'?"$targetTable.NAME as ".'"text"':"$targetTable.NAME as text";
			    	$data	 = BaAddress::join($codeBaType,"$targetTable.SOURCE",'=',"$codeBaType.ID")
								    		->join($codePersonnelTitle,function ($query) use ($codePersonnelTitle,$codeBaType,$sourceColumnValue) {
												    				$query->on("$codePersonnelTitle.CODE",'=',"$codeBaType.CODE")
												    					->where("$codePersonnelTitle.ID",'=',$sourceColumnValue) ;
								    		})
								    		->select(
									    				"$targetTable.ID",
								    					"$targetTable.NAME",
									    				"$targetTable.ID as value",
										    			$field
									    			)
							    			->distinct()
								    		->get();
    			}
    			break;
    	}
    	return $data;
    }
}
