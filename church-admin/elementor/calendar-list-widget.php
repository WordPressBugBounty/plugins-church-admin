<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_calendar_list_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminCalendarList';
	}

	public function get_title() {
		return esc_html(__('Calendar List','church-admin'));
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'calendar' ];
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
			'days',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'How many days to show', 'church-admin' ),
				'placeholder' => '0',
				'min' => 0,
				'max' => 365,
				'step' => 1,
				'default' => 31,
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
        
        $this->end_controls_section();

    }


	protected function render() {
		
        $settings = $this->get_settings_for_display();
        $cats = !empty($settings['categories']) ? implode(',',$settings['categories']): 'All';
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/calendar-list.php');
        echo church_admin_calendar_list( $settings['days'],$cats,$settings['facility_id']);
	}
}