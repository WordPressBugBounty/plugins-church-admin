<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_attendance_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminAttendance';
	}

	public function get_title() {
		return esc_html(__('Attendance','church-admin'));
	}

	public function get_icon() {
		return 'eicon-skill-bar';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'giving','pledges' ];
	}



	protected function render() {
		
        $settings = $this->get_settings_for_display();
        if(!is_user_logged_in()){
            echo wp_login_form();
        }
        else{
            if(church_admin_level_check('Directory')){
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/individual_attendance.php');
		        echo church_admin_individual_attendance();
            }else{
                echo '<p>'.esc_html( __('Only logged in users with permission can use this feature','church-admin' ) ).'</p>';
            }

        }
		
		
	}
}