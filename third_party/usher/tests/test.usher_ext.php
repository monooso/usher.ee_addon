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
