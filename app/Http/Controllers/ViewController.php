<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class ViewController extends EBController {

	protected $dvController;
	function getDVController(){
		if($this->dvController==null)
			$this->dvController = new DVController();
		return $this->dvController;
	}

    public static function buildFilter($model, $id = null, $title = null, $defaultValue = null, $affectTo = [], $condition = [], $all = false, $none = false){
        $model = '\\App\\Models\\'.$model;
        $list = $model::getList($condition, $all, $none);
        if(count($list) && $defaultValue === null){
            $defaultValue = $list[0]->value;
        }
        $ret = ['list' => [
            'defaultValue' => $defaultValue,
            'items' => $list,
        ]];
        if($id) $ret['id'] = $id;
        if($title) $ret['title'] = $title;
        if($affectTo) $ret['affectTo'] = $affectTo;
        return $ret;
    }

    public function getTagData($tags = []){
        $time = strtotime(date('Y-m-d H:i:s').' UTC') * 1000;
        $ret = [];
        foreach($tags as $tag){
            $ret[$tag] = ['time' => $time, 'value' => rand(100,1000)];
        }
        return $ret;
    }

    public function getExpressionDataQuery($exp, $date = false){
        $ds = explode('.', $exp);
        $table = $ds[0];
        /*
        $table = array_shift($ds);
        $model = \Helper::getModelName($table);
        if(method_exists($model, "getValueQuery")){
            //add date to params list
            $ds[] = $date ? $date : date('Y-m-d');
            return $model::getValueQuery($ds);
        }
        */
        $value_field = $ds[1];
        $object_id = $ds[2];
        $dates = explode(',', $date ? $date : date('Y-m-d'));
        $sql = "select source_type,object_field,date_field,ext_condition from graph_data_source where source_name = '$table'";
        $rs = \DB::select($sql);
        $cond = "";
        $date_cond = "$date_field='$dates[0]'";
        $query = "";
        if(count($dates)>1){
            $date_cond = "$date_field between '$dates[0]' and '$dates[1]'";
        }
        foreach($rs as $r){
            $date_field = $r->date_field ? $r->date_field : 'occur_date';
            $object_field = $r->object_field ? $r->object_field : strtolower($r->source_type).'_id';
            if($r->ext_condition){
                $es = explode('.', $r->ext_condition);
                foreach($es as $i=>$e){
                    if($i+3<count($ds))
                        $cond .= " and $e={$ds[$i+3]}";
                }
            }
            $query = "select $value_field from $table where $date_cond and $object_field=$object_id{$cond}";
            break;
        }
        return $query;
    }

    private $expQueries = [];
    private $expTags = [];
    public function checkExpression($exp, $date = false){
        $pattern = '/TAG:([\w\s\.-]+)/i';
        preg_match_all($pattern, $exp, $matches);
        if(count($matches) > 1)
            foreach($matches[1] as $tag)
                if(!isset($this->expTags[$tag]))
                    $this->expTags[$tag] = $tag;
        $exp = preg_replace($pattern, '', $exp);

        $pattern = '/\w+(?:\.\w+){1,}/'; //?: remove group
        preg_match($pattern, $exp, $es);
        foreach($es as $ei){
            if(!isset($this->expQueries[$ei]))
                $this->expQueries[$ei] = $this->getExpressionDataQuery($ei, $date);
        }
    }

    public function getModelData(Request $request){
        $models = $request->models;
        $ret = [];
        foreach($models as $selectId => $model){
            $model = 'App\Models\\' . $model;
            $re = \DB::select('select ID as value, NAME as name from '.$model::getTableName().' order by NAME');
            $ret[$selectId] = $re;
        }
        return $ret;
    }

    public function getChartData($chartIdOrExps, $dateOrDateRange, &$chartSettings = null){
        $expressions = $chartIdOrExps;
        $dateRange = $dateOrDateRange;
        if(is_numeric($chartIdOrExps)){
            $chartId = $chartIdOrExps;
            $date = $dateOrDateRange;
            $expressions = [];
            $chartConfig = \App\Models\AdvChart::where('ID', $chartId)->select('CONFIG')->first();
            if($chartConfig) {
                $chartSettings = json_decode(str_replace('\"', '"', $chartConfig->CONFIG));
                if($chartSettings){
                    foreach($chartSettings->items as $index => $item){
                        if($item->expression)
                            $expressions[$index] = $item->expression;
                    }
                    $dateRange = $this->getDateRange($date, $chartSettings->timeRange, $chartSettings->timeRangeUnit);
                }
            }
        }
        $ret = [];
        foreach($expressions as $index => $exp){
            if(stripos($exp, 'TAG:') === 0){
                if(isset($this->vals[$exp]))
                    $ret[$index] = [[strtotime(date('Y-m-d H:i:s').' UTC') * 1000, $this->vals[$exp]]];
                else{
                    $tag = substr($exp, 4);
                    $d = $this->getTagData([$tag]);
                    $ret[$index] = [[$d[$tag]['time'], $d[$tag]['value']]];
                }
                continue;
            }
            $sql = $this->getExpressionDataQuery($exp, $dateRange);
            if($sql){
                $rs = \DB::select($sql);
                $ret[$index] = [];
                foreach($rs as $r)
                    $ret[$index][] = [strtotime($r->OCCUR_DATE.' UTC') * 1000, $r->value ? $r->value + 0 : 0];
            }
        }
        return $ret;
    }

    protected $vals = [
        ';;'	=> '".chr(59)."',
        '{{'	=> '".chr(123)."',
        '}}'	=> '".chr(125)."',
        '{'		=> '".(',
        '}'		=> ')."',
        ';'		=> '',
    ];

    public function loadChartsList(Request $request){
        $groupId = $request->groupId;
		$sql = "select id, title as name, config from adv_chart where group_id={$groupId} ".($this->user->isAdmin()?"":"and id in (select distinct user_role_chart.chart_id from user_role_chart, user_user_role
where user_user_role.user_id={$this->user->ID}
and user_user_role.role_id=user_role_chart.role_id)")." order by name";
        $ds = \DB::select($sql);
        $d_items = [];
        foreach($ds as $d){
            $d_items[] = ['value' => $d->id, 'name' => $d->name, 'config' => $d->config];
        }
        $charts = ['defaultValue' => '', 'items' => $d_items];
        return $charts;
    }

    public function loadGraphData(Request $request){
        $input = $request->all();
        $date = $input['date'];
        $date = ($date ? $date : date('Y-m-d'));
        $filters = isset($input['filters']) ? $input['filters'] : null;
        $exps = isset($input['valueExpressions']) ? $input['valueExpressions'] : null;
        $chartExps = isset($input['chartExpressions']) ? $input['chartExpressions'] : null;
        $chartIds = isset($input['chartIds']) ? $input['chartIds'] : null;

        $ret = ['valueData' => [], 'chartData' => []];
        \Log::info('filters::');
        \Log::info($filters);

        if($exps){
            $calExps = [];
            $calSqls = [];
            foreach($exps as $cellId => $exp){
                if (stripos($exp, 'select ') !== false){
                    $calSqls[$cellId] = $exp;
                    unset($exps[$cellId]);
                }
                else if (strpos($exp, '(') !== false){
                    $calExps[$cellId] = $exp;
                    unset($exps[$cellId]);
                }
                else
                    $this->checkExpression($exp, $date);
            }
            $valCalExps = $this->getDVController()->evalExps($calExps, $date, $filters);
            foreach($valCalExps as $cellId => $value)
                $ret['valueData'][$cellId] = $value;

            foreach($calSqls as $cellId => $sql){
                foreach($filters as $param => $value){
                    $sql = str_replace("@$param", $value, $sql);
                    $sql = $this->getDVController()->replaceConditionDate($sql, '@DATE', Carbon::parse($date));
                    $sql = $this->getDVController()->replaceConditionDate($sql, '@NOW', Carbon::now());
                }

                $value = 0;
                $re = \DB::select($sql);
                if(count($re)){
                    $value = array_values((array)$re[0])[0];
                }
                $ret['valueData'][$cellId] = $value;
            }
            

            if(count($this->expTags)){
                $d = $this->getTagData($this->expTags);
                foreach($d as $tag => $item){
                    $this->vals["TAG:$tag"] = $item['value'];
                }
            }
    
            $sql = '';
            foreach($this->expQueries as $exp => $q) if($q){
                $this->vals[$exp] = 0;
                $q = '(select' . ($this->isSqlServer ? ' top 1' : '') . " '$exp' as exp," . substr($q, strpos($q, ' ')) . ($this->isMySql ? ' limit 1' : ($this->isOracle ? ' and rownum=1' : '')) . ')';
                $sql .= ($sql ? ' union all ' : '') . $q;
            }
            if($sql){
                \Log::info($sql);
                $rs = \DB::select($sql);
                foreach($rs as $r)
                    $this->vals[$r->exp] = $r->value;
            }
            foreach($exps as $cellId => $exp){
                //fill values
                $value = '';
                $expression = str_ireplace(array_keys($this->vals), $this->vals, $exp);
                
                //check unallowed functions
                $tks = token_get_all('<?php "'.$expression.'";');
                foreach($tks as $tk){
                    if(isset($tk[0]) && ($tk[0] == 319 || $tk[0] == 308)){
                        $tk_name = $tk[1];
                        if(!in_array($tk_name, ['null', 'abs', 'sqrt', 'round', 'rand', 'floor', 'ceil', 'exp', 'log', 'pi', 'pow', 'is_numeric', 'is_nan', 'min', 'max', 'log', 'sin', 'cos', 'tan', 'asin', 'acos', 'atan']))
                            $value = "Not allowed: $tk_name";
                    }
                }
    
                try{
                    \Log::info($expression);
                    $value = eval('return "'.$expression.'";');
                } catch( \Throwable $ex ){
                    \Log::info("evalExpression ($expression) throw error: ".$ex->getMessage());
                    $value = "Expression error!";
                } catch( \Exception $ex ){
                    \Log::info("evalExpression ($expression) error: ".$ex->getMessage());
                    $value = "Expression error!";
                }
    
                $ret['valueData'][$cellId] = $value;
            }
        }

        if($chartExps){
            foreach($chartExps as $cellId => $chartExp){
                $ret['chartData'][$cellId] = $this->getChartData($chartExp['expressions'], $chartExp['dateRange']);
            }
        }

        if($chartIds){
            foreach($chartIds as $cellId => $chartId){
                $ret['chartData'][$cellId] = $this->getChartData($chartId, $date);
            }
        }

        return $ret;
    }

    public function getCodesChanged(Request $request){
        $ret = [];
        $affs = $request->affected;
        foreach($affs as $aff){
            if($aff == 'Object'){
                if($request->isTag == 'true'){
                    $objTypeMap = ['Flow' => 1, 'EnergyUnit' => 2, 'Tank' => 3, 'Storage' => 4, 'Equipment' => 5, 'Quality' => 6, 'Keystore' => 7, 'Welltest' => 8, 'Deferment' => 9, 'Comments' => 10, 'Environmental' => 11];
                    $condition = ['OBJECT_TYPE' => $objTypeMap[$request->objectType]];
                    $ret[$aff] = \App\Models\IntTagMapping::getList($condition);
                }
                else{
                    $facilityId = $request->facility;
                    $objectModel = '\\App\\Models\\'.$request->objectType;
                    $ret[$aff] = $objectModel::getList(['FACILITY_ID' => $facilityId]);
                }
            }
            elseif($aff == 'DataSource'){
                $objectModel = '\\App\\Models\\'.$request->objectType;
                $tableName = $objectModel::getTableName();
                $ret[$aff] = \App\Models\GraphDataSource::getList(['SOURCE_TYPE' => $tableName]);
            }
            elseif($aff == 'Attribute'){
                //$objectModel = '\\App\\Models\\'.$request->dataSource;
                //$tableName = $objectModel::getTableName();
                if($request->dataSource == 'tag') {
                    $objTypeMap = ['Flow' => 1, 'EnergyUnit' => 2, 'Tank' => 3, 'Storage' => 4, 'Equipment' => 5, 'Quality' => 6, 'Keystore' => 7, 'Welltest' => 8, 'Deferment' => 9, 'Comments' => 10, 'Environmental' => 11];
                    $condition = ['OBJECT_TYPE' => $objTypeMap[$request->objectType]];
                    //if($request->object) $condition['OBJECT_ID'] = $request->object;
                    $ret[$aff] = \App\Models\IntTagMapping::getList($condition);
                }
                else
                    $ret[$aff] = \App\Models\CfgFieldProps::getList(['TABLE_NAME' => $request->dataSource]);
            }
        }
        return $ret;
    }
	 
    public function flow() {
        $EB = [
            'filters' => [
                static::buildFilter('CodeFlowPhase', 'selectFlowPhase', 'Flow phase', 'all', [], [], true)
            ],
        ];

		return view ( 'eb.flow', [
            'EB' => $EB, 
        ]);
    }
    
    public function dashboard() {
        $config = [];
		$sql = "select id, name from network where network_type=2 ".($this->user->isAdmin()?"":"and id in (select distinct user_role_dashboard.dashboard_id from user_role_dashboard, user_user_role
where user_user_role.user_id={$this->user->ID}
and user_user_role.role_id=user_role_dashboard.role_id)")." order by name";
        $ds = \DB::select($sql);
        $d_items = [];
        $d_default = '';
        foreach($ds as $d){
            if(!$d_default) $d_default = $d->id;
            $d_items[] = ['value' => $d->id, 'name' => $d->name];
        }
        $dashboards = ['defaultValue' => $d_default, 'items' => $d_items];

        $diagram = \App\Models\NetWork::where('ID', $d_default)->select('ID', 'NAME', 'XML_CODE')->first();
        if(!$diagram)
            $diagram = (object)['ID' => 0, 'NAME' => '[Untitled Diagram]', 'XML_CODE' => ''];

        return view ( 'eb.dashboard', [
            'config' => $config,
            'dashboards' => $dashboards,
            'diagram' => $diagram
        ]);
    }
    
	function getDateRange($date, $number, $unit){
		$beginDate = Carbon::parse($date);
		if($number > 0){
			switch($unit){
				case 'day':
					$beginDate = $beginDate->subDays($number - 1);
					break;
				case 'week':
					$beginDate = $beginDate->subWeeks($number - 1)->startOfWeek();
					break;
				case 'month':
					$beginDate = $beginDate->subMonths($number - 1)->startOfMonth();
					break;
				case 'year':
					$beginDate = $beginDate->subYears($number - 1)->startOfYear();
					break;
			}
		}
		return $beginDate->format('Y-m-d') . ',' . $date;
    }
    
    public function graph(Request $request) {
        
        $sql = "select id, title as name from adv_chart where id in (select distinct user_role_chart.chart_id from user_role_chart, user_user_role
        where user_user_role.user_id={$this->user->ID}
        and user_user_role.role_id=user_role_chart.role_id)
        order by name";


        $ds = \DB::table('CHART_GROUP')->select('ID', 'NAME')->get();
        $d_items = [];
        $d_default = '';
        foreach($ds as $d){
            if(!$d_default) $d_default = $d->ID;
            $d_items[] = ['value' => $d->ID, 'name' => $d->NAME];
        }
        $chartGroups = ['defaultValue' => $d_default, 'items' => $d_items];

        $ds = \DB::table('ADV_CHART')->where(['GROUP_ID' => $d_default])->select('ID', 'TITLE as NAME', 'CONFIG')->orderBy('NAME')->get();
        $d_items = [];
        $chartId = $request->exists('id') ? $request->id : '';
        foreach($ds as $d){
            $d_items[] = ['value' => $d->ID, 'name' => $d->NAME, 'config' => $d->CONFIG];
        }
        $charts = ['defaultValue' => $chartId, 'items' => $d_items];

        $tmpData = $request->exists('data') ? json_decode($request->data) : null;
        $chartData = null;
        if($tmpData){
            $chartData = [];
            foreach($tmpData as $index => $seri){
                $chartData[$index] = [];
                for($i = 0, $l = count($seri); $i < $l; $i += 2)
                    $chartData[$index][] = [($i? $seri[0] + $seri[$i] : $seri[0]) * 1000, $seri[$i + 1]];
            }
        }
        $newData = null;
        $chartSettings = null;
        $date = $request->exists('date') ? $request->date : date('Y-m-d');
        if($chartId > 0) {
            $newData = $this->getChartData($chartId, $date, $chartSettings);
            if($chartData){
                foreach($chartData as $index => $seri){
                    $seri = array_merge($seri, $newData[$index]);
                }
            }
        }

        $EB = [];
        $this->buildCommonStaticFilters($EB);
        return view ( 'eb.graph', [
            'chartGroups' => $chartGroups,
            'charts' => $charts,
            'date' => $date,
            'chartId' => $chartId ? $chartId : 0,
            'chartSettings' => $chartSettings,
            'chartData' => $chartData,
            'EB' => $EB,
        ]);
    }
    
    function diagram(Request $request, $diagramType = 1) {
        $diagram = false;
        $diagramID = $request->exists('id') ? $request->id : '';
        if($diagramID)
            $diagram = \App\Models\NetWork::where(['ID' => $diagramID, 'NETWORK_TYPE' => $diagramType])->select('ID', 'NAME', 'XML_CODE')->first();
        if(!$diagram)
            $diagram = ['ID' => 0, 'NAME' => '[Untitled Diagram]', 'XML_CODE' => ''];
        $EB = [];
        $this->buildCommonStaticFilters($EB);
        $EB['charts'] = \App\Models\AdvChart::whereNotNull('GROUP_ID')->select('ID as value', 'TITLE as name', 'CONFIG as config')->orderBy('TITLE')->get();
        return view ( 'eb.diagram', ['EB' => $EB, 'diagram' => $diagram, 'diagramType' => $diagramType]);
    }

    public function configDiagram(Request $request) {
        return $this->diagram($request, 1);
    }

    public function configDashboard(Request $request) {
        return $this->diagram($request, 2);
    }

    public function configWorkflow(Request $request) {
        return $this->diagram($request, 3);
    }

    public function configAlloc(Request $request) {
        return $this->diagram($request, 4);
    }

    function buildCommonStaticFilters(&$EB){
        $EB['extraFilters'] = [
            'selectFlowPhase' => static::buildFilter('CodeFlowPhase', 'selectFlowPhase'),
            'selectEventType' => static::buildFilter('CodeEventType', 'selectEventType'),
            'selectAllocType' => static::buildFilter('CodeAllocType' ,'selectAllocType'),
            'selectPlanType' => static::buildFilter('CodePlanType', 'selectPlanType'),
            'selectForecastType' => static::buildFilter('CodeForecastType', 'selectForecastType'),
            'selectPort' => static::buildFilter('PdPort', 'selectPort'),
            'selectMeasureType' => static::buildFilter('PdTripMeasCode', 'selectPort'),
        ];
    }

    function saveFav(Request $request){
        $fav = $request->exists('fav') ? $request->fav : '';
        \App\Models\UserWorkspace::where('USER_ID', '=', $this->user->ID)->update(['FAV' => $fav]);
    }
}
	