<?php

/**
 * Usher_model tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Usher
 */

require_once PATH_THIRD .'usher/models/usher_model' .EXT;

class Test_usher_model extends Testee_unit_test_case {

    private $_extension_class;
    private $_package_name;
    private $_package_version;
    private $_site_id;
    private $_subject;


    /* --------------------------------------------------------------
     * PUBLIC METHODS
     * ------------------------------------------------------------ */
    
    /**
     * Runs before each test.
     *
     * @access  public
     * @return  void
     */
    public function setUp()
    {
        parent::setUp();

        $this->_extension_class = 'Example_package_ext';
        $this->_package_name    = 'example_package';
        $this->_package_version = '1.0.0';
        $this->_site_id         = 10;
        $this->_ee->config->setReturnValue('item', $this->_site_id, array('site_id'));
        $this->_subject         = new Usher_model($this->_package_name, $this->_package_version);
    }


    public function test__build_cp_url__success()
    {
        $url = 'C=example';
        $expected_result = BASE .AMP .$url;
        $this->assertIdentical($expected_result, $this->_subject->build_cp_url($url));
    }


    public function test__build_cp_url__invalid_url_fragment()
    {
        $this->assertIdentical(BASE, $this->_subject->build_cp_url(''));
        $this->assertIdentical(BASE, $this->_subject->build_cp_url(100));
        $this->assertIdentical(BASE, $this->_subject->build_cp_url(new StdClass()));
        $this->assertIdentical(BASE, $this->_subject->build_cp_url(FALSE));
    }


    public function test__get_admin_member_groups__success()
    {
        $db_result  = $this->_get_mock('db_query');
        $db_rows    = array(
            array('group_id' => '10', 'group_title' => 'SuperAdmins'),
            array('group_id' => '20', 'group_title' => 'Editors'),
            array('group_id' => '30', 'group_title' => 'Authors')
        );

        // The database should only be queried once, regardless of
        // how many times we call this method.
        $this->_ee->db->expectOnce('select', array('group_id, group_title'));
        $this->_ee->db->expectOnce('get_where', array('member_groups', array('can_access_cp' => 'y')));
        $this->_ee->db->setReturnReference('get_where', $db_result);

        $db_result->expectOnce('num_rows');
        $db_result->expectOnce('result_array');
        $db_result->setReturnValue('num_rows', count($db_rows));
        $db_result->setReturnValue('result_array', $db_rows);

        $expected_result = array();
        foreach ($db_rows AS $db_row)
        {
            $expected_result[] = new EI_member_group($db_row);
        }

        $actual_result = $this->_subject->get_admin_member_groups();

        $this->assertIdentical(count($expected_result), count($actual_result));
        for ($count = 0; $count < count($expected_result); $count++)
        {
            $this->assertIdentical($expected_result[$count], $actual_result[$count]);
        }

        // Test that the database isn't queried again.
        $this->_subject->get_admin_member_groups();
    }


    public function test__get_admin_member_groups__no_member_groups()
    {
        $db_result = $this->_get_mock('db_query');

        $this->_ee->db->setReturnReference('get_where', $db_result);

        $db_result->expectOnce('num_rows');
        $db_result->expectNever('result_array');

        $db_result->setReturnValue('num_rows', 0);
    
        $this->assertIdentical(array(), $this->_subject->get_admin_member_groups());
    }


    public function test__get_member_group_settings__success()
    {
        $group_id = '10';

        // Not great, as we have to mock the DB result for get_package_settings too.
        $db_result = $this->_get_mock('db_query');
        $db_rows = array(array('group_id' => $group_id, 'target_url' => 'here'));

        $this->_ee->db->setReturnReference('get_where', $db_result);
        $db_result->setReturnValue('num_rows', count($db_rows));
        $db_result->setReturnValue('result_array', $db_rows);

        // Now to the task in hand.
        $expected_result = new Usher_member_group_settings($db_rows[0]);
        $this->assertIdentical($expected_result, $this->_subject->get_member_group_settings($group_id));
    }


    public function test__get_member_group_settings__member_group_not_found()
    {
        $group_id = '10';

        // get_package_settings.
        $db_result = $this->_get_mock('db_query');
        $db_rows = array(array('group_id' => '20', 'target_url' => 'here'));

        $this->_ee->db->setReturnReference('get_where', $db_result);
        $db_result->setReturnValue('num_rows', count($db_rows));
        $db_result->setReturnValue('result_array', $db_rows);

        // Now to the task in hand.
        $this->assertIdentical(FALSE, $this->_subject->get_member_group_settings($group_id));
    }


    public function test__get_member_group_settings__invalid_group_id()
    {
        $this->_ee->db->expectNever('get_where');

        $this->assertIdentical(FALSE, $this->_subject->get_member_group_settings(0));
        $this->assertIdentical(FALSE, $this->_subject->get_member_group_settings('Invalid'));
        $this->assertIdentical(FALSE, $this->_subject->get_member_group_settings(new StdClass()));
        $this->assertIdentical(FALSE, $this->_subject->get_member_group_settings(TRUE));
    }


    public function test__get_package_name__success()
    {
        $this->assertIdentical($this->_package_name, $this->_subject->get_package_name());
    }


    public function test__get_package_settings__success()
    {
        $db_result = $this->_get_mock('db_query');
        $db_rows = array(
            array('group_id' => '10', 'target_url' => 'here'),
            array('group_id' => '20', 'target_url' => 'there'),
            array('group_id' => '30', 'target_url' => 'everywhere')
        );

        // The database should only be accessed once, even
        // though we're calling the method multiple times.
        $this->_ee->db->expectOnce('select', array('group_id, target_url'));
        $this->_ee->db->expectOnce('get_where', array('usher_settings', array('site_id' => $this->_site_id)));
        $this->_ee->db->setReturnReference('get_where', $db_result);

        $db_result->expectOnce('num_rows');
        $db_result->expectOnce('result_array');

        $db_result->setReturnValue('num_rows', count($db_rows));
        $db_result->setReturnValue('result_array', $db_rows);

        $expected_result = array();
        foreach ($db_rows AS $db_row)
        {
            $expected_result[] = new Usher_member_group_settings($db_row);
        }

        $actual_result = $this->_subject->get_package_settings();

        $this->assertIdentical(count($expected_result), count($actual_result));
        for ($count = 0; $count < count($expected_result); $count++)
        {
            $this->assertIdentical($expected_result[$count], $actual_result[$count]);
        }

        // Call the method again, to ensure the data is being cached.
        $actual_result = $this->_subject->get_package_settings();

        $this->assertIdentical(count($expected_result), count($actual_result));
        for ($count = 0; $count < count($expected_result); $count++)
        {
            $this->assertIdentical($expected_result[$count], $actual_result[$count]);
        }
    }


    public function test__get_package_settings__no_settings()
    {
        $db_result = $this->_get_mock('db_query');
        $this->_ee->db->setReturnReference('get_where', $db_result);

        $db_result->expectOnce('num_rows');
        $db_result->setReturnValue('num_rows', 0);
        $db_result->expectNever('result_array');

        $this->assertIdentical(array(), $this->_subject->get_package_settings());
    }


    public function test__get_package_settings_from_post_data__success()
    {
        $input = $this->_ee->input;

        $post_settings = array(
            array('group_id' => '10', 'target_url' => 'a'),
            array('group_id' => '20', 'target_url' => 'b'),
            array('group_id' => '30', 'target_url' => 'c')
        );

        $input->expectOnce('post', array('usher_settings', TRUE));
        $input->setReturnValue('post', $post_settings);

        $expected_result = array();
        foreach ($post_settings AS $group_setting)
        {
            $expected_result[] = new Usher_member_group_settings($group_setting);
        }
    
        $actual_result = $this->_subject->get_package_settings_from_post_data();

        $this->assertIdentical(count($expected_result), count($actual_result));
        for ($count = 0; $count < count($expected_result); $count++)
        {
            $this->assertIdentical($expected_result[$count], $actual_result[$count]);
        }
    }


    public function test__get_package_settings_from_post_data__no_post_data()
    {
        $this->_ee->input->setReturnValue('post', FALSE);
        $this->assertIdentical(array(), $this->_subject->get_package_settings_from_post_data());
    }


    public function test__get_package_settings_from_post_data__invalid_group_id()
    {
        $this->_ee->input->setReturnValue(
            'post',
            array(array('group_id' => '0', 'target_url' => 'a'))
        );
    
        $this->assertIdentical(array(), $this->_subject->get_package_settings_from_post_data());
    }


    public function test__get_package_settings_from_post_data__invalid_target_url()
    {
        $this->_ee->input->setReturnValue(
            'post',
            array(array('group_id' => '10', 'target_url' => ''))
        );
    
        $this->assertIdentical(array(), $this->_subject->get_package_settings_from_post_data());
    }


	public function test__get_package_theme_url__end_slash_exists()
	{
		$config_theme_url	= 'http://example.com/themes/';
		$return_theme_url	= 'http://example.com/themes/third_party/' .$this->_package_name .'/';
		
		$this->_ee->config->expectOnce('item', array('theme_folder_url'));
		$this->_ee->config->setReturnValue('item', $config_theme_url, array('theme_folder_url'));

		$this->assertIdentical($return_theme_url, $this->_subject->get_package_theme_url());
	}
		

	public function test__get_package_theme_url__no_end_slash_exists()
	{
		$config_theme_url	= 'http://example.com/themes';
		$return_theme_url	= 'http://example.com/themes/third_party/' .$this->_package_name .'/';
		
		$this->_ee->config->expectOnce('item', array('theme_folder_url'));
		$this->_ee->config->setReturnValue('item', $config_theme_url, array('theme_folder_url'));

		// Run the tests.
		$this->assertIdentical($return_theme_url, $this->_subject->get_package_theme_url());
	}
		

    public function test__get_package_version__success()
    {
        $this->assertIdentical($this->_package_version, $this->_subject->get_package_version());
    }


    public function test__get_site_id__success()
    {
        // Should only access the config object once.
        $this->_ee->config->expectOnce('item', array('site_id'));
    
        $this->assertIdentical(intval($this->_site_id), $this->_subject->get_site_id());
        $this->assertIdentical(intval($this->_site_id), $this->_subject->get_site_id());
    }


    public function test__install_extension__success()
    {
        $db         = $this->_ee->db;
        $dbforge    = $this->_ee->dbforge;

        // Register the extension.
        $hook_data = array(
            'class'     => $this->_extension_class,
            'enabled'   => 'y',
            'hook'      => 'cp_member_login',
            'method'    => 'on_cp_member_login',
            'priority'  => 5,
            'settings'  => '',
            'version'   => $this->_package_version
        );

        $db->expectOnce('insert', array('extensions', $hook_data));

        // Create the settings table.
        $table_fields = array(
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
        );

        $dbforge->expectOnce('add_field', array($table_fields));
        $dbforge->expectOnce('add_key', array(array('site_id', 'group_id')));
        $dbforge->expectOnce('create_table', array('usher_settings', TRUE));
    
        $this->_subject->install_extension();
    }


    public function test__save_package_settings__success()
    {
        $db = $this->_ee->db;

        $settings = array(
            new Usher_member_group_settings(array('group_id' => 10, 'target_url' => 'a')),
            new Usher_member_group_settings(array('group_id' => 20, 'target_url' => 'b')),
            new Usher_member_group_settings(array('group_id' => 30, 'target_url' => 'c'))
        );

        $base_insert_data = array('site_id' => $this->_site_id);

        $db->expectOnce('delete', array('usher_settings', array('site_id' => $this->_site_id)));
        $db->expectCallCount('insert', count($settings));

        for ($count = 0; $count < count($settings); $count++)
        {
            $insert_data = array_merge($base_insert_data, $settings[$count]->to_array());
            $db->expectAt($count, 'insert', array('usher_settings', $insert_data));
        }
    
        $this->_subject->save_package_settings($settings);
    }


    public function test__save_package_settings__no_settings()
    {
        $this->_ee->db->expectOnce('delete');
        $this->_ee->db->expectNever('insert');
        $this->_subject->save_package_settings(array());
    }


    public function test__save_package_settings__invalid_object()
    {
        $settings = array(
            new Usher_member_group_settings(array('group_id' => 10, 'target_url' => 'a')),
            new StdClass()
        );

        $this->_ee->db->expectNever('delete');
        $this->_ee->db->expectNever('insert');

        $message = 'Oh noes!';
        $this->_ee->lang->setReturnValue('line', $message);
        $this->expectException(new Exception($message));

        $this->_subject->save_package_settings($settings);
    }


    public function test__save_package_settings__missing_group_id()
    {
        $settings = array(new Usher_member_group_settings(array('target_url' => 'a')));

        $this->_ee->db->expectOnce('delete');
        $this->_ee->db->expectNever('insert');

        $this->_subject->save_package_settings($settings);
    }

    
    public function test__save_package_settings__missing_target_url()
    {
        $settings = array(new Usher_member_group_settings(array('group_id' => 10)));

        $this->_ee->db->expectOnce('delete');
        $this->_ee->db->expectNever('insert');

        $this->_subject->save_package_settings($settings);
    }


    public function test__update_extension__latest_version_installed()
    {
        $installed_version  = '1.0.0';
        $package_version    = '1.0.0';
        $subject            = new Usher_model($this->_package_name, $package_version);

        $this->assertIdentical(FALSE, $subject->update_extension($installed_version));
    }


    public function test__update_extension__newer_version_installed()
    {
        $installed_version  = '1.1.0';
        $package_version    = '1.0.0';
        $subject            = new Usher_model($this->_package_name, $package_version);

        $this->assertIdentical(FALSE, $subject->update_extension($installed_version));
    }


    public function test__update_extension__not_installed()
    {
        $installed_version  = '';
        $package_version    = '1.0.0';
        $subject            = new Usher_model($this->_package_name, $package_version);

        $this->assertIdentical(FALSE, $subject->update_extension($installed_version));
    }


    public function test__update_extension__update_required()
    {
        $installed_version  = '1.0.0';
        $package_version    = '1.1.0';
        $subject            = new Usher_model($this->_package_name, $package_version);
    
        // Update the extension version.
        $update_data    = array('version' => $package_version);
        $update_clause  = array('class' => $this->_extension_class);

        $this->_ee->db->expectOnce('update', array('extensions', $update_data, $update_clause));
        $subject->update_extension($installed_version);
    }


    public function test__uninstall_extension__success()
    {
        $class      = $this->_extension_class;
        $db         = $this->_ee->db;
        $dbforge    = $this->_ee->dbforge;

        $db->expectOnce('delete', array('extensions', array('class' => $class)));
        $dbforge->expectOnce('drop_table', array('usher_settings'));

        $this->_subject->uninstall_extension($class);
    }


}


/* End of file      : test.usher_model.php */
/* File ulocation    : third_party/usher/tests/test.usher_model.php */
