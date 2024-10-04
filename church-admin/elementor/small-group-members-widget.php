<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


class Elementor_church_admin_small_group_members_widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'churchAdminSmallGroupMembers';
	}

	public function get_title() {
		return esc_html(__('Small group Members','church-admin'));
	}

	public function get_icon() {
		return 'eicon-theme-builder';
	}

	public function get_categories() {
		return [ 'church-admin' ];
	}

	public function get_keywords() {
		return [ 'church admin', 'smallgroups','groups' ];
	}

	protected function register_controls() {
        $this->start_controls_section(
			'gift_options',
			[
				'label' => esc_html__( 'Small Group Members Options', 'church-admin' ),
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
        $this->end_controls_section();
	}

	protected function render() {
		
        $settings = $this->get_settings_for_display();
        require_once(plugin_dir_path(dirname(__FILE__) ).'/display/small-groups.php');
        $membts = implode(',',$settings['member_types']);
		echo church_admin_frontend_small_groups( $membts,FALSE);
	}
}