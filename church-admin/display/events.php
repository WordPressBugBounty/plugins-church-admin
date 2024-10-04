<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly



function church_admin_event_bookings_output( $event_id)
{
    $licence =get_option('church_admin_app_new_licence');
    if($licence!='standard' && $licence!='premium'){
        return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
        
    }
    wp_enqueue_script('church-admin-event-booking');
    global $wpdb,$church_admin_for_email;
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/events.php');
    $premium=get_option('church_admin_payment_gateway');
    if(empty($premium['currency_symbol'])){
        $premium=array('currency_symbol'=>'');
    }
    //get event details
    $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id ="'.(int)$event_id.'"');
   
    //abort with error message if no event
    if ( empty( $event) )
    {
        return __('Please add event_id to the shortcode or block','church-admin');
    
    }
    $sanitizedTitle=esc_html(sprintf(__('Booking for %1$s','church-admin' ) ,$event->title) );
    $out='<h2 class="ca-event-title">'.$event->title.'</h2>'."\r\n";
    //check to see if any tickets are available yet
    $ticketsLeftResult=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_tickets WHERE event_id="'.(int)$event_id.'" AND available_from<="'.esc_sql(wp_date("Y-m-d") ).'" AND available_until>="'.esc_sql(wp_date("Y-m-d") ).'"');
    //abort if no tickets
    if ( empty( $ticketsLeftResult) )
    {
        $out.='<p>'.esc_html( __('There are no tickets available for this event currently, please come back later','church-admin' ) ).'</p>'."\r\n";
        return $out;
    }
    //build ticket availability array
    $ticketAvailability=array();
    
    $totalAvailability=0;
    $ticketsLeft=array();
    foreach( $ticketsLeftResult AS $ticketLeftRow)
    {
        $ticketsSold=$wpdb->get_var('SELECT COUNT(ticket_id) FROM '.$wpdb->prefix.'church_admin_bookings WHERE ticket_type="'.(int)$ticketLeftRow->ticket_id.'"');
        
        
        $left=$ticketLeftRow->quantity-$ticketsSold;
        if ( empty( $_POST) ){
            $out.='<p>'.esc_html(sprintf(__('%1$s out of %2$s %3$s tickets left','church-admin' ) ,$left,$ticketLeftRow->quantity,$ticketLeftRow->name));
        }
        if( $left>0)
        {
            $ticketsLeft[$ticketLeftRow->ticket_id]=(array)$ticketLeftRow;
            $ticketsLeft[$ticketLeftRow->ticket_id]['DBleft']=$left;
            $ticketsLeft[$ticketLeftRow->ticket_id]['left']=$left;
            $totalAvailability+=$left;
        }
    }
    /***********************************
    *
    *   Paypal return
    *
    ************************************/
    if(!empty( $_REQUEST['booking_ref'] ) )
    {
        $booking_ref=sanitize_text_field(stripslashes($_REQUEST['booking_ref']));
        $out.=church_admin_view_booking( $booking_ref);
        //Send email
         $booking=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_bookings WHERE booking_ref="'.esc_sql( $booking_ref).'" LIMIT 1');
        if(!empty($booking))
        {
            $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id ="'.(int)$booking->event_id.'"');
            $sanitizedTitle=esc_html(sprintf(__('Booking for %1$s','church-admin' ) ,$event->title) );
            church_admin_email_send($booking->email,$sanitizedTitle,$message,null,null,null,null,null);
         
        }
    }
    /****************************************
    *
    *   Form Process
    *
    *****************************************/
    elseif(!empty( $_POST['save-booking'] ) && wp_verify_nonce( $_POST['save-booking'],'save-booking') )
    {
      
        foreach( $_POST['first_name'] AS $key=>$value)
        {
            if(church_admin_spam_check( sanitize_text_field( stripslashes( $value ) ),'text') ){
                exit('<p>'.esc_html( __('That appears to be spam',"church-admin")).'</p>'."\r\n");       
            }   
        }
         foreach( $_POST['last_name'] AS $key=>$value)
        {
            if(church_admin_spam_check( sanitize_text_field( stripslashes( $value) ),'text') ){
                exit('<p>'.esc_html( __('That appears to be spam',"church-admin")).'</p>'."\r\n");       
            }
        }
        if(church_admin_spam_check( sanitize_text_field( stripslashes( $_POST['booking_email'] ) ),'email') ){
            exit('<p>'.esc_html( __('That appears to be spam',"church-admin")).'</p>'."\r\n"); 
        }
        if(church_admin_spam_check( sanitize_text_field( stripslashes( $_POST['booking_phone'] ) ),'text') ){
            exit('<p>'.esc_html( __('That appears to be spam',"church-admin")).'</p>'."\r\n"); 
        }
        $booking_ref=church_admin_save_event_booking( (int)$event_id);
        $message=church_admin_view_booking( $booking_ref);
        $out.=$message;
        //Send email
        $booking_email=$wpdb->get_var('SELECT email FROM '.$wpdb->prefix.'church_admin_bookings WHERE booking_ref="'.esc_sql( $booking_ref).'" LIMIT 1');
        church_admin_email_send($booking_email,$sanitizedTitle,$message,null,null,null,null,null);
    }
    elseif( $totalAvailability>0)
    {
     
        if(!empty($church_admin_for_email)||(!empty($_REQUEST['action']) && $_REQUEST['action']=='ca_app')){
			//app or email so don't give booking form, provide link
			$ID=$wpdb->get_var ('SELECT ID FROM '.$wpdb->posts.' WHERE post_status="publish" AND post_content LIKE \'%[church_admin type="event" event_id="'.(int)$event_id.'"]%\'');
			//church_admin_debug($wpdb->last_query);
			if(!empty($ID))
			{
				$out.='<p><a class="button blue" href="'.esc_url(get_permalink($ID)).'">'.esc_html( __('Event booking page','church-admin' ) ).'</a></p>';
			}
			return $out;
		}
        /*********************************
        *
        *   Booking form
        *
        **********************************/
        
        $out.='<form id="ca-event-booking-form" action="'.esc_url(get_permalink()).'" method="POST">'."\r\n";

        /***********************
        * User logged in Check
        ***********************/
        $prePopulated=FALSE;   
        $x=1; 
        if(is_user_logged_in() ) 
        {
            $user=wp_get_current_user();
            $household_id=$wpdb->get_var('SELECT household_id FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
            if( $household_id)
            {
                $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE household_id="'.(int)$household_id.'" ORDER BY people_order ASC');
                if(!empty( $people) )
                {
                    $prePopulated=TRUE;
                    
                    foreach( $people AS $person)
                    {
                        $last_name=esc_html(implode(" ",array_filter(array( $person->prefix,$person->last_name) )) );
                        $out.='<div class="ca-event-booking-person" id="ticketNo'.(int)$x.'">'."\r\n";

                        $out.='<span class="ca-ticket-delete" data-ticketno="'.(int)$x.'">x</span>'."\r\n";
                        $out.='<input type="hidden" id="household_id'.(int)$x.'" name="household_id[]" value="'.(int)$person->household_id.'" >';
                        $out.='<input type="hidden" id="people_id'.(int)$x.'" name="people_id[]" value="'.(int)$person->people_id.'" >';
                        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('First name','church-admin' ) ).'*</label><input  type="text" id="first_name'.(int)$x.'" name="first_name[]" value="'.esc_html( $person->first_name).'"';
                        $out.=' class="church-admin-form-control" required="required" /></div>'."\r\n";
                        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Last name','church-admin' ) ).'*</label><input class="church-admin-form-control" type="text" id="last_name'.(int)$x.'" name="last_name[]" value="'.esc_attr($last_name).'" /></div>'."\r\n";

                        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Ticket required','church-admin' ) ).'*</label>';
                                          
                        $out.='<div id="ticketSelector'.(int)$x.'"><select class="church-admin-form-control ticket" id="ticket'.(int)$x.'" name="ticket[]"><option value="" data-price=0>'.esc_html( __('Choose ticket','church-admin' ) ).'</option>';
                        foreach( $ticketsLeftResult AS $key=>$ticket)
                        {
                            if( $ticketsLeft[$ticket->ticket_id]>0)
                            {
                                $out.='<option data-ticketname="'.esc_html( $ticket->name).'" ';
                                if(!empty( $ticket->ticket_price) || $ticket->ticket_price=='0.00'){
                                    $out.=' data-price="'.esc_attr($ticket->ticket_price).'"'; 
                                    $out.=' value="'.(int)$ticket->ticket_id.'">'.esc_html( $ticket->name);
                                }
                                if(!empty( $ticket->ticket_price)|| $ticket->ticket_price=='0.00' ){
                                    $out.=' ';
                                    if(!empty($premium['currency_symbol'])){
                                        $out.=esc_html($premium['currency_symbol']);
                                    }
                                    $out.= esc_html($ticket->ticket_price); 
                                }
                                $out.='</option>';
                            }
                        }
                        $out.="</select>\r\n</div>\r\n</div>\r\n";
                        if(!empty( $event->photo_permission) )
                        {
                            $out.='<div class="checkbox"><label><input type="checkbox" name="photo_permission[]" value="1" /> '.esc_html( __("Photo Permission",'church-admin' ) ).'</label></div>';
                        }
                        $out.="</div>";
                        $x++;
                    }
                               
                }
            }
        }   


        if ( empty( $prePopulated) )
        {    $out.='<div class="ca-event-booking-person" id="ticketNo'.(int)$x.'"><span class="ca-ticket-delete" data-ticketno="1">x</span>';
            $out.='<input type="hidden" name="ticketNo[]" value="'.(int)$x.'" />';
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('First name','church-admin' ) ).'*</label><input  type="text" id="first_name'.(int)$x.'" name="first_name[]" ';
            $out.=' class="church-admin-form-control" required="required" /></div>'."\r\n";
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Last name','church-admin' ) ).'*</label><input class="church-admin-form-control" type="text" id="last_name'.(int)$x.'" name="last_name[]" /></div>'."\r\n";
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Ticket required','church-admin' ) ).'*</label>';
            $out.='<div id="ticketSelector'.(int)$x.'"><select class="church-admin-form-control ticket" id="ticket'.(int)$x.'" name="ticket[]"><option value="" data-price=0>'.esc_html( __('Choose ticket','church-admin' ) ).'</option>';
            foreach( $ticketsLeftResult AS $key=>$ticket)
            {
                if( $ticketsLeft[$ticket->ticket_id]>0)
                {
                    $out.='<option data-ticketname="'.esc_html( $ticket->name).'" ';
                    if(!empty( $ticket->ticket_price) ){
                        $out.=' data-price="'.esc_attr($ticket->ticket_price).'" '; 
                    }
                    $out.='value="'.(int)$ticket->ticket_id.'" >'.esc_html( $ticket->name);
                    if(!empty( $ticket->ticket_price) )$out.=' '.esc_html($premium['currency_symbol'].$ticket->ticket_price); 
                    $out.='</option>';
                }
            }
            $out.="</select>\r\n</div>\r\n</div>\r\n";
            if(!empty( $event->photo_permission) )
            {
                $out.='<div class="checkbox"><label><input type="checkbox" name="photo_permission[]" value="1" /> '.esc_html( __("Photo Permission",'church-admin' ) ).'</label></div>';
            }
            $out.="</div>";
        }
        
        $out.='<input type="hidden" name="ticketNo[]" value='.(int)$x.'/>';   

        
        if( $ticketAvailability>1){
            $out.='<div class="addedTickets"></div><p> '.esc_html( __('Choose ticket types for current ticket(s) above to enable adding more people to the booking.','church-admin' ) ).'</p><p><button disabled="disabled" class="btn btn-danger ca-add-ticket" data-ticket=1>'.esc_html( __('Add ticket','church-admin' ) ).'</button></p>';
        }
        if( $premium){
            $out.='<p>'.esc_html( __('Total Cost','church-admin' ) ).': ';
            if(!empty($premium['currency_symbol'])){
                $out.=esc_html($premium['currency_symbol']);
            }
            $out.='<span class="total">0.00</span></p>';
        
        }
        
        if(!empty( $event->dietary) )
        {
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Any dietary needs','church-admin' ) ).'</label><input  type="text"  name="dietary[]" class="church-admin-form-control" /></div>'."\r\n";
        }
        if(!empty( $event->medical) )
        {
           $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Any medical needs','church-admin' ) ).'</label><input  type="text"  name="medical[]" class="church-admin-form-control" /></div>'."\r\n";
        }
        //custom fields
        if(!empty($event->custom))
        {
            $custom = maybe_unserialize($event->custom);
            if(!empty($custom) && is_array($custom)){
                foreach($custom AS $key=>$field){
                    $out.='<div class="church-admin-form-group"><label>'.esc_html($field ).'</label><input class="church-admin-form-control" type="text"  name="custom[]"  /></div>'."\r\n";

                }

            }


        }



        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Booking contact email','church-admin' ) ).'*</label><input  type="text"  name="booking_email" required=required class="church-admin-form-control" /></div>'."\r\n";
        $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Booking contact phone','church-admin' ) ).'*</label><input  type="text" required=required name="booking_phone" class="church-admin-form-control" /></div>'."\r\n";
        $out.='<input type="hidden" name="event_id" value="'.(int)$event_id.'" />';
        
        $out.=wp_nonce_field('save-booking','save-booking',false,FALSE);
        if(!empty( $premium) &&!empty($premium['paypal_email']) &&!empty($premium['paypal_currency']))
        {
            $out.='<input type="hidden" name="cmd" value="_xclick" />';
            $out.='<input type="hidden" name="business" value="'.esc_attr($premium['paypal_email']).'" />';
            $out.='<input type="hidden" name="receiver_email" value="'.esc_attr($premium['paypal_email']).'" />';
            $out.='<input type="hidden" name="currency_code" value="'.esc_attr($premium['paypal_currency']).'" />';
            $out.='<input type="hidden" name="custom" value="" class="booking_ref" />';
            $out.='<input type="hidden" name="item_name" value="'.esc_attr($sanitizedTitle).'" />';
            $out.='<input type="hidden" name="amount" class="booking-cost" />';
            $out.='<input type="hidden" name="return" class="return-url" value="" />';
            $out.='<input type="hidden" name="notify_url" value="'.esc_url(site_url().'/wp-admin/admin-ajax.php?action=church_admin_paypal_ipn').'" />';
           
            $out.='<p><input type="hidden" name="event_id" value="'.(int)$event_id.'" /><button class="ca-book btn btn-danger">'.esc_html( __('Book','church-admin' ) ).'</button></p>';
        }
        else
        {
            $out.=' <div class="church-admin-form-group"><button type="submit" class="btn btn-success">'.esc_html(__('Book','church-admin')).'</button></div>';
        }
        $out.='</form>';
        $out.='<script>
        var firstName="'.esc_html(__('First name','church-admin')).'";
        var lastName="'.esc_html(__('Last name','church-admin')).'";
        var ticketRequired="'.esc_html(__('Ticket required','church-admin')).'";
        var ticketChoose="'.esc_html(__('Choose ticket','church-admin')).'";
        var photo="'.esc_html(__("Photo Permission",'church-admin')).'";
        var dietary="'.esc_html(__("Any dietary needs",'church-admin')).'";
        var medical="'.esc_html(__('Any medical needs','church-admin')).'";
        
        var event='.json_encode( $event).';
        var totalTicketsLeft='.(int)$totalAvailability.';
        console.log("Total tickets left: "+totalTicketsLeft);
        var ticketsLeft='.json_encode( $ticketsLeft)."\r\n";
        if( $premium &&!empty($premium['currency_symbol'])){
            $out.='var currSymbol="'.$premium['currency_symbol'].'"'."\r\n";
        }
        else{
            $out.='var currSymbol =""'."\r\n";
        }
        $out.='jQuery(document).ready(function($)  {';
        if( $premium)
            {   
                $nonce=wp_create_nonce('event-booking');
                $out.='
            
            
            $(".ca-book").click(function(e)  {
                e.preventDefault();
                var cost=$(".booking-cost").val();
                console.log("booking button pressed "+cost);
                if(parseFloat(cost)==0){
                    
                    $("#ca-event-booking-form").submit();
                }
                else
                {
                    
                    $("#ca-event-booking-form").attr("action","'.CA_PAYPAL.'");
                    $(".ca-book").attr("disabled","disabled");
                    var data = $("#ca-event-booking-form").serializeArray();
                    console.log(data);
                    ';

                    $out.='data.push({name:"action",value:"church_admin"});
                    data.push({name:"method",value:"event-booking"});

                    data.push({name:"nonce",value:"'.esc_attr($nonce).'"});

                    $.ajax({
                                url: ajaxurl,
                                type: "POST",
                                data: data,
                                dataType:"json",
                                success: function(res) {
                                    console.log(res)
                                    if(res!="'.esc_html( __("No booking to save",'church-admin' ) ).'")
                                    {
                                        $(".booking_ref").val(res.booking_ref);
                                        $(".return-url").val("'.esc_url(get_permalink()).'?booking_ref="+res.booking_ref);
                                        $(".booking_cost").val(res.cost);
                                        $("#ca-event-booking-form").submit();
                                    }else{$(".ca-book").attr("disabled",FALSE);}
                                }
                            
                        });
                    
                }
            });
            ';
            }
        $out.='});</script>';
        
    }
    else{
        $out.='<p>'.esc_html( __('No current ticket availability','church-admin'));
    }
    return $out;
}

function church_admin_save_event_booking()
{
    global $wpdb;
    $out='';
    /*********************************
    *
    *   Process Booking
    *
    **********************************/
    //church_admin_debug("*************************\r\n".'church_admin_save_event_booking()'."\r\n Posted variables....\r\n".print_r( $_POST,TRUE) );
    $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$_POST['event_id'].'"');

    $form=$values=array();
    foreach( $_POST AS $key=>$value){
        $form[$key]=church_admin_sanitize( $value) ;
    }
    $booking_ref=md5(print_r( $form,TRUE) );
    $custom = !empty($form['custom']) ? maybe_serialize($form['custom']) : null;
    $check=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_bookings WHERE booking_ref="'.esc_sql( $booking_ref).'"');
    //church_admin_debug("Checked for already saved booking $check");
    if ( empty( $check) )
    {
        church_admin_debug('Starting ticket process');
        $values=array();
        foreach( $form['first_name'] AS $x=>$value)
        {
            if(!empty( $form['first_name'][$x] )&&!empty( $form['last_name'][$x] )&&!empty( $form['ticket'][$x] ) )
            {
                church_admin_debug('Valid ticket');
                if(!isset( $form['photo_permission'][$x] ) )$form['photo_permission'][$x]=0;
                if ( empty( $form['medical'][$x] ) )$form['medical'][$x]=''; 
                if ( empty( $form['dietary'][$x] ) )$form['dietary'][$x]='';
                $people_id = !empty($form['people_id'][$x]) ? (int)$form['people_id'][$x] : null;
                $household_id = !empty($form['household_id'][$x]) ? (int)$form['household_id'][$x] : null;
            
                
                $values[]='("'.esc_sql( $booking_ref).'","'.(int)$_POST['event_id'].'","'.(int)$people_id.'","'.(int)$household_id.'","'.esc_sql($form['first_name'][$x]).'","'.esc_sql($form['last_name'][$x]).'","'.(int)$form['ticket'][$x].'","'.esc_sql($form['booking_email']).'","'.date('Y-m-d').'","'.esc_sql($form['photo_permission'][$x]).'","'.esc_sql($form['dietary'][$x]).'","'.esc_sql($form['medical'][$x]).'","'.esc_sql($form['booking_phone']).'","'.esc_sql($custom).'")';
            }
        }
        //church_admin_debug(print_r( $values,TRUE) );
        if(!empty( $values) )
        {
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_bookings(booking_ref,event_id,household_id,people_id,first_name,last_name,ticket_type,email,booking_date,photo_permission,dietary,medical,phone,custom) VALUES '.implode(",",$values) );
            church_admin_debug( $wpdb->last_query);

            $event_link='<a href="'.esc_url(site_url()).'/?ca_download=tickets&booking_ref='.$booking_ref.'">'.esc_html( __('here','church-admin' ) ).'</a>';
            $message='<p>'.esc_html(sprintf(__('Thank you for booking into %1$s, download your details and tickets %2$s ','church-admin'  ),$event->title,$event_link)).'</p>';
            $out.=$message;

            
        }
        return $booking_ref;
    }else {return NULL;}
}