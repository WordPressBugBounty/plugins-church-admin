<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_calendar_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminCalendar';
	}

	public function get_title() {
		return esc_html(__('Calendar','church-admin'));
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'sermons','podcast' ];
	}

    protected function register_controls() {

		// Content Tab Start
        $facilities=church_admin_calendar_facilities_array();
	    $categories=church_admin_calendar_categories_array();
		$this->start_controls_section(
			'calendar',
			[
				'label' => esc_html__( 'Calendar Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
        $this->add_control(
			'categories',
			[
				'label' => esc_html__( 'Categories to show', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => $categories,
				
			]
		);
        $this->add_control(
			'facility_id',
			[
				'label' => esc_html__( 'Optional Facility', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				
				'options' => $facilities,
				
			]
		);
        $this->add_control(
			'style',
			[
				'label' => esc_html__( 'Style', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'New', 'church-admin' ),
				'label_off' => esc_html__( 'Table', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
        $this->end_controls_section();

    }


	protected function render() {
		
        $settings = $this->get_settings_for_display();
        $cats = !empty($settings['categories']) ? implode(',',$settings['categories']): 'All';
        if( empty($settings['style'] ))
        {
            require_once(plugin_dir_path(dirname(__FILE__) ) .'display/calendar.php');
            echo church_admin_display_calendar(NULL);
        }
        else
        {	
            require_once(plugin_dir_path(dirname(__FILE__) ) .'display/calendar.new.php');
            echo church_admin_display_new_calendar($cats,$settings['facility_id']);
        
        }
	}
}