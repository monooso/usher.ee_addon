<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Redirect members to a specific CP URL after login.
 *
 * @author		    Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package		    Usher
 */

class Usher_ext {
	
    private $_ee;

	public $description;
	public $docs_url;
	public $name;
	public $settings;
	public $settings_exist;
	public $version;
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	/**
	 * Class constructor.
	 *
	 * @access	public
	 * @param	mixed 	    $settings		Previously-saved extension settings.
	 * @return	void
	 */
	public function __construct($settings = array())
	{
		$this->_ee =& get_instance();
		$this->_ee->load->add_package_path(PATH_THIRD .'usher/');
		$this->_ee->load->model('usher_model');
        $this->_ee->lang->loadfile($this->_ee->usher_model->get_package_name());

        // Required extension properties.
        $this->description      = $this->_ee->lang->line('extension_description');
        $this->docs_url         = 'http://experienceinternet.co.uk/software/usher/docs/';
        $this->name             = $this->_ee->lang->line('extension_name');
        $this->settings         = array();      // Never used.
        $this->settings_exist   = 'y';
        $this->version          = $this->_ee->usher_model->get_package_version();
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
	 * Handles the cp_member_login hook.
	 *
	 * @see		http://expressionengine.com/user_guide/development/extension_hooks/cp/login/index.html#cp_member_login
	 * @access	public
	 * @param	object 		$member_data	Member data.
	 * @return	void
	 */
	public function on_cp_member_login(StdClass $member_data)
	{
        if ( ! isset($member_data->group_id)
            OR ! $group_settings = $this->_ee->usher_model->get_member_group_settings($member_data->group_id))
        {
            return;
        }

        $target_url = $this->_ee->usher_model->build_cp_url($group_settings->get_target_url());
        $this->_ee->functions->redirect($target_url);
	}
	
	
	/**
	 * Saves the extension settings.
	 *
	 * @access	public
	 * @return	void
	 */
	public function save_settings()
	{
        try
        {
            $this->_ee->usher_model->save_package_settings(
                $this->_ee->usher_model->get_package_settings_from_post_data()
            );

            $this->_ee->session->set_flashdata('message_success', $this->_ee->lang->line('flashdata__settings_saved'));
        }
        catch (Exception $e)
        {
            $this->_ee->session->set_flashdata('message_failure', $this->_ee->lang->line('flashdata__settings_not_saved'));
        }
	}
	
	
	/**
	 * Displays the extension settings form.
	 *
	 * @access	public
	 * @return	string
	 */
	public function settings_form()
	{
        // Load our glamourous assistants.
		$this->_ee->load->helper('form');
		$this->_ee->load->library('table');

        $theme_url = $this->_ee->usher_model->get_package_theme_url();

		$this->_ee->cp->add_to_foot('<script type="text/javascript" src="' .$theme_url.'js/libs/jquery.roland.js"></script>');
		$this->_ee->cp->add_to_foot('<script type="text/javascript" src="' .$theme_url .'js/cp.js"></script>');
		$this->_ee->javascript->compile();

		$this->_ee->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' .$theme_url .'css/cp.css" />');

        // Construct the member groups drop-down options.
        $admin_member_groups = $this->_ee->usher_model->get_admin_member_groups();
        $groups_dd = array();

        foreach ($admin_member_groups AS $member_group)
        {
            $groups_dd[$member_group->get_group_id()] = $member_group->get_group_title();
        }

        // View variables.
		$vars = array(
			'form_action'		=> 'C=addons_extensions' .AMP .'M=save_extension_settings' .AMP .'file=' .$this->_ee->usher_model->get_package_name(),
			'cp_page_title'		=> $this->_ee->lang->line('hd_settings'),
            'groups_dd'         => $groups_dd,
            'settings'          => $this->_ee->usher_model->get_package_settings(),
            'theme_url'         => $theme_url
		);

		return $this->_ee->load->view('settings', $vars, TRUE);
		
		// Collate the view variables.
		$vars = array(
			'hidden_fields'			=> array('file' => strtolower(substr(get_class($this), 0, -4))),
		);
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
