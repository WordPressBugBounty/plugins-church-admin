<?php
/*

Plugin Name: Church Admin
Plugin URI: http://www.churchadminplugin.com/
Description: Manage church life with address book, schedule, classes, small groups, and advanced communication tools - bulk email and sms. 
Version: 5.0.15
Tags: sermons, sermons, prayer, membership, SMS, Bible, events, calendar, email, small groups, contact form, giving, administration, management, child protection, safeguarding
Author: Andy Moyle
Text Domain: church-admin
Elementor tested up to: 3.26.0

Author URI: http://www.themoyles.co.uk
License:
----------------------------------------


Copyright (C) 2010-2022 Andy Moyle



    This program is free software: you can redistribute it and/or modify

    it under the terms of the GNU General Public License as published by

    the Free Software Foundation, either version 3 of the License, or

    (at your option) any later version.



    This program is distributed in the hope that it will be useful,

    but WITHOUT ANY WARRANTY; without even the implied warranty of

    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

    GNU General Public License for more details.



	http://www.gnu.org/licenses/

----------------------------------------
  ___ _   _             _ _         _                 _         _
 |_ _| |_( )___    __ _| | |   __ _| |__   ___  _   _| |_      | | ___  ___ _   _ ___
  | || __|// __|  / _` | | |  / _` | '_ \ / _ \| | | | __|  _  | |/ _ \/ __| | | / __|
  | || |_  \__ \ | (_| | | | | (_| | |_) | (_) | |_| | |_  | |_| |  __/\__ \ |_| \__ \
 |___|\__| |___/  \__,_|_|_|  \__,_|_.__/ \___/ \__,_|\__|  \___/ \___||___/\__,_|___/


*/
if(!defined('CHURCH_ADMIN_VERSION')){define('CHURCH_ADMIN_VERSION','5.0.15');}

define('CA_PAYPAL',"https://www.paypal.com/cgi-bin/webscr");
require_once( plugin_dir_path( __FILE__ ) .'includes/functions.php');
require_once( plugin_dir_path( __FILE__ ) .'elementor/elementor.php');
require_once( plugin_dir_path( __FILE__ ).'includes/enqueue.php');

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
if(is_admin() && !empty($_GET['page']) && $_GET['page']=='church_admin/index.php'){
    
   
    require_once( plugin_dir_path( __FILE__ ).'includes/new-style-callbacks.php');
    require_once( plugin_dir_path( __FILE__ ).'includes/custom_fields.php');
}
	
$church_admin_url='admin.php?page=church_admin/index.php';
$people_type=get_option('church_admin_people_type');
    
$level=get_option('church_admin_levels');
if(!defined('CA_ICON')){
    define('CA_ICON','data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDM2IDM2IiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zOnNlcmlmPSJodHRwOi8vd3d3LnNlcmlmLmNvbS8iIHN0eWxlPSJmaWxsLXJ1bGU6ZXZlbm9kZDtjbGlwLXJ1bGU6ZXZlbm9kZDtzdHJva2UtbGluZWpvaW46cm91bmQ7c3Ryb2tlLW1pdGVybGltaXQ6MjsiPgogICAgPGcgdHJhbnNmb3JtPSJtYXRyaXgoMC4wNzEwMTc4LDAsMCwwLjA2NjYyMzcsLTAuMjI4NzM4LDAuOTkyMTg4KSI+CiAgICAgICAgPHBhdGggZD0iTTI1NiwwTDIzOC4zLDIyTDE0Ny43LDEzNS4zTDE0Mi43LDE0MS43TDE0Mi43LDI4NEwxNy4zLDM2Mi42TDQxLjQsNDAwLjhMNTIsMzk0LjRMNTIsNTEyTDIzMy4zLDUxMkwyMzMuMyw0MjEuNEMyMzMuMyw0MDguNiAyNDMuMSwzOTguNyAyNTYsMzk4LjdDMjY4LjgsMzk4LjcgMjc4LjcsNDA4LjUgMjc4LjcsNDIxLjRMMjc4LjcsNTEyTDQ2MCw1MTJMNDYwLDM5NC40TDQ3MC42LDQwMC44TDQ5NC43LDM2Mi42TDM2OS4zLDI4NEwzNjkuMywxNDEuNkwzNjQuMywxMzUuMkwyNzMuNywyMkwyNTYsMFpNMjU2LDcyLjJMMzI0LDE1Ny4yTDMyNCwyNTUuNkwyNjgsMjIwLjlMMjU2LDIxMy4xTDI0NCwyMjAuOUwxODgsMjU1LjZMMTg4LDE1Ny4yTDI1Niw3Mi4yWk0yNTYsMTQ5LjRDMjQzLjUsMTQ5LjQgMjMzLjMsMTU5LjYgMjMzLjMsMTcyLjFDMjMzLjMsMTg0LjYgMjQzLjUsMTk0LjggMjU2LDE5NC44QzI2OC41LDE5NC44IDI3OC43LDE4NC42IDI3OC43LDE3Mi4xQzI3OC43LDE1OS42IDI2OC41LDE0OS40IDI1NiwxNDkuNFpNMjU2LDI2N0w0MTQuNiwzNjYuMUw0MTQuNiw0NjYuN0wzMjQsNDY2LjdMMzI0LDQyMS40QzMyNCwzODQuMSAyOTMuMywzNTMuNCAyNTYsMzUzLjRDMjE4LjcsMzUzLjQgMTg4LDM4NC4xIDE4OCw0MjEuNEwxODgsNDY2LjdMOTcuNCw0NjYuN0w5Ny40LDM2Ni4xTDI1NiwyNjdaIiBzdHlsZT0iZmlsbDpyZ2IoMTU2LDE2MiwxNjcpO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpyZ2IoMTU2LDE2MiwxNjcpO3N0cm9rZS13aWR0aDoxNC41MnB4OyIvPgogICAgPC9nPgo8L3N2Zz4K');

}
    /***************************
*
*   Export ical
*
****************************/
    add_action('wp_loaded','church_admin_ical_download');
    function church_admin_ical_download()
    {
        if(empty($_GET['page']) || $_GET['page']!='church_admin/index.php'){return;}
        if(!empty( $_GET['action'] )&&$_GET['action']=='export-ics')
        {
            if(!church_admin_level_check('Calendar') )exit('No download permission');
            require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
            church_admin_export_ical();
            exit();
        }
    
    }




add_action('wp_loaded','church_admin_debug_mode_switch');
function church_admin_debug_mode_switch()
{
    
    /***************************
    *
    *   Debug Mode
    *
    ****************************/
    if(!empty( $_GET['action'] )&& $_GET['action']=='toggle-debug-mode' )
    {
        if (empty($_GET['_wpnonce']) || ! wp_verify_nonce($_GET['_wpnonce'], 'toggle-debug-mode' ) ) {
            die( __( 'Failed security check', 'church-admin' ) ); 
        }
        $debug=get_option('church_admin_debug_mode');
        if ( empty( $debug ) )
        {
            update_option('church_admin_debug_mode',TRUE);

            setcookie('ca_debug_mode', 'DEBUG MODE ON', time()+31556926);
            if(!defined('CA_DEBUG')){define('CA_DEBUG',TRUE);}
        }
        else
        {
                
            unset( $_COOKIE['ca_debug_mode'] );
            setcookie('ca_debug_mode', "", time() - 3600);
            delete_option('church_admin_debug_mode',FALSE);
        }
    }	


    global $church_admin_url;
  
    if(!empty( $_COOKIE['ca_debug_mode'] ) )
    {
        if(!(defined('CA_DEBUG') ))define('CA_DEBUG',TRUE);
    }
    /*
    $debug_mode=get_option('church_admin_debug_mode');
    if(!empty( $debug_mode) )
    {
        if(!(defined('CA_DEBUG') ))define('CA_DEBUG',TRUE);
    }
    */
}





add_action('admin_init','church_admin_admin_init');
function church_admin_admin_init()  {

    

    if(empty($_GET['page']) || $_GET['page']!='church_admin/index.php'){return;}

    global $wpdb, $church_admin_url,$church_admin_menu;
    


 
    $church_admin_menu=array(
        
        'people'=>array(
            
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('People','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&amp;action=people&section=people','people'),
                    "dashicon"=>'dashicons-id',
                    "font-awesome"=>'<span class="ca-dashicons dashicons dashicons-admin-users ca-dashicons"></span>',
                    "background"=>"ca-yellow",
                    "callback"=>'church_admin_people_callback'
                ),
        'check-directory-issues'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Check for Directory issues','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=check-directory-issues&amp;section=people','check-directory-issues')
                ),
        'import-from-users'=>array(
            'module'=>'People',
            'parent'=>'people',
            'level'=>'Directory',
            'section'=>'people',
            'title'=>esc_html( __('Import users into directory','church-admin' ) ),
            'link'=>wp_nonce_url($church_admin_url.'&action=import-from-users&amp;section=people','import-from-users')
        ),
       
        'view-address-list'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('View address list','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=view-address-list&amp;section=people','view-address-list')
                ),
        'add-household'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Add household','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=add-household&amp;section=people', 'add-household')
                ),
        'import-csv'=>array(
                     'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Import CSV','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=import-csv&amp;section=people','import-csv')
                ),
        'directory-pdf'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Directory PDF','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=export-pdf&amp;section=people','address-list')
                ),
        'member-types'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Member types','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=member-types&amp;section=people','member-types')
                ),
        'edit-member-type'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Add member type','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=edit-member-type&amp;section=people','edit-member-type')
                ),
        
        
        'bulk-geocode'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Bulk geocode ','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=bulk-geocode&amp;section=people','bulk-geocode')
                ),
        'bulk-edit-anniversary'=>array(
            'module'=>'People',
            'parent'=>'people',
            'level'=>'Directory',
            'section'=>'people',
            'confirm'=>TRUE,
            'title'=>esc_html( __('Bulk edit anniversaries','church-admin' ) ),
            'link'=>wp_nonce_url($church_admin_url.'&action=bulk-edit-anniversary&amp;section=people','bulk-edit-anniversary')
        ),
        'bulk-edit-comms-permissions'=>array(
            'module'=>'People',
            'parent'=>'people',
            'level'=>'Directory',
            'section'=>'people',
            'confirm'=>TRUE,
            'title'=>esc_html( __('Bulk edit email permissions','church-admin' ) ),
            'link'=>wp_nonce_url($church_admin_url.'&action=bulk-edit-comms-permissions&amp;section=people','bulk-edit-comms-permissions')
        ),
        'bulk-edit-custom'=>array(
            'module'=>'People',
            'parent'=>'people',
            'level'=>'Directory',
            'section'=>'people',
            'confirm'=>TRUE,
            'title'=>esc_html( __('Bulk edit custom field entries','church-admin' ) ),
            'link'=>wp_nonce_url($church_admin_url.'&action=bulk-edit-custom&amp;section=people', 'bulk-edit-custom')
        ),
        'bulk-edit-dob'=>array(
            'module'=>'People',
            'parent'=>'people',
            'level'=>'Directory',
            'section'=>'people',
            'confirm'=>TRUE,
            'title'=>esc_html( __('Bulk edit dates of birth','church-admin' ) ),
            'link'=>wp_nonce_url($church_admin_url.'&action=bulk-edit-dob&amp;section=people','bulk-edit-dob')
        ),
        'create-users'=>array(
            'module'=>'People',
            'parent'=>'people',
            'level'=>'Directory',
            'section'=>'people',
            'title'=>esc_html( __('Create users ','church-admin' ) ).' &raquo;',
            'link'=>wp_nonce_url($church_admin_url.'&action=create-users&amp;section=people','create-users')
        ),
        'custom-fields'=>array(
            'module'=>'People',
            'parent'=>'people',
            'level'=>'Directory',
            'section'=>'people',
            'title'=>esc_html( __('Custom fields','church-admin' ) ),
            'link'=>wp_nonce_url($church_admin_url.'&action=custom-fields&amp;section=people','custom-fields')
        ),
        'download-csv'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Download CSV/labels ','church-admin' ) ).' &raquo;',
                    'link'=>wp_nonce_url($church_admin_url.'&action=download-csv&amp;section=people','download-csv')
                ),
        'recent-activity'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Recent people activity ','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=recent-activity&amp;section=people','recent-activity')
                ),
        'check-duplicates'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Check duplicates','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=check-duplicates&amp;section=people','check-duplicates')
                ),
        
        'photo-permissions'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Photo Permissions','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=photo-permissions&amp;section=people','photo-permissions')
                ),
        'photo-permissions-pdf'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Photo Permissions PDF','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=photo-permissions-pdf&amp;section=people','photo-permissions-pdf')
                ),
        'everyone-visible'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'title'=>esc_html( __('Everyone visible','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=everyone-visible&amp;section=people','everyone-visible')
                ),
        'delete-all'=>array(
                    'module'=>'People',
                    'parent'=>'people',
                    'level'=>'Directory',
                    'section'=>'people',
                    'confirm'=>TRUE,
                    'title'=>esc_html( __('Delete all','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=delete-all&amp;section=people','delete-all')
                ),
        
       
        
       
        'email-list'=>array(
                    'module'=>'Comms',
                    'parent'=>'comms',
                    'level'=>'Bulk_Email',
                    'section'=>'comms',
                    'title'=>esc_html( __('Sent Emails','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=email-list&amp;section=comms','email-list')
                ),
        'send-email'=>array(
                    'module'=>'Comms',
                    'parent'=>'comms',
                    'level'=>'Bulk_Email',
                    'section'=>'comms',
                    'title'=>esc_html( __('Send email','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=send-email&amp;section=comms','send-email')
                ),
  
        'email-settings'=>array(
                    'module'=>'Comms',
                    'parent'=>'comms',
                    'level'=>'Bulk_Email',
                    'section'=>'comms',
                    'title'=>esc_html( __('Email settings','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=email-settings&amp;section=comms','email-settings')
                ),
        
      
        'calendar'=>array(
                'licence_level'=>array('standard','basic','premium'),
                    'module'=>'Calendar',
                    'parent'=>'calendar',
                    'level'=>'Calendar',
                    'section'=>'calender',
                    'title'=>esc_html( __('Calendar','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=calendar&amp;section=calendar','calendar'),
                    "dashicon"=>'dashicons-calendar-alt',
                    "font-awesome"=>'<span class="ca-dashicons dashicons dashicons-calendar-alt ca-dashicons"></span>',
                    "background"=>"ca-lime",
                    "callback"=>'church_admin_calendar_callback'
                ),
                'calendar-list'=>array(
                    'module'=>'Calendar',
                    'parent'=>'calendar',
                    'level'=>'Calendar',
                    'section'=>'calender',
                    'title'=>esc_html( __('Calendar List','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=calendar-list&amp;section=calendar','calendar-list')
                ),        
            'add-calendar'=>array(
                    'module'=>'Calendar',
                    'parent'=>'calendar',
                    'level'=>'Calendar',
                    'section'=>'calender',
                    'title'=>esc_html( __('Add to calendar','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=add-calendar&amp;section=calendar','edit-calendar')
                ),
                'delete-calendar'=>array(
                    'module'=>'Calendar',
                    'parent'=>'calendar',
                    'level'=>'Calendar',
                    'section'=>'calender',
                    'title'=>esc_html( __('Delete all calendar events','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=delete-calendar&amp;section=calendar','delete-calendar')
                ),
        'view-categories'=>array(
                    'module'=>'Calendar',
                    'parent'=>'calendar',
                    'level'=>'Calendar',
                    'section'=>'calender',
                    'title'=>esc_html( __('View categories','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=categories&amp;section=calendar','view-categories')
                ),
        'edit-category'=>array(
                    'module'=>'Calendar',
                    'parent'=>'calendar',
                    'level'=>'Calendar',
                    'section'=>'calender',
                    'title'=>esc_html( __('Edit category','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=edit-category&amp;section=calendar','edit-category')
                ),
            'import-ics'=>array(
                'module'=>'Calendar',
                'parent'=>'calendar',
                'level'=>'Calendar',
                'section'=>'calender',
                'title'=>esc_html( __('Import ICS calendar','church-admin' ) ),
                'link'=>wp_nonce_url($church_admin_url.'&action=import-ics&amp;section=calendar','import-ics')
            ),
            'export-ics'=>array(
                'module'=>'Calendar',
                'parent'=>'calendar',
                'level'=>'Calendar',
                'section'=>'calender',
                'title'=>esc_html( __('Export ICS calendar','church-admin' ) ),
                'link'=>wp_nonce_url($church_admin_url.'&action=export-ics&amp;section=calendar','export-ics')
            ),
       
        'media'=>array(
            
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Media','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=media&amp;section=media','media'),
                    "dashicon"=>'dashicons-controls-volumeon',
                    "font-awesome"=>'<span class="ca-dashicons dashicons dashicons-media-audio ca-dashicons"></span>',
                    "background"=>"ca-blue",
                    "callback"=>'church_admin_media_callback'
                ),
        'upload-mp3'=>array(
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Upload/Add media','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=upload-mp3&amp;section=media','upload-mp3')
                ),
        'check-media-files'=>array(
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Add uploaded file','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=check-media-files&amp;section=media','check-media-files')
                ),
        'sermon-series'=>array(
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Sermon series','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=sermon-series&amp;section=media','sermon-series')
                ),
        'set-sermon-page'=>array(
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Set sermon page','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=set-sermon-page&amp;section=media','set-sermon-page')
                ),        
        'edit-sermon-series'=>array(
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Edit series','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=edit-sermon-series&amp;section=media','edit-sermon-series')
                ),
        'podcast-settings'=>array(
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Podcast settings','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=podcast-settings&amp;section=media','podcast-settings')
                ),
        'refresh-podcast'=>array(
                    'module'=>'Media',
                    'parent'=>'media',
                    'level'=>'Sermons',
                    'section'=>'media',
                    'title'=>esc_html( __('Refresh podcast','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=refresh-podcast&amp;section=media','refresh-podcast')
                ),
       
        
        'settings'=>array(
            
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Settings','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=settings&amp;section=settings','settings'),
                    "dashicon"=>'dashicons-admin-generic',
                    "font-awesome"=>'<span class="ca-dashicons dashicons dashicons-admin-settings ca-dashicons"></span>',
                    "background"=>"ca-gray",
                    "callback"=>'church_admin_settings_callback'
                ),

                'email-settings'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Bulk_Email',
                    'section'=>'comms',
                    'title'=>esc_html( __('Email settings','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=email-settings&amp;section=comms','email-settings')
                ),
                'email-templates'=>array(
                    'module'=>'Settings',
                    'parent'=>'Settings',
                    'level'=>'Bulk_Email',
                    'section'=>'comms',
                    'title'=>esc_html( __('Email Templates','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=email-templates&amp;section=comms','email-templates')
                ),
        'restrict-access'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Restrict access','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=restrict-access&amp;section=settings','restrict-access')
                ),
        'people-types'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('People types','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=people-types&amp;section=settings','people-types')
                ),
        'marital-status'=>array(
                     'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Marital status','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=marital-status&amp;section=settings','marital-status')
                ),
        'debug-log'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Debug Log','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=debug-log&amp;section=settings','debug-log')
                ),
        'installation-errors'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Installation errors','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=installation-errors&amp;section=settings','installation-errors')
                ),
                'email-templates'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Email templates','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=email-templates&amp;section=comms','email-templates')
                ),
        'permissions'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Permissions','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=permissions&amp;section=settings', 'permissions')
                ),
        
        'roles'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Roles','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=roles&amp;section=settings','roles')
                ),
        'replicate-roles'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Replicate roles','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=replicate-roles&amp;section=settings','replicate-roles')
                ),
        'reset-version'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Reset version','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=reset-version&amp;section=settings','reset-version')
                ),
        'shortcode-generator'=>array(
                    'module'=>'Settings',
                    'parent'=>'settings',
                    'level'=>'Directory',
                    'section'=>'settings',
                    'title'=>esc_html( __('Shortcode generator','church-admin' ) ),
                    'link'=>wp_nonce_url($church_admin_url.'&action=shortcode-generator&amp;section=settings','shortcode-generator')
                )
        
    );
    
    
}
/* initialise plugin */

//add_action( 'plugins_loaded', 'church_admin_initialise' );
add_action( 'init', 'church_admin_initialise' );
function church_admin_initialise() {
	global $church_admin_url,$level,$wpdb,$current_user,$church_admin_prayer_request_success,$member_types,$ministries;
    church_admin_constants();//setup constants first
	if(defined('CA_DEBUG') )$wpdb->show_errors();
	define('CA_PATH',plugin_dir_path( __FILE__) );
 
    //user email settings
    if(!empty( $_GET['action'] ) && $_GET['action']=='user-email-settings')
    {
        //church_admin_debug('User email settings');
        require_once( plugin_dir_path( __FILE__ ).'includes/user-profile.php');
        church_admin_profile();
        exit();
    }
    /****************************************************************************
     * When licence payment made, this site_url/licence-change=reset is visited
     ***************************************************************************/
    if(!empty($_GET['licence-change'])&&$_GET['licence-change']=='reset'){
        church_admin_debug('***** LICENCE CHANGE ********');
        echo'<!doctype html><html><head><title>Licence change check</title><script src="'.site_url().'/wp-includes/js/jquery/jquery.min.js" id="jquery-core-js"></script><style>body { text-align: center; padding:150px; }h1 { font-size: 50px; }body { font: 20px Helvetica, sans-serif; color: #333; }article { display: block; text-align:center; width: 650px; margin: 0 auto; }a { color: #dc8100; text-decoration: none; }a:hover { color: #333; text-decoration: none; }</style></head><body><article><p style="text-align:center"><img src="'.plugins_url('/', __FILE__ ) .'images/church-admin-logo.png"></p><h2>Licence change check</h2><p>';
        echo'<p>Site url:'.esc_url(site_url()).'</p>';
        $licence = get_option('church_admin_new_licence');
        if(!empty($licence)){
            echo'<h3>'.esc_html($licence).'</h3>';
            if($licence=='premium'){
                echo'<p><a href="'.admin_url().'/admin.php?page=church_admin%2Findex.php">Please head to the admin area and updatee to the new Premium Version</a></p>';
            }
        }
        else{
            echo'<h3>No licence found</h3><p>Please contact <a href="mailto:support@churchadminplugin.com&subject=Licence+issue+'.site_url().'">support@churchadminplugin.com</a> if you think that is wrong.</p><p>Subscribe now...</p>';
            echo '<p class="church-admin-module-select">Choose upgrade subscription, prices will convert to your currency<select class="ca_upgrade_select">
<option>Choose...</option><option value="https://buy.stripe.com/9AQ7ud5obdYZ836cNP">Monthly Subscription $9.99</option><option value="https://buy.stripe.com/28o6q9dUH3kl8365lp">Quarterly subscription $29.07 (save 3% a quarter)</option><option value="https://buy.stripe.com/28o8yhdUH6wx97a29e">Annual subscription $99.99 (equivalent to 2 months free per year)</option><option value="https://buy.stripe.com/bIY4i1dUH6wxdnqg05">Lifetime licence, one off payment $175</option><option value="https://buy.stripe.com/bIYbKt2bZ7AB6Z24ho">Special price for small congregations under 50 people, one off payment $30</option></select><script>
        jQuery(document).ready(function($){ 
        $(function(){
        
        $(".ca_upgrade_select").on("change", function () {
                var url =  $(this).val(); // get selected value
                console.log(url);
                if (url) { // require a URL
                    window.location = url; // redirect
                }
                return false;
            });
        });
    });
    </script>';
            
            }
        echo'<p>Church Admin Free version: '.esc_html(CHURCH_ADMIN_VERSION).'</p>';
        
       
        echo'<p><a href="'.site_url().'">Back to main site</a></p>';
        echo'<p><a href="'.admin_url().'/admin.php?page=church_admin%2Findex.php">Back to admin area</a></p>';
        echo'</article></body></html>';
        church_admin_debug('***** END LICENCE CHANGE ********');
        exit();
    }
    
	wp_get_current_user();
	

    //front end actions
    /**************************************
     * handle unsubscribe link from email
     * ************************************/
	if(!empty( $_GET['ca_unsub'] ) )
	{
        //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        
        $unsub = !empty($_GET['ca_unsub'])?sanitize_text_field(stripslashes($_GET['ca_unsub'])):null;
        //validate
        $validMD5 = FALSE;
        if(!empty($unsub)){
            $validMD5 = preg_match('/^[a-f0-9]{32}$/', $unsub);
        }
        
        if(!empty($validMD5)){
            $details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE md5(people_id)="'.esc_sql( $unsub ).'"');
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET email_send=0 WHERE md5(people_id)="'.esc_sql( $unsub ).'"');
			require_once( plugin_dir_path( __FILE__ ).'includes/unsubscribe.php');
			exit();
        }
	}
    /***************************
     * handle re-subscribe
     ***************************/
	if(!empty( $_GET['ca_sub'] ) )
	{
        //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        
        $resub = !empty($_GET['ca_unsub'])?sanitize_text_field(stripslashes($_GET['ca_sub'])):null;
        //validate
        $validMD5 = FALSE;
        if(!empty($resub)){
            $validMD5 = preg_match('/^[a-f0-9]{32}$/', $resub);
        }
        
        if(!empty($validMD5)){
		    $details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE md5(people_id)="'.esc_sql( $resub ).'"');
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET email_send=1 WHERE md5(people_id)="'.esc_sql( $resub ).'"');
			require_once( plugin_dir_path( __FILE__ ).'includes/resubscribe.php');
			exit();
        }
	}

        /**************************************
     * handle confirm email 
     *************************************/
    if(!empty( $_GET['confirm_email'] )&&!empty( $_GET['people_id'] ) )
	{
        church_admin_debug("**********************\r\n CONFIRM EMAIL");
        //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        $md5email = !empty($_GET['confirm_email'])? sanitize_text_field(stripslashes($_GET['confirm_email'])):null;
        $md5peopleID = !empty($_GET['people_id'])? sanitize_text_field(stripslashes($_GET['people_id'])) :null;
        //validate
        $validated = FALSE;
        if(!empty($md5email)){$validMD5email = preg_match('/^[a-f0-9]{32}$/', $md5email);}
        if(!empty($md5peopleID)){$validMD5peopleID = preg_match('/^[a-f0-9]{32}$/', $md5peopleID);}
        if(!empty($validMD5email ) && !empty($validMD5peopleID)){$validated = TRUE;}

        if(!empty($validated))
        {
            $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE md5(email)="'.esc_sql($md5email).'" AND md5(people_id)="'.esc_sql($md5peopleID ).'"');
            //church_admin_debug($wpdb->last_query);
            //church_admin_debug(print_r( $person,TRUE) );
            
            if(!empty( $person) )
            {
                $household_id = (int)$person->household_id;
                $bulk_email_method = get_option('church_admin_bulk_email_method');
                   
                //update GDPR reason
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET gdpr_reason="'.esc_sql(__('User confirmed from confirmation email','church-admin')).'" WHERE people_id="'.(int)$person->people_id.'"');
                if(defined('CA_DEBUG') )  {
                    //church_admin_debug $wpdb->last_query);
                }
                $adminApproval=get_option('church_admin_admin_approval_required');
                if ( empty( $adminApproval) )
                {
                    //no admin approval required!
                    require_once( plugin_dir_path( __FILE__ ).'includes/directory.php');
                    if ( empty( $person->user_id) )church_admin_create_user( $person->people_id,$person->household_id,null,null);
                    $CAUSER=TRUE;
                }else
                {
                  
                    $admin_message = get_option('church_admin_new_entry_admin_email');
                    $admin_message = str_replace('[HOUSEHOLD_ID]','[HOUSEHOLD_ID]&token=[NONCE]',$admin_message);
                    $admin_message=str_replace('[HOUSEHOLD_ID]',(int)$household_id,$admin_message);
                    $household_details= church_admin_household_details_table($person->household_id);
                    $admin_message=str_replace('[HOUSEHOLD_DETAILS]',$household_details,$admin_message);
                    
                    church_admin_debug($admin_message);
                    //translators: email subject where %1$s is the URL of the website
                    church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(sprintf(__('New household registration on %1$s','church-admin' ),site_url())),$admin_message,null,null,null,null,null,TRUE);
                   
                }
                require_once( plugin_dir_path( __FILE__ ).'includes/confirmed.php');
                exit();
            }
            else{
                echo'<p>Person not found</p>';
            }
        }
        exit();
    }

    //church admin app initialisation

	if(!empty( $_GET['ca-app'] ) )
	{
		require_once( plugin_dir_path( __FILE__ ).'app/app-admin.php');
		switch( $_GET['ca-app'] )
		{
			case'latest_media': 
                header("Content-Type: application/json"); 
                echo church_admin_json_latest_media();
                exit();
            break;

		}
	}
    //reset version
	if(!empty( $_GET['page'] )&&( $_GET['page']=='church_admin/index.php')&&!empty( $_GET['action'] )&& $_GET['action']=='reset-version')
	{
		//check_admin_referer('reset-version');
        update_option('church_admin_licence_checked',1);
        //delete_option('church_admin_trial');
		update_option("church_admin_version",0);
        require_once(plugin_dir_path( __FILE__) .'/includes/install.php');
        church_admin_install();
		$url=admin_url().'admin.php?page=church_admin%2Findex.php&message=Church+Admin+Version+Reset';
		wp_redirect( $url );
		exit;
	}
	//reset version
	define('OLD_CHURCH_ADMIN_VERSION',get_option('church_admin_version') );
	if(version_compare(OLD_CHURCH_ADMIN_VERSION,CHURCH_ADMIN_VERSION)<0)
	{
		//church_admin_debug('Firing install');
      
		require_once(plugin_dir_path( __FILE__) .'/includes/install.php');
		church_admin_install();
	}



    /***********************************************
     * only proceed if in church admin menu land
     ***********************************************/
    if(!is_admin() || empty($_GET['page']) || $_GET['page']!='church_admin/index.php'){return;}
	//Version Number
   
    


    /******************************************
     * initialise variables
     ******************************************/

  
    $member_types=church_admin_member_types_array();



	if(!empty( $_GET['ca_refresh'] ) )
	{
		delete_option('church-admin-directory-output');
		
	}
	
    /************************************
     * handle move household to site
     ************************************/
	if(!empty( $_POST['move_site_id'] ) )
	{
        //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        $move_site_id = !empty($_POST['move_site_id'])?(int)sanitize_text_field(stripslashes($_POST['move_site_id']) ) :null;
        $site_id = !empty($_POST['site_id'])?(int)sanitize_text_field(stripslashes($_POST['site_id'] ) ):null;
        //validate
        $validated = TRUE;
        if(empty($move_site_id) || !church_admin_int_check($site_id) ||  empty($sites[$move_site_id])){$validated = FALSE;}
        if(empty($site_id) || !church_admin_int_check($site_id) || empty($sites[$site_id])){$validated = FALSE;}
		
        if(!empty($validated) )
		{
			 $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET site_id="'.(int)$site_id.'" WHERE household_id="'.(int)$move_site_id.'"');

		}
	}
    /*****************************
     * handle unit save
     *****************************/
    if(!empty( $_POST['save-unit'] ) )
    {
        //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        $unit_id     = !empty( $_REQUEST['unit_id'] )? sanitize_text_field(stripslashes($_REQUEST['unit_id'])) :null;
        $name        = !empty($_POST['unit_name']) ? sanitize_text_field( stripslashes($_POST['unit_name']) ) :null;
        $description = !empty($_POST['unit_description']) ? sanitize_textarea_field(stripslashes( $_POST['unit_description']) ) :null;
        
        //validate
        $validated = TRUE;
        if(!empty($unit_id)){
            //check it exists
            if(empty($units[$unit_id])){
                $unit_id = FALSE; //non existent unit_id, so needs to be an insery
            }
        }
        if(empty($name)){$validated = FALSE;}
        if(empty($description)){$validated = FALSE;}
        if(!empty($validated)){
            if ( empty( $unit_id) )  {
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_units (name,description)VALUES("'.esc_sql($name).'","'.esc_sql($description).'")');
            }
            else{
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_units SET name="'.esc_sql($name).'",description="'.esc_sql($description).'" WHERE unit_id="'.(int)$unit_id.'"');
            }
        }
    }
    if(!empty( $_POST['save-subunit'] ) )
    {
         //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        $unit_id     = !empty( $_REQUEST['unit_id'] )? sanitize_text_field(stripslashes($_REQUEST['unit_id'] )) :null;
        $subunit_id     = !empty( $_REQUEST['subunit_id'] )? sanitize_text_field(stripslashes($_REQUEST['subunit_id'])) :null;
        $name        = !empty($_POST['unit_name']) ? sanitize_text_field(stripslashes( $_POST['unit_name']) ) :null;
        $description = !empty($_POST['unit_description']) ? sanitize_textarea_field( stripslashes($_POST['unit_description']) ) :null;
        $people = !empty($_POST['people']) ? sanitize_text_field(stripslashes( $_POST['people'] )) :null;

        //validate
        $validated = TRUE;
        if(!empty($unit_id)){
             //check it exists
             if(empty($units[$unit_id])){
                 $unit_id = FALSE; //non existent unit_id, so needs to be an insery
             }
        }
        if(!empty($subunit_id)||!church_admin_int_check($subunit_id)){$validated = FALSE;}
        if(empty($name)){$validated = FALSE;}
        if(empty($description)){$validated = FALSE;}
        
        if(!empty($validated))
        {

            if ( empty( $subunit_id) )$subunit_id=$wpdb->get_var('SELECT subunit_id FROM '.$wpdb->prefix.'church_admin_unit_meta WHERE name="'.esc_sql($name).'" AND description="'.esc_sql($description).'"');
        
        
        
            if ( empty( $subunit_id) ){
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_unit_meta (name,description,unit_id,active)VALUES("'.esc_sql($name).'","'.esc_sql($description).'","'.(int)$unit_id.'",1)');
                $subunit_id=(int)$wpdb->insert_id;
                //church_admin_debug $wpdb->last_query);
            }
            else{
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_unit_meta SET name="'.esc_sql($name).'",description="'.esc_sql($description).'" WHERE subunit_id="'.(int)$subunit_id.'"');
            }
            //handle people
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="unit" AND ID="'.(int)$subunit_id.'"'); 
            $autocompleted= maybe_unserialize(church_admin_get_people_id(trim($people )) );
            foreach( $autocompleted AS $x=>$name)
            {
                $p_id=church_admin_get_one_id(trim( $name) );//get the people_id
                if(!empty( $p_id) )
                {
                    church_admin_update_people_meta( $subunit_id,$p_id,'unit');//update person 
                }
            }
        }
       
    }

    //handle unconfirm GDPR
	if(!empty( $_GET['action'] )&&$_GET['action']=='ca_unconfirm_GDPR')
	{
		if(!is_user_logged_in() )return;
		if(current_user_can('manage_options') )
		{
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET gdpr_reason=NULL');
			
		}	
	}	
	
	

	if(!empty( $_POST['ind_att_csv'] ) )  {
        require_once( plugin_dir_path( __FILE__ ).'includes/individual_attendance.php');
        $out = church_admin_output_ind_att_csv();
        echo $out;
        exit();
    }
	//load_plugin_textdomain( 'church-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    load_plugin_textdomain( 'church-admin');
    $level=get_option('church_admin_levels');
    if(empty($level))
    {
        if ( empty( $level['Units'] ) )$level['Units']='administrator';
        if ( empty( $level['Ministries'] ) )$level['Ministries']='administrator';
        if ( empty( $level['Giving'] ) )$level['Giving']='administrator';
        if ( empty( $level['Directory'] ) )$level['Directory']='administrator';
        if ( empty( $level['Kidswork'] ) )$level['Kidswork']='administrator';
        if ( empty( $level['Small Groups'] ) )$level['Small Groups']='administrator';
        
        if ( empty( $level['Funnel'] ) ) $level['Funnel']='administrator';
        if ( empty( $level['Bulk Email'] ) )$level['Bulk Email']='administrator';
        if ( empty( $level['Sermons'] ) )$level['Sermons']='administrator';
        if ( empty( $level['Bulk SMS'] ) )$level['Bulk SMS']='administrator';
        if ( empty( $level['Calendar'] ) )$level['Calendar']='administrator';
        if ( empty( $level['Attendance'] ) )$level['Attendance']='administrator';
        if ( empty( $level['Member Type'] ) )$level['Member Type']='administrator';
        if ( empty( $level['Service'] ) )$level['Service']='administrator';
        if ( empty( $level['Sessions'] ) )$level['Sessions']='administrator';
        if(empty($level['Pastoral']))$level['Pastoral']='administrator';
        if ( empty( $level['Sessions'] ) )$level['Sessions']='administrator';
        if ( empty( $level['App'] ) )$level['App']='administrator';
        if ( empty( $level['Prayer Requests'] ) )$level['Prayer Requests']='administrator';
        if ( empty( $level['Events'] ) )$level['Events']='administrator';
        if ( empty( $level['Ministries'] ) )$level['Ministries']='administrator';
        if ( empty( $level['Classes'] ) )$level['Classes']='administrator';
        if ( empty( $level['Contact form'] ) )$level['Contact form']='administrator';
        update_option('church_admin_levels',$level);
    }
    if(!empty( $_POST['one_site'] ) ){
        //update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        $site_id=!empty($_POST['site_id'])?sanitize_text_field( stripslashes( $_POST['site_id'] ) ) :null;
        //validate
        $sites = church_admin_sites_array();
        if(!empty($sites[$site_id]))
        {
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET site_id="'.(int)$site_id.'"');
        }
    }



   
    //save the church admin note before any display happens

	if(!empty( $_POST['save-ca-comment'] ) )
 	{
 		//church_admin_debug('******************************'."\r\n Save Comment ".date('Y-m-d H:i:s')."\r\n");
 		//update sanitize, validate,escape v 3.7.25 2023-05-08
        //sanitize
        $parent_id = !empty( $_POST['parent_id'] )?sanitize_text_field( $_POST['parent_id'] ) : null;
        $comment_id = !empty( $_POST['comment_id'] )?sanitize_text_field( $_POST['comment_id'] ) : null;
        $comment = !empty($_POST['comment'])?sanitize_textarea_field($_POST['comment']):null;
        $comment_type = !empty( $_POST['comment_type'] )?sanitize_text_field( $_POST['comment_type'] ) : null;
        $ID = !empty( $_POST['ID'] )?sanitize_text_field( $_POST['ID'] ) : null;

 		foreach( $_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(sanitize_text_field( $value) );
 		if(!empty( $_POST['comment_id'] ) )
 		{
 			$sql='UPDATE '.$wpdb->prefix.'church_admin_comments SET comment="'.esc_sql($comment).'",comment_type="'.esc_sql($comment_type).'",parent_id="'.(int)$parent_id.'",author_id="'.(int)$current_user->ID.'",timestamp="'.date('Y-m-d h:i:s').'" comment_id="'.(int)$comment_id.'"';
 		}
 		else
 		{

 			$sql='INSERT INTO '.$wpdb->prefix.'church_admin_comments (comment,comment_type,parent_id,author_id,timestamp,ID)VALUES("'.esc_sql($comment).'","'.esc_sql($comment_type).'","'.(int)$parent_id.'","'.(int)$current_user->ID.'","'.date('Y-m-d h:i:s').'","'.(int)$ID.'")';
 		}
 		//church_admin_debug('******************************'."\r\n $sql \r\n");
 		$wpdb->query( $sql);
 		if ( empty( $comment_id ) )$comment_id=$wpdb->insert_id;

 		$comment=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_comments WHERE comment_id="'.(int)$comment_id.'"');

 	}

}



if(function_exists('register_block_type') ){
    require_once( plugin_dir_path( __FILE__ ) .'gutenberg/php-blocks.php');
}
add_action( 'delete_user', 'church_admin_delete_user' );//make sure user account disconnected from directory


function church_admin_delete_user( $user_id)
{
	global $wpdb;
    //sanitize 
    $sanitizedUserID = (!empty($user_id) && church_admin_int_check($user_id) ) ? (int)$user_id: null;
    //validate - check actual user with that ID
    if(!empty($sanitizedUserID)){
        $user = get_user_by('ID',$sanitizedUserID);
    }
    if(!empty($user)){
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="NULL" WHERE user_id="'.(int)$user->ID.'"');
    }
	
}

add_action('activated_plugin','church_admin_save_error');
function church_admin_save_error()  {
    update_option('church_admin_plugin_error',  ob_get_contents() );
}
add_action('load-church-admin', 'church_admin_add_screen_meta_boxes');




 /**
     *
     * Sets up constants for plugin
     *
     * @author  Andy Moyle
     * @param    null
     * @return
     * @version  0.1
     *
     */
function church_admin_constants()
{
   
   //define DB
    define('OLD_CHURCH_ADMIN_EMAIL_CACHE',WP_PLUGIN_DIR.'/church-admin-cache/');
    define('OLD_CHURCH_ADMIN_EMAIL_CACHE_URL',WP_PLUGIN_URL.'/church-admin-cache/');

    church_admin_create_directories();

}//end constants


 /**
 *
 * Add new household to admin toolbar
 *
 * @author  Andy Moyle
 * @param    null
 * @return   Array, key is order
 * @version  0.1
 *
 */
function church_admin_menu_item ( $wp_admin_bar) {

    $args = array (
            'id'        => 'household',
            'title'     =>esc_html( __('Household','church-admin' ) ),
            'href'      => wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;section=people&action=add-household','add-household'),
            'parent'    => 'new-content'
    );

  if(church_admin_level_check('Directory') )  $wp_admin_bar->add_node( $args );

  $args = array (
    'id'        => 'quickhousehold',
    'title'     =>esc_html( __('Quick Household','church-admin' ) ),
    'href'      => wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;section=people&action=quick-household','quick-household'),
    'parent'    => 'new-content'
);

if(church_admin_level_check('Directory') )  $wp_admin_bar->add_node( $args );


  $args = array (
    'id'        => 'sermon',
    'title'     =>esc_html( __('Sermon','church-admin' ) ),
    'href'      => wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=upload-mp3','upload-mp3'),
    'parent'    => 'new-content'
);
if(church_admin_level_check('Media') )  $wp_admin_bar->add_node( $args );






}

add_action('admin_bar_menu', 'church_admin_menu_item',71);




/******************************************************************************************************************************
*
* For prayer request, if made private in settings we want to show the login form at the template_redirect hook 
*
******************************************************************************************************************************/
function church_admin_private_prayer_template( $archive_template ) {
    global $post;
    $private=get_option('church-admin-private-prayer-requests');
    //church_admin_debugget_post_type());
    if ( (is_post_type_archive ( 'prayer-requests' ) ||   'prayer-requests'==get_post_type()) && !is_user_logged_in() && !empty($private) ) {
        //church_admin_debug('Prayer request not logged in');
        $located = locate_template( 'private-prayer.php' );
        //church_admin_debug($located);
        if(!empty($located)){
            
            $archive_template = $located;
        }
        else
        {
            $archive_template = dirname( __FILE__ ) . '/display/private-prayer.php';
        }
    } 
    return $archive_template;
}
add_filter( 'archive_template', 'church_admin_private_prayer_template' ) ;
add_filter( 'single_template', 'church_admin_private_prayer_template' ) ;


/******************************************************************************************************************************
*
* Show a submit prayer requests form at the top of the archive
*
******************************************************************************************************************************/
$theme = wp_get_theme(); // gets the current theme

if ( 'Avada' == $theme->name || 'Avada' == $theme->parent_theme ) {
    add_action('avada_before_main_container', 'church_admin_draft_prayer_request');
	define('CA_PRY_STYLE','style="min-width:80vw;"');
}
elseif ( 'The7' == $theme->name || 'The7' == $theme->parent_theme ) {
    add_action('presscore_before_loop', 'church_admin_draft_prayer_request');
}
elseif( 'Omega' == $theme->name || 'Omega' == $theme->parent_theme )  {
    
    add_action('omega_before_content', 'church_admin_draft_prayer_request');
}
elseif(has_action('church_admin_theme_before_loop') )
{
    add_action('church_admin_theme_before_loop', 'church_admin_draft_prayer_request');
}
elseif(has_action('fusion_blog_shortcode_before_loop') )
{
    add_action('fusion_blog_shortcode_before_loop', 'church_admin_draft_prayer_request');
}
else{
add_action('loop_start', 'church_admin_draft_prayer_request');
}

function church_admin_draft_prayer_request( $content)
{
    global $wpdb,$church_admin_prayer_request_success;
    
		if(is_post_type_archive('prayer-requests')&& is_archive() )
        {
			$private=get_option('church-admin-private-prayer-requests');
			//only show form if not private or logged in
			if (!$private ||(is_user_logged_in() && $private) )
			{
				$out='';

                if ( empty( $_POST['save_prayer_request'] )&&empty( $_POST['non_spammer'] )||!wp_verify_nonce( $_POST['non_spammer'],'prayer-request') )
                {
                        $out.='<div class="church-admin-prayer-request alignwide" ';
                        if(defined('CA_PRY_STYLE') ) $out.= CA_PRY_STYLE;
                        $out.='><h3>'.esc_html( __('Submit a prayer request','church-admin' ) ).'</h3>';
                        $message=get_option('church_admin_prayer_request_message');
                        if(!empty( $message) )$out.='<p>'. wp_kses_post( $message).'</p>';
                        $out.='<form action="" method="POST">';
                    
                        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Title','church-admin' ) ).'</label><input type="text" name="request_title" class="church-admin-form-control"></div>';
                        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Prayer request','church-admin' ) ).'</label><textarea name="request_content" class="church-admin-form-control" style="height:100px"></textarea></div>';
                        $out.='<div id="spam-proof">&nbsp;</div>';
                        $out.='<div class="church-admin-form-group"><input type="hidden" value="TRUE" name="save_prayer_request" /><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></div>';

                        $out.='</form></div>';
                        $nonce=wp_create_nonce('prayer-request');
                        $out.='<script>jQuery(document).ready(function( $) {var content="<div class=\"form-check\"><label>'.esc_html( __('Check box if not a spammer','church-admin' ) ).'<input type=\"checkbox\" name=\"non_spammer\" value=\"'.esc_attr($nonce).'\" /></label></div>"; $("#spam-proof").html(content);});</script>';
                }
                else{
                    $out=$church_admin_prayer_request_success;
                }
                echo $out;
            }
		}

}
add_action('loop_start', 'church_admin_draft_act_of_courage');

function church_admin_draft_act_of_courage( $content)
{
    global $wpdb,$church_admin_acts_success;

		if(is_post_type_archive('acts-of-courage') )
    {
			$private=get_option('church-admin-private-acts-of-courage');
			//only show form if not private or logged in
			if (!$private ||(is_user_logged_in() && $private) )
			{
				$out='';

      	if ( empty( $_POST['save_acts_request'] )&&empty( $_POST['non_spammer'] )||!wp_verify_nonce( $_POST['non_spammer'],'acts-of-courage') )
      	{
					$out.='<h3>'.esc_html( __('Submit an act of courage','church-admin' ) ).'</h3>';
					$message=get_option('church-admin-acts-of-courage-message');
					if(!empty( $message) )$out.='<p>'. wp_kses_post( $message).'</p>';
        	$out.='<form action="" method="POST">';
        	$out.='<table class="form-table"><tbody>';
        	$out.='<tr><th scope="row">'.esc_html( __('Title','church-admin' ) ).'</th><td><input type="text" name="request_title"></td></tr>';
        	$out.='<tr><th scope="row">'.esc_html( __('Your act of courage','church-admin' ) ).'</th><td><textarea name="request_content"></textarea></td></tr>';
					$out.='<tr id="spam-proof">&nbsp;</td></tr>';
					$out.='<tr><td cellspacing=2><input type="hidden" value="TRUE" name="save_act_of_courage_request" /><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr></table>';

					$out.='</form>';
					$nonce=wp_create_nonce('acts-of-courage');
					$out.='<script>jQuery(document).ready(function( $) {var content="<th scope=\"row\">'.esc_html( __('Check box if not a spammer','church-admin' ) ).'</th><td><input type=\"checkbox\" name=\"non_spammer\" value=\"'.esc_attr($nonce).'\" /></td></tr>"; $("#spam-proof").html(content);});</script>';
				}
				else{
                    $out = $church_admin_acts_success;
                }
      	echo $out;
			}
		}

}
/****************************************************************************
*
*	From 1.2800 register front end scripts early then enqueue on shortcode process
*
*****************************************************************************/
add_action( 'wp_enqueue_scripts', 'church_admin_register_frontend_scripts' );
add_action( 'admin_enqueue_scripts', 'church_admin_register_frontend_scripts' );
function church_admin_register_frontend_scripts() {
    global $post;
    

    
    wp_register_script('ca-draganddrop', plugins_url( '/', __FILE__ ) . 'includes/draganddrop.js', array( 'jquery' ) , filemtime(plugin_dir_path(__FILE__ ).'includes/draganddrop.js'),TRUE);
    wp_register_script('church-admin-form-case-enforcer',plugins_url( '/', __FILE__ ) . 'includes/jQuery.caseEnforcer.min.js',array( 'jquery' ),FALSE, TRUE);
    
   
	wp_register_script('church-admin-calendar-script',plugins_url( '/', __FILE__ ) . 'includes/calendar.js',array( 'jquery' ),FALSE, TRUE);
	wp_register_script('church-admin-calendar',plugins_url( '/', __FILE__ ) . 'includes/jQueryCalendar.js',array( 'jquery' ),FALSE, TRUE);
	
    /****************
     * Podcast
     ***************/
    wp_register_script('ca_podcast_audio_use',plugins_url('/', __FILE__ ) . 'includes/audio.use.js' , array( 'jquery' ) ,filemtime(plugin_dir_path(__FILE__ ).'includes/audio.use.js'), TRUE);
	wp_register_script( 'jquery-ui-datepicker','','',NULL );
	wp_register_script('church_admin_form_clone',plugins_url('/', __FILE__ ) . 'includes/jquery-formfields.js', array( 'jquery' ) ,FALSE, TRUE);
	//fix issue caused by some "premium" themes, which call google maps w/o key on every admin page. D'uh!
 	wp_dequeue_script('avia-google-maps-api');
	//now enqueue google map api with the key
	$src = 'https://maps.googleapis.com/maps/api/js';
	$key='?key='.get_option('church_admin_google_api_key').'&callback=Function.prototype';
	wp_register_script( 'church_admin_google_maps_api',$src.$key, array() ,FALSE);
	wp_register_script('church_admin_map', plugins_url('/', __FILE__ ) . 'includes/google_maps.js', array( 'jquery' ) ,filemtime(plugin_dir_path(__FILE__ ).'includes/google_maps.js'),FALSE);
	wp_register_script('church_admin_map_script', plugins_url('/', __FILE__ ) . 'includes/maps.js', array( 'jquery' ) ,filemtime(plugin_dir_path(__FILE__ ).'includes/maps.js'),FALSE);
    wp_register_script('jquery-ui-sortable','','',NULL );
    //google graph needs to be called early and in header, didn't like being registered and then enqueued later
	wp_register_script('church_admin_google_graph_api','https://www.google.com/jsapi', array( 'jquery' ) ,FALSE, FALSE);
	
	
}

add_action('wp_head','church_admin_ajaxurl');
function church_admin_ajaxurl()
{
	$ajax_nonce = wp_create_nonce("church_admin_mp3_play");
	?>
	<script type="text/javascript">
		var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
		var security= '<?php echo $ajax_nonce; ?>';
	</script>
	<?php
}
add_action('wp_enqueue_scripts', 'church_admin_init');
add_action('admin_enqueue_scripts', 'church_admin_init',9999);//adding withlow priority to be last to call google maps api
/**
 *
 * Initialises js scripts and css
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function church_admin_init()
{
	if(!empty( $_COOKIE['churchAdminBlur'] ) )wp_enqueue_style( 'church-admin-blur', plugins_url('includes/blur.css',__FILE__ ) ,'',NULL);
	
    //This function add scripts as needed
    
	//wp_enqueue_script('common','','',NULL);
	//wp_enqueue_script('wp-lists','','',NULL);
	//wp_enqueue_script('postbox','','',NULL);
    wp_register_script('church_admin_google_graph_api','https://www.google.com/jsapi', array( 'jquery' ) ,FALSE, FALSE);
	//wp_enqueue_style( 'dashicons' ); - added to style.new.css for less calls.
   

	if(!empty( $_POST['church_admin_search'] ) )church_admin_editable_script();

    
	if(isset( $_GET['action'] ) )
	{
		switch( $_GET['action'] )
		{
          
            case 'send-email':
                church_admin_autocomplete_script();
                church_admin_date_picker_script();
            break;
            case 'edit-ministry':
            case 'edit-subunit':
            case 'settings':
            case'people':
            case 'edit_ministry':
            case'view_ministry':
            case'church_admin_member_type':
            case 'send-sms':
            case'twilio-replies':
            case 'app-settings':
            case 'edit-custom-field-automation': 
            case 'pastoral-visit-list':
            case 'pastoral-settings':
            
            case 'edit-child-protection-incident':
                church_admin_autocomplete_script();
            break;
            case 'edit-calendar':
            case 'add-calendar':
            case 'podcast-settings':
            case 'edit-child-protection-incident':
           
                church_admin_media_uploader_enqueue();
                church_admin_date_picker_script();
            break;
           
			case'church_admin_add_calendar':
            case 'add-calendar':
           
            case'church_admin_series_event_edit':   
            case'church_admin_single_event_edit':
            case 'show-attendance':
            case'edit-attendance':
            case'church_admin_new_edit_calendar':
            case 'edit-calendar':
            case'edit_kidswork':
            case 'add-attendance':
            case 'weeks-attendance':
            case 'individual-attendance':
            case 'individual-attendance-csv':
            case 'individual_attendance':
            case 'individual-attendance-list':
            case 'edit-individual-attendance':
          
            case 'import-ics':
	
            case'edit-service':
            case'add-event':
            case'edit_safeguarding':
        
            case 'add-three-months':
			case 'edit_event':
            case 'edit_ticket_type':
            case 'bulk-bible-readings':
            case 'series-event-edit':
            case 'single-event-edit': 
            case 'bulk-edit-anniversary':
            case 'bulk-edit-dob':
            case 'bulk-edit-custom':
            case 'edit-pastoral-visit-note':
            case 'schedule-pastoral-visit':
            case 'whole-series-edit':
            case 'future-series-edit':
                church_admin_date_picker_script();
            break;
			case 'bulk_geocode':
			case 'bulk-geocode':	
				church_admin_google_map_api();
				wp_enqueue_script('ca_batch_geocode', plugins_url('/', __FILE__ ) . 'includes/batch_geocode.js', array( 'jquery' ) ,FALSE, TRUE);
			break;
			case 'services':
            case'individual-attendance':
            case'attendance':
                church_admin_date_picker_script();
                church_admin_frontend_graph_script();
            break;
			case'church_admin_cron_email':
				//church_admin_debug('Cron fired:'.date('Y-m-d h:i:s')."/r/n");
				church_admin_bulk_email();exit();
			break;
			case 'remove-queue':check_admin_referer('remove-queue');church_admin_remove_queue();break;
			case'send-email':case'church_admin_send_email':church_admin_email_script();church_admin_autocomplete_script();church_admin_date_picker_script();break;
			case'edit-resend':church_admin_email_script();church_admin_autocomplete_script();church_admin_date_picker_script();break;
			case'resend-new':

                church_admin_email_script();
                church_admin_autocomplete_script();
            break;
			case'resend_email':church_admin_email_script();church_admin_autocomplete_script();break;
			case'church_admin_send_sms':church_admin_email_script();church_admin_autocomplete_script();break;
			case'delete-group':
                church_admin_sg_map_script();
                church_admin_autocomplete_script();
            break;
			case'church_admin_search';church_admin_editable_script();break;
			//calendar
			case'church_admin_add_category':
			case'church_admin_edit_category':
            case 'edit-category':
                church_admin_farbtastic_script();
            break;

            case 'edit_bubble_booking':case 'add_bubble_booking':
                wp_enqueue_script('church-admin-form-case-enforcer',plugins_url( '/', __FILE__ ) . 'includes/jQuery.caseEnforcer.min.js',array( 'jquery' ),FALSE, TRUE);
            break;
            case 'test':
                church_admin_sortable_script();
            break;
            case 'small_groups':
            case 'smallgroups-cleanup':
            case 'groups-cleanup':
                    church_admin_sortable_script();
					church_admin_form_script();
					church_admin_autocomplete_script();
					$key=get_option('church_admin_google_api_key');
					if(!empty( $key) )church_admin_sg_map_script();
			break;
            case 'add-service':
            case 'edit-service':
			    church_admin_form_script();church_admin_date_picker_script();
            break;
            case 'edit-site':
            case 'edit_site':
                church_admin_form_script();
                church_admin_media_uploader_enqueue();
        
                $key=get_option('church_admin_google_api_key');
                if(!empty( $key) )
                {
                    church_admin_map_script();	
                }	
			break;
            
			case 'edit-small-group':
            case 'edit-group':	
            case 'add-group':	
						//church_admin_form_script();
						church_admin_autocomplete_script();
						church_admin_media_uploader_enqueue();
						wp_enqueue_script('ca-draganddrop');
						$key=get_option('church_admin_google_api_key');
						if(!empty( $key) )
						{
							church_admin_map_script();
							church_admin_sg_map_script();	
						}
			break;
			case 'small_groups': 			
						$key=get_option('church_admin_google_api_key');
						if(!empty( $key) )
						{
							church_admin_map_script();
							church_admin_sg_map_script();	
						}
			break;
			case'classes':church_admin_date_picker_script();church_admin_frontend_graph_script();break;
			case'view_class':
                church_admin_date_picker_script();
                church_admin_autocomplete_script();
                church_admin_frontend_graph_script();
            break;
            
			case'edit-class':
            case'edit_class':
             case 'edit-gift':
          
            case 'upload-mp3':
            case 'permissions':
            case'add-media-file':
            case'church_admin_permissions':    
                church_admin_date_picker_script();church_admin_autocomplete_script();
            break;
            case 'app-visits':
                church_admin_date_picker_script();
            break;
			
          case'list';church_admin_editable_script();break;
            
            case 'app':
                case'custom-fields':
            case 'edit-custom-field':
           
                church_admin_sortable_script();break;
			
			//directory
            case 'people-map':
                church_admin_map_script();
            break;
            case 'smallgroups-map':church_admin_sg_map_script();break;
			case'new_household':
            case 'add-household':
            case'church_admin_new_household':
                church_admin_form_script();
                church_admin_map_script();
                
                church_admin_media_uploader_enqueue();
                church_admin_date_picker_script();
            break;
          


			case'edit_household':
			case'view_household':
            case 'display-household':
            case 'display_household':
            case 'upload-mp3':
				church_admin_map_script();
                church_admin_date_picker_script();
                wp_enqueue_script('ca-draganddrop');
    
			break;
			case 'edit_people':
				church_admin_form_script();
				church_admin_date_picker_script();
				church_admin_media_uploader_enqueue();
                church_admin_map_script();
                wp_enqueue_script('ca-draganddrop');
			break;
			case'app':
            case'edit_sermon_series':
            case 'edit-sermon-series':
                church_admin_media_uploader_enqueue();
                wp_enqueue_script('ca-draganddrop');
			break;
			
		
			case'church_admin_update_order': 
                church_admin_update_order( $_GET['which'] );exit();
            break;
			case'get_people':church_admin_ajax_people(TRUE);break;
			case'people':case'edit_funnel':case'delete_funnel':church_admin_sortable_script();break;
            case 'upload-mp3':church_admin_date_picker_script();church_admin_autocomplete_script();break;
      
		}
	}
    elseif(isset( $_GET['page'] )&& $_GET['page']=='church_admin/index.php')
    {
        church_admin_date_picker_script();//needed on main menu page too
    }

}











/* Thumbnails */
add_action( 'after_setup_theme', 'ca_thumbnails' );
function ca_thumbnails()
{
        /**
 *
 * Add thumbnails for plugin use
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
    add_theme_support( 'post-thumbnails' );
    if ( function_exists( 'add_image_size' ) )
    {
        add_image_size('ca-people-thumb',75,75);
        add_image_size('ca-address-thumb',150,150);
        //add_image_size( 'ca-email-thumb', 300, 200 ); //300 pixels wide (and unlimited height)
        //add_image_size('ca-series-thumbnail',480,360);

    }

}
/* Thumbnails */
add_action( 'admin_enqueue_scripts','church_admin_public_css');
add_action('wp_enqueue_scripts','church_admin_public_css');
function church_admin_public_css()  {
   
    wp_enqueue_style('Church-Admin',plugins_url('/', __FILE__ ) . 'includes/style.new.css',NULL,filemtime(plugin_dir_path( __FILE__ ).'includes/style.new.css' ),'all');
}
add_action('admin_head',"church_admin_colorscheme");
function church_admin_colorscheme()
{    
    //add the users selected admin colors
    global $_wp_admin_css_colors; 
    
    $current_color = get_user_option( 'admin_color' );
    
    if ( empty( $current_color) )  {$current_color='fresh';}
    $church_admin_colors = $_wp_admin_css_colors[$current_color];
    $styles='#church-admin-menu{background:'.esc_attr($church_admin_colors->colors[1]).';}'."\r\n";
    $styles.='.church-admin-top-menu.active .church-admin-top-menu-item{background:'.esc_attr($church_admin_colors->colors[2]).'}'."\r\n";
    $styles.='.church-admin-top-menu.inactive .church-admin-submenu{background:'.esc_attr($church_admin_colors->colors[0]).';}'."\r\n";
    $styles.='.church-admin-submenu.active{background:'.esc_attr($church_admin_colors->colors[0]).';}'."\r\n";
    wp_add_inline_style( 'Church-Admin', $styles );

}
add_action('wp_head', 'church_admin_public_header');
function church_admin_public_header()
{
  
    $licence = get_option('church_admin_app_new_licence');
	echo"<!--
 
   ____ _                    _          _       _           _         ____  _             _       
  / ___| |__  _   _ _ __ ___| |__      / \   __| |_ __ ___ (_)_ __   |  _ \| |_   _  __ _(_)_ __  
 | |   | '_ \| | | | '__/ __| '_ \    / _ \ / _` | '_ ` _ \| | '_ \  | |_) | | | | |/ _` | | '_ \ 
 | |___| | | | |_| | | | (__| | | |  / ___ \ (_| | | | | | | | | | | |  __/| | |_| | (_| | | | | |
  \____|_| |_|\__,_|_|  \___|_| |_| /_/   \_\__,_|_| |_| |_|_|_| |_| |_|   |_|\__,_|\__, |_|_| |_|
                                                                                    |___/                   
\r\n";
    echo' FREE  Version: '.CHURCH_ADMIN_VERSION.' -->
        <style>table.church_admin_calendar{width:';
    if(get_option('church_admin_calendar_width') )  {
        echo (int)get_option('church_admin_calendar_width').'px}';
    }else {echo'700px}';}
    echo'</style>';
}

//Build Admin Menus
add_action('admin_menu', 'church_admin_menus');
/**
 *
 * Admin menu
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function church_admin_menus()

{

    global $level;
    
    add_menu_page('church_admin:Administration', esc_html(__('Church Admin','church-admin' ) ),  'read', 'church_admin/index.php', 'church_admin_main',CA_ICON,98);
}

// Admin Bar Customisation
/**
 *
 * Admin Bar Menu
 *
 * @author  Andy Moyle
 * @param    null
 * @return
 * @version  0.1
 *
 */
function church_admin_admin_bar_render() {

 	global $wp_admin_bar;
 	// Add a new top level menu link
 	// Here we add a customer support URL link
	if(current_user_can('publish_posts') )
	{
			$wp_admin_bar->add_menu( array('parent' => false, 
            'id' => 'church_admin', 
            'title' => esc_html(__('Church Admin','church-admin' ) ), 
            'href' => admin_url().'admin.php?page=church_admin/index.php' ) );
			if(church_admin_level_check('Directory') ){
                $wp_admin_bar->add_menu(array ('parent' => 'church_admin',
                    'id'=> 'household1',
                    'title'=>esc_html( __('New Household','church-admin' ) ),
                    'href'=>wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;section=people&action=add-household','add-household') 
                    ) 
                );
                $wp_admin_bar->add_menu(array ('parent' => 'church_admin',
                    'id'=> 'household2',
                    'title'=>esc_html( __('Quick Household','church-admin' ) ),
                    'href'=>wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;section=people&action=quick-household','quick-household') 
                    ) 
                );
            }
            if(church_admin_level_check('Calendar') ){
                $wp_admin_bar->add_menu(array ('parent' => 'church_admin',
                'id'=> 'calendar1',
                'title'=>esc_html( __('New Calendar Item','church-admin' ) ),
                'href'=>wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=edit-calendar','edit-calendar')
                )
            );
            }
            if(church_admin_level_check('Media') ){
                $wp_admin_bar->add_menu(array ('parent' => 'church_admin',
                'id'=> 'sermon1',
                'title'=>esc_html( __('New Sermon','church-admin' ) ),
                'href'=>wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=upload-mp3','upload-mp3')
                )
            );
            }
			if(current_user_can('manage_options') ){
                $wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'church_admin_settings', 'title' => esc_html(__('Settings','church-admin' ) ), 'href' => wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&action=settings','settings') ) );
            }
			$wp_admin_bar->add_menu(array('parent' => 'church_admin','id' => 'plugin_support', 'title' => esc_html(__('Plugin Support','church-admin' ) ), 'href' => 'https://www.churchadminplugin.com/support/' ) );
		}
}

// Finally we add our hook function
add_action( 'wp_before_admin_bar_render', 'church_admin_admin_bar_render' );




//main admin page function


function church_admin_main()
{
    global $wpdb;
	$user=wp_get_current_user();

    //check if premium is installed
    $installed_plugins = get_plugins();

    if(!empty($installed_plugins['church-admin-premium/index.php']) && !is_plugin_active('church-admin-premium/index.php')){
        echo'<div class="notice notice-danger"><h2>Church Admin Premium</h2>';
        echo'<p>'.esc_html(__('You have already installed  the premium version. Please activate "Church Admin Premium Version" and then deactivate "Church Admin".','church-admin') ).'</p>';
        echo'<p><a class="button-primary" href="'.esc_url(wp_nonce_url(admin_url().'plugins.php?action=deactivate&plugin=church-admin/church-admin.php','deactivate-plugin_church-admin/church-admin.php')).'">'.esc_html(__('Deactivate now','church-admin')).'</a></p>';
        wp_die();
    }
   
    if(is_plugin_active('church-admin-premium/index.php')){
        echo'<div class="notice notice-danger"><h2>Church Admin Premium</h2>';
        echo'<p>'.esc_html(__('You have already installed and activated the premium version. Please deactivate the free version of "Church Admin", using the button below.','church-admin')).'</p>';
        echo'<p><a class="button-primary" href="'.esc_url(wp_nonce_url(admin_url().'plugins.php?action=deactivate&plugin=church-admin/church-admin.php','deactivate-plugin_church-admin/church-admin.php')).'">'.esc_html(__('Deactivate now','church-admin')).'</a></p>';
        echo'</div>';
        wp_die();
    }
  
    //only using modern style now.
    echo'<div class="church-admin-wrap-new"><!--church_admin_main-->'."\r\n";
        if ( empty( $_GET['action'] ) )
        {
            church_admin_boxes_look();
        }else 
        {
            echo'<div class="church-admin-content">'."\r\n";
            echo'<div id="church-admin-header">'."\r\n";
            echo '<h1 class="church-admin-title"><a title="'.esc_html( __('Back to menu','church-admin')).'" href="'.esc_url(admin_url().'admin.php?page=church_admin/index.php').'"><span class="ca-dashicons dashicons dashicons-menu-alt3" style="text-decoration:none;"></span></a> Church Admin Plugin v'.CHURCH_ADMIN_VERSION.'</h1>';
            
            //church_admin_modules_dropdown();
            echo '</div><!-- END church-admin-header-->'."\r\n";
            church_admin_actions();
            echo'</div><!-- END church-admin-content-->'."\r\n";
        }
    echo'</div><!-- .church-admin-wrap-new -->'."\r\n";

   echo'<script>// shorthand no-conflict safe document-ready function
            jQuery(function( $) {

                $( document ).on( "click", ".notice-church-admin .notice-dismiss", function () {

                    var type = $( this ).closest( ".notice-church-admin" ).data( "notice" );

                    $.ajax( ajaxurl,
                    {
                        type: "POST",
                        data: {
                        action:"church-admin",
                        method: "dismissed_notice_handler",
                        type: type,
                        }
                    } );
                } );
            });</script>'."\r\n";
   
   
}
/**************************************************
 * 
 *  MAIN SHORTCODE FUNCTION
 * 
 *********************************************************/
function church_admin_shortcode( $atts, $content = null)
{
    
    $current_user=wp_get_current_user();
   if(!empty($atts) && is_array($atts)){
    //sort out true false issue where it gets evaluated as a string
   	foreach( $atts AS $key=>$value)
   	{
   		if( $value==='FALSE'||$value==='false')$atts[$key]=0;
   		if( $value==='TRUE'||$value==='true')$atts[$key]=1;
   	}
}
   	extract(shortcode_atts(array('series_id'=>null,'login_form'=>1,'allow_registrations'=>1,'vcf'=>1,'address_style'=>'one','loginform'=>1,'cache'=>3600,'upcoming'=>true,'playlist_id'=>NULL,'nowhite'=>FALSE,'start_date'=>NULL,'target'=>0,'fund'=>NULL,'monthly'=>TRUE,'cache'=>1,'pdf'=>1,'zoom'=>13,'class_id'=>NULL,'day_calendar'=>TRUE,'style'=>'new','kids'=>TRUE,'height'=>500,'width'=>900,"pdf_font_resize"=>TRUE,"updateable"=>1,"restricted"=>0,"loggedin"=>1,"type" => 'address-list','people_types'=>'all','site_id'=>0,'days'=>30,'year'=>date('Y'),'service_id'=>NULL,'photo'=>0,'category'=>NULL,'weeks'=>4,'ministry_id'=>NULL,'people_type_id'=>NULL,'member_type_id'=>NULL,'kids'=>1,'map'=>0,'series_id'=>NULL,'speaker_id'=>NULL,'file_id'=>NULL,'api_key'=>NULL,'facilities_id'=>NULL,'exclude'=>NULL,'today'=>FALSE,'first_initial'=>0,'show_age'=>FALSE,'show_years'=>FALSE,'most_popular'=>TRUE,'order'=>'DESC','people_types'=>NULL,'title'=>"",'event_id'=>NULL,'unit_id'=>NULL,'url'=>NULL,'comments_title'=>NULL,'url'=>NULL,'hide_views'=>FALSE,'mode'=>"households","max_fields"=>10,'admin_email'=>NULL,'no_address'=>NULL,'cols'=>3,'sermon_page'=>NULL,'initials'=>0,'allow_registration'=>TRUE,"email_text"=>'','background'=>FALSE,'colorscheme'=>'','custom_id'=>NULL,'links'=>TRUE,'name_style'=>'Full','how_many'=>9,'playnoshow'=>0,'show_email'=>0,'show_phone'=>0,'rolling'=>null,'custom_fields'=>0,'onboarding'=>0,'category'=>null,'cat_id'=>null,'full_privacy_show'=>1), $atts) );
    church_admin_posts_logout();
 
    //sanitize
    $allow_registrations = !empty($allow_registrations)?1:0;
    $vcf = !empty($vcf)?1:0;
    $show_email = !empty($show_email)?1:0;
    $show_phone = !empty($show_phone)?1:0;
    $full_privacy_show =!empty($full_privacy_show) ? 1 :0;
    $file_id = !empty($file_id) ? (int)$file_id: null;
    //validate
    if(!empty($zoom) && !church_admin_int_check($zoom)){
        $zoom = 13;
    }



    // TO DO further sanitization required...

    $out='<div class="church-admin-shortcode-output ';
    if(!empty( $colorscheme) )
    {
       switch( $colorscheme) 
       {
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
			case 'warmgrey':
				$out.=' ca-dark-mode-warm-grey ';
			break;
			case 'coolgrey':
				$out.=' ca-dark-mode-cool-grey ';
			break;
		}
    }
    if(!empty( $background) )$out.=' ca-background ';
    $out.='">';
    global $wpdb,$wp_query;

    	$upload_dir = wp_upload_dir();
		$path=$upload_dir['basedir'].'/church-admin-cache/';
    	//look to see if church directory is o/p on a password protected page
    	if(!empty( $wp_query->post->ID) )$pageinfo=get_page( $wp_query->post->ID);
    	//grab page info
    	//check to see if on a password protected page
    	if(!empty( $pageinfo)&& $pageinfo->post_password!=''&&isset( $_COOKIE['wp-postpass_' . COOKIEHASH] ) )
    	{
			$text = __('Log out of password protected posts','church-admin');
		//text for link
		$link = site_url().'?church_admin_logout=posts_logout';
		$out.= '<p><a href="' . esc_url( wp_nonce_url( $link, 'posts logout') ).'">' . esc_html( $text ). '</a></p>';
		//output logoutlink
    	}
        $licence = get_option('church_admin_new_licence');
    	//grab content
    	switch( $type)
    	{
            //new premium only
            case 'ministry-rota':
            case 'spiritual-gifts':
            case 'unit':
            case 'attendance':
            case 'volunteer':
            case 'video':
            case 'classes':
            case 'class':
            case 'facilities':
            case 'facility-booking':
            case 'event_booking':
            case 'event':
            case 'events':
            case'event-booking':
            case 'recent':
            case 'bible-readings':
            case 'bible-reading':
            case 'small-groups-list':
            case 'my-group':
            case 'small-group-signup':
            case 'small-groups':
            case 'map':
            case 'ministries':
            case 'my_rota':
            case 'my-rota':
            case 'rota':
            case 'rolling-average':
            case 'weekly-attendance':
            case 'monthly-attendance':
            case 'rolling-average-attendance':
            case 'graph':
            case 'anniversaries':
            case 'covid-prebooking':
            case'service-prebooking':
            case 'service-booking-pdf':
            case 'covid-prebooking-pdf':
            case 'how-much':
            case 'giving':
            case 'giving-totals':
            case 'pledge':
            case 'contact-form':
            case 'not-available':
            case 'latest-youtube':
            case 'toilet-message':
                    if($licence =='Premium'){
                            $out.='<p>To continue using this shortcode, please update the plugin to the new Premium version in tehChurch Admin Main menu</p>';
                            return $out;
                    }
                    if(empty($licence)||$licence!='premium'){
                            $out.='<p>'.esc_html(__('This is a premium feature, please upgrade or activate premium','church-admin')).'</p>';
                            $out.='<p><a href="'.site_url().'/?licence-change=reset">'.esc_html(__('Check Activation of premium','church-admin')).'</a></p>';
                            $out.='<p><a href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">'.esc_html(__('Upgrade','church-admin')).'</a></p>';
                            return $out;
                    }
            break;

            //end premium only




            case 'mailing-list':
                require_once( plugin_dir_path( __FILE__ ).'display/mailing-list.php');
                $out.=church_admin_mailing_list($member_type_id);
            break;
            
            case 'not-logged-in':
            case 'not-logged-in':
                    if(!is_user_logged_in() ){
                    if(!empty($login_form)){
                        $content.= '<div class="login"><h2>'.esc_html( __('Please login','church-admin') ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin') ).'</a></p>';
                        
                    }    
                    return $content;
                }
            break;
            case 'logged-in':
            case 'logged-in':
                    if(is_user_logged_in() )return $content;
            break;
            
			
			
			
			case 'sessions': 
                require_once( plugin_dir_path( __FILE__ ).'includes/sessions.php');
				$out.=church_admin_sessions(NULL,NULL);
			break;
            case 'video':
                if(!empty( $url) )
                {
                    
                    $embed=church_admin_generateVideoEmbedUrl( $url);
                    $out.="\r\n<!-- CHURCH ADMIN VIDEO EMBED -->\r\n";
                    $out.='<div class="container-fluid no-padding">'."\r\n";
                    $out.='<div style="position:relative;padding-top:56.25%">'."\r\n";
                    $out.='<iframe class="ca-video" loading="lazy" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.esc_url($embed['embed']).'" '."\r\n";
                    if(!empty( $embed['image'] ) )$out.='srcdoc="<style>*{padding:0;margin:0;overflow:hidden}html,body{height:100%}img,span{position:absolute;width:100%;top:0;bottom:0;margin:auto}span{height:1.5em;text-align:center;font:48px/1.5 sans-serif;color:white;text-shadow:0 0 0.5em black}</style><a href='.esc_url($embed['embed']).'?autoplay=1&mute=1><img src='.esc_url( $embed['image'] ) .' alt=Youtube><span class=ca-play>▶</span></a>" '."\r\n";
                    $out.='frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'."\r\n";
                    $out.="</div>\r\n</div>\r\n";
                    $views=church_admin_youtube_views_api( $embed['id'] );
                    //translators: video views with count
                    if(!empty( $views)&& empty( $hide_views) )$out.='<p>'.esc_html( sprintf(__('%1$s views','church-admin' ) ,$views) ).'</p>';
                }
            break;
			case 'podcast':
                wp_enqueue_script('church-admin-datepicker');
                church_admin_podcast_script();
				require_once( plugin_dir_path( __FILE__ ).'display/sermon-podcast.php');
	            
                    $out.=church_admin_podcast_display( $series_id,$file_id,$exclude,$most_popular,$order,$nowhite);

			break;
            
            case 'sermons':
                wp_enqueue_script('church-admin-datepicker');
                require_once( plugin_dir_path( __FILE__ ).'display/new-sermon-podcast.php');
                //validate variables
                $how_many = (!empty($how_many) && church_admin_int_check($how_many)) ? (int)$how_many : 9;
                $nowhite = !empty( $nowhite ) ? 1 : 0;
                $playnoshow = !empty( $playnoshow ) ? 1 : 0;
                $start_date = (!empty($start_date) && church_admin_checkdate($start_date)) ? $start_date : null;
                $rolling = (!empty($rolling) && church_admin_int_check($rolling)) ? (int)$rolling : null;
                
                
                $out.= church_admin_new_sermons_display($how_many,$nowhite,$playnoshow,$start_date,$rolling);
            break;
            case 'sermon-series':
				
				require_once( plugin_dir_path( __FILE__ ).'display/sermon-series.php');
	               $out.=church_admin_all_the_series_display( $sermon_page);

			break;  
            case 'single-sermon':
                wp_enqueue_script('ca_podcast_audio_use');
                require_once( plugin_dir_path( __FILE__ ).'display/sermon-podcast.php');
                //$file_id=(int)church_admin_sanitize($_REQUEST['id']);
                $out.=church_admin_podcast_file_detail( $file_id , $exclude);
          
            break;
            case 'latest-sermon':
                wp_enqueue_script('ca_podcast_audio_use');
                require_once( plugin_dir_path( __FILE__ ).'display/sermon-podcast.php');
               
                    $out.=church_admin_podcast_file_detail( NULL,NULL);
                
            break;
            case 'watch-latest-sermon':
                require_once( plugin_dir_path( __FILE__ ).'display/recent-message.php');
                $out.=church_admin_latest_sermon();
            break;
            case 'player':
                wp_enqueue_script('ca_podcast_audio_use');
                require_once( plugin_dir_path( __FILE__ ).'display/sermon-podcast.php');
                if(!empty( $file_id) )  {$out.=church_admin_player( $file_id);}else{$out.=esc_html( __('No file specified','church-admin'));}
            break;
           
      case 'calendar':
            wp_enqueue_script('church-admin-calendar');//Jan 2020 version
			wp_enqueue_script('church_admin_calendar');
            $out.='<div class="church-admin-calendar">';
			if ( empty( $facilities_id)&& !empty( $pdf) )
			{
				$out.='<table><tr><td>'.esc_html( __('Yearly Planner PDFs','church-admin' ) ).' </td><td>  <form name="guideform" action="'.esc_url($_SERVER['PHP_SELF']).'" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- '.esc_html( __('Choose a PDF','church-admin' ) ).' --</option>';
				for ( $x=0; $x<5; $x++ )
				{
					$y=date('Y')+$x;
					$out.='<option value="'.home_url().'/?ca_download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.(int)$y.'">'.(int)$y.esc_html( __('Year Planner','church-admin' ) ).'</option>';
				}
				$out.='</select></form></td></tr></table>';
			}
			if( $style=='old'||!empty( $facilities_id) )
			{
            		require_once( plugin_dir_path( __FILE__ ).'display/calendar.php');
            		$out.=church_admin_display_calendar( $facilities_id);
            }
            else
            {
            	require_once( plugin_dir_path( __FILE__ ).'display/calendar.new.php');
            	//$out.=church_admin_new_calendar_display('day',$day_calendar);
                $out.=church_admin_display_new_calendar($cat_id,$facilities_id);
        	}
            $out.='</div>';
      break;
      case 'calendar-table':
        wp_enqueue_script('church-admin-calendar');//Jan 2020 version
        wp_enqueue_script('church_admin_calendar');
        $out.='<div class="church-admin-calendar">';
        if ( empty( $facilities_id)&& !empty( $pdf) )
        {
            $out.='<table><tr><td>'.esc_html( __('Yearly Planner PDFs','church-admin' ) ).' </td><td>  <form name="guideform" action="'.esc_url($_SERVER['PHP_SELF']).'" method="get"><select name="guidelinks" onchange="window.location=document.guideform.guidelinks.options[document.guideform.guidelinks.selectedIndex].value"> <option selected="selected" value="">-- '.esc_html( __('Choose a PDF','church-admin' ) ).' --</option>';
            for ( $x=0; $x<5; $x++ )
            {
                $y=date('Y')+$x;
                $out.='<option value="'.home_url().'/?ca_download=yearplanner&amp;yearplanner='.wp_create_nonce('yearplanner').'&amp;year='.(int)$y.'">'.(int)$y.esc_html( __('Year Planner','church-admin' ) ).'</option>';
            }
            $out.='</select></form></td></tr></table>';
        }
        
        require_once( plugin_dir_path( __FILE__ ).'display/calendar.php');
        $out.=church_admin_display_calendar( $facilities_id,$category);
        
        $out.='</div>';
  break;
    
    case 'facilities':
                require_once( plugin_dir_path( __FILE__ ).'display/calendar.php');
            	$out.=church_admin_display_calendar( $facilities_id);
    break;
    case 'facility-booking':
        //require_once( plugin_dir_path( __FILE__ ).'display/facility-bookings.php');
       
        //$out.=church_admin_facility_booking( $facilities_id);
    break;
    case 'names':
				if ( empty( $loggedin)||is_user_logged_in() )
				{
					require_once( plugin_dir_path( __FILE__ ).'/display/names.php'); $out.=church_admin_names( $member_type_id,$people_types);
				}
				else //login required
				{
					$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin') ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.esc_url( wp_lostpassword_url( get_permalink() ) ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin') ).'</a></p>';
				}
			break;

	case 'calendar-list':
            	require_once( plugin_dir_path( __FILE__ ).'/display/calendar-list.php'); 
                $out.=church_admin_calendar_list( $days,$category);
    break;
 
      
        case 'recent':
            $access=TRUE;
      		if(is_user_logged_in() )
      		{
      			
      			$current_user=wp_get_current_user();
      			$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
      			$restrictedList=get_option('church-admin-restricted-access');
      			if(is_array( $restrictedList)&&in_array( $people_id,$restrictedList) )$access=FALSE;
      		}    
			if ( empty( $loggedin)||is_user_logged_in() && $access)
			{
				require_once( plugin_dir_path( __FILE__ ).'includes/recent.php');
				$out.=church_admin_recent_display( $weeks,$member_type_id);
			}
			else //login required
			{
				if(!$access && is_user_logged_in()  )$out.='<div class="notice notice-warning inline">'.esc_html( __("You haven't been granted access to this infromation",'church-admin' ) ) .'</div>';
                $out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.esc_url( wp_lostpassword_url( get_permalink() ) ).'" title="Lost Password">'.esc_html(__('Help! I don\'t know my password','church-admin')).'</a></p>';
			}
			break;
            case 'phone-list':
                $access=TRUE;
                if(is_user_logged_in() )
      		    {
      			
                    $current_user=wp_get_current_user();
                    $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
                    $restrictedList=get_option('church-admin-restricted-access');
                    if(is_array( $restrictedList)&&in_array( $people_id,$restrictedList) )$access=FALSE;
      		    }
			     if ( empty( $loggedin)||is_user_logged_in() && $access)
			    { 
                    require_once( plugin_dir_path( __FILE__ ).'display/phone-list.php');
                     $out.=church_admin_frontend_phone_list( $people_type_id,$member_type_id);
                }
                else //login required
			     {
					if ( empty( $access) ) $out.='<h2>'.esc_html(__('You have not been granted access to the address list','church-admin')).'</h2>';
					else $out.='<div class="login"><h2>'.esc_html(__('Please login','church-admin')).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.esc_url(wp_lostpassword_url(get_permalink() )).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin') ).'</a></p>';
                }
            break;
            case 'custom-field':
            case 'custom-fields':
                $out.='<div class="church-admin-custom-field">';
                require_once( plugin_dir_path( __FILE__ ).'display/custom-fields.php');
                $out.=church_admin_display_custom_field( $days,$show_years,$custom_id);
                $out.='</div>'; 
            break;
            case 'address-list':
            case'addresslist':
            case 'directory':
      		    //assumed no access allowed
                 $out.='<div class="church-admin-directory">'; 
                $access=FALSE;
                if( $loggedin)
                {
                   if(!is_user_logged_in() ) 
                   {
                      $out.='<div class="login"><h2>'.esc_html( __( 'Please login', 'church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p>';
                      $out.='</div>';
                      return $out;
                   }
                   if(!empty( $member_type_id) )
                    {
                        if( $member_type_id=='All'||$member_type_id=='all'||$member_type_id=='#')
                        {
                            $access=true;
                        }
                        else
                        {
                            $current_user=wp_get_current_user();
                            $mtArray=explode(",",$member_type_id);
                            //church_admin_debug("mtArray");
                            //church_admin_debugprint_r( $mtArray,TRUE) );
                            $mt_id=$wpdb->get_var('SELECT member_type_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
                            //church_admin_debug('User mt '.$mt_id);
                            if ( empty( $mt_id) )return'<p>'.esc_html( __('Your login does not permit viewing the address list','church-admin' ) ).'</p>';
                    
                            if(!church_admin_level_check('Directory')&&!empty( $mt_id)&&!in_array( $mt_id,$mtArray) )  {
                                $out.='<p>'.esc_html( __('Your login does not permit viewing the address list','church-admin' ) ).'</p>';
                                $out.='</div>';
                                return $out;
                            }
                            $access=TRUE;
                        }
                    }   
                    if ( empty( $member_type_id) )$access=true;
                    
                    
                    if(!church_admin_level_check('Directory')&& !empty($restrictedList) && is_array( $restrictedList)&&in_array( $people_id,$restrictedList) ){
                        return'<p>'.esc_html( __('Your login does not permit viewing the address list', 'church-admin' ) ).'</p>'; 
                    }
                    if(church_admin_level_check('Directory') )$access=TRUE;   
                }
                else
                {
                    //open access
                    $access=TRUE;
                }
      		
			if(!empty( $access) )
			{
				if(!empty( $pdf) )
				{
                    $out.='<div class="church-admin-address-pdf-links">';
					switch( $pdf)
					{
						case '2':
							$out.='<p><a  rel="nofollow" href="'.home_url().'/?ca_download=addresslist&amp;addresslist='.wp_create_nonce('address-list','address-list').'&amp;title='.urlencode( $title).'&amp;loggedin='.$loggedin.'&amp;pdfversion=2&amp;member_type_id='.$member_type_id.'" target="_blank"> '.esc_html( __('PDF version','church-admin' ) ).'</a></p>';
						break;
                        case 'multi':

							$out.='<p><a  rel="nofollow" target="_blank" href="'.wp_nonce_url(home_url().'/?ca_download=addresslist-family-photos&amp;address_style=multi&amp;loggedin='.$loggedin.'&amp;title='.urlencode( $title).'&amp;kids='.$kids.'&amp;member_type_id='.$member_type_id,'address-list').'">'.esc_html( __('PDF version','church-admin' ) ).'</a></p>';
						break;
						default:
                        case 1:    
							$out.='<p><a  rel="nofollow" target="_blank" href="'.wp_nonce_url(home_url().'/?ca_download=addresslist-family-photos&amp;loggedin='.$loggedin.'&amp;title='.urlencode( $title).'&amp;kids='.$kids.'&amp;member_type_id='.$member_type_id,'address-list' ).'">'.esc_html( __('PDF version','church-admin' ) ).'</a></p>';
						break;

					}
                    $out.='</div>';
				}
				if( $style=='old')
				{
                    require_once( plugin_dir_path( __FILE__ ).'display/address-list.old.php');
            		$out.=church_admin_frontend_directory( $member_type_id,$map,$photo,$api_key,$kids,$site_id,$updateable,$address_style);
	   			}
                else
                {
                    require_once( plugin_dir_path( __FILE__ ).'display/address-list.php');
                    $out.=church_admin_frontend_directory( $member_type_id,$map,$photo,$api_key,$kids,$site_id,$updateable,$first_initial,0,$vcf,$address_style);
                    //$out.=church_admin_frontend_directory( $member_type_id,$map,$photo,$api_key,$kids,$site_id,$updateable,$first_initial,$cache,$address_style);
                    //$out.=' <p><a href="'.get_permalink().'?ca_refresh=TRUE">'.esc_html( __("Refresh",'church-admin' ) ).'</a></p>';
        	    }
                $out.='</div>';
            }
            else{ $out.='<h2>'.esc_html( __('You have not been granted access to the address list','church-admin' ) ).'</h2>';}
				
      break;
        case 'bible-readings':
        case 'bible-reading':
            require_once( plugin_dir_path( __FILE__ ).'display/bible-readings.php' );
            $out.=church_admin_bible_reading_shortcode();    
        break;
       
        case 'hello':
            if(is_user_logged_in() )
            {
                $user=wp_get_current_user();
                $name=$wpdb->get_var('SELECT first_name FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
                if( !empty( $name) ){
                    //translators: %1$s is users name
                    $out.=esc_html( sprintf(__('Welcome back %1$s', 'church-admin' ), $name ) );
                }
            }
        break;
  
    case 'map':
        $out.=church_admin_map_shortcode( $atts, $content);
    break;
    case 'shortcode-generator':
        
        require_once( plugin_dir_path( __FILE__ ).'includes/shortcode-generator.php' );
        $out.=church_admin_shortcode_generator();
    break;
    case 'register':
    case 'basic-register':
                wp_enqueue_script('ca-draganddrop');
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_script('church_admin_map_script');
                $out.=church_admin_register( $atts, $content);
    break;
   
     
            case 'anniversaries':
                if ( empty( $loggedin)||is_user_logged_in() )
                {
                    require_once( plugin_dir_path( __FILE__ ).'includes/birthdays.php');
                    $out.=church_admin_frontend_anniversaries( $member_type_id,$people_type_id, $days,$show_age,$show_email,$show_phone);
                }
                else //login required
                {
                    $out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin') ).'</a></p>';
                }
    
                break;
			case 'birthdays':
			if ( empty( $loggedin)||is_user_logged_in() )
			{
				require_once( plugin_dir_path( __FILE__ ).'includes/birthdays.php');
                $out.=church_admin_frontend_birthdays( $member_type_id,$people_type_id, $days,$show_age,$show_email,$show_phone);
			}
			else //login required
			{
				$out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin') ).'</a></p>';
			}

			break;
			case 'restricted':
				//restricts content to certain member_type_ids
				if(!is_user_logged_in() )
				{
					if(!empty( $loginform) )  { 
                            $out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin') ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.wp_lostpassword_url(get_permalink() ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin') ).'</a></p>';
                    }
                    else{
                        $out.='<p>'.esc_html( __('Restricted content','church-admin') ).'</p>';
                    }
				}
                elseif(church_admin_user_member_level( $member_type_id) )  {
                    $out.=do_shortcode( $content);
                }
                else{
                    $out.=esc_html( __('You are not permitted to view this content','church-admin') );
                }
			break;
			case 'follow-up':
				if(is_user_logged_in()&& church_admin_level_check('Directory') )
				{
					require_once( plugin_dir_path( __FILE__ ).'includes/people_activity.php');
					return church_admin_recent_people_activity();
				}
				else{
                    $out.=esc_html( __( 'You are not permitted to view this content', 'church-admin' ) );
                }
			break;
			default:
				if ( empty( $loggedin)||is_user_logged_in() )
				{

						//$out.='<p><a  rel="nofollow" href="'.home_url().'/?ca_download=addresslist&amp;addresslist='.wp_create_nonce('member'.$member_type_id ).'&amp;member_type_id='.$member_type_id.'">'.esc_html( __('PDF version','church-admin' ) ).'</a></p>';
        	    require_once( plugin_dir_path( __FILE__ ).'display/address-list.php');
         	   $out.=church_admin_frontend_directory( $member_type_id,$map,$photo,$api_key,$kids,$site_id,$updateable);
					 }
					 else //login required
					 {
						 $out.='<div class="login"><h2>'.esc_html( __('Please login','church-admin') ).'</h2>'.wp_login_form(array('echo'=>FALSE,'redirect'=>get_permalink()) ).'</div>'.'<p><a href="'.esc_url( wp_lostpassword_url(get_permalink() ) ).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin') ).'</a></p>';
					 }
       		break;
       
            
          
           
           
            case 'latest-youtube':
            case 'latest_youtube':
                require_once( plugin_dir_path( __FILE__ ).'display/latest-youtube.php');
                $out.= church_admin_latest_youtube( $playlist_id,$cache);
            break;
          

    	}

//output content instead of shortcode!
    $out.='</div>';
return $out;
}

add_shortcode('church_admin_unsubscribe','church_admin_unsubscribe');
function church_admin_unsubscribe()
{
	$out='<p>'.esc_html( __('This shortcode is deprecated','church-admin' ) ).'</p>';
	return $out;
}
add_shortcode('church_admin_recent','church_admin_recent');
function church_admin_recent( $atts, $content = null)
{
    extract(shortcode_atts(array('month'=>1), $atts) );
    require_once( plugin_dir_path( __FILE__ ).'includes/recent.php');
    $out = church_admin_recent_display( $month);
	return $out;
}
add_shortcode("church_admin", "church_admin_shortcode");

add_shortcode("church_admin_map","church_admin_map_shortcode");
function church_admin_map_shortcode( $atts, $content = null)
{
    $out='';
    extract(shortcode_atts(array('zoom'=>13,'member_type_id'=>1,'small_group'=>1,'unattached'=>0,'loggedin'=>1,'width'=>"100%",'height'=>"1000px",'colorscheme'=>''), $atts) );
    global $wpdb;

    //sanitize
    $zoom = !empty($zoom) ? church_admin_sanitize($zoom): 13;
    $width = !empty($width) ? church_admin_sanitize($width): '100%';
    $height = !empty($height) ? church_admin_sanitize($height): '100%';
    $member_type_id = !empty($member_type_id) ? (int)church_admin_sanitize($member_type_id): null;
    $small_group = !empty($small_group) ? 1 : 0;
    $logged_in = !empty($logged_in) ? 1 : 0;
    
    //validate
    if(!empty($zoom) && !church_admin_int_check($zoom)){
        $zoom = 13;
    }
    $out.='<div class="alignwide church-admin-shortcode-output ';
	if(!empty( $colorscheme) )  {

		switch( $colorscheme)
		{
			case 'white':
				$out.='ca-background ';
			break;
			case 'bluegrey':
			default: 
				$out.=' ca-dark-mode-blue-grey ';
			break;
		}
	}
	$out.='">';
    $out.= church_admin_map( $zoom,$member_type_id,$small_group,$unattached,$loggedin,$width,$height);
    $out.='</div>';
    return $out;
}
function church_admin_map( $zoom=13,$member_type_id=NULL,$small_group=1,$unattached=1,$loggedin=1,$width="100%",$height="500px")
{


     //sanitize
     $zoom = !empty($zoom) ? church_admin_sanitize($zoom): 13;
     $width = !empty($width) ? church_admin_sanitize($width): null;
     $height = !empty($height) ? church_admin_sanitize($height): null;
     $member_type_id = !empty($member_type_id) ? (int)church_admin_sanitize($member_type_id): null;
     $small_group = !empty($small_group) ? 1 : 0;
     $logged_in = !empty($logged_in) ? 1 : 0;

    //validate
    if(!empty($zoom) && !church_admin_int_check($zoom)){
        $zoom = 13;
    }


	global $wpdb;    
		$out='';
	//if(defined("CA_DEBUG") )//church_admin_debug("****************\r\n church_admin_map function");
	if ( empty( $loggedin)||is_user_logged_in() )
	{
		wp_enqueue_script('church_admin_google_maps_api');
		wp_enqueue_script('church_admin_map');

    
    $coords=church_admin_center_coordinates($wpdb->prefix.'church_admin_household');
    //if(defined("CA_DEBUG") )//church_admin_debug('Center coordinates'."\r\n".print_r( $coords,TRUE) );
    $out.='<div class="church-admin-member-map"><script type="text/javascript">var xml_url="'.site_url().'/?ca_download=address-xml&member_type_id='.esc_html( $member_type_id).'&small_group='.esc_html( $small_group).'&unattached='.esc_html( $unattached).'&address-xml='.wp_create_nonce('address-xml').'";';
    $out.=' var lat='.esc_html( $coords->lat).';';
    $out.=' var lng='.esc_html( $coords->lng).';';

	$out.=' var zoom='.(int) $zoom.';';
	$out.=' var translation=["'.esc_html( __('Small Groups','church-admin' ) ).'","'.esc_html( __('Unattached','church-admin' ) ).'","'.esc_html( __('In a group','church-admin' ) ).'","'.esc_html( __('Group','church-admin' ) ).'"];';
    $out.='jQuery(document).ready(function()  {console.log("Ready to lead");
    load(lat,lng,xml_url,zoom,translation);});</script><div id="church-admin-member-map" style="width:'.esc_attr( $width ).';height:'.esc_attr( $height ).'"></div>';
    $out.='<div id="groups" ><p><img src="https://maps.google.com/mapfiles/kml/paddle/blu-circle.png" />'.esc_html( __('Small Group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/red-circle.png" />'.esc_html( __('Not in a small group','church-admin' ) ).'<br><img src="https://maps.google.com/mapfiles/kml/paddle/grn-circle.png" />'.esc_html( __('In a small Group','church-admin') ).'</p></div>';
    
	}
	else {
		$out.='<h3>'.esc_html( __('You need to be logged in to view the map','church-admin') ).'</h3>'.wp_login_form(array('echo'=>false) );
	}
    return $out;

}
add_shortcode("church_admin_register","church_admin_register");
function church_admin_register( $atts, $content = null)
{
 	
    extract(shortcode_atts(array('allow_registrations'=>1,'member_type_id'=>1,'allow'=>NULL,'background'=>FALSE,'exclude'=>NULL,'custom_fields'=>0,'onboarding'=>0,'admin_email'=>1,'full_privacy_show'=>1), $atts) );
    $full_privacy_show =!empty($full_privacy_show) ? 1 :0;

    $noshow=$allowArray=array();
    if(!empty( $exclude) )
	{
		$noshow=explode(",",$exclude);
	}
    if(!empty( $allow) )
	{
		$allowArray=explode(",",$allow);
	}

    //allow for custom_fields shortcode value to override old style exclude array
    if(!empty($custom)){

        if (($key = array_search('custom_fields', $noshow)) !== false) {
            unset($noshow[$key]);
        }

    }
    //church_admin_debug $allowArray,TRUE);
    require_once( plugin_dir_path( __FILE__ ).'includes/front_end_register.php');
    wp_enqueue_script('church_admin_google_maps_api');
    wp_enqueue_script('church_admin_map_script');
    wp_enqueue_script('ca-draganddrop');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('church-admin-ui',plugins_url('/',dirname(__FILE__) ). 'css/jquery-ui-1.13.2.css',false,"1.13.2",false);
    $out=church_admin_front_end_register( $member_type_id, $noshow, $admin_email, $allowArray, $allow_registrations,$onboarding,$full_privacy_show);
    return $out;
}

function church_admin_posts_logout()
{
    if ( isset( $_GET['church_admin_logout'] ) && ( 'posts_logout' == $_GET['church_admin_logout'] ) &&check_admin_referer( 'posts logout' ) )
    {
	setcookie( 'wp-postpass_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH );
	wp_redirect( wp_get_referer() );
	die();
    }
}


add_action( 'init', 'church_admin_posts_logout' );

//end of logout functions



add_action('init','church_admin_download');
function church_admin_download()
{
    global $wpdb;

    /*********************************
     * Handle delete me from app
     * 
     ******************************/

     if(!empty($_GET['church_admin_delete'])){

        //translators %1$s is the name of the website
        echo'<!doctype html><html><head><title>'.esc_html(__('User delete','church-admin')).'</title><style>body { text-align: center; padding:150px; }h1 { font-size: 50px; }body { font: 20px Helvetica, sans-serif; color: #333; }article { display: block; text-align:center; width: 650px; margin: 0 auto; }a { color: #dc8100; text-decoration: none; }a:hover { color: #333; text-decoration: none; }</style></head><body><article><p style="text-align:center"><img src="'.plugins_url('/', __FILE__ ) .'images/church-admin-logo.png"></p><h2>'.esc_html(sprintf(__('User deletion for %1$s','church-admin'),get_bloginfo('name'))).'</h2><p>';
    
        $people_id = !empty($_GET['church_admin_delete'])? church_admin_sanitize($_GET['church_admin_delete']): null;
        $token = !empty($_GET['token'])? church_admin_sanitize($_GET['token']): null;
        if(!empty($people_id) && !empty($token)){
            $loginStatus = $wpdb->get_row('SELECT b.UUID AS token,a.member_type_id,a.people_id,a.user_id,a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql($token).'"');
            
            if(!empty($loginStatus) && $people_id == $loginStatus->people_id){
                $household_numbers = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$loginStatus->household_id.'"');
				if(!empty($household_numbers) && $household_numbers==1){$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$loginStatus->household_id.'"');}
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_app WHERE people_id="'.(int)$loginStatus->people_id.'"');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$loginStatus->people_id.'"');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$loginStatus->people_id.'"');
				require_once(ABSPATH.'wp-admin/includes/user.php');
				if(!user_can($loginStatus->user_id,'manage_options') ){

                    if(empty(count_user_posts($loginStatus->user_id))){
                        //only delete account if no posts
                        echo '<h2>'.__('Success','church-admin').'</h2><p>'.__('You have been deleted from the directory and your user account has been deleted. Sorry to see you go.','church-admin').'</p>';
                        wp_delete_user($loginStatus->user_id,null);

                    }
                    else
                    {
                        echo '<h2>'.__('Success','church-admin').'</h2><p>'.__('You have been deleted from the directory BUT your user account has not been deleted because you have publisehed posts on the site. A site admin will remove you.','church-admin').'</p>';

                    }
                }
                else{

                    echo '<h2>'.__('Success','church-admin').'</h2><p>'.__('You have been deleted from the directory BUT your user account has not been deleted because you are a site admin.','church-admin').'</p>';

                }

				
                exit();

            }else
            {

            echo '<p>'.__('User not found, so nothing deleted.','church-admin').'</p>';
            }


        }
        echo'<div><p><a href="'.site_url().'">'.esc_html( __('Back to main site','church-admin')).'</a></p></div></article></body></html>';
        exit();

    }




	if(!empty( $_REQUEST['ca_download'] ) )
	{
		header("X-Robots-Tag: noindex, nofollow", true);
        $member_type_id=NULL;
        if ( empty( $_REQUEST['addressType'] ) )  {$addressType='street';}
        else
        {
            switch( $_REQUEST['addressType'] )
            {
                default:
                case 'street':
                    $addressType='street';
                break;
                case 'mailing':
                    $addressType='mailing';
                break;
            
            }
        }
        $loggedin=!empty( $_REQUEST['loggedin'] )?1:0;
	
	
        $member_type_id = !empty($_REQUEST['member_type_id']) ?church_admin_sanitize($_REQUEST['member_type_id']) : null;
        $people_type_id = !empty($_REQUEST['people_type_id']) ?church_admin_sanitize($_REQUEST['people_type_id']) : null;
        if(!empty( $_REQUEST['date'] ) )  {
            $date=sanitize_text_field(stripslashes($_REQUEST['date']));
            if(!church_admin_checkdate($date)){
                $date=date('Y-m-d');
            }
        }else{$date=date('Y-m-d');}
        if(!empty( $_REQUEST['pdf_font_resize'] ) )  {
            $resize=sanitize_text_field(stripslashes($_REQUEST['pdf_font_resize']));
        }else{$resize=FALSE;}
        if(!empty( $_REQUEST['pdfversion'] ) )  {
            $pdfversion=sanitize_text_field(stripslashes($_REQUEST['pdfversion']));
        }else{$pdfversion=1;}
        if(!empty( $_REQUEST['service_id'] ) )  {
            $service_id=sanitize_text_field(stripslashes($_REQUEST['service_id']));
            //validate
            if(!church_admin_int_check($service_id)){$service_id=1;}
        }else
        {
            $service_id=1;
        }
        $facilities_id = !empty($_REQUEST['facilities_id'])? church_admin_sanitize($_REQUEST['facilities_id']) : null;
        $cat_id = !empty($_REQUEST['cat_id'])? church_admin_sanitize($_REQUEST['cat_id']) : null;

      
        $kids=!empty($_REQUEST['kids'])?1:0;
        $id = !empty($_REQUEST['id'])?church_admin_sanitize($_REQUEST['id']):0;
        
        if(!empty( $_REQUEST['start_date'] ) )  {
            $start_date=sanitize_text_field(stripslashes($_REQUEST['start_date']));
            //validate
            if(!church_admin_checkdate($start_date)){
                $start_date = date('Y-m-d');
            }
        }else{$start_date=date('Y-m-d');}
        if(!empty( $_REQUEST['end_date'] ) )  {
            $end_date= sanitize_text_field(stripslashes( $_REQUEST['end_date'] ) ) ;
            //validate
            if(!church_admin_checkdate($end_date)){
                $end_date=date('Y-m-d',strtotime("+3 years") );
            }
        }else{$end_date=date('Y-m-d',strtotime("+3 years") );}
        $showDOB = !empty( $_REQUEST['showDOB'] ) ?1:0;
    
        if(!empty( $_REQUEST['service_id'] ) )  {
            $service_id= sanitize_text_field(stripslashes($_REQUEST['service_id']));
            //validate
            if(!church_admin_int_check($service_id)){$service_id=FALSE;}
        }else{$service_id=FALSE;}
        if(!empty( $_REQUEST['people_id'] ) )  {
            $people_id= sanitize_text_field(stripslashes($_REQUEST['people_id']));
            //validate 
            if(!church_admin_int_check($people_id)){
                $people_id = FALSE;
            }
        
        }else{$people_id=FALSE;}
            if(!empty( $_REQUEST['file_id'] ) )  {
                $file_id= sanitize_text_field( stripslashes( $_REQUEST['file_id'] ) );
                //validate
                if(!church_admin_int_check($file_id)){
                    $file_id= FALSE;
                }
            }else{$file_id=FALSE;}
        if(!empty( $_REQUEST['event_id'] ) )  {
                $event_id=$_REQUEST['event_id'];
                //validate
                if(!church_admin_int_check($event_id)){
                    $event_id= FALSE;
                }
        }else{$event_id=FALSE;}
        if(!empty( $_REQUEST['unit_id'] ) )  {
                $unit_id=$_REQUEST['unit_id'];
                //validate
                if(!church_admin_int_check($unit_id)){
                    $unit_id= FALSE;
                }
        }else{$unit_id=FALSE;}
        $booking_ref = !empty($_REQUEST['booking_ref'])?sanitize_text_field(stripslashes($_REQUEST['booking_ref'])):NULL;
        if(!empty( $_REQUEST['date_id'] ) )  {
            $date_id= sanitize_text_field( stripslashes( $_REQUEST['date_id'] ) ) ;
        }else{$date_id=FALSE;}
        if(!empty( $_REQUEST['fund'] ) )  {
            $fund=urldecode( sanitize_text_field(stripslashes($_REQUEST['fund'] ) ) );
        }else{$fund='All';}
        if(!empty( $_REQUEST['title'] ) )  {
            $title =  sanitize_text_field(stripslashes($_REQUEST['title'] ) ) ;
        }else{$title=NULL;}
        if(!empty( $_REQUEST['initials'] ) )  {$initials=1;}else{$initials=0;}
        
        switch( $_REQUEST['ca_download'] )
        {
            case 'monthly-calendar-pdf':
                require_once(plugin_dir_path(__FILE__ ).'includes/pdf_creator.php');
                church_admin_monthly_calendar_pdf($start_date, $facilities_id,$cat_id    );
            break;
            case 'weekly-calendar-pdf':
                require_once(plugin_dir_path(__FILE__ ).'includes/pdf_creator.php');
                church_admin_weekly_calendar_pdf($facilities_id,$cat_id,$start_date);
            break;
           
            case 'photo-permissions-pdf':
                            //church_admin_debug('photo-permissions-pdf');
                if(church_admin_level_check('Directory') )
                {
                    require_once(plugin_dir_path(__FILE__ ).'includes/pdf_creator.php');
                    $peopleIDs= !empty($_GET['people_type_id'])?church_admin_sanitize($_GET['people_type_id']):null;
                    //church_admin_debug($peopleIDs);
                    church_admin_photo_permissions_pdf( $peopleIDs);
                }else{
                    echo'<div class="error"><p>'.esc_html( __("You don't have permissions",'church-admin' ) ).'</p></div>';
                }
            break;
            case 'smallgroup-signup':
                require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                church_admin_smallgroup_signup_pdf( $title);
            break;
            case 'sermon-notes':
                require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                church_admin_sermon_notes_pdf( $file_id);
            break;
            
       
         
    
            case'address-list':
            case'addresslist':
                
                church_admin_debug('Doing address list PDF ');
                church_admin_debug($_REQUEST);
                $pdf_version =!empty($_REQUEST['pdf_version'])? (int)church_admin_sanitize($_REQUEST['pdf_version']):2;
                $showDOB = FALSE;
                $title =!empty($_REQUEST['title'])? (int)church_admin_sanitize($_REQUEST['title']) : __('Address List','church-admin');
                $loggedin=TRUE;


                if(wp_verify_nonce( $_GET['_wpnonce'],'address-list') )
                {		
                    if ( !empty( $_REQUEST['address_style'] ) ){
                        switch( $_REQUEST['address_style'] )
                        {   
                            default:
                            case 'multi':
                                $address_style="multi";
                            break;
                            case'single':
                                $address_style='single';
                            break;
                        }
                    }
                    
                    require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');

                    switch( $pdf_version)
                    {
                        case 1:default:     
                            church_admin_address_pdf_v1( $member_type_id,$loggedin,$showDOB,$title ,$address_style,1);
                        break;
                        case 2:
                            //church_admin_debug('about to do pdf directory');
                            church_admin_address_pdf_v2( $member_type_id,$loggedin);
                        break;
                        case 3:
                            church_admin_address_pdf_v1( $member_type_id,$loggedin,$showDOB,$title ,$address_style,0);
                        break;
                    }
                        
                }
            break;
            case'addresslist-family-photos':
                church_admin_debug('Doing family photos PDF ');
                if(!empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'],'address-list') )
                {
                    require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                    $member_type_id=!empty( $_REQUEST['member_type_id'] )?$_REQUEST['member_type_id']:0;
                    $title = !empty( $_REQUEST['title'] )?$_REQUEST['title']:__('Address List','church-admin');
                    church_admin_debug($title);
                    church_admin_address_pdf_v1( $member_type_id,$loggedin,FALSE,$title);
                }else{
                    echo'<p>'.esc_html(__('You can only download if coming from a valid link',' church-admin') ).'</p>';
                }
            break;	
      
            
     
            case 'csv-filter':
                if(church_admin_level_check('Directory') )
                {
                    require_once( plugin_dir_path( __FILE__ ).'includes/csv.php');
                    church_admin_people_csv();
                }
                else{
                    echo esc_html( __("You don't have permissions to do that", 'church-admin' ) );
                }
            break;
    
            case 'people-csv':
                    if(wp_verify_nonce( $_REQUEST['people-csv'],'people-csv') )
                    {
                        require_once( plugin_dir_path( __FILE__ ).'includes/csv.php');
                        church_admin_people_csv();
                    }
                    else
                    {
                        echo'<p>'.esc_html(__('You can only download if coming from a valid link',' church-admin') ).'</p>';
                    }
            break;
            
            case 'address-xml':
                $member_type_id=!empty( $_REQUEST['member_type_id'] )?$_REQUEST['member_type_id']:'#';
                $small_group=!empty( $_REQUEST['small_group'] )?1:0;
                require_once( plugin_dir_path(__FILE__).'includes/pdf_creator.php' );
                
                church_admin_address_xml( $member_type_id,$small_group);
                exit();
            break;
            case'cron-instructions':
                if ( wp_verify_nonce( $_GET['cron-instructions'], 'cron-instructions') )  {
                    require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                    church_admin_cron_pdf();
                }
                else {
                    echo'<p>'.esc_html(__('You can only download if coming from a valid link',' church-admin') ).'</p>';
                    exit();
                }
            break;

            case'yearplanner':
                if ( wp_verify_nonce( $_REQUEST['yearplanner'], 'yearplanner' ) )  {
                    require_once ( plugin_dir_path(__FILE__).'includes/pdf_creator.php' );
                    church_admin_year_planner_pdf( sanitize_text_field(stripslashes($_REQUEST['year'] )));
                } else {
                    echo'<p>'.esc_html(__('You can only download if coming from a valid link',' church-admin') ).'</p>';
                    exit();
                }
            break;
            
          
            case 'vcf-person':
                $okay=FALSE;
                if(!empty( $_GET['token'] ) )
                {
                    //church_admin_debug("Coming from app");
                    //coming from app so no nonce, but login can be checked
                    $sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes($_GET['token']) ) ).'"';
                    //church_admin_debug($sql);
                    $result=$wpdb->get_var( $sql);
                    //church_admin_debug("Result: ".$result);
                    if( !empty( $result) ) { 
                    
                        $okay = TRUE; 
                    }
                }
                //only allow login if from logged in app user or correct nonce
                if( !empty($okay) || wp_verify_nonce( $_REQUEST['_wpnonce'],$_GET['id'] ) )
                {
                    //church_admin_debug('passed checks');
                    
                    require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                    $people_id=!empty($_GET['id'])?(int)$_GET['id']:null;
                    if(empty($people_id)){
                        //app sends $_GET['people_id']
                        $people_id=!empty($_GET['people_id'])?(int)$_GET['people_id']:null;
                    }
                    ca_person_vcard( $people_id );
                } else {
                    echo'<p>'.esc_html( __('You can only download if coming from a valid link','church-admin' ) ).'</p>';
                }
                exit();
            break;
            case 'vcf':
                $okay=FALSE;
                if(!empty( $_GET['token'] ) )
                {
                    $sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( $_GET['token'] ) ).'"';
                    $result=$wpdb->get_var( $sql);
                    if(!empty( $result) )$okay=TRUE;
                }
                if( $okay||wp_verify_nonce( $_REQUEST['_wpnonce'],$_REQUEST['id'] ) )
                {
                    require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                    ca_vcard( $id );
                }else{
                    echo'<p>'.esc_html(__('You can only download if coming from a valid link',' church-admin') ).'</p>';
                }
                exit();
            break;
            case'mailinglabel':
                if(church_admin_level_check('Directory') )
                {
                    require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                    church_admin_label_pdf( $member_type_id,$loggedin,$addressType);
                
                }
                exit();

            break;
            case'householdlabel':
                if(church_admin_level_check('Directory') )
                {
                    require_once( plugin_dir_path( __FILE__ ).'includes/pdf_creator.php');
                    church_admin_household_label_pdf( $member_type_id,$loggedin,$addressType);
                }
                exit();
            break;

        }
	exit();	
	}

}
function church_admin_delete_backup()
{
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/church-admin-cache/*';
    $files = glob( $path); // get all file names
    foreach( $files as $file)
    { // iterate files
        if(is_file( $file) )unlink( $file); // delete file
    }
    $text="<?php exit('----------------------------------------
  ___ _   _             _ _         _                 _         _
 |_ _| |_( )___    __ _| | |   __ _| |__   ___  _   _| |_      | | ___  ___ _   _ ___
  | || __|// __|  / _` | | |  / _` | '_ \ / _ \| | | | __|  _  | |/ _ \/ __| | | / __|
  | || |_  \__ \ | (_| | | | | (_| | |_) | (_) | |_| | |_  | |_| |  __/\__ \ |_| \__ \
 |___|\__| |___/  \__,_|_|_|  \__,_|_.__/ \___/ \__,_|\__|  \___/ \___||___/\__,_|___/
'); \r\n // Nothing is good! ";
    $church_admin_fp = fopen( $upload_dir['basedir'].'/church-admin-cache/debug_log.php', 'w');
    if($church_admin_fp){
        fwrite( $church_admin_fp, $text."\r\n");
        fclose($church_admin_fp);
    }
    $church_admin_fp = fopen( $upload_dir['basedir'].'/church-admin-cache/index.php', 'w');
    if($church_admin_fp){
        fwrite( $church_admin_fp, $text."\r\n");
        fclose($church_admin_fp);
    }
 
}





function church_admin_activation_log_clear()
{
     delete_option('church_admin_plugin_error');
                                              
     echo'<div class="notice notice-success"><h2>'. esc_html( __( 'Installation errors cleared', 'church-admin' ) ).'</h2></div>';
}









function church_admin_from_name( $from ) {
    if(!empty( $_POST['from_name'] ) )  {
        return esc_html(sanitize_text_field( stripslashes($_POST['from_name'] ) ) );
        }
        else {
            return get_option('blogname');
        }
    }
function church_admin_from_email( $email ) {
    if(!empty( $_POST['from_email'] ) )  {
            return esc_html(sanitize_text_field( stripslashes( $_POST['from_email'] ) ) );
        }else {
            return get_option('church_admin_default_from_email');
        }
    
}
function church_admin_debug($message)
{
    $debug=get_option('church_admin_debug_mode');
    if(!defined('CA_DEBUG') && empty($debug) )return;
    if(is_array( $message)||is_object( $message) )$message=print_r( $message,TRUE);
    $text="<?php exit('Nothing is good!') ;?>";
	$upload_dir = wp_upload_dir();
	$debug_path=$upload_dir['basedir'].'/church-admin-cache/';
	if(file_exists( $debug_path.'debug.log') )unlink( $debug_path.'debug.log');
	if(!file_exists( $debug_path.'debug_log.php') )
	{

		$church_admin_fp = fopen( $debug_path.'debug_log.php', 'w');
		if($church_admin_fp){
            fwrite( $church_admin_fp, $text."\r\n");
            
        }
	}
	if ( empty( $church_admin_fp) )$church_admin_fp = fopen( $debug_path.'debug_log.php', 'a');
    if(!empty($church_admin_fp)){
        fwrite( $church_admin_fp, $message."\r\n");
        fclose( $church_admin_fp);
    }
    if(!file_exists( $debug_path.'index.php') )
    {
        $church_admin_fp = fopen( $debug_path.'index.php', 'w');
        if(!empty($church_admin_fp)){
            fwrite( $church_admin_fp, $text."\r\n");
            fclose( $church_admin_fp);
        }
    }
}

register_deactivation_hook(__FILE__, 'church_admin_deactivation');

function church_admin_deactivation() {
    
    $args = get_option('church_admin_happy_birthday_arguments');
	wp_clear_scheduled_hook( 'church_admin_happy_birthday_email',$args );



    $cron=get_option('cron');
    foreach($cron AS $id=>$cronArray)
    {
        if(!empty($cron[$id]['church_admin_bulk_email'])){
            unset($cron[$id]['church_admin_bulk_email']);
        }
      
    }

    update_option('cron',$cron);
}

/**************************************
 * CHURCH ADMIN BULK EMAIL 
 **************************************/

add_action('church_admin_bulk_email','church_admin_bulk_email');
function church_admin_bulk_email()
{
    church_admin_debug('****** BULK EMAIL CRON ********');
	global $wpdb;

	$max_email=get_option('church_admin_bulk_email');

	if ( empty( $max_email) )$max_email=100;
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_email WHERE schedule <=NOW() OR schedule is NULL  LIMIT 0,'.$max_email;
    //church_admin_debug $sql);
	$result=$wpdb->get_results( $sql);

	if(!empty( $result) )
	{
		foreach( $result AS $row)
		{
            if(!is_email($row->recipient)){
                //delete from queue and skip
                $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_email WHERE email_id="'.(int)$row->email_id.'"');
                continue;
            }
            $from_name = !empty($row->from_name) ? $row->from_name : get_option('church_admin_default_from_name');
            $from_email = !empty($row->from_email) ? $row->from_email : get_option('church_admin_default_from_email');
            $reply_email = !empty($row->reply_email) ? $row->reply_email : get_option('church_admin_default_from_email');
            $reply_name = !empty($row->reply_name) ? $row->reply_name : get_option('church_admin_default_from_name');

            church_admin_email_send($row->recipient,$row->subject,$row->message,$from_name,$from_email,unserialize( $row->attachment),$reply_name,$reply_email,TRUE);
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_email WHERE email_id="'.(int) $row->email_id.'"');
           
		}
	}
    echo 'Done';
    exit;
}

//add donate link on config page
add_filter( 'plugin_row_meta', 'church_admin_plugin_meta_links', 10, 2 );
function church_admin_plugin_meta_links( $links, $file ) {
	$plugin = plugin_basename(__FILE__);
	// create link
	if ( $file == $plugin ) {
		return array_merge(
			$links,
			array( '<a href="http://www.churchadminplugin.com/support">Support</a>','<a href="https://pay.sumup.io/b2c/QEEPP89C">Donate</a>' )
		);
	}
	return $links;
}


add_action( 'wp_trash_post', 'church_admin_delete_from_app_menu');

function church_admin_delete_from_app_menu($postID)
{
    global $wpdb;
    $post=$wpdb->get_row('SELECT * FROM '.$wpdb->posts.' WHERE ID="'.(int)$postID.'"');
    church_admin_debug('******* POST delete'.$postID.' *******');
       if(!empty($post->post_type) && $post->post_type == 'app-content'){
        church_admin_debug('App menu upated on deleting '.$postID);
        require_once(plugin_dir_path( __FILE__) .'/app/app-setup.php');
        ca_update_app_menu('remove',$post->post_title);
    }

}


add_action( 'transition_post_status', 'church_admin_post_message', 10, 3 );

function church_admin_post_message( $new_status, $old_status, $post ) 
{
    //update app cache time
   
    //if(!defined('CA_DEBUG') )define('CA_DEBUG',TRUE);
	//church_admin_debug("**********************************\r\nPublish firing\r\n".date('Y-m-d H:i:s') );
    //church_admin_debug("Post title {$post->post_title}");
    //church_admin_debug("Post type {$post->post_type}");
    //church_admin_debug("New Status {$new_status}");
    //church_admin_debug("Old Status {$old_status}");
    //app content modified check
    $continue=FALSE;
    switch($post->post_type){

        case 'post':
        case 'bible-readings':
        case 'prayer-requests':
        case 'app-content':
            $continue = TRUE;
        break;    
    }
    if( $new_status!='publish') 
    {
        $continue = FALSE;
    }
    if(empty($continue)) {return;}



    if( $post->post_type=='app-content' && ( $new_status=='update'||$new_status=='publish') )
    {
        church_admin_debug('App menu updated on publishing '.$post->post_title);
        require_once(plugin_dir_path( __FILE__) .'/app/app-setup.php');
        ca_update_app_menu('add',$post->post_title);
    }
    
    global $wpdb,$videoURL;
    //$debug=FALSE;//stop push notifications while testing
    $user=wp_get_current_user();
    $sender=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    if(!empty( $sender) )$username=implode(" ",array_filter(array( $sender->first_name,$sender->prefix,$sender->last_name) ));

    $no_push=get_option('church_admin_no_push');
	if( $no_push)
    {
        //church_admin_debug("No push");
        return;
    }
    
	
	$title='';
	$type=get_post_type( $post );
	$sent=get_post_meta( $post->ID,'Email Sent',TRUE);
    $pushOK=TRUE;
    if(!empty( $_POST['church_admin_no_push'] ) )return;
    if( $new_status == 'publish' && $old_status != 'publish' && !empty( $type) && ( $type=='prayer-requests'||$type=='post'||$type=='bible-readings') && $pushOK)
    {
     	//church_admin_debug("Post status changed to PUBLISH");

	
		switch( $type)
		{
			case 'acts-of-courage':
                $title=__('New Act of Courage','church-admin');
                $contactType='acts-of-courage';
                $ministry=__('Prayer requests send','church-admin');
            break;
			case 'prayer-requests':
				$title=__('New Prayer Request','church-admin');
                $contactType='prayer';
				$ministry=__('Prayer requests send','church-admin');	
			break;
			case 'bible-readings':
                $title=__('New Bible Reading','church-admin');
                $contactType='bible';
                $ministry=__('Bible readings send','church-admin');
                break;
			case 'post':
                $title=__('New Blog Post','church-admin');
                $contactType='posts';
                $ministry=__('News send','church-admin');
            break;
            
		}
        if( $type=='post')$type='posts';
		//church_admin_debug("ContactType = $contactType Ministry = $ministry");
		/****************************************
		*
		* Push Notification
		*
		****************************************/
		if( $_SERVER['SERVER_NAME']!="localhost")
        {
           
        	
                    $private=get_option('church-admin-private-prayer-requests');
                    if( $type=='bible-readings')
                    {
                        //only send bible readings to logged in app users who accept bible readings
                        $myTokens=$wpdb->get_results('SELECT a.*,a.pushToken,b.meta_date FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE  a.active=1 AND a.pushToken!="" AND a.people_id=b.people_id AND b.meta_type="bible-readings-notifications"');
                        //church_admin_debug $wpdb->last_query);
                        $pushTokens=array();
                        if(!empty( $myTokens) )
                        {
                            foreach( $myTokens AS $myToken)
                            {
                                if(!in_array( $myToken->pushToken,$pushTokens) )  {
                                    $pushTokens[]=$myToken->pushToken;
                                }
                                //church_admin_debug("Push token for ".church_admin_formatted_name( $myTokens).' is '.$myToken->pushToken);
                            }
                        }
                        
                    }elseif( $private && $type=='prayer-requests')
                    {
                        //only send prayer requestes to logged in app users who accept prayer requests
                        $myTokens=$wpdb->get_results('SELECT a.pushToken,b.meta_date FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.active=1 AND a.pushToken!="" AND a.people_id=b.people_id AND b.meta_type="prayer-requests-notifications"');
                        //church_admin_debug $wpdb->last_query);
                        $pushTokens=array();
                        if(!empty( $myTokens) )
                        {
                            foreach( $myTokens AS $myToken)if(!in_array( $myToken->pushToken,$pushTokens) )  {$pushTokens[]=$myToken->pushToken;}
                        }
                       
                    }elseif( $type=='post')
                    {
                        $myTokens=$wpdb->get_results('SELECT pushToken FROM '.$wpdb->prefix.'church_admin_people  WHERE active=1 AND pushToken!="" AND news_send=1');
                        //church_admin_debug $wpdb->last_query);
                        $pushTokens=array();
                        if(!empty( $myTokens) )
                        {
                            foreach( $myTokens AS $myToken)if(!in_array( $myToken->pushToken,$pushTokens) )  {$pushTokens[]=$myToken->pushToken;}
                        }
                        
                    }else 
                    {
                        //send to all app subscribers
                        //$data["to"]="/topics/church".$appID;
                        $myTokens=$wpdb->get_results('SELECT a.pushToken,b.meta_date FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.active=1 AND a.pushToken!="" AND a.people_id=b.people_id AND b.meta_type="news-notifications"');
                        //church_admin_debug $wpdb->last_query);
                        $pushTokens=array();
                        if(!empty( $myTokens) )
                        {
                            foreach( $myTokens AS $myToken)if(!in_array( $myToken->pushToken,$pushTokens) )  {$pushTokens[]=$myToken->pushToken;}
                        }
                        
                    }
                    church_admin_send_push('tokens',$type,$pushTokens,'Our Church App',$title,get_option('blogname'));
				
    	}//only send push if not localhost
		
		/***************************************
		*
		*	Email send
		*
		****************************************/
		if ( empty( $sent) )
        {
            
            church_admin_debug("Preparing to send ".$contactType);
            
            $post_title = get_the_title( $post->ID );
            $post_url = get_permalink( $post->ID );

            $email_title=$title.' - '.$post->post_title;
            $content_post = get_post( $post->ID);
            //church_admin_debug("Post contents************\r\n".print_r( $post,TRUE) );
            $content=church_admin_prepare_post_for_email( $post->post_content,$type,NULL);
          
            //church_admin_debug("***** CONTENT *****");
            //church_admin_debug $content);
            //church_admin_debug("***** END CONTENT *****");
        

            

                $sql='SELECT DISTINCT a.first_name,a.last_name,a.people_id,a.email FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="'.esc_sql( $type).'"  AND a.email!="" AND email_send!=0 AND gdpr_reason!=""';
                //church_admin_debug $sql);
                $results=$wpdb->get_results( $sql);
                if(empty($results)){return;}

                $mailersend_api = get_option('church_admin_mailersend_api_key');
                if(!empty($mailersend_api)){
                    
                    //use mailersend bulk method.
                    $recipients = array();
                    foreach( $results AS $row)
                    {
                       $recipients[]=array('name'=>church_admin_formatted_name($row),'first_name'=>$row->first_name,'email'=>$row->email,'people_id'=>$row->people_id);
                    }
                    church_admin_mailersend_bulk($recipients,$title,'<h2>'.$email_title.'</h2>'.$content,$user->email,$user->name,$user->email,$user->name,null,FALSE);
                }
                else
                {
                    foreach( $results AS $row)
                    {
                        church_admin_email_send($row->email,$title,'<h2>'.$email_title.'</h2>'.$content,$user->name,$user->email,null,null,null);

                    }

                }
               
             
        
            //set sent field in post meta
            update_post_meta( $post->ID, 'Email Sent', 1);
        }
	} //just published
    //church_admin_debug("************ End of church admin publish post function **************");
}

function ca_prayer_create_posttype() {
	$labels = array(
		'name'                => _x( 'Prayer Requests', 'Post Type General Name', 'church-admin' ),
		'singular_name'       => _x( 'Prayer Request', 'Post Type Singular Name', 'church-admin' ),
		'menu_name'           => __( 'Prayer Requests', 'church-admin' ),
		'parent_item_colon'   => __( 'Parent Prayer Request', 'church-admin' ),
		'all_items'           => __( 'All Prayer Requests', 'church-admin' ),
		'view_item'           => __( 'View Prayer Request', 'church-admin' ),
		'add_new_item'        => __( 'Add New Prayer Request', 'church-admin' ),
		'add_new'             => __( 'Add New', 'church-admin' ),
		'edit_item'           => __( 'Edit Prayer Request', 'church-admin' ),
		'update_item'         => __( 'Update Prayer Request', 'church-admin' ),
		'search_items'        => __( 'Search Prayer Requests', 'church-admin' ),
		'not_found'           => __( 'Not Found', 'church-admin' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'church-admin' ),
	);
	$noPrayer=get_option('church-admin-no-prayer');
	if ( empty( $noPrayer) )
	{
		register_post_type( 'prayer-requests',
	// CPT Options
		array(
			'labels' => $labels,
			'public' => true,
			'exclude_from_search'=>true,
			'has_archive' => true,
			'publicly_queryable'=>true,
			'show_ui'=>true,
			'supports' => array( 'thumbnail','title','editor','comments' ),
			'show_in_menu'        => TRUE,
			'show_in_nav_menus'   => TRUE
		)
	);
	}
}
add_action( 'init', 'ca_prayer_create_posttype' );

/****************************************************************************************
*
*  From v2.2520 app content has it's own post type app-content, so create app-content and move content over
*
****************************************************************************************/
add_action( 'init', 'ca_app_content_create_posttype' );
function ca_app_content_create_posttype() {
	 $licence = get_option('church_admin_app_new_licence');
	if(!empty( $licence) && $licence == "premium")
	{
		
		$labels = array(
			'name'                => _x( 'App Content', 'Post Type General Name', 'church-admin' ),
			'singular_name'       => _x( 'App Content', 'Post Type Singular Name', 'church-admin' ),
			'menu_name'           => __( 'App Content', 'church-admin' ),
			'parent_item_colon'   => __( 'Parent App Content', 'church-admin' ),
			'all_items'           => __( 'All App Content', 'church-admin' ),
			'view_item'           => __( 'View App Content', 'church-admin' ),
			'add_new_item'        => __( 'Add New App Content', 'church-admin' ),
			'add_new'             => __( 'Add New', 'church-admin' ),
			'edit_item'           => __( 'Edit App Content', 'church-admin' ),
			'update_item'         => __( 'Update App Content', 'church-admin' ),
			'search_items'        => __( 'Search App Content', 'church-admin' ),
			'not_found'           => __( 'Not Found', 'church-admin' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'church-admin' ),

		);
        $args=array(
			'labels' => $labels,
			'public' => true,
			'exclude_from_search'=>true,
			'has_archive' => true,
			'publicly_queryable'=>true,
			'show_ui'=>true,
			'supports' => array( 'thumbnail','title','editor'),
           	'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
            
			);
        $gutenbergapp=get_option('church_admin_app_gutenberg');
        if(!empty($gutenbergapp))$args['show_in_rest']=true;
		register_post_type( 'app-content',$args);
			
		
		//church_admin_fix_app_default_content();
	}	
}
/****************************
*
*
* Bible Reading Plan
*
*
*****************************/

function ca_bible_reading_create_posttype() {
    $noBible=get_option('church-admin-no-bible-readings');
	if ( empty( $noBible) )
	{
        $labels = array(
            'name'                => _x( 'Bible Readings', 'Post Type General Name', 'church-admin' ),
            'singular_name'       => _x( 'Bible Reading', 'Post Type Singular Name', 'church-admin' ),
            'menu_name'           => __( 'Bible Readings', 'church-admin' ),
            'parent_item_colon'   => __( 'Parent Bible Reading', 'church-admin' ),
            'all_items'           => __( 'All Bible Readings', 'church-admin' ),
            'view_item'           => __( 'View Bible Reading', 'church-admin' ),
            'add_new_item'        => __( 'Add New Bible Reading', 'church-admin' ),
            'add_new'             => __( 'Add New', 'church-admin' ),
            'edit_item'           => __( 'Edit Bible Reading', 'church-admin' ),
            'update_item'         => __( 'Update Bible Reading', 'church-admin' ),
            'search_items'        => __( 'Search Bible Readings', 'church-admin' ),
            'not_found'           => __( 'Not Found', 'church-admin' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'church-admin' ),
            
        );

	register_post_type( 'bible-readings',
	// CPT Options
		array(
          
             'hierarchical'          => false,
			'labels' => $labels,
			'public' => true,
			'exclude_from_search'=>false,
			'has_archive' => true,
			'publicly_queryable'=>true,
			'show_ui'=>true,
			'supports' => array( 'thumbnail','title','editor','comments' ),
			'show_in_menu'        => TRUE,
			'show_in_nav_menus'   => TRUE,
            'show_in_rest' => true,
            'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail','custom-fields', 'comments', 'revisions', 'permalinks', 'featured_image' )
			)
	);
	}
}

add_action( 'init', 'ca_bible_reading_create_posttype' );
/****************************
*
*
* Acts of courage
*
*
*****************************/

function ca_acts_of_courage_create_posttype() {

	$acts=get_option('church-admin-acts-of-courage');
	if( $acts)
	{
		$labels = array(
		'name'                => _x( 'Acts of Courage', 'Post Type General Name', 'church-admin' ),
		'singular_name'       => _x( 'Act of Courage', 'Post Type Singular Name', 'church-admin' ),
		'menu_name'           => __( 'Acts of Courage', 'church-admin' ),
		'parent_item_colon'   => __( 'Parent Act of Courage', 'church-admin' ),
		'all_items'           => __( 'All Acts of Courage', 'church-admin' ),
		'view_item'           => __( 'View Act of Courage', 'church-admin' ),
		'add_new_item'        => __( 'Add New Acts of Courage', 'church-admin' ),
		'add_new'             => __( 'Add New', 'church-admin' ),
		'edit_item'           => __( 'Edit Acts of Courage', 'church-admin' ),
		'update_item'         => __( 'Update Act of Courage', 'church-admin' ),
		'search_items'        => __( 'Search Acts of Courage', 'church-admin' ),
		'not_found'           => __( 'Not Found', 'church-admin' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'church-admin' ),

		);

		register_post_type( 'acts-of-courage',
	// CPT Options
		array(
			'labels' => $labels,
			'public' => true,
			'exclude_from_search'=>false,
			'has_archive' => true,
			'publicly_queryable'=>true,
			'show_ui'=>true,
			'supports' => array( 'thumbnail','title','editor','comments' ),
			'show_in_menu'        => TRUE,
			'show_in_nav_menus'   => TRUE
		)
	);
}
}

add_action( 'init', 'ca_acts_of_courage_create_posttype' );

/****************************
*
*
* Service Planner
* Working behind teh scenes as of v3.8.3
*
*****************************/
/*
function ca_service_create_posttype() {
    $noServicePlanner=get_option('church-admin-no-service-planner');
	if ( empty( $noServicePlanner) )
	{
        $labels = array(
            'name'                => _x( 'Service Planner', 'Post Type General Name', 'church-admin' ),
            'singular_name'       => _x( 'Service plan', 'Post Type Singular Name', 'church-admin' ),
            'menu_name'           => __( 'Service plans', 'church-admin' ),
            'parent_item_colon'   => __( 'Parent Service Plan', 'church-admin' ),
            'all_items'           => __( 'All Service Plans', 'church-admin' ),
            'view_item'           => __( 'View Service Plan', 'church-admin' ),
            'add_new_item'        => __( 'Add New Service Plan', 'church-admin' ),
            'add_new'             => __( 'Add New', 'church-admin' ),
            'edit_item'           => __( 'Edit Service Plan', 'church-admin' ),
            'update_item'         => __( 'Update Service Plan', 'church-admin' ),
            'search_items'        => __( 'Search Service Plans', 'church-admin' ),
            'not_found'           => __( 'Not Found', 'church-admin' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'church-admin' ),

        );

        register_post_type( 'service-plans',
        // CPT Options
            array(
                'labels' => $labels,
                'public' => true,
                'exclude_from_search'=>false,
                'has_archive' => true,
                'publicly_queryable'=>true,
                'show_ui'=>true,
                'supports' => array( 'thumbnail','title','editor','comments' ),
                'show_in_menu'        => TRUE,
                'show_in_nav_menus'   => TRUE,
                'show_in_rest' => true,
                'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail','custom-fields', 'comments', 'revisions', 'permalinks', 'featured_image' )
                )
        );
        }
}


*/



/**

* Adds a meta box to the post editing screen

*/

function ca_brp_custom_meta() {

    add_meta_box( 'ca_brp_meta', __( 'Scripture', 'church-admin' ), 'ca_brp_meta_callback', 'bible-readings','normal','high' );

}

add_action( 'add_meta_boxes', 'ca_brp_custom_meta' );

add_action('edit_form_after_title',  'ca_move_metabox_after_title'  );

 

function ca_move_metabox_after_title () {

    global $post, $wp_meta_boxes;

    do_meta_boxes( get_current_screen(), 'after_title', $post );

    unset( $wp_meta_boxes[get_post_type( $post )]['after_title'] );

}
/**
 * Outputs the content of the meta box
 */
function ca_brp_meta_callback( $post ) 
{
    wp_nonce_field( basename( __FILE__ ), 'ca_brp__nonce' );
    $stored_meta = get_post_meta( $post->ID ,'bible-passage',TRUE);
    ?>

    <p>
        <label for="meta-text" class="ca_brp_-row-title"><?php _e( 'Bible Passage', 'church-admin' )?></label>
        <input type="text" name="meta-text" class="large-text" id="meta-text" value="<?php if ( isset ( $stored_meta ) ) echo esc_attr($stored_meta); ?>" />
    </p>

    <?php
}

/**
 * Saves the custom meta input
 */
function ca_brp__meta_save( $post_id ) {

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'ca_brp__nonce' ] ) && wp_verify_nonce( $_POST[ 'ca_brp__nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

   
        $passage = !empty($_POST['meta-text']) ? church_admin_sanitize($_POST['meta-text']) : null;
        if (empty($passage)) return;

        update_post_meta( $post_id, 'bible-passage', esc_attr($passage) );
    

}
add_action( 'save_post', 'ca_brp__meta_save' );

// Add the custom columns to the bible-readings post type:
add_filter( 'manage_bible-readings_posts_columns', 'church_admin_set_custom_bible_readings_columns' );
function church_admin_set_custom_bible_readings_columns( $columns) 
{
   
   
    $newcolumns=array() ;
    foreach( $columns as $key=>$value) {
        if( $key=='date') 
        {  // when we find the date column
            
           $newcolumns['passage'] = __( 'Bible passage', 'church-admin' ); 
           $newcolumns['thumbnail'] = __( 'Featured Image', 'church-admin' );
           
               
            
        }  
        $newcolumns[$key]=$value;
    }   
   
     return $newcolumns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_bible-readings_posts_custom_column' , 'church_admin_custom_bible_readings_column', 10, 2 );
function church_admin_custom_bible_readings_column( $column, $post_id ) {
    switch ( $column ) {

        case 'passage' :
            $passages=get_post_meta( $post_id,'bible-passage',true);
            if ( is_string( $passages ) )
                echo esc_html( $passages);
            else
                _e( 'Unable to get passage(s)', 'church-admin' );
        break;
        case'thumbnail':
            if(has_post_thumbnail( $post_id) )
            {
                echo get_the_post_thumbnail( $post_id, 'ca-address-thumb' );
            }
            else
            {
                _e('No featured image','church-admin');
            }
        break;
        case 'resend':
            echo '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=resend-bible-reading&ID='.(int)$post_id,'resend-bible-reading').'">'.esc_html( __('Resend','church-admin' ) ).'</a>';
            
        break;
    }
}

function ca_bible_reading_passage( $content ) {

    //this function prepends the passage to content for bible readings
	global $post;
    if ( is_single() && 'bible-readings' == get_post_type() ) {
        $custom_content='';
        $version=get_option('church_admin_bible_version');
        if(empty($version)){
            $version='ESV';
        }
        $passages=explode(",",get_post_meta( $post->ID ,'bible-passage',TRUE) );
        if(!empty( $passages) )
        {
           // //church_admin_debugprint_r( $passages,true) );
            foreach( $passages AS $key=>$passage)
            {
                $sanitizedPassage = church_admin_bible_audio_link( $passage, $version );
                $dayNo=get_the_date('z')+1;
                $custom_content.='<div class="ca-bible-date">'.get_the_date().' '.esc_html( __('Day','church-admin' ) ).' '.$dayNo.'</div>';
                if(!empty( $passage) )
                {
                    $custom_content .= '<p class="ca-bible-reading"><a href="https://www.biblegateway.com/passage/?search='.urlencode( $sanitizedPassage['passage']).'&version='.urlencode( $version).'&interface=print" target="_blank" >'.esc_html( $sanitizedPassage['passage']).'</a></p>';
                    $headphonesSVG='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6 23v-11c-4.036 0-6 2.715-6 5.5 0 2.807 1.995 5.5 6 5.5zm18-5.5c0-2.785-1.964-5.5-6-5.5v11c4.005 0 6-2.693 6-5.5zm-12-13.522c-3.879-.008-6.861 2.349-7.743 6.195-.751.145-1.479.385-2.161.716.629-5.501 4.319-9.889 9.904-9.889 5.589 0 9.29 4.389 9.916 9.896-.684-.334-1.415-.575-2.169-.721-.881-3.85-3.867-6.205-7.747-6.197z" /></svg>';
                   
                    //church_admin_debugprint_r( $bibleCV,true) );
                    if(!empty( $bibleCV['url'] ) )$custom_content.='<p><a href="'.$sanitizedPassage['url'].'">'.$headphonesSVG.' '.$sanitizedPassage['linkText'].'</a></p>';
                }
            }
            $custom_content .= $content;
        }
        return $custom_content;
    } else {
        return $content;
    }
}
add_filter( 'the_content', 'ca_bible_reading_passage' );
/****************************
*
*
* Ajax operations
*
*
*****************************/


add_action('wp_ajax_church_admin_calendar_date_display','church_admin_date');
add_action('wp_ajax_nopriv_church_admin_calendar_date_display', '');

/**
 *
 * Ajax image upload
 *
 * @author  Andy Moyle
 * @param    null
 * @return   html
 * @version  0.1
 *
 */
add_action('wp_ajax_church_admin_image_upload','church_admin_image_upload');
add_action('wp_ajax_nopriv_church_admin_image_upload', 'church_admin_image_upload');
function church_admin_image_upload()
{
	//church_admin_debug("********************\r\nAJAX Image upload");
	// These files need to be included as dependencies when on the front end.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	// Let WordPress handle the upload.
	// Remember, 'my_image_upload' is the name of our file input in our form above.
	$attachment_id = media_handle_upload( 'file-0', 0 );
	//church_admin_debug("attachment_id: ".$attachment_id);
	if ( is_wp_error( $attachment_id ) ) {
		exit();
	} else {
		// The image was uploaded successfully!
		$image=wp_get_attachment_image_src(  $attachment_id, "thumbnail", false );
		//church_admin_debugprint_r( $image,TRUE) );
		echo json_encode(array('src' => esc_url( $image[0] ), 'attachment_id' => (int)$attachment_id ) );
		exit();
	}
		
	

}

/**
 *
 * Popup of calendar events
 *
 * @author  Andy Moyle
 * @param    null
 * @return   html
 * @version  0.1
 *
 */
add_action('wp_ajax_church_admin_calendar_event_display','church_admin_calendar_event_display');
add_action('wp_ajax_nopriv_church_admin_calendar_event_display', 'church_admin_calendar_event_display');
function church_admin_calendar_event_display()
{
	//church_admin_debug('Calendar Event' .date('Y-m-d h:i:s') );
	global $wpdb;
	$date_sql=1;
	$out='';
	$dates=explode(',',$_POST['date'] );
    foreach( $dates AS $key=>$value)  { $datesql[]='a.start_date="'.esc_sql( $value).'"';}
    if(!empty( $datesql) ) {
        $date_sql=' ('.implode(' || ',$datesql).')';
    }else{ 
        echo esc_html( __( 'No event to show', 'church-admin' ) );
        exit();
    }

	$sql='SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a LEFT JOIN '.$wpdb->prefix.'church_admin_calendar_category b ON b.cat_id = a.cat_id WHERE '.esc_sql( $date_sql );


	$result=$wpdb->get_results( $sql);

	if(!empty( $result) )
	{
		foreach( $result AS $row)
		{
			$out.='<div class="ca-event ">';
			$out.='<span class="ca-close">x</span>';
			$out.='<h2 style="color:'.esc_html( $row->bgcolor).'">'.esc_html( $row->title).'</h2>';
			$out.='<p>'.esc_html( mysql2date( get_option('date_format'), $row->start_date) ).' '.esc_html( mysql2date(get_option('time_format'),$row->start_time) ).' -  '.esc_html( mysql2date(get_option('time_format'),$row->end_time) ).'</p>';
			if(!empty( $row->description) )$out.='<p>'.esc_html( $row->description ).'</p>';
			if(!empty( $row->page_id) )$out.='<p><a href="'.get_permalink( $row->page_id ).'">'.esc_html( __('More information','church-admin' ) ).'</p>';
			if(!empty( $row->booking_id) )$out.='<p><a class="button-primary" href="'.esc_url( get_permalink( $row->booking_id) ).'">'.esc_html( __('Book Now', 'church-admin' ) ).'</p>';
			$out.='</div>';
		}
	}
	else
	{
		$out= esc_html( __('No event to show','church-admin') );
	}
	echo json_encode(array('id'=>esc_html( sanitize_text_field(stripslashes($_POST['date'] ) ) ),'output'=>$out) );
	exit();
}



/****************************************
 * MAIN AJAX HANDLER
 *****************************************/

add_action('wp_ajax_church_admin','church_admin_ajax_handler');
add_action('wp_ajax_nopriv_church_admin', 'church_admin_ajax_handler');

function church_admin_ajax_handler()
{

	global $wpdb;
	$current_user = wp_get_current_user();	
		switch ( $_REQUEST['method'] )
		{

            
           
            case 'kiosk-app-delete':
                church_admin_debug('**** kiosk-app-delete ****');
                check_ajax_referer('kiosk-app-ajax','nonce');
                church_admin_debug($_POST);
                $ID=church_admin_sanitize($_POST['id']);
                if(empty($ID)){return; exit();}
                $display_fields = get_option('church_admin_kiosk_app_form');
                church_admin_debug($display_fields);
                if(!empty($display_fields[$ID])){
                    unset($display_fields[$ID]);
                    
                    update_option('church_admin_kiosk_app_form',$display_fields);
                }
                echo (int)$ID;
                exit();
            break;
            case 'kiosk_form_order':
                check_ajax_referer('kiosk-app-ajax','nonce');
                if(!empty( $_POST['order'] ) )
				{
					$order=explode(",",church_admin_sanitize($_POST['order']) );
					$saved_fields=get_option('church_admin_kiosk_app_form');
					foreach( $order AS $key=>$name)
					{
						//church_admin_debug("Handling $name and giving it order of $key");
						if(!empty( $saved_fields[$name] ) )$saved_fields[$name]['order']=(int)$key;
					}
					
					update_option('church_admin_kiosk_app_form',$saved_fields);
				}

            break;
            case'pastoral-list-remove':
                check_ajax_referer('pastoral-list-remove','nonce');
                $people_id=!empty($_POST['people_id'])?church_admin_sanitize($_POST['people_id'] ) :exit();
                $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="pastoral-visit-required"');
                echo (int)$people_id;
                exit();
            break;
            case 'add-to-group':
                check_ajax_referer('add-to-group','nonce');
                //church_admin_debug('*** Add to group AJAX ****');
                //sanitize
                $people_id=!empty($_POST['people_id'])?sanitize_text_field(stripslashes($_POST['people_id'] ) ):exit();
                $group_id = !empty($_POST['group_id'])?sanitize_text_field(stripslashes($_POST['group_id'] ) ):exit();
                //validate
                if(!church_admin_int_check($people_id)){
                    //church_admin_debug('Invalid people_id');
                    exit();
                }
                if(!church_admin_int_check($group_id)){
                    //church_admin_debug('Invalid group_id');
                    exit();
                }
                //check
                $checked_people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
                if(empty($checked_people_id)){
                    //church_admin_debug('People_id not in DB');
                    exit();
                }
                $groups=church_admin_groups_array();
                if(empty($groups[$group_id])){
                    //church_admin_debug('Group not recognised');
                    exit();
                }

                //safe to proceed
                //delete persons current group
                $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="smallgroup"');
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,people_id,ID,meta_date) VALUES("smallgroup","'.(int)$people_id.'","'.(int)$group_id.'","'.wp_date('Y-m-d').'")');
                //send out put back
                
                $output=array('people_id'=>$people_id);
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                echo json_encode( $output);
                die();

            break;
            case 'sermon_length':
                //church_admin_debug('Sermon length AJAX');
                check_ajax_referer('sermon_length','nonce');
                $file_id=(int)$_REQUEST['id'];
                $length=sanitize_text_field(stripslashes($_REQUEST['length'] ) );
                $regex='/(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]/';
                $match=preg_match($regex,$length);
                //church_admin_debug("match - ".$match);
                if(!empty($match)){
                    $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_files SET length="'.esc_sql($length).'" WHERE file_id="'.(int)$file_id.'"');
                    //church_admin_debug($wpdb->last_query);
                }
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                if($wpdb->rows_affected==1){
                    //translators: %1$s is a time of the recording
                    echo esc_html(sprintf(__('%1$s updated','church-admin' ) , $length));
                }
                exit();
            break;
            

            case 'facility-booking':
                check_ajax_referer('facility-booking','nonce');
                //church_admin_debugprint_r( $_POST,TRUE) );
                
                //sanitize
                $facilities_id=!empty($_POST['facilities_id'])?sanitize_text_field(stripslashes($_POST['facilities_id'])):null;
                $startDate=!empty($_POST['facilities_id'])?sanitize_text_field(stripslashes( $_POST['start_date'] )):null;
                $startTime=!empty( $_POST['start_time'] )?sanitize_text_field(stripslashes( $_POST['start_time'] )):null;
                $endTime=!empty( $_POST['end_time'] )?sanitize_text_field(stripslashes( $_POST['start_time'] )):null;

                //validate
                if(empty($facilities_id)||!church_admin_int_check($facilities_id)){exit();}
                if(empty($start_date)||!church_admin_checkdate($start_date)){exit();}
                if(empty($startTime) || !preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9]?$#', $startTime)){exit();}
                if(empty($endTime) || !preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9]?$#', $endTime)){exit();}

                $check=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE facilities_id="'.(int)$facilities_id.'" AND 
                (startTime BETWEEN "'.esc_sql($startTime).'" AND "'.esc_sql($endTime).'") OR
                (endTime BETWEEN "'.esc_sql($startTime).'" AND "'.esc_sql($endTime).'") OR
                (startTime BETWEEN "'.esc_sql($startTime).'" AND "'.esc_sql($endTime).'" AND endTime BETWEEN "'.esc_sql($startTime).'" AND "'.esc_sql($endTime).'") OR
                (startTime<="'.esc_sql($startTime).'" AND endTime>="'.esc_sql($endTime).'")');
                
                if ( empty( $check) )  {
                    $out=array('message'=> '<span style="color:green;">'. esc_html(__('Date and time are  available','church-admin')).'</span>','available'=>1);
                }else{$out=array('message'=>'<span style="color:red;">'. esc_html( __('Date and time are NOT available','church-admin') ).'</span>','available'=>0);}
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                echo json_encode( $out);
                die();
            break;  
            case 'update-site':
                check_ajax_referer('update-site','nonce');
                //sanitize
                $ID=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;
                $div=!empty($_POST['div'])?sanitize_text_field( stripslashes( $_POST['div'] ) ):null;
                $lat = !empty($_POST['lat'])?sanitize_text_field( stripslashes( $_POST['lat'] ) ):null;
                $lng = !empty($_POST['lng'])?sanitize_text_field( stripslashes( $_POST['lng'] ) ):null;

                //validate 
                $errors = array();
                if(empty($ID) || !church_admin_int_check($ID)){
                    $errors[] = __('Invalid site ID','church-admin');
                }
                if(empty($lat) || !church_admin_validate_latitude($lat)){
                    $errors[] = __('Invalid latitude','church-admin');
                }
               if(empty($lat) || !church_admin_validate_longitude($lng)){
                $errors[] = __('Invalid longitude','church-admin');
                }

                if(!empty($errors) )
                {
                    $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sites SET lat="'.esc_sql( $lat ).'",lng="'.esc_sql( $lng ).'" WHERE  site_id="'.(int)$ID.'"');
                    //church_admin_debug $wpdb->last_query);
                }   
                else
                {
                    $output = array('errors' => esc_html(implode(", ",$errors)) );
                }     
            break;

            case 'update-directory':
                //church_admin_debugprint_r( $_POST,TRUE) );
              
                check_ajax_referer('update-directory','nonce');
                $ID=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;
                if(empty($ID)||!church_admin_int_check($ID)){exit("Invalid ID");}
                $div=!empty($_POST['div'])?sanitize_text_field( stripslashes( $_POST['div'] ) ):null;
                $what=!empty($_POST['what'])?sanitize_text_field( stripslashes( $_POST['what'] ) ):null;
                $custom_id=!empty($_POST['custom-id'])?sanitize_text_field(stripslashes($_POST['custom-id'])):null;
                $lat=!empty($_POST['lat'])?sanitize_text_field(stripslashes($_POST['lat'])):null;
                $lng=!empty($_POST['lng'])?sanitize_text_field(stripslashes($_POST['lng'])):null;             
                if(!empty( $_POST['value'] ) )$value=church_admin_sanitize($_POST['value'] ) ;
                if(!empty($value) && is_array($value)){$value=maybe_serialize($value);}
                //validate
                
                $errors = array();
                if(empty($ID) || !church_admin_int_check($ID)){
                    $errors[] = __('Invalid site ID','church-admin');
                }
                if(!empty($lat) && !church_admin_validate_latitude($lat)){
                    $errors[] = __('Invalid latitude','church-admin');
                }
               if(!empty($lat) && !church_admin_validate_longitude($lng)){
                $errors[] = __('Invalid longitude','church-admin');
                }

                if(!empty($errors))
                {
                    exit();
                }
                switch( $what)
                {
                    case 'wedding_anniversary':
                        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET wedding_anniversary="'.esc_sql( $value).'" WHERE household_id="'.(int)$ID.'"');
                    break;
                    case 'member_type_id':
                        if(!empty( $ID) && !empty( $value) ) $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET member_type_id="'.(int)$value.'" WHERE people_id="'.(int)$ID.'"');
                        //church_admin_debug $wpdb->last_query);
                        $mtOut=church_admin_member_type_option( $value);
                    break;
                    case 'people_type_id':
                        if(!empty( $ID) && !empty( $value) ) $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET people_type_id="'.(int)$value.'" WHERE people_id="'.(int)$ID.'"');
                        //church_admin_debug $wpdb->last_query);
                    break;
                    case 'household-custom':
                        
                        $metaData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_custom_fields_meta WHERE section="household" AND custom_id="'.(int)$custom_id.'" AND household_id="'.(int)$ID.'"');
                        //church_admin_debug($wpdb->last_query);
                        if( !empty($metaData->ID))
                        {
                            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_custom_fields_meta SET section="household", data="'.esc_sql($value).'" , household_id="'.(int)$ID.'"  WHERE ID="'.(int)$metaData->ID.'"' );
                        }
                        else
                        {
                            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_custom_fields_meta (custom_id,household_id,data,section) VALUES("'.esc_sql($custom_id).'","'.(int)$ID.'","'.esc_sql($value).'","household")');
                        }
                        //church_admin_debug($wpdb->last_query);
                    break;

                    case 'phone':$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET phone="'.esc_sql( $value).'" WHERE household_id="'.(int)$ID.'"');break;
                    case 'address':$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET address="'.esc_sql( $value).'" WHERE household_id="'.(int)$ID.'"');break;
                    case 'mailing-address':$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET mailing_address="'.esc_sql( $value).'" WHERE household_id="'.(int)$ID.'"');break;
                    case 'email':$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET email="'.esc_sql( $value).'" WHERE people_id="'.(int)$ID.'"');break;
                    case 'mobile':case 'cell':
                        
                        $e164cell=!empty( $value)?church_admin_e164( $value):'';
                        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET mobile="'.esc_sql( $value).'", e164cell="'.esc_sql( $e614cell).'" WHERE people_id="'.(int)$ID.'"');
                        
                    break;


                    case 'geocode':
                        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET geocoded=1, lat="'.esc_sql($lat ).'",lng="'.esc_sql($lng ).'" WHERE  household_id="'.(int)$ID.'"');
                        
                    break;
                }
                //church_admin_debug($wpdb->last_query);
                //reset app address list cache
                delete_option('church_admin_app_address_cache');
                delete_option('church_admin_app_admin_address_cache');
                
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                $outputArray=array();
                if(!empty( $div) ) $outputArray['div']=esc_html( $div);
                if(!empty( $mtOut) )$outputArray['mtout']=$mtOut;
                echo json_encode( $outputArray);
              
                exit();
            break;  
            case 'smallgroup-map-geocode':
                //church_admin_debug('Small group map geocode');
                check_ajax_referer('smallgroup-map-geocode','nonce');
                //church_admin_debugprint_r( $_POST,TRUE) );
                //sanitize
                $lat = !empty($_POST['lat'])?sanitize_text_field( stripslashes( $_POST['lat'] ) ):null;
                $lng = !empty($_POST['lng'])?sanitize_text_field( stripslashes( $_POST['lng'] ) ):null;
                $id=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;

                //validate
                $errors=array();
                if(empty($id) || !church_admin_int_check($id)){
                    $errors[] = __('Invalid small group','church-admin');
                }
                if(empty($lat) || !church_admin_validate_latitude($lat)){
                    $errors[] = __('Invalid latitude','church-admin');
                }
               if(empty($lat) || !church_admin_validate_longitude($lng)){
                $errors[] = __('Invalid longitude','church-admin');
                }
                if(empty( $errors ))
                {
                    $sql='UPDATE '.$wpdb->prefix.'church_admin_smallgroup SET lat="'.esc_sql($lat ).'",lng="'.esc_sql($lng).'" WHERE  id="'.(int)$id.'"';
                    //church_admin_debug $sql);
                    $wpdb->query( $sql);
                    $output = array('lat'=>esc_attr($lng),'lng'=>esc_attr( $lng ));
                }
                else
                {
                    $output = array('errors' => esc_html(implode(", ",$errors)) );
                }
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                echo json_encode($output);
                exit();
            break;
            case 'ignore-last-name':
                check_ajax_referer('ignore-last-name','nonce');
                if(!empty( $_POST['household_id'] ) )
                {
                    $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET ignore_last_name_check=1 WHERE household_id="'.(int)$_POST['household_id'].'"');
                    header('Access-Control-Max-Age: 1728000');
                    header('Access-Control-Allow-Origin: *');
                    header('Access-Control-Allow-Methods: *');
                    header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                    header('Access-Control-Allow-Credentials: true');
                    echo (int)$_POST['household_id'];
                    die();
                }
            break;

            
            case 'show-help':
                //church_admin_debug $_REQUEST["which"] );
                require_once( plugin_dir_path( __FILE__ ).'includes/helper.php');
                $out=church_admin_helper( $_REQUEST["which"] );
                if ( empty( $out) )$out='';
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                echo $out;
                die();
            break;
            case'dismissed-notice-handler':
                church_admin_debug('**** dismissed-notice-handler ******');
                church_admin_debug($_REQUEST);

                check_ajax_referer('dismissed-notice-handler','nonce');
                //church_admin_debug('Dismiss notice');
                switch( $_REQUEST['type'] )
                {
                    case 'small-churches-offer-dismissed':update_option('dismissed-small-churches-offer-dismissed',TRUE);break;
                    case 'church-admin-free-version': update_option('dismissed-church-admin-free-version',TRUE);break;
                    case 'church-admin-roles-permissions': update_option('dismissed-church-admin-roles-permissions',TRUE);break;
                    case 'dismissed-church-admin-please-review':update_option('dismissed-church-admin-please-review',TRUE);break;
                    case "church-admin-email-settings":update_option('dismissed-church-admin-email-settings',TRUE);break;
                    case "church-admin-set-sermon-page":update_option('dismissed-church-admin-set-sermon-page',TRUE);break;
                    case "church-admin-bible-version":update_option('dismissed-church-admin-bible-version',TRUE);break;
                    case "church-admin-app":update_option( 'dismissed-church-admin-app', TRUE );break;
                    case "church-admin-gdpr":update_option( 'dismissed-church-admin-gdpr', TRUE );break;
                    case "church-admin-cron"://church_admin_debug('cron dismissed');update_option( 'dismissed-church-admin-cron', TRUE );echo'Dismissed';break;
                }
                exit();
            break;
            
            case 'refresh-sms':
                check_ajax_referer('refresh-sms','nonce');
                $out='';
                $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_twilio_messages WHERE mobile="'.esc_sql( $_POST['e164cell'] ).'" AND direction=0 AND message_id>"'.(int)$_POST['lastID'].'" ORDER BY message_date ASC';
               
                $results=$wpdb->get_results( $sql);
                if(!empty( $results) )
                {
                    foreach( $results AS $row)
                    {
                        //church_admin_debugprint_r( $row,TRUE) );
                        $out.='<div class="ca-message-blue"><div class="ca-message-content">';
                        $out.= '<div class="ca-message-content">'.esc_html( $row->message).'</div>';
                        $out.='<div class="ca-message-timestamp-left">'.mysql2date(get_option('date_format').' '.get_option('time_format'),$row->message_date).'</div></div>';
                        
                    }
                    $id=$row->message_id;
                }
                $output=array('messages'=>$out,'id'=>$id);
                //church_admin_debugprint_r( $output,TRUE) );
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                echo json_encode( $output);
                die();
            break;
            case 'refresh-sms-replies':
                check_ajax_referer('refresh-sms-replies','nonce');
                require_once(plugin_dir_path( __FILE__) .'/includes/twilio.php');
                $out=church_admin_sms_replies_list();
                
                header('Access-Control-Max-Age: 1728000');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: *');
                header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
                header('Access-Control-Allow-Credentials: true');
                echo json_encode( $out);
                die();
            break;    
            case 'remove-from-favourites':
                //church_admin_debugprint_r( $_POST,TRUE) );
                check_ajax_referer('remove-from-favourites','nonce');
                $user_id=(int)$_POST['user_id'];
                $what=$_POST['what'];
                $favourites=get_option('church-admin-favourites');
                $userFavourites=$favourites[$user_id];
                $key = array_search( $what, $userFavourites);
               if ( $key !== false)
                {
                    //church_admin_debug $key);
                    unset( $favourites[$user_id][$key] );
                }
                update_option('church-admin-favourites',$favourites);
                church_admin_favourites_menu( $user_id);
                exit();
            break;
            case 'add-to-favourites':
                check_ajax_referer('add-to-favourites','nonce');
                $user_id=(int)$_POST['user_id'];
                $what=$_POST['what'];
                if( $what=='favourites')exit();
                $favourites=array();
                $favourites=get_option('church-admin-favourites');
                if(in_array( $what,$favourites[$user_id] ) )exit();
                $favourites[$user_id][]=$what;
                update_option('church-admin-favourites-v2',$favourites);
                church_admin_favourites_menu( $user_id);
                exit();
            break;
            case 'email-checker':
                check_ajax_referer('email-checker','nonce');
                $email=!empty($_POST['email'] )?sanitize_text_field( stripslashes($_POST['email'] )):null;
                $id=!empty($_REQUEST['id'])?sanitize_text_field(stripslashes($_REQUEST['id'])):null;
                //church_admin_debug('Email checker :'.$email);
                if(!empty($email) && is_email( $email) && email_exists( $email) )
                {
                    $response=array('found'=>TRUE,'email'=>$email);
                    if(!empty( $id ) )$response['id']=(int)$id;
                
                }else {
                    $response=array('found'=>FALSE,'nonce'=>wp_create_nonce('second-step'),'email'=>$email);
                    if(!empty( $id ) )$response['id']=(int)$id;
                }
                echo json_encode( $response);
                exit();
            break;
            case 'remove-series-image':
                check_ajax_referer('remove-series-image');
                //sanitize
                $series_id=!empty($_REQUEST['series_id'])?sanitize_text_field(stripslashes($_REQUEST['series_id'])):null;
                //validate
                if(!church_admin_int_check($series_id)){exit();}
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_services SET attachment_id="" WHERE series_id="'.(int)$series_id.'"');
                wp_delete_attachment( $series_id );
                exit();
            break;
            case 'calendar-render':
                check_ajax_referer('calendar','nonce');
                require_once(plugin_dir_path( __FILE__) .'/display/calendar.new.php');
                $date = !empty($_REQUEST['date'] )?sanitize_text_field(stripslashes($_REQUEST['date'] )):wp_date('Y-m-d');
                if(!church_admin_checkdate($date)){$date=wp_date('Y-m-d');}
                
                $d=explode("-",$date);
                $facilities_id=!empty($_REQUEST['fac_ids'])?sanitize_text_field(stripslashes($_REQUEST['fac_ids'])):null;
                $cat_id=!empty($_REQUEST['cat_id'])?sanitize_text_field(stripslashes($_REQUEST['cat_id'])):null;
                church_admin_render_month( $d[1],$d[0],$d[2],$cat_id,$facilities_id);
                exit();
            break;
            case 'calendar-day-render':
                check_ajax_referer('calendar','nonce');
                require_once(plugin_dir_path( __FILE__) .'/display/calendar.new.php');
                $date = !empty($_REQUEST['date'] )?sanitize_text_field(stripslashes($_REQUEST['date'] )):wp_date('Y-m-d');
                if(!church_admin_checkdate($date)){$date=wp_date('Y-m-d');}
                $facilities_id=!empty($_REQUEST['facilities_id'])?sanitize_text_field(stripslashes($_REQUEST['facilities_id'])):null;
                $cat_id=!empty($_REQUEST['cat_id'])?sanitize_text_field(stripslashes($_REQUEST['cat_id'])):null;
                church_admin_render_day( $date,$cat_id,$facilities_id );
                
                exit();
            break;
            
            case 'edit-app-menu':
                check_ajax_referer('edit-app-menu','nonce');
                $chosenMenu=get_option('church_admin_app_new_menu');
                $menuItem=sanitize_text_field( stripslashes($_POST['menuItem'] ) );
                $menuTitle=sanitize_text_field(stripslashes( $_POST['menuTitle'] ));
                if(!empty( $chosenMenu[$menuItem] ) )
                {
                    $chosenMenu[$menuItem]['item']=$menuTitle;
                }
                update_option('church_admin_app_new_menu',$chosenMenu);
                echo '<span class="ca-editable" data-item="'.$menuItem.'">'.$menuTitle.'</span>';
                exit();
            break;
            case 'app-menu-show':
                check_ajax_referer('edit-app-menu','nonce');
                $chosenMenu=get_option('church_admin_app_new_menu');
                //church_admin_debugprint_r( $chosenMenu,TRUE) );
                $menuItem=sanitize_text_field( stripslashes($_POST['menuItem'] ));
                $status=$_POST['status'];
                if( $status=="ON")
                {
                    $chosenMenu[$menuItem]['show']=1;
                }else  $chosenMenu[$menuItem]['show']=0;
                update_option('church_admin_app_new_menu',$chosenMenu);
                //church_admin_debugprint_r( $chosenMenu,TRUE) );
                echo'DONE';
                exit();
            break;    
            case 'app-menu-login':
                check_ajax_referer('edit-app-menu','nonce');
                $chosenMenu=get_option('church_admin_app_new_menu');
                //church_admin_debugprint_r( $chosenMenu,TRUE) );
                $menuItem=sanitize_text_field( stripslashes($_POST['menuItem'] ) );
                $status=sanitize_text_field( stripslashes($_POST['status']));
                if( $status=="ON")
                {
                    $chosenMenu[$menuItem]['loggedinOnly']=1;
                }else  $chosenMenu[$menuItem]['loggedinOnly']=0;
                update_option('church_admin_app_new_menu',$chosenMenu);
                //church_admin_debugprint_r( $chosenMenu,TRUE) );
                echo'DONE';
                exit();
            break;     
            case 'add-ticket':
                check_ajax_referer('add-ticket','nonce');
                require_once(plugin_dir_path( __FILE__) .'/includes/events.php');
                $x=sanitize_text_field( stripslashes($_REQUEST['id']));
                
                echo church_admin_event_ticket_form(NULL,$x,FALSE);
                
                exit();
            break;
            case 'event-ticket':
                check_ajax_referer('event-ticket','nonce');
                require_once(plugin_dir_path( __FILE__) .'/display/events.php');
                $x=sanitize_text_field( stripslashes($_REQUEST['id']));
                $event_id=!empty($_REQUEST['event_id'])?sanitize_text_field(stripslashes($_REQUEST['event_id'])):null;
                if(empty($event_id)||!church_admin_int_check($event_id)){exit();}
                $ticket=church_admin_front_end_ticket( (int)$event_id,$x,TRUE);
                echo wp_kses_post($ticket['output']);
                exit();
            break;
            case 'event-booking':
                check_ajax_referer('event-booking','nonce');
                require_once(plugin_dir_path( __FILE__) .'/display/events.php');
                
                $booking_ref =  church_admin_save_event_booking();
                //church_admin_debug $booking_ref);
                //get cost
                $cost=$wpdb->get_var('SELECT SUM(a.ticket_price) FROM '.$wpdb->prefix.'church_admin_tickets a, '.$wpdb->prefix.'church_admin_bookings b WHERE a.ticket_id=b.ticket_type AND b.booking_ref="'.esc_sql( $booking_ref).'"');
                //church_admin_debug $wpdb->last_query);
                //church_admin_debug $cost);
                header('Access-Control-Max-Age: 1728000');
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: *');
				header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
				header('Access-Control-Allow-Credentials: true');
                $output=json_encode(array('booking_ref'=>$booking_ref,'cost'=>$cost) );
                //church_admin_debug $output);
                echo $output;
                exit();
                exit();
            break;
                
            case 'assign_funnel':
				check_admin_referer('assign_funnel','nonce');
                //sanitize
                $funnel_id=!empty($_REQUEST['funnel_id']) ? sanitize_text_field( stripslashes( $_REQUEST['funnel_id'] ) ):null;
                $people_id=!empty($_REQUEST['people_id']) ? sanitize_text_field( stripslashes( $_REQUEST['people_id'] ) ):null;
                $assign_id=!empty($_REQUEST['assign_id']) ? sanitize_text_field( stripslashes( $_REQUEST['assign_id'] ) ):null;
                $member_type_id=!empty($_REQUEST['member_type_id']) ? sanitize_text_field( stripslashes( $_REQUEST['member_type_id'] ) ):null;
                //validate
                if(empty($funnel_id) || !church_admin_int_check($funnel_id)){exit();}
                if(empty($people_id) || !church_admin_int_check($people_id)){exit();}
                if(empty($assign_id) || !church_admin_int_check($assign_id)){exit();}
                if(empty($member_type_id) || !church_admin_int_check($member_type_id)){exit();}

				$check=$wpdb->get_var('SELECT id FROM '.$wpdb->prefix.'church_admin_follow_up WHERE funnel_id="'.(int)$funnel_id.'" AND people_id="'.(int)$people_id.'" AND member_type_id="'.(int)$member_type_id.'" AND assign_id="'.(int)$assign_id.'" AND assigned_date="'.esc_sql(date("Y-m-d")).'"');
				if(!$check)
				{
					$sql='INSERT INTO '.$wpdb->prefix.'church_admin_follow_up' .'(funnel_id,people_id,member_type_id,assign_id,assigned_date,completion_date)VALUES("'.(int)$funnel_id.'","'.(int)$people_id.'","'.(int)$member_type_id.'","'.(int)$assign_id.'","'.esc_sql(date("Y-m-d")).'","0000-00-00")';
					//church_admin_debug $sql);
        			$wpdb->query( $sql);
				}
				//find name of funnel
				$funnel=$wpdb->get_var('SELECT action FROM '.$wpdb->prefix.'church_admin_funnels WHERE funnel_id="'.(int)$funnel_id.'"');
				//church_admin_debug $wpdb->last_query);
				$response=array('people_id'=>(int)$people_id );
				if(!empty( $funnel) )  {
                    //translators: %1$s is a name
                    $response['message']= esc_html(sprintf(__('Assigned to %1$s','church-admin' ) ,$funnel));
                }else{
                    $response['message']=__('Oopsie','church-admin');
                }
				header('Access-Control-Max-Age: 1728000');
				header('Access-Control-Allow-Origin: *');
				header('Access-Control-Allow-Methods: *');
				header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
				header('Access-Control-Allow-Credentials: true');
				echo json_encode( $response);
				exit();
				
			break;
			case 'app_menu_order':
                check_admin_referer('church_admin_app_menu_order','nonce');
				//church_admin_debug("Posted".print_r( $_REQUEST,TRUE) );
				if(!empty( $_POST['order'] ) )
				{
					$order=explode(",",church_admin_sanitize($_POST['order']) );
					$appMenu=get_option('church_admin_app_new_menu');
					foreach( $order AS $key=>$name)
					{
						//church_admin_debug("Handling $name and giving it order of $key");
						if(!empty( $appMenu[$name] ) )$appMenu[$name]['order']=(int)$key;
					}
					//church_admin_debugprint_r( $appMenu,TRUE) );
					update_option('church_admin_app_new_menu',$appMenu);
				}
			break;
			case 'update-oversight':
				check_admin_referer('update-oversight','nonce');
				//church_admin_debugprint_r( $_POST,TRUE) );
                //sanitize
                $name=!empty( $_POST['name'])?sanitize_text_field(stripslashes( $_POST['name'])):null;
                $people=!empty( $_POST['people'])?sanitize_text_field(stripslashes( $_POST['people'])):null;
                $cell_id = !empty( $_POST['cell_id'])?sanitize_text_field(stripslashes( $_POST['cell_id'])):null;
                //validate
                if(empty($name)){exit();}
                if(empty($cell_id)|| ! church_admin_int_check($cell_id)){exit();}


				if(!empty( $name ) )  {$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_cell_structure SET name="'.esc_sql($name ).'" WHERE ID="'.(int)$cell_id.'"');}
				if(!empty( $people ) )
				{
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="oversight" AND ID="'.(int)$cell_id.'"');
					$autocompleted=explode(',',$people );//string with entered names

				foreach( $autocompleted AS $x=>$name)
				{
					$p_id=church_admin_get_one_id(trim( $name) );//get the people_id

					if(!empty( $p_id) )
					{
						church_admin_update_people_meta((int)$cell_id,$p_id,'oversight');//update person as leader at that level
					}
				}
			}
				
			break;
		
			case 'remove-image':
				check_ajax_referer( 'remove-image', 'nonce' );
                $ID=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;
                if(empty($ID))exit();
				switch( $_POST['type'] )
				{
					case'people':$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET attachment_id=NULL WHERE people_id="'.(int)$ID.'"');break;
					case'household':$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET attachment_id=NULL WHERE household_id="'.(int)$ID.'"');break;
				}
                if(is_attachment($ID)){wp_delete_attachment($ID);}
				echo (int)$ID;
				exit();
			break;
			case 'show-person':
				check_ajax_referer( 'show-person', 'security' );
				require_once(plugin_dir_path( __FILE__) .'/display/address-list.php');
				$data= church_admin_people_data((int)$_POST['id'] );
				//church_admin_debugprint_r( $data,TRUE) );

                //sanitize
                $map= !empty($_POST['map'])?sanitize_text_field(stripslashes($_POST['map'])):null;
                $updateable= !empty($_POST['updateable'])?sanitize_text_field(stripslashes($_POST['updateable'])):null;
                $photo = !empty($_POST['photo'])?sanitize_text_field(stripslashes($_POST['photo'])):null;
                $vcf = !empty($_POST['vcf'])?sanitize_text_field(stripslashes($_POST['vcf'])):null;
                $address_style= !empty($_POST['address_style'])?sanitize_text_field(stripslashes($_POST['address_style'])):null;

				echo church_admin_formatted_household( $data,$map,$updateable,$photo,$vcf,$address_style );
				exit();
			break;
            case 'show-personv2':
				check_ajax_referer( 'show-person', 'security' );
				require_once(plugin_dir_path( __FILE__) .'/display/address-list.php');
				$data= church_admin_people_data((int)$_POST['id'] );
				//church_admin_debugprint_r( $data,TRUE) );
				header('Access-Control-Max-Age: 1728000');
		          header('Access-Control-Allow-Origin: *');
		          header('Access-Control-Allow-Methods: *');
		          header('Access-Control-Allow-Headers: Content-MD5, X-Alt-Referer');
		          header('Access-Control-Allow-Credentials: true');
                //sanitize
                $map= !empty($_POST['map'])?sanitize_text_field(stripslashes($_POST['map'])):null;
                $updateable= !empty($_POST['updateable'])?sanitize_text_field(stripslashes($_POST['updateable'])):null;
                $photo = !empty($_POST['photo'])?sanitize_text_field(stripslashes($_POST['photo'])):null;
                $vcf = !empty($_POST['vcf'])?sanitize_text_field(stripslashes($_POST['vcf'])):null; 
                
                $outputData=church_admin_formatted_household( data,$map,$updateable,$photo,$vcf,$address_style );
                echo json_encode(array('householdIndex'=>wp_kses_post($data['household_index']),'id'=>(int)$data['household_id'],'entry'=>wp_kses_post($outputData)) );
				exit();
			break;
			//podcast
			case "podcast-file"://checked
                check_ajax_referer( 'sermon-display', 'nonce' );
				require_once(plugin_dir_path( __FILE__) .'/display/sermon-podcast.php');
				//church_admin_debug('podcast file');
                $ID=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;
				echo church_admin_podcast_file_detail((int)$ID,FALSE);
				exit();
			break;
            case 'dropdown'://checked
                check_ajax_referer( 'sermon-display', 'nonce' );
                if(defined('CA_DEBUG') )
                {
                    //church_admin_debug('FUNCTION church_admin_podcast_files_list');
                    //church_admin_debugprint_r( $_REQUEST,TRUE) );
                }
				require_once(plugin_dir_path( __FILE__) .'/display/sermon-podcast.php');
                $response=array();
                if(defined('CA_DEBUG') )  {
                    //church_admin_debug('Calling church_admin_podcast_file_list');
                }
                //sanitize
                $series_id=!empty($_REQUEST['series_id']) ? sanitize_text_field( stripslashes( $_REQUEST['series_id'] ) ):null;
                $page=!empty($_REQUEST['page']) ? sanitize_text_field( stripslashes( $_REQUEST['page'] ) ):null;
                $limit=!empty($_REQUEST['limit']) ? sanitize_text_field( stripslashes( $_REQUEST['limit'] ) ):null;
                $speaker=!empty($_REQUEST['speaker']) ? sanitize_text_field( stripslashes( $_REQUEST['speaker'] ) ):null;
                $order =!empty($_REQUEST['order']) ? sanitize_text_field( stripslashes( $_REQUEST['order'] ) ):null;
                
                $file_list=church_admin_podcast_files_list((int)$series_id,(int)$page,(int)$limit,esc_html( $speaker ),NULL,esc_html( $order ) );
                $series_detail=''; 
                if(!empty( $_REQUEST['series_id'] ) )$series_detail=church_admin_podcast_series_detail((int)$series_id,NULL);
                
                $SQL=array();
                if(!empty( $series_id ) )
                {
                    $SQL[]=' AND series_id="'.(int)$series_id.'"';
                }
                if(!empty( $_REQUEST['speaker'] ) )
                {
                    $SQL[]=' AND speaker LIKE "%'.esc_sql( $speaker).'%"';
                }
                $first_sermon_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE 2=2 '.implode(" ",$SQL).' ORDER BY pub_date '.esc_sql( $order ).' LIMIT 1');
                $first_sermon=church_admin_podcast_file_detail( $first_sermon_id,$exclude=NULL);
                
                $outputArray=array('series_detail'=>$series_detail,'file_list'=>$file_list,'first_sermon'=>$first_sermon,'file_id'=>$first_sermon_id);
                if(defined('CA_DEBUG') )
                {
                    //church_admin_debugprint_r( $outputArray,TRUE) );
                   
                }
				echo json_encode( $outputArray);
				exit();
			break;    
			case 'series-dropdown':
                check_ajax_referer( 'sermon-display', 'nonce' );
                //checked
				require_once(plugin_dir_path( __FILE__) .'/display/sermon-podcast.php');

                //sanitize
                $id =!empty($_REQUEST['id']) ? sanitize_text_field( stripslashes( $_REQUEST['id'] ) ):null;
                $page=!empty($_REQUEST['page']) ? sanitize_text_field( stripslashes( $_REQUEST['page'] ) ):null;
                $limit=!empty($_REQUEST['limit']) ? sanitize_text_field( stripslashes( $_REQUEST['limit'] ) ):null;

                $response=array();
                $file_list=church_admin_podcast_files_list((int)$id,(int)$page,(int)$limit );
                $series_detail=church_admin_podcast_series_detail((int)$id,NULL);
                $first_sermon_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE series_id="'.(int)$id.'" ORDER BY pub_date DESC LIMIT 1');
                $first_sermon=church_admin_podcast_file_detail( $first_sermon_id,$exclude=NULL);
				echo json_encode(array('series_detail'=>$series_detail,'file_list'=>$file_list,'first_sermon'=>$first_sermon,'file_id'=>$first_sermon_id) );
				exit();
			break;
            case 'speaker-dropdown'://checked
                check_ajax_referer( 'sermon-display', 'nonce' );
                $id =!empty($_REQUEST['id']) ? sanitize_text_field( stripslashes( $_REQUEST['id'] ) ):null;
                $page=!empty($_REQUEST['page']) ? sanitize_text_field( stripslashes( $_REQUEST['page'] ) ):null;
                $limit=!empty($_REQUEST['limit']) ? sanitize_text_field( stripslashes( $_REQUEST['limit'] ) ):null;

				require_once(plugin_dir_path( __FILE__) .'/display/sermon-podcast.php');
                $response=array();
                $file_list=church_admin_podcast_files_list(NULL,(int)$page,(int)$limit,esc_html( $speaker ) );
                $series_detail=church_admin_podcast_series_detail(NULL,NULL);
                $first_sermon_id=$wpdb->get_var('SELECT file_id FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE speaker LIKE"%'.esc_sql( $speaker ).'%" ORDER BY pub_date DESC LIMIT 1');
                $first_sermon=church_admin_podcast_file_detail( $first_sermon_id,$exclude=NULL);
				echo json_encode(array('series_detail'=>$series_detail,'file_list'=>$file_list,'first_sermon'=>$first_sermon,'file_id'=>$first_sermon_id) );
				exit();
			break;    
			case 'latest-series-sermon':
                check_ajax_referer( 'sermon-display', 'nonce' );
			require_once(plugin_dir_path( __FILE__) .'/display/sermon-podcast.php');

				echo church_admin_podcast_latest_sermon((int)$_REQUEST['id'] );
				exit();
			break;
			case 'more-sermons'://checked
                check_ajax_referer( 'sermon-display', 'nonce' );
				require_once(plugin_dir_path( __FILE__) .'/display/sermon-podcast.php');
                $series_id =!empty($_REQUEST['series_id']) ? sanitize_text_field( stripslashes( $_REQUEST['series_id'] ) ):null;
                $page=!empty($_REQUEST['page']) ? sanitize_text_field( stripslashes( $_REQUEST['page'] ) ):null;
                $limit=!empty($_REQUEST['limit']) ? sanitize_text_field( stripslashes( $_REQUEST['limit'] ) ):null;
                $sermon_search =  !empty($_REQUEST['sermon-search']) ? sanitize_text_field( stripslashes( $_REQUEST['sermon-search'] ) ):null;
                $speaker = !empty($_REQUEST['speaker'])? church_admin_sanitize($_REQUEST['speaker']):null;
				echo church_admin_podcast_files_list(esc_attr($series_id),(int)$page,(int)$limit,esc_attr( $speaker ),$sermon_search );
				exit();
			break;
            case 'sermon-search'://checked
                check_ajax_referer( 'sermon-display', 'nonce' );
                $page=!empty($_REQUEST['page']) ? sanitize_text_field( stripslashes( $_REQUEST['page'] ) ):null;
                $limit=!empty($_REQUEST['limit']) ? sanitize_text_field( stripslashes( $_REQUEST['limit'] ) ):null;
                $sermon_search =  !empty($_REQUEST['sermon-search']) ? sanitize_text_field( stripslashes( $_REQUEST['sermon-search'] ) ):null;
				require_once(plugin_dir_path( __FILE__) .'/display/sermon-podcast.php');
				echo church_admin_podcast_files_list((int)$series_id,(int)$page,(int)$limit,esc_html( $speaker ),$sermon_search );
				exit();
			break;
			case 'unattach_user'://checked
				check_ajax_referer( 'church_admin_unattach_user', 'nonce' );
				church_admin_unattach_user();
			break;
			case 'autocomplete'://checked
				check_ajax_referer( 'church-admin-autocomplete', 'security' );
				church_admin_ajax_people(TRUE);
			break;
			case 'mp3_plays'://checked
				church_admin_debug('Logging a play');
                church_admin_debug($_POST);
				//check_ajax_referer( 'church_admin_mp3_play', 'security' );
				church_admin_mp3_plays();
			break;
			case 'username_check'://checked
                check_ajax_referer( 'church_admin_username_check', 'nonce' );
				church_admin_username_check();
			break;

			case 'filter'://checked
				require_once(plugin_dir_path( __FILE__) .'/includes/filter.php');

				church_admin_filter_callback();
			break;
			case 'filter_email'://checked
                church_admin_debug('filter_email ajax fired');
				require_once(plugin_dir_path( __FILE__) .'/includes/filter.php');
                
				church_admin_filter_email_callback();
			break;
			case 'people_activate'://checked
                check_ajax_referer( 'activate', 'nonce' );
				church_admin_people_activate_callback();
			break;
			case'note_delete':
                check_ajax_referer( 'note_delete', 'nonce' );
				church_admin_note_delete_callback();
			break;
			case 'calendar_date_display':
				church_admin_date();
			break;

			case'connect_user':
				check_ajax_referer('connect_user','nonce',TRUE);
				if(church_admin_level_check('Directory') )
				{
                    //church_admin_debugprint_r( $_POST,TRUE) );
                    //sanitize
                    $userID=!empty($_POST['user_id'])?sanitize_text_field(stripslashes($_POST['user_id'])):null;
                    $people_id=!empty($_POST['people_id'])?sanitize_text_field(stripslashes($_POST['people_id'])):null;
                    //validate
                    if(empty($userID)){exit();}
                    if(!church_admin_int_check($userID)){exit();}
                    if(empty($people_id)){exit();}
                    if(!church_admin_int_check($people_id)){exit();}



                    $ID=church_admin_user_id_exists( $userID );
                    if( !empty( $ID) )
                    {
                        $sql='UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$userID.'" WHERE people_id="'.(int)$people_id.'"';
                        //church_admin_debug $sql);
                        $wpdb->query( $sql);
                        $user=get_userdata($userID );
                        $response= json_encode(array('login'=>$user->user_login,'people_id'=>(int)$people_id ) );
                        //church_admin_debug $response);
                        echo $response;
                    }
				}
				exit();
			break;
			case'create_user':
				check_ajax_referer('create_user','nonce',TRUE);
                //sanitize
            
                $people_id=!empty($_POST['people_id'])?sanitize_text_field(stripslashes($_POST['people_id'])):null;
                //validate
                if(empty($people_id)){exit();}
                if(!church_admin_int_check($people_id)){exit();}


				if(church_admin_level_check('Directory') )
				{
                    

                    
                        $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
                        
                        church_admin_create_user( $person->people_id,$person->household_id,null,null);
                        $userID=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
                        $user= get_userdata( $userID);
                        $response= json_encode(array('login'=>$user->user_login.' '.$out,'people_id'=>(int)$people_id ) );
                        //church_admin_debug $response);
                        echo $response;

                        
                    
                }
				exit();
			break;
			case 'individual_attendance':
					//church_admin_debug('Individual attendance');
					check_ajax_referer('individual_attendance','nonce',TRUE);
                    //sanitize
                    $meeting_type=!empty($_REQUEST['meeting_type'])?sanitize_text_field(stripslashes($_REQUEST['meeting_type'])):null;
                    $meeting_id = !empty($_REQUEST['meeting_id'])?sanitize_text_field(stripslashes($_REQUEST['meeting_id'])):null;
                    $date = !empty($_REQUEST['date'])?sanitize_text_field(stripslashes($_REQUEST['date'])):null;
                    //validate
                    if(empty($meeting_type)){exit();}
                    if(empty($meeting_id)||!church_admin_int_check($meeting_id)){exit();}
                    if(empty($date)||!church_admin_checkdate($date)){exit();}

					$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE meeting_type="'.esc_sql( $meeting_type ).'" AND meeting_id="'.(int)$meeting_id.'" AND `date`="'.esc_sql( $date).'"';
					//church_admin_debug $sql);
					$results=$wpdb->get_results( $sql);
					//church_admin_debugprint_r( $results,TRUE) );
					$out=array();
					if(!empty( $results) )
					{
						foreach( $results AS $row)
						{
							$out[]='person-'.$row->people_id;

						}
						//church_admin_debugprint_r( $out,TRUE) );
						echo json_encode( $out);
					}
					exit();
			break;
			case 'image_upload':
			check_ajax_referer('church_admin_image_upload','nonce',TRUE);
				// These files need to be included as dependencies when on the front end.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				// Let WordPress handle the upload.
				// Remember, 'my_image_upload' is the name of our file input in our form above.
				$attachment_id = media_handle_upload( 'file-0', 0 );
				//church_admin_debug $attachment_id);
				if ( is_wp_error( $attachment_id ) ) {
						// There was an error uploading the image.
				} else {
				// The image was uploaded successfully!
				$image=wp_get_attachment_image_src(  $attachment_id, "medium", false );
				//church_admin_debugprint_r( $image,TRUE) );
				echo json_encode(array('src'=>$image[0],'attachment_id'=>$attachment_id) );
				exit();
			}
			break;
            case 'household-upload':
                //church_admin_debug("*********************\r\n Household image upload");
                //church_admin_debugprint_r( $_POST,TRUE) );
                //church_admin_debugprint_r( $_FILES,TRUE) );
                check_ajax_referer('household-image-upload','nonce',TRUE);
				// These files need to be included as dependencies when on the front end.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				// Let WordPress handle the upload.
				$attachment_id = media_handle_upload( 'userImage', 0 );
                //church_admin_debug("attachment id $attachment_id");
				
				if ( is_wp_error( $attachment_id ) ) {
						// There was an error uploading the image.
				} else {

                $id=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;    
				// The image was uploaded successfully!
				$image=wp_get_attachment_image_src(  $attachment_id, "medium", false );
				//church_admin_debugprint_r( $image,TRUE) );
                if(!empty( $id ) )$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET attachment_id="'.(int)$attachment_id.'" WHERE household_id="'.(int)$id.'"');    
				echo json_encode(array('src'=>$image[0],'attachment_id'=>$attachment_id,'div'=>'household-image','id'=>'attachment_id') );
				exit();
			}
			break;
            case 'smallgroup-upload':
                //church_admin_debug("*********************\r\n People image upload");
                //church_admin_debugprint_r( $_POST,TRUE) );
                //church_admin_debugprint_r( $_FILES,TRUE) );
                
                check_ajax_referer('smallgroup-image-upload','nonce',TRUE);
				// These files need to be included as dependencies when on the front end.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				// Let WordPress handle the upload.
				$attachment_id = media_handle_upload( 'userImage', 0 );
				//church_admin_debug("attachment id $attachment_id");
				if ( is_wp_error( $attachment_id ) ) {
						// There was an error uploading the image.

				} else {
                    $id=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;    
                    // The image was uploaded successfully!
                    $image=wp_get_attachment_image_src(  $attachment_id, "medium", false );
                    //church_admin_debugprint_r( $image,TRUE) );
                    if(!empty( $id ) )$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_smallgroup SET attachment_id="'.(int)$attachment_id.'" WHERE id="'.(int)$id.'"');    
                    echo json_encode(array('src'=>$image[0],'attachment_id'=>$attachment_id,'div'=>'smallgroup-image'.(int)$id,'id'=>'attachment_id') );
                    exit();
			}
			break;
            case 'people-upload':
                //church_admin_debug("*********************\r\n People image upload");
                //church_admin_debugprint_r( $_POST,TRUE) );
                //church_admin_debugprint_r( $_FILES,TRUE) );
                
                check_ajax_referer('people-image-upload','nonce',TRUE);
				// These files need to be included as dependencies when on the front end.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				// Let WordPress handle the upload.
				$attachment_id = media_handle_upload( 'userImage', 0 );
				//church_admin_debug("attachment id $attachment_id");
				if ( is_wp_error( $attachment_id ) ) {
						// There was an error uploading the image.

				} else {
                    $id=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;    
                    // The image was uploaded successfully!
                    $image=wp_get_attachment_image_src(  $attachment_id, "medium", false );
                    //church_admin_debugprint_r( $image,TRUE) );
                    if(!empty( $id ) )$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET attachment_id="'.(int)$attachment_id.'" WHERE people_id="'.(int)$id.'"');    
                    echo json_encode(array('src'=>$image[0],'attachment_id'=>$attachment_id,'div'=>'people-image'.(int)$id,'id'=>'attachment_id','people_id'=>(int)$id ) );
                    exit();
			}
			break;
          
            case 'series-upload':
                //church_admin_debug("*********************\r\n People image upload");
                //church_admin_debugprint_r( $_POST,TRUE) );
                //church_admin_debugprint_r( $_FILES,TRUE) );
                
                check_ajax_referer('series-image-upload','nonce',TRUE);
				// These files need to be included as dependencies when on the front end.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				// Let WordPress handle the upload.
				$attachment_id = media_handle_upload( 'userImage', 0 );
				//church_admin_debug("attachment id $attachment_id");
				if ( is_wp_error( $attachment_id ) ) {
						// There was an error uploading the image.

				} else {
				    // The image was uploaded successfully!
                    $id=!empty($_POST['id'])?sanitize_text_field(stripslashes($_POST['id'])):null;    
                    $image=wp_get_attachment_image_src(  $attachment_id, "medium", false );
                    //church_admin_debugprint_r( $image,TRUE) );
                    if(!empty( $id) )$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_sermon_series SET series_image="'.(int)$attachment_id.'" WHERE series_id="'.(int)$id.'"');    
                    echo json_encode(array('src'=>$image[0],'attachment_id'=>$attachment_id,'div'=>'series-image','id'=>'attachment_id') );
                    exit();
			}
			break;
			case 'remove-app-logo':
				check_ajax_referer('remove-app-logo','nonce',TRUE);
				delete_option('church_admin_app_logo');
				echo TRUE;
				exit();
			break;
			case 'update-app-logo':
				check_ajax_referer('update-app-logo','nonce',TRUE);
                $logo= !empty($_POST['logo'])?sanitize_text_field(stripslashes($_POST['logo'])):null;
				if(!empty($logo)){update_option('church_admin_app_logo',$logo ) ;}
				echo TRUE;
				exit();
			break;
			case 'category_list':
				//filter count
                check_ajax_referer('church_admin_filter','nonce',TRUE);
				require_once(plugin_dir_path( __FILE__) .'/includes/filter.php');
				echo church_admin_filter_count(null);
				exit();
			break;
           
			
		}



}

add_action('init','church_admin_receive_prayer');

function church_admin_receive_prayer()
{
	//handle front end prayer request which needs to happen later than plugins_loaded action
	global $church_admin_prayer_request_success;
	if(!empty( $_POST['save_prayer_request'] )&&!empty( $_POST['non_spammer'] )&&wp_verify_nonce( $_POST['non_spammer'],'prayer-request') )
	{
        if(is_user_logged_in()){
            $user=wp_get_current_user();
            $ID=$user->ID;
        }
        if(empty($ID))$ID=1;
        //church_admin_debug("**** church_admin_receive_prayer ****");
        //church_admin_debug('$_POST ');
        //church_admin_debug($_POST);
        $post_content=sanitize_textarea_field( stripslashes($_POST['request_content'] ));
        $post_title=wp_strip_all_tags( stripslashes($_POST['request_title'] ));
        $post_type='prayer-requests';
		$args=array(
                'post_author'=>(int)$ID,
				'post_content'=>$post_content,
				'post_title'=>$post_title,
				'post_status'=>'draft',
				'post_type'=>$post_type
			);
                            
		if(church_admin_level_check('Prayer') )  {$args['post_status']='publish';}
        //post_exists not available in front end, so check and require post.php if needed
        if ( ! function_exists( 'post_exists' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }
        //church_admin_debug('Args for insert_post');
        //church_admin_debug($args);
        if(post_exists( $post_title, $post_content, '', $post_type, '' ) )return;
		$postid = wp_insert_post( $args);

		if( $postid)
		{
                //church_admin_debug('Post inserted');
				//the post is valid
				$church_admin_prayer_request_success='<div class="notice notice-success">';
				if( $args['post_status']=='publish')  {
                    $church_admin_prayer_request_success.=__('Your prayer-request has been published','church-admin');
                    //church_admin_debug('Post status - publish');
                }
				else
				{
                    //church_admin_debug('Post status - draft');
					$church_admin_prayer_request_success.=__('Your prayer-request has been put in the moderation queue','church-admin');
					$message='<p>'.esc_html( __('New prayer request draft for moderation','church-admin' ) ).'</p><p><a href="'.admin_url().'?edit.php?post_type=prayer-requests">'.esc_html( __('View prayer requests','church-admin' ) ).'</a>';
				    $prm= get_option('prayer-request-moderation');
                    
                    if(empty( $prm) )$prm=get_option('church_admin_default_from_email');

                    
                    church_admin_email_send($prm,esc_html(__('Prayer Request Draft','church-admin' ) ),esc_html(__('A draft prayer request has been posted. Please moderate','church-admin') ),null,null,null,null,null,TRUE);
                    /*
                    //church_admin_debug('Email to be sent to :'.$prm);
                    add_filter( 'wp_mail_from_name','church_admin_from_name' );
                    add_filter( 'wp_mail_from', 'church_admin_from_email');
                    add_filter('wp_mail_content_type','church_admin_email_type');
                    
                    if(wp_mail( $prm,esc_html(__('Prayer Request Draft','church-admin' ) ),esc_html(__('A draft prayer request has been posted. Please moderate','church-admin') )))
                    {
                        //church_admin_debug('Email sent');
                    }
                    
                    //church_admin_debug print_r( $GLOBALS['phpmailer'] ,TRUE));
                    remove_filter('wp_mail_content_type','church_admin_email_type');
                    remove_filter( 'wp_mail_from_name','church_admin_from_name' );
                    remove_filter( 'wp_mail_from', 'church_admin_from_email');
                    */
                    
                    /*****************************************
                     * push to admins if required!
                     * 
                     * ***************************************/
                    $prayer_request_people_ids=get_option('church_admin_prayer_request_receive_push_to_admin');
                    //church_admin_debug('Attempting to push prayer request moderation');
                    //church_admin_debugprint_r( $prayer_request_people_ids,TRUE) );
                     $licence = get_option('church_admin_app_new_licence');;
                    if(!empty( $prayer_request_people_ids)&&!empty( $licence) )
                    {
                        $pushTokens=church_admin_get_push_tokens_from_ids( $prayer_request_people_ids);
                        //church_admin_debug('Push tokens');
                        //church_admin_debugprint_r( $pushTokens,TRUE) );
                        if(!empty( $pushTokens) )
                        {
                            require_once( plugin_dir_path( __FILE__ ).'includes/push.php');
                        
                            $dataMessage=$message= esc_html( __('Please moderate a new prayer request','church-admin') );
                            //$dataMessage=$message.'<p><a href="'.admin_url().'edit.php?post_type=prayer-requests">'.esc_html( __('View prayer requests','church-admin' ) ).'</a>';
                            
                            //church_admin_debug('Data Message variable: ' . $dataMessage);
                            church_admin_filtered_push( $message,$pushTokens,esc_html(  __('Prayer Request Moderation','church-admin') ),$dataMessage,'prayer',NULL);

                        }


                    }
                    
				}
				$church_admin_prayer_request_success.='</div>';
		}
	}
}





add_action('init','church_admin_acts_courage');

function church_admin_acts_courage()
{
	//handle front end prayer request which needs to happen later than plugins_loaded action
	global $church_admin_acts_success;
	if(!empty( $_POST['save_act_of_courage_request'] )&&!empty( $_POST['non_spammer'] )&&wp_verify_nonce( $_POST['non_spammer'],'acts-of-courage') )
	{

		$args=array(
								'post_content'=>sanitize_textarea_field( stripslashes($_POST['request_content'] ) ),
								'post_title'=>wp_strip_all_tags( stripslashes($_POST['request_title'] ) ),
								'post_status'=>'draft',
								'post_type'=>'acts-of-courage'
							);
		if(church_admin_level_check('Prayer') )  {$args['post_status']='publish';}


		$postid = wp_insert_post( $args);

		if( $postid)
		{

				//the post is valid
				$church_admin_acts_success='<div class="notice notice-success">';
				if( $args['post_status']=='publish')  {
                    $church_admin_acts_success.=esc_html(  __('Your act of courage has been published','church-admin') );
                }
				else
				{
					$church_admin_acts_success.=esc_html(  __('Your act of courage has been put in the moderation queue','church-admin') );
					$message='<p>'.esc_html(  __('New act of courage draft for moderation','church-admin') ).'</p>';
					//wp_mail(get_option('church_admin_default_from_email'),esc_html(  __('New act of courage draft for moderation','church-admin') ) ,$message);
                    church_admin_email_send(get_option('church_admin_default_from_email'),esc_html(  __('New act of courage draft for moderation','church-admin') ),$message,null,null,null,null,null,TRUE);
				}
				$church_admin_acts_success.='</div>';
		}
	}
}

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */

function church_admin_login_redirect( $redirect_to, $request, $user ) {
   	$check=get_option('church_admin_login_redirect');
   	if( $check && isset( $user->roles) && is_array( $user->roles) ) {
        //check for subscribers
        if (in_array('subscriber', $user->roles) ) {
            // redirect them to another URL, in this case, the homepage 
            $redirect_to =  $check;
        }
    }

    return $redirect_to;
}

add_filter( 'login_redirect', 'church_admin_login_redirect', 10, 3 );



add_action( 'save_post', 'church_admin_sermon_check', 10,3 );
function church_admin_sermon_check( $post_id, $post, $update)
{
    if(strpos( $post->post_content,'[church_admin') !== false && strpos( $post->post_content,'podcast') !== false)
    {
        
        update_option('church_admin_sermon_page',$post_id);
      
    }
}

function church_admin_find_sermon_page()
{
    /*
    global $wpdb;
    $result=$wpdb->get_var('SELECT ID FROM '.$wpdb->posts.' WHERE post_content LIKE "%[church_admin%" AND post_content LIKE"%podcast%" AND post_status="publish" LIMIT 1');
    if( $result) 
    {
        $link=get_permalink( $result);
      
        return $link;
    }
    */
    $sermonPageID=get_option('church-admin-sermon-page');
    if(!empty( $sermonPageID) )  {return get_permalink( $sermonPageID);}
    else return get_permalink(); 

}

function church_admin_cpanel_fix()
{
    //church_admin_debug("Cpanel fix");
    //look in CA_PATH
    function church_admin_remover( $path)
    {
        if (is_dir( $path) ) 
        {
            if ( $dh = opendir( $path) ) 
            {
                while (( $file = readdir( $dh) ) !== false) 
                {
                    if( $file==".ea-php-cli.cache")
                    {
                        unlink( $path.$file);
                        //church_admin_debug(" .ea-php-cli.cache deleted from ".$path);
                    }
                }
                    closedir( $dh);
            }
        }
    }
    church_admin_remover(CA_PATH);
    church_admin_remover(CA_PATH.'/display/');
    church_admin_remover(CA_PATH.'/includes/');
    church_admin_remover(CA_PATH.'/app/');
    church_admin_remover(CA_PATH.'/gutenberg/');
    church_admin_remover(CA_PATH.'/css/');
    
    
}

/*************************************************************
*
* Add column for custom button to the app-content post type
*
*************************************************************/
// Add the custom columns to the book post type:
add_filter( 'manage_app-content_posts_columns', 'church_admin_custom_app_content_columns' );
function church_admin_custom_app_content_columns( $columns) {
    unset( $columns['date'] );
   $columns['mybutton'] = __( 'App button code', 'church-admin' );
     $columns['date'] =__('Date','church-admin');
    return $columns;
}

// Add the data to the custom columns for the book post type:
add_action( 'manage_app-content_posts_custom_column' , 'church_admin_custom_app_content_column', 10, 2 );
function church_admin_custom_app_content_column( $column, $post_id ) {
    
    switch ( $column ) {
      
        case 'mybutton' :
            echo esc_html('<button id="myButton" class="button red" data-page="'.sanitize_title(get_the_title( $post_id) ).'">'.get_the_title( $post_id).'</button>');
        break;
    }
}

/*************************************************************
*
* Add don't send push notification for this post meta box
*
*************************************************************/

function church_admin_add_custom_box()
{
    $screens = ['post'];
    foreach ( $screens as $screen) {
        add_meta_box(
            'church_admin_no_push',           // Unique ID
            'Church Admin Post Settings',  // Box title
            'church_admin_custom_box_html',  // Content callback, must be of type callable
            $screen                   // Post type
        );
    }
}
add_action('add_meta_boxes', 'church_admin_add_custom_box');

function church_admin_custom_box_html( $post)
{
    ?>
    <label for="church_admin_field">Don't send a push notification when this post is published</label>
    <input type="checkbox" name="church_admin_no_push" id="church_admin_field" value=1/>
    
    <?php
}



function church_admin_create_directories()
{
    //use native WordPress functions to check and create directories 2020-09-30
   
    $upload_dir = wp_upload_dir();
    $church_admin_sermon_dir=$upload_dir['basedir'].'/sermons/';
    wp_mkdir_p( $church_admin_sermon_dir);
    $index="<?php\r\n//nothing is good;\r\n?>";
    if(is_dir( $church_admin_sermon_dir) )
    {
        $church_admin_fp =  fopen( $church_admin_sermon_dir.'index.php', 'w');
        if($church_admin_fp){
            fwrite( $church_admin_fp, $index);
            fclose( $church_admin_fp); 
        }
           
    }
    $church_admin_cache_dir=$upload_dir['basedir'].'/church-admin-cache/';
    wp_mkdir_p( $church_admin_cache_dir);
    if(is_dir( $church_admin_sermon_dir) )
    {
        $church_admin_fp = fopen( $church_admin_cache_dir.'index.php', 'w');
        if($church_admin_fp){
            fwrite( $church_admin_fp, $index);
            fclose( $church_admin_fp); 
        }
           
    }
}


/**
 * Adds a privacy policy statement.
 */
function church_admin_add_privacy_policy_content() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}
	$content = '<p class="privacy-policy-tutorial">' . __( 'The Church Admin plugin handles a fair of amount personal data within the website in a secure way. ', 'church-admin' ) . '</p>'
			
			. '<h3>'.esc_html( __('Registration','church-admin' ) ).'</h3>'
            .'<p>'.esc_html( __('When you register on this website, your information is stored in our database and viewable by the church admin team. You can set your own privacy settings to restrict who can see what and if and how you are communicated with.','church-admin'))
            .'<h3>'.esc_html( __('Address list','church-admin' ) ).'</h3>'
            .'<p>'.esc_html( __('The church has an address list which is visible to logged in users of the website. You can opt whether or not your personal data appears on the address list.','church-admin' ) ).'</p>'
            .'<h3>'.esc_html( __('Schedules','church-admin' ) ).'</h3>'
            .'<p>'.esc_html( __('The church organises who is doing what and when for our services in schedules. You can opt to recieve email, SMS and push notifications of your involvement. The schedule is also visible to other logged in users of the site, showing only names and what jobs and when.','church-admin'))
            .'<h3>'.esc_html( __('Communications','church-admin' ) ).'</h3>'
            .'<p>'.esc_html( __('Logged in users can set the communications settings for their household. You can opt to receive or not receive phone calls, SMS, email and mail letters.','church-admin'))
            .'<h3>'.esc_html( __('Donations','church-admin' ) ).'</h3>'
            .'<p>'.esc_html( __('If you make a donation using PayPal on the website, the donation details are stored in the database and viewable by the admin team','church-admin. UK churches will use your name, address and donation amount to make a Gift Aid claim if you have opted for Gift Aid claims.','church-admin'))
            .'<h3>'.esc_html( __('Classes and events','church-admin' ) ).'</h3>'
            .'<p>'.esc_html( __('If you book a place for a class or event your personal data will be stored for that booking to allow event checkin and attendance records.','church-admin'))
            .'<h3>'.esc_html( __('MailChimp (if used)','church-admin' ) ).'</h3>'
            .'<p>'.esc_html( __('This site uses MailChimp as its email sending partner. To send email, first name, last name, email and which member level, gender, small groups and classes is stored on their database to allow the correct emails to be sent.','church-admin'))
            .'<h3>'.esc_html( __('Google (if api key is set','church-admin'))
            .'<p>'.esc_html( __('To allow map pins on mapping on the website, latitude and longitude of addresses are used by mapping on site'))
            .'<h3>'.esc_html( __('Right to be forgotten','church-admin'))
            .'<p>'.esc_html( __('If you are logged in user, you can completely delete all your household information','church-admin'));
            

	wp_add_privacy_policy_content( 'Church Admin Plugin', wp_kses_post( wpautop( $content, false ) ) );
}

add_action( 'admin_init', 'church_admin_add_privacy_policy_content' );


/********************
 * AUTOMATIONS
 *********************/
add_action('church_admin_followup_email','church_admin_followup_email',1,2);
function church_admin_followup_email()
{
    church_admin_debug('*** church_admin_followup_email ****');
    global $wpdb;
    $followup_template = get_option('church_admin_followup_email_template');
    $from_name = !empty($followup_template['from_name'])? $followup_template['from_name']:get_option('church_admin_default_from_name');;
    $from_email = !empty($followup_template['from_email'])? $followup_template['from_email']:get_option('church_admin_default_from_email');
    $days_ago = wp_date('Y-m-d',strtotime($followup_template['days'].' days ago'));
    //church_admin_debug('Looking for first registered '.$days_ago);
    $households = $wpdb->get_results('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_household a, '.$wpdb->prefix.'church_admin_people b WHERE a.household_id=b.household_id AND b.head_of_household=1 AND b.email_send=1 AND  b.first_registered="'.esc_sql($days_ago).'" AND b.gdpr_reason IS NULL AND b.user_id IS NULL');
    church_admin_debug($wpdb->last_query);
    if(empty($households)){return;}
    church_admin_debug('New households found');
    foreach($households AS $household){
        $check=$wpdb->get_var('SELECT meta_date FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$household->people_id.'" AND meta_type="confirmation_reminder"');
        if(!empty($check)){
            //translators: %1$s is a name, %2$s is a date
            church_admin_debug(sprintf('Reminder sent to %1$s on %2$s',church_admin_formatted_name($household),$check));
            continue;
        }
        $name = church_admin_formatted_name($household);
        $email = $household->email;
        if(empty($email)){continue;}
        
        $message = $followup_template['message'];
        $message = str_replace('[NOT_CONFIRMED]',' <a href="'.home_url().'?confirm_email='.md5( $household->email).'&amp;people_id='.md5( $household->people_id).'">'.esc_html( __( 'Please click to confirm your email','church-admin') ).'</a>',$message);
        
        $message = str_replace('[CHURCH_URL]',make_clickable(site_url()),$message);
        $send_message = str_replace('[NAME]',$name,$message);
        church_admin_email_send($email,$followup_template['subject'],$send_message,$from_name,$from_email,null,null,null,FALSE);
    }

}
/***********************************
 * TEST BIRTHDAY/ANNIVERSARY EMAIL
 ***********************************/
function church_admin_send_test_automation_email()
{
    global $wpdb,$church_admin_url;
    $people_types=get_option('church_admin_people_type');
    if(!empty($_POST['save']))
    { 
        
        
        $email=!empty($_POST['test-email'])? church_admin_sanitize($_POST['test-email']):null;
        if(empty($email)){
            echo'<div class="notice notice-succes"><h2>'.esc_html(__('No test email ','church-admin')).'</h2></div>';
            return;
        }
        $which =!empty($_POST['which-automation'])? church_admin_sanitize($_POST['which-automation']):null;
        if(empty($which)){
            echo'<div class="notice notice-succes"><h2>'.esc_html(__('No automation specified','church-admin')).'</h2></div>';
            return;

        }
        $tableStyle='style="font-family: Arial;font-size:1em; border-collapse: collapse;margin-bottom:10px"';
        $thStyle = 'style="border: 1px solid #ddd;padding: 12px; text-align: left; background-color: #CCC;color: white;" ';
        $tdStyle = 'style="border: 1px solid #ddd;padding: 8px;"';
        switch($which){

            case 'happy-birthday':
                $stored_message = get_option('church_admin_happy_birthday_template');
                //church_admin_debug($stored_message);
                if(empty($stored_message)){
                    echo'<div class="notice notice-succes"><h2>'.esc_html(__('No template setup ','church-admin')).'</h2></div>';
                    return;
                
                }
                $message = $stored_message['message'];
                $subject = $stored_message['subject'];
                $from_name = !empty($stored_message['from_name'])?$stored_message['from_name']:get_option('church_admin_default_from_name');;
                $from_email = !empty($stored_message['from_email'])?$stored_message['from_email']:get_option('church_admin_default_from_email');
                $reply_name = !empty($stored_message['reply_name'])?$stored_message['reply_name']:get_option('church_admin_default_from_name');;
                $reply_email = !empty($stored_message['reply_email'])?$stored_message['reply_email']:get_option('church_admin_default_from_email'); 
                $subject = str_replace('[first_name]','John',$subject);
                $subject = str_replace('[last_name]','Doe',$subject);
                
                
                $send_message = str_replace('[date]',wp_date(get_option('date_format')),$message);
                $send_message = str_replace('[first_name]','John',$send_message);
                $send_message = str_replace('[last_name]','Doe',$send_message);
                $send_message = str_replace('[email]','john@doe.com',$send_message);
                $send_message = str_replace('[cell]','<a href="'.esc_url('tel:0123456789').'">0123456789</a>',$send_message);
                
                $birthyear = '1970';
                $currentyear = date("Y");
                $age = $currentyear - $birthyear;
                $send_message = str_replace('[age]',$age,$send_message);
                 break;

            case 'global-birthday':
                $message = get_option('church_admin_global_birthday_template');
                //church_admin_debug($message);
                if(empty($message)){
                    echo'<div class="notice notice-succes"><h2>'.esc_html(__('No template setup ','church-admin')).'</h2></div>';
                    return;
                
                }
                $subject = $message['subject'];
                $from_name = !empty( $message['from_name'] )? $message['from_name'] : get_option('church_admin_default_from_name');
                $from_email = !empty( $message['from_email'] )? $message['from_email'] : get_option('church_admin_default_from_email');

                $reply_name = !empty( $message['reply_name'] )? $message['reply_name'] : get_option('church_admin_default_from_name');
                $reply_email = !empty( $message['reply_email'] )? $message['reply_email'] : get_option('church_admin_default_from_email');




                $birthdaysTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Adult/Child','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>';
                $birthdaysTable.='<tr><td '.$tdStyle.'>John Doe</td><td '.$tdStyle.'>'.esc_html($people_types[1]).'</td><td '.$tdStyle.'>'.make_clickable('john@doe.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:0123456789').'">0123456789</a></td></tr>';
                $birthdaysTable.='<tr><td '.$tdStyle.'>Jane Doe</td><td '.$tdStyle.'>'.esc_html($people_types[2]).'</td><td '.$tdStyle.'>'.make_clickable('jane@doe.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:0987654321').'">0123456789</a></td></tr>';
                
                $birthdaysTable.='</tbody></table>';
                $send_message = str_replace('[birthdays]',$birthdaysTable,wpautop($message['message']));
                $send_message = str_replace('[date]',wp_date(get_option('date_format')),$send_message);
            break;

            case 'happy-anniversary':

                $message = get_option('church_admin_happy_anniversary_template');
                //church_admin_debug($message);
                if(empty($message)){
                    echo'<div class="notice notice-succes"><h2>'.esc_html(__('No template setup ','church-admin')).'</h2></div>';
                    return;
                
                }
                $subject = $message['subject'];
                $send_message = $message['message'];
                $from_name = !empty($message['from_name'])?$message['from_name'] : get_option('church_admin_default_from_name');
                $from_email = !empty($message['from_email'])?$message['from_email'] : get_option('church_admin_default_from_email');
                $reply_name = !empty($message['reply_name'])?$message['reply_name'] : get_option('church_admin_default_from_name');
                $reply_email = !empty($message['reply_email'])?$message['reply_email']:get_option('church_admin_default_from_email');


                $subject = str_replace('[couple_name]','John & Jane Doe',$subject);
                $subject = str_replace('[couple_names]','John & Jane Doe',$subject);
                //sort message shortcode
                $send_message = str_replace('[date]',wp_date(get_option('date_format')),$send_message);
                $send_message = str_replace('[couple_names]','John & Jane Doe',$send_message);
                $send_message = str_replace('[couple_name]','John & Jane Doe',$send_message);
                $send_message = str_replace('[email]','john@doe.com',$send_message);
                $send_message = str_replace('[cell]','<a href="'.esc_url('tel:0123456789').'">0123456789</a>',$send_message);
                $send_message = str_replace('[years]',20,$send_message);
                break;
            
            case 'global-anniversary':
                $message = get_option('church_admin_global_anniversary_template');
                //church_admin_debug($message);
                if(empty($message)){
                    echo'<div class="notice notice-succes"><h2>'.esc_html(__('No template setup ','church-admin')).'</h2></div>';
                    return;
                
                }
                $subject = $message['subject'];
                $send_message = wpautop($message['message']);
                $from_name = !empty($message['from_name'])?$message['from_name'] : get_option('church_admin_default_from_name');
                $from_email = !empty($message['from_email'])?$message['from_email'] : get_option('church_admin_default_from_email');
                $reply_name = !empty( $message['reply_name'] )? $message['reply_name'] : get_option('church_admin_default_from_name');
                $reply_email = !empty( $message['reply_email'] )? $message['reply_email'] : get_option('church_admin_default_from_email');


                $anniversaryTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>'."\r\n";
                $anniversaryTable.='<tr><td '.$tdStyle.'>John & Jane Doe</td><<td '.$tdStyle.'>'.make_clickable('john@doe.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:0987654321').'">0123456789</a></td></tr>'."\r\n";
                $anniversaryTable.='<tr><td '.$tdStyle.'>Fred & Sally Smith</td><td '.$tdStyle.'>'.make_clickable('fred@smith.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:01357924680').'">01357924680</a></td></tr>'."\r\n";
                $anniversaryTable.='</tbody></table>'."\r\n";
                $send_message = str_replace('[anniversaries]',$anniversaryTable,$send_message);
                break;
            case 'global-both':
                $message = get_option('church_admin_global_both_template');
                //church_admin_debug($message);
                if(empty($message)){
                    echo'<div class="notice notice-succes"><h2>'.esc_html(__('No template setup ','church-admin')).'</h2></div>';
                    return;
                
                }
                $subject = $message['subject'];
                $from_name = !empty($message['from_name'])?$message['from_name'] : get_option('church_admin_default_from_name');
                $from_email = !empty($message['from_email'])?$message['from_email'] : get_option('church_admin_default_from_email');

                $reply_name = !empty( $message['reply_name'] )? $message['reply_name'] : get_option('church_admin_default_from_name');
                $reply_email = !empty( $message['reply_email'] )? $message['reply_email'] : get_option('church_admin_default_from_email');


                $send_message = wpautop($message['message']);

                $birthdaysTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Adult/Child','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>';
                $birthdaysTable.='<tr><td '.$tdStyle.'>John Doe</td><td '.$tdStyle.'>'.esc_html(__('Adult','church-admin')).'</td><td '.$tdStyle.'>'.make_clickable('john@doe.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:0123456789').'">0123456789</a></td></tr>';
                $birthdaysTable.='<tr><td '.$tdStyle.'>Jane Doe</td><td '.$tdStyle.'>'.esc_html(__('Adult','church-admin')).'</td><td '.$tdStyle.'>'.make_clickable('jane@doe.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:0987654321').'">0123456789</a></td></tr>';
                $birthdaysTable.='</tbody></table>';
                $anniversaryTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>'."\r\n";
                $anniversaryTable.='<tr><td '.$tdStyle.'>John & Jane Doe</td><td '.$tdStyle.'>'.make_clickable('john@doe.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:0987654321').'">0123456789</a></td></tr>'."\r\n";
                $anniversaryTable.='<tr><td '.$tdStyle.'>Fred & Sally Smith</td><td '.$tdStyle.'>'.make_clickable('fred@smith.com').'</td><td '.$tdStyle.'><a href="'.esc_url('tel:01357924680').'">01357924680</a></td></tr>'."\r\n";
                $anniversaryTable.='</tbody></table>'."\r\n";
               
                /**********************************
                 * Build message
                 ***********************************/
                $send_message = $message['message'];


                //birthday_text
                $tag = 'birthday_text';
                $regex = '\\['                             // Opening bracket.
                . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
                . "($tag)"                     // 2: Shortcode name.
                . '(?![\\w-])'                       // Not followed by word character or hyphen.
                . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
                .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
                .     '(?:'
                .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
                .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
                .     ')*?'
                . ')'
                . '(?:'
                .     '(\\/)'                        // 4: Self closing tag...
                .     '\\]'                          // ...and closing bracket.
                . '|'
                .     '\\]'                          // Closing bracket.
                .     '(?:'
                .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
                .             '[^\\[]*+'             // Not an opening bracket.
                .             '(?:'
                .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
                .                 '[^\\[]*+'         // Not an opening bracket.
                .             ')*+'
                .         ')'
                .         '\\[\\/\\2\\]'             // Closing shortcode tag.
                .     ')?'
                . ')'
                . '(\\]?)';  

                
                preg_match_all( '/'. $regex .'/s',$send_message, $matches );
                    
                
                if(!empty($birthdaysTable)){
                    // replace  [birthday_text] and [birthdays]
                    $send_message = str_replace($matches[0],$matches[5][0],$send_message);
                    $send_message = str_replace('[birthdays]',$birthdaysTable,$send_message);

                }else
                {
                    //no birthdays so strip out [birthday_text] and [birthdays]
                    $send_message = str_replace($matches[0],'',$send_message);
                    $send_message = str_replace('[birthdays]','',$send_message);
                }
                //anniversaries
                $tag = 'anniversary_text';
                $regex = '\\['                             // Opening bracket.
                . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
                . "($tag)"                     // 2: Shortcode name.
                . '(?![\\w-])'                       // Not followed by word character or hyphen.
                . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
                .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
                .     '(?:'
                .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
                .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
                .     ')*?'
                . ')'
                . '(?:'
                .     '(\\/)'                        // 4: Self closing tag...
                .     '\\]'                          // ...and closing bracket.
                . '|'
                .     '\\]'                          // Closing bracket.
                .     '(?:'
                .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
                .             '[^\\[]*+'             // Not an opening bracket.
                .             '(?:'
                .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
                .                 '[^\\[]*+'         // Not an opening bracket.
                .             ')*+'
                .         ')'
                .         '\\[\\/\\2\\]'             // Closing shortcode tag.
                .     ')?'
                . ')'
                . '(\\]?)';  

                
                preg_match_all( '/'. $regex .'/s', $send_message, $matches );
                    
                
                if(!empty($anniversaryTable)){
                    // replace  [birthday_text] and [birthdays]
                    $send_message = str_replace($matches[0],$matches[5][0],$send_message);
                    $send_message = str_replace('[anniversaries]',$anniversaryTable,$send_message);

                }else
                {
                    //no birthdays so strip out [birthday_text] and [birthdays]
                    $send_message = str_replace($matches[0],'',$send_message);
                    $send_message = str_replace('[anniversaries]','',$send_message);
                }

                $send_message = str_replace('[date]',wp_date(get_option('date_format')),$send_message);

                
                            
               
                //church_admin_debug('*** Finished message ***');
            break;
        }

        if(!empty($send_message)){
            
            church_admin_email_send($email,$subject,$send_message,$from_name,$from_email,null,$reply_name,$reply_email,TRUE);
            echo'<div class="notice notice-succes"><h2>'.esc_html(__('Test message sent','church-admin')).'</h2></div>';
        }
    }
    echo'<h2>'.esc_html(__('Send test email','church-admin')).'</h2>';
    echo'<p>'.__('Send a test email with some random birthday and anniversary names inserted','church-admin').'</p>';
    echo'<form action="'.wp_nonce_url($church_admin_url.'&action=send-test-automation-emails&amp;section=key-dates','send-test-automation-emails').'" method="POST">';
    echo'<div class="church-admin-form-group"><label>'.esc_html( __( 'Send test email to', 'church-admin' ) ) .'</label><input class="church-admin-form-control" type="email" name="test-email"></div>';
    echo'<div  class="church-admin-form-group"><label>'.__('Which automation','church-admin').'</label>';
    echo'<select name="which-automation">';
    echo '<option value="happy-birthday">'.esc_html( __( 'Individual Happy Birthday', 'church-admin' ) ) .'</option>';
    echo '<option value="global-birthday">'.esc_html( __( 'Global Happy Birthday', 'church-admin' ) ) .'</option>';
    echo '<option value="happy-anniversary">'.esc_html( __( 'Individual Happy Anniversary', 'church-admin' ) ) .'</option>';
    echo '<option value="global-anniversary">'.esc_html( __( 'Global Happy Anniversaries', 'church-admin' ) ) .'</option>';
    echo '<option value="global-both">'.esc_html( __( 'Global Anniversaries & birthdays', 'church-admin' ) ) .'</option>';
    echo'</select></div>';
    echo'<p><input type="hidden" name="save" value=1><input type="submit" class="button-primary" value="'.esc_html(__('Send Test email')).'"></p></form>';
}


/**************************
 * HAPPY BIRTHDAY EMAIL
 ***************************/

add_action('church_admin_happy_birthday_email','church_admin_happy_birthday_email',1,2);
function church_admin_happy_birthday_email($args)
{
    //church_admin_debug('*** church_admin_happy_birthday_email ****');
    //church_admin_debugwp_date('y-m-d h:i:s'));
    global $wpdb;
    //church_admin_debug($args);
    $memb_sql='';
    
    if(!empty($args)){
        $membsql=array();
        foreach($args AS $key=>$id){
            $membsql[]=' (member_type_id="'.(int)$id.'") ';
        }
        if(!empty($memb_sql)){$memb_sql = 'AND ('.implode(' OR ',$membsql).')';}
    }

    $this_month = date('m');
	$this_day = date('d');
    $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE  MONTH(date_of_birth)="'.(int)$this_month.'" AND DAY(date_of_birth)="'.(int)$this_day.'" '.$memb_sql);
    //church_admin_debug($wpdb->last_query);
    //church_admin_debug($people);
    if(!empty($people)){
        //church_admin_debug('Got people');
        $stored_message = get_option('church_admin_happy_birthday_template');
        //church_admin_debug($stored_message);
        $message = $stored_message['message'];
        $subject = $stored_message['subject'];
        $from_name = !empty($stored_message['from_name'])?$stored_message['from_name']:get_option('church_admin_default_from_name');
        $from_email = !empty($stored_message['from_email'])?$stored_message['from_email']:get_option('church_admin_default_from_email');
        $reply_name = !empty($stored_message['reply_name'])?$stored_message['reply_name']:get_option('church_admin_default_from_name');
        $reply_email = !empty($stored_message['reply_email'])?$stored_message['reply_email']:get_option('church_admin_default_from_email');
        if(empty($message)){return;}
        foreach($people AS $person){
            $email = $person->email;
            $email_send = $person->email_send;
            $send_message = $message;
            //if  a child change to head of household
            if(!empty($person->people_type_id) && $person->people_type_id==2)
            {
                //church_admin_debug('Handle child birthday');
                //birthday person is a child so do it differently
                $parent=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$person->household_id.'"');
                $email = $parent->email;
                $email_send = $parent->email_send;
                $send_message = $stored_message['parent_message'];
                $send_message = str_replace('[PARENT_NAME]',$parent->first_name, $send_message);
                $send_message = str_replace('[parent_name]',$parent->first_name, $send_message);
                $send_message = str_replace('[child_name]',$person->first_name, $send_message);
            }
            
            if(empty($email)){continue;}
            if(empty($email_send)){continue;}//don't send if no permission


            $privacy = maybe_unserialize($person->privacy);
            //prepare subject
            $person_subject = $subject;
            $person_subject = str_replace('[first_name]',$person->first_name,$person_subject);
            $person_subject = str_replace('[last_name]',$person->last_name,$person_subject);
            
            
            $send_message = str_replace('[date]',wp_date(get_option('date_format')),$send_message);
            $send_message = str_replace('[first_name]',$person->first_name,$send_message);
            $send_message = str_replace('[last_name]',$person->last_name,$send_message);
            if(empty($privacy['show-email'])){
                $send_message = str_replace('[email]','',$send_message);
            }
            else{
                $send_message = str_replace('[email]',make_clickable($person->email),$send_message);
            }
            if(empty($privacy['show-cell'])){
                $send_message = str_replace('[cell]','<a href="'.esc_url('tel:'.$person->cell).'">'.$person->cell.'</a>',$send_message);
            }
            else{
                $send_message = str_replace('[cell]','',$send_message);
            }
            $birthyear = mysql2date("Y",$person->date_of_birth);
            $currentyear = date("Y");
            $age = $currentyear - $birthyear;
            $send_message = str_replace('[age]',$age,$send_message);
              //church_admin_debug($send_message);
            church_admin_email_send($email,$person_subject,wp_kses_post( wpautop( $send_message ) ),$from_name,$from_email ,null);

            


            
        }

    }
}
/**************************
 * GLOBAL BIRTHDAY  EMAIL
 ***************************/
add_action('church_admin_global_birthday_email','church_admin_global_birthday_email',1,2);
function church_admin_global_birthday_email($args)
{
    //church_admin_debug('*** church_admin_global_birthday_email ****');
    //church_admin_debugwp_date('y-m-d h:i:s'));
    $message = get_option('church_admin_global_birthday_template');
    $subject = $message['subject'];
    $from_name = !empty( $message['from_name'] )    ?   $message['from_name']   :   get_option('church_admin_default_from_name');
    $from_email = !empty( $message['from_email'] )  ?   $message['from_email']  :   get_option('church_admin_default_from_email');
    $reply_name = !empty($message['reply_name'])    ?   $message['reply_name']  :   get_option('church_admin_default_from_name');
    $reply_email = !empty($message['reply_email'])  ?   $message['reply_email'] :   get_option('church_admin_default_from_email');



    if(empty($message)){
        //church_admin_debug('No template setup');
        return;
    }
    global $wpdb;
    //church_admin_debug($args);

    //get today's birthdays
    
    //sort out member types stuff
    $people_types=get_option('church_admin_people_type');
    $member_types=church_admin_member_types_array();
    //church_admin_debug('member types array');
    //church_admin_debug($member_types);
    $tags = array();//for use if MailChimp
    $memb_sql='';
    
    if(!empty($args)){
        $membsql=array();
        foreach($args AS $key=>$id){
            $membsql[]=' (member_type_id="'.(int)$id.'") ';
            $tags[]=$member_types[$id];
        }
        if(!empty($memb_sql)){$memb_sql = 'AND ('.implode(' OR ',$membsql).')';}
    }else{
        $tags = $member_types;
    }
    //church_admin_debug('tags');
    //church_admin_debug($tags);
    $this_month = wp_date('m');
	$this_day = wp_date('d');
    $people = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE show_me=1 AND email_send=1 AND MONTH(date_of_birth)="'.(int)$this_month.'" AND DAY(date_of_birth)="'.(int)$this_day.'" '.$memb_sql);
    //church_admin_debug($wpdb->last_query);
    $tableStyle='style="font-family: Arial;font-size:1em; border-collapse: collapse;margin-bottom:10px"';
    $thStyle = 'style="border: 1px solid #ddd;padding: 12px; text-align: left; background-color: #CCC;color: white;" ';
    $tdStyle = 'style="border: 1px solid #ddd;padding: 8px;"';

    if(!empty($people)){
        //church_admin_debug('Processing people');
        $birthdaysTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Adult/Child','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>';
        foreach($people AS $person){
            $privacy = maybe_unserialize($person->privacy);
            $name=church_admin_formatted_name($person);
            /*
            $birthyear = mysql2date("Y",$person->date_of_birth);
			$currentyear = wp_date("Y");
			$age = $currentyear - $birthyear;
            */
            $adult_child = $people_types[$person->people_type_id];
            if(!empty($person->people_type_id) && $person->people_type_id==2 || $person->people_type_id==3 ){
                $parents_names = church_admin_parents($person->people_id);
                //translators: %1$s is a name of the parent
                if(!empty($parents_names)){$adult_child = sprintf(__('Child of %1$s','church-admin'),$parents_names);}
                $head_of_household_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$person->household_id.'"');
                if(!empty($head_of_household_details)){
                    $person->email = !empty($head_of_household_details->email) ? $head_of_household_details->email : null;
                    $person->mobile = !empty($head_of_household_details->mobile) ? $head_of_household_details->mobile : null;
                }
            }
            $email = (!empty($person->email) &&!empty($person->email_send) &&!empty($privacy['show-email']) ) ? '<a href="'.esc_url('mailto:'.$person->email).'">'.esc_html($person->email).'</a>':'&nbsp;';
            $mobile = (!empty($person->mobile) &&!empty($person->sms_send) &&!empty($privacy['show-mobile']) ) ? '<a href="'.esc_url('tel:'.$person->e164cell).'">'.esc_html($person->mobile).'</a>':'&nbsp;';
            $birthdaysTable.='<tr><td '.$tdStyle.'>'.esc_html($name).'</td><td '.$tdStyle.'>'.esc_html($adult_child).'</td><td '.$tdStyle.'>'.$email.'</td><td '.$tdStyle.'>'.$mobile.'</td></tr>';
        }
        $birthdaysTable.='</tbody></table>';
        
        $send_message = str_replace('[birthdays]',$birthdaysTable,wpautop($message['message']));
        $send_message = str_replace('[date]',wp_date(get_option('date_format')),$send_message);
    }
    else{
        return;
    }
    //church_admin_debug($send_message);
    //send to all
   
        //use native wp_mail/queued email
        $send_people = $wpdb->get_results('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE email_send=1 '.$memb_sql.' GROUP BY email');
        if(!empty($send_people)){
            foreach($send_people AS $person){
                if(!empty($person->email)){
                    //church_admin_debug('Sent to '.$person->email);
                    church_admin_email_send($person->email,$subject,$send_message,$from_name,$from_email,null,$reply_name,$reply_email);
                }
            }
        }

    

}
/**************************
 * HAPPY ANNIVERSARY EMAIL
 ***************************/
add_action('church_admin_happy_anniversary_email','church_admin_happy_anniversary_email',1,2);
function church_admin_happy_anniversary_email($args)
{
    $member_types = church_admin_member_types_array();
    $people_type_id=1;
    //church_admin_debug('*** church_admin_happy_anniversary_email ****');
    //church_admin_debugwp_date('y-m-d h:i:s'));
    global $wpdb;
    //church_admin_debug($args);
    $message = get_option('church_admin_happy_anniversary_template');
   
    if(empty($message)){
        //church_admin_debug('No template yet');
        return;
    }
    $subject = $message['subject'];
    $send_message = $message['message'];
    $from_name = !empty($message['from_name'])?$message['from_name'] : get_option('church_admin_default_from_name');
    $from_email = !empty($message['from_email'])?$message['from_email'] : get_option('church_admin_default_from_email');
    $reply_name = !empty($message['reply_name'])?$message['reply_name'] : get_option('church_admin_default_from_name');
    $reply_email = !empty($message['reply_email'])?$message['reply_email'] : get_option('church_admin_default_from_email');
    $this_month = wp_date('m');
	$this_day = wp_date('d');
    $memb_sql='';
    
    if(!empty($args)){
        $membsql=array();
        foreach($args AS $key=>$id){
            $membsql[]=' (member_type_id="'.(int)$id.'") ';
            $tags[]=$member_types[$id];
        }
        if(!empty($memb_sql)){$memb_sql = 'AND ('.implode(' OR ',$membsql).')';}
    }
    //get Households
    $households = $wpdb->get_results('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.show_me=1 AND a.email_send=1 AND MONTH(b.wedding_anniversary)="'.(int)$this_month.'" AND DAY(b.wedding_anniversary)="'.(int)$this_day.'" '.$memb_sql.' GROUP BY a.household_id');
    //church_admin_debug($wpdb->last_query);

    if(empty($households)){ return;}
  
    foreach($households AS $household)
    {
        //church_admin_debug('Processing households');
        $adults = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id = "'.(int)$household->household_id.'" AND people_type_id="'.(int)$people_type_id.'"');
        //church_admin_debug($wpdb->last_query);
        if(empty($adults)){continue;}
        //church_admin_debug('Adults found');
        $names = $first_names = $last_names=$emails = array();

        foreach($adults AS $adult)
        {
            $first_names[] = $adult->first_name;
            if(!in_array($adult->last_name,$last_names)){$last_names[] = $adult->last_name;}
            $names[]=church_admin_formatted_name($adult);
            if(!empty($adult->email)){$emails[]=$adult->email;}
        }
        //handle the fact that some couples have different last names (cultural not ethical BTW)
        if(count($last_names)==1){
            $name = implode (' & ',$first_names).' '.$last_names[0];
        }
        else
        {
            $name = implode (' & ',$names);
        }

        $weddingyear = $wpdb->get_var('SELECT YEAR(wedding_anniversary) FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household->household_id.'"');
        $currentyear = wp_date("Y");
        $no_of_years = $currentyear - $weddingyear;
        

       //sort subject shortcode
       $subject = str_replace('[couple_name]',$name,$subject);
       $subject = str_replace('[couple_names]',$name,$subject);
       //sort message shortcode
       $send_message = str_replace('[date]',$name,$send_message);
       $send_message = str_replace('[couple_name]',$name,$send_message);
       $send_message = str_replace('[couple_names]',$name,$send_message);
       $send_message = str_replace('[years]',$no_of_years,$send_message);
     
       //church_admin_debug($send_message);
       foreach($emails AS $key=>$to){
        church_admin_email_send( $to, $subject,  wpautop( $send_message ),$from_name,$from_email,null ,$reply_name,$reply_email);
       }
    
    }



}
/**************************
 * GLOBAL ANNIVERSARY EMAIL
 ***************************/
add_action('church_admin_global_anniversary_email','church_admin_global_anniversary_email',1,2);
function church_admin_global_anniversary_email($args)
{
    $member_types = church_admin_member_types_array();
    //church_admin_debug('*** church_admin_global_anniversary_email ****');
    //church_admin_debugwp_date('y-m-d h:i:s'));
    global $wpdb;
    //church_admin_debug($args);
    $message = get_option('church_admin_global_anniversary_template');
   
    if(empty($message)){
        //church_admin_debug('No template yet');
        return;
    }
    $people_type_id = 1;
    $subject = $message['subject'];
    $send_message = wpautop($message['message']);
    $from_name = !empty($message['from_name'])?$message['from_name'] : get_option('church_admin_default_from_name');
    $from_email = !empty($message['from_email'])?$message['from_email'] : get_option('church_admin_default_from_email');
    $reply_name = !empty($message['reply_name'])?$message['reply_name'] : get_option('church_admin_default_from_name');
    $reply_email = !empty($message['reply_email'])?$message['reply_email'] : get_option('church_admin_default_from_email');
    //sort out member types stuff
     $member_types=church_admin_member_types_array();
     //church_admin_debug('member types array');
     //church_admin_debug($member_types);
     $tags = array();//for use if MailChimp
     $memb_sql='';
     
     if(!empty($args)){
         $membsql=array();
         foreach($args AS $key=>$id){
             $membsql[]=' (member_type_id="'.(int)$id.'") ';
             $tags[]=$member_types[$id];
         }
         if(!empty($memb_sql)){$memb_sql = 'AND ('.implode(' OR ',$membsql).')';}
     }else{
         $tags = $member_types;
     }
     //church_admin_debug('tags');
     //church_admin_debug($tags);


    //get anniversaries

    $this_month = wp_date('m');
	$this_day = wp_date('d');
    $households = $wpdb->get_results('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.show_me=1 AND a.email_send=1 AND MONTH(b.wedding_anniversary)="'.(int)$this_month.'" AND DAY(b.wedding_anniversary)="'.(int)$this_day.'" '.$memb_sql.' GROUP BY a.household_id');
    //church_admin_debug($wpdb->last_query);

    if(empty($households)){ return;}
    $tableStyle='style="font-family: Arial; font-size:12px; border-collapse: collapse;"';
    $thStyle = 'style="border: 1px solid #ddd;padding: 12px; text-align: left; background-color: #CCC;color: white;" ';
    $tdStyle = 'style="border: 1px solid #ddd;padding: 8px;"';

    //church_admin_debug('Build anniversary table');
    $anniversaryTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>'."\r\n";
    foreach($households AS $household)
    {
        //church_admin_debug('Processing households');
        $adults = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id = "'.(int)$household->household_id.'" AND people_type_id="'.(int)$people_type_id.'"');
        //church_admin_debug($wpdb->last_query);
        if(empty($adults)){continue;}
        //church_admin_debug($adults);
        $names = $first_names = $last_names=$emails = $mobiles = array();

        foreach($adults AS $adult)
        {
            $privacy = maybe_unserialize($adult->privacy);
            $first_names[] = $adult->first_name;
            if(!in_array($adult->last_name,$last_names)){$last_names[] = $adult->last_name;}
            $names[]=church_admin_formatted_name($adult);
            if(!empty($adult->email) && !empty($privacy['show-email'])){
                $emails[]=make_clickable($adult->email);
            }
            if(!empty($adult->mobile) && !empty($privacy['show-mobile'])){
                $mobiles[]='<a href="'.esc_url('tel:'.$adult->e164).'>'.esc_html($adult->mobile).'</a>';;
            }
        }
        //handle the fact that some couples have different last names (cultural not ethical BTW)
        if(count($last_names)==1){
            $name = implode (' & ',$first_names).' '.$last_names[0];
        }
        else
        {
            $name = implode (' & ',$names);
        }
        /*
        $weddingyear = $wpdb->get_var('SELECT YEAR(wedding_anniversary) FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household->household_id.'"');
        $currentyear = wp_date("Y");
        $age = $currentyear - $weddingyear;
        */

        $anniversaryTable.='<tr><td '.$tdStyle.'>'.esc_html($name).'</td><td '.$tdStyle.'>'.implode('<br/>',$emails).'</td><td '.$tdStyle.'>'.implode('<br/>',$mobiles).'</td></tr>'."\r\n";
    
    
    }
    $anniversaryTable.='</tbody></table>'."\r\n";
    //church_admin_debug($anniversaryTable);
    
    //build message
    $send_message = str_replace('[anniversaries]',$anniversaryTable,$send_message);
    $send_message = str_replace('[date]',wp_date(get_option('date_format')),$send_message);
   
        //use native wp_mail/queued email
        $send_people = $wpdb->get_results('SELECT first_name,email FROM '.$wpdb->prefix.'church_admin_people WHERE email_send=1 '.$memb_sql.' GROUP BY email');
        if(!empty($send_people)){
            foreach($send_people AS $person){
                if(!empty($person->email)){

                    $subject = str_replace('[first_name]',$person->first_name,$subject);


                    //church_admin_debug('Sent to '.$person->email);
                    church_admin_email_send($person->email,$subject,$send_message,$from_name,$from_email,null ,$reply_name,$reply_email);
                
                }
            }
        }

    
}

add_action('church_admin_global_birthday_and_anniversary_email','church_admin_global_birthday_and_anniversary_email',1,2);
function church_admin_global_birthday_and_anniversary_email($args)
{
 
    $people_type_id=1;
    $people_types=get_option('church_admin_people_type');
    //church_admin_debug('*** church_admin_global_birthday_and_anniversary_email ****');
    //church_admin_debugwp_date('y-m-d h:i:s'));
    $message = get_option('church_admin_global_both_template');
    $subject = $message['subject'];
    if(empty($message)){
        //church_admin_debug('No template yet');
        return;
    }
    $from_name = !empty($message['from_name'])?$message['from_name'] : get_option('church_admin_default_from_name');
    $from_email = !empty($message['from_email'])?$message['from_email'] : get_option('church_admin_default_from_email');
    $reply_name = !empty($message['reply_name'])?$message['reply_name'] : get_option('church_admin_default_from_name');
    $reply_email = !empty($message['reply_email'])?$message['reply_email'] : get_option('church_admin_default_from_email');
    global $wpdb;
    //church_admin_debug($args);

    //sort out member types stuff
    $member_types=church_admin_member_types_array();
    $tags = array();//for use if MailChimp
    $memb_sql='';
    
    if(!empty($args)){
        $membsql=array();
        foreach($args AS $key=>$id){
            $membsql[]=' (member_type_id="'.(int)$id.'") ';
            $tags[]=$member_types[$id];
        }
        if(!empty($memb_sql)){$memb_sql = 'AND ('.implode(' OR ',$membsql).')';}
    }

    //get birthdays
    $this_month = wp_date('m');
	$this_day = wp_date('d');
    /*
    $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE  MONTH(date_of_birth)="'.(int)$this_month.'" AND DAY(date_of_birth)="'.(int)$this_day.'" '.$memb_sql);
   */
    
    //do queries
    $people = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE show_me=1 AND email_send=1 AND MONTH(date_of_birth)="'.(int)$this_month.'" AND DAY(date_of_birth)="'.(int)$this_day.'" '.$memb_sql);
    //church_admin_debug($wpdb->last_query);
    //church_admin_debug('Number of rows '.$wpdb->num_rows);
    $households = $wpdb->get_results('SELECT a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.show_me=1 AND a.email_send=1 AND MONTH(b.wedding_anniversary)="'.(int)$this_month.'" AND DAY(b.wedding_anniversary)="'.(int)$this_day.'" '.$memb_sql.' GROUP BY a.household_id');
    //church_admin_debug($wpdb->last_query);
    //church_admin_debug('Number of rows '.$wpdb->num_rows);
    if(empty($people) && empty($households)){
        //empty
        //church_admin_debug('No birthdays or anniversaries today');
        return;
    }
    
    
    //church_admin_debug($wpdb->last_query);
    $tableStyle='style="font-family: Arial;font-size:1em; border-collapse: collapse;margin-bottom:10px"';
    $thStyle = 'style="border: 1px solid #ddd;padding: 12px; text-align: left; background-color: #CCC;color: white;" ';
    $tdStyle = 'style="border: 1px solid #ddd;padding: 8px;"';
    $birthdaysTable = null;
    if(!empty($people)){

       
        //church_admin_debug('Build birthday table');
        $birthdaysTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Adult/Child','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>';
        foreach($people AS $person){
            $name=church_admin_formatted_name($person);
           
            $adult_child = $people_types[$person->people_type_id];
            if(!empty($person->people_type_id) && ($person->people_type_id==2 || $person->people_type_id==3 )){
                $parents_names = church_admin_parents($person->people_id);
                //translators: %1$s is a name of the parent
                if(!empty($parents_names)){$adult_child = sprintf(__('Child of %1$s','church-admin'),$parents_names);}
                $head_of_household_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND household_id="'.(int)$person->household_id.'"');
                if(!empty($head_of_household_details)){
                    $person->email = !empty($head_of_household_details->email) ? $head_of_household_details->email : null;
                    $person->mobile = !empty($head_of_household_details->mobile) ? $head_of_household_details->mobile : null;
                }
            }
            $email = (!empty($person->email) &&!empty($person->email_send) ) ? '<a href="'.esc_url('mailto:'.$person->email).'">'.esc_html($person->email).'</a>':'&nbsp;';
            $mobile = (!empty($person->mobile) &&!empty($person->sms_send) ) ? '<a href="'.esc_url('tel:'.$person->e164cell).'">'.esc_html($person->mobile).'</a>':'&nbsp;';
            $birthdaysTable.='<tr><td '.$tdStyle.'>'.esc_html($name).'</td><td '.$tdStyle.'>'.esc_html($adult_child).'</td><td '.$tdStyle.'>'.$email.'</td><td '.$tdStyle.'>'.$mobile.'</td></tr>';
        }
        $birthdaysTable.='</tbody></table>';
        //church_admin_debug($birthdaysTable);
    }

    //get anniversaries
    
    $anniversaryTable = null;
    if(!empty($households)){
        //church_admin_debug('Build anniversary table');
        $anniversaryTable='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Name','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th></tr></thead><tbody>'."\r\n";
        //church_admin_debug('Processing households');
        foreach($households AS $household)
        {
            
            $adults = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id = "'.(int)$household->household_id.'" AND people_type_id="'.(int)$people_type_id.'"');
			//church_admin_debug($wpdb->last_query);
            //church_admin_debug('Number of rows '.$wpdb->num_rows);
            if(empty($adults)){
                //church_admin_debug('No adults found');
                continue;
            }
			$names = $first_names = $last_names=$emails =$mobiles =  array();

			foreach($adults AS $adult)
			{
				$first_names[] = $adult->first_name;
				if(!in_array($adult->last_name,$last_names)){$last_names[] = $adult->last_name;}
				$names[]=church_admin_formatted_name($adult);
                
                $privacy=maybe_unserialize($adult->privacy);
                if(!empty($adult->email) && !empty($privacy['show-email'])){$emails[]=make_clickable($adult->email);}
                if(!empty($adult->mobile) && !empty($privacy['show-cell'])){$mobiles[]='<a href="'.esc_url('tel:'.$adult->e164cell).'">'.esc_html($adult->mobile).'</a>';}
			}
			//handle the fact that some couples have different last names (cultural not ethical BTW)
			if(count($last_names)==1){
				$name = implode (' & ',$first_names).' '.$last_names[0];
			}
			else
			{
				$name = implode (' & ',$names);
			}
            //church_admin_debug('Names for this household '.$name);
         
            

            $anniversaryTable.='<tr><td '.$tdStyle.'>'.esc_html($name).'</td><td '.$tdStyle.'>'.implode('<br/>',$emails).'</td><td '.$tdStyle.'>'.implode('<br/>',$mobiles).'</td></tr>'."\r\n";
        
       
        }
        $anniversaryTable.='</tbody></table>'."\r\n";
        //church_admin_debug($anniversaryTable);
        //church_admin_debug('Finished processing anniversary table');
    }

    /**********************************
     * Build message
     ***********************************/
    $send_message = $message['message'];


    //birthday_text
    $tag = 'birthday_text';
    $regex = '\\['                             // Opening bracket.
    . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
    . "($tag)"                     // 2: Shortcode name.
    . '(?![\\w-])'                       // Not followed by word character or hyphen.
    . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
    .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
    .     '(?:'
    .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
    .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
    .     ')*?'
    . ')'
    . '(?:'
    .     '(\\/)'                        // 4: Self closing tag...
    .     '\\]'                          // ...and closing bracket.
    . '|'
    .     '\\]'                          // Closing bracket.
    .     '(?:'
    .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
    .             '[^\\[]*+'             // Not an opening bracket.
    .             '(?:'
    .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
    .                 '[^\\[]*+'         // Not an opening bracket.
    .             ')*+'
    .         ')'
    .         '\\[\\/\\2\\]'             // Closing shortcode tag.
    .     ')?'
    . ')'
    . '(\\]?)';  

    
    preg_match_all( '/'. $regex .'/s', $send_message, $matches );
		
	
    if(!empty($birthdaysTable)){
        // replace  [birthday_text] and [birthdays]
        $send_message = str_replace($matches[0],$matches[5][0],$send_message);
        $send_message = str_replace('[birthdays]',$birthdaysTable,$send_message);

    }else
    {
        //no birthdays so strip out [birthday_text] and [birthdays]
        $send_message = str_replace($matches[0],'',$send_message);
        $send_message = str_replace('[birthdays]','',$send_message);
    }
    //anniversaries
    $tag = 'anniversary_text';
    $regex = '\\['                             // Opening bracket.
    . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
    . "($tag)"                     // 2: Shortcode name.
    . '(?![\\w-])'                       // Not followed by word character or hyphen.
    . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
    .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
    .     '(?:'
    .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
    .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
    .     ')*?'
    . ')'
    . '(?:'
    .     '(\\/)'                        // 4: Self closing tag...
    .     '\\]'                          // ...and closing bracket.
    . '|'
    .     '\\]'                          // Closing bracket.
    .     '(?:'
    .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
    .             '[^\\[]*+'             // Not an opening bracket.
    .             '(?:'
    .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
    .                 '[^\\[]*+'         // Not an opening bracket.
    .             ')*+'
    .         ')'
    .         '\\[\\/\\2\\]'             // Closing shortcode tag.
    .     ')?'
    . ')'
    . '(\\]?)';  

    
    preg_match_all( '/'. $regex .'/s', $send_message, $matches );
		
	
    if(!empty($anniversaryTable)){
        // replace  [birthday_text] and [birthdays]
        $send_message = str_replace($matches[0],$matches[5][0],$send_message);
        $send_message = str_replace('[anniversaries]',$anniversaryTable,$send_message);

    }else
    {
        //no birthdays so strip out [birthday_text] and [birthdays]
        $send_message = str_replace($matches[0],'',$send_message);
        $send_message = str_replace('[anniversaries]','',$send_message);
    }

    $send_message = str_replace('[date]',wp_date(get_option('date_format')),$send_message);

     //church_admin_debug('*** Finished message ***');
    //church_admin_debug($send_message);
  
    //send to all
    
        //use native wp_mail/queued email
        $send_people = $wpdb->get_results('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE email_send=1 '.$memb_sql.' GROUP BY email');
        if(!empty($send_people)){
            foreach($send_people AS $person){
                if(!empty($person->email)){
                    //church_admin_debug('Sent to '.$person->email);
                    church_admin_email_send($person->email,$subject,$send_message,$from_name,$from_email,null,$reply_name,$reply_email);
                }
            }
        }

    

    

}

add_action('church_admin_custom_fields_automations','church_admin_custom_fields_automations',1,2);
function church_admin_custom_fields_automations()
{
    global $wpdb;
    //church_admin_debug('*** church_admin_custom_fields_automations ****');
    $automations = get_option('church_admin_custom_fields_automations');
    if(empty($automations)){
        //church_admin_debug('No custom field automations');
        return;
    }
    $custom_fields = church_admin_custom_fields_array();

    foreach($automations AS $key=>$auto){
       /*
       //here is the structure of each $auto...
       $args=array('name'=>$name,
                    'custom_id'=>$custom_id,
                    'contacts' =>$people_ids,
                    'email_type' =>$email_type
        );
        */
        $people_ids=maybe_unserialize($auto['contacts']);
        $emails=array();
        foreach($people_ids AS $x=>$people_id){
            $emails[]=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
        }
        $emails = array_filter($emails);
        $custom_field_details = $custom_fields[$auto['custom_id']];
        $transients = get_option('church_admin_transient_custom_id'.$auto['custom_id']);
        if(empty($transients)){continue;}
        /*
        * Here is the structure of each $transient...   
        *    array('from_name'=>$from_name, 'from_email'=>$from_email,people_id'=>$people_id,'household'=>$household_id,'old_value'=>$old_value,'new_value'=>$new_value)
        */
        $from_name = !empty($transients['from_name'])?$transients['from_name'] : get_option('church_admin_default_from_name');
        $from_email = !empty($transients['from_email'])?$transients['from_email'] : get_option('church_admin_default_from_email');
        $tableStyle='style="font-family: Arial;font-size:1em; border-collapse: collapse;margin-bottom:10px"';
        $thStyle = 'style="border: 1px solid #ddd;padding: 12px; text-align: left; background-color: #CCC;color: white;" ';
        $tdStyle = 'style="border: 1px solid #ddd;padding: 8px;"';
        $table='<table '.$tableStyle.'><thead><tr><th '.$thStyle.'>'.esc_html(__('Person','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Email','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Cell','church-admin')).'</th><th '.$thStyle.'>'.esc_html(__('Old value')).'</th><th '.$thStyle.'>'.esc_html(__('New value','church-admin')).'</th></tr></thead><tbody>';

        foreach($transients AS $y=>$tr){
            $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$tr['people_id'].'"');
            if(empty($person)){continue;}
            $name = church_admin_formatted_name($person);
            $email = !empty($person->email)?make_clickable($person->email):'';
            $cell = !empty($person->mobile)?'<a href="tel:'.$person->e164cell.'">'.$person->mobile.'</a>':'';
            $table.='<tr><td '.$tdStyle.'>'.esc_html($name).'</a></td><td '.$tdStyle.'>'.$email.'</td><td '.$tdStyle.'>'.$cell.'</td><td '.$tdStyle.'>'.esc_html($tr['old_value']).'</td><td '.$tdStyle.'>'.esc_html($tr['new_value']).'</td></tr>';
        }
        $table.='</tbody></table>';
        //translators: %1$s is a name of the custom field
        $subject = esc_html(sprintf(__('Changes to custom field %1$s','church-admin'),$custom_field_details['name']));
        $message='<p>'.$subject.'</p>';
        $message.=$table;
        //church_admin_debug($message);
        foreach($emails AS $z=>$to){
            //church_admin_debug('Sent to '.$to);
            church_admin_email_send($to,$subject,$message,$from_name,$from_email,null);
        }
        delete_option('church_admin_transient_custom_id'.$auto['custom_id']);
    }
}


if(is_multisite()){
    add_filter('upload_dir', 'church_admin_fix_upload_paths');
}

function church_admin_fix_upload_paths($data)
{
    $add = '/sites/'.get_current_blog_id();
    //check whether this has already been done
    $pos = strpos($data['basedir'], $add);
    if ($pos === false) {
        $data['basedir'] = $data['basedir'].'/sites/'.get_current_blog_id();
        $data['path'] = $data['basedir'].$data['subdir'];
        $data['baseurl'] = $data['baseurl'].'/sites/'.get_current_blog_id();
        $data['url'] = $data['baseurl'].$data['subdir'];
    } else {
       // do nothing because it has already been done
    } 
    return $data;
}




function church_admin_test_function(){
    echo'Nothing';   
}