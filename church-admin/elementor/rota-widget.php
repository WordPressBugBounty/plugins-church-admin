<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_rota_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminMyRota';
	}

	public function get_title() {
		return esc_html(__('Schedule','church-admin'));
	}

	public function get_icon() {
		return 'eicon-calendar';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'rota','schedule' ];
	}
    protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'rota',
			[
				'label' => esc_html__( 'Schedule Options', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
        $services = church_admin_services_array();
        $this->add_control(
			'service_id',
			[
				'label' => esc_html__( 'Type', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 1,
				'options' => $services
				
			]
		);
        $this->add_control(
			'name_style',
			[
				'label' => esc_html__( 'Type', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 1,
                'options'=> [ 
                    'Full'=>__('Full Name','church-admin'),
                     'Initials'=>__('Initials','church-admin'), 
                     'FirstNameFirstLetterLastName'=>__('First name and last name initial','church-admin')
                     ] ,
                     'default'=>'Full'              
            
            ]
                );
        //Initials
		$this->add_control(
			'initials',
			[
				'label' => esc_html__( 'Initials', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
         //links
		$this->add_control(
			'links',
			[
				'label' => esc_html__( 'Links', 'church-admin' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'church-admin' ),
				'label_off' => esc_html__( 'No', 'church-admin' ),
				'return_value' => 1,
				'default' => 1,
			]
		);
        $this->add_control(
			'weeks',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__( 'Weeks to show', 'church-admin' ),
				'placeholder' => '0',
				'min' => 1,
				'max' => 52,
				'step' => 1,
				'default' => 3,
			]
		);
        $this->end_controls_section();

    }


	protected function render() {
		require_once(plugin_dir_path(dirname(__FILE__) ) .'display/rota.php');
        $settings = $this->get_settings_for_display();
		if(is_user_logged_in()||empty( $settings['logged_in'] ) )
        {
        
            if(!empty( $_REQUEST['rota_date'] ) )  {
                $date=sanitize_text_field(stripslashes($_REQUEST['rota_date']));
            }else{
                $date=wp_date('Y-m-d');
            }
            if(!church_admin_checkdate($date)){$date = wp_date('Y-m-d');}
            echo church_admin_front_end_rota( $settings['service_id'],$settings['weeks'],TRUE,$date,$settings['title'],$settings['initials'],$settings['links'],$settings['name_style'] );
        }
        else //login required
        {
            echo '<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
        }
	}
}