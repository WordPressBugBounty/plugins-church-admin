<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_my_rota_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminMyRota';
	}

	public function get_title() {
		return esc_html(__('My Schedule','church-admin'));
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'rota','schedule' ];
	}



	protected function render() {
		
        $settings = $this->get_settings_for_display();
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/rota.php');
        if ( empty( $loggedin)||is_user_logged_in() )
        {
            $out.=church_admin_my_rota();
        }
        else //login required
        {
            $out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
        }
	}
}