<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly




function church_admin_register_elementor_widgets( $widgets_manager ) {
	
	//address list
	require_once(plugin_dir_path(__FILE__).'/address-list-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_address_list_widget() );
            
	
	//calendar
	require_once(plugin_dir_path(__FILE__).'/calendar-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_calendar_widget() );
	
	//calendar list
	require_once(plugin_dir_path(__FILE__).'/calendar-list-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_calendar_list_widget() );
	


	


	//recent activity
	require_once(plugin_dir_path(__FILE__).'/recent-activity-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_recent_activity_widget() );

	//register
	require_once(plugin_dir_path(__FILE__).'/register-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_register_widget() );

	//sermon 
	require_once(plugin_dir_path(__FILE__).'/sermons-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_sermons_widget() );

	
	
	

}
add_action( 'elementor/widgets/register', 'church_admin_register_elementor_widgets' );

function church_admin_elementor_editor_scripts() {

	$src = 'https://maps.googleapis.com/maps/api/js';
	$key='?key='.get_option('church_admin_google_api_key');
			
	wp_register_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);

}
add_action( 'elementor/editor/before_enqueue_scripts', 'church_admin_elementor_editor_scripts' );

/******************************************
 * Elementor Category
 *****************************************/
function church_admin_add_elementor_widget_categories( $elements_manager ) {

	$elements_manager->add_category(
		'church-admin',
		[
			'title' => 'Church Admin',
			'icon' => 'fa fa-plug',
		]
	);
	

}
add_action( 'elementor/elements/categories_registered', 'church_admin_add_elementor_widget_categories' );