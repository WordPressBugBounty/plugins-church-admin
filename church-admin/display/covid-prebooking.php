<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly
function church_admin_covid_attendance( $service_id,$mode="individuals",$maxfields=10,$days=7,$adminEmail=NULL,$emailText='')
{
    church_admin_debug('**** church_admin_covid_attendance *****');
    church_admin_debug(func_get_args());
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
    
    global $wpdb,$church_admin_url;
    if(!isset( $adminEmail) )
    {
        $adminEmails=array(get_option('church_admin_default_from_email') );
    }
    elseif(!empty( $admin_email)&&$admin_email=='OFF')
    {
        $adminEmails=NULL;
    }
    else
    {
        $adminEmails=explode(",",$adminEmail);
    }
    $out='<div class="church-admin-service-booking">';
    if(!empty( $_GET['cancel-service-booking'] ) )
    {
        $out='<div id="myModal" class="ca-modal alignfull"><div class="ca-modal-content"><span class="ca-close">X</span><h2>'.esc_html( __('Sorry that cancel button no longer works, please contact the church to cancel','church-admin' ) ).'</h2></div></div>';
        $out.='<script>jQuery(document).ready(function( $)  { 
        $("body .ca-close").click(function()  {$(".ca-modal").css("display","none");})
               });
              
                </script>';
    }
    if(!empty( $_GET['cancel-service-bookingv2'] ) )
    {
        
        if(defined('CA_DEBUG') )church_admin_debug('Attempting to cancel service booking '.$_GET['cancel-service-bookingv2'] );
        $check=$wpdb->get_results('SELECT a.*,b.*,c.* FROM '.$wpdb->prefix.'church_admin_covid_attendance a,'.$wpdb->prefix.'church_admin_calendar_date b,'.$wpdb->prefix.'church_admin_services c WHERE a.date_id=b.date_id AND a.service_id=c.service_id AND a.token="'.esc_sql( sanitize_text_field(stripslashes($_GET['cancel-service-bookingv2'] ) )).'"');
        if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
        
        if(!empty( $check) )
        {
            $people=array();
            foreach( $check AS $row)
            {
                $people[]=esc_html( $row->people_id);
                $cancelled_service_details=esc_html(sprintf(__('%1$s on %2$s at %3$s','church-admin' ) ,$row->service_name,mysql2date(get_option('date_format'),$row->start_date),mysql2date(get_option('time_format'),$row->start_time)) );
            }
            $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE token="'.esc_sql(sanitize_text_field( stripslashes($_GET['cancel-service-bookingv2']) ) ).'"');
            $out='<div id="myModal" class="ca-modal"><div class="ca-modal-content"><span class="ca-close">X</span><h2>'.esc_html( __('Service booking has been cancelled','church-admin' ) ).'</h2>'.esc_html( $cancelled_service_details).'<br>'.esc_html(implode(", ",$people)).'</div></div>';
            if(defined('CA_DEBUG') )church_admin_debug( $out);
            if(!empty( $adminEmails) )
            {
                //email admins
         
                foreach( $adminEmails AS $key=>$email)
                {
                    $emailMessage=$out.'<p><a href="'.wp_nonce_url($church_admin_url.'&action=service-prebooking&amp;section=services','service-prebooking').'">'.esc_html( __('View current bookings','church-admin' ) ).'</a></p>';
             
                    church_admin_email_send($email,esc_html(__('Service prebooking','church-admin' ) ),$emailMessage,null,null,null,null,null,TRUE);
                }
            
            }
            $out.='<script>jQuery(document).ready(function( $)  { 
        $("body .ca-close").click(function()  {$(".ca-modal").css("display","none");})
               });
              
                </script>';
            
        }
        return $out;
    }
    $headers=array();
    
    $wpdb->show_errors;
    
    $serviceDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
    if(empty($serviceDetails)){
        return esc_html(sprintf( __('Service not found from ID %1$s','church-admin'),(int)$service_id));

    }
    if(empty($serviceDetails->max_attendance)){
        return esc_html(__('No maximum attendance specified','church-admin'));

    }
    if(empty($serviceDetails->bubbles)){
        return esc_html(__('No maximum bubbles specified','church-admin'));

    }

        if(!empty( $serviceDetails)&&(!empty( $serviceDetails->max_attendance)||!empty( $serviceDetails->bubbles) ))
        {
            $out.='<h2 id="prebook-sunday">'.esc_html(sprintf(__('Service prebooking for %1$s','church-admin' ) ,$serviceDetails->service_name) ).'</h2>';
            /****************************************************************
            *
            *   Handle Booking
            *
            *****************************************************************/
           if(!empty( $_POST['save-service-booking'] ) )
           {
                if(defined('CA_DEBUG') )church_admin_debug("**********************************************\r\nService Prebooking Attempt ".date("Y-m-d h:i:s") );
               if(defined('CA_DEBUG') )church_admin_debug(print_r( $_POST,TRUE) );
           }
            
            /*******************************************
            *
            *   WAITING LIST  
            *
            *********************************************/
           if(!empty( $_POST['date_id'] )&&!empty( $_POST['event_id'] )&&!empty( $_POST['service_id'] )&&!empty( $_POST['waiting_list'] ) && wp_verify_nonce( $_POST['save-waiting'], 'save-waiting' )&&(empty( $_POST['person_comment'] ) )&&$service_id==(int)$_POST['service_id']&& !empty( $_POST['person_email'] )&&is_email( $_POST['person_email'] )&& !empty( $_POST['person_name'] )&& isset( $_POST['person_phone'] ) )
           {
                /*******************************************
                *
                *   Use spam checker   
                *
                *********************************************/
                if ( empty( $_POST['real-person'] ) )exit('<p>'.esc_html( __('You forgot to check the I am a real person form field',"church-admin")).'</p>'); 
                if(church_admin_spam_check( sanitize_text_field(stripslashes($_POST['person_email']) ),'email') )exit('<p>'.esc_html( __('That appears to be spam email',"church-admin")).'</p>');  
                if(church_admin_spam_check( sanitize_text_field(stripslashes($_POST['person_phone']) ),'text') )exit('<p>'.esc_html( __('That appears to be spam - phone',"church-admin")).'</p>'); 
                foreach( $_POST['person_name'] AS $key=>$name)
                {
                   if(!empty( $name) && church_admin_spam_check( sanitize_text_field(stripslashes($name) ),'text') )exit('<p>'.esc_html( __('That appears to be spam - one of the name form fields: '.$name,"church-admin")).'</p>');  
                }
                $event_id=(int)$_POST['event_id'];
                $date_id=(int)$_POST['date_id'];
                //$service_id=(int)$_POST['service_id'];
                $email=sanitize_text_field( stripslashes($_POST['person_email'] ) );
                $phone=sanitize_text_field( stripslashes($_POST['person_phone'] ) );
                $maxBubbleID=$wpdb->get_var('SELECT MAX(bubble_id) FROM '.$wpdb->prefix.'church_admin_covid_attendance');
                $bubble_id=$maxBubbleID+1;
                foreach( $_POST['person_name'] AS $key=>$name)
                {
                    if(!empty( $name)&&is_email(sanitize_text_field( stripslashes($_POST['person_email'] ) )))
                    {
                        $person=esc_sql(sanitize_text_field( stripslashes($name) ));
                        
                    
                        $check=$wpdb->get_var('SELECT covid_id FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE people_id="'.esc_sql($person).'" AND email="'.esc_sql($email).'" AND phone="'.esc_sql($phone).'" AND service_id="'.(int)$service_id.'" AND date_id="'.(int)$date_id.'"');
                        
                        if ( empty( $check) )
                        {
                            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_covid_attendance (people_id, email, phone,service_id,date_id,bubble_id,booking_date,waiting_list)VALUES("'.esc_sql($person).'","'.esc_sql($email).'","'.esc_sql($phone).'","'.(int)$service_id.'","'.(int)$date_id.'","'.(int)$bubble_id.'","'.esc_sql( wp_date('Y-m-d H:i:s') ) .'","1")');
                            $people[]='<p>'.esc_html( $name).'</p>';
                            
                        }
                    }
                }
                if(!empty( $people) )
                {
                    //sanitize
                    $event_id = !empty($_POST['event_id']) ? sanitize_text_field(stripslashes($_POST['event_id'])):null;
                    $date_id = !empty($_POST['date_id']) ? sanitize_text_field(stripslashes($_POST['date_id'])):null;
                    $email = !empty($_POST['person_email']) ? sanitize_text_field(stripslashes($_POST['person_email'])):null;
                    $phone = !empty($_POST['person_phone']) ? sanitize_text_field(stripslashes($_POST['person_phone'])):null;
                    //validate
                    if(empty($event_id)|| !ctyep_digit($event_id)){exit();}
                    if(empty($date_id)|| !ctyep_digit($date_id)){exit();}
                    if(empty($email) || !is_email($email)){exit(__('Invalid email','church-admin'));}

                    $nextService=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_services b  WHERE a.event_id="'.(int)$event_id.'" AND a.date_id="'.(int)$date_id.'" AND a.event_id=b.event_id LIMIT 1');

                    $message=esc_html(sprintf(__('Waiting list message for %1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$nextService->start_date),$nextService->start_time)); 
                    
                    $message.='<table><tr><td>'.esc_html( __('Email','church-admin' ) ).'</td><td>'.esc_html($email ).'</td></tr>';
                    $message.='<tr><td>'.esc_html( __('Phone','church-admin' ) ).'</td><td>'.esc_html($phone).'</td></tr>';
                    $message.='<tr><td>'.esc_html( __('Waiting list names','church-admin' ) ).'</td><td>'.esc_html(implode("",$people)).'</td></tr></table>';
                    church_admin_email_send($email,esc_html(__('Service waiting list','church-admin' ) ),$message,null,null,null,null,null,TRUE); 
                            
                        if(!empty( $adminEmails) )
                        {     //admin email version
                            foreach( $adminEmails AS $key=>$email)
                            {
                                church_admin_email_send($email,esc_html(__('Service waiting list','church-admin' ) ),$message,null,null,null,null,null,TRUE); 
                            }
                            remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
                       }
                       
                    $out.=wp_kses_post($message);
                    //$out.='<div id="myModal" class="ca-modal"><div class="ca-modal-content"><span class="ca-close">X</span>'.$message.' </div></div>';   
                }//else{$out.='<div id="myModal" class="ca-modal"><div class="ca-modal-content"><span class="ca-close">X</span>'.esc_html( __('You only need to do it once!','church-admin' ) ).' </div></div>';   
               
           }  
           /*******************************************
           *
           *   REAL BOOKING 
           *
           *********************************************/
            elseif(!empty( $_POST['date_id'] )&&!empty( $_POST['event_id'] )&&!empty( $_POST['service_id'] )&&!empty( $_POST['save-service-booking'] ) &&(empty( $_POST['person_comment'] ) )&&$service_id==(int)$_POST['service_id'] )
            {
                /*******************************************
                *
                *   Use spam checker   
                *
                *********************************************/
                foreach( $_POST['person_name'] AS $key=>$name)
                {
                   if(!empty( $name)&& church_admin_spam_check( sanitize_text_field(stripslashes($name) ),'text') ){
                    exit('<p>'.esc_html( __('That appears to be spam - one of the name form fields',"church-admin")).'</p>');  
                   }
                }
                if(church_admin_spam_check(  sanitize_text_field(stripslashes($_POST['person_email']) ),'email') )exit('<p>'.esc_html(__('That appears to be spam - the email address form field',"church-admin")).'</p>');  
                if(church_admin_spam_check(  sanitize_text_field(stripslashes($_POST['person_phone']) ),'text') )exit('<p>'.esc_html(__('That appears to be spam - the phone number form field',"church-admin")).'</p>'); 
                if ( empty( $_POST['real-person'] ) )exit('<p>'.esc_html(__('You forgot to check the I am a real person form field',"church-admin")).'</p>'); 
                
                if(defined('CA_DEBUG') )church_admin_debug('Attempting to save');
                //actual booking
                //sanitize
                $event_id=!empty($_POST['event_id'])?sanitize_text_field(stripslashes($_POST['event_id'])):null;
                $date_id=!empty($_POST['date_id'])?sanitize_text_field(stripslashes($_POST['date_id'])):null;
                //$service_id=!empty($_POST['service_id'])?sanitize_text_field(stripslashes($_POST['service_id'])):null;
                $email= !empty($_POST['person_email']) ? sanitize_text_field( stripslashes($_POST['person_email']) ):null;
                $phone=!empty($_POST['person_phone']) ?sanitize_text_field( stripslashes($_POST['person_phone']) ):null;

                //validate
                if(empty($event_id) ||!church_admin_int_check($event_id)){ return __('No event selected','church-admin');}
                if(empty($date_id) ||!church_admin_int_check($date_id)){ return __('No date selected','church-admin');}
                if(empty($service_id) ||!church_admin_int_check($service_id)){ return __('No service selected','church-admin');}
                if(empty($name) || !is_email($email )){ return __('Invalid email','church-admin');}
                if(empty($phone) ){ return __('No phone number','church-admin');}

                $check=$wpdb->get_var('SELECT a.service_id FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b  WHERE a.event_id=b.event_id AND a.service_id="'.(int)$service_id.'" AND b.event_id="'.(int)$event_id.'" AND b.date_id="'.(int)$date_id.'"');
                if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
                if( $check)
                {
                    $nextService=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_services b  WHERE a.event_id="'.(int)$event_id.'" AND a.date_id="'.(int)$date_id.'" AND a.event_id=b.event_id LIMIT 1');
                    if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
                    $bookingTitle=esc_html(sprintf(__('Service pre-booking for %1$s on %2$s at %3$s','church-admin' ) ,$nextService->service_name, mysql2date(get_option('date_format'),$nextService->start_date),$nextService->start_time));
                    $message='<h3>'.esc_html( $bookingTitle).'</h3>';
                    
                    
                    $people=array();
                    $date_id=(int)$_POST['date_id'];
                    $maxBubbleID=$wpdb->get_var('SELECT MAX(bubble_id) FROM '.$wpdb->prefix.'church_admin_covid_attendance');
                    $bubble_id=$maxBubbleID+1;
                    $token=MD5("Service Booking - ".$bubble_id);
                    $names = !empty($_POST['person_name'] )? church_admin_sanitize($_POST['person_name'] ):array();
                    foreach( $_POST['person_name'] AS $key=>$person)
                    {
                        if(empty( $name)){ continue;}
                        
                            
                        
                        $check=$wpdb->get_var('SELECT covid_id FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE people_id="'.esc_sql($person).'" AND email="'.esc_sql($email).'" AND phone="'.esc_sql($phone).'" AND service_id="'.(int)$service_id.'" AND date_id="'.(int)$date_id.'"');
                        
                        if ( empty( $check) )
                        {
                            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_covid_attendance (people_id, email, phone,service_id,date_id,bubble_id,token,booking_date)VALUES("'.esc_sql($person).'","'.esc_sql($email).'","'.esc_sql($phone).'","'.(int)$service_id.'","'.(int)$date_id.'","'.(int)$bubble_id.'","'.esc_sql($token).'","'.esc_sql(wp_date('Y-m-d H:i:s')).'")');
                            $people[]='<p>'.esc_html( $name).'</p>';
                        }else{
                            $message.=esc_html(sprintf(__('%1$s already booked in','church-admin' ) , $name) ).'<br>';
                        }
                         

                    }
                    
                    if(!empty( $people) )
                    {//only send booking if not a repeat
                        
                        
                        $message.='<p><strong>'.esc_html( __('Booking names','church-admin' ) ).'</strong></p>'.esc_html(implode("",$people));
                        $message.='<p>'.esc_html(sprintf(__('Booking ID is %1$s','church-admin' ) ,(int)$bubble_id ) ).'</p>';
                        $cancelURL=add_query_arg( 'cancel-service-bookingv2', $token, get_permalink( ) ); 
                        $emailMessage=$message.'<!--Button--><center><table align="center" cellspacing="0" cellpadding="0" width="100%"><tr><td align="center" style="padding: 10px;"><table border="0" class="mobile-button" cellspacing="0" cellpadding="0"><tr><td align="center" bgcolor="#2b3138" style="background-color: #2b3138; margin: auto; max-width: 600px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; padding: 15px 20px; " width="100%"><!--[if mso]>&nbsp;<![endif]--><a href="'.esc_url($cancelURL).'" target="_blank" style="16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; font-weight:normal; text-align:center; background-color: #2b3138; text-decoration: none; border: none; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; display: inline-block;"><span style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; font-weight:normal; line-height:1.5em; text-align:center;">'.esc_html( __('Cancel Booking','church-admin' ) ).'</span></a><!--[if mso]>&nbsp;<![endif]--></td></tr></table></td></tr></table></center>';
                        
                        //booker email version
                        church_admin_email_send($email,esc_html( $bookingTitle),$emailMessage.wpautop( $emailText),null,null,null,null,null,TRUE); 
                        
                        if(!empty( $adminEmails) )
                        {     //admin email version
                            foreach( $adminEmails AS $key=>$email)
                            {
                                $emailMessage.='<p><a href="'.wp_nonce_url($church_admin_url.'&action=service-prebooking&amp;section=services','service-prebooking').'">'.esc_html( __('View current bookings','church-admin' ) ).'</a></p>';
                                church_admin_email_send($email,esc_html( $bookingTitle),$emailMessage.wpautop( $emailText),null,null,null,null,null,TRUE); 
                             
                            }
                        }
                     
                    }
                    $out.=$message;
                    //$out.='<div id="myModal" class="ca-modal alignfull"><div class="ca-modal-content"><span class="ca-close">X</span>'.$message.'</div></div>';
                }
               
            }
            else
            {
                $date_id = !empty($_POST['date_id'])? sanitize_text_field(stripslashes($_POST['date_id'] )):null;
                //$service_id = !empty($_POST['service_id'])? sanitize_text_field(stripslashes($_POST['service_id'] )):null;
                /****************************************************************
                *
                *   Booking form
                *
                *****************************************************************/
                if(is_user_logged_in() )
                { 
                    $user=wp_get_current_user();
                    $household=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
                    if(!empty( $household) )
                    {
                        $household_id=$household->household_id;
                        if(!empty( $household->email) )$email=$household->email;
                        if(!empty( $household->mobile) )$mobile=$household->mobile;
                    }
                    if(!empty( $household_id) )$people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'"');
                }
                $services=$wpdb->get_results('SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE a.event_id="'.(int)$serviceDetails->event_id.'" AND a.service_id="'.(int)$service_id.'" AND a.event_id=b.event_id AND b.start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL '.(int)$days.' DAY) ORDER BY b.start_date,b.start_time ASC');
                if(!empty( $services) )
                {
                    $out.='<form action="'.esc_url(get_permalink()).'" method="POST">';
                    $out.='<p><select name="date_id">';
                    $first=$option='';
                    foreach( $services AS $service)
                    {
                        if(!empty( $date_id  )&& $date_id == $service->date_id)
                        {
                            $first='<option selected="selected" value="'.(int)$service->date_id.'">'.esc_html(sprintf(__('%1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$service->start_date),$service->start_time)).'</option>';
                        }else {
                            $option.='<option value="'.(int)$service->date_id.'">'.esc_html(sprintf(__('%1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$service->start_date),$service->start_time)).'</option>';
                        }
                    }
                    $out.=$first.$option.'</select> &nbsp;<input type="hidden" name="service_id" value="'.(int)$service->service_id.'" /><input type="submit" class="btn btn-danger button-primary" value="'.esc_html( __('Choose service date and time','church-admin' ) ).'" /></p></form>';
                }
                $nextService=NULL;
                if(!empty( $date_id )&&!empty($service_id)  )
                {
                    $nextService=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_services b  WHERE a.event_id=b.event_id AND a.date_id="'.(int)$_POST['date_id'].'" AND b.service_id="'.(int)$service_id.'"');
                }
                if ( empty( $nextService) )
                {
                    $sql='SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_services b  WHERE b.service_id="'.(int)$service_id.'" AND a.event_id="'.(int)$serviceDetails->event_id.'" AND unix_timestamp(CONCAT_WS(" ", a.start_date,a.start_time) )>="'.time().'" AND a.event_id=b.event_id ORDER BY a.start_date ASC LIMIT 1';
                    $nextService=$wpdb->get_row($sql);
                    church_admin_debug('NEXT SERVICE QUERY');
                    church_admin_debug($sql);
                }
                
                if(!empty( $nextService) )
                {
                   
                    
                   
                    if( $mode=="bubbles"||$mode=="bubble")
                    {
                        //booking by bubbles
                        $currentBubbles=$wpdb->get_var('SELECT COUNT(DISTINCT(bubble_id) ) FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE date_id="'.(int)$nextService->date_id.'" AND service_id="'.(int)$nextService->service_id.'"');
                        
                        if ( empty( $currentBubbles) )$currentBubbles=0;
                        $out.='<h3>'.esc_html(sprintf(__('Booking availability for %1$s on %2$s','church-admin' ) ,$serviceDetails->service_name,mysql2date(get_option('date_format'),$nextService->start_date) )).'</h3>';
                        $out.='<p><strong>'.esc_html(sprintf(__('%1$s household bubbles booked out of %2$s so far','church-admin' ) ,$currentBubbles,$serviceDetails->bubbles)).'</strong></p>';
                        $left=(int)$serviceDetails->bubbles-$currentBubbles;
                    }
                    else
                    {
                    
                        $current_attendance=$wpdb->get_var('SELECT COUNT(people_id) FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE waiting_list=0 AND date_id="'.(int)$nextService->date_id.'" AND service_id="'.(int)$nextService->service_id.'"');
                        if ( empty( $current_attendance) )$current_attendance=0;
                        $out.='<h3>'.esc_html(sprintf(__('Booking availability for %1$s on %2$s','church-admin' ) ,$serviceDetails->service_name,mysql2date(get_option('date_format'),$nextService->start_date) ) ).'</h3>';
                        $out.='<p><strong>'.esc_html(sprintf(__('%1$s places booked out of %2$s so far','church-admin' ) ,$current_attendance,$serviceDetails->max_attendance)).'</strong></p>';
                        $left=(int)$serviceDetails->max_attendance-$current_attendance;
                    }
                     
                    if( $left>0)
                    {
                       
                            
                            $fields=0;
                            if( $mode=="bubbles"||$mode=='bubble')//add bubble in 2021-04-29
                            {
                                //no of form fields equal to max bubble size
                                $fields=$serviceDetails->bubble_size;
                               
                            }
                            else
                            {
                                 $fields=min( $left,$maxfields);
                            }
                            //added form only shown if some fields!
                            if(!empty( $fields)&&$fields>0)
                            {
                                $out.='<h3>'.esc_html( __('Just fill in this form to book ','church-admin' ) ).'</h3>';
                                $out.='<form action="" method="post" autocomplete="off">';
                                $out.='<div class="church-admin-form-group" ><label>'.esc_html( __('Booking email','church-admin' ) ).'</label><input type="text" class="church-admin-form-control"  name="person_email" required="required"';
                                if(!empty( $email) )$out.=' value="'.esc_html( $email).'" ';
                                $out.='/></div>';
                                $out.='<div class="church-admin-form-group" ><label>'.esc_html( __('Contact number','church-admin' ) ).'</label><input type="text" class="church-admin-form-control"  name="person_phone" required="required"';
                                if(!empty( $mobile) )$out.=' value="'.esc_html( $mobile).'" ';    
                                $out.='/></div>';
                                if(is_user_logged_in() )
                                {
                                if(!empty( $people) )
                                    {
                                            $names=array();
                                            foreach( $people AS $person)$names[]=implode(" ",array_filter(array( $person->first_name,$person->prefix,$person->last_name) ));
                                            for ( $x=0; $x<$fields; $x++)
                                            {
                                                $out.='<div class="church-admin-form-group" ><label>'.esc_html( __('Name','church-admin' ) ).'</label><input type="text" readonly onfocus="if (this.hasAttribute(\'readonly\') ) {
                                                    this.removeAttribute(\'readonly\');
                                                    // fix for mobile safari to show virtual keyboard
                                                    this.blur();    this.focus();  }"  autocomplete="off" class="church-admin-form-control camelcase" name="person_name[]"';
                                                if(!empty( $names[$x] ) )$out.=' value="'.esc_html( $names[$x] ).'" ';
                                                $out.='/></div>';
                                            }
                                    }
                                    
                                }
                                else
                                {
                                    for ( $x=1; $x<=$fields; $x++)
                                    {
                                        $out.='<div class="church-admin-form-group" ><label>'.esc_html( __('Name','church-admin' ) ).'</label><input type="text" readonly onfocus="if (this.hasAttribute(\'readonly\') ) {
                                            this.removeAttribute(\'readonly\');
                                            // fix for mobile safari to show virtual keyboard
                                            this.blur();    this.focus();  }"  autocomplete="off" class="church-admin-form-control camelcase" name="person_name[]" /></div>';
                                    }
                                }
                                $out.=wp_nonce_field('save-service-booking','save-service-booking',true,false);
                                $out.='<div class="all-about-jesus"></div>';
                                // honeypot field not visible to real people
                                $out.='<p class="ca-winnie-the-pooh"><label>Give me some honey</label><input type="text" name="person_comment"></p>';
                                $out.='<p><input type="hidden" name="event_id" value="'.(int)$nextService->event_id.'" /><input type="hidden" name="service_id" value="'.(int)$nextService->service_id.'" /><input type="hidden" name="date_id" value="'.(int)$nextService->date_id.'" /><input type="submit"  value="'.esc_html( __('Book','church-admin' ) ).'" class="btn btn-danger button-primary" /></p></form>';
                            }else{
                                //no fields
                                $out.='<h3>'.esc_html( __('This service appears to be fully booked','church-admin' ) ).'</h3>';
                                
                            }
                    }
                    else
                    {//no space left so offer waiting list
                        $out.='<h3 style="color:red">'.esc_html( __('This service is fully booked','church-admin' ) ).'</h3>';
                        $out.='<p>'.esc_html( __('Would you like to be added to the waiting list in case of cancellations?','church-admin' ) ).'</p>';
                        $out.='<form action="" method="post">';
                       
                        $out.='<div class="church-admin-form-group" ><label>'.esc_html( __('Your email','church-admin' ) ).'</label><input type="text" class="church-admin-form-control"  name="person_email" required="required" /></div>';
                        $out.='<div class="church-admin-form-group" ><label>'.esc_html( __('Your phone number','church-admin' ) ).'</label><input type="text" class="church-admin-form-control"  name="person_phone" required="required" /></div>';
                        for ( $x=1; $x<=5; $x++)
                        {
                            $out.='<div class="church-admin-form-group" ><label>'.esc_html( __('Name','church-admin' ) ).'</label><input type="text" onfocus="this.removeAttribute(\'readonly\');" readonly  class="church-admin-form-control camelcase" name="person_name[]" /></div>';
                        }
                        $out.='<div class="all-about-jesus"></div>';
                        //note this next field is a honey pot not viewable to real people
                        $out.='<p class="ca-winnie-the-pooh"><label>Give me some honey</label><input type="text" name="person_comment"></p>';
                        $out.=wp_nonce_field('save-waiting','save-waiting',TRUE, FALSE);
                        $out.='<p><input type="hidden" name="waiting_list" value="TRUE" /><input type="hidden" name="event_id" value="'.(int)$nextService->event_id.'" /><input type="hidden" name="service_id" value="'.(int)$nextService->service_id.'" /><input type="hidden" name="date_id" value="'.(int)$nextService->date_id.'" /><input type="submit"  value="'.esc_html( __('Add to waiting list','church-admin' ) ).'" class="btn btn-danger button-primary" /></p></form>';
                    }
                    
                }
                else
                {
                    $out.='<p>'.esc_html( __('No service details found in calendar, an admin needs to edit the service details','church-admin' ) ).'</p>';
                }
            }
        }
        else
        {
            $out.='<p>'.esc_html( __('No/incomplete service details found','church-admin' ) ).'</p>';
        }
       $out.='<script>jQuery(document).ready(function( $)  { 
       
       $(".all-about-jesus").html(\'<div class="church-admin-form-group"><label><input type="checkbox" name="real-person" value="yes" /> \'+"'.esc_html( __('I am a real person, not a spammer','church-admin' ) ).'"+"</label></div>");
       
       $(".camelcase").caseEnforcer("capitalize");
        $("body .ca-close").click(function()  {$(".ca-modal").css("display","none");})
               
        });
              
                </script>';
   $out.='</div>';
    return $out;
}