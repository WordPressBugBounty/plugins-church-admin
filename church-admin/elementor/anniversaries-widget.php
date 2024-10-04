<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_anniversaries_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminAnniversaries';
	}

	public function get_title() {
		return esc_html(__('Anniversaries','church-admin'));
	}

	public function get_icon() {
		return 'eicon-banner';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'anniversaries' ];
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
        // people types
        $people_types = church_admin_member_types_array();
        $this->add_control(
            'people_types',
            [
                'label' => esc_html__( 'People Types', 'church-admin' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple' => true,
                'options' => $people_types,
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
		//Show email
		$this->add_control(
			'show_email',
			[
				'label' => esc_html__( 'Show email address', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
        //Show email
		$this->add_control(
			'show_phone',
			[
				'label' => esc_html__( 'Show phone number', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
		//Show age
		$this->add_control(
			'show_age',
			[
				'label' => esc_html__( 'Show years', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
		$this->add_control(
			'days',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'How many days ahead', 'church-admin' ),
				'placeholder' => '0',
				'min' => 0,
				'max' => 365,
				'step' => 1,
				'default' => 31,
			]
		);
		






		$this->end_controls_section();

		// Content Tab End



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
			$peoplets = implode(',',$settings['people_types']);
			
			
            if ( empty( $attributes['loggedin'] )||is_user_logged_in() )
            {
                require_once(plugin_dir_path(dirname(__FILE__) ).'includes/birthdays.php');
                echo church_admin_frontend_anniversaries( (int)$membts,(int)$peoplets, (int)$settings['days'],(int)$settings['show_age'],(int)$settings['show_email'],(int)$settings['show_phone'] );
            }
            else //login required
            {
                echo '<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
            }
		}

	}
}