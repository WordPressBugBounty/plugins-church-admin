<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_not_available_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminNotAvailable';
	}

	public function get_title() {
		return esc_html(__('Not Available','church-admin'));
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'availability','schedule' ];
	}



	protected function render() {
		
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/not-available.php');
	    echo church_admin_not_available();
		
	}
}