<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authenticated Controller
 *
 * Provides a base class for all controllers that must check user login
 * status.
 *
 * @package    Bonfire\Core\Controllers
 * @category   Controllers
 * @author     Bonfire Dev Team
 * @link       http://guides.cibonfire.com/helpers/file_helpers.html
 *
 */
class Authenticated_Controller extends Base_Controller
{

	//--------------------------------------------------------------------

	/**
	 * Class constructor setup login restriction and load various libraries
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		// Load the Auth library before the parent constructor to ensure
		// the current user's settings are honored by the parent
		$this->load->library('users/auth');

		// Make sure we're logged in.
		if ( isset( $this->uri->segments[4]) && strpos($this->uri->segments[4], "api_") == 0 ){
			if ( !$this->auth->is_logged_in() ){
				header('Content-type: application/json');
				$response->success = false;
				$response->error = "please login";
				die(json_encode($response));
			}
		}

		$this->auth->restrict();

		$this->set_current_user();

		// Load additional libraries
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('', '');
		// $this->form_validation->CI =& $this;	// Hack to make it work properly with HMVC
	}//end construct()

	//--------------------------------------------------------------------

}

/* End of file Authenticated_Controller.php */
/* Location: ./application/core/Authenticated_Controller.php */