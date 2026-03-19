<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_recent_activity_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminRecentActivity';
	}

	public function get_title() {
		return esc_html(__('Recent activity','church-admin'));
	}

	public function get_icon() {
		return 'eicon-click';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'directory' ];
	}

    protected function register_controls() {
        $this->start_controls_section(
			'recent_options',
			[
				'label' => esc_html__( 'Recent Activity Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'weeks',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'Weeks to show', 'church-admin' ),
				'placeholder' => '0',
				'min' => 1,
				'max' => 52,
				'step' => 1,
				'default' => 3,
			]
		);
        $this->end_controls_section();
	}

	protected function render() {
		
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/not-available.php');
        if ( empty( $loggedin)||is_user_logged_in() )
        {
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/recent.php');
            
            echo church_admin_recent_display( $settings['weeks'] );
        }
        else //login required
        {
            echo '<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
        }
		
	}
}