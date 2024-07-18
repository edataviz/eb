<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\EBBuilder;

class DynamicModel extends Model {
	
	protected static $INSTANCE = null;
	
	protected $primaryKey = 'ID';
	protected $isOracleModel = false;
	protected $isReservedName = false;
	public 		$timestamps = false;
	protected $autoFillableColumns = true;
	protected static $isAddAllAsDefault	= false;
	public static $shareFillable 		= null;
	
	protected static $idField = 'ID';
	protected static $fieldVALUE = 'ID';
	protected static $fieldNAME = 'NAME';
	protected static $fieldACTIVE = 'ACTIVE';
	protected static $fieldORDER = 'ORDER';

	public static function getInstance(){
		if (!static ::$INSTANCE||(get_called_class()!=get_class(static ::$INSTANCE))) {
			static ::$INSTANCE = with(new static);
		}
		return static ::$INSTANCE;
	}
	
	
	public function __construct(array $attributes = []) {
		parent::__construct($attributes);
		$this->isOracleModel = config('database.default')==='oracle';
		if ($this->isReservedName){
			$this->table = $this->table.'_';
		}
		
		if ($this->isOracleModel){
			$this->primaryKey = strtolower($this->primaryKey);
			if ($this->dates&&count($this->dates)>0){
				$sDates = [];
				foreach($this->dates as $item ){
					$sDates[] = strtolower($item);
				}
				$this->dates = array_merge($this->dates,$sDates);
			}
		}
		
		if ($this->autoFillableColumns) {
			if(!static::$shareFillable) static::$shareFillable = [];
			if(array_key_exists($this->table, static::$shareFillable)) 
				$this->fillable = static::$shareFillable[$this->table];
			else{
				$fillable = $this->getTableColumns();
				if(($key = array_search($this->primaryKey, $fillable)) !== false||
						($key = array_search(strtoupper($this->primaryKey), $fillable)) !== false||
						($key = array_search(strtolower($this->primaryKey), $fillable)) !== false) {
					unset($fillable[$key]);
				}
					
				$this->fillable = $fillable;
				static::$shareFillable[$this->table] = $this->fillable;
			}
		}
	}
	
	
	public function setDatesPropery($dates){
		$this->dates = $dates;
	}
	
	public function getDatesPropery(){
		return $this->dates;
	}
	
	
	public function setTable($tableName){
		$this->table = $tableName;
	}

	public function getTableColumns() {
		$columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
		/* if ($this->isOracleModel){
			$results = \Helper::extractColumns($columns);
			$columns = $results;
		} */
		return $columns;
	}
	
	public function __get($key)
	{
		if ($this->isOracleModel) {
			if (is_null($this->getAttribute($key))) {
				return $this->getAttribute(strtolower($key));
			} 
		} 
		return $this->getAttribute($key);
	}
	
 	protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new EBBuilder($conn, $grammar, $conn->getPostProcessor());
    }
	
	public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
	{
		return parent::belongsTo($related,$foreignKey,$this->isOracleModel?strtolower($otherKey):$otherKey,$relation);
	}
	
	public function hasMany($related, $foreignKey = null, $localKey = null)
	{
		return parent::hasMany($related,$foreignKey,$this->isOracleModel?strtolower($localKey):$localKey);
	}
	
	public function hasOne($related, $foreignKey = null, $localKey = null)
	{
		return parent::hasOne($related,$foreignKey,$this->isOracleModel?strtolower($localKey):$localKey);
	}
	
	public static function getTableName()
	{
		return with(new static)->getTable();
	}
	
	public static function getDateFields(){
		return with(new static)->getDates();
	}
	
	public static function getAll(){
		/* $instance = new static;  
		if (method_exists($instance, "loadActive")) 
			$entries = $instance->loadActive();
		else */
		$entries = static ::all();
		return $entries;
	}
	
	public static function getOptionDefault($modelName,$unit){
		$aOption 		= ["modelName"	=> $modelName];
		if (static::$isAddAllAsDefault) $aOption['default']	= ['ID'=>0,'NAME'=>'All'];
		return $aOption;
	}
	
	public static function loadActive($extraData = null){
		return static :: where(static::$fieldACTIVE, "=", 1)->orderBy(static::$fieldORDER)->get();
	}

	public static function getList($condition = [], $all = false, $none = false){
		if(static::$fieldACTIVE) $condition[static::$fieldACTIVE] = 1;
		$list = static :: where($condition)->select(static::$fieldVALUE.' as value', static::$fieldNAME.' as name');
		if(static::$fieldORDER) $list = $list->orderBy(static::$fieldORDER);
		$list = $list->orderBy('name')->get()->all();
        if($none){
            array_unshift($list, ['value' => 'none', 'name' => '(None)']);
        }
        if($all){
            array_unshift($list, ['value' => 'all', 'name' => '(All)']);
		}
		return $list;
	}
}
