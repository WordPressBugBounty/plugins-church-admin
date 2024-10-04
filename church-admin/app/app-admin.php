<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly


/****************************************
 * APP AJAX FUNCTIONS
 ***************************************/

/************************
 * APP VERSION >=24
 ************************/

add_action("wp_ajax_ca_app", "ca_app_ajax");
add_action("wp_ajax_nopriv_ca_app", "ca_app_ajax");
function ca_app_ajax()
{
	
	
	global $wpdb;
	//initialise variables
	$loginStatus=FALSE;
	$style=get_option('church_admin_app_style');
    $church_id=get_option('church_admin_app_id');
	$menu_title=get_option('church_admin_app_menu_title');
	$output = array( 'message'=>"",'content'=>esc_html( __( 'No content yet', 'church-admin') ) );
	$output['menu_title']=!empty($menu_title) ? esc_html($menu_title) : __('Menu','church-admin');
	if(!empty($loginStatus->token)){

		$output['token'] = esc_html( $loginStatus->token );
	}
	$token=null;

	


	//create headers
	
	header('Access-Control-Max-Age: 1728000');
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
	//"Origin, X-Requested-With, Content-Type, Accept"
	header('Access-Control-Allow-Credentials: true');

	//exit if no method
	if ( empty( $_REQUEST['method'] ) )
	{
		echo json_encode( $output);
		exit();
	}
	//check login status
	if(!empty( $_REQUEST['token'] ) )$token=sanitize_text_field( stripslashes ($_REQUEST['token'] ) );
	if(!empty( $token) )
	{
		
		//check login status
		$loginStatus=$wpdb->get_row('SELECT b.UUID AS token,a.member_type_id,a.people_id,a.user_id,a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql( $token).'"');
		//church_admin_debug( $wpdb->last_query);
		//church_admin_debug( $loginStatus);
        //if app token, update
		if(!empty( $_REQUEST['pushToken'] ) )
        {
            $sql='UPDATE '.$wpdb->prefix.'church_admin_people SET pushToken="'.esc_sql( sanitize_text_field(stripslashes($_REQUEST['pushToken']) ) ).'" WHERE people_id="'.(int)$loginStatus->people_id.'"';
			//church_admin_debug( $wpdb->last_query);
            if(!empty( $loginStatus->people_id) )$wpdb->query( $sql);
        }
	}

	if(!empty($loginStatus))
	{
		//first time Bible Streak set up
	
		$show_streak = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$loginStatus->people_id.'" AND meta_type="show-bible-readings-streak"');
		if( empty ( $show_streak ) ){
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,people_id,ID, meta_date)VALUES("show-bible-readings-streak", "'.(int)$loginStatus->people_id.'",1,"'.date('Y-m-d').'") ');
		}
	}
	$method=ltrim( sanitize_text_field(stripslashes($_REQUEST['method']) ), "#" );

	$currentHomePage = get_option('church-admin-app-homepage');
	church_admin_debug('Option to set different home page content '.$currentHomePage);
	if(!empty($currentHomePage) && $method =='home'){
		church_admin_debug('Using '.$currentHomePage);
		$method = $currentHomePage;
	}

	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	switch( $method)
	{
		case 'create-user':
			if ( empty( $loginStatus) )  {
				$output=ca_app_new_login_form('address');
			}
			elseif(!empty( $loginStatus)&& church_admin_level_check('Directory',$loginStatus->user_id) ){
				$output = ca_app_new_create_user($loginStatus);
			}
			else{
				$output = array('message'=>__("You don't have permission to do that",'church-admin')); 
			}
		break;
		case 'change-password':
			if ( empty( $loginStatus) )  {
				$output=ca_app_new_login_form('address');
			}
			elseif(!empty( $loginStatus)&& church_admin_level_check('Directory',$loginStatus->user_id) ){
				$output = ca_app_new_password_change($loginStatus);
			}
			else{
				$output = array('message'=>__("You don't have permission to do that",'church-admin')); 
			}
		break;
		case 'delete-me':
			if ( empty( $loginStatus) )  {
				$output=ca_app_new_login_form('account');
			}
			else 
			{
				$household_numbers = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$loginStatus->household_id.'"');
				if(!empty($household_numbers) && $household_numbers==1){$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$loginStatus->household_id.'"');}
				$person= $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$loginStatus->people_id.'"');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_app WHERE people_id="'.(int)$loginStatus->people_id.'"');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$loginStatus->people_id.'"');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$loginStatus->people_id.'"');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE people_id="'.(int)$loginStatus->people_id.'"');
				//wp_delete_user($loginStatus->user_id,null);
				$admin_message=__('App user has deleted themselves, please delete user account on website','church-admin');
				$admin_message.='<a href="'.get_edit_user_link($loginStatus->user_id).'">'.sprintf(__('User account edit/delete %1$s','church-admin'),church_admin_formatted_name($name)).'</a></p>';
				church_admin_email_send(get_option('church_admin_default_from_email'),__('Household deleted','church-admin'),$admin_message);
				$output=array('content'=>'<h2>'.__('Success','church-admin').'</h2><p>'.__('You have been deleted from the directory and your user account has been deleted. Sorry to see you go.','church-admin').'</p>','view'=>'html');
				
			}
		break;
		case 'volunteer':
		case 'serving':
			if ( empty( $loginStatus) )  {
				$output=ca_app_new_login_form('volunteer');
			}
			else 
			{
				church_admin_app_log_visit( $loginStatus,__('Serving','church-admin') );
				$output=ca_app_volunteer($loginStatus);

			}

		break;
		case 'delete-my-prayer':
			if ( empty( $loginStatus) )  {
				$output=ca_app_new_login_form('my-prayer');
			}
			else 
			{
				$output=ca_app_delete_prayer($loginStatus);

			}
		break;
		case 'answer-my-prayer':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('my-prayer');}
			else $output=ca_app_answer_prayer($loginStatus);
		break;
		case 'render-my-prayer':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('my-prayer');}
			else {
				church_admin_app_log_visit( $loginStatus, __('My prayer','church-admin') );
				$output=ca_app_show_prayer($loginStatus);
			}
		break;
		case 'edit-my-prayer':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('edit-my-prayer');}
			else $output=ca_app_edit_my_prayer_form( $loginStatus );
		break;
		case 'save-my-prayer':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('edit-my-prayer');}
			else $output=ca_app_save_my_prayer( $loginStatus );
		break;
		case 'get-notifications-form':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('notifications');}
			else $output=ca_app_new_get_notification_settings_form( $loginStatus);
		break;
		case 'update-notification-settings':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('notifications');}
			else $output=ca_app_new_save_notification_settings( $loginStatus);
		break;
		case 'not-available-save':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('not-available');}
			else $output=ca_app_new_not_available_save( $loginStatus);
		break;
		case 'not-available':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('not-available');}
			else $output=ca_app_new_not_available( $loginStatus);
		break;
		case 'comment':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('comment');}
			else $output=ca_app_new_comment( $loginStatus);
		break;
		case 'my-rota':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('my-rota');}
			else $output=ca_app_new_my_rota( $loginStatus);
		break;
		case 'refresh-address-list':
			church_admin_app_log_visit( $loginStatus, __('Address List','church-admin') );
			$output=ca_app_new_refresh_address_list( $loginStatus);
		break;
		case 'connect-user':
			$output=ca_app_new_connect_user( $loginStatus);
		break;
			
		case 'app-content':
			$output=ca_app_new_app_page( $loginStatus);
		break;
		case 'register':
			$appRegistrations=get_option('church_admin_no_app_registrations');
			if(!empty( $appRegistrations) )return;
			church_admin_app_log_visit( $loginStatus, __('Register','church-admin') );
			if(isset( $_REQUEST['email_send'] ) )//always set 1 or 0
			{
				if(!empty( $loginStatus)&&church_admin_level_check('Directory',$loginStatus->user_id) )
				{
					//admin registering someone, so no required field checks
					$output=ca_app_new_register_process( $loginStatus);
				}
				elseif ( empty( $_REQUEST['first_name'] )||empty( $_REQUEST['last_name'] )||empty( $_REQUEST['email_address'] ) )
				{
					$output=ca_app_new_register( $loginStatus);
					$output['message']=esc_html( __( 'Please fill in required fields', 'church-admin' ) );
				}	
				else $output=ca_app_new_register_process( $loginStatus);
			}
			else $output=ca_app_new_register( $loginStatus);
		break;
		case 'sms-reply':
			church_admin_app_log_visit( $loginStatus, __('SMS reply','church-admin') );
			$output=ca_app_new_sms_reply( $loginStatus);
		break;
		case 'sms-thread':
			$output=ca_app_new_sms_thread( $loginStatus);
		break;
		case 'sms-replies':
			$output=ca_app_new_sms_replies( $loginStatus);
		break;
		case 'save-class':
			$output=ca_app_new_save_class( $loginStatus);
		break;
		case 'edit-class':
			$output= ca_app_new_edit_class( $loginStatus);
		break;
		case 'save-checkin-students':
			$output=ca_app_new_checkin_class_students( $loginStatus);
		break;
		case 'save-class-students':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('classes');}
			else $output=ca_app_new_save_class_students( $loginStatus);
		break;
		case 'classes':
			church_admin_app_log_visit( $loginStatus, __('Classes','church-admin' ) ,$loginStatus);
			$output=ca_app_new_classes( $loginStatus);
		break;
		case 'mygroup':
			church_admin_app_log_visit( $loginStatus, __('My group','church-admin') );
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('mygroup');}
			else $output=ca_app_new_mygroup( $loginStatus);
		break;
		case 'checkin':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('checkin');}
			$output=ca_app_new_checkin( $loginStatus);
		break;
		case 'save-rota':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('rota');}
			else $output=ca_app_new_save_rota( $loginStatus);
		break;
		case 'rota':
			church_admin_app_log_visit( $loginStatus, __('Schedule','church-admin') );
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('rota');}
			$output=ca_app_new_rota( $loginStatus);
		break;
		case 'change-member-type':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('address');}
			else $output=ca_app_change_member_type( $loginStatus);
		break;

		case 'change-group':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('address');}
			else $output=ca_app_change_small_group( $loginStatus);
		break;
		case 'forgotten-password':
			$output=ca_app_new_forgotten_password();
		break;
		case 'delete-contact-message':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('contact-messages');}
			else $output=ca_app_new_delete_contact_message( $loginStatus);
		break;
		case 'contact-messages':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('contact-messages');}
			else $output=ca_app_new_contact_messages( $loginStatus);
		break;
		case 'media':
			
			$output=ca_app_new_media($loginStatus);
		break;
		case 'send-prayer':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('prayer');}
			else
			{
				$output=ca_app_new_prayer_send( $loginStatus);
			}
		break;
		case 'approve-prayer':
			if(!church_admin_level_check('Prayer',$loginStatus->user_id) ) exit();
			$output=ca_app_new_approve_prayer( $loginStatus);
		break;
		case 'reject-prayer':
			if(!church_admin_level_check('Prayer',$loginStatus->user_id)  ) exit();
			$output=ca_app_new_reject_prayer( $loginStatus);
		break;
		case 'prayer':
			
			church_admin_app_log_visit( $loginStatus, __('Prayer','church-admin') );
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('prayer');}
			else $output=ca_app_new_prayer( $loginStatus);
		break;
		case 'post':
			$output=ca_app_new_single_post( (int)$_REQUEST['id'],$loginStatus );
		break;
		case 'posts':
			//church_admin_app_log_visit( $loginStatus, __('Posts','church-admin') );
			$output=ca_app_new_posts('post',$loginStatus,1);
		break;
		case 'bible-readings-archive':
		case '#bible-readings-archive':
			church_admin_app_log_visit( $loginStatus, __('Bible Readings Archive','church-admin') );
			$output=ca_app_new_posts('bible-readings',$loginStatus,1);
		break;
		case 'calendar':
			church_admin_app_log_visit( $loginStatus, __('Calendar','church-admin') );
			$output=ca_app_new_calendar( $loginStatus);
		break;
		case '#courage':
		case 'courage':
			
			$output=ca_app_new_acts_of_courage();
		break;
		case 'bible':
		case 'bible-readings':
		case '#bible-readings':
			church_admin_app_log_visit( $loginStatus, __('Bible Readings','church-admin') );
			$output=ca_app_new_bible_readings( $loginStatus);
		break;
		case 'home':
		case 'giving':
		case 'smallgroup':
			
			$output=ca_app_new_app_content( $method,$loginStatus);
		break;
		case 'account':
			church_admin_app_log_visit( $loginStatus, __('Account','church-admin') );
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('account');}
			else{$output=ca_app_new_account( $loginStatus);}
		break;
		case 'login':
			$output=ca_app_new_login();
			//church_admin_debug('AT LINE 243 $output[content]');
			//church_admin_debug( $output['content'] );
		break;
		case 'search':
			church_admin_app_log_visit( $loginStatus, __('Search address list','church-admin') );
			church_admin_debug('***** CASE search *****');
			
			if ( empty( $loginStatus) )  {
				$output = ca_app_new_login_form ( 'search' );
			}
			else
			{
				$output = ca_app_new_search( $loginStatus );
			}
		break;
		case 'address-list':
			church_admin_app_log_visit( $loginStatus, __('Address List','church-admin') );
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('address-list');}
			else
			{
				$whichAppAddressList=get_option('church_admin_which_app_address_list_type');
				if ( empty( $whichAppAddressList)||$whichAppAddressList=='new')
				{
					$output=ca_app_new_address_list( $loginStatus);
				}
				else $output=ca_app_old_address_list( $loginStatus);
			}
		break;
		case '3circles':
			church_admin_app_log_visit( $loginStatus,'3 Circles');
		break;
		
		case 'address_edit':
			$noEditing = get_option('church_admin_no_app_editing');
			if(empty($noEditing)||(!empty($loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id))){
				if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('account');}
				else
				{
					$household_id=(int)sanitize_text_field(stripslashes($_REQUEST['household_id']));
					if(church_admin_level_check('Directory',$loginStatus->user_id) || $household_id=$loginStatus->household_id)
					{
						$output=ca_app_new_address_edit( $household_id,$loginStatus);	
					}
					else
					{
						$output = array( 'token'=>esc_html($token),
										'message'=>esc_html(  __( 'Error', 'church-admin' ) ),
										'content'=>esc_html( __( 'There is no directory entry for your login yet', 'church-admin') ) );
						
					}
					
				}
			}
		break;
		case 'people_delete':
			//church_admin_debug('PEOPLE DEBUG');
			$noEditing = get_option('church_admin_no_app_editing');
			if(empty($noEditing)||(!empty($loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id))){
				if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('account');}
				else
				{
					$people_id=(int)sanitize_text_field(stripslashes($_REQUEST['people_id']));
					$household_id=(int)sanitize_text_field(stripslashes($_REQUEST['household_id']));
					if(church_admin_level_check('Directory',$loginStatus->user_id) || $household_id==$loginStatus->household_id)
					{
						$output=ca_app_new_people_delete( $people_id,$household_id,$loginStatus);
					}
					else
					{
						$output = array( 'token'=>esc_html( $token ),
										'message'=>esc_html(  __( 'Error', 'church-admin' ) ),
										'content'=>esc_html( __( 'You cannot delete that person','church-admin') )
									);
					}
					
				}
			}
		break;
		case 'people_edit':
			$noEditing = get_option('church_admin_no_app_editing');
			if(empty($noEditing)||(!empty($loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id))){
				if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('account');}
				else
				{
					$people_id=(int)sanitize_text_field(stripslashes($_REQUEST['people_id']));
					$household_id=(int)sanitize_text_field(stripslashes($_REQUEST['household_id']));
					$output = ca_app_new_people_edit( $people_id,$household_id,$loginStatus);
					//array('content'=>'People Edit content '.$people_id,"page_title"=>esc_html( __('Person edit','church-admin' ) ),'view'=>'html');
				}
			}
		break;
		case 'calendar-edit':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('calendar');}
			elseif(!empty( $loginStatus) && !church_admin_level_check('Calendar',$loginStatus->user_id) )
			{
				$output=ca_app_new_calendar( $loginStatus);
				$output['message']=esc_html( __( 'You do not have calendar edit permissions', 'church-admin' ) );
			}
			else
			{
				$output= ca_app_new_calendar_form( sanitize_text_field(stripslashes($_REQUEST['type']) ),sanitize_text_field(stripslashes($_REQUEST['date_id']) ),sanitize_text_field(stripslashes($_REQUEST['event_id']) ) );
			}
		break;
		case 'calendar-save':
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('calendar');}
			elseif(!empty( $loginStatus) && !church_admin_level_check('Calendar',$loginStatus->user_id) )
			{
				$output=ca_app_new_calendar( $loginStatus);
				$output['message']=esc_html( __( 'You do not have calendar edit permissions', 'church-admin' ) );
			}
			else
			{
				$output=ca_app_new_calendar_save( $loginStatus);
			}
		break;
		case 'calendar-delete':
			//church_admin_debug('Calendar delete');
			if ( empty( $loginStatus) )  {$output=ca_app_new_login_form('calendar');}
			elseif(!empty( $loginStatus) && !church_admin_level_check('Calendar',$loginStatus->user_id) )
			{
				$output=ca_app_new_calendar( $loginStatus);
				$output['message']=esc_html(  __( 'You do not have calendar edit permissions', 'church-admin') );
			}
			else
			{
				//church_admin_debug("trying to delete");
				//church_admin_debug( $_REQUEST);
				$output = ca_app_new_calendar_delete( sanitize_text_field(stripslashes($_REQUEST['type']) ),sanitize_text_field(stripslashes($_REQUEST['date_id']) ),sanitize_text_field(stripslashes($_REQUEST['event_id']) ));
			}
		break;
	}
	if(!empty( $loginStatus->token) &&is_array( $output) ){
		$output['token'] = $loginStatus->token;
	}
	
	$output['church_id']=(int)$church_id;
	//menu built in login process, if not called build it now.
	$menuPeopleID=!empty( $loginStatus->people_id)?$loginStatus->people_id:FALSE;
	$menuOutput=ca_build_menu( $menuPeopleID);
	if ( empty( $menuOutput) )
	{
		$menuOutput=array('<li id="home-tab-button" class="tab-button" data-tab="#home" data-cached=1> <span class="languagespecificHTML" data-text="home">Home</span></li>','<li id="logout-tab-button" class="tab-button" data-tab="#logout"  data-tap-toggle="false"><span class="languagespecificHTML" data-text="logout">Logout</span></li>');
	}
	$output['menu']=implode("\r\n",$menuOutput);

	/*************************
	 * language
	 ************************/
	$language=array(
					'new-message'=>esc_html(__('New push message received','church-admin')),
					'native-settings'=>esc_html( __('Device settings', 'church-admin' ) ),
					'try-again'=>esc_html( __('Try again', 'church-admin' ) ),
					'prayer-item-title'=>esc_html( __('Prayer item title','church-admin') ),
					'prayer-item-description'=>esc_html( __('Prayer item description','church-admin') ),
					'save'=>esc_html( __('Save','church-admin') ),
					'email-address'=>esc_html( __('Email address','church-admin') ),
					'reset'=>esc_html( __('Reset password','church-admin') ),
					'messages-cleared' => esc_html( __('Messages cleared','church-admin')  ),
					'messages' => esc_html( __('Messages','church-admin') ),
					'delete' => esc_html( __('Delete','church-admin') ),
					'email-address' => esc_html( __('Email address','church-admin') ),
					'reset-password' => esc_html( __('Reset password','church-admin') ),
					'reset' => esc_html( __('Reset','church-admin') ),
					'monday' => esc_html( __('Monday','church-admin') ),
					'tuesday'=>esc_html( __('Tuesday','church-admin') ),
					'wednesday'=>esc_html( __('Wednesday','church-admin') ),
					'thursday'=>esc_html( __('Thursday','church-admin') ),
					'friday'=>esc_html( __('Friday','church-admin') ),
					'saturday'=>esc_html( __('Saturday','church-admin') ),
					'sunday'=>esc_html( __('Sunday','church-admin') ),
					'thank-god-for-today'=>esc_html( __('Thank God for today','church-admin') ),
					'my-prayer-list-for'=>esc_html( __('My prayer list for ','church-admin') ),
					'add-an-item'=>esc_html( __('Add an item','church-admin') ),
					'pray-which-days'=>esc_html( __('Pray which days','church-admin') ),
					'answered-prayer'=>esc_html( __('Answered prayer','church-admin') ),
					'edit'=>esc_html( __('Edit','church-admin') ),
					'delete'=>esc_html( __('Delete','church-admin') ),
					'answered'=>esc_html( __('Answered','church-admin') ),
					'prayer-settings' => esc_html( __('Prayer settings','church-admin') ),
					'reset'=>esc_html(__('Reset','church-admin'))
				);
	$output['language']=$language;

	
	/*************************
	* Send $output to app
	**************************/
	//church_admin_debug('Ouput for app');
	//church_admin_debug($output);
	echo json_encode( $output);
	exit();
}
function ca_refresh_app_cache( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),);

	/*************************
	* Build menu
	**************************/
	if ( empty( $output['menu'] ) )
	{
		//menu built in login process, if not called build it now.
		$menuPeopleID=!empty( $loginStatus->people_id)?$loginStatus->people_id:FALSE;
		$menuOutput=ca_build_menu( $menuPeopleID);
		
		if ( empty( $menuOutput) )
		{
			$menuOutput=array('<li id="home-tab-button" class="tab-button" data-tab="#home" data-cached=1> <span class="languagespecificHTML" data-text="home">Home</span></li>','<li id="logout-tab-button" class="tab-button" data-tab="#logout"  data-tap-toggle="false"><span class="languagespecificHTML" data-text="logout">Logout</span></li>');
		}
		$output['menu']=implode("\r\n",$menuOutput);
	}
	

	//app-content pages
	
	$output['cached_content']['smallgroup']='';
	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type="app-content" AND post_status="publish"');
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			$output['cached_content'][sanitize_title( $row->post_title)]['page_title']=esc_html( $row->post_title );
			if(!empty($row->post_content) && has_blocks( $row->post_content ) )
			{
				remove_filter( 'the_content', 'wpautop' );
				$output['cached_content'][sanitize_title( $row->post_title)]['content']=apply_filters("the_content", do_blocks( $row->post_content ));
			}
			else
			{
				$output['cached_content'][sanitize_title( $row->post_title)]['content'] = do_shortcode($row->post_content);
			}


		}
		
	}
	$appContentModified=get_option('church_admin_modified_app_content');
	$output['last_modified']=$appContentModified;
	
	//address
	//church_admin_debug("REFRESH app address cache");
	$whichAppAddressList=get_option('church_admin_which_app_address_list_type');
	if ( empty( $whichAppAddressList)||$whichAppAddressList=='new')
	{
		$output['cached_content']['address']=ca_app_new_address_list( $loginStatus);
	}
	else
	{
		//cache old style
		$output['cached_content']['address']=ca_app_old_address_list( $loginStatus);

	}
	//if coming off an edit or a delete there's a next variable to show what screen next
	if(!empty( $_REQUEST['next'] ) )
	{
		switch( sanitize_text_field(stripslashes($_REQUEST['next']) ) )
		{
			case 'address': 
				switch( $whichAppAddressList)
				{
					default:
					case 'new':
						$output['content']=ca_app_new_address_list( $loginStatus);
					break;
					case 'old':
						$output['content']=ca_app_old_address_list( $loginStatus);
					break;
				}
			break;
			
			case 'account':
				$accountArray=ca_app_new_account( $loginStatus);
				$output['content']=$accountArray['content'];
			break;
			
		}
	}	
	//groups - app content page so add in group
	$output['cached_content']['smallgroup']=array('view'=>'html','page_title'=>esc_html( __('Groups','church-admin') ),'content'=>ca_app_new_groups($loginStatus) );
	$output['cached_content']['rota']=ca_app_new_rota( $loginStatus);
	
	$output['cached_content']['media']=ca_app_new_media($loginStatus);
	$output['cached_content']['news']=ca_app_new_posts('post',$loginStatus,0);
	$output['cached_content']['calendar']=ca_app_new_calendar( $loginStatus);
	//church_admin_debug( $output);
	//church_admin_debug("****** REFRESH app address cache *****");
	return $output;
}
/**************************
 * Account
 **************************/
function ca_app_new_account( $loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$noEditing = get_option('church_admin_no_app_editing');
	global $wpdb;
	//church_admin_debug("******* ca_app_new_account *****");
	
	if(!empty( $token) )
	{
		$person=$wpdb->get_row('SELECT a.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql( $token).'"');
	}
	if(!empty( $loginStatus->household_id) )
	{
		$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$loginStatus->household_id.'" AND head_of_household=1');
	}
	//church_admin_debug('$person object');
	//church_admin_debug( $person);
	if ( empty( $person) )
	{
		$output = array( 'token'=>esc_html( $token ),'message'=>esc_html( __('There is no directory entry for your login yet','church-admin' ) ),'content'=>'');
		return $output;
	}
	
	$others=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$loginStatus->household_id.'" AND people_id!="'.(int)$person->people_id.'" ORDER BY people_order');
	//church_admin_debug( $wpdb->last_query);
	$address=$wpdb->get_row('SELECT phone, address,lat,lng FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$loginStatus->household_id.'"');
	
	$platform = !empty( $_REQUEST['platform'] )? sanitize_text_field( stripslashes ($_REQUEST['platform'] ) ):'browser';
	if(!empty( $_REQUEST['notifications'] ) )
	{
		//church_admin_debug("notification status");
		//church_admin_debug( $_REQUEST['notifications'] );
		$notifications = sanitize_text_field(stripslashes($_REQUEST['notifications'] ));
	}
	/*****************
	 * Build Output
	 ****************/
	$content='';
	
	 if(!empty($noEditing)){
		$content ='<p>'.__('Editing has been disabled','church-admin').'</p>';
		//no edit version
		$content.='<h3>'.esc_html( __('Address','church-admin' ) ).'</h3>';
		$add=!empty( $address->address)?esc_html( $address->address):'';
		$phone=!empty( $address->phone)?esc_html( $address->phone):'';
		$content.='<ul id="list" class="ui-listview"><li class="address"><div>'.esc_html( $add ).'</li>';
		$content.='<li class="phone"><div >'.esc_html( $phone ) .'</li></ul>';
		$content.='<h3>'.esc_html( __( 'People in your household', 'church-admin' ) ).'</h3>';
		$content.='<ul class="account ui-listview">';
		if(!empty( $person) )
		{
			$content.='<li class="person"><div ><h4>'.church_admin_formatted_name( $person).'</h4>';
			if(!empty( $person->attachment_id) )$content.='<p>'.esc_url( wp_get_attachment_image( $person->attachment_id,'thumbnail','',array('class'=>'person-image') ) ).'</p>';
			if(!empty($person->people_id) && $person->people_id == $loginStatus->people_id){
				
				$content.='<p><a class="linkButton red" onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.site_url().'/?church_admin_delete='.(int)$loginStatus->people_id.'&amp;token='.$token.'">'.esc_html(__('Delete me','church-admin')).'</a></p>';
			}
			$content.='</li>';
		}
		if(!empty( $others) )
		{
			foreach( $others AS $person)
			{
				$content.='<li class="person"><div><h4>'.church_admin_formatted_name( $person).'</h4>';
				if(!empty( $person->attachment_id) )$content.='<p>'.esc_url( wp_get_attachment_image( $person->attachment_id,'thumbnail','',array('class'=>'person-image') ) ).'</p>';
				$content.='</li>';
			}
	
			}
			
			$content.='</ul>';
	 }
	 else
	 {
		$content.='<p><button class="action button" data-tab="notifications">'.esc_html( __('Notification settings','church-admin' ) ).'</button></p>';
	//address
	$content.='<h3>'.esc_html( __('Address','church-admin' ) ).'</h3>';
	$add=!empty( $address->address)?esc_html( $address->address):__('Add address','church-admin');
	$phone=!empty( $address->phone)?esc_html( $address->phone):__('Add home phone','church-admin');
	$content.='<ul id="list" class="ui-listview"><li class="address"><div  class="address_edit ui-btn ui-btn-icon-right ui-icon-edit" id="'.(int)$person->household_id .'" data-householdid="'.(int)$person->household_id .'" data-tab="address_edit">'.esc_html( $add ).'</li>';
	$content.='<li class="phone"><div  class="address_edit ui-btn ui-btn-icon-right ui-icon-edit" id="'.(int)$person->household_id .'" data-householdid="'.(int)$person->household_id .'" data-tab="address_edit">'.esc_html( $phone ) .'</li></ul>';
	$content.='<h3>'.esc_html( __( 'People in your household', 'church-admin' ) ).'</h3>';
	$content.='<ul class="account ui-listview">';
	if(!empty( $person) )
	{
		$content.='<li class="person"><div  class="people_edit ui-btn ui-btn-icon-right ui-icon-edit" data-householdid="'.(int)$person->household_id.'" id="'.(int)$person->people_id.'" data-peopleid="'.(int)$person->people_id.'" data-tab="people_edit" data-next="account"><h4>'.church_admin_formatted_name( $person).'</h4>';
		if(!empty( $person->attachment_id) )$content.='<p>'.esc_url( wp_get_attachment_image( $person->attachment_id,'thumbnail','',array('class'=>'person-image') ) ).'</p>';
		
		if(!empty($person->people_id) && $person->people_id == $loginStatus->people_id){
			//$content.='<p><button onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" class="action button" data-tab="delete-me" data-householdid="'.(int)$person->household_id.'" id="'.(int)$person->people_id.'" data-peopleid="'.(int)$person->people_id.'">'.__('Delete me','church-admin').'</button></p>';
			$content.='<p><a class="linkButton red" onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.site_url().'/?church_admin_delete='.(int)$loginStatus->people_id.'&amp;token='.$token.'">'.esc_html(__('Delete me','church-admin')).'</a></p>';
		}
		$content.='</li>';
	}
	if(!empty( $others) )
	{
		foreach( $others AS $person)
		{
			$content.='<li class="person"><div  class="people_edit ui-btn ui-btn-icon-right ui-icon-edit"  data-peopleid="'.(int)$person->people_id.'"  data-householdid="'.(int)$person->household_id.'" data-tab="people_edit"><h4>'.church_admin_formatted_name( $person).'</h4>';
			if(!empty( $person->attachment_id) )$content.='<p>'.esc_url( wp_get_attachment_image( $person->attachment_id,'thumbnail','',array('class'=>'person-image') ) ).'</p>';
			if(!empty($person->people_id) && $person->people_id == $loginStatus->people_id){
				//$content.='<p><button onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" class="action button" data-tab="delete-me" data-householdid="'.(int)$person->household_id.'" id="'.(int)$person->people_id.'" data-peopleid="'.(int)$person->people_id.'">'.__('Delete me','church-admin').'</button></p>';
				$token = !empty($loginStatus->token)?$loginStatus->token:null;
				$content.='<p><a class="linkButton red" onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.site_url().'/?church_admin_delete='.(int)$loginStatus->people_id.'&amp;token='.$token.'">'.esc_html(__('Delete me','church-admin')).'</a></p>';
			}
			$content.='</li>';
		}
	
	}
	$content.='<li  class="person"><div  data-householdid="'.(int)$person->household_id.'" data-peopleid="0" data-tab="people_edit" class="people_edit ui-btn ui-btn-icon-right ui-icon-edit">'.esc_html( __( 'Add someone', 'church-admin' ) ).'</li>';
	$content.='</ul>';

	 }
	
	
	
	//end build output
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'content'=>$content,'view'=>'html','page_title'=>esc_html( __('Account','church-admin') ) );
	//church_admin_debug( $output);
	return $output;
}



/***************************
 * Address List
 **************************/

 function ca_app_new_search( $loginStatus )
 {
	church_admin_debug('************** ca_app_new_search  *****************');
	church_admin_debug('Login Status');
	//church_admin_debug($loginStatus);
	global $wpdb;
	$groups=church_admin_groups_array();
	$member_types=church_admin_member_types_array();
	$appPeopleTypes=get_option('church_admin_app_people_types');
	if ( empty( $appPeopleTypes) )
	{
		$appPeopleTypes=array(1,3);
		update_option('church_admin_app_people_types',$appPeopleTypes);
	}
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Address search','church-admin' ) ),'view'=>'html');
	//check user has correct member type
	$mt=get_option('church_admin_app_member_types');
	$sql_safe_membSQL='';
	if ( empty( $mt) )$mt=array(1);
	foreach( $mt AS $key=>$type)  {$mtsql[]='a.member_type_id='.(int)$type;}
	if(!empty( $mtsql) )  {$sql_safe_membSQL=' AND ('.implode(' OR ',$mtsql).' ) ';}else{$sql_safe_membSQL='';}

	$access=FALSE;
	if ( user_can( $loginStatus->user_id,'manage_options') ) {
		$access = TRUE;
		church_admin_debug('User can manage options');
	}
	if ( !empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) ) {
		$access = TRUE;
		church_admin_debug('User has directory permissions');
	}
	if ( in_array( $loginStatus->member_type_id,$mt) ) {
		$access = TRUE;
		church_admin_debug('User is in allowed Member type');
	}
	if ( empty ( $access ) )
	{
		//church_admin_debug("App address list - wrong member type");
		//church_admin_debug(print_r( $loginStatus,TRUE) );
		$output['message']=__("Unfortunately you can't access the directory list",'church-admin');
		if ( empty( $loginStatus) )
		{
			$output['content'].='<p>'.esc_html( __('Please login','church-admin' ) ).'</p>';
			$loginForm=ca_app_new_login_form('address');
			$output['content'].=$loginForm['content'];
		}
		church_admin_debug('User did not have allowed access');
		return $output;
	}
	//check user is not on restricted list
	$restrictedList=get_option('church-admin-restricted-access');
	if(!user_can( $loginStatus->user_id,'manage_options') && is_array( $restrictedList)&& in_array( $loginStatus->people_id,$restrictedList) )
	{ 
		$output['message']=__("Unfortunately you can't access the directory list",'church-admin');
		if ( empty( $loginStatus) )
		{
			$output['content'].='<p>'.esc_html( __('Please login','church-admin' ) ).'</p>';
			$loginForm=ca_app_new_login_form('address');
			$output['content'].=$loginForm['content'];
		}
		return $output;
	}
	/*******************
	 * Safe to proceed
	 ******************/

	 $peopleSQL=array();
	 foreach( $appPeopleTypes AS $key=>$type)  {$peopleSQL[]='a.people_type_id='.(int)$type;}
	 $peopleTypeSQL='';
	 if(!empty( $peopleSQL) )
	 {
		 $peopleTypeSQL=' AND ('.implode(' OR ',$peopleSQL).' ) ';
	 }

	$addressList=array();
	$s=sanitize_text_field( stripslashes ($_REQUEST['search'] ) );
	
	//SEARCH
	$sql_safe_search='(CONCAT_WS(" ",a.first_name,a.last_name) LIKE("%'.esc_sql($s).'%")||CONCAT_WS(" ",a.first_name,a.middle_name,a.last_name) LIKE("%'.esc_sql($s).'%")||a.nickname LIKE("%'.esc_sql($s).'%")||a.first_name LIKE("%'.esc_sql($s).'%")||a.middle_name LIKE("%'.esc_sql($s).'%")||a.last_name LIKE("%'.esc_sql($s).'%")||a.email LIKE("%'.$s.'%")||a.mobile LIKE("%'.esc_sql($s).'%")||b.address LIKE("%'.esc_sql($s).'%")||b.phone LIKE("%'.esc_sql($s).'%")  ||b.address LIKE("%'.esc_sql($s).'%")  || b.phone LIKE ("%'.esc_sql($s).'%") ) AND';
	
	if(!empty( $_REQUEST['all'] ) && !empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
	{
		//admin level search
		$sql_safe_membSQL='';//no member_type_id restriction
		$output['message']=__('Search includes all member types','church-admin');
		$sql='SELECT a.*,b.address,b.lat,b.lng,b.attachment_id as household_attachment_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE '.$sql_safe_search.' a.household_id=b.household_id  '.$sql_safe_membSQL.' '.$peopleTypeSQL.'  ORDER BY a.last_name,a.first_name ASC ';
		//church_admin_debug($sql);
	}
	else
	{
		//other search
		
		$sql='SELECT a.*,b.address,b.lat,b.lng,b.attachment_id as household_attachment_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE '.$sql_safe_search.' a.household_id=b.household_id AND  a.show_me=1 AND a.active=1 '.$sql_safe_membSQL.'  '.$peopleTypeSQL.' ORDER BY a.last_name,a.first_name ASC ';
		//church_admin_debug($sql);
	}
		
		
		

	//church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	if ( empty( $results) )
	{
		
		$output['content']='<p><button class="action button" data-tab="address">'.esc_html( __("Back to address list",'church-admin' ) ).'</button><p>'.esc_html( __('Nothing found','church-admin' ) ).'</p>';
		return $output;
	}

	foreach( $results AS $row)
	{
		$person='<li class="ui-li ui-li-divider ui-bar-d "><span class="ui-btn ui-btn-icon-right ui-icon-plus vcf" data-peopleid="'.(int)$row->people_id.'" ><h3 class="ui-li-heading">'.church_admin_formatted_name( $row).'</h3></span>';
		if(!empty( $row->household_attachment_id )){
			$person.='<p>'.wp_get_attachment_image( $row->household_attachment_id,'medium','',array('class'=>'household-image','loading'=>'lazy') ).'</p>';
		}
		if(!empty( $row->attachment_id) )$person.='<p>'.wp_get_attachment_image( $row->attachment_id,'thumbnail','',array('class'=>'person-image','loading'=>'lazy') ).'</p>';
		if(!empty( $row->mobile) )$person.='<p ><a href="'.esc_url('tel:'.church_admin_e164( $row->mobile) ).'">'.esc_html( $row->mobile).'</a></p>';
		if(!empty( $row->email) )$person.='<p><a href="'.esc_url('mailto:'.$row->email).'">'.esc_html( $row->email).'</a></p>';
		if(!empty( $row->lat) && !empty( $row->lng) ) 
		{
			$person.='<p>'.esc_html( $row->address).'</p>';
			$person.='<p ><a class="linkButton green" href="'.esc_url('https://www.google.com/maps/search/?api=1&query='.$row->lat.','.$row->lng.'&amp;t=m&amp;z=16').'">'.esc_html( __("Map",'church-admin' ) ).'</a>'."\t".'&nbsp;<a href="https://www.google.com/maps/dir/?api=1&destination='.urlencode( $row->address).'" class="linkButton">'.esc_html( __('Directions','church-admin' ) ).'</a></p>'."\r\n\t";
		}
		else
		{
			if(!empty( $row->address) )$person.='<p>'.esc_html( $row->address).'</p>';
		}
		if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
		{

			$person.='<div class="admin-tasks-toggle ui-btn ui-btn-icon-right ui-icon-carat-d" data-id="person-'.(int)$row->people_id.'" >'.esc_html( __('Administrator tasks','church-admin' ) ).'</div><div class="admin-tasks" id="person-'.(int)$row->people_id.'">';
			//user id
			
			
			//Password
			if(!empty($row->user_id)){

				$person.='<p><strong>'.esc_html( __('User account','church-admin' ) ).'</strong> '.church_admin_user_check( $row,TRUE).'</p>';
				/*
				$person.='<div class="church-admin-form-group"><label>'.esc_html( __('Change password','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" id="new-password"></div>';
				$person.='</select></div><p><button class="action button" data-tab="change-password" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';
				*/

			}
			else{

				$person.='create user account';
			}		
			
			//Member Type
			$person.='<div class="church-admin-form-group"><label>'.esc_html( __('Change  member level','church-admin' ) ).'</label>';
		
			$person.='<select class="church-admin-form-control member_types" id="member_type_id'.(int)$row->people_id.'">';
			foreach( $member_types AS $id=>$type)
			{
				$person.='<option value="'.(int)$id.'" '.selected( $id,$row->member_type_id,FALSE).'>'.esc_html( $type).'</option>';
			}
			$person.='</select></div><p><button class="action button" data-tab="change-member-type" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';
			//Small Group
			$person.='<div class="church-admin-form-group"><label>'.esc_html( __('Small Group','church-admin' ) ).'</label>';
			$personGroup=church_admin_get_people_meta( $row->people_id,'smallgroup');
			$person.='<select class="church-admin-form-control smallgroup" id="id'.(int)$row->people_id.'">';
			foreach( $groups AS $id=>$group)
			{
				$person.='<option value="'.(int)$id.'" '.selected( $id,$personGroup['0'],FALSE).'>'.esc_html( $group).'</option>';
			}
			$person.='</select></div><p><button class="action button" data-tab="change-group" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';
			$person.='<p><button class="button green action" data-tab="people_edit" data-householdid="'.(int)$row->household_id.'" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Edit person','church-admin' ) ).'</button></p>';
			$person.='<p><button class="button red action" data-tab="people_delete" data-householdid="'.(int)$row->household_id.'"  data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Delete person','church-admin' ) ).'</button></p>';
			$adminPerson='<p><button class="button green action" data-tab="address_edit" data-next="address" data-householdid="'.(int)$row->household_id.'" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Edit person','church-admin' ) ).'</button></p>';
		
			$person.='</div>';
		}
		$person.='</li>';
		$addressList[]=$person;
	}
	$html='<p><button class="action button" data-tab="address">'.esc_html( __("Back to address list",'church-admin' ) ).'</button>';
	$html='<ul data-role="listview" data-theme="d" data-divider-theme="d" >';
	$html.=implode("\r\n",$addressList);
	$html.='</ul>';
	$message=esc_html( sprintf(__('Search for "%1$s"','church-admin' ) ,sanitize_text_field( stripslashes ($_REQUEST['search'] ) ) ) );
	$output['content']=$html;
	$output['message']=$message;
	church_admin_debug('************** END ca_app_new_search  *****************');
	return $output;
}

function ca_app_new_refresh_address_list( $loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
	{
		delete_option('church_admin_app_address_cache');
		delete_option('church_admin_app_admin_address_cache');
		church_admin_new_app_build_address_list( $loginStatus);
		update_option('church_admin_modified_app_content',time() );
	}
	
	$output=ca_app_new_address_list( $loginStatus);
	$output['message'].=__('Address list refreshed','church-admin');
	return $output;
	
}

function ca_app_new_address_list( $loginStatus)
{
	global $wpdb;
	$time_start = microtime(true); 
	//church_admin_debug( '*************** App address list - start '. $time_start.'****************');
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'content'=>'','view'=>'html','page_title'=>esc_html( __('Address List','church-admin' ) ),'message'=>'');
	//church_admin_debug( $loginStatus);
	//check user has correct member type
	$mt=get_option('church_admin_app_member_types');
	
	if ( empty( $mt) ){
		if(church_admin_level_check('Directory',$loginStatus->user_id)){$output['message'].=_("Which members types to show hasn't been set, so showing them all",'church-admin');}
		$mt= church_admin_member_type_ids();
		update_option('church_admin_app_member_types',$mt);
	}
	church_admin_debug('**** Looking for these member types');
	church_admin_debug( $mt);
	church_admin_debug('**** Login Status');
	church_admin_debug( $loginStatus);
	$user_id=!empty( $loginStatus->user_id)?$loginStatus->user_id:NULL;
	$member_type_id=!empty( $loginStatus->member_type_id)?$loginStatus->member_type_id:NULL;

	foreach( $mt AS $key=>$type)  {$mtsql[]='a.member_type_id='.(int)$type;}
	$access=FALSE;
	if(user_can( $user_id,'manage_options') )$access=TRUE;
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) ) $access=TRUE;
	if(in_array( $member_type_id,$mt) ) $access=TRUE;
	if(!$access)
	{
		//church_admin_debug("App address list - wrong member type");
		//church_admin_debug(print_r( $loginStatus,TRUE) );
		$output['content']='<p>'.esc_html( __("Unfortunately you can't access the directory list",'church-admin' ) ).'</p>';
		$output['content'].='<p>Error: wrong member type</p>'.print_r( $loginStatus,TRUE);
		if ( empty( $loginStatus) )
		{
			$output['content'].='<p>'.esc_html( __('Please login','church-admin' ) ).'</p>';
			$loginForm=ca_app_new_login_form('address');
			$output['content'].=$loginForm['content'];
		}
		return $output;
	}
	//check user is not on restricted list
	$restrictedList=get_option('church-admin-restricted-access');
	if(!user_can( $loginStatus->user_id,'manage_options') && is_array( $restrictedList)&&in_array( $loginStatus->people_id,$restrictedList) )
	{ 
		$output['message'].=__("Unfortunately you can't access the directory list",'church-admin');
		$outpt['content']='';
		return $output;
	}
	/*******************
	 * Safe to proceed
	 ******************/
	
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
	{
		//admin version
		//church_admin_debug('Attempt to retrieve admin cached version');
		$cachedVersion=get_option('church_admin_app_admin_address_cache');
	}
	else
	{
		//normal version
		//church_admin_debug('Attempt to retrieve cached version');
		$cachedVersion=get_option('church_admin_app_address_cache');
	}
	
	
	
	
	$html='<p><input id="s" type="text" placeholder="'.esc_html( __('Search for?','church-admin' ) ).'" /></p>';
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) ) $html.='<p><input type="checkbox" id="all-list" />'.esc_html( __("(Admins only) Include all member types",'church-admin' ) ).'</p>';
	$html.='<p><button id="search" data-tab="#search" class="button action" type="submit">'.esc_html( __('Search','church-admin' ) ).'</button></p>';
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) ) 
	{
		$html.='<p><button class="button action green" data-tab="register">'.esc_html( __('Add someone','church-admin' ) ).'</button></p>';
		$html.='<p><button class="button action red" data-tab="refresh-address-list">'.esc_html( __('Refresh Address List','church-admin' ) ).'</button></p>';
	}
	if ( empty( $cachedVersion) )
	{
		//church_admin_debug('Fresh Address list build');
		$content=$html.church_admin_new_app_build_address_list( $loginStatus);
	}
	else
	{
		//church_admin_debug('Using cached version');
		$content=$html.$cachedVersion;
	}
	if ( empty( $content) )
	{
		$output['message']=__("No people to show in address list",'church-admin');
		$output['content']='';
	}
	else
	{
		
		$output['content']=$content;
	}
	$output['page_title']=__('Address list','church-admin');
	$time_end = microtime(true); 
	//church_admin_debug('App address list - end '. $time_end);
	$time_taken=$time_end-$time_start;
	//church_admin_debug('App address list - time taken '. $time_taken);
	return $output;
}

function church_admin_new_app_build_address_list( $loginStatus)
{
	//church_admin_debug("***** church_admin_new_app_build_address_list ***** ");
	global $wpdb;
	
	$groups=church_admin_groups_array();
	$member_types=church_admin_member_types_array();
	$appPeopleTypes=get_option('church_admin_app_people_types');
	if ( empty( $appPeopleTypes) )
	{
		$appPeopleTypes=array(1,3);
		update_option('church_admin_app_people_types',$appPeopleTypes);
	}
	//used when caching after address list change
	
	//sort out member type section of SQL statement
	$mt=get_option('church_admin_app_member_types');
	if ( empty( $mt) ){
		
		$mt= church_admin_member_type_ids();
		update_option('church_admin_app_member_types',$mt);
	}
	foreach( $mt AS $key=>$type)  {$mtsql[]='a.member_type_id='.(int)$type;}
    if(!empty( $mtsql) )  {$membSQL=' AND ('.implode(' OR ',$mtsql).' ) ';}else{$membSQL='';}
	$peopleSQL=array();
	foreach( $appPeopleTypes AS $key=>$type)  {$peopleSQL[]='a.people_type_id='.(int)$type;}
	$peopleTypeSQL='';
	if(!empty( $peopleSQL) )
	{
		$peopleTypeSQL=' AND ('.implode(' OR ',$peopleSQL).' ) ';
	}
	//sql statement
	$sql='SELECT a.*, LEFT(a.last_name, 1) AS initial,b.address,b.lat,b.lng,b.phone FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id=b.household_id AND a.show_me=1 AND a.active=1 '.esc_sql($membSQL).' '.esc_sql($peopleTypeSQL).' ORDER BY a.last_name,a.first_name ASC ';
	//church_admin_debug( $sql);
	
	$alphabet = range('A','Z');
	$addressList=$adminAddressList=array();
	//church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	if ( empty( $results) )
	{
		
		return FALSE;
	}
	$w3w=get_option('church_admin_what_three_words');
	$show_address = 0;
	$show_landline = 0;
	foreach( $results AS $row)
	{
		church_admin_debug($row);
		$privacy = maybe_unserialize($row->privacy);
		if(!empty($privacy['show-address'])){
			$show_address = 1;
		}
		if(!empty($privacy['show-landline'])){
			$show_landline = 1;
		}
		$others=array();
		$othersInHousehold=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$row->household_id.'" AND people_id!="'.(int)$row->people_id.'" AND show_me=1 ORDER BY people_order ASC');
		if(!empty( $othersInHousehold) )
		{
			foreach( $othersInHousehold AS $key=>$person)$others[]=church_admin_formatted_name( $person);
		}
		$person=$adminPerson='<li class="ui-li ui-li-divider ui-bar-d address-concertina letter'.$row->initial.'"><span class="ui-btn ui-btn-icon-right ui-icon-plus vcf" data-peopleid="'.(int)$row->people_id.'" ><h3 class="ui-li-heading">'.church_admin_formatted_name( $row).'</h3></span>';
		if(!empty( $row->attachment_id) && !empty($row->photo_permission)){
			church_admin_debug('image ' .$row->attachment_id);
			$image='<p>'.wp_get_attachment_image( $row->attachment_id,'thumbnail','',array('class'=>'person-image','loading'=>'lazy') ).'</p>';
			$adminPerson.=$image;
			$person.=$image;
		}
		if(!empty( $others) ){
			$others='<p>'.esc_html( __('Others in household:','church-admin' ) ).' '.implode(', ',$others);
			$adminPerson.=$others;
			$person.=$others;
		}

		


		if(!empty( $row->mobile) )
		{
			if(!empty($privacy['show-cell']))
			{
				$person.='<p><a href="'.esc_url('tel:'.church_admin_e164( $row->mobile) ).'">'.esc_html( $row->mobile).'</a></p>';
			}
			$adminPerson.='<p><a href="'.esc_url('tel:'.church_admin_e164( $row->mobile) ).'">'.esc_html( $row->mobile).'</a></p>';
		}
		if(!empty( $row->email) ){
			if(!empty($privacy['show-email']))
			{
				$person.='<p><a href="'.esc_url('mailto:'.$row->email).'">'.esc_html( $row->email).'</a></p>';
			}
			$adminPerson.='<p><a href="'.esc_url('mailto:'.$row->email).'">'.esc_html( $row->email).'</a></p>';
		}
		if(!empty( $row->phone) ){
			
			if(!empty($privacy['show-landline']))
			{
				$person.='<p ><a href="'.esc_url('tel:'.$row->phone).'">'.esc_html( $row->phone).'</a></p>';
			}
			$adminPerson.='<p ><a href="'.esc_url('tel:'.$row->phone).'">'.esc_html( $row->phone).'</a></p>';
		}
		if(!empty($row->address)){

			if(!empty($privacy['show-address']))
			{
				$person.='<p>'.esc_html( $row->address).'</p>';
				$adminPerson.='<p>'.esc_html( $row->address).'</p>';
			}else
			{
				$adminPerson.= '<p  style="margin:10px 0px"><strong>'.esc_html(__('Address & map buttons only show to admins','church-admin') ) .'</strong></p>';
				$adminPerson.='<p>'.esc_html( $row->address).'</p>';
			}
			
		}
		if(!empty( $row->lat) && !empty( $row->lng) ) 
		{
			if(!empty($privacy['show-address']))
			{
				
				$person.='<p style="margin:10px 0px"><a class="linkButton green" href="'.esc_url('https://www.google.com/maps/search/?api=1&query='.$row->lat.','.$row->lng.'&amp;t=m&amp;z=16').'">'.esc_html( __("Map",'church-admin' ) ).'</a>'."\t".'&nbsp;<a href="https://www.google.com/maps/dir/?api=1&destination='.urlencode( $row->address).'" class="linkButton button-map">'.esc_html( __('Directions','church-admin' ) ).'</a></p>'."\r\n\t";
				if(!empty( $w3w)&&$w3w=='on')$person.=church_admin_what_three_words( $row,$wpdb->prefix.'church_admin_household');
			}
			$adminPerson.='<p style="margin:10px 0px" ><a class="linkButton green" href="'.esc_url('https://www.google.com/maps/search/?api=1&query='.$row->lat.','.$row->lng.'&amp;t=m&amp;z=16').'">'.esc_html( __("Map",'church-admin' ) ).'</a>'."\t".'&nbsp;<a href="https://www.google.com/maps/dir/?api=1&destination='.urlencode( $row->address).'" class="linkButton button-map">'.esc_html( __('Directions','church-admin' ) ).'</a></p>'."\r\n\t";
			if(!empty( $w3w)&&$w3w=='on')$adminPerson.=church_admin_what_three_words( $row,$wpdb->prefix.'church_admin_household');
		}
		
		
		$adminPerson.='<div class="admin-tasks-toggle ui-btn ui-btn-icon-right ui-icon-carat-d" data-id="person-'.(int)$row->people_id.'" >'.esc_html( __('Administrator tasks','church-admin' ) ).'</div><div class="admin-tasks" id="person-'.(int)$row->people_id.'">';
		//Password
		if(!empty($row->user_id)){

			$adminPerson.='<p><strong>'.esc_html( __('User account','church-admin' ) ).'</strong> '.church_admin_user_check( $row,TRUE).'</p>';

			$adminPerson.='<div class="church-admin-form-group"><label>'.esc_html( __('Change password','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" id="new-password"></div>';
			$adminPerson.='<p><button class="action button" data-tab="change-password" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';


		}
		elseif(!empty($row->email)){

			$adminPerson.='<p><button class="action button" data-tab="create-user" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Create user account','church-admin' ) ).'</button></p>';
		}
		//Member Type
		$adminPerson.='<div class="church-admin-form-group"><label>'.esc_html( __('Change member level','church-admin' ) ).'</label>';
		
		$adminPerson.='<select class="church-admin-form-control member_types" id="member-type-id'.(int)$row->people_id.'">';
		foreach( $member_types AS $id=>$type)
		{
			$adminPerson.='<option value="'.(int)$id.'" '.selected( $id,$row->member_type_id,FALSE).'>'.esc_html( $type).'</option>';
		}
		$adminPerson.='</select></div><p><button class="action button" data-tab="change-member-type" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';
		//Small Group
		//church_admin_debug('Small group bit');
		$adminPerson.='<div class="church-admin-form-group"><label>'.esc_html( __('Small Group','church-admin' ) ).'</label>';
		$personGroup=church_admin_get_people_meta( $row->people_id,'smallgroup');
		if ( empty( $personGroup) )$personGroup=array(null);
		//church_admin_debug('church_admin_get_people_meta');
		//church_admin_debug( $personGroup);
		//church_admin_debug( $groups);
		$adminPerson.='<select class="church-admin-form-control smallgroup" id="id'.(int)$row->people_id.'">';
		foreach( $groups AS $id=>$group)
		{
			$adminPerson.='<option value="'.(int)$id.'" '.selected( $id,$personGroup[0],FALSE).'>'.esc_html( $group).'</option>';
		}
		$adminPerson.='</select></div><p><button class="action button" data-tab="change-group" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';



		$adminPerson.='<p><button class="button green action" data-tab="people_edit" data-next="address" data-householdid="'.(int)$row->household_id.'" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Edit person','church-admin' ) ).'</button></p>';
		$adminPerson.='<p><button class="button green action" data-tab="address_edit" data-next="address" data-householdid="'.(int)$row->household_id.'" data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Edit address','church-admin' ) ).'</button></p>';
		
		$adminPerson.='<p><button class="button red action" data-next="address" data-tab="people_delete" data-householdid="'.(int)$row->household_id.'"  data-peopleid="'.(int)$row->people_id.'">'.esc_html( __('Delete person','church-admin' ) ).'</button></p>';
		$adminPerson.='</div>';
		$adminPerson.='</li>';
		
		$person.='</li>';
		$addressList[$row->initial][]=$person;
		$adminAddressList[$row->initial][]=$adminPerson;
	}
	
	//church_admin_debug( $addressList);
	

	$adminHtml=$html='<ul class="ui-listview" data-role="listview" data-theme="d" data-divider-theme="d" >';
	
    foreach( $alphabet AS $key=>$letter)
    {
		if ( empty( $addressList[$letter] ) )
		{
			$html.='<li class="ui-li ui-li-divider ui-bar-d address-letter"><span class="ui-btn">'.$letter.'</span></li>';
			$adminHtml.='<li class="ui-li ui-li-divider ui-bar-d address-letter"><span class="ui-btn">'.$letter.'</span></li>';
		}
		else 
		{
			$html.='<li class="ui-li ui-li-divider ui-bar-d"  ><span class="ui-btn ui-btn-icon-right ui-icon-carat-r address-letter"  data-tab="address-letter" data-letter="'.$letter.'">'.$letter.'</span></li></li>';
			$adminHtml.='<li class="ui-li ui-li-divider ui-bar-d"><span class="ui-btn ui-btn-icon-right ui-icon-carat-r address-letter"  data-tab="address-letter" data-letter="'.$letter.'">'.$letter.'</span></li></li>';
			$html.=implode("\r\n",$addressList[$letter] );
			$adminHtml.=implode("\r\n",$adminAddressList[$letter] );

		}
		
    }
    $html.='</ul>';
	$adminHtml.='</ul>';
	
	update_option('church_admin_app_address_cache',$html,'no');
	update_option('church_admin_app_admin_address_cache',$adminHtml,'no');
	
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
	{
		$adminHtml.='<p><button class="action button red">'.esc_html( __('Refresh address list cached','church-admin' ) ).'</button></p>';
		return $adminHtml;
	}
	elseif(!empty( $loginStatus) )
	{
		return $html;
	}


}


/***************************
 * Address Edit
 **************************/
function ca_app_new_address_edit( $household_id,$loginStatus)
{
	//church_admin_debug('******** ca_app_new_address_edit ********');
	//church_admin_debug( $_REQUEST);
	global $wpdb;
	$content='Edit Address';
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'token'=>$token,'content'=>$content,'view'=>'html','page_title'=>esc_html( __('Edit Address','church-admin') ));
	if( $household_id!=$loginStatus->household_id && !church_admin_level_check('Directory',$loginStatus->user_id) )
	{
			$output['error']=__("You don't have permissions to edit that address",'church-admin');
			return $output;
	}
	
	if(!empty( $_REQUEST['address'] )||!empty( $_REQUEST['phone'] ) )
	{
		//update
		$address=esc_sql(sanitize_text_field( stripslashes ($_REQUEST['address'] ) ) );
		$phone=esc_sql(sanitize_text_field( stripslashes ($_REQUEST['phone'] ) ) );
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET address="'.$address.'",phone="'.$phone.'", last_updated=NOW(), updated_by="'.(int)$loginStatus->people_id.'" WHERE household_id="'.(int)$household_id.'"');
		$message=__('Address edit saved','church-admin');
		church_admin_new_app_build_address_list( $loginStatus);
		update_option('church_admin_modified_app_content',time() );
		if(!empty($household_id) && $household_id==$loginStatus->household_id){
			$output = ca_app_new_account( $loginStatus );
		}
		else
		{
			$output = ca_app_new_address_list( $loginStatus );
		}
		$output['message']=$message;
		
	}
	else
	{
		
		$addressData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"');
		$content.=print_r( $addressData,TRUE);
		$address=(!empty( $addressData->address) )?esc_html( $addressData->address):'';
		$phone=(!empty( $addressData->phone) )?esc_html( $addressData->phone):'';
		$content='<div class="church-admin-form-group"><label>'.esc_html( __('Address','church-admin' ) ).'</label><textarea class="church-admin-form-control" style="height:75px;" id="address" >'.esc_textarea( $address ).'</textarea></div>';
		$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Home phone','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" id="phone" value="'.esc_html( $phone ).'" /></div>';
		$content.='<p><button class="button action red" data-tab="address_edit" data-householdid="'.(int)$household_id.'">'.esc_html( __('Save Address') ).'</button></p>';
		$output['content']=$content;
	}
	delete_option('church_admin_app_address_cache');
    delete_option('church_admin_app_admin_address_cache');
	
	return $output;
}


/**************************
 *  App menu
 **************************/
function ca_app_new_menu()
{
	$menu='<li id="home-tab-button" class="tab-button" data-tab="#home" data-cached=1>'.esc_html( __('Home','church-admin' ) ).'</li>';
	$menu.='<li id="logout-tab-button" class="tab-button" data-tab="#logout"  data-tap-toggle="false">'.esc_html( __('Logout','church-admin' ) ).'</li>';
    return $menu;  
}
/***************************
 *  App page content
 **************************/
function ca_app_new_app_content( $what,$loginStatus)
{
	church_admin_debug('**** ca_app_new_app_content ****');
	global $wpdb;
	$output = array( );
	if(!empty($loginStatus->token)){$output['token']= esc_html( $loginStatus->token );}
	$output['page_title']=ucwords( $what);
	$output['view']='html';

	$licence_level= get_option('church_admin_app_new_licence');
	if(!empty($licence_level) && $licence_level!='premium')
    {
		
		$content='<p>Unfortunately your church is not currently subscribed to the church app, we\'d love to have you back, so please do resubscribe the church at <a href="https://www.churchadminplugin.com/app">https://www.churchadminplugin.com/app</a>, or email <a href="mailto:support@churchadminplugin.com">support@churchadminplugin.com</a> for help</p>';
		$content.='<p>Developer debug information</p>';
		$content.=church_admin_url_check();
	}
	else
	{
		
		$defaultContentIDs=get_option('church_admin_app_defaults');
		if(empty($defaultContentIDs)){

			church_admin_fix_app_default_content();
			$defaultContentIDs=get_option('church_admin_app_defaults');
		}
		$page=$wpdb->get_row('SELECT * FROM '.$wpdb->posts.' WHERE ID="'.(int)$defaultContentIDs[$what].'"');
		if(empty($page)){
			$output = array( 'token'=>esc_html( $token ),'message'=>__("Page not found",'church-admin') );
			return $output;
		}
        //$content=do_blocks(do_shortcode( $page->post_content) );
		if( has_blocks( $page->post_content ) )
		{
			church_admin_debug('processing blocks');
			remove_filter( 'the_content', 'wpautop' );
			$content=apply_filters("the_content", do_blocks( $page->post_content ));
		}
		else
		{
			church_admin_debug('do shortcode');
			$content = do_shortcode($page->post_content);
		}
		switch( $what)
		{
			case 'giving':
				church_admin_app_log_visit( $loginStatus, __('Giving','church-admin') );
				$paypal=get_option('church_admin_payment_gateway');
				$paypalEmail=$paypal['paypal_email'];
				$funds=get_option('church_admin_giving_funds');
				if( $paypal['gift_aid'] )  {$giftAid=1;}else{$giftAid=0;}
				/**********************
				*   Add paypal giving form
				***********************/
				if(!empty( $paypal)&&!empty( $paypal['show_in_app'] ) )
				{
					$content.=ca_app_giving();
				}
			break;
			case 'smallgroup':
				church_admin_app_log_visit( $loginStatus, __('Small groups','church-admin') );
				$content.=ca_app_new_groups($loginStatus);
			break;	
			case 'home':
				church_admin_app_log_visit( $loginStatus, __('Home','church-admin') );
			break;
		}
        
	}
	/*
	//add in rota data if shortcode used
	$rotaData = ca_app_new_rota( $loginStatus);
	$rotaContent='';
	if(!empty($rotaData))
	{
		church_admin_debug('FOUND rota data');
		$rotaContent = $rotaData['content'];
		
	}
	$content = str_replace('{church_admin_app_rota}',$rotaContent,$content);
	$output['content']=$content;
	*/
	$output['content']=$content;
	$style=get_option('church_admin_app_style');
	if(!empty($style)){$output['style']=$style;}
	//$cacheOutput=ca_refresh_app_cache( $loginStatus);
	//church_admin_debug( $cacheOutput);
	//if(!empty( $cacheOutput) )$output=array_merge( $output,$cacheOutput);
	//church_admin_debug($output);
	return $output;
}

/***************************
 * Bible readings
 **************************/
function ca_app_new_bible_readings( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$streak='';
	//bible readings ID starts at 1 date('z') returns 0 for Jan 1

	$version=get_option('church_admin_bible_version');
	if(!empty( $_REQUEST['version'] ) )$version=sanitize_text_field(stripslashes($_REQUEST['version']) );
	
	$ID=date('z',strtotime('Today') )+1;
	if(!empty( $_REQUEST['date'] ) )
	{
		$ID=date('z',strtotime( sanitize_text_field(stripslashes($_REQUEST['date']) ) ) );

	}
	/***************************************************************************
	 * Older versions of app use  date variable, new version uses post ID
	 ***************************************************************************/
	$headphonesSVG='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6 23v-11c-4.036 0-6 2.715-6 5.5 0 2.807 1.995 5.5 6 5.5zm18-5.5c0-2.785-1.964-5.5-6-5.5v11c4.005 0 6-2.693 6-5.5zm-12-13.522c-3.879-.008-6.861 2.349-7.743 6.195-.751.145-1.479.385-2.161.716.629-5.501 4.319-9.889 9.904-9.889 5.589 0 9.29 4.389 9.916 9.896-.684-.334-1.415-.575-2.169-.721-.881-3.85-3.867-6.205-7.747-6.197z" /></svg>';
	//check to see if there is a post in bible-readings for the date

	
	
	//v1.1.0 of the app sends $_REQUEST['date'] to get date, still need to add 1 though!
	//if(!empty( $_REQUEST['date'] ) ) $ID=date('z' , strtotime( $_REQUEST['date'] ) )+1;
	//android sends the date in a way strtotime cannot formatting
	if(!empty( $_REQUEST['date'] ) )
	{
		//church_admin_debug("GET date from variable");
		//$d=\DateTime::createFromFormat('Y-m-d',$_REQUEST['date'] );
		//android is a pain! need to only use first15 charas of GET['date']
		$date=substr( sanitize_text_field(stripslashes($_REQUEST['date']) ),0,15);
		//church_admin_debug("stripped date $date");
		$d = new dateTime( $date);
		//church_admin_debug("DateTime object");
		//church_admin_debug(print_r( $d,TRUE) );
		$date=$d->format('Y-m-d');
		//church_admin_debug("SQL formatted $date");
		$ID=$d->format('z')+1;
	}
	else
	{
		$date=date('Y-m-d');
	}
	if( $date=='1970-01-01')  {$date=date('Y-m-d');}//handle Android notification handler bug
	if(defined('CA_DEBUG') )//church_admin_debug('Date looking for : '.$date);
	
	
	

	//church_admin_debug( $sql);
	$output='<p class="ui-li-desc" id="bible-reading-date"><button class="bible-date-picker" data-date="'.esc_html( $date ).'">'.esc_html( __('Change date','church-admin' ) ).'</button></p>';

	//use date if no ID passed
	if(!empty( $_REQUEST['ID'] ) )
	{
		$ID=sanitize_text_field(stripslashes($_REQUEST['ID']));
		$sql='SELECT * FROM '.$wpdb->posts.' WHERE post_type="bible-readings" AND ID="'.(int)$ID.'"';
	}
	else
	{
		$sql = 'SELECT * FROM '.$wpdb->posts.' WHERE post_type="bible-readings" AND DATE_FORMAT(post_date, "%Y-%m-%d")="'.esc_sql( $date ).'" AND (post_status="publish" OR post_status="future") LIMIT 1';
	}


	$bible_readings = $wpdb->get_results( $sql );

	if(!empty( $bible_readings) )
	{//use the Bible Reading post type
		$output='<p class="ui-li-desc" id="bible-reading-date"><button class="bible-date-picker" data-date="'.esc_html( $date ).'">'.esc_html( __('Change date','church-admin' ) ).'</button></p>';

		$output.='<!-- bible-readings post_type-->';
		foreach( $bible_readings AS $bible_reading)
		{
			
			
			
			$yesterday=wp_date('Y-m-d',strtotime('yesterday'));
			$today=wp_date('Y-m-d');
			if(!empty($loginStatus->people_id)){
				$yesterday_user_streak=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$loginStatus->people_id.'" AND meta_type="bible-reading-streak"');
			}
			church_admin_debug('*** Yesterday user streak ***');
			//church_admin_debug($wpdb->last_query);
			//church_admin_debug($yesterday_user_streak);
			church_admin_debug('*** ***');
			//using streak freeze?
			//get date last streak saved
			if(!empty($loginStatus->people_id)){
				$last_unbroken_date=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID>0 AND people_id="'.(int)$loginStatus->people_id.'" AND meta_type="previous-bible-reading-streak"');
			}
			church_admin_debug('*** previous bible reading streak ***');
			//church_admin_debug($wpdb->last_query);
			//church_admin_debug($last_unbroken_date);
			church_admin_debug('*** ***');
			$today=wp_date('Y-m-d');
			$reading_date=$wpdb->get_var('SELECT DATE_FORMAT(post_date, "%Y-%m-%d")  FROM '.$wpdb->posts.' WHERE ID="'.(int)$bible_reading->ID.'"');
			church_admin_debug('**** Reading date ****');
			//church_admin_debug($wpdb->last_query);
			//church_admin_debug($reading_date);
			if( !empty($yesterday_user_streak) && !empty($last_unbroken_date) && $today!=$reading_date &&!empty($loginStatus))
			{
				church_admin_debug('*** Checking for streak freeze ***');
				//Bible reading date is not today, so check if it is the last broken streak date
				//add one day to last streak saved, giving poss date of broken streak
				$d=strtotime($last_unbroken_date->meta_date.' 10:00:00');
				$d+=24*60*60;
				$broken_date=date('Y-m-d',$d);
				church_admin_debug('Broken date '.$broken_date);
				$check=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->postmeta.' WHERE meta_key="user-read-'.(int)$loginStatus->user_id.'" AND post_id="'.esc_sql($bible_reading->ID).'"');
				church_admin_debug('**** Read? ****');
				//church_admin_debug($wpdb->last_query);
				//church_admin_debug($check);
				church_admin_debug('*** ***');


				if(empty($check) && $reading_date==$broken_date &&!empty($loginStatus))
				{
					church_admin_debug('**** Date chosen is currently not in streak ****');

					//using freeze
					$previous_streak=$last_unbroken_date->ID;
					//update $yesterday_user_streak object
					$yesterday_user_streak->ID=$previous_streak+$yesterday_user_streak->ID;
					church_admin_debug('Updated obj for yesterday_user_streak');
					//church_admin_debug($yesterday_user_streak);
					church_admin_debug('*** ***');
					update_post_meta( $bible_reading->ID,'streak-freeze-'.(int)$loginStatus->user_id,date('Y-m-d'));
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID="'.(int)$yesterday_user_streak->ID.'" , meta_date="'.esc_sql(wp_date('Y-m-d')).'" WHERE meta_type="bible-reading-streak"');
					//church_admin_debug($wpdb->last_query);
					$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID="'.(int)$yesterday_user_streak->ID.'" , meta_date="'.esc_sql(wp_date('Y-m-d')).'" WHERE meta_type="previous-bible-reading-streak" AND people_id="'.(int)$loginStatus->people_id.'"');
					//church_admin_debug($wpdb->last_query);
					church_admin_debug('*** ***');
				}
			}

			if(!empty($loginStatus->user_id)){update_post_meta( $bible_reading->ID,'user-read-'.(int)$loginStatus->user_id,date('Y-m-d'));}
			//church_admin_debug($wpdb->last_query);

		
			if ( empty( $yesterday_user_streak ) && !empty( $loginStatus->people_id))
			{
				//insert into table
				$wpdb->query('INSERT INTO '. $wpdb->prefix.'church_admin_people_meta' .' (people_id,ID,meta_type,meta_date) VALUES("'.(int)$loginStatus->people_id.'",1,"bible-reading-streak","'.esc_sql(wp_date('Y-m-d')).'"),("'.(int)$loginStatus->people_id.'",0,"previous-bible-reading-streak","'.esc_sql(wp_date('Y-m-d')).'")');
			}
			elseif (!empty($yesterday_user_streak->meta_date) &&  $yesterday_user_streak->meta_date == $today ){
				//do nothing, already read today
			}
			elseif (!empty($yesterday_user_streak->meta_date) &&  $yesterday_user_streak->meta_date == $yesterday )
			{
				//add one and update
				$streak = $yesterday_user_streak->ID + 1;
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID="'.(int)$streak.'" , meta_date="'.esc_sql(wp_date('Y-m-d')).'" WHERE meta_id="'.(int)$yesterday_user_streak->meta_id.'"');
				$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID="'.(int)$streak.'" , meta_date="'.esc_sql(wp_date('Y-m-d')).'" WHERE meta_type="previous-bible-reading-streak" AND people_id="'.(int)$loginStatus->people_id.'"');
			}
			else {
				//not read yesterday or today, so update table with streak of 1
				if(!empty($yesterday_user_streak->meta_id))$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID="1" , meta_date="'.esc_sql(wp_date('Y-m-d')).'" WHERE meta_id="'.(int)$yesterday_user_streak->meta_id.'"');
			}
			//church_admin_debug($wpdb->last_query);
			



			$title=esc_html( $bible_reading->post_title);
			$passage=get_post_meta( $bible_reading->ID ,'bible-passage',TRUE);
			if(!empty( $passage) )
            {
                $output.='<p class="ca-bible-reading"><a href="https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.urlencode( $version).'&interface=print"  >'.esc_html( $passage).'</a></p>';

                $bibleCV=church_admin_bible_audio_link( $passage,$version);
                if(!empty( $bibleCV['url'] ) )$output.='<p class="ca-bible-audio-link"><a href="'.$bibleCV['url'].'">'.$headphonesSVG.' '.$bibleCV['linkText'].'</a></p>';
            }
			$output.='<div class="ca-bible-commentary">';
            $blocks = parse_blocks( $bible_reading->post_content);
                foreach ( $blocks as $block) {
					church_admin_debug('*** Handle Block ***');
					church_admin_debug( $block );
                    if ( $block['blockName'] == 'core/embed' && $block['attrs']['type']=='audio') {

                        $output.='<p><audio controls><source src="'.$block['attrs']['url'].'" type="audio/mpeg">'.$block['attrs']['url'].'</audio></p>';
                    }
					elseif ( $block['blockName'] == 'core/embed' && $block['attrs']['type']=='video') {
						$video=church_admin_generateVideoEmbedUrl( $block['attrs']['url']);
						$videoUrl=$video['embed'];
                        
						$output.='<div style="margin-top:20px;"><div style="position:relative;padding-top:56.25%;"><iframe class="ca-video" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.$videoUrl.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe></div></div>';
                    }
					elseif( $block['blockName']=='core/buttons'){
						$button = $block['innerBlocks'][0]['innerHTML'];
						$output .='<p>'.str_replace('class="wp-block-button__link wp-element-button"', 'class="button red"', $button).'</p>';
					}
                    elseif( $block['blockName']=='core/shortcode')
					{
						//church_admin_debug('Shortcode block');
						//church_admin_debug(do_shortcode( $block['innerHTML'] ) );
						$output.= do_shortcode( $block['innerHTML'] );
					}
					else $output.= render_block( $block);
                }

            $output.='</div>';
			$output.='<p class="ca-bible-author-meta">'.get_the_author_meta( 'display_name',$bible_reading->post_author).'</p>';
			$args=array('post_id'=>(int)$bible_reading->ID,'orderby'=>'comment_date','order'=>'ASC');
			$comments=get_comments( $args);
			$output.='<h3>'.esc_html( __("Comments",'church-admin' ) ).'</h3>';
			$output.='<ul id="list" class="ui-listview">';
			if(!empty( $comments) )
			{
				
				foreach( $comments AS $key=>$comment)
				{
					$output.='<li class="ui-li-static">';
					$output.=$comment->comment_content.'<br>';
					if(!empty( $comment->comment_author) )
					{
						$output.='<em>'.esc_html( sprintf(__('%1$s on %2$s','church-admin' ) , $comment->comment_author,mysql2date(get_option('date_format').' '.get_option('time_format'),$comment->comment_date) )).'</em>';
					}
					
					$output.='</li>';
				}
				
			}else{$output.='<li class="ui-li-static">'.esc_html( __('No comments yet','church-admin' ) ).'</li>';}
			if(comments_open( $bible_reading->ID) )
			{
				$output.='<li class="ui-li-static">';
				$output.='<h4>'.esc_html( __('Leave your comment','church-admin' ) ).'</h4><textarea id="my-comment"></textarea><br><button class="action button green" data-tab="comment" data-id="'.(int)$bible_reading->ID .'">'.esc_html( __('Reply','church-admin' ) ).'</button></li>';
			}
			//do the streak!
			$steak=null;
			church_admin_debug('line 1310');
			if(!empty($loginStatus->people_id)){
				$show_streak = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$loginStatus->people_id.'" AND meta_type="show-bible-readings-streak"');
			}
			if ( !empty( $show_streak->ID ) ) {$streak = church_admin_bible_reading_streak( $loginStatus,$date);}
			
		}
		
		
	}
	else
	{//use the old style bible reading plan
		$output='<p class="ui-li-desc" id="bible-reading-date"><button class="bible-date-picker" data-date="'.esc_html( $date ).'">'.esc_html( __('Change date','church-admin' ) ).'</button></p>';

		$output.='<!-- uploaded CSV method -->';
		$title=__('Bible readings','church-admin');
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_brplan WHERE ID="'.(int)$ID.'"';
		$data=$wpdb->get_row( $sql);
		if(!empty( $_REQUEST['version'] ) )$version=sanitize_text_field(stripslashes($_REQUEST['version'] ) );
		if ( empty( $version) )$version=get_option('church_admin_bible_version');
		if ( empty( $version) )$version="ESV";
		$readings=maybe_unserialize( $data->readings);
		$date = (!empty( $_REQUEST['date'] )&& church_admin_checkdate( sanitize_text_field(stripslashes($_REQUEST['date'] )) ) )?sanitize_text_field(stripslashes($_REQUEST['date']) ):date('Y-m-d');
		$out=array('<h2>'.mysql2date(get_option('date_format'),$date).'</h2>');
		if(!empty( $readings) )
		{
			
			
			foreach( $readings AS $key=>$passage)
			{

                $bibleCV=church_admin_bible_audio_link( $passage,$version);
                $output.='<p class="ca-bible-reading"><a href="https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.urlencode( $version).'&interface=print" >'.esc_html( $passage).'</a></p>';


                if(!empty( $bibleCV['url'] ) )$output.='<p><a href="'.$bibleCV['url'].'">'.$headphonesSVG.' '.$bibleCV['linkText'].'</a></p>';

               
			}
			
		}else $output.='<p>'.esc_html( __('No passages','church-admin')) .'</p>';

	}
	//church_admin_debug($output);
	return array('page_title'=>$title,'content'=>$output,'view'=>'html','streak'=>$streak);
	

}

function church_admin_bible_reading_streak( $loginStatus)
{
	//return;
	church_admin_debug('**** BIBLE READING STREAK FUNCTION ****');
	global $wpdb;
	if ( empty( $loginStatus) )return;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$personStreak=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="bible-reading-streak" AND people_id="'.(int)$loginStatus->people_id.'"');
	$dateIDs=$wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_status="publish" AND post_type="bible-readings" AND post_date>= DATE(NOW() - INTERVAL 7 DAY) ORDER BY post_date');
	
	$streak=array();
	$output='<div id="bible-reading-days">';
	for($x=6;$x>=0;$x--){
        $date=date('D',strtotime("-$x days"));
        $mysqldate = date('Y-m-d',strtotime("-$x days"));
       	$ID=$wpdb->get_var('SELECT ID FROM '.$wpdb->posts.' WHERE post_status="publish" AND post_type="bible-readings" AND DATE(post_date)="'.esc_sql($mysqldate).'" LIMIT 1');
		//church_admin_debug($wpdb->last_query);
		$read=FALSE;
		if(!empty($ID))
		{
			$read=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'postmeta WHERE post_id="'.(int)$ID.'" AND meta_key="user-read-'.(int)$loginStatus->user_id.'"');
			//church_admin_debug($wpdb->last_query);
		}
		$freeze=false;
		$freeze= $wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'postmeta WHERE post_id="'.(int)$ID.'" AND meta_key="streak-freeze-'.(int)$loginStatus->user_id.'"');
		if(!empty($freeze))
		{
			$output.='<div class="streakDay" data-date="'.esc_html($mysqldate).'" data-userid="'.(int)$loginStatus->user_id.'"><div class="sDay">'.$date.'</div><div class="stDay bluestreak" style="color:#1CB0F6"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM6c2VyaWY9Imh0dHA6Ly93d3cuc2VyaWYuY29tLyIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoyOyI+CiAgICA8cGF0aCBkPSJNMjU2LDUxMkMzOTcuNCw1MTIgNTEyLDM5Ny40IDUxMiwyNTZDNTEyLDExNC42IDM5Ny40LDAgMjU2LDBDMTE0LjYsMCAwLDExNC42IDAsMjU2QzAsMzk3LjQgMTE0LjYsNTEyIDI1Niw1MTJaTTM2OSwyMDlMMjQxLDMzN0MyMzEuNiwzNDYuNCAyMTYuNCwzNDYuNCAyMDcuMSwzMzdMMTQzLjEsMjczQzEzMy43LDI2My42IDEzMy43LDI0OC40IDE0My4xLDIzOS4xQzE1Mi41LDIyOS44IDE2Ny43LDIyOS43IDE3NywyMzkuMUwyMjQsMjg2LjFMMzM1LDE3NUMzNDQuNCwxNjUuNiAzNTkuNiwxNjUuNiAzNjguOSwxNzVDMzc4LjIsMTg0LjQgMzc4LjMsMTk5LjYgMzY4LjksMjA4LjlMMzY5LDIwOVoiIHN0eWxlPSJmaWxsOnJnYigwLDEzMCwyNDEpO2ZpbGwtcnVsZTpub256ZXJvOyIvPgo8L3N2Zz4K" /></div></div>';	
		}
		elseif(!empty( $read) )
		{
			
			$output.='<div class="streakDay" data-date="'.esc_html($mysqldate).'" data-userid="'.(int)$loginStatus->user_id.'"><div class="sDay">'.$date.'</div><div class="stDay orangestreak"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM6c2VyaWY9Imh0dHA6Ly93d3cuc2VyaWYuY29tLyIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoyOyI+CiAgICA8cGF0aCBkPSJNMjU2LDUxMkMzOTcuNCw1MTIgNTEyLDM5Ny40IDUxMiwyNTZDNTEyLDExNC42IDM5Ny40LDAgMjU2LDBDMTE0LjYsMCAwLDExNC42IDAsMjU2QzAsMzk3LjQgMTE0LjYsNTEyIDI1Niw1MTJaTTM2OSwyMDlMMjQxLDMzN0MyMzEuNiwzNDYuNCAyMTYuNCwzNDYuNCAyMDcuMSwzMzdMMTQzLjEsMjczQzEzMy43LDI2My42IDEzMy43LDI0OC40IDE0My4xLDIzOS4xQzE1Mi41LDIyOS44IDE2Ny43LDIyOS43IDE3NywyMzkuMUwyMjQsMjg2LjFMMzM1LDE3NUMzNDQuNCwxNjUuNiAzNTkuNiwxNjUuNiAzNjguOSwxNzVDMzc4LjIsMTg0LjQgMzc4LjMsMTk5LjYgMzY4LjksMjA4LjlMMzY5LDIwOVoiIHN0eWxlPSJmaWxsOnJnYigyNTUsMTUwLDQ3KTtmaWxsLXJ1bGU6bm9uemVybzsiLz4KPC9zdmc+Cg==" /></div></div>';
		}
		else
		{
			$output.='<div class="streakDay"><div class="sDay" data-date="'.esc_html($mysqldate).'"  data-userid="'.(int)$loginStatus->user_id.'">'.$date.'</div><div class="stDay greyStreak"><img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDUxMiA1MTIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM6c2VyaWY9Imh0dHA6Ly93d3cuc2VyaWYuY29tLyIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoyOyI+CiAgICA8cGF0aCBkPSJNMjU2LDUxMkMzOTcuNCw1MTIgNTEyLDM5Ny40IDUxMiwyNTZDNTEyLDExNC42IDM5Ny40LDAgMjU2LDBDMTE0LjYsMCAwLDExNC42IDAsMjU2QzAsMzk3LjQgMTE0LjYsNTEyIDI1Niw1MTJaTTM2OSwyMDlMMjQxLDMzN0MyMzEuNiwzNDYuNCAyMTYuNCwzNDYuNCAyMDcuMSwzMzdMMTQzLjEsMjczQzEzMy43LDI2My42IDEzMy43LDI0OC40IDE0My4xLDIzOS4xQzE1Mi41LDIyOS44IDE2Ny43LDIyOS43IDE3NywyMzkuMUwyMjQsMjg2LjFMMzM1LDE3NUMzNDQuNCwxNjUuNiAzNTkuNiwxNjUuNiAzNjguOSwxNzVDMzc4LjIsMTg0LjQgMzc4LjMsMTk5LjYgMzY4LjksMjA4LjlMMzY5LDIwOVoiIHN0eWxlPSJmaWxsOnJnYigxNTEsMTUwLDE1MSk7ZmlsbC1ydWxlOm5vbnplcm87Ii8+Cjwvc3ZnPgo=" /></div></div>';
		}
		
	}
	$output.='</div>';
	if(!empty($personStreak)){
		$output.='<div id="person-streak"><img width="32" height="32" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+Cjxzdmcgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDQ0OCA1MTIiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM6c2VyaWY9Imh0dHA6Ly93d3cuc2VyaWYuY29tLyIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoyOyI+CiAgICA8cGF0aCBkPSJNMTU5LjMsNS40QzE2Ny4xLC0xLjkgMTc5LjIsLTEuOCAxODcsNS41QzIxNC42LDMxLjQgMjQwLjUsNTkuMyAyNjQuNyw4OS41QzI3NS43LDc1LjEgMjg4LjIsNTkuNCAzMDEuNyw0Ni42QzMwOS42LDM5LjIgMzIxLjgsMzkuMiAzMjkuNyw0Ni43QzM2NC4zLDc5LjcgMzkzLjYsMTIzLjMgNDE0LjIsMTY0LjdDNDM0LjUsMjA1LjUgNDQ4LDI0Ny4yIDQ0OCwyNzYuNkM0NDgsNDA0LjIgMzQ4LjIsNTEyIDIyNCw1MTJDOTguNCw1MTIgMCw0MDQuMSAwLDI3Ni41QzAsMjM4LjEgMTcuOCwxOTEuMiA0NS40LDE0NC44QzczLjMsOTcuNyAxMTIuNyw0OC42IDE1OS4zLDUuNFpNMjI1LjcsNDE2QzI1MSw0MTYgMjczLjQsNDA5IDI5NC41LDM5NUMzMzYuNiwzNjUuNiAzNDcuOSwzMDYuOCAzMjIuNiwyNjAuNkMzMTkuOCwyNTUgMzE3LDI0OS40IDMxMi44LDI0My44TDI2Mi4yLDMwMi42QzI2Mi4yLDMwMi42IDE4MC44LDE5OSAxNzUuMSwxOTJDMTMzLjEsMjQzLjggMTEyLDI3My4yIDExMiwzMDYuOEMxMTIsMzc1LjQgMTYyLjYsNDE2IDIyNS43LDQxNloiIHN0eWxlPSJmaWxsOnJnYigyNTUsMCw0Mik7ZmlsbC1ydWxlOm5vbnplcm87Ii8+Cjwvc3ZnPgo=" />&nbsp; '.sprintf( 
			_n(
				'%d day Bible reading streak',
				'%d days Bible reading streak',
				(int)$personStreak,'church-admin'
			), 
			number_format_i18n($personStreak)
		).'</div>';
	}
	return $output;


}
/***************************
 * Calendar
 ***************************/
function ca_app_new_calendarv2($loginStatus){
	church_admin_debug("******** APP CALENDAR ************");

	//initialise
	global $wpdb;
	$start_date = !empty($_REQUEST[start_date]) && church_admin_checkdate($_REQUEST[start_date]) ? church_admin_sanitize($_REQUEST[start_date]): wp_date('Y-m-d');
	$display_start_date = mysql2date(get_option('date_format'),$start_date);
	$dates=array();
	$output = array(	'page_title'=>esc_html( __('Calendar','church-admin' ) ),
						'view'=>'list',
						'button'=>'<button class="button blue" class="date-picker">'.esc_html('Date picker').'</button>'
					);

	//grab dates
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE general_calendar=1 AND start_date BETWEEN "'.esc_sql($start_date).'"  AND DATE_ADD("'.esc_sql($start_date).'" , INTERVAL 8 DAY) ORDER BY start_date,start_time ASC';
	$results=$wpdb->get_results($sql);

	if(empty($results)){
		$output['content'] = sprintf(__('No events for the week starting %1$s','church-admin'),$display_start_date);
		return $output;
	}

	//build array of dates 




}




function ca_app_new_calendar( $loginStatus)
{
	//church_admin_debug("******** APP CALENDAR ************");
	$user_id=!empty( $loginStatus->user_id)?$loginStatus->user_id:NULL;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	global $wpdb;
	$append=FALSE;
	if(!empty( $_REQUEST['date'] ) )
	{
		$d=new DateTime( sanitize_text_field(stripslashes($_REQUEST['date'] )) );
		$date= $d->format('Y-m-d') ;
		$nextDate=$d->modify('+8 day');
	}
	if ( empty( $date) ){
		$d=new DateTime();
		$date= $d->format('Y-m-d') ;
		$nextDate=$d->modify('+8 day');
	}
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE general_calendar=1 AND start_date BETWEEN "'.esc_sql($date).'"  AND DATE_ADD("'.esc_sql($date).'" , INTERVAL 8 DAY) ORDER By start_date,start_time ASC';
	//church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	$output = array();
	if(!empty($loginStatus->token)){$output['token']=esc_html( $loginStatus->token );}
	$button='';
	$dates='';//'<ul id="list" class="ui-listview">';
	
	if(!empty( $loginStatus) && church_admin_level_check("Calendar",$loginStatus->user_id) )
	{
		$dates.='<li class="ui-li-static"><button class="button green action" data-tab="calendar-edit" data-type="single" data-id="0">'.esc_html( __('Add event','church-admin' ) ).'</button></li>';
	}
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			//church_admin_debug( $row);
			$thisTS=strtotime( $row->start_date);
			$lastWeek=date("Y-m-d",$thisTS-604800);
			$dates.= '<li  class="ui-li-static ui-body-inherit calItem"  data-date="'.esc_html( $row->start_date).'" data-prev-week="'.$lastWeek.'">';
		
			$dates.='<span class="ui-li-heading">'.esc_html( $row->title).'</span>';
			
			$dates.='<p class="ui-li-desc">';
			if(!empty($row->event_image)){
				$dates.= wp_get_attachment_image( $row->event_image, 'medium', false).'<br/>';
			}
			$dates.=' <strong>'.mysql2date(get_option('date_format'),$row->start_date).' '.mysql2date(get_option('time_format'),$row->start_time).'-'.mysql2date(get_option('time_format'),$row->end_time).'</strong><br>';

			$dataDetail =  $row->title.' '.mysql2date(get_option('date_format'),$row->start_date).' '.mysql2date(get_option('time_format'),$row->start_time).'-'.mysql2date(get_option('time_format'),$row->end_time);
			//church_admin_debug($dataDetail);
			church_admin_debug(esc_attr($dataDetail));
			if( $row->description) $dates.=sanitize_text_field( stripslashes ($row->description) ).'<br>';
			if( $row->location) $dates.=sanitize_text_field( stripslashes ($row->location) ).'<br>';
			$dates.='</p>';
			if( $row->link) $dates.='<p><a href="'.esc_url( $row->link).'" class="linkButton">'.esc_html( $row->link_title).'</a></p>'; 
			$dates.='<p><a  rel="nofollow" href="'.esc_url(site_url().'/?ca_download=ical&amp;date_id='.(int)$row->date_id).'"><span class="ui-button ui-icon-action"></span> '.esc_html( __("Download",'church-admin' ) ).'</a></p>';
			if(!empty($row->service_id)){
				
				 	$dates.='<p><button class="button  action" data-tab="rota" data-serviceid="'.(int)$row->service_id.'" data-rotadate="'.esc_attr($row->start_date).'">'.__('Service schedule','church-admin').'</button></p>';
			}
			if(!empty( $loginStatus) && church_admin_level_check('Calendar',$loginStatus->user_id) )
			{
				//church_admin_debug('Can edit calendar');

				//$dates.='<div class="admin-tasks-toggle ui-btn ui-btn-icon-right ui-icon-carat-d" data-id="event-'.(int)$row->date_id.'">'.esc_html( __('Administrator tasks','church-admin' ) ).'</div><div class="admin-tasks" id="event-'.(int)$row->date_id.'">';
				
				$dates.='<p><select class="select-action"><option>'.__('Choose admin task','church-admin').'</option>';
				
				
				if(!empty($row->service_id)){
					//add link to rota for this service
				   if(church_admin_level_check('Rota')){
						
						$dates.='<option data-tab="edit-rota" data-serviceid="'.(int)$row->service_id.'" data-rotaid="'.esc_attr($row->start_date).'">'.__('Edit service schedule','church-admin').'</option>';
				   }
				   
				  
				}

				if ( empty( $row->recurring) || $row->recurring=='s')
				{
					/*
					$dates.='<p><button class="button green action" data-tab="calendar-edit" data-type="single" data-eventid="'.(int)$row->event_id.'" data-dateid="'.(int)$row->date_id.'">'.esc_html( __('Edit event','church-admin')).'</button></p>';
					$dates.='<p><button class="button red action" data-tab="calendar-delete" data-type="single" data-eventid="'.(int)$row->event_id.'"  data-dateid="'.(int)$row->date_id.'">'.esc_html( __('Delete event','church-admin' ) ).'</button></p>';
					*/
					$dates.='<option data-tab="calendar-edit" data-type="single" data-eventid="'.(int)$row->event_id.'" data-dateid="'.(int)$row->date_id.'">'.esc_html( __('Edit event','church-admin')).'</option>';
					$dates.='<option data-detail="'.esc_attr($dataDetail).'"  data-tab="calendar-delete" data-type="single" data-eventid="'.(int)$row->event_id.'"  data-dateid="'.(int)$row->date_id.'">'.esc_html( __('Delete event','church-admin' ) ).'</option>';

				}
				else
				{
					/*
					$dates.='<p><button class="button green action" data-tab="calendar-edit" data-type="single" data-eventid="'.(int)$row->event_id.'"  data-dateid="'.(int)$row->date_id.'">'.esc_html( __('Edit this occurrence','church-admin' ) ).'</button></p>';
					$dates.='<p><button class="button green action" data-tab="calendar-edit" data-type="all" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'">'.esc_html( __('Edit all occurrences','church-admin' ) ).'</button></p>';
					$dates.='<p><button class="button green action" data-tab="calendar-edit" data-type="future" data-id="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'"  >'.esc_html( __('Edit this and future ','church-admin' ) ).'</button></p>';
					
					$dates.='<p><button class="button red action" data-tab="calendar-delete" data-type="single" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'">'.esc_html( __('Delete this occurrence','church-admin' ) ).'</button></p>';
					$dates.='<p><button class="button red action" data-tab="calendar-delete" data-type="all" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'">'.esc_html( __('Delete all occurrences','church-admin' ) ).'</button></p>';
					$dates.='<p><button class="button red action" data-tab="calendar-delete" data-type="future" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'">'.esc_html( __('Delete this & future','church-admin' ) ).'</button></p>';
					*/
					
					$dates.='<option data-tab="calendar-edit" data-type="single" data-eventid="'.(int)$row->event_id.'"  data-dateid="'.(int)$row->date_id.'">'.esc_html( __('Edit this occurrence','church-admin' ) ).'</option>';
					$dates.='<option data-tab="calendar-edit" data-type="all" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'">'.esc_html( __('Edit all occurrences','church-admin' ) ).'</option>';
					$dates.='<option data-tab="calendar-edit" data-type="future" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'"  >'.esc_html( __('Edit this and future ','church-admin' ) ).'</option>';
					
					$dates.='<option data-tab="calendar-delete"  data-detail="'.esc_attr($dataDetail).'"   data-type="single" data-detail="" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'"> '.esc_html( __('Delete this occurrence','church-admin' ) ).'</option>';
					$dates.='<option data-tab="calendar-delete"  data-detail="'.esc_attr($dataDetail).'"  data-type="all" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'">'.esc_html( __('Delete all occurrences','church-admin' ) ).'</option>';
					$dates.='<option data-tab="calendar-delete"  data-detail="'.esc_attr($dataDetail).'"  data-type="future" data-dateid="'.(int)$row->date_id.'" data-eventid="'.(int)$row->event_id.'"> '.esc_html( __('Delete this & future','church-admin' ) ).'</option>';
					$dates.='</select></p>';

				}
				//$dates.='</div>';
				$dates.='</select>';
			}
			$dates.='</li>';
			$last_date=$row->start_date;
		}
		$button='<button class="button tab-button" data-tab="calendar" data-date="'.esc_html( $last_date).'">'.esc_html( __('Load more','church-admin' ) ).'</button>';
	}else
	{
		$dates.='<li class="ui-li-static ui-body-inherit calItem">'.esc_html( __("No events to display yet",'church-admin' ) ).'</li>';
	}
	//$dates.='</ul>';
	$button='<button class="button action green" data-tab="calendar" data-date="'. esc_attr( $nextDate->format( 'Y-m-d' ) ).'">'.esc_html( __('Next 7 days','church-admin' ) ).'</button>';
	return array('page_title'=>esc_html( __('Calendar','church-admin' ) ),'content'=>$dates,'view'=>'list','button'=>$button);
}

function ca_app_new_calendar_save( $loginStatus)
{
	church_admin_debug("******** SAVE CALENDAR ***********");
	church_admin_debug('$_REQUEST:');
	church_admin_debug( $_REQUEST);
	global $wpdb;
	
	$image = !empty($_REQUEST['image'])?church_admin_sanitize($_REQUEST['image']):null;
	$attachment_id = null;
	if(!empty($image)){
		//church_admin_debug($image);
		$attachment_id = church_admin_save_base64_image( $image, 'person-'.$people_id );

	}
	else
	{
		$attachment_id = $wpdb->get_var('SELECT attachment_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id ="'.(int)$people_id.'"');
		church_admin_debug('No image uploaded so falling back to previous value if it exists '.$attachment_id);
	}
	$dateID=!empty( $_REQUEST['date_id'] )?(int)sanitize_text_field(stripslashes($_REQUEST['date_id'])):NULL;

	if(empty($attachment_id) && !empty($dateID))
	{
		$attachment_id = $wpdb->get_var('SELECT event_image FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$dateID.'" LIMIT 1');
	}
	$eventID=!empty( $_REQUEST['event_id'] )?(int)sanitize_text_field(stripslashes($_REQUEST['event_id'])):NULL;

		$title=!empty( $_REQUEST['title'] )?sanitize_text_field(sanitize_text_field( stripslashes ($_REQUEST['title'] ) ) ):null;
		$description=!empty( $_REQUEST['description'] )?sanitize_text_field(sanitize_textarea_field( stripslashes ($_REQUEST['description'] ) ) ):null;
		$location = !empty( $_REQUEST['location'] )?sanitize_text_field(sanitize_text_field( stripslashes ($_REQUEST['location'] ) ) ):null;
		//$date=!empty( $_REQUEST['date'] )&&church_admin_checkdate( $_REQUEST['date'] )?$_REQUEST['date']:null;
		if(!empty( $_REQUEST['date'] ) )
		{
			//$d= new DateTime( $_REQUEST['date'] );
			//$date=$d->format('Y-m-d');
			$date=substr( sanitize_text_field(stripslashes($_REQUEST['date'] ) ),0,15);
			//church_admin_debug("stripped date $date");
			$d = new dateTime( $date);
			//church_admin_debug("DateTime object");
			//church_admin_debug(print_r( $d,TRUE) );
			$date=$d->format('Y-m-d');
		}
		$start_time=!empty( $_REQUEST['start_time'] )?sanitize_text_field( stripslashes ($_REQUEST['start_time'])  ):'00:00';
		$end_time=!empty( $_REQUEST['end_time'] )?sanitize_text_field( stripslashes ($_REQUEST['end_time'] )):'23:59';
		$cat_id=!empty( $_REQUEST['cat_id'] )?(int)$_REQUEST['cat_id']:1;
		$nextEventID=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date')+1;
		$recurring=!empty( $_REQUEST['recurring'] )?sanitize_text_field( stripslashes ($_REQUEST['recurring'])  ):'s';
		$how_many=!empty( $_REQUEST['how_many'] )?(int)$_REQUEST['how_many']:1;
		
		$data=array('title'=>$title,'description'=>$description,'location'=>$location,'start_date'=>$date,'start_time'=>$start_time,'end_time'=>$end_time,'cat_id'=>$cat_id,'event_id'=>$eventID,'recurring'=>$recurring,'how_many'=>$how_many,'next_event_id'=>$nextEventID,'event_image'=>$attachment_id);
		//church_admin_debug($data);
		ca_app_new_save_cal_event($loginStatus, $data,$dateID,$eventID,sanitize_text_field(stripslashes($_REQUEST['type']) ),'calendar');
			
		$message=__('Calendar event saved','church-admin');
		$content='';
		$button='<button class="button action" data-tab="calendar" >'.esc_html( __('Back to calendar','church-admin' ) ).'</button>';

	
	update_option('church_admin_modified_app_content',time() );
	$output=ca_app_new_calendar( $loginStatus);
	$output['message']=$message;
	return $output;
}


function ca_app_new_save_cal_event($loginStatus, $data,$dateID,$eventID,$type,$eventType)
{
	
	church_admin_debug("******** ca_app_new_save_cal_event ***********");
	church_admin_debug("DATE ID: $dateID");
	church_admin_debug("EVENT ID: $eventID");
	global $wpdb;
	$nextEventID=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date')+1;
	//delete current events as required
	switch( $type)
	{
		case 'single':
			if(!empty( $dateID) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$dateID.'"');
			$event_id=$nextEventID;
		break;
		case 'all':
			if(!empty( $eventID) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$eventID.'"');
			$event_id=$nextEventID;
		break;
		case 'future':
			if(!empty( $dateID)&& !empty( $eventID) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$eventID.'" AND date_id="'.(int)$dateID.'"');
			$event_id=$nextEventID;
		break;
	}
	//church_admin_debug( $wpdb->last_query);
	//case for new event...
	if ( empty( $event_id) )$event_id=$nextEventID;

	
	$values=array();
	switch( $data['recurring'] )
	{
		
		case 's':
			$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.esc_sql( $data['start_date'] ).'","'.esc_sql( $data['start_time'] ).'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","s","1","1","'.esc_sql( $eventType).'","'.esc_sql($data['event_image']).'")';
		break;
		case '1':
			//daily
			for ( $x=0; $x<$data['how_many']; $x++)
			{
				$start_date=date('Y-m-d',strtotime("{$data['start_date']}+$x day") );
				$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.esc_sql( $start_date).'","'.esc_sql( $data['start_time'] ).'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","1","'.(int)$data['how_many'].'","1","'.esc_sql( $eventType).'","'.esc_sql($data['event_image']).'")';
			}
		break;
		case '7':
			//weekly
			for ( $x=0; $x<$data['how_many']; $x++)
			{
				$start_date=date('Y-m-d',strtotime("{$data['start_date']}+$x week") );
				$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.$start_date.'","'.$data['start_time'].'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","7","'.(int)$data['how_many'].'","1","'.esc_sql( $eventType).'","'.esc_sql($data['event_image']).'")';	
			}
		break;
		case '14':
			//fortnightly
			for ( $x=0; $x<$data['how_many']; $x++)
			{
				$start_date=date('Y-m-d',strtotime("{$data['start_date']} + $x fortnight") );
				$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.$start_date.'","'.$data['start_time'].'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","14","'.(int)$data['how_many'].'","1","'.esc_sql( $eventType).'","'.esc_sql($data['event_image']).'")';	
			}
		break;
		case'm':
			//monthly
			for ( $x=0; $x<$data['how_many']; $x++)
			{
				$start_date=date('Y-m-d',strtotime("{$data['start_date']}+$x month") );
				$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.$start_date.'","'.$data['start_time'].'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","m","'.(int)$data['how_many'].'","1","'.esc_sql( $eventType).'","'.esc_sql($data['event_image']).'")';	
			}

		break;
		case 'q':
			for ( $x=0; $x<$data['how_many']; $x++)
			{
				$y=$x*84;
				$start_date=date('Y-m-d',strtotime("{$data['start_date']}+$y day") );
				$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.$start_date.'","'.$data['start_time'].'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","m","'.(int)$data['how_many'].'","1","'.esc_sql( $eventType).'","'.esc_sql($data['event_image']).'")';	
			}

		break;
		case'a':
			//annually
			for ( $x=0; $x<$data['how_many']; $x++)
			{
				$start_date=date('Y-m-d',strtotime("{$data['start_date']}+$x year") );
				$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.$start_date.'","'.$data['start_time'].'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","a","'.(int)$data['how_many'].'","1","'.esc_sql( $eventType).'","'.esc_sql($data['event_image']).'")';	
			}

		break;
		default:
			//nth day
			$nth=substr( $data['recurring'],1,1);
			$day=substr( $data['recurring'],2,1);
			//church_admin_debug("Nth day $nth and $day");
			/*for ( $x=0; $x<$data['how_many']; $x++)
			{
				$date=new DateTime( $data['start_date'] );
				$date->modify("+ $x month");
				$start_date=church_admin_nth_day( $nth,$day,$date->format('Y-m-d') );
				//church_admin_debug("Start date $start_date");
				$values[]='("'.esc_sql( $data['title'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.$start_date.'","'.$data['start_time'].'","'.esc_sql( $data['end_time'] ).'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","'.esc_sql( $data['recurring'] ).'","'.(int)$data['how_many'].'","1","'.esc_sql( $eventType).'")';
			}*/
			$type=substr( $data['recurring'],0,1);//whether l or r
			
			church_admin_debug("Nth day $nth and $day");
			for ( $x=0; $x<$form['how_many']; $x++)
			{
				$date=new DateTime( $data['start_date'] );
				$date->modify("+ $x month");
				if($type=='l'){
					$days=array(0=>'Sunday',1=>"Monday",2=>"Tuesday",3=>"Wednesday",4=>"Thursday",5=>"Friday",6=>"Saturday");
					$start_date=date('Y-m-d',strtotime("last $days[$day]",$date->format('U')));
				}
				else{
					$start_date=church_admin_nth_day( $nth,$day,$date->format('Y-m-d') );
				}
				//church_admin_debug("Start date $start_date");
				if(!empty($start_date)){
					$values[]='("'.esc_sql($data['title']) .'","'.esc_sql($data['description']) .'","'. esc_sql($data['location']) .'","'.esc_sql($start_date).'","'.esc_sql($data['start_time']).'","'.esc_sql($data['end_time']) .'","'.(int)$data['cat_id'].'","'.(int)$event_id.'","'.esc_sql( $data['recurring']) .'","'.(int)$data['how_many'].'","1","'.esc_sql($eventType).'","'.esc_sql($data['event_image']).'")';
				}
			}
		break;
	}
		// execute query
		$sql='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,cat_id,event_id,recurring,how_many,general_calendar,event_type,event_image) VALUES '.implode(",",$values);
		
		//church_admin_debug( $sql);
		$wpdb->query( $sql);
	
	return $event_id;
}



function ca_app_new_calendar_form( $type,$dateID,$eventID)
{
	global $wpdb;
	$currData=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$dateID.'"');
	//church_admin_debug( $currData);
	$title=!empty( $currData->title)?esc_html( $currData->title):'';
	$description=!empty( $currData->description)?esc_html( $currData->description):'';
	$location=!empty( $currData->location)?esc_html( $currData->location):'';
	$cat_id=!empty( $currData->cat_id)?(int)$currData->cat_id:null;
	$date=!empty( $currData->start_date)?mysql2date(get_option('date_format'),$currData->start_date):mysql2date(get_option('date_format'),date('Y-m-d') );
	$sqldate=!empty( $currData->start_date)?esc_sql( $currData->start_date):date('Y-m-d');
	$start_time=!empty( $currData->start_time)?$currData->start_time:"00:00";
	$end_time=!empty( $currData->end_time)?$currData->end_time:"23:59";
	$recurring=!empty( $currData->recurring)? $currData->recurring:'s';
	$how_many=!empty( $currData->how_many)?(int)$currData->how_many:'1';
	$image = !empty($currData->event_image)?wp_get_attachment_src($currData->event_image,'medium'):null;
	//message of what editing
	
	switch( sanitize_text_field(stripslashes($_REQUEST['type'] ) ))
	{
		case 'single': $message=__('Editing just this occurence','church-admin'); break;
		case 'all': $message =__('Editing all occurrences past and future','church-admin');break;
		case 'future': 
			$message = esc_html( sprintf(__('Editing all occurrence from %1$s','church-admin' ) ,mysql2date(get_option('date_format'),$currDate->start_date) ));
		break;
	}
	$content='<div class="church-admin-form-group"><label>'.esc_html( __('Event title','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" id="title" value="'.$title.'" /></div>';
	
	if(!empty($image)){
		
		$content.='<p><img src="'.$image[0].'" id="image" data-data="" /></p>';
	}
	else{
		
		$content.='<p><img id="image"  src="'.plugins_url('/', dirname(__FILE__) ) . 'images/church-admin-logo.png"  /></p>';
	}
	$content.='<p>'.esc_html(__('Change Image','church-admin')).': <button class="get-image" data-source="camera"><img src="./img/camera.png" /></button>&nbsp; <button class="get-image" data-source="library"><img src="./img/gallery.png" /></button></p>';


	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Description','church-admin' ) ).'</label><textarea id="description" class="church-admin-form-control">'.esc_textarea( $description ).'</textarea></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Location','church-admin' ) ).'</label><input type="text" class="church-admin-form-control" id="location" value="'.$location.'" /></div>';
	/******************
	 * Category
	 *****************/
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Category','church-admin' ) ).'</label><select class="church-admin-form-control" id="cat_id">';
	$sql="SELECT * FROM ".$wpdb->prefix."church_admin_calendar_category";
	$results=$wpdb->get_results( $sql);
	foreach( $results AS $row)
	{
		$content.='<option value="'.(int)$row->cat_id.'" '.selected( $row->cat_id,$cat_id,FALSE).' style="background:'.esc_html( $row->bgcolor).';color:'.esc_attr($row->text_color).'">'.esc_html( $row->category).'</option>';
	}
	$content.='</select></div>';
	/******************
	 * Date
	 *****************/
	$locale=get_locale();
	//convert to JS version
	$locale=str_replace('_','-',$locale);
	$content.='<p class="ui-li-desc"><button class="calendar-date-picker" data-date="'.$sqldate.'" data-locale="'. esc_attr($locale).'">'.esc_html($date).'</button></p>';
	$content.='<input type="hidden" id="date-picker" value="'.esc_attr($sqldate).'" />';
	/******************
	 * Times
	 *****************/
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Start time','church-admin' ) ).'</label><input class="church-admin-form-control" type="time" id="start_time" value="'.esc_attr($start_time).'" /></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('End time','church-admin' ) ).'</label><input class="church-admin-form-control" type="time" id="end_time" value="'.esc_attr($end_time).'" /></div>';
	/*****************
	 * Recurring
	 *****************/
	if( $type=='single')$recurring='s';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Recurring','church-admin' ) ).'</label>';
	
	
	
	$content.= '<select id="recurring" name="recurring" class="church-admin-form-control">';
	$content.= '<option value="s" '.selected( $recurring,"s",FALSE).'>'.esc_html( __('Single','church-admin' ) ).'</option>';
	$content.= '<option value="1" '.selected( $recurring,1,FALSE).'>'.esc_html( __('Daily','church-admin' ) ).'</option>';
	$content.= '<option value="7" '.selected( $recurring,7,FALSE).'>'.esc_html( __('Weekly','church-admin' ) ).'</option>';
	$content.= '<option value="14" '.selected( $recurring,14,FALSE).'>'.esc_html( __('Fortnightly','church-admin' ) ).'</option>';
	$content.= '<option value="m" '.selected( $recurring,"m",FALSE).'>'.esc_html( __('Monthly on same date','church-admin' ) ).'</option>';
	$content.= '<option value="q" '.selected( $recurring,"q",FALSE).'>'.esc_html( __('Quarterly (every84 days)','church-admin' ) ).'</option>';
	$content.= '<option value="a" '.selected( $recurring,"a",FALSE).'>'.esc_html( __('Annually on same date','church-admin' ) ).'</option>';
	$content.= '<option value="n10" '.selected( $recurring,"n10",FALSE).'>'.esc_html( __('1st Sunday','church-admin' ) ).'</option>';
	$content.= '<option value="n20" '.selected( $recurring,"n20",FALSE).'>'.esc_html( __('2nd Sunday','church-admin' ) ).'</option>';
	$content.= '<option value="n30" '.selected( $recurring,"n30",FALSE).'>'.esc_html( __('3rd Sunday','church-admin' ) ).'</option>';
	$content.= '<option value="n40" '.selected( $recurring,"n40",FALSE).'>'.esc_html( __('4th Sunday','church-admin' ) ).'</option>';
	$content.= '<option value="n50" '.selected( $recurring,"n50",FALSE).'>'.esc_html( __('5th Sunday','church-admin' ) ).'</option>';
	$content.= '<option value="l00" '.selected( $recurring,"l0",FALSE).'>'.esc_html( __('Last Sunday','church-admin' ) ).'</option>';

	$content.= '<option value="n11" '.selected( $recurring,"n11",FALSE).'>'.esc_html( __('1st Monday','church-admin' ) ).'</option>';
	$content.= '<option value="n21" '.selected( $recurring,"n21",FALSE).'>'.esc_html( __('2nd Monday','church-admin' ) ).'</option>';
	$content.= '<option value="n31" '.selected( $recurring,"n31",FALSE).'>'.esc_html( __('3rd Monday','church-admin' ) ).'</option>';
	$content.= '<option value="n41" '.selected( $recurring,"n41",FALSE).'>'.esc_html( __('4th Monday','church-admin' ) ).'</option>';
	$content.= '<option value="n51" '.selected( $recurring,"n51",FALSE).'>'.esc_html( __('5th Monday','church-admin' ) ).'</option>';
	$content.= '<option value="l11" '.selected( $recurring,"l1",FALSE).'>'.esc_html( __('Last Monday','church-admin' ) ).'</option>';

	$content.= '<option value="n12" '.selected( $recurring,"n12",FALSE).'>'.esc_html( __('1st Tuesday','church-admin' ) ).'</option>';
	$content.= '<option value="n22" '.selected( $recurring,"n22",FALSE).'>'.esc_html( __('2nd Tuesday','church-admin' ) ).'</option>';
	$content.= '<option value="n32" '.selected( $recurring,"n32",FALSE).'>'.esc_html( __('3rd Tuesday','church-admin' ) ).'</option>';
	$content.= '<option value="n42" '.selected( $recurring,"n42",FALSE).'>'.esc_html( __('4th Tuesday','church-admin' ) ).'</option>';
	$content.= '<option value="n52" '.selected( $recurring,"n52",FALSE).'>'.esc_html( __('5th Tuesday','church-admin' ) ).'</option>';
	$content.= '<option value="l22" '.selected( $recurring,"l2",FALSE).'>'.esc_html( __('Last Tuesday','church-admin' ) ).'</option>';

	$content.= '<option value="n13" '.selected( $recurring,"n13",FALSE).'>'.esc_html( __('1st Wednesday','church-admin' ) ).'</option>';
	$content.= '<option value="n23" '.selected( $recurring,"n23",FALSE).'>'.esc_html( __('2nd Wednesday','church-admin' ) ).'</option>';
	$content.= '<option value="n33" '.selected( $recurring,"n33",FALSE).'>'.esc_html( __('3rd Wednesday','church-admin' ) ).'</option>';
	$content.= '<option value="n43" '.selected( $recurring,"n43",FALSE).'>'.esc_html( __('4th Wednesday','church-admin' ) ).'</option>';
	$content.= '<option value="n53" '.selected( $recurring,"n53",FALSE).'>'.esc_html( __('5th Wednesday','church-admin' ) ).'</option>';
	$content.= '<option value="l33" '.selected( $recurring,"l3",FALSE).'>'.esc_html( __('Last Wednesday','church-admin' ) ).'</option>';

	$content.= '<option value="n14" '.selected( $recurring,"n14",FALSE).'>'.esc_html( __('1st Thursday','church-admin' ) ).'</option>';
	$content.= '<option value="n24" '.selected( $recurring,"n24",FALSE).'>'.esc_html( __('2nd Thursday','church-admin' ) ).'</option>';
	$content.= '<option value="n34" '.selected( $recurring,"n34",FALSE).'>'.esc_html( __('3rd Thursday','church-admin' ) ).'</option>';
	$content.= '<option value="n44" '.selected( $recurring,"n44",FALSE).'>'.esc_html( __('4th Thursday','church-admin' ) ).'</option>';
	$content.= '<option value="n54" '.selected( $recurring,"n54",FALSE).'>'.esc_html( __('5th Thursday','church-admin' ) ).'</option>';
	$content.= '<option value="l44" '.selected( $recurring,"l5",FALSE).'>'.esc_html( __('Last Thursday','church-admin' ) ).'</option>';

	$content.= '<option value="n15" '.selected( $recurring,"n14",FALSE).'>'.esc_html( __('1st Friday','church-admin' ) ).'</option>';
	$content.= '<option value="n25" '.selected( $recurring,"n24",FALSE).'>'.esc_html( __('2nd Friday','church-admin' ) ).'</option>';
	$content.= '<option value="n35" '.selected( $recurring,"n34",FALSE).'>'.esc_html( __('3rd Friday','church-admin' ) ).'</option>';
	$content.= '<option value="n45" '.selected( $recurring,"n44",FALSE).'>'.esc_html( __('4th Friday','church-admin' ) ).'</option>';
	$content.= '<option value="n55" '.selected( $recurring,"n44",FALSE).'>'.esc_html( __('5th Friday','church-admin' ) ).'</option>';
	$content.= '<option value="l55" '.selected( $recurring,"l5",FALSE).'>'.esc_html( __('Last Friday','church-admin' ) ).'</option>';


	$content.= '<option value="n16" '.selected( $recurring,"n16",FALSE).'>'.esc_html( __('1st Saturday','church-admin' ) ).'</option>';
	$content.= '<option value="n26" '.selected( $recurring,"n26",FALSE).'>'.esc_html( __('2nd Saturday','church-admin' ) ).'</option>';
	$content.= '<option value="n36" '.selected( $recurring,"n36",FALSE).'>'.esc_html( __('3rd Saturday','church-admin' ) ).'</option>';
	$content.= '<option value="n46" '.selected( $recurring,"n46",FALSE).'>'.esc_html( __('4th Saturday','church-admin' ) ).'</option>';
	$content.= '<option value="n56" '.selected( $recurring,"n56",FALSE).'>'.esc_html( __('5th Friday','church-admin' ) ).'</option>';
	$content.= '<option value="l66" '.selected( $recurring,"l6",FALSE).'>'.esc_html( __('Last Friday','church-admin' ) ).'</option>';

	$content.= '</select></div>';	
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Frequency','church-admin' ) ).'</label><input class="church-admin-form-control" type="number" id="how_many" value="'.esc_attr($how_many).'" /></div>';

	/*********
	 * SAVE
	 ********/
	$content.='<p><button class="button action red" data-tab="calendar-save" data-type="'.esc_html( $type).'" data-eventid="'.(int)$eventID.'" data-dateid="'.(int)$dateID.'" data-savenow="yes">'.esc_html( __('Save','church-admin' ) ).'</button></p>';


	$button='<button class="button action green" data-tab="calendar">'.esc_html( __('Back to calendar','church-admin' ) ).'</button>';
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Calendar event edit','church-admin' ) ),'content'=>$content,'message'=>$message,'button'=>$button,'view'=>'html');
	return $output;
}


function ca_app_new_calendar_delete( $type,$dateID,$eventID)
{
	global $wpdb;
	switch( $type)
	{
		case 'single':
			if(!empty( $dateID) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$dateID.'"');
			$message=__('Single event deleted','church-admin');
		break;
		case 'all':
			if(!empty( $eventID) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$eventID.'"');
			
			$message=__('All occurrences of  event deleted','church-admin');
		break;
		case 'future':
			if(!empty( $dateID)&& !empty( $eventID) ){
				$date = $wpdb->get_var('SELECT start_date FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$dateID.'"');
				if(!empty($date))
				{
					$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$eventID.'" AND start_date>="'.esc_sql($date).'" ');
					$message=__('This and future occurrences of  event deleted','church-admin');
				}
				else
				{
					$message = __('Date to start deleting from not found','church-admin');
				}
				
			}
			
		break;
	}
	$content='';
	$button='<button class="button action" data-tab="calendar" >'.esc_html( __('Back to calendar','church-admin' ) ).'</button>';
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Calendar event delete','church-admin' ) ),'content'=>$content,'message'=>$message,'button'=>$button,'view'=>'html');
	update_option('church_admin_modified_app_content',time() );
	return $output;


}
/***************************
 * Groups
 **************************/
function ca_app_new_groups($loginStatus)
{
	global $wpdb,$wp_locale;
	$content='<p><button id="mygroup" data-tab="#mygroup" class="button action">'.esc_html( __('My group','church-admin' ) ).'</button></p><ul > ';
			
	$allowed_html = [
		'iframe' => [
			'src' => [],
			'allow' => [],
			'width' => [],
			'height' => [],
			'frameborder' => [],
			'allowFullScreen' => []
		], 
		'img'=>[
			'src'=>[],
			'class'=>[]
		],
		'p' =>['br'=>[]],
		'a'      => [
			'href'  => [],
			'title' => [],
		],
		'br'     => [],
		'em'     => [],
		'strong' => [],
		];
	if(!empty( $_REQUEST['token'] ) ){
		church_admin_app_log_visit( $loginStatus, __('Groups','church-admin' ) ,$loginStatus);
	}
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id!=1';
	$results = $wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		foreach( $results as $row)
		{
			$leaders=NULL;
			$ldrsResults=$wpdb->get_results('SELECT CONCAT_WS(" ", a.first_name, a.last_name) AS name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE b.meta_type="smallgroupleader" AND b.ID="'.(int)$row->id.'" AND a.people_id=b.people_id');
			
			if(!empty( $ldrsResults) )
			{
				$curr_leaders=array();
				foreach( $ldrsResults AS $ldrsRow)
				{
					$curr_leaders[]=$ldrsRow->name;
				}
				$leaders=esc_html(implode(", ",$curr_leaders) );
			}
			$image=null;
			if(!empty( $row->attachment_id) ) $image = wp_get_attachment_image( $row->attachment_id,'medium',FALSE,array('class'=>"group-image") );
			$description=!empty( $row->description)?wp_kses( $row->description,$allowed_html):null;
			$contact=NULL;
			if(!empty( $row->contact_number) )
			{
				if(is_email( $row->contact_number) )
				{
					$contact='<a href="'.esc_url('mailto:'.$row->contact_number).'">'.esc_html( __('Email leader','church-admin' ) ).'</a>';
				}
				else
				{
					$contact='<a href="'.esc_url('tel:'.$row->contact_number).'">'.esc_html( $row->contact_number).'</a>';
				}

			}
			$content.='<li><h3 class="ui-li-heading">'.esc_html( $row->group_name).'</h3>';
            if( $image)$content.='<p>'. esc_url($image).'</p>';
            if( $description)$content.='<p>'.wp_kses( $description,$allowed_html).'</p>';
            $content.='<p>'.esc_html( $wp_locale->get_weekday( $row->group_day).' '.mysql2date(get_option('time_format'),$row->group_time) ).'</p>';
			$content.='<p>'.esc_html( $row->address).'</p>';
            $content.='<p>'.wp_kses( $contact,$allowed_html).'</p>';
			$content.='<hr/></li>';
		}

	}else
	{
		$content=__('No small groups yet','church-admin');

	}
	return $content;
}

/*************************
 * Login
 ************************/
function ca_app_new_login_form( $next)
{
	$html='<div class="ui-content">';
    $html.='<input type="hidden" value="'.$next.'" id="whereNext" />';
    $html.='<p><input id="username" autocomplete="username" type="text" placeholder="'.esc_html( __("Username",'church-admin' ) ).'" autocorrect="off" autocapitalize="none" /></p>';
    $html.='<p><input id="password"  type="password" autocomplete="current-password" placeholder="'.esc_html( __("Password",'church-admin' ) ).'" /></p>';
	$html.='<p><input type="checkbox" id="show-password">'.esc_html( __('Show password','church-admin' ) ).'</p>';
    $html.='<p><button class="button action" data-tab="#login" id="login" >'.esc_html( __("Login",'church-admin' ) ).'</button></p>';
    $html.='<p><button class="button action" data-tab="#forgotten" id="forgotten"  >'.esc_html( __("Forgotten password",'church-admin' ) ).'</button></p>';
    $appRegistrations=get_option('church_admin_no_app_registrations');
	if ( empty( $appRegistrations) )$html.='<p><button class="button action" data-tab="#register" id="register"  >'.esc_html( __("Register",'church-admin' ) ).'</button></p>';
    $html.='</div>';
	$output = array( 'content'=>$html,'view'=>'html','page_title'=>esc_html( __('Please login','church-admin')) );
	return $output;
}
/***************************
 * Process login
 **************************/
function ca_app_new_login()
{
	global $wpdb;
	church_admin_debug("*************** PROCESS LOGIN ***************");
	$output=array();
	if(!empty( $_REQUEST['next'] ) )
	{
		$next=esc_html( sanitize_text_field(stripslashes($_REQUEST['next']) ) );

	}
	else $next='account';

	if(!empty( $_REQUEST['username'] ) )
	{
		//backwards compatible for older versions of app
		$username = urldecode( sanitize_text_field(stripslashes($_REQUEST["username"] )) );
		$password = sanitize_text_field(stripslashes($_REQUEST["password"]) );
	}
	else
	{
		//username1 & password1 to get round Google Captcha plugin blocking logins
		$username = urldecode( sanitize_text_field(stripslashes($_REQUEST["username1"]) ) );
		$password  = sanitize_text_field(stripslashes($_REQUEST["password1"]) );
	}
	$user=wp_authenticate( $username,$password);
	if(is_wp_error( $user) )
	{
		church_admin_debug('Not authenticated');
		return ca_app_new_login_form( $next);
	}
	else
	{
		church_admin_debug('Authenticated');
		
		church_admin_debug('Check for people record');
		$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
		church_admin_debug( $wpdb->last_query);
		if ( empty( $people_id) )
		{
			church_admin_debug('No people id for user');
			//no directory entry for that login
			$token = !empty($loginStatus->token)?$loginStatus->token:null;
			$output = array( 'token'=>esc_html( $token ),'message'=>esc_html( __('There is no directory entry for your login yet','church-admin' ) ),'content'=>'');
			return $output;
		}
		church_admin_debug('People ID is: '.(int)$people_id);
		//create unique token
		do {
			$token = bin2hex(random_bytes(20) );
		}WHILE(!empty( $wpdb->get_var('SELECT UUID FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql( $token).'"') ));
		church_admin_debug('Login Token: '. $token);
		//update APP table
		church_admin_debug('Check app table');
		$app_id=$wpdb->get_var('SELECT app_id FROM '.$wpdb->prefix.'church_admin_app WHERE people_id="'.(int)$people_id.'" AND user_id="'.(int)$user->ID.'"');
		church_admin_debug( $wpdb->last_query);
		if( $app_id)
		{
			church_admin_debug('Already in app table ');
			//refresh entry with new token
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_app SET UUID="'.esc_sql( $token).'" WHERE people_id="'.(int)$people_id.'" AND user_id="'.(int)$user->ID.'"');
			church_admin_debug( $wpdb->last_query);
		}
		else
		{
			church_admin_debug('Not already in app table ');
			//create entry
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_app (UUID,user_id,last_login,people_id)VALUES("'.esc_sql( $token).'","'.(int)$user->ID.'","'.date('Y-m-d h:i:s').'","'.(int)$people_id.'")');
			church_admin_debug( $wpdb->last_query);
		}

		$loginStatus=$wpdb->get_row('SELECT b.UUID AS token,a.member_type_id,a.people_id,a.user_id,a.household_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_app b WHERE a.user_id=b.user_id AND b.UUID="'.esc_sql(sanitize_text_field( stripslashes ($token) )).'"');
		church_admin_debug("*** JUST LOGGED IN ***");
		church_admin_debug( $loginStatus);
		church_admin_debug('Do next: '.$next);
		
		switch( $next)
		{
			default:
			case 'account': 
				//church_admin_debug('Getting account content with token '.$token);
				church_admin_app_log_visit( $loginStatus, __('Account','church-admin' ) ,$loginStatus);
				$output= ca_app_new_account( $loginStatus);
			break;
			case 'address-list':
				church_admin_app_log_visit( $loginStatus, __('Address list','church-admin' ) ,$loginStatus);
				$output=ca_app_new_address_list( $loginStatus);
			break;
			case 'search':
				church_admin_app_log_visit( $loginStatus, __('Address search','church-admin' ) ,$loginStatus);
				$output=ca_app_new_search ( $loginStatus );
			break;	
			case 'notifications':
				church_admin_app_log_visit( $loginStatus, __('Notifications settings','church-admin' ) ,$loginStatus);
				$output=ca_app_new_get_notification_settings_form( $loginStatus);
			break;
			case 'calendar':
				church_admin_app_log_visit( $loginStatus, __('Calendar','church-admin' ) ,$loginStatus);
				$output=ca_app_new_calendar( $loginStatus);
			break;
			
			case 'rota':
				church_admin_app_log_visit( $loginStatus, __('Schedule','church-admin' ) ,$loginStatus);
				$output=ca_app_new_rota( $loginStatus);
			break;
			case 'classes':
				church_admin_app_log_visit( $loginStatus, __('Classes','church-admin' ) ,$loginStatus);
				$output=ca_app_new_classes( $loginStatus);
			break;
			case 'my-rota':
				church_admin_app_log_visit( $loginStatus, __('My schedule','church-admin' ) ,$loginStatus);
				$output=ca_app_new_my_rota( $loginStatus);
			break;
			case 'not-available':
				church_admin_app_log_visit( $loginStatus, __('Not available','church-admin' ) ,$loginStatus);
				$output=ca_app_new_not_available( $loginStatus);
			break;
			case 'mygroup':
				church_admin_app_log_visit( $loginStatus, __('My group','church-admin' ) ,$loginStatus);
				$output=ca_app_new_mygroup( $loginStatus);
			break;
			case 'my-prayer':
				church_admin_app_log_visit( $loginStatus, __('My prayer','church-admin' ) ,$loginStatus);
				$output = ca_app_show_prayer($loginStatus);
			break;
		}
		//church_admin_debug('AT LINE 1809 $output[content]');
		//church_admin_debug( $output['content'] );
		//menu
		$people_id=!empty($loginStatus->people_id) ? $loginStatus->people_id : null;
		$menuOutput=ca_build_menu($people_id);
		//church_admin_debug('$menuOutput');
		//church_admin_debug($menuOutput);
		$output['menu']=implode("\r\n",$menuOutput);
		//church_admin_debug('After login create app cache again');
		//church_admin_debug('Login Status Obj');
		//church_admin_debug( $loginStatus);
		$cacheOutput=ca_refresh_app_cache( $loginStatus);
		//church_admin_debug( $cacheOutput);
		if(!empty( $cacheOutput) )$output=array_merge( $output,$cacheOutput);
		//church_admin_debug('AT LINE 1816 $output[content]');
		//church_admin_debug( $output['content'] );
		church_admin_debug('***** FINISH ca_app_new_login ******');
		return $output;
	}
	
        
}

/***************************
 * People Delete
 **************************/
function ca_app_new_people_delete( $people_id,$household_id,$loginStatus)
{
	
	church_admin_delete_people( $people_id,$household_id,FALSE,FALSE);
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
	church_admin_new_app_build_address_list( $loginStatus);
	$output=ca_app_new_address_list( $loginStatus);
	$output['message']=__('Person deleted','church-admin');
	update_option('church_admin_modified_app_content',time() );
	return $output;
}
/***************************
 * People Edit
 **************************/


function ca_app_new_people_edit( $people_id,$household_id,$loginStatus)
{
	global $wpdb;
	$saved_member_type_id=get_option('church_admin_member_type_id_for_registrations');
	if(empty($saved_member_type_id)){$saved_member_type_id=1;}
	//church_admin_debug('******** ca_app_new_people_edit ********');
	//church_admin_debug( $_REQUEST);
	global $wpdb;
	$content='Edit Address';
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),
			'content'=>$content,
			'view'=>'html',
			'page_title'=>esc_html( __('Edit person','church-admin') )
		);
	if( $household_id!=$loginStatus->household_id && !church_admin_level_check('Directory',$loginStatus->user_id) )
	{
			$output['error']=__("You don't have permissions to edit that person",'church-admin');
			return $output;
	}
	else
	{
		$household_id=(int)sanitize_text_field(stripslashes($_REQUEST['household_id']));
	}
	if(!empty( $_REQUEST['person_save'] ) )
	{
		$attachment_id=null;
		//church_admin_debug($_REQUEST);
		$first_name=sanitize_text_field( stripslashes ($_REQUEST['first_name'] ) );
		$prefix=!empty($_REQUEST['prefix']) ? sanitize_text_field( stripslashes ($_REQUEST['prefix'] ) ): null;
		$last_name=sanitize_text_field( stripslashes ($_REQUEST['last_name'] ) );
		$sex = isset($_REQUEST['sex']) ? church_admin_sanitize($_REQUEST['sex']):1;
		$marital_status = !empty($_REQUEST['marital_status']) ? church_admin_sanitize($_REQUEST['marital_status']):0;
	

		$email=sanitize_text_field( stripslashes ($_REQUEST['email'] ) );
		$mobile=sanitize_text_field( stripslashes ($_REQUEST['mobile'] )  );
		$sms_send=(int)sanitize_text_field(stripslashes($_REQUEST['sms_send']));
		$email_send=(int)sanitize_text_field(stripslashes($_REQUEST['email_send']));
		$mail_send=(int)sanitize_text_field(stripslashes($_REQUEST['mail_send']));
		$phone_calls=(int)sanitize_text_field(stripslashes($_REQUEST['phone_calls']));
		$show_me=(int)sanitize_text_field(stripslashes($_REQUEST['show_me']));
		$photo_permission=(int)sanitize_text_field(stripslashes($_REQUEST['photo_permission']));
		$image = !empty($_REQUEST['image'])?church_admin_sanitize($_REQUEST['image']):null;
		$date_of_birth = !empty($_REQUEST['date_of_birth']) ? church_admin_sanitize($_REQUEST['date_of_birth']):null;
		//for backwards compatability use old date_of_birth empty
		if(empty($date_of_birth) &&!empty($people_id))
		{
			$date_of_birth=$wpdb->get_var('SELECT date_of_birth FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		}
		if(!empty($date_of_birth) && $date_of_birth=='0000-00-00'){$date_of_birth = null;}
		church_admin_debug('Date of Birth '.$date_of_birth);
		church_admin_debug('Escaped'.esc_sql($date_of_birth));
		$wedding_anniversary = !empty($_REQUEST['wedding_anniversary']) ? church_admin_sanitize($_REQUEST['wedding_anniversary']):null;
		//for backwards compatability use old date_of_birth empty
		if(empty($wedding_anniversary) && !empty($household_id))
		{
			$wedding_anniversary=$wpdb->get_var('SELECT wedding_anniversary FROM '.$wpdb->prefix.'church_admin_household WHERE household_id="'.(int)$household_id.'"');
		}
		if(!empty($wedding_anniversary) && $wedding_anniversary=='0000-00-00'){$wedding_anniversary= null;}
		//grab current attachment image if exists
		$attachment_id = null;
		$attachment_id = $wpdb->get_var('SELECT attachment_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id ="'.(int)$people_id.'"');
		if(!empty($attachment_id)){
			$old_image=wp_get_attachment_image_src($attachment_id,'medium');
		}
		
		if(!empty($image)){
			
			if(empty($old_image) || (!empty($old_image) && $image!=$old_image[0])){
				//either no old image or the old image url is not the same as what has been uploaded. Therefore save and get a new attachment_id
			
				//church_admin_debug($image);
				$attachment_id = church_admin_save_base64_image( $image, 'person-'.$people_id );
			}

		}
		
		//privacy
		$privacy=array();
		if(!empty($_REQUEST['show_email'])){$privacy['show-email']=1;}else{$privacy['show-email']=0;}
		if(!empty($_REQUEST['show_cell'])){$privacy['show-cell']=1;}else{$privacy['show-cell']=0;}
		if(!empty($_REQUEST['show_landline'])){$privacy['show-landline']=1;}else{$privacy['show-landline']=0;}
		if(!empty($_REQUEST['show_address'])){$privacy['show-address']=1;}else{$privacy['show-address']=0;}
		$updated_by = (int)$loginStatus->user_id;
		
		$priv=serialize($privacy);
		//church_admin_debug($priv);
		if ( empty( $people_id) )
		{
			$people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.esc_sql($first_name).'" AND last_name="'.esc_sql($last_name).'" AND email="'.esc_sql($email).'" AND mobile="'.esc_sql($mobile).'" AND household_id="'.(int)$household_id.'"');
		}
		$data =array('first_name' =>$first_name,
							'prefix'=>$prefix,
							'last_name'=>$last_name,
							'email'=>$email,
							'mobile'=>$mobile,
							'sms_send'=>$sms_send,
							'email_send'=>$email_send,
							'mail_send'=>$mail_send,
							'phone_calls'=>$phone_calls,
							'show_me'=>$show_me,
							'photo_permission'=>$photo_permission,
							'household_id'=>$household_id,
							
							'privacy'=>maybe_serialize($privacy),
							'attachment_id'=>$attachment_id,
							'updated_by'=>$updated_by,
							'last_updated'=>wp_date('Y-m-d H:i:s'),
							
							'date_of_birth'=>$date_of_birth,
							'marital_status'=>$marital_status,
							'sex'=>$sex
						);
						church_admin_debug($data);
		if ( empty( $people_id) )
		{
			$data['member_type_id'] = $saved_member_type_id;
			$data['first_registered'] = wp_date('Y-m-d H:i:s');
			$wpdb->insert($wpdb->prefix.'church_admin_people',$data);
			$people_id=$wpdb->insert_id;
			$oldEmail=NULL;
		}
		else
		{
			//update

			$people_id = (int)$_REQUEST['people_id'];
			if(!empty( $people_id) )
			{
				$oldEmail=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
				//save to DB
				$wpdb->update($wpdb->prefix.'church_admin_people',$data,	array('people_id'=>$people_id));			
				
			}
			
		}
		church_admin_debug( $wpdb->last_query);
		if(!empty($wedding_anniversary)){
			$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_household SET wedding_anniversary="'.esc_sql($wedding_anniversary).'" WHERE household_id="'.(int)$household_id.'"');
		}
		if(!empty( $_REQUEST['prayer'] ) )
		{
			church_admin_update_people_meta(1,$people_id,'prayer-requests');
		}
		else
		{
			church_admin_delete_people_meta(null,$people_id,'prayer-requests');
		}
		if(!empty( $_REQUEST['bible'] ) )
		{
			church_admin_update_people_meta(1,$people_id,'bible-readings');
		}
		else
		{
			church_admin_delete_people_meta(null,$people_id,'bible-readings');
		}
		
		
		$output['message']='<p>'.esc_html( __('Person record updated','church-admin' ) ).'</p>';
		//$output['message'].='<p><button class="button action" data-tab="address">'.esc_html( __('Back to address list','church-admin' ) ).'</button>';
		delete_option('church_admin_app_address_cache');
		delete_option('church_admin_app_admin_address_cache');
		church_admin_new_app_build_address_list( $loginStatus);
		//church_admin_debug( $loginStatus);
		$whichAppAddressList=get_option('church_admin_which_app_address_list_type');
		switch( $_REQUEST['next'] )
		{
			case 'account': $output=ca_app_new_account( $loginStatus);break;
			case 'address': 
				switch( $whichAppAddressList)
				{
					default:
					case 'new':
						$output=ca_app_new_address_list( $loginStatus);
					break;
					case 'old':
						$output=ca_app_old_address_list( $loginStatus);
					break;
				}
			break;
		}
		return $output;
	}
	$person=$wpdb->get_row('SELECT a.*,b.wedding_anniversary FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b WHERE a.household_id= b.household_id AND a.people_id="'.(int)$people_id.'"');
	//church_admin_debug($person);
	$bible_readings=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="bible-readings"');
	$prayer_requests=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE people_id="'.(int)$people_id.'" AND meta_type="prayer-requests"');
	$content='<input type="hidden" value="'.(int)$people_id.'" id="people_id" />';
	$content.='<input type="hidden" value="'.(int)$household_id.'" id="household_id" />';
	
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('First name','church-admin' ) ).'</label><input class="church-admin-form-control" id="first_name" type="text"  autocorrect="off" autocapitalize="none" ';
	if(!empty( $person->first_name) )$content.=' value="'.esc_html( $person->first_name).'" ';
	$content.='/></div>';
	$use_prefix=get_option('church_admin_use_prefix');
	if(!empty( $use_prefix) )
	{
		$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Prefix','church-admin' ) ).'</label><input class="church-admin-form-control"  id="prefix" type="text"  autocorrect="off" autocapitalize="none" ';
		if(!empty( $person->prefix) )$content.='value="'.esc_html( $person->prefix).'" ';
		$content.='/></div>';
	}
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Last name','church-admin' ) ).'</label><input class="church-admin-form-control"  id="last_name" type="text"  autocorrect="off" autocapitalize="none" ';
	if(!empty( $person->last_name) ) $content.=' value="'.esc_html( $person->last_name).'" ';
	$content.='/></div>';

	
	if(!empty($person->attachment_id)){
		$image=wp_get_attachment_image_src($person->attachment_id,'medium');
		$content.='<p><img src="'.$image[0].'" id="image" /></p>';
		$content.='<input type="hidden" value="'.(int)$person->attachment_id.'" id="attachment_id">';
	}
	else{
		if(isset( $person->sex)  && $person->sex==1)  {$image='man.svg';}else{$image='woman.svg';}
		$content.='<img id="image"  src="'.plugins_url('/', dirname(__FILE__) ) . 'images/'.$image.'"  />';
	}
	$content.='<p class="middle-align">'.esc_html(__('Change Image','church-admin')).':&nbsp; <span class="get-image" data-source="camera"><img src="./img/camera.png" /></span>&nbsp; <span class="get-image" data-source="library"><img src="./img/gallery.png" /></span></p>';

	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Email','church-admin' ) ).'</label><input class="church-admin-form-control"  id="email" type="text"  autocorrect="off" autocapitalize="none" ';
	if(!empty( $person->email) )$content.=' value="'.esc_html( $person->email).'" ';
	$content.='/></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Cellphone','church-admin' ) ).'</label><input class="church-admin-form-control" id="mobile" type="text"  autocorrect="off" autocapitalize="none" ';
	if(!empty( $person->mobile) )$content.=' value="'.esc_html( $person->mobile).'" ';
	$content.='/></div>';
	//date of birth
	if(!empty($person->date_of_birth) && $person->date_of_birth=="0000-00-00"){$person->date_of_birth = NULL;}
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Date of birth','church-admin' ) ).' </label>';
	$sqldate = !empty($person->date_of_birth) ? $person->date_of_birth : null;
	$date = !empty($person->date_of_birth) ? mysql2date(get_option('date_format'),$person->date_of_birth ) : null;
	$content.='&nbsp; <span class="dob-output">';
	if(!empty($date)){
		$content.= $date;
	}
	$content.='</span>';
	$locale=get_locale();
	//convert to JS version
	$locale=str_replace('_','-',$locale);
	$content.='<p class="ui-li-desc"><button id="dob-output" data-output="dob-output" class="date-picker" data-locale="'.esc_attr($locale).'" data-date="'.esc_attr($sqldate).'">'.__('Date picker','church-admin').'</button></p>';
	$content.='</div>';
	//gender
	$current_gender = isset($person->sex) ? (int)$person->sex : 1; 
	church_admin_debug('current gender: '.$current_gender);
	$gender=get_option('church_admin_gender');
	//church_admin_debug($gender);
	$content.='<div class="church-admin-form-group"><label >'.esc_html( __( 'Gender','church-admin') ).'</label><select id="sex" class="sex church-admin-form-control" >';
	foreach( $gender AS $key => $value )  {
		$content.= '<option value="'.esc_html( $key).'" '.selected($key,$current_gender,FALSE).'>'.esc_html( $value).'</option>';
	}
	$content.='</select></div>'."\r\n";
	//marital status
	$church_admin_marital_status=get_option('church_admin_marital_status');
	$content.='<div class="church-admin-form-group"><label for="marital_status">'.esc_html( __( 'Marital Status','church-admin') ).'</label><select data-name="marital_status" name="marital_status" id="marital_status" class="marital_status church-admin-form-control">';
	$current_MS = !empty($person->marital_status) ? (int)$person->marital_status : 0;
	foreach( $church_admin_marital_status AS $id=>$type)
	{	
		$content.= '<option value="'.(int)$id.'" '.selected($id,$current_MS,FALSE).'>'.esc_html($type).'</option>';
	}
	$content.='</select></div>';
	//wedding anniversary
	 //wedding_anniversary
	 $wa=get_option('church_admin_show_wedding_anniversary');
	 if(!empty($wa)){
		 $wedding_anniversary=!empty($person->wedding_anniversary)?$person->wedding_anniversary:null;
		 $content.= '<div class="church-admin-form-group" id="wedding-anniversary" ';
		 if(empty($person->marital_status)){
			$content.='style="display:none" ';
		 }
		 $content.='><label>'.esc_html( __( 'Wedding Anniversary','church-admin') ).'</label>';
		 if(!empty($person->wedding_anniverary) && $person->wedding_anniversary=='0000-00-00'){$person->wedding_anniversary = null;}
		 $sqldate = !empty($person->wedding_anniversary) ? $person->wedding_anniversary : null;
		 
		 $date = !empty($person->wedding_anniversary) ? mysql2date(get_option('date_format'),$person->wedding_anniversary ) : null;
		 $content.='&nbsp; <span class="wa-output">';
		 if(!empty($date)){
			 $content.= $date;
		 }
		 $content.='</span>';
		 $locale=get_locale();
		 //convert to JS version
		 $locale=str_replace('_','-',$locale);
		 if(empty($sqldate)){$sqldate=wp_date('Y-m-d');}
		 $content.='<p class="ui-li-desc"><button  id="wa-output" data-output="wa-output" class="date-picker" data-locale="'.esc_attr($locale).'" data-date="'.esc_attr($sqldate).'">'.__('Date picker','church-admin').'</button></p>';
		 $content.='</div>';
	 }

	 $content.='<script>
	 jQuery(document).ready(function($){
 
		 $("#marital_status").on("change", function (e) {
			 
			 var selected = $("option:selected", this).val();
			 console.log(selected);
			 if(selected==="3"){ 
				 console.log("show wa field");
				 $("#wedding-anniversary").show();
			 }
 
		 });
	 });
	 </script>';




	$content.='<p><strong>'.esc_html( __('I give permission...','church-admin' ) ).'</strong></p>'; 
	$content.='<div class="church-admin-form-group"><input id="email_send" class="email-permissions" type="checkbox"  value="1"';
	if(!empty( $person->email_send) )$content.=' checked="checked"';
	$content.='/><label>'.esc_html( __('To receive emails','church-admin' ) ).'</label></div>';
	$noPrayer=get_option('church-admin-no-prayer');
	if ( empty( $noPrayer) ){   
		$content.='<div class="church-admin-form-group"><input  class="email-permissions" id="prayer_requests" type="checkbox" value="1" ';
		if(!empty( $prayer_requests) )$content.=' checked="checked"';
		$content.='/><label>'.esc_html( __('To receive prayer request emails','church-admin' ) ).'</label></div>';
	}
	
	$noBibleReadings=get_option('church-admin-no-bible-readings');
	if ( empty( $noBibleReadings) ){
           
		
		$content.='<div class="church-admin-form-group"><input class="email-permissions"  id="bible_readings" type="checkbox"  value="1"';
		if(!empty( $bible_readings) )$content.=' checked="checked"';
		$content.='/><label>'.esc_html( __('To receive Bible reading emails','church-admin' ) ).'</label></div>';
	}
	$content.='<div class="church-admin-form-group"><input  class="email-permissions"  id="rota_email" type="checkbox"  value="1"';
	if(!empty( $person->rota_email) )$content.=' checked="checked"';
	$content.='/><label>'.esc_html( __('To receive schedule reminder emails','church-admin' ) ).'</label></div>'; 
	
	

	$content.='<div class="church-admin-form-group"><input id="sms_send" type="checkbox"  value="1"';
	if(!empty( $person->sms_send) )$content.=' checked="checked"';
	$content.='/><label>'.esc_html( __('To receive SMS','church-admin' ) ).'</label></div>'; 


	$content.='<div class="church-admin-form-group"><input id="mail_send" type="checkbox"  value="1"';
	if(!empty( $person->mail_send) )$content.=' checked="checked"';
	$content.='/><label>'.esc_html( __('To receive mail','church-admin' ) ).'</label></div>'; 

	
	$content.='<div class="church-admin-form-group"><input id="phone_calls" type="checkbox"  value="1"';
	if(!empty( $person->phone_calls) )$content.=' checked="checked"';
	$content.='/><label>'.esc_html( __('To receive phone calls','church-admin' ) ).'</label></div>';     
	$content.='<div class="church-admin-form-group"><input id="show_me" type="checkbox"  value="1"';
	if(!empty( $person->show_me) )$content.=' checked="checked"';
	$content.='/><label>'.esc_html( __('To show me on the password protected address list','church-admin' ) ).'</label></div>';
	$content.='<p><strong>'.esc_html( __('Refine address list privacy','church-admin' ) ).'</strong></p>';
        $fine_privacy=!empty($person->privacy)?maybe_unserialize($person->privacy):array();
        //show email
        $content.='<div class="church-admin-form-group"><input type="checkbox" name="show-email" id="show_email" class="show_extras" ';
        if(!empty( $fine_privacy['show-email']) )  {$content.=' checked ="checked" ';}
        $content.='/> '.esc_html( __("Show email address",'church-admin' ) ).' </div>';
        //show cell
		$content.='<div class="church-admin-form-group"><input type="checkbox" name="show-cell" id="show_cell" class="show_extras"  ';
        if(!empty( $fine_privacy['show-cell']) )  {$content.=' checked ="checked" ';}
        $content.='/> '.esc_html( __("Show cell number",'church-admin' ) ).' </div>';
        //show landline
        $content.='<div class="church-admin-form-group"><input type="checkbox" name="show-landline" id="show_landline" class="show_extras"   ';
        if(!empty( $fine_privacy['show-landline']) )  {$content.=' checked ="checked" ';}
        $content.='/> '.esc_html( __("Show landline",'church-admin' ) ).' </div>';
        //show address
        $content.='<div class="church-admin-form-group"><input type="checkbox" name="show-address" id="show_address"  class="show_extras"  ';
        if(!empty( $fine_privacy['show-address']) )  {$content.=' checked ="checked" ';}
        $content.='/> '.esc_html( __("Show address",'church-admin' ) ).' </div>';


	$content.='<div class="church-admin-form-group"><input id="photo_permission" type="checkbox"  value="1"';
	if(!empty( $person->photo_permission) )$content.=' checked="checked"';
	$content.='/><label>'.esc_html( __('To show photos of me on the website and app','church-admin' ) ).'</label></div>';

	//add in extra privacy fields...



	$content.='<p><button class="button green action" data-tab="people_edit" id="save_people_edit" data-save=1 data-householdid="'.(int)$household_id.'" data-peopleid="'.(int)$people_id.'" data-next="'.esc_attr(sanitize_text_field(stripslashes($_REQUEST['next'])) ).'">'.esc_html( __('Save','church-admin' ) ).'</button> </p>';
	$content.='<p><button class="button red action" id="people_delete" data-tab="people_delete" data-householdid="'.(int)$household_id.'" data-peopleid="'.(int)$people_id.'">'.esc_html( __('Delete','church-admin' ) ).'</button> </p>';
	$content.='</div>';
	$output['content']=$content;
	
	update_option('church_admin_modified_app_content',time() );
	return $output;
}

function ca_app_change_member_type( $loginStatus)
{
	global $wpdb;
	if( $household_id!=$loginStatus->household_id && !church_admin_level_check('Directory',$loginStatus->user_id) )
	{
			$output['message']=__("You don't have permissions to edit that address",'church-admin');
			return $output;
	}
	$people_id=(int)sanitize_text_field(stripslashes($_REQUEST['people_id']));
	$member_type_id=(int)church_admin_sanitize($_REQUEST['member_type_id']);
	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET member_type_id="'.$member_type_id.'", last_update=NOW(), updated_by="'.(int)$loginStatus->user_id.'" WHERE people_id="'.(int)$people_id.'"');
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'message'=>esc_html( __('Member type changed','church-admin') ));
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
	church_admin_new_app_build_address_list( $loginStatus);
	return $output;
}

function ca_app_change_small_group( $loginStatus)
{
	global $wpdb;
	//church_admin_debug(print_r( $loginStatus,TRUE) );
	if( $household_id!=$loginStatus->household_id && !church_admin_level_check('Directory',$loginStatus->user_id) )
	{
			$output['message']=esc_html(__("You don't have permissions to edit that address",'church-admin'));
			return $output;
	}
	$people_id=(int)sanitize_text_field(stripslashes($_REQUEST['people_id']));
	$ID=(int)sanitize_text_field(stripslashes($_REQUEST['id']));
	church_admin_delete_people_meta(NULL,$people_id,'smallgroup');
	church_admin_update_people_meta( $ID,$people_id,'smallgroup',date('Y-m-d') );
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
	church_admin_new_app_build_address_list( $loginStatus);
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'message'=>esc_html( __('Smallgroup changed','church-admin')) );
	return $output;
}
/***************************
 * Acts of Courage
 **************************/
function ca_app_new_acts_of_courage()
{
	global $wpdb;

	$postsPerPage=10;
	if(!empty( $_REQUEST['page'] ) )  {$paged=(int)sanitize_text_field(stripslashes($_REQUEST['page']));}else{$paged=1;}
	if(!empty( $_REQUEST['paged'] ) )  {$paged=(int)sanitize_text_field(stripslashes($_REQUEST['paged']));}else{$paged=1;}
	$postCount=wp_count_posts('acts-of-courage');
	$maxPosts=$postCount->publish;
	$maxNoOfPages=ceil( $maxPosts/$postsPerPage);
	$posts_array = array();

	$args = array("post_type" => "acts-of-courage", "orderby" => "date", "order" => "DESC", "post_status" => "publish", "posts_per_page" => (int)$postsPerPage,'paged'=>$paged);
	$posts = new WP_Query( $args);

	if( $posts->have_posts() )
	{
		//church_admin_debug('Found some posts');
		$content='';
		while( $posts->have_posts() ):
			$posts->the_post();
            //church_admin_debug('Title '.get_the_title() );
			$thumbnail= wp_get_attachment_url(get_post_thumbnail_id() );
            $title=get_the_title();
			$link= get_the_permalink();
			$date= get_the_date();
			$ID=get_the_ID();
			$max_pages=$maxNoOfPages;
           
			$content.='<li class="ui-li-static ui-body-inherit newsItem tab-button"  data-id="'.(int)$ID.'" data-tab="single-post">';
			$content.='<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
				//show image if available
			if ( $thumbnail) {$content.='<img height="100" width="150" class="alignleft" src="'.esc_url($thumbnail).'">';}
			$content.='<h3>'.esc_html($title).'</h3><p>'.esc_html($date).'<br style="clear:left;" /></p></div></li>';
		endwhile;
    }
	$next=$paged+1;
	if( $next<=$maxNoOfPages)
	{
		
		$button='<button class="button tab-button" data-tab="posts" data-paged="'.(int)$next.'"';
		if( $cat_name)$button.=' data-catname="'.esc_html( $cat_name).'" ';
		$button.='>'.esc_html( __('Load more','church-admin' ) ).'</button>';
	}
	else
	{
		$button=null;
	}
	if(!empty( $cat_id ) )
	{
		$pageTitle=get_cat_name( $cat_id);
	}
	if ( empty( $pageTitle) )$pageTitle=__('Acts of Courage','church-admin');
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'page_title'=>$pageTitle,'view'=>'list','button'=>$button,'content'=>$content);
	return $output;
}
/**************************
 * Posts
 ************************/
function ca_app_new_posts($type,$loginStatus,$log)
{
	global $wpdb;
	if(empty($type))$type='posts';
	switch($type)
	{
		case 'bible-readings':
			$type='bible-readings'; 
		break;
		default:
			$type='post';
		break;
	}
	$postsPerPage=10;
	if(!empty( $_REQUEST['page'] ) )  {$paged=(int)sanitize_text_field(stripslashes($_REQUEST['page']));}else{$paged=1;}
	if(!empty( $_REQUEST['paged'] ) )  {$paged=(int)sanitize_text_field(stripslashes($_REQUEST['paged']));}else{$paged=1;}
	if(!empty( $_REQUEST['cat_name'] ) )
	{
	    $cat_name=sanitize_text_field(stripslashes($_REQUEST['cat_name']));
		$cat_name=str_replace("-category","",$cat_name);//sorts main menu!
		//church_admin_debug(' Cat name'.$cat_name);
        $idObj = get_category_by_slug( $cat_name);
	    $cat_id = $idObj->term_id;
        //church_admin_debug("Cat id is {$cat_id}");
		$cat_count = get_category( $idObj);
        //church_admin_debug("category count ".print_r( $cat_count,TRUE) );
        $maxPosts=$cat_count->count;
		if(!empty($log)){
			church_admin_app_log_visit( $loginStatus, esc_html( $cat_name),'church-admin');
		}
		
	}else
	{
		
		$postCount=wp_count_posts('post');
		$maxPosts=$postCount->publish;
		if(!empty($log)){
			church_admin_app_log_visit( $loginStatus, __('News','church-admin') );
		}
	}
	
	//if(defined('CA_DEBUG') )//church_admin_debug("Posts per page".$postsPerPage);
	//if(defined('CA_DEBUG') )//church_admin_debug("Max posts".$maxPosts);
	
	$maxNoOfPages=ceil( $maxPosts/$postsPerPage);
	//if(defined('CA_DEBUG') )//church_admin_debug("No of pages".$maxNoOfPages);
	$posts_array = array();

	$args = array("post_type" => $type, "orderby" => "date", "order" => "DESC", "post_status" => "publish", "posts_per_page" => $postsPerPage,'paged'=>$paged);
	if(!empty( $_REQUEST['page'] ) )$args['paged']=(int)sanitize_text_field(stripslashes($_REQUEST['page']));
	if(!empty( $cat_id) )  {$args['cat']=$cat_id;}
	church_admin_debug(print_r( $args,TRUE) );
	$posts = new WP_Query( $args);
	//church_admin_debug($posts);
	if( $posts->have_posts() )
	{
		church_admin_debug('Found some posts');
		$content='';
		while( $posts->have_posts() ):
			$posts->the_post();
            //church_admin_debug('Title '.get_the_title() );
			$thumbnail= wp_get_attachment_url(get_post_thumbnail_id() );
            $title=get_the_title();
			$link= get_the_permalink();
			$date= get_the_date();
			$ID=get_the_ID();
			$max_pages=$maxNoOfPages;
           
			$content.='<li class="ui-li-static ui-body-inherit newsItem tab-button"  data-id="'.(int)$ID.'" data-tab="single-post">';
			$content.='<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
				//show image if available
			if ( $thumbnail) {$content.='<img height="100" width="150" class="alignleft" src="'.esc_url($thumbnail).'">';}
			$content.='<h3>'.esc_html($title).'</h3><p>'.esc_html($date).'<br style="clear:left;" /></p></div></li>';
		endwhile;
    }
	$next=$paged+1;
	if( $next<=$maxNoOfPages)
	{
		
		$button='<button class="button tab-button" data-tab="posts" data-paged="'.(int)$next.'"';
		if( !empty($cat_name) ) {
			$button.=' data-catname="'.esc_html( $cat_name).'" ';
		}
		$button.='>'.esc_html( __('Load more','church-admin' ) ).'</button>';
	}
	else
	{
		$button=null;
	}
	if(!empty( $cat_id ) )
	{
		$pageTitle=get_cat_name( $cat_id);
	}
	if ( empty( $pageTitle) ){$pageTitle=esc_html( __('News', 'church-admin') );}
	if($type=='bible-readings'){$pageTitle=esc_html( __('Bible readings', 'church-admin' ) );}
	$output = array( 'page_title'=>$pageTitle,'view'=>'list','button'=>$button,'content'=>$content);
	if(!empty($loginStatus->token)){$output['token']=esc_html( $loginStatus->token );}
	return $output;
}
/***********************
 *  Single Post
 ************************/
function ca_app_new_single_post( $ID,$loginStatus)
{
	global $wpdb,$wp_embed;
	$thisPost=get_post( $ID);
	
	$user = get_userdata( $thisPost->post_author);
	switch($thisPost->post_type){
		case 'bible-readings': 
			$what = __('Bible reading','church-admin');
		break;
		case 'prayer-requests': 
			$what = __('Prayer request','church-admin');
		break;
		default:
			$what = __('Post','church-admin');
		break;

	}
	$logTitle = sprintf('%1$s - %2$s', $what, $thisPost->post_title) ;
	church_admin_app_log_visit( $loginStatus, $logTitle);


    $previous_post=get_previous_post( $thisPost->ID,TRUE,'',$thisPost->post_type);
    if(!empty( $previous_post) )$prevID=$previous_post->ID;
    //church_admin_debug(print_r( $previous_post,TRUE) );
    $next_post=get_next_post( $thisPost->ID,TRUE,'',$thisPost->post_type);
    if(!empty( $next_post) )$nextID=$next_post->ID;
    $links='<p>';
    if(!empty( $prevID) )$links.='<button class="newsItem button" id="'.(int)$prevID.'" data-id="'.(int)$prevID.'" data-tab="single-post">'.esc_html( __('Previous','church-admin' ) ).'</button> &nbsp;';
    if(!empty( $nextID) )$links.='<button class="newsItem button" id="'.(int)$nextID.'" data-tab="'.(int)$nextID.'" data-tab="single-post">'.esc_html( __('Next','church-admin' ) ).'</button>';
    $links.='</p>';
	//handle blocks need to use the filter for embed blocks to work	
	if( has_blocks( $thisPost->post_content ) )
	{
		remove_filter( 'the_content', 'wpautop' );
		$content=apply_filters("the_content", do_blocks( $thisPost->post_content ));
	}
	else
	{
		$content = do_shortcode($thisPost->post_content);
	}
	$content.='<p>'.$links.'</p>';


	$author=get_the_author_meta('display_name',$thisPost->post_author);
	$content.='<p>'.esc_html( sprintf(__('Posted by %1$s on %2$s','church-admin' ) ,$author,mysql2date(get_option('date_format'),$thisPost->post_date) )).'</p>';
	
	$args=array('post_id'=>(int)$thisPost->ID,'orderby'=>'comment_date','order'=>'ASC');
	
	$comments=get_comments( $args);
	$content.='<h3>'.esc_html( __("Comments",'church-admin' ) ).'</h3>';
	$content.='<ul id="list" class="ui-listview" >';
	if(!empty( $comments) )
	{
		
		foreach( $comments AS $key=>$comment)
		{
			$content.='<li class="ui-li-static">';
			$content.=$comment->comment_content.'<br>';
			if(!empty( $comment->comment_author) )
			{
				$content.='<em>'.esc_html(sprintf(__('%1$s on %2$s','church-admin' ) ,esc_html( $comment->comment_author),mysql2date(get_option('date_format').' '.get_option('time_format'),$comment->comment_date) )).'</em>';
			}
			
			$content.='</li>';
		}
		
	}else{$content.='<li class="ui-li-static">'.esc_html( __('No comments yet','church-admin' ) ).'</li>';}
	if(comments_open( $thisPost->ID) )
	{
		$content.='<li class="ui-li-static">';
		$content.='<h4>'.esc_html( __('Leave your reply','church-admin' ) ).'</h4><textarea id="my-comment"></textarea><br><button class="action button green" data-tab="comment" data-id="'.(int)$thisPost->ID.'">'.esc_html( __('Reply','church-admin' ) ).'</button></li>';
	}
	$content.='</ul>';
	//church_admin_debug(print_r( $comments,true) );
	$output = array('page_title'=>$thisPost->post_title,'view'=>'html','content'=>$content);
	if(!empty($loginStatus->token)){
		$output['token']=esc_html($loginStatus->token);
	}
	return $output;

}
/****************
 * Prayer
 ****************/

function ca_app_new_prayer_send( $loginStatus)
{
	global $wpdb;
	if(!defined('CA_DEBUG') )define('CA_DEBUG',TRUE);
	church_admin_debug('********** church_admin_prayer_send ************');
	church_admin_debug('$_REQUEST');
	church_admin_debug(print_r( $_REQUEST,TRUE) );
	church_admin_debug('$loginStatus');
	church_admin_debug(print_r( $loginStatus,TRUE) );

	if ( empty( $_REQUEST['content'] )||empty( $_REQUEST['title'] ) )
	{
		$out=array('message'=>__("Empty prayer request",'church-admin') );
		return $out;
	}	
	$title = wp_strip_all_tags(stripslashes( $_REQUEST['title'] ) );
	$email_title = __('Prayer request - ','church-admin').$title;
	$content = sanitize_textarea_field(stripslashes( $_REQUEST['content'] ) );
	$args=array('post_content'=>$content,'post_title'=>$title,'post_status'=>'draft','post_type'=>'prayer-requests');
	$ID=$wpdb->get_var('SELECT ID FROM '.$wpdb->posts.' WHERE post_content="'.esc_sql( $args['post_content'] ).'" AND post_title="'.esc_sql( $args['post_title'] ).'" AND post_type="prayer-requests"');
	if(!empty( $ID) )
	{
		$out=array('message'=>__("Prayer request already posted",'church-admin') );
		return $out;
	}
	//if(user_can( $user_id, 'manage_options' ) )$args['post_status']='publish';
        if(!empty( $loginStatus) && church_admin_level_check('Prayer',$loginStatus->user_id) )
        {
            if(defined("CA_DEBUG") )//church_admin_debug("User doesn't need moderation {$loginStatus->user_id}");
            $args['post_status']='publish';
			$message=__('Your prayer request has been published','church-admin');
        }
		
		$args['post_author']=(int)$loginStatus->user_id;
		//church_admin_debug('$args');
		//church_admin_debug(print_r( $args,TRUE) );
		$post_id = wp_insert_post( $args);

		if(!is_wp_error( $post_id) )  {
  			//the post is valid

  			if(!user_can( $loginStatus->user_id, 'manage_options' ) )
            {
                $prm= get_option('prayer-request-moderation');
                if(empty( $prm) )$prm=get_option('church_admin_default_from_email');
                //wp_mail( $prm,__('Prayer Request Draft','church-admin' ) ,__('A draft prayer request has been posted. Please moderate','church-admin') );
				church_admin_email_send($prm,__('Prayer Request Draft','church-admin' ),__('A draft prayer request has been posted. Please moderate','church-admin'),null,null,null,null,null,TRUE);
				$message=__('You prayer request will be published after moderation','church-admin');
				//push message admin to approve
				$pushTokens=array();
				$prayer_request_people_ids=get_option('church_admin_prayer_request_receive_push_to_admin');
				//church_admin_debug('Push token');
				if(!empty( $prayer_request_people_ids) )
				{
					foreach( $prayer_request_people_ids AS $people_id)
					{
						$pushTokens[]=$wpdb->get_var('SELECT pushToken FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'" AND pushToken!=""');
					}
				}
                
                church_admin_debug( $wpdb->last_query);
                if(!empty( $pushToken) )
                {
                    church_admin_debug('Sending to '.$pushToken);
                    /*****************************
                    * Send push message 
                    ******************************/
                    
                    
                    $pushMessage=$dataMessage=__('New prayer request for moderating',"church-admin");
                    $pushType='prayer';
                    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/push.php');
                    church_admin_filtered_push( $pushMessage,$pushTokens,esc_html(__('Moderation required','church-admin' ) ),$dataMessage,$pushType,NULL);

                }

            }else
			{

				/**************************************
				 * App push and Email send
				 *************************************/
				church_admin_debug('*** attempt push ****');
				$user = get_userdata($loginStatus->user_id,);
				//app push
				$myTokens=$wpdb->get_results('SELECT a.people_id,a.pushToken,b.meta_date FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.active=1 AND a.pushToken!="" AND a.people_id=b.people_id AND b.meta_type="prayer-requests-notifications"');
                church_admin_debug($wpdb->last_query);
				$pushTokens=array();
				if(!empty( $myTokens) )
				{
					church_admin_debug('got some DB data');
					foreach( $myTokens AS $myToken)if(!in_array( $myToken->pushToken,$pushTokens) )  {$pushTokens[]=$myToken->pushToken;}
					church_admin_debug($pushTokens);
					church_admin_send_push('tokens','prayer-requests',$pushTokens,'Our Church App',$title,get_option('blogname'));
				}

				
				//email send
				church_admin_debug('*** attempt email ****');
				$sql='SELECT DISTINCT a.first_name,a.last_name,a.people_id,a.email FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="prayer-requests"  AND a.email!="" AND email_send!=0 AND gdpr_reason!=""';
                church_admin_debug($sql);
                $results=$wpdb->get_results( $sql);
                if(!empty($results)){
					church_admin_debug('got DB data');
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
				}
			}

		}
		//church_admin_debug('prepare output');
		$output=ca_app_new_prayer( $loginStatus);
		$output['message']=$message;
		//church_admin_debug($output);
		return $output;
}
function ca_app_new_approve_prayer( $loginStatus)
{
	global $wpdb;
	$ID=!empty($_REQUEST['id'])?sanitize_text_field(stripslashes($_REQUEST['id'])):null;
	if(empty($ID)||!church_admin_int_check($ID)){
		return FALSE;
	}
	$wpdb->query('UPDATE '.$wpdb->posts.' SET post_status="publish" WHERE ID="'.(int)$ID.'"');
	$output=ca_app_new_prayer( $loginStatus);
	$output['message']=__('Prayer request approved','church-admin');
	return $output;
}
function ca_app_new_reject_prayer( $loginStatus)
{
	global $wpdb;
	if(!defined('CA_DEBUG') )define('CA_DEBUG',TRUE);
	$ID=!empty($_REQUEST['id'])?sanitize_text_field(stripslashes($_REQUEST['id'])):null;
	if(empty($ID)||!church_admin_int_check($ID)){
		return FALSE;
	}
	//church_admin_debug(print_r( $_REQUEST,TRUE) );
	wp_delete_post( (int)$ID);
	$output=ca_app_new_prayer( $loginStatus);
	$output['message']=__('Prayer request deleted','church-admin');
	return $output;
}
function ca_app_new_prayer( $loginStatus)
{
	global $wpdb;
	if(!empty( $_REQUEST['paged'] ) )  {$paged=(int)$_REQUEST['paged'];}else{$paged=0;}
	$prayerPosts=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->posts.' WHERE post_type="prayer-requests" AND post_status="publish"');
	//church_admin_debug('Prayer posts '.$prayerPosts);
	$max_pages=ceil( $prayerPosts/10);
	//church_admin_debug('Pages '.$max_pages);
	if(!empty( $loginStatus->token ) )church_admin_app_log_visit( $loginStatus, esc_html(__('Prayer Request','church-admin' ) ),$loginStatus);
	church_admin_app_log_visit( $loginStatus, __('Prayer Request','church-admin') );
	$private=get_option('church-admin-private-prayer-requests');
	if( $private)
	{
		$token = !empty($loginStatus->token)?$loginStatus->token:null;
		if ( empty( $token ) )
		{//private but no token
			$output = array( 'token'=>esc_html( $token ),'error'=>'login required');

		}
		else
		{//private and check token
			$sql='SELECT user_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql($token ).'"';
			$result=$wpdb->get_var( $sql);
			if ( empty( $result) )
			{//private and no login

				$output = array( 'token'=>esc_html( $token ),'error'=>'login required');
			}
			else
			{//private and logged in
				$content=ca_new_prayer_reqs( $paged,$loginStatus);
			}
		}
	}
	else
	{
			//not private
			$content=ca_new_prayer_reqs( $paged,$loginStatus);
	}
	$next=$paged++;
	if( $next<$max_pages)
	{
		
		$button='<button class="button tab-button" data-tab="prayer" data-paged="'.(int)$next.'">'.esc_html( __('Load more','church-admin' ) ).'</button>';
	}
	else
	{
		$button=null;
	}
	$out=array('page_title'=>esc_html( __('Prayer Requests','church-admin' ) ),'view'=>'html','button'=>$button,'content'=>$content);
	return $out;
}

function ca_new_prayer_reqs( $paged,$loginStatus)
{
	global $wpdb;
	$postsPerPage=10;
	$allowed_html = [
		'iframe' => [
			'src' => [],
			'allow' => [],
			'width' => [],
			'height' => [],
			'frameborder' => [],
			'allowFullScreen' => []
		], 
		'img'=>[
			'src'=>[],
			'class'=>[]
		],
		'p' =>['br'=>[]],
		'a'      => [
			'href'  => [],
			'title' => [],
		],
		'br'     => [],
		'em'     => [],
		'strong' => [],
		];
	$content='<div class="request"><p><input id="prayer-title" type="text" placeholder="'.esc_html( __("Title",'church-admin' ) ).'"  autocorrect="off" autocapitalize="none" /></p>';
	$content.='<p><textarea id="prayer-request" autocorrect="off" data-text="prayer-request" placeholder="'.esc_html( __("Your prayer request",'church-admin' ) ).'"></textarea></p>';
	$content.='<p><button class="button action" data-tab="send-prayer" > '.esc_html( __("Send",'church-admin' ) ).'</button> </p></div>';
	$content.='<h2>'.esc_html( __("Prayer requests",'church-admin' ) ).'</h2>';
	$content.='<ul class="prayer">';
	
	if(!empty($loginStatus->user_id) && church_admin_level_check('Prayer',$loginStatus->user_id)){
		$prayerPosts=$wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type="prayer-requests" AND (post_status="publish" OR post_status="draft") ORDER BY post_date DESC LIMIT '.(int)$paged*$postsPerPage.','.(int)$postsPerPage);
	}
	else{
		$prayerPosts=$wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type="prayer-requests" AND (post_status="publish") ORDER BY post_date DESC LIMIT '.(int)$paged*$postsPerPage.','.(int)$postsPerPage);
	}
	foreach( $prayerPosts AS $prayer)
	{
		if( $prayer->post_status=='draft'&& church_admin_level_check('Prayer',$loginStatus->user_id))
		{
			$content.='<li class="prayerItem" style="color:#AAA!important"><h3>' .esc_html(sprintf(__('Moderate "%1$s"','church-admin' ) ,esc_html( $prayer->post_title) )). '</h3><p><em>' .esc_html( __('Posted','church-admin' ) ).': '.mysql2date(get_option('date_format'),$prayer->post_date).'</em></p><p><em>'.esc_html( __("By:",'church-admin' ) ).' '.church_admin_formatted_name_from_user( $prayer->post_author).'</em></p><div>'. wpautop( $prayer->post_content).'</div>';
			$content.='<p><button class="button green action" data-tab="approve-prayer" data-id="'.(int)$prayer->ID.'">'.esc_html( __("Approve",'church-admin' ) ).'</button></p>';
			$content.='<p><button class="button red action" data-tab="reject-prayer" data-id="'.(int)$prayer->ID.'">'.esc_html( __("Reject",'church-admin' ) ).'</button></p>';
			$content.='</li>';
		}
		else
		{
			$content.='<li class="prayerItem"><h3>'.esc_html( $prayer->post_title ).'</h3><p><em>'.esc_html( __('Posted','church-admin' ) ).': '.mysql2date(get_option('date_format'),$prayer->post_date) .'</em></p><p><em>'.esc_html( __("By:",'church-admin' ) ).' '.esc_html( church_admin_formatted_name_from_user( $prayer->post_author) ) .'</em></p><div>'. wp_kses(wpautop( $prayer->post_content ),$allowed_html ).'</div>';
			$args=array('post_id'=>(int)$prayer->ID,'orderby'=>'comment_date','order'=>'ASC');
			$comments=get_comments( $args);
			$content.='<h4>'.esc_html( __("Comments",'church-admin' ) ).'</h4>';
			$content.='<ul id="list" >';
			if(!empty( $comments) )
			{
				
				foreach( $comments AS $key=>$comment)
				{
					$content.='<li>';
					$content.=$comment->comment_content.'<br>';
					if(!empty( $comment->comment_author) )
					{
						$content.='<em>'.esc_html( sprintf(__('%1$s on %2$s','church-admin' ) , $comment->comment_author,mysql2date(get_option('date_format').' '.get_option('time_format'),$comment->comment_date) ) ).'</em>';
					}
					
					$content.='</li>';
				}
				
			}else{$content.='<li>'.esc_html( __('No comments yet','church-admin' ) ).'</li>';}
			if(comments_open( $prayer->ID) )
			{
				$content.='<li>';
				$content.='<h4>'.esc_html( __('Leave your comment','church-admin' ) ).'</h4><p><textarea id="my-comment"></textarea></p><p><button class="action button green" data-tab="comment" data-id="'.(int)$prayer->ID .'">'.esc_html( __('Reply','church-admin' ) ).'</button></p></li>';
			}

			$content.='</ul></li>';
		}

	}


	return $content;
}

/******************
 * Media
 *****************/
function ca_app_new_media($loginStatus)
{
	global $wpdb;
	$button='';
	$next=2;
	$max=$wpdb->get_var('SELECT COUNT(file_id) FROM '.$wpdb->prefix.'church_admin_sermon_files');
	$pages=ceil( $max/10);
	$paged=!empty($_REQUEST['paged'])?sanitize_text_field(stripslashes($_REQUEST['paged'])):1;
	if(!empty( $paged )&& $paged >1)
	{
		$offset=10*(int)$paged-10;
		
		if( $paged<$pages)
		{
			$next=$paged+1;
		}
		else $next=false;

	}else{$offset=0;}
	$upload_dir = wp_upload_dir();
	$path=$upload_dir['basedir'].'/sermons/';
	$url=$upload_dir['baseurl'].'/sermons/';
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),);
	
	if ( empty( $_REQUEST['id'] ) )
	{
		church_admin_app_log_visit( $loginStatus, __('Media','church-admin') );
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files ORDER BY pub_date DESC LIMIT '.$offset.',10';
		//church_admin_debug( $sql);
		$results=$wpdb->get_results( $sql);
		$content='';
		if(!empty( $results) )
		{
			foreach( $results AS $row)
			{
				$content.='<li class="action mediaItem" id="'.(int)$row->file_id.'" data-tab="media" data-id="'.(int)$row->file_id.'" >';
				$content.='<div  class="ui-btn ui-btn-icon-right ui-icon-carat-r">';
				$content.='<h3>'.esc_html( $row->file_title).'</h3>';
				if(!empty( $row->description) )$content.='<p>'.esc_html( $row->description).'</p>';
				$content.='<p>'.esc_html( $row->speaker).' - '.mysql2date(get_option('date_format'),$row->pub_date).'</p>';
				$content.= '</div></li>';
			}
			if( $next)$button='<button class="button action" data-tab="media" data-paged="'.(int)$next.'">'.esc_html( __('Older Sermons','church-admin' ) ).'</button>';
		}else{
			if(empty($paged)){
				$output['content']=__('No sermons yet','church-admin');
			}else{$output['message']=__('No more sermons','church-admin');}
		}
	}
	else
	{
		//grab one sermon
		$ID=!empty($_REQUEST['id'])?sanitize_text_field(stripslashes($_REQUEST['id'])):null;
		if(!empty($ID) && church_admin_int_check($ID)){
			$row=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_sermon_files WHERE file_id="'.(int)$ID.'"');
		}
		$token = !empty($loginStatus->token)?$loginStatus->token:null;
		if ( empty( $row) )
		{
			$button='<button class="action" data-tab="media">'.esc_html( __('Back to sermons','church-admin' ) ).'</button>';
			$output = array( 'token'=>esc_attr( $token ),'message'=>esc_html( __('No sermon found','church-admin' ) ),'button'=>$button);
			return $output;
		}
		else
		{
			  $content='';
                   
				$file=NULL;
                if(!empty( $row->video_url) )  {$video=church_admin_generateVideoEmbedUrl( $row->video_url);}else $video=FALSE;
                if(!empty( $row->file_name) )$file=$url.$row->file_name;
                if(!empty( $row->external_file) ){
						//2023-09-17 Google drive broken so fix here..
						$row->external_file = str_replace('export=download','export=open',$row->external_file );	
					$file=$row->external_file;
				}

				
                $nonce=wp_create_nonce("church_admin_mp3_play");
                $sermonlink=church_admin_find_sermon_page();
                $title=!empty($row->title)?str_replace('"','',$row->file_title):'';
                
				church_admin_app_log_visit( $loginStatus, sprintf(__('Media - %1$s','church-admin'),$title ));
                if(!empty( $sermonlink) )$share='<p class="social-share"><a target="_blank" class="ca-share"  href="https://www.facebook.com/sharer/sharer.php?u='.$sermonlink.'?sermon='.$row->file_slug.'"><svg class="ca-share-icon" style="width:50px;height:50px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20" /><g><path class="social-share-path" d="M2.89 2h14.23c.49 0 .88.39.88.88v14.24c0 .48-.39.88-.88.88h-4.08v-6.2h2.08l.31-2.41h-2.39V7.85c0-.7.2-1.18 1.2-1.18h1.28V4.51c-.22-.03-.98-.09-1.86-.09-1.85 0-3.11 1.12-3.11 3.19v1.78H8.46v2.41h2.09V18H2.89c-.49 0-.89-.4-.89-.88V2.88c0-.49.4-.88.89-.88z" /></g></svg></a> &nbsp; <a  class="ca-share"  target="_blank"  href="https://twitter.com/intent/tweet?text='.$row->file_title.' '.$sermonlink.'?sermon='.$row->file_slug.'"><svg class="ca-share-icon" width="50px" height="50px" viewBox="0 0 50 50" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;"> <g transform="matrix(0.149844,0,0,0.142713,2.84961,4.51512)"><path d="M178.57,127.15L290.27,0L263.81,0L166.78,110.38L89.34,0L0,0L117.13,166.93L0,300.25L26.46,300.25L128.86,183.66L210.66,300.25L300,300.25M36.01,19.54L76.66,19.54L263.79,281.67L223.13,281.67" style="fill:rgb(223,230,232);fill-rule:nonzero;"/>
    </g></svg></a>&nbsp;<a style="text-decoration:none" href="mailto:?subject='.$row->file_title.'&amp;body='.$sermonlink.'?sermon='.$row->file_slug.'"><svg class="ca-share-icon"  style="width:50px;height:50px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20" /><g><path class="social-share-path" d="M3.87 4h13.25C18.37 4 19 4.59 19 5.79v8.42c0 1.19-.63 1.79-1.88 1.79H3.87c-1.25 0-1.88-.6-1.88-1.79V5.79c0-1.2.63-1.79 1.88-1.79zm6.62 8.6l6.74-5.53c.24-.2.43-.66.13-1.07-.29-.41-.82-.42-1.17-.17l-5.7 3.86L4.8 5.83c-.35-.25-.88-.24-1.17.17-.3.41-.11.87.13 1.07z" /></g></svg></a></p>';


				//title
				$content.='<li><h3>'.esc_html( $title).'</h3>';
				$content.='<p>'.esc_html( $row->speaker).' - '.mysql2date(get_option('date_format'),$row->pub_date).'</p>';
				//video
				if(!empty( $video) )$content.='<div><div style="position:relative;padding-top:56.25%;"><iframe class="ca-video" style="position:absolute;top:0;left:0;width:100%;height:100%;" src="'.$video['embed'].'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
				//audio
				if(!empty( $file) )$content.='<p><audio class="sermonmp3" src="'.esc_url( $file).'"  preload="auto" controls></audio></p>';
				//audio embed
				if(!empty( $row->embed_code) ){
					 // WP's default allowed tags
					 global $allowedtags;

					 // allow iframe only in this instance
					 $iframe = array( 'iframe' => array(
										 'src' => array (),
										 'allow' => array(),
										 'width' => array (),
										 'height' => array (),
										 'frameborder' => array(),
										 'allowFullScreen' => array() // add any other attributes you wish to allow
										 ) );
 
					 $allowed_html = array_merge( $allowedtags, $iframe );
 
					 // Sanitize user input.
					 
					 $content.= wp_kses( $row->embed_code, $allowed_html );

				}
                //description
				if(!empty( $row->description) )$content.='<p>'.esc_html( $row->description).'</p>';
				//verses

				if(!empty( $row->bible_texts) )
				{
					$pass=array();
					$version=get_option('church_admin_bible_version');
					$passages=explode(",",$row->bible_texts);
					if(!empty( $passages)&&is_array( $passages) )
					{
						foreach( $passages AS $passage)$pass[]='<a href="https://www.biblegateway.com/passage/?search='.urlencode( $passage).'&version='.$version.'&interface=print" target="_blank">'.esc_html( $passage).'</a>'."\r\n";

						$content.='<p>'.esc_html( __('Scriptures','church-admin' ) ).':&nbsp;</td><td>'.implode(", ",$pass).'</p>';
					}
				}
				//share
				if(!empty( $row->transcript) )$content.='<p><a  rel="nofollow" href="'.site_url().'?ca_download=sermon-notes&amp;file_id='.(int)$row->file_id.'">'.esc_html( __('PDF notes','church-admin' ) ).'</a></p>';
				if(!empty( $share) )$content.=$share;
				$button='<button class="button action" data-tab="media">'.esc_html( __('Back to sermons','church-admin' ) ).'</button>';
		}
	}
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),
	'page_title'=>esc_html( __('Sermons','church-admin' ) ),
	'view'=>'list','content'=>$content,'button'=>$button);
	return $output;
}

/************************
 * CONTACT FORM MESSAGES
 **********************/
function ca_app_new_contact_messages( $loginStatus)
{
	
	$output = array( 'token'=>esc_html( $loginStatus->token ),'page_title'=>esc_html( __('Contact form messages','church-admin' ) ),'view'=>'list');

	$settings=get_option('church_admin_contact_form_settings');
	if ( empty( $settings) || $settings['pushToken']!=$loginStatus->people_id)
	{
		//no user access
		$output['content']=__("You don't have permission for this content",'church-admin');
		return $output;
		
	}
	$output['content']=ca_app_new_contact_message_output();
 return $output;

}

function ca_app_new_contact_message_output()
{
	global $wpdb;
	$messages=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_contact_form ORDER BY post_date DESC');
	if ( empty( $messages) )
	{
		//no messages
		$content='<li  class="contact ui-li-static ui-li-divider" >'.esc_html( __("No contact form messages",'church-admin' ) ).'</li>';
		return $content;
		
	}
	/**********************************
	 *  Build messages output
	 *********************************/
	
	$content='';
	foreach( $messages AS $message)
	{
		
		$content.='<li class="contact ui-li-static ui-li-divider" id="message'.(int)$message->contact_id.'"><p><strong>'.esc_html( $message->subject).'</strong> '.mysql2date(get_option('date_format'),$message->post_date).'</p>';
		$content.='<p>'.esc_html( $message->name).' <a href="'.esc_url('mailto:'.$message->email.'&subject='.esc_html( __('Re:','church-admin' ) ).esc_html( $message->subject) ).'">'.esc_html( $message->email).'</a></p>';
		if(!empty( $message->phone) )$content.='<p><a href="'.esc_url('tel:'.$message->phone).'">'.esc_html( $message->phone).'</a></p>';
		$content.='<p>'.esc_html( $message->message).'</p>';
		if(!empty( $row->transcript) )$content.= '<p><a  rel="nofollow" href="'.site_url().'?ca_download=sermon-notes&amp;file_id='.(int)$row->file_id.'">PDF</a></p>'; 
		$content.='<p><button class="action button" data-tab="delete-contact-message" data-id="'.(int)$message->contact_id.'">'.esc_html( __('Delete message','church-admin' ) ).'</button>';
		$content.='</li>';

	}
	
	
	return $content;
}

function ca_app_new_delete_contact_message( $loginStatus)
{
	//church_admin_debug( $_REQUEST);
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),);
	global $wpdb;
	$settings=get_option('church_admin_contact_form_settings');
	if ( empty( $settings) || $settings['pushToken']!=$loginStatus->people_id)
	{
		//no user access
		$output['content']=__("You don't have permission for this content",'church-admin');
		return $output;
		
	}
	$ID=!empty($_REQUEST['id'])?sanitize_text_field(stripslashes($_REQUEST['id'])):null;
	
	if ( empty( $ID ) )
	{
		$output['message']=__("No message selected for deletion",'church-admin');
		$output['content']=ca_app_new_contact_message_output();
		$output['view']='list';
		$output['listaction']='replace';
		return $output;
	}
	else
	{
		if(church_admin_int_check($ID)){
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_contact_form WHERE contact_id="'.(int)$_REQUEST['id'].'"');
			$output['message']=__('Message deleted','church-admin');
			$output['content']=ca_app_new_contact_message_output();
			$output['view']='list';
			$output['listaction']='replace';
		}
		else
		{
			$output['message']=__('An error occurred','church-admin');
		}
	}
	return $output;
}

/************************
 * Forgotten Password
 **********************/
function ca_app_new_forgotten_password()
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Password reset','church-admin' ) ),'view'=>'html');
	$login = trim( sanitize_text_field(stripslashes($_REQUEST['user_login'] )));
	church_admin_debug("Forgotten password login ".$login);
	$user_data = get_user_by('login', $login);
	if ( empty( $user_data) )$user_data = get_user_by('email', $login);
	
	if ( empty( $user_data) )  {
		$output = array( 'token'=>esc_html( $token ),'message'=>'<p>User details not found, please try again</p>');
		return $output;
	}
	else
	{
		//church_admin_debug("User date".print_r( $user_data,true) );
		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key = get_password_reset_key( $user_data );
		$message = '<p>Someone has requested a password reset for the following account at '. "\r\n\r\n";
		$message .= network_home_url( '/' ) . "</p>\r\n\r\n";
		$message .= '<p>'.esc_html(sprintf(__('Username: %s'), $user_login) ) . "</p>\r\n\r\n";
		$message .= '<p>If this was a mistake, just ignore this email and nothing will happen.</p>' . "\r\n\r\n";
		$message .= '<p>To reset your password, visit the following address:</p>' . "\r\n\r\n";
		$message .= '<p><a href="' . site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login), 'login') . '">'.esc_html( __("Reset",'church-admin' ) ).'</a></p>'."\r\n";
		/*
			* The blogname option is escaped with esc_html on the way into the database
			* in sanitize_option we want to reverse this for the plain text arena of emails.
			*/
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$title = sprintf( __('[%s] Password Reset'), $blogname );
		//$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );
		//church_admin_debug( $message);
		add_filter( 'wp_mail_from_name','church_admin_from_name' );
		add_filter( 'wp_mail_from', 'church_admin_from_email');
		add_filter('wp_mail_content_type','church_admin_email_type');
		if ( $message)
		{	
			church_admin_email_send($user_email,wp_specialchars_decode( $title ),$message,null,null,null,null,null,TRUE);
			$output['message']=__('Password email has been sent to your registered email address','church-admin');
		}
		else
		{
			$error=esc_html(sprintf(__('Password reset email failed to send to %1$s','church-admin' ) ,$user_email));
		
			$output['message']=$error;
		
		}
		remove_filter( 'wp_mail_from_name','church_admin_from_name' );
		remove_filter( 'wp_mail_from', 'church_admin_from_email');
		remove_filter('wp_mail_content_type','church_admin_email_type');
	}
	$output['content']='<p><button class="button action" data-tab="account">'.esc_html( __('Back to account','church-admin' ) ).'</button></p>';
	return $output;
}

/************************
 * Schedule
 **********************/



function ca_app_new_rota( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	church_admin_debug('*** ca_app_new_rota ***');
	church_admin_debug('$_REQUEST');
	church_admin_debug(print_r($_REQUEST,TRUE));
	$output = array( 'page_title'=>esc_html( __('Schedule','church-admin' ) ),'view'=>'html');
	if(!empty($loginStatus)){
		$output['token']=esc_html( $loginStatus->token );
	}

	//require login
	if(empty($loginStatus->people_id))
	{
		church_admin_debug('Not logged in');
		$output=ca_app_new_login_form('rota');
		return $output;

	}else{church_admin_debug('login check passed');}
	//check for services
	$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services');
	if(empty($services)){
		$output['content']=__('No services have been set up yet','church-admin');
		return $output;
	}else{church_admin_debug('services check passed');}
	//check for rota jobs
	$rotaJobs = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings');
	if(empty($rotaJobs)){
		$output['content']=__('No schedule jobs have been set up yet','church-admin');
		return $output;
	}else{church_admin_debug('schedule jobs check passed');}
	$rotas = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_date>=CURDATE( )');
	if(empty($rotaJobs)){
		$output['content']=__('No current or future schedule dates have been set up yet','church-admin');
		return $output;
	}else{church_admin_debug('schedules check passed');}
	
	
	$time_start = microtime(true); 
	//church_admin_debug('***** ca_app_new_rota'. $time_start.' ******');
	
	
	

	$rota_id=!empty( $_REQUEST['rota_id'] )?(int)sanitize_text_field(stripslashes($_REQUEST['rota_id'])) :null;
	//check rota_id exists
	$check=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_id="'.(int)$rota_id.'" LIMIT 1');
	if(empty($check)){$rota_id=null;}
	$rotaDropdown='';
	//church_admin_debug($_REQUEST);
	if(!empty($_REQUEST['service_id']) && !empty($_REQUEST['rota_date'])){
		church_admin_debug('From calendar page');
		$from_calendar=1;
		$rota_date=church_admin_sanitize($_REQUEST['rota_date']);
		$service_id = church_admin_sanitize($_REQUEST['service_id']);

		$sql='SELECT a.*,b.service_name,a.rota_date FROM '.$wpdb->prefix.'church_admin_new_rota  a,'.$wpdb->prefix.'church_admin_services b WHERE a.rota_date="'.esc_sql($rota_date).'" AND a.service_id="'.(int)$service_id.'" AND a.service_id =b.service_id';
		//church_admin_debug( $sql);
		$selectedService=$wpdb->get_row( $sql);
	}
	else
	{
		church_admin_debug('From rota page');
		//grab next 12 meetings
		$from_calendar=0;

		$sql='SELECT a.rota_date, a.rota_id,b.service_name,a.service_time,c.venue FROM '.$wpdb->prefix.'church_admin_new_rota a LEFT JOIN '.$wpdb->prefix.'church_admin_services b ON a.service_id=b.service_id  LEFT JOIN '.$wpdb->prefix.'church_admin_sites c ON b.site_id=c.site_id WHERE a.rota_date >= CURDATE( ) AND b.active=1 GROUP BY a.service_id, a.rota_date ORDER BY rota_date ASC LIMIT 36';
		church_admin_debug($sql);
		$results=$wpdb->get_results( $sql);
		church_admin_debug($results);
		if(empty( $results) )
		{
			$output['content']='<p>'.__('No schedule data found','church-admin').'</p>';
			return $output;
		}
		else
		{
			church_admin_debug('Service picker');
			$rotaDropdown.='<h3>'.esc_html( __('Pick Service','church-admin' ) ).'</h3>';
			$rotaDropdown.='<p><select id="rota_id">';
			foreach( $results AS $row)
			{

				$rotaInstance=mysql2date("j M",$row->rota_date).' '.mysql2date(get_option('time_format'),$row->service_time).' '.esc_html( $row->service_name);
				$rotaDropdown.='<option value="'.(int)$row->rota_id.'" '.selected( $rota_id,$row->rota_id,FALSE).'>'.$rotaInstance.'</option>';
			}
			$rotaDropdown.='</select></p>';
			$rotaDropdown.='<p><button class="action button green" data-tab="rota">'.esc_html( __('Pick service','church-admin' ) ).'</button></p>';
			//Pick first service if none selected
			if ( empty( $rota_id) )$rota_id=(int)$results[0]->rota_id;
			$sql='SELECT a.*,b.service_name,a.rota_date FROM '.$wpdb->prefix.'church_admin_new_rota  a,'.$wpdb->prefix.'church_admin_services b WHERE a.rota_id="'.(int)$rota_id.'" AND a.service_id =b.service_id';
			//church_admin_debug( $sql);
			$selectedService=$wpdb->get_row( $sql);
		}
		
		
	}
	church_admin_debug($wpdb->last_query);
	church_admin_debug('Selected service...');
	church_admin_debug(print_r( $selectedService,TRUE) );
	//workout which rota jobs are required
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
	$requiredRotaJobs=$rotaDates=array();
	foreach( $rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize( $rota_task->service_id);
		if(is_array( $allServiceID)&&in_array( $selectedService->service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
	}
	
	$rotaOutput='';
	if(!empty($loginStatus) && empty($from_calendar))
	{
		$rotaOutput.='<p><button class="action button" data-tab="my-rota">'.esc_html( __('My schedule','church-admin' ) ).'</button><p>';
		$rotaOutput.='<p><button class="action button" data-tab="not-available">'.esc_html( __('My availability','church-admin' ) ).'</button><p>';
	}
	if ( empty( $selectedService->rota_date) )return $output['content']=__('No schedule date selected','church-admin');

	$rotaOutput.='<h3>'.esc_html( $selectedService->service_name.' '.mysql2date(get_option('date_format'),$selectedService->rota_date) ).'</h3>';
	if(!empty( $loginStatus) &&church_admin_level_check('Rota',$loginStatus->user_id) )
	{
		//ADMIN USER
		foreach( $requiredRotaJobs AS $rota_task_id=>$value)
		{
			//use esc_textarea not to ruin htmlentities
			$people=esc_textarea(church_admin_rota_people( $selectedService->rota_date,$rota_task_id,$selectedService->service_id,'service') );
			//church_admin_debug('Rota task ID'.$rota_task_id.' People: "'.$people.'"');
			if ( empty( $people)||strlen( $people)<50)
			{
				
				$rotaOutput.='<div class="church-admin-form-group"><label>'.esc_attr( $value).'</label><input class="rota church-admin-form-control"';
				if ( empty( $people) )	$rotaOutput.=' style="border:1px solid red" ';
				$rotaOutput.=' data-taskid="'.(int)$rota_task_id.'" ';
				if(!empty( $people) )	{$rotaOutput.=' value="'.$people.'" ';}
				$rotaOutput.='/></div>';

			}
			else
			{
				//use textarea where more than 50 charas
				$rotaOutput.='<div class="church-admin-form-group"><label>'.esc_attr( $value).'</label><textarea class="rota church-admin-form-control" data-taskid="'.(int)$rota_task_id.'" >';
				if(!empty( $people) )	{$rotaOutput .= esc_textarea( $people );}
				$rotaOutput.='</textarea></div>';
			}
			
				
			

		}
		$rotaOutput.='<p><button class="button action" data-rotadate="'.esc_html( $selectedService->rota_date).'" data-tab="save-rota" data-serviceid="'.(int)$selectedService->service_id.'" data-rotaid="'.(int)$rota_id.'">'.esc_html( __('Save changes','church-admin' ) ).'</button></p>';
		$rotaOutput.='<script>jQuery(document).ready(function( $)  {$("textarea").each(function(textarea) {
			$(this).height( $(this)[0].scrollHeight );
		});});</script>';
	}
	else
	{
		//NON ADMIN
		$rotaOutput.='<table><tbody>';
		foreach( $requiredRotaJobs AS $rota_task_id=>$value)
		{
			$people=esc_html(church_admin_rota_people( $selectedService->rota_date,$rota_task_id,$selectedService->service_id,'service') );
			if ( empty( $people) )$people=__('Not assigned yet','church-admin');
			$rotaOutput.='<tr><th scope="row">'.esc_html( $value).'</th><td>'.$people.'</td></tr>';
			

		}
		$rotaOutput.='</tbody></table>';
	}
	
	
	
	$output['rota_date']=$selectedService->rota_date;
	$output['content']=$rotaDropdown.$rotaOutput;
	$time_end = microtime(true); 
	//church_admin_debug('ca_app_new_rota'. $time_end);
	$time_taken=$time_end-$time_start;
	//church_admin_debug('ca_app_new_rota Time Taken:'. $time_taken);
	return $output;

}

function ca_app_new_save_rota( $loginStatus)
{
	
	church_admin_debug('***** ca_app_new_save_rota *****');
	//church_admin_debug($_REQUEST);
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	if ( empty( $loginStatus)||!church_admin_level_check('Rota',$loginStatus->user_id) )
	{
		$output = array( 'token'=>esc_html( $token ),'message'=>__("You don't have permission to do that",'church-admin') );
		return $output;
	}
	$service_id =!empty($_REQUEST['service_id'])? (int)sanitize_text_field(stripslashes($_REQUEST['service_id'])):0;
	church_admin_debug('Service id: "'.$service_id.'"');
	church_admin_debug('Is numeric '.church_admin_int_check($service_id));
	if ( empty( $service_id))
	{
		church_admin_debug('Missing service id '.$service_id);
		$output = array( 'token'=>esc_html( $token ),'message'=>__("Missing service id",'church-admin') );
		return $output;
	}
	$service = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
	if ( empty( $service))
	{
		church_admin_debug('Missing service for  '.$service_id);
		$output = array( 'token'=>esc_html( $token ),'message'=>__("Service not found",'church-admin') );
		return $output;
	}



	if ( !church_admin_int_check($service_id))
	{
		church_admin_debug('Invalid service id '.$service_id.' church_admin_int_check: '.church_admin_int_check($service_id) );
		$output = array( 'token'=>esc_html( $token ),'message'=>__("Invalid service id",'church-admin') );
		return $output;
	}
	$rota_date=!empty($_REQUEST['rota_date'])?sanitize_text_field( stripslashes ($_REQUEST['rota_date'] ) ):null;
	if ( empty( $rota_date) || !church_admin_checkdate( $rota_date) )
	{
		$output = array( 'token'=>esc_html( $token ),'message'=>__("Missing rota date",'church-admin') );
		return $output;
	}
	$rota_tasks=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_rota_settings ORDER BY rota_order');
	//get required rota tasks for service id
	$requiredRotaJobs=$rotaDates=array();
	foreach( $rota_tasks AS $rota_task)
	{
		$allServiceID=maybe_unserialize( $rota_task->service_id);
		if(is_array( $allServiceID)&&in_array( $service_id,$allServiceID) )$requiredRotaJobs[$rota_task->rota_id]=$rota_task->rota_task;
	}
	$errorMessage=array();
	foreach( $requiredRotaJobs AS $rota_task_id=>$task)
	{
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE rota_task_id="'.(int)$rota_task_id.'" AND rota_date="'.esc_sql( $rota_date).'" AND service_id="'.(int)$service_id.'" AND mtg_type="service"');
		//church_admin_debug($wpdb->last_query);
		$people=array_filter(unserialize(church_admin_get_people_id( sanitize_text_field(stripslashes($_REQUEST['rotataskid'.$rota_task_id]) ) ) ));
		
		
		foreach( $people AS $key=>$person)
		{
			//check availability
			$check=false;
			//only check if stored in directory
			if(church_admin_int_check( $person) )$check=$wpdb->get_var('SELECT not_id FROM '.$wpdb->prefix.'church_admin_not_available WHERE unavailable="'.esc_sql( $rota_date).'" AND people_id="'.(int)$person.'"');
			if ( empty( $check) )
			{
				$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_new_rota (rota_date,rota_task_id,people_id,service_id,mtg_type,service_time)VALUES("'.esc_sql( $rota_date).'","'.(int)$rota_task_id.'","'.esc_sql( $person).'","'.(int)$service_id.'","service","'.esc_sql( $service->service_time).'" )');
				church_admin_debug( $wpdb->last_query);
			}
			else
			{
				
				$name=church_admin_get_person( $person);
				church_admin_debug('Not adding '.$name);
				$errorMessage[]= esc_html(sprintf(__('%1$s not available','church-admin' ) ,$name));
			}
		}
	}
	//church_admin_debug( $errorMessage);
	//rota_id has changed so update it befor calling ca_app_new_rota
	$_REQUEST['rota_id']=(int)$wpdb->insert_id;
	$output=ca_app_new_rota( $loginStatus);
	$output['message']=__('Rota details saved');
	if(!empty( $errorMessage) )
	{
		$output['message'].='<br><strong>'.implode("<br>",$errorMessage).'</strong>';
	}
	//church_admin_debug( $output);
	//church_admin_debug('***** END DAVE ROTA ******');
	return $output;
}


function ca_app_new_checkin( $loginStatus)
{

	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$wpdb->show_errors();
	//grab household
	$household=array();
	$family=$wpdb->get_results('SELECT *  FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$loginStatus->household_id.'" ORDER BY people_order');
	if(!empty( $family) )
	{
		foreach( $family as $person)
		{
			$household[]=array('people_id'=>(int)$person->people_id,'name'=>church_admin_formatted_name( $person),'people_type_id'=>$person->people_type_id);
		}
	}

	if(!empty( $_REQUEST['type'] ) )
	{
		$type=FALSE;
		switch( sanitize_text_field(stripslashes($_REQUEST['type'] ) ) )
		{
			case 'service':$type='service';break;
			case 'group':$type='smallgroup';break;
			case 'class':$type='class';break;
			case 'event':$type='event';break;
		}
		if ( empty( $type) )
		{
			$output = array( 'token'=>esc_html( $token ),'message'=>esc_html( __('No meeting type specified','church-admin')) );
			return $output;
		}
		$people_id=!empty($_REQUEST['people_id'])?(int)sanitize_text_field(stripslashes($_REQUEST['people_id'])):null;
		$id=!empty($_REQUEST['id'])?(int)sanitize_text_field(stripslashes($_REQUEST['id'])):null;
		if ( empty( $id) ||!church_admin_int_check($id))
		{
			$output = array( 'token'=>esc_html( $token ),'message'=>esc_html( __('No occurrence specified','church-admin') ));
			return $output;
		}
		$date=wp_date('Y-m-d');
		foreach( $household AS $key=>$person)
		{
			if(!empty( $_REQUEST['people'.(int)$people_id] ) )
			{
				//check in $person['people_id] to event
				switch( $person['people_type_id'] )
					{
						case 1:$which='adults=adults+1'; $v='"1","0"';break;
						case 2:$which='children=children+1'; $v='"0","1"';break;
						default:$which='adults=adults+1'; $v='"1","0"';break;
					}

					$check=$wpdb->get_var('SELECT attendance_id FROM '.$wpdb->prefix.'church_admin_individual_attendance WHERE people_id="'.(int)$person['people_id'].'" AND meeting_type="'.esc_sql( $what).'" AND meeting_id="'.(int)$id.'" AND `date`="'.esc_sql( $date).'"');
					if ( empty( $check) )
					{
							$sql='INSERT '.$wpdb->prefix.'church_admin_individual_attendance (people_id,meeting_type,meeting_id,`date`) VALUES("'.(int)$person['people_id'].'","'.esc_sql( $type).'","'.(int)$id.'","'.esc_sql( $date).'")';
							if(defined('CA_DEBUG') )//church_admin_debug( $sql);
							$wpdb->query( $sql);
							//main attendance
							$sql='SELECT attendance_id FROM '.$wpdb->prefix.'church_admin_attendance WHERE mtg_type="'.esc_sql( $type).'" AND service_id="'.(int)$id.'" AND `date`="'.esc_sql( $date).'"';
							if(defined('CA_DEBUG') )//church_admin_debug( $sql);
							$check=$wpdb->get_var( $sql);
							if(!empty( $check) )
							{
								$sql='UPDATE '.$wpdb->prefix.'church_admin_attendance SET '.$which.' WHERE mtg_type="'.esc_sql( $type).'" AND service_id="'.(int)$id.'" AND `date`="'.esc_sql( $date).'"';
								if(defined('CA_DEBUG') )//church_admin_debug( $sql);
								$wpdb->query( $sql);
							}
							else {
								$sql='INSERT INTO '.$wpdb->prefix.'church_admin_attendance (adults,children,mtg_type,service_id,`date`) VALUES ('.esc_sql($v).',"'.esc_sql( $type).'","'.(int)$id.'","'.esc_sql( $date).'")';
								if(defined('CA_DEBUG') )//church_admin_debug( $sql);
								$wpdb->query( $sql);
							}

					}
			}
		}
		$output = array( 'token'=>esc_html( $token ),'message'=>esc_html( __('Checkin saved','church-admin')) );
		return $output;
	}
	else
	{
		$day=wp_date('w');
		$events=array();
		//find service today
		$services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_day="'.esc_sql( $day).'"');
		if(!empty( $services) )
		{
			foreach( $services AS $service)
			{
				$events[]=array('type'=>'service','id'=>esc_html( $service->service_id),'name'=>esc_html( $service->service_name) );
			}
		}
		//check for small group
		//look for right small group
		$sql='SELECT a.leadership,a.group_name,a.ID FROM '.$wpdb->prefix.'church_admin_smallgroup a, '.$wpdb->prefix.'church_admin_people_meta' .' b WHERE a.id=b.ID AND b.people_id="'.(int)$loginStatus->people_id.'" AND b.meta_type="smallgroup" AND a.group_day="'.(int)$day.'"';
		$group=$wpdb->get_row( $sql);
		if(!empty( $group) )$events[]=array('type'=>'smallgroup','id'=>esc_html( $group->ID),'name'=>esc_html( $group->group_name) );
		//look for Classes
		$sql='SELECT a.name,a.class_id FROM '.$wpdb->prefix.'church_admin_classes a, '.$wpdb->prefix.'church_admin_people_meta' .' b WHERE a.class_id=b.ID AND b.people_id="'.(int)$loginStatus->people_id.'" AND b.meta_type="class" AND a.next_start_date="'.esc_sql(wp_date('Y-m-d')).'"';
		$classes=$wpdb->get_results( $sql);
		if(!empty( $classes) )
		{
			foreach( $classes AS $class)
			{
				$events[]=array('type'=>'class','id'=>esc_html( $class->class_id),'name'=>esc_html( $class->name) );
			}
		}
		//$events array populated or empty
		if ( empty( $events) )
		{
			$output = array( 'token'=>esc_html( $token ),'view'=>'html','page_title'=>esc_html( __('Check in','church-admin' ) ),'content'=>'','message'=>esc_html( __('Nothing to check in too','church-admin') ));
			return $output;
		}

		
		//build output
		//event section
		$content='<div class="church-admin-form-group"><label>'.esc_html( __('Choose event to check in too','church-admin' ) ).'</div>';
		$content.='<select id="event">';
		foreach( $events AS $key=>$event)
		{
			$content.='<option value="'.(int)$event['id'].'" data-type="'.esc_html( $event['type'] ).'">'.esc_html( $event['name'] ).'</option>';
		}
		$content.='</select></div>';
		//household section
		$content.='<h3>'.esc_html( __('Who to checkin','church-admin' ) ).'</h3>';
		foreach( $household AS $key=>$person)
		{
			$content.='<div class="church-admin-form-group"><input type="checkbox" class="person" value="'.(int)$person['people_id'].'">'.esc_html( $person['name'] ).'</div>';
		}
		$content.='<p><button class="action button" id="save-checkin" data-tab="save-checkin">'.esc_html( __('Check in','church-admin' ) ).'</button></p>';

	}
	$output = array( 'token'=>esc_html( $token ),'view'=>'html','content'=>$content,'page_title'=>esc_html( __('Check in','church-admin') ) );
	return $output;
}


function ca_app_new_my_rota( $loginStatus)
{

	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	global $wpdb;
	church_admin_app_log_visit( $loginStatus, __('My rota','church-admin') );
	//church_admin_debug(print_r( $loginStatus,TRUE) );
	if ( empty( $loginStatus->people_id) )
	{
		$output = array('token'=>esc_html($token),'message'=>'login required','content'=>'','view'=>'list');

	}
	else
	{
		$people=$wpdb->get_row('SELECT a.first_name,a.prefix,a.last_name, a.people_id FROM '.$wpdb->prefix.'church_admin_people a WHERE user_id="'.(int)$loginStatus->user_id.'"');

		if ( empty( $people->people_id) )
		{
			$output = array( 'token'=>esc_html( $token ),'message'=>"Your user identity is not connected to a church user profile.",'content'=>'','view'=>'html');

		}
		else
		{
			$content='';
			$sql='SELECT a.service_name,c.service_time, b.rota_task,c.rota_date,a.service_id FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_rota_settings b, '.$wpdb->prefix.'church_admin_new_rota c WHERE a.service_id=c.service_id AND c.mtg_type="service" AND c.rota_task_id=b.rota_id AND a.active=1 AND c.people_id="'.(int)$people->people_id.'" AND c.rota_date>=CURDATE() ORDER BY c.rota_date,c.service_time ASC';

			$results=$wpdb->get_results( $sql);
			if(!empty( $results) )
			{
				
				$task=$output = array( 'token'=>esc_html( $token ),);
				foreach( $results AS  $row)
				{

					$service=esc_html( $row->service_name.' '.$row->service_time);
					$date=mysql2date(get_option('date_format'),$row->rota_date);
					$content.='<p><strong>'.$date.'</strong><br>'.esc_html( $row->rota_task).' - '.esc_html( $row->service_name.' '.$row->service_time).'</p>';
					
				}
				
				$output = array( 'token'=>esc_html( $token ),'view'=>'html','page_title'=>esc_html( __('My schedule','church-admin' ) ),'content'=>$content);
			}
			else
			{

				$output = array( 'token'=>esc_html( $token ),'message'=>_('You are not scheduled to do anything'),
								'content'=>'<p><button class="action button" data-tab="rota">'.esc_html( __('Back to schedule','church-admin' ) ).'</button></p>');
			}	
		}

	}
	return $output;
}
/*******************************************
 * My group
 ******************************************/
function ca_app_new_mygroup( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$groupID=$wpdb->get_var('SELECT ID FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroup" AND people_id="'.(int)$loginStatus->people_id.'"');
	church_admin_debug('My group: '.$groupID);
	if ( empty( $groupID) || $groupID==1)
	{
		$button='<button class="button action" data-tab="smallgroup">'.esc_html( __('Back to groups','church-admin' ) ).'</button>';
		$output = array('button'=>$button,'view'=>'html','page_title'=>esc_html( __('My group','church-admin' ) ), 'token'=>esc_html( $token ),'content'=>'<p>'.esc_html( __("You don't appear to be in a group",'church-admin' ) ).'</p>' );
		//church_admin_debug($output);
		return $output;
	}
	$smallgroupLeader=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="smallgroupleader" AND ID="'.(int)$groupID.'"');
	$groupDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_smallgroup WHERE id="'.(int)$groupID.'"');
	$admin=FALSE;
	if( $loginStatus->people_id==$smallgroupLeader)$admin=TRUE;

	//handle admin
	if(!empty( $admin) && !empty( $_REQUEST['people'] ) )
	{
		$content.='<li><p>Adding people coming in next update</p></li>';
	}

	$content='';
	if( $admin)
	{
		$content.='<li><p><strong>'.esc_html( __('Add people to group','church-admin' ) ).'</strong></p>';
		$content.='<p>'.esc_html( __('Use first name and last name, and comma between each person','church-admin' ) ).'</p>';
		$content.='<div class="church-admin-form-group"><input type="text" id="people" class="church-admin-form-control" /></div>';
		$content.='<p><button class="action button" data-tab="save-group-people">'.esc_html( __('Save group','church-admin' ) ).'</button></p></li>';
	}
	//get people in group
	$results=$wpdb->get_results('SELECT a.*, a.attachment_id AS person_image_id, b.*,c.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_household b, '.$wpdb->prefix.'church_admin_people_meta c WHERE a.household_id=b.household_id AND a.people_id=c.people_id  AND c.meta_type="smallgroup" AND c.ID="'.(int)$groupID.'" AND a.show_me=1 ORDER BY a.last_name ASC,a.first_name ASC');
	church_admin_debug( $wpdb->last_query);
	if(!empty( $results) )
	{
		//fix any duplicates using $already
		$already=array();
		foreach( $results AS $row)
		{
			if(in_array($row->people_id,$already)){
				church_admin_debug('Duplicate found in meta table');
				$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_id="'.(int)$row->meta_id.'"');
				continue;
			}
			$already[]=$row->people_id;
			//church_admin_debug( $row);
			$content.='<li class="ui-li ui-li-divider ui-bar-d "><span class="ui-btn ui-btn-icon-right ui-icon-plus vcf" data-peopleid="'.(int)$row->people_id.'" ><span class="ui-li-heading">'.church_admin_formatted_name( $row).'</h3></span>';
			if(!empty( $row->person_image_id) )$content.='<p>'.wp_get_attachment_image( $row->person_image_id,'thumbnail','',array('class'=>'person-image','loading'=>'lazy') ).'</p>';
			$privacy=maybe_unserialize($row->privacy);
			if(!empty( $row->mobile) && !empty($privacy['show-cell']))$content.='<p ><a href="'.esc_url('tel:'.church_admin_e164( $row->mobile) ).'">'.esc_html( $row->mobile).'</a></p>';
			if(!empty( $row->email)  && !empty($privacy['show-email']))$content.='<p><a href="'.esc_url('mailto:'.$row->email).'">'.esc_html( $row->email).'</a></p>';
			if(!empty( $row->lat) && !empty( $row->lng)  && !empty($privacy['show-address'])) 
			{
				$content.='<p>'.esc_html( $row->address).'</p>';
				$content.='<p ><a class="linkButton green" href="'.esc_url('https://www.google.com/maps/search/?api=1&query='.$row->lat.','.$row->lng.'&amp;t=m&amp;z=16').'">'.esc_html( __("Map",'church-admin' ) ).'</a>'."\t".'&nbsp;<a href="https://www.google.com/maps/dir/?api=1&destination='.urlencode( $row->address).'" class="button button-map">'.esc_html( __('Directions','church-admin' ) ).'</a></p>'."\r\n\t";
			}
			else
			{
				if(!empty( $row->address)  && !empty($privacy['show-address']))$content.='<p>'.esc_html( $row->address).'</p>';
			}
			
		}
		$content.='</ul>';
	}
	$button='<button class="button action" data-tab="smallgroup">'.esc_html( __('Back to groups','church-admin' ) ).'</button>';
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( $groupDetails->group_name),'view'=>'list','content'=>$content,'button'=>$button);
	return $output;
}

function ca_app_new_classes( $loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	global $wpdb;
	$allowed_html = [
		'iframe' => [
			'src' => [],
			'allow' => [],
			'width' => [],
			'height' => [],
			'frameborder' => [],
			'allowFullScreen' => []
		], 
		'img'=>[
			'src'=>[],
			'class'=>[]
		],
		'p' =>['br'=>[]],
		'a'      => [
			'href'  => [],
			'title' => [],
		],
		'br'     => [],
		'em'     => [],
		'strong' => [],
		];
	$content='';
	
	if(!empty( $loginStatus) && church_admin_level_check('Classes',$loginStatus->user_id) )
	{
		$content.='<li><p><button class="button action" data-tab="edit-class" data-id=0>'.esc_html( __('Add Class','church-admin' ) ).'</button></p></li>';
	}
	$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE end_date >= CURDATE() ORDER by next_start_date ASC');
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			//church_admin_debug( $row);
			$content.='<li class="ui-li ui-li-divider ui-bar-d "><h3>'.esc_html( $row->name).'</h3>';
			
			switch( $row->recurring)
			{
				case '1':
					$rec=__('daily','church-admin');
					
				break;
				case '7':$rec=__('weekly','church-admin'); break;
				case '14':$rec=__('daily','church-admin'); break;
				case 'm':$rec=__('monthly','church-admin'); break;
				case 'a':$rec=__('annually','church-admin'); break;
				default:$rec='';break;
			}
			$content.='<p>'.wp_kses(wpautop( $row->description),$allowed_html).'</p>';
			if(!empty( $row->location) )$content.='<p>'.esc_html( $row->location).'</p>';
			$content.='<p>'.esc_html(sprintf(__('Starts on the %1$s, %2$s for %3$s times %4$s - %5$s','church-admin'  ),mysql2date(get_option('date_format'),$row->next_start_date),$rec,$row->how_many,$row->start_time,$row->end_time) ).'</p>';
			
			if(!empty( $loginStatus) && church_admin_level_check('Classes',$loginStatus->user_id) )
			{
				$content.='<div class="admin-tasks-toggle ui-btn ui-btn-icon-right ui-icon-carat-d" data-id="class-'.(int)$row->class_id.'" >'.esc_html( __('Administrator tasks','church-admin' ) ).'</div><div class="admin-tasks" id="class-'.(int)$row->class_id.'">';
				$content.='<p><button class="button action red" data-tab="edit-class" data-id="'.(int)$row->class_id.'">'.esc_html( __('Edit Class','church-admin' ) ).'</button></p>';
				//add students
				
				$students=array();
				$currentStudents=$wpdb->get_results('SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="class" AND b.ID="'.(int)$row->class_id.'"' );
				if(!empty( $currentStudents) )
				{
					foreach( $currentStudents AS $currStudent)
					{
						$students[]=church_admin_formatted_name( $currStudent);
					}
				}
				$content.='<p class="church-admin-form-group">'.esc_html( __('Update students (comma separate them)','church-admin' ) ).'<label></label><textarea class="church-admin-form-control" id="class'.(int)$row->class_id.'">'.esc_textarea( implode( ', ', $students ) ).'</textarea></p>';
				$content.='<p><button class="button action" data-tab="save-class-students" data-id="'.(int)$row->class_id.'">'.esc_html( __('Update students','church-admin' ) ).'</button></p>';
				
				if(!empty( $currentStudents) )
				{
					//check in section
					$content.='<p><strong>'.esc_html( sprintf(__('Check in Students %1$s','church-admin' ) ,mysql2date(get_option('date_format'),$row->next_start_date) )) .'</strong></p>';
					foreach( $currentStudents AS $currStudent)
					{
						$content.='<p><input type="checkbox" class="person" value="'.(int)$currStudent->people_id.'">'.esc_html( church_admin_formatted_name( $currStudent ) ).'</p>';
					}
					$content.='<p><button class="button action" data-tab="save-checkin-students" data-date="'.esc_html( $row->next_start_date).'" data-id="'.(int)$row->class_id.'">'.esc_html( __('Checkin students','church-admin' ) ).'</button></p>';
				}
				$content.='</div>';
			}
			$content.'</li>';
		}
	}
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Classes','church-admin')),'content'=>$content,'view'=>'list');
	return $output;
}

function ca_app_new_save_class_students( $loginStatus)
{

	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),);
	if ( empty( $loginStatus) ||  !church_admin_level_check('Classes',$loginStatus->user_id) )
	{
		$output['message']=__("You don't have permissions to edit students",'church-admin');
		return $output;
	}
	$class_id=!empty($_REQUEST['class_id'])?sanitize_text_field(stripslashes($_REQUEST['class_id'])):null;
	if ( empty( $class_id )||!church_admin_int_check($class_id))
	{
		$output['message']=__("Which class?",'church-admin');
		return $output;
	}
	//delete current students
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="class" AND ID="'.(int)$class_id.'"');
	//get student people_ids
	$people=array_filter(unserialize(church_admin_get_people_id(sanitize_text_field( stripslashes ($_REQUEST['students']) )) ));
	foreach( $people AS $key=>$people_id)
	{
		
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,ID,people_id,meta_date)VALUES("class","'.(int)$class_id.'","'.esc_sql( $people_id).'","'.date('Y-m-d').'" )');
		//church_admin_debug( $wpdb->last_query);
	}
	$output=ca_app_new_classes( $loginStatus);
	$output['message']=__('Class students updated','church-admin');
	return $output;
}

function ca_app_new_checkin_class_students( $loginStatus)
{
	global $wpdb;
	$output=array();
	//church_admin_debug( $_REQUEST);
	if ( empty( $loginStatus) ||  !church_admin_level_check('Classes',$loginStatus->user_id) )
	{
		$output['message']=__("You don't have permissions to checkin students",'church-admin');
		return $output;
	}
	//sanitize
	$class_id=!empty($_REQUEST['class_id'])?church_admin_sanitize($_REQUEST['class_id']):null;
	$people=!empty($_REQUEST['people'])?church_admin_sanitize($_REQUEST['people']):array();
	$date=!empty($_REQUEST['date'])?church_admin_sanitize($_REQUEST['date']):null;
	//validate
	if(empty($people)){
		$output['message']=__('No people specified','church-admin');
		return $output;
	}
	if(empty($date) || !church_admin_checkdate($date)){
		$output['message']=__('No valid date  specified','church-admin');
		return $output;
	}
	if ( empty( $class_id ) )
	{
		$output['message']=__("Which class?",'church-admin');
		return $output;
	}

	$class_id = !empty($_REQUEST['class_id']) ? sanitize_text_field(stripslashes($_REQUEST['class_id'])):null;
	if(!empty($class_id) && church_admin_int_check($class_id))
	{
		foreach( $people AS $key=>$people_id)
		{
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_individual_attendance (people_id,meeting_type,meeting_id,`date`) VALUES("'.(int)$people_id.'","class","'.(int)$_REQUEST['class_id'].'","'.esc_sql( $date) .'")');
		}
		$output['message']=__('Class students checked in','church-admin');
	}
	else{
		$output['message']=__('Invalid class ID','church-admin');
	}
	return $output;
	
}

function ca_app_new_edit_class( $loginStatus)
{
	global $wpdb;
	//church_admin_debug( $_REQUEST);
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Edit Class','church-admin' ) ),'view'=>'html');
	if ( empty( $loginStatus) || !church_admin_level_check('Classes',$loginStatus->user_id) )
	{
		$output['message']=__("You don't have permissions to checkin students",'church-admin');
		return $output;
	}
	$class_name = !empty( $_REQUEST['class_name'])?sanitize_text_field(stripslashes( $_REQUEST['class_name'])):null;
	$class_id = !empty( $_REQUEST['class_id'])?sanitize_text_field(stripslashes( $_REQUEST['class_id'])):null;


	if(!empty( $class_id) )
	{
		$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"');
		$class_id=(int)$class_id;
	}
	
	$content='<p><button class="button action green" data-tab="classes">'.esc_html( __('Back to classes','church-admin' ) ).'</button>';
	//class name
	$content.='<div class="church-admin-form-group"><label>'.esc_html(__('Class name','church-admin') ).'</label>';
	$content.='<input type="text" id="class-name" class="church-admin-form-control" ';
	if(!empty( $data->name) )$content.=' value="'.esc_html( $data->name).'" ';
	$content.='/></div>';
	//description
	$content.='<div class="church-admin-form-group"><label>'.esc_html(__('Description','church-admin') ).'</label>';
	$content.='<textarea id="description" class="church-admin-form-control"> ';
	if(!empty( $data->name) )$content.=esc_textarea( $data->description );
	$content.='</textarea></div>';
	//location
	$content.='<div class="church-admin-form-group"><label>'.esc_html(__('Location','church-admin') ).'</label>';
	$content.='<input type="text" id="location" class="church-admin-form-control" ';
	if(!empty( $data->location) )$content.=' value="'.esc_html( $data->location).'" ';
	$content.='/></div>';
	/******************
	 * Date
	 *****************/
	$sqldate=date('Y-m-d');
	$date=__('Pick date','church-admin');
	if(!empty( $data->next_start_date) )
	{
		$sqldate=$data->next_start_date;
		$date=mysql2date(get_option('date_format'),$data->next_start_date);
	}
	$content.='<p class="ui-li-desc"><button class="date-picker" data-date="'.$sqldate.'">'.$date.'</button></p>';
	$content.='<input type="hidden" id="date-picker" value="'.$sqldate.'" />';
	/******************
	 * Times
	 *****************/
	$start_time=!empty( $data->start_time)?esc_html( $data->start_time):'19:00';
	$end_time=!empty( $data->end_time)?esc_html( $data->end_time):'21:00';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Start time','church-admin' ) ).'</label><input class="church-admin-form-control" type="time" id="start_time" value="'.$start_time.'" /></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('End time','church-admin' ) ).'</label><input class="church-admin-form-control" type="time" id="end_time" value="'.$end_time.'" /></div>';
	/*****************
	 * Recurring
	 *****************/
	$recurring=!empty( $data->recurring)?esc_html( $data->recurring):'s';
	
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Recurring','church-admin' ) ).'</label>';
	$content.='<select id="recurring" class="church-admin-form-control">';
	$content.='<option value="s" '.selected( $recurring,"s",FALSE).'>'.esc_html( __('Single','church-admin' ) ).'</option>';
	$content.='<option value="1" '.selected( $recurring,1,FALSE).'>'.esc_html( __('Daily','church-admin' ) ).'</option>';
	$content.='<option value="7" '.selected( $recurring,7,FALSE).'>'.esc_html( __('Weekly','church-admin' ) ).'</option>';
	$content.='<option value="14" '.selected( $recurring,14,FALSE).'>'.esc_html( __('Fortnightly','church-admin' ) ).'</option>';
	$content.='<option value="m" '.selected( $recurring,"m",FALSE).'>'.esc_html( __('Monthly on same date','church-admin' ) ).'</option>';
	$content.='<option value="a" '.selected( $recurring,"a",FALSE).'>'.esc_html( __('Annually on same date','church-admin' ) ).'</option>';
	$content.='<option value="n10" '.selected( $recurring,"n10",FALSE).'>'.esc_html( __('1st Sunday','church-admin' ) ).'</option>';
	$content.='<option value="n20" '.selected( $recurring,"n20",FALSE).'>'.esc_html( __('2nd Sunday','church-admin' ) ).'</option>';
	$content.='<option value="n30" '.selected( $recurring,"n30",FALSE).'>'.esc_html( __('3rd Sunday','church-admin' ) ).'</option>';
	$content.='<option value="n40" '.selected( $recurring,"n40",FALSE).'>'.esc_html( __('4th Sunday','church-admin' ) ).'</option>';
	$content.='<option value="n11" '.selected( $recurring,"n11",FALSE).'>'.esc_html( __('1st Monday','church-admin' ) ).'</option>';
	$content.='<option value="n21" '.selected( $recurring,"n21",FALSE).'>'.esc_html( __('2nd Monday','church-admin' ) ).'</option>';
	$content.='<option value="n31" '.selected( $recurring,"n31",FALSE).'>'.esc_html( __('3rd Monday','church-admin' ) ).'</option>';
	$content.='<option value="n41" '.selected( $recurring,"n41",FALSE).'>'.esc_html( __('4th Monday','church-admin' ) ).'</option>';
	$content.='<option value="n12" '.selected( $recurring,"n12",FALSE).'>'.esc_html( __('1st Tuesday','church-admin' ) ).'</option>';
	$content.='<option value="n22" '.selected( $recurring,"n22",FALSE).'>'.esc_html( __('2nd Tuesday','church-admin' ) ).'</option>';
	$content.='<option value="n32" '.selected( $recurring,"n32",FALSE).'>'.esc_html( __('3rd Tuesday','church-admin' ) ).'</option>';
	$content.='<option value="n42" '.selected( $recurring,"n42",FALSE).'>'.esc_html( __('4th Tuesday','church-admin' ) ).'</option>';
	$content.='<option value="n13" '.selected( $recurring,"n11",FALSE).'>'.esc_html( __('1st Wednesday','church-admin' ) ).'</option>';
	$content.='<option value="n23" '.selected( $recurring,"n21",FALSE).'>'.esc_html( __('2nd Wednesday','church-admin' ) ).'</option>';
	$content.='<option value="n33" '.selected( $recurring,"n31",FALSE).'>'.esc_html( __('3rd Wednesday','church-admin' ) ).'</option>';
	$content.='<option value="n33" '.selected( $recurring,"n41",FALSE).'>'.esc_html( __('4th Wednesday','church-admin' ) ).'</option>';
	$content.='<option value="n14" '.selected( $recurring,"n12",FALSE).'>'.esc_html( __('1st Thursday','church-admin' ) ).'</option>';
	$content.='<option value="n24" '.selected( $recurring,"n22",FALSE).'>'.esc_html( __('2nd Thursday','church-admin' ) ).'</option>';
	$content.='<option value="n34" '.selected( $recurring,"n32",FALSE).'>'.esc_html( __('3rd Thursday','church-admin' ) ).'</option>';
	$content.='<option value="n44" '.selected( $recurring,"n42",FALSE).'>'.esc_html( __('4th Thursday','church-admin' ) ).'</option>';
	$content.='<option value="n15" '.selected( $recurring,"n12",FALSE).'>'.esc_html( __('1st Friday','church-admin' ) ).'</option>';
	$content.='<option value="n25" '.selected( $recurring,"n22",FALSE).'>'.esc_html( __('2nd Friday','church-admin' ) ).'</option>';
	$content.='<option value="n35" '.selected( $recurring,"n32",FALSE).'>'.esc_html( __('3rd Friday','church-admin' ) ).'</option>';
	$content.='<option value="n45" '.selected( $recurring,"n42",FALSE).'>'.esc_html( __('4th Friday','church-admin' ) ).'</option>';
	$content.='<option value="n16" '.selected( $recurring,"n12",FALSE).'>'.esc_html( __('1st Saturday','church-admin' ) ).'</option>';
	$content.='<option value="n26" '.selected( $recurring,"n22",FALSE).'>'.esc_html( __('2nd Saturday','church-admin' ) ).'</option>';
	$content.='<option value="n36" '.selected( $recurring,"n32",FALSE).'>'.esc_html( __('3rd Saturday','church-admin' ) ).'</option>';
	$content.='<option value="n46" '.selected( $recurring,"n42",FALSE).'>'.esc_html( __('4th Saturday','church-admin' ) ).'</option>';
	$content.='</select></div>';
	$how_many=!empty( $data->how_many)?(int)$data->how_many:1;
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Frequency','church-admin' ) ).'</label><input class="church-admin-form-control" type="number" id="how_many" value="'.$how_many.'" /></div>';

	/*******************
	 * People
	 ******************/
	$currentStudents=$wpdb->get_results('SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b WHERE a.people_id=b.people_id AND b.meta_type="class" AND b.ID="'.(int)$class_id.'"' );
	if(!empty( $currentStudents) )
	{
		foreach( $currentStudents AS $currStudent)
		{
			$students[]=church_admin_formatted_name( $currStudent);
		}
	}
	$content.='<div class="church-admin-form-group"><label>'.esc_html(__('Students','church-admin') ).'</label>';
	$content.='<textarea id="people" class="church-admin-form-control"> ';
	if(!empty( $students) )$content.=esc_html(implode(", ",$students) );
	$content.='</textarea></div>';
	$content.='<p><button class="action button" data-tab="save-class" data-id="'.(int)$class_id.'">'.esc_html( __('Save','church-admin' ) ).'</button></p>';
	$output['content']=$content;
	return $output;

}

function ca_app_new_save_class( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	//church_admin_debug( $_REQUEST);
	if( empty( $loginStatus) || !church_admin_level_check('Classes',$loginStatus->user_id) )
	{
		$output['message']=__("You don't have permissions to checkin students",'church-admin');
		return $output;
	}
	//sanitize
	$start_date=!empty($_REQUEST['date'])?sanitize_text_field( stripslashes ($_REQUEST['date'] ) ):null;
	$class_id = !empty($_REQUEST['class_id'])?sanitize_text_field( stripslashes ($_REQUEST['class_id'] ) ):null;
	$title=!empty($_REQUEST['title'])?sanitize_text_field( stripslashes ($_REQUEST['title'] ) ):null;
	$description=!empty($_REQUEST['description'])?sanitize_textarea_field( stripslashes ($_REQUEST['description'] ) ):null;
	$location=!empty($_REQUEST['location'])?sanitize_text_field( stripslashes ($_REQUEST['location'] ) ):null;
	$recurring=!empty($_REQUEST['recurring'])?sanitize_text_field( stripslashes ($_REQUEST['recurring'] ) ):null;
	$how_many=!empty($_REQUEST['how_many'])?sanitize_text_field( stripslashes ($_REQUEST['how_many'] ) ):null;
	//validate
	if(empty($start_date) || !church_admin_checkdate($start_date))
	{
		$output['message']=__("No start date for class ",'church-admin');
		return $output;
	}

	
	$event_id=NULL;
	if(!empty( $class_id ))
	{
		//church_admin_debug('Non empty class_id returned');
		$class_id=(int)$_REQUEST['class_id'];
		//get event_id to delete from calendar
		$event_id=$wpdb->get_var('SELECT event_id FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"');

		//delete current students
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="class" AND ID="'.(int)$class_id.'"');
		//church_admin_debug( $wpdb->last_query);
	}
	
	$allowed_html = [
		'iframe' => [
			'src' => [],
			'allow' => [],
			'width' => [],
			'height' => [],
			'frameborder' => [],
			'allowFullScreen' => []
		], 
		'img'=>[
			'src'=>[],
			'class'=>[]
		],
		'p' =>['br'=>[]],
		'a'      => [
			'href'  => [],
			'title' => [],
		],
		'br'     => [],
		'em'     => [],
		'strong' => [],
		];
	
	$data=array(
		'name'=>$title,
		'description'=>wp_kses($description ,$allowed_html),
		'location'=>$location,
		'recurring'=>$recurring ,
		'how_many'=>(int)$how_many,
		'start_date'=>$start_date,
		'start_time'=>$start_time,
		'end_time'=>$end_time
	);
	//church_admin_debug( $data);
	//save in calendar table
	$event_id= ca_app_new_save_cal_event($loginStatus, $data,NULL,$event_id,'all','class');
	$end_date=$wpdb->get_var('SELECT start_date FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$event_id.'" ORDER BY start_date DESC LIMIT 1');
	//save in class table
	if ( empty( $class_id) )$class_id=$wpdb->get_var('SELECT class_id FROM '.$wpdb->prefix.'church_admin_classes WHERE name="'.esc_sql( $data['name'] ).'" AND next_start_date="'.esc_sql( $start_date).'" AND start_time="'.esc_sql( $data['start_time'] ).'" AND event_id="'.(int)$event_id.'"');
	if ( empty( $class_id) )
	{
		//insert
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_classes (name,description,location,recurring, how_many,next_start_date,start_time,end_time,event_id,end_date) VALUES("'.esc_sql( $data['name'] ).'","'.esc_sql( $data['description'] ).'","'.esc_sql( $data['location'] ).'","'.esc_sql( $data['recurring'] ).'","'.esc_sql( $data['how_many'] ).'","'.esc_sql( $data['start_date'] ).'","'.esc_sql( $data['start_time'] ).'","'.esc_sql( $data['end_time'] ).'","'.(int)$event_id.'","'.esc_sql( $end_date).'")');
		$class_id=$wpdb->insert_id;
	}
	else
	{
		//update
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_classes SET name="'.esc_sql( $data['name'] ).'",description="'.esc_sql( $data['description'] ).'",location="'.esc_sql( $data['location'] ).'",recurring="'.esc_sql( $data['recurring'] ).'",how_many="'.esc_sql( $data['how_many'] ).'",next_start_date="'.esc_sql( $data['start_date'] ).'",start_time="'.esc_sql( $data['start_time'] ).'",end_time="'.esc_sql( $data['end_time'] ).'",event_id="'.(int)$event_id.'", end_date="'.esc_sql( $end_date).'" WHERE class_id="'.(int)$class_id.'"');
	}
	//update students
	$people=array_filter(unserialize(church_admin_get_people_id( sanitize_text_field(stripslashes($_REQUEST['students'] )) ) ));
	foreach( $people AS $key=>$people_id)
	{
		
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (meta_type,ID,people_id,meta_date)VALUES("class","'.(int)$class_id.'","'.esc_sql( $people_id).'","'.date('Y-m-d').'" )');
		//church_admin_debug( $wpdb->last_query);
	}

	$output=ca_app_new_classes( $loginStatus);
	$output['message']=__('Class saved','church-admin');
	return $output;
}



add_action("wp_ajax_ca_push_refresh", "ca_token_refresh");
add_action("wp_ajax_nopriv_ca_push_refresh", "ca_token_refresh");
add_action("wp_ajax_ca_token_refresh", "ca_token_refresh");
add_action("wp_ajax_nopriv_ca_token_refresh", "ca_token_refresh");
function ca_token_refresh()
{
	
	//church_admin_debug("PUSH TOKEN REFRESH");
    global $wpdb;
    if(!empty( $_REQUEST['token'] )&&!empty( $_REQUEST['pushToken'] ) )
    {
        $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_app WHERE UUID="'.esc_sql(sanitize_text_field( stripslashes ($_REQUEST['token'] ) ) ).'"');
        //church_admin_debug( $wpdb->last_query);
		
		//church_admin_debug('$people_id is '.$people_id);
		
		if( $people_id)
        {
			$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
			//church_admin_debug( $wpdb->last_query);
			
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET pushToken="'.esc_sql(sanitize_text_field( stripslashes ($_REQUEST['pushToken'] ) ) ).'" WHERE people_id="'.(int)$people_id.'"');
            //church_admin_debug( $wpdb->last_query);
			
			//church_admin_debug('Push Token refreshed for '.church_admin_formatted_name( $person).' with people id: '.$people_id);
			


        }
    }else{echo 'oops';}
    exit();
}

/*********************
 * SMS REPLIES
 ********************/

function ca_app_new_sms_replies( $loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	church_admin_app_log_visit( $loginStatus, __('SMS replies','church-admin'),$loginStatus);
	//church_admin_debug('********* START: ca_app_new_sms_replies *********');
	//church_admin_debug('Login Status');
	//church_admin_debug( $loginStatus);
	global $wpdb;
	$admin_people_ids=get_option('church_admin_twilio_receive_push_to_admin');
	//church_admin_debug('Admin people ids');
	//church_admin_debug( $admin_people_ids);
    if(!empty( $admin_people_ids) && in_array( $loginStatus->people_id,$admin_people_ids) )
    {
		$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('SMS replies','church-admin' ) ),
					'view'=>'list',
					'content'=>''
		);
		$results=$wpdb->get_results('SELECT t1.* FROM '.$wpdb->prefix.'church_admin_twilio_messages t1 INNER JOIN ( SELECT `mobile`, MAX(message_date) AS max_message_date FROM '.$wpdb->prefix.'church_admin_twilio_messages WHERE direction=0 GROUP BY `mobile` ) t2 ON t1.`mobile` = t2.`mobile` AND t1.message_date = t2.max_message_date ORDER BY t1.message_date DESC');
		//note `mobile` is in e164cell format
        if(!empty( $results) )
        {
			foreach( $results AS $row)
			{
				$messageTS=mysql2date(get_option('date_format').' '.get_option('time_format'),$row->message_date);
				$nameObj=$wpdb->get_row('SELECT first_name,prefix,last_name FROM '.$wpdb->prefix.'church_admin_people WHERE e164cell="'.esc_sql( $row->mobile).'"');
				
				$name=!empty( $nameObj)?church_admin_formatted_name( $nameObj):__('Unknown','church-admin');
				$mobile=esc_html( $row->mobile);
				$message=esc_html( $row->message);

				$output['content'].='<li class="ui-li-static ui-li-divider"><h3>'.$name.' '.$mobile.'</h3>';
				$output['content'].=esc_html(sprintf(__('Received %1$s','church-admin' ) ,$messageTS)).'</br>';
				$output['content'].='<p>'.$message.'</p>';
				$output['content'].='<p><button class="button action" data-tab="sms-thread" data-mobile="'.$mobile.'">'.esc_html( __('View thread','church-admin' ) ).'</button></p></li>';
			}
		}
		else
		{
			$output['content']='<li class="ui-li-statid">'.esc_html( __('No SMS replies yet','church-admin' ) ).'</li>';
		}

	}
	else
	{
		$output = array( 'token'=>esc_html( $token ),'message'=>esc_html(__("You don't have permissions to look at SMS replies",'church-admin' ) ),'view'=>'html','content'=>'');
		
	}
	return $output;
	//church_admin_debug('********* END: ca_app_new_sms_replies *********');
}

function ca_app_new_sms_thread( $loginStatus)
{
	//church_admin_debug('*********** ca_app_new_sms_thread ************');
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$admin_people_ids=get_option('church_admin_twilio_receive_push_to_admin');
    if(!empty( $admin_people_ids) && in_array( $loginStatus->people_id,$admin_people_ids) )
    {
		$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('SMS thread','church-admin' ) ),
					'view'=>'html',
					'content'=>''
		);
		if ( empty( $_REQUEST['mobile'] ) )
		{
			$output['message']=__('No mobile number for thread','church-admin');
			$output['content']='<p><button class="button action" data-tab="sms-replies">'.esc_html( __('Back to SMS replies','church-admin' ) ).'</button></p>';
			return $output;
		}
		$e164cell=!empty($_REQUEST['mobile'])?sanitize_text_field(stripslashes($_REQUEST['mobile'] ) ):null;
		if(empty($e164cell)){return;}
		$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE e164cell="'.esc_sql( $e164cell).'"');
		//church_admin_debug( $wpdb->last_query);
		//church_admin_debug( $person);
		$correspondent=!empty( $person)?church_admin_formatted_name( $person):__('Unknown','church-admin');
		$correspondent.=' ('.esc_html( $e164cell).')';
		$output['content'].='<p><button class="action button green" data-tab="sms-replies">'.esc_html( __('Back to SMS replies','church-admin' ) ).'</button></p>';
		$output['content'].='<h3>'.esc_html(sprintf(__('SMS with %1$s','church-admin' ) ,$correspondent)).'</h3>';
		$results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_twilio_messages WHERE mobile="'.esc_sql( $e164cell).'" ORDER BY message_date ASC');
		if(!empty( $results) )
		{
			$output['content'].='<div class="ca-message-container"><div class="ca-sms-messages">';
			foreach( $results AS $row)
			{
				switch( $row->direction)
				{
					case '0': 
						$class= 'class="ca-message-blue"';
						$tsClass='class="ca-message-timestamp-left"';
					break;
					case '1': 
						$class= 'class="ca-message-orange"';
						$tsClass='class="ca-message-timestamp-right"';    
					break;
				}
				$output['content'].='<div '.$class.'>';
				$message=esc_html( $row->message);
				if(function_exists('make_clickable') )$message=make_clickable( $message);
				$output['content'].= '<div class="ca-message-content">'.$message.'</div>'."\r\n";
				$output['content'].='<div '.$tsClass.'>'.mysql2date(get_option('date_format').' '.get_option('time_format'),$row->message_date).'</div></div>'."\r\n";
			}
			$messageID=(int)$row->message_id;
			$output['content'].='</div>'."\r\n".'<div class="message_id" data-messageid="'.$messageID.'"></div>'."\r\n";
			$output['content'].='</div>'."\r\n";
		}
		else
		{
			$output['content'].='<p>'.esc_html( __('No thread yet','church-admin' )).'</p>';
		}
		$output['content'].='<h3>'.esc_html( __('Send reply','church-admin' ) ).'</h3>';
		
		$output['content'].='<textarea class="ca-send-message" id="sms-reply"></textarea>';
		$output['content'].='<p><button class="action button" data-tab="sms-reply" data-mobile="'.esc_html( $e164cell).'">'.esc_html( __('Reply','church-admin')).'</button></p>';


	}
	else
	{
		$output = array( 'token'=>esc_html( $token ),'message'=>esc_html(__("You don't have permissions to look at SMS replies",'church-admin' ) ),'view'=>'html','content'=>'');
		
	}
	return $output;
}

function ca_app_new_sms_reply( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	//church_admin_debug('******** START: ca_app_new_sms_reply ********');

	$admin_people_ids=get_option('church_admin_twilio_receive_push_to_admin');
    if(!empty( $admin_people_ids) && in_array( $loginStatus->people_id,$admin_people_ids) )
    {
		//church_admin_debug('Okay to process');
		$mobile=!empty( $_REQUEST['mobile'] )? sanitize_text_field( stripslashes($_REQUEST['mobile'] ) ):NULL;
		if(empty($mobile)) return array( 'message' => __( 'No mobile number in your message', 'church-admin' ) );
		
		$sendMobile=str_replace("+","",$mobile);
		$reply=!empty( $_REQUEST['reply'] )?sanitize_text_field(stripslashes ($_REQUEST['reply'] ) ):NULL;
		if(empty($reply)) return array( 'message' => __( 'Empty message', 'church-admin' ) );
		
		//church_admin_debug('Message: '.$reply);
		require_once(plugin_dir_path(dirname(__FILE__) ).'includes/sms.php');
		church_admin_sms( $sendMobile,$reply,FALSE);
		$output=ca_app_new_sms_thread( $loginStatus);
		$output['message']=__('Sent','church-admin');
	}
	else
	{
		$output = array( 'token'=>esc_html( $token ),'message'=>esc_html(__("You don't have permissions to send SMS replies",'church-admin' ) ),'view'=>'html','content'=>'');
		
	}
	return $output;

	//church_admin_debug('******** END: ca_app_new_sms_reply ********');
}

/*************************************
 * Registration
 ************************************/

function ca_app_new_register( $loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;

	$appRegistrations=get_option('church_admin_no_app_registrations');
	if(!empty( $appRegistrations) )
	{
		$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Register','church-admin' ) ),'view'=>'html','content'=>esc_html( __('User registrations disabled','church-admin' )) );
		return $output;
	}
	$output = array( 'token'=>esc_html( $token ),'page_title'=>esc_html( __('Register','church-admin' ) ),'view'=>'html');
	$content='<p>'.esc_html( __('* are required fields','church-admin' ) ).'</p><div class="church-admin-form-group"><label>'.esc_html( __('First name*','church-admin' ) ).'</label><input type="text" id="first_name" class="church-admin-form-control" /></div>';
	$prefix=get_option('church_admin_use_prefix');
	if(!empty( $prefix) )$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Prefix','church-admin' ) ).'</label><input type="text" id="prefix" class="church-admin-form-control" /></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Last name*','church-admin' ) ).'</label><input type="text" id="last_name" class="church-admin-form-control" /></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Email*','church-admin' ) ).'</label><input type="text" id="email_address" class="church-admin-form-control" /></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Cellphone','church-admin' ) ).'</label><input type="text" id="mobile" class="church-admin-form-control" /></div>';
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('Address','church-admin' ) ).'</label><textarea id="address" class="church-admin-form-control"></textarea></div>';
	$gender=get_option('church_admin_gender');
	$content.='<div class="church-admin-form-group"><label >'.esc_html( __('Gender','church-admin' ) ).'</label><select id="gender" class="sex church-admin-form-control" >';
	foreach( $gender AS $key=>$value)  {$content.= '<option value="'.esc_html( $key).'" >'.esc_html( $value).'</option>';}
	$content.='</select></div>'."\r\n";
	$content.='<div class="church-admin-form-group"><label>'.esc_html( __('I give permission...','church-admin' ) ).'</label></div>';
	$content.='<div class="checkbox"><label ><input type="checkbox" id="email_send" name="email_send" value="TRUE" data-name="email_send" /> '.esc_html( __('To receive email','church-admin' ) ).'</label></div>';
	$content.='<p>'.esc_html( __('Refine type of email you can receive','church-admin' ) ).'</p>';
	$content.='<div class="checkbox"><label ><input type="checkbox" name="news_send" id="news_send" value="TRUE"  class="email-permissions" data-name="news_send" /> '.esc_html( __('To receive blog posts by email','church-admin' ) ).'</label></div>';
	//PRAYER REQUESTS
	if(post_type_exists('prayer-requests') )
	{
		$content.='<div class="checkbox"><label ><input type="checkbox" value="1" id="prayer_requests" data-name="prayer_chain"  class="email-permissions"  name="prayer_requests" /> '.esc_html( __('To receive Prayer requests by email','church-admin' ) ).'</label></div>';
	}
	//BIBLE READINGS
	if(post_type_exists('bible-readings') )
	{
		$content.='<div class="checkbox"><label ><input type="checkbox" value="1" id="bible_readings" data-name="bible_readings"  class="email-permissions"  name="bible_readings" /> '.esc_html( __('To receive new Bible Reading notes by email','church-admin' ) ).'</label></div>';
	}
	
	$content.='<p>'.esc_html( __('Other privacy permissions','church-admin' ) ).'</p>';
	$content.='<div class="checkbox"><label ><input type="checkbox" id="photo_permission" value="TRUE" data-name="photo_permission" /> '.esc_html( __('To use my photo in the directory and on the website','church-admin' ) ).'</label></div>';
	$content.='<div class="checkbox"><label ><input type="checkbox" id="sms_send" value="TRUE" data-name="sms_send" /> '.esc_html( __('To receive SMS','church-admin' ) ).'</label></div>';
	$content.='<div class="checkbox"><label ><input type="checkbox" id="mail_send" value="TRUE" data-name="mail_send" /> '.esc_html( __('To receive mail','church-admin' ) ).'</label></div>';
	$content.='<div class="checkbox"><label ><input type="checkbox" id="show_me" value="TRUE" data-name="show_me" /> '.esc_html( __('To show me on the password protected address list','church-admin' ) ).'</label></div>';
	$content.='<p><button class="button action" data-tab="register-process">'.esc_html( __("Register",'church-admin' ) ).'</button></p>';

	$output['content']=$content;
	return $output;
}
function ca_app_new_register_process( $loginStatus)
{
    global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$appRegistrations=get_option('church_admin_no_app_registrations');
	if(!empty( $appRegistrations) )return;

	//church_admin_debug( $_POST);
    $email=!empty($_POST['email_address'])?sanitize_text_field( stripslashes ($_POST['email_address'] ) ):null;
    $first_name=!empty($_POST['first_name'])?sanitize_text_field( stripslashes ($_POST['first_name'] ) ):null;
	$prefix=!empty($_POST['prefix'])?sanitize_text_field( stripslashes ($_POST['prefix'] ) ):null;
    $last_name=!empty($_POST['last_name'] )?sanitize_text_field( stripslashes ($_POST['last_name'] ) ):null;
    $mobile=!empty($_POST['mobile'])?sanitize_text_field( stripslashes ($_POST['mobile'] ) ):null;
	$e164cell=!empty( $mobile)?church_admin_e164( $mobile):NULL;
	$address=!empty($_POST['address'])?sanitize_text_field( stripslashes ($_POST['address'] ) ): null;
	$gender=!empty( $_POST['gender'] )?1:0;
	$send_email=!empty( $_POST['email_send'] )?1:0;
	$blog_posts=!empty( $_POST['blog_posts'] )?1:0;
	$prayer_requests=!empty( $_POST['prayer'] )?1:0;	
	$bible_readings=!empty( $_POST['bible'] )?1:0;
	$photo_permission=!empty( $_POST['photo_permission'] )?1:0;
    $sms_receive=!empty( $_POST['sms_send'] )?1:0;
	$pushToken=!empty( $_POST['pushToken'] )?$_POST['pushToken']:NULL;
	
	if(!empty($loginStatus->people_id)){$gdpr_reason=__('Admin added');}else{$gdpr_reason=null;}

    if(!empty( $loginStatus->user_id) )$pushToken=NULL;//registering someone else so don't save push token.
	$errors=array();
	if ( empty( $first_name) )$errors[]='No first name!';
	if ( empty( $last_name) )$errors[]='No last name!';
    if(church_admin_spam_check( $email,'email')&&!church_admin_level_check('Directory',$loginStatus->user_id) )$errors[]='That was not a recognisable email';
    if(!empty( $first_name) && church_admin_spam_check( $first_name,'text') )$errors[]='Spammy first name!';
    if(!empty( $last_name) && church_admin_spam_check( $last_name,'text') )$errors[]='Spammy last name!';
    if(!empty( $email) && is_email($email) )$check=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql( $email).'"');
    if( $check)  {$errors[]=esc_html(__('Your email address is already registered.','church-admin' ) ).'</br/><button class="button" data-tab="#forgotten" id="forgotten"  >'.esc_html( __('Forgotten Password','church-admin' )).'</button>';}
    $output='';
    if(!empty( $errors) )
    {
        $output = array( 'token'=>esc_html( $token ),'message'=>implode('<br>',$errors) );
    }
    else
    {
		$user_id = !empty($loginStatus->user_id) ? (int)$loginStatus->user_id:null;
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,last_updated,first_registered,updated_by) VALUES("'.esc_sql( $address).'","'.esc_sql(wp_date("Y-m-d H:i:s")).'","'.esc_sql(wp_date("Y-m-d")).'","'.esc_sql($user_id).'")');
        $household_id=$wpdb->insert_id;
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,mobile,e164cell,household_id,people_type_id,member_type_id,email_send,sms_send,photo_permission,active,pushToken,head_of_household,first_registered,updated_by,gdpr_reason)VALUES("'.esc_sql( $first_name).'","'.esc_sql( $last_name).'","'.esc_sql( $email).'","'.esc_sql($mobile ).'","'.esc_sql( $e164cell).'","'.(int)$household_id.'","1","1","'.(int)$send_email.'","'.(int)$sms_receive.'","'.(int)$photo_permission.'",1,"'.esc_sql( $pushToken).'","1","'.esc_sql(wp_date('Y-m-d')).'","'.esc_sql($user_id).'","'.esc_sql($gdpr_reason).'")');
        $people_id=$wpdb->insert_id;
		$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		church_admin_update_people_meta( $prayer_requests,$people_id,'prayer-requests',wp_date('Y-m-d') );
		church_admin_update_people_meta( $bible_readings,$people_id,'bible-readings',wp_date('Y-m-d') );
		church_admin_update_people_meta( $blog_posts,$people_id,'posts',wp_date('Y-m-d') );


        $output = array( 'token'=>esc_html( $token ),'message'=>esc_html(__('Done','church-admin')),'content'=>'<p>'.esc_html(__('Thank you for registering','church-admin')),'page_title'=>esc_html(__('Register','church-admin')),'view'=>'html');
		//registrations with email get extranotice
		if(!empty( $email) )$output['content'].='<p>'.esc_html(__(' You will get a confirmation email and then an admin will give you a user login','church-admin')).'</p>';
        //send admin email
        
        $adminmessage=get_option('church_admin_new_entry_admin_email');
		$admin_message = str_replace('[HOUSEHOLD_ID]','[HOUSEHOLD_ID]&token=[NONCE]',$admin_message);
		$admin_message=str_replace('[HOUSEHOLD_ID]',(int)$household_id,$admin_message);
		$admin_message=str_replace('[HOUSEHOLD_DETAILS]','',$admin_message);
		$adminmessage.='<p>'.church_admin_formatted_name($person).'</p>';
		if(!empty( $email ) )$adminmessage.='<p><a href="'.esc_url('mailto:'.$email ).'">'.esc_html( $email ).'</a></p>';
		if(!empty( $mobile) )$adminmessage.='<p><a href="call:'.esc_url( $mobile ).'">'.esc_html( $mobile ).'</a></p>';
		if(!empty( $address ) )$adminmessage.='<p>'.esc_html( $address ).'</p>';   
		$admin_email=get_option('church_admin_default_from_email');
        if(!empty( $admin_email) )
        {
			$subject = esc_html(__('A new person has been registered on the app','church-admin'));
			$from_name = get_option('church_admin_default_from_name');
			$from_email = get_option('church_admin_default_from_email');
			
            church_admin_email_send($admin_email,$subject,wp_kses_post($adminmessage),$from_name,$from_email,null,$from_name,$from_email,FALSE);
        }
        church_admin_email_confirm( $people_id);
    }
	delete_option('church_admin_app_address_cache');
    delete_option('church_admin_app_admin_address_cache');
	return $output;
}

function ca_app_new_app_page( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	if(!empty( $_REQUEST['page_name'] ) )
	{
		$page_name=sanitize_text_field(stripslashes($_REQUEST['page_name'] ) ) ;
		$row=$wpdb->get_row('SELECT post_title, post_content FROM '.$wpdb->posts.' WHERE post_status="publish" AND post_type="app-content" AND post_name="'.esc_sql($page_name).'" LIMIT 1');
		if(!empty( $row) )
		{
			
			if(!empty( $_REQUEST['token'] ) )church_admin_app_log_visit( $loginStatus,  $row->post_title,$loginStatus);
			//$content=do_blocks( $row->post_content);
			if( has_blocks( $row->post_content ) )
			{
				remove_filter( 'the_content', 'wpautop' );
				$content='<!--has blocks so do the magic -->'.apply_filters("the_content", do_blocks( $row->post_content ));
			}
			else
			{
				$content = do_shortcode($row->post_content);
			}
			$content=do_shortcode( $content);
			$output = array( 'token'=>esc_html( $token ),
				'content'=>$content,'view'=>'html',
				'page_title'=>esc_html( $row->post_title) 
			);
		}
		else{$output = array('view'=>'html', 
				'token'=>esc_html( $token ),
				'content'=>esc_html( __('Nothing here yet','church-admin') ),
				'page_title'=>esc_html(__('404 error','church-admin'))
			)  ;}
	}
	else{$output = array('view'=>'html', 
		'token'=>esc_html( $token ),
		'content'=>esc_html( __('Nothing here yet','church-admin') ),
		'page_title'=>esc_html(__('404 error','church-admin'))
	 );}

	return $output;
}

function ca_app_new_create_user( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ));
	if ( empty( $loginStatus) ||  !church_admin_level_check('Directory',$loginStatus->user_id) )
	{
		$output['message']=__("You don't have permissions to do that.",'church-admin');
		return $output;
	}
	$people_id=!empty($_POST['people_id'])?sanitize_text_field(stripslashes($_POST['people_id'])):null;
	if(!empty( $people_id )&&church_admin_int_check( $people_id ) )
	{
		$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		
		church_admin_create_user( $person->people_id,$person->household_id,null,null);
		$userID=$wpdb->get_var('SELECT user_id FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
		$user= get_userdata( $userID);
		$message=esc_html( __('User account created','church-admin') );
	}else{
		$message= esc_html(__('User account NOT created','church-admin'));
	}
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
	$output=ca_app_new_address_list( $loginStatus);
	$output['message']= $message;
	return $output;
}


function ca_app_new_connect_user( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),);
	if ( empty( $loginStatus) ||  !church_admin_level_check('Directory',$loginStatus->user_id) )
	{
		$output['message']=__("You don't have permissions to do that.",'church-admin');
		return $output;
	}
	$people_id=!empty($_POST['people_id'])?sanitize_text_field(stripslashes($_POST['people_id'])):null;
	$user_id=!empty($_POST['user_id'])?sanitize_text_field(stripslashes($_POST['user_id'])):null;

	if(!empty( $people_id )&&church_admin_int_check( $people_id )&& !empty( $user_id )&& church_admin_int_check( $user_id ) )
	{
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people SET user_id="'.(int)$user_id.'" , last_update=NOW(), updated_by="'.(int)$loginStatus->user_id.'" WHERE people_id="'.(int)$people_id.'"');
		$output['message']=__('User account connected','church-admin');
	}
	else{
		$output['message']=__('User account NOT connected','church-admin');
	}
	delete_option('church_admin_app_address_cache');
	delete_option('church_admin_app_admin_address_cache');
	return $output;
}

function ca_app_new_comment( $loginStatus)
{
	global $wpdb;
	if ( empty( $_REQUEST['my-comment'] ) ) return array('message'=>esc_html( __('No comment submitted','church-admin') ));
	//church_admin_debug( $loginStatus);
	$commentdata=array();
	$user_id=$loginStatus->user_id;
	if(user_can( $user_id, 'edit_posts') )
	{
		$commentdata['comment_approved']=1;
	}
	else
	{
		$commentdata['comment_approved']=0;
	}
	$person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$loginStatus->user_id.'"');
	$commentdata['comment_author']=church_admin_formatted_name( $person);
	$commentdata['comment_email']=$person->email;
	$commentdata['comment_content']=sanitize_text_field( stripslashes ($_REQUEST['my-comment'] ) );
	$commentdata['user_id']=$loginStatus->user_id;
	$commentdata['comment_post_ID']=(int)$_REQUEST['id'];
	wp_insert_comment( $commentdata);

	//re output post with comment
	$output=ca_app_new_single_post( (int)$_REQUEST['id'] ,$loginStatus);
	$output['message']=__('Comment submitted','church-admin');
	//update cache so that comment is remembered.
	$appContentModified=time();
	update_option('church_admin_modified_app_content',$appContentModified);
	$cache=ca_refresh_app_cache( $loginStatus);
	$style=get_option('church_admin_app_style');
	$output['style']=$style;
	$output=array_merge( $output,$cache);
	
	
	return $output;
}

/********************************************
 * Not available
 */

function ca_app_new_not_available_save( $loginStatus)
{
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	//church_admin_debug( $_REQUEST['dates'] );
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_not_available WHERE people_id="'.(int)$loginStatus->people_id.'"');
	if(!empty( $_REQUEST['dates'] ) )
	{
		$dates=church_admin_sanitize($_REQUEST['dates']);
		$values=array();
		foreach( $dates AS $key=>$date)
		{
			if (church_admin_checkdate( $date) )$values[]='("'.(int)$loginStatus->people_id.'","'.esc_sql( $date ).'")';
		}
		if(!empty( $values) )$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_not_available (people_id,unavailable) VALUES '.implode(",",$values) );
	}
	$output=ca_app_new_not_available( $loginStatus);
	$output['message']=__('Thank you, your non-availability has been updated','church-admin');
	return $output;
}
function ca_app_new_not_available( $loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	global $wpdb;
	$output = array( 'token'=>esc_html( $token ),'view'=>'html','page_title'=>esc_html( __('My availability','church-admin') ) );
	if ( empty( $loginStatus->people_id) )
	{
		$output['content']=__('You need to be on the system to use this feature.','church-admin');
		return $output;
	}
	church_admin_app_log_visit( $loginStatus, __('My availability','church-admin'),$loginStatus);
	$person=$loginStatus;
	

	$services=$wpdb->get_results('SELECT service_day FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_day');
    if ( empty( $services) )
    {
        return '<p>'.esc_html( __('No services have been setup','church-admin' ) ).'</p>';
    }
    $dayNames=array(0=>'Sun',1=>"Mon",2=>"Tues",3=>"Wed",4=>"Thu",5=>"Fri",6=>"Sat");
    $days=array();
    foreach( $services AS $service)
    {
        $days[]=$dayNames[$service->service_day];
    }
    
 
    //create form 
    $out='<h3>'.esc_html( __('Please choose dates you are NOT available to serve on service schedules','church-admin' ) ).'</h3>';
    $begin=new DateTime('This Sunday');
    $end= new DateTime('+120 days');
    
    
    
   
    //get users unavailable dates
    $userNoDates=array();
    $unavailableDates=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_not_available WHERE people_id="'.(int)$person->people_id.'"');
    
    if(!empty( $unavailableDates) )
    {
        foreach( $unavailableDates AS $unavail)
        {
            $userNoDates[]=$unavail->unavailable;
        }
    }
 
    while ( $begin <= $end) // Loop will work begin to the end date 
    {
       
        if(in_array( $begin->format("D"),$days) ) 
        {
            $out.='<div class="church-admin-form-group"><input type="checkbox" class="unavailable" data-date="'.$begin->format("Y-m-d").'" ';
            if(in_array( $begin->format("Y-m-d"),$userNoDates) )$out.=' checked="checked" ';
            $out.=' value="'.$begin->format("Y-m-d").'" /> <label>'.$begin->format("D").' '.mysql2date(get_option('date_format'),$begin->format("Y-m-d") ).'</label></div>';
        }

        $begin->modify('+1 day');
    }
	$out.='<p><button class="button action" data-tab="not-available-save">'.esc_html( __('Save','church-admin' ) ).'</button></p>';
	$output['content']=$out;
	return $output;
}

function ca_app_new_get_notification_settings_form( $loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'view'=>'html','message'=>'');
	if ( empty( $loginStatus->people_id) )
	{
		$output['content']=__('You need to be on the system to use this feature.','church-admin');
		return $output;
	}
	//notifications
	$metaArray=church_admin_person_meta_array( $loginStatus->people_id);
	//church_admin_debug( $metaArray);
	$output = array( 'token'=>esc_html( $token ),'view'=>'html','page_title'=>esc_html( __('Notification settings','church-admin') ));
	$content='<div id="notifications">';
		$content.='<div class="church-admin-form-group"><input type="checkbox" class="notifications" id="bible-readings" ';
		if(!empty( $metaArray['bible-readings-notifications'] ) )$content.=' checked="checked" ';
		$content.='/><label>'.esc_html( __('Bible readings','church-admin' ) ).'</div>';
		$content.='<div class="church-admin-form-group"><input type="checkbox" class="notifications" id="prayer-requests" ';
		if(!empty( $metaArray['prayer-requests-notifications'] ) )$content.=' checked="checked" ';
		$content.='/><label>'.esc_html( __('Prayer requests','church-admin' ) ).'</div>';
		$content.='<div class="church-admin-form-group"><input type="checkbox" class="notifications" id="news" ';
		if(!empty( $metaArray['news-notifications'] ) )$content.=' checked="checked" ';
		$content.='/><label>'.esc_html( __('News','church-admin' ) ).'</div>';
		$content.='<div class="church-admin-form-group"><input type="checkbox" class="notifications" id="show-bible-readings-streak" ';
		if(!empty( $metaArray['show-bible-readings-streak'] ) )$content.=' checked="checked" ';
		$content.='/><label>'.esc_html( __('Show Bible reading streak','church-admin' ) ).'</div>';
		$content.='<p><button class="action button" data-tab="update-notifications">'.esc_html( __('Update notification settings')).'</button></p>';
	$content.='</div>';
	$output['content']=$content;

	return $output;
}


function ca_app_new_save_notification_settings( $loginStatus)
{
	//church_admin_debug("********** ca_app_new_save_notification_settings **************");
	
	global $wpdb;
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'view'=>'html','page_title'=>esc_html( __('Notification Settings','church-admin') ));
	if ( empty( $loginStatus->people_id) )
	{
		$output['content']=__('You need to be on the system to use this feature.','church-admin');
		return $output;
	}
	
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="bible-readings-notifications" AND people_id="'.(int)$loginStatus->people_id.'"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="prayer-requests-notifications" AND people_id="'.(int)$loginStatus->people_id.'"');
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="news-notifications" AND people_id="'.(int)$loginStatus->people_id.'"');
	if(!empty( $_REQUEST['show-bible-readings-streak'] ) ){$value = 1; } else { $value = 0; }
	church_admin_debug('line 4180');
	$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET ID = "'.(int)$value.'" WHERE meta_type="show-bible-readings-streak" AND people_id="'.(int)$loginStatus->people_id.'"');
	//church_admin_debug($wpdb->last_query);
	if(!empty( $_REQUEST['bible-readings'] ) )$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,meta_type,ID,meta_date)VALUES("'.(int)
	$loginStatus->people_id.'","bible-readings-notifications",1,"'.esc_sql(wp_date('Y-m-d')).'")');
	//church_admin_debug( $wpdb->last_query);
	if(!empty( $_REQUEST['prayer-requests'] ) )$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,meta_type,ID,meta_date)VALUES("'.(int)$loginStatus->people_id.'","prayer-requests-notifications",1,"'.esc_sql(wp_date('Y-m-d')).'")');
	//church_admin_debug( $wpdb->last_query);
	if(!empty( $_REQUEST['news'] ) )$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people_meta (people_id,meta_type,ID,meta_date)VALUES("'.(int)$loginStatus->people_id.'","news-notifications",1,"'.esc_sql(wp_date('Y-m-d')).'")');
	//church_admin_debug( $wpdb->last_query);
	$output=ca_app_new_get_notification_settings_form( $loginStatus);
	$output['message']=__('Notification settings updated','church-admin');
	return $output;
}


function ca_app_old_address_list( $loginStatus)
{
    global $wpdb;
	$groups=church_admin_groups_array();
	$member_types=church_admin_member_types_array();
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),'view'=>'html','page_title'=>esc_html( __('Address List','church-admin')),'content'=>'');

  
	if(!empty( $loginStatus) )$person=$wpdb->get_row('SELECT * FROM '. $wpdb->prefix.'church_admin_people'.' WHERE user_id="'.(int)$loginStatus->user_id.'"');
	
	//Get ordered results
	$mt=get_option('church_admin_app_member_types');
	//church_admin_debug("Member types");
	//church_admin_debug(print_r( $mt,TRUE) );
	//reject if wrong member type
	if(!empty( $loginStatus) && !user_can( $loginStatus->user_id,'manage_options') && !in_array( $loginStatus->member_type_id,$mt) )
	{
		//church_admin_debug("App address list - wrong member type");
		echo json_encode( array('address_list'=>'<p>'.esc_html( __("Unfortunately you can't access the directory list",'church-admin' ) ).'</p>') );
		exit();
	}
	//reject if restricted access
	$restrictedList=get_option('church-admin-restricted-access');
	if(!empty( $loginStatus) && !user_can( $loginStatus->user_id,'manage_options') && is_array( $restrictedList)&&in_array( $person->people_id,$restrictedList) )
	{ 
		//church_admin_debug("App address list - user on restricted list");
		$output['content'].='<p>'.esc_html( __("Unfortunately you can't access the directory list",'church-admin' ) ).'</p>';
		return $output;
		
	}
	//$output['content'].= '<ul class="ui-listview" data-role="listview" data-theme="d" data-divider-theme="d" >';
	$output['content'].= '<ul data-role="listview" data-theme="d" data-divider-theme="d" >';
	//output var
	$add='';
	$address=array();
	//adds search
	$search='<p><input id="s" type="text" placeholder="'.esc_html( __('Search for?','church-admin' ) ).'" /></p>';
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) ) {
		$search.='<p><input type="checkbox" id="all-list" />'.esc_html( __('(Admins only) Include all member types','church-admin' ) ).'</p>';
	}
	$search.='<p><button id="search" data-tab="#search" class="button action" type="submit">'.esc_html( __('Search','church-admin' ) ).'</button></p><br/>';

	$admin=FALSE;
	if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
	{
		$admin=TRUE;
		$search.='<p class="addItem" style="padding:.7em 1em;"><button id="add-directory" class="button green ">'.esc_html( __('Add directory entry','church-admin' ) ).'</button></p>';
	}
	$address[] = $search;
	if ( empty( $mt) )$mt=array(1);
	foreach( $mt AS $key=>$type)  {$mtsql[]='member_type_id='.(int)$type;}
	if(!empty( $mtsql) )  {$membSQL=' AND ('.implode(' OR ',$mtsql).' ) ';}else{$membSQL='';}
	$orderSQL=' ORDER BY last_name ASC ';
	$sql='SELECT DISTINCT household_id, last_name FROM '.$wpdb->prefix.'church_admin_people WHERE head_of_household=1 AND show_me=1 AND active=1 '.$membSQL.$orderSQL;
	church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	church_admin_debug(print_r( $results,TRUE) );
	if(!empty( $results) )
	{
		
		foreach( $results AS $ordered_row)
		{	
			$show_address =0;
			$show_phone = 0;
			
			$add.='';
			//church_admin_debug('Processing household_id: '.$ordered_row->household_id);
				$addressLine=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_household WHERE  household_id="'.esc_sql( (int)$ordered_row->household_id).'"');

				$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.esc_sql( $ordered_row->household_id).'"  AND show_me=1 AND active="1" ORDER BY people_order ASC, people_type_id ASC,sex DESC';
					//church_admin_debug(print_r( $addressLine) );
				$people_results=$wpdb->get_results( $sql);

				$first_names=$adults=$children=$emails=$mobiles=$photos=array();
				$last_name='';
				$x=0;
					$photo=FALSE;
				foreach( $people_results AS $person)
				{
					//church_admin_debug('Processing people_id: '.$person->people_id. ' '.$person->first_name.' '.$person_last_name);
					//build first part of name
					$privacy= maybe_unserialize($person->privacy);
					if(!empty($privacy['show-address'])){$show_address = 1;}



					if( $person->photo_permission)$photo=TRUE;
					$name=$person->first_name.' ';
					$middle_name=get_option('church_admin_use_middle_name');
					if(!empty( $middle_name)&&!empty( $person->middle_name) )$name.=$person->middle_name.' ';
					$nickname=get_option('church_admin_use_nickname');
					if(!empty( $nickname)&&!empty( $person->nickname) )$name.='('.$person->nickname.') ';
					//last name
					$prefix=get_option('church_admin_use_prefix');
					if(!empty( $prefix) &&!empty( $person->prefix) )  {	$Prefix=$person->prefix.' ';}else{$Prefix='';}
					$last_name=esc_html( $Prefix.$person->last_name);

					if( $person->people_type_id=='1')
					{
						$adults[$last_name][]=esc_html( $name);

						$first_names[]=$name;
						if(!empty($privacy['show-email']) && !empty($person->email) ) {
							 $emails[$name]=$person->email;
						}
						if(!empty($privacy['show-cell']) && !empty( $person->mobile)  ){
							$mobiles[$name]=esc_html( $person->mobile);
						}
						
						if(!empty( $person->attachment_id) )$photos[$name]=$person->attachment_id;
						$x++;
					}
					else
					{
						$children[]=esc_html(trim( $name) );
						if(!empty( $person->attachment_id) )$photos[$name]=$person->attachment_id;
					}
					

				} 

				//create output
				$adults = array_filter( $adults);
				$mobiles = array_filter($mobiles);
				$emails = array_filter($emails);
				//church_admin_debug(print_r( $adults,TRUE) );
				$adultline=array();
				//Adults names
				$add='<li class="ui-li ui-li-divider ui-bar-d "><h3 class="ui-li-heading">';
				//$add='<li><h3>';
				foreach( $adults as $lastname=>$firstnames)  {$adultline[]=implode(" &amp; ",$firstnames).' '.$lastname;}
				$add .="\r\n".esc_html(implode(" &amp; ",$adultline) ).'</h3>';

				if(!empty( $children) )$add.='<p>'.esc_html(implode(", ",$children) ).'</p>';
				if(!empty( $addressLine->attachment_id)&&!empty($photo))
				{
					$image=wp_get_attachment_image( $addressLine->attachment_id,'medium');
					$add.='<p>'.$image.'</p>';
				}
				if(!empty($show_address) && !empty( $addressLine->address) )
				{
					$add.='<p>'.str_replace(',',',<br>',$addressLine->address).'</p>';
				}
				if(!empty($show_address) && !empty( $addressLine->lat)  && !empty( $addressLine->lng) && !empty($addressLine->address))
				{
					$add.='<p><a href="'.esc_url('https://www.google.com/maps/search/?api=1&query='.$addressLine->lat.','.$addressLine->lng.'&amp;t=m&amp;z=16').'" class="button button-map"><i class="far fa-map"></i> '.esc_html( __('Map','church-admin' ) ).'</a></p>'."\r\n\t";
					$add.='<p><a href="https://www.google.com/maps/dir/?api=1&destination='.urlencode( $addressLine->address).'" class="button button-map"><i class="fas fa-route"></i> '.esc_html( __('Directions','church-admin' ) ).'</a></p>'."\r\n\t";
				}
				if ( $addressLine->phone)$add.='<p><a class="email ca-email" href="'.esc_html('tel:'.str_replace(' ','',$addressLine->phone) ).'">'.esc_html( $addressLine->phone)."</a></p>\n\r\t\t";
				if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
				{
					$add.='<p><button  class="button red action" data-next="address" data-tab="address_edit" id="'.(int)$addressLine->household_id .'" data-householdid="'.(int)$addressLine->household_id .'" data-tab="address_edit">'.esc_html( __('Edit address','church-admin' ) ).'</button></p>';
				}
				if(!empty( $emails) )
				{
					foreach( $emails AS $name=>$email)
					{
						$add.='<p>'.esc_html( $name).':<a href="mailto:'.esc_html( $email).'">'.esc_html( $email).'</a></p>';
					}
				}
				if(!empty( $mobiles) )
				{
					foreach( $mobiles AS $name=>$mobile)
					{
						$add.='<p>'.esc_html( $name).': <a href="tel:'.esc_html(str_replace(' ','',$mobile) ).'">'.esc_html( $mobile).'</a></p>';
					}
				}
				if(!empty( $loginStatus) && church_admin_level_check('Directory',$loginStatus->user_id) )
				{
					foreach( $people_results  AS $person)
					{
						$name=esc_html(church_admin_formatted_name( $person) );
						$add.='<div class="admin-tasks-toggle ui-btn ui-btn-icon-right ui-icon-carat-d" data-id="person-'.(int)$person->people_id.'" >'.esc_html(sprintf(__('Admin tasks for %1$s','church-admin' ) ,$name)).'</div><div class="admin-tasks" id="person-'.(int)$person->people_id.'">';
						$add.='<p><button class="button green action"  data-next="address" data-tab="people_edit" data-householdid="'.(int)$person->household_id.'" data-peopleid="'.(int)$person->people_id.'">'.esc_html(sprintf(__('Edit %1$s','church-admin' ) ,$name)).'</button></p>';
						$add.='<p><button class="button red action" data-next="address" data-tab="people_delete" data-householdid="'.(int)$person->household_id.'"  data-peopleid="'.(int)$person->people_id.'">'.esc_html(sprintf(__('Delete %1$s','church-admin' ) ,$name)).'</button></p>';
						
						if(!empty( $person->email) )$add.='<p>'.church_admin_user_check( $person,TRUE).'</p>';

						//Small Group
						//church_admin_debug('Small group bit');
						$add.='<p class="church-admin-form-group"><label>'.esc_html( __('Small Group','church-admin' ) ).'</label>';
						$personGroup=church_admin_get_people_meta( $person->people_id,'smallgroup');
						if ( empty( $personGroup) )$personGroup=array(null);
						//church_admin_debug('church_admin_get_people_meta');
						//church_admin_debug( $personGroup);
						//church_admin_debug( $groups);
						$add.='<select class="church-admin-form-control smallgroup" id="id'.(int)$person->people_id.'">';
						foreach( $groups AS $id=>$group)
						{
							$add.='<option value="'.(int)$id.'" '.selected( $id,$personGroup[0],FALSE).'>'.esc_html( $group).'</option>';
						}
						$add.='</select></p><p><button class="action button" data-tab="change-group" data-peopleid="'.(int)$person->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';	

						//Member Type
						$add.='<p class="church-admin-form-group"><label>'.esc_html( __('Change member level','church-admin' ) ).'</label>';
						$add.='<select class="church-admin-form-control member_types" id="member-type-id'.(int)$person->people_id.'">';
						foreach( $member_types AS $id=>$type)
						{
							$add.='<option value="'.(int)$id.'" '.selected( $id,$person->member_type_id,FALSE).'>'.esc_html( $type).'</option>';
						}
						$add.='</select></p><p><button class="action button" data-tab="change-member-type" data-peopleid="'.(int)$person->people_id.'">'.esc_html( __('Change','church-admin' ) ).'</button></p>';
						$add.='</div>';
					}	
				}
				$add.= '<hr/><!--end household --></li>';


			$address['add'.$ordered_row->household_id]=$add;
	}


	}else{$address=array('<li>'.esc_html( __('No address list yet','church-admin' ) ).'</li>');}

	$output['content'].=implode("\r\n ",array_unique( $address) ).'</ul>';
	return $output;
}
/****************************
 * 
 * Save my prayer
 ***************************/
function ca_app_save_my_prayer( $loginStatus ){
	church_admin_debug('******* Save my prayer *******');
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	global $wpdb;
	//expected fields title,description,save_day,today, token,id
	$output = array( 'token'=>esc_html( $token ),'view'=>'html','page_title'=>esc_html( __('Edit My Prayer','church-admin') ));
    
	if(empty( $loginStatus) ){
		return ca_app_new_login_form('edit_my_prayer');
	}
	else{
		$person=$wpdb->get_row('SELECT * FROM '. $wpdb->prefix.'church_admin_people'.' WHERE user_id="'.(int)$loginStatus->user_id.'"');
	}
	//sanitize data
	$ID = !empty($_REQUEST['id'])? (int)$_REQUEST['id'] : null;
	$title = !empty($_POST['title'])?sanitize_text_field( stripslashes ($_POST['title'] ) ):null;
	$description = !empty( $_REQUEST['description'] ) ? sanitize_textarea_field( stripslashes( $_REQUEST['description'] ) ): null;
	$days =  !empty( $_REQUEST['days'] ) ? church_admin_sanitize( $_REQUEST['days'] ) :null ;

	if(empty($title)){
		//can't have a prayer  without a title
		return ca_app_edit_my_prayer($loginStatus,$ID);
	}
	if( empty( $days ) )
	{
		//can't have a prayer  without a day to pray it
		return ca_app_edit_my_prayer_form($loginStatus,$ID);
	}
	
	if( !is_array( $days ) ){
		//$days must be an array of days
		return ca_app_edit_my_prayer($loginStatus,$ID);
	}
	//expected $days values are int 0 to 6
	$sql_days=array('0'=>0,'1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0,'6'=>0);
	foreach($days AS $key=>$day)
	{
		switch($day)
		{
			case '1':$sql_days[$key]=1;break;
			default:$sql_days[$key]=0;break;
		}

	}
	if( empty( $sql_days ) ){
		//no days so reject!
		return ca_app_edit_my_prayer($loginStatus,$ID);
	}
	//church_admin_debug($sql_days);

	// We now have $title, $description, $days that have been sanitized
	if( empty( $ID ) )
	{
		//check to see if in DB already
		$ID=$wpdb->get_row('SELECT prayer_id FROM '.$wpdb->prefix.'church_admin_my_prayer WHERE people_id="'.(int)$loginStatus->people_id .'" AND title = "'.esc_sql( $title ).'" AND description = "'.esc_sql($description).'" AND day0 = "'.esc_sql($sql_days['0']).'" AND  day1 = "'.esc_sql($sql_days['1']).'" AND  day2 = "'.esc_sql($sql_days['2']).'" AND  day3 = "'.esc_sql($sql_days['3']).'" AND  day4 = "'.esc_sql($sql_days['4']).'" AND  day5 = "'.esc_sql($sql_days['5']).'" AND day6 = "'.esc_sql($sql_days['6']).'"');
	}
	if( !empty( $ID ) ) {
		//update
		$wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_my_prayer SET people_id="'.(int)$loginStatus->people_id .'" , title = "'.esc_sql( $title ).'" , description = "'.esc_sql($description).'" , day0 = "'.esc_sql($sql_days['0']).'" , day1 = "'.esc_sql($sql_days['1']).'", day2 = "'.esc_sql($sql_days['2']).'", day3 = "'.esc_sql($sql_days['3']).'", day4 = "'.esc_sql($sql_days['4']).'", day5 = "'.esc_sql($sql_days['5']).'" , day6 = "'.esc_sql($sql_days['6']).'" WHERE prayer_id="'.(int)$ID.'"');
	}
	else {
		//insert
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_my_prayer (title,description,day0,day1,day2,day3,day4,day5,day6,people_id,date_started)VALUES("'.esc_sql( $title ).'" ,"'.esc_sql($description).'" , "'.esc_sql($sql_days['0']).'" ,"'.esc_sql($sql_days['1']).'","'.esc_sql($sql_days['2']).'","'.esc_sql($sql_days['3']).'","'.esc_sql($sql_days['4']).'","'.esc_sql($sql_days['5']).'","'.esc_sql($sql_days['6']).'","'.(int)$loginStatus->people_id .'" ,"'.date('Y-m-d').'")');
	}
	//church_admin_debug($wpdb->last_query);
	$output = ca_app_show_prayer($loginStatus);
	$output['message']=__('Prayer saved','church-admin');
	return $output;
}
/**************************
 * EDIT Prayer form
 **************************/
function ca_app_edit_my_prayer_form($loginStatus)
{
	church_admin_debug('ca_app_edit_my_prayer_form');
	//church_admin_debug($_REQUEST);
	global $wpdb,$wp_locale;
	if(empty( $loginStatus) ){
		return ca_app_new_login_form('my-prayer');
	}
	$ID= !empty($_REQUEST['id'])?  sanitize_text_field(stripslashes($_REQUEST['id'])) : 0;
	
	
	if(!empty($ID) && church_admin_int_check( $ID )){
		$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_my_prayer WHERE people_id="'.(int)$loginStatus->people_id.'" AND prayer_id="'.(int)$ID.'"');
		//church_admin_debug($wpdb->last_query);
		//church_admin_debug($data);
	}
	
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	//build output
	$output = array( 'token'=>esc_html($token),'view'=>'html', 'page_title'=>esc_html( __('Edit my prayer','church-admin') ));


	$output['content'] = '<div class="church-admin-form-group"><label>'.esc_html( __( 'Title' , 'church-admin' )).'</label><input class="church-admin-form-control" type="text"  id="prayer-item-title" ';
	if ( !empty( $data->title ) ) { $output['content'] .= 'value="'.esc_attr($data->title).'"'; }
	$output['content'] .= '/></div>';

	$output['content'] .= '<div class="church-admin-form-group"><label>'.esc_html( __( 'Description' , 'church-admin' )).'</label><textarea class="church-admin-form-control" type="text"  id="prayer-item-description" >';
	if ( !empty( $data->description ) ) { $output['content'] .= esc_textarea( $data->description ) ; }
	$output['content'] .= '</textarea></div>';
		
	$output['content'] .= '<div class="church-admin-form-group"><input type="checkbox" class="days" id="day0"  value=0 '.checked($data->day0,1,FALSE).'/>'.$wp_locale->get_weekday(0). '</div>';
	$output['content'] .= '<div class="church-admin-form-group"><input type="checkbox" class="days"  id="day1"  value=0 '.checked($data->day1,1,FALSE).'/>'.$wp_locale->get_weekday(1). '</div>';
	$output['content'] .= '<div class="church-admin-form-group"><input type="checkbox" class="days" id="day2"   value=0 '.checked($data->day2,1,FALSE).'/>'.$wp_locale->get_weekday(2). '</div>';
	$output['content'] .= '<div class="church-admin-form-group"><input type="checkbox" class="days" id="day3"   value=0 '.checked($data->day3,1,FALSE).'/>'.$wp_locale->get_weekday(3). '</div>';
	$output['content'] .= '<div class="church-admin-form-group"><input type="checkbox" class="days"  id="day4"  value=0 '.checked($data->day4,1,FALSE).'/>'.$wp_locale->get_weekday(4). '</div>';
	$output['content'] .= '<div class="church-admin-form-group"><input type="checkbox" class="days"  id="day5"  value=0 '.checked($data->day5,1,FALSE).'/>'.$wp_locale->get_weekday(5). '</div>';
	$output['content'] .= '<div class="church-admin-form-group"><input type="checkbox" class="days"  id="day6"  value=0 '.checked($data->day6,1,FALSE).'/>'.$wp_locale->get_weekday(6). '</div>';
	$output['content'] .= '<input type="hidden" id="id" value="'.(int)$ID.'" />';
	$output['content'] .= '<p><button class="button action" data-tab="save-my-prayer" id="save-my-prayer" >'.esc_html( __('Save','church-admin' ) ).'</button></p>';
	
	return $output;

}
/*************************
 * Answer prayers 
 ************************/
function ca_app_answer_prayer($loginStatus)
{
	global $wpdb,$wp_locale;
	if(empty( $loginStatus) ){
		return ca_app_new_login_form( 'myprayer' );
	}
	if(empty( $_REQUEST['id'] ) || !church_admin_int_check( $_REQUEST['id'] )) return array( 'message'=> __( 'Prayer not deleted', 'church-admin') );
	$ID=(int)$_REQUEST['id'];
	$wpdb->query( 'UPDATE '.$wpdb->prefix.'church_admin_my_prayer SET date_answered="'.esc_sql(wp_date('Y-m-d')).'" WHERE prayer_id="'.(int)$ID.'" AND people_id="'.(int)$loginStatus->people_id.'"' );

	$output= ca_app_show_prayer( $loginStatus );
	$output['message'] = __('Prayer transfer to answered section','church-admin');

	return $output;
	
}
/*************************
 * Show prayers 
 ************************/
function ca_app_show_prayer($loginStatus)
{
	global $wpdb,$wp_locale;
	if(empty( $loginStatus) ){
		return ca_app_new_login_form('myprayer');
	}
	$day=wp_date('w');
	
	//initalise output 
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html($token),'view'=>'html');
	$output['content'] = '<ul id="list" class="ui-listview">';
	$allowed_html = [
		'iframe' => [
			'src' => [],
			'allow' => [],
			'width' => [],
			'height' => [],
			'frameborder' => [],
			'allowFullScreen' => []
		], 
		'img'=>[
			'src'=>[],
			'class'=>[]
		],
		'p' =>['br'=>[]],
		'a'      => [
			'href'  => [],
			'title' => [],
		],
		'br'     => [],
		'em'     => [],
		'strong' => [],
		];
	

	//retrieve prayers for identified user
	
	if(!empty($_REQUEST['answered'])){
		//answered prayer
		$output['page_title']= __('Answered prayers','church-admin');
		$sql .='SELECT * FROM '.$wpdb->prefix.'church_admin_my_prayer WHERE  date_answered is NOT NULL AND people_id="'.(int)$loginStatus->people_id.'" AND date_answered IS NOT null ORDER BY date_answered DESC';
		$date_title = __( 'Answered', 'church-admin' );
		$date='date_answered';
	}
	else{
		//normal prayer
		$output['page_title'] = esc_html( sprintf( __('My prayer list for %1$s','church-admin' ) , $wp_locale->get_weekday( $day ) ) ) ;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'church_admin_my_prayer WHERE day'.(int)$day.'="1" AND date_answered is NULL AND people_id="'.(int)$loginStatus->people_id.'" ORDER BY date_started DESC';
		$date_title = esc_html( __( 'First prayed', 'church-admin' ) );
		$date='date_started';
	}
	$prayers_for_today=$wpdb->get_results($sql);
	//church_admin_debug($wpdb->last_query);
	if( empty( $prayers_for_today ) ){
		
		if(!empty($_REQUEST['answered'])){
			$output['content'] .= '<li id="prayerItem" class="prayerItem"><p><strong>'.esc_html( __( 'No answered prayer items', 'church-admin' ) ) .'</strong></p></li>';
		}else{
			$output['content'] .= '<li id="prayerItem" class="prayerItem"><p><strong>'.esc_html( __('No personal prayer items','church-admin') ).'</strong></p><p><button class="button action green" data-tab="add-prayer">'.esc_html( __( 'Add prayer item','church-admin' ) ).' </p></li>';
		}	
	}
	else{
		
		foreach( $prayers_for_today AS $prayer ){

			$output['content'] .= '<li id="prayerItem'.(int)$prayer->prayer_id.'" class="prayerItem swipe" data-key="'.(int)$prayer->prayer_id.'"><p><strong>'.esc_html($prayer->title).'</strong></p><p>'.wp_kses($prayer->description,$allowed_html).'<br/><small>'.esc_html($date_title.' '.mysql2date(get_option('date_format'),$prayer->$date)).'</small></p><div class="prayer-edit prayer-flex hide action" id="prayer-edit-'.(int)$prayer->prayer_id.'" data-tab="edit-my-prayer"  data-id="'.(int)$prayer->prayer_id.'"><img src="./img/edit-white.svg" /></div><div class="prayer-answered prayer-flex hide action" data-tab="answer-my-prayer" id="prayer-answered-'.(int)$prayer->prayer_id.'"  data-id="'.(int)$prayer->prayer_id.'"><img src="./img/answered-white.svg" /></div><div class="prayer-delete prayer-flex hide action" id="prayer-delete-'.(int)$prayer->prayer_id.'" data-tab="delete-my-prayer"  data-id="'.(int)$prayer->prayer_id.'"><img src="./img/delete-white.svg" /></div><div class="expand" data-id="'.(int)$prayer->prayer_id.'"><img src="./img/handle-white.svg" /></div></li>';
		}
	}
	
	$output['content'] .='</ul>';
	$output['content'] .= church_admin_display_churchwide_prayers();
	return $output;

}
function ca_app_delete_prayer($loginStatus)
{
	global $wpdb,$wp_locale;
	if(empty( $loginStatus) ){
		return ca_app_new_login_form('myprayer');
	}

	$ID=!empty($_REQUEST['id'])? (int)$_REQUEST['id']: null;

	if ( empty( $ID ) ) {
		return $output['message']=esc_html( __( 'Prayer not deleted', 'church-admin' ) );
	}
	
	$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_my_prayer WHERE people_id="'.(int)$loginStatus->people_id.'" AND prayer_id="'.(int)$ID.'"');
	
	$output =  ca_app_show_prayer( $loginStatus );
	$output['message']=esc_html( __( 'Prayer deleted', 'church-admin' ) );

	return $output;
}


function church_admin_display_churchwide_prayers()
{
	$out='';
	$todays_prayer ='';
	$prayers = get_option( 'church_admin_churchwide_prayer' );
	$day = date('N');

	if( !empty( $prayers[$day] ) ){
		$todays_prayer = '<h3>'.$prayers['title'].'</h3>'.wpautop( $prayers[$day] );
	}

	return $todays_prayer;





}

function ca_app_volunteer($loginStatus)
{
	$token = !empty($loginStatus->token)?$loginStatus->token:null;
	$output = array( 'token'=>esc_html( $token ),
						'view'=>'html',
						'page_title'=>esc_html( __('Serving','church-admin') )
					);
	
	global $wpdb;
	$content='';
	$ministry_ids=!empty($_REQUEST['ministry_id'])?church_admin_sanitize($_REQUEST['ministry_id']):null;
	if(!empty($ministry_ids))
	{
		foreach( $ministry_ids AS $key=>$ministry_id)
          {
			if(empty($ministry_id)){continue;}
              $person=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,last_name) AS name,email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$loginStatus->people_id.'"');
              if(!empty( $ministry_id) && !empty( $person) )
              {
                church_admin_update_people_meta( (int)$ministry_id,$loginStatus->people_id,'volunteer',date('Y-m-d'));
                $ministry=$wpdb->get_var('SELECT ministry FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int)$ministry_id.'"');
				$token = md5(NONCE_SALT.$people_id);
                $approve=wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=approve-volunteer&token='.$token.'&ministry_id='.(int)$ministry_id.'&people_id='.(int)$people_id,'approve-volunteer');
                $decline=wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=decline-volunteer&token='.$token.'&ministry_id='.(int)$ministry_id.'&people_id='.(int)$people_id,'decline-volunteer');
                $contact=__('No contact details','church-admin');
                if(!empty( $person->email) )  {$contact=$person->email;}
                elseif(!empty( $person->mobile) )  {$contact=$person->mobile;}
                $message='<p>'.esc_html(sprintf(__('%1$s (%2$s) has just volunteered for %3$s, please approve them or get in touch','church-admin'  ),$person->name,$contact,$ministry)).'</p>';
                $message.='<p><a href="'.$approve.'">'.esc_html( __('Approve them','church-admin' ) ).'</a></p>';
                $message.='<p><a href="'.$decline.'">'.esc_html( __('Decline them','church-admin' ) ).'</a></p>';
                $team_contact=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="team_contact" AND ID="'.(int)$ministry_id.'"');
                $team_contact_email=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int) $team_contact.'"');
                if ( empty( $team_contact_email) )$team_contact_email=get_option('church_admin_default_from_email');
                $subject=esc_html(sprintf(__('New volunteer request for %1$s','church-admin' ) ,$ministry));

				church_admin_email_send($team_contact_email,$subject,$message,null,null,null,null,null,TRUE);
              }

             
          }
		  $content.="<p>".esc_html( __('Thank you for volunteering, we will be in touch','church-admin' ) ).'</p>';
	}
	else
	{	
		$content='<p>'.esc_html( __('You can volunteer for various ministries in the church here. The team leaders will be in touch, after you apply.','church-admin' ) ).'</p>';
		$ministries=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_ministries WHERE volunteer=1 ORDER BY ministry ASC');
		if(!empty( $ministries) )
		{
			$content.='<table>';
			foreach($ministries AS $ministry){
				$content .= '<tr><td>'.esc_html( $ministry->ministry).'</td>';
				$already=$wpdb->get_var('SELECT meta_type FROM '.$wpdb->prefix.'church_admin_people_meta WHERE (meta_type="ministry" OR meta_type="volunteer") AND ID="'.(int) $ministry->ID.'" AND people_id="'.(int)$loginStatus->people_id.'"');
				if(!empty( $already) && ($already=='ministry'))  {$content.='<td>'.esc_html( __('Already serving','church-admin' ) ).'</td>';}
				elseif(!empty( $already) && ($already=='volunteer'))  {$content.='<td>'.esc_html( __('Pending request','church-admin' ) ).'</td>';}
				else{
					$content.='<td><input type="checkbox" class="ministry" data-id="'.(int) $ministry->ID.'" /></td>';
				}
			}
			$content.='</table><p><button class="action button" data-tab="save-serving">'.esc_html( __('Apply','church-admin' ) ).'</button></p>';
		}
		else
		{
			$content=__('No ministry opportunities available','church-admin');
		}
	}	
	$output['content']=$content;
	return $output;
}

function church_admin_app_logs()
{
	global $wpdb;

	//sanitize
	$date = !empty($_REQUEST['date']) ? church_admin_sanitize($_REQUEST['date']) : wp_date('Y-m-d');
	//validate
	if(empty($date) || !church_admin_checkdate($date)){
		$date = wp_date('Y-m-d');
	}
	$displayDate = mysql2date(get_option('date_format'),$date);
	//initialise output
	echo '<h3>'.esc_html( __( 'App visits', 'church-admin' ) ).'</h3>';

	echo'<form action="admin.php?page=church_admin/index.php&action=app-visits" method="POST">';
	wp_nonce_field('app-visits');
	echo'<div class="church-admin-form-group"><label>'.esc_html( __('Date','church-admin') ).'</label>';
	echo church_admin_date_picker(NULL,'date',FALSE,'2023-07-01',NULL,'app_date','app_date',FALSE,NULL,NULL,NULL);
	
	echo '<input type="submit" class="button-primary" value="'.__('Go','church-admin').'" />';
	echo '</div></form>';

	//get data
	$results = $wpdb->get_results('SELECT SUM(visits) AS alltime, `page` FROM '.$wpdb->prefix.'church_admin_app_log_visit GROUP BY `page` ORDER BY alltime DESC');
	if (!empty( $results)){

		$total_visits = $all_time_visits = 0;

		$theader = '<tr><th>'.esc_html(__('Page','church-admin')).'</th><th>'.esc_html(sprintf(__('Visits %1$s','church-admin'),$displayDate ) ) .'</th><th>'.esc_html( __('All views since 2nd July 2023','church-admin')).'</th></tr>';
		echo '<h3>'. esc_html( sprintf( __('App views for %1$s','church-admin') ,$displayDate ) ) .'</h3>';
		echo'<table class="church_admin stiped"><thead>'.$theader.'</thead><tbody>';
		foreach($results AS $row){
			$visits=$wpdb->get_var('SELECT visits FROM '.$wpdb->prefix.'church_admin_app_log_visit WHERE page="'.esc_sql($row->page).'" AND visit_date = "'.esc_sql($date).'"');
			if(!empty($row->page)){
				echo'<tr><td>'.esc_html($row->page).'</td><td>'.(int)$visits.'</td><td>'.(int)$row->alltime.'</td></tr>';
				$total_visits += $visits;
				$all_time_visits += $row->alltime;
			}
			
		}
		echo'<tr><td><strong>'.esc_html(__('Totals','church-admin')).'</strong></td><td><strong>'.(int)$visits.'</strong></td><td><strong>'.(int)$all_time_visits.'</strong></td></tr>';
		echo '</tbody></table>';

	}else{
		echo'<p>'. esc_html( sprintf( __('No app views for %1$s'),$displayDate ) ).'</p>';
	}


}

function ca_app_new_password_change($loginStatus){
	global $wpdb;
	if(empty($loginStatus) || !church_admin_level_check('Directory',$loginStatus->user_id)){
		$output=array('message'=>__('You cannot do that ','church-admin'));
		return $output;
	}
	$people_id = !empty($_REQUEST['people_id']) ? church_admin_sanitize($_REQUEST['people_id']) : null;
	$password = !empty($_REQUEST['new_password']) ? church_admin_sanitize($_REQUEST['new_password']) : null;


	//validate
	$errors = array();
	if(empty($password)){$errors[]=__('No password given','church-admin');}
	if(!church_admin_int_check($people_id)){$errors[]=__('Invalid people id','church-admin');}
	$data = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	if(empty($data)){$errors[]=__('Person not found','church-admin');}
	

	if(!empty($errors)){
		$output=array('message'=>implode('<br/>',$errors));
		return $output;
	}


	//check whether there is a user
	if(empty($data->user_id)){

		church_admin_create_user($people_id,$data->household_id,null,$password);

		$output=array('message'=>__('New user account created with password','church-admin'));
		return $output;
	}
	else{
		wp_set_password($password,$data->user_id );
		$output=array('message'=>__('Password updated','church-admin'));
		return $output;
	}



}