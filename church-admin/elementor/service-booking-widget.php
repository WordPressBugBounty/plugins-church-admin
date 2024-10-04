<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_service_booking_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminServiceBooking';
	}

	public function get_title() {
		return esc_html(__('Service Booking','church-admin'));
	}

	public function get_icon() {
		return 'eicon-checkout';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'service' ];
	}

    protected function register_controls() {

		// Content Tab Start
        $services=church_admin_services_array();
	    
		$this->start_controls_section(
			'booking',
			[
				'label' => esc_html__( 'Service Booking Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
        $this->add_control(
			'service_id',
			[
				'label' => esc_html__( 'Service to show', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				
				'options' => $services,
                'default'=>array_key_first($services)
				
			]
		);
        $this->add_control(
			'booking_mode',
			[
				'label' => esc_html__( 'Booking mode', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'label_block' => true,
				
				'options' => ['bubbles'=>__('Household bubbles','church-admin'),'individuals'=>__('Individuals','church-admin')],
				'default' =>'bubbles'
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
			'max_fields',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'Maximum per booking', 'church-admin' ),
				'placeholder' => '0',
				'min' => 0,
				'max' => 12,
				'step' => 1,
				'default' => 2,
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
        $cats = !empty($settings['categories']) ? implode(',',$settings['categories']): 'All';
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/covid-prebooking.php');
        if ( empty( $settings['loggedin'] )||is_user_logged_in() )
        {
            echo church_admin_covid_attendance((int)$settings['service_id'],esc_html( $settings['booking_mode'] ),(int)$settings['max_fields'],(int)$settings['days'],$settings['admin_email'],null);
        }
        else
        {
            echo '<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
                        
        }
	}
}