<?php



if( !defined( 'WP_UNINSTALL_PLUGIN' ) ){
    exit();
}

global $wpdb;

$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_inventory');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_my_prayer');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_pledge');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_giving');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_giving_meta');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_covid_attendance');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_plan_visit');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_attendance');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_brplan');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_app');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_app_visits');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_safeguarding');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_bible_books');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_calendar_category');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_cell_structure');
$wpdb->query( 'DROP  TABLE IF EXISTS '.$wpdb->prefix.'church_admin_classes');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_comments');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_contact_form');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_custom_fields');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_custom_fields_meta');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_calendar_date');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_events');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_bookings');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_tickets');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_sermon_files');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_email');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_email_build');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_facilities_bookings');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_facilities');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_funnels');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_follow_up');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_household');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_hope_team');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_individual_attendance');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_kidswork');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_not_available');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_people_meta');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_metrics');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_metrics_meta');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_member_types');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_ministries');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_event_payments');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_people');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_donor_receipts');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_new_rota');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_rotas');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_rota_settings');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_smallgroup');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_services');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_session');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_session_meta');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_sites');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_sermon_series');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_twilio_messages');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_units');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_unit_meta');
$wpdb->query( 'DROP TABLE IF EXISTS '.$wpdb->prefix.'church_admin_visits');
//delete options
$wpdb->query('DELETE FROM '.$wpdb->prefix.'options WHERE option_name LIKE "%church_admin%"');
//delete cache directory
$upload_dir = wp_upload_dir();
$folder=$upload_dir['basedir'].'/church-admin-cache/';
 
//Get a list of all of the file names in the folder.
$files = glob($folder . '/*');
 
//Loop through the file list.
foreach($files as $file){
    //Make sure that this is a file and not a directory.
    if(is_file($file)){
    //Use the unlink function to delete the file.
    unlink($file);
    }
}
rmdir($folder);