<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_event_booking_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminEventBooking';
	}

	public function get_title() {
		return esc_html(__('Event booking','church-admin'));
	}

	public function get_icon() {
		return 'eicon-star-o';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'events' ];
	}

    public function get_script_depends() {
        return   ['church-admin-form-case-enforcer','church-admin-event-booking'];

    }

	protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'event-options',
			[
				'label' => esc_html__( 'Event Booking Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

	
		// event
		$events = church_admin_events_array();
		$this->add_control(
			'event_id',
			[
				'label' => esc_html__( 'Which upcoming event', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				
				'options' => $events,
				'default' => array_key_first($events),
			]
		);
        






		$this->end_controls_section();

		// Content Tab End



	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		

        require_once(plugin_dir_path(dirname(__FILE__) ).'display/events.php');
        echo church_admin_event_bookings_output( $settings['event_id'] );
    }
		
}