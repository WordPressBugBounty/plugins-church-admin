<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_small_groups_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminSmallGroups';
	}
    public function get_script_depends() {
		return [ 'church_admin_sg_map_script','church_admin_google_graph_api','church_admin_google_maps_api' ];
	}


	public function get_title() {
		return esc_html(__('Small Groups','church-admin'));
	}

	public function get_icon() {
		return 'eicon-hotspot';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'map','small groups' ];
	}

	protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'groups_options',
			[
				'label' => esc_html__( 'Small Groups  Options', 'church-admin' ),
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
    
        
        //Logged in only
        $this->add_control(
            'logged_in',
            [
                'label' => esc_html__( 'Show only to logged in users', 'church-admin' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'church-admin' ),
                'label_off' => esc_html__( 'All', 'church-admin' ),
                'return_value' => 1,
                'default' => 1,
            ]
        );
        //Photos
        $this->add_control(
            'photos',
            [
                'label' => esc_html__( 'Photos', 'church-admin' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'church-admin' ),
                'label_off' => esc_html__( 'No', 'church-admin' ),
                'return_value' => 1,
                'default' => 1,
            ]
        );
        //PDF Link
		$this->add_control(
			'pdf_link',
			[
				'label' => esc_html__( 'PDF link', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
        	// Show Google Maps
		$google_api_key = null;
		$google_api_key = get_option('church_admin_google_api_key');
		
		if(!empty($google_api_key)){
		
			$this->add_control(
				'show_google_maps',
				[
					'label' => esc_html__( 'Show Map', 'church-admin' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Yes', 'church-admin' ),
					'label_off' => esc_html__( 'No', 'church-admin' ),
					'return_value' => 1,
					'default' => 1,
				]
			);
		}


        $this->add_control(
            'zoom',
            [
                'type' => \Elementor\Controls_Manager::NUMBER,
                'label' => esc_html__( 'Zoom level', 'church-admin' ),
                'placeholder' => '0',
                'min' => 1,
                'max' => 20,
                'step' => 1,
                'default' => 13,
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
		


		$this->end_controls_section();

		// Content Tab End



	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		global $wpdb;
        require_once(plugin_dir_path(dirname(__FILE__) ).'display/small-group-list.php');
		echo church_admin_small_group_list( $settings['show_google_maps'],$settings['zoom'],$settings['photos'],$settings['logged_in'],null,$settings['pdf_link'] );
    }

	
}