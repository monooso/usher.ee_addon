<?php

/**
 * Usher_model tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @package         Usher
 */

require_once PATH_THIRD .'usher/models/usher_model' .EXT;

class Test_usher_model extends Testee_unit_test_case {

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

        $this->_package_name    = 'example_package';
        $this->_package_version = '1.0.0';
        $this->_site_id         = 10;
        $this->_ee->config->setReturnValue('item', $this->_site_id, array('site_id'));
        $this->_subject         = new Usher_model($this->_package_name, $this->_package_version);
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

}

/* End of file      : test.usher_model.php */
/* File location    : third_party/usher/tests/test.usher_model.php */
