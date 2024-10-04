<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 *
 * displays list of services
 *
 * @author  Andy Moyle
 * @param    NULL
 * @return   html
 * @version  0.945
 *
 *	2016-05-12 Added sites
 *
 */
function church_admin_service_list( $message=NULL)
{
    $expected_frequency = array('ah'=>__('Ad hoc','church-admin'),
                        '1'=>__('Daily','church-admin'),
                        '14'=>__('Fortnightly','church-admin'),
                        'm'=>__('Monthly','church-admin'),
                        'a'=>__('Annually','church-admin'),
                        '70'=>__('Weekly on Sunday','church-admin'),
                        '71'=>__('Weekly on Monday','church-admin'),
                        '72'=>__('Weekly on Tuesday','church-admin'),
                        '73'=>__('Weekly on Wednesday','church-admin'),
                        '74'=>__('Weekly on Thursday','church-admin'),
                        '75'=>__('Weekly on Friday','church-admin'),
                        '76'=>__('Weekly on Saturday','church-admin'),
                        'n10'=>__('First Sunday','church-admin'),
                        'n11'=>__('First Monday','church-admin'),
                        'n12'=>__('First Tuesday','church-admin'),
                        'n13'=>__('First Wednesday','church-admin'),
                        'n14'=>__('First Thursday','church-admin'),
                        'n15'=>__('First Friday','church-admin'),
                        'n16'=>__('First Saturday','church-admin'),
                        'n20'=>__('Second Sunday','church-admin'),
                        'n21'=>__('Second Monday','church-admin'),
                        'n22'=>__('Second Tuesday','church-admin'),
                        'n23'=>__('Second Wednesday','church-admin'),
                        'n24'=>__('Second Thursday','church-admin'),
                        'n25'=>__('Second Friday','church-admin'),
                        'n26'=>__('Second Saturday','church-admin'),
                        'n30'=>__('Third Sunday','church-admin'),
                        'n31'=>__('Third Monday','church-admin'),
                        'n32'=>__('Third Tuesday','church-admin'),
                        'n33'=>__('Third Wednesday','church-admin'),
                        'n34'=>__('Third Thursday','church-admin'),
                        'n35'=>__('Third Friday','church-admin'),
                        'n36'=>__('Third Saturday','church-admin'),
                        'n40'=>__('Fourth Sunday','church-admin'),
                        'n41'=>__('Fourth Monday','church-admin'),
                        'n42'=>__('Fourth Tuesday','church-admin'),
                        'n43'=>__('Fourth Wednesday','church-admin'),
                        'n44'=>__('Fourth Thursday','church-admin'),
                        'n45'=>__('Fourth Friday','church-admin'),
                        'n46'=>__('Fourth Saturday','church-admin'),


                    );
    global $wpdb,$wp_locale;
    $out='';
    if(!empty( $message) )
    {
        $out.='<div class="notice notice-success inline"><h2>'.esc_html( $message).'</h2></div>';
    }
	$out.='<p><a  class="button-primary" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-service&section=services','edit-service').'">'.esc_html( __('Add a service','church-admin' ) ).'</a></p>';
   

    $sql='SELECT a.*,b.venue AS site FROM '.$wpdb->prefix.'church_admin_services a ,'.$wpdb->prefix.'church_admin_sites b WHERE a.site_id=b.site_id';
    $results=$wpdb->get_results( $sql);
    if( $results)
    {
        $theader='<tr><th class="column-primary">'.esc_html( __('Service Name','church-admin' ) ).'</th><th>'.esc_html( __('Edit','church-admin' ) ).'</th><th>'.esc_html( __('Delete','church-admin' ) ).'</th><th>'.esc_html( __('Active','church-admin' ) ).'</th><th>'.esc_html( __('Service Frequency','church-admin' ) ).'</th><th>'.esc_html( __('Time','church-admin' ) ).'</th><th>'.esc_html( __('Site','church-admin' ) ).'</th><th>'.esc_html( __('Max attendance','church-admin' ) ).'</th><th>'.esc_html( __('Schedule Shortcode','church-admin' ) ).'</th></tr>';
        $out.='<table class="widefat striped wp-list-table"><thead>'.$theader.'</thead><tbody>';
        foreach( $results AS $row)
        {
										
            $edit='<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit-service&amp;id='.(int) $row->service_id,'edit-service').'">'.esc_html( __('Edit','church-admin' ) ).'</a>';
            $delete='<a onclick="return confirm(\''.esc_html( __('Are you sure?','church-admin' ) ).'\');" href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=delete-service&amp;id='.(int) $row->service_id,'delete-service').'">'.esc_html( __('Delete','church-admin' ) ).'</a>';
			$site= '<a href="'.wp_nonce_url('admin.php?page=church_admin/index.php&amp;action=edit_site&amp;id='.(int) $row->site_id,'edit_site').'">'.esc_html( $row->site).'</a>';          
            if( $row->active==1)  {$active=esc_html(__('Active','church-admin'));}else{$active=esc_html(__('Inactive','church-admin'));}
            $serviceDay = esc_html($expected_frequency[$row->service_frequency]);
            $serviceTime=esc_html( $row->service_time);
            if(!empty( $row->end_time) ) $serviceTime.='-'.$row->end_time;
            $bookingShortcodes='<strong>'.esc_html( __('By individuals','church-admin' ) ).'</strong><br>'.intval( $row->max_attendance).'<br>[church_admin type="covid-prebooking" loggedin=FALSE max_fields=10 days=7 admin_email="'.get_option('church_admin_default_from_email').'" service_id="'.intval( $row->service_id).'"]<br>';
            if(!empty( $row->bubbles) )
            {
                $bookingShortcodes.='<strong>'.esc_html( __('By households/support bubbles','church-admin' ) ).'</strong><br>';
                $bookingShortcodes.= esc_html(sprintf(__('%1$s bubbles of max bubble size %2$s','church-admin' ) ,intval( $row->bubbles),intval( $row->bubble_size)) );
                $bookingShortcodes.='<br>[church_admin type="covid-prebooking" loggedin=FALSE  days=7 mode="bubbles" admin_email="'.get_option('church_admin_default_from_email').'" service_id="'.(int)$row->service_id.'"]';
            }
            $shortcode='[church_admin type="rota" service_id="'.intval( $row->service_id).'" weeks=5]';


            $out.='<tr>
                <td class="column-primary" data-colname="'.esc_html( __('Service name','church-admin' ) ).'">'.esc_html( $row->service_name).'<button type="button" class="toggle-row"><span class="screen-reader-text">show details</span></button></td>
                <td data-colname="'.esc_html( __('Edit','church-admin' ) ).'">'.$edit.'</td>
                <td data-colname="'.esc_html( __('Delete','church-admin' ) ).'">'.$delete.'</td>
                <td data-colname="'.esc_html( __('Active','church-admin' ) ).'">'.$active.'</td>
                <td data-colname="'.esc_html( __('Service Frequency','church-admin' ) ).'">'.$serviceDay.'</td>
                <td data-colname="'.esc_html( __('Time','church-admin' ) ).'">'.$serviceTime.'</td>
                <td data-colname="'.esc_html( __('Site','church-admin' ) ).'">'.$site.'</td>
                <td data-colname="'.esc_html( __('Booking shortcodes','church-admin' ) ).'">'.$bookingShortcodes.'</td>
                <td data-colname="'.esc_html( __('Service shortcode','church-admin' ) ).'">'.$shortcode.'</td>
            </tr>';
  
        }
        $out.='</tbody><tfoot>'.$theader.'</tfoot></table>';
    }
    return $out;
}


/**
 *
 * delete a service
 *
 * @author  Andy Moyle
 * @param    $service_id
 * @return   html
 * @version  0.1
 *
 *
 */
function church_admin_delete_service( $service_id)
{
	global $wpdb;
    echo'<h2>'.esc_html( __('Delete service','church-admin' ) ).'</h2>';
	if(!empty( $_POST['confirm_delete'] ) )
	{
		$event_id=$wpdb->get_var('SELECT event_id FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$service_id.'"');
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.esc_sql((int)$service_id).'"');
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_new_rota WHERE service_id="'.esc_sql((int)$service_id).'"');
        if(!empty( $event_id) )$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.esc_sql((int)$event_id).'" AND start_date>="'.date('Y-m-d').'"');
		echo'<div class="notice notice-success inline"><p>'.esc_html( __('Service deleted','church-admin' ) ).'</p></div>';
        echo   church_admin_service_list(NULL);

	}
	else
	{
		echo'<form action="" method="POST"><p><label>'.esc_html( __('Are you sure?','church-admin' ) ).'</label><input type="hidden" name="confirm_delete" value="yes" /><input class="button-primary" type="submit" value="'.esc_html( __('Yes','church-admin' ) ).'" /></p></form>';
	}

}


/**
 *
 * edit a service
 *
 * @author  Andy Moyle
 * @param    $service_id
 * @return   html
 * @version  0.1
 *
 *
 */
function church_admin_edit_service( $id)
{
    global $wpdb,$wp_locale;
    $wpdb->show_errors;
    $expected_frequency = array('ah'=>__('Ad hoc','church-admin'),
                        '1'=>__('Daily','church-admin'),
                        '14'=>__('Fortnightly','church-admin'),
                        'm'=>__('Monthly','church-admin'),
                        'a'=>__('Annually','church-admin'),
                        '70'=>__('Weekly on Sunday','church-admin'),
                        '71'=>__('Weekly on Monday','church-admin'),
                        '72'=>__('Weekly on Tuesday','church-admin'),
                        '73'=>__('Weekly on Wednesday','church-admin'),
                        '74'=>__('Weekly on Thursday','church-admin'),
                        '75'=>__('Weekly on Friday','church-admin'),
                        '76'=>__('Weekly on Saturday','church-admin'),
                        'n10'=>__('First Sunday','church-admin'),
                        'n11'=>__('First Monday','church-admin'),
                        'n12'=>__('First Tuesday','church-admin'),
                        'n13'=>__('First Wednesday','church-admin'),
                        'n14'=>__('First Thursday','church-admin'),
                        'n15'=>__('First Friday','church-admin'),
                        'n16'=>__('First Saturday','church-admin'),
                        'n20'=>__('Second Sunday','church-admin'),
                        'n21'=>__('Second Monday','church-admin'),
                        'n22'=>__('Second Tuesday','church-admin'),
                        'n23'=>__('Second Wednesday','church-admin'),
                        'n24'=>__('Second Thursday','church-admin'),
                        'n25'=>__('Second Friday','church-admin'),
                        'n26'=>__('Second Saturday','church-admin'),
                        'n30'=>__('Third Sunday','church-admin'),
                        'n31'=>__('Third Monday','church-admin'),
                        'n32'=>__('Third Tuesday','church-admin'),
                        'n33'=>__('Third Wednesday','church-admin'),
                        'n34'=>__('Third Thursday','church-admin'),
                        'n35'=>__('Third Friday','church-admin'),
                        'n36'=>__('Third Saturday','church-admin'),
                        'n40'=>__('Fourth Sunday','church-admin'),
                        'n41'=>__('Fourth Monday','church-admin'),
                        'n42'=>__('Fourth Tuesday','church-admin'),
                        'n43'=>__('Fourth Wednesday','church-admin'),
                        'n44'=>__('Fourth Thursday','church-admin'),
                        'n45'=>__('Fourth Friday','church-admin'),
                        'n46'=>__('Fourth Saturday','church-admin'),
                    );
    if(!empty($_POST['save'])){
        
        $form=array();
        foreach( $_POST AS $key=>$value){
            $form[$key]=church_admin_sanitize( $value) ;
        }
        //validate service_frequency
        //default to weekly on Sunday
        if(empty($form['service_frequency'])||empty($expected_frequency[$form['service_frequency']])){
            $form['service_frequency']=70;
        }

        
        //deal with new site
        if(!empty( $form['site_name'] ) )
        {
        	$site_id=$wpdb->get_var('SELECT site_id FROM '.$wpdb->prefix.'church_admin_sites WHERE venue="'.esc_sql( $form['site_name'] ).'"');
        	if ( empty( $check) )  {
                $site_id=$wpdb->query('Insert INTO '.$wpdb->prefix.'church_admin_sites (venue)VALUES("'.esc_sql( $form['site_name'] ).'")');
            }
        	$form['site_id']=$wpdb->insert_id;
        }
        if(!empty( $form['active'] ) )  {
            $active=1;
        }else{
            $active=0;
        }
        if(!empty( $_POST['service_id'] ) )$id=(int)sanitize_text_field(stripslashes($_POST['service_id']));
        if ( empty( $id) )$id=$wpdb->get_var('SELECT service_id FROM '.$wpdb->prefix.'church_admin_services WHERE service_name="'.esc_sql( $form['service_name'] ).'" AND site_id="'.esc_sql( $form['site_id'] ).'" AND service_frequency="'.esc_sql( $form['service_frequency'] ).'"  AND service_time="'.esc_sql( $form['service_time'] ).'" ');
        church_admin_debug ("service id $id");
        if( $id)
        {//update
            $sql='UPDATE '.$wpdb->prefix.'church_admin_services SET service_name="'.esc_sql( $form['service_name'] ).'" , service_frequency="'.esc_sql( $form['service_frequency'] ).'" , service_time="'.esc_sql( $form['service_time'] ).'" , site_id="'.esc_sql( $form['site_id'] ).'", active="'.(int)$active.'",max_attendance="'.(int)$form['max_attendance'] .'",bubbles="'.(int)$form['bubbles'] .'", bubble_size="'.(int) $form['bubble_size'] .'" WHERE service_id="'.esc_sql( $id).'"';
            church_admin_debug( $sql);
            $wpdb->query( $sql);
        }//update
        else
        {//insert
           $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_services (service_name,service_frequency,service_time,site_id,active,max_attendance,bubbles,bubble_size) VALUES ("'.esc_sql( $form['service_name'] ).'","'.esc_sql( $form['service_frequency'] ).'","'.esc_sql( $form['service_time'] ).'","'.esc_sql( $form['site_id'] ).'","'.(int)$active.'","'.(int) $form['max_attendance'] .'","'.(int)$form['bubbles'] .'","'.(int)$form['bubble_size'] .'")');
            $id=$wpdb->insert_id;
        }//insert
        
        /*******************************************
        *
        *   Handle Calendar
        *
        ********************************************/
        $description =   !empty($form['description']) ? $form['description'] : null;
        $event_id = !empty($_POST['event_id'] ) ? sanitize_text_field(stripslashes($_POST['event_id'] )):null;

        if(!empty( $event_id ) && church_admin_int_check( $event_id ) )
        {
            $eventDetails=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$event_id.'" ORDER BY start_date ASC LIMIT 1');
            church_admin_debug(print_r( $eventDetails,TRUE) );
            if(!empty( $eventDetails) ){
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_services SET  first_date="'.esc_sql( $eventDetails->start_date).'", end_time="'.$eventDetails->end_time.'",how_many="'.esc_sql( $eventDetails->how_many).'",recurring="'.esc_sql( $eventDetails->recurring).'",event_id="'.intval( $eventDetails->event_id).'" WHERE service_id="'.(int)$id.'"');
                church_admin_debug($wpdb->last_query);
            }
            
        }
        else//only overwrite event if no event id
        {
            $calendar =  !empty($form['calendar'] ) ? church_admin_sanitize($_POST['calendar'] ) : null;
            if(!empty( $calendar  ) )
            {       

                //first remove current event if exists
                $event_id=$wpdb->get_var('SELECT event_id FROM '.$wpdb->prefix.'church_admin_services' .' WHERE service_id="'.(int)$id.'"');
                if( $event_id)$wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE event_id="'.(int)$event_id.'"');
                //get next event id
                $event_id=$wpdb->get_var('SELECT MAX(event_id) FROM '.$wpdb->prefix.'church_admin_calendar_date')+1;


                $form['location']=$wpdb->get_var('SELECT venue FROM '.$wpdb->prefix.'church_admin_sites WHERE site_id="'.(int) $form['site_id'] .'"');

                switch( $form['service_frequency'] )
                {
                    
                    case 'ah':
                    
                        $values[]='("'.esc_sql( $form['service_name']).'",NULL,"'.esc_sql( $form['location'] ).'","'.esc_sql( $form['start_date'] ).'","'.esc_sql( $form['service_time'] ).'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","s","1",1,"'.esc_sql(__('Service','church-admin')).'",NULL,NULL,null)';
                    break;
                    case '1':
                        //daily
                        for ( $x=0; $x<$form['how_many']; $x++)
                        {
                            $start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x day") );
                            $values[]='("'.esc_sql( $form['service_name']).'",NULL,"'.esc_sql( $form['location'] ).'","'.esc_sql( $start_date).'","'.esc_sql( $form['service_time'] ).'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","1","'.(int)$form['how_many'].'",1,"'.esc_sql(__('Service','church-admin')).'",NULL,NULL,null)';
                        }
                    break;
                    case '70':
                    case '71':
                    case '72':
                    case '73':
                    case '74':
                    case '75':
                    case '76':
                        //weekly
                        for ( $x=0; $x<$form['how_many']; $x++)
                        {
                            $start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x week") );
                            $values[]='("'.esc_sql( $form['service_name']).'",NULL,"'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['service_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","7","'.(int)$form['how_many'].'",1,"'.esc_sql(__('Service','church-admin')).'",NULL,NULL,null)';	
                        }
                    break;
                    case '14':
                        //fortnightly
                        for ( $x=0; $x<$form['how_many']; $x++)
                        {
                            $start_date=date('Y-m-d',strtotime("{$form['start_date']} + $x fortnight") );
                            $values[]='("'.esc_sql( $form['service_name']).'",NULL,"'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['service_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","14","'.(int)$form['how_many'].'",1,"'.esc_sql(__('Service','church-admin')).'",NULL,NULL,null)';	
                        }
                    break;
                    case'm':
                        //monthly
                        for ( $x=0; $x<$form['how_many']; $x++)
                        {
                            $start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x month") );
                            $values[]='("'.esc_sql( $form['service_name']).'",NULL,"'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['service_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","m","'.(int)$form['how_many'].'",1,"'.esc_sql(__('Service','church-admin')).'",NULL,NULL,null)';	
                        }

                    break;
                    
                    case'a':
                        //annually
                        for ( $x=0; $x<$form['how_many']; $x++)
                        {
                            $start_date=date('Y-m-d',strtotime("{$form['start_date']}+$x year") );
                            $values[]='("'.esc_sql( $form['service_name']).'",NULL,"'.esc_sql( $form['location'] ).'","'.$start_date.'","'.$form['service_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","a","'.(int)$form['how_many'].'",1,"'.esc_sql(__('Service','church-admin')).'",NULL,NULL,null)';	
                        }

                    break;
                    
                    default:
                        //nth day
                        $type=substr( $form['service_frequency'],0,1);//whether l or r
                        $nth=substr( $form['service_frequency'],1,1);
                        
                        $day=substr( $form['service_frequency'],2,1);
                        church_admin_debug($form['service_frequency']);
                        church_admin_debug("Nth day  n = $nth and day = $day");
                        $formdate=mysql2date('Y-m-01',$form['start_date']);//needs to be 1st of month to safely add one month each iteration
                        $rec_date=new DateTime($formdate );
                        for ( $x=0; $x<$form['how_many']; $x++)
                        {
                            
                            
                            $rec_date->modify("+ $x month");
                            $start_date=church_admin_nth_day( $nth,$day,$rec_date->format('Y-m-d') );
                            
                            //church_admin_debug("Start date $start_date");
                            if(!empty($start_date)){
                                $values[]='("'.esc_sql( $form['service_name']).'",NULL,"'.esc_sql( $form['location'] ).'","'.esc_sql($start_date).'","'.$form['service_time'].'","'.esc_sql( $form['end_time'] ).'","'.(int)$form['cat_id'].'","'.(int)$event_id.'","'.esc_sql( $form['service_frequency'] ).'","'.(int)$form['how_many'].'",1,"'.esc_sql(__('Service','church-admin')).'",NULL,NULL,null)';
                            }
                        }
                    break;
                }
                if( !empty( $values ) ){
                    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_calendar_date (title,description,location,start_date,start_time,end_time,cat_id,event_id,recurring,how_many,general_calendar,event_type,link,link_title,event_image) VALUES '.implode(",",$values);
                   
                }
                $wpdb->query( $sql);
                church_admin_debug($wpdb->last_query);
                
                $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_services SET first_date="'.esc_sql( $form['start_date'] ).'", end_time="'.$form['end_time'].'",how_many="'.esc_sql( $form['how_many'] ).'",event_id="'.(int)$event_id.'" WHERE service_id="'.(int)$id.'"');
                church_admin_debug($wpdb->last_query);
            }
        }//only overwite calendar if event id not picked!
        
        
        echo'<div class="notice notice-success"><h2>'.esc_html(__('Service saved','church-admin')).'</h2></div>';
        echo church_admin_service_list();
    }
    else{




        if( $id)$data=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_services WHERE service_id="'.(int)$id.'"');
    
        $dayID=!empty($data->service_day)?$data->service_day:null;
        echo'<h2>'.esc_html( __('Add/Edit service','church-admin' ) ).'</h2>';
        echo'<form action="" method="post">';
        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Service Name','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" required="required" name="service_name" ';
        if(!empty( $data->service_name) )echo' value="'.esc_html( $data->service_name).'" ';
        echo'/></div>';
        
        /*****************************
         * Service Frequency
         ****************************/
        
        echo'<div class="church-admin-form-group" ><label>'.esc_html( __('Service Frequency','church-admin' ) ).'</label>';
        
        $service_frequency = !empty($data->service_frequency) ? esc_attr($data->service_frequency) : '70';
        echo'<select class="church-admin-form-control" id="service_frequency" name="service_frequency">';
       
        foreach($expected_frequency AS $frequency=>$description){
            echo'<option value="'.esc_attr($frequency).'" '.selected($service_frequency,$frequency,FALSE).'>'.esc_html($description).'</option>';

        }
        
       
        echo'</select></div>';

        echo'<div class="church-admin-form-group"><label>'.esc_html( __('Service Time','church-admin' ) ).'</label><input class="church-admin-form-control"  type="time" name="service_time" ';
        if(!empty( $data->service_time) )echo' value="'.esc_html( $data->service_time).'" ';
        echo'/></div>';
        $sites=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'church_admin_sites');
        if(!empty( $sites) )
        {
                echo'<div class="church-admin-form-group"><label>'.esc_html( __('Site','church-admin' ) ).'</label><select class="church-admin-form-control"  name="site_id">';
                
                $first=$option='';
                foreach( $sites AS $site)
                {
                        if(!empty( $data->site_id)&&$site->site_id==$data->site_id)  {$first='<option selected=selected value="'.intval( $site->site_id).'">'.esc_html( $site->venue).'</option>';}
                        $option.='<option value="'.intval( $site->site_id).'">'.esc_html( $site->venue).'</option>';
                }
                echo $first.$option;
                echo'<select></div>';
            }
            echo'<div class="church-admin-form-group"><label>'.esc_html( __('Add a site','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="site_name" /></div>';
            if ( empty( $data)||!empty( $data->active) )  {$active=1;}else{$active=0;}
            echo '<div class="church-admin-form-group"><label><input type="checkbox" name="active" value=1 '.checked(1,$active,FALSE).'>'.esc_html( __('Active','church-admin' ) ).'</label></div>';
            
        
            if ( empty( $data->first_date) )  {$calendar=0;}else{$calendar=1;}
            
            $calEntries=$wpdb->get_results('SELECT event_id,title FROM '.$wpdb->prefix.'church_admin_calendar_date WHERE start_date>="'.date('Y-m-d').'" GROUP BY event_id,title');
        
            if(!empty( $calEntries) )
            {
                echo'<div class="church-admin-form-group"><label>'.esc_html( __('Connect to current calendar event','church-admin' ) ).'</label><select class="church-admin-form-control event_id" name="event_id">';
                echo'<option value="">'.esc_html( __('Select calendar event if wanted','church-admin' ) ).'</option>';
                foreach( $calEntries AS $calEntry)
                {
                    $currID=(!empty( $data->event_id) )?(int)$data->event_id:null;
                    echo '<option value="'.(int)$calEntry->event_id.'" '.selected( $currID,$calEntry->event_id,TRUE).'>'.esc_html( $calEntry->title).'</option>';
                }
                echo '</select></div>';
                
            }
                
                
                
            echo '<div class="church-admin-form-group calCheckRow"><label><input type="checkbox" name="calendar" class="calendarCheck" value="1" >'.esc_html( __('Or create new calendar event (only for regular recurring services. Use to override any previous event selection. CURRENT BOOKINGS WILL BE LOST )','church-admin' ) ).'</label></div>';
            
            echo'<div  class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __("Start date",'church-admin' ) ).'</label>';
        if(!empty( $data->first_date) )  {$db_date=$data->first_date;}else{$db_date=NULL;}
            echo  church_admin_date_picker( $db_date,'start_date',FALSE,NULL,NULL,'start_date','start_date',FALSE);
            
            
            echo'</div>';
        
            if(!empty( $data->service_frequency) )  {$service_frequency=$data->service_frequency;}else{$service_frequency=7;}
        
            

    

            $categories=church_admin_calendar_categories_array();
        
            echo'<div class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __('Calendar Category','church-admin' ) ).'</label><select class="church-admin-form-control" name="cat_id" > ';
            foreach($categories AS $cat_id=>$name){
                $curr = !empty($data->cat_id) ? (int)$data->cat_id : NULL;
                echo '<option value="'.(int)$cat_id.'"  '.selected($curr,$cat_id).'>'.esc_html( $name).'</option>';
            }
            echo'</select></div>';
            

            echo'<div class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __('How many times?','church-admin' ) ).'</label><input class="church-admin-form-control how_many" type="text" name="how_many" ';
            if(!empty( $data->how_many) )echo ' value="'.intval( $data->how_many).'" ';
            echo'/></div>';
            echo'<div class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __('Service end time','church-admin' ) ).'</label><input class="church-admin-form-control" type="time" name="end_time" class="end_time" ';
            if(!empty( $data->end_time) )echo ' value="'.esc_html( $data->end_time).'" ';
            echo'/></div>';
        
            echo'<div class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __("For covid-19 prebooking, set up either a maximum individual attendance or maximum houshold bubbles and bubble size",'church-admin' ) ).'</label></div>';
            echo '<div class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __('Max Attendance','church-admin' ) ).'</label><input class="church-admin-form-control"  type="text" name="max_attendance" class="max_attendance" ';
            if(!empty( $data->max_attendance) )echo ' value="'.esc_html( $data->max_attendance).'" ';
            echo'/></div>';
            echo '<div class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __('Max Household bubbles','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="bubbles" class="bubbles" ';
            if(!empty( $data->bubbles) )echo ' value="'.esc_html( $data->bubbles).'" ';
            echo'/></div>';
            echo '<div class="church-admin-form-group calendar" style="display:none"><label>'.esc_html( __('Household bubble max size','church-admin' ) ).'</label><input class="church-admin-form-control" type="text" name="bubble_size" class="bubble_size" ';
            if(!empty( $data->bubble_size) )echo ' value="'.esc_html( $data->bubble_size).'" ';
            echo'/></div>';
            if(!empty( $id) )echo'<input type="hidden" name="service_id" value="'.(int)$id.'" />';


        echo'<p><input type="hidden" name="save" value="service" /><input class="button-primary"  type="submit" value="'.esc_html( __('Save Service','church-admin' ) ).'&raquo;" /></p></form>';
            echo'<script>jQuery(document).ready(function( $)  {
                $(".recurring-item").on("change",function()  {
                    var val=$(this).val();
                    if(val==="n")$(".recurring-row").show();
                })
                var showCalendar=$(".calendarCheck").prop("checked");
                if(showCalendar)$(".calendar").show();
                console.log("show calendar:"+showCalendar);
                $(".calendarCheck").on("change",function()  {
                    if( $(this).is(":checked") )
                    {
                        console.log("Show calendar")
                        $(".calendar").show();
                    }
                    else
                    {
                        $(".calendar").hide();
                    }
                })
                $(".event_id").on("change",function()
                {
                    $(".calendar").hide();
                    $(".calendarCheck").prop("checked", false);
                    $(".calCheckRow").hide();
                })
                    
            });</script>';
    }
}

