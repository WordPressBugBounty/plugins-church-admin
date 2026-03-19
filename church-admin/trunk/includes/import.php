<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function church_admin_import_from_users()
{
    global $wpdb;
    $current_user=wp_get_current_user();
    $results=$wpdb->get_results('SELECT * FROM '.$wpdb->users);
    if ( empty( $results) )return __('No user accounts found, which is impossible!','church-admin');

    foreach( $results AS $row)
    {
        $first_name=$last_name="";
        $first_name=get_user_meta( $row->ID,'first_name',TRUE);
        $last_name=get_user_meta( $row->ID,'last_name',TRUE);
        if ( empty( $last_name) )$last_name=$row->user_nicename;
        $people_id=$wpdb->get_var('SELECT people_id FROM '.$wpdb->prefix.'church_admin_people WHERE email="'.esc_sql( $row->user_email).'"');

        if ( empty( $people_id) )
        {
           
            
            $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address) VALUES("")');
            $household_id=$wpdb->insert_id;
            $people_id=$wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_people (updated_by,people_type_id,head_of_household,member_type_id,user_id,household_id,show_me,gdpr_reason,first_name,last_name,email,first_registered) VALUES("'.$current_user->ID.'",1,1,1,"'.(int)$row->ID.'","'.(int)$household_id.'",0,"'.esc_sql(__('Imported from website user account')).'","'.esc_sql( $first_name).'","'.esc_sql( $last_name).'","'.esc_sql( $row->user_email).'","'.esc_sql(mysql2date('Y-m-d',$row->user_registered)).'")');
            /* translators: 1: is a user ID, 2:name part 3: name part 4:part of a name */
            echo '<p>'.esc_html(sprintf(__('User %1$s: %2$s %3$s %4$s added.','church-admin' ) ,(int)$row->ID,esc_html( $first_name),esc_html( $last_name),esc_html( $row->user_email) ));
        }
        else
        {
             /* translators: 1: is a user ID, 2: name part 3: name part */
            echo'<p>'.esc_html(sprintf(__('User %1$s: %2$s %3$s already in the directory','church-admin' ) ,(int)$row->ID, $first_name, $last_name)).'</p>';
        }
    }



}