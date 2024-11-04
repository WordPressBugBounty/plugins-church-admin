<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * This function sets up which modules are displayed on the tabs, default on install is all displayed
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 *	2017-01-02 Improved display, using translated strings
 *
 */
function church_admin_modules()
{
	$modules=get_option('church_admin_modules');
	asort($modules);
	
	if(!empty( $_POST['save-ca-modules'] ) )
	{

		foreach( $modules AS $mod=>$status)
		{
			if ( empty( $_POST[$mod] ) )  {$modules[$mod]=FALSE;}else {$modules[$mod]=TRUE;}
		}

		update_option('church_admin_modules',$modules);
        echo '<div class="notice notice-success inline"><h2>'.esc_html( __('Modules updated - refresh page to show changes in menu')).'</h2></div>';
	}

		echo'<h1 class="modules">'.esc_html( __('Set which module tabs are visible ','church-admin' ) ).'</h2>';
		echo'<form action="" method="POST">';
		echo'<table class="form-table"><tbody>';
		$toggle=TRUE;
		foreach( $modules AS $mod=>$status)
		{
			//Need to be translateable, so used stored data and convert to translated display string
			switch( $mod)
			{
				case 'Automations':$display = __('Automations','church-admin'); break;
				case 'Keydates':$display=__('Key dates','church-admin');break;
				case 'Support':$display=__('Support Church Admin','church-admin');break;
				case'Contact':$display=__('Contact','church-admin');break;
				case'People':$display=__('People','church-admin');break;
				
				case'Children':$display=__('Children','church-admin');break;
				case'Comms':$display=__('Comms','church-admin');break;
				case'Groups':$display=__('Groups','church-admin');break;
				case'Calendar':$display=__('Calendar','church-admin');break;
                case'Giving':$display=__('Giving','church-admin');break;
				case'Media':$display=__('Media','church-admin');break;
				case'Facilities':$display=__('Facilities','church-admin');break;
				case'Ministries':$display=__('Ministries','church-admin');break;
				case'Services':$display=__('Services','church-admin');break;
				case'Sessions':$display=__('Sessions','church-admin');break;
				case'Classes':$display=__('Classes','church-admin');break; //Added by Jostein 3.04.2019
				case'Attendance':$display=__('Attendance','church-admin');break; //Added by Jostein 3.04.2019
				case'Units':$display=__('Units','church-admin');break;
                case "Events": $display=__('Events','church-admin');break;
				case "Gifts":$display=__('Spiritual Gifts','church-admin');break;
				default:$display=$mod;break;
			}
			if( $mod!='Podcast' && $mod!='App')
			{
				echo'<tr>';
				echo'<th scope="row">'.esc_html( $display).'</th><td><input type="checkbox" value="TRUE" name="'.esc_html( $mod).'" ';
				if(!empty( $status) ) echo' checked="checked" ';
				echo' /></td>';
				echo'</tr>';
			}
		}

		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save-ca-modules" value="TRUE" /><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" class="button-primary" /></td></tr>';
		echo'</tbody></table></form>';
	
}

/**
 * This function is for email settings
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */

function church_admin_email_settings()
{
	global $wpdb;


	echo'<h1>'.esc_html( __('Email Settings in Church Admin Plugin','church-admin' ) ).'</h1>';
	echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/setting-up-email-with-church-admin-plugin/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
   
	if(!church_admin_level_check('Email') )return __("You don't have permission to change email settings",'church-admin');



	if(!empty( $_POST['save-email-settings'] ) )
	{
		//default from
		update_option('church_admin_default_from_name',church_admin_sanitize($_POST['church_admin_default_from_name']));
		update_option('church_admin_default_from_email',church_admin_sanitize($_POST['church_admin_default_from_email']));
		/***************************
		 * METHOD
		 ****************************/
		switch( $_POST['email-method'] )
		{
			case 'website':
				update_option('church_admin_transactional_email_method','native');
				update_option('church_admin_bulk_email_method','native');
				delete_option('church_admin_mailchimp_settings');
				
			break;
			case 'smtpserver':
				update_option('church_admin_transactional_email_method','smtpserver');
				update_option('church_admin_bulk_email_method','smtpserver');
				delete_option('church_admin_mailchimp_settings');

			break;
			
		}
		switch($_POST['bulk-email-method']){

			
			case 'website':
				update_option('church_admin_bulk_email_method','native');
				update_option('church_admin_bulk_email_method','native');
				delete_option('church_admin_mailchimp_settings');
			break;
			case 'smtpserver':
				update_option('church_admin_bulk_email_method','smtpserver');
				
				delete_option('church_admin_mailchimp_settings');

			break;
		}
		if(!empty($_POST['mailersend-api'])){
			update_option('church_admin_transactional_email_method','mailersend');
			update_option('church_admin_bulk_email_method','mailersend');
			$mailersend_api = church_admin_sanitize($_POST['mailersend-api']);
			update_option('church_admin_mailersend_api_key',$mailersend_api);
		}else{
			delete_option('church_admin_mailersend_api_key');
		}

		/***************************
		 * CRON
		 ****************************/
		if(!empty( $_POST['quantity'] ) )  {
			update_option('church_admin_bulk_email',church_admin_sanitize($_POST['quantity']) );
		}else{
			delete_option('church_admin_bulk_email');
		}
		if(!empty( $_POST['cron'] ) )
		{
			$message='';
			update_option('church_admin_cron',church_admin_sanitize($_POST['cron'] ));
			switch( $_POST['cron'] )
			{
				case'wp-cron':
								wp_clear_scheduled_hook('church_admin_bulk_email');
								add_action('church_admin_bulk_email','church_admin_bulk_email');
								$timestamp=time();
								wp_schedule_event( $timestamp, 'hourly', 'church_admin_bulk_email');
				break;
				case 'cron':
								wp_clear_scheduled_hook('church_admin_bulk_email');
								$message='<p><a  class="button-secondary"  target="_blank" href="'.site_url().'/?ca_download=cron-instructions&amp;cron-instructions='.wp_create_nonce('cron-instructions').'">'.esc_html( __('PDF Instructions for email cron setup','church-admin' ) ).'</a></p>';
				break;
				default:
								wp_clear_scheduled_hook('church_admin_bulk_email');
								update_option('church_admin_cron','immediate');
	      	    break;
			}
		}
		else{delete_option('church_admin_cron');}
		/***************************
		 * SMTP
		 ****************************/
		$expected=array('host','auth','port','username','password','secure','from','from_name','reply_email','reply_name');
		
		foreach( $expected AS $key=>$value)
		{
			if(!empty( $_POST[$value] ) )  {
				$settings[$value]=church_admin_sanitize( $_POST[$value] );
			}
			
		}
		if(!empty($settings['secure']) && $settings['secure'] == __('Choose...','church-admin')){unset($settings['secure']);}
		
		if(!empty( $settings) )
		{
			update_option('church_admin_smtp_settings',$settings);
		}
		else
		{
			delete_option('church_admin_smtp_settings');
			
		}
		echo'<div class="notice notice-success inline"><h2>'.esc_html( __('Email settings saved','church-admin' ) ).'</h2>';
	
		echo '<p><a class="button-secondary"  href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=test-email','test-email').'">'.esc_html( __('Send test email','church-admin' ) ).'</a></p>';
			
		echo'</div>';
	}

	$mailersend_api = get_option('church_admin_mailersend_api_key');
	$bulk_email_method=get_option('church_admin_bulk_email_method');

	$email_method = get_option('church_admin_transactional_email_method');

	
	
	echo'<p><a  class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=test-email','test-email').'">'.esc_html( __('Send test email','church-admin' ) ).'</a></p>';
	echo'<form action="admin.php?page=church_admin%2Findex.php&action=email-settings" method="POST">';
	wp_nonce_field('email-settings');
	echo'<h3>Mailersend</h3>';
	echo'<p>'.esc_html(__('We recommend using Mailersend for all your email needs. They can handle individual emails sent by the plugin and bulk email. The free plan allows 3,000 emails per month, extras charged at USD $1 per 1,000. Please deactivate other WordPress email settings plugins if using, to prevent clashing.','church-admin') ).'</p>';
	echo'<p><a class="button-secondary" target="_blank" href="https://www.mailersend.com?ref=ljmqhjdx9oz5">'.esc_html(__('Sign up to Mailersend','church-admin')).'</a></p>';
	echo'<p><a  class="button-secondary" target="_blank" href="https://www.churchadminplugin.com/tutorials/mailersend-setup">'.esc_html(__('Tutorial on setting up Mailersend','church-admin')).'</a></p>';
	echo church_admin_mailersend_get_domains();

	$from_email = get_option('church_admin_default_from_email');
	$from_name = get_option('church_admin_default_from_name');
	echo'<div class="church-admin-form-group"><label>'.esc_html(__('Default from email','church-admin')).'</label><input class="church-admin-form-control" required="required" name="church_admin_default_from_email" value="'.esc_attr($from_email).'"></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html(__('Default from name','church-admin')).'</label><input class="church-admin-form-control" required="required" name="church_admin_default_from_name" value="'.esc_attr($from_name).'"></div>';
	
	
	if(!empty($mailersend_api )){echo '<p>'.esc_html('With Mailersend api key set, all other email settings are ignored! Everything is done.','church-admin').'</p>';}
	echo'<div class="church-admin-form-group"><label>'.esc_html(__('Mailersend API key','church-admin') ).'</label><input class="church-admin-form-control" type="text" name="mailersend-api" value="'.esc_attr($mailersend_api).'"></div><p><input type="hidden" name="save-email-settings" value="1" /><input type="submit" class="button-primary" value="'.esc_html( __('Save API key','church-admin' ) ).'" /></p>';




	$smtp_settings = get_option('church_admin_smtp_settings');
	
	if(!empty($smtp_settings)){
		echo'<p><a  class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=delete-smtp-settings','delete-smtp-settings').'">'.esc_html( __('Delete SMTP settings','church-admin' ) ).'</a></p>';

	}
	
	echo'<h3>'.__('Transactional email method','church-admin').'</h3>';

	if ( function_exists( 'mail' ) )
	{
		echo'<div class="church-admin-form-group"><input type="radio" class="email-method" name="email-method" value="website" '.checked('native',$email_method,false).'/><label>'.esc_html( __('Send using native WordPress email functions','church-admin' ) ).'</label></div>';
	}
	else{
		echo '<p>'.esc_html(__('Your hosting provider has disabled the native mail() function, so you must add SMTP credentials by checking below and filling out the form or using another plugin to provide SMTP email','church-admin')).'</p>';
	 
	}
	echo'<div class="church-admin-form-group"><input type="radio" class="email-method" name="email-method" value="mailersend" '.checked('mailersend',$email_method,false).'/><label>'.esc_html( __('Send using Mailersend.','church-admin' ) ).'</label></div>';
	
	echo'<div class="church-admin-form-group"><input type="radio" class="email-method" name="email-method" value="smtpserver" '.checked('smtpserver',$email_method,false).'/><label>'.esc_html( __('Send using SMTP server and Church Admin SMTP settings.','church-admin' ) ).'</label></div>';
	
	echo'<hr/>';
	
	/***************************
	* Bulk Email Method
	****************************/
	echo'<h3>'.__('Bulk email method','church-admin').'</h3>';





	if ( function_exists( 'mail' ) )
	{
		echo'<div class="church-admin-form-group"><input type="radio" class="bulk-email-method" name="bulk-email-method" value="website" '.checked('native',$bulk_email_method,false).'/><label>'.esc_html( __('Send using native WordPress email functions','church-admin' ) ).'</label></div>';
	}
	else{
		echo '<p>'.esc_html(__('Your hosting provider has disabled the native mail() function, so you must add SMTP credentials by checking below and filling out the form or using another plugin to provide SMTP email','church-admin')).'</p>';
	 
	}
	echo'<div class="church-admin-form-group"><input type="radio" class="bulk-email-method" name="bulk-email-method" value="mailersend" '.checked('mailersend',$bulk_email_method,false).'/><label>'.esc_html( __('Send using Mailersend.','church-admin' ) ).'</label></div>';
	echo'<div class="church-admin-form-group"><input type="radio" class="bulk-email-method" name="bulk-email-method" value="smtpserver" '.checked('smtpserver',$bulk_email_method,false).'/><label>'.esc_html( __('Send using SMTP server and Church Admin SMTP settings.','church-admin' ) ).'</label></div>';
	echo'<hr/>';

	/***************************
	* SMTP Settings
	****************************/
	$settings=get_option('church_admin_smtp_settings');
	
	echo'<div id="smtp-settings" ';
	switch($email_method){
		case 'smtpserver':
		case 'mailersend':
			echo' style="display:block" ';
		break;
		default:
		  echo' style="display:none" ';
		break;
	}

	echo'>';
	
	echo'<h3>'.esc_html( __('SMTP settings','church-admin' ) ).'</h3>';
	if(empty($settings)){
		echo'<p style="color:red">'.esc_html(__('Please add SMTP settings below','church-admin')).'</p>';
	}
	
	if(!empty($mailersend_api) && !function_exists('mail')){
		echo '<p style="color:red"><strong>'.esc_html(__('Looks like you need some SMTP settings for other WordPress emails','church-admin')).'</strong></p>';
	}
	echo '<p><a  target="_blank" href="https://support.google.com/accounts/answer/185833?hl">'.esc_html(__('Gmail accounts will need an app password not your normal password','church-admin')).'</a></p>';	
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Host','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="host" ';
	if(!empty( $settings['host'] ) ) echo ' value="'.esc_html( $settings['host'] ).'" ';
	echo'/></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Authorisation required?','church-admin' ) ).'</label><input class="church-admin-form-control"  type="checkbox" name="auth" value="TRUE" ';
	if(!empty( $settings['auth'] ) ) echo ' checked="checked" ';
	echo'/></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Port','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="port" ';
	if(!empty( $settings['port'] ) ) echo ' value="'.esc_html( $settings['port'] ).'" ';
	echo'/></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Username','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="username"  ';
	if(!empty( $settings['username'] ) ) echo ' value="'.esc_html( $settings['username'] ).'" ';
	echo'/></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Password','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="password"  ';
	if(!empty( $settings['password'] ) ) echo ' value="'.esc_html( $settings['password'] ).'" ';
	echo'/></div>';
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Security','church-admin' ) ).'</label><select  class="church-admin-form-control"  name="secure"><option>'.esc_html(__('Choose...','church-admin')).'</option> ';
	$secure = !empty($settings['secure']) ? $settings['secure'] : null;
	echo'<option value="ssl" '.selected('ssl',$secure,FALSE).'>SSL</option><option value="tls" '.selected('tls',$secure,FALSE).'>TLS</option><option value="">None</option>';
	echo'</select></div>';

	echo'</div>';





	/*********************
	* cron settings
	******************/
	$cron=get_option('church_admin_cron');
	$current_cron=!empty( $cron)?$cron:'immediate';
	echo'<div id="email-cron-settings" ';
	if(!empty($mailersend_api)) echo ' style="display:none" ';
	echo'>';
	echo'<h3>'.esc_html( __('Email Queueing for transactional and bulk email','church-admin' ) ).'</h3>';
	echo'<div class="church-admin-form-group"><input type="radio" class="speed" id="immediate"  name="cron" value="immediate" '.checked( $current_cron,'immediate',false).'/><label>'.esc_html( __('Send Emails Immediately','church-admin' ) ).' ('.esc_html( __("Use this option if your hosting company doesn't limit how many emails you can send an hour",'church-admin' ) ).')</label></div>';
	echo'<div class="church-admin-form-group"><input type="radio"  class="speed cron" name="cron" value="cron" '.checked( $current_cron,'cron',false).'/><label>'.esc_html( __('I want to use cron','church-admin' ) ).' ('.esc_html( __('Use this option if you are on a Linux server and are limited how many emails you can send an hour','church-admin' ) ).')</label></div>';
	$cronCommand='<strong>curl --silent '.admin_url().'/admin-ajax.php?action=church_admin_cronemail</strong>';
	//translators: %1$s is a cron command
	echo'<p>'.wp_kses_post(sprintf(__('Set up a cron job with the command %1$s in your cPanel or hosting account','church-admin' ) ,$cronCommand)).'</p>';
	echo'<div class="church-admin-form-group"><input  id="wp-cron" type="radio" class="speed wp-cron" name="cron" value="wp-cron" '.checked( $current_cron,'wp-cron',FALSE).'/><label>'.esc_html( __('I want to use wp-cron','church-admin' ) ).' ('.esc_html( __("Use this option if you are on a Windows server or don't understand cron and are limited how many emails you can send an hour. It will be set to send a batch every hour.",'church-admin' ) ).')</label></div>';
	echo '<div id="batch-size" ';
	if(!empty($current_cron) &&($current_cron=='immediate'))echo ' style="display:none" ';
	echo' class="church-admin-form-group"><label>'.esc_html( __('Max emails per hour? (required)','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="quantity" value="'.get_option('church_admin_bulk_email').'" /></div>';
	echo'</div>';
	
	/****************
	 * End of FORM 
	 ***************/
	echo'<p><input type="hidden" name="save-email-settings" value="1" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></form>';
	echo'<script>jQuery(document).ready(function( $)  {
		$(".speed").change(function(){
			var speed = $(".speed:checked").val();
			console.log("speed"+speed);
			switch(speed){
				case "cron":
				case "wp-cron":
					$("#batch-size").show();
				break;
				case "immediate":
					$("#batch-size").hide();
				break;
				
			}
		});
		$(".bulk-email-method").change(function () {
			
			$("#email-cron-settings").show();
			
			var bulk_email_method=$(".bulk-email-method:checked").val();
			console.log("Bulk Email method" + bulk_email_method);
			switch(bulk_email_method)
			{
				
				case "website":
					$("#smtp-settings").hide();
					$("#email-cron-settings").show();
				break;
				case "smtpserver":
					$("#smtp-settings").show();
					$("#email-cron-settings").show();
				break;
			}
		});
		$(".email-method").change(function()  {
			var email_method=$(".email-method:checked").val();
			console.log("Email method" + email_method);
			switch(email_method)
			{
				
				case "website":
					$("#smtp-settings").hide();
					$("#email-cron-settings").show();
				break;
				case "smtpserver":
					$("#smtp-settings").show();
					$("#email-cron-settings").show();
				break;
			}
		});
	});
	</script>';
}


/**
 * This function sets up smtp settings for wp_mail()
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */
function church_admin_smtp_settings()
{

	$smtp_display='display:none';
	if(!empty( $_POST['save-smtp-settings'] ) )
	{
		foreach( $_POST AS $key=>$value)
		{
			if(!empty( $_POST[$key] ) )  {
				$settings[$key]=church_admin_sanitize( $value);
			}
		}
		if(!empty( $settings) )
		{
			update_option('church_admin_smtp_settings',$settings);
			echo'<div class="notice notice-success inline"><p>'.esc_html( __('SMTP Settings saved','church-admin' ) ).'</p></div>';
		}
		else
		{
			delete_option('church_admin_smtp_settings');
			echo'<div class="notice notice-success inline"><p>'.esc_html( __('SMTP Settings deleted','church-admin' ) ).'</p></div>';
		}
		$smtp_display='display:block';
	}

		$settings=get_option('church_admin_smtp_settings');
		echo'<h2>'.esc_html( __('Use your own smtp server settings for sending email from the website','church-admin' ) ).'</h2>';
		echo'<p>'.esc_html( __('Leave blank and save to delete current settings','church-admin' ) ).'</p>';
		echo'<p>'.esc_html( __('Using these settings changes the way Wordpress sends email across your whole site, to using your smtp server','church-admin' ) ).'</p>';
		echo '<p><a target="_blank" href="https://support.google.com/accounts/answer/185833?hl">'.esc_html(__('Gmail accounts will need an app password not your normal password','church-admin')).'</a></p>';	
	echo'<form action="" method="POST">';
		

		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Host','church-admin' ) ).'</label><input  class="church-admin-form-control" type="text" name="host" placeholder="smtp.gmail.com" ';
		if(!empty( $settings['host'] ) ) echo ' value="'.esc_html( $settings['host'] ).'" ';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Authorisation required?','church-admin' ) ).'</label><input class="church-admin-form-control"  type="checkbox" name="auth" value="TRUE" ';
		if(!empty( $settings['auth'] ) ) echo ' checked="checked" ';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Port','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="port" placeholder="465" ';
		if(!empty( $settings['port'] ) ) echo ' value="'.esc_html( $settings['port'] ).'" ';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Username','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="username" placeholder="yourname@gmail.com" ';
		if(!empty( $settings['username'] ) ) echo ' value="'.esc_html( $settings['username'] ).'" ';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Password','church-admin' ) ).'</label><input class="church-admin-form-control" id="smtp-password" type="password" name="password" placeholder="password" ';
		if(!empty( $settings['password'] ) ) echo ' value="'.esc_html( $settings['password'] ).'" ';
		echo'/><input type="checkbox" id="toggle-password">'.esc_html(__('Show','church-admin')).'</div>';
		echo'<script>
			jQuery(document).ready(function($){
				$("#toggle-password").change(function() {
					if(this.checked) {
						$("#smtp-password").attr("type","text");
					}
					else
					{
						$("#smtp-password").attr("type","password");
					}
				});
			});
		</script>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Security','church-admin' ) ).'</label><select class="church-admin-form-control"   name="secure"> ';
		if(!empty( $settings['secure'] ) ) echo ' <option value="'.esc_html( $settings['secure'] ).'">'.esc_html( $settings['secure'] ).'</option> ';
		echo'<option value="ssl">SSL</option><option value="tls">TLS</option><option value="">None</option>';
		echo'</select></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP From Email','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="from" placeholder="yourname@gmail.com" ';
		if(!empty( $settings['from'] ) ) echo ' value="'.esc_html( $settings['from'] ).'" ';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP From Name','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="from_name" placeholder="'.esc_html( __('Your name','church-admin' ) ).'" ';
		if(!empty( $settings['from_name'] ) ) echo ' value="'.esc_html( $settings['from_name'] ).'" ';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP Reply Email','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="reply_email" placeholder="yourname@gmail.com" ';
		if(!empty( $settings['reply_name'] ) ) echo ' value="'.esc_html( $settings['reply_email'] ).'" ';
		echo'/></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMTP From Name','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="reply_name" placeholder="'.esc_html( __('Reply name','church-admin' ) ).'" ';
		if(!empty( $settings['reply_name'] ) ) echo ' value="'.esc_html( $settings['reply_name'] ).'" ';
		echo'/></div>';
		echo'<p><input type="hidden" name="save-smtp-settings" value="TRUE" /><input class="button-primary"  type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></p>';
		echo'</form>';

}


/**
 * This function sets up roles
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */

function church_admin_roles(){
	global $wpdb;
	$modules = array('App'=>__('App','church-admin'),
					'Attendance'=>__('Attendance','church-admin'),
					'Bible'=>__('Bible Readings','church-admin'),
					'Bulk_Email'=>__('Bulk Email','church-admin'),
					'Bulk_SMS'=>__('Bulk SMS','church-admin'),
					'Calendar'=>__('Calendar','church-admin'),
					'Check-In'=>__('Check in','church-admin'),
					'Contact_Form'=>__('Contact Form','church-admin'),
					'Directory'=>__('Directory','church-admin'),
					'Events'=>__('Events','church-admin'),
					'Facilities'=>__('Facilities','church-admin'),
					'Funnel'=>__('Follow Up Funnels','church-admin'),
					'Giving'=>__('Giving','church-admin'),
					'Groups'=>__('Groups','church-admin'),
					'Inventory'=>__('Inventory','church-admin'),
					'Kidswork'=>__('Childrens Ministry','church-admin'),
					'Ministries'=>__('Ministries','church-admin'),
					'Pastoral'=>__('Pastoral','church-admin'),
					'Push'=>__('Push','church-admin'),
					
					'Service'=>__('Services','church-admin'),
					'Sessions'=>__('Sessions','church-admin'),
					
					'Gifts'=>__('Spiritual Gifts','church-admin'),
					'Units'=>__('Units','church-admin'),
				);
		echo'<h2>'.esc_html( __('Roles','church-admin' ) ).'</h2>';
		echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/permissions-and-roles/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
		$levels=get_option('church_admin_levels');
		$user_roles = get_option( $wpdb->prefix.'user_roles');
		
		if(!empty($_POST['save-permissions'])){
			
			$new_roles=array();
			foreach($modules AS $module=>$display_name){
				
				$this_level = !empty($_POST[$module])  ? church_admin_sanitize($_POST[$module]) : 'administrator';
				
				if(!empty($user_roles[$this_level]	)){
					$new_roles[$module] = $this_level;
				}else{
					$new_roles[$module] = 'administrator';
				}
			}
			
			update_option('church_admin_levels',$new_roles);
			echo'<div class="notice notice-success inline"><p>'.esc_html( __('Roles Updated','church-admin' ) ).'</p></div>';

		}
			$levels=get_option('church_admin_levels');
			echo'<p>'.esc_html( __('You can either set individuals  or allow roles like admin/editor/subscriber to have permission for various tasks','church-admin' ) ).'</p>';
			echo'<p><a href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&amp;action=permissions','permissions')).'">'.esc_html( __('Set individual permissions','church-admin' ) ).'</a></p>';
			echo'<form action="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=roles&section=settings','roles')).'" method="POST">';
			wp_nonce_field('roles');

			foreach($modules AS $module=>$display_name){

				echo'<div class="church-admin-form-group"><label>'.esc_html($display_name).'</label><select class="church-admin-form-control" name="'.esc_attr($module).'">';
				foreach($user_roles AS $role_name=>$role){
					$current = !empty($levels[$module]) ? $levels[$module] : 'administrator';
					echo'<option value="'.esc_attr($role_name).'" '.selected($current,$role_name,FALSE).'>'.esc_html($role['name']).'</option>';
				
				}
				echo'</select></div>';
			}
			echo'<p><input type="hidden" name="save-permissions" value="Yeah Jesus is Lord"><input class="button-primary" value="'.esc_attr(__('Save','church-admin')).'" type="submit"></p>';

			echo'</form>';

		

}



function church_admin_roles_old()
{
	global $wpdb;
	echo'<h2>'.esc_html( __('Roles','church-admin' ) ).'</h2>';
	echo'<p><a class="tutorial-link" target="_blank" href="https://www.churchadminplugin.com/tutorials/permissions-and-roles/"><span class="dashicons dashicons-welcome-learn-more"></span>&nbsp;'.esc_html(__('Learn more','church-admin')).'</a></p>';
	


	$roles_display='display:none';
	$levels=get_option('church_admin_levels');
	if(empty($levels['Pastoral'])){$levels['Pastoral']='administrator';}
	unset($levels['Prayer Chain']);
	ksort($levels);
	$available_levels=get_option( $wpdb->prefix.'user_roles');

	if(!empty( $_POST['save-permissions'] ) )
	{
		$form=$newlevels=array();
		foreach( $_POST AS $key=>$value)$form[str_replace('_',' ',$key)]=sanitize_text_field( $value);
		foreach( $form AS $key=>$value)  $newlevels[substr( $key,5)]=$value;

		foreach( $levels AS $key=>$value)
		{
			if(!empty( $newlevels[$key] )&&array_key_exists( $newlevels[$key],$available_levels) )$levels[$key]=$newlevels[$key];
	    }

		update_option('church_admin_levels',$levels);
		echo'<div class="notice notice-success inline"><p>'.esc_html( __('Roles Updated','church-admin' ) ).'</p></div>';
		$roles_display='display:block';
	}

	


		echo'<p>'.esc_html( __('You can either set individuals  or allow roles like admin/editor/subscriber to have permission for various tasks','church-admin' ) ).'</p>';
		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&amp;action=permissions','permissions').'">'.esc_html( __('Set individual permissions','church-admin' ) ).'</a></p>';

		echo'<form action="admin.php?page=church_admin/index.php&amp;action=roles&section=settings" method="POST">';
		wp_nonce_field('roles');
		echo'<table class="form-table"><tbody>';
		foreach( $levels AS $key=>$value)
		{

			//Need to be translateable, so used stored data and convert to translated display string
			switch( $key)
			{
				case'App':$display=__('App','church-admin');break;
				case'Attendance':$display=__('Attendance','church-admin');break;
				case 'ChildProtection':$display=__('Child Protection','church-admin');break;
				case 'Prayer':$display=__('Prayer Requests','church-admin');break;
				
				case'Directory':$display=__('People','church-admin');break;
				
				case'Children':$display=__('Children','church-admin');break;
				case 'Contact form':$display=__('Contact form','church-admin'); break;
				case'Comms':$display=__('Comms','church-admin');break;
				case'Groups':$display=__('Groups','church-admin');break;
				case'Calendar':$display=__('Calendar','church-admin');break;
				case'Media':$display=__('Media','church-admin');break;
				case'Facilities':$display=__('Facilities','church-admin');break;
				case'Ministries':$display=__('Ministries','church-admin');break;
				case'Service':$display=__('Service','church-admin');break;
				case'Sessions':$display=__('Sessions','church-admin');break;
				case'Member Type':$display=__('Member Type','church-admin');break;
				case'Sermons':$display=__('Sermons','church-admin');break;
				case 'Pastoral':$display=__('Pastoral','church-admin');break;
				
				case'Bulk SMS':$display=__('Bulk SMS','church-admin');break;
				
                case'Events':$display=__('Events','church-admin');break;
				case'Bulk Email':$display=__('Bulk Email','church-admin');break;
				case'Visitor':$display=__('Visitor','church-admin');break;
				case'Funnel':$display=__('Funnel','church-admin');break;
				default:$display=$key;break;
			}
			echo'<tr><th scope="row">'.$display.'</th><td><select name="level'.$key.'">';
			echo'<option value="'.$value.'" selected="selected">'.$value.'</option>';
			foreach( $available_levels AS $avail_key=>$avail_value)echo'<option value="'.$avail_key.'">'.$avail_key.'</option>';
			echo'</select></td></tr>';
		}
		echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save-permissions" value="TRUE" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr>';
		echo'</tbody></table></form>';
		

}



/**
 * This function sets up Bulk SMS
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */


function church_admin_sms_settings()
{
    require_once(plugin_dir_path(dirname(__FILE__) ).'/includes/sms.php');
    $sms_country_code=get_option('church_admin_sms_iso');
    $sms_sender=get_option('church_admin_sms_reply');
    /********************************************************
    *
    * Check if old style provider is set already
    *
    **********************************************************/
    $smsProvider=get_option('church_admin_sms_provider');
    if ( empty( $smsProvider) )
    {
        $bulksms=get_option('church_admin_bulksms');
        if(!empty( $bulksms) )
        {
            $smsProvider='bulksms.com';
            update_option('church_admin_sms_provider','bulksms.com');
            delete_option('church_admin_bulksms');
        }
        $cloudservicezm=get_option('church_admin_cloudservicezm');
        if(!empty( $cloudservicezm) )
        {
            $smsProvider='cloudservicezm.com';
            update_option('church_admin_sms_provider','cloudservicezm.com');
            delete_option('church_admin_cloudservicezm');
        }
    }
    if(!empty( $smsProvider) )
    {
        $sender=get_option('church_admin_sms_reply');
        switch( $smsProvider)
        {
            case 'bulksms.com':
            case'cloudservicezm.com':
                    $sms_username=get_option('church_admin_sms_username');
                    $sms_password=get_option('church_admin_sms_password');
            break;
            case 'textmagic.com':
                    $sms_username=get_option('church_admin_sms_username');
                    $api_key=get_option('church_admin_sms_api_key');
            break;
            case 'twilio':
                    $smsSID=get_option('church_admin_twilio_SID');
                    $smsToken=get_option('church_admin_twilio_token');
            break;
        }
    }
    echo'<h2>'.esc_html( __("You can send bulk SMS to your directory using one of these SMS service providers",'church-admin' ) ).'</h2>';
    if ( empty( $_POST) )
	{
		echo'<p>'.esc_html( __("Click on an image to sign up with a provider",'church-admin' ) ).'</p>';
		echo'<table class="widefat wp-list-table"><thead><tr><th class="column-primary">'.esc_html( __('SMS provider','church-admin' ) ).'</th><th>'.esc_html( __('Details','church-admin' ) ).'</th></thead><tbody><tr>';
		echo'<tr><td class="column-primary" data-colname="Twilio">Twilio<button type="button" class="toggle-row">
		<span class="screen-reader-text">show details</span>
		</button></td><td><a href="https://www.twilio.com/referral/YjV7bl"><img src="'.plugins_url('/images/twilio-logo.png',dirname(__FILE__) ).'" alt="Twilio" /><br>'.esc_html( __('Get $10 free when you purchase $10 of credit','church-admin' ) ).'</a><br>'.esc_html( __('Twilio require you to purchase a cell number from them to send SMS at $1pm, but that allows you to send and receive SMS from this website','church-admin' ) ).'<button type="button" class="toggle-row">
			<span class="screen-reader-text">show details</span>
		</button></td></tr>';
			echo'<tr><td class="column-primary"  data-colname="Textmagic">Textmagic<button type="button" class="toggle-row">
			<span class="screen-reader-text">show details</span>
		</button></td><td><a href="https://shareasale.com/r.cfm?b=1370513&u=2478286&m=75317&urllink=&afftrack="><img src="'.plugins_url('/images/textmagic-logo.jpeg',dirname(__FILE__) ).'" alt="textmagic.com" /><br>'.esc_html( __('Pricing available in USD, GBP, AUD and EUR','church-admin' ) ).'</a></td></tr>';
			
			echo'<tr><td class="column-primary"  data-colname="BulkSMS">BulkSMS<button type="button" class="toggle-row">
			<span class="screen-reader-text">show details</span>
		</button></td><td><a href="https://www.bulksms.com"><img src="'.plugins_url('/images/bulksms-logo.png',dirname(__FILE__) ).'" alt="bulksms.com" /></a><br>'.esc_html( __('Pricing available in GBP','church-admin' ) ).'</td></tr>';
			echo'<tr><td  class="column-primary" data-colname="Zambia CloudService">Cloudservice Zambia<button type="button" class="toggle-row">
			<span class="screen-reader-text">show details</span>
		</button></td><td><a href="https://www.cloudservicezm.com/"><img src="'.plugins_url('/images/cloudservicezm-logo.jpeg',dirname(__FILE__) ).'" alt="cloudservicezm.com" /></a><br>'.esc_html( __('Unknown pricing - Zambia only','church-admin' ) ).'</td></tr>';
			echo'</tr></tbody></table>';
	}
    if(!empty( $_POST['save-sms-settings'] ) )
    {
        switch( $_POST['sms-provider'] )
        {
            case'twilio':
                update_option('church_admin_sms_provider','twilio');
                update_option('church_admin_twilio_token',church_admin_sanitize( $_POST['sms-token'] ) );
                update_option('church_admin_twilio_SID',church_admin_sanitize($_POST['sms-SID'] ) );
                $smsSID=church_admin_sanitize( $_POST['sms-SID'] );
                $smsToken=church_admin_sanitize($_POST['sms-token'] );
                $smsProvider='twilio';
            break;
            case'textmagic.com':
                update_option('church_admin_sms_provider','textmagic.com');
                update_option('church_admin_sms_api_key',church_admin_sanitize( $_POST['sms-api-key'] ) );
                $smsProvider='textmagic.com';
                $sms_api_key=church_admin_sanitize( $_POST['sms-api-key'] );
            break;
            case'bulksms.com':
                update_option('church_admin_sms_provider','bulksms.com');
                update_option('church_admin_sms_password',sanitize_text_field( $_POST['sms-password'] ) );
                $smsProvider='bulksms.com';
                $sms_password=church_admin_sanitize( $_POST['sms-password'] );
            break;
            case'cloudservicezm.com':
                update_option('church_admin_sms_provider','cloudservicezm.com');
                update_option('church_admin_sms_password',sanitize_text_field( $_POST['sms-password'] ) );
                $smsProvider='cloudservicezm.com';
                $sms_password=church_admin_sanitize( $_POST['sms-password'] );
            break;     
        }
        update_option('church_admin_sms_username',church_admin_sanitize( $_POST['sms-username'] ) );
        $sms_username=sanitize_text_field( $_POST['sms-username'] );
        update_option('church_admin_sms_iso',church_admin_sanitize( $_POST['sms-iso'] ) );
        $sms_iso=sanitize_text_field( $_POST['sms-iso'] );
        update_option('church_admin_sms_reply',church_admin_sanitize( $_POST['sms-sender'] ) );
        $sms_sender = church_admin_sanitize( $_POST['sms-sender'] );
        echo'<div class="notice notice-success inline"><h2>'.esc_html( __('SMS provider settings saved','church-admin' ) ).'</h2></div>';
    }
        echo'<h2>'.esc_html( __('SMS provider settings','church-admin' ) ).'</h2>';
        echo'<form action="" method="post">';
        echo'<table class="form-table">';
        echo'<tr><th scope="row">'.esc_html( __('Country code (eg 44 for GB)','church-admin' ) ).'</th><td><input type="text" name="sms-iso" ';
        if(!empty( $sms_country_code) ) echo' value="'.esc_html( $sms_country_code).'" ';
        echo'/></td></tr>';
        echo'<tr><th scope="row">'.esc_html( __('SMS sender cell eg:4412345678901, except Twilio +4412345678901','church-admin' ) ).'</th><td><input type="text" name="sms-sender" ';
        if(!empty( $sms_sender) ) echo' value="'.esc_html( $sms_sender).'" ';
        echo'/></td></tr>';
        echo'<tr><th scope="row">'.esc_html( __("Select SMS provider",'church-admin' ) ).'</th><td><select class="sms_provider" name="sms-provider">';
        if(!empty( $smsProvider) )echo'<option selected="selected" value="'.esc_html( $smsProvider).'">'.esc_html( $smsProvider).'</option>';
        echo'<option value="textmagic.com">textmagic.com</option>';
        echo'<option value="twilio">Twilio</option>';
        echo'<option value="bulksms.com">bulksms.com</option>';
        echo'<option value="cloudservicezm.com">cloudservicezm.com</option>';
        echo'</select></td></tr>';
        
        if(!empty( $smsProvider) )
        {
            switch( $smsProvider)
            {
                case 'twilio':
                        $showAPI=FALSE;
                        $showP=FALSE;
                        $showSID=TRUE;
                        $showToken=TRUE;
                        $showUserName=FALSE;
                    break;
                    default:case'textmagic.com':
                        $showAPI=TRUE; $showToken=$showSID=$showP=FALSE;
                    break;
                    case 'bulksms.com':case'cloudservicezm.com':
                        $showToken=$showSID=$showAPI=FALSE;
						$showUserName=$showP=TRUE;
                    break;
            }
        }else{$showAPI=TRUE; $showUP=FALSE;}
        
        echo'<tr class="sms-username"';
        if ( empty( $showUserName) )echo ' style="display:none" ';
        echo'><th scope="row">'.esc_html( __('Username','church-admin' ) ).'</th><td><input type="text" name="sms-username" ';
        if(!empty( $sms_username) )echo'value="'.esc_html( $sms_username).'" ';
        echo'/></td></tr>';
        echo'<tr class="sms-api-key"';
        if ( empty( $showAPI) )echo ' style="display:none" ';
        echo'><th scope="row">'.esc_html( __('API key','church-admin' ) ).'</th><td><input type="text" name="sms-api-key" ';
        if(!empty( $api_key) )echo'value="'.esc_html( $api_key).'" ';
        echo'/></td></tr>';
        
        echo'<tr class="sms-password" ';
        if ( empty( $showP) )echo ' style="display:none" ';
        echo'><th scope="row">'.esc_html( __('Password','church-admin' ) ).'</th><td><input type="text" name="sms-password" ';
        if(!empty( $sms_password) )echo'value="'.esc_html( $sms_password).'" ';
        echo'/></td></tr>';
        /*******************
        * Twilio
        ********************/
        echo'<tr class="sms-SID"';
        if ( empty( $showSID) )echo ' style="display:none" ';
        echo'><th scope="row">'.esc_html( __('Twilio SID','church-admin' ) ).'</th><td><input type="text" name="sms-SID" ';
        if(!empty( $smsSID) )echo'value="'.esc_html( $smsSID).'" ';
        echo'/></td></tr>';
        echo'<tr class="sms-token"';
        if ( empty( $showToken) )echo ' style="display:none" ';
        echo'><th scope="row">'.esc_html( __('Twilio Token','church-admin' ) ).'</th><td><input type="text" name="sms-token" ';
        if(!empty( $smsToken) )echo'value="'.esc_html( $smsToken).'" ';
        echo'/></td></tr>';
        echo'<tr><th scope="row">&nbsp;</th><td><input type="hidden" name="save-sms-settings" value="TRUE" /><input type="submit" value="'.esc_html( __('Save','church-admin' ) ).' &raquo;" class="button-primary" /></form>';
         echo'</table>';
        
        
        echo'<script>jQuery(document).ready(function( $)  {
            $(".sms_provider").on("change",function()  {
                var sms_provider=$(this).val();
                switch(sms_provider)
                {
                    case "twilio":
                        $(".sms-api-key").hide();
                        $(".sms-password").hide();
                        $(".sms-SID").show();
                        $(".sms-token").show();
                         $(".sms-username").hide();
                    break;
                    case "textmagic.com":
                        $(".sms-api-key").show();
                        $(".sms-password").hide();
                        $(".sms-SID").hide();
                        $(".sms-token").hide();
                        $(".sms-username").show();
                    break;
                    case "bulksms.com":case"cloudservicezm.com":
                        $(".sms-api-key").hide();
                       $(".sms-username").show();
                        $(".sms-password").show();
                        $(".sms-SID").hide();
                        $(".sms-token").hide();
                    break;    
                }
            })
        });</script>';
    
    
    
    
    
}
/**
 * This function general settings
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */

 function church_admin_general_settings()
 {
	 global $wpdb;
	 $member_types = church_admin_member_types_array();
	echo'<h2>'.esc_html( __('Settings ','church-admin' ) ).'</h2>';
	//echo wp_login_url().'</br/>';
	if(!empty( $_POST['save-general-settings'] ) )
	{
		
		if(!empty($_POST['member_type_id'])){

			$member_type_id = (int)church_admin_sanitize($_POST['member_type_id']);
			if(!empty($member_types[$member_type_id])){
				update_option('church_admin_member_type_id_for_registrations',$member_type_id);
			}

		}
	

        if(!empty( $_POST['prayer-request-moderation'] ) ){
			update_option('prayer-request-moderation',church_admin_sanitize( $_POST['prayer-request-moderation'] ) );
		}
	
        
		update_option('church_admin_pdf_size',church_admin_sanitize($_POST['pdf_size'] ));
		

		//change in prayer requests or bible readings requires a permalinks refresh
		flush_rewrite_rules();
		
		if(!empty($_POST['register-page'])){
			update_option('church_admin_register_page',esc_url(sanitize_text_field(stripslashes($_POST['register-page']))));
		}
		else
		{
			delete_option('church_admin_register_page');
		}
		if(!empty( $_POST['prayer-request-message'] ) )  {
			update_option('church_admin_prayer_request_message',church_admin_sanitize( $_POST['prayer-request-message'] ) );
		}else{
			delete_option('church_admin_prayer_request_message');
		}
		if(!empty( $_POST['socials'] ) )  {
			update_option('church-admin-socials',TRUE);
		}else{
			delete_option('church-admin-socials');
		}
		if(!empty( $_POST['login-redirect'] ) )
		{
			update_option('church_admin_login_redirect',church_admin_sanitize($_POST['login-redirect'] ));
		}else{delete_option('church_admin_login_redirect');}
        
		if(!empty( $_POST['no-push'] ) )
        {
            update_option('church_admin_no_push',TRUE);
        }
        else
        {
            delete_option('church_admin_no_push');
        }
		if ( empty( $_POST['private-prayer'] ) )
		{
			update_option('church-admin-private-prayer-requests',FALSE);

		}else
		{
			update_option('church-admin-private-prayer-requests',TRUE);

		}

		
		if(!empty( $_POST['prayer-request-admin-push'] ) )
        {
                $prayer_request_people_ids=maybe_unserialize(church_admin_get_people_id( church_admin_sanitize($_POST['prayer-request-admin-push'] ) ) );
                update_option('church_admin_prayer_request_receive_push_to_admin', $prayer_request_people_ids);
        }
        else
        {
                delete_option('church_admin_prayer_request_receive_push_to_admin');
        }
		if ( empty( $_POST['private-acts-of-courage'] ) )
		{
			update_option('church-admin-private-acts-of-courage',FALSE);

		}else
		{
			update_option('church-admin-private-acts-of-courage',TRUE);

		}
       
		if ( empty( $_POST['wedding_anniversary'] ) )  {
			update_option('church_admin_show_wedding_anniversary',FALSE);
		}else{update_option('church_admin_show_wedding_anniversary',TRUE);}	
		if ( empty( $_POST['use_prefix'] ) )  {
			update_option('church_admin_use_prefix',FALSE);
		}else{update_option('church_admin_use_prefix',TRUE);}
		
		//TITLES
		if ( empty( $_POST['use_titles'] ) )  {
			update_option('church_admin_use_titles',FALSE);
		}else{update_option('church_admin_use_titles',TRUE);}
		
		if(!empty($_POST['titles'])){
			$titles = explode(",",church_admin_sanitize($_POST['titles']));
			update_option('church_admin_titles',$titles);
		}
		else{
			delete_option('church_admin_titles');
		}


		if ( empty( $_POST['use_middle'] ) )  {
			update_option('church_admin_use_middle_name',FALSE);
		}else{
			update_option('church_admin_use_middle_name',TRUE);
		}
		//if ( empty( $_POST['use_nickname'] ) )  {update_option('church_admin_use_nickname',FALSE);}else{update_option('church_admin_use_nickname',TRUE);}
		if(!empty( $_POST['google_api'] ) )  {
			update_option('church_admin_google_api_key',church_admin_sanitize($_POST['google_api']) );
		}else{
			delete_option('church_admin_google_api_key');
		}
		if(!empty( $_POST['pagination'] ) ){
			update_option('church_admin_pagination_limit',intval( $_POST['pagination'] ) );
		}

		if(!empty( $_POST['user_subject'] ) ){
			update_option('church_admin_user_created_email_subject',church_admin_sanitize( $_POST['user_subject'] ) );
		}
		
		
		if(!empty( $_POST['church_admin_receipt_email_from_name'] ) ){
			update_option('church_admin_receipt_email_from_name',church_admin_sanitize( $_POST['church_admin_receipt_email_from_name'] ) );
		}
		if(!empty( $_POST['church_admin_receipt_email_from_email'] ) ){
			update_option('church_admin_receipt_email_from_email',church_admin_sanitize( $_POST['church_admin_receipt_email_from_email'] ) );
		}
		if(!empty( $_POST['church_admin_receipt_email_template'] ) ){
			update_option('church_admin_receipt_email_template',church_admin_sanitize( $_POST['church_admin_receipt_email_template'] ) );
		}
		if(isset( $_POST['church_admin_label'] ) )
		{
		switch( $_POST['church_admin_label'] )
		{
			case 'L7163': $option='L7163';break;
			case '5160': $option='5160';break;
			case '5161': $option='5161';break;
			case '5162': $option='5162';break;
			case '5163': $option='5163';break;
			case '5164': $option='5164';break;
			case '8600': $option='8600';break;
			case '3422': $option='3422';break;
			default :$option='L7163';break;
		}
		update_option('church_admin_label',$option);
		}else{delete_option('church_admin_label');}
		if(isset( $_POST['what-three-words'] ) )
		{	update_option('church_admin_what_three_words','on');
			update_option('church_admin_what_three_words_language',church_admin_sanitize($_POST['what-three-words-language'] ) );
		}else{
			update_option('church_admin_what_three_words','off');
		}
		
		echo'<div class="notice notice-success inline"><p>'.esc_html( __('Settings updated','church-admin' ) ).'</p></div>';
		$generalDisplay='display:block';
	}


		
		
		echo'<form action="" method="POST">';
		
        echo'<h2>'.esc_html( __('General Settings','church-admin' ) ).'</h2>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Remove data on plugin delete','church-admin' ) ).' </label>';
		
		
     
		//login redirect
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Redirect page after login by "subscriber"','church-admin' ) ).'</label><select class="church-admin-form-control" name="login-redirect">';
		$redirect=get_option('church_admin_login_redirect');
        
        //add in a dropdown of pages and post_status
		echo church_admin_posts_and_pages_dropdown($redirect);
		echo'</select></div>';
		/**************************
		 * Register page
		 **************************/
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('What page is your register block/shortcode on?','church-admin' ) ).'</label><select class="church-admin-form-control" name="register-page">';
		$current=get_option('church_admin_register_page');
        
        //add in a dropdown of pages and post_status
		echo church_admin_posts_and_pages_dropdown($current);
		echo'</select></div>';
	
		echo '<div class="church-admin-form-group"><label>'.esc_html( __( 'Member type for new registrations', 'church-admin' ) ).'</label><select class="church-admin-form-control" name="member_type_id">';
        foreach($member_types AS $id=>$type){
            echo'<option '. selected( $saved_member_type_id , $id , FALSE ).' value="'.(int)$id.'">'.esc_html($type).'</option>';
        }    
        echo'</select></div>';
		
		/***********************
		 * GDPR
		 ***********************/
		echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=registration-followup-email-setup','registration-followup-email-setup').'">'.esc_html(__('Confirmation email settings','church-admin')).'</a></p>';
		/***********************
		 * Directory Settings
		 ***********************/
		echo'<table class="form-table" id="directory-settings"><thead><tr><th colspan=2><h2>'.esc_html( __('Directory settings','church-admin' ) ).'</th></tr><thead><tbody>';
		echo'<tr><th scope="row">'.esc_html( __('Use titles for names','church-admin' ) ).'</th><td><input type="checkbox" name="use_titles" value="TRUE" ';
		$prefix=get_option('church_admin_use_titles');
		if( $prefix) echo ' checked="checked" ';
		echo '/></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Comma separated list of titles to use (eg Mr, Mrs, Bro., Sis., Elder., Pastor','church-admin' ) ).'</th><td><input type="text" name="titles"  ';
		$titles=get_option('church_admin_titles');
		if(!empty($titles)) echo ' value="'.esc_attr(implode(",",$titles)).'" ';
		echo '/></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Use prefix for names','church-admin' ) ).'</th><td><input type="checkbox" name="use_prefix" value="TRUE" ';
		$prefix=get_option('church_admin_use_prefix');
		if( $prefix) echo ' checked="checked" ';
		echo '/></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Use middle name for names','church-admin' ) ).'</th><td><input type="checkbox" name="use_middle" value="TRUE" ';
		$middle=get_option('church_admin_use_middle_name');
		if( $middle) echo ' checked="checked"';
		echo '/></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Show Wedding Anniversary form field','church-admin' ) ).'</th><td><input type="checkbox" name="wedding_anniversary" value="TRUE" ';
		$wa=get_option('church_admin_show_wedding_anniversary');
		if( $wa) echo ' checked="checked"';
		echo '/></td></tr>';

		/*
		echo'<tr><th scope="row">'.esc_html( __('Add nickname for names','church-admin' ) ).'</th><td><input type="checkbox" name="use_nickname" value="TRUE" ';
		$nickname=get_option('church_admin_use_nickname');
		if( $nickname)echo ' checked="checked"';
		echo '/></td></tr>';
		*/
		echo'<tr><th scope="row">'.esc_html( __('Google Maps API key','church-admin' ) ).'</th><td><input type="text" name="google_api" value="'.get_option('church_admin_google_api_key').'" /></td></tr>';
		echo'<tr><td colspan="2"><a taregt="_blank" href="https://www.churchadminplugin.com/tutorials/google-api-key/">'.esc_html( __('How to get a Google API key','church-admin' ) ).'</a></td></tr>';
		echo'<tr><th scope="row">'.esc_html( __('Directory Records per page','church-admin' ) ).'</th><td><input type="text" name="pagination" value="'.get_option('church_admin_pagination_limit').'" /></td></tr>';
		
		echo'<tr><th>'.esc_html( __('Stop push notification and email send on content publishing','church-admin' ) ).'</th><td>';
        echo'<input type="checkbox" name="no-push" value="1" ';
        $no_push= get_option('church_admin_no_push');
        if( $no_push) echo' checked="checked" ';
        echo'/></td></tr>';
		/***************************
		 * What three words
		 **************************/
		$googleAPI=get_option('church_admin_google_api_key');
		if(!empty( $googleAPI) )
		{//requires google API key to work
			$w3w='on';
			$w3w=get_option('church_admin_what_three_words');
			echo'<tr><th colspan=2><h2>'.esc_html( __('What three words','church-admin' ) ).'</h2></th></tr>';
			echo'<tr><th colspan=2>'.esc_html( __('What Three Words has divided the world into 3 metre squares and gave each square a unique combination of three words. It’s the easiest way to find and share exact locations.','church-admin' ) ).'</th></tr>';
			echo '<tr><th scope="row">'.esc_html( __('What Three Words enabled')).'</th><td><input type="checkbox" name="what-three-words" '.checked( $w3w,'on',false).'/></td></tr>';
			//grab what three words languages
			$response = wp_remote_get( esc_url_raw('https://api.what3words.com/v3/available-languages?key=7F5FVM60' ) );
			$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
			
			$w3wLanguage=get_option('church_admin_what_three_words_language');
			if ( empty( $w3wLanguage) )$w3wLanguage='en';
			$languages=$api_response['languages'];
			if(!empty( $languages) )
			{
				echo '<tr><th scope="row">'.esc_html( __('What Three Words language')).'</th><td><select name="what-three-words-language">';
				foreach( $languages AS $key=>$detail)
				{
					echo'<option value="'.esc_html( $detail['code'] ).'" '.selected( $w3wLanguage,$detail['code'],false).'>'.esc_html( $detail['nativeName'] ).'</option>';
				}
				echo'</select></td></tr>';
			}

			echo'</tbody></table>';
		}
		/***********************
		 * PDF and Mailing Label settings
		 ***********************/
		echo'<table class="form-table"><thead><tr><th colspan=2><h2>'.esc_html( __('PDF and Mailing Label settings','church-admin' ) ).'</th></tr><thead><tbody>';
		echo '<tr><th scope="row">'.esc_html( __('PDF Page Size','church-admin' ) ).'</th><td><select name="pdf_size">';
		$pdf_size=get_option('church_admin_pdf_size');
		echo'<option value="A4" '.selected( $pdf_size,'A4').'>A4</option>';
		echo'<option value="Letter" '.selected( $pdf_size,'Letter').'>Letter</option>';
		echo'<option value="Legal" '.selected( $pdf_size,'Legal').'>Legal</option>';
		echo'</select></td></tr>';
		echo '<tr><th scope="row">Avery &#174; Label</th><td><select name="church_admin_label">';

		$l=get_option('church_admin_label');
		echo'<option value="L7163"';
		if( $l=='L7163') echo' selected="selected" ';
		echo'>L7163</option>';
		echo'<option value="5160"';
		if( $l=='5160') echo' selected="selected" ';
		echo'>5160</option>';
		echo'<option value="5161';
		if( $l=='5161') echo' selected="selected" ';
		echo'>5161</option>';
		echo'<option value="5162"';
		if( $l=='5162') echo' selected="selected" ';
		echo'>5162</option>';
		echo'<option value="5163"';
		if( $l=='5163') echo' selected="selected" ';
		echo'>5163</option>';
		echo'<option value="5164"';
		if( $l=='5164') echo' selected="selected" ';
		echo'>5164</option>';
		echo'<option value="8600"';
		if( $l=='8600') echo' selected="selected" ';
		echo'>8600</option>';
		echo'<option value="3422"';
		if( $l=='3422') echo' selected="selected" ';
		echo'>3422</option></select></td></tr>';
		/**************************************
		 * Donor Receipt email settings
		 *****************************************/
		$template=get_option('church_admin_receipt_email_template');
		$from_name=$from_email='';
		$from_name = get_option('church_admin_receipt_email_from_name');
		$from_email = get_option('church_admin_receipt_email_from_email');
		echo'<table class="form-table"><thead><tr><th colspan=2><h2>'.esc_html( __('Donation receipt email settings','church-admin' ) ).'</th></tr><thead><tbody>';
		echo '<tr><th scope="row">'.esc_html( __('Email from name','church-admin' ) ).'</th><td><input type="text" name="church_admin_receipt_email_from_name" value="'.esc_html( $from_name).'" /></td></tr>';
		echo '<tr><th scope="row">'.esc_html( __('Email from email','church-admin' ) ).'</th><td><input type="text" name="church_admin_receipt_email_from_email" value="'.esc_html( $from_email).'" /></td></tr>';
		echo '<tr><th scope="row">'.esc_html( __('Email template before amounts','church-admin' ) ).'</th><td><textarea name="church_admin_receipt_email_template"  cols=80 rows=10>'.esc_textarea( $template ).'</textarea></td></tr>';
		echo'</table>';

		echo'<p><input type="hidden" name="save-general-settings" value="save"><input class="button-primary" type="submit" value="'.esc_attr(__('Save','church-admin')).'"></p></form>';

 }



 function church_admin_marital_status()
 {
 		$marital_status=get_option('church_admin_marital_status');
 		echo'<h2 class="marital-settings">'.esc_html( __('Marital Status ','church-admin' ) ).'</h2>';


 		$thead='<tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Marital Status','church-admin' ) ).'</th></tr>';
 		echo'<table class="widefat"><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
 		echo'<p><a class="button-primary"  href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=settings&action=edit_marital_status','edit_marital_status').'">'.esc_html( __('Add','church-admin' ) ).'</a></p>';
 		foreach( $marital_status AS $key=>$value)
 		{
 			echo'<tr>';
 			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=settings&action=edit_marital_status&amp;ID='.(int)$key,'edit_marital_status').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
 			$delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=settings&action=delete_marital_status&amp;ID='.(int)$key,'delete_marital_status').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
 			if( $key==0)  {echo'<td>&nbsp;</td><td>&nbsp;</td>';}else{echo'<td>'.$edit.'</td><td>'.$delete.'</td>';}
 			echo'<td>'.esc_html( $value).'</td></tr>';
 		}
 		echo'</tbody></table>';
 	
 }

 function church_admin_delete_marital_status( $ID)
 {
 	global $wpdb;
 	$marital_status=get_option('church_admin_marital_status');

 	if(!empty( $marital_status[$ID] ) )
 	{
 		$old=$marital_status[$ID];
 		unset( $marital_status[$ID] );
 		update_option('church_admin_marital_status',$marital_status);
 		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET marital_status=0 WHERE marital_status="'.(int)$id.'"');
 		echo'<div class="notice notice-inline notice-success">'.$old.' - '.esc_html( __('Marital Status removed','church-admin' ) ).'</div>';
 	}
 	echo church_admin_marital_status();
 }

  function church_admin_edit_marital_status( $ID)
 {
 	$marital_status=get_option('church_admin_marital_status');

 	if(!empty( $marital_status[$ID] ) )
 	{
 		$old=$marital_status[$ID];
 	}
 	if(!empty( $_POST['marital_status'] ) )
 	{
 		$marital_status[]=sanitize_text_field( $_POST['marital_status'] );
 		update_option('church_admin_marital_status',$marital_status);
 		echo'<div class="notice notice-inline notice-success">'.esc_html( __('Marital Status updated','church-admin' ) ).'</div>';
 		echo church_admin_marital_status();
 	}
 	else
 	{
 		echo '<h2>'.esc_html( __('Add/Edit Marital Status','church-admin' ) ).'</h2><form action="" method="POST"><table class="form-table"><tr><th scope="row">'.esc_html( __('Marital Status','church-admin' ) ).'</th><td><input type="text" name="marital_status" ';
 		if(!empty( $old) ) echo' value="'.esc_html( $old).'" ';
 		echo'/></td></tr><tr><td colspacing=2><input class="button-primary" type="submit" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr></table></form>';

 	}

 }
 /**
 * This function lists people types
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 * from v1.05
 *
 */

 function church_admin_people_types_list()
 {

 	echo'<h2>'.esc_html( __('People Types ','church-admin' ) ).'</h2>';


 	echo'<p>'.esc_html( __('If you add people types to "Adult", "Child"/"Teen" the shortcode [church_admin type="address-list"] will not work as expected! Use [church_admin type="directory"] instead','church-admin' ) ).'</p>';
 	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=settings&action=edit_people_type','edit_people_type').'">'.esc_html( __('Add People Type','church-admin' ) ).'</a></p>';
	$thead='<tr><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('People Type','church-admin' ) ).'</th></tr>';
 	echo'<table class="widefat"><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
	$people_types=get_option('church_admin_people_type');
 	foreach( $people_types AS $ID=>$type)
 	{
 		$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=settings&action=edit_people_type&amp;ID='.(int)$ID,'edit_people_type').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
 		if( $ID>2)  {$delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&section=settings&action=delete_people_type&amp;ID='.(int)$ID,'delete_people_type').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';}else{$delete='&nbsp;';}
 		echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html( $type).'</td></tr>';
 	}
	echo'</tbody></table>';
	
 }

 function church_admin_edit_people_type( $ID)
 {
 		$people_types=get_option('church_admin_people_type');

 		if(!empty( $_POST['people_type'] ) )
 		{
 			$out='<div class="notice notice-success inline">'.esc_html( __('People type saved','church-admin' ) ).'</div>';
 			$new=sanitize_text_field( $_POST['people_type'] );
 			if(!empty( $ID) )$people_types[$ID]=$new;
 			if ( empty( $ID)&& !empty( $new)&&!in_array( $new,$people_types) )$people_types[]=$new;
 			update_option('church_admin_people_type',$people_types);
            $out.=church_admin_people_types_list();
 		}
     else
     {
			$people_types=get_option('church_admin_people_type');
 			$out='<h2>'.esc_html( __('Edit People Type','church-admin' ) ).'</h2>';
 			$out.='<form action="" method="POST"><p><label>'.esc_html( __('People Type','church-admin' ) ).'</label><input type="text" name="people_type"';
 			if(!empty( $ID)&&!empty( $people_types[$ID] ) )$out.=' value="'.esc_html( $people_types[$ID] ).'" ';
 			$out.='/><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
     }
 		return $out;
 }

  function church_admin_delete_people_type( $ID)
 {
 		$people_types=get_option('church_admin_people_type');
 		unset( $people_types[$ID] );
 		update_option('church_admin_people_type',$people_types);
 		$out='<div class="notice notice-success inline">'.esc_html( __('People type deleted','church-admin' ) ).'</div>';
 		return $out;
 }

/******************************************************
*
* Restrict address list access from certain people
*
*******************************************************/
function church_admin_restrict_access()
{
	
	echo'<h1 >'.esc_html( __('Restrict access to address list from certain people','church-admin' ) ).'</h2>';
	
	if(!empty( $_POST['save-restricted'] ) )
	{
		if(!empty( $_POST['people'] ) )$people=unserialize(church_admin_get_people_id(sanitize_text_field( $_POST['people'] ) ));
		if(!empty( $people) )  {update_option('church-admin-restricted-access',$people);}else{delete_option('church-admin-restricted-access');}
	}
	$restrictedList=get_option('church-admin-restricted-access');
	$people='';
	if(!empty( $restrictedList) )$people=church_admin_get_people( $restrictedList);
	echo'<p>'.esc_html( __('Restrict access to the directory by certain users','church-admin' ) ).'</p>';
	echo'<form action="" method="POST">';
	echo'<p>'.church_admin_autocomplete('people','friends','to',$people,FALSE).'</p>';
	echo'<input type="hidden" name="save-restricted" value="1" /><input type="submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
	
 	
}


function church_admin_debug_log()
{
    echo'<h2>'.esc_html( __('Debug Log','church-admin' ) ).'</h2>';
	if(is_multisite())echo'<p>Multisite install</p>';
	if(defined('CA_DEBUG') )  {echo'<p>'.esc_html( __('Debug mode is on','church-admin' ) ).'</p>';}else{echo'<p>'.esc_html( __('Debug mode is off','church-admin' ) ).'</p>';}
	echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=toggle-debug-mode','toggle-debug-mode').'">'.esc_html( __('Toggle debug mode','church-admin' ) ).'</a></p>';
    //debug log display
	   $upload_dir = wp_upload_dir();
        $debug_path=$upload_dir['basedir'].'/church-admin-cache/debug_log.php';
		$debug_url=content_url().'/uploads/church-admin-cache/debug_log.php';


	
	
	
	
	if(file_exists( $debug_path) )
	{
		$filesize=filesize( $debug_path);
		//translators: %1$s is a file path
		echo'<p>'.esc_html(sprintf(__('Debug path: %1$s','church-admin'),$debug_path)).'</p>';
		echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=clear-debug','clear-debug').'">'.esc_html( __('Clear Debug Log','church-admin' ) ).'</a></p>';
	
		$filesize=filesize( $debug_path);
		$size=size_format( $filesize, $decimals = 2 );
		//translators: %1$s is file size
		echo'<p>'.esc_html(sprintf(__('Debug file is currently %1$s','church-admin' ) ,$size)).'</p>';
	
		if( $filesize<2097152)
		{
			echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=send-to-support','send-to-support').'">'.esc_html( __('Send debug file to support','church-admin' ) ).'</a></p>';
			echo'<h3>'.__('Debug Log','church-admin').'</h3>';
			if(function_exists('highlight_file')){
				highlight_file($debug_path);
			}
			else {
				echo '<pre>';
				echo esc_html(file_get_contents($debug_path));
				echo'</pre>';
			}
		}
		else
		{
			echo'<p>'.esc_html( __('Your debug log is too big to send to support','church-admin' ) ).'</p>';
		}
		}
	else{
		echo'<p>'.esc_html( __('No debug log yet, because no errors encountered!','church-admin' ) ).'</p>';
	}
}

function church_admin_send_debug_to_support()
{
	global $wp_version,$wpdb;
	$upload_dir = wp_upload_dir();
	$debug_path=$upload_dir['basedir'].'/church-admin-cache/debug_log.php';
	if(!file_exists( $debug_path) ) return '<p>'.esc_html( __("Debug log is empty",'church-admin' ) ).'</p>';
	$filesize=@filesize( $debug_path);
	$size=size_format( $filesize, $decimals = 2 );
	echo'<h2>'.esc_html( __("Send debug log to support",'church-admin' ) ).'</h2>';
	//translators: %1$s is a file size
	echo'<p>'.esc_html(sprintf(__('Debug file is currently %1$s','church-admin' ) ,$size)).'</p>';
	if( $filesize>2097152)  {echo '<p>'.esc_html( __("Debug log is too big",'church-admin' ) ).'</p>';return;}
	if( $filesize<500)  {echo '<p>'.esc_html( __("Debug log is too small to send (not enough debug data). Please repeat the task you are wanting to report a bug for and refresh this page.",'church-admin' ) ).'</p>';return;}
	
	echo'<p>'.esc_html( __('This works best if you clear the debug log, recreate the issue and then send the new debug log file.','church-admin' ) ).'</p>';
	echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;section=settings&action=clear-debug','clear-debug').'">'.esc_html( __('Clear Debug Log','church-admin' ) ).'</a></p>';
	
	if(!empty( $_POST['send'] ) )
	{
		$max_upload = (int)(ini_get('upload_max_filesize') );
		$max_post = (int)(ini_get('post_max_size') );
		$memory_limit = (int)(ini_get('memory_limit') );
		$file_uploads=ini_get( 'file_uploads' )?'Yes':'No';
		$upload_dir = wp_upload_dir();
        $path=$upload_dir['basedir'].'/sermons/';
        $url=$upload_dir['baseurl'].'/sermons/';
		$hasSermonDir=is_dir( $sermonDirPath)?'Yes':'No';
		$premium=get_option('church_admin_payment_gateway');
		$hasPremium=!empty( $premium)?'Yes':'No';
		church_admin_gz( $debug_path,9);
		$fileToSend=$debug_path.'.gz';
		$subject='Debug Log from '.site_url();
		$message='<table><tr><th scope="row">Site</th><td>'.esc_html(site_url() ).'</td></tr>';
		$message.='<tr><th scope="row">WordPress version</th><td>'.esc_html( $wp_version).'</td></tr>';
		$message.='<tr><th scope="row">Church Admin Plugin version</th><td>'.CHURCH_ADMIN_VERSION.'</td></tr>';
		$message.='<tr><th scope="row">Premium?</th><td>'.esc_html( $premium).'</td></tr>';
		$message.='<tr><th scope="row">PHP version</th><td>'.esc_html(PHP_VERSION).'</td></tr>';
		
		$message.='<tr><th scope="row">Max Upload size</th><td>'.esc_html( $max_upload).'</td></tr>';
		$message.='<tr><th scope="row">Post Max size</th><td>'.esc_html( $max_post).'</td></tr>';
		$message.='<tr><th scope="row">Memory limit</th><td>'.esc_html( $memory_limit).'</td></tr>';
		$message.='<tr><th scope="row">File Uploads</th><td>'.esc_html( $file_uploads).'</td></tr>';
		$message.='<tr><th scope="row">Sermon Directory?</th><td>'.esc_html( $hasSermonDir).'</td></tr>';
		if(!empty( $sermonDirPath) )$message.='<tr><th scope="row">Sermon Directory path</th><td>'.esc_html( $sermonDirPath).'</td></tr>';
		
		$message.='<tr><th scope="row">Issue</th><td>'.wp_kses( $_POST['message'],'post').'</td></tr>';
		$message.='</table>';
		echo $message;

		church_admin_email_send('support@churchadminplugin.com',$subject,$message,null,null,array( $fileToSend),null,null,TRUE);
	
		if(!empty( $success) )echo'<div class="notice notice-success"><h2>Debug log sent</h2></div>';
		if(unlink( $fileToSend) )echo'<p>'.esc_html( $fileToSend).' deleted</p>';
	}
	else
	{
		echo'<h3>'.esc_html( __('Ready to send','church-admin' ) ).'</h3>';
		echo'<form action="" method="POST">';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Describe the issue in as much detail as you can, especially steps to reproduce','church-admin' ) ).'</label><textarea required class="church-admin-form-control" style="height:100px;" name="message"></textarea></div>';
		echo'<p><input type="hidden" name="send" value="yes" /><input class="button-primary" type="submit" value="'.esc_html( __('Send','church-admin' ) ).'" /></p></form>';
	}
}



function church_admin_global_communications_settings()
{
	global $wpdb;
	$premium=get_option('church_admin_payment_gateway');
	echo'<h2>'.esc_html( __("Global communications settings",'church-admin' ) ).'</h2>';
	echo'<p style="color:red">'.esc_html( __('Use with caution as overrides individuals settings','church-admin' ) ).'</p>';
	if(!current_user_can('manage_options') )
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html( __('Only site administrators can change global email settings')).'</h2></div>';
		return;
	}
	$results=$wpdb->get_results('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email IS NOT NULL AND gdpr_reason IS NOT NULL');
	if(empty($results))
	{
		echo '<div class="notice notice-danger"><h2>'.esc_html( __('No people with email address and GDPR reason set','church-admin' ) ).'</h2></div>';
		return;
	}
	$peopleIDs=array();
	foreach($results AS $row){
		$peopleIDs[]=(int)$row->people_id;
	}
	if(!empty($_POST['save'])){
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="posts" OR meta_type="bible-readings" OR meta_type="prayer-requests" OR meta_type="bible-readings-notifications" OR meta_type="prayer-requests-notifications" OR meta_type="posts-notifications"');

		$sql='INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,ID,people_id,meta_date) VALUES ';


		foreach($peopleIDs AS $key=>$people_id)
		{
			//the no-one case is handled by the fact the meta key for that people_id no longer exists
			
			$values=array();
			switch($_POST['posts'])
			{
				case 'everyone':
					$values[]='("posts",1,"'.(int)$people_id.'","'.date('Y-m-d').'")';
					$message['posts']=__("Posts - everyone",'church-admin');
				break;
				default:
						$message['posts']=__("Posts - no-one",'church-admin');
				break;
			}
			switch($_POST['bible-readings'])
			{
				case 'everyone':
					$values[]='("bible-readings",1,"'.(int)$people_id.'","'.date('Y-m-d').'")';
					$message["bible-readings"]=__("Bible readings - everyone",'church-admin');
				break;
				default:
						$message["bible-readings"]=__("Bible readings - no-one",'church-admin');
					break;
			}
			switch($_POST['prayer-requests'])
			{
				case 'everyone':
					$values[]='("prayer-requests",1,"'.(int)$people_id.'","'.date('Y-m-d').'")';
					$message["prayer-requests"]=__("Prayer requests - everyone",'church-admin');
				break;
				default:
						$message["prayer-requests"]=__("Prayer requests - no-one",'church-admin');
				break;
			}
			if(!empty($premium)){
				switch($_POST['posts-notifications'])
				{
					case 'everyone':
						$values[]='("posts-notifications",1,"'.(int)$people_id.'","'.date('Y-m-d').'")';
						$message["posts-notifications"]=__("Post notifications - everyone",'church-admin');
					break;
					default:
						$message["posts-notifications"]=__("Post notifications - no-one",'church-admin');
					break;
				}
				switch($_POST['bible-readings-notifications'])
				{
					case 'everyone':
						$values[]='("bible-readings-notifications",1,"'.(int)$people_id.'","'.date('Y-m-d').'")';
						$message["bible-readings-notifications"]=__("Bible readings notifications - everyone",'church-admin');
					break;
					default:
						$message["bible-readings-notifications"]=__("Bible readings notifications - no-one",'church-admin');
					break;
				}
				switch($_POST['prayer-requests-notifications'])
				{
					case 'everyone':
						$values[]='("prayer-requests-notifications",1,"'.(int)$people_id.'","'.date('Y-m-d').'")';
						$message["prayer-requests-notifications"]=__("Prayer requests notifications - everyone",'church-admin');
					break;
					default:
						$message["prayer-requests-notifications"]=__("Prayer requests notifications - no-one",'church-admin');
					break;
					
				}

			}
		}
		if( !empty( $values ) ){
			$wpdb->query( $sql.implode( ",", $values ) );
		}
		echo'<div class="notice notice-success"><h2>'.esc_html( __('Global Communications settings updated','church-admin' ) ).'</h2><p>'.implode('<br/>',$message).'</p></div>';
	}

	echo '<form action="" method="POST">';
	echo'<h3>'.esc_html( __('Emails','church-admin' ) ).'</h2>';
	
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('Blog posts','church-admin' ) ).'</label><select class="church-admin-form-control" name="posts"><option value="everyone">'.esc_html( __('Everyone','church-admin' ) ).'</option><option value="no-one">'.esc_html( __('No-one','church-admin' ) ).'</option></select></div>';
	
	if(!post_type_exists('bible-readings') )
	{
		echo'<p>'.esc_html( __("You don't have Bible Readings post type currently",'church-admin' ) ).'</p>';;
	}
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('Bible readings','church-admin' ) ).'</label><select class="church-admin-form-control" name="bible-readings"><option value="everyone">'.esc_html( __('Everyone','church-admin' ) ).'</option><option value="no-one">'.esc_html( __('No-one','church-admin' ) ).'</option></select></div>';
	if(!post_type_exists('prayer-requests') )
	{
		echo'<p>'.esc_html( __("You don't have Bible Readings post type currently",'church-admin' ) ).'</p>';;
	}
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('Prayer Requests','church-admin' ) ).'</label><select class="church-admin-form-control" name="prayer-requests"><option value="everyone">'.esc_html( __('Everyone','church-admin' ) ).'</option><option value="no-one">'.esc_html( __('No-one','church-admin' ) ).'</option></select></div>';

	
	if(!empty($premium))
	{
		echo'<h3>'.esc_html( __('Push Notifications','church-admin' ) ).'</h3>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Blog posts','church-admin' ) ).'</label><select class="church-admin-form-control" name="posts-notifications"><option value="everyone">'.esc_html( __('Everyone','church-admin' ) ).'</option><option value="no-one">'.esc_html( __('No-one','church-admin' ) ).'</option></select></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Bible readings','church-admin' ) ).'</label><select class="church-admin-form-control" name="bible-readings-notifications"><option value="everyone">'.esc_html( __('Everyone','church-admin' ) ).'</option><option value="no-one">'.esc_html( __('No-one','church-admin' ) ).'</option></select></div>';
		echo'<div class="church-admin-form-group"><label>'.esc_html( __('Prayer Requests','church-admin' ) ).'</label><select class="church-admin-form-control" name="prayer-requests-notifications"><option value="everyone">'.esc_html( __('Everyone','church-admin' ) ).'</option><option value="no-one">'.esc_html( __('No-one','church-admin' ) ).'</option></select></div>';

	}
	/*
	echo'<h3>'.esc_html( __('SMS','church-admin' ) ).'</h3>';
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('SMS send','church-admin' ) ).'</label><select class="church-admin-form-control" name="sms_send"><option value="everyone">'.esc_html( __('Everyone','church-admin' ) ).'</option><option value="no-one">'.esc_html( __('No-one','church-admin' ) ).'</option></select></div>';
	*/
	echo'<p><input type="hidden" name="save" value="1" /><input type="submit" value="'.esc_html( __( 'Save', 'church-admin' ) ).'" class="button-primary" /></p></form>';


}

function church_admin_new_user_template()
{	

	if(!empty($_POST['save'])){
		
		update_option('church_admin_user_created_email',wp_kses_post(stripslashes( $_POST['user_template'] ) ) );
		echo '<div class="notice notice-success"><h2>'.esc_html('New user email template updated','church-admin').'</h2></div>';
	}
	
	$message=get_option('church_admin_user_created_email');

	if ( empty( $message) )
	{
		$message='<p>'.esc_html( __('The web team at','church-admin' ) ). ' <a href="[SITE_URL]">[SITE_URL]</a> '.esc_html( __('have just created a user login for you.','church-admin' ) ).'</p><p>'.esc_html( __('Your username is','church-admin' ) ).' <strong>[USERNAME]</strong></p><p>'.esc_html( __('Your password is','church-admin' ) ).' <strong>[PASSWORD]</strong></p><p>'.esc_html( __('We also have an app you can download for [ANDROID] and [IOS]','church-admin' ) ).' </p>';
	}
	echo'<h2>'.esc_html(__('New user email template','church-admin')).'<h2>';
	echo'<p>'.esc_html(__('This is the tempate for the email message that is sent when a new user is created from Church Admin plugin. You can use [SITE_URL], [USERNAME], [PASSWORD] as shortcodes to be replaced by the relevant data. Premium plugin users can also use [ANDROID] and [IOS] for app store links - although https://ourchurchapp.online takes users to the correct app store on their device.','church-admin')).'</p>';
	echo'<form action="" method="POST">';
	echo'<div class="church-admin-form-group"><label>'.esc_html(__('New user email template','church-admin') ).'</label>';
	echo'<textarea name="user_template" class="church-admin-form-control">';
	echo wp_kses_post($message);
	echo'</textarea></div>';
	echo'<p><input type="hidden" name="save" value="yes"><input class="button-primary" type="submit" value="'.esc_html(__('Save','church-admin')).'"></p></form>';

	


}


/*****************************
*
* Choose filters to show
*
*****************************/
function church_admin_choose_filters()
{
    echo '<h2>'.esc_html( __("Choose which filters are shown",'church-admin' ) ).'</h2>';
    $menuFilters=array(
		'contact-form'=>esc_html( __('Contact form','church-admin' ) ),
		'show-me'=>esc_html( __('Shown in directory','church-admin' ) ),
	'address'=>esc_html( __('Address','church-admin' ) ),
	'bible-readings'=>esc_html(__('Bible readings','church-admin')),
	"genders"=>esc_html( __('Genders','church-admin' ) ),
	'photo-permission'=>esc_html( __('Photo permission','church-admin' ) ),
	'user-accounts'=>esc_html( __('User accounts','church-admin' ) ),
	'email-addresses'=>esc_html( __('Email address','church-admin' ) ),
	'phone-calls'=>esc_html(__("Receive phone calls",'church-admin' ) ),
	'cell'=>esc_html( __('Cell phone','church-admin' ) ),
	'classes'=>esc_html( __('Classes','church-admin' ) ),
	'gdpr'=>esc_html( __('Data protection confirmed','church-admin' ) ),
	'people_types'=>esc_html( __('People types','church-admin' ) ),
	'active'=>esc_html( __('Active','church-admin' ) ),
	'marital'=>esc_html( __('Marital Status','church-admin' ) ),
	'sites'=>esc_html( __('Sites','church-admin' ) ),
	'member_types'=>esc_html( __('Member Types','church-admin' ) ),
	'small-groups'=>esc_html( __('Small groups','church-admin' ) ),
	'ministries'=>esc_html( __('Ministries','church-admin' ) ),
	'birth-year'=>esc_html( __('Birth year','church-admin' ) ),
	'birth-month'=>esc_html( __('Birth month','church-admin' ) ),
	'parents'=>esc_html( __('Parents','church-admin' ) ),
	'spiritual-gifts'=>esc_html( __('Spiritual gifts','church-admin' ) ),
	'email-send'=>esc_html( __('Email Permission','church-admin') ),
	'age-related'=>esc_html(__('Age related groups','church-admin'))
	);
	ksort($menuFilters);
    //add on custom fields
    $customFields=church_admin_get_custom_fields();
	
    if(!empty( $customFields) )
    {
        foreach ( $customFields AS $ID=>$field)
        {
            $menuFilters[sanitize_title( $field['name'] )]=$field['name'];
        }
    }
    if(!empty( $_POST['save-filters'] ) )
    {
        $chosenFilters=array();
        foreach ( $menuFilters AS $ID=>$field)
		{
            if(!empty( $_POST[$ID] ) )$chosenFilters[$ID]=TRUE;
        }
        update_option('church-admin-which-filters',$chosenFilters);
        echo'<div class="notice notice-success inline"><h2>'.esc_html( __("Filter choice saved",'church-admin' ) ).'</h2></div>';
    }
	$whichFilters=get_option("church-admin-which-filters");
    echo'<form action="" method="POST">';
    echo'<table class="form-table"><tbody>';
    foreach( $menuFilters AS $ID=>$field)
    {
        echo'<tr><th scope="row">'.esc_html( $field).'</th><td><input type="checkbox" name="'.esc_html( $ID).'" ';
        if(!empty( $whichFilters[$ID] ) )echo 'checked="checked" ';
        echo' value=1/></td></tr>';
    }
    echo'<tr><td colspan=2><input type="hidden" name="save-filters" value="TRUE" /><input type="Submit" class="button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></td></tr></table>';

    
    
}