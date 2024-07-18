<?php
$currentSubmenu ='/ghg/es/combustion';
$mainTab        = 'CombustionEmissionGroup';
$tables         = [$mainTab	=>['name'=>'Data Input']];
$isAction = true;
$detailTableTab = 'EmissionCombCalcMethod';
?>

@extends('core.es')
@section('funtionName')
@stop

@section('adaptData')
    @parent
    <script>
        actions.loadUrl = "/combustion/es/load";
        actions.saveUrl = "/combustion/es/save";

        // Load Content
        actions.extraDataSetColumns = {'SOURCE_CLASS_ID':'SOURCE_CATEGORY_ID',
                                            'SEGMENT_ID':'SECTOR_ID',
                                        'CALC_OPTION_ID':'CALC_SECTION_ID',
                                   'EMISSION_FORMULA_ID':'CALC_OPTION_ID',
                              'EMISSION_FACTOR_TABLE_ID':'CALC_OPTION_ID'};

        source['SOURCE_CATEGORY_ID']	={	dependenceColumnName	:	['SOURCE_CLASS_ID','SOURCE_TYPE_ID'],
            url						: 	'/combustion/es/loadsrc'
        };
        source['SECTOR_ID']	={	dependenceColumnName	:	['SEGMENT_ID'],
            url						: 	'/combustion/es/loadsrc'
        };

        // Child
        source['CALC_SECTION_ID']	    ={	dependenceColumnName	:	['CALC_OPTION_ID','EMISSION_FORMULA_ID','EMISSION_FACTOR_TABLE_ID'],
            url						: 	'/combustion/es/loadsrc'
        };
        source['CALC_OPTION_ID']	    ={	dependenceColumnName	:	['EMISSION_FORMULA_ID','EMISSION_FACTOR_TABLE_ID'],
            url						: 	'/combustion/es/loadsrc'
        };

        source.initRequest = function(tab,columnName,newValue,collection){
            postData = actions.loadedData[tab];
            var srcType = null;
            var result = $.grep(collection, function(e){
                return e['ID'] == newValue||e['id'] == newValue;
            });
            if (result.length > 0) {
                srcType = typeof result[0]['CODE'] != "undefined"?result[0]['CODE'] :result[0]['code'];
            }
            else return null;

            srcData = {name : columnName,
                value : newValue,
                srcType : srcType,
                Facility : postData['Facility']};
            return srcData;
        }

        var pid;
        var date_parent;
        var oInitExtraPostData = editBox.initExtraPostData;
        editBox.initExtraPostData = function (id,rowData){
            var idata = oInitExtraPostData(id,rowData);
            pid = id;
//            date_parent = rowData['BEGIN_DATE'];
            date_parent = rowData['EFFECTIVE_DATE'];
            idata.tabTable  = '{{$detailTableTab}}';
            return 	idata;
        };
        // lấy ra parent id
        actions['idNameOfDetail'] = ['COMBUSTION_EMISSION_GROUP_ID', 'ID','EFFECTIVE_DATE'];

        actions['doMoreAddingRow'] = function(addingRow){
            addingRow['COMBUSTION_EMISSION_GROUP_ID'] = pid;
            if(typeof  date_parent != "undefined") addingRow['EFFECTIVE_DATE'] = date_parent;
            return addingRow;
        }
    </script>
@stop

@section('editBoxParams')
    @parent
    <script>
        editBox.loadUrl = "/esc/load";
        editBox.saveUrl = "/esc/save";
    </script>
@stop


