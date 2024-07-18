<?php

namespace App\Models;

use App\Models\CodeSafetyCategory;
use App\Models\DynamicModel;

class FacilitySafetyCategory extends DynamicModel {
	protected $table = 'facility_safety_category';
	public function codeSafetyCategory() {
		return $this->belongsTo ( 'App\Models\CodeSafetyCategory', 'CODE_SAFETY_CATEGORY_ID', 'ID' );
	}
}
