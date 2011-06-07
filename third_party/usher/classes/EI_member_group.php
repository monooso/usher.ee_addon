<?php

/**
 * Member Group datatype.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @package         EI
 */

require_once PATH_THIRD .'usher/helpers/EI_number_helper' .EXT;

if ( ! class_exists('EI_member_group'))
{

class EI_member_group {

    private $_group_id;
    private $_group_title;


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
     * Returns the group title.
     *
     * @access  public
     * @return  string
     */
    public function get_group_title()
    {
        return $this->_group_title;
    }
    
    
    /**
     * Resets the instance properties.
     *
     * @access    public
     * @return    EI_member_group
     */
    public function reset()
    {
        $this->_group_id    = 0;
        $this->_group_title = '';

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
     * Sets the group title.
     *
     * @access  public
     * @param   string        $group_title      The group title.
     * @return  string
     */
    public function set_group_title($group_title)
    {
        if (is_string($group_title))
        {
            $this->_group_title = $group_title;
        }

        return $this->get_group_title();
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
            'group_title'   => $this->get_group_title()
        );
    }
}

}

/* End of file      : EI_member_group.php */
