<?php 
namespace App\Models; 
 
class RptReport extends EbBussinessModel { 
	protected $table = 'RPT_REPORT'; 
	
	public static function loadByGroupId($groupId){
		$originAttrCase = \Helper::setGetterUpperCase();
		/*
		$fileField		= config('database.default')==='oracle'?"FILE_ as FILE":"FILE";
		$field			= \Helper::getIdentifierColumn('GROUP');
		$where			= [$field	=> $groupId, 'ACTIVE' => 1];
		$reports 		= RptReport::where($where)
 							->select("ID", "NAME", 'EXPORT_PDF', 'EXPORT_HTML', 'EXPORT_XLS', 'EXPORT_CSV', 'EXPORT_XML', $fileField)
							->orderBy($field)
							->orderBy('ORDER')
							->orderBy('NAME')
							->get();
		*/
		$reports = auth()->user()->getUserReports($groupId);
		\Helper::setGetterCase($originAttrCase);
		foreach($reports as $r)
			$r['FORMATS'] = ($r['EXPORT_PDF']?'pdf,':'').($r['EXPORT_XLS']?'Excel,':'').($r['EXPORT_CSV']?'csv,':'').($r['EXPORT_HTML']?'html,':'').($r['EXPORT_XML']?'xml,':'');
		return $reports;
	}
	
	/* public function getAttribute($key)
	{
		if ($this->isOracleModel&&($key=="FILE"||$key=="file")) {
			$value = parent::getAttribute("FILE_");
			if (is_null($value)) $value = parent::getAttribute("file_");
			return $value;
		}
		return parent::getAttribute($key);
	} */
} 
