<?php

/**
 * Usher extension tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @package         Usher
 */

require_once PATH_THIRD .'usher/tests/mocks/mock.usher_model' .EXT;
require_once PATH_THIRD .'usher/ext.usher' .EXT;

class Test_usher_ext extends Testee_unit_test_case {

    private $_model;
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

        // Create the mock model.
        Mock::generate('Mock_usher_model', get_class($this) .'_mock_usher_model');
        $this->_model           = $this->_get_mock('usher_model');
        $this->_ee->usher_model = $this->_model;

        // Create the test subject.
        $this->_subject = new Usher_ext();
    }


    public function test__construct__get_version_success()
    {
        $this->_model->setReturnValue('get_package_version', '1.0.0');

        $subject = new Usher_ext();
        $this->assertIdentical('1.0.0', $subject->version);
    }


    public function test__activate_extension__success()
    {
        $this->_model->expectOnce('install_extension');
        $this->_subject->activate_extension();
    }


    public function test__disable_extension__success()
    {
        $this->_model->expectOnce('uninstall_extension');
        $this->_subject->disable_extension();
    }


    public function test__on_cp_member_login__success()
    {
        $group_id               = '10';
        $group_settings         = new Usher_member_group_settings(array('group_id' => $group_id, 'target_url' => 'target_url'));
        $member_data            = new StdClass();
        $member_data->group_id  = $group_id;
        $target_url             = 'http://example.com/target_url/';

        $this->_model->expectOnce('get_member_group_settings', array($group_id));
        $this->_model->setReturnValue('get_member_group_settings', $group_settings);

        // Redirect.
        $this->_model->expectOnce('build_cp_url', array($group_settings->get_target_url()));
        $this->_model->setReturnValue('build_cp_url', $target_url);
        $this->_ee->functions->expectOnce('redirect', array($target_url));
    
        $this->_subject->on_cp_member_login($member_data);
    }


    public function test__on_cp_member_login__missing_member_data()
    {
        $member_data = new StdClass();

        $this->_model->expectNever('get_member_group_settings');
        $this->_model->expectNever('build_cp_url');
        $this->_ee->functions->expectNever('redirect');
    
        $this->_subject->on_cp_member_login($member_data);
    }


    public function test__on_cp_member_login__member_group_not_found()
    {
        $group_id               = '10';
        $member_data            = new StdClass();
        $member_data->group_id  = $group_id;

        $this->_model->expectOnce('get_member_group_settings', array($group_id));
        $this->_model->setReturnValue('get_member_group_settings', FALSE);

        $this->_model->expectNever('build_cp_url');
        $this->_ee->functions->expectNever('redirect');
    
        $this->_subject->on_cp_member_login($member_data);
    }


    public function test__save_settings__success()
    {
        // Retrieve the settings from the POST data.
        $settings = array(
            new Usher_member_group_settings(array('group_id' => '10', 'target_url' => 'a')),
            new Usher_member_group_settings(array('group_id' => '20', 'target_url' => 'b')),
            new Usher_member_group_settings(array('group_id' => '30', 'target_url' => 'c'))
        );

        $this->_model->expectOnce('get_package_settings_from_post_data');
        $this->_model->setReturnValue('get_package_settings_from_post_data', $settings);

        // Save the settings to the database.
        $this->_model->expectOnce('save_package_settings', array($settings));

        // Set the flashdata.
        $message = 'Success!';
        $this->_ee->lang->setReturnValue('line', $message);
        $this->_ee->session->expectOnce('set_flashdata', array('message_success', $message));
    
        $this->_subject->save_settings();
    }


    public function test__save_settings__exception()
    {
        $this->_model->expectOnce('get_package_settings_from_post_data');
        $this->_model->setReturnValue('get_package_settings_from_post_data', array());  // Doesn't matter.

        $this->_model->expectOnce('save_package_settings');
        $this->_model->throwOn('save_package_settings', new Exception('Disaster!'));
    
        $message = 'Oh noes!';
        $this->_ee->lang->setReturnValue('line', $message);
        $this->_ee->session->expectOnce('set_flashdata', array('message_failure', $message));
    
        $this->_subject->save_settings();
    }


    public function test__update_extension__success()
    {
        $installed_version  = '1.0.0';
        $return_value       = 'Wibble';     // Should just be passed along.

        $this->_model->expectOnce('update_extension', array($installed_version));
        $this->_model->setReturnValue('update_extension', $return_value);
    
        $this->assertIdentical($return_value, $this->_subject->update_extension($installed_version));
    }

}


/* End of file      : test.usher_ext.php */
/* File location    : third_party/usher/tests/test.usher_ext.php */
