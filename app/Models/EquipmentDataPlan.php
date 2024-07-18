<?php
namespace App\Models;

class EquipmentDataPlan extends EbBussinessModel
{
    protected $table 	= 'EQUIPMENT_DATA_PLAN';
    protected $primaryKey 	= 'ID';
    protected $dates 		= ['OCCUR_DATE'];
}