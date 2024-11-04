<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

function church_admin_permissions()
{

	global $wpdb;
	if(!current_user_can('manage_options') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html( __('Only site administrators can change options')).'</h2></div>';
		return;
	}
	$check=$wpdb->get_var('SELECT COUNT(user_id) FROM '.$wpdb->prefix.'church_admin_people');
	if ( empty( $check) )

	{

		echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Please create or connect Wordpress User accounts for people in the directory first.','church-admin' ) ).'</strong></p></div>';
		return;
	}

	echo '<h2>'.esc_html( __('Who is allowed to do what?','church-admin' ) ).'</h2>';
	echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/permissions-and-roles/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
	if(!empty( $_POST['save'] ) )

		{//form saved
			$user_permissions=array();
			
			unset( $_POST['save'] );
			if(!empty( $_POST['delete_all'] ) )  {delete_option('church_admin_user_permissions');echo'<div class="notice notice-success inline"><p>'.esc_html( __('No individual user permissions are stored','church-admin' ) ).'</p></div>';}
			else
			{
				if(!empty( $_POST['Gifts'] ) )
				{
					$gifts=church_admin_get_user_id( church_admin_sanitize($_POST['Gifts'] ));
					if(!empty( $gifts) )$user_permissions['Gifts']=$gifts;
				}
				if(!empty( $_POST['Bible'] ) )
				{
					$bible=church_admin_get_user_id( church_admin_sanitize($_POST['Bible'] ));
					if(!empty( $bible) )$user_permissions['Bible']=$bible;
				}
				if(!empty( $_POST['Check_in'] ) )
				{
					$checkin=church_admin_get_user_id( church_admin_sanitize($_POST['Check_in'] ));
					if(!empty( $checkin) )$user_permissions['Check_in']=$checkin;
				}
				if(!empty( $_POST['Classes'] ) )
				{
					$classes=church_admin_get_user_id( church_admin_sanitize($_POST['Classes'] ));
					if(!empty( $classes) )$user_permissions['Classes']=$classes;
				}
				if(!empty( $_POST['Contact_Form'] ) )
				{
					$contact_form=church_admin_get_user_id( church_admin_sanitize($_POST['Contact_Form'] ));
					if(!empty( $contact_form) )$user_permissions['Contact_Form']=$contact_form;
				}
				if(!empty( $_POST['Directory'] ) )
				{
					$directory=church_admin_get_user_id( church_admin_sanitize($_POST['Directory'] ));
					if(!empty( $directory) )$user_permissions['Directory']=$directory;
				}
				if(!empty( $_POST['Events'] ) )
				{
					$events=church_admin_get_user_id( church_admin_sanitize($_POST['Events'] ));
					if(!empty( $events) )$user_permissions['Events']=$events;
				}
				if(!empty( $_POST['Kidswork'] ) )
				{
					$kidswork=church_admin_get_user_id( church_admin_sanitize($_POST['Kidswork']) );
					if(!empty( $kidswork) )$user_permissions['Kidswork']=$kidswork;
				}
				if(!empty( $_POST['Calendar'] ) )
				{
					$calendar=church_admin_get_user_id( church_admin_sanitize($_POST['Calendar'] ) );
					if(!empty( $calendar) )$user_permissions['Calendar']=$calendar;
				}

				
				if(!empty( $_POST['Sermons'] ) )
				{
					$sermons=church_admin_get_user_id( church_admin_sanitize($_POST['Sermons']) );
					if(!empty( $sermons) )$user_permissions['Sermons']=$sermons;
				}

				if(!empty( $_POST['Funnel'] ) )
				{
					$funnel=church_admin_get_user_id( church_admin_sanitize($_POST['Funnel']) );
					if(!empty( $funnel) )$user_permissions['Funnel']=$funnel;
				}
				if(!empty( $_POST['Inventory'] ) )
				{
					$Inventory=church_admin_get_user_id( church_admin_sanitize($_POST['Inventory']) );
					if(!empty( $Inventory) )$user_permissions['Inventory']=$Inventory;
				}
				if(!empty( $_POST['Bulk_Email'] ) )
				{
					$Bulk_Email=church_admin_get_user_id( church_admin_sanitize($_POST['Bulk_Email']) );
					if(!empty( $Bulk_Email) )$user_permissions['Bulk_Email']=$Bulk_Email;
				}
				if(!empty( $_POST['Bulk_SMS'] ) )
				{
					$sms=church_admin_get_user_id( church_admin_sanitize($_POST['Bulk_SMS']) );
					if(!empty( $sms) )$user_permissions['Bulk_SMS']=$sms;
				}

				if(!empty( $_POST['Facilities'] ) )
				{
					$facilities=church_admin_get_user_id( church_admin_sanitize($_POST['Facilities'] ));
					if(!empty($facilities) )$user_permissions['Facilities']=$facilities;
				}
				if(!empty( $_POST['Attendance'] ) )
				{
					$att=church_admin_get_user_id( church_admin_sanitize($_POST['Attendance'] ) );
					if(!empty( $att) )$user_permissions['Attendance']=$att;
				}
			
				if(!empty( $_POST['Ministries'] ) )
				{
					$mi=church_admin_get_user_id( church_admin_sanitize($_POST['Ministries']) );
					if(!empty( $mi) )$user_permissions['Ministries']=$mi;
				}
				if(!empty( $_POST['Pastoral'] ) )
				{
					$pa=church_admin_get_user_id( church_admin_sanitize($_POST['Pastoral']) );
					if(!empty( $pa) )$user_permissions['Pastoral']=$pa;
				}
				if(!empty( $_POST['Push'] ) )
				{
					$push=church_admin_get_user_id( church_admin_sanitize($_POST['Push']) );
					if(!empty( $push) )$user_permissions['Push']=$push;
				}
				if(!empty( $_POST['Groups'] ) )
				{
					$sg=church_admin_get_user_id( $_POST['Groups'] );
					if(!empty( $sg) )$user_permissions['Groups']=$sg;
				}
				if(!empty( $_POST['Service'] ) )
				{
					$service=church_admin_get_user_id( church_admin_sanitize($_POST['Service']) );
					if(!empty( $service) )$user_permissions['Service']=$service;
				}
				
				if(!empty( $_POST['Giving'] ) )
				{
					$giving=church_admin_get_user_id( church_admin_sanitize($_POST['Giving'] ) );
					if(!empty( $giving) )$user_permissions['Giving']=$giving;
				}

				if(!empty( $_POST['Units'] ) )
				{
					$units=church_admin_get_user_id( church_admin_sanitize($_POST['Units'] ) );
					if(!empty( $units) )$user_permissions['Units']=$units;
				}
				if(!empty( $_POST['Sessions'] ) )
				{
					$sessions=church_admin_get_user_id( church_admin_sanitize( $_POST['Sessions'] ));
					if(!empty( $sessions) )$user_permissions['Sessions']=$sessions;
				}
				if(!empty( $_POST['Prayer_Requests'] ) )
				{
					$Prayer_Requests=church_admin_get_user_id( church_admin_sanitize( $_POST['Prayer_Requests'] ) );
					if(!empty($Prayer_Requests) )$user_permissions['Prayer Requests']=$Prayer_Requests;
				}
				if(!empty( $user_permissions) )

				{//some people have been specified so save them



					echo'<div class="notice notice-success inline"><p><strong>'.esc_html(__('Permissions Saved','church-admin') ).'</strong></p></div>';

					update_option('church_admin_user_permissions',$user_permissions);

				}

				else

				{//no-one specified, make sure option is deleted

					delete_option('church_admin_user_permissions');

					echo'<div class="notice notice-success inline"><p>'.esc_html( __('No individual user permissions are stored','church-admin' ) ).'</p></div>';

				}

			}

		}//form saved

		

			$user_permissions=get_option('church_admin_user_permissions');
			if(empty($user_permissions)){$user_permissions=array();}
			if ( empty( $user_permissions['Directory'] ) )$user_permissions['Directory']='';
			if ( empty( $user_permissions['Classes'] ) )$user_permissions['Classes']='';
			
			if ( empty( $user_permissions['Bible'] ) ) $user_permissions['Bible']='';
			if ( empty( $user_permissions['Bulk_SMS'] ) ) $user_permissions['Bulk_SMS']='';
			if ( empty( $user_permissions['Events'] ) ) $user_permissions['Events'] ='';
			if ( empty( $user_permissions['Facilities'] ) ) $user_permissions['Facilities'] ='';
			if ( empty( $user_permissions['Bulk_Email'] ) ) $user_permissions['Bulk_Email'] ='';
			if ( empty( $user_permissions['Push'] ) ) $user_permissions['Push'] ='';
			if ( empty( $user_permissions['Sermons'] ) ) $user_permissions['Sermons'] = '';
			if ( empty( $user_permissions['Check_in'] ) ) $user_permissions['Check_in'] = '';
			if ( empty( $user_permissions['Calendar'] ) ) $user_permissions['Calendar'] = '';
			if ( empty( $user_permissions['Gifts'] ) ) $user_permissions['Gifts'] = '';
			if ( empty( $user_permissions['Inventory'] ) ) $user_permissions['Inventory'] ='';
			if ( empty( $user_permissions['Attendance'] ) ) $user_permissions['Attendance'] ='';
			if ( empty( $user_permissions['Ministries'] ) ) $user_permissions['Ministries'] ='';
			if ( empty( $user_permissions['Pastoral'] ) ) $user_permissions['Pastoral'] ='';
			if ( empty( $user_permissions['Funnel'] ) ) $user_permissions['Funnel']='';
			if ( empty( $user_permissions['Contact_Form'] ) ) $user_permissions['Contact_Form'] ='';
			
			if ( empty( $user_permissions['Pastoral'] ) ) $user_permissions['Pastoral'] ='';
			if ( empty( $user_permissions['Groups'] ) ) $user_permissions['Groups'] ='';
			if ( empty( $user_permissions['Contact form'] ) )$user_permissions['Contact form']='';
			if ( empty( $user_permissions['Service'] ) ) $user_permissions['Service'] = '';
			if ( empty( $user_permissions['Prayer Requests'] ) ) $user_permissions['Prayer Requests'] = '';
			if ( empty( $user_permissions['Kidswork'] ) ) $user_permissions['Kidswork'] = '';
			if ( empty( $user_permissions['Giving'] ) ) $user_permissions['Giving'] = '';
			if ( empty( $user_permissions['Units'] ) ) $user_permissions['Units'] = '';
			if ( empty( $user_permissions['Sessions'] ) ) $user_permissions['Sessions'] = '';
			echo'<form action="admin.php?page=church_admin/index.php&section=settings&action=permissions" method="post">';
			wp_nonce_field('permissions');
		//church_admin_autocomplete( $name='people',$first_id='friends',$second_id='to',$current_data=array(),$user_id=FALSE)
			echo'<table class="form-table"><tbody>';
			echo'<tr><th scope="row" >'.esc_html( __('Delete All user permissions','church-admin' ) ).'</th><td><input type="checkbox" class="delete_all_permissions" value="yes" name="delete_all" />'.esc_html( __("Don't forget to save!",'church-admin' ) ).'</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Attendance','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Attendance','auto-attendance','att',$user_permissions['Attendance'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Bible Readings','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Bible','auto-bible','email',$user_permissions['Bible'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Bulk Email','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Bulk_Email','auto-bulk-email','email',$user_permissions['Bulk_Email'],TRUE);
			echo '</td></tr>';

			echo'<tr><th scope="row" >'.esc_html( __('Bulk SMS','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Bulk_SMS','auto-bulk-sms','sms',$user_permissions['Bulk_SMS'],TRUE);
			echo '</td></tr>';

			echo'<tr><th scope="row" >'.esc_html( __('Calendar','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Calendar','auto-calendar','cal',$user_permissions['Calendar'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Check-in','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Check_in','auto-check-in','che',$user_permissions['Check_in'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Classes','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Classes','auto-Classes','cla',$user_permissions['Classes'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Contact Form','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Contact_Form','auto-contact','con',$user_permissions['Contact_Form'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Directory','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Directory','auto-Directory','dir',$user_permissions['Directory'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Events','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Events','auto-events','eve',$user_permissions['Events'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Follow Up Funnels','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Funnel','auto-funnel','funn',$user_permissions['Funnel'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Spiritual Gifts','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Gifts','auto-gifts','gif',$user_permissions['Gifts'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Giving','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Giving','auto-giving','giv',$user_permissions['Giving'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Groups','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Groups','auto-small-groups','sg',$user_permissions['Groups'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Inventory','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Inventory','auto-inventory','inv',$user_permissions['Inventory'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Kidswork','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Kidswork','auto-kidswork','ki',$user_permissions['Kidswork'],TRUE);
			echo '</td></tr>';


			echo'<tr><th scope="row" >'.esc_html( __('Ministries','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Ministries','auto-ministries','mi',$user_permissions['Ministries'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Pastoral','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Pastoral','auto-pastoral','pa',$user_permissions['Pastoral'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Prayer Requests','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Prayer_Requests','auto-prayer-chain','pr',$user_permissions['Prayer Requests'],TRUE);
			echo '</td></tr>';
			echo'<tr><th scope="row" >'.esc_html( __('Push Messaging','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Push','auto-prayer-chain','pr',$user_permissions['Push'],TRUE);
			echo '</td></tr>';
			

			echo'<tr><th scope="row" >'.esc_html( __('Sermons','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Sermons','auto-sermons','ser',$user_permissions['Sermons'],TRUE);
			echo '</td></tr>';

			echo'<tr><th scope="row" >'.esc_html( __('Service','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Service','auto-service','ser',$user_permissions['Service'],TRUE);
			echo '</td></tr>';

			echo'<tr><th scope="row" >'.esc_html( __('Sessions','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Sessions','auto-sessions','ses',$user_permissions['Sessions'],TRUE);
			echo '</td></tr>';

			

			echo'<tr><th scope="row" >'.esc_html( __('Units','church-admin' ) ).'</th><td>';
			echo church_admin_autocomplete('Units','auto-units','un',$user_permissions['Units'],TRUE);
			echo '</td></tr>';

			echo'<tr><th scope="row" >&nbsp;</th><td><input type="hidden" name="save" value="yes" /><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" class="button-primary" /></td></tr></tbody></table>';

			echo'</form>';
			echo'<script type="text/javascript">jQuery(document).ready(function( $) {
			$(".delete_all_permissions").click(function()  {
				$(".to").val("");

			});
	});</script>';






}//end function
