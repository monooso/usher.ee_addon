<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Redirect members to a specific CP URL after login.
 *
 * @author		Stephen Lewis (http://github.com/experience/)
 * @package		Usher
 */

class Usher_ext {
	
    private $_ee;

	public $description = 'Redirect members to a specific CP URL after login.';
	public $docs_url = 'http://github.com/experience/sl.usher.ee2_addon/';
	public $name = 'Usher';
	public $settings = array();
	public $settings_exist = 'y';
	public $version = '';
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	/**
	 * Class constructor.
	 *
	 * @access	public
	 * @param	array 		$settings		Previously-saved extension settings.
	 * @return	void
	 */
	public function __construct(Array $settings = array())
	{
		$this->_ee =& get_instance();
		
		$this->_ee->load->add_package_path(PATH_THIRD .'usher/');
		$this->_ee->load->model('usher_model');
		
		$this->version = $this->_ee->usher_model->get_package_version();
	}
	
	
	/**
	 * Activates the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$this->_ee->usher_model->install_extension();
	}
	
	
	/**
	 * Disables the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->_ee->usher_model->uninstall_extension();
	}
	
	
	/**
	 * Handlers to cp_member_login hook.
	 *
	 * @see		http://expressionengine.com/public_beta/docs/development/extension_hooks/cp/login/index.html#cp_member_login
	 * @access	public
	 * @param	object 		$member_data	Member data.
	 * @return	void
	 */
	public function on_cp_member_login(StdClass $member_data)
	{
        /*
		if ( ! $member_data->group_id)
		{
			return;
		}
		
		$group_settings = $this->_ee->usher_model->get_member_group_settings($member_data->group_id);
		
		if ($group_settings[$member_data->group_id]['redirect_on_login'] == 'y')
		{
			$this->_ee->functions->redirect(BASE .AMP
				.$this->_ee->usher_model->get_default_cp_path()
				.$group_settings[$member_data->group_id]['redirect_url']
			);
		}
         */
	}
	
	
	/**
	 * Saves the extension settings.
	 *
	 * @access	public
	 * @return	void
	 */
	public function save_settings()
	{
        /*
		// Need to explicitly load the language file.
		$this->_ee->lang->loadfile('usher');
		
		// Save the settings.
		if ($this->_ee->usher_model->save_settings())
		{
			$this->_ee->session->set_flashdata('message_success', $this->_ee->lang->line('settings_saved'));
		}
		else
		{
			$this->_ee->session->set_flashdata('message_failure', $this->_ee->lang->line('settings_not_saved'));
		}
         */
	}
	
	
	/**
	 * Displays the extension settings form.
	 *
	 * @access	public
	 * @return	string
	 */
	public function settings_form()
	{
        /*
		// Load our glamorous assistants.
		$this->_ee->load->helper('form');
		$this->_ee->load->library('table');
		
		$default_cp_path = $this->_ee->usher_model->get_default_cp_path();
		
		// Collate the view variables.
		$vars = array(
			'action_url' 			=> 'C=addons_extensions' .AMP .'M=save_extension_settings',
			'cp_page_title'			=> $this->_ee->lang->line('extension_name'),
			'default_cp_path'		=> $default_cp_path,
			'hidden_fields'			=> array('file' => strtolower(substr(get_class($this), 0, -4))),
			'member_groups'			=> $this->_ee->usher_model->get_member_groups(),
			'member_group_settings'	=> $this->_ee->usher_model->get_member_group_settings(),
			'redirect_options'		=> array('n' => $this->_ee->lang->line('no'), 'y' => $this->_ee->lang->line('yes'))
		);
		
		// Load the view.
		return $this->_ee->load->view('settings', $vars, TRUE);
         */
	}
	
	
	/**
	 * Updates the extension.
	 *
	 * @access	public
	 * @param	string		$installed_version  	The currently installed version.
	 * @return	bool
	 */
	public function update_extension($installed_version = '')
	{
		return $this->_ee->usher_model->update_extension($installed_version);
	}
	
}

/* End of file		: ext.usher.php */
/* File location	: third_party/usher/ext.usher.php */
