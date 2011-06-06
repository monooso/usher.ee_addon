<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Redirect members to a specific CP URL after login.
 *
 * @author		    Stephen Lewis (http://github.com/experience/)
 * @package		    Usher
 * @version		    0.1.0
 */

require_once PATH_THIRD .'usher/classes/usher_member_group_settings' .EXT;

class Usher_model extends CI_Model {
	
	private $_ee;
    private $_extension_class;
	private $_member_groups;
    private $_package_name;
    private $_package_settings;
    private $_package_version;
    private $_settings;
    private $_site_id;
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Class constructor.
	 *
	 * @access	public
     * @param   string      $package_name       Package name. Used for testing.
     * @param   string      $package_version    Package versio. Used for testing.
	 * @return	void
	 */
	public function __construct($package_name = '', $package_version = '')
	{
		$this->_ee              =& get_instance();
        $this->_package_name    = $package_name ? strtolower($package_name) : 'usher';
        $this->_package_version = $package_version ? $package_version : '0.1.0';
        $this->_extension_class = ucfirst($this->_package_name) .'_ext';
	}
	
	
	/**
	 * Activates the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$hooks = array(
			array(
				'hook'		=> 'cp_member_login',
				'method'	=> 'cp_member_login',
				'priority'	=> 10
			)
		);
		
		foreach ($hooks AS $hook)
		{
			$this->_ee->db->insert(
				'extensions',
				array(
					'class'		=> $this->_extension_class,
					'enabled'	=> 'y',
					'hook'		=> $hook['hook'],
					'method'	=> $hook['method'],
					'priority'	=> $hook['priority'],
					'version'	=> $this->_version
				)
			);
		}
		
		// Create the settings table.
		$fields = array(
			'site_id' => array(
				'constraint'	=> 8,
				'null'			=> FALSE,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'member_group_id' => array(
				'constraint'	=> 4,
				'null'			=> FALSE,
				'type'			=> 'smallint',
				'unsigned'		=> TRUE
			),
			'redirect_on_login' => array(
				'constraint'	=> 1,
				'default'		=> 'n',
				'null'			=> FALSE,
				'type'			=> 'char'
			),
			'redirect_url' => array(
				'constraint'	=> '128',
				'null'			=> TRUE,
				'type'			=> 'varchar'
			)
		);
		
		$this->load->dbforge();
		$this->_ee->dbforge->add_field($fields);
		
		// PRIMARY KEY `site_id_member_group_id` (`site_id`, `member_group_id`)
		$this->_ee->dbforge->add_key('site_id', TRUE);
		$this->_ee->dbforge->add_key('member_group_id', TRUE);
		
		$this->_ee->dbforge->create_table('usher_settings', TRUE);
	}


    /**
     * Deinstalls the extension. So named to preserve consistency
     * with the standard module deinstallation method.
     *
     * @access  public
     * @return  void
     */
    public function deinstall_extension()
    {
        $this->_ee->load->dbforge();

        $this->_ee->db->delete('extensions', array('class' => $this->_extension_class));
        $this->_ee->dbforge->drop_table('usher_settings');
    }
	
	
	/**
	 * Returns the member groups.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_member_groups()
	{
		if ( ! $this->_member_groups)
		{
			$this->_load_member_groups_from_db();
		}
		
		return $this->_member_groups;
	}
	
	
    /**
     * Returns the package name.
     *
     * @access  public
     * @return  string
     */
    public function get_package_name()
    {
        return $this->_package_name;
    }


    /**
     * Returns the package settings.
     *
     * @access  public
     * @return  array
     */
    public function get_package_settings()
    {
        if ( ! $this->_package_settings)
        {
            $settings = array();

            $db_result = $this->_ee->db->select('group_id, target_url')
                ->get_where('usher_settings', array('site_id' => $this->get_site_id()));

            if ( ! $db_result->num_rows())
            {
                return $settings;
            }

            foreach ($db_result->result_array() AS $db_row)
            {
                $settings[] = new Usher_member_group_settings($db_row);
            }

            $this->_package_settings = $settings;
        }

        return $this->_package_settings;
    }


    /**
     * Returns the package version.
     *
     * @access  public
     * @return  string
     */
    public function get_package_version()
    {
        return $this->_package_version;
    }


    /**
     * Returns the site ID.
     *
     * @access    public
     * @return    int
     */
    public function get_site_id()
    {
        if ( ! $this->_site_id)
        {
            $this->_site_id = intval($this->_ee->config->item('site_id'));
        }

        return $this->_site_id;
    }


    /**
     * Installs the extension. So named to preserve consistency with the
     * standard module installation method.
     *
     * @access  public
     * @return  void
     */
    public function install_extension()
    {
        // Register the extension hook.
        $hook_data = array(
            'class'     => $this->_extension_class,
            'enabled'   => 'y',
            'hook'      => 'cp_member_login',
            'method'    => 'on_cp_member_login',
            'priority'  => 5,
            'settings'  => '',
            'version'   => $this->get_package_version()
        );

        $this->_ee->db->insert('extensions', $hook_data);

        // Create the settings table.
        $this->_ee->load->dbforge();

        $this->_ee->dbforge->add_field(array(
            'site_id' => array(
                'constraint'    => 5,
                'null'          => FALSE,
                'type'          => 'int',
                'unsigned'      => TRUE
            ),
            'group_id' => array(
                'constraint'    => 4,
                'null'          => FALSE,
                'type'          => 'smallint',
                'unsigned'      => TRUE
            ),
            'target_url' => array(
                'constraint'    => 150,
                'null'          => FALSE,
                'type'          => 'varchar'
            )
        ));

        $this->_ee->dbforge->add_key(array('site_id', 'group_id'));
        $this->_ee->dbforge->create_table('usher_settings', TRUE);
    }


    /**
     * Saves the specified settings to the database.
     *
     * @access  public
     * @param   array       $settings       An array of Usher_member_group_settings objects.
     * @return  void
     */
    public function save_package_settings(Array $settings = array())
    {
        // Validate the settings.
        foreach ($settings AS $group_settings)
        {
            if ( ! $group_settings instanceof Usher_member_group_settings)
            {
                throw new Exception($this->_ee->lang->line('exception__save_package_settings__invalid_datatype'));
            }
        }

        // Delete the existing site settings.
        $this->_ee->db->delete('usher_settings', array('site_id' => $this->get_site_id()));

        // Get out early.
        if ( ! $settings)
        {
            return;
        }

        // Save the new site settings.
        $base_insert_data = array('site_id' => $this->get_site_id());

        foreach ($settings AS $group_settings)
        {
            if ( ! $group_settings->get_group_id() OR ! $group_settings->get_target_url())
            {
                continue;
            }

            $this->_ee->db->insert('usher_settings', array_merge(
                $base_insert_data,
                $group_settings->to_array()
            ));
        }
    }


    /**
     * Updates the extension.
     *
     * @access  public
     * @param   string        $installed_version        The currently-installed version.
     * @return  mixed
     */
    public function update_extension($installed_version = '')
    {
        if ( ! $installed_version
            OR version_compare($installed_version, $this->get_package_version(), '>='))
        {
            return FALSE;
        }

        $this->_ee->db->update(
            'extensions',
            array('version' => $this->get_package_version()),
            array('class'   => $this->_extension_class)
        );
    }
	
	

	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Loads the member groups from the database.
	 *
	 * @access	private
	 * @return	array
	 */
	private function _load_member_groups_from_db()
	{
		$member_groups = array();
		
		$db_groups = $this->_ee->db
			->select('group_id, group_title')
			->get_where('member_groups', array('can_access_cp' => 'y', 'site_id' => $this->_site_id));
		
		if ($db_groups->num_rows() > 0)
		{
			foreach ($db_groups->result() AS $db_group)
			{
				$member_groups[$db_group->group_id] = $db_group->group_title;
			}
		}
		
		$this->_member_groups = $member_groups;
	}
	
	
	/**
	 * Updates the settings from the input.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _update_settings_from_input()
	{
		$settings = array();
		
		if (is_array($this->_ee->input->post('member_groups')))
		{
			$default_settings = $this->_get_default_member_group_settings();
			
			foreach ($this->_ee->input->post('member_groups') AS $input_group_id => $input_group_settings)
			{
				$member_group_settings = array(
					'member_group_id' 	=> $input_group_id,
					'redirect_on_login'	=> $input_group_settings['redirect_on_login'],
					'redirect_url'		=> $input_group_settings['redirect_url']
				);
				
				$settings[$input_group_id] = array_merge($default_settings, $member_group_settings);
			}
		}
		
		$this->_settings = $settings;
	}
	
}

/* End of file		: usher_model.php */
/* File location	: /system/expressionengine/third_party/usher/models/usher_model.php */
