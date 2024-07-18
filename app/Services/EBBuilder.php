<?php

namespace App\Services;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

class EBBuilder extends Builder {
	protected $isOracleModel = false;
	
	public function __construct(ConnectionInterface $connection,
			Grammar $grammar = null,
			Processor $processor = null)
	{
		parent::__construct($connection,$grammar,$processor);
		$this->isOracleModel = config('database.default')==='oracle';
	}
	
	public function orderBy($column, $direction = 'asc')
	{
		if ($this->isOracleModel&&
				($column=="ORDER"||$column=="order"||
				\Helper::endsWith($column, ".ORDER")||
				\Helper::endsWith($column, ".order"))) {
			return parent::orderBy($column."_", $direction);
		}
		return parent::orderBy($column, $direction);
	}
	
	public function getColumnListing($table)
	{
		$columns = parent::getColumnListing($table);
		if ($this->isOracleModel){
			$results = \Helper::extractColumns($columns);
			$columns = $results;
		}
		return $columns;
	}
}
