<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_giving_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminGiving';
	}

	public function get_title() {
		return esc_html(__('Giving','church-admin'));
	}

	public function get_icon() {
		return 'eicon-paypal-button';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'giving' ];
	}

	protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'address-list',
			[
				'label' => esc_html__( 'Giving Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

	
		// funds
        $funds=get_option('church_admin_giving_funds');
		
		$this->add_control(
			'funds',
			[
				'label' => esc_html__( 'Funds', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => false,
				'options' => $funds,
				'default' => [ 0 ],
			]
		);
        //monthly
		$this->add_control(
			'monthly',
			[
				'label' => esc_html__( 'Allow recurring monthly', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);

	    $this->end_controls_section();

	}

	protected function render() {
		
        $settings = $this->get_settings_for_display();
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/giving.php');

        $funds=get_option('church_admin_giving_funds');
        $fund = !empty( $settings['fund'] ) ? $funds[$settings['fund']] : null;


        echo church_admin_giving_form( $fund,$settings['monthly']);
		
	}
}