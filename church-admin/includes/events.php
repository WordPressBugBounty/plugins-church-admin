<?php

if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly

/**
 * Outputs Events
 *
 * Author:     Andy Moyle
 * Author URI: http://www.churchadminplugin.com
 *
 *
 */
function church_admin_events()
{
    global $wpdb;
    echo'<h2>'.esc_html( __('Event ticketing','church-admin' ) ).'</h2>';
     $licence = get_option('church_admin_app_new_licence');;
    $premium=get_option('church_admin_payment_gateway');
        
    if ( empty( $licence) )
    {
        echo'<p>'.esc_html( __('You can set up events with multiple free tickets','church-admin' ) ).'</p>';
        echo'<script async src="https://js.stripe.com/v3/buy-button.js"></script><h3>'.__('Upgrade with monthly premium subscription','church-admin').'</h3><p><stripe-buy-button  buy-button-id="buy_btn_1O72fYCiMTVZnQYvV1EirMpC" publishable-key="pk_live_oHDAZ7byvqkNgaJaWYlLVRKp00rzFE4mAQ"></p>';
    }
    else
    {
        echo'<p>'.esc_html( __('You can set up events with multiple free and/or paid for tickets','church-admin' ) ).'</p>';
        $buttonText=__('PayPal setup','church-admin');
        echo'<p><a class="button-secondary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=payment-gateway-setup','payment-gateway-setup').'">'.$buttonText.'</a></p>';
    }
   
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_event','edit_event').'">'.esc_html( __('Add an event','church-admin' ) ).'</a></p>';
    
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_events ORDER BY event_date DESC';
    $results=$wpdb->get_results( $sql);
    if(!empty( $results) )
    {
        $tableheader='<tr><th class="column-primary">'.esc_html( __('Event Name','church-admin' ) ).'</th>
        <th>'.esc_html( __('Edit','church-admin' ) ).'</th>
        <th>'.esc_html( __('Delete','church-admin' ) ).'</th>
        <th>'.esc_html( __('Add ticketholder','church-admin' ) ).'</th>
        <th>'.esc_html( __('Date','church-admin' ) ).'</th>
        
        <th>'.esc_html( __('Tickets','church-admin' ) ).'</th>';
        if(!empty( $licence) )$tableheader.='<th>'.esc_html( __('Gross revenue','church-admin' ) ).'</th>';
        $tableheader.='<th>'.esc_html( __('Shortcode','church-admin' ) ).'</th><th>'.esc_html( __('Bookings','church-admin' ) ).'</th></tr>';
       echo'<table class="widefat striped wp-list-table"><thead>'.$tableheader.'</thead><tbody>';

       foreach( $results AS $row)
       {
            $bookings=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_bookings WHERE event_id="'.(int)$row->event_id.'"');
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_event&amp;event_id='.(int)$row->event_id,'edit_event').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
         $view_event='<a title="'.esc_html( __('View event','church-admin' ) ).'" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=view-event&amp;event_id='.(int)$row->event_id,'view-event').'">'.esc_html( $row->title).'</a>';
        $checkin='';
         if(!empty( $bookings) )
        {
            $edit='<a title="'.esc_html( __('Edit event','church-admin' ) ).'" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_event&amp;event_id='.(int)$row->event_id,'view_bookings').'">'.esc_html( __("Edit",'church-admin' ) ).'</a>';
            $delete=__("Can't be deleted",'church-admin');
            $checkin = '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=event-checkin&amp;event_id='.(int)$row->event_id,'event-checkin').'">'.esc_html( __("Checkin Tickets",'church-admin' ) ).'</a></p>';
        }
        else
        {
             $edit='&nbsp;';
             $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_event&amp;event_id='.(int)$row->event_id,'delete_event').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
        }
      
         $addTicketHolder='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-booking&amp;event_id='.(int)$row->event_id,'edit-booking').'">'.esc_html( __('Add','church-admin' ) ).'</a>';
       
        $bookingsLinks='<a title="'.esc_html( __('View bookings','church-admin' ) ).'" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=view_bookings&amp;event_id='.(int)$row->event_id,'view_bookings').'">'.esc_html( __('View bookings','church-admin' ) ).'</a><br><a href="'.site_url().'/?ca_download=bookings_csv&amp;event_id='.(int)$row->event_id.'&_wpnonce='.wp_create_nonce('bookings_csv').'">'.esc_html( __('Bookings CSV','church-admin' ) ).'</a><br><a href="'.site_url().'/?ca_download=bookings_pdf&amp;event_id='.(int)$row->event_id.'&_wpnonce='.wp_create_nonce('bookings_pdf').'">'.esc_html( __('Bookings PDF','church-admin' ) ).'</a>';
        $tickets=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_tickets WHERE event_id="'.(int)$row->event_id.'"');
        $revenue=0.0;
        if(!empty( $licence) )
        {
            $currency_symbol=!empty( $premium['currency_symbol'] )?$premium['currency_symbol']:'$';
            $revenue=$wpdb->get_var('SELECT SUM(amount) FROM '.$wpdb->prefix.'church_admin_event_payments WHERE event_id="'.(int)$row->event_id.'"');
            if(!empty( $revenue) )  {
                $revenueOutput=esc_html( $currency_symbol.$revenue);
            }
            else{
                $revenueOutput='&nbsp;';
            }
        }
        $ticketsOutput=array();
        $ticketsOutput[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_ticket_type&amp;event_id='.(int)$row->event_id,'edit_ticket_type').'">'.esc_html( __('Add a ticket type','church-admin' ) ).'</a>'; 
        if(!empty( $tickets) )
        {
           foreach( $tickets AS $ticket)
           {
               $bookings=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_bookings WHERE ticket_type="'.(int)$ticket->ticket_id.'"'); 
                if ( empty( $bookings) )
                {
                    $ticketsOutput[]='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_ticket_type&amp;ticket_id='.(int)$ticket->ticket_id.'&amp;event_id='.(int)$row->event_id,'edit_ticket_type').'">'.esc_html( $ticket->name).'</a>';
                }
                else $ticketsOutput[]=esc_html( $ticket->name.' ('.(int)$bookings.')');
           }
        }
          
          echo'<tr>';
          echo'<td class="column-primary" data-colname="'.esc_html( __('Event name','church-admin' ) ).'">'.$view_event.'<button type="button" class="toggle-row">
          <span class="screen-reader-text">show details</span></button></td>';
          echo'<td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>';
          echo '<td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>';
          echo'<td data-colname="'.esc_html( __('Add ticketholder','church-admin' ) ).'">'.$addTicketHolder.'</td>';
          echo'<td data-colname="'.esc_html( __('Date','church-admin' ) ).'">'.mysql2date(get_option('date_format').' '.get_option('time_format'),$row->event_date).'</td>';
          
          echo'<td data-colname="'.esc_html( __('Tickets','church-admin' ) ).'">'.implode('<br>',$ticketsOutput).'</td>';
          if(!empty( $licence) )echo'<td data-colname="'.esc_html( __('Revenue','church-admin' ) ).'">'.$revenueOutput.'</td>';
          echo'<td data-colname="'.esc_html( __('Shortcode','church-admin' ) ).'">[church_admin type="event_booking" event_id="'.(int)$row->event_id.'"]</td><td>'.$bookingsLinks.$checkin.'</td></tr>';

       }
       echo'</tbody><tfoot>'.$tableheader.'</tfoot></table>';
    }
    else{echo'<p>'.esc_html( __('No events created yet','church-admin' ) ).'</p>';}
    

}

function church_admin_view_event( $event_id)
{
    global $wpdb;
     $licence = get_option('church_admin_app_new_licence');;
    $premium=get_option('church_admin_payment_gateway');
    $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$event_id.'"');
    if(!empty( $event) )
    {
        echo'<h2>'.esc_html( $event->title).'</h2>';
        echo '<p>'.mysql2date(get_option('date_format').' '.get_option('time_format'),$event->event_date).'</p>';
        echo'<p>'.esc_html( $event->location).'</p>';
        echo '<p>Shortcode: [church_admin type="event" event_id="'.(int)$event_id.'"]</p>';
        echo '<p>'.esc_html( sprintf(__('Event id for block is %1$s','church-admin' ) ,(int)$event_id)).'</p>';
        
        $custom = !empty($event->custom) ? maybe_unserialize($event->custom) : null;
        if(!empty($custom)){
            echo'<p><strong>'. esc_html(_n( 'Custom field', 'Custom fields', count($custom), 'church-admin' )).'</strong></p>';
            echo implode("<br>",$custom);

        }
        
        echo '<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=view_bookings&amp;event_id='.(int)$event_id,'view_bookings').'">'.esc_html( __('View Bookings','church-admin' ) ).'</a></p>';





        echo '<h2>'.esc_html( __('Tickets','church-admin' ) ).'</p>';
        echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_ticket_type&amp;event_id='.(int)$event_id,'edit_ticket_type').'">'.esc_html( __('Add a ticket type','church-admin' ) ).'</a></p>';
        $tickets=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_tickets WHERE event_id="'.(int)$event_id.'"');
        $revenue=0.0;
        if(!empty( $licence) )
        {
            $revenue=$wpdb->get_var('SELECT SUM(amount) FROM '.$wpdb->prefix.'church_admin_event_payments WHERE event_id="'.(int)$event_id.'"');
            if(!empty( $revenue) )  {$revenueOutput=esc_html( $premium['currency_symbol'].$revenue);}
            else{$revenueOutput='&nbsp;';}
        }
        if(!empty( $tickets) )
        {
            echo'<p>'.esc_html( __('Tickets cannot be edited or deleted once there are bookings for that ticket','church-admin' ) ).'</p>';
            $thead='<tr><th>'.esc_html( __("Edit",'church-admin' ) ).'</th><th>'.esc_html( __("Delete",'church-admin' ) ).'</th><th>'.esc_html( __("Ticket",'church-admin' ) ).'</th><th>'.esc_html( __("Description",'church-admin' ) ).'</th><th>'.esc_html( __("Quantity available",'church-admin' ) ).'</th><th>'.esc_html( __("Bookings",'church-admin' ) ).'</th></tr>';
            if(!empty( $licence) )
            {
                $thead='<tr><th>'.esc_html( __("Edit",'church-admin' ) ).'</th><th>'.esc_html( __("Delete",'church-admin' ) ).'</th><th>'.esc_html( __("Ticket",'church-admin' ) ).'</th><th>'.esc_html( __("Description",'church-admin' ) ).'</th><th>'.esc_html( __("Optional Custom fields",'church-admin' ) ).'</th><th>'.esc_html( __("Quantity available",'church-admin' ) ).'</th><th>'.esc_html( __("Ticket sales",'church-admin' ) ).'</th><th>'.esc_html( __('Price','church-admin' ) ).'</th></tr>';
            }
            echo'<table class="widefat striped"><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
           foreach( $tickets AS $ticket)
           {
               $bookings=$wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'church_admin_bookings WHERE ticket_type="'.(int)$ticket->ticket_id.'"'); 
                if ( empty( $bookings) )
                {
                    $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_ticket_type&amp;ticket_id='.(int)$ticket->ticket_id.'&amp;event_id='.(int)$event_id,'edit_ticket_type').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
                    $delete='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_ticket_type&amp;ticket_id='.(int)$ticket->ticket_id.'&amp;event_id='.(int)$event_id,'delete_ticket_type').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
                }else
                {
                    $edit='Not editable';
                    $delete='Not deletable';
                }
                $curr_symbol = !empty($premium['currency_symbol'])? $premium['currency_symbol'] :'$';
                $custom = !empty($ticket->custom) ? implode('<br>',maybe_unserialize($ticket->custom)) : '&nbsp;';
               echo'<tr><td>'.$edit.'</td><td>'.$delete.'</td><td>'.esc_html( $ticket->name).'</td><td>'.esc_html( $ticket->description).'</td><td></td>'.wp_kses_post($custom).'<td>'.(int)$ticket->quantity.'</td><td>'.(int)$bookings.'</td>';
               if(!empty( $licence) )echo'<td>'.esc_html($curr_symbol.$ticket->ticket_price).'</td>';
               echo'</tr>';
               
           }
             echo'</tbody></table>';
        }
          
    }
}



function church_admin_delete_event( $event_id)
{
    global $wpdb;
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_bookings WHERE event_id="'.(int)$event_id.'"' );
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$event_id.'"' );
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_tickets WHERE event_id="'.(int)$event_id.'"' );
            echo'<div class="notice notice-inline"><p><strong>'.esc_html( __('Event deleted','church-admin' ) ).'</strong></p></div>';
        church_admin_events();
}

/*
* Add/Edit Event
*
* @author  Andy Moyle
* @param    $event_id
* @return   html string
* @version  0.1
*
*
*/


function church_admin_edit_event( $event_id=NULL)
{
    global $wpdb;
    
    $premium=get_option('church_admin_payment_gateway');
 
    
    if(!empty( $event_id) )  {
        $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$event_id.'"');
    }else{
        $data=new stdClass();
    }   
    if(!empty( $_POST['edit_event'] ) )
    {
        /****************************
        *
        *   Process Event
        *
        *****************************/
        //clean form input
        $sqlsafe=array();
        foreach ( $_POST AS $key=>$value)  {$sqlsafe[$key]=esc_sql(church_admin_sanitize( $value) );}
        $sqlsafe['event_datetime']=$sqlsafe['event_date'].' '.$sqlsafe['event_time'].':00';
        if(!empty( $_POST['medical'] ) )  {$medical=1;}else{$medical=0;}
        if(!empty( $_POST['dietary'] ) )  {$dietary=1;}else{$dietary=0;}
        if(!empty( $_POST['photo_permission'] ) )  {$photo_permission=1;}else{$photo_permission=0;}
        if(!empty($_POST['custom'])){
            $custom = maybe_serialize(array_filter(church_admin_sanitize($_POST['custom'])));
        }
        else{
            $custom = NULL;
        }
        if ( empty( $event_id) )
        {
            $event_id=$wpdb->get_var('SELECT event_id FROM '.$wpdb->prefix.'church_admin_events WHERE title="'.$sqlsafe['event_title'].'" AND location="'.$sqlsafe['event_location'].'" AND event_date="'.$sqlsafe['event_datetime'].'" AND custom = "'.esc_sql($custom).'"');
        }
        if ( empty( $event_id) )
        {
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_events (title,location,event_date,medical,dietary,photo_permission,custom) VALUES ("'.$sqlsafe['event_title'].'","'.$sqlsafe['event_location'].'" ,"'.$sqlsafe['event_datetime'].'","'.$medical.'","'.$dietary.'","'.$photo_permission.'","'.esc_sql($custom).'")');
            $event_id=$wpdb->insert_id;
        }
        else
        {
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_events SET title="'.$sqlsafe['event_title'].'", location="'.$sqlsafe['event_location'].'" , event_date="'.$sqlsafe['event_datetime'].'",photo_permission="'.(int)$photo_permission.'",medical="'.(int)$medical.'", dietary="'.(int)$dietary.'" , custom = "'.esc_sql($custom).'" WHERE event_id="'.(int)$event_id.'"' );
        }
        echo'<div class="notice notice-success inline"><h2>'.esc_html( __('Event saved','church-admin' ) ).'</div>';
        church_admin_view_event( $event_id);
    }
    else
    {
        echo'<h2>'.esc_html( __('Add/Edit Event','church-admin' ) ).'</h2>';
        echo'<form action="" method="POST">';
        
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Event title','church-admin' ) ).'</label><input  type="text" id="event_title" name="event_title" ';
        if(!empty( $data->title) ) echo' value="'.esc_html( $data->title).'" ';
        echo' class="church-admin-form-control" required="required" /></div>'; 
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Event location','church-admin' ) ).'</label><input  type="text" id="event_location" name="event_location" ';
        if(!empty( $data->location) ) echo' value="'.esc_html( $data->location).'" ';
        echo' class="church-admin-form-control" required="required" /></div>';  
        if(!empty( $data->event_date) )  {$date=mysql2date('Y-m-d',$data->event_date);}else{$date=NULL;}
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Event Date','church-admin'));
		echo'</label>'. church_admin_date_picker( $date,'event_date',FALSE,date('Y'),date('Y')+10,'event_date','event_date');
		echo '</div>';
        if(!empty( $data->event_date) )  {$time=mysql2date('H:i',$data->event_date);}else{$time='';}        
        
        echo '<div class="church-admin-form-group"><label>'.esc_html( __('Event time','church-admin' ) ).'</label><input  type="time" id="event_time" name="event_time" ';
        if(!empty( $data->event_date) ) echo' value="'.$time.'" ';
        echo' class="church-admin-form-control" required="required" /></div>';
        echo '<div class="checkbox"><label><input type="checkbox"  name="photo_permission" value="1" ';
        if(!empty( $data->photo_permission) ) echo 'checked="checked" ';
        echo'/>'.esc_html( __("Photo Permission question",'church-admin' ) ).'</label></div>';
        echo '<div class="checkbox"><label><input type="checkbox"  name="dietary" value="1" ';
        if(!empty( $data->dietary) ) echo 'checked="checked" ';
        echo'/>'.esc_html( __("Dietary question",'church-admin' ) ).'</label></div>';
        echo '<div class="checkbox"><label><input type="checkbox"  name="medical" value="1" ';
        if(!empty( $data->medical) ) echo 'checked="checked" ';
        echo'/>'.esc_html( __("Medical needs question",'church-admin' ) ).'</label></div>';

    
        echo '<div class="church-admin-form-group"><label>'.esc_html( __("Optional custom fields",'church-admin' ) ).'</label>';
        $custom = !empty($ticket->custom)?maybe_unserialize($ticket->custom):array();
        for($x=0; $x<=3;$x++){
            echo'<input class="church-admin-form-control" type="custom" name="custom[]" placeholder="'.esc_attr(__('Optional custom field','church-admin')).'" ';
            if(!empty($custom[$x])) echo ' value="'.esc_attr($custom[$x]).'" ';
            echo'><br>';
        }
        echo'</div>';
         echo'<p><input type="hidden" name="edit_event" value="edit_event" /><input type="submit" value="'.esc_html( __('Save event','church-admin' ) ).'" class="button-primary" />';
    }
}

function church_admin_edit_ticket_type( $event_id,$ticket_id=NULL)
{
    global $wpdb;
    $licence =  church_admin_app_licence_check();;
    $premium=get_option('church_admin_payment_gateway');

    $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$event_id.'"');
    if(!empty( $ticket_id) )
    {
        $ticket=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_tickets WHERE ticket_id="'.(int)$ticket_id.'"');
        
    }
    echo'<h2>'.esc_html( sprintf(__('Edit ticket for %1$s','church-admin' ) ,$event->title)).'</h2>';
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=view-event&amp;event_id='.(int)$event_id,'view-event').'">'.esc_html( __('View event','church-admin' ) ).'</a></p>';
    if(!empty( $_POST['edit-ticket-type'] ) )
    {
        $sqlsafe=array();
        foreach( $_POST AS $key=>$value){
            $sqlsafe[$key]=esc_sql(church_admin_sanitize( $value) );
        }
        
        if ( empty( $sqlsafe['ticket_price'] ) )$sqlsafe['ticket_price']=0;
        if ( empty( $ticket_id) )$ticket_id=$wpdb->get_var('SELECT ticket_id FROM '.$wpdb->prefix.'church_admin_tickets WHERE name="'.$sqlsafe['ticket_name'].'" AND available_from="'.$sqlsafe['available_from'].'" AND available_until="'.$sqlsafe['available_until'].'" AND quantity="'.$sqlsafe['quantity'].'"  AND event_id="'.(int)$event_id.'"');
            if ( empty( $ticket_id) )
            {
                $sql='INSERT INTO '.$wpdb->prefix.'church_admin_tickets (name,description,available_from,available_until,quantity,event_id) VALUES("'.$sqlsafe['ticket_name'].'","'.$sqlsafe['description'].'","'.$sqlsafe['available_from'].'","'.$sqlsafe['available_until'].'","'.$sqlsafe['quantity'].'","'.(int)$event_id.'")';
                if(!empty( $premium) )
                {
                    if ( empty( $sqlsafe['ticket_price'] ) )$sqlsafe['ticket_price']=0;
                    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_tickets (name,description,available_from,available_until,quantity,event_id,ticket_price) VALUES("'.$sqlsafe['ticket_name'].'","'.$sqlsafe['description'].'","'.$sqlsafe['available_from'].'","'.$sqlsafe['available_until'].'","'.$sqlsafe['quantity'].'","'.(int)$event_id.'","'.$sqlsafe['ticket_price'].'" )';
                }
                
            }
            else
            {
                
                $sql='UPDATE '.$wpdb->prefix.'church_admin_tickets SET name="'.$sqlsafe['ticket_name'].'",description="'.$sqlsafe['description'].'",available_from="'.$sqlsafe['available_from'].'",available_until="'.$sqlsafe['available_until'].'",quantity="'.$sqlsafe['quantity'].'",ticket_price="'.$sqlsafe['ticket_price'].'",event_id="'.(int)$event_id.'" WHERE ticket_id="'.(int)$ticket_id.'"';
            }
            $wpdb->query( $sql);
        echo'<div class="notice notice-success"><h2>'.esc_html( __('Ticket saved','church-admin' ) ).'</h2></div>';
        church_admin_view_event( $event_id);
    }
    else
    {
        echo '<form action="" method="post">';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __("Ticket name",'church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="ticket_name" ';
        if(!empty( $ticket->name) )echo ' value="'.esc_html( $ticket->name).'" ';
        echo'/></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __("Ticket description",'church-admin' ) ).'</label><textarea class="church-admin-form-control" name="description"> ';
        if(!empty( $ticket->description) )echo esc_textarea( $ticket->description);
        echo'</textarea></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __("Quantity available",'church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="quantity" ';
        if(!empty( $ticket->quantity) )echo ' value="'.esc_html( $ticket->quantity).'" ';
        echo'/></div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __("Available from",'church-admin' ) ).'</label>';
        if(!empty( $ticket->available_from) )  {$from=$ticket->available_from;}else{$from=NULL;}
        echo church_admin_date_picker( $from,'available_from',FALSE,NULL,NULL,'church-admin-form-control','available_from',FALSE);
        echo'</div>';
        echo '<div class="church-admin-form-group"><label>'.esc_html( __("Available until",'church-admin' ) ).'</label>';
        if(!empty( $ticket->available_until) )  {$until=$ticket->available_until;}else{$until=NULL;}
        echo church_admin_date_picker( $until,'available_until',FALSE,NULL,NULL,'church-admin-form-control','available_until',FALSE);
        echo'</div>';
        if(!empty( $licence) )
        {
            echo '<div class="church-admin-form-group"><label>'.esc_html( __("Ticket price",'church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="ticket_price" ';
            if(!empty( $ticket->ticket_price) )echo ' value="'.esc_html( $ticket->ticket_price).'" ';
            echo'/></div>';
        }
        

        echo'<p><input type="hidden" name="edit-ticket-type" value="1" /><input type="submit" class="button-primary" value="'.esc_html( __('Save ticket','church-admin' ) ).'" /></p></form>';
    }
}
function church_admin_delete_ticket_type( $event_id,$ticket_id=NULL)
{
    global $wpdb;
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_tickets WHERE event_id="'.(int)$event_id.'" AND ticket_id="'.(int)$ticket_id.'"');
    echo'<div class="notice notice-success"><h2>'.esc_html( __('Ticket deleted','church-admin' ) ).'</h2></div>';
    church_admin_view_event( $event_id);
    
}
function church_admin_bookings_csv( $event_id)
{
    global $wpdb;
    $event_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$event_id.'"');
    //church_admin_debug(print_r( $event_details,TRUE) );
     $licence = get_option('church_admin_app_new_licence');;
    $csv='"'.esc_html( __('First Name','church-admin' ) ).'","'.esc_html( __('Last Name','church-admin' ) ).'","'.esc_html( __('Ticket Type','church-admin' ) ).'","'.esc_html( __('Booking Date','church-admin' ) ).'","'.esc_html( __('Contact email','church-admin' ) ).'",';
    if(!empty( $premium) )$csv.='"'.esc_html( __('Ticket price','church-admin' ) ).'"';
    if(!empty( $event_details->photo_permission) )$csv.='"'.esc_html( __('Photo Permission','church-admin' ) ).'",';
    if(!empty( $event_details->medical) )$csv.='"'.esc_html( __('Medical','church-admin' ) ).'",';
    if(!empty( $event_details->dietary) )$csv.='"'.esc_html( __('Dietary Needs','church-admin' ) ).'",';
    if(!empty($event_details->custom)){
        $custom=maybe_unserialize($event_details->custom);
        foreach($custom AS $key=>$field){
            $csv.='"'.esc_html( $field ).'"';
        }

    }
    $csv.="\r\n";
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_bookings WHERE event_id="'.(int)$event_id.'"');
    if(!empty( $results) )
    {
        foreach( $results AS $row)
        {
            
            $ticket=$wpdb->get_var('SELECT name FROM '.$wpdb->prefix.'church_admin_tickets WHERE ticket_id="'.(int)$row->ticket_type.'"');
            $csv.='"'.esc_html( $row->first_name).'","'.esc_html( $row->last_name).'","'.esc_html( $ticket).'","'.esc_html( $row->booking_date).'","'.esc_html( $row->email).'"';
            if(!empty( $licence) )$csv.='"'.$ticket->ticket_price.'"';
            if(!empty( $event_details->photo_permission) )$csv.=',"'.$row->photo_permission.'",';
            if(!empty( $event_details->medical) )$csv.='"'.str_replace('"',"'",$row->medical).'",';
            if(!empty( $event_details->dietary) )$csv.='"'.str_replace('"',"'",$row->dietary).'"';
            if(!empty($custom)){
                $count=count($custom);
                $custom_answers=maybe_unserialize($row->custom);
                for($x=0;$x<$count;$x++){
                    if(!empty($custom_answers[$x])){
                        $csv.='"'.esc_html($custom_answers[$x]).'",';
                    }else{
                        $csv.='"",';
                    }
                }
        
            }
            $csv.="\r\n";
            
        }
    }
    
    
    header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="bookings.csv"');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header("Content-Disposition: attachment; filename=\"bookings.csv\"");
	echo $csv;
    exit();
}
function church_admin_bookings_pdf( $event_id)
{
    $premium=get_option('church_admin_payment_gateway');
    
    
    if(empty($premium['currency_symbol'])){
        $premium['currency_symbol']='';
    }
    global $wpdb;
    $event_details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$event_id.'"');
     require_once(plugin_dir_path(dirname(__FILE__) ).'includes/fpdf.php');
    //initiate PDF
     $pdf = new fpdf();
        // Add a Unicode font (uses UTF-8)
    $pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
    $pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
    $pdf->SetAutoPageBreak(1,10);    

     $pdf->SetAutoPageBreak(1,15);
     $pdf->AddPage('P',get_option('church_admin_pdf_size') );
     $pageWidth=$pdf->GetPageWidth()-30;
    $colWidth=$pageWidth/5;
     $pdf->SetX(10);
    //title
    $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id ="'.(int)$event_id.'"');
     $title=esc_html(sprintf(__('Bookings for %1$s','church-admin' ) ,$event->title) );
    $pdf->SetFont('DejaVu','B',16);
    $pdf->Cell(0,8,$title,0,1,'C');
    //header
    $pdf->SetFont('DejaVu','B',8);
    $pdf->Cell(5,5,esc_html( __('No.','church-admin' ) ),1,0,'C');
    $pdf->Cell(25,5,esc_html( __('First Name','church-admin' ) ),1,0,'C');
    $pdf->Cell(25,5,esc_html( __('Last Name','church-admin' ) ),1,0,'C');
    $pdf->Cell(25,5,esc_html( __('Ticket Type','church-admin' ) ),1,0,'C');
    if(!empty( $premium) )$pdf->Cell(25,5,esc_html( __('Ticket price','church-admin' ) ),1,0,'C');
    $pdf->Cell(10,5,esc_html( __('Photo','church-admin' ) ),1,0,'C');
    $pdf->Cell(25,5,esc_html( __('Booking Date','church-admin' ) ),1,0,'C');
    $pdf->Cell(0,5,esc_html( __('Email','church-admin' ) ),1,1,'C');
    $pdf->SetFont('DejaVu','',8);
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_bookings WHERE event_id="'.(int)$event_id.'"');
    
    if(!empty( $results) )
    {
        $total=0;
        $no=1;
        foreach( $results AS $row)
        {
            
            $ticket=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_tickets WHERE ticket_id="'.(int)$row->ticket_type.'"');
            
            $pdf->Cell(5,5,$no,1,0,'C');
            $pdf->Cell(25,5,esc_html( $row->first_name),1,0,'C');
            $pdf->Cell(25,5,esc_html( $row->last_name),1,0,'C');
            $pdf->Cell(25,5,esc_html( $ticket->name),1,0,'C');
            if(!empty( $premium) )
            {
                $pdf->Cell(25,5,$premium['currency_symbol'].esc_html( $ticket->ticket_price),1,0,'C');
                $total+=$ticket->ticket_price;
            }
            if(!empty($row->photo_permission)){ 
                $pdf->Cell(10,5,esc_html( __('Yes','church-admin' ) ),1,0,'C');
            }
            else{
                $pdf->Cell(10,5,esc_html( __('No','church-admin' ) ),1,0,'C');
            }
            $pdf->Cell(25,5,mysql2date(get_option('date_format'),$row->booking_date),1,0,'C');
            $pdf->Cell(0,5,esc_html( $row->email),1,1,'C');
            
          
            $no++;
        }
        if(!empty( $premium) )$pdf->Cell(0,5,esc_html( __('Total','church-admin' ) ).': '.$premium['currency_symbol'].number_format( $total,2),1,0,'C');
            
    }
    $pdf->Output();
    exit();
}

function church_admin_view_bookings( $event_id)
{
    global $wpdb;
    $premium=get_option('church_admin_payment_gateway');
    $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id ="'.(int)$event_id.'"');

    $title=esc_html(sprintf(__('Bookings for %1$s','church-admin' ) ,$event->title) );
    echo'<h2>'.$title.'</h2>';
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-booking&amp;event_id='.(int)$event->event_id,'edit-booking').'">'.esc_html( __('Add a ticket (new booking)','church-admin' ) ).'</a></p>';
    echo'<p><a href="'.site_url().'/?ca_download=bookings_csv&amp;event_id='.(int)$event->event_id.'&_wpnonce='.wp_create_nonce('bookings_csv').'" class="button-secondary">'.esc_html( __('Bookings CSV','church-admin' ) ).'</a></p><p><a href="'.site_url().'/?ca_download=bookings_pdf&amp;event_id='.(int)$event->event_id.'&_wpnonce='.wp_create_nonce('bookings_pdf').'" class="button-secondary">'.esc_html( __('Bookings PDF','church-admin' ) ).'</a></p>';
    /**********************************************
    *
    *   Put ticket types in array $ticket_types
    *
    ***********************************************/
    $ticket_types_results=$wpdb->get_results('SELECT name,ticket_id FROM '.$wpdb->prefix.'church_admin_tickets WHERE event_id="'.(int)$event_id.'"');
    if(!empty( $ticket_types_results) )
    {
       $ticket_types=array();
        foreach( $ticket_types_results AS $row )$ticket_types[$row->ticket_id]=$row->name;
    }
    /*********************************************
    *
    *   Grab Bookings
    *
    ***********************************************/
    //$bookings=$wpdb->get_results('SELECT a.*, CONCAT_WS(" ",b.first_name,b.last_name) AS name FROM '.$wpdb->prefix.'church_admin_bookings a  LEFT JOIN '.$wpdb->prefix.'church_admin_people b ON b.people_id = a.person WHERE a.event_id="'.esc_sql( $event_id).'" ');
    $bookings=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_bookings WHERE event_id="'.(int)$event_id.'" GROUP BY booking_ref ORDER BY booking_date DESC');
    $number = $wpdb->num_rows;
    if(!empty( $bookings) )
    {
        
        foreach( $bookings AS $booking)
        {
            if ( empty( $booking->booking_ref) )
            {
                //fix for v2.4330
                $booking->booking_ref=md5(print_r( $booking,TRUE) );
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_bookings SET booking_ref="'.esc_sql( $booking->booking_ref).'" WHERE ticket_id="'.(int)$booking->ticket_id.'"');
            }
            echo '<h2>'.esc_html( __('Booking','church-admin' ) ).' #'.(int)$number.'</h2>'.church_admin_view_booking( $booking->booking_ref);
            $number--;
        }
    
    }
}

function church_admin_view_booking( $booking_ref)
{
    global $wpdb;
    $premium=get_option('church_admin_payment_gateway');
    if(empty($premium)){$premium=array();}
    if(empty($premium['currency_symbol'])){
        $premium['currency_symbol']='';
    }
    //$wpdb->show_errors;
    $out='';
    $bookings=$wpdb->get_results('SELECT a.*, CONCAT_WS(" ",b.first_name,b.last_name) AS name, c.ticket_price,c.name AS ticket_name FROM ('.$wpdb->prefix.'church_admin_bookings a, '.$wpdb->prefix.'church_admin_tickets c)  LEFT JOIN '.$wpdb->prefix.'church_admin_people b ON b.people_id = a.people_id WHERE a.booking_ref="'.esc_sql( $booking_ref).'" AND a.ticket_type=c.ticket_id');
    
    if(!empty( $_POST['token'] ) && wp_verify_nonce( $_POST['token'],'manual_payment') )
    {
        $out.='<p>Processing payment</p>';
        $check=$wpdb->get_var('SELECT payment_id FROM '.$wpdb->prefix.'church_admin_event_payments WHERE amount="'.esc_sql(sanitize_text_field(stripslashes( $_POST['manual_payment'] ))).'" AND txn_id="Manual" AND booking_ref="'.esc_sql( sanitize_text_field(stripslashes( $_POST['booking_ref'])) ).'" AND event_id="'.(int)sanitize_text_field(stripslashes( $_POST['event_id'])).'"');
        if ( empty( $check) )
        { 
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_event_payments (amount,booking_ref,event_id,txn_id,payment_date) VALUES ("'.esc_sql(sanitize_text_field(stripslashes(  $_POST['manual_payment'] ))).'","'.esc_sql(sanitize_text_field(stripslashes(  $_POST['booking_ref'] ))).'","'.(int)sanitize_text_field(stripslashes( $_POST['event_id'])).'","Manual","'.esc_sql(wp_date('Y-m-d H:i:s')).'")');
            echo'<div class="notice notice-success">'.esc_html( __('Manual payment added','church-admin' ) ).'</div>';
        }
    }
    if(!empty( $bookings) )
    {
        $total=0;
        $out.='<div class="booking">';
        $out.='<p><strong>'.esc_html( __('Booking date','church-admin' ) ).'</strong>: '.mysql2date(get_option('date_format'),$bookings[0]->booking_date).'</p>';
        $out.='<p><strong>'.esc_html( __('Booking contact email','church-admin' ) ).'</strong>: '.esc_html( $bookings[0]->email).'</p>';
        $out.='<p><strong>'.esc_html( __('Booking phone','church-admin' ) ).'</strong>: '.esc_html( $bookings[0]->phone).'</p>';

        if(!empty($bookings[0]->custom)){
            
            $custom_questions = maybe_unserialize($wpdb->get_var('SELECT custom FROM '.$wpdb->prefix.'church_admin_events WHERE event_id="'.(int)$bookings[0]->event_id.'"'));
            $custom_answers = maybe_unserialize($bookings[0]->custom);
            
            if(!empty($custom_questions)){
                for($x=0;$x<count($custom_questions);$x++){
                    $out.='<p><strong>'.esc_html($custom_questions[$x]).'</strong>: '.esc_html($custom_answers[$x]).'</p>';
                }
            }

        }
        $out.='<table class="widefat table table-bordered table-striped"><thead><tr>';
            if(is_admin() )
            {
                $out.='<th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th>';
            }
            $out.='<th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Ticket','church-admin' ) ).'</th>';
            if(!empty( $premium) )$out.='<th>'.esc_html( __('Ticket cost','church-admin' ) ).'</th>';
            $out.='</tr></thead><tbody>';
        foreach( $bookings AS $booking)
        {
            if ( empty( $booking->name) )$booking->name=$booking->people_id;
            $out.='<tr>';
            if(is_admin() )
            {
                $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-booking&amp;event_id='.(int)$booking->event_id.'&amp;ticket_id='.(int)$booking->ticket_id.'&booking_ref='.esc_html( $booking->booking_ref),'edit-booking').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
                $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_booking&amp;ticket_id='.(int)$booking->ticket_id,'delete_booking').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
                $out.='<td>'.$edit.'</td><td>'.$delete.'</th>';
            }
            $out.='<td>'.esc_html( $booking->first_name.' '.$booking->last_name).'</td><td>'.esc_html( $booking->ticket_name).'</td>';
            if(!empty( $premium) )
            {
                $out.='<td>'.esc_html( $premium['currency_symbol'].$booking->ticket_price).'</td>';
                $total+=$booking->ticket_price;
            }
            $out.='</tr>';
        }
        if(!empty( $premium) )
        {
            if(is_admin() )
            {
                $out.='<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>'.esc_html( __('Total:','church-admin' ) ).'</td><td><strong>'.$premium['currency_symbol'].number_format( $total,2).'</strong></td></tr>';
            }
            else
            {
                   $out.='<tr><td>&nbsp;</td><td>'.esc_html( __('Total:','church-admin' ) ).'</td><td>'.$premium['currency_symbol'].number_format( $total,2).'</td></tr>';
            }
            $totalPayments=0;
            $paymentsResults=$wpdb->get_results('SELECT amount,payment_date FROM '.$wpdb->prefix.'church_admin_event_payments WHERE event_id="'.(int)$booking->event_id.'" AND booking_ref="'.esc_sql($booking->booking_ref).'"');
            //church_admin_debug( $wpdb->last_query);
            //church_admin_debug(print_r( $paymentResults,TRUE) );
            if(!empty( $paymentsResults) )
            {
                
                
                if(is_admin() )
                {
                    foreach( $paymentsResults AS $payment)
                    {
                        $out.='<tr><td>&nbsp;</td><td>&nbsp;</td><td>'.mysql2date(get_option('date_format').' '.get_option('time_format'),$payment->payment_date).'</td><td>'.esc_html( __('Payments:','church-admin' ) ).'</td><td><strong>'.$premium['currency_symbol'].number_format( $payment->amount,2).'</strong></td></tr>';
                        $totalPayments+=$payment->amount;
                    }
                }
                else
                {
                   foreach( $paymentsResults AS $payment)
                   {
                       $out.='<tr><td>'.mysql2date(get_option('date_format').' '.get_option('time_format'),$payment->payment_date).'</td><td>'.esc_html( __('Payments:','church-admin' ) ).'</td><td>'.$premium['currency_symbol'].number_format( $payment->amount,2).'</td></tr>';
                        $totalPayments+=$payment->amount;
                   }
                }
            }
            $left=$total-$totalPayments;
            if(is_admin() )
             {
                  $nonce=wp_create_nonce('manual_payment');
                  $out.='<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>'.esc_html( __('Left to pay:','church-admin' ) ).'</td><td><strong>'.$premium['currency_symbol'].number_format( $left,2).'</strong></td></tr>';
                  if( $left>0)
                  {    
                      $out.='<tr><td colspan="4"><form action="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=view_bookings&event_id='.$booking->event_id,'view_bookings').'" method="POST"><input type="text" name="manual_payment" placeholder="'.esc_html( __('Manual payment amount','church-admin' ) ).'" /><input type="hidden" name="booking_ref" value="'.esc_html( $booking->booking_ref).'" /><input type="hidden" name="event_id" value="'.esc_html( $booking->event_id).'" /><input type=hidden name="token" value="'.$nonce.'" /><br><input class="button-secondary" type="submit" value="'.esc_html( __('Add payment','church-admin' ) ).'" /></form></td></tr><tr><td colspan=4>'.esc_html( __('Please press refresh if a payment is not yet showing','church-admin' ) ).'</td></tr>';
                  }
                if( $left<0)$out.='<tr><td colspan=3>&nbsp;</td><td colspan=1><h2 style="color:red">'.esc_html( __('There is an overpayment on this booking - please action a refund','church-admin' ) ).'</h2></td></tr>';
            }
            else
            {
                if( $left>0)
                {
                    $out.='<tr><td>&nbsp;</td><td>'.esc_html( __('Left to pay:','church-admin' ) ).'</td><td>'.$premium['currency_symbol'].number_format( $left,2).'</td></tr>';
                    $out.='<tr><td colspan="2">&nbsp;</td><td><form class="booking-form" action="'.CA_PAYPAL.'" method="POST">';
                    $out.='<input type="hidden" name="cmd" value="_xclick" />';
                    $out.='<input type="hidden" name="business" value="'.$premium['paypal_email'].'" />';
                    $out.='<input type="hidden" name="receiver_email" value="'.$premium['paypal_email'].'" />';
                    $out.='<input type="hidden" name="currency_code" value="'.$premium['paypal_currency'].'" />';
                    $out.='<input type="hidden" name="custom" value="'.esc_html( $booking->booking_ref).'" />';
                    $out.='<input type="hidden" name="item_name" value="'.esc_html( __('Booking payment','church-admin' ) ).'" />';
                    $out.='<input type="hidden" name="amount" value="'.$left.'" />';
                    $out.='<input type="hidden" name="notify_url" value="'.site_url().'/wp-admin/admin-ajax.php?action=church_admin_paypal_ipn" />';
                    $out.='<button class="btn btn-danger">'.esc_html( __('Pay balance with PayPal','church-admin' ) ).'</button>';
                    $out.='</td></tr>';
                }
            }
           $out.='<p><a href="'.esc_url(site_url().'/?ca_download=tickets&booking_ref='.$booking->booking_ref).'">'.__('Download tickets','church-admin').'</a></p>';     
        }
        if(is_admin() )$out.='<tr><td colspan=4>'.'<a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-booking&amp;event_id='.(int)$booking->event_id.'&amp;booking_ref='.esc_html( $booking->booking_ref),'edit-booking').'">'.esc_html( __('Add a ticket','church-admin' ) ).'</a></td></tr>';
        $out.='</tbody></table></div>';
    }else{$out.='<p>'.esc_html( __('No booking found','church-admin' ) ).'</p>';}
        
        
    return $out;
}


function church_admin_edit_booking( $ticket_id,$event_id,$booking_ref)
{
    global $wpdb;
    if(!empty( $ticket_id) )$booking_ref=$wpdb->get_var('SELECT booking_ref FROM '.$wpdb->prefix.'church_admin_bookings WHERE ticket_id="'.(int)$ticket_id.'"');
    if(!empty( $_POST['save_booking'] )&& wp_verify_nonce( $_POST['save_booking'],'save_booking') )
    {
        if(!empty( $ticket_id) )
        {
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_bookings SET first_name="'.esc_sql(sanitize_text_field( stripslashes($_POST['first_name']) ) ).'", last_name="'.esc_sql(sanitize_text_field( $_POST['last_name'] ) ).'", email="'.esc_sql(sanitize_text_field( stripslashes($_POST['email']) ) ).'", event_id="'.esc_sql(sanitize_text_field( stripslashes($_POST['event_id'] )) ).'",ticket_type="'.esc_sql(sanitize_text_field(stripslashes( $_POST['ticket'] )) ).'", booking_ref="'.esc_sql( $booking_ref).'" WHERE ticket_id="'.(int)$ticket_id.'"');
        }
        else
        {
            if ( empty( $booking_ref) )$booking_ref=esc_sql(md5(print_r( $_POST,TRUE) ));
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_bookings (first_name,last_name,email,ticket_type,event_id,booking_ref,booking_date) VALUES("'.esc_sql(sanitize_text_field( $_POST['first_name'] ) ).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['last_name'] )) ).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['email'] )) ).'","'.esc_sql(sanitize_text_field(stripslashes( $_POST['ticket']) ) ).'","'.esc_sql(sanitize_text_field( stripslashes($_POST['event_id'] )) ).'","'.esc_sql( $booking_ref).'","'.esc_sql(wp_date('Y-m-d')).'")');
        }
        echo'<div class="notice notice-success"><p><strong>'.esc_html( __('Ticket updated','church-admin' ) ).'</strong></p></div>';
        
        echo church_admin_view_bookings( $event_id);
    }
    else
    {
        $data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_bookings WHERE ticket_id="'.(int)$ticket_id.'"');
        echo'<h2>'.esc_html( __('Edit Ticket','church-admin' ) ).'</h2>';
        echo'<form action="" method="post">';
        echo   '<div class="church-admin-form-group"><label>'.esc_html( __('First Name','church-admin')).'</label><input class="church-admin-form-control" type="text" name="first_name" ';
        if(!empty( $data->first_name) ) echo ' value="'.esc_html( $data->first_name).'" ';
        echo'/></div>';
        echo   '<div class="church-admin-form-group"><label>'.esc_html( __('Last Name','church-admin')).'</label><input class="church-admin-form-control"  type="text" name="last_name" ';
        if(!empty( $data->last_name) ) echo ' value="'.esc_html( $data->last_name).'" ';
        echo'/></div>';
        echo   '<div class="church-admin-form-group"><label>'.esc_html( __('Email','church-admin')).'</label><input class="church-admin-form-control"  type="text" name="email" ';
        if(!empty( $data->email) ) echo ' value="'.esc_html( $data->email).'" ';
        echo'/></div>';
        echo   '<div class="church-admin-form-group"><label>'.esc_html( __('Ticket','church-admin')).'</label></div>';
        $ticketSQL='SELECT * FROM '.$wpdb->prefix.'church_admin_tickets WHERE event_id ="'.(int)$event_id.'"';
         $tickets=$wpdb->get_results( $ticketSQL);
    
        if(!empty( $tickets) )
        {
        
            $ticketChoice=$cost='';
            foreach( $tickets AS $ticket)
            {
            
                if(!empty( $premium) ) $cost=$premium['currency_symbol'].$ticket->ticket_price;
                echo '<div class="checkbox"><input type="radio" class="caTicket" name="ticket"  ';
                if(!empty( $data->ticket_type) && $data->ticket_type==$ticket->ticket_id) echo 'checked="checked" ';
                echo'value="'.$ticket->ticket_id.'" /><label>'.esc_html( $ticket->name).' ';
                echo'</label></div>'."\r\n";
            }
        }
        $nonce=wp_create_nonce('save_booking');
        echo'<p><input type="hidden" name="event_id" value="'.(int)$event_id.'" /><input type="hidden" name="save_booking" value="'.$nonce.'" /><input type="submit" class="button-primary"  value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>';
    }
}
function church_admin_delete_booking( $ticket_id)
{
    global $wpdb;
    $event_id=$wpdb->get_var('SELECT event_id FROM '.$wpdb->prefix.'church_admin_bookings WHERE ticket_id="'.(int)$ticket_id.'"');
    
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_bookings WHERE ticket_id="'.(int)$ticket_id.'"');
    echo'<div class="notice notice-success"><p><strong>'.esc_html( __('Booking deleted','church-admin' ) ).'</strong></p></div>';
    church_admin_view_bookings( $event_id);
}

function church_admin_ticket_checkin($event_id)
{
    global $wpdb;

    if(empty($event_id)||!church_admin_int_check($event_id)){
        return '<p>'.__('No event specified','church-admin').'</p>';
    }
    //get event
    $event=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_events WHERE event_id ="'.(int)$event_id.'"');
    if(empty($event)){
        return '<p>'.__('Event not found','church-admin').'</p>';
    }
    $out='<h2>'.esc_html(sprintf(__('Event Check in for %1$s','church-admin' ) ,$event->title) ).'</h2>';
    $out.='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin%2Findex.php&action=edit-booking&event_id='.(int)$event_id,'edit-booking').'">'.__('Add a ticket').'</a></p>';
    //get tickets
    $results = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_bookings WHERE event_id="'.(int)$event_id.'" ORDER BY last_name, first_name');
   
    if(empty($results)){

        $out.='<p>'.__('No tickets found','church-admin').'</p>';
        return $out;
    }
       
    foreach($results AS $row){
        if(!empty($row->check_in)){$checkin=' checked="checked" ';}else{$checkin='';}
        $out.='<div class="checkbox"> <input type="checkbox" class="checkin" data-id="'.(int)$row->ticket_id.'" '.$checkin.'>'.esc_html(church_admin_formatted_name($row)).' <em><span class="check-in-time" id="ticket'.(int)$row->ticket_id.'"></span></em></div>';
    }
    $out.='<script>
    jQuery(document).ready(function($){
    var nonce="'.wp_create_nonce('ticket-checkin').'";    
    $("input.checkin").on("change", function() {
        var id = $(this).data("id");
        if(this.checked){
              console.log("checked " + id);
              
              jQuery.post(ajaxurl, {action:"church_admin","ticketid":id,"method":"ticket-checkin","nonce":nonce},
						function(response)  {
                           
							console.log(response);
                            $("#ticket"+response).html("'.__('Checked in').'");

						}
					);
             
              
        }
        else{
            console.log("unchecked " + id);
          
            jQuery.post(ajaxurl, {action:"church_admin","ticketid":id,"method":"undo-ticket-checkin","nonce":nonce},
						function(response)  {
                           
							console.log(response);
                            $("#ticket"+response).html("'.__('Check in undone').'");

						}
					);
             
              
        }
        
    });
});
    </script>';
    return $out;
}