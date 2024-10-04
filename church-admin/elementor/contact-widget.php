<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_contact_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminContact';
	}

	public function get_title() {
		return esc_html(__('Contact form','church-admin'));
	}

	public function get_icon() {
		return 'eicon-envelope';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'contact form' ];
	}



	protected function render() {
		
        $settings = $this->get_settings_for_display();
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/contact.php');

        echo church_admin_contact_public();
		
	}
}