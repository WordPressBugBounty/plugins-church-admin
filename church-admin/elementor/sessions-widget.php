<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_sessions_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminSessions';
	}

	public function get_title() {
		return esc_html(__('Sessions','church-admin'));
	}

	public function get_icon() {
		return 'eicon-frame-expand';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'sessions' ];
	}



	protected function render() {
		
        $settings = $this->get_settings_for_display();
		require_once(plugin_dir_path(dirname(__FILE__) ) .'includes/sessions.php');

        echo church_admin_sessions(NULL,NULL);
		
	}
}