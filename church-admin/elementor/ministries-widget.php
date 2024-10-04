<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_ministries_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminMinistries';
	}

	public function get_title() {
		return esc_html(__('Ministries','church-admin'));
	}

	public function get_icon() {
		return 'eicon-global-settings';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'sessions' ];
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

	
		// member types
		$member_types = church_admin_member_types_array();
		$this->add_control(
			'member_types',
			[
				'label' => esc_html__( 'Member Types', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => $member_types,
				'default' => [ 1,2,3 ],
			]
		);
        $ministries = church_admin_ministries_array();
        $this->add_control(
			'ministries',
			[
				'label' => esc_html__( 'Ministries', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => $ministries,
				
			]
		);

        $this->end_controls_section();

    }


	protected function render() {
		$member_types = church_admin_member_types_array();


        $settings = $this->get_settings_for_display();
        $membts = implode(',',$settings['member_types']);
        $ministries = implode(',',$settings['ministries']);
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/ministries.php');
        echo church_admin_frontend_ministries( $ministries,$membts);
    
		
	}
}