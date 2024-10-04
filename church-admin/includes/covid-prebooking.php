<?php
if ( ! defined( 'ABSPATH' ) ) exit('You need Jesus!'); // Exit if accessed directly



function church_admin_delete_service_booking( $covid_id)
{
    global $wpdb;
    $booking=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE covid_id="'.(int) $covid_id.'" LIMIT 1');
    if(!empty( $booking) )
    {
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE covid_id="'.(int) $covid_id.'"');
        $out='<div class="notice notice-success inline"><h2>'.esc_html( sprintf(__('Booking for %1$s deleted','church-admin' ) , $booking->people_id) ).'</h2></div>';
        $out.=church_admin_covid_attendance_list( $booking->service_id,$booking->date_id);
    }
    else{$out='<div class="notice notice-success inline"><h2>'.esc_html( __('No booking found','church-admin' ) ).'</h2></div>';}
    return $out;
}
function church_admin_delete_bubble_booking( $bubble_id)
{
    global $wpdb;
    $booking=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE bubble_id="'.(int)$bubble_id.'" LIMIT 1');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE bubble_id="'.(int)$bubble_id.'"');
    $out='<div class="notice notice-success inline"><h2>'.esc_html( __('Household/bubble booking deleted','church-admin' ) ).'</h2></div>';
    $out.=church_admin_covid_attendance_list( $booking->service_id,$booking->date_id);
    return $out;
}

function church_admin_add_bubble_booking_to_service( $bubble_id)
{
    global $wpdb;
    $details=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE bubble_id="'.(int)$bubble_id.'"');
    if(!empty( $details) )
    {
        $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_covid_attendance SET waiting_list=0 WHERE bubble_id="'.(int)$bubble_id.'"');
       
        if(defined('CA_DEBUG') )church_admin_debug( $wpdb->last_query);
       

        $bookings=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance  WHERE bubble_id="'.(int)$bubble_id.'"');

        $people=array();
        if(!empty( $bookings) )
        {
            foreach( $bookings AS $booking)$people[]=esc_html( $booking->people_id);//Yes i know people_id is a name in this table!!!
            $email=$booking->email;
            $date_id=$booking->date_id;
            $service_id=$booking->service_id;
        }
        $service=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_calendar_date a, '.$wpdb->prefix.'church_admin_services b  WHERE b.service_id="'.(int)$service_id.'" AND a.date_id="'.(int)$date_id.'" AND a.event_id=b.event_id LIMIT 1');
        $bookingTitle=esc_html( sprintf(__('You are now off the waiting list and booked in for the service for %1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$service->start_date),$service->start_time));
        /************************************************
         *  Send email for booking
         ************************************************/
        $token=MD5("Service Booking - ".$bubble_id);
        $message='<p><strong>'.$bookingTitle.'</strong></p>';
        
        $message.='<p>'.esc_html( __('Booking names','church-admin' ) ).'</p>'.implode("<br>",$people);
        $message.='<p>'.esc_html( sprintf(__('Booking ID is %1$s','church-admin' ) ,$bubble_id )).'</p>';
       
        church_admin_email_send($email,esc_html( __('Your waiting list service booking is now a firm booking','church-admin' ) ),$message,null,null,null,null,null,TRUE);
        echo church_admin_covid_attendance_list((int)$details->service_id,(int)$details->date_id);
    } else{
        echo'<div class="notice notice-success inline"><h2>'.esc_html( __('No booking found','church-admin' ) ).'</h2></div>';
        echo church_admin_covid_attendance_list(NULL,NULL);
    }
}


function church_admin_covid_attendance_list( $service_id=NULL,$date_id=NULL)
{
    global $wpdb;
    if(!empty( $_POST['service_id'] ) )$service_id=(int)$_POST['service_id'];
    if(!empty( $_POST['date_id'] ) )$date_id=(int)$_POST['date_id'];
    
    $out='<h2>'.esc_html( __('Service Prebookings','church-admin' ) ).'</h2>';
    $allServices=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE active=1');
   
    if ( empty( $allServices) )
    {
        return '<div class="notice notice-error inline"><h2>'.esc_html( __('Please create a service first','church-admin' ) ).'</h2></div>';
    }elseif( $wpdb->num_rows==1)
    {
        
        $service_id=$allServices[0]->service_id;
    }
    else
    {
       $out.='<form action="'.get_permalink().'" method="POST">';
        $out.='<select name="service_id">';
            foreach( $allServices AS $aService)
            {
                $out.='<option value="'.intval( $aService->service_id).'">'.esc_html( $aService->service_name).'</option>';
            }
            $out.='</select><input type="submit" class="button-secondary" value="'.esc_html( __('Choose service','church-admin' ) ).'" /></p></form>'; 
    }
    if(!empty( $service_id) )
    {
        $serviceDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
        church_admin_debug(print_r( $serviceDetails,TRUE) );
        
    }
    if(!empty( $serviceDetails) )
    {
        $services=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$serviceDetails->event_id.'" AND start_date>= DATE_ADD(NOW(), INTERVAL  - 28 DAY) ORDER BY start_date ASC');
        if(!empty( $services) )
        {
             if(!empty( $date_id) )
            {
               $nextService=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE date_id="'.(int)$date_id.'"');
                
            }
            else
            {
               $nextService=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$serviceDetails->event_id.'" AND unix_timestamp(CONCAT_WS(" ", start_date,start_time) )>="'.time().'" ORDER BY start_date ASC LIMIT 1');

            }
            $out.='<p>'.esc_html( __('Now choose which service','church-admin' ) ).'</p>';
            $out.='<form action="'.get_permalink().'" method="post">';
            $out.='<input type="hidden" name="service_id" value="'.(int)$service_id.'" />';
            $out.='<select name="date_id">';
            $first=$option='';
            foreach( $services AS $service)
            {
                if(!empty( $date_id)&& $date_id==$service->date_id)
                {
                    $first='<option selected="selected" value="'.(int)$service->date_id.'">'.esc_html( sprintf(__('%1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$service->start_date),$service->start_time)).'</option>';
                }
                elseif ( empty( $_POST['date_id'] )&&!empty( $nextService->date_id)&&$nextService->date_id==$service->date_id)
                {
                    $first.='<option selected="selected" value="'.(int)$service->date_id.'">'.esc_html( sprintf(__('%1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$service->start_date),$service->start_time) ).'</option>';
                }
                else
                {
                    $option.='<option value="'.(int)$service->date_id.'">'.esc_html( sprintf(__('%1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$service->start_date),$service->start_time)).'</option>';
                }
            }
            if ( empty( $first) )$first='<option>'.esc_html( __('Choose Service','church-admin' ) ).'</option>';
            $out.=$first.$option;
            $out.='</select><input type="submit" class="button-primary" value="'.esc_html( __('Choose service date and time','church-admin' ) ).'" /></p></form>';
            
            $nextServiceDetails=esc_html( sprintf(__('%1$s on %2$s at %3$s','church-admin' ) ,$nextService->title,mysql2date(get_option('date_format'),$nextService->start_date),$nextService->start_time));
            $out.='<h2>'.esc_html( $nextServiceDetails).'</h2>';
            $out.='<p><a  rel="nofollow" class="button-secondary" href="'.site_url().'?ca_download=service_booking_pdf&amp;date_id='.(int)$nextService->date_id.'&service_id='.intval( $serviceDetails->service_id).'">PDF</a></p>';
            $out.='<p><a  rel="nofollow" class="button-secondary" href="'.site_url().'?ca_download=service_booking_alphabetical_pdf&amp;date_id='.(int)$nextService->date_id.'&service_id='.intval( $serviceDetails->service_id).'">'.esc_html( __('PDF sorted alphabetically by last name','church-admin' ) ).'</a></p>';
            $out.='<p><a  rel="nofollow" class="button-secondary" href="'.site_url().'?ca_download=service_booking_csv&amp;date_id='.(int)$nextService->date_id.'&service_id='.intval( $serviceDetails->service_id).'">CSV</a></p>';
            
            $out.='<p><a  rel="nofollow" class="button-secondary" href="'.site_url().'?ca_download=service_booking_bubble_pdf&amp;date_id='.(int)$nextService->date_id.'&service_id='.intval( $serviceDetails->service_id).'">'.esc_html( __('PDF in bubble booking format','church-admin' ) ).'</a></p>';
            /**************************************************************************************
            *
            *   Calculate bubble sizes
            *
            ***************************************************************************************/
            $bubbles=$wpdb->get_results('SELECT COUNT(bubble_id) AS counted FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE service_id="'.intval( $serviceDetails->service_id).'" AND waiting_list=0 AND date_id="'.(int)$nextService->date_id.'" GROUP BY bubble_id');
            $totalBubbles=0;
            if(!empty( $bubbles) )
            {
                $bubbleCounts=array();
                foreach( $bubbles AS $bubble)
                {
                    if ( empty( $bubbleCounts[$bubble->counted] ) )
                    {
                        $bubbleCounts[$bubble->counted]=1;
                        $totalBubbles++;
                    }
                    else 
                    {
                        $bubbleCounts[$bubble->counted]++;
                        $totalBubbles++;
                    }
                }
                ksort( $bubbleCounts);
                $out.='<h3>'.esc_html( __('Household/Bubble counts','church-admin' ) ).'</h3>';
                $out.='<p>'.esc_html( sprintf(__('%1$s total household/bubble bookings','church-admin' ) ,$totalBubbles)).'</p>';
                $totalIndividuals=0;
                foreach( $bubbleCounts AS $size=>$count)
                {
                    $totalIndividuals+=( $size*$count);
                    
                }
                $out.='<p>'.esc_html( sprintf(__('%1$s total people booked in','church-admin' ) ,$totalIndividuals)).'</p>';
                $out.='<table class="widefat"><thead><tr><th>'.esc_html( __('Household/Bubble size','church-admin' ) ).'</th><th>'.esc_html( __('How many bookings of that size','church-admin' ) ).'</th></tr></thead><tbody>';
                
                foreach( $bubbleCounts AS $size=>$count)
                {
                    $out.='<tr><td>'.intval( $size).'</td><td>'.intval( $count).'</td></tr>';
                }
                $out.='</tbody></table>';
            }
            $out.='<p><a class="button-primary" href="'.esc_url(wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=add_bubble_booking&service_id='.(int)$serviceDetails->service_id,'add_bubble_booking')).'">'.esc_html( sprintf(__('Add bubble to %1$s','church-admin' ) ,$nextServiceDetails) ).'</a></p>';
            $out.='<h3>'.esc_html( __('Service booking detail','church-admin' ) ).'</h3>';
            
            $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE service_id="'.intval( $serviceDetails->service_id).'" AND date_id="'.(int)$nextService->date_id.'" AND waiting_list=0 ORDER BY bubble_id ASC';
            
            $results=$wpdb->get_results( $sql);
            if(!empty( $results) )
            {
                
                $thead='<tr><th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('No.','church-admin' ) ).'</th><th>'.esc_html( __('Edit bubble booking','church-admin' ) ).'</th><th>'.esc_html( __('Delete Individual','church-admin' ) ).'</th><th>'.esc_html( __('Delete bubble booking','church-admin' ) ).'</th><th>'.esc_html( __('Bubble Booking ID','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th><th>'.esc_html( __('Contact Number','church-admin' ) ).'</th></tr>';
                $out.='<table class="widefat wp-list-table striped"><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
                $no=1;
                foreach( $results AS $row)
                {
                    $editBooking='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_bubble_booking&amp;id='.$row->bubble_id,'edit_bubble_booking').'">'.esc_html( __('Edit bubble','church-admin' ) ).'</a>';
                    $deleteInd='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_service_booking&amp;id='.$row->covid_id,'delete_service_booking').'">'.esc_html( __('Delete individual','church-admin' ) ).'</a>';
                    $deleteBub='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_bubble_booking&amp;id='.$row->bubble_id,'delete_bubble_booking').'">'.esc_html( __('Delete bubble','church-admin' ) ).'</a>';
                    
                    $out.='<tr>
                    <td class="column-primary" data-colname="'.esc_html( __('Name','church-admin' ) ).'">'.esc_html( $row->people_id).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                    <td data-colname="'.esc_html( __('No.','church-admin' ) ).'">'.$no.'</td>
                    <td data-colname="'.esc_html( __('Edit booking','church-admin' ) ).'">'.$editBooking.'</td>
                    <td data-colname="'.esc_html( __('Delete individual','church-admin' ) ).'">'.$deleteInd.'</td>
                    <td data-colname="'.esc_html( __('Delete booking','church-admin' ) ).'" >'.$deleteBub.'</td>
                    <td data-colname="'.esc_html( __('Bubble ID','church-admin' ) ).'">'.intval( $row->bubble_id).'</td>
                    <td data-colname="'.esc_html( __('Email','church-admin' ) ).'"><a href="'.esc_url('mailto:'.$row->email).'">'.esc_html( $row->email).'</a></td>
                    <td data-colname="'.esc_html( __('Phone','church-admin' ) ).'">'.esc_html( $row->phone).'</td></tr>';
                    $no++;
                }
                $out.='</tbody></table>';
            }
            else
            {
                $out.='<div class="notice notice-success inline"><h2>'.esc_html( __('No bookings for this service yet','church-admin') ) ;
                $out.='<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_bubble_booking','edit_bubble_booking').'">'.esc_html( __('Add booking','church-admin' ) ).'</a></p>';
                $out.='</h2></div>';
            }
            /*******************************
             * NOW do waiting list bookings!
             * ******************************/
            $out.='<h2>'.esc_html( __('Waiting List','church-admin' ) ).'</h2>';
            $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE service_id="'.intval( $serviceDetails->service_id).'" AND date_id="'.(int)$nextService->date_id.'" AND waiting_list=1 ORDER BY bubble_id ASC';
            
            $results=$wpdb->get_results( $sql);
            if(!empty( $results) )
            {
                
                $thead='<tr><th class="column-primary">'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('No.','church-admin' ) ).'</th><th>'.esc_html( __('Add bubble to service bookings','church-admin' ) ).'</th><th>'.esc_html( __('Bubble ID','church-admin' ) ).'</th><th>'.esc_html( __('Email','church-admin' ) ).'</th><th>'.esc_html( __('Contact Number','church-admin' ) ).'</th></tr>';
                $out.='<table class="widefat wp-list-table striped"><thead>'.$thead.'</thead><tfoot>'.$thead.'</tfoot><tbody>';
                $no=1;
                foreach( $results AS $row)
                {
                    $addToService='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=add_bubble_booking_to_service&amp;id='.$row->bubble_id,'add_bubble_booking').'">'.esc_html( __('Add bubble to Service','church-admin' ) ).'</a>';
                    //note that $row->people_id is actually the name of the person on the booking
                    
                    $out.='<tr>
                    <td class="column-primary" data-colname="'.esc_html( __('Name','church-admin' ) ).'">'.esc_html( $row->people_id).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                    <td data-colname="'.esc_html( __('No.','church-admin' ) ).'">'.$no.'</td>
                    <td data-colname="'.esc_html( __('Add bubble to service booking','church-admin' ) ).'">'.$addToService.'</td>
                    <td data-colname="'.esc_html( __('Bubble ID','church-admin' ) ).'">'.(int)$row->bubble_id.'</td>
                    <td data-colname="'.esc_html( __('No.','church-admin' ) ).'"><a href="'.esc_url('mailto:'.$row->email).'">'.esc_html( $row->email).'</a></td>
                    <td data-colname="'.esc_html( __('Phone','church-admin' ) ).'">'.esc_html( $row->phone).'</td></tr>';
                    $no++;
                }
                $out.='</tbody></table>';
            }
            else
            {
                $out.='<div class="notice notice-success inline"><h2>'.esc_html( __('Nobody is on the waiting list for this service yet','church-admin' ) );
             
                $out.='</h2></div>';
            }




        }else return '<div class="notice notice-error inline"><h2>'.esc_html( __('No service occasions found','church-admin' ) ).'</h2></div>';
    }
 return $out;

}

function church_admin_edit_bubble_booking( $id=NULL)
{
    global $wpdb;
    
    $out='<h2>'.esc_html( __('Service Booking Edit','church-admin' ) ).'</h2>';
    if(!empty( $id) )
    {
        $bookings=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE bubble_id="'.(int)$id.'"');
        $service=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE a.event_id=b.event_id  AND b.date_id="'.intval( $bookings[0]->date_id).'"');
    }else
    {
        $bubble_id=$wpdb->get_var('SELECT MAX(bubble_id) FROM '.$wpdb->prefix.'church_admin_covid_attendance');
        if ( empty( $bubble_id) )$bubble_id=0;
        $id=$bubble_id+1;
    
    }
    if(!empty( $_POST['save-bubble-booking'] ) &&!empty( $_POST['email'] ) &&!empty( $_POST['phone'] ) )
    {
      
        $date_id=(int)$_POST['date_id'];
        $service=$wpdb->get_row('SELECT a.*,b.* FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE b.date_id="'.(int)$date_id.'" AND a.event_id=b.event_id');
        $email=esc_sql(sanitize_text_field( stripslashes($_POST['email'] )) );
        $phone=esc_sql(sanitize_text_field( stripslashes($_POST['phone'] ) ));
        $values=array();
        if(!empty( $id) )  {$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_covid_attendance WHERE bubble_id="'.(int)$id.'"');}
        $names = !empty($_POST['name']) ? church_admin_sanitize($_POST['name']) : array();
        foreach( $names AS $key=>$value)
        {
            if(!empty( $value) )$values[]='("'.$email.'","'.$phone.'","'.$service->service_id.'","'.$date_id.'","'.esc_sql(sanitize_text_field( $value) ).'","'.$id.'")';
        }
        $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_covid_attendance (email,phone,service_id,date_id,people_id,bubble_id) VALUES '.implode(",",$values) );
        $out.='<div class="notice notice-success inline"><h2>'.esc_html( sprintf(__('Booking saved for %1$s on %2$s','church-admin' ) ,$service->service_name,mysql2date(get_option("date_format"),$service->start_date).' '.mysql2date(get_option('time_format'),$service->start_time) )).'</h2></div>';
        $out.=church_admin_covid_attendance_list( $service->service_id,$service->date_id);
    }
    else
    {
        if(!empty( $service) )
        {
            $out.='<p>'.esc_html( sprintf(__('Edit booking for  %1$s on %2$s','church-admin' ),$service->service_name,mysql2date(get_option("date_format"),$service->start_date).' '.mysql2date(get_option('time_format'),$service->start_time) ) ).'</p>';
        }
        
        $services=$wpdb->get_results('SELECT a.*, b.* FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE  a.event_id=b.event_id AND b.start_date >= NOW() ORDER BY b.start_date,b.start_time DESC');
        
        if(!empty( $services) )
        {
            $out.='<form action="" method="POST">';
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Select service','church-admin' ) ).'</label><select name="date_id">';
            $first=$option='';
            if(!empty( $service) )$first='<option value="'.$service->date_id.'" selected="selected">'.esc_html( sprintf(__('%1$s at %2$s','church-admin' ) ,mysql2date(get_option('date_format'),$service->start_date),$service->start_time)).'</option>';
            foreach( $services AS $serviceRow)
            {
                    $option.='<option value="'.intval( $serviceRow->date_id).'">'.esc_html( sprintf(__('%1$s at %2$s','church-admin' ) ,$serviceRow->service_name,mysql2date(get_option('date_format'),$serviceRow->start_date).' '.$serviceRow->start_time)).'</option>';
            }
            $out.=$first.$option.'</select></p>';
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Email address','church-admin' ) ).'</label><input type="email" class="church-admin-form-control" name="email" ';
            if(!empty( $bookings[0]->email) ) $out.=' value="'.esc_html( $bookings[0]->email).'" ';
            $out.='/></div>';
            $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Phone contact','church-admin' ) ).'</label><input type="text" class="church-admin-form-control" name="phone" ';
            if(!empty( $bookings[0]->phone) ) $out.=' value="'.esc_html( $bookings[0]->phone).'" ';
            $out.='/></div>';
            for ( $x=0; $x<=9; $x++)
            {
                $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Name','church-admin' ) ).'</label><input type="text" class="church-admin-form-control camelcase" name="name[]" ';
            if(!empty( $bookings[$x]->people_id) ) $out.=' value="'.esc_html( $bookings[$x]->people_id).'" ';
            $out.='/></div>';
            }
            $out.='<input type="hidden" name="save-bubble-booking" value="1" /><input type="submit" class="btn btn-danger button-primary" value="'.esc_html( __('Save','church-admin' ) ).'" /></p></form>'; 
            $out.='<script>jQuery(document).ready(function( $)  { 
                $(".camelcase").caseEnforcer("capitalize");
            
            });</script>';
        }
        else{
            $out.='<div class="notice notice-warning"><h2>'.esc_html( __('No future services set up yet in calendar','church-admin' ) ).'</h2></div>';
            require_once(plugin_dir_path(dirname(__FILE__) ).'includes/services.php');
            $out.=church_admin_service_list();
        }
    }
    return $out;
    
}

function church_admin_service_booking_pdf_form()
{
    global $wpdb;

   $services=$wpdb->get_results('SELECT a.*,a.service_id,b.date_id, b.* FROM '.$wpdb->prefix.'church_admin_services a, '.$wpdb->prefix.'church_admin_calendar_date b WHERE a.active=1 AND a.event_id=b.event_id AND b.start_date BETWEEN NOW() AND  DATE_ADD(NOW(), INTERVAL 28 DAY) ORDER BY b.start_date ASC,b.start_time ASC'); 
    $out='';
   
    $out.='<p>'.esc_html( __('Choose which service','church-admin' ) ).'</p>';
    $out.='<form action="'.get_permalink().'" method="post">';
    $out.='<div class="church-admin-form-group"><label>'.esc_html( __('Pick service','church-admin' ) ).'</label><select class="church-admin-form-control" name="service">';
    foreach( $services AS $service)
    {
        $detail=esc_html( sprintf(__('%1$s on %2$s at %3$s','church-admin' ) ,$service->service_name,mysql2date(get_option('date_format'),$service->start_date),mysql2date(get_option('time_format'),$service->start_time) ));
        $out.='<option value="'.(int)$service->service_id.'-'.$service->date_id.'">'.esc_html( $detail).'</option>';
        $serviceDetails[$service->date_id]=$detail;
    }
    $out.='</select></div><p><input type="submit" value="'.esc_html( __('Pick Service','church-admin' ) ).'" /></p></form>';

    if(!empty( $_POST['service'] ) )
    {
        $serviceWanted=explode("-",sanitize_text_field(stripslashes($_POST['service'] )));
        $out.='<h2>'.esc_html( $serviceDetails[$serviceWanted[1]] ).'</h2>';
        $out.='<p><a class="btn btn-success button-secondary" href="'.site_url().'?ca_download=service_booking_pdf&amp;date_id='.(int)$serviceWanted[1].'&service_id='.(int)$serviceWanted[0].'">PDF</a></p>';
        $out.='<p><a class="btn btn-success button-secondary" href="'.site_url().'?ca_download=service_booking_alphabetical_pdf&amp;date_id='.(int)$serviceWanted[1].'&service_id='.(int)$serviceWanted[0].'">'.esc_html( __('PDF sorted alphabetically by last name','church-admin' ) ).'</a></p>';
        $out.='<p><a class="btn btn-success button-secondary" href="'.site_url().'?ca_download=service_booking_csv&amp;date_id='.(int)$serviceWanted[1].'&service_id='.(int)$serviceWanted[0].'">CSV</a></p>';
        
        $out.='<p><a class="btn btn-success button-secondary" href="'.site_url().'?ca_download=service_booking_bubble_pdf&amp;date_id='.(int)$serviceWanted[1].'&service_id='.(int)$serviceWanted[0].'">'.esc_html( __('PDF in bubble booking format','church-admin' ) ).'</a></p>';
    }     
    return $out;
}