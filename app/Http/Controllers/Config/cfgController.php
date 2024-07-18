<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 10/08/2018
 * Time: 1:40 PM
 */

namespace App\Http\Controllers\Config;


use App\Http\Controllers\CodeController;
use App\Models\CfgConfig;

class CfgController extends CodeController
{
    public function __construct() {
        parent::__construct();
        $this->editFilterName = 'editfilter.cfconfig';
        $this->editFilterPrefix = 'secondary_';
    }

    public function getGroupFilter($postData){
        $filterGroups = array('productionFilterGroup'	=> [],
            'dateFilterGroup'   => array(   ['id'=>'config_date_begin'  ,'name'=>'From Date'],
                                            ['id'=>'config_date_end'    ,'name'=>'To']),
            'frequenceFilterGroup'		=> [],
            'enableButton' =>false
        );

        return $filterGroups;
    }

    public function getDataSet($postData,$dcTable,$facility_id,$occur_date,$properties){
        $tableName 		= array_key_exists('TABLE_NAME', $postData)?$postData['TABLE_NAME']:'';
        $dataSet        = $tableName? CfgConfig::loadBy(['TABLE_NAME' => $tableName]) : [];
        return ['dataSet'=>$dataSet];
    }
}