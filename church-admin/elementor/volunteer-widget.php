<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_volunteer_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminVolunteer';
	}

	public function get_title() {
		return esc_html(__('Volunteer','church-admin'));
	}

	public function get_icon() {
		return 'eicon-heart';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'volunteer','serving' ];
	}



	protected function render() {
		
        $settings = $this->get_settings_for_display();
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/volunteer.php');

        echo church_admin_display_volunteer();
		
	}
}