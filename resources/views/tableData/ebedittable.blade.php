<html>
<head>
	<meta charset='UTF-8' />
	<link rel='stylesheet' type='text/css' href='/common/css/style.css?2'  />
    <link rel="stylesheet" href="/common/css/jquery-ui.css" />
	<link rel='stylesheet' href='/common/css/tableDataStyle.css?1'  />
	<script src="/common/js/jquery-2.1.3.js"></script>
	<script src="/common/js/jquery-ui.js"></script>
 	<script src="/common/js/colResizable-1.6.js"></script> 

	<script type="text/javascript" src="/common/js/jquery.dataTables.lazy_mofo.js"></script>
	<script type="text/javascript" src="/common/js/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="/common/js/jquery-ui-sliderAccess.js"></script>
	<script type="text/javascript" src="/common/js/jquery.dataTables.js"></script> 
	
	<script>var disableSearchingTargets = [];</script>
	<script type="text/javascript" src="/common/js/utils.js?12"></script>
	<script type="text/javascript" src="/common/js/eb.js?30"></script>
 
</head>
<body>
<div id='box_sql' style='display:none;'>
Insert:
<textarea id='sql_insert' style='width:700px;height:100px;margin-bottom:10px'>
</textarea>
<br>
Update:
<textarea id='sql_update' style='width:700px;height:100px;margin-bottom:10px'>
</textarea>
</div>
<div id='box_sql2' style='display:none;'>
	<textarea id='sql_2' style='width:700px;height:300px;margin-bottom:10px'></textarea>
</div>
<script>
    // Processors for tables larger than 1000 row
    $( document ).ready(function() {
        $('.active_page_0').change( function () {
            var value = $(this).val();
            $('.active_page_0').val(value);
        }).keyup( function () {
                $(this).change();
            });
    });
</script>
<?php
error_reporting(E_ERROR);
// $tablename=$_REQUEST['table'];
// $action=$_REQUEST['action'];
$global_table_name=$tablename;
//echo $tablename;
// enter your database host, name, username, and password

// header('Content-Type: text/html; charset=utf-8');
/* 
include('lazy_mofo.php');

$db_host = 'localhost';
$db_name = 'energy_builder_from_server';
$db_user = 'root';
$db_pass = '';

// connect with pdo 
try {
	$dbh = new PDO("mysql:host=$db_host;dbname=$db_name;", $db_user, $db_pass);
}
catch(PDOException $e) {
	die('pdo connection error: ' . $e->getMessage());
}

// create LM object, pass in PDO connection
$lm = new lazy_mofo($dbh); 
 */

// table name for updates, inserts and deletes
// $lm->table = $tablename;

// identity / primary key for table
/*if(strtolower(substr($lm->table,0,5))=="code_")
{
	$lm->identity_name = 'id';
}
else if(strtolower(substr($lm->table,0,3))=="lo_")
{
	$lm->identity_name = 'id';
}
else
{
	$lm->identity_name = 'id';
}
*/

$rel_parents=array();
$rel_columns=array();
$rel_tables=array();
$lm->grid_show_search_box = true;

$checkbox_fields = ['ACTIVE',
'ALLOC_ADJUST',
'ALLOC_COMP',
'ALLOC_CONDENSATE',
'ALLOC_GAS',
'ALLOC_GASLIFT',
'ALLOC_INCLUDED',
'ALLOC_OIL',
'ALLOC_WATER',
'ALLOW_OVERRIDE',
'BED_AVAILABLE',
'CH4_EMISSION',
'CO2_EMISSION',
'CTV',
'DAY_BY_DAY',
'DISABLE_DC',
'ENABLE',
'EXPORT_HTML',
'EXPORT_PDF',
'EXPORT_XLS',
'FDC_DISPLAY',
'FIXED',
'FORECAST_COMP',
'FORECAST_CONDENSATE',
'FORECAST_GAS',
'FORECAST_GASLIFT',
'FORECAST_OIL',
'FORECAST_WATER',
'GRANTED',
'INPUT_ENABLE',
'INPUT_VISIBLE',
'IS_ACTIVE',
'IS_ALLOC_NODE',
'IS_DATE_RANGE',
'IS_DEFAULT',
'IS_IMPORT',
'IS_LOAD',
'IS_MANDATORY',
'MINUS',
'N2O_EMISSION',
'USE_DIAGRAM',
'USE_FDC',
'USE_GRAPH'];

if(config('database.default')==='sqlsrv')
{

	//$schema = 'eb4sqlserver';
	$schema = config('database.connections.sqlsrv.schema');
	$sSQL = "select column_name, data_type, IS_NULLABLE nullable,  column_default FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$tablename' AND TABLE_SCHEMA='$schema'";
	//$sSQL="SHOW COLUMNS FROM $tablename";
	$result=$lm->query($sSQL);
	$sSQL = "SELECT 
   OBJECT_NAME(f.referenced_object_id) table_name,
   COL_NAME(fc.parent_object_id,fc.parent_column_id) column_name
FROM 
   sys.foreign_keys AS f
INNER JOIN 
   sys.foreign_key_columns AS fc 
      ON f.OBJECT_ID = fc.constraint_object_id
INNER JOIN 
   sys.tables t 
      ON t.OBJECT_ID = fc.referenced_object_id
WHERE 
   OBJECT_NAME (f.parent_object_id) = '$tablename'";   
	$tmp=$lm->query($sSQL);
	$refs = [];
	foreach($tmp as $row)
	{
		$refs[$row['column_name']] = $row['table_name'];
	}
	
	foreach($result as $row)
	{
		$column_name=$row['column_name'];

		if($row['nullable']=="NO")
			$lm->required[$column_name]="*";

		if($row['data_type']=="datetime" || $row['data_type']=="datetime2")
		{
			$lm->form_input_control[$column_name]="--datetime";
			$lm->grid_input_control[$column_name]="--datetime";
		}
		else if($row['data_type']=="date")
		{
			$lm->form_input_control[$column_name]="--date";
			$lm->grid_input_control[$column_name]="--date";
		}
		else if(in_array(strtoupper($column_name),$checkbox_fields))
		{
			$lm->form_input_control[$column_name]="--checkbox";
			$lm->grid_input_control[$column_name]="--checkbox";
		}
		else if(strtoupper($column_name)=="ID")
		{
			//$lm->form_input_control[$column_name]="--hidden";
		}
		else
			$lm->grid_input_control[$column_name]="--text";
		if(array_key_exists($column_name, $refs)) // || $row['Key']=='UNI')
		{
			{
				$ref_table=$refs[$column_name];

				{
					$s_where="";
					$s_order="";
					if(strtolower(substr($ref_table,0,5))=='code_')
					{
						$s_where='where active=1';
						$s_order='order by "order",id';
					}
					$lm->form_input_control[$column_name]= "select id, name from [$ref_table] $s_where $s_order --select".($row['nullable']=="YES"?"":"#");
					$lm->grid_input_control[$column_name]= "select id, name from [$ref_table] $s_where $s_order --select".($row['nullable']=="YES"?"":"#");
				}
			}
		}
	}
}
else if(config('database.default')==='oracle')
{
	$sSQL = "select COLUMN_NAME, DATA_TYPE, NULLABLE,  DATA_DEFAULT column_default from user_tab_cols where table_name = '$tablename'";
	//$sSQL="SHOW COLUMNS FROM $tablename";
	$result=$lm->query($sSQL);
	
	$sSQL = "SELECT a.column_name, c_pk.table_name
  FROM user_cons_columns a
  JOIN user_constraints c ON a.owner = c.owner
                        AND a.constraint_name = c.constraint_name
  JOIN user_constraints c_pk ON c.r_owner = c_pk.owner
                           AND c.r_constraint_name = c_pk.constraint_name
 WHERE c.constraint_type = 'R'
   AND a.table_name = '$tablename'";
	$tmp=$lm->query($sSQL);
	$refs = [];
	foreach($tmp as $row)
	{
		$refs[strtolower($row['column_name'])] = $row['table_name'];
	}
	foreach($result as $row)
	{
		$column_name=strtolower($row['column_name']);

		if($row['nullable']=="N")
			$lm->required[$column_name]="*";

		if($row['data_type']=="DATETIME")
		{
			$lm->form_input_control[$column_name]="--datetime";
			$lm->grid_input_control[$column_name]="--datetime";
		}
		else if($row['data_type']=="DATE")
		{
			$lm->form_input_control[$column_name]="--date";
			$lm->grid_input_control[$column_name]="--date";
		}
		else if(in_array(strtoupper($column_name),$checkbox_fields))
		{
			$lm->form_input_control[$column_name]="--checkbox";
			$lm->grid_input_control[$column_name]="--checkbox";
		}
		else if(strtoupper($column_name)=="ID")
		{
			//$lm->form_input_control[$column_name]="--hidden";
		}
		else
			$lm->grid_input_control[$column_name]="--text";
		if(array_key_exists($column_name, $refs)) // || $row['Key']=='UNI')
		{
			{
				$ref_table=$refs[$column_name];

				{
					$s_where="";
					$s_order="";
					if(strtolower(substr($ref_table,0,5))=='code_')
					{
						$s_where='where active=1';
						$s_order='order by order_,id';
					}
					$lm->form_input_control[$column_name]= "select id, name from $ref_table $s_where $s_order --select".($row['nullable']=="Y"?"":"#");
					$lm->grid_input_control[$column_name]= "select id, name from $ref_table $s_where $s_order --select".($row['nullable']=="Y"?"":"#");
				}
			}
		}
	}
}
else
{
	$schema = config('database.connections.mysql.database');
	$sSQL="SHOW COLUMNS FROM $tablename";
	$result=$lm->query($sSQL);
	//$result=mysql_query($sSQL) or die("error:".mysql_error());
	foreach($result as $row)
	{
		$column_name=($row['Field']);

		if($row['Null']=="NO")
			$lm->required[$column_name]="*";

		if($row['Type']=="datetime")
		{
			$lm->form_input_control[$column_name]="--datetime";
			$lm->grid_input_control[$column_name]="--datetime";
		}
		else if($row['Type']=="date")
		{
			$lm->form_input_control[$column_name]="--date";
			$lm->grid_input_control[$column_name]="--date";
		}
		else if($row['Type']=="bit" || $row['Type']=="tinyint(1)" || (strtoupper($column_name)=="ACTIVE"))
		{
			$lm->form_input_control[$column_name]="--checkbox";
			$lm->grid_input_control[$column_name]="--checkbox";
		}
		else if(strtoupper($column_name)=="ID")
		{
			//$lm->form_input_control[$column_name]="--hidden";
		}
		else
			$lm->grid_input_control[$column_name]="--text";
		if($row['Key']=='MUL') // || $row['Key']=='UNI')
		{
			$s2="SELECT REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME
					FROM information_schema.KEY_COLUMN_USAGE
					WHERE TABLE_NAME = '$tablename' and TABLE_SCHEMA='$schema'
					and COLUMN_NAME='$column_name' and REFERENCED_COLUMN_NAME is not null and REFERENCED_TABLE_NAME is not null";
			$ret2=$lm->query($s2);
			if($r2=$ret2[0])//mysql_fetch_array($ret2))
			{
				$ref_table=$r2['REFERENCED_TABLE_NAME'];
				$rel_tables[$ref_table]=$column_name;
				$rel_column="parent_id";
/*
				$rel_sql="SELECT count(1) p_count from information_schema.columns where table_name='$ref_table' and column_name='$rel_column' and TABLE_SCHEMA='$schema'";
				$rel_result=$lm->query($rel_sql);
				$rel_r=$rel_result[0];

				if($rel_r[p_count]==1)
				{
					$rel_s2="SELECT REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME
							FROM information_schema.KEY_COLUMN_USAGE
							WHERE TABLE_NAME = '$ref_table'
							and COLUMN_NAME='$rel_column' and REFERENCED_COLUMN_NAME is not null and REFERENCED_TABLE_NAME is not null and TABLE_SCHEMA='$schema'";
					$rel_ret2=$lm->query($rel_s2);
//echo $rel_s2;
					if($rel_r2=$rel_ret2[0])
					{
						array_push($rel_columns, $column_name);
						$rel_parents[$column_name]=$ref_table."$$".$rel_tables[$rel_r2[REFERENCED_TABLE_NAME]];
					}
				}
*/				
				//else
				{
					$s_where="where 1=1";
					$s_order="";
					$sql="select (SELECT 1 FROM information_schema.`COLUMNS` WHERE `TABLE_NAME`='$ref_table' and `COLUMN_NAME`='name' and TABLE_SCHEMA='$schema') NAME_FIELD,
					(SELECT COLUMN_COMMENT FROM information_schema.`COLUMNS` WHERE `TABLE_NAME`='$tablename' and `COLUMN_NAME`='$column_name' and TABLE_SCHEMA='$schema' and COLUMN_COMMENT like 'FK_FILTER:%') FK_FILTER 
					";
					$r_hf=$lm->query($sql);
					$namefield=$r_hf[0]['NAME_FIELD']?"`name`":$r2[REFERENCED_COLUMN_NAME];
					if($r_hf[0]['FK_FILTER']){
						$fk_filter = substr($r_hf[0]['FK_FILTER'],10);
						$s_where .= ' and '.$fk_filter;
					}
					if(strtolower(substr($ref_table,0,5))=='code_')
					{
						$s_where .= ' and active=1';
						$s_order = 'order by `order`,`id`';
					}
					$lm->form_input_control[$column_name]= "select $r2[REFERENCED_COLUMN_NAME], $namefield from `$ref_table` $s_where $s_order ; --select".($row['Null']=="YES"?"":"#");
					$lm->grid_input_control[$column_name]= "select $r2[REFERENCED_COLUMN_NAME], $namefield from `$ref_table` $s_where $s_order ; --select".($row['Null']=="YES"?"":"#");
				}
			}
		}
	}
}

// optional, define grid sort order
//$lm->grid_default_order_by = 'object_name';


// optional, make friendly names for fields
//$lm->rename = array('country_id' => 'Country');


// optional, define input controls on the form
//$lm->form_input_control = array('photo' => '--image', 'is_active' => '--checkbox', 'country_id' => 'select country_id, country_name from country; --select');


// optional, define editable input controls on the grid
//$lm->grid_input_control = array('is_active' => '--checkbox');


// optional, define output control on the grid; make email clickable and the photo a clickable link
//$lm->grid_output_control = array('contact_email' => '--email', 'photo' => '--image');


// optional, query for grid(). if the last column selected is the primary key identity, then the [edit] and [delete] links are displayed
//$lm->grid_sql = "select m.market_id, m.market_name, m.photo, m.contact_email, c.country_name, m.is_active, m.create_date, m.notes, market_id from market m left join country c on m.country_id = c.country_id order by m.market_id desc";


// optional, define what is displayed on edit form
//$lm->form_sql = 'select market_id, market_name, country_id, photo, contact_email, is_active, create_date, notes from market where market_id = :market_id';
//$lm->form_sql_param = array(':market_id' => intval(@$_REQUEST['market_id']));

/*
// optional, display a related table under the edit record form
$lm->child_title = 'Sub Markets';
$lm->child_table = 'sub_market';
$lm->child_identity_name = 'sub_market_id';
$lm->child_parent_identity_name = 'market_id';
$lm->child_input_control = array('photo' => '--image');
*/

// use the lm controller
$lm->run();
//if($action=='edit' || $action=='insert')
{
?>
<script>

function genSQL(type)
{
	var ids = [];
	$('input[name="_delete[]"]:checked').each(function(){
		ids.push($(this).val());
	});
	if(ids.length == 0)
		return;
		
	$( '#box_sql2' ).dialog({
		width: 730,
		modal: true,
		title: 'Generate SQL'
	});
	$('#sql_2').val('Loading...');
	postRequest('/loadtabledata/gensql',
		{
			table: '<?php echo $tablename; ?>',
			ids: ids,
			type: type
		},
		function(data){
			$('#sql_2').val(data);
		});
}

function _genSQL(id){
	//$('#box_sql').show();
	var ids = [id];
	$( '#box_sql' ).dialog({
		width: 730,
		modal: true,
		title: 'Generate SQL'
	});
	$('#sql_insert').val('Loading...');
	$('#sql_update').val('Loading...');
	postRequest('/loadtabledata/gensql',
		{
			table: '<?php echo $tablename; ?>',
			ids: ids,
			type: 0
		},
		function(data){
			var ds=data.split('~@^@~');
			$('#sql_insert').val(ds[0]);
			$('#sql_update').val(ds[1]);
		});
}

function _delete_rows(){
	var ids="";
	var idarray = [];
	$('input[name="_delete[]"]:checked').each(function(){
		ids+=(ids==""?"":",")+$(this).val();
		idarray.push(ids);
	});
	if(ids!=""&&idarray.length>0){
		if(!confirm('Are you sure you want to detete selected record(s)?'))
			return;
		showWaiting();
		$.ajax({
			url: "/loadtabledata/delete",
			type: "post",
			data: {
					table	: '{{$tablename}}',
					//ids		: idarray,
					ids		: ids
				},
			success:function(data){
				hideWaiting();
				alert(data);
				location.reload();
			},
			error: function(data) {
				hideWaiting();
				alert("delete error");
			}
		});
	}
}
function saveChanges()
{
	$("#button_save_changes").click();
}
function addRecord()
{
	var href = $('.lm_grid_add_link').attr('href');
	var add = "/loadtabledata/edittable?action=edit&table={{$tablename}}";
	if(href){
        window.location.href = href; //causes the browser to refresh and load the requested url
    }else{
        window.location.href = add;
	}
}

function applyTableData()
{
	if(typeof($.fn.editable) !== "undefined") $.fn.editable.defaults.mode = 'inline';
		
		var length = $("#lm_grid").find("tr:first th").length;
		var targets = Array.from(Array(length).keys());
		var dTargets = typeof(disableSearchingTargets) !== "undefined"?disableSearchingTargets:[];
		dTargets = dTargets.filter(function(elem, index, self) {
		    return index == self.indexOf(elem);
		});

		var uoms = [];
		$.each(dTargets, function( index, value ) {
	 		var uom = { targets		: value};
 		uom["createdCell"] 	= function (td, cellData, rowData, row, col) {
 			var select 		= $(cellData);
 			var source 		= select.toArray();
 			source = select.find('option').map(function () {
 			    if (this.value != "") { // you can compare value here
 			        return {value	: this.value,text:this.text};
 			    }
 			}).get();

 			cellData 		= select.find(":selected").val();
 			cellDataValue 	= select.find(":selected").text();
				var newId 		= select.attr('id');
				$("#"+newId).change(function (e) {
					cellDataValue 	= $("#"+newId).find(":selected").text();
	 				rowData[col] 	= cellDataValue;
 			});
				/* $(td).editable({
        	    type		: 'select',
        	    title		: 'edit',
        	    emptytext	: '',
        	    onblur		: 'cancel',
        	    value		: cellData,
        	    showbuttons	: false,
        	    source		: source,
	        	    success		: function(response, newValue) {
//				 						$(td).html("<input  type='hidden' id='"+newId+"'value='"+newValue+"'  name='"+select.attr('name')+"'></input>");
				 						$("#"+newId).val(newValue);
				 		 				rowData[col] = cellDataValue;
							  },
				  display	: function(value, sourceData) {}
        	}); */
				rowData[col] = cellDataValue;
//				$(td).html(cellDataValue+"<input  type='hidden' id='"+newId+"'value='"+cellData+"'  name='"+select.attr('name')+"'></input>");
		}
 		uoms.push(uom);
    });
		uoms.push({
        orderable	: false,
        targets		: targets
     });
	var tbl = $('#lm_grid').DataTable({	searching	: true,
										ordering	: false,
										destroy		: true,
										paging		: false,
										scrollX		: false,
										autoWidth	: false,
										info		: false,
								        columnDefs	: uoms,
//									        dom			: '<"top"f>rt<"bottom"ilp><"clear">'
								      } );
}
 $(function() {
		$( ".lm_date_select" ).datepicker({
		    changeMonth:true,
		     changeYear:true,
		     dateFormat:"mm/dd/yy"
		});

		$(".lm_datetime_select").datetimepicker();

		$("#lm_grid").colResizable({
			liveDrag:true, 
			gripInnerHtml:"<div class='grip'></div>", 
			draggingClass:"dragging"
			});
		
 		$("#lm_grid").wrap("<div id='par' style='width:"+($("#lm_grid").width()+10)+"px'></div>");

 		applyTableData();
  });
<?php
//foreach($rel_columns as $rel_col)
//{
//	$rel_cols=explode("$$",$rel_parents[$rel_col]);
//	echo <<<EOT
//$("#$rel_cols[1]").change(function(e){
//	postRequest(
//	             "../common/getcodelist.php",
//	             {table:"$rel_cols[0]",id:"",parent_field:"parent_id",parent_value:$(this).val()},
//	             function(data) {
//	             	$('#$rel_col').html(data);
//	             	$('#$rel_col').change();
//	             }
//	          );
//});
//$("#$rel_cols[1]-1").change(function(e){
//	postRequest(
//	             "../common/getcodelist.php",
//	             {table:"$rel_cols[0]",id:"",parent_field:"parent_id",parent_value:$(this).val()},
//	             function(data) {
//	             	$('#$rel_col-1').html(data);
//	             	$('#$rel_col-1').change();
//	             }
//	          );
//});
//EOT;
//}
?>
  </script>
<?php
}
?>
</body>
</html>