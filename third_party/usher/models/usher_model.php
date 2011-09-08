<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Redirect members to a specific CP URL after login.
 *
 * @author		    Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package		    Usher
 * @version		    1.0.1
 */

require_once PATH_THIRD .'usher/classes/EI_member_group' .EXT;
require_once PATH_THIRD .'usher/classes/usher_member_group_settings' .EXT;

class Usher_model extends CI_Model {
	
    private $_admin_member_groups;
	private $_ee;
    private $_extension_class;
    private $_package_name;
    private $_package_settings;
    private $_package_version;
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
        $this->_package_version = $package_version ? $package_version : '1.0.1';
        $this->_extension_class = ucfirst($this->_package_name) .'_ext';
	}


    /**
     * Builds a full Control Panel URL, given the target URL fragment.
     *
     * @access  public
     * @param   string        $url_fragment        The target URL fragment.
     * @return  string
     */
    public function build_cp_url($url_fragment)
    {
        return ( ! is_string($url_fragment) OR $url_fragment == '')
            ? BASE
            : BASE .AMP .$url_fragment;
    }
	
	
	/**
	 * Returns the admin member groups.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_admin_member_groups()
	{
        if ( ! is_array($this->_admin_member_groups))
        {
            $groups = array();

            $db_result = $this->_ee->db->select('group_id, group_title')
                ->get_where('member_groups', array('can_access_cp' => 'y'));

            if ( ! $db_result->num_rows())
            {
                return $groups;
            }

            foreach ($db_result->result_array() AS $db_row)
            {
                $groups[] = new EI_member_group($db_row);
            }

            $this->_admin_member_groups = $groups;
        }

        return $this->_admin_member_groups;
	}


    /**
     * Returns the setting for the specified member group.
     *
     * @access  public
     * @param   int|string        $group_id        The member group ID.
     * @return  Usher_member_group_settings|FALSE
     */
    public function get_member_group_settings($group_id)
    {
        if ( ! valid_int($group_id, 1))
        {
            return FALSE;
        }

        $settings = $this->get_package_settings();

        foreach ($settings AS $group_settings)
        {
            if ($group_settings->get_group_id() == $group_id)
            {
                return $group_settings;
            }
        }

        return FALSE;
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

            if ($db_result->num_rows())
            {
                foreach ($db_result->result_array() AS $db_row)
                {
                    $settings[] = new Usher_member_group_settings($db_row);
                }
            }

            $this->_package_settings = $settings;
        }

        return $this->_package_settings;
    }


    /**
     * Retrieves the package settings from the POST data.
     *
     * @access  public
     * @return  array
     */
    public function get_package_settings_from_post_data()
    {
        $settings = array();

        if ( ! $input_settings = $this->_ee->input->post('usher_settings', TRUE))
        {
            return $settings;
        }

        foreach ($input_settings AS $group_settings)
        {
            $temp_settings = new Usher_member_group_settings($group_settings);

            if ( ! $temp_settings->get_group_id() OR ! $temp_settings->get_target_url())
            {
                continue;
            }

            $settings[] = new Usher_member_group_settings($group_settings);
        }

        return $settings;
    }


    /**
     * Returns the package theme URL.
     *
     * @access  public
     * @return  string
     */
    public function get_package_theme_url()
    {
		$theme_url = $this->_ee->config->item('theme_folder_url');
		$theme_url .= substr($theme_url, -1) == '/'
			? 'third_party/'
			: '/third_party/';

		return $theme_url .$this->get_package_name() .'/';
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
     * Uninstalls the extension. So named to preserve consistency
     * with the standard module uninstallation method.
     *
     * @access  public
     * @return  void
     */
    public function uninstall_extension()
    {
        $this->_ee->load->dbforge();

        $this->_ee->db->delete('extensions', array('class' => $this->_extension_class));
        $this->_ee->dbforge->drop_table('usher_settings');
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

}

/* End of file		: usher_model.php */
/* File location	: /system/expressionengine/third_party/usher/models/usher_model.php */
