<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function church_admin_volunteer_approval( $people_id,$ministry_id)
{
    global $wpdb,$current_user;
    echo '<h2>'.esc_html( __('Ministry volunteer approval','church-admin' ) ).'</h2>';
    $current_user = wp_get_current_user();

    if(empty($current_user)){
        echo wp_login_form();
        return;
    }
    $proceed = FALSE;
    if(church_admin_level_check('Ministry')){$proceed=TRUE;}
    $user_people_row = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
    if(empty($user_people_row)){
        echo'<p>'.__('You do not appear to be in any directory','church-admin');
        return;
    }

    $team_contact=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int) $ministry_id.'" AND meta_type="team_contact" AND people_id="'.(int)$user_people_row->people_id.'"');
    if(!empty($team_contact)){$proceed = TRUE;}

    if(empty($proceed)){
        echo'<p>'.__('You do not have permission to approve volunteers','church-admin');
        return;
    }

    //safe to proceed

    $ministry=$wpdb->get_var('SELECT ministry FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int) $ministry_id.'"');
    
    

        $person=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,last_name) AS name,email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
        if(!empty( $person) && !empty( $ministry) )
        {
            $contact=__('No contact details','church-admin');
            if(!empty( $person->email) )  {$contact=$person->email;}
            elseif(!empty( $person->mobile) )  {$contact=$person->mobile;}
            $wpdb->query('UPDATE '.$wpdb->prefix.'church_admin_people_meta SET meta_type="ministry" WHERE meta_type="volunteer" AND people_id="'.(int)$people_id.'" AND ID="'.(int) $ministry_id.'"');
            echo '<p><strong>'.esc_html(sprintf(__('%1$s has been approved for ministry "%2$s". Please get in touch with them at %3$s','church-admin' ) ,$person->name, $ministry, $contact )).'<strong></p>';

        }
        echo church_admin_volunteer_display();

    



}


function church_admin_volunteer_decline( $people_id,$ministry_id)
{
    global $wpdb,$current_user;
    echo '<h2>'.esc_html( __('Ministry volunteer decline','church-admin' ) ).'</h2>';
    $current_user = wp_get_current_user();

    if(empty($current_user)){
        echo wp_login_form();
        return;
    }
    $proceed = FALSE;
    if(church_admin_level_check('Ministry')){$proceed=TRUE;}

    $user_people_row = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'church_admin_people WHERE user_id="'.(int)$current_user->ID.'"');
    if(empty($user_people_row)){
        echo'<p>'.__('You do not appear to be in any directory','church-admin');
        return;
    }

    $team_contact=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people_meta WHERE ID="'.(int) $ministry_id.'" AND meta_type="team_contact" AND people_id="'.(int)$user_people_row->people_id.'"');
    if(!empty($team_contact)){$proceed = TRUE;}

    if(empty($proceed)){
        echo'<p>'.__('You do not have permission to decline volunteers','church-admin');
        return;
    }

    //safe to proceed
    $ministry=$wpdb->get_var('SELECT ministry FROM '.$wpdb->prefix.'church_admin_ministries WHERE ID="'.(int) $ministry_id.'"');
       
    $person=$wpdb->get_row('SELECT CONCAT_WS(" ",first_name,last_name) AS name,email FROM '.$wpdb->prefix.'church_admin_people WHERE people_id="'.(int)$people_id.'"');
    if(!empty( $person) )
    {
        $contact=__('No contact details','church-admin');
        if(!empty( $person->email) )  {$contact=$person->email;}
        elseif(!empty( $person->mobile) )  {$contact=$person->mobile;}
        $wpdb->query('DELETE FROM '.$wpdb->prefix.'church_admin_people_meta WHERE meta_type="volunteer"  AND people_id="'.(int)$people_id.'" AND ID="'.intval( $ministry_id).'"');
        echo '<p><strong>'.esc_html(sprintf(__('%1$s has been declined for ministry "%2$s". Please get in touch with them at %3$s','church-admin' ) , $person->name,$ministry, $contact) ) .'<strong></p>';

    }
		echo church_admin_volunteer_display();
    



}

function church_admin_volunteer_display()
{

	global $wpdb;
	$out='';
    
	$results=$wpdb->get_results('SELECT CONCAT_WS(" ", a.first_name,a.last_name) AS name,a.people_id,b.meta_date AS date, c.ministry,c.ID as ministry_id FROM '.$wpdb->prefix.'church_admin_people a, '.$wpdb->prefix.'church_admin_people_meta b, '.$wpdb->prefix.'church_admin_ministries c WHERE a.people_id=b.people_id AND b.meta_type="volunteer" AND b.ID=c.ID');
	if(!empty( $results) )
	{
		$out.='<table class="widefat wp-list-table striped"><thead><tr><th>'.esc_html( __('Approve','church-admin' ) ).'</th><th>'.esc_html( __('Decline','church-admin' ) ).'</th><th>'.esc_html( __('Date requested','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Ministry task','church-admin' ) ).'</th></tr></thead><tbody>';
		foreach( $results AS $row)
		{
				$approve = '<a href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=approve-volunteer&ministry_id='.intval( $row->ministry_id).'&people_id='.(int)$row->people_id,'approve-volunteer').'">'.esc_html( __("Approve",'church-admin' ) ).'</a>';
                
				$decline='<a href="'.wp_nonce_url(admin_url().'admin.php?page=church_admin/index.php&amp;action=decline-volunteer&ministry_id='.intval( $row->ministry_id).'&people_id='.(int)$row->people_id,'decline-volunteer').'">'.esc_html( __('Decline','church-admin' ) ).'</a>';
				$out.='<tr><td>'.$approve.'</td><td>'.$decline.'</td><td>'.mysql2date(get_option('date_format'),$row->date).'</td><td>'.esc_html( $row->name).'</td><td>'.esc_html( $row->ministry).'</td></tr>';
		}
		$out.='</tbody><tfoot><tr><th>'.esc_html( __('Approve','church-admin' ) ).'</th><th>'.esc_html( __('Decline','church-admin' ) ).'</th><th>'.esc_html( __('Date requested','church-admin' ) ).'</th><th>'.esc_html( __('Name','church-admin' ) ).'</th><th>'.esc_html( __('Ministry task','church-admin' ) ).'</th></tr></tfoot></table>';
	}
	else{$out.='<p>'.esc_html( __('No serving requests currently','church-admin' ) ).'</p>';}
	return $out;
}
