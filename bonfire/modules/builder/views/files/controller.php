<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
	Define the various parts of the class here as variables with
	{placeholders} for variable data. Below, we'll replace the parts
	as needed.

	This should make modifying the way the class is built much easier.
*/

$controller_name_lower = strtolower($controller_name);
$primary_key_field = set_value("primary_key_field");

//--------------------------------------------------------------------
// !CLASS PARTS
//--------------------------------------------------------------------

$mb_class_wrapper =<<<END
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * {$controller_name} controller
 */
class {$controller_name} extends {extend_class}
{

	//--------------------------------------------------------------------

{class_content}

}
END;

//--------------------------------------------------------------------

$mb_constructor = "
	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		\$this->is_api = isset( \$this->uri->segments[4]) && strpos(\$this->uri->segments[4], 'api_') == 0 ;

		if ( \$this->is_api ){
			\$this->_restrict('Contact.Content.View');
			header('Content-type: application/json');
		}

		{restrict}";

if ($db_required != '')
{
	$mb_constructor .= "
		\$this->load->model('" . $module_name_lower . "_model', null, true);";
}

$mb_constructor .= "
		\$this->lang->load('" . $module_name_lower . "');
		{constructor_extras}";

// check that it is an admin area controller before adding the sub_nav
if ($controller_name != $module_name_lower)
{
	$mb_constructor .= "
		//Template::set_block('sub_nav', '" . $controller_name_lower . "/_sub_nav');";
}

$mb_constructor .= '
		Assets::add_js("codeigniter-csrf.js");
		Assets::add_module_js(\'' . $module_name_lower . '\', \'' . $module_name_lower . '.js\');
	}

	//--------------------------------------------------------------------

';

//--------------------------------------------------------------------

$mb_index = '
	/**
	 * Displays a list of form data.
	 *
	 * @return void
	 */
	public function index()
	{
';

$mb_index .= "
		Template::set('toolbar_title', 'Manage ".$module_name."');
		Template::render();
	}

	//--------------------------------------------------------------------

";

//--------------------------------------------------------------------

$mb_index_front = "
	/**
	 * Displays a list of form data.
	 *
	 * @return void
	 */
	public function index()
	{
";

if ($db_required != '')
{
	$mb_index_front .= "
		\$records = \$this->".$module_name_lower."_model->find_all();

		Template::set('records', \$records);";
}

$mb_index_front .= "
		Template::render();
	}

	//--------------------------------------------------------------------

";

//--------------------------------------------------------------------

//starts with capital letter
$Module_name = ucfirst($module_name);

$mb_save =<<<END
	//--------------------------------------------------------------------
	// !PRIVATE METHODS
	//--------------------------------------------------------------------

	/**
	 * Summary
	 *
	 * @param String \$type Either "insert" or "update"
	 * @param Int	 \$id	The ID of the record to update, ignored on inserts
	 *
	 * @return Mixed    An INT id for successful inserts, TRUE for successful updates, else FALSE
	 */
	private function save_{$module_name_lower}(\$type='insert', \$id=0)
	{
		if (\$type == 'update')
		{
			\$_POST['{$primary_key_field}'] = \$id;
		}

		// make sure we only pass in the fields we want
		{save_data_array}

		if (\$type == 'insert')
		{
			\$id = \$this->{$module_name_lower}_model->insert(\$data);

			if (is_numeric(\$id))
			{
				\$return = \$id;
			}
			else
			{
				\$return = FALSE;
			}
		}
		elseif (\$type == 'update')
		{
			\$return = \$this->{$module_name_lower}_model->update(\$id, \$data);
		}

		return \$return;
	}

	//--------------------------------------------------------------------
	// !API METHODS
	//--------------------------------------------------------------------

	public function file(\$file){
		\$this->load->view("content/\$file");
	}

	private function _restrict(\$perm, \$msg='Permission denied'){
		if ( !\$this->auth->has_permission(\$perm) ){
			\$this->_error(\$msg);
		}
	}

	private function _error(\$msg){
		\$response->success = false;
		\$response->error = \$msg;
		die(json_encode(\$response));
	}

	//POST module/api_list { skip:0, limit: 10}
	public function api_list(){
		\$this->_restrict('{$Module_name}.Content.View', 'Permission denied');
		header('Content-type: application/json');
		\$json = isset(\$_POST['json']) ? \$_POST['json'] : (object)array();
		
		\$skip = isset(\$json->skip) ? intval(\$json->skip) : 0;
		\$limit = isset(\$json->limit) ? intval(\$json->limit) : 0;

		\$records = \$limit == 0 ? \$this->{$module_name_lower}_model->offset(\$skip)->find_all() : \$this->{$module_name_lower}_model->offset(\$skip)->limit(\$limit)->find_all();
		\$this->output->append_output('{"success":true, "data":[');
		if ( !\$records ){
			\$this->output->append_output(']}');
			return;
		}

		\$first = true;
		foreach (\$records as \$row) {
			if ( \$first ){
				\$this->output->append_output(json_encode(\$row));
				\$first = false;
			}else{
				\$this->output->append_output(','.json_encode(\$row));
			}
			 
		}
		\$this->output->append_output("]}");
	}

	//POST modile/api_save { }
	public function api_save(){
		header('Content-type: application/json');
		\$this->_restrict('{$Module_name}.Content.Edit', 'Permission denied');
		\$json = \$_POST['json'];
		
		\$id = isset(\$json->id) ? \$json->id : 0 ;

		unset(\$_POST['json']);
		\$_POST = (array)\$json;

		if ( \$id === 0 ){
			if ( \$insert_id = \$this->save_{$module_name_lower}() ){
				\$response->success = true;
				\$response->data = \$this->{$module_name_lower}_model->find(\$insert_id);
				die(json_encode(\$response));
			}

			\$this->_error(lang('{$module_name_lower}_create_failure')."\\n".validation_errors().\$this->{$module_name_lower}_model->error);
		}

		if (\$this->save_{$module_name_lower}('update', \$id)){
			\$response->success = true;
			\$response->data = \$this->{$module_name_lower}_model->find(\$id);
			die(json_encode(\$response));
		}

		\$this->_error(lang('{$module_name_lower}_edit_failure').' '.validation_errors().' '.\$this->{$module_name_lower}_model->error);
	}

	//POST module/api/get { id: 12 }
	public function api_get(){
		\$this->_restrict('{$Module_name}.Content.View', 'Permission denied');
		header('Content-type: application/json');
		\$json = isset(\$_POST['json']) ? \$_POST['json'] : (object)array();

		\$record =  isset(\$json->id) ? \$this->{$module_name_lower}_model->find(intval(trim(\$json->id))) : false;

		if ( !\$record ){
			\$result->error = 'record not found';
			\$result->success = false;
			die(json_encode(\$result));
		}

		\$result->data = \$record;
		\$result->success = true;

		die(json_encode(\$result));
	}

	//POST module/api_delete { id: 12 }
	public function api_delete(){
		\$this->_restrict('{$Module_name}.Content.Delete', 'Permission denied');
		header('Content-type: application/json');
		\$json = isset(\$_POST['json']) ? \$_POST['json'] : (object)array();

		\$deleted =  isset(\$json->id) ? \$this->{$module_name_lower}_model->delete(intval(trim(\$json->id))) : false;

		if ( \$deleted ){
			\$response->success = true;
			\$response->data = lang('{$module_name_lower}_delete_success');
			die(json_encode(\$response));
		}
		
		\$this->_error(lang('{$module_name_lower}_delete_failure') . \$this->{$module_name_lower}_model->error );
	}

END;

//--------------------------------------------------------------------
// !BUILD THE CLASS
//--------------------------------------------------------------------

// Constructor
$body = $mb_constructor;

if ($controller_name == $module_name_lower)
{
	$body = str_replace('{restrict}', '$this->load->library(\'form_validation\');', $body);
}
else
{
	$body = str_replace('{restrict}', '$this->auth->restrict(\''.preg_replace("/[ -]/", "_", ucfirst($module_name)).'.'.ucfirst($controller_name).'.View\');', $body);
}

$extras = '';
$date_included = FALSE;
$datetime_included = FALSE;
$textarea_included = FALSE;

for ($counter = 1; $field_total >= $counter; $counter++)
{
	$db_field_type = set_value("db_field_type$counter");
	$view_datepicker = '';

	if ($db_field_type != NULL)
	{
		if ($db_field_type == 'DATE' && $date_included === FALSE)
		{
			$extras .= '
			Assets::add_css(\'flick/jquery-ui-1.8.13.custom.css\');
			Assets::add_js(\'jquery-ui-1.8.13.min.js\');';
			$date_included = TRUE;
		}
		elseif ($db_field_type == 'DATETIME' && $datetime_included === FALSE)
		{
			// if a date field hasn't been included already then add in the jquery ui files
			if ($date_included === FALSE)
			{
				$extras .= '
			Assets::add_css(\'flick/jquery-ui-1.8.13.custom.css\');
			Assets::add_js(\'jquery-ui-1.8.13.min.js\');';
			}
			$extras .= '
			Assets::add_css(\'jquery-ui-timepicker.css\');
			Assets::add_js(\'jquery-ui-timepicker-addon.js\');';
			$date_included = TRUE;
			$datetime_included = TRUE;
		}
		elseif (($db_field_type == 'TEXT' || $db_field_type == 'MEDIUMTEXT' || $db_field_type == 'LONGTEXT')
			&& $textarea_included === FALSE
			&& ! empty($textarea_editor)
		)
		{
			// if a date field hasn't been included already then add in the jquery ui files
			if ($textarea_editor == 'ckeditor')
			{
				$extras .= '
			Assets::add_js(Template::theme_url(\'js/editors/ckeditor/ckeditor.js\'));';
			}
			elseif ($textarea_editor == 'xinha')
			{
				$extras .= '
			Assets::add_js(Template::theme_url(\'js/editors/xinha_conf.js\'));
			Assets::add_js(Template::theme_url(\'js/editors/xinha/XinhaCore.js\'));';
			}
			elseif ($textarea_editor == 'markitup')
			{
				$extras .= '
			Assets::add_css(Template::theme_url(\'js/editors/markitup/skins/markitup/style.css\'));
			Assets::add_css(Template::theme_url(\'js/editors/markitup/sets/default/style.css\'));

			Assets::add_js(Template::theme_url(\'js/editors/markitup/jquery.markitup.js\'));
			Assets::add_js(Template::theme_url(\'js/editors/markitup/sets/default/set.js\'));';
			}
			elseif ($textarea_editor == 'tinymce')
			{
				$extras .= '
			Assets::add_js(Template::theme_url(\'js/editors/tiny_mce/tiny_mce.js\'));
			Assets::add_js(Template::theme_url(\'js/editors/tiny_mce/tiny_mce_init.js\'));';
			}

			$textarea_included = TRUE;
		}
	}
}

$body = str_replace('{constructor_extras}', $extras, $body);
unset($extras);

//--------------------------------------------------------------------

// Index Method

if ( is_array($action_names) AND in_array('index', $action_names))
{
	// check if this is the front controller
	if ($controller_name == $module_name_lower)
	{
		$body .= $mb_index_front;
	}
	else
	{
		$body .= $mb_index;
	}
}

//--------------------------------------------------------------------
// check if this is the front controller
if ($controller_name != $module_name_lower)
{

	// Create

	if (in_array('create', $action_names))
	{
		$body = str_replace('{create_permission}', preg_replace("/[ -]/", "_", ucfirst($module_name)).'.'.ucfirst($controller_name).'.Create', $body);
	}

	//--------------------------------------------------------------------

	// Edit

	if (in_array('edit', $action_names))
	{
		$body = str_replace('{edit_permission}', preg_replace("/[ -]/", "_", ucfirst($module_name)).'.'.ucfirst($controller_name).'.Edit', $body);
		$body = str_replace('{delete_permission}', preg_replace("/[ -]/", "_", ucfirst($module_name)).'.'.ucfirst($controller_name).'.Delete', $body);
	}

	//--------------------------------------------------------------------

	// Save
	if ($db_required != '')
	{
		$body .= $mb_save;
	}

	$save_data_array = '
		$data = array();';

	for ($counter = 1; $field_total >= $counter; $counter++)
	{
		// only build on fields that have data entered.

		// Due to the required if rule if the first field is set the the others must be
		if (set_value("view_field_label$counter") == NULL)
		{
			continue; 	// move onto next iteration of the loop
		}

		// we set this variable as it will be used to place the comma after the last item to build the insert db array
		$last_field = $counter;

		if($db_required == 'new' && $table_as_field_prefix === TRUE)
		{
				$field_name = $module_name_lower . '_' . set_value("view_field_name$counter");
		}
		elseif($db_required == 'new' && $table_as_field_prefix === FALSE)
		{
				$field_name = set_value("view_field_name$counter");
		}
		else
		{
				$field_name = set_value("view_field_name$counter");
		}

		$form_name = $field_name; //$module_name_lower . '_' . set_value("view_field_name$counter");


		// setup the data array for saving to the db
		// set defaults for certain field types
		switch (set_value("db_field_type$counter"))
		{
			case 'DATE':
				$save_data_array .= "\n\t\t".'$data[\''.$field_name.'\']        = $this->input->post(\''.$form_name.'\') ? $this->input->post(\''.$form_name.'\') : \'0000-00-00\';';
				break;

			case 'DATETIME':
				$save_data_array .= "\n\t\t".'$data[\''.$field_name.'\']        = $this->input->post(\''.$form_name.'\') ? $this->input->post(\''.$form_name.'\') : \'0000-00-00 00:00:00\';';
				break;

			default:
				$save_data_array .= "\n\t\t".'$data[\''.$field_name.'\']        = $this->input->post(\''.$form_name.'\');';
				break;
		}
	}

	$body = str_replace('{save_data_array}', $save_data_array, $body);

	unset($rules);
}

//--------------------------------------------------------------------

// Wrap the class content into the actual class

$controller = str_replace('{class_content}', $body, $mb_class_wrapper);

if ($controller_name == $module_name_lower)
{
	$controller = str_replace('{extend_class}', 'Front_Controller', $controller);
}
else
{
	$controller = str_replace('{extend_class}', 'Admin_Controller', $controller);
}

// Echo out the final controller

echo $controller;

// Clean up memory

unset($body, $mb_class_wrapper, $mb_constructor, $mb_index, $mb_delete, $mb_save, $controller);
