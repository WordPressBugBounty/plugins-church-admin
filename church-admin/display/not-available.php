<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_not_available()
{
    global $wpdb;
    $licence =get_option('church_admin_app_new_licence');
		if($licence!='standard' && $licence!='premium'){
			return '<div class="error"><p>'.esc_html( __("This feature is for premium and standard versions only",'church-admin' ) ).'<br><a class="button-primary" href="https://buy.stripe.com/fZedSB9ErbQRcjm14V">Upgrade</a></p></div>';
			
		}
  
    $out='';
    //Check for login
    if(!is_user_logged_in() )
    {
        return '<div class="login"><h2>'.esc_html( __('Please login','church-admin' ) ).'</h2>'.wp_login_form(array('echo'=>FALSE) ).'</div>'.'<p><a href="'.esc_url(wp_lostpassword_url(get_permalink() )).'" title="Lost Password">'.esc_html( __('Help! I don\'t know my password','church-admin' ) ).'</a></p></div></div>';
    }
    //check person is in directory
    $user=wp_get_current_user();
    if(church_admin_level_check('Rota',$user->user_id) && !empty($_REQUEST['people_id']) )
    {
        $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$_REQUEST['people_id'].'"');

    }
    else
    {
        $person=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$user->ID.'"');
    }
    
    if ( empty( $person) )
    {

        return '<p>'.esc_html( __('Your login is not connected to a directory entry','church-admin' ) ).'</p>';
    }
    if(!empty( $_POST['not-available'] ) )
    {
        //delete current entries
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_not_available WHERE people_id="'.(int)$person->people_id.'"');
        //process form
        $dateSQL=array();
        if(!empty( $_POST['dates'] ) )
        {
            foreach( $_POST['dates'] AS $key=>$date)
            {
                if(!church_admin_checkdate( $date) )continue;
                $dateSQL[]='("'.(int)$person->people_id.'","'.esc_sql( $date).'")';
            }
            if(!empty( $dateSQL) ) $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_not_available (people_id,unavailable)VALUES '.implode(",",$dateSQL) );
        }
        $out.='<div class="notice notice-success"><h2>'.esc_html( __('Unavailable dates saved','church-admin' ) ).'</h2></div>';
    }
    $services=$wpdb->get_results('SELECT service_day FROM '.$wpdb->prefix.'church_admin_services ORDER BY service_day');
    if ( empty( $services) )
    {
        $out= '<p>'.esc_html( __('No services have been setup','church-admin' ) ).'</p>';
        if(church_admin_level_check('Rota') )$out.='<p><a class="button-primary" href="'.wp_nonce_url("admin.php?page=church_admin/index.php&section=rota&amp;action=edit-service",'edit-service').'">'.esc_html( __('Please set up a service first','church-admin' ) ).'</a></p>';
		return $out;
    }
    $dayNames=array(0=>'Sun',1=>"Mon",2=>"Tues",3=>"Wed",4=>"Thu",5=>"Fri",6=>"Sat");
    $days=array();
    foreach( $services AS $service)
    {
        
        if(!empty($dayNames[$service->service_day]) && $service->service_day<=6)$days[]=$dayNames[$service->service_day];
    }
    
 
   
    
    
    if(church_admin_level_check('Rota',$user->ID))
    {
        $out.='<h2>'.esc_html( __('Non Availability','church-admin' ) ).'</h2>';
        $people=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_people ORDER by last_name,first_name');
        if(!empty($people))
        {
            $out.='<form action="" method="POST"><div class="church-admin-form-group"><label>'.esc_html( __('Admins can select a person','church-admin' ) ).'</label><select name="people_id" class="church-admin-form-control">';
            foreach($people AS $p)
            {
                $out.='<option value="'.(int)$p->people_id.'" '.selected($p->people_id,$person->people_id,FALSE).'>'.esc_html( church_admin_formatted_name($p)).'</option>';
            }
            $out.='</select></div><p><input type="submit" class="button-primary" value="'.esc_html( __( 'Choose person','church-admin' ) ).'" /></p></form>';
        }

    }
     //create form 
     if(church_admin_level_check('Rota',$user->ID)){
        $out.='<h3>'.esc_html( sprintf( __('Set non availability for %1$s','church-admin' ),church_admin_formatted_name( $person ) ) ).'</h3>';
    }
    else{
        $out.='<h3>'.esc_html( __('Please choose dates you are NOT available to serve on service schedules','church-admin' ) ).'</h3>';
    }
    
    $out.='<form action="" method="POST">';
    if(!empty($_POST['people_id'])){
        $out.='<input type="hidden" name="people_id" value="'.(int)$_POST['people_id'].'" />';
    }
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
            $out.='<div class="church-admin-form-group"><input type="checkbox" name="dates[]" ';
            if(in_array( $begin->format("Y-m-d"),$userNoDates) )$out.=' checked="checked" ';
            $out.=' value="'.$begin->format("Y-m-d").'" /> <label>'.esc_html($begin->format("D").' '.mysql2date(get_option('date_format'),$begin->format("Y-m-d") ) ).'</label></div>';
        }

        $begin->modify('+1 day');
    }
    $out.='<p><input type="hidden" name="not-available" value="yes" /><input type="submit" class="button" value="'.esc_html( __('Save','church-admin') ).'" /></p></form>';
    return $out;
}