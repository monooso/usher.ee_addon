<?php

/**
 * Usher member group settings tests.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @package         Usher
 */

require_once PATH_THIRD .'usher/classes/usher_member_group_settings' .EXT;

class Test_usher_member_group_settings extends Testee_unit_test_case {

    private $_props;
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

        $this->_props   = array('group_id' => 10, 'target_url' => 'example_url');
        $this->_subject = new Usher_member_group_settings($this->_props);
    }


    public function test__construct__unknown_properties()
    {
        // If this runs without throwing an error, we're good.
        $props = array('utter' => 'nonsense');
        new Usher_member_group_settings($props);
    }


    public function test__reset__success()
    {
        $subject = $this->_subject->reset();
        $this->assertIdentical(0, $subject->get_group_id());
        $this->assertIdentical('', $subject->get_target_url());
    }


    public function test__set_group_id__success()
    {
        $this->assertIdentical(100, $this->_subject->set_group_id('100'));
    }


    public function test__set_group_id__invalid()
    {
        $this->assertIdentical($this->_props['group_id'], $this->_subject->set_group_id(0));
        $this->assertIdentical($this->_props['group_id'], $this->_subject->set_group_id(-100));
        $this->assertIdentical($this->_props['group_id'], $this->_subject->set_group_id(FALSE));
        $this->assertIdentical($this->_props['group_id'], $this->_subject->set_group_id(TRUE));
        $this->assertIdentical($this->_props['group_id'], $this->_subject->set_group_id('invalid'));
        $this->assertIdentical($this->_props['group_id'], $this->_subject->set_group_id(new StdClass()));
    }


    public function test__set_target_url__success()
    {
        $this->assertIdentical('target_url', $this->_subject->set_target_url('target_url'));
    }


    public function test__set_target_url__invalid()
    {
        $this->assertIdentical($this->_props['target_url'], $this->_subject->set_target_url(TRUE));
        $this->assertIdentical($this->_props['target_url'], $this->_subject->set_target_url(FALSE));
        $this->assertIdentical($this->_props['target_url'], $this->_subject->set_target_url(100));
        $this->assertIdentical($this->_props['target_url'], $this->_subject->set_target_url(new StdClass()));
    }


    public function test__to_array__success()
    {
        $this->assertIdentical($this->_props, $this->_subject->to_array());
    }

}

/* End of file      : test.usher_model.php */
/* File location    : third_party/usher/tests/test.usher_model.php */
