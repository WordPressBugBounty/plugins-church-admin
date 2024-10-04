<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
function church_admin_display_classes( $today,$allow_registration=TRUE)
{
	$licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
	global $wpdb,$current_user;
	if(defined('CA_DEBUG') )$wpdb->show_errors();
	$user = wp_get_current_user();

	$out='';
	if ( empty( $today) )
	{
		$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE end_date >= CURDATE() ';
	}
	else
	{
		$sql='SELECT a.* FROM '.$wpdb->prefix.'church_admin_classes a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE a.event_id=b.event_id AND b.start_date=CURDATE() ';
	}
	if(defined('CA_DEBUG') )church_admin_debug( $sql);
	$results=$wpdb->get_results( $sql);
	if(!empty( $results) )
	{
		foreach( $results AS $row)
		{
			$out.=church_admin_display_class( $row->class_id,FALSE,$allow_registration);
		}
		
	}//there are classes
	else
	{
		if ( empty( $today) )$out.='<p>'.esc_html( __('No classes running at the moment','church-admin' ) ).'</p>';
		else{$out.='<p>'.esc_html( __('No classes today','church-admin' ) ).'</p>';}
	}

	return $out;

}
function church_admin_display_class( $class_id=NULL,$show=TRUE,$allow_registration=TRUE)
{

	if(defined("CA_DEBUG") )church_admin_debug("*******************\r\v Class Booking");
	global $wpdb,$current_user, $church_admin_for_email;
	$wpdb->show_errors();
	$user = wp_get_current_user();
	
	$out='';
	$sql='SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"';
    
	$row=$wpdb->get_row( $sql);
	if ( empty( $row) )  {
		return esc_html(__('No class found','church-admin') ) ;
	}
	/*******************
	* 	Process details
	*******************/
	if(!empty( $_POST['class-register'] ) )
	{
		
		/*******************
		* 	Class Booking
		*******************/
		$class_id= !empty($_POST['class_id'])? sanitize_text_field(stripslashes($_POST['class_id'])): null;
		if (empty( $class_id) || !church_admin_int_check($class_id) ){
			return esc_html(__('No class selected','church-admin'));
		}
		
		if(is_user_logged_in() )
		{
			/**********************************
			* 	Logged in User
			* 	expecting array of people_id
			**********************************/
			$userDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
			$people_id=array();
			if(!empty( $_POST['people_id'] ) )
			{
				$people_ids = church_admin_sanitize($_POST['people_id']);
				foreach( $people_ids AS $key=>$people_id)
				{
					if(!church_admin_int_check($people_id)){continue;}
					$personDetail=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$userDetails->household_id.'" AND people_id="'.(int)$people_id.'"');
					if(!empty( $personDetail) )
					{
						$people_ids[]=$personDetail->people_id;
						church_admin_update_people_meta((int)$class_id,(int)$people_id,'class');
					}
				}
			}
			$booking_email=$userDetails->email;
		}
		else
		{
			if ( empty( $_POST['funky-bit'] ) )return __('You appear to be a spammer - not checked the human bit','church-admin');
			//sanitize
			$booking_email=!empty($_POST['booking_email'])? trim(sanitize_text_field( stripslashes( $_POST['booking_email'] ) )):null;;
            
			$first_name=!empty($_POST['booking_first_name'])?trim(sanitize_text_field( stripslashes( $_POST['booking_first_name'] )) ):null;
            $last_name=trim(sanitize_text_field( stripslashes( $_POST['booking_last_name'] )) );
            $email_send=0;
            if(!empty( $_POST['email_send'] ) )$email_send=1;
            //do spam checks
            if(church_admin_spam_check( $booking_email,'email') )exit(__('You appear to be a spammer- email') );
            if(church_admin_spam_check( $first_name,'text') )exit(__('You appear to be a spammer - first name') );
            if(church_admin_spam_check( $last_name,'text') )exit(__('You appear to be a spammer - last name') );
            $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE first_name="'.esc_sql( $first_name).'" AND last_name="'.esc_sql( $last_name).'" AND email="'.esc_sql( $booking_email).'"');
            $message='';
            if(!$people_id)
            {
                 //probably not in database!
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (privacy)VALUES("1")');
                $household_id=$wpdb->insert_id;
                $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,email,email_send,household_id,first_registered)VALUES("'.esc_sql( $first_name).'","'.esc_sql( $last_name).'","'.esc_sql( $booking_email).'","'.$email_send.'","'.(int)$household_id.'","'.esc_sql(wp_date('Y-m-d')).'")');
                $people_id=$wpdb->insert_id;
                church_admin_email_confirm( $people_id);
                $message='Please confim your email by clicking the link in the email we have just sent you.';
            }
            church_admin_update_people_meta((int)$class_id,(int)$people_id,'class');
            
            $people_ids=array( $people_id);
		}
		$out.='<div class="notice notice-sucess inline"><h2>'.esc_html(sprintf(__('%1$s Class Booked. ','church-admin' ),$row->name) ).'</h2></div>';
		church_admin_class_booking_email( $class_id,$people_ids,$booking_email);
	}
	elseif(!empty( $_POST['class_check_in'] ) )
	{
		/*******************
		* 	Check in Class
		*******************/		
	}
	else
	{
		/*******************
		* 	Show details
		*******************/
		if ( empty( $show) )$out.='<h2 class="ca-class-toggle" id="class-'.(int)$class_id.'">'.esc_html( $row->name).'</h2><div class="class-'.(int)$class_id.'" >';
		else $out.='<h2>'.esc_html( $row->name).'</h2><div class=""class-'.(int)$class_id.'">';
		if(!empty( $row->description) )$out.=$row->description;
		if(!empty( $row->next_start_date) )$out.='<p>'.esc_html( mysql2date(get_option('date_format'),$row->next_start_date) );
		if(!empty( $row->end_date) )$out.=' - '.esc_html(mysql2date(get_option('date_format'),$row->end_date) );
		$out.='</p><p>';
		switch( $row->recurring)
		{
			case'1':
				$out.=esc_html(sprintf(__('From %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$row->start_time),mysql2date(get_option('time_format'),$row->end_time) ) );
			break;    
			case'7':
				$out.=esc_html(sprintf(__('Weekly from %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$row->start_time),mysql2date(get_option('time_format'),$row->end_time) ) );
			break; 
			case'14':
				$out.=esc_html(sprintf(__('Fortnightly from %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$row->start_time),mysql2date(get_option('time_format'),$row->end_time) )); 
			break;  
			case 'm':
				$out.=esc_html(sprintf(__('Monthly from %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$row->start_time),mysql2date(get_option('time_format'),$row->end_time) ) ); 
			break; 
			case 'a':
				$out.=esc_html(sprintf(__('Annually from %1$s to %2$s','church-admin' ) ,mysql2date(get_option('time_format'),$row->start_time),mysql2date(get_option('time_format'),$row->end_time) )); 
			break;  
			 
		}
		$out.='<p>';
		
		if(!empty($church_admin_for_email)||(!empty($_REQUEST['action']) && $_REQUEST['action']=='ca_app')){
			//app or email so don't give booking form, provide link
			$ID=$wpdb->get_var ('SELECT ID FROM '.$wpdb->posts.' WHERE post_status="publish" AND post_content LIKE \'%[church_admin type="class" class_id="'.(int)$class_id.'"]%\'');
			//church_admin_debug($wpdb->last_query);
			if(!empty($ID))
			{
				$out.='<p><a class="button blue" href="'.esc_url( get_permalink( $ID ) ).'">'.esc_html( __("Class booking page") ).'</a></p>';
			}
			return $out;
		}
		elseif(!is_user_logged_in() )
		{
			/*******************
			* 	Not logged in
			*******************/
			$out.=church_admin_first_step((int)$row->class_id);
			$out.='<div id="form'.(int)$row->class_id.'" style="display:none">';
			$out.=church_admin_class_booking_form( $row->class_id,NULL);
			$out.='</div><!--form'.(int)$row->class_id.'-->';
		}
		else
		{
			/*******************
			* 	Logged in, simple booking
			*******************/
			if(empty($user)){$user=wp_get_current_user();}
			$household_id=NULL;
			$household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
			$out.=church_admin_class_booking_form( $class_id,$household_id);
		}
		$out.='<hr/></div><!--details for '.esc_html( $row->name).'-->';
	}	
		
	return $out;

}

function church_admin_class_booking_form( $class_id,$household_id)
{
	global $wpdb;


	if ( empty( $household_id) )
	{
		$out='<form action="" method="POST"><h3>'.esc_html( __('Booking form','church-admin')).'</h3>';
		$out.='<table class="form-table"><tr><th scope="row">'.esc_html( __('First name','church-admin') ).'</th><td><input type="text" required="required" name="booking_first_name" /></td></tr><tr><th scope="row">'.esc_html( __('Last name','church-admin')).'</th><td><input type="text" required="required" name="booking_last_name" /></td></tr><tr><th scope="row">'.esc_html(__('Email','church-admin')).'</th><td><input type="text" required="required" class="booking-email ca-email" name="booking_email" /></td></tr>';
		if(is_user_logged_in() )$out.='<tr><th scope="row">'.esc_html(__('Do you want to be on our weekly email list?','church-admin')).'</th><td><input type="checkbox" name="email_send" /></td></tr>';
		
		$out.='<tr><td colspan=2><input type="hidden" name="funky-bit" class="funky-bit" /><input type="hidden" name="class_id" value="'.(int)$class_id.'" /><input type="hidden" name="class-register" value="yes" /><input type="submit" value="'.esc_html(__('Book','church-admin') ).'" /></td></tr></table></form>';
		
	}	
	else
	{
		$household=$wpdb->get_results('SELECT *,people_id FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY people_order');
		$out='<h3 class="ca-book-in">'.esc_html( __('Book in members of your household','church-admin' ) ).'</h3>';
		$out.='<form action="" method="POST"><table class="form-table">';
		foreach( $household AS $person)
		{
			$out.='<tr><td style="width:50px;"><input type="checkbox" value="'.(int)$person->people_id.'" name="people_id[]" ';
			$check=$wpdb->get_var('SELECT meta_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="class" AND people_id="'.(int)$person->people_id.'" AND ID="'.(int)$class_id.'"');
			if( $check) $out.=' checked="checked" ';
			$out.='" /></td><td  class="ca-names">'.esc_html(church_admin_formatted_name( $person) ).'</td></tr>';
		}
		$out.='<tr><td colspan=2><input type="hidden" name="class_id" value="'.(int)$class_id.'" /><input type="hidden" name="class-register" value="yes" /><input type="submit" value="'.esc_html( __('Book','church-admin' ) ).'" /></td></tr></table></form>';
	}
	return $out;
}



function church_admin_class_booking_email( $class_id,$people_ids,$booking_email)
{
    global $wpdb;
    if (empty( $class_id) )return;
    if ( empty( $people_ids) )return;
    if(!is_array( $people_ids) )$people_ids=array( $people_ids);
    
    $class_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_classes WHERE class_id="'.(int)$class_id.'"');
    $people=array();
    foreach( $people_ids AS $key=>$people_id){
		$people[]=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
	}
    $message='<p>'.esc_html(sprintf(__('Thanking you for signing up for %1$s. The first occasion is %2$s at %3$s. Further details will follow.','church-admin'  ),esc_html( $class_details->name),mysql2date(get_option('date_format'),$class_details->next_start_date),mysql2date(get_option('time_format'),$class_details->start_time) ) ) .'</p>';
    $message.='<p>'.esc_html(__('On this booking','church-admin') ).'</p>';
    foreach( $people AS $person)
    {
        $message.='<p>'.esc_html(church_admin_formatted_name( $person) ).'</p>';
    }
    $subject=esc_html(sprintf(__('Class booking for %1$s','church-admin' ) ,$class_details->name) );

	church_admin_email_send($booking_email,$subject,$message,null,null,null,null,null,FALSE);


    
}