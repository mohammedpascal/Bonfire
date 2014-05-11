<?php

$view = '
<div class="alert alert-block alert-error fade in" ng-show="_error">
	<a class="close" data-dismiss="alert">&times;</a>
	<h4 class="alert-heading">
		{{_error}}
	</h4>
</div>

<form class="form-horizontal" ng-init="delete=false" >
		<fieldset>';

$on_click = '';
$xinha_names = '';

for ($counter = 1; $field_total >= $counter; $counter++)
{
	$maxlength = NULL; // reset this variable

	// only build on fields that have data entered.
	//Due to the requiredif rule if the first field is set the the others must be

	if (set_value("view_field_label$counter") == NULL)
	{
		continue;   // move onto next iteration of the loop
	}

	$field_label = set_value("view_field_label$counter");
	$form_name  = $module_name_lower . '_' . set_value("view_field_name$counter");
	$field_name = set_value("view_field_name$counter");
	$field_type = set_value("view_field_type$counter");

	$validation_rules = $this->input->post('validation_rules'.$counter);

	$required = '';
	if (is_array($validation_rules))
	{
		// rules have been selected for this fieldset

		foreach ($validation_rules as $key => $value)
		{
			if ($value == 'required')
			{
				$required = ". lang('bf_form_label_required')"; //' <span class="required">*</span>';
			}

		}
	}

	// field type
	switch($field_type)
	{
		// Some consideration has gone into how these should be implemented
		// I came to the conclusion that it should just setup a mere framework
		// and leave helpful comments for the developer
		// Modulebuilder is meant to have a minimium amount of features.
		// It sets up the parts of the form that are repitive then gets the hell out
		// of the way.

		// This approach maintains these aims/goals

		case('textarea'):

			if ( ! empty($textarea_editor))
			{
				// Setup the editor for textareas
				if ($textarea_editor == 'xinha')
				{
					if ($xinha_names != '')
					{
						$xinha_names .= ', ';
					}
					$xinha_names .= '\'' . $field_name . '\'';
				}
			}

			$view .= PHP_EOL . "
			<div class=\"control-group <?php echo form_error('{$field_name}') ? 'error' : ''; ?>\">
				<?php echo form_label('{$field_label}'{$required}, '{$form_name}', array('class' => 'control-label') ); ?>
				<div class='controls'>
					<?php echo form_textarea( array( 'name' => '{$form_name}', 'ng-model'=>'record.{$field_name}', 'id' => '{$form_name}', 'rows' => '5', 'cols' => '80' ) ); ?>
					<span class='help-inline'><?php echo form_error('{$field_name}'); ?></span>
				</div>
			</div>";

			break;

		case('radio'):

			$view .= PHP_EOL . "
			<div class=\"control-group <?php echo form_error('{$field_name}') ? 'error' : ''; ?>\">
				<?php echo form_label('{$field_label}'{$required}, '', array('class' => 'control-label', 'id' => '{$form_name}_label') ); ?>
				<div class='controls' aria-labelled-by='{$form_name}_label'>
					<label class='radio' for='{$form_name}_option1'>
						<input id='{$form_name}_option1' ng-model='record.{$field_name}' name='{$form_name}' type='radio' class=''  />
						Radio option 1
					</label>
					<label class='radio' for='{$form_name}_option2'>
						<input id='{$form_name}_option2' ng-model='record.{$field_name}' name='{$form_name}' type='radio' class='' />
						Radio option 2
					</label>
					<span class='help-inline'><?php echo form_error('{$field_name}'); ?></span>
				</div>
			</div>";

			break;

		case('select'):

			$view .= PHP_EOL . "
			<div class=\"control-group <?php echo form_error('{$field_name}') ? 'error' : ''; ?>\">
				<?php echo form_label('{$field_label}'{$required}, '{$form_name}', array('class' => 'control-label') ); ?>
				<div class='controls'>
					<select id='{$form_name}' type='{$type}' ng-model='record.{$field_name}' name='{$form_name}' {$maxlength} >
						<option value='1'>1</option>
					</select>
					<span class='help-inline'><?php echo form_error('{$field_name}'); ?></span>
				</div>
			</div>";

			break;

		case('checkbox'):

			$view .= PHP_EOL . "
			<div class=\"control-group <?php echo form_error('{$field_name}') ? 'error' : ''; ?>\">
				<?php echo form_label('{$field_label}'{$required}, '{$form_name}', array('class' => 'control-label') ); ?>
				<div class='controls'>
					<label class='checkbox' for='{$form_name}'>
						<input type='checkbox' id='{$form_name}' ng-model='record.{$field_name}' name='{$form_name}' value='1' >
						<span class='help-inline'><?php echo form_error('{$field_name}'); ?></span>
					</label>
				</div>
			</div>";

			break;

		case('input'):
		case('password'):
		default: // input.. added bit of error detection setting select as default

			if ($field_type == 'input')
			{
				$type = 'text';
			}
			else
			{
				$type = 'password';
			}
			if (set_value("db_field_length_value$counter") != NULL)
			{
				$maxlength = 'maxlength="' . set_value("db_field_length_value$counter") . '"';

				if (set_value("db_field_type$counter") == 'DECIMAL' || set_value("db_field_type$counter") == 'FLOAT' || set_value("db_field_type$counter") == 'DOUBLE')
				{
					list($len, $decimal) = explode(",", set_value("db_field_length_value$counter"));
					$max = $len;

					if (isset($decimal) && $decimal != 0)
					{
						$max = $len + 1; // Add 1 to allow for the decimal
					}

					$maxlength = 'maxlength="' . $max . '"';
				}
			}
			$db_field_type = set_value("db_field_type$counter");

			$view .= PHP_EOL . "
			<div class=\"control-group <?php echo form_error('{$field_name}') ? 'error' : ''; ?>\">
				<?php echo form_label('{$field_label}'{$required}, '{$form_name}', array('class' => 'control-label') ); ?>
				<div class='controls'>
					<input id='{$form_name}' type='{$type}' ng-model='record.{$field_name}' name='{$form_name}' {$maxlength}  />
					<span class='help-inline'><?php echo form_error('{$field_name}'); ?></span>
				</div>
			</div>";

			break;

	} // end switch
} // end for loop

if ( ! empty($on_click))
{
	$on_click .= '"';
}

$delete = '';

if ($action_name != 'create')
{
	$delete_permission = preg_replace("/[ -]/", "_", ucfirst($module_name)).'.'.ucfirst($controller_name).'.Delete';

	$delete = '
			<?php if ($this->auth->has_permission(\'' . $delete_permission . '\')) : ?>
				&nbsp; <button class="btn btn-danger" ng-click="delete=true" >Delete</button>
			<?php endif; ?>';

}

$view .= PHP_EOL . '
			<div class="form-actions" ng-show="_wait!=true && delete==false">
				<input type="submit" ng-click="saveRecord()" class="btn btn-primary" value="<?php echo lang(\''.$module_name_lower.'_action_'.$action_name.'\'); ?>"  />
				&nbsp;
				<button class="btn btn-warning" ng-click="cancel()" ><?php echo lang(\''.$module_name_lower.'_cancel\'); ?></button>
				' . $delete . '
			</div>

			<div class="form-actions" ng-show="_wait!=true && delete==true" >
				<span >Are you sure?</span>
				<button class="btn btn-primary" ng-click="deleteRecord(); delete=false"  > Yes </button>
				<button class="btn btn-warning" ng-click="delete=false" > No </button>
			</div>

			<div class="form-actions" ng-show="_wait==true">
				<p>Please wait...</p>
			</div>
		
		</fieldset>
    </form>';

if ($xinha_names != '')
{
	$view .= PHP_EOL . '
	<script type="text/javascript">
		var xinha_plugins = [ \'Linker\' ],
			xinha_editors = [ ' . $xinha_names . ' ];

		function xinha_init() {
			if ( ! Xinha.loadPlugins(xinha_plugins, xinha_init)) {
				return;
			}

			var xinha_config = new Xinha.Config();

			xinha_editors = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);
			Xinha.startEditors(xinha_editors);
		}
		xinha_init();
	</script>';

}

echo $view;
