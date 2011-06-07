<?php

/**
 * Usher member group settings datatype.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Usher
 */

require_once PATH_THIRD .'usher/helpers/EI_number_helper' .EXT;

class Usher_member_group_settings {

    private $_group_id;
    private $_target_url;


    /* --------------------------------------------------------------
     * PUBLIC METHODS
     * ------------------------------------------------------------ */
    
    /**
     * Constructor.
     *
     * @access  public
     * @param   array        $props     Instance properties.
     * @return  void
     */
    public function __construct(Array $props = array())
    {
        $this->reset();

        foreach ($props AS $key => $val)
        {
            $method_name = 'set_' .$key;
            if (method_exists($this, $method_name))
            {
                $this->$method_name($val);
            }
        }
    }


    /**
     * Returns the group ID.
     *
     * @access  public
     * @return  int
     */
    public function get_group_id()
    {
        return $this->_group_id;
    }


    /**
     * Returns the target URL.
     *
     * @access  public
     * @return  string
     */
    public function get_target_url()
    {
        return $this->_target_url;
    }
    
    
    /**
     * Resets the instance properties.
     *
     * @access    public
     * @return    Usher_member_group_settings
     */
    public function reset()
    {
        $this->_group_id    = 0;
        $this->_target_url  = '';

        return $this;
    }


    /**
     * Sets the group ID.
     *
     * @access  public
     * @param   int|string        $group_id       The member group ID.
     * @return  int
     */
    public function set_group_id($group_id)
    {
        if (valid_int($group_id, 1))
        {
            $this->_group_id = intval($group_id);
        }

        return $this->get_group_id();
    }


    /**
     * Sets the target URL.
     *
     * @access  public
     * @param   string        $target_url        The target URL.
     * @return  string
     */
    public function set_target_url($target_url)
    {
        if (is_string($target_url))
        {
            $this->_target_url = $target_url;
        }

        return $this->get_target_url();
    }
    
    
    /**
     * Returns the instance as an associative array.
     *
     * @access  public
     * @return  array
     */
    public function to_array()
    {
        return array(
            'group_id'      => $this->get_group_id(),
            'target_url'    => $this->get_target_url()
        );
    }
}

/* End of file      : usher_member_group_settings.php */
/* File location    : third_party/usher/classes/usher_member_group_settings.php */
