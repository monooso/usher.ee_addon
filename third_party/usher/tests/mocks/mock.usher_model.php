<?php

/**
 * Mock Usher_model class.
 *
 * @author		    Stephen Lewis (http://github.com/experience/)
 * @package		    Usher
 */

class Mock_usher_model {

    public function build_cp_url($url_fragment) {}
    public function get_admin_member_groups() {}
    public function get_member_group_settings($group_id) {}
    public function get_package_name() {}
    public function get_package_settings() {}
    public function get_package_settings_from_post_data() {}
    public function get_package_version() {}
    public function get_site_id() {}
    public function install_extension() {}
    public function save_package_settings(Array $settings = array()) {}
    public function uninstall_extension() {}
    public function update_extension($installed_version = '') {}

}

/* End of file      : mock.usher_model.php */
/* File location    : third_party/usher/tests/mocks/mock.usher_model.php */
