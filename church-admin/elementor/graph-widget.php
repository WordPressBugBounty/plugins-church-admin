<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_graph_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminGraph';
	}
    public function get_script_depends() {
		return [ 'jquery-ui-datepicker', 'church_admin_google_graph_api' ];
	}


	public function get_title() {
		return esc_html(__('Attendance Graphs','church-admin'));
	}

	public function get_icon() {
		return 'eicon-hotspot';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'map' ];
	}

	protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'graph',
			[
				'label' => esc_html__( 'Graph Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

	
		
		$this->add_control(
			'type',
			[
				'label' => esc_html__( 'Type', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'weekly',
				'options' => [
					
					'weekly' => esc_html__( 'Weekly', 'church-admin' ),
					'rolling' => esc_html__( 'Rolling', 'church-admin' ),
				],
				
			]
		);
       
		$services = church_admin_services_array();
        $this->add_control(
			'service_id',
			[
				'label' => esc_html__( 'Type', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 1,
				'options' => $services
				
			]
		);
        $this->add_control(
			'height',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'Height in px', 'church-admin' ),
				'placeholder' => '0',
				'min' => 0,
				'max' => 1200,
				'step' => 1,
				'default' => 400,
			]
		);
		
        $this->add_control(
			'width',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'Widtht in px', 'church-admin' ),
				'placeholder' => '0',
				'min' => 0,
				'max' => 1800,
				'step' => 1,
				'default' => 600,
			]
		);
		$this->add_control(
			'start_date',
			[
				'label' => esc_html__( 'Due Date', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
			]
		);

        $this->add_control(
			'end_date',
			[
				'label' => esc_html__( 'End Date', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
			]
		);


		$this->end_controls_section();

		// Content Tab End



	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		global $wpdb;
        require_once(plugin_dir_path(dirname(__FILE__) ).'display/graph.php');
		echo church_admin_graph( $settings['type'],$settings['service_id'],$settings['start_date'],$settings['end_date'],$settings['width'],$settings['height'],FALSE);
    }

	
}