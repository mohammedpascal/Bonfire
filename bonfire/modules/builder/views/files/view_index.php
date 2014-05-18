
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$Module_name = ucfirst($module_name);
$api_url = SITE_AREA .'/' . $controller_name . '/' . $module_name_lower;

$view =<<<END
<script type="text/javascript">

	var {$module_name_lower}Module = angular.module('{$module_name_lower}Module',['ngRoute', 'CIBonfire']);

	{$module_name_lower}Module.constant('{$module_name_lower}_url', '<?=site_url("$api_url")?>');

	{$module_name_lower}Module.controller('{$Module_name}Controller', {$Module_name}Controller);


	function {$Module_name}Controller (\$scope, \$http, \$timeout, {$module_name_lower}_url, BFModule) {
		\$scope.records = [];

		\$scope.record = {};
		\$scope.oldRecord = {};
		\$scope._info = "";
		\$scope._wait = "";
		\$scope._error = "";
		
		init();

		function init(){
			BFModule.create({$module_name_lower}_url).list({}, function(response){
				\$scope.records = response.data;
			});
		}

		\$scope.editRecord = function(record){
			\$scope.oldRecord = record;
			\$scope.record = angular.copy(record);
			\$scope.active = "edit";
		}

		\$scope.saveRecord = function(){
			\$scope._wait = true;
			BFModule.create({$module_name_lower}_url).save( \$scope.record, function(response){
				\$scope._wait = false;
				if ( response.success ) {
					if ( \$scope.record.id ){ //update
						angular.copy(\$scope.record, \$scope.oldRecord);
					}else{ //create
						\$scope.records.push(response.data);
						\$scope.record = {};
					}
					
					\$scope.active = "list";
				}else{
					\$scope.showError(response.error);
				}
			});
		}

		\$scope.deleteRecord = function(){
			\$scope._wait = true;
			BFModule.create({$module_name_lower}_url).delete( \$scope.record.id, function(response){
				\$scope._wait = false;
				if ( response.success ) {
					var index=\$scope.records.indexOf(\$scope.oldRecord);
	  				\$scope.records.splice(index,1); 
					\$scope.showInfo(response.data);
					\$scope.active = "list";
				}else{
					\$scope.showError(response.error);
				}
			});
		}

		\$scope.cancel = function(){
			\$scope.record = {};
			\$scope.active = "list";
		}

		\$scope.showInfo = function(info){
			\$scope._info = info;
			\$timeout(function(){
	            \$scope._info = "";
	        },3000);
		}

		\$scope.showError = function(error){
			\$scope._error = error;
			\$timeout(function(){
	            \$scope._error = "";
	        },3000);
		}
	}

</script>

<?php \$can_edit = \$this->auth->has_permission('{edit_permission}'); ?>

<div class="admin-box"  ng-app="{$module_name_lower}Module" ng-controller="{$Module_name}Controller" >

	<div class="alert alert-success" ng-show="_info">
		{{_info}}
	</div>

	<ul class="nav nav-pills" ng-init="active='list';loaded=true">
	  <li ng-click="active='list'" ng-class="{ active: active=='list' }" ><a href="#">List</a></li>
	  <li ng-click="active='create'; record={}" ng-class="{ active: active=='create' }" ><a href="#">Create</a></li>
	  <li ng-show="active=='edit'" ng-class="{ active: active=='edit' }" ><a href="#">Edit</a></li>
	</ul>

	<br>

	<div  ng-show="active=='create'" ng-include="'<?=site_url("$api_url")?>/file/create.php'"></div>

	<div  ng-show="active=='edit'" ng-include="'<?=site_url("$api_url")?>/file/edit.php'"></div>

	<div class="input-prepend" ng-show="active=='list' && records" >
	  <span class="add-on">Filter</span>
	  <input class="span2" id="prependedInput" type="text" ng-model="filterText" placeholder="keyword">
	</div>

	<table class="table table-striped table-bordered" ng-show="active=='list' && records" >

		<tr>
			<th>ID</th>
			{table_header} 
		</tr>
		<tr ng-repeat="row in records" >
			<td ng-cloak >{{row.id}}</td>
			{table_records}
			<?php if (\$can_edit) : ?>
			<td> <a href="#" ng-click="editRecord(row)"><span class="icon-pencil"></span> Edit</a></td>
			<? endif ?>
		</tr>
	</table>

	<div class="well" ng-show="active=='list' && records.length==0" >
		No records found
	</div>

	<br> <br> <br>

</div>
END;

$headers = '';
for ($counter = 1; $field_total >= $counter; $counter++)
{
	// only build on fields that have data entered.

	// Due to the required if rule if the first field is set the others must be
	if (set_value("view_field_label$counter") == NULL)
	{
		continue; 	// move onto next iteration of the loop
	}
	$label = set_value("view_field_label$counter");
	$name = set_value("view_field_name$counter");
	$headers .= '
			<th><?php echo lang("' . $module_name_lower . '_field_'.$name.'"); ?></th>';
}

$headers .= '
			<th style="width: 50px"> <span class="icon-pencil"></span> Edit</th>';

$field_prefix = '';

// only add maintenance columns to view when module is creating a new db table
// (columns should already be present and handled below when existing table is used)
if ($db_required == 'new')
{
	if ($use_soft_deletes == 'true')
	{
		$headers .= '
					<th><?php echo lang("' . $module_name_lower . '_column_deleted"); ?></th>';
	}
	if ($use_created == 'true')
	{
		$headers .= '
					<th><?php echo lang("' . $module_name_lower . '_column_created"); ?></th>';
	}
	if ($use_modified == 'true')
	{
		$headers .= '
					<th><?php echo lang("' . $module_name_lower . '_column_modified"); ?></th>';
	}
    if ($table_as_field_prefix === TRUE)
    {
        $field_prefix = $module_name_lower . '_';
    }
}

$table_records = '';
$pencil_icon   = "'<span class=\"icon-pencil\"></span> ' . ";
for ($counter = 1; $field_total >= $counter; $counter++)
{
	// only build on fields that have data entered.

	//Due to the requiredif rule if the first field is set then the others must be

	if (set_value("view_field_name$counter") == NULL || set_value("view_field_name$counter") == $primary_key_field)
	{
		continue; 	// move onto next iteration of the loop
	}

	$field_name = $field_prefix . set_value("view_field_name$counter");

	
	$table_records .= "
			<td no-cloak >{{row.$field_name}}</td>";
	
}

// only add maintenance columns to view when module is creating a new db table
// (columns should already be present and handled above when existing table is used)
if($db_required == 'new')
{
	if ($use_soft_deletes == 'true')
	{
		$table_records .= '
					<td><?php echo $record->'.set_value("soft_delete_field").' > 0 ? lang(\''.$module_name_lower.'_true\') : lang(\''.$module_name_lower.'_false\')?></td>';
		$field_total++;
	}
	if ($use_created == 'true')
	{
		$table_records .= '
					<td><?php e($record->'.set_value("created_field").') ?></td>';
		$field_total++;
	}
	if ($use_modified == 'true')
	{
		$table_records .= '
					<td><?php e($record->'.set_value("modified_field").') ?></td>';
		$field_total++;
	}
}

$view = str_replace('{cols_total}', $field_total + 1 , $view);
$view = str_replace('{table_header}', $headers, $view);
$view = str_replace('{table_records}', $table_records, $view);
$view = str_replace('{delete_permission}', preg_replace("/[ -]/", "_", ucfirst($module_name)).'.'.ucfirst($controller_name).'.Delete', $view);
$view = str_replace('{edit_permission}', preg_replace("/[ -]/", "_", ucfirst($module_name)).'.'.ucfirst($controller_name).'.Edit', $view);

echo $view;

unset($view, $headers);