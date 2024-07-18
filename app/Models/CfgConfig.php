<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 7/08/2018
 * Time: 2:53 PM
 */

namespace App\Models;


class CfgConfig extends EbBussinessModel
{
    protected $table = 'cfg_config';
    protected $dates = ['EFFECTIVE_DATE','END_DATE'];


    public static function loadBy($option=['ID' => 0]){
        $query = CfgConfig::join('facility','cfg_config.FACILITY_ID','=','facility.ID');
        if($option&&count($option)>0) $query->where($option);
        $result = $query->select('cfg_config.ID',"cfg_config.FACILITY_ID","cfg_config.EFFECTIVE_DATE","cfg_config.END_DATE",
                                "facility.NAME as NAME")
                        ->get();
        $result->prepend((object)['ID' =>0	,'CODE' =>0	,'NAME' => '(Default)','FACILITY_ID' => 0 ]);
        return $result;
    }

    public function updateDependRecords($occur_date,$values,$postData) {
        if ($postData&&array_key_exists('action', $postData)&&array_key_exists('sourceCfConfig', $postData)) {
            $action             = $postData['action'];
            $sourceCfConfig     = $postData['sourceCfConfig']?$postData['sourceCfConfig']:null;
            if ($action=='clone'){
                CfgFieldProps::where(['TABLE_NAME'=>$this->TABLE_NAME,'CONFIG_ID'=>$this->ID])->delete();
                $cfgFieldProps = CfgFieldProps::where(['TABLE_NAME'=>$this->TABLE_NAME,'CONFIG_ID'=>$sourceCfConfig])->get();
                foreach($cfgFieldProps as $key => $cfgFieldProp ){
                    $newCfgFieldProp = $cfgFieldProp->replicate();
                    unset($newCfgFieldProp->CONFIG_ID);
                    unset($newCfgFieldProp->config_id);
                    $newCfgFieldProp->CONFIG_ID = $this->ID;
                    $newCfgFieldProp->save();
                }
            }
        }
        return $this;
    }
}