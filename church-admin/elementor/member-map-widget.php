<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_member_map_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminMemberMap';
	}
    public function get_script_depends() {
		return [ 'church_admin_map', 'church_admin_google_maps_api' ];
	}


	public function get_title() {
		return esc_html(__('Member map','church-admin'));
	}

	public function get_icon() {
		return 'eicon-google-maps';
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
			'birthdays',
			[
				'label' => esc_html__( 'Anniversaries Options', 'church-admin' ),
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

		if(is_user_logged_in() )
        {
            
            $membts = implode(',',$settings['member_types']);
            $service=$wpdb->get_row('SELECT lat,lng  FROM '.$wpdb->prefix.'church_admin_sites WHERE lat!="" AND lng!="" ORDER BY site_id ASC LIMIT 1');
            echo '<div class="church-admin-member-map"><script type="text/javascript">var xml_url="'.site_url().'/?ca_download=address-xml&member_type_id='.esc_attr( $membts ).'&address-xml='.wp_create_nonce('address-xml').'";';
            echo ' var lat='.esc_html( $service->lat).';';
            echo ' var lng='.esc_html( $service->lng).';';
            echo ' var zoom='.esc_html( $settings['zoom'] ).';';
            echo ' var translation=["'.esc_html( __('Small Groups','church-admin' ) ).'","'.esc_html( __('Unattached','church-admin' ) ).'","'.esc_html( __('In a group','church-admin' ) ).'","'.esc_html( __('Group','church-admin' ) ).'"];';
            echo 'jQuery(document).ready(function()  {console.log("Ready to lead");
        load(lat,lng,xml_url,zoom,translation);});</script><div id="church-admin-member-map" style="width:'.$settings['width'].';height:'.$settings['height'].'"></div>';
            echo '<div id="groups" ><p><img src="https://maps.google.com/mapfiles/kml/paddle/blu-circle.png" />'.esc_html( __('Small Group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/red-circle.png" />'.esc_html( __('Not in a small group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/grn-circle.png" />'.esc_html( __('In a small Group','church-admin' ) ).'</p></div>';
            echo '</div>';
        }
        else {
            echo '<h3>'.esc_html( __('You need to be logged in to view the map','church-admin' ) ).'</h3>'.wp_login_form(array('echo'=>false) );
        }
    }

	
}