<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_facilities( $current=NULL,$facilities_id=1)
{
	global $wpdb;
	echo'<h2>'.esc_html( __('Use this section to organise facilities like rooms, video projectors','church-admin' ) ).'</h2>';
	echo' <p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_facility&section=facilities','edit_facility').'">'.esc_html( __('Add Facility','church-admin' ) ).'</a></p>';
    church_admin_facilities_list( $current,$facilities_id);
}

function church_admin_facilities_list( $current=NULL,$facilities_id=1)
{ 
    global $wpdb;
    $licence = get_option('church_admin_app_new_licence');;

	$facilities=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities ORDER BY facilities_order');
    if(!empty( $facilities) )
	{
		
        $theader='<tr><th class="column-primary">'.esc_html( __('Facility','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Facility Shortcode','church-admin' ) ).'</th><th>'.esc_html( __('Hourly rate','church-admin' ) ).'</th><th>'.esc_html( __('Terms documentation','church-admin' ) ).'</th><th>'.esc_html( __('Admin contact email','church-admin' ) ).' </th></tr>';
		echo'<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tbody class="content">';
		foreach( $facilities AS $facility)
		{
			$edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_facility&amp;facilities_id='.(int)$facility->facilities_id,'edit-facility').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';

            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete_facility&facilities_id='.$facility->facilities_id,'delete-facility').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
            if(!empty( $licence) )
            {
                $premium=get_option('church_admin_payment_gateway');
                if(!empty( $premium['currency_symbol'] ) )  {$currSymbol=$premium['currency_symbol'];} else{$currSymbol='';}
                $hourlyrate=$currSymbol.$facility->hourly_rate;
                $hourlyrate=__('Coming soon','church-admin');
            }
            else
            {
                $hourlyrate='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Please buy the premium version to set an hourly rate','church-admin' ) ).'</a>';
                $hourlyrate=__('Coming soon','church-admin');
            }
            if(!empty( $facility->admin_email) )
            {
                $admin_email='<a href="'.esc_url('mailto:'.$facility->admin_email).'">'.esc_html( $facility->admin_email).'</a>';
            }
            else{$admin_email='&nbsp;';}
            if(!empty( $facility->terms_doc) )
            {
                $terms_doc='<a href="'.esc_url( $facility->terms_doc).'">'.esc_html( $facility->terms_doc).'</a>';
                
            }
            else{$terms_doc='&nbsp;';}
            $terms_doc=__('Coming soon','church-admin');
			echo'<tr  id="'.$facility->facilities_id.'">
                <td data-colname="'.esc_html( __('Facility','church-admin' ) ).'" class="column-primary">'.esc_html( $facility->facility_name).'<button type="button" class="toggle-row"><span class="screen-reader-text">'.esc_html( __('Show details','church-admin' ) ).'</span></button></td>
                <td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
                <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
                <td data-colname="'.esc_html( __('Shortcode','church-admin' ) ).'">[church_admin type="calendar" facilities_id="'.$facility->facilities_id.'"]</td>
                <td data-colname="'.esc_html( __('Hourly rate','church-admin' ) ).'">'.$hourlyrate.'</td>
                <td data-colname="'.esc_html( __('Terms documentation','church-admin' ) ).'">'.$terms_doc.'</td>
                <td data-colname="'.esc_html( __('Admin email','church-admin' ) ).'">'.$admin_email.'</td>
            </tr>';

		}
		echo'</tbody><tfoot>'.$theader.'</tfoot></table>';
		
	}
	


}

function church_admin_facility_hire( $facilities_id=NULL)
{
    global $wpdb;
    $facilitiesDetails=array();

    echo'<h2>'.esc_html( __('Facility Hire','church-admin' ) ).'</h2>';
    
    $facilities=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities ORDER BY facilities_order');
    if(!empty( $facilities) )
	{
		echo'<form action="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=facility-hires&section=facilities&amp;facilities_id='.(int)$facilities_id,'facility-hires').'" method="POST"><table><tbody><tr><th scope="row">'.esc_html( __('Choose facility to view','church-admin' ) ).'</th><td><select name="facilities_id">';
		foreach( $facilities AS $fac)
        {
            echo'<option value="'.esc_html( $fac->facilities_id).'">'.esc_html( $fac->facility_name).'</option>';
            $facilitiesDetails[$fac->facilities_id]=esc_html( $fac->facility_name);
        }
		echo'</select><td><input type="submit" class="button-primary" name="'.esc_html( __('Choose facility','church-admin' ) ).'" /></td></tr></tbody></table></form>';
	}
    if ( empty( $facilities_id) ) return;
    $date=date('Y-m-01');
    if(!empty( $_POST['date'] )&&church_admin_checkdate( $_REQUEST['date'] ) )$date=sanitize_text_field(stripslashes($_REQUEST['date']));
    $year=date('Y',strtotime( $date) );
    $month=date('m',strtotime( $date) );
    $readableDate= date('M Y',strtotime( $date) );
    $sql='SELECT * FROM '.$wpdb->prefix.'church_admin_facilities_bookings WHERE YEAR(start_date)="'.esc_sql( $year).'" AND MONTH(start_date)="'.esc_sql( $month).'" ORDER BY start_date ASC';
    $results=$wpdb->get_results( $sql);
    if ( empty( $results) ){
        return esc_html(sprintf(__('No %1$s facility booking for %2$s','church-admin' ) ,$facilitiesDetails[$facilities_id],$readableDate));
    }
    $theader='<tr><th>'.esc_html( __('Event','church-admin' ) ).'</th><th>'.esc_html( __('Approve','church-admin' ) ).'</th><th>'.esc_html( __('Decline','church-admin' ) ).'</th><th>'.esc_html( __('Date','church-admin')).'</th><th>'.esc_html( __('Hirer details','church-admin' ) ).'</th><th>'.esc_html( __('Cost','church-admin' ) ).'</th><th>'.esc_html( __('Paid','church-admin' ) ).'</tr>';
    echo'<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tbody>';
    foreach( $results AS $row)
    {
       if ( empty( $row->admin_approved) )
       {
           $approve='<a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=approve-facility-booking&ID='.(int)$row->ID.'&section=facilities','approve-facility-booking').'">'.esc_html( __('Approve','church-admin' ) ).'</a>';
            $decline='<a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=decline-facility-booking&ID='.(int)$row->ID.'&section=facilities','decline-facility-booking').'">'.esc_html( __('Decline','church-admin' ) ).'</a>';
       }
       else{$approve=$decline='&nbsp;';}
       $details=esc_html( $row->name).'<br>';
       if(!empty( $row->organisation) )$details.=esc_html( $row->organisation).'<br>';
       $details.=esc_html( $row->address).'<br>';
       $details.='<a href="'.esc_url('tel:'.$row->phone).'">'.esc_html( $row->phone).'</a><br>';
       echo'<tr><td class="column-primary" data-colname="event">'.esc_html( $row->event).'</td>
                <td data-colname="approve">'.$approve.'</td>
                <td data-colname="decline">'.$decline.'</td>
                <td data-colname="date">'.mysql2date(get_option('date_format').' '.get_option('time_format'),$row->start_date.' '.$row->start_time).'</td>
                <td date=colname="hirer">'.$details.'</td>
                <td data-colname="cost">'.esc_html( $row->cost).'</th>
                <td data-colname="paid">'.esc_html( __('Coming soon','church-admin')).'</th>
            </tr>';

    }
    echo'</tbody><tfoot></table>';
}

function church_admin_facility_bookings( $facilities_id=NULL)
{
    global $wpdb;
    $facility_name = null;
    echo'<h2>'.esc_html( __('Facility bookings','church-admin' ) ).'</h2>';
    if ( empty( $facilities_id) )$facilities_id=1;
	echo $facilities_id;
    $facilities=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities ORDER BY facilities_order');
    if(!empty( $facilities) )
	{
		echo'<form action="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=facility-bookings','facility-bookings').'" method="POST">';
        
	
        echo'<table><tbody><tr><th scope="row">'.esc_html( __('Choose facility calendar to view','church-admin' ) ).'</th><td><select name="facilities_id">';
		foreach( $facilities AS $fac)  {
            echo'<option value="'.esc_html( $fac->facilities_id).'" '.selected($fac->facilities_id,$facilities_id,FALSE).'>'.esc_html( $fac->facility_name).'</option>';
            if(!empty($facilities_id) && $facilities_id == $fac->facilities_id){
                $facility_name = $fac->facility_name;
            }
        }
		echo'</select><td><input type="submit" class="button-primary" name="'.esc_html( __('Choose facility','church-admin' ) ).'" /></td></tr></tbody></table></form>';
	}
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
    echo'<p>'.esc_html(sprintf(__('The shortcode to show this calendar on a website page or post is %1$s','church-admin' ) ,'[church_admin type="calendar" facilities_id="'.(int)$facilities_id.'"]')).'</p>';
    echo'<p><a class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=add-calendar&amp;date='.esc_attr(wp_date('Y-m-d')).'&facility_id='.(int)$facilities_id,'edit-calendar').'">'.esc_html(sprintf(__('New calendar event for %1$s','church-admin'),$facility_name)).'</a></p>';
	church_admin_new_calendar(wp_date('Y-m-d'),$facilities_id);
}



function church_admin_edit_facility( $facilities_id=NULL)
{
    church_admin_module_dropdown('facilities');
    global $wpdb;
     $licence = get_option('church_admin_app_new_licence');;
    if(isset( $_POST['edit_facility'] ) )
    {
	    
        $sqlsafe=array();
        foreach( $_POST AS $key=>$value)$sqlsafe[$key]=esc_sql(sanitize_text_field(stripslashes($value) ));
        if ( empty( $facilities_id) ){
            $facilities_id=$wpdb->get_var('SELECT facilities_id FROM '.$wpdb->prefix.'church_admin_facilities WHERE facility_name="'.$sqlsafe['facility_name'].'"');
        }
        if ( empty( $sqlsafe['hourly_rate'] ) )$sqlsafe['hourly_rate']=0.00;
	    if(!empty( $facilities_id) && church_admin_int_check($facilities_id) )
        {
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_facilities SET facility_name="'.$sqlsafe['facility_name'].'",hourly_rate="'.$sqlsafe['hourly_rate'].'",admin_email="'.$sqlsafe['admin_email'].'" WHERE facilities_id="'.(int)$facilities_id.'"');
        }
        else
        {
            $nextorder=1+$wpdb->get_var('SELECT facilities_order FROM '.$wpdb->prefix.'church_admin_facilities ORDER BY facilities_order LIMIT 1');
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_facilities(facilities_order,facility_name,hourly_rate,admin_email)VALUES("'.esc_sql( $nextorder).'","'.$sqlsafe['facility_name'].'","'.$sqlsafe['hourly_rate'].'","'.$sqlsafe['admin_email'].'")');
        }
        
        echo'<div class="notice notice-success inline"><p>'.esc_html( __('Facility Updated','church-admin' ) ).'</p></div>';
        require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
        church_admin_facilities();
    }
    else
    {
        if(!empty( $facilities_id) )
        {
            $facility=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities WHERE facilities_id="'.(int)$facilities_id.'"');
        }
        echo'<h2>';
        if( $facilities_id)  {
            echo' '.esc_html( __('Edit','church-admin' ) ).' ';
        }else{
            echo esc_html(__('Add','church-admin' ) ).' ';
        }
        echo esc_html(__('Facility','church-admin' ) ).'</h2>';
        if ( empty( $licence) )
        {
            echo'<p><a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&action=upgrade&section=app','upgrade').'">'.esc_html( __('Please buy the premium version to set an hourly rate','church-admin' ) ).'</a></p>';
        }
        echo'<form action="" method="POST"><table class="form-table">';
        echo'<tr><th scope="row">'.esc_html( __('Facility','church-admin' ) ).'</th><td><input type="text" name="facility_name" ';
        if(!empty( $facility->facility_name) )  {
            echo'value="'.esc_html( $facility->facility_name).'" ';
        }
        echo'/></td></tr>';
        if(!empty( $licence) )
        {
            echo'<tr><th scope="row">'.esc_html( __('Hourly rate','church-admin' ) ).'</th><td><input type="text" name="hourly_rate"';
            if(!empty( $facility->hourly_rate) ) echo' value="'.(float)$facility->hourly_rate.'" ';
            echo'</td></tr>';
        }
        echo'<tr><th scope="row">'.esc_html( __('Admin contact email','church-admin' ) ).'</th><td><input type="email" name="admin_email" ';
        if(!empty( $facility->admin_email) )  {
            echo'value="'.esc_html( $facility->admin_email).'" ';
            
        }
        else
        {
            $user=wp_get_current_user();
            echo ' value="'.esc_html( $user->user_email).'" ';
        }
        echo'</td></tr>';

       
        echo'<tr><td colspan=2><input type="hidden" name="edit_facility" value="yes" /><input  class="button-primary"  type="submit" value="'.esc_html( __('Save Facility','church-admin' ) ).' &raquo;" /></td></tr></table></form>';
        
    }
}
function church_admin_delete_facility( $facilities_id=NULL)
{
    global $wpdb;

    if( $facilities_id)
    {
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_facilities WHERE facilities_id="'.esc_sql( $facilities_id).'"');
        echo'<div class="notice notice-success inline"><p><strong>'.esc_html( __('Facility Deleted','church-admin' ) ).'</strong></p></div>';
    }
    require_once(plugin_dir_path(dirname(__FILE__) ).'includes/calendar.php');
    church_admin_facilities();
}


function church_admin_approve_facility_booking( $booking_id)
{
    global $wpdb;
    $out='<h2>'.esc_html( __('Facility Booking Approval','church-admin' ) ).'</h2>';
    if ( empty( $booking_id) )return $out.'<p>'.esc_html( __('Incorrect booking id specified','church-admin' ) ).'</p>';
    $bookingDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities_bookings WHERE ID="'.(int)$booking_id.'"');
    if ( empty( $bookingDetails) )return $out.'<p>'.esc_html( __('No booking found','church-admin' ) ).'</p>';
    $facilityDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities WHERE facilities_id="'.(int)$bookingDetails->facilities_id.'"');
    //update booking to approve
    $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_facilities_bookings SET admin_approved=1 WHERE ID="'.(int)$booking_id.'"');

    //generate invoice if premium
    $licence_level = church_admin_app_licence_check(); 
	if($licence_level!='premium')
    {
        church_admin_facility_booking_invoice( $booking_id);
    }
    else
    {
        $subject = esc_html(sprintf(__('Approved -  Booking for %1$s on %2$s between %3$s - %4$s','church-admin' ) , $facilityDetails->facility_name,$html['start_date'],$html['start_time'],$html['end_time'] ));
        $message='<h2>'.$subject.'</h2>';
        $message.='<table>';
        $message.='<tr><th scope="row">'.esc_html( __('Event','church-admin' ) ).'</th><td>'.esc_html( $bookingDetails->event).'</td></tr>';
        $message.='<tr><th scope="row">'.esc_html( __('Hirer name','church-admin' ) ).'</th><td>'.esc_html( $bookingDetails->name).'</td></tr>';
        if(!empty( $bookingDetails->organisation) )$message.='<tr><th scope="row">'.esc_html( __('Organisation','church-admin' ) ).'</th><td>'.esc_html( $bookingDetails->organisation).'</td></tr>';
        $message.='<tr><th scope="row">'.esc_html( __('Address','church-admin' ) ).'</th><td>'.esc_html( $bookingDetails->address).'</td></tr>';
        $message.='<tr><th scope="row">'.esc_html( __('Contact number','church-admin' ) ).'</th><td>'.esc_html( $bookingDetails->mobile).'</td></tr>';
        $message.='<tr><th scope="row">'.esc_html( __('Email','church-admin' ) ).'</th><td>'.esc_html( $bookingDetails->email_address).'</td></tr>';
        if(!empty( $facilityDetails->terms) ){
            $message.='<tr><th scope="row">'.esc_html( __('Terms and conditions','church-admin' ) ).'</th><td><a href="'.esc_url( $facilityDetails->terms).'">'.esc_html( __('Download','church-admin')).'</a></td></tr>';
        }
        $message.='<tr><th scope="row">'.esc_html( __('Booking Status','church-admin' ) ).'</th><td>'.esc_html( __('Approved','church-admin' ) ).'</td></tr>';
        $message.='</table>';
        //prepare email
        if(!empty( $facilityDetails->admin_email) )  {$admin_email=$facilityDetails->admin_email;}else{$admin_email=get_option('church_admin_default_from_email');}

        church_admin_email_send($form['email_address'],$subject,$message,null,null,null,null,null,FALSE);
        $out.='<p>'.esc_html( __('Email sent to booker','church-admin' ) ).'</p>';
        $out.=$message; 
    }
       
}

function church_admin_facility_booking_invoice( $booking_id)
{
    global $wpdb;
    if ( empty( $booking_id) )return FALSE;
    $bookingDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities_bookings WHERE ID="'.(int)$booking_id.'"');
    if ( empty( $bookingDetails) )return FALSE;
    $facilityDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_facilities WHERE facilities_id="'.(int)$bookingDetails->facilities_id.'"');
    $url=get_option('church_admin_facility_booking_page'.$bookingDetails->facilities_id);



}