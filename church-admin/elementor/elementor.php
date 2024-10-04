<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly




function church_admin_register_elementor_widgets( $widgets_manager ) {
	
	//address list
	require_once(plugin_dir_path(__FILE__).'/address-list-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_address_list_widget() );
            
	//anniversaries
	require_once(plugin_dir_path(__FILE__).'/anniversaries-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_anniversaries_widget() );
	
	//attendance
	require_once(plugin_dir_path(__FILE__).'/attendance-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_attendance_widget() );
	
	//birthdays
	require_once(plugin_dir_path(__FILE__).'/birthdays-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_birthdays_widget() );

	//calendar
	require_once(plugin_dir_path(__FILE__).'/calendar-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_calendar_widget() );
	
	//calendar list
	require_once(plugin_dir_path(__FILE__).'/calendar-list-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_calendar_list_widget() );
	
	//contact form
	require_once(plugin_dir_path(__FILE__).'/contact-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_contact_widget() );

	//event booking
	require_once(plugin_dir_path(__FILE__).'/event-booking-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_event_booking_widget() );

	//giving
	require_once(plugin_dir_path(__FILE__).'/giving-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_giving_widget() );

	//graph
	require_once(plugin_dir_path(__FILE__).'/graph-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_graph_widget() );
	
	//member map
	require_once(plugin_dir_path(__FILE__).'/member-map-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_member_map_widget() );

	//not available
	require_once(plugin_dir_path(__FILE__).'/ministries-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_ministries_widget() );

	//my rota
	require_once(plugin_dir_path(__FILE__).'/my-rota-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_my_rota_widget() );

	//not available
	require_once(plugin_dir_path(__FILE__).'/not-available-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_not_available_widget() );

	//pledge
	require_once(plugin_dir_path(__FILE__).'/pledges-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_pledge_widget() );

	//recent activity
	require_once(plugin_dir_path(__FILE__).'/recent-activity-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_recent_activity_widget() );

	//register
	require_once(plugin_dir_path(__FILE__).'/register-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_register_widget() );

	//rota
	require_once(plugin_dir_path(__FILE__).'/rota-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_rota_widget() );
	
	//sermon series
	require_once(plugin_dir_path(__FILE__).'/series-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_series_widget() );

	//sermon 
	require_once(plugin_dir_path(__FILE__).'/sermons-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_sermons_widget() );

	//sermon 
	require_once(plugin_dir_path(__FILE__).'/service-booking-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_service_booking_widget() );

	//sessions
	require_once(plugin_dir_path(__FILE__).'/sessions-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_sessions_widget() );

	//small group
	require_once(plugin_dir_path(__FILE__).'/small-groups-widget.php');
	$widgets_manager->register(  new \Elementor_church_admin_small_groups_widget() );

	//small group
	require_once(plugin_dir_path(__FILE__).'/small-group-members-widget.php');
	$widgets_manager->register(  new \Elementor_church_admin_small_group_members_widget() );

	//spiritual widget
	require_once(plugin_dir_path(__FILE__).'/spiritual-gifts-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_spirital_gifts_widget() );


	//volunteer
	require_once(plugin_dir_path(__FILE__).'/volunteer-widget.php');
	$widgets_manager->register( new \Elementor_church_admin_volunteer_widget() );
	
	
	
	
	
	

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