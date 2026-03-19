<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_series_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminSeries';
	}

	public function get_title() {
		return esc_html(__('Sermon Series','church-admin'));
	}

	public function get_icon() {
		return 'eicon-headphones';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'sermons','podcast' ];
	}

	protected function register_controls() {

		// Content Tab Start

		$this->start_controls_section(
			'series',
			[
				'label' => esc_html__( 'Sermon Series Option', 'church-admin' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'sermon_page',
			[
				'label' => esc_html__( 'Sermon page Link', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::URL,
				'options' =>false,
				'label_block' => true,
			]
		);


		$this->end_controls_section();

		// Content Tab End



	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
        if(empty($settings['series_page'])){
            echo '<p>'.__('Please set a sermon page for this Elementor widget','church-admin').'</p>';
            return;
        }
		require_once(plugin_dir_path(dirname(__FILE__) ).'display/sermon-series.php');
		echo church_admin_all_the_series_display( $settings['sermon_page'] );

	}
}