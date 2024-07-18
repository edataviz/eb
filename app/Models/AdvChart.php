<?php 
namespace App\Models; 
use App\Models\DynamicModel; 

class AdvChart extends DynamicModel 
{ 
	protected $table = 'ADV_CHART'; 
    public function __construct(array $attributes = []) {
    	parent::__construct();
    }
} 
