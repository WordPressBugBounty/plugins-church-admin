<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_sermons_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminSermons';
	}

	public function get_title() {
		return esc_html(__('Sermons','church-admin'));
	}

	public function get_icon() {
		return 'eicon-headphones';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'sermons','podcast' ];
	}

    protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'address-list',
			[
				'label' => esc_html__( 'Ministries Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

	
		$this->add_control(
			'start_date',
			[
				'label' => esc_html__( 'First sermon date', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
			]
		);
        $this->add_control(
            'rolling',
            [
                'label' => esc_html__( 'Rolling start date', 'church-admin' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'church-admin' ),
                'label_off' => esc_html__( 'No', 'church-admin' ),
                'return_value' => 1,
                'default' => 1,
            ]
        );
        $this->add_control(
            'nowhite',
            [
                'label' => esc_html__( 'Get rid of white space above video (caused by your theme)', 'church-admin' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'church-admin' ),
                'label_off' => esc_html__( 'No', 'church-admin' ),
                'return_value' => 1,
                'default' => 1,
            ]
        );
        $this->add_control(
			'how_many',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'How many sermons per page', 'church-admin' ),
				'placeholder' => '0',
				'min' => 0,
				'max' => 52,
				'step' => 1,
				'default' => 12,
			]
		);
        $this->add_control(
            'playnoshow',
            [
                'label' => esc_html__( "Show audio plays counter", 'church-admin' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'No', 'church-admin' ),
                'label_off' => esc_html__( 'Yes', 'church-admin' ),
                'return_value' => 1,
                'default' => 1,
            ]
        );
        $this->end_controls_section();

    }


	protected function render() {
		
        $settings = $this->get_settings_for_display();
        require_once(plugin_dir_path(dirname(__FILE__) ) .'display/new-sermon-podcast.php');
        $how_many = !empty( $settings['how_many'] ) ? $settings['how_many'] : 12;
        $nowhite=empty( $settings['nowhite'] )?0:1;
        $playnoshow=empty($settings['playnoshow'])?0:1;
        $rolling=(!empty($settings['rolling']) && church_admin_int_check($settings['rolling'])) ? (int)$settings['rolling'] : null;
        $start_date=(!empty($settings['start_date']) &&church_admin_checkdate($settings['start_date'])) ? $settings['start_date'] : null;
        echo church_admin_new_sermons_display($how_many,$nowhite,$playnoshow,$start_date,$rolling);
	}
}