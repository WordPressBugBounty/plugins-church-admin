<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_address_list_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminAddressList';
	}

	public function get_title() {
		return esc_html(__('Address List','church-admin'));
	}

	public function get_icon() {
		return 'eicon-site-identity';
	}
	public function get_script_depends() {
		return [  'church_admin_google_maps_api' ];
	}
	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'address','directory' ];
	}

	protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'address-list',
			[
				'label' => esc_html__( 'Address List Options', 'church-admin' ),
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
		//Children
		$this->add_control(
			'children',
			[
				'label' => esc_html__( 'Show children', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
		//Updateable
		$this->add_control(
			'updateable',
			[
				'label' => esc_html__( 'User update link', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
		//VCF link
		$this->add_control(
			'vcf_link',
			[
				'label' => esc_html__( 'V-card link', 'church-admin' ),
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
					'label' => esc_html__( 'Show Google Map', 'church-admin' ),
					'type' => \Elementor\Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Yes', 'church-admin' ),
					'label_off' => esc_html__( 'No', 'church-admin' ),
					'return_value' => 1,
					'default' => 1,
				]
			);
		}






		$this->end_controls_section();

		// Content Tab End


		// Style Tab Start

		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'elementor-addon' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Text Color', 'elementor-addon' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .hello-world' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Style Tab End

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		

		if(!empty($settings['logged_in']) && !is_user_logged_in()){
			
			echo wp_login_form();

		}
		else{

			$google_api_key = null;
			$google_api_key = get_option('church_admin_google_api_key');
			
			require_once(plugin_dir_path(dirname(__FILE__) ) .'display/address-list.php');
		
						
			$membts = implode(',',$settings['member_types']);
			
			if(!empty( $settings['pdf_link'] ) )
			{
					echo'<div class="church-admin-address-pdf-links"><p><a  target="_blank" href="'.wp_nonce_url(home_url().'/?ca_download=addresslist-family-photos&amp;kids='.$settings['children'].'&amp;member_type_id='.esc_attr($membts),'address-list' ).'">'.esc_html( __('PDF version','church-admin' ) ).'</a></p></div>';
						
			}

			echo church_admin_frontend_directory( $membts,$settings['show_google_maps'],$settings['photos'],$google_api_key,$settings['children'],null,$settings['updateable'],1,0,$settings['vcf_link'],1 );
		}

	}
}