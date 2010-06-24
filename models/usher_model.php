<?php if ( ! defined('BASEPATH')) exit('Invalid file request');

/**
 * Redirect members to a specific CP URL after login.
 *
 * @author		Stephen Lewis <addons@experienceinternet.co.uk>
 * @link 		http://github.com/experience/sl.usher.ee2_addon/
 * @package		Usher
 * @version		0.1.0
 */

class Usher_model extends CI_Model {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * ExpressionEngine object.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_ee;
	
	/**
	 * The extension class name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_extension_class = 'Usher_ext';
	
	/**
	 * The extension version.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_version = '0.1.0';
	
	/**
	 * Member groups.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_member_groups = array();
	
	/**
	 * The extension settings.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_settings = array();
	
	/**
	 * The site ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_site_id = '1';
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Class constructor.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		$this->_ee =& get_instance();
		$this->_site_id = $this->_ee->config->item('site_id');
		
		/**
		 * Annoyingly, this method is still called, even if the extension
		 * isn't installed. We need to check if such nonsense is afoot,
		 * and exit promptly if so.
		 */
		
		if ( ! isset($this->_ee->extensions->version_numbers[$this->_extension_class]))
		{
			return;
		}
		
		// Load the settings.
		$this->_load_settings_from_db();
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
	 * Disables the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->_ee->db->delete('extensions', array('class' => $this->_extension_class));
		
		$this->load->dbforge();
		$this->_ee->dbforge->drop_table('usher_settings');
	}
	
	
	/**
	 * Returns the default CP path (the CP homepage).
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_default_cp_path()
	{
		return 'D=cp';
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
	 * Returns the member group settings.
	 *
	 * @access	public
	 * @param	string		$member_group_id		An optional member group ID.
	 * @return	array
	 */
	public function get_member_group_settings($member_group_id = '')
	{
		$return 			= array();
		$member_groups 		= $this->get_member_groups();
		$default_settings	= $this->_get_default_member_group_settings();
		
		/**
		 * If a non-existent member group has been specified,
		 * just return the default settings.
		 */
		
		if ($member_group_id && ! in_array($member_group_id, array_keys($member_groups)))
		{
			return array($member_group_id => array_merge($default_settings, array('member_group_id' => $member_group_id)));
		}
		
		/**
		 * Loop through the member groups.
		 */
		
		foreach ($member_groups AS $group_id => $group_title)
		{
			if ( ! $member_group_id OR ($member_group_id == $group_id))
			{
				$return[$group_id] = isset($this->_settings[$group_id])
					? array_merge($default_settings, $this->_settings[$group_id])
					: $default_settings;
			}
		}
		
		return $return;
	}
	
	
	/**
	 * Returns the site settings.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_settings()
	{
		return $this->_settings;
	}
	
	
	/**
	 * Returns the extension version.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_version()
	{
		return $this->_version;
	}
	
	
	/**
	 * Saves the site settings.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function save_settings()
	{
		$this->_update_settings_from_input();
		
		// Delete the existing site settings.
		$this->_ee->db->delete('usher_settings', array('site_id' => $this->_site_id));
		
		foreach ($this->_settings AS $group_id => $group_settings)
		{
			$temp_settings = array_merge(array('site_id' => $this->_site_id), $group_settings);
			$this->_ee->db->insert('usher_settings', $temp_settings);
		}
		
		return TRUE;
	}
	
	
	/**
	 * Updates the extension.
	 *
	 * @access	public
	 * @param 	string		$current_version		The current version.
	 * @return	bool
	 */
	public function update_extension($current_version = '')
	{
		if ( ! $current_version OR $current_version == $this->_version)
		{
			return FALSE;
		}
		
		// Update the version number.
		if ($current_version < $this->_version)
		{
			$this->_ee->db->update(
				'extensions',
				array('version' => $this->_version),
				array('class' => $this->_extension_class)
			);
		}
		
		return TRUE;
	}
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Returns the default member group settings.
	 *
	 * @access	private
	 * @return	array
	 */
	private function _get_default_member_group_settings()
	{
		return array(
			'site_id'			=> $this->_site_id,
			'member_group_id'	=> '',
			'redirect_on_login'	=> 'n',
			'redirect_url'		=> ''
		);
	}
	
	
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
	 * Loads the settings from the database.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _load_settings_from_db()
	{
		$settings = array();
		
		// Load the settings from the database.
		$db_settings = $this->_ee->db->get_where('usher_settings', array('site_id' => $this->_site_id));
		
		// If we have saved settings, parse them.
		if ($db_settings->num_rows() > 0)
		{
			$this->_ee->load->helper('string');
			
			$default_settings = $this->_get_default_member_group_settings();

			foreach ($db_settings->result_array() AS $db_row)
			{
				$settings[$db_row['member_group_id']] = array_merge($default_settings, $db_row);
			}
		}
		
		$this->_settings = $settings;
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