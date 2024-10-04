<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_spirital_gifts_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminSpiritualGifts';
	}

	public function get_title() {
		return esc_html(__('Spiritual gifts','church-admin'));
	}

	public function get_icon() {
		return 'eicon-notification';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'giving','pledges' ];
	}

	protected function register_controls() {
        $this->start_controls_section(
			'gift_options',
			[
				'label' => esc_html__( 'Spiritual Gifts Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'admin_email',
			[
				'label' => esc_html__( 'Email results to ', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'email'
			]
		);
        $this->end_controls_section();
	}

	protected function render() {
		
        $settings = $this->get_settings_for_display();
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/spiritual-gifts.php');
		if ( empty( $settings['admin_email'] ) )$settings['admin_email']='';
		echo church_admin_spiritual_gifts( $settings['admin_email'] );
		
	}
}