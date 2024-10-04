<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_register_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminRegister';
	}

	public function get_title() {
		return esc_html(__('Register','church-admin'));
	}

	public function get_icon() {
		return 'eicon-user-circle-o';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'register' ];
	}
    protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'register',
			[
				'label' => esc_html__( 'Register Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
        // member types
        $member_types = church_admin_member_types_array();
        $this->add_control(
			'member_type_id',
			[
				'label' => esc_html__( 'Member type to save as', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 1,
				'options' => $member_types,
				
			]
		);
        $this->add_control(
			'exclude',
			[
				'label' => esc_html__( 'Exclude', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => array('gender'=>esc_html(__('Gender','church-admin')),'date_of_birth'=>esc_html(__('Date of birth','church-admin')),'groups'=>esc_html(__('Small groups','church-admin')),'custom'=>esc_html(__('Custom fields','church-admin'))),
				'default' => [ 1,2,3 ],
			]
		);
        $this->add_control(
			'allow',
			[
				'label' => esc_html__( 'Allow', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => array('sites'=>esc_html(__('Sites','church-admin')),'groups'=>esc_html(__('Small groups','church-admin')),'ministries'=>esc_html(__('Ministries','church-admin'))),
				'default' => [ 1,2,3 ],
			]
		);
        //eadmin email
        $this->add_control(
			'admin_email',
			[
				'label' => esc_html__( 'Send email to admin', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
            //allow_registrations
            $this->add_control(
                'allow_registrations',
                [
                    'label' => esc_html__( 'Allow registrations', 'church-admin' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => esc_html__( 'Yes', 'church-admin' ),
                    'label_off' => esc_html__( 'No', 'church-admin' ),
                    'return_value' => 1,
                    'default' => 1,
                ]
            );
        //sjow onboarding custom fields
        $this->add_control(
            'onboarding',
            [
                'label' => esc_html__( 'Show onboarding custom fields', 'church-admin' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'church-admin' ),
                'label_off' => esc_html__( 'No', 'church-admin' ),
                'return_value' => 1,
                'default' => 1,
            ]
        );
        //full privacy show
        $this->add_control(
            'full_privacy_show',
            [
                'label' => esc_html__( 'Show all privacy options', 'church-admin' ),
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
        require_once(plugin_dir_path(dirname(__FILE__) ) .'includes/front_end_register.php');
		echo church_admin_front_end_register( (int)$settings['member_type_id'], $settings['exclude'], $settings['admin_email'] , $settings['allow'], $settings['allow_registrations'],$settings['onboarding'],$settings['full_privacy_show']);
	}
}